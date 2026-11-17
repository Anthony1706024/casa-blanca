@extends('layouts.panel', ['nav' => 'mozo.nav'])

@section('titulo', 'Panel de Mozo')
@section('icono', 'fa-utensils')
@section('subtitulo', 'Registra los pedidos de tus clientes')

@section('acciones')
    <a href="{{ route('mozo.pedidos.create') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm shadow-sm">
        <i class="fas fa-plus-circle mr-1"></i> Registrar Pedido
    </a>
@endsection

@section('contenido')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('mozo.pedidos.index', ['estado' => 'pendiente']) }}" class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm hover:border-orange-300 transition">
            <p class="text-gray-500 text-sm">Pedidos pendientes de pago</p>
            <p class="text-black text-3xl font-bold mt-1">{{ $pendientes }}</p>
        </a>
        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">Mis pedidos de hoy</p>
            <p class="text-black text-3xl font-bold mt-1">{{ $misPedidosHoy }}</p>
        </div>
        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">Prepedidos de clientes por pagar</p>
            <p class="text-black text-3xl font-bold mt-1">{{ $pedidosCliente }}</p>
        </div>
    </div>
@endsection
