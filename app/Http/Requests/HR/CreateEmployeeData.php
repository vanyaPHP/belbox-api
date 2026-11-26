<?php

declare(strict_types=1);

namespace App\Http\Requests\HR;

use App\Enums\Identity\Role;
use App\Rules\BelarusianMobilePhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEmployeeData extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string'],
            'roles' => ['required', 'array'],
            'roles.*' => ['required', 'string', Rule::enum(Role::class)],
            'start_date' => ['required', 'date', 'date_format:Y-m-d'],
            'phone_number' => ['required', 'string', new BelarusianMobilePhone()],
            'personal_email' => ['required', 'email', 'unique:employees,personal_email'],
            'birth_date' => ['required', 'date', 'date_format:Y-m-d'],
            'address' => ['required', 'string'],
            'iban' => ['required', 'string'],
        ];
    }
}
