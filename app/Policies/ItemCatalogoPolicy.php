<?php

namespace App\Policies;

use App\Models\User;

/**
 * Todos los módulos BPIM son exclusivos de super_admin (ver spec
 * docs/superpowers/specs/2026-09-08-modulo-bpim-y-landing-design.md).
 */
class ItemCatalogoPolicy
{
    private function isSuperAdmin(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function view(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function update(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function delete(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function restore(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function forceDelete(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }
}
