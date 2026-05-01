<?php

namespace App\Services\Notifications;

use App\Models\NotificationTemplate;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Log;

/**
 * Titik sentral pengiriman notifikasi Step 4.
 *
 * Flow per channel:
 * 1. Ambil template dari notification_templates (DB).
 * 2. Cek email_enabled / whatsapp_enabled pada template.
 *    - false  → log skipped + return (tidak kirim, tidak fallback).
 * 3. Render subject/body menggunakan NotificationShortcodeRegistry.
 * 4. Jika template ditemukan dan body tidak kosong → kirim via Email/WA service.
 * 5. Jika template tidak ditemukan ATAU body kosong → jalankan $fallback().
 *
 * Landing Page ZIP {{double_brace}} tidak disentuh — hanya {snake_case}.
 */
class NotificationDispatcher
{
    public function __construct(
        protected NotificationTemplateService    $templateService,
        protected NotificationShortcodeRegistry  $registry,
        protected EmailNotificationService       $emailService,
        protected WhatsAppNotificationService    $whatsAppService,
        protected WhatsAppMessageTemplateService $legacyTemplateService,
    ) {}

    // ── Email helpers ────────────────────────────────────────────────────────

    /**
     * Kirim email ke member.
     * $fallback dipanggil jika template DB tidak ditemukan atau body kosong.
     */
    public function notifyMemberEmail(
        string $eventKey,
        User $user,
        array $payload,
        mixed $notifiable,
        Closure $fallback,
    ): void {
        $this->sendEmail(
            eventKey:   $eventKey,
            targetKey:  'member',
            recipient:  ['email' => $user->email, 'name' => $user->name],
            payload:    $payload,
            notifiable: $notifiable,
            fallback:   $fallback,
        );
    }

    /**
     * Kirim email ke semua admin notification email dari settings.
     */
    public function notifyAdminEmail(
        string $eventKey,
        array $payload,
        mixed $notifiable,
        Closure $fallback,
    ): void {
        $this->sendAdminEmail(
            eventKey:   $eventKey,
            payload:    $payload,
            notifiable: $notifiable,
            fallback:   $fallback,
        );
    }

    /**
     * Kirim email ke sponsor/affiliate (EPI Channel user).
     */
    public function notifySponsorEmail(
        string $eventKey,
        User $user,
        array $payload,
        mixed $notifiable,
        Closure $fallback,
    ): void {
        $this->sendEmail(
            eventKey:   $eventKey,
            targetKey:  'sponsor',
            recipient:  ['email' => $user->email, 'name' => $user->name],
            payload:    $payload,
            notifiable: $notifiable,
            fallback:   $fallback,
        );
    }

    // ── WhatsApp helpers ─────────────────────────────────────────────────────

    /**
     * Kirim WhatsApp ke member.
     * $legacyData dipakai oleh WhatsAppMessageTemplateService sebagai fallback.
     */
    public function notifyMemberWhatsApp(
        string $eventKey,
        User $user,
        array $payload,
        mixed $notifiable,
        array $legacyData = [],
    ): void {
        $this->sendWhatsApp(
            eventKey:      $eventKey,
            targetKey:     'member',
            phone:         (string) ($user->whatsapp_number ?? ''),
            payload:       $payload,
            notifiable:    $notifiable,
            legacyData:    $legacyData,
            recipientName: $user->name,
        );
    }

    /**
     * Kirim WhatsApp ke semua admin phone dari DripSender settings.
     */
    public function notifyAdminWhatsApp(
        string $eventKey,
        array $payload,
        mixed $notifiable,
        array $legacyData = [],
    ): void {
        $template = $this->getTemplate($eventKey, 'admin');

        if ($template !== null && ! $template->whatsapp_enabled) {
            $this->logWhatsAppSkipped(eventKey: $eventKey, targetKey: 'admin', reason: 'template_whatsapp_disabled', templateId: $template->id, notifiable: $notifiable);
            return;
        }

        $message = $this->renderWhatsAppMessage($eventKey, 'admin', $payload, $template, $legacyData);

        if (blank($message)) {
            return;
        }

        $this->whatsAppService->sendAdminAlert(
            message:  $message,
            eventType: $eventKey,
            metadata: $this->withTemplateMeta(
                base:           ['notifiable' => $notifiable],
                targetKey:      'admin',
                templateId:     $template?->id,
                templateSource: $this->resolveSource($template, $message, $legacyData, $eventKey),
            ),
        );
    }

