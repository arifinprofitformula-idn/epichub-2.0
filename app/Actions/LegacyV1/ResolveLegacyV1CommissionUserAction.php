<?php

namespace App\Actions\LegacyV1;

use App\Enums\LegacyV1CommissionMigrationStatus;
use App\Models\LegacyV1Commission;
use App\Models\User;

class ResolveLegacyV1CommissionUserAction
{
    public function __construct(
        protected ResolveLegacyV1UserMatchAction $resolveUserMatch,
    ) {}

    /**
     * @return array{user:?User, matched_by:?string, conflict:?string, migration_status:LegacyV1CommissionMigrationStatus}
     */
    public function execute(LegacyV1Commission $commission): array
    {
        $match = $this->resolveUserMatch->execute(
            epicId: $commission->legacy_user_epic_id,
            email: $commission->legacy_user_email,
            whatsapp: $commission->legacy_user_whatsapp,
        );

        if ($match['conflict'] !== null) {
            return [
                'user' => null,
                'matched_by' => null,
                'conflict' => $match['conflict'],
                'migration_status' => LegacyV1CommissionMigrationStatus::UnresolvedUser,
            ];
        }

        if (! $match['user']) {
            return [
                'user' => null,
                'matched_by' => null,
                'conflict' => null,
                'migration_status' => LegacyV1CommissionMigrationStatus::UnresolvedUser,
            ];
        }

        return [
            'user' => $match['user'],
            'matched_by' => $match['matched_by'],
            'conflict' => null,
            'migration_status' => LegacyV1CommissionMigrationStatus::Resolved,
        ];
    }
}
