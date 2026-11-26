<?php

declare(strict_types=1);

namespace App\Models\HR;

use App\Enums\HR\DocumentType;
use Database\Factories\HR\EmployeeDocumentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $employee_id
 * @property DocumentType $document_type
 * @property string $path
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 *
 * @property-read Employee $employee
 */
class EmployeeDocument extends Model
{
    /** @use HasFactory<EmployeeDocumentFactory> */
    use HasUuids, HasFactory;

    protected $casts = [
        'document_type' => DocumentType::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
