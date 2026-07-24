<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel Mozo | Casa Blanca</title>

    <!-- Tailwind CSS vía CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            background: #0d0d0d;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        /* Scrollbar personalizada */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0d0d0d;
        }
        ::-webkit-scrollbar-thumb {
            background: #f97316;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #ea580c;
        }

        /* Animaciones */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        /* Hover en tarjetas */
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(249, 115, 22, 0.15);
        }
    </style>
</head>
<body>

    <!-- ==========================================
         MENÚ DE NAVEGACIÓN - MOZO
    ========================================== -->
    @include('mozo.nav')

    <!-- ==========================================
         CONTENIDO PRINCIPAL
    ========================================== -->
    <div class="ml-64 p-8 fade-in">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">
                    <i class="fas fa-utensils text-orange-500 mr-3"></i>
                    Panel de Mozo
                </h1>
                <p class="text-gray-400 mt-1">
                    Gestiona los pedidos y servicio del restaurante
                </p>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-[#1a1a1a] rounded-lg p-6 border border-orange-500/20 card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Pedidos Activos</p>
                            <p class="text-white text-3xl font-bold mt-1">12</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-500/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-clipboard-list text-orange-500 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-orange-500 text-sm mt-3">
                        <i class="fas fa-clock mr-1"></i> 4 en preparación
                    </p>
                </div>

                <div class="bg-[#1a1a1a] rounded-lg p-6 border border-green-500/20 card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Pedidos Hoy</p>
                            <p class="text-white text-3xl font-bold mt-1">23</p>
                        </div>
                        <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-500 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-green-500 text-sm mt-3">
                        <i class="fas fa-arrow-up mr-1"></i> 8% más que ayer
                    </p>
                </div>

                <div class="bg-[#1a1a1a] rounded-lg p-6 border border-orange-500/20 card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Mesas Ocupadas</p>
                            <p class="text-white text-3xl font-bold mt-1">8</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-500/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-chair text-orange-500 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-yellow-500 text-sm mt-3">
                        <i class="fas fa-info-circle mr-1"></i> 5 mesas disponibles
                    </p>
                </div>

                <div class="bg-[#1a1a1a] rounded-lg p-6 border border-green-500/20 card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm">Pagos Pendientes</p>
                            <p class="text-white text-3xl font-bold mt-1">5</p>
                        </div>
                        <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-credit-card text-green-500 text-xl"></i>
                        </div>
                    </div>
                    <p class="text-red-500 text-sm mt-3">
                        <i class="fas fa-exclamation-circle mr-1"></i> $340.00 por cobrar
                    </p>
                </div>
            </div>

            <!-- Sección de Acciones Rápidas -->
            <div class="bg-[#1a1a1a] rounded-lg p-6 border border-orange-500/20">
                <h3 class="text-xl font-bold text-white mb-4">
                    <i class="fas fa-bolt text-orange-500 mr-2"></i>
                    Acciones Rápidas
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="#" class="bg-[#2a2a2a] p-6 rounded-lg text-center hover:border-orange-500 border border-transparent transition card-hover">
                        <i class="fas fa-plus-circle text-orange-500 text-3xl mb-3"></i>
                        <p class="text-white font-medium text-lg">Registrar Pedido</p>
                        <p class="text-gray-400 text-sm mt-1">Nuevo pedido en el restaurante</p>
                    </a>
                    <a href="#" class="bg-[#2a2a2a] p-6 rounded-lg text-center hover:border-green-500 border border-transparent transition card-hover">
                        <i class="fas fa-list text-green-500 text-3xl mb-3"></i>
                        <p class="text-white font-medium text-lg">Ver Pedidos</p>
                        <p class="text-gray-400 text-sm mt-1">Lista de todos los pedidos</p>
                    </a>
                    <a href="#" class="bg-[#2a2a2a] p-6 rounded-lg text-center hover:border-orange-500 border border-transparent transition card-hover">
                        <i class="fas fa-money-bill-wave text-orange-500 text-3xl mb-3"></i>
                        <p class="text-white font-medium text-lg">Pagar Pedido</p>
                        <p class="text-gray-400 text-sm mt-1">Procesar pagos de pedidos</p>
                    </a>
                </div>
            </div>

            <!-- Últimos Pedidos -->
            <div class="mt-8 bg-[#1a1a1a] rounded-lg p-6 border border-orange-500/20">
                <h3 class="text-xl font-bold text-white mb-4">
                    <i class="fas fa-clock text-orange-500 mr-2"></i>
                    Últimos Pedidos
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-orange-500/20">
                                <th class="pb-3 text-gray-400 font-semibold text-sm">N° Pedido</th>
                                <th class="pb-3 text-gray-400 font-semibold text-sm">Mesa</th>
                                <th class="pb-3 text-gray-400 font-semibold text-sm">Cliente</th>
                                <th class="pb-3 text-gray-400 font-semibold text-sm">Total</th>
                                <th class="pb-3 text-gray-400 font-semibold text-sm">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-white/5">
                                <td class="py-3 text-white">#001</td>
                                <td class="py-3 text-gray-300">Mesa 5</td>
                                <td class="py-3 text-gray-300">Juan Pérez</td>
                                <td class="py-3 text-white font-semibold">$45.00</td>
                                <td class="py-3">
                                    <span class="px-3 py-1 bg-yellow-500/20 text-yellow-500 rounded-full text-xs font-semibold">
                                        En preparación
                                    </span>
                                </td>
                            </tr>
                            <tr class="border-b border-white/5">
                                <td class="py-3 text-white">#002</td>
                                <td class="py-3 text-gray-300">Mesa 8</td>
                                <td class="py-3 text-gray-300">María García</td>
                                <td class="py-3 text-white font-semibold">$78.50</td>
                                <td class="py-3">
                                    <span class="px-3 py-1 bg-green-500/20 text-green-500 rounded-full text-xs font-semibold">
                                        Entregado
                                    </span>
                                </td>
                            </tr>
                            <tr class="border-b border-white/5">
                                <td class="py-3 text-white">#003</td>
                                <td class="py-3 text-gray-300">Mesa 3</td>
                                <td class="py-3 text-gray-300">Carlos Ruiz</td>
                                <td class="py-3 text-white font-semibold">$32.00</td>
                                <td class="py-3">
                                    <span class="px-3 py-1 bg-orange-500/20 text-orange-500 rounded-full text-xs font-semibold">
                                        Pendiente
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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