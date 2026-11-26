<?php

declare(strict_types=1);

namespace App\Http\Resources\HR;

use App\Models\HR\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EmployeeDocument
 */
class EmployeeDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_type' => $this->document_type,
            'path' => $this->path,
        ];
    }
}
