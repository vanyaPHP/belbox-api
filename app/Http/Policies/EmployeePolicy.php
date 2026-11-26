<?php

declare(strict_types=1);

namespace App\Http\Policies;

use App\Enums\Identity\Role;
use App\Models\HR\Employee;

final readonly class EmployeePolicy
{
    public static function canView(Employee $employee): bool
    {
        return auth()->user()->hasRoles([Role::SuperAdmin->value, Role::HrHead->value])
            || $employee->user_id === auth()->user()->id
            || $employee->manager_id === auth()->user()->employee->id
            || $employee->isInDownlineOf(auth()->user()->employee);
    }

    public static function canWrite(?Employee $employee = null): bool
    {
        if (! $employee) {
            return auth()->user()->hasRoles([
                Role::SuperAdmin->value,
                Role::HrHead->value,
                Role::HrManager->value
            ]);
        }

        return auth()->user()->hasRoles([Role::SuperAdmin->value, Role::HrHead->value])
            || $employee->manager_id === auth()->user()->employee->id;
    }
}
