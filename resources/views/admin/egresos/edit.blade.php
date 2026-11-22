@extends('layouts.panel', ['nav' => 'admin.nav'])

@section('titulo', 'Editar egreso')
@section('icono', 'fa-file-invoice-dollar')
@section('subtitulo', 'Registrado el '.$egreso->created_at->format('d/m/Y H:i'))
@section('ancho', 'max-w-4xl')

@section('acciones')
    <a href="{{ route('admin.egresos.index', ['periodo' => 'dia', 'fecha' => $egreso->fecha->toDateString()]) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold transition text-sm border border-gray-200">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
@endsection

@section('contenido')
    <form method="POST" action="{{ route('admin.egresos.update', $egreso->id) }}" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm">
        @csrf @method('PUT')
        @include('admin.egresos._form')
        <div class="mt-6 flex justify-end">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg font-semibold transition text-sm shadow-sm">
                <i class="fas fa-save mr-2"></i> Guardar cambios
            </button>
        </div>
    </form>
@endsection
