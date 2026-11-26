<?php

declare(strict_types=1);

namespace Tests;

use App\Models\User;
use Laravel\Sanctum\Sanctum;

class TestCaseWithAuthorizedSuperAdmin extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->superAdmin()->create();
        Sanctum::actingAs($user);
    }
}
