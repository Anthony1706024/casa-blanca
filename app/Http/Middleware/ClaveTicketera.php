<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Solo el agente de la ticketera entra a /ticketera: envía "Authorization: Bearer <TICKETERA_CLAVE>"
class ClaveTicketera
{
    public function handle(Request $request, Closure $next): Response
    {
        $clave = (string) config('services.ticketera.clave');

        if ($clave === '') {
            return response()->json(['mensaje' => 'La cola de impresión no está configurada (falta TICKETERA_CLAVE).'], 403);
        }

        if (! hash_equals($clave, (string) $request->bearerToken())) {
            return response()->json(['mensaje' => 'Clave de la ticketera incorrecta.'], 401);
        }

        return $next($request);
    }
}
