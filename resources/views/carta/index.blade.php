<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Carta | Casa Blanca</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body { background: #ffffff; font-family: 'Segoe UI', Arial, sans-serif; color: #1a1a1a; }
        .campo { width: 100%; padding: 0.625rem 1rem; background: #f9fafb; border: 1px solid #d1d5db; border-radius: 0.5rem; color: #000; font-size: 0.875rem; }
        .campo:focus { border-color: #f97316; outline: none; box-shadow: 0 0 0 2px #fed7aa; }
        .etiqueta { display: block; color: #374151; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.875rem; }
    </style>
</head>
<body>
    <header class="bg-white border-b border-orange-100 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <img src="/img/logo-claro.webp" alt="Casa Blanca" class="h-12 w-auto">
            </a>
            <span class="text-orange-500 text-sm font-semibold">Nuestra carta</span>
        </div>
    </header>

    <main class="max-w-7xl mx-auto p-4 md:p-8">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-3">
                <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                @foreach(array_unique($errors->all()) as $mensaje)
                    <p><i class="fas fa-exclamation-circle text-red-500 mr-2"></i>{{ $mensaje }}</p>
                @endforeach
            </div>
        @endif

        <h1 class="text-2xl md:text-3xl font-bold text-black mb-1">Haz tu prepedido</h1>
        <p class="text-gray-500 mb-6">Elige tus platos, déjanos tu DNI y paga en caja cuando quieras.</p>

        <form method="POST" action="{{ route('carta.store') }}">
            @csrf

            <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="etiqueta" for="dni">DNI <span class="text-red-500">*</span></label>
                    <input type="text" id="dni" name="dni" value="{{ old('dni') }}" class="campo" inputmode="numeric" maxlength="8" pattern="[0-9]{8}" title="8 dígitos" required>
                </div>
                <div>
                    <label class="etiqueta" for="cliente_nombre">Nombre</label>
                    <input type="text" id="cliente_nombre" name="cliente_nombre" value="{{ old('cliente_nombre') }}" class="campo" maxlength="150">
                </div>
                <div>
                    <label class="etiqueta" for="observacion">Observación</label>
                    <input type="text" id="observacion" name="observacion" value="{{ old('observacion') }}" class="campo" maxlength="255" placeholder="Ej: sin ají">
                </div>
            </div>

            @include('pedidos._selector')
        </form>
    </main>
</body>
</html>
