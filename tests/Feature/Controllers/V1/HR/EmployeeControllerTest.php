<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\V1\HR;

use App\Enums\HR\EmployeeStatus;
use App\Enums\Identity\Role;
use App\Models\HR\Employee;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCaseWithAuthorizedHrManager;

class EmployeeControllerTest extends TestCaseWithAuthorizedHrManager
{
    public function test_hr_head_see_all_not_fired_employees(): void
    {
        Employee::factory()->count(3)->create();

        $user = User::factory()->hrHead()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.hr.employees.index'));
        $response->assertStatus(200);
        $response->assertJsonCount(4);
    }

    public function test_hr_manager_sees_only_its_own_employees(): void
    {
        Employee::factory()->count(3)
            ->withManager($this->currentHrManager)
            ->create();

        Employee::factory()->count(2)->create();

        $response = $this->getJson(route('api.v1.hr.employees.index'));
        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_upline_user_sees_only_his_downline_employees(): void
    {
        $driverWithDownline = User::factory()->driver()->create();

        Employee::factory()->count(3)
            ->withHead($driverWithDownline->employee)
            ->create();

        Employee::factory()->count(2)->create();

        Sanctum::actingAs($driverWithDownline);
        $response = $this->getJson(route('api.v1.hr.employees.index'));
        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_it_shows_profile_to_its_owner(): void
    {
        $user = User::factory()->driver()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.hr.employees.show', [
            'id' => $user->employee->id,
        ]));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $user->employee->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
        ]);
    }

    public function test_it_creates_employee(): void
    {
        $data = [
            'full_name' => 'Example Driver',
            'email' => 'test_driver@example.com',
            'password' => 'password',
            'roles' => ['driver'],
            'start_date' => now()->addDays(2)->format('Y-m-d'),
            'phone_number' => '+375441233222',
            'personal_email' => 'personal_email@example.com',
            'birth_date' => now()->subYears(25)->format('Y-m-d'),
            'address' => 'Minsk, Nemiga 3',
            'iban' => 'BY01ALFA33344445555577777787',
        ];

        $response = $this->postJson(route('api.v1.hr.employees.store'), $data);
        $response->assertStatus(201);
        $response->assertJson([
            'user' => [
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'roles' => $data['roles'],
            ],
            'status' => EmployeeStatus::Pending->value,
            'start_date' => $data['start_date'],
            'personal_email' => $data['personal_email'],
            'birth_date' => $data['birth_date'],
            'address' => $data['address'],
            'iban' => $data['iban'],
        ]);
    }

    public function test_it_updates_employee(): void
    {
        $employee = User::factory()
            ->driver(['manager_id' => $this->currentHrManager->id])
            ->create()
            ->employee;

        $headEmployee = User::factory()
            ->driver()
            ->create()
            ->employee;

        $response = $this->putJson(route('api.v1.hr.employees.update', [
            'id' => $employee->id,
        ]), [
            'full_name' => 'John Ivanovich',
            'head_id' => $headEmployee->id,
            'roles' => ['driver'],
            'personal_email' => 'new_example_email@example.test',
            'address' => 'Minsk, Nemiga 4',
        ]);
        $response->assertNoContent();
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'head_id' => $headEmployee->id,
            'personal_email' => 'new_example_email@example.test',
            'address' => 'Minsk, Nemiga 4',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $employee->user->id,
            'full_name' => 'John Ivanovich',
        ]);
        $this->assertDatabaseHas('user_roles', [
            'user_id' => $employee->user->id,
            'role' => Role::Driver,
        ]);
    }

    public function test_it_fires_employee(): void
    {
        $this->freezeTime();

        $employee = Employee::factory()
            ->withManager($this->currentHrManager)
            ->create();

        $response = $this->postJson(route('api.v1.hr.employees.fire', [
            'id' => $employee->id,
        ]));
        $response->assertNoContent();
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'status' => EmployeeStatus::Fired,
            'end_date' => now(),
            'deleted_at' => now(),
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $employee->user_id,
            'deleted_at' => now(),
        ]);
    }
}
