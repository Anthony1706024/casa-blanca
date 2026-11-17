@extends('layouts.panel', ['nav' => 'mozo.nav'])

@section('titulo', 'Registrar Pedido')
@section('icono', 'fa-plus-circle')
@section('subtitulo', 'Selecciona lo que pide el cliente; pagará en caja con su DNI')

@section('contenido')
    <form method="POST" action="{{ route('mozo.pedidos.store') }}">
        @csrf

        <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="etiqueta" for="dni">DNI del cliente <span class="text-red-500">*</span></label>
                <input type="text" id="dni" name="dni" value="{{ old('dni') }}" class="campo" inputmode="numeric" maxlength="8" pattern="[0-9]{8}" title="8 dígitos" required>
            </div>
            <div>
                <label class="etiqueta" for="cliente_nombre">Nombre</label>
                <input type="text" id="cliente_nombre" name="cliente_nombre" value="{{ old('cliente_nombre') }}" class="campo" maxlength="150">
            </div>
            <div>
                <label class="etiqueta" for="mesa">Mesa</label>
                <input type="text" id="mesa" name="mesa" value="{{ old('mesa') }}" class="campo" maxlength="20">
            </div>
            <div>
                <label class="etiqueta" for="observacion">Observación</label>
                <input type="text" id="observacion" name="observacion" value="{{ old('observacion') }}" class="campo" maxlength="255">
            </div>
        </div>

        @include('pedidos._selector')
    </form>
@endsection
