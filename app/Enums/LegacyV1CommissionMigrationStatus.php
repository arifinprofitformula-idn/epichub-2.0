<?php

namespace App\Enums;

enum LegacyV1CommissionMigrationStatus: string
{
    case Pending = 'pending';
    case Resolved = 'resolved';
    case UnresolvedUser = 'unresolved_user';
    case UnresolvedProduct = 'unresolved_product';
    case UnknownStatus = 'unknown_status';
    case Payable = 'payable';
    case IncludedInPayout = 'included_in_payout';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Resolved => 'Resolved',
            self::UnresolvedUser => 'Unresolved User',
            self::UnresolvedProduct => 'Unresolved Product',
            self::UnknownStatus => 'Unknown Status',
            self::Payable => 'Payable',
            self::IncludedInPayout => 'Included In Payout',
            self::Error => 'Error',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Resolved => 'success',
            self::Payable, self::IncludedInPayout => 'info',
            self::Pending => 'warning',
            self::UnresolvedUser, self::UnresolvedProduct, self::UnknownStatus => 'danger',
            self::Error => 'gray',
        };
    }
}
