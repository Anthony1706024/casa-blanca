<!-- ==========================================
     SELECTOR DE PRODUCTOS: lo usan el mozo y la caja (la carta pública tiene su propio carrito)
     El precio es referencial: el total real lo calcula el servidor
========================================== -->
<div class="relative mb-6">
    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
    <input type="search" id="buscarProducto" placeholder="Buscar plato o bebida..."
           class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-black text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200">
</div>

@forelse($productos->groupBy(fn ($producto) => $producto->categoria->nombre ?? 'Otros') as $categoria => $grupo)
    <section data-seccion class="mb-8">
        <h2 class="text-lg font-bold text-black mb-3 border-b-2 border-orange-500 inline-block pb-1">{{ $categoria }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach($grupo as $producto)
                <article data-producto="{{ mb_strtolower($producto->nombre) }}" class="bg-white border border-gray-200 rounded-lg p-3 flex gap-3 items-center shadow-sm">
                    <button type="button" class="flex-1 min-w-0 flex items-center gap-3 text-left rounded-lg"
                            aria-label="Ver detalle de {{ $producto->nombre }}"
                            data-visor
                            @if($producto->imagen) data-visor-imagen="{{ $producto->imagen_url }}" @endif
                            data-visor-titulo="{{ $producto->nombre }}"
                            data-visor-precio="S/ {{ number_format($producto->precio_venta, 2) }}"
                            @if($producto->incluye) data-visor-incluye="{{ json_encode($producto->incluye) }}" @endif
                            data-visor-texto="{{ $producto->descripcion }}">
                        <span class="w-14 h-14 rounded-lg bg-orange-50 flex items-center justify-center flex-shrink-0 overflow-hidden">
                            @if($producto->imagen)
                                <img src="{{ $producto->imagen_mini_url }}" alt="" class="w-full h-full object-cover" loading="lazy" width="56" height="56">
                            @else
                                <i class="fas fa-utensils text-orange-300"></i>
                            @endif
                        </span>
                        <span class="flex-1 min-w-0">
                            <span class="block font-semibold text-black text-sm">{{ $producto->nombre }}</span>
                            <span class="block text-orange-600 font-bold text-sm">S/ {{ number_format($producto->precio_venta, 2) }}</span>
                        </span>
                    </button>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button type="button" data-restar aria-label="Quitar uno" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold">−</button>
                        <input type="number" name="items[{{ $producto->id }}]" value="{{ old('items.' . $producto->id, 0) }}"
                               min="0" max="{{ min(99, $producto->disponible) }}" data-precio="{{ $producto->precio_venta }}"
                               aria-label="Cantidad de {{ $producto->nombre }}"
                               class="w-12 h-8 text-center bg-gray-50 border border-gray-300 rounded-lg text-sm [appearance:textfield]">
                        <button type="button" data-sumar aria-label="Agregar uno" class="w-8 h-8 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-bold">+</button>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@empty
    <p class="py-12 text-center text-gray-400">No hay productos disponibles en este momento.</p>
@endforelse

<!-- Resumen fijo -->
<div class="sticky bottom-0 bg-white border border-orange-200 rounded-lg shadow-lg p-4 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
    <div>
        <span class="text-gray-500 text-sm"><span id="resumenCantidad">0</span> producto(s)</span>
        <p class="text-2xl font-bold text-black">S/ <span id="resumenTotal">0.00</span></p>
    </div>
    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-lg font-semibold transition text-sm shadow-sm">
        <i class="fas {{ $iconoBoton ?? 'fa-paper-plane' }} mr-2"></i> {{ $textoBoton ?? 'Enviar Pedido' }}
    </button>
</div>

@include('partials.visor-producto')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cantidades = document.querySelectorAll('input[name^="items["]');

        function actualizarResumen() {
            let unidades = 0;
            let total = 0;
            cantidades.forEach(function (campo) {
                const cantidad = parseInt(campo.value) || 0;
                unidades += cantidad;
                total += cantidad * parseFloat(campo.dataset.precio);
            });
            document.getElementById('resumenCantidad').textContent = unidades;
            document.getElementById('resumenTotal').textContent = total.toFixed(2);
        }

        cantidades.forEach(function (campo) {
            function cambiar(valor) {
                campo.value = Math.max(0, Math.min(parseInt(campo.max), valor));
                actualizarResumen();
            }
            const tarjeta = campo.closest('article');
            tarjeta.querySelector('[data-sumar]').addEventListener('click', () => cambiar((parseInt(campo.value) || 0) + 1));
            tarjeta.querySelector('[data-restar]').addEventListener('click', () => cambiar((parseInt(campo.value) || 0) - 1));
            campo.addEventListener('change', () => cambiar(parseInt(campo.value) || 0));
        });

        document.getElementById('buscarProducto').addEventListener('input', function () {
            const texto = this.value.trim().toLowerCase();
            document.querySelectorAll('[data-seccion]').forEach(function (seccion) {
                let visibles = 0;
                seccion.querySelectorAll('[data-producto]').forEach(function (tarjeta) {
                    const coincide = tarjeta.dataset.producto.includes(texto);
                    tarjeta.classList.toggle('hidden', !coincide);
                    visibles += coincide ? 1 : 0;
                });
                seccion.classList.toggle('hidden', visibles === 0);
            });
        });

        actualizarResumen();
    });
</script>
