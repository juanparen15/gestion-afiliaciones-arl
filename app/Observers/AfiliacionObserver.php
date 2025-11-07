<?php

namespace App\Observers;

use App\Events\AfiliacionCreada;
use App\Models\Afiliacion;

class AfiliacionObserver
{
    /**
     * Handle the Afiliacion "created" event.
     */
    public function created(Afiliacion $afiliacion): void
    {
        // Disparar evento solo si el estado es pendiente
        if ($afiliacion->estado === 'pendiente') {
            event(new AfiliacionCreada($afiliacion));
        }
    }
}
