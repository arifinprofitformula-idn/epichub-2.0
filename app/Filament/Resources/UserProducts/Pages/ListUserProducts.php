<?php

namespace App\Filament\Resources\UserProducts\Pages;

use App\Actions\Access\GrantProductAccessAction;
use App\Enums\AccessLogAction;
use App\Filament\Resources\UserProducts\UserProductResource;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListUserProducts extends ListRecords
{
    protected static string $resource = UserProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('grant_manual_access')
                ->label('Grant access')
                ->color('primary')
                ->form([
                    Select::make('user_id')
                        ->label('User')
                        ->options(fn (): array => User::query()->orderBy('email')->pluck('email', 'id')->all())
                        ->searchable()
                        ->required(),
                    Select::make('product_id')
                        ->label('Produk')
                        ->options(fn (): array => Product::query()->orderBy('title')->pluck('title', 'id')->all())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $actor = auth()->user();

                    if (! $actor instanceof User) {
                        throw new \RuntimeException('Unauthorized.');
                    }

                    $user = User::query()->findOrFail($data['user_id']);
                    $product = Product::query()->findOrFail($data['product_id']);

                    app(GrantProductAccessAction::class)->execute(
                        user: $user,
                        product: $product,
                        actor: $actor,
                        logAction: AccessLogAction::ManualGrant,
                    );
                }),
        ];
    }
}

