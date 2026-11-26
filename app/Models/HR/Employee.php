<?php

declare(strict_types=1);

namespace App\Models\HR;

use App\Enums\HR\EmployeeStatus;
use App\Models\User;
use Database\Factories\HR\EmployeeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_id
 * @property string|null $head_id
 * @property string|null $manager_id
 * @property EmployeeStatus $status
 * @property Carbon $start_date
 * @property string|null $phone_number
 * @property string|null $personal_email
 * @property Carbon $birth_date
 * @property string $address
 * @property string|null $iban
 * @property array $metadata
 * @property Carbon|null $end_date
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon|null $deleted_at
 *
 * @property-read User $user
 * @property-read Employee|null $head
 * @property-read Collection<int, Employee> $headedEmployees
 * @property-read Employee|null $manager
 * @property-read Collection<int, Employee< $managedEmployees
 * @property-read Collection<int, EmployeeDocument> $documents
 */
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasUuids, SoftDeletes, HasFactory;

    protected $casts = [
        'status' => EmployeeStatus::class,
        'start_date' => 'date',
        'birth_date' => 'date',
        'metadata' => 'array',
        'end_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'head_id');
    }

    public function headedEmployees(): HasMany
    {
        return $this->hasMany(Employee::class, 'head_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function managedEmployees(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id');
    }

    public function getUplineQuery(string $column = 'head_id'): Builder
    {
        $table = $this->getTable();
        $parentId = $this->{$column};
        if (! $parentId) {
            return static::query()->whereRaw('1 = 0');
        }

        $sql = "(
            WITH RECURSIVE upline AS (
                SELECT * FROM {$table} WHERE id = ? AND deleted_at IS NULL
                UNION ALL
                SELECT e.* FROM {$table} e
                INNER JOIN upline u ON e.id = u.{$column}
                WHERE e.deleted_at IS NULL
            )
            SELECT * FROM upline
        ) AS {$table}";

        return static::query()->fromRaw($sql, [$parentId]);
    }

    public function getDownlineQuery(string $column = 'head_id'): Builder
    {
        $table = $this->getTable();

        $sql = "(
            WITH RECURSIVE downline AS (
                SELECT * FROM {$table} WHERE {$column} = ? AND deleted_at IS NULL
                UNION ALL
                SELECT e.* FROM {$table} e
                INNER JOIN downline d ON e.{$column} = d.id
                WHERE e.deleted_at IS NULL
            )
            SELECT * FROM downline
        ) AS {$table}";

        return static::query()->fromRaw($sql, [$this->id]);
    }

    public function isInDownlineOf(Employee $employee, string $column = 'head_id'): bool
    {
        if ($this->id === $employee->id || ! $this->{$column}) {
            return false;
        }

        if ($this->{$column} === $employee->id) {
            return true;
        }

        return $this->getUplineQuery($column)
            ->where('id', $employee->id)
            ->exists();
    }
}
