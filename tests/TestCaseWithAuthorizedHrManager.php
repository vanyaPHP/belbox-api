<?php

declare(strict_types=1);

namespace Tests;

use App\Models\HR\Employee;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class TestCaseWithAuthorizedHrManager extends TestCase
{
    protected Employee $currentHrManager;

    public function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->hrManager()->create();
        Sanctum::actingAs($user);
        $this->currentHrManager = $user->employee;
    }
}
