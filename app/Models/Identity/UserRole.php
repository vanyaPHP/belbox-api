<?php

declare(strict_types=1);

namespace App\Models\Identity;

use App\Enums\Identity\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_id
 * @property Role $role
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class UserRole extends Model
{
    use HasUuids;

    protected $casts = [
        'role' => Role::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
