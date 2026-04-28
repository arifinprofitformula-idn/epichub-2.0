<?php

namespace App\Filament\Resources\EventRegistrations\Schemas;

use App\Enums\EventRegistrationStatus;
use App\Models\Event;
use App\Models\User;
use App\Models\UserProduct;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            /* ── 1. Data Registrasi ── */
            Section::make('Data Registrasi')
                ->description('Event, peserta, dan status kehadiran')
                ->icon('heroicon-o-clipboard-document-check')
                ->iconColor('primary')
                ->extraAttributes(['class' => 'fi-evtreg-section-main'])
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('event_id')
                            ->label('Event')
                            ->options(fn () => Event::query()
                                ->orderBy('starts_at')
                                ->orderBy('title')
                                ->get()
                                ->mapWithKeys(fn (Event $e) => [
                                    $e->id => $e->title.($e->starts_at ? ' — '.$e->starts_at->format('d M Y') : ''),
                                ]))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Select::make('status')
                            ->label('Status Kehadiran')
                            ->options(collect(EventRegistrationStatus::cases())
                                ->mapWithKeys(fn (EventRegistrationStatus $s) => [$s->value => $s->label()])
                                ->all())
                            ->required()
                            ->default(EventRegistrationStatus::Registered->value)
                            ->native(false),

                        Select::make('user_id')
                            ->label('Peserta (User)')
                            ->options(fn () => User::query()
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (User $u) => [
                                    $u->id => $u->name.' — '.$u->email,
                                ]))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),

                        Select::make('user_product_id')
                            ->label('Akses Produk (opsional)')
                            ->options(fn () => UserProduct::query()
                                ->with(['user', 'product'])
                                ->latest('granted_at')
                                ->limit(200)
                                ->get()
                                ->mapWithKeys(fn (UserProduct $up) => [
                                    $up->id => ($up->user?->name ?? '—').' · '.($up->product?->title ?? 'ID '.$up->id),
                                ]))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->native(false)
                            ->helperText('Hubungkan ke akses produk jika registrasi berasal dari pembelian')
                            ->columnSpanFull(),
                    ]),
                ])
                ->columnSpanFull(),

            /* ── 2. Waktu & Catatan ── */
            Section::make('Waktu & Catatan')
                ->description('Tanggal registrasi, kehadiran, dan catatan tambahan')
                ->icon('heroicon-o-clock')
                ->iconColor('info')
                ->extraAttributes(['class' => 'fi-evtreg-section-meta'])
                ->schema([
                    Grid::make(2)->schema([
                        DateTimePicker::make('registered_at')
                            ->label('Tanggal Registrasi')
                            ->seconds(false)
                            ->nullable()
                            ->helperText('Kosongkan untuk menggunakan waktu sekarang'),

                        DateTimePicker::make('attended_at')
                            ->label('Tanggal Hadir')
                            ->seconds(false)
                            ->nullable()
                            ->helperText('Isi saat peserta sudah dikonfirmasi hadir'),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->nullable()
                            ->placeholder('Catatan tambahan tentang registrasi ini...')
                            ->columnSpanFull(),
                    ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
