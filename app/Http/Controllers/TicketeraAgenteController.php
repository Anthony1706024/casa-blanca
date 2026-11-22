<?php

namespace App\Http\Controllers;

use App\Models\Impresion;
use App\Services\ColaImpresionService;
use Illuminate\Http\Request;

// Endpoints que consulta el agente de la PC de caja (resources/ticketera/agente). Protegidos por ClaveTicketera.
class TicketeraAgenteController extends Controller
{
    public function pendientes(Request $request, ColaImpresionService $cola)
    {
        $limite = min(max($request->integer('limite', 5), 1), 20);

        return response()->json($cola->tomarPendientes($limite)->map(fn (Impresion $impresion) => [
            'id' => $impresion->id,
            'tipo' => $impresion->tipo,
            'descripcion' => $impresion->descripcion,
            'datos' => $impresion->datos,
        ])->values());
    }

    public function impresa($id, ColaImpresionService $cola)
    {
        $cola->marcarImpresa(Impresion::findOrFail($id));

        return response()->json(['ok' => true]);
    }

    public function error(Request $request, $id, ColaImpresionService $cola)
    {
        $datos = $request->validate(['mensaje' => 'nullable|string|max:1000']);
        $impresion = Impresion::findOrFail($id);

        $cola->marcarError($impresion, $datos['mensaje'] ?? 'Error sin detalle');

        return response()->json(['ok' => true, 'estado' => $impresion->estado]);
    }
}
