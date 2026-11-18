<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Casa Blanca | Restaurante Campestre en Poroto</title>
    <meta name="description" content="{{ $contenido['hero_texto'] }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|playfair-display:400,400i,500,500i,600,600i" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --crema: #faf6ef;
            --crema-2: #f3eadc;
            --papel: #fffdf9;
            --tinta: #26211c;
            --gris: #6b6258;
            --linea: #e9dfd0;
            --terracota: #c2410c;
            --terracota-osc: #9a3412;
            --verde: #4d6b3c;
            --verde-osc: #3a5230;
            --dorado: #e0b25c;
            --whatsapp: #25d366;
            --serif: 'Playfair Display', Georgia, 'Times New Roman', serif;
            --sans: 'Instrument Sans', 'Segoe UI', sans-serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; scroll-padding-top: 96px; }
        body { font-family: var(--sans); background: var(--papel); color: var(--tinta); line-height: 1.65; overflow-x: hidden; }
        img { display: block; max-width: 100%; }
        a { color: inherit; text-decoration: none; }
        em { font-style: italic; }

        .contenedor { max-width: 1180px; margin: 0 auto; padding: 0 24px; }

        /* Textos comunes */
        .ceja { display: inline-block; font-size: 12px; font-weight: 600; letter-spacing: .22em; text-transform: uppercase; color: var(--terracota); padding-bottom: 6px; border-bottom: 1px solid currentColor; margin-bottom: 18px; }
        .titulo { font-family: var(--serif); font-weight: 400; font-size: clamp(32px, 4.6vw, 52px); line-height: 1.12; color: var(--tinta); }
        .titulo em { color: var(--verde); }
        .adorno { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 22px 0; color: var(--dorado); }
        .adorno::before, .adorno::after { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .adorno span { width: 56px; height: 1px; background: currentColor; }
        .adorno-izq { justify-content: flex-start; }
        .bajada { font-family: var(--serif); font-style: italic; font-size: 18px; color: var(--gris); }
        .cabecera { max-width: 720px; margin: 0 auto 56px; text-align: center; }

        /* Botones */
        .boton { display: inline-flex; align-items: center; justify-content: center; gap: 9px; padding: 13px 26px; border-radius: 999px; font-weight: 600; font-size: 13px; letter-spacing: .12em; text-transform: uppercase; border: 1.5px solid transparent; transition: background .2s, color .2s, border-color .2s, transform .2s; white-space: nowrap; }
        .boton:hover { transform: translateY(-1px); }
        .boton-terracota { background: var(--terracota); color: #fff; }
        .boton-terracota:hover { background: var(--terracota-osc); }
        .boton-verde { background: var(--verde); color: #fff; }
        .boton-verde:hover { background: var(--verde-osc); }
        .boton-borde { border-color: var(--tinta); color: var(--tinta); }
        .boton-borde:hover { background: var(--tinta); color: #fff; }
        .boton-claro { border-color: rgba(255, 255, 255, .75); color: #fff; }
        .boton-claro:hover { background: #fff; color: var(--tinta); }
        .boton-whatsapp { background: var(--whatsapp); color: #fff; }
        .boton-whatsapp:hover { background: #1ebe5b; }
        .botones { display: flex; flex-wrap: wrap; gap: 12px; }

        /* Navegación flotante */
        .nav { position: fixed; top: 16px; left: 0; right: 0; z-index: 60; padding: 0 16px; }
        .nav-caja { max-width: 1240px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 8px 10px 8px 22px; border-radius: 999px; background: rgba(255, 253, 249, .9); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); box-shadow: 0 10px 30px rgba(38, 33, 28, .12); border: 1px solid rgba(255, 255, 255, .7); }
        .nav-logo img { height: 46px; width: auto; }
        .nav-enlaces { display: flex; align-items: center; gap: 24px; font-size: 12px; font-weight: 600; letter-spacing: .14em; text-transform: uppercase; }
        .nav-enlaces a:not(.boton):hover { color: var(--terracota); }
        .nav-acciones { display: flex; align-items: center; gap: 8px; }
        .nav-acciones .boton { padding: 11px 20px; font-size: 12px; }
        .nav-personal { font-size: 12px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--gris); padding: 10px 12px; border-radius: 999px; }
        .nav-personal:hover { color: var(--terracota); }
        .nav-boton { display: none; background: none; border: 0; font-size: 22px; color: var(--tinta); cursor: pointer; width: 44px; height: 44px; border-radius: 50%; }

        /* Portada */
        .portada { position: relative; min-height: min(100vh, 920px); min-height: min(100svh, 920px); display: flex; align-items: center; color: #fff; padding: 140px 0 90px; background: #2b2620 center / cover no-repeat; }
        .portada::before { content: ''; position: absolute; inset: 0; background: linear-gradient(100deg, rgba(20, 16, 12, .78) 0%, rgba(20, 16, 12, .45) 55%, rgba(20, 16, 12, .25) 100%); }
        .portada::after { content: ''; position: absolute; inset: 14px; border: 1px solid rgba(255, 255, 255, .22); border-radius: 22px; pointer-events: none; }
        .portada .contenedor { position: relative; display: grid; grid-template-columns: 1.25fr .85fr; gap: 56px; align-items: center; width: 100%; }
        .portada .ceja { color: var(--dorado); border-color: rgba(224, 178, 92, .55); }
        .portada h1 { font-family: var(--serif); font-weight: 400; font-size: clamp(40px, 5.6vw, 68px); line-height: 1.08; margin-bottom: 22px; }
        .portada h1 em { color: var(--dorado); }
        .portada-texto { font-family: var(--serif); font-style: italic; font-size: 19px; max-width: 540px; color: rgba(255, 255, 255, .9); margin-bottom: 34px; }
        .portada .botones { flex-wrap: nowrap; }
        .portada .boton { padding: 13px 22px; }

        .vidrio { background: rgba(255, 255, 255, .12); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, .28); border-radius: 22px; overflow: hidden; }
        .vidrio-cabeza { padding: 22px 24px 18px; }
        .vidrio-ceja { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 600; letter-spacing: .2em; text-transform: uppercase; color: var(--dorado); margin-bottom: 8px; }
        .vidrio-ceja::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--dorado); box-shadow: 0 0 0 3px rgba(224, 178, 92, .3); }
        .vidrio h2 { font-family: var(--serif); font-weight: 400; font-size: 25px; line-height: 1.2; margin-bottom: 6px; }
        .vidrio p { font-size: 14px; color: rgba(255, 255, 255, .85); }
        .vidrio-fila { display: grid; grid-template-columns: 96px 1fr; gap: 12px; padding: 14px 24px; border-top: 1px solid rgba(255, 255, 255, .2); font-size: 13px; }
        .vidrio-fila strong { font-size: 11px; letter-spacing: .18em; text-transform: uppercase; }
        .vidrio-fila span { color: rgba(255, 255, 255, .85); }

        .bajar { position: absolute; left: 50%; bottom: 34px; transform: translateX(-50%); display: flex; flex-direction: column; align-items: center; gap: 8px; font-size: 11px; letter-spacing: .25em; color: rgba(255, 255, 255, .8); }
        .bajar::after { content: ''; width: 1px; height: 34px; background: rgba(255, 255, 255, .6); }

        /* Secciones */
        .seccion { padding: 104px 0; }
        .seccion-crema { background: var(--crema); }
        .seccion-crema-2 { background: var(--crema-2); }

        /* Tres experiencias */
        .experiencias { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; }
        .experiencia { position: relative; aspect-ratio: 3 / 4; border-radius: 26px; overflow: hidden; color: #fff; display: flex; flex-direction: column; justify-content: flex-end; padding: 30px; box-shadow: 0 24px 50px rgba(38, 33, 28, .18); }
        .experiencia img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .8s; }
        .experiencia::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0, 0, 0, .15) 0%, rgba(0, 0, 0, .1) 35%, rgba(0, 0, 0, .78) 100%); z-index: 1; }
        .experiencia > *:not(img) { position: relative; z-index: 2; }
        .experiencia:hover img { transform: scale(1.06); }
        .experiencia small { position: absolute !important; top: 28px; left: 30px; font-size: 11px; font-weight: 600; letter-spacing: .22em; text-transform: uppercase; color: var(--dorado); }
        .experiencia h3 { font-family: var(--serif); font-weight: 400; font-size: 30px; line-height: 1.15; margin-bottom: 12px; }
        .experiencia p { font-size: 14px; color: rgba(255, 255, 255, .88); margin-bottom: 16px; }
        .experiencia span { font-size: 12px; font-weight: 600; letter-spacing: .16em; text-transform: uppercase; }

        /* Historia */
        .historia { display: grid; grid-template-columns: 1fr 1.05fr; gap: 72px; align-items: center; }
        .historia-fotos { position: relative; padding: 0 60px 70px 0; }
        .historia-fotos .grande { width: 78%; aspect-ratio: 4 / 5; object-fit: cover; border-radius: 22px; box-shadow: 0 24px 50px rgba(38, 33, 28, .16); }
        .historia-fotos .chica { position: absolute; right: 0; bottom: 0; width: 52%; aspect-ratio: 4 / 5; object-fit: cover; border-radius: 18px; border: 8px solid var(--crema); box-shadow: 0 24px 50px rgba(38, 33, 28, .22); }
        .historia-texto p { color: var(--gris); margin-bottom: 14px; }
        .cifras { display: grid; grid-template-columns: repeat(3, 1fr); margin-top: 34px; border-top: 1px solid var(--linea); padding-top: 26px; text-align: center; }
        .cifras div + div { border-left: 1px solid var(--linea); }
        .cifras strong { display: block; font-family: var(--serif); font-weight: 400; font-size: 36px; color: var(--terracota); line-height: 1.1; }
        .cifras span { font-size: 11px; font-weight: 600; letter-spacing: .2em; text-transform: uppercase; color: var(--gris); }

        /* Restaurante */
        .bloque { display: grid; grid-template-columns: 1.05fr 1fr; gap: 64px; align-items: center; }
        .bloque-invertido .bloque-foto { order: 2; }
        .bloque-foto img { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; border-radius: 22px; box-shadow: 0 24px 50px rgba(38, 33, 28, .16); }
        .bloque h3 { font-family: var(--serif); font-weight: 400; font-size: 30px; line-height: 1.2; margin-bottom: 14px; }
        .bloque p { color: var(--gris); margin-bottom: 18px; }
        .lista { list-style: none; margin-bottom: 30px; }
        .lista li { display: flex; gap: 14px; padding: 8px 0; font-size: 15px; color: var(--tinta); }
        .lista li::before { content: ''; flex: 0 0 18px; height: 1px; background: var(--terracota); margin-top: 13px; }

        .platos-cabecera { display: flex; align-items: end; justify-content: space-between; gap: 20px; margin: 96px 0 28px; }
        .platos-cabecera h3 { font-family: var(--serif); font-weight: 400; font-size: 30px; }
        .platos-cabecera h3 em { color: var(--verde); }
        .flechas { display: flex; gap: 10px; }
        .flechas button { width: 46px; height: 46px; border-radius: 50%; border: 1.5px solid var(--tinta); background: transparent; color: var(--tinta); cursor: pointer; transition: background .2s, color .2s; }
        .flechas button:hover { background: var(--tinta); color: #fff; }
        .carrusel { display: grid; grid-auto-flow: column; grid-auto-columns: calc((100% - 3 * 18px) / 4); gap: 18px; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; padding-bottom: 6px; }
        .carrusel::-webkit-scrollbar { display: none; }
        .plato { position: relative; scroll-snap-align: start; aspect-ratio: 4 / 5; border-radius: 20px; overflow: hidden; background: var(--crema-2); border: 0; padding: 0; cursor: pointer; text-align: left; font: inherit; color: #fff; }
        .plato img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s; }
        .plato:hover img { transform: scale(1.05); }
        .plato-info { position: absolute; left: 0; right: 0; bottom: 0; padding: 40px 18px 16px; background: linear-gradient(transparent, rgba(0, 0, 0, .78)); }
        .plato-info strong { display: block; font-family: var(--serif); font-style: italic; font-weight: 400; font-size: 19px; line-height: 1.2; }
        .plato-info span { font-size: 13px; font-weight: 600; color: var(--dorado); }
        .plato-sin-foto { display: flex; align-items: center; justify-content: center; height: 100%; font-size: 40px; color: #d6c4ad; }

        /* Atractivos */
        .fotos-atractivos { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .foto-atractivo { position: relative; aspect-ratio: 4 / 5; border-radius: 20px; overflow: hidden; color: #fff; }
        .foto-atractivo img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s; }
        .foto-atractivo:hover img { transform: scale(1.05); }
        .foto-atractivo div { position: absolute; left: 0; right: 0; bottom: 0; padding: 44px 20px 18px; background: linear-gradient(transparent, rgba(0, 0, 0, .8)); }
        .foto-atractivo h3 { font-family: var(--serif); font-weight: 400; font-size: 22px; }
        .foto-atractivo p { font-size: 13px; color: rgba(255, 255, 255, .85); }
        .iconos { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .icono-tarjeta { background: #fff; border: 1px solid var(--linea); border-radius: 20px; padding: 26px; transition: border-color .2s, box-shadow .2s; }
        .icono-tarjeta:hover { border-color: #d9c3a5; box-shadow: 0 16px 34px rgba(38, 33, 28, .08); }
        .icono-circulo { width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: var(--crema); color: var(--verde); font-size: 20px; margin-bottom: 16px; }
        .icono-tarjeta h3 { font-family: var(--serif); font-weight: 500; font-size: 20px; margin-bottom: 6px; }
        .icono-tarjeta p { font-size: 14px; color: var(--gris); }
        .icono-tarjeta small { display: block; font-size: 11px; font-weight: 600; letter-spacing: .18em; text-transform: uppercase; color: var(--terracota); margin-bottom: 6px; }

        /* Eventos */
        .banner { position: relative; border-radius: 26px; overflow: hidden; min-height: 440px; display: flex; align-items: flex-end; color: #fff; box-shadow: 0 30px 60px rgba(38, 33, 28, .2); margin-bottom: 28px; }
        .banner img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .banner::before { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(0, 0, 0, .72), rgba(0, 0, 0, .15)); z-index: 1; }
        .banner-texto { position: relative; z-index: 2; padding: 44px; max-width: 600px; }
        .banner-texto h3 { font-family: var(--serif); font-weight: 400; font-size: clamp(30px, 4vw, 44px); line-height: 1.12; margin-bottom: 14px; }
        .banner-texto h3 em { color: var(--dorado); }
        .banner-texto p { font-family: var(--serif); font-style: italic; color: rgba(255, 255, 255, .9); margin-bottom: 24px; }
        .etiquetas { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
        .etiqueta { display: inline-flex; align-items: center; gap: 10px; background: #fff; border: 1px solid var(--linea); border-radius: 999px; padding: 12px 20px; font-weight: 500; font-size: 15px; }
        .etiqueta i { color: var(--terracota); }

        /* Galería */
        .galeria { display: grid; grid-template-columns: repeat(4, 1fr); grid-auto-rows: 210px; gap: 14px; }
        .galeria figure { position: relative; border-radius: 18px; overflow: hidden; }
        .galeria figure:nth-child(6n + 1) { grid-row: span 2; }
        .galeria figure:nth-child(6n + 3) { grid-column: span 2; }
        .galeria figure:last-child:nth-child(6n + 5) { grid-column: span 2; }
        .galeria img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s; }
        .galeria figure:hover img { transform: scale(1.05); }
        .galeria figcaption { position: absolute; left: 0; right: 0; bottom: 0; padding: 30px 16px 12px; color: #fff; font-family: var(--serif); font-style: italic; font-size: 16px; background: linear-gradient(transparent, rgba(0, 0, 0, .7)); }

        /* Testimonios */
        .testimonios { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 22px; }
        .testimonio { background: #fff; border: 1px solid var(--linea); border-radius: 22px; padding: 32px; display: flex; flex-direction: column; }
        .estrellas { color: var(--dorado); font-size: 14px; letter-spacing: 3px; margin-bottom: 16px; }
        .testimonio blockquote { font-family: var(--serif); font-style: italic; font-size: 18px; line-height: 1.55; color: var(--tinta); margin-bottom: 22px; flex: 1; }
        .testimonio strong { display: block; font-size: 14px; letter-spacing: .12em; text-transform: uppercase; }
        .testimonio span { font-size: 13px; color: var(--gris); }

        /* Siguiente paso */
        .pasos { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; }
        .paso { background: #fff; border: 1px solid var(--linea); border-radius: 22px; padding: 34px 30px; display: flex; flex-direction: column; align-items: flex-start; }
        .paso .icono-circulo { background: var(--crema-2); color: var(--terracota); }
        .paso h3 { font-family: var(--serif); font-weight: 400; font-size: 25px; margin-bottom: 8px; }
        .paso p { color: var(--gris); font-size: 15px; margin-bottom: 24px; flex: 1; }

        /* Contacto */
        .contacto { display: grid; grid-template-columns: .9fr 1.1fr; gap: 56px; align-items: stretch; }
        .datos { list-style: none; margin: 8px 0 28px; }
        .datos li { display: flex; gap: 16px; padding: 16px 0; border-bottom: 1px solid var(--linea); }
        .datos i { width: 20px; color: var(--terracota); margin-top: 4px; text-align: center; }
        .datos small { display: block; font-size: 11px; font-weight: 600; letter-spacing: .18em; text-transform: uppercase; color: var(--gris); }
        .datos a:hover { color: var(--terracota); }
        .pagos { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 6px; }
        .pagos span { font-size: 13px; padding: 5px 12px; border-radius: 999px; background: var(--crema-2); }
        .mapa { border-radius: 22px; overflow: hidden; min-height: 420px; border: 1px solid var(--linea); box-shadow: 0 24px 50px rgba(38, 33, 28, .12); }
        .mapa iframe { width: 100%; height: 100%; min-height: 420px; border: 0; display: block; }

        /* Pie: el logo original luce sobre negro */
        .pie { background: #000; color: #cfc6bb; padding: 64px 0 28px; font-size: 14px; }
        .pie-rejilla { display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 40px; }
        .pie-logo { width: 170px; margin-bottom: 14px; }
        .pie h4 { color: #fff; font-family: var(--serif); font-weight: 400; font-size: 19px; margin-bottom: 14px; }
        .pie li { list-style: none; padding: 4px 0; }
        .pie a:hover { color: var(--dorado); }
        .redes { display: flex; gap: 10px; margin-top: 16px; }
        .redes a { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 1px solid #3a342e; color: #fff; }
        .redes a:hover { background: var(--terracota); border-color: var(--terracota); color: #fff; }
        .pie-legal { border-top: 1px solid #2a2520; margin-top: 44px; padding-top: 20px; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 10px; color: #8f857a; font-size: 13px; }

        /* WhatsApp flotante y barra del celular */
        .flotante { position: fixed; right: 22px; bottom: 22px; z-index: 55; width: 58px; height: 58px; border-radius: 50%; background: var(--whatsapp); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 0 0 8px rgba(37, 211, 102, .2), 0 12px 24px rgba(0, 0, 0, .2); }
        .barra-movil { display: none; }

        /* Aparición suave al bajar (solo con JavaScript y si el usuario no pidió menos movimiento) */
        .js .aparecer { opacity: 0; transform: translateY(24px); transition: opacity .8s ease, transform .8s ease; }
        .js .aparecer.visible { opacity: 1; transform: none; }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .js .aparecer { opacity: 1; transform: none; transition: none; }
            .experiencia img, .plato img, .foto-atractivo img, .galeria img { transition: none; }
        }

        @media (max-width: 1100px) {
            .nav-enlaces { gap: 16px; }
            .nav-personal { display: none; }
        }

        @media (max-width: 960px) {
            .nav-caja { padding-left: 16px; }
            .nav-boton { display: block; }
            .nav-enlaces { display: none; position: absolute; top: calc(100% + 10px); left: 16px; right: 16px; flex-direction: column; align-items: stretch; gap: 0; background: var(--papel); border-radius: 22px; padding: 10px 22px 18px; box-shadow: 0 20px 40px rgba(38, 33, 28, .18); }
            .nav-enlaces.abierto { display: flex; }
            .nav-enlaces a { padding: 14px 0; border-bottom: 1px solid var(--linea); }
            .nav-enlaces .nav-personal { display: block; padding: 14px 0; border-bottom: 0; }
            .nav-acciones .boton { display: none; }

            .portada .contenedor { grid-template-columns: 1fr; gap: 40px; }
            .vidrio { max-width: 520px; }
            .experiencias { grid-template-columns: 1fr 1fr; }
            .experiencia:last-child { grid-column: span 2; aspect-ratio: 16 / 9; }
            .historia, .bloque, .contacto { grid-template-columns: 1fr; gap: 48px; }
            .bloque-invertido .bloque-foto { order: 0; }
            .carrusel { grid-auto-columns: calc((100% - 18px) / 2.3); }
            .galeria { grid-template-columns: repeat(2, 1fr); }
            .pasos { grid-template-columns: 1fr; }
            .pie-rejilla { grid-template-columns: 1fr 1fr; }
            .pie-rejilla > div:first-child { grid-column: span 2; }
        }

        @media (max-width: 640px) {
            .seccion { padding: 76px 0; }
            .contenedor { padding: 0 18px; }
            .nav { top: 10px; padding: 0 10px; }
            .nav-logo img { height: 40px; }
            .portada { padding: 120px 0 110px; }
            .portada::after { inset: 8px; }
            .portada .botones { flex-wrap: wrap; }
            .portada .boton { flex: 1 1 100%; }
            .vidrio-fila { grid-template-columns: 84px 1fr; padding: 12px 20px; }
            .bajar { display: none; }
            .experiencias { grid-template-columns: 1fr; }
            .experiencia, .experiencia:last-child { grid-column: auto; aspect-ratio: 4 / 5; }
            .historia-fotos { padding: 0 30px 50px 0; }
            .cifras strong { font-size: 30px; }
            .platos-cabecera { margin-top: 72px; }
            .flechas { display: none; }
            .carrusel { grid-auto-columns: 72%; }
            .foto-atractivo { aspect-ratio: 4 / 3; }
            .banner { min-height: 480px; }
            .banner-texto { padding: 28px; }
            .galeria { grid-auto-rows: 160px; }
            .galeria figure:nth-child(6n + 3) { grid-column: auto; }
            .pie-rejilla { grid-template-columns: 1fr; }
            .pie-rejilla > div:first-child { grid-column: auto; }
            body { padding-bottom: 76px; }
            .flotante { display: none; }
            .barra-movil { display: flex; position: fixed; left: 10px; right: 10px; bottom: 10px; z-index: 55; gap: 6px; padding: 6px; border-radius: 999px; background: rgba(255, 253, 249, .95); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0 10px 30px rgba(0, 0, 0, .2); }
            .barra-movil a { flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 12px 6px; border-radius: 999px; font-size: 12px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; }
            .barra-movil a:first-child { background: var(--terracota); color: #fff; }
            .barra-movil .wa { color: #128c4a; }
        }
    </style>
</head>
<body>
    <script>document.documentElement.classList.add('js');</script>
    @php
        $C = \App\Models\Contenido::class;
        $whatsapp = $contenido['whatsapp'] ? 'https://wa.me/'.$contenido['whatsapp'] : null;
        $wa = fn ($mensaje) => $whatsapp ? $whatsapp.'?text='.rawurlencode($mensaje) : null;
        // Una imagen sin subir usa la de la portada
        $imagen = fn ($clave) => $C::urlImagen($contenido[$clave] ?: $contenido['hero_imagen']);
        $cartaPdf = asset('docs/carta-casa-blanca.pdf');
        $atractivosFoto = $instalaciones->filter(fn ($item) => $item->imagen);
        $atractivosIcono = $instalaciones->reject(fn ($item) => $item->imagen);
        $historia = $C::lineas($contenido['historia_texto']);
        $mapa = $contenido['mapa_coordenadas'] ?: $contenido['direccion'];
    @endphp

    <nav class="nav" aria-label="Principal">
        <div class="nav-caja">
            <a href="{{ url('/') }}" class="nav-logo"><img src="{{ asset('img/logo-claro.webp') }}" alt="Casa Blanca, Restaurante Campestre, Poroto"></a>
            <div class="nav-enlaces" id="navEnlaces">
                <a href="#nosotros">Nosotros</a>
                <a href="#restaurante">Restaurante</a>
                @if($instalaciones->isNotEmpty())<a href="#atractivos">Atractivos</a>@endif
                @if($eventos->isNotEmpty())<a href="#eventos">Eventos</a>@endif
                <a href="#hospedaje">Hospedaje</a>
                <a href="#contacto">Contacto</a>
                @auth
                    <a href="{{ url('/dashboard') }}" class="nav-personal"><i class="fas fa-user"></i> Mi panel</a>
                @else
                    <a href="{{ route('login') }}" class="nav-personal"><i class="fas fa-sign-in-alt"></i> Iniciar sesión</a>
                @endauth
            </div>
            <div class="nav-acciones">
                @if($whatsapp)
                    <a href="{{ $wa('Hola, quisiera reservar una mesa en Casa Blanca.') }}" target="_blank" rel="noopener" class="boton boton-verde"><i class="fab fa-whatsapp"></i> Reservar</a>
                @endif
                <button class="nav-boton" id="navBoton" aria-label="Abrir menú" aria-expanded="false" aria-controls="navEnlaces"><i class="fas fa-bars"></i></button>
            </div>
        </div>
    </nav>

    <header class="portada" style="background-image: url('{{ $C::urlImagen($contenido['hero_imagen']) }}');">
        <div class="contenedor">
            <div>
                @if($contenido['hero_etiqueta'])<span class="ceja">{{ $contenido['hero_etiqueta'] }}</span>@endif
                <h1>{{ $C::titulo($contenido['hero_titulo']) }}</h1>
                @if($contenido['hero_texto'])<p class="portada-texto">{{ $contenido['hero_texto'] }}</p>@endif
                <div class="botones">
                    <a href="{{ route('carta.index') }}" class="boton boton-terracota"><i class="fas fa-utensils"></i> Hacer mi pedido</a>
                    <a href="{{ $cartaPdf }}" target="_blank" rel="noopener" class="boton boton-claro"><i class="fas fa-file-pdf"></i> Ver carta (PDF)</a>
                </div>
            </div>

            <aside class="vidrio">
                <div class="vidrio-cabeza">
                    <div class="vidrio-ceja">Campestre desde {{ $contenido['cifra1_valor'] ?: '2015' }}</div>
                    <h2>Restaurante · Piscinas · Hospedaje</h2>
                    <p>Buena comida, áreas verdes y diversión para toda la familia, a minutos de Trujillo.</p>
                </div>
                <a href="#restaurante" class="vidrio-fila"><strong>Comer</strong><span>Comida marina y criolla</span></a>
                <a href="#atractivos" class="vidrio-fila"><strong>Disfrutar</strong><span>3 piscinas, juegos y canchas</span></a>
                <a href="#hospedaje" class="vidrio-fila"><strong>Descansar</strong><span>Hospedaje por días u horas</span></a>
            </aside>
        </div>
        <a href="#experiencias" class="bajar">BAJAR</a>
    </header>

    <main>
        {{-- TRES EXPERIENCIAS --}}
        <section id="experiencias" class="seccion seccion-crema">
            <div class="contenedor">
                <div class="cabecera aparecer">
                    <span class="ceja">Experiencias</span>
                    <h2 class="titulo">{{ $C::titulo($contenido['exp_titulo']) }}</h2>
                    <div class="adorno"><span></span></div>
                    @if($contenido['exp_texto'])<p class="bajada">{{ $contenido['exp_texto'] }}</p>@endif
                </div>
                <div class="experiencias">
                    @foreach([1 => ['Restaurante', '#restaurante', 'Ver la carta'], 2 => ['Piscinas', '#atractivos', 'Ver atractivos'], 3 => ['Hospedaje', '#hospedaje', 'Ver hospedaje']] as $n => [$ceja, $ancla, $enlace])
                        <a href="{{ $ancla }}" class="experiencia aparecer">
                            <img src="{{ $imagen('exp'.$n.'_imagen') }}" alt="" loading="lazy">
                            <small>{{ $ceja }}</small>
                            <h3>{{ $contenido['exp'.$n.'_titulo'] }}</h3>
                            @if($contenido['exp'.$n.'_texto'])<p>{{ $contenido['exp'.$n.'_texto'] }}</p>@endif
                            <span>{{ $enlace }} →</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- NUESTRA HISTORIA --}}
        <section id="nosotros" class="seccion seccion-crema-2">
            <div class="contenedor historia">
                <div class="historia-fotos aparecer">
                    <img src="{{ $imagen('historia_imagen1') }}" alt="Casa Blanca, restaurante campestre" class="grande" loading="lazy">
                    <img src="{{ $imagen('historia_imagen2') }}" alt="" class="chica" loading="lazy">
                </div>
                <div class="historia-texto aparecer">
                    <span class="ceja">Nuestra historia</span>
                    <h2 class="titulo">{{ $C::titulo($contenido['historia_titulo']) }}</h2>
                    <div class="adorno adorno-izq"><span></span></div>
                    @foreach($historia as $parrafo)
                        <p>{{ $parrafo }}</p>
                    @endforeach
                    <div class="cifras">
                        @foreach([1, 2, 3] as $n)
                            @if($contenido['cifra'.$n.'_valor'])
                                <div><strong>{{ $contenido['cifra'.$n.'_valor'] }}</strong><span>{{ $contenido['cifra'.$n.'_texto'] }}</span></div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- RESTAURANTE --}}
        <section id="restaurante" class="seccion">
            <div class="contenedor">
                <div class="cabecera aparecer">
                    <span class="ceja">Restaurante</span>
                    <h2 class="titulo">{{ $C::titulo($contenido['restaurante_titulo']) }}</h2>
                    <div class="adorno"><span></span></div>
                </div>
                <div class="bloque">
                    <div class="bloque-foto aparecer"><img src="{{ $imagen('restaurante_imagen') }}" alt="Restaurante Casa Blanca" loading="lazy"></div>
                    <div class="aparecer">
                        <span class="ceja">Nuestra cocina</span>
                        <h3>Buena mesa, mejor compañía.</h3>
                        @if($contenido['restaurante_texto'])<p>{{ $contenido['restaurante_texto'] }}</p>@endif
                        @if($lista = $C::lineas($contenido['restaurante_lista']))
                            <ul class="lista">
                                @foreach($lista as $punto)<li>{{ $punto }}</li>@endforeach
                            </ul>
                        @endif
                        <div class="botones">
                            <a href="{{ route('carta.index') }}" class="boton boton-verde"><i class="fas fa-utensils"></i> Hacer mi pedido</a>
                            <a href="{{ $cartaPdf }}" target="_blank" rel="noopener" class="boton boton-borde"><i class="fas fa-file-pdf"></i> Ver carta</a>
                        </div>
                    </div>
                </div>

                @if($destacados->isNotEmpty())
                    <div id="platos" class="platos-cabecera aparecer">
                        <div>
                            <span class="ceja">Lo más pedido</span>
                            <h3>{{ $C::titulo($contenido['platos_titulo']) }}</h3>
                            @if($contenido['platos_texto'])<p class="bajada">{{ $contenido['platos_texto'] }}</p>@endif
                        </div>
                        <div class="flechas">
                            <button type="button" data-carrusel="-1" aria-label="Platos anteriores"><i class="fas fa-arrow-left"></i></button>
                            <button type="button" data-carrusel="1" aria-label="Más platos"><i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>
                    <div class="carrusel" id="carrusel">
                        @foreach($destacados as $plato)
                            <button type="button" class="plato" aria-label="Ver detalle de {{ $plato->nombre }}"
                                    @if($plato->imagen)
                                        data-visor
                                        data-visor-imagen="{{ asset('storage/'.$plato->imagen) }}"
                                        data-visor-titulo="{{ $plato->nombre }}"
                                        data-visor-precio="S/ {{ number_format($plato->precio_venta, 2) }}"
                                        @if($plato->incluye) data-visor-incluye="{{ json_encode($plato->incluye) }}" @endif
                                        data-visor-texto="{{ $plato->descripcion }}"
                                    @endif>
                                @if($plato->imagen)
                                    <img src="{{ asset('storage/'.$plato->imagen) }}" alt="" loading="lazy">
                                @else
                                    <span class="plato-sin-foto"><i class="fas fa-utensils"></i></span>
                                @endif
                                <span class="plato-info">
                                    <strong>{{ $plato->nombre }}</strong>
                                    <span>S/ {{ number_format($plato->precio_venta, 2) }}</span>
                                </span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        {{-- ATRACTIVOS --}}
        @if($instalaciones->isNotEmpty())
            <section id="atractivos" class="seccion seccion-crema">
                <div class="contenedor">
                    <div class="cabecera aparecer">
                        <span class="ceja">Atractivos</span>
                        <h2 class="titulo">{{ $C::titulo($contenido['instalaciones_titulo']) }}</h2>
                        <div class="adorno"><span></span></div>
                        @if($contenido['instalaciones_texto'])<p class="bajada">{{ $contenido['instalaciones_texto'] }}</p>@endif
                    </div>
                    @if($atractivosFoto->isNotEmpty())
                        <div class="fotos-atractivos">
                            @foreach($atractivosFoto as $atractivo)
                                <article class="foto-atractivo aparecer">
                                    <img src="{{ $atractivo->imagen_url }}" alt="{{ $atractivo->titulo }}" loading="lazy">
                                    <div>
                                        <h3>{{ $atractivo->titulo }}</h3>
                                        @if($atractivo->descripcion)<p>{{ $atractivo->descripcion }}</p>@endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                    @if($atractivosIcono->isNotEmpty())
                        <div class="iconos">
                            @foreach($atractivosIcono as $atractivo)
                                <article class="icono-tarjeta aparecer">
                                    <div class="icono-circulo"><i class="fas {{ $atractivo->icono }}"></i></div>
                                    <h3>{{ $atractivo->titulo }}</h3>
                                    @if($atractivo->descripcion)<p>{{ $atractivo->descripcion }}</p>@endif
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- ACTIVIDADES --}}
        @if($actividades->isNotEmpty())
            <section id="actividades" class="seccion">
                <div class="contenedor">
                    <div class="cabecera aparecer">
                        <span class="ceja">Actividades</span>
                        <h2 class="titulo">{{ $C::titulo($contenido['actividades_titulo']) }}</h2>
                        <div class="adorno"><span></span></div>
                        @if($contenido['actividades_texto'])<p class="bajada">{{ $contenido['actividades_texto'] }}</p>@endif
                    </div>
                    <div class="iconos">
                        @foreach($actividades as $actividad)
                            <article class="icono-tarjeta aparecer">
                                <div class="icono-circulo"><i class="fas {{ $actividad->icono }}"></i></div>
                                @if($actividad->detalle)<small>{{ $actividad->detalle }}</small>@endif
                                <h3>{{ $actividad->titulo }}</h3>
                                @if($actividad->descripcion)<p>{{ $actividad->descripcion }}</p>@endif
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- EVENTOS --}}
        @if($eventos->isNotEmpty())
            <section id="eventos" class="seccion seccion-crema-2">
                <div class="contenedor">
                    <div class="cabecera aparecer">
                        <span class="ceja">Eventos y alquiler del local</span>
                        <h2 class="titulo">{{ $C::titulo($contenido['eventos_titulo']) }}</h2>
                        <div class="adorno"><span></span></div>
                        @if($contenido['eventos_texto'])<p class="bajada">{{ $contenido['eventos_texto'] }}</p>@endif
                    </div>
                    <div class="banner aparecer">
                        <img src="{{ $imagen('eventos_imagen') }}" alt="" loading="lazy">
                        <div class="banner-texto">
                            <h3>Celebra con todo el <em>espacio</em> para los tuyos.</h3>
                            <p>Piscinas, áreas verdes, sonido y nuestra cocina para tu evento.</p>
                            @if($whatsapp)
                                <a href="{{ $wa('Hola, quisiera cotizar un evento en Casa Blanca.') }}" target="_blank" rel="noopener" class="boton boton-terracota"><i class="fab fa-whatsapp"></i> Cotizar mi evento</a>
                            @endif
                        </div>
                    </div>
                    <div class="etiquetas">
                        @foreach($eventos as $evento)
                            <span class="etiqueta aparecer" @if($evento->descripcion) title="{{ $evento->descripcion }}" @endif><i class="fas {{ $evento->icono }}"></i> {{ $evento->titulo }}</span>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- HOSPEDAJE --}}
        <section id="hospedaje" class="seccion">
            <div class="contenedor bloque bloque-invertido">
                <div class="bloque-foto aparecer"><img src="{{ $imagen('hospedaje_imagen') }}" alt="Hospedaje en Casa Blanca" loading="lazy"></div>
                <div class="aparecer">
                    <span class="ceja">Hospedaje</span>
                    <h2 class="titulo" style="margin-bottom: 0;">{{ $C::titulo($contenido['hospedaje_titulo']) }}</h2>
                    <div class="adorno adorno-izq"><span></span></div>
                    @if($contenido['hospedaje_texto'])<p>{{ $contenido['hospedaje_texto'] }}</p>@endif
                    @if($lista = $C::lineas($contenido['hospedaje_lista']))
                        <ul class="lista">
                            @foreach($lista as $punto)<li>{{ $punto }}</li>@endforeach
                        </ul>
                    @endif
                    @if($whatsapp)
                        <a href="{{ $wa('Hola, quisiera consultar la disponibilidad del hospedaje en Casa Blanca.') }}" target="_blank" rel="noopener" class="boton boton-verde"><i class="fab fa-whatsapp"></i> Consultar disponibilidad</a>
                    @endif
                </div>
            </div>
        </section>

        {{-- GALERÍA --}}
        @if($galeria->isNotEmpty())
            <section id="galeria" class="seccion seccion-crema">
                <div class="contenedor">
                    <div class="cabecera aparecer">
                        <span class="ceja">Galería</span>
                        <h2 class="titulo">{{ $C::titulo($contenido['galeria_titulo']) }}</h2>
                        <div class="adorno"><span></span></div>
                        @if($contenido['galeria_texto'])<p class="bajada">{{ $contenido['galeria_texto'] }}</p>@endif
                    </div>
                    <div class="galeria">
                        @foreach($galeria as $foto)
                            <figure class="aparecer">
                                <img src="{{ $foto->imagen_url }}" alt="{{ $foto->titulo }}" loading="lazy">
                                <figcaption>{{ $foto->titulo }}</figcaption>
                            </figure>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- TESTIMONIOS --}}
        @if($testimonios->isNotEmpty())
            <section id="testimonios" class="seccion">
                <div class="contenedor">
                    <div class="cabecera aparecer">
                        <span class="ceja">Testimonios</span>
                        <h2 class="titulo">{{ $C::titulo($contenido['testimonios_titulo']) }}</h2>
                        <div class="adorno"><span></span></div>
                    </div>
                    <div class="testimonios">
                        @foreach($testimonios as $testimonio)
                            @php
                                // Estrellas solo si el detalle trae notas (ej: "Comida 5 · Servicio 5 · Ambiente 5")
                                preg_match_all('/\b([1-5])\b/', $testimonio->detalle ?? '', $notas);
                                $estrellas = $notas[1] ? (int) round(array_sum($notas[1]) / count($notas[1])) : 0;
                            @endphp
                            <article class="testimonio aparecer">
                                @if($estrellas)
                                    <div class="estrellas" aria-label="{{ $estrellas }} de 5 estrellas">{!! str_repeat('<i class="fas fa-star"></i>', $estrellas) !!}</div>
                                @endif
                                <blockquote>“{{ $testimonio->descripcion }}”</blockquote>
                                <strong>{{ $testimonio->titulo }}</strong>
                                @if($testimonio->detalle)<span>{{ $testimonio->detalle }}</span>@endif
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- SIGUIENTE PASO --}}
        <section class="seccion seccion-crema-2">
            <div class="contenedor">
                <div class="cabecera aparecer">
                    <span class="ceja">Te esperamos</span>
                    <h2 class="titulo">{{ $C::titulo($contenido['cta_titulo']) }}</h2>
                    <div class="adorno"><span></span></div>
                    @if($contenido['cta_texto'])<p class="bajada">{{ $contenido['cta_texto'] }}</p>@endif
                </div>
                <div class="pasos">
                    <article class="paso aparecer">
                        <div class="icono-circulo"><i class="fas fa-utensils"></i></div>
                        <h3>Haz tu pedido</h3>
                        <p>Elige tus platos desde la carta, déjanos tu DNI y paga en caja cuando llegues.</p>
                        <a href="{{ route('carta.index') }}" class="boton boton-terracota">Hacer mi pedido</a>
                    </article>
                    @if($whatsapp)
                        <article class="paso aparecer">
                            <div class="icono-circulo"><i class="fas fa-calendar-check"></i></div>
                            <h3>Reserva tu mesa</h3>
                            <p>Escríbenos por WhatsApp y te guardamos un lugar para ti y tu familia.</p>
                            <a href="{{ $wa('Hola, quisiera reservar una mesa en Casa Blanca.') }}" target="_blank" rel="noopener" class="boton boton-verde"><i class="fab fa-whatsapp"></i> Reservar</a>
                        </article>
                        <article class="paso aparecer">
                            <div class="icono-circulo"><i class="fas fa-champagne-glasses"></i></div>
                            <h3>Cotiza tu evento</h3>
                            <p>Cumpleaños, bodas, bautizos, promociones o almuerzos de empresa.</p>
                            <a href="{{ $wa('Hola, quisiera cotizar un evento en Casa Blanca.') }}" target="_blank" rel="noopener" class="boton boton-borde">Cotizar</a>
                        </article>
                    @endif
                </div>
            </div>
        </section>

        {{-- CONTACTO --}}
        <section id="contacto" class="seccion">
            <div class="contenedor contacto">
                <div class="aparecer">
                    <span class="ceja">Contacto</span>
                    <h2 class="titulo">Ven a conocernos en <em>Poroto</em>.</h2>
                    <ul class="datos">
                        @if($contenido['direccion'])
                            <li><i class="fas fa-location-dot"></i><div><small>Dirección</small>
                                @if($contenido['mapa_enlace'])<a href="{{ $contenido['mapa_enlace'] }}" target="_blank" rel="noopener">{{ $contenido['direccion'] }}</a>@else{{ $contenido['direccion'] }}@endif
                            </div></li>
                        @endif
                        @if($contenido['horario'])<li><i class="fas fa-clock"></i><div><small>Horario</small>{{ $contenido['horario'] }}</div></li>@endif
                        @if($contenido['telefono'])
                            <li><i class="fas fa-phone"></i><div><small>Teléfono</small><a href="tel:{{ preg_replace('/\D/', '', $contenido['telefono']) }}">{{ $contenido['telefono'] }}</a></div></li>
                        @endif
                        @if($whatsapp)<li><i class="fab fa-whatsapp"></i><div><small>WhatsApp</small><a href="{{ $whatsapp }}" target="_blank" rel="noopener">Escríbenos</a></div></li>@endif
                        @if($contenido['pagos'])
                            <li><i class="fas fa-credit-card"></i><div><small>Métodos de pago</small>
                                <div class="pagos">@foreach(array_filter(array_map('trim', explode(',', $contenido['pagos']))) as $pago)<span>{{ $pago }}</span>@endforeach</div>
                            </div></li>
                        @endif
                    </ul>
                    <div class="botones">
                        @if($contenido['mapa_enlace'])<a href="{{ $contenido['mapa_enlace'] }}" target="_blank" rel="noopener" class="boton boton-borde"><i class="fas fa-diamond-turn-right"></i> Cómo llegar</a>@endif
                        @if($whatsapp)<a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="boton boton-whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp</a>@endif
                    </div>
                </div>
                @if($mapa)
                    <div class="mapa aparecer">
                        <iframe src="https://www.google.com/maps?q={{ rawurlencode($mapa) }}&z=16&output=embed" title="Mapa de Casa Blanca en Poroto" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                @endif
            </div>
        </section>
    </main>

    <footer class="pie">
        <div class="contenedor">
            <div class="pie-rejilla">
                <div>
                    <img src="{{ asset('img/logo.webp') }}" alt="Casa Blanca" class="pie-logo">
                    <p>Restaurante campestre en Poroto desde {{ $contenido['cifra1_valor'] ?: '2015' }}. Comida marina y criolla, piscinas, áreas verdes y hospedaje.</p>
                    <div class="redes">
                        @if($contenido['instagram'])<a href="{{ $contenido['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>@endif
                        @if($contenido['facebook'])<a href="{{ $contenido['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>@endif
                        @if($contenido['tiktok'])<a href="{{ $contenido['tiktok'] }}" target="_blank" rel="noopener" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>@endif
                        @if($contenido['youtube'])<a href="{{ $contenido['youtube'] }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>@endif
                        @if($whatsapp)<a href="{{ $whatsapp }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>@endif
                    </div>
                </div>
                <div>
                    <h4>Visítanos</h4>
                    <ul>
                        @if($contenido['direccion'])<li>{{ $contenido['direccion'] }}</li>@endif
                        @if($contenido['horario'])<li>{{ $contenido['horario'] }}</li>@endif
                        @if($contenido['telefono'])<li>{{ $contenido['telefono'] }}</li>@endif
                    </ul>
                </div>
                <div>
                    <h4>Explora</h4>
                    <ul>
                        <li><a href="{{ route('carta.index') }}">Hacer mi pedido</a></li>
                        <li><a href="{{ $cartaPdf }}" target="_blank" rel="noopener">Carta en PDF</a></li>
                        @if($eventos->isNotEmpty())<li><a href="#eventos">Eventos</a></li>@endif
                        <li><a href="#hospedaje">Hospedaje</a></li>
                        <li><a href="{{ route('login') }}">Acceso del personal</a></li>
                    </ul>
                </div>
            </div>
            <div class="pie-legal">
                <span>© {{ date('Y') }} Casa Blanca, Restaurante Campestre. Todos los derechos reservados.</span>
                <a href="{{ asset('creditos-fotos.txt') }}" style="text-decoration: underline;">Créditos de fotos</a>
            </div>
        </div>
    </footer>

    @if($whatsapp)
        <a href="{{ $wa('Hola, quisiera información sobre Casa Blanca.') }}" target="_blank" rel="noopener" class="flotante" aria-label="Escríbenos por WhatsApp"><i class="fab fa-whatsapp"></i></a>
    @endif
    <nav class="barra-movil" aria-label="Accesos rápidos">
        <a href="{{ route('carta.index') }}"><i class="fas fa-utensils"></i> Pedir</a>
        <a href="{{ $cartaPdf }}" target="_blank" rel="noopener"><i class="fas fa-file-pdf"></i> Carta</a>
        @if($whatsapp)<a href="{{ $wa('Hola, quisiera reservar una mesa en Casa Blanca.') }}" target="_blank" rel="noopener" class="wa"><i class="fab fa-whatsapp"></i> Reservar</a>@endif
    </nav>

    @include('partials.visor-producto')

    <script>
        // Menú del celular
        const navBoton = document.getElementById('navBoton');
        const navEnlaces = document.getElementById('navEnlaces');
        navBoton.addEventListener('click', function () {
            const abierto = navEnlaces.classList.toggle('abierto');
            navBoton.setAttribute('aria-expanded', abierto);
            navBoton.querySelector('i').className = abierto ? 'fas fa-times' : 'fas fa-bars';
        });
        navEnlaces.querySelectorAll('a[href^="#"]').forEach(function (enlace) {
            enlace.addEventListener('click', function () {
                navEnlaces.classList.remove('abierto');
                navBoton.setAttribute('aria-expanded', false);
                navBoton.querySelector('i').className = 'fas fa-bars';
            });
        });

        // Flechas del carrusel de platos
        const carrusel = document.getElementById('carrusel');
        document.querySelectorAll('[data-carrusel]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                carrusel.scrollBy({ left: carrusel.clientWidth * 0.8 * Number(boton.dataset.carrusel), behavior: 'smooth' });
            });
        });

        // Aparición suave de las secciones
        const elementos = document.querySelectorAll('.aparecer');
        if ('IntersectionObserver' in window) {
            const observador = new IntersectionObserver(function (entradas) {
                entradas.forEach(function (entrada) {
                    if (entrada.isIntersecting) {
                        entrada.target.classList.add('visible');
                        observador.unobserve(entrada.target);
                    }
                });
            }, { rootMargin: '0px 0px -8% 0px' });
            elementos.forEach(function (el) { observador.observe(el); });
        } else {
            elementos.forEach(function (el) { el.classList.add('visible'); });
        }
    </script>
</body>
</html>