    /**
     * Kirim WhatsApp ke sponsor/affiliate.
     */
    public function notifySponsorWhatsApp(
        string $eventKey,
        User $user,
        array $payload,
        mixed $notifiable,
        array $legacyData = [],
    ): void {
        $this->sendWhatsApp(
            eventKey:      $eventKey,
            targetKey:     'sponsor',
            phone:         (string) ($user->whatsapp_number ?? ''),
            payload:       $payload,
            notifiable:    $notifiable,
            legacyData:    $legacyData,
            recipientName: $user->name,
        );
    }

    // ── Core dispatch ────────────────────────────────────────────────────────

    private function sendEmail(
        string $eventKey,
        string $targetKey,
        array $recipient,
        array $payload,
        mixed $notifiable,
        Closure $fallback,
    ): void {
        $template = $this->getTemplate($eventKey, $targetKey);

        if ($template !== null && ! $template->email_enabled) {
            $this->logEmailSkipped(
                eventKey:   $eventKey,
                targetKey:  $targetKey,
                email:      $recipient['email'],
                reason:     'template_email_disabled',
                templateId: $template->id,
                notifiable: $notifiable,
            );
            return;
        }

        if ($template !== null) {
            $subject = trim($this->registry->render($template->email_subject ?? '', $payload, $eventKey));
            $body    = trim($this->registry->render($template->email_body ?? '', $payload, $eventKey));

            if (! blank($subject) && ! blank($body)) {
                $html = $this->wrapBodyAsHtml($body);

                $this->emailService->sendRenderedTransactionalEmail(
                    recipient:   $recipient,
                    subject:     $subject,
                    htmlContent: $html,
                    eventType:   $eventKey,
                    metadata:    $this->withTemplateMeta(
                        base:           ['notifiable' => $notifiable],
                        targetKey:      $targetKey,
                        templateId:     $template->id,
                        templateSource: 'database',
                    ),
                );

                return;
            }

            Log::warning("NotificationDispatcher: template email kosong [{$eventKey}/{$targetKey}], fallback ke Blade view.");
        }

        // Fallback ke Blade view existing
        try {
            $fallback();
        } catch (\Throwable $e) {
            Log::error("NotificationDispatcher: email fallback gagal [{$eventKey}/{$targetKey}]", ['error' => $e->getMessage()]);
        }
    }

    private function sendAdminEmail(
        string $eventKey,
        array $payload,
        mixed $notifiable,
        Closure $fallback,
    ): void {
        $template = $this->getTemplate($eventKey, 'admin');

        if ($template !== null && ! $template->email_enabled) {
            $this->logEmailSkipped(
                eventKey:   $eventKey,
                targetKey:  'admin',
                email:      'admin',
                reason:     'template_email_disabled',
                templateId: $template->id,
                notifiable: $notifiable,
            );
            return;
        }

        if ($template !== null) {
            $subject = trim($this->registry->render($template->email_subject ?? '', $payload, $eventKey));
            $body    = trim($this->registry->render($template->email_body ?? '', $payload, $eventKey));

            if (! blank($subject) && ! blank($body)) {
                $this->emailService->sendRenderedAdminNotification(
                    subject:     $subject,
                    htmlContent: $this->wrapBodyAsHtml($body),
                    eventType:   $eventKey,
                    metadata:    $this->withTemplateMeta(
                        base:           ['notifiable' => $notifiable],
                        targetKey:      'admin',
                        templateId:     $template->id,
                        templateSource: 'database',
                    ),
                );

                return;
            }
        }

        try {
            $fallback();
        } catch (\Throwable $e) {
            Log::error("NotificationDispatcher: admin email fallback gagal [{$eventKey}]", ['error' => $e->getMessage()]);
        }
    }

