@extends('layouts.panel', ['nav' => 'cajera.nav'])

@section('titulo', 'Nuevo Pedido')
@section('icono', 'fa-plus-circle')
@section('subtitulo', 'Registra lo que pide el cliente en caja; al guardar pasas directo al cobro')

@section('contenido')
    <form method="POST" action="{{ route('cajera.pedidos.store') }}">
        @csrf

        @include('pedidos._datos-cliente')

        @include('pedidos._selector', ['textoBoton' => 'Guardar y cobrar', 'iconoBoton' => 'fa-cash-register'])
    </form>
@endsection
