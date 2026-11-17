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
            background: #ffffff;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #1a1a1a;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
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

        /* Badges de roles - Versión clara */
        .badge-admin {
            background: #fee2e2;
            color: #dc2626;
        }
        .badge-mozo {
            background: #fed7aa;
            color: #ea580c;
        }
        .badge-cajera {
            background: #dcfce7;
            color: #16a34a;
        }
        .badge-gerente {
            background: #dbeafe;
            color: #2563eb;
        }
        .badge-recepcion {
            background: #f3e8ff;
            color: #9333ea;
        }
        
        /* Estilos para tabla en fondo blanco */
        .table-container {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .table-container table thead th {
            color: #6b7280;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
            padding: 12px 16px;
        }

        .table-container table tbody td {
            color: #1a1a1a;
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
        }

        .table-container table tbody tr:hover {
            background-color: #f9fafb;
        }

        /* Botones en versión clara */
        .btn-edit {
            background: #eff6ff;
            color: #2563eb;
        }
        .btn-edit:hover {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .btn-delete {
            background: #fef2f2;
            color: #dc2626;
        }
        .btn-delete:hover {
            background: #fee2e2;
            color: #b91c1c;
        }

        .btn-primary {
            background: #f97316;
            color: white;
        }
        .btn-primary:hover {
            background: #ea580c;
        }

        /* Mensajes de éxito/error en versión clara */
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #16a34a;
        }
        .alert-success i {
            color: #22c55e;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #dc2626;
        }
        .alert-error i {
            color: #ef4444;
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
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-users text-orange-500 mr-3"></i>
                        Lista de Usuarios
                    </h1>
                    <p class="text-gray-500 mt-1">
                        Gestiona los usuarios del sistema
                    </p>
                </div>
                <a href="{{ route('admin.usuarios.create') }}" 
                   class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold transition flex items-center gap-2 shadow-md hover:shadow-lg">
                    <i class="fas fa-user-plus"></i>
                    Nuevo Usuario
                </a>
            </div>

            <!-- Mensajes de éxito/error -->
            @if(session('success'))
                <div class="alert-success px-6 py-4 rounded-lg mb-6 flex items-center gap-3">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert-error px-6 py-4 rounded-lg mb-6 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Tabla de Usuarios -->
            <div class="table-container overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Fecha Registro</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuarios as $usuario)
                            <tr>
                                <td class="font-medium">#{{ $usuario->id }}</td>
                                <td class="font-medium">{{ $usuario->name }}</td>
                                <td>{{ $usuario->email }}</td>
                                <td>
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
                                <td class="text-gray-500 text-sm">
                                    {{ $usuario->created_at ? $usuario->created_at->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.usuarios.edit', $usuario->id) }}" 
                                           class="btn-edit px-3 py-1 rounded text-sm font-medium transition flex items-center gap-1">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        @if($usuario->id != auth()->id())
                                        <form method="POST" action="{{ route('admin.usuarios.destroy', $usuario->id) }}" 
                                              onsubmit="return confirm('¿Estás seguro de eliminar este usuario?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn-delete px-3 py-1 rounded text-sm font-medium transition flex items-center gap-1">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-500">
                                    <i class="fas fa-users text-4xl block mb-3 text-gray-300"></i>
                                    No hay usuarios registrados
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $usuarios->links() }}
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-gray-400 text-sm">
                © {{ date('Y') }} Casa Blanca Club Campestre. Todos los derechos reservados.
            </div>
        </div>
    </div>

</body>
</html>