@extends('layouts.panel', ['nav' => 'cajera.nav'])

@section('titulo', 'Recibos Emitidos')
@section('icono', 'fa-receipt')
@section('subtitulo', 'Consulta y reimprime los recibos')

@section('contenido')
    <form method="GET" action="{{ route('cajera.recibos.index') }}" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6 flex flex-col sm:flex-row gap-4 sm:items-end">
        <div class="flex-1">
            <label class="etiqueta">DNI</label>
            <input type="text" name="dni" value="{{ request('dni') }}" class="campo" maxlength="8">
        </div>
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg font-semibold transition text-sm">
            <i class="fas fa-search mr-2"></i> Buscar
        </button>
    </form>

    @include('recibos._tabla', ['recibos' => $recibos])
@endsection
