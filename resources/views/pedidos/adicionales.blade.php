{{-- Agregar adicionales a un pedido pendiente. Lo usan caja y mozo: $prefijo = 'cajera' | 'mozo'. --}}
@extends('layouts.panel', ['nav' => $prefijo.'.nav'])

@section('titulo', 'Agregar adicionales')
@section('icono', 'fa-plus-circle')
@section('subtitulo', collect(["Pedido {$pedido->numero}", $pedido->mesa ? "Mesa {$pedido->mesa}" : null, $pedido->cliente_nombre])->filter()->implode(' · '))

@section('acciones')
    <a href="{{ route($prefijo.'.pedidos.show', $pedido->id) }}" class="text-gray-600 hover:text-black transition text-sm py-2">
        <i class="fas fa-arrow-left mr-1"></i> Volver al pedido
    </a>
@endsection

@section('contenido')
    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6 text-sm">
        <p class="font-semibold text-black mb-1">Ya tiene (S/ {{ number_format($pedido->total, 2) }}):</p>
        <p class="text-gray-700">
            {{ $pedido->items->map(fn ($item) => $item->cantidad.' × '.$item->producto_nombre.($item->esAdicional() ? ' (adicional)' : ''))->implode(' · ') }}
        </p>
        <p class="text-gray-500 text-xs mt-2">Lo que agregues sale como <b>adicional</b>: caja imprime para cocina solo lo nuevo.</p>
    </div>

    <form method="POST" action="{{ route($prefijo.'.pedidos.adicionales.store', $pedido->id) }}">
        @csrf

        @include('pedidos._selector', ['textoBoton' => 'Agregar al pedido', 'iconoBoton' => 'fa-plus-circle'])
    </form>
@endsection
