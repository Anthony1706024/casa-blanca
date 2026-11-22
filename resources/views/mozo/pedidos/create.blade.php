@extends('layouts.panel', ['nav' => 'mozo.nav'])

@section('titulo', 'Registrar Pedido')
@section('icono', 'fa-plus-circle')
@section('subtitulo', 'Selecciona lo que pide el cliente; pagará en caja con su DNI')

@section('contenido')
    <form method="POST" action="{{ route('mozo.pedidos.store') }}">
        @csrf

        @include('pedidos._datos-cliente')

        @include('pedidos._selector')
    </form>
@endsection
