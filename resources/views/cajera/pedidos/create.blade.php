@extends('layouts.panel', ['nav' => 'cajera.nav'])

@section('titulo', 'Nuevo Pedido')
@section('icono', 'fa-plus-circle')
@section('subtitulo', 'Solo la mesa es obligatoria. El cobro se registra después, cuando el cliente pague')

@section('contenido')
    <form method="POST" action="{{ route('cajera.pedidos.store') }}">
        @csrf

        @include('pedidos._datos-cliente')

        @include('pedidos._selector', ['textoBoton' => 'Guardar', 'iconoBoton' => 'fa-save'])
    </form>
@endsection
