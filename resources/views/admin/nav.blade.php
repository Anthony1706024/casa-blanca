<!-- ==========================================
     MENÚ LATERAL - ADMIN (RESPONSIVE)
========================================== -->

<!-- Botón Hamburguesa (visible en móvil) -->
<button id="menuToggle" class="fixed top-4 left-4 z-50 p-2 rounded-lg bg-[#1a1a1a] border border-orange-500/20 text-white hover:bg-orange-500/10 transition md:hidden">
    <i class="fas fa-bars text-xl"></i>
</button>

<!-- Overlay (fondo oscuro al abrir el menú en móvil) -->
<div id="menuOverlay" class="fixed inset-0 bg-black/60 z-40 hidden md:hidden"></div>

<!-- Menú Lateral -->
<nav id="sidebarMenu" class="fixed top-0 left-0 h-screen w-64 bg-[#0d0d0d] border-r border-orange-500/20 overflow-y-auto z-50 transition-transform duration-300 -translate-x-full md:translate-x-0">
    
    <!-- Logo -->
    <div class="p-6 border-b border-orange-500/20">
        <div class="flex items-center gap-3">
            <img src="/img/logo.webp" alt="Casa Blanca" class="w-10 h-10 object-contain">
            <div>
                <h2 class="text-white font-bold text-lg">CASA BLANCA</h2>
                <p class="text-orange-500 text-xs font-semibold">Administrador</p>
            </div>
        </div>
    </div>

    <!-- Menú -->
    <div class="p-4">
        <p class="text-gray-600 text-xs uppercase tracking-wider font-semibold mb-4">Menú Principal</p>

        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" 
           class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
            <i class="fas fa-home w-5 text-center"></i>
            <span class="text-sm font-medium">Dashboard</span>
        </a>

        <p class="text-gray-600 text-xs uppercase tracking-wider font-semibold mt-6 mb-4">Usuarios</p>

        <!-- Lista de Usuarios - SOLO index -->
        <a href="{{ route('admin.usuarios.index') }}" 
           class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.usuarios.index') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
            <i class="fas fa-users w-5 text-center"></i>
            <span class="text-sm font-medium">Lista de Usuarios</span>
        </a>

        <!-- Agregar Usuario - SOLO create -->
        <a href="{{ route('admin.usuarios.create') }}" 
           class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.usuarios.create') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
            <i class="fas fa-user-plus w-5 text-center"></i>
            <span class="text-sm font-medium">Agregar Usuario</span>
        </a>

        <p class="text-gray-600 text-xs uppercase tracking-wider font-semibold mt-6 mb-4">Insumos</p>

        <a href="{{ route('admin.insumos.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.insumos.index') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
            <i class="fas fa-cubes w-5 text-center"></i>
            <span class="text-sm font-medium">Ver Insumos</span>
        </a>
        <a href="{{ route('admin.insumos.categorias.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.insumos.categorias.index') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
            <i class="fas fa-tags w-5 text-center"></i>
            <span class="text-sm font-medium">Categorías de Insumo</span>
        </a>
        <a href="{{ route('admin.insumos.create') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.insumos.create') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
            <i class="fas fa-cube w-5 text-center"></i>
            <span class="text-sm font-medium">Registrar Insumo</span>
        </a>

        <p class="text-gray-600 text-xs uppercase tracking-wider font-semibold mt-6 mb-4">Almacén</p>

        <!-- Ver Almacén - SOLO index -->
        <a href="{{ route('admin.almacen.index') }}" 
           class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.almacen.index') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
            <i class="fas fa-warehouse w-5 text-center"></i>
            <span class="text-sm font-medium">Ver Almacén</span>
        </a>

        <!-- Agregar Categoría - SOLO categorias.index -->
        <a href="{{ route('admin.categorias.index') }}" 
           class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.categorias.index') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
            <i class="fas fa-tags w-5 text-center"></i>
            <span class="text-sm font-medium">Agregar Categoría</span>
        </a>
        <!-- Agregar Subcategoría - SOLO subcategorias.index -->
        <a href="{{ route('admin.subcategorias.index') }}" 
        class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.subcategorias.index') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
            <i class="fas fa-layer-group w-5 text-center"></i>
            <span class="text-sm font-medium">Agregar Subcategoría</span>
        </a>
        <!-- Registrar Producto - SOLO create -->
        <a href="{{ route('admin.almacen.create') }}" 
           class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.almacen.create') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
            <i class="fas fa-box w-5 text-center"></i>
            <span class="text-sm font-medium">Registrar Producto</span>
        </a>

        <p class="text-gray-600 text-xs uppercase tracking-wider font-semibold mt-6 mb-4">Ventas</p>

        <!-- Ver Ventas -->
        <a href="{{ route('admin.ventas.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.ventas.index') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
            <i class="fas fa-shopping-cart w-5 text-center"></i>
            <span class="text-sm font-medium">Ver Ventas</span>
        </a>

        <!-- Cola de impresión -->
        <a href="{{ route('impresiones.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('impresiones.*') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
            <i class="fas fa-print w-5 text-center"></i>
            <span class="text-sm font-medium">Impresiones</span>
        </a>

        <p class="text-gray-600 text-xs uppercase tracking-wider font-semibold mt-6 mb-4">Finanzas</p>

        <a href="{{ route('admin.ingresos.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.ingresos.*') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
            <i class="fas fa-chart-line w-5 text-center"></i>
            <span class="text-sm font-medium">Ingresos Diarios</span>
        </a>
        <a href="{{ route('admin.egresos.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.egresos.*') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
            <i class="fas fa-file-invoice-dollar w-5 text-center"></i>
            <span class="text-sm font-medium">Egresos</span>
        </a>

        <p class="text-gray-600 text-xs uppercase tracking-wider font-semibold mt-6 mb-4">Precios</p>

        <a href="{{ route('admin.precios.index') }}" 
           class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.precios.*') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
            <i class="fas fa-tags w-5 text-center"></i>
            <span class="text-sm font-medium">Precios y Detalle</span>
        </a>

        <p class="text-gray-600 text-xs uppercase tracking-wider font-semibold mt-6 mb-4">Página Web</p>

        <a href="{{ route('admin.contenido.index') }}" 
           class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.contenido.*') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
            <i class="fas fa-globe w-5 text-center"></i>
            <span class="text-sm font-medium">Contenido de la Página</span>
        </a>

        <!-- Cerrar Sesión -->
        <div class="border-t border-orange-500/20 mt-6 pt-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-400 hover:bg-red-500/10 hover:text-red-500 transition w-full">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i>
                    <span class="text-sm font-medium">Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </div>
