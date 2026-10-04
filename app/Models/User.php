<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Determine whether the user holds the given role.
     */
    public function hasRole(UserRole|string $role): bool
    {
        return $this->role === ($role instanceof UserRole ? $role : UserRole::tryFrom($role));
    }

    /**
     * Determine whether the user holds any one of the given roles.
     */
    public function hasAnyRole(UserRole|string ...$roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::Admin);
    }

    public function isCashier(): bool
    {
        return $this->hasRole(UserRole::Cashier);
    }

    public function isStaff(): bool
    {
        return $this->hasRole(UserRole::Staff);
    }

    /**
     * Count the administrators. Used to guarantee the last one cannot be removed.
     */
    public static function adminCount(): int
    {
        return static::query()->where('role', UserRole::Admin->value)->count();
    }
}
