<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\V1\Identity;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function test_existing_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token',
        ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_not_registered_user_can_not_login(): void
    {
        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(404);
        $response->assertJsonFragment([
            'error' => 'User not found.',
        ]);
    }

    public function test_user_with_wrong_password_can_not_login(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'error' => 'Wrong password.',
        ]);
    }

    public function test_user_can_get_self_info(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        Sanctum::actingAs($user);
        $response = $this->getJson(route('api.v1.auth.me'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'roles' => [],
                'permissions' => [],
            ],
        ]);
    }

    public function test_not_authenticated_user_can_not_get_self_info(): void
    {
        $response = $this->getJson(route('api.v1.auth.me'));
        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.v1.auth.logout'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'Logged out successfully.',
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_not_authenticated_user_can_not_logout(): void
    {
        $response = $this->postJson(route('api.v1.auth.logout'));
        $response->assertStatus(401);
    }
}
