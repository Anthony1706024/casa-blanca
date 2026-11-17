<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Crear Usuario | Casa Blanca</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            background: #f8fafc;
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
        .fade-in { animation: fadeIn 0.5s ease; }
        
        /* Estilos para formulario en fondo blanco */
        .form-container {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .form-container label {
            color: #374151;
            font-weight: 600;
        }
        
        .form-input {
            background: #f9fafb;
            border: 1px solid #d1d5db;
            color: #1a1a1a;
            width: 100%;
            padding: 12px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            border-color: #f97316;
            outline: none;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
            background: #ffffff;
        }
        
        .form-input::placeholder {
            color: #9ca3af;
        }
        
        .form-select {
            background: #f9fafb;
            border: 1px solid #d1d5db;
            color: #1a1a1a;
            width: 100%;
            padding: 12px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 20px;
        }
        
        .form-select:focus {
            border-color: #f97316;
            outline: none;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
            background-color: #ffffff;
        }
        
        .form-select option {
            background: #ffffff;
            color: #1a1a1a;
        }
        
        .btn-cancel {
            background: #f3f4f6;
            color: #374151;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: #e5e7eb;
            color: #1a1a1a;
        }
        
        .btn-submit {
            background: #f97316;
            color: white;
            padding: 12px 32px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            background: #ea580c;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }
        
        .error-message {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 4px;
        }
        
        .header-title {
            color: #1a1a1a;
        }
        
        .header-subtitle {
            color: #6b7280;
        }
        
        .back-link {
            color: #6b7280;
        }
        
        .back-link:hover {
            color: #1a1a1a;
        }
    </style>
</head>
<body>

    @include('admin.nav')

    <div class="ml-64 p-8 fade-in">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold header-title">
                        <i class="fas fa-user-plus text-orange-500 mr-3"></i>
                        Crear Usuario
                    </h1>
                    <p class="header-subtitle mt-1">Registra un nuevo usuario en el sistema</p>
                </div>
                <a href="{{ route('admin.usuarios.index') }}" 
                   class="back-link transition flex items-center gap-2 font-medium">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>

            <!-- Formulario -->
            <div class="form-container p-6">
                <form method="POST" action="{{ route('admin.usuarios.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div>
                            <label class="block mb-2">Nombre completo</label>
                            <input type="text" name="name" value="{{ old('name') }}" 
                                   class="form-input"
                                   placeholder="Ej: Juan Pérez" required>
                            @error('name')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block mb-2">Correo electrónico</label>
                            <input type="email" name="email" value="{{ old('email') }}" 
                                   class="form-input"
                                   placeholder="Ej: usuario@casablanca.com" required>
                            @error('email')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contraseña -->
                        <div>
                            <label class="block mb-2">Contraseña</label>
                            <input type="password" name="password" 
                                   class="form-input"
                                   placeholder="Mínimo 6 caracteres" required>
                            @error('password')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Rol -->
                        <div>
                            <label class="block mb-2">Rol</label>
                            <select name="role_id" class="form-select" required>
                                <option value="">Seleccionar rol</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id }}" {{ old('role_id') == $rol->id ? 'selected' : '' }}>
                                        {{ ucfirst($rol->nombre) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 mt-8">
                        <a href="{{ route('admin.usuarios.index') }}" 
                           class="btn-cancel">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="btn-submit flex items-center gap-2">
                            <i class="fas fa-save"></i> Guardar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>