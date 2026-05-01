<?php

namespace App\Services\Settings;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Schema;

class AppSettingService
{
    private bool $tableExists;

    public function __construct()
    {
        $this->tableExists = Schema::hasTable('app_settings');
    }

    public function get(string $key, mixed $default = null, string $group = 'general'): mixed
    {
        if (! $this->tableExists) {
            return $default;
        }

        return AppSetting::get($key, $default, $group);
    }

    public function set(string $key, mixed $value, string $group = 'general', bool $encrypted = false, ?string $type = null): void
    {
        if (! $this->tableExists) {
            return;
        }

        AppSetting::set($key, $value, $group, $encrypted, $type);
    }

    public function getMailketing(string $key, mixed $default = null): mixed
    {
        return $this->get($key, $default, 'mailketing');
    }

    public function setMailketing(string $key, mixed $value, bool $encrypted = false, ?string $type = null): void
    {
        $this->set($key, $value, 'mailketing', $encrypted, $type);
    }

    /** @return array<string, mixed> */
    public function getAllMailketing(): array
    {
        if (! $this->tableExists) {
            return [];
        }

        return AppSetting::where('group', 'mailketing')
            ->get()
            ->mapWithKeys(function (AppSetting $row): array {
                $value = AppSetting::get($row->key, null, 'mailketing');
                return [$row->key => $value];
            })
            ->toArray();
    }

    public function getDripSender(string $key, mixed $default = null): mixed
    {
        return $this->get($key, $default, 'dripsender');
    }

    public function setDripSender(string $key, mixed $value, bool $encrypted = false, ?string $type = null): void
    {
        $this->set($key, $value, 'dripsender', $encrypted, $type);
    }

    /** @return array<string, mixed> */
    public function getAllDripSender(): array
    {
        if (! $this->tableExists) {
            return [];
        }

        return AppSetting::where('group', 'dripsender')
            ->get()
            ->mapWithKeys(function (AppSetting $row): array {
                $value = AppSetting::get($row->key, null, 'dripsender');

                return [$row->key => $value];
            })
            ->toArray();
    }
}
