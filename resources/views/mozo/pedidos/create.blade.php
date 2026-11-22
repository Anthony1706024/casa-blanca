@extends('layouts.panel', ['nav' => 'mozo.nav'])

@section('titulo', 'Registrar Pedido')
@section('icono', 'fa-plus-circle')
@section('subtitulo', 'Solo la mesa es obligatoria. El cliente paga en caja')

@section('contenido')
    <form method="POST" action="{{ route('mozo.pedidos.store') }}">
        @csrf

        @include('pedidos._datos-cliente')

        @include('pedidos._selector')
    </form>
@endsection
