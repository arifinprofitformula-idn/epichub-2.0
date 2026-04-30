<?php

namespace App\Actions\Oms;

class NormalizeOmsCreateAccountPayloadAction
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     epic_code: ?string,
     *     name: ?string,
     *     email: ?string,
     *     whatsapp_number: ?string,
     *     store_name: ?string,
     *     sponsor_epic_code: ?string,
     *     sponsor_name: ?string,
     *     encrypted_password: ?string
     * }
     */
    public function execute(array $payload): array
    {
        return [
            'epic_code' => $this->firstFilled($payload, ['kode_epic', 'kode_new_epic', 'epic_code']),
            'name' => $this->firstFilled($payload, ['nama_epic', 'nama_new_epic', 'name']),
            'email' => $this->firstFilled($payload, ['email_epic', 'email_addr_new_epic', 'email']),
            'whatsapp_number' => $this->firstFilled($payload, ['no_tlp_epic', 'no_tlp_new_epic', 'whatsapp_number', 'phone']),
            'store_name' => $this->firstFilled($payload, ['nama_epi_store', 'nama_epi_store_new_epic', 'store_name']),
            'sponsor_epic_code' => $this->firstFilled($payload, ['sponsor_epic_code', 'kode_epic_sponsor']),
            'sponsor_name' => $this->firstFilled($payload, ['sponsor_name', 'nama_epic_sponsor']),
            'encrypted_password' => $this->firstFilled($payload, ['encrypted_password', 'password_terenkripsi', 'password_encrypted']),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function firstFilled(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = data_get($payload, $key);

            if ($value === null) {
                continue;
            }

            $value = trim((string) $value);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
