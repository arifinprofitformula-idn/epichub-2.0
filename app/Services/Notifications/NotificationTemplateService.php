<?php

namespace App\Services\Notifications;

use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Schema;

class NotificationTemplateService
{
    public function __construct(
        protected NotificationShortcodeRegistry $registry,
    ) {}

    // ── Read ─────────────────────────────────────────────────────────────────

    public function getTemplate(string $eventKey, string $targetKey): ?NotificationTemplate
    {
        if (! $this->tableExists()) {
            return null;
        }

        return NotificationTemplate::findTemplate(
            $this->registry->resolveTemplateEventKey($eventKey, $targetKey),
            $targetKey,
        );
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, NotificationTemplate> */
    public function getTemplatesByEvent(string $eventKey): \Illuminate\Database\Eloquent\Collection
    {
        if (! $this->tableExists()) {
            return collect();
        }

        return NotificationTemplate::forEvent($eventKey);
    }

    /** @return array<string, array<string, NotificationTemplate>> keyed by [event_key][target_key] */
    public function getAllTemplatesGrouped(): array
    {
        if (! $this->tableExists()) {
            return [];
        }

        return NotificationTemplate::all()
            ->groupBy('event_key')
            ->map(fn ($group) => $group->keyBy('target_key'))
            ->toArray();
    }

    // ── Write ─────────────────────────────────────────────────────────────────

    /** @param array<string, mixed> $data */
    public function saveTemplate(array $data): NotificationTemplate
    {
        $template = NotificationTemplate::firstOrNew([
            'event_key'  => $data['event_key'],
            'target_key' => $data['target_key'],
        ]);

        $template->fill([
            'email_enabled'   => (bool) ($data['email_enabled'] ?? true),
            'whatsapp_enabled'=> (bool) ($data['whatsapp_enabled'] ?? true),
            'email_subject'   => $data['email_subject'] ?? null,
            'email_body'      => $data['email_body'] ?? null,
            'whatsapp_body'   => $data['whatsapp_body'] ?? null,
        ]);

        $template->save();

        return $template;
    }

    public function resetToDefault(string $eventKey, string $targetKey): bool
    {
        $template = $this->getTemplate($eventKey, $targetKey);

        if (! $template) {
            return false;
        }

        $template->resetToDefault();

        return true;
    }

    public function resetEventToDefault(string $eventKey): int
    {
        $templates = $this->getTemplatesByEvent($eventKey);
        $count     = 0;

        foreach ($templates as $template) {
            $template->resetToDefault();
            $count++;
        }

        return $count;
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function renderEmailSubject(string $eventKey, string $targetKey, array $payload): string
    {
        $template = $this->getTemplate($eventKey, $targetKey);
        $content  = $template?->email_subject ?? '';

        if (blank($content)) {
            return '';
        }

        return $this->registry->render($content, $payload, $eventKey);
    }

    public function renderEmailBody(string $eventKey, string $targetKey, array $payload): string
    {
        $template = $this->getTemplate($eventKey, $targetKey);
        $content  = $template?->email_body ?? '';

        if (blank($content)) {
            return '';
        }

        return $this->registry->render($content, $payload, $eventKey);
    }

    public function renderWhatsAppBody(string $eventKey, string $targetKey, array $payload): string
    {
        $template = $this->getTemplate($eventKey, $targetKey);
        $content  = $template?->whatsapp_body ?? '';

        if (blank($content)) {
            return '';
        }

        return $this->registry->render($content, $payload, $eventKey);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    /**
     * Validate all template fields for a given event+target.
     *
     * @return array{warnings: string[], deprecated: string[], invalid: string[]}
     */
    public function validateTemplateFields(string $eventKey, string $targetKey, array $fields): array
    {
        $warnings   = [];
        $deprecated = [];
        $invalid    = [];

        $fieldsToCheck = array_filter([
            $fields['email_subject'] ?? null,
            $fields['email_body'] ?? null,
            $fields['whatsapp_body'] ?? null,
        ]);

        foreach ($fieldsToCheck as $content) {
            if (blank($content)) {
                continue;
            }

            $result = $this->registry->validateContent($content, $eventKey, $targetKey);

            foreach ($result['invalid_shortcodes'] as $key) {
                $hint = isset($result['suggestions'][$key])
                    ? " (mungkin maksud Anda: {{$result['suggestions'][$key]}}?)"
                    : '';
                $invalid[] = "Shortcode {{$key}} tidak dikenal{$hint}.";
            }

            foreach ($result['deprecated_aliases'] as $alias => $canonical) {
                $deprecated[] = "Shortcode {{$alias}} masih didukung, tetapi disarankan menggunakan {{$canonical}}.";
            }
        }

        return [
            'warnings'   => array_unique($warnings),
            'deprecated' => array_unique($deprecated),
            'invalid'    => array_unique($invalid),
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function tableExists(): bool
    {
        return Schema::hasTable('notification_templates');
    }
}
