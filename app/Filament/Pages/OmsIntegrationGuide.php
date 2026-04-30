<?php

namespace App\Filament\Pages;

use App\Models\OmsIntegrationLog;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class OmsIntegrationGuide extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $navigationLabel = 'OMS Integration';

    protected static string|UnitEnum|null $navigationGroup = 'System / Integrations';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.oms-integration-guide';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'endpointUrl' => url('/api/oms/epi-channel/create-account'),
            'integrationEnabled' => (bool) config('epichub.oms.integration_enabled', config('epichub.oms.enabled', false)),
            'signatureConfigured' => trim((string) config('epichub.oms.signature_secret', '')) !== '',
            'bearerConfigured' => trim((string) config('epichub.oms.inbound_secret', '')) !== '',
            'passwordKeyConfigured' => trim((string) config('epichub.oms.password_encryption_key', '')) !== '',
            'successCode' => (string) config('epichub.oms.response.success', '00'),
            'failedCode' => (string) config('epichub.oms.response.failed', '99'),
            'latestLogs' => $this->getLatestLogs(),
        ];
    }

    /**
     * @return Collection<int, OmsIntegrationLog>|EloquentCollection<int, OmsIntegrationLog>
     */
    protected function getLatestLogs(): Collection|EloquentCollection
    {
        if (! Schema::hasTable('oms_integration_logs')) {
            return collect();
        }

        return OmsIntegrationLog::query()
            ->where('action', 'create_account')
            ->latest('processed_at')
            ->latest('id')
            ->limit(10)
            ->get();
    }
}
