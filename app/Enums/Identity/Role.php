<?php

declare(strict_types=1);

namespace App\Enums\Identity;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case SystemAdmin = 'system_admin';
    case HrHead = 'hr_head';
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
            self::HrHead => [
                Permission::ReadEmployees->value,
                Permission::WriteEmployees->value,
                Permission::ReadHirings->value,
                Permission::ApproveHirings->value,
                Permission::RejectHirings->value,
            ],
            self::HrManager => [
                Permission::ReadEmployees->value,
                Permission::WriteEmployees->value,
            ],
            self::Driver => [
                Permission::ReadEmployees->value,
            ],
            default => [],
        };
    }
}
