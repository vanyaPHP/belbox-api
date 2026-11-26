<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DevEnvironmentUsersSeeder extends Seeder
{
    public function run(): void
    {
        $startDate = now()->subYear()->format('Y-m-d');

        User::factory()
            ->state([
                'full_name' => 'Super Admin',
                'email' => 'super_admin@belbox.by',
                'password' => 'password',
            ])
            ->superAdmin([
                'start_date' => $startDate,
                'personal_email' => 'personal_super_admin@gmail.com',
            ])
            ->create();

        $hrHead = User::factory()
            ->state([
                'full_name' => 'HR Head',
                'email' => 'hr_head@belbox.by',
                'password' => 'password',
            ])
            ->hrHead([
                'start_date' => $startDate,
                'personal_email' => 'personal_hr_head@gmail.com',
            ])
            ->create();

        $hrManager = User::factory()
            ->state([
                'full_name' => 'HR Manager',
                'email' => 'hr_manager@belbox.by',
                'password' => 'password',
            ])
            ->hrManager([
                'head_id' => $hrHead->employee->id,
                'manager_id' => $hrHead->employee->id,
                'start_date' => $startDate,
                'personal_email' => 'personal_hr_manager@gmail.com',
            ])
            ->create();

        $driverUpline = User::factory()
            ->state([
                'full_name' => 'Driver Upline',
                'email' => 'driver_upline@belbox.by',
                'password' => 'password',
            ])
            ->driver([
                'manager_id' => $hrManager->employee->id,
                'start_date' => $startDate,
                'personal_email' => 'personal_driver_upline@gmail.com',
            ])
            ->create();

        User::factory()
            ->state([
                'full_name' => 'Driver Downline',
                'email' => 'driver_downline@belbox.by',
                'password' => 'password',
            ])
            ->driver([
                'head_id' => $driverUpline->employee->id,
                'manager_id' => $hrManager->employee->id,
                'start_date' => $startDate,
                'personal_email' => 'personal_driver_downline@gmail.com',
            ])
            ->create();
    }
}
