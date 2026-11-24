<?php

declare(strict_types=1);

namespace App\Enums\Identity;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case HrManager = 'hr_manager';
    case BranchManager = 'branch_manager';
    case Driver = 'driver';
    case BusinessCustomer = 'business_customer';
    case Customer = 'customer';

    public function permissions(): array
    {
        return match ($this) {
            self::SuperAdmin => array_map(
                fn (Permission $permission) => $permission->value,
                Permission::cases(),
            ),
            default => [],
        };
    }
}
