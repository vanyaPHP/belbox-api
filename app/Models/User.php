<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Identity\Role;
use App\Models\Identity\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string $id
 * @property string $full_name
 * @property string $email
 * @property string $password
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read Collection<int, UserRole> $roles
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasUuids, HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    protected $casts = [
        'password' => 'hashed',
    ];

    public function roles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    /**
     * @return array<string>
     */
    public function rolesList(): array
    {
        return $this->roles()
            ->get()
            ->map(fn (UserRole $userRole): string => $userRole->role->value)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string>
     */
    public function permissions(): array
    {
        $roles = $this->rolesList();
        $permissions = [];

        foreach ($roles as $role) {
            $rolePermissions = Role::from($role)->permissions();
            $permissions = array_unique(array_merge($permissions, $rolePermissions));
        }

        return $permissions;
    }
}
