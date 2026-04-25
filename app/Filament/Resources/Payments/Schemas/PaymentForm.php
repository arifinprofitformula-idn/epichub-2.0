<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Section::make('Payment')
                        ->schema([
                            TextInput::make('payment_number')
                                ->label('Payment No.')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('order.order_number')
                                ->label('Order No.')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('payment_method')
                                ->label('Metode')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('status')
                                ->label('Status')
                                ->disabled()
                                ->dehydrated(false),
                        ])
                        ->columns(2)
                        ->columnSpan(2),

                    Section::make('Detail')
                        ->schema([
                            TextInput::make('amount')
                                ->label('Amount')
                                ->prefix('Rp')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('proof_of_payment')
                                ->label('Proof path')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('verified_by')
                                ->label('Verified by (user_id)')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('verified_at')
                                ->label('Verified at')
                                ->disabled()
                                ->dehydrated(false),
                        ])
                        ->columns(2)
                        ->columnSpan(2),
                ]),
            ]);
    }
}

