<?php

namespace Tests\Concerns;

use App\Models\User;
use Spatie\Permission\Models\Permission;

trait GrantsPaaPlanPermissions
{
    /** Otorga al usuario los permisos del recurso Planadquisicione (gobernado por Shield). */
    protected function grantPlanPermissions(User $user): void
    {
        foreach (['view_any', 'view', 'create', 'update', 'delete', 'delete_any'] as $p) {
            $user->givePermissionTo(Permission::findOrCreate("{$p}_planadquisicione"));
        }
    }
}
