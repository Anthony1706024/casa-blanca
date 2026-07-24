<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel Administrador | Casa Blanca</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body { background: #0d0d0d; font-family: 'Segoe UI', Arial, sans-serif; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0d0d0d; }
        ::-webkit-scrollbar-thumb { background: #f97316; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #ea580c; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeIn 0.5s ease; }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 40px rgba(249, 115, 22, 0.15); }
    </style>
</head>
<body>

    @include('admin.nav')

    <!-- ==========================================
         CONTENIDO PRINCIPAL
    ========================================== -->
    <div class="md:ml-64 p-4 md:p-8 fade-in">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">
                    <i class="fas fa-crown text-orange-500 mr-3"></i>
                    Panel de Administrador
                </h1>
                <p class="text-gray-400 mt-1">
                    Gestiona todo el sistema del club campestre
                </p>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
                <div class="bg-[#1a1a1a] rounded-lg p-6 border border-orange-500/20 card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Usuarios</p>
                            <p class="text-white text-3xl font-bold mt-1">24</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-500/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-users text-orange-500 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-green-500 text-sm mt-3">
                        <i class="fas fa-arrow-up mr-1"></i> 12% este mes
                    </p>
                </div>

                <div class="bg-[#1a1a1a] rounded-lg p-6 border border-green-500/20 card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Productos</p>
                            <p class="text-white text-3xl font-bold mt-1">156</p>
                        </div>
                        <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-box text-green-500 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-green-500 text-sm mt-3">
                        <i class="fas fa-arrow-up mr-1"></i> 8% este mes
                    </p>
                </div>

                <div class="bg-[#1a1a1a] rounded-lg p-6 border border-orange-500/20 card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Insumos</p>
                            <p class="text-white text-3xl font-bold mt-1">89</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-500/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-cubes text-orange-500 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-yellow-500 text-sm mt-3">
                        <i class="fas fa-exclamation-triangle mr-1"></i> 5 bajo stock
                    </p>
                </div>

                <div class="bg-[#1a1a1a] rounded-lg p-6 border border-green-500/20 card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Ventas Hoy</p>
                            <p class="text-white text-3xl font-bold mt-1">S/ 1,250</p>
                        </div>
                        <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-green-500 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-green-500 text-sm mt-3">
                        <i class="fas fa-arrow-up mr-1"></i> 23% vs ayer
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-gray-600 text-sm">
                © {{ date('Y') }} Casa Blanca Club Campestre. Todos los derechos reservados.
            </div>
        </div>
    </div>

</body>
</html>