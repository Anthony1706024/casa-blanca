@extends('layouts.panel', ['nav' => 'admin.nav'])

@section('titulo', 'Registrar Insumo')
@section('icono', 'fa-cube')
@section('subtitulo', 'Las bebidas y gaseosas también se registran como insumo')
@section('ancho', 'max-w-4xl')

@section('contenido')
    <form method="POST" action="{{ route('admin.insumos.store') }}" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm">
        @csrf
        @include('admin.insumos._form')
    </form>
@endsection
