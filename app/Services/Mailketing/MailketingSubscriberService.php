<?php

namespace App\Services\Mailketing;

use App\Models\Course;
use App\Models\Event;
use App\Models\MailketingList;
use App\Models\MailketingSubscriberLog;
use App\Models\User;
use App\Services\Settings\AppSettingService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class MailketingSubscriberService
{
    public function __construct(
        protected MailketingClient $client,
        protected AppSettingService $settings,
    ) {}

    public function addUserToDefaultList(User $user): void
    {
        $this->addToConfiguredList($user, 'mailketing_default_list_id', 'user_registered');
    }

    public function addCustomerToCustomerList(User $user): void
    {
        $this->addToConfiguredList($user, 'mailketing_customer_list_id', 'order_paid_customer');
    }

    public function addEpiChannelToEpiChannelList(User $user): void
    {
        $this->addToConfiguredList($user, 'mailketing_epi_channel_list_id', 'epi_channel_active', [
            'company' => 'EPI Channel',
        ]);
    }

    public function addEventParticipantToList(User $user, Event $event): void
    {
        $this->addToConfiguredList($user, 'mailketing_event_participant_list_id', 'event_registration', [
            'event_id' => $event->id,
            'event_title' => $event->title,
        ]);
    }

    public function addCourseStudentToList(User $user, Course $course): void
    {
        $this->addToConfiguredList($user, 'mailketing_course_student_list_id', 'course_enrollment', [
            'course_id' => $course->id,
            'course_title' => $course->title,
        ]);
    }

    public function addToList(User $user, $listId, array $metadata = []): void
    {
        $email = trim((string) $user->email);
        $eventType = (string) ($metadata['event_type'] ?? 'subscriber_automation');
        $listId = is_scalar($listId) ? trim((string) $listId) : '';
        $listName = $this->resolveListName($listId);

        if (! $this->isAutomationEnabled()) {
            $this->logResult($user, $listId, $listName, $email, 'skipped', [], 'automation_disabled', $eventType, $metadata);
            return;
        }

        if ($email === '') {
            $this->logResult($user, $listId, $listName, $email, 'skipped', [], 'missing_email', $eventType, $metadata);
            return;
        }

        if ($listId === '') {
            $this->logResult($user, $listId, $listName, $email, 'skipped', [], 'missing_list_id', $eventType, $metadata);
            return;
        }

        try {
            $result = $this->client->addSubscriberToList($this->buildPayload($user, $listId, $metadata));

            $this->logResult(
                user: $user,
                listId: $listId,
                listName: $listName,
                email: $email,
                status: $result['success'] ? 'sent' : 'failed',
                response: $result['raw'] ?? [],
                errorMessage: $result['success'] ? null : ($result['message'] ?? 'Unknown error'),
                eventType: $eventType,
                metadata: Arr::except($metadata, ['event_type']),
            );
        } catch (\Throwable $e) {
            Log::error('MailketingSubscriberService: gagal add subscriber ke list', [
                'user_id' => $user->id,
                'list_id' => $listId,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);

            $this->logResult($user, $listId, $listName, $email, 'failed', [], $e->getMessage(), $eventType, $metadata);
        }
    }

    private function addToConfiguredList(User $user, string $settingKey, string $eventType, array $metadata = []): void
    {
        $this->addToList(
            user: $user,
            listId: (string) $this->settings->getMailketing($settingKey, ''),
            metadata: array_merge($metadata, ['event_type' => $eventType]),
        );
    }

    private function isAutomationEnabled(): bool
    {
        return (bool) $this->settings->getMailketing('enable_subscriber_automation', false);
    }

    private function buildPayload(User $user, string $listId, array $metadata): array
    {
        [$firstName, $lastName] = $this->splitName((string) $user->name);
        $phone = $user->normalizedWhatsappNumber();

        return array_filter([
            'list_id' => $listId,
            'email' => $user->email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'city' => $this->resolveOptionalUserField($user, ['city', 'kota']),
            'state' => $this->resolveOptionalUserField($user, ['state', 'province', 'provinsi']),
            'country' => 'Indonesia',
            'company' => (string) ($metadata['company'] ?? 'EPIC HUB'),
            'phone' => $phone,
            'mobile' => $phone,
        ], fn (mixed $value): bool => ! blank($value));
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return [
            $parts[0] ?? '',
            count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '',
        ];
    }

    private function resolveOptionalUserField(User $user, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = data_get($user, $key);

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }

            $value = data_get($user->getAttributes(), $key);

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private function resolveListName(string $listId): ?string
    {
        if ($listId === '') {
            return null;
        }

        return MailketingList::query()
            ->where('list_id', $listId)
            ->value('list_name');
    }

    private function logResult(
        User $user,
        string $listId,
        ?string $listName,
        string $email,
        string $status,
        array $response,
        ?string $errorMessage,
        string $eventType,
        array $metadata,
    ): void {
        MailketingSubscriberLog::query()->create([
            'list_id' => $listId !== '' ? $listId : null,
            'list_name' => $listName,
            'user_id' => $user->id,
            'email' => $email,
            'status' => $status,
            'response' => $response !== [] ? $response : null,
            'error_message' => $errorMessage,
            'event_type' => $eventType,
            'metadata' => Arr::except($metadata, ['event_type']),
        ]);
    }
}
