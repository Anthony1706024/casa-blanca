<!-- ==========================================
     VISOR: modal con la foto y los datos del producto.
     Se abre al hacer clic en un elemento con data-visor.
     Atributos: data-visor-titulo y, opcionales, data-visor-imagen, data-visor-precio, data-visor-texto y data-visor-incluye (lista JSON).
     No depende de Tailwind: también se usa en la página de inicio.
========================================== -->
<div id="visor" role="dialog" aria-modal="true" aria-labelledby="visorTitulo" hidden>
    <div class="visor-caja">
        <button type="button" id="visorCerrar" class="visor-cerrar" aria-label="Cerrar">&times;</button>
        <img id="visorImagen" src="" alt="" hidden>
        <div class="visor-datos">
            <h2 id="visorTitulo"></h2>
            <p id="visorPrecio"></p>
            <div id="visorDetalle" hidden>
                <h3>Qué trae</h3>
                <ul id="visorIncluye"></ul>
                <p id="visorTexto"></p>
            </div>
        </div>
    </div>
</div>

<style>
    #visor { position: fixed; inset: 0; z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(0, 0, 0, .8); }
    #visor[hidden], #visorImagen[hidden], #visorDetalle[hidden] { display: none; }
    .visor-caja { position: relative; display: flex; flex-direction: column; width: 100%; max-width: 760px; max-height: calc(100vh - 32px); background: #fff; border-radius: 16px; overflow: hidden; }
    #visorImagen { flex: 1 1 auto; min-height: 0; width: 100%; object-fit: contain; background: #1c1917; }
    .visor-datos { flex: none; max-height: 45vh; overflow-y: auto; padding: 16px 64px 18px 20px; color: #1c1917; }
    #visorTitulo { margin: 0; font-size: 20px; font-weight: 700; line-height: 1.3; }
    #visorPrecio { margin: 4px 0 0; color: #c2410c; font-size: 18px; font-weight: 700; }
    #visorDetalle { margin-top: 12px; padding-top: 12px; border-top: 1px solid #e7e5e4; }
    #visorDetalle h3 { margin: 0 0 2px; font-size: 14px; font-weight: 700; }
    #visorIncluye { margin: 4px 0 0; padding: 0; list-style: none; display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 2px 16px; color: #1c1917; font-size: 15px; }
    #visorIncluye li::before { content: '✓'; margin-right: 8px; color: #15803d; font-weight: 700; }
    #visorTexto { margin: 6px 0 0; color: #57534e; font-size: 15px; white-space: pre-line; }
    #visorTexto:empty { display: none; }
    #visorImagen:not([hidden]) ~ .visor-datos { padding-right: 20px; }
    .visor-cerrar { position: absolute; top: 10px; right: 10px; width: 44px; height: 44px; border: 0; border-radius: 50%; background: rgba(0, 0, 0, .65); color: #fff; font-size: 28px; line-height: 1; cursor: pointer; }
    .visor-cerrar:hover { background: #000; }
    [data-visor] { cursor: pointer; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const visor = document.getElementById('visor');
        const imagen = document.getElementById('visorImagen');
        const cerrar = document.getElementById('visorCerrar');
        let origen = null;

        function abrir(elemento) {
            const datos = elemento.dataset;
            // Muchos productos repiten el nombre como descripción
            const texto = datos.visorTexto && datos.visorTexto !== datos.visorTitulo ? datos.visorTexto : '';

            origen = elemento;
            imagen.hidden = !datos.visorImagen;
            imagen.src = datos.visorImagen || '';
            imagen.alt = datos.visorTitulo || '';
            document.getElementById('visorTitulo').textContent = datos.visorTitulo || '';
            document.getElementById('visorPrecio').textContent = datos.visorPrecio || '';
            const incluye = datos.visorIncluye ? JSON.parse(datos.visorIncluye) : [];
            document.getElementById('visorIncluye').replaceChildren(...incluye.map(function (linea) {
                const item = document.createElement('li');
                item.textContent = linea;
                return item;
            }));
            document.getElementById('visorTexto').textContent = texto;
            document.getElementById('visorDetalle').hidden = !texto && !incluye.length;
            visor.hidden = false;
            document.body.style.overflow = 'hidden';
            cerrar.focus();
        }

        function ocultar() {
            visor.hidden = true;
            imagen.src = '';
            document.body.style.overflow = '';
            if (origen) {
                origen.focus();
            }
        }

        // Si la foto no carga, se muestran igual los datos del producto
        imagen.addEventListener('error', function () {
            if (!visor.hidden) {
                imagen.hidden = true;
            }
        });

        // Delegado: sirve para todas las tarjetas, aunque el buscador las oculte y vuelva a mostrar
        document.addEventListener('click', function (evento) {
            const elemento = evento.target.closest('[data-visor]');
            if (elemento) {
                evento.preventDefault();
                abrir(elemento);
            }
        });

        cerrar.addEventListener('click', ocultar);
        visor.addEventListener('click', function (evento) {
            if (evento.target === visor) {
                ocultar();
            }
        });
        document.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape' && !visor.hidden) {
                ocultar();
            }
            // Las imágenes con role="button" no responden solas al teclado como un botón real
            if (evento.key === 'Enter' && evento.target.matches('img[data-visor]')) {
                abrir(evento.target);
            }
        });
    });
</script>
