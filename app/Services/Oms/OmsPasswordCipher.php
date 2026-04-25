<?php

namespace App\Services\Oms;

use RuntimeException;

class OmsPasswordCipher
{
    public function decrypt(string $payload): string
    {
        $key = $this->resolveKey();

        if (str_contains($payload, ':')) {
            [$ivB64, $cipherB64] = explode(':', $payload, 2);
            $iv = base64_decode($ivB64, true);
            $cipher = base64_decode($cipherB64, true);
        } else {
            $raw = base64_decode($payload, true);

            if ($raw === false || strlen($raw) < 17) {
                throw new RuntimeException('Invalid encrypted password.');
            }

            $iv = substr($raw, 0, 16);
            $cipher = substr($raw, 16);
        }

        if ($iv === false || $cipher === false || strlen($iv) !== 16) {
            throw new RuntimeException('Invalid encrypted password.');
        }

        // NOTE: Adjust to OMS agreed encryption format if it differs.
        $plain = openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        if ($plain === false || $plain === '') {
            throw new RuntimeException('Failed to decrypt password.');
        }

        return (string) $plain;
    }

    public function encrypt(string $plain): string
    {
        $key = $this->resolveKey();
        $iv = random_bytes(16);

        // NOTE: Adjust to OMS agreed encryption format if it differs.
        $cipher = openssl_encrypt($plain, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        if ($cipher === false) {
            throw new RuntimeException('Failed to encrypt password.');
        }

        return base64_encode($iv).':'.base64_encode($cipher);
    }

    protected function resolveKey(): string
    {
        $raw = (string) config('epichub.oms.password_encryption_key', '');

        if ($raw === '') {
            throw new RuntimeException('OMS password encryption key missing.');
        }

        $decoded = base64_decode($raw, true);

        if ($decoded !== false && strlen($decoded) === 32) {
            return $decoded;
        }

        return hash('sha256', $raw, true);
    }
}
