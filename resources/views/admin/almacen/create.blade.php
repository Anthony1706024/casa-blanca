<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrar Producto | Casa Blanca</title>

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
        .input-error { border-color: #ef4444 !important; }
        .preview-image { max-width: 150px; max-height: 150px; border-radius: 8px; border: 2px solid #f97316; }
    </style>
</head>
<body>

    @include('admin.nav')

    <div class="md:ml-64 p-4 md:p-8 fade-in">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-black">
                        <i class="fas fa-box text-orange-500 mr-3"></i>
                        Registrar Producto
                    </h1>
                    <p class="text-gray-500 mt-1 text-sm md:text-base">Ingresa los datos del nuevo producto</p>
                </div>
                <a href="{{ route('admin.almacen.index') }}" 
                   class="text-gray-600 hover:text-black transition flex items-center gap-2 text-sm md:text-base">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>

            <!-- Formulario -->
            <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200 shadow-sm">
                <form method="POST" action="{{ route('admin.almacen.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                        <!-- Código -->
                        <div>
                            <label class="block text-gray-700 mb-2 font-semibold text-sm md:text-base">
                                Código <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="codigo" value="{{ old('codigo', $codigoSugerido) }}" 
                                   class="w-full px-4 py-2 md:py-3 bg-gray-50 border border-gray-300 rounded-lg text-black focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200 {{ $errors->has('codigo') ? 'input-error' : '' }} text-sm md:text-base"
                                   placeholder="Ej: PRO-000001" required>
                            @error('codigo')
                                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nombre -->
                        <div>
                            <label class="block text-gray-700 mb-2 font-semibold text-sm md:text-base">
                                Nombre del producto <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nombre" value="{{ old('nombre') }}" 
                                   class="w-full px-4 py-2 md:py-3 bg-gray-50 border border-gray-300 rounded-lg text-black focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200 {{ $errors->has('nombre') ? 'input-error' : '' }} text-sm md:text-base"
                                   placeholder="Ej: Arroz Extra" required>
                            @error('nombre')
                                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Categoría -->
                        <div>
                            <label class="block text-gray-700 mb-2 font-semibold text-sm md:text-base">
                                Categoría <span class="text-red-500">*</span>
                            </label>
                            <select name="categoria_id" id="categoriaSelect" 
                                    class="w-full px-4 py-2 md:py-3 bg-gray-50 border border-gray-300 rounded-lg text-black focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200 {{ $errors->has('categoria_id') ? 'input-error' : '' }} text-sm md:text-base" required>
                                <option value="">Seleccionar categoría</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('categoria_id')
                                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Subcategoría -->
                        <div>
                            <label class="block text-gray-700 mb-2 font-semibold text-sm md:text-base">Subcategoría</label>
                            <select name="subcategoria_id" id="subcategoriaSelect"
                                    class="w-full px-4 py-2 md:py-3 bg-gray-50 border border-gray-300 rounded-lg text-black focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200 text-sm md:text-base">
                                <option value="">Sin subcategoría</option>
                                @foreach($subcategorias as $sub)
                                    <option value="{{ $sub->id }}" {{ old('subcategoria_id') == $sub->id ? 'selected' : '' }}
                                            data-categoria="{{ $sub->categoria_id }}">
                                        {{ $sub->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subcategoria_id')
                                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        @include('admin.almacen._tipo')

                        <!-- Stock -->
                        <div id="campoStock">
                            <label class="block text-gray-700 mb-2 font-semibold text-sm md:text-base">
                                Stock inicial <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="stock" value="{{ old('stock', 0) }}" min="0" step="1"
                                   class="w-full px-4 py-2 md:py-3 bg-gray-50 border border-gray-300 rounded-lg text-black focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200 {{ $errors->has('stock') ? 'input-error' : '' }} text-sm md:text-base"
                                   required>
                            @error('stock')
                                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Precio Compra -->
                        <div>
                            <label class="block text-gray-700 mb-2 font-semibold text-sm md:text-base">Precio de compra (S/)</label>
                            <input type="number" name="precio_compra" value="{{ old('precio_compra') }}" step="0.01" min="0"
                                   class="w-full px-4 py-2 md:py-3 bg-gray-50 border border-gray-300 rounded-lg text-black focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200 {{ $errors->has('precio_compra') ? 'input-error' : '' }} text-sm md:text-base"
                                   placeholder="0.00">
                            @error('precio_compra')
                                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Precio Venta -->
                        <div>
                            <label class="block text-gray-700 mb-2 font-semibold text-sm md:text-base">Precio de venta (S/)</label>
                            <input type="number" name="precio_venta" value="{{ old('precio_venta') }}" step="0.01" min="0"
                                   class="w-full px-4 py-2 md:py-3 bg-gray-50 border border-gray-300 rounded-lg text-black focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200 {{ $errors->has('precio_venta') ? 'input-error' : '' }} text-sm md:text-base"
                                   placeholder="0.00">
                            @error('precio_venta')
                                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Imagen -->
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 mb-2 font-semibold text-sm md:text-base">Imagen del producto</label>
                            <input type="file" name="imagen" id="imagen" accept="image/*"
                                   class="w-full px-4 py-2 md:py-3 bg-gray-50 border border-gray-300 rounded-lg text-black focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200 text-sm md:text-base file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-orange-500 file:text-white hover:file:bg-orange-600">
                            <p class="text-gray-400 text-xs mt-1">
                                <i class="fas fa-info-circle mr-1"></i> Formatos: JPG, PNG, GIF, WEBP | Máx: 8MB | Se convierte a WebP automáticamente
                            </p>
                            <div id="preview" class="mt-3 hidden">
                                <p class="text-gray-600 text-sm mb-2">Vista previa:</p>
                                <img id="previewImage" src="#" alt="Vista previa" class="preview-image">
                            </div>
                            @error('imagen')
                                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 mb-2 font-semibold text-sm md:text-base">Qué trae (se ve al hacer clic en la carta)</label>
                            <textarea name="descripcion" rows="3" 
                                      class="w-full px-4 py-2 md:py-3 bg-gray-50 border border-gray-300 rounded-lg text-black focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200 {{ $errors->has('descripcion') ? 'input-error' : '' }} text-sm md:text-base"
                                      placeholder="Ej: Viene con papa y menestra, o Botella de 500 ml">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-3 md:gap-4 mt-8">
                        <a href="{{ route('admin.almacen.index') }}" 
                           class="w-full sm:w-auto px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition text-center text-sm md:text-base border border-gray-200">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto px-8 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-semibold transition flex items-center justify-center gap-2 text-sm md:text-base shadow-sm">
                            <i class="fas fa-save"></i> Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Previsualización de imagen
        document.getElementById('imagen').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImage').src = e.target.result;
                    document.getElementById('preview').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        // Filtro de subcategorías por categoría
        document.getElementById('categoriaSelect').addEventListener('change', function() {
            const categoriaId = this.value;
            const subcategoriaSelect = document.getElementById('subcategoriaSelect');
            const options = subcategoriaSelect.querySelectorAll('option');
            
            options.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                    return;
                }
                const optionCategoria = option.getAttribute('data-categoria');
                if (categoriaId === '' || optionCategoria === categoriaId) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
            
            subcategoriaSelect.value = '';
        });
    </script>

</body>
</html>