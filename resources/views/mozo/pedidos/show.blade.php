@extends('layouts.panel', ['nav' => 'mozo.nav'])

@section('titulo', "Pedido {$pedido->numero}")
@section('icono', 'fa-receipt')
@section('subtitulo', collect([$pedido->mesa ? "Mesa {$pedido->mesa}" : null, $pedido->cliente_nombre, $pedido->dni ? "DNI {$pedido->dni}" : null])->filter()->implode(' · '))
@section('ancho', 'max-w-3xl')

@section('acciones')
    <a href="{{ route('mozo.pedidos.index') }}" class="text-gray-600 hover:text-black transition text-sm py-2">
        <i class="fas fa-arrow-left mr-1"></i> Mis pedidos
    </a>
@endsection

@section('contenido')
    <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-bold text-black">Detalle</h2>
            @include('pedidos._estado', ['estado' => $pedido->estado])
        </div>

        @if($pedido->observacion)
            <p class="text-sm text-gray-600 mb-3"><span class="text-gray-500">Observación:</span> {{ $pedido->observacion }}</p>
        @endif

        @include('pedidos._items', ['prefijo' => 'mozo'])

        <div class="flex justify-between text-xl font-bold mt-4">
            <span>Total</span>
            <span class="text-orange-600">S/ {{ number_format($pedido->total, 2) }}</span>
        </div>

        @if($pedido->estado === \App\Models\Pedido::PENDIENTE)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-5 pt-5 border-t border-gray-200">
                <a href="{{ route('mozo.pedidos.adicionales', $pedido->id) }}" class="text-center bg-orange-500 hover:bg-orange-600 text-white px-4 py-2.5 rounded-lg font-semibold transition text-sm">
                    <i class="fas fa-plus-circle mr-1"></i> Agregar adicionales
                </a>
                <form method="POST" action="{{ route('mozo.pedidos.anular', $pedido->id) }}" onsubmit="return confirm('¿Anular este pedido?')">
                    @csrf
                    <button type="submit" class="w-full text-red-600 bg-red-50 hover:bg-red-100 px-4 py-2.5 rounded-lg font-semibold transition text-sm">
                        <i class="fas fa-ban mr-1"></i> Anular pedido
                    </button>
                </form>
            </div>
            <p class="text-xs text-gray-400 mt-2">Los adicionales le aparecen a caja para que imprima en cocina solo lo nuevo. El cobro lo registra caja.</p>
        @endif
    </div>
@endsection
