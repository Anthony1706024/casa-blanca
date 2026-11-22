<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\ClaveTicketera;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 👇 REGISTRAR EL MIDDLEWARE CON ALIAS
        $middleware->alias([
            'checkrole' => CheckRole::class,
            'ticketera.clave' => ClaveTicketera::class,
        ]);

        // El agente de la ticketera no tiene sesión: se identifica con su clave (ClaveTicketera)
        $middleware->validateCsrfTokens(except: ['ticketera/*']);

        // Railway atiende por https y reenvía a la app por http: se confía en su proxy
        // para que los enlaces y formularios salgan con https
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
