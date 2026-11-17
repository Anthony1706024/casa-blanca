@extends('layouts.panel', ['nav' => 'admin.nav'])

@section('titulo', 'Editar '.$config['singular'])
@section('icono', $config['icono'])
@section('subtitulo', $config['nombre'])
@section('ancho', 'max-w-4xl')

@section('acciones')
    <a href="{{ route('admin.contenido.items', $item->tipo) }}" class="text-gray-600 hover:text-black transition text-sm py-2">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
@endsection

@section('contenido')
    <form method="POST" action="{{ route('admin.contenido.items.update', $item->id) }}" enctype="multipart/form-data" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm">
        @csrf @method('PUT')
        @include('admin.contenido._item-campos')
        <div class="flex justify-end mt-6">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-lg font-semibold transition text-sm shadow-sm">
                <i class="fas fa-save mr-2"></i> Guardar
            </button>
        </div>
    </form>
@endsection
