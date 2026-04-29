<?php

namespace App\Filament\Resources\LegacyV1ImportBatches\Tables;

use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LegacyV1ImportBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('Batch')
                    ->sortable(),

                TextColumn::make('source_type')
                    ->label('Tipe')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('file_name')
                    ->label('File')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('summary.users_imported')
                    ->label('Users')
                    ->placeholder('-'),

                TextColumn::make('summary.accesses_granted')
                    ->label('Granted')
                    ->placeholder('-'),

                TextColumn::make('summary.conflict_count')
                    ->label('Conflict')
                    ->placeholder('-'),

                TextColumn::make('summary.error_count')
                    ->label('Error')
                    ->placeholder('-'),

                TextColumn::make('started_at')
                    ->label('Mulai')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label('Selesai')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('source_type')
                    ->options([
                        'users' => 'Users',
                        'users_db' => 'Users DB',
                        'accesses' => 'Accesses',
                        'accesses_db' => 'Accesses DB',
                        'orders_db' => 'Orders DB',
                        'payments_db' => 'Payments DB',
                        'payouts_db' => 'Payouts DB',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'completed_with_issues' => 'Completed With Issues',
                        'rolled_back' => 'Rolled Back',
                    ]),
            ])
            ->recordActions([
                Action::make('report')
                    ->label('Report')
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn ($record): string => route('filament.admin.pages.legacy-migration-report', ['batch' => $record->id])),
            ]);
    }
}
