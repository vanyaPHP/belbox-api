<?php

declare(strict_types=1);

namespace App\Http\Resources\HR;

use App\Models\HR\Employee;
use App\Models\Identity\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Employee
 */
class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', [
                'id' => $this->user->id,
                'full_name' => $this->user->full_name,
                'email' => $this->user->email,
                'roles' => $this->when(
                    $this->user->relationLoaded('roles'),
                    fn () => $this->user->roles->map(fn (UserRole $role) => $role->role->value)->toArray(),
                    null,
                ),
            ], null),
            'head' => $this->whenLoaded(
                'head',
                $this->head?->user?->full_name,
                null
            ),
            'manager' => $this->whenExistsLoaded(
                'manager',
                $this->manager?->user?->full_name,
                null
            ),
            'status' => $this->status->value,
            'start_date' => $this->start_date->format('Y-m-d'),
            'phone_number' => $this->phone_number,
            'personal_email' => $this->personal_email,
            'birth_date' => $this->birth_date->format('Y-m-d'),
            'address' => $this->address,
            'iban' => $this->iban,
            'metadata' => $this->metadata,
            'end_date' => $this->end_date?->utc(),
            'documents' => $this->whenLoaded(
                'documents',
                EmployeeDocumentResource::collection($this->documents),
                null
            ),
            'created_at' => $this->created_at->utc(),
            'updated_at' => $this->updated_at->utc(),
            'deleted_at' => $this->deleted_at?->utc(),
        ];
    }
}
