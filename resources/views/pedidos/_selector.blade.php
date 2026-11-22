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
                               min="0" max="{{ min(99, $producto->disponible) }}" data-precio="{{ $producto->precio_venta }}" data-nombre="{{ $producto->nombre }}"
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
    <div class="flex gap-2">
        <button type="button" id="verSeleccion" class="border-2 border-orange-500 text-orange-600 hover:bg-orange-50 px-5 py-3 rounded-lg font-semibold transition text-sm">
            <i class="fas fa-list-ul mr-2"></i> Ver
        </button>
        <button type="submit" class="flex-1 sm:flex-none bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-lg font-semibold transition text-sm shadow-sm">
            <i class="fas {{ $iconoBoton ?? 'fa-paper-plane' }} mr-2"></i> {{ $textoBoton ?? 'Enviar Pedido' }}
        </button>
    </div>
</div>

<!-- Lo seleccionado hasta ahora -->
<div id="modalSeleccion" class="hidden fixed inset-0 z-50 bg-black/50 flex items-end sm:items-center justify-center sm:p-4" role="dialog" aria-modal="true" aria-labelledby="tituloSeleccion">
    <div class="bg-white w-full sm:max-w-lg rounded-t-2xl sm:rounded-xl shadow-xl max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 id="tituloSeleccion" class="font-bold text-black text-lg"><i class="fas fa-list-ul text-orange-500 mr-2"></i>Lo que llevas</h3>
            <button type="button" data-cerrar-seleccion class="w-9 h-9 rounded-full hover:bg-gray-100 text-gray-500 text-xl" aria-label="Cerrar">&times;</button>
        </div>
        <div class="overflow-y-auto px-5 py-2 flex-1">
            <p id="seleccionVacia" class="py-8 text-center text-gray-400">Todavía no seleccionas productos.</p>
            <ul id="listaSeleccion" class="divide-y divide-gray-100"></ul>
        </div>
        <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
            <span class="text-gray-500 text-sm"><span id="seleccionCantidad">0</span> producto(s)</span>
            <span class="text-xl font-bold text-black">S/ <span id="seleccionTotal">0.00</span></span>
        </div>
        <div class="px-5 pb-5">
            <button type="button" data-cerrar-seleccion class="w-full bg-gray-900 hover:bg-black text-white py-3 rounded-lg font-semibold text-sm">Seguir eligiendo</button>
        </div>
    </div>
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
            pintarSeleccion();
        }

        // Cambia la cantidad de un producto: lo usan las tarjetas y la ventana "Ver"
        function cambiar(campo, valor) {
            campo.value = Math.max(0, Math.min(parseInt(campo.max), valor));
            campo.closest('article').classList.toggle('border-orange-400', parseInt(campo.value) > 0);
            actualizarResumen();
        }

        cantidades.forEach(function (campo) {
            const tarjeta = campo.closest('article');
            tarjeta.querySelector('[data-sumar]').addEventListener('click', () => cambiar(campo, (parseInt(campo.value) || 0) + 1));
            tarjeta.querySelector('[data-restar]').addEventListener('click', () => cambiar(campo, (parseInt(campo.value) || 0) - 1));
            campo.addEventListener('change', () => cambiar(campo, parseInt(campo.value) || 0));
        });

        // ---------- Ventana "Ver": lo seleccionado hasta ahora ----------
        const modal = document.getElementById('modalSeleccion');
        const lista = document.getElementById('listaSeleccion');

        function pintarSeleccion() {
            let unidades = 0;
            let total = 0;
            const filas = [];
            cantidades.forEach(function (campo) {
                const cantidad = parseInt(campo.value) || 0;
                if (!cantidad) return;
                const precio = parseFloat(campo.dataset.precio);
                unidades += cantidad;
                total += cantidad * precio;

                const li = document.createElement('li');
                li.className = 'py-3 flex items-center gap-3';
                const datos = document.createElement('div');
                datos.className = 'flex-1 min-w-0';
                const nombre = document.createElement('p');
                nombre.className = 'font-semibold text-black text-sm';
                nombre.textContent = campo.dataset.nombre;
                const unitario = document.createElement('p');
                unitario.className = 'text-gray-500 text-xs';
                unitario.textContent = 'S/ ' + precio.toFixed(2) + ' c/u';
                datos.append(nombre, unitario);

                const control = document.createElement('div');
                control.className = 'flex items-center gap-1';
                const boton = (texto, clase, etiqueta, accion) => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.className = clase;
                    b.innerHTML = texto;
                    b.setAttribute('aria-label', etiqueta + ' ' + campo.dataset.nombre);
                    b.addEventListener('click', accion);
                    return b;
                };
                const numero = document.createElement('span');
                numero.className = 'w-7 text-center font-bold';
                numero.textContent = cantidad;
                control.append(
                    boton('−', 'w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 font-bold', 'Quitar uno de', () => cambiar(campo, cantidad - 1)),
                    numero,
                    boton('+', 'w-8 h-8 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-bold', 'Agregar uno de', () => cambiar(campo, cantidad + 1)),
                    boton('<i class="fas fa-trash-alt"></i>', 'ml-1 w-8 h-8 rounded-lg text-gray-400 hover:text-red-600', 'Quitar', () => cambiar(campo, 0))
                );

                const subtotal = document.createElement('span');
                subtotal.className = 'w-20 text-right font-bold text-black whitespace-nowrap';
                subtotal.textContent = 'S/ ' + (cantidad * precio).toFixed(2);

                li.append(datos, control, subtotal);
                filas.push(li);
            });
            lista.replaceChildren(...filas);
            document.getElementById('seleccionVacia').classList.toggle('hidden', filas.length > 0);
            document.getElementById('seleccionCantidad').textContent = unidades;
            document.getElementById('seleccionTotal').textContent = total.toFixed(2);
        }

        const cerrarSeleccion = () => modal.classList.add('hidden');
        document.getElementById('verSeleccion').addEventListener('click', function () {
            pintarSeleccion();
            modal.classList.remove('hidden');
        });
        modal.querySelectorAll('[data-cerrar-seleccion]').forEach(b => b.addEventListener('click', cerrarSeleccion));
        modal.addEventListener('click', e => { if (e.target === modal) cerrarSeleccion(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarSeleccion(); });

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

        cantidades.forEach(campo => campo.closest('article').classList.toggle('border-orange-400', (parseInt(campo.value) || 0) > 0));
        actualizarResumen();
    });
</script>
