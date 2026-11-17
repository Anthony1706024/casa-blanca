@extends('layouts.panel', ['nav' => 'admin.nav'])

@section('titulo', 'Editar Insumo')
@section('icono', 'fa-edit')
@section('subtitulo', 'Modifica los datos del insumo')
@section('ancho', 'max-w-4xl')

@section('contenido')
    <form method="POST" action="{{ route('admin.insumos.update', $insumo->id) }}" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm">
        @csrf @method('PUT')
        @include('admin.insumos._form')
    </form>
@endsection
