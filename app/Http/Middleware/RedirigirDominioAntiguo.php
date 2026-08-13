<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirige el dominio antiguo (arl.ticsistemas.com.co) al nuevo
 * (tramites.ticsistemas.com.co) con un 301 permanente, conservando la ruta
 * y la query. Necesario porque los PDF y correos ya emitidos (con su QR de
 * verificación) apuntan al dominio antiguo. Mientras el hostname "arl" siga
 * enrutando a esta app (por el túnel de Cloudflare), este middleware hace que
 * /actas/verificar/{codigo} y cualquier otra ruta terminen en el dominio nuevo.
 */
class RedirigirDominioAntiguo
{
    /**
     * Hosts antiguos que deben redirigir al dominio nuevo.
     */
    protected array $hostsAntiguos = [
        'arl.ticsistemas.com.co',
    ];

    protected string $hostNuevo = 'tramites.ticsistemas.com.co';

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array(strtolower($request->getHost()), $this->hostsAntiguos, true)) {
            return redirect()->away(
                'https://' . $this->hostNuevo . $request->getRequestUri(),
                301
            );
        }

        return $next($request);
    }
}
