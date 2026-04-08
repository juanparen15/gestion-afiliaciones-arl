<?php

namespace App\Policies;

use App\Models\User;

class WhatsappAgentPolicy
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
}
