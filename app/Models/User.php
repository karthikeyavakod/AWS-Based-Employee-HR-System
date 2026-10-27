<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
            ->withTimestamps();
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function departmentManagers(): HasMany
    {
        return $this->hasMany(DepartmentManager::class);
    }

    public function managedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'id')->join('department_managers', 'departments.id', '=', 'department_managers.department_id')->where('department_managers.user_id', $this->id);
    }

    public function isDepartmentManager(): bool
    {
        return $this->hasRole('department-manager') || $this->departmentManagers()->exists();
    }

    public function managedDepartmentIds(): array
    {
        return $this->departmentManagers()->pluck('department_id')->all();
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()
            ->where(function ($query) use ($role) {
                $query->where('slug', $role)
                    ->orWhere('name', $role);
            })
            ->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        if ($roles === []) {
            return false;
        }

        return $this->roles()
            ->where(function ($query) use ($roles) {
                $query->whereIn('slug', $roles)
                    ->orWhereIn('name', $roles);
            })
            ->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission)
                    ->orWhere('name', $permission);
            })
            ->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }
}
