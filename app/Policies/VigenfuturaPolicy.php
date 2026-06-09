<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vigenfutura;
use Illuminate\Auth\Access\HandlesAuthorization;

class VigenfuturaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_vigenfutura');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Vigenfutura $vigenfutura): bool
    {
        return $user->can('view_vigenfutura');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_vigenfutura');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vigenfutura $vigenfutura): bool
    {
        return $user->can('update_vigenfutura');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Vigenfutura $vigenfutura): bool
    {
        return $user->can('delete_vigenfutura');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_vigenfutura');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Vigenfutura $vigenfutura): bool
    {
        return $user->can('force_delete_vigenfutura');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_vigenfutura');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Vigenfutura $vigenfutura): bool
    {
        return $user->can('restore_vigenfutura');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_vigenfutura');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Vigenfutura $vigenfutura): bool
    {
        return $user->can('replicate_vigenfutura');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_vigenfutura');
    }
}
