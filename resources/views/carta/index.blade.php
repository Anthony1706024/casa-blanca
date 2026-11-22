<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Carta | Casa Blanca</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Montserrat:wght@300;400;500;600;700&display=swap">

    <style>
        /* Estilo de la carta impresa: mármol blanco, títulos manuscritos, mayúsculas espaciadas y verde de la marca */
        :root {
            --tinta: #1f1f1c;
            --gris: #6b6a64;
            --linea: #e6e4dc;
            --verde: #5c8a27;
            --verde-oscuro: #3f6418;
            --lima: #dbe86a;
            --marmol: #fbfbf8;
            --script: 'Great Vibes', 'Segoe Script', cursive;
            --texto: 'Montserrat', 'Segoe UI', Arial, sans-serif;
        }
        /* Las clases de Tailwind (flex, grid) no deben ganarle al atributo hidden */
        [hidden] { display: none !important; }
        body {
            font-family: var(--texto); color: var(--tinta);
            background-color: var(--marmol);
            background-image:
                radial-gradient(ellipse 60% 40% at 15% 10%, rgba(0,0,0,.035), transparent 70%),
                radial-gradient(ellipse 50% 30% at 85% 45%, rgba(0,0,0,.03), transparent 70%),
                radial-gradient(ellipse 70% 35% at 30% 85%, rgba(0,0,0,.03), transparent 70%);
            background-attachment: fixed;
        }
        .script { font-family: var(--script); font-weight: 400; line-height: 1; }
        .espaciado { letter-spacing: .42em; text-transform: uppercase; font-weight: 400; }
        .verde { color: var(--verde); }
        .fondo-verde { background: var(--verde); }
        .chips { scrollbar-width: none; }
        .chips::-webkit-scrollbar { display: none; }
        .chip { border: 1px solid var(--linea); color: var(--gris); background: #fff; }
        .chip:hover { border-color: var(--verde); color: var(--verde); }
        .chip-activo, .chip-activo:hover { background: var(--verde); border-color: var(--verde); color: #fff; }

        .hoja { scroll-margin-top: 120px; background: rgba(255,255,255,.72); border: 1px solid var(--linea); border-radius: 22px; overflow: hidden; }
        .hoja-foto { width: 128px; height: 128px; border-radius: 50%; object-fit: cover; border: 5px solid #fff; box-shadow: 0 8px 24px rgba(0,0,0,.12); flex-shrink: 0; }
        @media (min-width: 640px) { .hoja-foto { width: 168px; height: 168px; } }

        .fila { display: grid; grid-template-columns: 52px minmax(0, 1fr) auto; gap: 4px 12px; align-items: center; padding: 12px 0; border-top: 1px dashed var(--linea); }
        .fila:first-child { border-top: 0; }
        .fila-foto { width: 52px; height: 52px; border-radius: 50%; overflow: hidden; background: #f1f0ea; display: flex; align-items: center; justify-content: center; }
        .fila-nombre { font-size: .8rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; text-align: left; line-height: 1.35; }
        .fila-nombre:hover { color: var(--verde); }
        .fila-desc { font-size: .78rem; color: var(--gris); line-height: 1.4; margin-top: 2px; }
        .precio { font-weight: 700; font-size: .95rem; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .precio small { font-weight: 500; font-size: .7rem; color: var(--gris); margin-right: 2px; }

        .variante { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; }
        .fila.doble .variantes { grid-column: 1 / -1; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; margin-top: 6px; }
        @media (min-width: 640px) { .fila.doble .variantes { grid-column: 2 / -1; } }
        .fila.doble .variante { flex-direction: row; align-items: center; justify-content: space-between; gap: 4px; border: 1px solid var(--linea); background: #fff; border-radius: 12px; padding: 6px 6px 6px 10px; min-width: 0; }
        .etiqueta-var { display: block; font-size: .62rem; letter-spacing: .18em; text-transform: uppercase; color: var(--gris); }
        .variante.en-pedido .precio, .variante.en-pedido .etiqueta-var { color: var(--verde-oscuro); }
        .fila.doble .variante.en-pedido { border-color: var(--verde); }

        .mas { width: 36px; height: 36px; border-radius: 50%; background: var(--verde); color: #fff; font-size: 1.15rem; font-weight: 700; line-height: 1; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(63,100,24,.3); }
        .mas:hover { background: var(--verde-oscuro); }
        .stepper { display: inline-flex; align-items: center; height: 36px; border: 2px solid var(--verde); border-radius: 999px; background: #fff; }
        .stepper button { width: 30px; height: 100%; color: var(--verde-oscuro); font-weight: 700; font-size: 1.05rem; }
        .stepper span { min-width: 18px; text-align: center; font-weight: 700; font-variant-numeric: tabular-nums; }
        .mas:focus-visible, .stepper button:focus-visible, .chip:focus-visible, .fila-nombre:focus-visible { outline: 2px solid var(--verde); outline-offset: 2px; }

        .campo { width: 100%; padding: 0.7rem 1rem; background: #f9faf7; border: 1px solid #d6d6cf; border-radius: 0.6rem; color: #000; font-size: 1rem; }
        .campo:focus { border-color: var(--verde); outline: none; box-shadow: 0 0 0 2px #dfeacb; }
        .etiqueta { display: block; color: #3d3d38; margin-bottom: 0.35rem; font-weight: 600; font-size: 0.8rem; }
        .boton-verde { background: var(--verde); color: #fff; }
        .boton-verde:hover { background: var(--verde-oscuro); }

        .modal { position: fixed; inset: 0; z-index: 900; display: flex; align-items: flex-end; justify-content: center; background: rgba(0, 0, 0, .55); }
        .modal-caja { width: 100%; max-width: 560px; max-height: 92vh; display: flex; flex-direction: column; background: #fff; border-radius: 20px 20px 0 0; overflow: hidden; }
        @media (min-width: 640px) { .modal { align-items: center; padding: 16px; } .modal-caja { border-radius: 20px; } }
        .barra-carrito { padding-bottom: max(12px, env(safe-area-inset-bottom)); }
        @media (prefers-reduced-motion: no-preference) { .pulso { animation: pulso .35s ease; } }
        @keyframes pulso { 50% { transform: scale(1.2); } }
    </style>
</head>
<body class="pb-28">
    @php
        $dinero = fn ($valor) => rtrim(rtrim(number_format((float) $valor, 2), '0'), '.');
    @endphp

    <!-- Cabecera fija con el carrito y las secciones -->
    <header class="bg-white/95 backdrop-blur border-b sticky top-0 z-40" style="border-color: var(--linea)">
        <div class="max-w-3xl mx-auto px-4 py-2.5 flex items-center justify-between gap-3">
            <a href="{{ url('/') }}" class="flex items-center gap-3 flex-shrink-0" aria-label="Volver al inicio">
                <img src="/img/logo-claro.webp" alt="Casa Blanca" class="h-10 w-auto">
            </a>
            <div class="flex items-center gap-1">
                <a href="{{ asset('docs/carta-casa-blanca.pdf') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-gray-50" style="color: var(--gris)" title="Descargar la carta en PDF">
                    <i class="fas fa-file-pdf"></i><span class="hidden sm:inline">PDF</span>
                </a>
                <button type="button" id="abrirCarrito" class="relative inline-flex items-center gap-2 boton-verde text-sm font-semibold px-4 py-2.5 rounded-full shadow-sm" aria-label="Ver mi pedido">
                    <i class="fas fa-shopping-basket"></i><span class="hidden sm:inline">Mi pedido</span>
                    <span id="contadorCarrito" class="absolute -top-2 -right-2 min-w-[22px] h-[22px] px-1 rounded-full bg-black text-white text-xs font-bold flex items-center justify-center" hidden>0</span>
                </button>
            </div>
        </div>

        @if(count($secciones) > 1)
            <nav class="chips max-w-3xl mx-auto px-4 pb-2.5 flex gap-2 overflow-x-auto" id="chips" aria-label="Secciones de la carta">
                @foreach($secciones as $seccion)
                    <a href="#{{ $seccion['id'] }}" data-chip="{{ $seccion['id'] }}" class="chip flex-shrink-0 text-xs font-semibold px-3.5 py-1.5 rounded-full whitespace-nowrap transition">{{ $seccion['chip'] }}</a>
                @endforeach
            </nav>
        @endif
    </header>

    <main class="max-w-3xl mx-auto px-4">
        <!-- Portada, como la primera página de la carta impresa -->
        <section class="text-center pt-8 pb-6">
            <p class="script text-4xl sm:text-5xl">Restaurant Campestre</p>
            <h1 class="espaciado text-xl sm:text-2xl mt-2" style="letter-spacing: .55em">Casa Blanca</h1>
            @if($portada)
                <img src="{{ $portada->imagen_url }}" alt="{{ $portada->nombre }}" class="mx-auto mt-6 w-56 h-56 sm:w-72 sm:h-72 rounded-full object-cover border-8 border-white shadow-xl">
            @endif
            <p class="script text-3xl mt-5 verde">¡Para los que gustan del buen comer!</p>

            <div class="fondo-verde text-white rounded-2xl mt-6 px-5 py-3 flex flex-col sm:flex-row items-center justify-between gap-1 text-xs">
                <span><i class="fas fa-map-marker-alt mr-1"></i> <b>POROTO</b> · Naturaleza, sabor y tradición</span>
                @if($contenido['telefono'])
                    <span style="color: var(--lima)"><b>RESERVACIONES:</b> {{ $contenido['telefono'] }}</span>
                @endif
            </div>

            <div class="mt-6 bg-white rounded-2xl border px-5 py-4 text-left flex gap-4 items-start" style="border-color: var(--linea)">
                <span class="mas flex-shrink-0" aria-hidden="true">+</span>
                <p class="text-sm" style="color: var(--gris)">Toca <b class="text-black">+</b> para agregar un plato a tu pedido. Cuando termines, abre <b class="text-black">Mi pedido</b>, escribe tu DNI y tu número de mesa, y envíalo. Pagas en caja.</p>
            </div>
        </section>

        <div class="relative mb-6">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="search" id="buscarProducto" placeholder="Buscar plato o bebida..." autocomplete="off"
                   class="w-full pl-11 pr-4 py-3 bg-white border rounded-full text-black focus:outline-none" style="border-color: var(--linea)">
        </div>

        @forelse($secciones as $seccion)
            <section id="{{ $seccion['id'] }}" data-seccion class="hoja mb-6">
                <header class="relative px-5 sm:px-7 pt-6 pb-2 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        @if($seccion['script'])
                            <h2 class="script text-[2.6rem] sm:text-5xl">{{ $seccion['script'] }}</h2>
                        @endif
                        @if($seccion['titulo'])
                            <p class="espaciado {{ $seccion['script'] ? 'text-lg sm:text-xl mt-1' : 'text-lg sm:text-xl font-semibold' }}" @if(! $seccion['script']) role="heading" aria-level="2" @endif>{{ $seccion['titulo'] }}</p>
                        @endif
                        <i class="fas fa-leaf verde text-sm mt-2 block" aria-hidden="true"></i>
                    </div>
                    @if($seccion['foto'])
                        <img src="{{ $seccion['foto']->imagen_url }}" alt="" class="hoja-foto" loading="lazy">
                    @endif
                </header>

                <ul class="px-5 sm:px-7 pb-4">
                    @foreach($seccion['filas'] as $fila)
                        @php
                            $producto = $fila['producto'];
                            $doble = count($fila['variantes']) > 1;
                        @endphp
                        <li data-producto="{{ mb_strtolower(collect($fila['variantes'])->pluck('producto.nombre')->implode(' ')) }}" class="fila {{ $doble ? 'doble' : '' }}">
                            <button type="button" class="fila-foto" aria-label="Ver foto de {{ $producto->nombre }}" @include('carta._visor')>
                                @if($producto->imagen)
                                    <img src="{{ $producto->imagen_mini_url }}" alt="" class="w-full h-full object-cover" loading="lazy" width="52" height="52">
                                @else
                                    <i class="fas fa-utensils text-gray-300"></i>
                                @endif
                            </button>
                            <div class="min-w-0">
                                <button type="button" class="fila-nombre" @include('carta._visor')>{{ $producto->nombre }}</button>
                                @if($fila['descripcion'])
                                    <p class="fila-desc">{{ $fila['descripcion'] }}</p>
                                @endif
                            </div>
                            <div class="variantes">
                                @foreach($fila['variantes'] as $variante)
                                    @php $item = $variante['producto']; @endphp
                                    <div class="variante"
                                         data-id="{{ $item->id }}"
                                         data-nombre="{{ $item->nombre }}"
                                         data-precio="{{ $item->precio_venta }}"
                                         data-max="{{ min(99, $item->disponible) }}"
                                         data-imagen="{{ $item->imagen ? $item->imagen_mini_url : '' }}">
                                        <span class="{{ $variante['etiqueta'] ? '' : 'text-right' }}">
                                            @if($variante['etiqueta'])<span class="etiqueta-var">{{ $variante['etiqueta'] }}</span>@endif
                                            <span class="precio"><small>S/</small>{{ $dinero($item->precio_venta) }}</span>
                                        </span>
                                        <button type="button" data-agregar class="mas" aria-label="Agregar {{ $item->nombre }}">+</button>
                                        <span data-stepper class="stepper" hidden>
                                            <button type="button" data-menos aria-label="Quitar uno de {{ $item->nombre }}">−</button>
                                            <span data-cantidad aria-live="polite">0</span>
                                            <button type="button" data-mas aria-label="Agregar uno de {{ $item->nombre }}">+</button>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="py-16 text-center text-gray-400">No hay platos disponibles en este momento.</p>
        @endforelse

        <p id="sinResultados" class="py-12 text-center text-gray-400" hidden>No encontramos platos con ese nombre.</p>

        <!-- Cierre, como la última página de la carta impresa -->
        <section class="hoja mt-10 mb-6 px-6 py-8 text-center">
            <img src="/img/logo-claro.webp" alt="Casa Blanca" class="h-16 w-auto mx-auto">
            <div class="mt-5 space-y-1.5 text-sm" style="color: var(--gris)">
                @if($contenido['direccion'])<p><i class="fas fa-map-marker-alt verde mr-1.5"></i>{{ $contenido['direccion'] }}</p>@endif
                @if($contenido['horario'])<p><i class="far fa-clock verde mr-1.5"></i>{{ $contenido['horario'] }}</p>@endif
                @if($contenido['pagos'])<p><i class="far fa-credit-card verde mr-1.5"></i>{{ $contenido['pagos'] }}</p>@endif
            </div>
            <p class="script text-4xl mt-6">¡Gracias por elegirnos!</p>
            <a href="{{ asset('docs/carta-casa-blanca.pdf') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 mt-5 text-sm font-semibold verde hover:underline">
                <i class="fas fa-file-pdf"></i> Descargar la carta en PDF
            </a>
        </section>
    </main>

    <!-- Barra inferior: aparece cuando hay algo en el pedido -->
    <div id="barraCarrito" class="barra-carrito fixed inset-x-0 bottom-0 z-30 px-4 pt-3" hidden>
        <button type="button" id="abrirCarritoBarra" class="max-w-3xl mx-auto w-full flex items-center justify-between gap-3 text-white rounded-2xl px-5 py-3.5 shadow-xl" style="background: var(--tinta)">
            <span class="flex items-center gap-3">
                <span id="barraCantidad" class="fondo-verde text-white text-sm font-bold rounded-full min-w-[28px] h-7 px-2 flex items-center justify-center">0</span>
                <span class="font-semibold">Ver mi pedido</span>
            </span>
            <span class="font-bold">S/ <span id="barraTotal">0.00</span></span>
        </button>
    </div>

    <!-- Modal del carrito -->
    <div id="carrito" class="modal" role="dialog" aria-modal="true" aria-labelledby="carritoTitulo" hidden>
        <div class="modal-caja shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color: var(--linea)">
                <h2 id="carritoTitulo" class="script text-4xl">Tu pedido</h2>
                <button type="button" id="cerrarCarrito" class="w-10 h-10 rounded-full hover:bg-gray-100 text-gray-500 text-2xl leading-none" aria-label="Cerrar">&times;</button>
            </div>

            <form method="POST" action="{{ route('carta.store') }}" id="formPedido" class="flex-1 overflow-y-auto" novalidate>
                @csrf
                <div class="px-5 py-4">
                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                            @foreach(array_unique($errors->all()) as $mensaje)
                                <p><i class="fas fa-exclamation-circle text-red-500 mr-2"></i>{{ $mensaje }}</p>
                            @endforeach
                        </div>
                    @endif

                    <p id="carritoVacio" class="py-8 text-center text-gray-400">
                        <i class="fas fa-leaf text-3xl block mb-3" style="color: #c9d9b0"></i>
                        Todavía no agregas platos.<br>Toca <b>+</b> en la carta para agregarlos.
                    </p>
                    <ul id="lineasCarrito" class="divide-y divide-gray-100"></ul>

                    <div id="totalCarrito" class="flex items-center justify-between border-t-2 border-gray-100 mt-2 pt-3" hidden>
                        <span class="espaciado text-xs" style="color: var(--gris)">Total</span>
                        <span class="text-2xl font-bold text-black">S/ <span id="carritoTotal">0.00</span></span>
                    </div>
                </div>

                <div id="datosCliente" class="px-5 pb-5 grid grid-cols-2 gap-3" hidden>
                    <h3 class="col-span-2 espaciado text-xs font-semibold pt-1">Tus datos</h3>
                    <div>
                        <label class="etiqueta" for="dni">DNI <span class="text-red-500">*</span></label>
                        <input type="text" id="dni" name="dni" value="{{ old('dni') }}" class="campo" inputmode="numeric" maxlength="8" pattern="[0-9]{8}" autocomplete="off" required>
                    </div>
                    <div>
                        <label class="etiqueta" for="mesa">N.° de mesa <span class="text-red-500">*</span></label>
                        <input type="text" id="mesa" name="mesa" value="{{ old('mesa') }}" class="campo" maxlength="20" placeholder="Ej: 12" autocomplete="off" required>
                    </div>
                    <div class="col-span-2">
                        <label class="etiqueta" for="cliente_nombre">Nombre</label>
                        <input type="text" id="cliente_nombre" name="cliente_nombre" value="{{ old('cliente_nombre') }}" class="campo" maxlength="150" autocomplete="name">
                    </div>
                    <div class="col-span-2">
                        <label class="etiqueta" for="observacion">Observación</label>
                        <input type="text" id="observacion" name="observacion" value="{{ old('observacion') }}" class="campo" maxlength="255" placeholder="Ej: sin ají, sin cebolla">
                    </div>
                    <p id="errorCliente" class="col-span-2 text-red-600 text-sm" hidden></p>
                    <div id="itemsOcultos" hidden></div>
                    <button type="submit" id="enviarPedido" class="col-span-2 mt-1 boton-verde disabled:opacity-60 py-3.5 rounded-full font-bold shadow-sm">
                        <i class="fas fa-paper-plane mr-2"></i> Enviar pedido
                    </button>
                    <p class="col-span-2 text-center text-gray-400 text-xs">Pagas en caja con tu DNI. Los precios finales los confirma caja.</p>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmación del pedido enviado -->
    @if(session('pedido_registrado'))
        @php $registrado = session('pedido_registrado'); @endphp
        <div id="confirmacion" class="modal" role="dialog" aria-modal="true" aria-labelledby="confirmacionTitulo">
            <div class="modal-caja shadow-2xl px-6 py-8 text-center">
                <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center text-3xl mb-3" style="background: #e8f1dc; color: var(--verde)"><i class="fas fa-check"></i></div>
                <h2 id="confirmacionTitulo" class="script text-5xl">¡Pedido enviado!</h2>
                <p class="text-gray-500 mt-2">Tu pedido es el <b class="font-mono verde">{{ $registrado['numero'] }}</b></p>
                <dl class="grid grid-cols-2 gap-3 my-6 text-left">
                    <div class="rounded-xl p-4" style="background: #f1f5ea"><dt class="espaciado text-[.65rem]" style="color: var(--gris)">Mesa</dt><dd class="text-2xl font-bold text-black">{{ $registrado['mesa'] }}</dd></div>
                    <div class="rounded-xl p-4" style="background: #f1f5ea"><dt class="espaciado text-[.65rem]" style="color: var(--gris)">Total</dt><dd class="text-2xl font-bold text-black">S/ {{ $registrado['total'] }}</dd></div>
                </dl>
                <p class="text-gray-600">Acércate a caja con tu DNI <b>{{ $registrado['dni'] }}</b> para pagar.</p>
                <button type="button" id="cerrarConfirmacion" class="mt-6 w-full text-white py-3.5 rounded-full font-bold" style="background: var(--tinta)">Seguir viendo la carta</button>
            </div>
        </div>
    @endif

    @include('partials.visor-producto')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const CLAVE = 'casablanca.carrito';
            const productos = {};
            document.querySelectorAll('.variante[data-id]').forEach(function (unidad) {
                productos[unidad.dataset.id] = {
                    nombre: unidad.dataset.nombre,
                    precio: parseFloat(unidad.dataset.precio),
                    max: parseInt(unidad.dataset.max),
                    imagen: unidad.dataset.imagen,
                    unidad: unidad,
                };
            });

            const carritoModal = document.getElementById('carrito');
            const lineas = document.getElementById('lineasCarrito');
            const form = document.getElementById('formPedido');
            const errorCliente = document.getElementById('errorCliente');
            const dinero = (valor) => valor.toFixed(2);
            let carrito = {};

            // ---------- Estado ----------
            function guardar() {
                try { localStorage.setItem(CLAVE, JSON.stringify(carrito)); } catch (e) {}
            }

            function inicial() {
                const deVuelta = @json(old('items'));
                if (@json(session()->has('pedido_registrado'))) {
                    return {};
                }
                if (deVuelta) {
                    return deVuelta;
                }
                try { return JSON.parse(localStorage.getItem(CLAVE)) || {}; } catch (e) { return {}; }
            }

            function fijar(id, cantidad) {
                const producto = productos[id];
                if (!producto) return;
                cantidad = Math.max(0, Math.min(producto.max, cantidad));
                if (cantidad === 0) {
                    delete carrito[id];
                } else {
                    carrito[id] = cantidad;
                }
                guardar();
                pintar();
            }

            // Solo se conservan los productos que siguen en la carta, sin pasar lo disponible
            Object.entries(inicial()).forEach(function ([id, cantidad]) {
                const producto = productos[id];
                cantidad = parseInt(cantidad) || 0;
                if (producto && cantidad > 0) {
                    carrito[id] = Math.min(producto.max, cantidad);
                }
            });
            guardar();

            // ---------- Pintado ----------
            function pintar() {
                let unidades = 0;
                let total = 0;

                Object.entries(productos).forEach(function ([id, producto]) {
                    const cantidad = carrito[id] || 0;
                    unidades += cantidad;
                    total += cantidad * producto.precio;
                    producto.unidad.querySelector('[data-agregar]').hidden = cantidad > 0;
                    producto.unidad.querySelector('[data-stepper]').hidden = cantidad === 0;
                    producto.unidad.querySelector('[data-cantidad]').textContent = cantidad;
                    producto.unidad.classList.toggle('en-pedido', cantidad > 0);
                });

                const contador = document.getElementById('contadorCarrito');
                if (contador.textContent !== String(unidades) && unidades > 0) {
                    contador.classList.remove('pulso');
                    void contador.offsetWidth;
                    contador.classList.add('pulso');
                }
                contador.textContent = unidades;
                contador.hidden = unidades === 0;
                document.getElementById('barraCarrito').hidden = unidades === 0 || !carritoModal.hidden;
                document.getElementById('barraCantidad').textContent = unidades;
                document.getElementById('barraTotal').textContent = dinero(total);
                document.getElementById('carritoTotal').textContent = dinero(total);
                document.getElementById('carritoVacio').hidden = unidades > 0;
                document.getElementById('totalCarrito').hidden = unidades === 0;
                document.getElementById('datosCliente').hidden = unidades === 0;

                lineas.replaceChildren(...Object.entries(carrito).map(function ([id, cantidad]) {
                    const producto = productos[id];
                    const li = document.createElement('li');
                    li.className = 'py-3 flex items-center gap-3';
                    li.dataset.id = id;

                    const foto = document.createElement('span');
                    foto.className = 'w-14 h-14 rounded-full bg-gray-100 overflow-hidden flex-shrink-0 flex items-center justify-center';
                    if (producto.imagen) {
                        const img = document.createElement('img');
                        img.src = producto.imagen;
                        img.alt = '';
                        img.className = 'w-full h-full object-cover';
                        foto.append(img);
                    } else {
                        foto.innerHTML = '<i class="fas fa-utensils text-gray-300"></i>';
                    }

                    const datos = document.createElement('div');
                    datos.className = 'flex-1 min-w-0';
                    const nombre = document.createElement('p');
                    nombre.className = 'font-semibold text-black text-sm leading-snug';
                    nombre.textContent = producto.nombre;
                    const precio = document.createElement('p');
                    precio.className = 'text-gray-500 text-xs';
                    precio.textContent = 'S/ ' + dinero(producto.precio) + ' c/u';
                    const control = document.createElement('div');
                    control.className = 'mt-1.5 flex items-center gap-2';
                    control.innerHTML =
                        '<span class="stepper"><button type="button" data-menos aria-label="Quitar uno">−</button>' +
                        '<span>' + cantidad + '</span>' +
                        '<button type="button" data-mas aria-label="Agregar uno"' + (cantidad >= producto.max ? ' disabled style="opacity:.35"' : '') + '>+</button></span>' +
                        '<button type="button" data-quitar class="ml-1 text-gray-400 hover:text-red-600 text-sm px-2 py-1" aria-label="Quitar del pedido"><i class="fas fa-trash-alt"></i></button>';
                    datos.append(nombre, precio, control);

                    const subtotal = document.createElement('p');
                    subtotal.className = 'font-bold text-black whitespace-nowrap';
                    subtotal.textContent = 'S/ ' + dinero(cantidad * producto.precio);

                    li.append(foto, datos, subtotal);
                    return li;
                }));
            }

            // ---------- Botones de cantidad (carta y modal) ----------
            document.addEventListener('click', function (evento) {
                const boton = evento.target.closest('[data-agregar], [data-mas], [data-menos], [data-quitar]');
                if (!boton) return;
                const id = boton.closest('[data-id]').dataset.id;
                const actual = carrito[id] || 0;
                if (boton.matches('[data-agregar], [data-mas]')) fijar(id, actual + 1);
                if (boton.matches('[data-menos]')) fijar(id, actual - 1);
                if (boton.matches('[data-quitar]')) fijar(id, 0);
            });

            // ---------- Modal ----------
            function abrirCarrito() {
                carritoModal.hidden = false;
                document.body.style.overflow = 'hidden';
                pintar();
                document.getElementById('cerrarCarrito').focus();
            }

            function cerrarCarrito() {
                carritoModal.hidden = true;
                document.body.style.overflow = '';
                pintar();
            }

            document.getElementById('abrirCarrito').addEventListener('click', abrirCarrito);
            document.getElementById('abrirCarritoBarra').addEventListener('click', abrirCarrito);
            document.getElementById('cerrarCarrito').addEventListener('click', cerrarCarrito);
            carritoModal.addEventListener('click', function (evento) {
                if (evento.target === carritoModal) cerrarCarrito();
            });
            // En captura, para ver el visor antes de que su propio Escape lo cierre:
            // si la foto está abierta, Escape cierra solo la foto.
            document.addEventListener('keydown', function (evento) {
                const visor = document.getElementById('visor');
                if (evento.key === 'Escape' && !carritoModal.hidden && (!visor || visor.hidden)) cerrarCarrito();
            }, true);

            // ---------- Envío ----------
            form.addEventListener('submit', function (evento) {
                const dni = document.getElementById('dni');
                const mesa = document.getElementById('mesa');
                let mensaje = '';
                if (!Object.keys(carrito).length) mensaje = 'Agrega al menos un plato a tu pedido.';
                else if (!/^\d{8}$/.test(dni.value.trim())) mensaje = 'Ingresa tu DNI de 8 dígitos.';
                else if (!mesa.value.trim()) mensaje = 'Indica el número de tu mesa.';

                if (mensaje) {
                    evento.preventDefault();
                    errorCliente.textContent = mensaje;
                    errorCliente.hidden = false;
                    (mensaje.includes('DNI') ? dni : mesa).focus();
                    return;
                }

                const ocultos = document.getElementById('itemsOcultos');
                ocultos.replaceChildren(...Object.entries(carrito).map(function ([id, cantidad]) {
                    const campo = document.createElement('input');
                    campo.type = 'hidden';
                    campo.name = 'items[' + id + ']';
                    campo.value = cantidad;
                    return campo;
                }));
                const boton = document.getElementById('enviarPedido');
                boton.disabled = true;
                boton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Enviando...';
            });

            // ---------- Buscador ----------
            document.getElementById('buscarProducto').addEventListener('input', function () {
                const texto = this.value.trim().toLowerCase();
                let total = 0;
                document.querySelectorAll('[data-seccion]').forEach(function (seccion) {
                    let visibles = 0;
                    seccion.querySelectorAll('[data-producto]').forEach(function (fila) {
                        const coincide = fila.dataset.producto.includes(texto);
                        fila.hidden = !coincide;
                        visibles += coincide ? 1 : 0;
                    });
                    seccion.hidden = visibles === 0;
                    total += visibles;
                });
                document.getElementById('sinResultados').hidden = total > 0;
            });

            // ---------- Sección activa al bajar ----------
            const chips = document.getElementById('chips');
            if (chips) {
                const secciones = [...document.querySelectorAll('[data-seccion]')];
                let activa = null;
                function marcarActiva() {
                    let actual = secciones[0];
                    secciones.forEach(function (seccion) {
                        if (!seccion.hidden && seccion.getBoundingClientRect().top < 160) actual = seccion;
                    });
                    if (!actual || actual.id === activa) return;
                    activa = actual.id;
                    chips.querySelectorAll('[data-chip]').forEach(function (chip) {
                        const es = chip.dataset.chip === activa;
                        chip.classList.toggle('chip-activo', es);
                        if (es) chips.scrollTo({ left: chip.offsetLeft - 16, behavior: 'smooth' });
                    });
                }
                window.addEventListener('scroll', marcarActiva, { passive: true });
                marcarActiva();
            }

            // ---------- Confirmación ----------
            const confirmacion = document.getElementById('confirmacion');
            if (confirmacion) {
                document.body.style.overflow = 'hidden';
                document.getElementById('cerrarConfirmacion').focus();
                function cerrarConfirmacion() {
                    confirmacion.hidden = true;
                    document.body.style.overflow = '';
                }
                document.getElementById('cerrarConfirmacion').addEventListener('click', cerrarConfirmacion);
                confirmacion.addEventListener('click', function (evento) {
                    if (evento.target === confirmacion) cerrarConfirmacion();
                });
            }

            pintar();

            // Si el servidor devolvió errores, se reabre el pedido para corregirlos
            if (@json($errors->any())) abrirCarrito();
        });
    </script>
</body>
</html>
