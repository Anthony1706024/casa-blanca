<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recibo;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $query = Recibo::with(['pedido', 'pagos', 'cajero']);

        if ($request->filled('dni')) {
            $query->whereHas('pedido', fn ($q) => $q->where('dni', 'LIKE', "%{$request->dni}%"));
        }

        if ($request->filled('fecha')) {
            $query->whereDate('created_at', $request->fecha);
        }

        $total = (clone $query)->sum('total');
        $recibos = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        return view('admin.ventas.index', compact('recibos', 'total'));
    }
}
