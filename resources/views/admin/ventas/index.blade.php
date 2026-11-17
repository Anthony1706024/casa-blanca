@extends('layouts.panel', ['nav' => 'admin.nav'])

@section('titulo', 'Ventas')
@section('icono', 'fa-shopping-cart')
@section('subtitulo', 'Recibos emitidos por caja')

@section('contenido')
    <form method="GET" action="{{ route('admin.ventas.index') }}" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
        <div>
            <label class="etiqueta">DNI</label>
            <input type="text" name="dni" value="{{ request('dni') }}" class="campo" maxlength="8">
        </div>
        <div>
            <label class="etiqueta">Fecha</label>
            <input type="date" name="fecha" value="{{ request('fecha') }}" class="campo">
        </div>
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2.5 rounded-lg font-semibold transition text-sm">
            <i class="fas fa-search mr-2"></i> Buscar
        </button>
    </form>

    <div class="bg-orange-50 border border-orange-200 rounded-lg px-6 py-4 mb-6 flex justify-between items-center">
        <span class="text-gray-700 font-semibold">Total de los recibos listados</span>
        <span class="text-2xl font-bold text-orange-600">S/ {{ number_format($total, 2) }}</span>
    </div>

    @include('recibos._tabla', ['recibos' => $recibos])
@endsection
