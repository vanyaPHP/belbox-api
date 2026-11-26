<?php

declare(strict_types=1);

namespace App\Services\HR;

use App\Enums\HR\EmployeeStatus;
use App\Enums\Identity\Role;
use App\Http\Requests\HR\CreateEmployeeData;
use App\Http\Requests\HR\UpdateEmployeeData;
use App\Models\HR\Employee;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final readonly class EmployeeService
{
    private const array EMPLOYEE_UPDATE_FIELDS = [
        'head_id', 'manager_id', 'phone_number',
        'personal_email', 'address', 'iban',
    ];

    public function store(CreateEmployeeData $data): ?Employee
    {
        try {
            return DB::transaction(function () use ($data) {
                $user = User::query()->create([
                    'full_name' => $data->input('full_name'),
                    'email' => $data->input('email'),
                    'password' => $data->input('password'),
                ]);
                $rolesData = array_map(
                    fn(string $role): array => ['role' => Role::from($role)],
                    $data->input('roles')
                );
                $user->roles()->createMany($rolesData);

                return Employee::query()->create([
                    'user_id' => $user->id,
                    'status' => EmployeeStatus::Pending,
                    'start_date' => Carbon::createFromFormat('Y-m-d', $data->input('start_date')),
                    'phone_number' => $data->input('phone_number'),
                    'personal_email' => $data->input('personal_email'),
                    'birth_date' => $data->input('birth_date'),
                    'address' => $data->input('address'),
                    'iban' => $data->input('iban'),
                    'metadata' => [],
                ]);
            });
        } catch (\Throwable) {
            return null;
        }
    }

    public function update(Employee $employee, UpdateEmployeeData $data): bool
    {
        try {
            return DB::transaction(function () use ($employee, $data) {
                if ($data->input('full_name') !== null) {
                    $employee->user->update(['full_name' => $data->input('full_name')]);
                }
                if ($data->input('roles') !== null && is_array($data->input('roles'))) {
                    $employee->user->roles()->delete();

                    $newRoles = array_map(
                        fn(string $role): array => ['role' => Role::from($role)],
                        $data->input('roles'),
                    );

                    $employee->user->roles()->createMany($newRoles);
                }

                $employeeData = [];
                foreach (self::EMPLOYEE_UPDATE_FIELDS as $field) {
                    if ($data->input($field) !== null) {
                        $employeeData[$field] = $data->input($field);
                    }
                }

                $employee->update($employeeData);

                return true;
            });
        } catch (\Throwable) {
            return false;
        }
    }

    public function fire(Employee $employee): bool
    {
        try {
            return DB::transaction(function () use ($employee) {
                $employee->update([
                    'status' => EmployeeStatus::Fired,
                    'end_date' => now(),
                ]);

                $employee->user->delete();
                $employee->delete();

                return true;
            });
        } catch (\Throwable) {
            return false;
        }
    }
}
