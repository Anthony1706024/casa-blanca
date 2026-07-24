<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editar Usuario | Casa Blanca</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            background: #0d0d0d;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0d0d0d; }
        ::-webkit-scrollbar-thumb { background: #f97316; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #ea580c; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeIn 0.5s ease; }
    </style>
</head>
<body>

    @include('admin.nav')

    <div class="ml-64 p-8 fade-in">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        <i class="fas fa-user-edit text-orange-500 mr-3"></i>
                        Editar Usuario
                    </h1>
                    <p class="text-gray-400 mt-1">Modifica la información del usuario</p>
                </div>
                <a href="{{ route('admin.usuarios.index') }}" 
                   class="text-gray-400 hover:text-white transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>

            <!-- Formulario -->
            <div class="bg-[#1a1a1a] rounded-lg p-6 border border-orange-500/20">
                <form method="POST" action="{{ route('admin.usuarios.update', $usuario->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div>
                            <label class="block text-gray-300 mb-2 font-semibold">Nombre completo</label>
                            <input type="text" name="name" value="{{ old('name', $usuario->name) }}" 
                                   class="w-full px-4 py-3 bg-[#2a2a2a] border border-gray-700 rounded-lg text-white focus:border-orange-500 focus:outline-none transition"
                                   placeholder="Ej: Juan Pérez" required>
                            @error('name')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-gray-300 mb-2 font-semibold">Correo electrónico</label>
                            <input type="email" name="email" value="{{ old('email', $usuario->email) }}" 
                                   class="w-full px-4 py-3 bg-[#2a2a2a] border border-gray-700 rounded-lg text-white focus:border-orange-500 focus:outline-none transition"
                                   placeholder="Ej: usuario@casablanca.com" required>
                            @error('email')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contraseña (opcional) -->
                        <div>
                            <label class="block text-gray-300 mb-2 font-semibold">Nueva Contraseña</label>
                            <input type="password" name="password" 
                                   class="w-full px-4 py-3 bg-[#2a2a2a] border border-gray-700 rounded-lg text-white focus:border-orange-500 focus:outline-none transition"
                                   placeholder="Dejar en blanco para no cambiar">
                            @error('password')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-gray-500 text-xs mt-1">Mínimo 6 caracteres</p>
                        </div>

                        <!-- Rol -->
                        <div>
                            <label class="block text-gray-300 mb-2 font-semibold">Rol</label>
                            <select name="role_id" class="w-full px-4 py-3 bg-[#2a2a2a] border border-gray-700 rounded-lg text-white focus:border-orange-500 focus:outline-none transition" required>
                                <option value="">Seleccionar rol</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id }}" {{ old('role_id', $usuario->role_id) == $rol->id ? 'selected' : '' }}>
                                        {{ ucfirst($rol->nombre) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 mt-8">
                        <a href="{{ route('admin.usuarios.index') }}" 
                           class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-lg font-semibold transition">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-8 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-semibold transition flex items-center gap-2">
                            <i class="fas fa-save"></i> Actualizar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>