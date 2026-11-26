<?php

namespace Database\Factories;

use App\Enums\Identity\Role;
use App\Models\HR\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => fake()->firstName() . ' ' . fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
        ];
    }

    public function withRoles(Role ...$roles): static
    {
        return $this->afterCreating(function (User $user) use ($roles) {
            $roles = array_map(fn (Role $role): array => ['role' => $role->value], $roles);

            $user->roles()->createMany($roles);
        });
    }

    public function superAdmin(array $employeeAttributes = []): static
    {
        return $this
            ->withRoles(Role::SuperAdmin)
            ->has(Employee::factory()->state($employeeAttributes));
    }

    public function hrHead(array $employeeAttributes = []): static
    {
        return $this
            ->withRoles(Role::HrHead)
            ->has(Employee::factory()->state($employeeAttributes));
    }

    public function hrManager(array $employeeAttributes = []): static
    {
        return $this
            ->withRoles(Role::HrManager)
            ->has(Employee::factory()->state($employeeAttributes));
    }

    public function driver(array $employeeAttributes = []): static
    {
        return $this
            ->withRoles(Role::Driver)
            ->has(Employee::factory()->state($employeeAttributes));
    }
}
