<?php

namespace App\Services\Mailketing;

use App\Models\EmailNotificationLog;
use App\Services\Settings\AppSettingService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailketingClient
{
    private const BASE_URL = 'https://api.mailketing.co.id/api/v1';

    private AppSettingService $settings;

    public function __construct(AppSettingService $settings)
    {
        $this->settings = $settings;
    }

    public function isEnabled(): bool
    {
        return (bool) $this->settings->getMailketing('enable_mailketing', false);
    }

    /** @return array{ok: bool, message: string, missing: string[]} */
    public function validateSettings(): array
    {
        $missing = [];

        if (! $this->isEnabled()) {
            return ['ok' => false, 'message' => 'Mailketing tidak diaktifkan.', 'missing' => ['enable_mailketing']];
        }

        if (blank($this->apiToken())) {
            $missing[] = 'mailketing_api_token';
        }
        if (blank($this->settings->getMailketing('mailketing_from_name'))) {
            $missing[] = 'mailketing_from_name';
        }
        if (blank($this->settings->getMailketing('mailketing_from_email'))) {
            $missing[] = 'mailketing_from_email';
        }

        return [
            'ok'      => empty($missing),
            'message' => empty($missing) ? 'Pengaturan lengkap.' : 'Pengaturan tidak lengkap: '.implode(', ', $missing),
            'missing' => $missing,
        ];
    }

    /**
     * Kirim email via Mailketing.
     *
     * @param  array{recipient: string, subject: string, content: string, recipient_name?: string, event_type?: string, notifiable_type?: string, notifiable_id?: mixed, log_metadata?: array<string, mixed>, suppress_logging?: bool}  $payload
     * @return array{success: bool, status: string, message: string, raw: array}
     */
    public function sendEmail(array $payload): array
    {
        $validation = $this->validateSettings();
        if (! $validation['ok']) {
            return $this->failure($validation['message']);
        }

        $logEntry = null;

        if (! (bool) ($payload['suppress_logging'] ?? false)) {
            $logEntry = EmailNotificationLog::record([
                'event_type'      => $payload['event_type'] ?? null,
                'notifiable_type' => $payload['notifiable_type'] ?? null,
                'notifiable_id'   => $payload['notifiable_id'] ?? null,
                'recipient_email' => $payload['recipient'],
                'recipient_name'  => $payload['recipient_name'] ?? null,
                'subject'         => $payload['subject'],
                'status'          => 'pending',
                'metadata'        => $payload['log_metadata'] ?? [],
            ]);
        }

        $post = [
            'api_token'  => $this->apiToken(),
            'from_name'  => $this->settings->getMailketing('mailketing_from_name'),
            'from_email' => $this->settings->getMailketing('mailketing_from_email'),
            'recipient'  => $payload['recipient'],
            'subject'    => $payload['subject'],
            'content'    => $payload['content'],
        ];

        foreach (['attach1', 'attach2', 'attach3'] as $att) {
            if (! empty($payload[$att])) {
                $post[$att] = $payload[$att];
            }
        }

        $result = $this->post('/send', $post);

        if ($result['success']) {
            $logEntry?->update([
                'status'            => 'sent',
                'provider_response' => $result['raw'],
                'sent_at'           => now(),
            ]);
        } else {
            $logEntry?->update([
                'status'            => 'failed',
                'provider_response' => $result['raw'],
                'error_message'     => $result['message'],
                'failed_at'         => now(),
            ]);
        }

        return $result;
    }

    /**
     * Ambil semua list dari Mailketing.
     *
     * @return array{success: bool, status: string, message: string, raw: array, lists: array}
     */
    public function getLists(): array
    {
        if (blank($this->apiToken())) {
            return array_merge($this->failure('API token kosong.'), ['lists' => []]);
        }

        $result = $this->post('/viewlist', ['api_token' => $this->apiToken()]);

        return array_merge($result, [
            'lists' => $result['raw']['lists'] ?? [],
        ]);
    }

    /**
     * Tambah subscriber ke list Mailketing.
     *
     * @param  array{list_id: int|string, email: string, first_name?: string, last_name?: string, city?: string, state?: string, country?: string, company?: string, phone?: string, mobile?: string}  $payload
     * @return array{success: bool, status: string, message: string, raw: array}
     */
    public function addSubscriberToList(array $payload): array
    {
        $validation = $this->validateSettings();
        if (! $validation['ok']) {
            return $this->failure($validation['message']);
        }

        return $this->post('/addsubtolist', array_merge(
            ['api_token' => $this->apiToken()],
            $payload,
        ));
    }

    /** @return array{success: bool, status: string, message: string, raw: array} */
    private function post(string $endpoint, array $data): array
    {
        try {
            $response = Http::asForm()
                ->timeout(30)
                ->post(self::BASE_URL.$endpoint, $data);

            $body = $response->json() ?? [];

            if (! is_array($body)) {
                $body = [];
            }

            $apiStatus = $body['status'] ?? '';

            if ($response->successful() && $apiStatus === 'success') {
                return [
                    'success' => true,
                    'status'  => 'success',
                    'message' => $body['response'] ?? 'OK',
                    'raw'     => $body,
                ];
            }

            $errorMessage = $this->parseError($body, $response->status());

            return [
                'success' => false,
                'status'  => $apiStatus ?: 'error',
                'message' => $errorMessage,
                'raw'     => $body,
            ];
        } catch (ConnectionException $e) {
            Log::warning('MailketingClient: connection timeout', ['endpoint' => $endpoint]);

            return $this->failure('Koneksi ke Mailketing timeout atau gagal: '.$e->getMessage());
        } catch (\Throwable $e) {
            Log::error('MailketingClient: unexpected error', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);

            return $this->failure('Error tidak terduga: '.$e->getMessage());
        }
    }

    private function parseError(array $body, int $httpStatus): string
    {
        $response = strtolower($body['response'] ?? '');

        return match (true) {
            str_contains($response, 'wrong api token')   => 'API token salah atau tidak valid.',
            str_contains($response, 'unknown sender')    => 'Pengirim (from_email) tidak dikenal atau belum diverifikasi.',
            str_contains($response, 'no credits')        => 'Kredit Mailketing habis.',
            str_contains($response, 'empty recipient')   => 'Email penerima tidak boleh kosong.',
            str_contains($response, 'empty subject')     => 'Subject tidak boleh kosong.',
            str_contains($response, 'empty content')     => 'Konten email tidak boleh kosong.',
            str_contains($response, 'blacklist')         => 'Email penerima ada di blacklist.',
            $httpStatus === 401                          => 'Autentikasi gagal (HTTP 401).',
            $httpStatus === 429                          => 'Rate limit Mailketing terlampaui (HTTP 429).',
            default                                      => filled($body['response'] ?? '') ? $body['response'] : "HTTP {$httpStatus}: Permintaan gagal.",
        };
    }

    /** @return array{success: bool, status: string, message: string, raw: array} */
    private function failure(string $message): array
    {
        return ['success' => false, 'status' => 'error', 'message' => $message, 'raw' => []];
    }

    private function apiToken(): string
    {
        return (string) $this->settings->getMailketing('mailketing_api_token', '');
    }
}
