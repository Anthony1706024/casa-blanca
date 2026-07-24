<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lista de Usuarios | Casa Blanca</title>

    <!-- Tailwind CSS vía CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            background: #0d0d0d;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

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

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(249, 115, 22, 0.15);
        }

        /* Badges de roles */
        .badge-admin {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        .badge-mozo {
            background: rgba(249, 115, 22, 0.2);
            color: #f97316;
        }
        .badge-cajera {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }
        .badge-gerente {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }
        .badge-recepcion {
            background: rgba(168, 85, 247, 0.2);
            color: #a855f7;
        }
    </style>
</head>
<body>

    <!-- ==========================================
         MENÚ DE NAVEGACIÓN - ADMIN
    ========================================== -->
    @include('admin.nav')

    <!-- ==========================================
         CONTENIDO PRINCIPAL
    ========================================== -->
    <div class="ml-64 p-8 fade-in">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-users text-orange-500 mr-3"></i>
                        Lista de Usuarios
                    </h1>
                    <p class="text-gray-400 mt-1">
                        Gestiona los usuarios del sistema
                    </p>
                </div>
                <a href="{{ route('admin.usuarios.create') }}" 
                   class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold transition flex items-center gap-2">
                    <i class="fas fa-user-plus"></i>
                    Nuevo Usuario
                </a>
            </div>

            <!-- Mensajes de éxito/error -->
            @if(session('success'))
                <div class="bg-green-500/20 border border-green-500/30 text-green-400 px-6 py-4 rounded-lg mb-6 flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-500"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-500/20 border border-red-500/30 text-red-400 px-6 py-4 rounded-lg mb-6 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Tabla de Usuarios -->
            <div class="bg-[#1a1a1a] rounded-lg p-6 border border-orange-500/20">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-orange-500/20">
                                <th class="pb-3 text-gray-400 font-semibold text-sm">ID</th>
                                <th class="pb-3 text-gray-400 font-semibold text-sm">Nombre</th>
                                <th class="pb-3 text-gray-400 font-semibold text-sm">Email</th>
                                <th class="pb-3 text-gray-400 font-semibold text-sm">Rol</th>
                                <th class="pb-3 text-gray-400 font-semibold text-sm">Fecha Registro</th>
                                <th class="pb-3 text-gray-400 font-semibold text-sm text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuarios as $usuario)
                            <tr class="border-b border-white/5 hover:bg-white/5 transition">
                                <td class="py-3 text-white">#{{ $usuario->id }}</td>
                                <td class="py-3 text-white font-medium">{{ $usuario->name }}</td>
                                <td class="py-3 text-gray-300">{{ $usuario->email }}</td>
                                <td class="py-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                        @if($usuario->role && $usuario->role->nombre == 'admin') badge-admin
                                        @elseif($usuario->role && $usuario->role->nombre == 'mozo') badge-mozo
                                        @elseif($usuario->role && $usuario->role->nombre == 'cajera') badge-cajera
                                        @elseif($usuario->role && $usuario->role->nombre == 'gerente') badge-gerente
                                        @elseif($usuario->role && $usuario->role->nombre == 'recepcion') badge-recepcion
                                        @else badge-admin
                                        @endif
                                    ">
                                        {{ $usuario->role ? ucfirst($usuario->role->nombre) : 'Sin Rol' }}
                                    </span>
                                </td>
                                <td class="py-3 text-gray-400 text-sm">
                                    {{ $usuario->created_at ? $usuario->created_at->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.usuarios.edit', $usuario->id) }}" 
                                           class="text-blue-400 hover:text-blue-300 transition px-3 py-1 rounded bg-blue-500/10 hover:bg-blue-500/20 text-sm">
                                            <i class="fas fa-edit mr-1"></i> Editar
                                        </a>
                                        @if($usuario->id != auth()->id())
                                        <form method="POST" action="{{ route('admin.usuarios.destroy', $usuario->id) }}" 
                                              onsubmit="return confirm('¿Estás seguro de eliminar este usuario?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-400 hover:text-red-300 transition px-3 py-1 rounded bg-red-500/10 hover:bg-red-500/20 text-sm">
                                                <i class="fas fa-trash mr-1"></i> Eliminar
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-400">
                                    <i class="fas fa-users text-4xl block mb-3 opacity-30"></i>
                                    No hay usuarios registrados
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="mt-6">
                    {{ $usuarios->links() }}
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