</nav>

<!-- ==========================================
     ESTILOS DEL MENÚ
========================================== -->
<style>
    /* Scrollbar del menú */
    nav::-webkit-scrollbar {
        width: 4px;
    }
    nav::-webkit-scrollbar-track {
        background: transparent;
    }
    nav::-webkit-scrollbar-thumb {
        background: #f97316;
        border-radius: 2px;
    }
    nav::-webkit-scrollbar-thumb:hover {
        background: #ea580c;
    }

    /* Transiciones suaves */
    nav a {
        transition: all 0.2s ease;
    }

    /* Iconos */
    nav a i {
        font-size: 16px;
    }

    /* Ajuste para móvil */
    @media (max-width: 768px) {
        /* El contenido principal debe tener margen izquierdo 0 en móvil */
        .ml-64 {
            margin-left: 0 !important;
        }
        /* Padding reducido en móvil */
        .p-8 {
            padding: 1rem !important;
        }
    }
</style>

<!-- ==========================================
     JAVASCRIPT PARA TOGGLE DEL MENÚ
========================================== -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const sidebarMenu = document.getElementById('sidebarMenu');
        const menuOverlay = document.getElementById('menuOverlay');

        function toggleMenu() {
            const isOpen = sidebarMenu.classList.contains('translate-x-0');
            
            if (isOpen) {
                sidebarMenu.classList.remove('translate-x-0');
                sidebarMenu.classList.add('-translate-x-full');
                menuOverlay.classList.add('hidden');
                menuToggle.innerHTML = '<i class="fas fa-bars text-xl"></i>';
            } else {
                sidebarMenu.classList.remove('-translate-x-full');
                sidebarMenu.classList.add('translate-x-0');
                menuOverlay.classList.remove('hidden');
                menuToggle.innerHTML = '<i class="fas fa-times text-xl"></i>';
            }
        }

        // Evento del botón hamburguesa
        menuToggle.addEventListener('click', toggleMenu);

        // Cerrar menú al hacer clic en el overlay
        menuOverlay.addEventListener('click', function() {
            if (sidebarMenu.classList.contains('translate-x-0')) {
                toggleMenu();
            }
        });

        // Cerrar menú al hacer clic en un enlace (móvil)
        const menuLinks = sidebarMenu.querySelectorAll('a:not([href="#"])');
        menuLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768 && sidebarMenu.classList.contains('translate-x-0')) {
                    toggleMenu();
                }
            });
        });

        // Cerrar menú al redimensionar a desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebarMenu.classList.remove('-translate-x-full');
                sidebarMenu.classList.add('translate-x-0');
                menuOverlay.classList.add('hidden');
                menuToggle.innerHTML = '<i class="fas fa-bars text-xl"></i>';
            } else {
                if (!sidebarMenu.classList.contains('translate-x-0') && !sidebarMenu.classList.contains('-translate-x-full')) {
                    sidebarMenu.classList.add('-translate-x-full');
                }
            }
        });
    });
</script>