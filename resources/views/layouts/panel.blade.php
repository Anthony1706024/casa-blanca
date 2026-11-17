<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo') | Casa Blanca</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body { background: #ffffff; font-family: 'Segoe UI', Arial, sans-serif; color: #1a1a1a; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f0f0f0; }
        ::-webkit-scrollbar-thumb { background: #f97316; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #ea580c; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeIn 0.5s ease; }
        .campo { width: 100%; padding: 0.625rem 1rem; background: #f9fafb; border: 1px solid #d1d5db; border-radius: 0.5rem; color: #000; font-size: 0.875rem; }
        .campo:focus { border-color: #f97316; outline: none; box-shadow: 0 0 0 2px #fed7aa; }
        .etiqueta { display: block; color: #374151; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.875rem; }
    </style>
</head>
<body>

    @include($nav)

    <div class="md:ml-64 p-4 md:p-8 fade-in">
        <div class="@yield('ancho', 'max-w-7xl') mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 pl-12 md:pl-0">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-black">
                        <i class="fas @yield('icono') text-orange-500 mr-3"></i>
                        @yield('titulo')
                    </h1>
                    <p class="text-gray-500 mt-1 text-sm md:text-base">@yield('subtitulo')</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @yield('acciones')
                </div>
            </div>

            <!-- Mensajes -->
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-3 text-sm">
                    <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-3 text-sm">
                    <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                    @foreach(array_unique($errors->all()) as $mensaje)
                        <p><i class="fas fa-exclamation-circle text-red-500 mr-2"></i>{{ $mensaje }}</p>
                    @endforeach
                </div>
            @endif

            @yield('contenido')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
