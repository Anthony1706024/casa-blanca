<!-- ==========================================
     MENÚ LATERAL - MOZO
========================================== -->
<nav class="fixed top-0 left-0 h-screen w-64 bg-[#0d0d0d] border-r border-orange-500/20 overflow-y-auto z-50">
    <!-- Logo -->
    <div class="p-6 border-b border-orange-500/20">
        <div class="flex items-center gap-3">
            <img src="/img/logo.png" alt="Casa Blanca" class="w-10 h-10 object-contain">
            <div>
                <h2 class="text-white font-bold text-lg">CASA BLANCA</h2>
                <p class="text-orange-500 text-xs font-semibold">Mozo</p>
            </div>
        </div>
    </div>

    <!-- Menú -->
    <div class="p-4">
        <p class="text-gray-600 text-xs uppercase tracking-wider font-semibold mb-4">Menú Principal</p>

        <!-- Dashboard -->
        <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-orange-500/10 text-orange-500 hover:bg-orange-500/20 transition mb-1">
            <i class="fas fa-home w-5 text-center"></i>
            <span class="text-sm font-medium">Dashboard</span>
        </a>

        <p class="text-gray-600 text-xs uppercase tracking-wider font-semibold mt-6 mb-4">Pedidos</p>

        <!-- Registrar Pedido -->
        <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-orange-500/10 hover:text-white transition mb-1">
            <i class="fas fa-plus-circle w-5 text-center"></i>
            <span class="text-sm font-medium">Registrar Pedido</span>
        </a>

        <!-- Ver Pedidos -->
        <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-orange-500/10 hover:text-white transition mb-1">
            <i class="fas fa-clipboard-list w-5 text-center"></i>
            <span class="text-sm font-medium">Ver Pedidos</span>
        </a>

        <p class="text-gray-600 text-xs uppercase tracking-wider font-semibold mt-6 mb-4">Pagos</p>

        <!-- Pagar Pedido -->
        <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-orange-500/10 hover:text-white transition mb-1">
            <i class="fas fa-money-bill-wave w-5 text-center"></i>
            <span class="text-sm font-medium">Pagar Pedido</span>
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
</style>