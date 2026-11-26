<?php

declare(strict_types=1);

namespace Database\Factories\HR;

use App\Enums\HR\DocumentType;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeDocument>
 */
class EmployeeDocumentFactory extends Factory
{
    protected $model = EmployeeDocument::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'document_type' => DocumentType::Passport,
            'path' => $this->faker->imageUrl(),
        ];
    }
}
