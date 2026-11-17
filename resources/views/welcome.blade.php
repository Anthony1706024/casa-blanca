<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Casa Blanca | Restaurante Campestre en Poroto</title>
    <meta name="description" content="{{ $contenido['hero_texto'] }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|lobster:400" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --naranja: #f97316;
            --naranja-oscuro: #c2410c;
            --crema: #fff7ed;
            --verde: #15803d;
            --tinta: #1c1917;
            --gris: #57534e;
            --borde: #f1e7dc;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; scroll-padding-top: 90px; }
        body { font-family: 'Instrument Sans', 'Segoe UI', sans-serif; background: #fff; color: var(--tinta); line-height: 1.6; }
        img { display: block; max-width: 100%; }
        a { color: inherit; text-decoration: none; }

        .contenedor { max-width: 1180px; margin: 0 auto; padding: 0 24px; }

        /* Navegación */
        .nav { position: sticky; top: 0; z-index: 50; background: rgba(255, 255, 255, .95); backdrop-filter: blur(10px); border-bottom: 1px solid var(--borde); }
        .nav .contenedor { display: flex; align-items: center; justify-content: space-between; height: 76px; }
        .nav-logo img { height: 56px; width: auto; }
        .nav-enlaces { display: flex; align-items: center; gap: 28px; font-size: 15px; font-weight: 500; }
        .nav-enlaces a:not(.boton):hover { color: var(--naranja-oscuro); }
        .nav-boton { display: none; background: none; border: 0; font-size: 24px; color: var(--tinta); cursor: pointer; padding: 8px; }

        /* Botones */
        .boton { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 15px; transition: background .2s, color .2s, border-color .2s; }
        .boton-principal { background: var(--naranja-oscuro); color: #fff; }
        .boton-principal:hover { background: #9a3412; }
        .boton-secundario { background: #fff; color: var(--tinta); border: 1.5px solid #d6d3d1; }
        .boton-secundario:hover { border-color: var(--naranja-oscuro); color: var(--naranja-oscuro); }
        .boton-whatsapp { background: var(--verde); color: #fff; }
        .boton-whatsapp:hover { background: #166534; }
        .botones { display: flex; flex-wrap: wrap; gap: 12px; }

        /* Portada */
        .portada { background: linear-gradient(180deg, var(--crema), #fff); padding: 56px 0 72px; }
        .portada .contenedor { display: grid; grid-template-columns: 1fr 1.1fr; gap: 56px; align-items: center; }
        .portada-etiqueta { display: inline-block; color: var(--verde); font-weight: 600; font-size: 14px; letter-spacing: .04em; text-transform: uppercase; margin-bottom: 12px; }
        .portada h1 { font-family: 'Lobster', 'Instrument Sans', cursive; font-weight: 400; font-size: clamp(40px, 5.5vw, 64px); line-height: 1.1; color: var(--naranja-oscuro); margin-bottom: 18px; }
        .portada p { color: var(--gris); font-size: 18px; margin-bottom: 28px; }
        .portada-imagen { border-radius: 20px; overflow: hidden; box-shadow: 0 24px 60px rgba(120, 53, 15, .18); aspect-ratio: 4 / 3; }
        .portada-imagen img { width: 100%; height: 100%; object-fit: cover; }

        /* Secciones */
        .seccion { padding: 80px 0; }
        .seccion-crema { background: var(--crema); }
        .seccion-cabecera { max-width: 640px; margin: 0 auto 44px; text-align: center; }
        .seccion-cabecera h2 { font-family: 'Lobster', 'Instrument Sans', cursive; font-weight: 400; font-size: clamp(30px, 4vw, 42px); color: var(--naranja-oscuro); margin-bottom: 8px; }
        .seccion-cabecera p { color: var(--gris); }
        .rejilla { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

        /* Tarjetas */
        .tarjeta { background: #fff; border: 1px solid var(--borde); border-radius: 16px; overflow: hidden; }
        .tarjeta-imagen { aspect-ratio: 16 / 10; background: var(--crema); display: flex; align-items: center; justify-content: center; color: #fdba74; font-size: 40px; }
        .tarjeta-imagen img { width: 100%; height: 100%; object-fit: cover; }
        .tarjeta-foto { display: block; width: 100%; height: 100%; padding: 0; border: 0; background: none; }
        .tarjeta-cuerpo { padding: 20px; }
        .tarjeta h3 { font-size: 19px; margin-bottom: 4px; }
        .tarjeta p { color: var(--gris); font-size: 15px; }

        /* Platos */
        .platos { grid-template-columns: repeat(4, 1fr); }
        .plato-fila { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; }
        .plato-fila h3 { font-size: 16px; }
        .precio { color: var(--naranja-oscuro); font-weight: 700; white-space: nowrap; }
        .centrado { display: flex; justify-content: center; margin-top: 36px; }

        /* Galería */
        .galeria { display: grid; grid-template-columns: repeat(4, 1fr); grid-auto-rows: 200px; gap: 14px; }
        .galeria figure { position: relative; border-radius: 14px; overflow: hidden; }
        .galeria figure:first-child { grid-column: span 2; grid-row: span 2; }
        .galeria img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
        .galeria figure:hover img { transform: scale(1.04); }
        .galeria figcaption { position: absolute; left: 0; right: 0; bottom: 0; padding: 28px 14px 10px; color: #fff; font-weight: 600; font-size: 14px; background: linear-gradient(transparent, rgba(0, 0, 0, .7)); }

        /* Testimonios */
        .testimonio { padding: 24px; }
        .testimonio blockquote { color: var(--gris); margin-bottom: 14px; }
        .testimonio strong { display: block; }
        .testimonio span { color: var(--gris); font-size: 14px; }

        /* Llamado final */
        .llamado { background: var(--naranja-oscuro); color: #fff; text-align: center; padding: 64px 0; }
        .llamado h2 { font-family: 'Lobster', 'Instrument Sans', cursive; font-weight: 400; font-size: clamp(28px, 4vw, 40px); margin-bottom: 8px; }
        .llamado p { margin-bottom: 24px; }
        .llamado .botones { justify-content: center; }
        .llamado .boton-principal { background: #fff; color: var(--naranja-oscuro); }
        .llamado .boton-principal:hover { background: var(--crema); }

        /* Pie: el logo original luce sobre negro */
        .pie { background: #000; color: #d6d3d1; padding: 40px 0; font-size: 14px; }
        .pie .contenedor { display: grid; grid-template-columns: auto 1fr auto; gap: 32px; align-items: center; }
        .pie-logo { width: 150px; }
        .pie-datos p { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
        .pie-datos i { color: var(--naranja); width: 16px; }
        .pie-redes { display: flex; gap: 10px; justify-content: flex-end; }
        .pie-redes a { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: #1c1917; color: #fff; }
        .pie-redes a:hover { background: var(--naranja-oscuro); }
        .pie-legal { border-top: 1px solid #292524; margin-top: 24px; padding-top: 16px; text-align: center; color: #a8a29e; }

        @media (max-width: 960px) {
            .portada .contenedor { grid-template-columns: 1fr; gap: 32px; }
            .rejilla, .platos { grid-template-columns: repeat(2, 1fr); }
            .galeria { grid-template-columns: repeat(2, 1fr); }
            .nav-boton { display: block; }
            .nav-enlaces { display: none; position: absolute; top: 76px; left: 0; right: 0; flex-direction: column; align-items: stretch; gap: 4px; background: #fff; padding: 12px 24px 20px; border-bottom: 1px solid var(--borde); }
            .nav-enlaces.abierto { display: flex; }
            .nav-enlaces a { padding: 10px 0; }
            .pie .contenedor { grid-template-columns: 1fr; text-align: center; justify-items: center; }
            .pie-datos p { justify-content: center; }
        }

        @media (max-width: 560px) {
            .rejilla, .platos { grid-template-columns: 1fr; }
            .seccion { padding: 56px 0; }
            .botones .boton { width: 100%; }
        }
    </style>
</head>
<body>
    @php($whatsapp = $contenido['whatsapp'] ? 'https://wa.me/'.$contenido['whatsapp'] : null)

    <nav class="nav">
        <div class="contenedor">
            <a href="{{ url('/') }}" class="nav-logo"><img src="{{ asset('img/logo-claro.webp') }}" alt="Casa Blanca, Restaurante Campestre, Poroto"></a>
            <div class="nav-enlaces" id="navEnlaces">
                @if($instalaciones->isNotEmpty())<a href="#instalaciones">Instalaciones</a>@endif
                @if($destacados->isNotEmpty())<a href="#platos">Platos</a>@endif
                @if($galeria->isNotEmpty())<a href="#galeria">Galería</a>@endif
                @if($testimonios->isNotEmpty())<a href="#testimonios">Testimonios</a>@endif
                <a href="{{ route('carta.index') }}">Carta y Pedido</a>
                @auth
                    <a href="{{ url('/dashboard') }}" class="boton boton-principal"><i class="fas fa-user"></i> Mi panel</a>
                @else
                    <a href="{{ route('login') }}" class="boton boton-secundario"><i class="fas fa-sign-in-alt"></i> Iniciar sesión</a>
                @endauth
            </div>
            <button class="nav-boton" id="navBoton" aria-label="Abrir menú" aria-expanded="false"><i class="fas fa-bars"></i></button>
        </div>
    </nav>

    <header class="portada">
        <div class="contenedor">
            <div>
                @if($contenido['hero_etiqueta'])<span class="portada-etiqueta">{{ $contenido['hero_etiqueta'] }}</span>@endif
                <h1>{{ $contenido['hero_titulo'] }}</h1>
                @if($contenido['hero_texto'])<p>{{ $contenido['hero_texto'] }}</p>@endif
                <div class="botones">
                    <a href="{{ route('carta.index') }}" class="boton boton-principal"><i class="fas fa-utensils"></i> Ver carta y pedir</a>
                    @if($whatsapp)
                        <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="boton boton-whatsapp"><i class="fab fa-whatsapp"></i> Reservar mesa</a>
                    @elseif($instalaciones->isNotEmpty())
                        <a href="#instalaciones" class="boton boton-secundario">Ver instalaciones</a>
                    @endif
                </div>
            </div>
            <div class="portada-imagen">
                <img src="{{ \App\Models\Contenido::urlImagen($contenido['hero_imagen']) }}" alt="Entrada de Casa Blanca">
            </div>
        </div>
    </header>

    <main>
        @if($instalaciones->isNotEmpty())
            <section id="instalaciones" class="seccion">
                <div class="contenedor">
                    <div class="seccion-cabecera">
                        <h2>{{ $contenido['instalaciones_titulo'] }}</h2>
                        <p>{{ $contenido['instalaciones_texto'] }}</p>
                    </div>
                    <div class="rejilla">
                        @foreach($instalaciones as $instalacion)
                            <article class="tarjeta">
                                <div class="tarjeta-imagen">
                                    @if($instalacion->imagen)
                                        <img src="{{ $instalacion->imagen_url }}" alt="{{ $instalacion->titulo }}" loading="lazy">
                                    @else
                                        <i class="fas fa-leaf"></i>
                                    @endif
                                </div>
                                <div class="tarjeta-cuerpo">
                                    <h3>{{ $instalacion->titulo }}</h3>
                                    <p>{{ $instalacion->descripcion }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if($destacados->isNotEmpty())
            <section id="platos" class="seccion seccion-crema">
                <div class="contenedor">
                    <div class="seccion-cabecera">
                        <h2>{{ $contenido['platos_titulo'] }}</h2>
                        <p>{{ $contenido['platos_texto'] }}</p>
                    </div>
                    <div class="rejilla platos">
                        @foreach($destacados as $plato)
                            <article class="tarjeta">
                                <div class="tarjeta-imagen">
                                    @if($plato->imagen)
                                        <button type="button" class="tarjeta-foto" aria-label="Ver detalle de {{ $plato->nombre }}"
                                                data-visor
                                                data-visor-imagen="{{ asset('storage/'.$plato->imagen) }}"
                                                data-visor-titulo="{{ $plato->nombre }}"
                                                data-visor-precio="S/ {{ number_format($plato->precio_venta, 2) }}"
                                                @if($plato->incluye) data-visor-incluye="{{ json_encode($plato->incluye) }}" @endif
                                                data-visor-texto="{{ $plato->descripcion }}">
                                            <img src="{{ asset('storage/'.$plato->imagen) }}" alt="" loading="lazy">
                                        </button>
                                    @else
                                        <i class="fas fa-utensils"></i>
                                    @endif
                                </div>
                                <div class="tarjeta-cuerpo plato-fila">
                                    <h3>{{ $plato->nombre }}</h3>
                                    <span class="precio">S/ {{ number_format($plato->precio_venta, 2) }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="centrado">
                        <a href="{{ route('carta.index') }}" class="boton boton-principal">Ver la carta completa</a>
                    </div>
                </div>
            </section>
        @endif

        @if($galeria->isNotEmpty())
            <section id="galeria" class="seccion">
                <div class="contenedor">
                    <div class="seccion-cabecera">
                        <h2>{{ $contenido['galeria_titulo'] }}</h2>
                        <p>{{ $contenido['galeria_texto'] }}</p>
                    </div>
                    <div class="galeria">
                        @foreach($galeria as $foto)
                            <figure>
                                <img src="{{ $foto->imagen_url }}" alt="{{ $foto->titulo }}" loading="lazy">
                                <figcaption>{{ $foto->titulo }}</figcaption>
                            </figure>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if($testimonios->isNotEmpty())
            <section id="testimonios" class="seccion seccion-crema">
                <div class="contenedor">
                    <div class="seccion-cabecera">
                        <h2>{{ $contenido['testimonios_titulo'] }}</h2>
                    </div>
                    <div class="rejilla">
                        @foreach($testimonios as $testimonio)
                            <article class="tarjeta testimonio">
                                <blockquote>“{{ $testimonio->descripcion }}”</blockquote>
                                <strong>{{ $testimonio->titulo }}</strong>
                                @if($testimonio->detalle)<span>{{ $testimonio->detalle }}</span>@endif
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section class="llamado">
            <div class="contenedor">
                <h2>{{ $contenido['cta_titulo'] }}</h2>
                <p>{{ $contenido['cta_texto'] }}</p>
                <div class="botones">
                    <a href="{{ route('carta.index') }}" class="boton boton-principal"><i class="fas fa-utensils"></i> Hacer mi prepedido</a>
                    @if($whatsapp)
                        <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="boton boton-whatsapp"><i class="fab fa-whatsapp"></i> Escríbenos</a>
                    @endif
                </div>
            </div>
        </section>
    </main>

    <footer class="pie">
        <div class="contenedor">
            <img src="{{ asset('img/logo.webp') }}" alt="" class="pie-logo">
            <div class="pie-datos">
                @if($contenido['direccion'])<p><i class="fas fa-map-marker-alt"></i> {{ $contenido['direccion'] }}</p>@endif
                @if($contenido['horario'])<p><i class="fas fa-clock"></i> {{ $contenido['horario'] }}</p>@endif
                @if($contenido['telefono'])<p><i class="fas fa-phone"></i> {{ $contenido['telefono'] }}</p>@endif
            </div>
            <div class="pie-redes">
                @if($contenido['instagram'])<a href="{{ $contenido['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>@endif
                @if($contenido['facebook'])<a href="{{ $contenido['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>@endif
                @if($contenido['youtube'])<a href="{{ $contenido['youtube'] }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>@endif
                @if($whatsapp)<a href="{{ $whatsapp }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>@endif
            </div>
        </div>
        <div class="contenedor">
            <p class="pie-legal" style="grid-column: 1 / -1;">© {{ date('Y') }} Casa Blanca, Restaurante Campestre. Todos los derechos reservados. · <a href="{{ asset('creditos-fotos.txt') }}" style="text-decoration: underline;">Créditos de fotos</a></p>
        </div>
    </footer>

    @include('partials.visor-producto')

    <script>
        const navBoton = document.getElementById('navBoton');
        const navEnlaces = document.getElementById('navEnlaces');

        navBoton.addEventListener('click', function () {
            const abierto = navEnlaces.classList.toggle('abierto');
            navBoton.setAttribute('aria-expanded', abierto);
            navBoton.querySelector('i').className = abierto ? 'fas fa-times' : 'fas fa-bars';
        });
    </script>
</body>
</html>
