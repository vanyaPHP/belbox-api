<?php

namespace Tests\Feature\Controllers\V1\HR;

use App\Enums\HR\EmployeeStatus;
use App\Models\HR\Employee;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCaseWithAuthorizeHrHead;

class HiringRequestControllerTest extends TestCaseWithAuthorizeHrHead
{
    public function test_it_rejects_access_to_hiring_requests_to_not_hr_head(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.hr.hiring-requests.index'));
        $response->assertStatus(403);
    }

    public function test_it_shows_hiring_requests(): void
    {
        Employee::factory()->count(3)->create([
            'status' => EmployeeStatus::Pending,
        ]);

        $response = $this->getJson(route('api.v1.hr.hiring-requests.index'));
        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_it_approves_hiring_request(): void
    {
        $hiringRequest = Employee::factory()->create(['status' => EmployeeStatus::Pending]);

        $response = $this->post(route('api.v1.hr.hiring-requests.approve', [
            'id' => $hiringRequest->id,
        ]));

        $response->assertStatus(200);
        $this->assertDatabaseHas('employees', [
            'id' => $hiringRequest->id,
            'status' => EmployeeStatus::Hired,
        ]);
    }

    public function test_it_rejects_hiring_request(): void
    {
        $hiringRequest = Employee::factory()->create(['status' => EmployeeStatus::Pending]);

        $response = $this->post(route('api.v1.hr.hiring-requests.reject', [
            'id' => $hiringRequest->id,
        ]));

        $response->assertStatus(200);
        $this->assertDatabaseHas('employees', [
            'id' => $hiringRequest->id,
            'status' => EmployeeStatus::Rejected,
        ]);
    }
}
