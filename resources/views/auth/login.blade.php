<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar sesión | Casa Blanca</title>

    <style>
        /* ============================
           CONFIGURACIÓN GENERAL
        ============================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            min-height: 100vh;
            background: #0d0d0d;
        }

        /* ============================
           CONTENEDOR PRINCIPAL
        ============================ */
        .login-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: 
                linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.75)),
                url('/img/entrada.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 20px;
        }

        /* ============================
           LOGO SUPERIOR - MÁS GRANDE
        ============================ */
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-container img {
            max-width: 220px;
            height: auto;
            display: block;
            margin: 0 auto 15px;
            filter: drop-shadow(0 4px 20px rgba(0, 0, 0, 0.5));
        }

        .logo-container h1 {
            color: #f97316;
            font-size: 38px;
            font-weight: 800;
            letter-spacing: 6px;
            text-shadow: 0 2px 15px rgba(0, 0, 0, 0.5);
            text-transform: uppercase;
        }

        .logo-container h1 span {
            color: #FFFFFF;
        }

        .logo-container p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 5px;
        }

        /* ============================
           FORMULARIO - TRANSPARENTE
        ============================ */
        .login-box {
            width: 100%;
            max-width: 440px;
            background: rgba(13, 13, 13, 0.75);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 50px 45px 40px;
            border: 1px solid rgba(249, 115, 22, 0.15);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ============================
           BIENVENIDA
        ============================ */
        .welcome {
            text-align: center;
            margin-bottom: 30px;
        }

        .welcome h3 {
            color: white;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .welcome p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }

        /* ============================
           ERRORES
        ============================ */
        .error-container {
            margin-bottom: 20px;
            padding: 12px 16px;
            border-radius: 10px;
            background: rgba(220, 38, 38, 0.15);
            border: 1px solid rgba(220, 38, 38, 0.25);
            color: #fca5a5;
            font-size: 14px;
        }

        .error {
            margin-top: 6px;
            color: #f87171;
            font-size: 13px;
        }

        /* ============================
           FORMULARIO
        ============================ */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            outline: none;
            font-size: 15px;
            color: white;
            transition: all 0.3s ease;
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .form-group input:focus {
            border-color: #f97316;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
        }

        /* ============================
           RECORDAR / CONTRASEÑA
        ============================ */
        .remember {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .remember label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 13px;
            cursor: pointer;
        }

        .remember input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #f97316;
            cursor: pointer;
        }

        .forgot-password {
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #f97316;
            text-decoration: underline;
        }

        /* ============================
           BOTÓN
        ============================ */
        .login-button {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 25px rgba(249, 115, 22, 0.3);
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 35px rgba(249, 115, 22, 0.4);
        }

        .login-button:active {
            transform: translateY(0);
        }

        /* ============================
           REGISTRO
        ============================ */
        .register {
            margin-top: 25px;
            text-align: center;
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        }

        .register a {
            color: #f97316;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .register a:hover {
            color: #fb923c;
            text-decoration: underline;
        }

        /* ============================
           FOOTER
        ============================ */
        .footer {
            margin-top: 35px;
            text-align: center;
            color: rgba(255, 255, 255, 0.25);
            font-size: 12px;
            letter-spacing: 1px;
        }

        /* ============================
           RESPONSIVE
        ============================ */
        @media (max-width: 768px) {
            .login-box {
                padding: 35px 25px 30px;
                margin: 10px;
                max-width: 100%;
            }

            .logo-container h1 {
                font-size: 30px;
                letter-spacing: 4px;
            }

            .logo-container img {
                max-width: 170px;
            }

            .welcome h3 {
                font-size: 20px;
            }
        }

        @media (max-width: 480px) {
            .login-box {
                padding: 25px 18px 20px;
            }

            .logo-container h1 {
                font-size: 24px;
                letter-spacing: 3px;
            }

            .logo-container img {
                max-width: 140px;
            }

            .login-button {
                padding: 14px;
                font-size: 14px;
            }

            .form-group input {
                padding: 12px 14px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <!-- LOGO - MÁS GRANDE -->
            <div class="logo-container">
                <img src="/img/logo.webp" alt="Casa Blanca" />
                <h1>CASA <span>BLANCA</span></h1>
                <p>Club Campestre</p>
            </div>

            <!-- BIENVENIDA -->
            <div class="welcome">
                <h3>Bienvenido</h3>
                <p>Ingresa tus correo para continuar</p>
            </div>

            <!-- ERRORES GENERALES -->
            @if ($errors->any())
                <div class="error-container">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- FORMULARIO -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- EMAIL -->
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="admin@casablanca.com"
                        required
                        autofocus
                        autocomplete="username"
                    >
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- CONTRASEÑA -->
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Ingresa tu contraseña"
                        required
                        autocomplete="current-password"
                    >
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- RECORDAR -->
                <div class="remember">
                    <label>
                        <input type="checkbox" name="remember">
                        Recordarme
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-password">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                <!-- BOTÓN -->
                <button type="submit" class="login-button">
                    INICIAR SESIÓN
                </button>
            </form>

            <!-- REGISTRO -->
            @if (Route::has('register'))
                <div class="register">
                    ¿No tienes una cuenta?
                    <a href="{{ route('register') }}">Regístrate</a>
                </div>
            @endif

            <!-- FOOTER -->
            <div class="footer">
                © {{ date('Y') }} Casa Blanca Club Campestre
            </div>
        </div>
    </div>
</body>
</html>