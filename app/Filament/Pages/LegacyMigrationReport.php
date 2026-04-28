<?php

namespace App\Filament\Pages;

use App\Actions\LegacyV1\GenerateLegacyMigrationReportAction;
use App\Filament\Navigation\AdminNavigationGroup;
use App\Models\LegacyV1ImportBatch;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class LegacyMigrationReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Migration Report';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Administrasi;

    protected static ?int $navigationSort = 56;

    protected string $view = 'filament.pages.legacy-migration-report';

    public ?LegacyV1ImportBatch $batch = null;

    /**
     * @var array<string, mixed>
     */
    public array $summary = [];

    public function mount(GenerateLegacyMigrationReportAction $report): void
    {
        $batchId = request()->query('batch');
        $query = LegacyV1ImportBatch::query()->latest('id');

        if (is_string($batchId) && $batchId !== '') {
            $query->whereKey((int) $batchId);
        }

        $this->batch = $query->first();
        $this->summary = $this->batch ? $report->execute($this->batch, persist: true) : [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'batch' => $this->batch,
            'summary' => $this->summary,
            'recentBatches' => LegacyV1ImportBatch::query()->latest('id')->limit(12)->get(),
            'recentErrors' => $this->batch
                ? $this->batch->importErrors()->latest('id')->limit(15)->get()
                : collect(),
        ];
    }
}
