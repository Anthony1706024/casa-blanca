@extends('layouts.panel', ['nav' => 'admin.nav'])

@section('titulo', 'Contenido de la Página')
@section('icono', 'fa-globe')
@section('subtitulo', 'Textos, portada y datos de contacto de tu página web')
@section('ancho', 'max-w-4xl')

@section('acciones')
    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold transition text-sm border border-gray-200">
        <i class="fas fa-external-link-alt mr-1"></i> Ver página
    </a>
@endsection

@section('contenido')
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
        @foreach(\App\Models\SitioItem::TIPOS as $tipo => $config)
            <a href="{{ route('admin.contenido.items', $tipo) }}" class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm hover:border-orange-300 transition font-semibold text-sm text-black">
                <i class="fas {{ $config['icono'] }} text-orange-500 mr-2"></i> {{ $config['nombre'] }}
            </a>
        @endforeach
    </div>

    <form method="POST" action="{{ route('admin.contenido.guardar') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        @foreach($grupos as $grupo => $campos)
            <fieldset class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6 space-y-4">
                <legend class="px-2 font-bold text-black">{{ $grupo }}</legend>

                @foreach($campos as $clave => $campo)
                    <div>
                        <label class="etiqueta" for="{{ $clave }}">{{ $campo[1] }}</label>
                        @if($campo[2] === 'parrafo')
                            <textarea id="{{ $clave }}" name="{{ $clave }}" rows="3" class="campo">{{ old($clave, $valores[$clave]) }}</textarea>
                        @elseif($campo[2] === 'imagen')
                            <img src="{{ \App\Models\Contenido::urlImagen($valores[$clave]) }}" alt="" class="h-40 rounded-lg border border-gray-200 object-cover mb-2">
                            <input type="file" id="{{ $clave }}" name="{{ $clave }}" accept="image/*" class="campo">
                            <p class="text-gray-400 text-xs mt-1">JPG, PNG o WEBP, máximo 8MB. Déjalo vacío para conservar la actual.</p>
                        @else
                            <input type="{{ $campo[2] === 'url' ? 'url' : 'text' }}" id="{{ $clave }}" name="{{ $clave }}" value="{{ old($clave, $valores[$clave]) }}" class="campo">
                        @endif
                    </div>
                @endforeach
            </fieldset>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-lg font-semibold transition text-sm shadow-sm">
                <i class="fas fa-save mr-2"></i> Guardar contenido
            </button>
        </div>
    </form>
@endsection
