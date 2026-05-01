<?php

namespace App\Services\DripSender;

use App\Services\Settings\AppSettingService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DripSenderClient
{
    public function __construct(
        protected AppSettingService $settings,
    ) {}

    public function isEnabled(): bool
    {
        return (bool) $this->settings->getDripSender('enable_dripsender', false);
    }

    /** @return array{ok: bool, message: string, missing: list<string>} */
    public function validateSettings(): array
    {
        $missing = [];

        if (! $this->isEnabled()) {
            return [
                'ok' => false,
                'message' => 'DripSender tidak diaktifkan.',
                'missing' => ['enable_dripsender'],
            ];
        }

        if (blank($this->apiKey())) {
            $missing[] = 'dripsender_api_key';
        }

        if (blank($this->baseUrl())) {
            $missing[] = 'dripsender_base_url';
        }

        return [
            'ok' => $missing === [],
            'message' => $missing === [] ? 'Pengaturan lengkap.' : 'Pengaturan tidak lengkap: '.implode(', ', $missing),
            'missing' => $missing,
        ];
    }

    /** @param array<string, mixed> $payload
     *  @return array{success: bool, status: string, message: string, raw: mixed, http_status: int|null}
     */
    public function sendMessage(array $payload): array
    {
        $validation = $this->validateSettings();

        if (! $validation['ok']) {
            return $this->failure($validation['message']);
        }

        $phone = $this->normalizePhone((string) ($payload['phone'] ?? ''));
        $text = trim((string) ($payload['text'] ?? ''));

        if ($phone === null) {
            return $this->failure('Nomor WhatsApp kosong atau tidak valid.');
        }

        if ($text === '') {
            return $this->failure('Pesan WhatsApp tidak boleh kosong.');
        }

        $body = array_filter([
            'api_key' => $this->apiKey(),
            'phone' => $phone,
            'text' => $text,
            'media_url' => $payload['media_url'] ?? null,
            'group_id' => $payload['group_id'] ?? null,
            'isInteractive' => $payload['isInteractive'] ?? null,
            'footer' => $payload['footer'] ?? null,
            'buttons' => $payload['buttons'] ?? null,
            'send_at' => $payload['send_at'] ?? null,
        ], fn (mixed $value): bool => $value !== null && $value !== '');

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->timeout($this->requestTimeout())
                ->post($this->baseUrl().'/send', $body);

            return $this->normalizeSendResponse($response);
        } catch (ConnectionException $e) {
            Log::warning('DripSenderClient: connection error on send', ['error' => $e->getMessage()]);

            return $this->failure('Koneksi ke DripSender timeout atau gagal: '.$e->getMessage());
        } catch (\Throwable $e) {
            Log::error('DripSenderClient: unexpected send error', ['error' => $e->getMessage()]);

            return $this->failure('Error tidak terduga saat mengirim WhatsApp: '.$e->getMessage());
        }
    }

    /** @param array<string, mixed> $payload
     *  @return array{success: bool, status: string, message: string, raw: mixed, http_status: int|null}
     */
    public function sendMediaMessage(array $payload): array
    {
        return $this->sendMessage($payload);
    }

    /** @return array{success: bool, status: string, message: string, raw: mixed, http_status: int|null, lists: array<int, array<string, mixed>>} */
    public function getLists(): array
    {
        if (blank($this->apiKey())) {
            return array_merge($this->failure('API key kosong.'), ['lists' => []]);
        }

        try {
            $response = Http::withHeaders(['api-key' => $this->apiKey()])
                ->acceptJson()
                ->timeout($this->requestTimeout())
                ->get($this->baseUrl().'/lists/');

            $result = $this->normalizeGetResponse($response, 'Berhasil mengambil lists dari DripSender.');
            $lists = $this->extractLists($result['raw']);

            return array_merge($result, ['lists' => $lists]);
        } catch (ConnectionException $e) {
            Log::warning('DripSenderClient: connection error on getLists', ['error' => $e->getMessage()]);

            return array_merge($this->failure('Koneksi ke DripSender timeout atau gagal: '.$e->getMessage()), ['lists' => []]);
        } catch (\Throwable $e) {
            Log::error('DripSenderClient: unexpected getLists error', ['error' => $e->getMessage()]);

            return array_merge($this->failure('Error tidak terduga saat mengambil lists: '.$e->getMessage()), ['lists' => []]);
        }
    }

    /** @return array{success: bool, status: string, message: string, raw: mixed, http_status: int|null, contacts: array<int, array<string, mixed>>} */
    public function getListContacts(string $listId, bool $all = false): array
    {
        if (blank($this->apiKey())) {
            return array_merge($this->failure('API key kosong.'), ['contacts' => []]);
        }

        try {
            $response = Http::withHeaders(['api-key' => $this->apiKey()])
                ->acceptJson()
                ->timeout($this->requestTimeout())
                ->get($this->baseUrl().'/lists/'.urlencode($listId), $all ? ['all' => 'true'] : []);

            $result = $this->normalizeGetResponse($response, 'Berhasil mengambil kontak list DripSender.');
            $contacts = $this->extractContacts($result['raw']);

            return array_merge($result, ['contacts' => $contacts]);
        } catch (ConnectionException $e) {
            Log::warning('DripSenderClient: connection error on getListContacts', ['error' => $e->getMessage()]);

            return array_merge($this->failure('Koneksi ke DripSender timeout atau gagal: '.$e->getMessage()), ['contacts' => []]);
        } catch (\Throwable $e) {
            Log::error('DripSenderClient: unexpected getListContacts error', ['error' => $e->getMessage()]);

            return array_merge($this->failure('Error tidak terduga saat mengambil kontak list: '.$e->getMessage()), ['contacts' => []]);
        }
    }

    public function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = $this->defaultCountryCode().substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = $this->defaultCountryCode().$digits;
        } elseif (str_starts_with($digits, '62')) {
            $digits = '62'.substr($digits, 2);
        }

        if (! str_starts_with($digits, $this->defaultCountryCode())) {
            return null;
        }

        if (strlen($digits) < 10 || strlen($digits) > 15) {
            return null;
        }

        return $digits;
    }

    private function requestTimeout(): int
    {
        return (bool) $this->settings->getDripSender('dripsender_enable_queue', false) ? 15 : 30;
    }

    private function apiKey(): string
    {
        return (string) $this->settings->getDripSender('dripsender_api_key', '');
    }

    private function baseUrl(): string
    {
        return rtrim((string) $this->settings->getDripSender('dripsender_base_url', 'https://api.dripsender.id'), '/');
    }

    private function defaultCountryCode(): string
    {
        return preg_replace('/\D+/', '', (string) $this->settings->getDripSender('dripsender_default_country_code', '62')) ?: '62';
    }

    /** @return array{success: bool, status: string, message: string, raw: mixed, http_status: int|null} */
    private function normalizeSendResponse(Response $response): array
    {
        $rawBody = trim($response->body());
        $json = $response->json();
        $statusCode = $response->status();

        if ($response->successful() && strtoupper($rawBody) === 'OK') {
            return [
                'success' => true,
                'status' => 'sent',
                'message' => 'OK',
                'raw' => 'OK',
                'http_status' => $statusCode,
            ];
        }

        if ($response->successful()) {
            return [
                'success' => true,
                'status' => 'sent',
                'message' => $this->responseMessage($json, $rawBody, 'WhatsApp berhasil dikirim.'),
                'raw' => $this->sanitizeRaw($json ?? $rawBody),
                'http_status' => $statusCode,
            ];
        }

        return [
            'success' => false,
            'status' => 'failed',
            'message' => $this->parseErrorMessage($statusCode, $json, $rawBody),
            'raw' => $this->sanitizeRaw($json ?? $rawBody),
            'http_status' => $statusCode,
        ];
    }

    /** @return array{success: bool, status: string, message: string, raw: mixed, http_status: int|null} */
    private function normalizeGetResponse(Response $response, string $successMessage): array
    {
        $rawBody = trim($response->body());
        $json = $response->json();
        $statusCode = $response->status();

        if ($response->successful()) {
            return [
                'success' => true,
                'status' => 'success',
                'message' => $this->responseMessage($json, $rawBody, $successMessage),
                'raw' => $this->sanitizeRaw($json ?? $rawBody),
                'http_status' => $statusCode,
            ];
        }

        return [
            'success' => false,
            'status' => 'failed',
            'message' => $this->parseErrorMessage($statusCode, $json, $rawBody),
            'raw' => $this->sanitizeRaw($json ?? $rawBody),
            'http_status' => $statusCode,
        ];
    }

    private function responseMessage(mixed $json, string $rawBody, string $fallback): string
    {
        if (is_array($json)) {
            foreach (['message', 'status', 'response'] as $key) {
                $value = trim((string) data_get($json, $key, ''));

                if ($value !== '') {
                    return $value;
                }
            }
        }

        return $rawBody !== '' ? $rawBody : $fallback;
    }

    private function parseErrorMessage(int $statusCode, mixed $json, string $rawBody): string
    {
        $message = $this->responseMessage($json, $rawBody, '');
        $normalized = strtolower($message);

        return match (true) {
            $statusCode === 400 => $message !== '' ? $message : 'Bad request ke DripSender (HTTP 400).',
            $statusCode === 401, str_contains($normalized, 'api key') => 'API key DripSender tidak valid.',
            $statusCode === 404 => 'Endpoint DripSender tidak ditemukan (HTTP 404).',
            $statusCode === 408 => 'Request ke DripSender timeout (HTTP 408).',
            $statusCode === 422 => $message !== '' ? $message : 'Validasi request DripSender gagal (HTTP 422).',
            $statusCode === 500 => 'DripSender mengalami internal server error (HTTP 500).',
            $message !== '' => $message,
            default => "Permintaan ke DripSender gagal (HTTP {$statusCode}).",
        };
    }

    /** @return array<int, array<string, mixed>> */
    private function extractLists(mixed $raw): array
    {
        if (is_array($raw) && array_is_list($raw)) {
            return $raw;
        }

        if (is_array($raw)) {
            foreach (['data', 'lists', 'result'] as $key) {
                $value = data_get($raw, $key);

                if (is_array($value) && array_is_list($value)) {
                    return $value;
                }
            }
        }

        return [];
    }

    /** @return array<int, array<string, mixed>> */
    private function extractContacts(mixed $raw): array
    {
        if (is_array($raw) && array_is_list($raw)) {
            return $raw;
        }

        if (is_array($raw)) {
            foreach (['data', 'contacts', 'members', 'result'] as $key) {
                $value = data_get($raw, $key);

                if (is_array($value) && array_is_list($value)) {
                    return $value;
                }
            }
        }

        return [];
    }

    private function sanitizeRaw(mixed $raw): mixed
    {
        if (is_array($raw)) {
            unset($raw['api_key'], $raw['api-key']);

            foreach ($raw as $key => $value) {
                $raw[$key] = $this->sanitizeRaw($value);
            }

            return $raw;
        }

        if (is_string($raw)) {
            return preg_replace('/api[_-]?key["\']?\s*[:=]\s*["\']?[^"\',\s]+/i', 'api_key=[REDACTED]', $raw) ?? $raw;
        }

        return $raw;
    }

    /** @return array{success: bool, status: string, message: string, raw: array, http_status: int|null} */
    private function failure(string $message): array
    {
        return [
            'success' => false,
            'status' => 'failed',
            'message' => $message,
            'raw' => [],
            'http_status' => null,
        ];
    }
}
