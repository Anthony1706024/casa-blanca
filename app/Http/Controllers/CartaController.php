<?php

namespace App\Http\Controllers;

use App\Models\Contenido;
use App\Models\Producto;
use App\Services\CartaService;
use App\Services\VentaService;
use Illuminate\Http\Request;

class CartaController extends Controller
{
    // ==========================================
    // CARTA PÚBLICA: el cliente recorre la carta, arma su carrito y envía el prepedido
    // ==========================================
    public function index(CartaService $carta)
    {
        $productos = Producto::disponiblesParaVenta();

        return view('carta.index', [
            'secciones' => $carta->secciones($productos),
            'portada' => $carta->portada($productos),
            'contenido' => Contenido::valores(),
        ]);
    }

    public function store(Request $request, VentaService $ventas)
    {
        $datos = $request->validate([
            'dni' => 'nullable|digits:8',
            'mesa' => 'required|string|max:20',
            'cliente_nombre' => 'nullable|string|max:150',
            'observacion' => 'nullable|string|max:255',
            'items' => 'required|array',
            'items.*' => 'integer|min:0|max:99',
        ], [
            'dni.digits' => 'El DNI debe tener 8 dígitos (o déjalo vacío).',
            'mesa.required' => 'Indica el número de tu mesa.',
            'mesa.max' => 'El número de mesa admite como máximo 20 caracteres.',
            'items.required' => 'Agrega al menos un producto a tu pedido.',
        ]);

        $datos['mesa'] = trim($datos['mesa']);

        $pedido = $ventas->crearPedido($datos, $datos['items'], 'cliente');

        return redirect()->route('carta.index')->with('pedido_registrado', [
            'numero' => $pedido->numero,
            'mesa' => $pedido->mesa,
            'dni' => $pedido->dni,
            'total' => number_format((float) $pedido->total, 2),
        ]);
    }
}
