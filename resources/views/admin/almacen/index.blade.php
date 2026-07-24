<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ver Almacén | Casa Blanca</title>

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
        .badge-stock-bajo { background: rgba(239, 68, 68, 0.15); color: #dc2626; }
        .badge-stock-normal { background: rgba(34, 197, 94, 0.15); color: #16a34a; }
        .producto-imagen { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb; }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    </style>
</head>
<body>

    @include('admin.nav')

    <div class="md:ml-64 p-4 md:p-8 fade-in">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-black">
                        <i class="fas fa-warehouse text-orange-500 mr-3"></i>
                        Ver Almacén
                    </h1>
                    <p class="text-gray-500 mt-1 text-sm md:text-base">Gestiona el inventario de productos</p>
                </div>
                <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                    <a href="{{ route('admin.categorias.index') }}" 
                       class="flex-1 sm:flex-none bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold transition flex items-center justify-center gap-2 text-sm border border-gray-200">
                        <i class="fas fa-tags"></i> Categorías
                    </a>
                    <a href="{{ route('admin.subcategorias.index') }}" 
                       class="flex-1 sm:flex-none bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold transition flex items-center justify-center gap-2 text-sm border border-gray-200">
                        <i class="fas fa-layer-group"></i> Subcategorías
                    </a>
                    <a href="{{ route('admin.almacen.create') }}" 
                       class="flex-1 sm:flex-none bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold transition flex items-center justify-center gap-2 text-sm shadow-sm">
                        <i class="fas fa-plus-circle"></i> Nuevo
                    </a>
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

            <!-- Buscador y Filtros -->
            <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm mb-6">
                <form method="GET" action="{{ route('admin.almacen.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                    <div>
                        <label class="block text-gray-600 text-xs md:text-sm mb-2 font-medium">Buscar</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="w-full px-4 py-2 md:py-3 bg-gray-50 border border-gray-300 rounded-lg text-black focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200 text-sm md:text-base"
                               placeholder="Buscar por código o nombre">
                    </div>
                    <div>
                        <label class="block text-gray-600 text-xs md:text-sm mb-2 font-medium">Categoría</label>
                        <select name="categoria" id="filtro_categoria" 
                                class="w-full px-4 py-2 md:py-3 bg-gray-50 border border-gray-300 rounded-lg text-black focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200 text-sm md:text-base">
                            <option value="">Todas las categorías</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ request('categoria') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-xs md:text-sm mb-2 font-medium">Subcategoría</label>
                        <select name="subcategoria" id="filtro_subcategoria"
                                class="w-full px-4 py-2 md:py-3 bg-gray-50 border border-gray-300 rounded-lg text-black focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200 text-sm md:text-base">
                            <option value="">Todas las subcategorías</option>
                            @foreach($subcategorias as $sub)
                                <option value="{{ $sub->id }}" {{ request('subcategoria') == $sub->id ? 'selected' : '' }}
                                        data-categoria="{{ $sub->categoria_id }}">
                                    {{ $sub->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 md:py-3 rounded-lg font-semibold transition text-sm md:text-base shadow-sm">
                            <i class="fas fa-search mr-2"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla de Productos -->
            <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm md:text-base">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="pb-3 text-gray-500 font-semibold text-xs md:text-sm">Imagen</th>
                                <th class="pb-3 text-gray-500 font-semibold text-xs md:text-sm">Código</th>
                                <th class="pb-3 text-gray-500 font-semibold text-xs md:text-sm">Producto</th>
                                <th class="pb-3 text-gray-500 font-semibold text-xs md:text-sm hidden sm:table-cell">Categoría</th>
                                <th class="pb-3 text-gray-500 font-semibold text-xs md:text-sm hidden lg:table-cell">Subcategoría</th>
                                <th class="pb-3 text-gray-500 font-semibold text-xs md:text-sm text-center">Stock</th>
                                <th class="pb-3 text-gray-500 font-semibold text-xs md:text-sm text-right hidden sm:table-cell">Precio</th>
                                <th class="pb-3 text-gray-500 font-semibold text-xs md:text-sm text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productos as $producto)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                <td class="py-3">
                                    <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" 
                                         class="producto-imagen w-10 h-10 md:w-12 md:h-12">
                                </td>
                                <td class="py-3">
                                    <span class="text-orange-600 font-mono text-xs md:text-sm font-medium">{{ $producto->codigo }}</span>
                                </td>
                                <td class="py-3 text-black font-medium text-xs md:text-sm">{{ $producto->nombre }}</td>
                                <td class="py-3 text-gray-600 text-xs md:text-sm hidden sm:table-cell">{{ $producto->categoria->nombre ?? 'Sin categoría' }}</td>
                                <td class="py-3 text-gray-500 text-xs md:text-sm hidden lg:table-cell">{{ $producto->subcategoria->nombre ?? '-' }}</td>
                                <td class="py-3 text-center">
                                    <span class="px-2 md:px-3 py-1 rounded-full text-xs font-semibold 
                                        {{ $producto->stock <= 5 ? 'badge-stock-bajo' : 'badge-stock-normal' }}">
                                        {{ $producto->stock }}
                                    </span>
                                    @if($producto->stock <= 5)
                                        <span class="text-red-500 text-xs ml-2 hidden md:inline font-medium">¡Bajo!</span>
                                    @endif
                                </td>
                                <td class="py-3 text-right text-black font-semibold text-xs md:text-sm hidden sm:table-cell">
                                    S/ {{ number_format($producto->precio_venta ?? 0, 2) }}
                                </td>
                                <td class="py-3 text-center">
                                    <div class="flex items-center justify-center gap-1 md:gap-2">
                                        <button onclick="openStockModal({{ $producto->id }}, '{{ $producto->nombre }}', {{ $producto->stock }})"
                                                class="text-green-600 hover:text-green-700 transition px-2 md:px-3 py-1 rounded bg-green-50 hover:bg-green-100 text-xs md:text-sm font-medium">
                                            <i class="fas fa-edit"></i>
                                            <span class="hidden md:inline ml-1">Stock</span>
                                        </button>
                                        <a href="{{ route('admin.almacen.edit', $producto->id) }}" 
                                           class="text-blue-600 hover:text-blue-700 transition px-2 md:px-3 py-1 rounded bg-blue-50 hover:bg-blue-100 text-xs md:text-sm font-medium">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.almacen.destroy', $producto->id) }}" 
                                              onsubmit="return confirm('¿Eliminar este producto?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700 transition px-2 md:px-3 py-1 rounded bg-red-50 hover:bg-red-100 text-xs md:text-sm font-medium">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-gray-400">
                                    <i class="fas fa-boxes text-4xl block mb-3 opacity-30"></i>
                                    No hay productos registrados
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 overflow-x-auto">
                    {{ $productos->links() }}
                </div>
            </div>

            <div class="mt-8 text-center text-gray-400 text-xs md:text-sm">
                © {{ date('Y') }} Casa Blanca Club Campestre. Todos los derechos reservados.
            </div>
        </div>
    </div>

    <!-- Modal para actualizar stock -->
    <div id="stockModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden p-4">
        <div class="bg-white rounded-lg p-6 md:p-8 border border-gray-200 shadow-xl max-w-md w-full">
            <h3 class="text-xl md:text-2xl font-bold text-black mb-4">
                <i class="fas fa-edit text-orange-500 mr-2"></i>
                Actualizar Stock
            </h3>
            <p class="text-gray-600 mb-4 text-sm md:text-base">Producto: <span id="productoNombre" class="text-black font-semibold"></span></p>
            
            <form id="stockForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2 text-sm md:text-base font-medium">Cantidad en stock</label>
                    <input type="number" id="stockInput" name="stock" min="0" step="1"
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-black text-lg md:text-xl font-bold focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200">
                </div>
                <div class="flex flex-col sm:flex-row justify-end gap-3">
                    <button type="button" onclick="closeStockModal()" 
                            class="w-full sm:w-auto px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition text-sm md:text-base font-medium">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="w-full sm:w-auto px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition text-sm md:text-base font-medium shadow-sm">
                        <i class="fas fa-save mr-2"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openStockModal(id, nombre, stock) {
            document.getElementById('productoNombre').textContent = nombre;
            document.getElementById('stockInput').value = stock;
            document.getElementById('stockForm').action = '/admin/almacen/' + id + '/stock';
            document.getElementById('stockModal').classList.remove('hidden');
        }

        function closeStockModal() {
            document.getElementById('stockModal').classList.add('hidden');
        }

        document.getElementById('stockModal').addEventListener('click', function(e) {
            if (e.target === this) closeStockModal();
        });
    </script>

</body>
</html>