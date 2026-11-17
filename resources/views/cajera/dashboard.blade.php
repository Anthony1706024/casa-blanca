@extends('layouts.panel', ['nav' => 'cajera.nav'])

@section('titulo', 'Panel de Caja')
@section('icono', 'fa-cash-register')
@section('subtitulo', 'Busca el pedido por DNI y registra el cobro')

@section('contenido')
    <form method="GET" action="{{ route('cajera.pedidos.index') }}" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6 flex flex-col sm:flex-row gap-4 sm:items-end">
        <div class="flex-1">
            <label class="etiqueta" for="dni">DNI del cliente</label>
            <input type="text" id="dni" name="dni" class="campo" inputmode="numeric" maxlength="8" autofocus>
        </div>
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg font-semibold transition text-sm">
            <i class="fas fa-search mr-2"></i> Buscar pedido
        </button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="{{ route('cajera.pedidos.index') }}" class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm hover:border-orange-300 transition">
            <p class="text-gray-500 text-sm">Pedidos por cobrar</p>
            <p class="text-black text-3xl font-bold mt-1">{{ $pendientes }}</p>
        </a>
        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">Monto por cobrar</p>
            <p class="text-black text-3xl font-bold mt-1">S/ {{ number_format($porCobrar, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
            <p class="text-gray-500 text-sm">Cobrado hoy</p>
            <p class="text-black text-3xl font-bold mt-1">S/ {{ number_format($cobradoHoy, 2) }}</p>
        </div>
        <a href="{{ route('cajera.recibos.index') }}" class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm hover:border-orange-300 transition">
            <p class="text-gray-500 text-sm">Recibos de hoy</p>
            <p class="text-black text-3xl font-bold mt-1">{{ $recibosHoy }}</p>
        </a>
    </div>
@endsection
