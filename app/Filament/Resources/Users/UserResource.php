<?php

namespace App\Filament\Resources\Users;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\AccessLog;
use App\Models\OmsIntegrationLog;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Password;
use RuntimeException;
use Spatie\Permission\Models\Role;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Administration;

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $lastActivitySubquery = AccessLog::query()
            ->selectRaw('MAX(created_at)')
            ->whereColumn('access_logs.user_id', 'users.id');

        $latestOmsStatusSubquery = OmsIntegrationLog::query()
            ->select('status')
            ->whereColumn('oms_integration_logs.email', 'users.email')
            ->latest('processed_at')
            ->latest('id')
            ->limit(1);

        return parent::getEloquentQuery()
            ->select('users.*')
            ->selectSub($lastActivitySubquery, 'last_activity_at')
            ->selectSub($latestOmsStatusSubquery, 'latest_oms_status')
            ->with([
                'roles:id,name',
                'epiChannel:id,user_id,epic_code,status,metadata',
                'referrerEpiChannel:id,user_id,epic_code,metadata',
            ])
            ->withCount([
                'orders',
                'userProducts as active_user_products_count' => fn (Builder $query) => $query->active(),
            ]);
    }

    public static function canViewAny(): bool
    {
        return static::canManageUsers();
    }

    public static function canView($record): bool
    {
        return static::canManageUsers();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return static::canManageUsers();
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canForceDelete($record): bool
    {
        return false;
    }

    public static function canForceDeleteAny(): bool
    {
        return false;
    }

    public static function canReplicate($record): bool
    {
        return false;
    }

    public static function canManageUsers(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->can('manage_users');
    }

    public static function canManageRoles(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->can('manage_roles');
    }

    /**
     * @return array<string, string>
     */
    public static function assignableRolesForCurrentActor(): array
    {
        $actor = auth()->user();

        if (! $actor instanceof User) {
            return [];
        }

        $query = Role::query()->orderBy('name');

        if (! $actor->hasRole('super_admin')) {
            $query->where('name', '!=', 'super_admin');
        }

        return $query->pluck('name', 'name')->all();
    }

    public static function canModifyRolesForRecord(User $record): bool
    {
        $actor = auth()->user();

        if (! $actor instanceof User || ! $actor->can('manage_roles')) {
            return false;
        }

        if (! $actor->hasRole('super_admin') && $record->hasRole('super_admin')) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<int, string>  $roles
     */
    public static function syncRolesForUser(User $record, array $roles, User $actor): void
    {
        if (! $actor->can('manage_roles')) {
            throw new RuntimeException('Anda tidak memiliki izin untuk mengubah role.');
        }

        $roles = collect($roles)
            ->filter(fn (mixed $role): bool => is_string($role) && $role !== '')
            ->unique()
            ->values()
            ->all();

        if (! $actor->hasRole('super_admin')) {
            if ($record->hasRole('super_admin')) {
                throw new RuntimeException('Hanya superadmin yang dapat mengubah role akun superadmin.');
            }

            if (in_array('super_admin', $roles, true)) {
                throw new RuntimeException('Hanya superadmin yang dapat memberikan role superadmin.');
            }
        }

        if ($record->is($actor) && ! collect($roles)->intersect(['super_admin', 'admin'])->count()) {
            throw new RuntimeException('Akun yang sedang digunakan harus tetap memiliki akses panel admin.');
        }

        $record->syncRoles($roles);

        logger()->info('Admin updated user roles', [
            'actor_id' => $actor->id,
            'target_user_id' => $record->id,
            'roles' => $roles,
        ]);
    }

    public static function sendResetPasswordLink(User $record, User $actor): void
    {
        if (! $actor->can('manage_users')) {
            throw new RuntimeException('Anda tidak memiliki izin untuk mengirim reset password.');
        }

        $status = Password::broker()->sendResetLink([
            'email' => $record->email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw new RuntimeException(__($status));
        }

        logger()->info('Admin sent reset password link', [
            'actor_id' => $actor->id,
            'target_user_id' => $record->id,
            'target_email' => $record->email,
        ]);
    }
}
