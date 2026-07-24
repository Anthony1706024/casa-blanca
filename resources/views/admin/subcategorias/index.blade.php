<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Subcategorías | Casa Blanca</title>

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
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-3px); box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    @include('admin.nav')

    <div class="md:ml-64 p-4 md:p-8 fade-in">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-black">
                        <i class="fas fa-layer-group text-orange-500 mr-3"></i>
                        Subcategorías
                    </h1>
                    <p class="text-gray-500 mt-1 text-sm md:text-base">Gestiona las subcategorías de productos</p>
                </div>
                <a href="{{ route('admin.almacen.index') }}" 
                   class="text-gray-600 hover:text-black transition flex items-center gap-2 text-sm md:text-base">
                    <i class="fas fa-arrow-left"></i> Volver al Almacén
                </a>
            </div>

            <!-- Mensajes -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-3 text-sm">
                    <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-3 text-sm">
                    <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
                </div>
            @endif

            <!-- Formulario para nueva subcategoría -->
            <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6">
                <h3 class="text-base md:text-lg font-bold text-black mb-4">
                    <i class="fas fa-plus-circle text-orange-500 mr-2"></i>
                    Nueva Subcategoría
                </h3>
                <form method="POST" action="{{ route('admin.subcategorias.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                    @csrf
                    <div>
                        <input type="text" name="nombre" placeholder="Nombre de la subcategoría" required
                               class="w-full px-4 py-2 md:py-3 bg-gray-50 border border-gray-300 rounded-lg text-black focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200 text-sm md:text-base">
                    </div>
                    <div>
                        <input type="text" name="descripcion" placeholder="Descripción (opcional)"
                               class="w-full px-4 py-2 md:py-3 bg-gray-50 border border-gray-300 rounded-lg text-black focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200 text-sm md:text-base">
                    </div>
                    <div>
                        <select name="categoria_id" required
                                class="w-full px-4 py-2 md:py-3 bg-gray-50 border border-gray-300 rounded-lg text-black focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200 text-sm md:text-base">
                            <option value="">Seleccionar categoría</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 md:py-3 rounded-lg font-semibold transition text-sm md:text-base">
                            <i class="fas fa-save mr-2"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Lista de subcategorías -->
            <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                    @forelse($subcategorias as $subcategoria)
                    <div class="bg-gray-50 rounded-lg p-3 md:p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border border-gray-200 card-hover">
                        <div>
                            <h4 class="text-black font-semibold text-sm md:text-base">{{ $subcategoria->nombre }}</h4>
                            <p class="text-gray-500 text-xs md:text-sm">{{ $subcategoria->descripcion ?? 'Sin descripción' }}</p>
                            <p class="text-gray-400 text-xs mt-1">
                                <i class="fas fa-tag mr-1"></i> {{ $subcategoria->categoria->nombre ?? 'Sin categoría' }}
                            </p>
                            <p class="text-gray-400 text-xs mt-1">
                                <i class="fas fa-box mr-1"></i> {{ $subcategoria->productos_count ?? 0 }} productos
                            </p>
                        </div>
                        <form method="POST" action="{{ route('admin.subcategorias.destroy', $subcategoria->id) }}"
                              onsubmit="return confirm('¿Eliminar esta subcategoría?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 transition px-3 py-1 rounded bg-red-50 hover:bg-red-100 text-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                    @empty
                    <div class="col-span-2 text-center py-8 text-gray-400">
                        <i class="fas fa-layer-group text-4xl block mb-3 opacity-30"></i>
                        No hay subcategorías registradas
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</body>
</html>