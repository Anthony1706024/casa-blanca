@extends('layouts.panel', ['nav' => 'cajera.nav'])

@section('titulo', 'Agregar adicionales')
@section('icono', 'fa-plus-circle')
@section('subtitulo', "Pedido {$pedido->numero}".($pedido->mesa ? " · Mesa {$pedido->mesa}" : '')." · DNI {$pedido->dni}")

@section('acciones')
    <a href="{{ route('cajera.pedidos.show', $pedido->id) }}" class="text-gray-600 hover:text-black transition text-sm py-2">
        <i class="fas fa-arrow-left mr-1"></i> Volver al cobro
    </a>
@endsection

@section('contenido')
    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6 text-sm">
        <p class="font-semibold text-black mb-1">Ya tiene (S/ {{ number_format($pedido->total, 2) }}):</p>
        <p class="text-gray-700">
            {{ $pedido->items->map(fn ($item) => $item->cantidad.' × '.$item->producto_nombre)->implode(' · ') }}
        </p>
    </div>

    <form method="POST" action="{{ route('cajera.pedidos.adicionales.store', $pedido->id) }}">
        @csrf

        @include('pedidos._selector', ['textoBoton' => 'Agregar al pedido', 'iconoBoton' => 'fa-plus-circle'])
    </form>
@endsection