    private function sendWhatsApp(
        string $eventKey,
        string $targetKey,
        string $phone,
        array $payload,
        mixed $notifiable,
        array $legacyData,
        ?string $recipientName = null,
    ): void {
        $template = $this->getTemplate($eventKey, $targetKey);

        if ($template !== null && ! $template->whatsapp_enabled) {
            $this->logWhatsAppSkipped(
                eventKey:   $eventKey,
                targetKey:  $targetKey,
                reason:     'template_whatsapp_disabled',
                templateId: $template->id,
                notifiable: $notifiable,
                phone:      $phone,
            );
            return;
        }

        $message       = $this->renderWhatsAppMessage($eventKey, $targetKey, $payload, $template, $legacyData);
        $templateSource = $this->resolveSource($template, $message, $legacyData, $eventKey);

        if (blank($message)) {
            Log::warning("NotificationDispatcher: WhatsApp message kosong [{$eventKey}/{$targetKey}], skip.");
            return;
        }

        $this->whatsAppService->sendToPhone(
            phone:     $phone,
            message:   $message,
            eventType: $eventKey,
            metadata:  $this->withTemplateMeta(
                base:           ['notifiable' => $notifiable, 'recipient_name' => $recipientName],
                targetKey:      $targetKey,
                templateId:     $template?->id,
                templateSource: $templateSource,
            ),
        );
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function getTemplate(string $eventKey, string $targetKey): ?NotificationTemplate
    {
        try {
            return $this->templateService->getTemplate($eventKey, $targetKey);
        } catch (\Throwable $e) {
            Log::warning("NotificationDispatcher: gagal ambil template [{$eventKey}/{$targetKey}]", ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function renderWhatsAppMessage(
        string $eventKey,
        string $targetKey,
        array $payload,
        ?NotificationTemplate $template,
        array $legacyData,
    ): string {
        if ($template !== null) {
            $rendered = trim($this->registry->render($template->whatsapp_body ?? '', $payload, $eventKey));
            if (! blank($rendered)) {
                return $rendered;
            }
        }

        // Fallback ke WhatsAppMessageTemplateService hardcoded
        return $this->legacyTemplateService->render($eventKey, $legacyData);
    }

    private function resolveSource(?NotificationTemplate $template, string $message, array $legacyData, string $eventKey): string
    {
        if ($template === null) {
            return 'fallback';
        }

        $rendered = trim($this->registry->render($template->whatsapp_body ?? '', [], $eventKey));
        $legacyMsg = $this->legacyTemplateService->render($eventKey, $legacyData);

        return $message === $legacyMsg ? 'fallback' : 'database';
    }

    /** Bungkus plain text sebagai HTML aman dengan whitespace dipertahankan. */
    private function wrapBodyAsHtml(string $body): string
    {
        $escaped = htmlspecialchars($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return '<div style="font-family: Arial, sans-serif; font-size: 14px; line-height: 1.7; color: #333; white-space: pre-line; padding: 0;">'
            . $escaped
            . '</div>';
    }

    private function withTemplateMeta(array $base, string $targetKey, ?int $templateId, string $templateSource): array
    {
        $base['target_key']      = $targetKey;
        $base['template_id']     = $templateId;
        $base['template_source'] = $templateSource;

        return $base;
    }

    private function logEmailSkipped(string $eventKey, string $targetKey, string $email, string $reason, int $templateId, mixed $notifiable): void
    {
        Log::info("NotificationDispatcher: email skipped [{$eventKey}/{$targetKey}]", [
            'reason'      => $reason,
            'recipient'   => $email,
            'template_id' => $templateId,
        ]);
    }

    private function logWhatsAppSkipped(string $eventKey, string $targetKey, string $reason, int $templateId, mixed $notifiable, string $phone = ''): void
    {
        $this->whatsAppService->logMessage(
            recipientPhone: $phone ?: 'unknown',
            message:        '',
            eventType:      $eventKey,
            status:         'skipped',
            metadata:       ['target_key' => $targetKey, 'template_id' => $templateId],
            errorMessage:   $reason,
            notifiable:     $notifiable instanceof \Illuminate\Database\Eloquent\Model ? $notifiable : null,
        );
    }
}
