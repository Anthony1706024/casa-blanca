@extends('layouts.panel', ['nav' => 'admin.nav'])

@section('titulo', $config['nombre'])
@section('icono', $config['icono'])
@section('subtitulo', 'Lo que se muestra en esta sección de tu página web')

@section('acciones')
    <a href="{{ route('admin.contenido.index') }}" class="text-gray-600 hover:text-black transition text-sm py-2">
        <i class="fas fa-arrow-left mr-1"></i> Volver a Contenido
    </a>
@endsection

@section('contenido')
    <form method="POST" action="{{ route('admin.contenido.items.store', $tipo) }}" enctype="multipart/form-data" class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6">
        @csrf
        <h2 class="font-bold text-black mb-4">Agregar {{ $config['singular'] }}</h2>
        @include('admin.contenido._item-campos', ['item' => null])
        <div class="flex justify-end mt-4">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg font-semibold transition text-sm">
                <i class="fas fa-plus-circle mr-2"></i> Agregar
            </button>
        </div>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($items as $elemento)
            <article class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden {{ $elemento->activo ? '' : 'opacity-60' }}">
                <div class="h-40 bg-orange-50 flex items-center justify-center">
                    @if($elemento->imagen)
                        <img src="{{ $elemento->imagen_url }}" alt="{{ $elemento->titulo }}" class="w-full h-full object-cover">
                    @else
                        <i class="fas {{ $config['icono'] }} text-3xl text-orange-300"></i>
                    @endif
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-black">{{ $elemento->titulo }}</h3>
                    @if($elemento->descripcion)<p class="text-gray-500 text-sm mt-1">{{ $elemento->descripcion }}</p>@endif
                    @unless($elemento->activo)<p class="text-xs text-gray-500 mt-1">Oculto en la página</p>@endunless
                    <div class="flex gap-2 mt-3">
                        <a href="{{ route('admin.contenido.items.edit', $elemento->id) }}" class="text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded text-sm"><i class="fas fa-pen mr-1"></i> Editar</a>
                        <form method="POST" action="{{ route('admin.contenido.items.destroy', $elemento->id) }}" onsubmit="return confirm('¿Eliminar este elemento?')">
                            @csrf @method('DELETE')
                            <button type="submit" aria-label="Eliminar {{ $elemento->titulo }}" class="text-red-600 bg-red-50 hover:bg-red-100 px-3 py-1 rounded text-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <p class="py-8 text-center text-gray-400 sm:col-span-2 lg:col-span-3">Aún no hay elementos. Esta sección no se muestra en la página hasta que agregues uno.</p>
        @endforelse
    </div>
@endsection
