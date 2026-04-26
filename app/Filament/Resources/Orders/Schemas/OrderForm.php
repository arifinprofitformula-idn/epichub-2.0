<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Section::make('Order')
                        ->schema([
                            TextInput::make('order_number')
                                ->label('Order No.')
                                ->disabled()
                                ->dehydrated(false),

                            Select::make('status')
                                ->label('Status')
                                ->options(collect(OrderStatus::cases())->mapWithKeys(fn (OrderStatus $status) => [$status->value => $status->label()])->all())
                                ->required(),

                            TextInput::make('total_amount')
                                ->label('Total')
                                ->prefix('Rp')
                                ->disabled()
                                ->dehydrated(false),
                        ])
                        ->columns(2)
                        ->columnSpan(1),

                    Section::make('Pembeli')
                        ->schema([
                            TextInput::make('customer_name')
                                ->label('Nama')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('customer_email')
                                ->label('Email')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('customer_phone')
                                ->label('Telepon')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('referrerEpiChannel.epic_code')
                                ->label('EPIC Referrer')
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('EPIC-HOUSE'),

                            TextInput::make('referral_source')
                                ->label('Referral Source')
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('-'),
                        ])
                        ->columns(1)
                        ->columnSpan(1),
                ]),
            ]);
    }
}

