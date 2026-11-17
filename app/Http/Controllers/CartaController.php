<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\VentaService;
use Illuminate\Http\Request;

class CartaController extends Controller
{
    // ==========================================
    // CARTA PÚBLICA: el cliente ve lo disponible y arma su prepedido
    // ==========================================
    public function index()
    {
        $productos = Producto::disponiblesParaVenta();

        return view('carta.index', compact('productos'));
    }

    public function store(Request $request, VentaService $ventas)
    {
        $datos = $request->validate([
            'dni' => 'required|digits:8',
            'cliente_nombre' => 'nullable|string|max:150',
            'observacion' => 'nullable|string|max:255',
            'items' => 'required|array',
            'items.*' => 'integer|min:0|max:99',
        ], [
            'dni.required' => 'Ingresa tu DNI para registrar el pedido.',
            'dni.digits' => 'El DNI debe tener 8 dígitos.',
            'items.required' => 'Selecciona al menos un producto.',
        ]);

        $pedido = $ventas->crearPedido($datos, $datos['items'], 'cliente');

        return redirect()->route('carta.index')
            ->with('success', "¡Pedido {$pedido->numero} registrado! Acércate a caja con tu DNI {$pedido->dni} para pagar S/ ".number_format((float) $pedido->total, 2).'.');
    }
}
