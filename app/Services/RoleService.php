<?php

namespace App\Services;

use App\Models\Role;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RoleService
{
    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data): Role {
            return Role::create($this->prepareData($data));
        });
    }

    public function update(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data): Role {
            $role->update($this->prepareData($data, $role));

            return $role->refresh();
        });
    }

    public function delete(Role $role): bool
    {
        if ($role->slug === 'super-admin') {
            throw new DomainException('The Super Admin role cannot be deleted.');
        }

        if ($role->users()->exists()) {
            throw new DomainException('This role is assigned to users and cannot be deleted.');
        }

        return DB::transaction(function () use ($role): bool {
            $role->permissions()->sync([]);

            return (bool) $role->delete();
        });
    }

    public function syncPermissions(Role $role, array $permissionIds): void
    {
        DB::transaction(function () use ($role, $permissionIds): void {
            $role->permissions()->sync($permissionIds);
        });
    }

    private function prepareData(array $data, ?Role $role = null): array
    {
        $payload = Arr::only($data, ['name', 'slug', 'description']);

        if (! Schema::hasColumn('roles', 'slug')) {
            return Arr::except($payload, ['slug']);
        }

        $shouldGenerateSlug = $role === null
            ? blank($payload['slug'] ?? null)
            : array_key_exists('slug', $payload) && blank($payload['slug']);

        if ($shouldGenerateSlug) {
            $payload['slug'] = $this->uniqueSlug($payload['name'], $role);
        }

        return $payload;
    }

    private function uniqueSlug(string $name, ?Role $role = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Role::where('slug', $slug)
                ->when($role !== null, fn ($query) => $query->whereKeyNot($role->getKey()))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
