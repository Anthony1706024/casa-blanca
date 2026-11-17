<!-- ==========================================
     MENÚ LATERAL - MOZO / CAJERA (RESPONSIVE)
     Recibe: $rol, $secciones = ['Título' => [[ruta, icono, texto], ...]]
========================================== -->
<button id="menuToggle" class="fixed top-4 left-4 z-50 p-2 rounded-lg bg-[#1a1a1a] border border-orange-500/20 text-white md:hidden">
    <i class="fas fa-bars text-xl"></i>
</button>
<div id="menuOverlay" class="fixed inset-0 bg-black/60 z-40 hidden md:hidden"></div>

<nav id="sidebarMenu" class="fixed top-0 left-0 h-screen w-64 bg-[#0d0d0d] border-r border-orange-500/20 overflow-y-auto z-50 transition-transform duration-300 -translate-x-full md:translate-x-0">
    <div class="p-6 border-b border-orange-500/20">
        <div class="flex items-center gap-3">
            <img src="/img/logo.webp" alt="Casa Blanca" class="w-10 h-10 object-contain">
            <div>
                <h2 class="text-white font-bold text-lg">CASA BLANCA</h2>
                <p class="text-orange-500 text-xs font-semibold">{{ $rol }}</p>
            </div>
        </div>
    </div>

    <div class="p-4">
        @foreach($secciones as $titulo => $enlaces)
            <p class="text-gray-600 text-xs uppercase tracking-wider font-semibold mt-4 mb-4">{{ $titulo }}</p>
            @foreach($enlaces as [$ruta, $icono, $texto])
                <a href="{{ route($ruta) }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs($ruta) ? 'bg-orange-500/10 text-orange-500' : 'text-gray-400 hover:bg-orange-500/10 hover:text-white' }} transition mb-1">
                    <i class="fas {{ $icono }} w-5 text-center"></i>
                    <span class="text-sm font-medium">{{ $texto }}</span>
                </a>
            @endforeach
        @endforeach

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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const menu = document.getElementById('sidebarMenu');
        const overlay = document.getElementById('menuOverlay');

        function alternarMenu() {
            menu.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        document.getElementById('menuToggle').addEventListener('click', alternarMenu);
        overlay.addEventListener('click', alternarMenu);
    });
</script>
