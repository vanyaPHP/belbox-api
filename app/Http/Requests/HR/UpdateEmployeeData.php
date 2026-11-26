<?php

declare(strict_types=1);

namespace App\Http\Requests\HR;

use App\Enums\Identity\Role;
use App\Rules\BelarusianMobilePhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeData extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'head_id' => ['nullable', 'exists:employees,id'],
            'full_name' => ['nullable', 'string'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['nullable', 'string', Rule::enum(Role::class)],
            'phone_number' => ['nullable', 'string', new BelarusianMobilePhone()],
            'personal_email' => ['nullable', 'email', 'unique:employees,personal_email'],
            'address' => ['nullable', 'string'],
            'iban' => ['nullable', 'string'],
        ];
    }
}
