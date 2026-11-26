<?php

declare(strict_types=1);

namespace App\Http\Requests\HR;

use App\Enums\HR\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadDocumentData extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', Rule::enum(DocumentType::class)],
            'file' => ['required', 'file'],
        ];
    }
}
