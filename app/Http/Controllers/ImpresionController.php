<?php

namespace App\Http\Controllers;

use App\Models\Impresion;
use App\Services\ColaImpresionService;
use App\Services\TicketeraService;

// Seguimiento de la cola de impresión, para caja y admin
class ImpresionController extends Controller
{
    public function index(ColaImpresionService $cola, TicketeraService $ticketera)
    {
        $impresiones = Impresion::select(['id', 'tipo', 'descripcion', 'estado', 'intentos', 'error', 'created_at', 'impresa_en'])
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $atascadas = $cola->hayAtascadas();
        $enCola = $ticketera->enCola() && $ticketera->configurada();
        $nav = auth()->user()->isAdmin() ? 'admin.nav' : 'cajera.nav';

        return view('impresiones.index', compact('impresiones', 'atascadas', 'enCola', 'nav'));
    }

    public function reintentar($id, ColaImpresionService $cola)
    {
        $impresion = Impresion::findOrFail($id);

        if ($impresion->estado !== Impresion::ERROR) {
            return back()->with('error', 'Solo se reintentan las impresiones con error.');
        }

        $cola->reintentar($impresion);

        return back()->with('success', "«{$impresion->descripcion}» volvió a la cola.");
    }
}
