<?php

namespace Database\Factories\HR;

use App\Enums\HR\EmployeeStatus;
use App\Models\HR\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'head_id' => null,
            'manager_id' => null,
            'status' => EmployeeStatus::Active,
            'start_date' => fake()->dateTimeBetween('+1 day', '+5 days')
                ->format('Y-m-d'),
            'phone_number' => '+375'
                . fake()->randomElement(['25', '29', '33', '44'])
                . fake()->numerify('#######'),
            'personal_email' => fake()->unique()->safeEmail(),
            'birth_date' => fake()->dateTimeBetween('-55 years', '-20 years')
                ->format('Y-m-d'),
            'address' => fake()->address(),
            'iban' => 'BY'
                . fake()->numerify('##')
                . 'ALFA'
                . fake()->numerify('####################'),
            'metadata' => [],
            'end_date' => null,
        ];
    }

    public function withHead(Employee $head): static
    {
        return $this->state(fn () => ['head_id' => $head->id]);
    }

    public function withManager(Employee $manager): static
    {
        return $this->state(fn () => ['manager_id' => $manager->id]);
    }
}
