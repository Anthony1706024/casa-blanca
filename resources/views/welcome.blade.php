<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Casa Blanca Club Campestre</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* Reset y base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Instrument Sans', sans-serif;
            background: #0d0d0d;
            color: #e0e0e0;
            min-height: 100vh;
        }

        :root {
            --orange: #f97316;
            --orange-dark: #ea580c;
            --orange-light: #fb923c;
            --orange-glow: rgba(249, 115, 22, 0.3);
            --green: #22c55e;
            --green-dark: #16a34a;
            --green-light: #4ade80;
            --green-glow: rgba(34, 197, 94, 0.3);
            --black: #0d0d0d;
            --black-light: #1a1a1a;
            --black-lighter: #2a2a2a;
        }

        /* ==========================================
           NAVEGACIÓN
        ========================================== */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 16px 40px;
            background: rgba(13, 13, 13, 0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(249, 115, 22, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .logo {
            font-size: 24px;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar .logo span {
            color: var(--orange);
        }

        .navbar .logo .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
            box-shadow: 0 4px 20px var(--orange-glow);
        }

        .navbar .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .navbar .nav-links a {
            color: #a0a0a0;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }

        .navbar .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--orange);
            transition: width 0.3s ease;
        }

        .navbar .nav-links a:hover::after {
            width: 100%;
        }

        .navbar .nav-links a:hover {
            color: var(--orange);
        }

        .navbar .nav-links .btn-nav {
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            color: white !important;
            padding: 10px 28px;
            border-radius: 10px;
            font-weight: 600;
            box-shadow: 0 4px 20px var(--orange-glow);
            transition: all 0.3s ease;
        }

        .navbar .nav-links .btn-nav::after {
            display: none;
        }

        .navbar .nav-links .btn-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px var(--orange-glow);
        }

        /* Hamburguesa */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: #a0a0a0;
            font-size: 24px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 12px 20px;
            }
            .navbar .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 70px;
                left: 0;
                right: 0;
                background: rgba(13, 13, 13, 0.98);
                padding: 20px;
                border-bottom: 1px solid rgba(249, 115, 22, 0.15);
                gap: 15px;
            }
            .navbar .nav-links.open {
                display: flex;
            }
            .menu-toggle {
                display: block;
            }
        }

        /* ==========================================
           HERO
        ========================================== */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 80px 40px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #0d0d0d 0%, #1a1a1a 100%);
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 12s ease-in-out infinite;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -5%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(34, 197, 94, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 16s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }
            50% {
                transform: translate(30px, -30px) scale(1.1);
            }
        }

        .hero-content {
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .hero-text {
            animation: fadeUp 1s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-text .badge {
            display: inline-block;
            background: rgba(249, 115, 22, 0.15);
            color: var(--orange);
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: 1px solid rgba(249, 115, 22, 0.2);
            margin-bottom: 20px;
        }

        .hero-text h1 {
            font-size: 64px;
            font-weight: 700;
            color: white;
            line-height: 1.1;
            margin-bottom: 20px;
        }

        .hero-text h1 .highlight {
            background: linear-gradient(135deg, var(--orange), var(--orange-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-text p {
            font-size: 18px;
            color: #a0a0a0;
            line-height: 1.8;
            margin-bottom: 30px;
            max-width: 500px;
        }

        .hero-text .btn-group {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            color: white;
            padding: 14px 36px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 30px var(--orange-glow);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 40px var(--orange-glow);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            padding: 14px 36px;
            border: 2px solid rgba(249, 115, 22, 0.3);
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-secondary:hover {
            background: rgba(249, 115, 22, 0.1);
            border-color: var(--orange);
            transform: translateY(-3px);
        }

        .hero-image {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            animation: fadeUp 1.2s ease;
        }

        .hero-card {
            background: linear-gradient(135deg, #1a1a1a, #2a2a2a);
            border-radius: 16px;
            padding: 30px 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .hero-card:hover {
            transform: translateY(-5px);
            border-color: var(--orange);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .hero-card .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .hero-card .title {
            color: white;
            font-size: 14px;
            font-weight: 600;
        }

        .hero-card .desc {
            color: #a0a0a0;
            font-size: 12px;
            margin-top: 4px;
        }

        .hero-card.big {
            grid-column: span 2;
            min-height: 180px;
        }

        .hero-card.big .icon {
            font-size: 60px;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 40px;
            padding-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .stat-item .stat-icon {
            color: var(--orange);
            font-size: 20px;
            margin-bottom: 4px;
        }

        .stat-item .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: white;
        }

        .stat-item .stat-number span {
            color: var(--orange);
        }

        .stat-item .stat-label {
            font-size: 13px;
            color: #a0a0a0;
        }

        @media (max-width: 1024px) {
            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 40px;
            }
            .hero-text p {
                margin-left: auto;
                margin-right: auto;
            }
            .hero-text .btn-group {
                justify-content: center;
            }
            .hero-stats {
                justify-items: center;
            }
            .hero-text h1 {
                font-size: 48px;
            }
            .hero-image {
                max-width: 400px;
                margin: 0 auto;
            }
        }

        @media (max-width: 768px) {
            .hero {
                padding: 100px 20px 60px;
            }
            .hero-text h1 {
                font-size: 36px;
            }
            .hero-image {
                grid-template-columns: 1fr 1fr;
                max-width: 100%;
            }
            .hero-card.big {
                grid-column: span 2;
            }
            .hero-stats {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }

        /* ==========================================
           SECCIONES
        ========================================== */
        .section {
            padding: 80px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header .label {
            color: var(--orange);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 12px;
        }

        .section-header h2 {
            font-family: 'Instrument Sans', sans-serif;
            font-size: 42px;
            color: white;
            font-weight: 700;
        }

        .section-header h2 span {
            color: var(--orange);
        }

        .section-header p {
            color: #a0a0a0;
            font-size: 16px;
            margin-top: 12px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* ==========================================
           CARACTERÍSTICAS
        ========================================== */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background: linear-gradient(135deg, #1a1a1a, #2a2a2a);
            padding: 36px 30px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--orange), var(--green), var(--orange));
            background-size: 200% 100%;
            opacity: 0;
            transition: opacity 0.3s ease;
            animation: gradientMove 3s ease infinite;
        }

        @keyframes gradientMove {
            0%,
            100% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            border-color: rgba(249, 115, 22, 0.2);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
        }

        .feature-card .icon {
            width: 70px;
            height: 70px;
            background: rgba(249, 115, 22, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 16px;
            transition: all 0.3s ease;
        }

        .feature-card:hover .icon {
            background: rgba(249, 115, 22, 0.2);
            transform: scale(1.1);
        }

        .feature-card h3 {
            color: white;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .feature-card p {
            color: #a0a0a0;
            font-size: 14px;
            line-height: 1.7;
        }

        /* ==========================================
           MENÚ
        ========================================== */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
        }

        .menu-item {
            background: linear-gradient(135deg, #1a1a1a, #2a2a2a);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .menu-item::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--orange), var(--green));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .menu-item:hover::after {
            transform: scaleX(1);
        }

        .menu-item:hover {
            transform: translateY(-5px);
            border-color: rgba(249, 115, 22, 0.2);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .menu-item .emoji {
            font-size: 48px;
            display: block;
            margin-bottom: 12px;
        }

        .menu-item h4 {
            color: white;
            font-size: 18px;
            font-weight: 600;
        }

        .menu-item .desc {
            color: #a0a0a0;
            font-size: 13px;
            margin-top: 4px;
        }

        .menu-item .price {
            color: var(--orange);
            font-size: 22px;
            font-weight: 700;
            margin-top: 10px;
            display: block;
        }

        /* ==========================================
           GALERÍA
        ========================================== */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .gallery-item {
            aspect-ratio: 1;
            background: linear-gradient(135deg, #1a1a1a, #2a2a2a);
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .gallery-item::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.2), rgba(34, 197, 94, 0.2));
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .gallery-item:hover::before {
            opacity: 1;
        }

        .gallery-item:hover {
            transform: scale(1.05);
            border-color: var(--orange);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
        }

        .gallery-item .emoji {
            font-size: 56px;
            position: relative;
            z-index: 1;
            transition: transform 0.4s ease;
        }

        .gallery-item:hover .emoji {
            transform: scale(1.2);
        }

        .gallery-item .label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 16px;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            color: white;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            transform: translateY(100%);
            transition: transform 0.4s ease;
        }

        .gallery-item:hover .label {
            transform: translateY(0);
        }

        .gallery-item.tall {
            grid-row: span 2;
        }

        .gallery-item.wide {
            grid-column: span 2;
        }

        @media (max-width: 1024px) {
            .gallery-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .gallery-grid {
                grid-template-columns: 1fr;
            }
            .gallery-item.tall,
            .gallery-item.wide {
                grid-row: span 1;
                grid-column: span 1;
            }
            .section {
                padding: 60px 20px;
            }
            .section-header h2 {
                font-size: 32px;
            }
        }

        /* ==========================================
           TESTIMONIOS
        ========================================== */
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .testimonial-card {
            background: linear-gradient(135deg, #1a1a1a, #2a2a2a);
            padding: 30px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            border-color: rgba(249, 115, 22, 0.2);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .testimonial-card .stars {
            color: var(--orange);
            font-size: 18px;
            margin-bottom: 12px;
            letter-spacing: 3px;
        }

        .testimonial-card blockquote {
            color: #e0e0e0;
            font-size: 15px;
            line-height: 1.7;
            margin-bottom: 16px;
            font-style: italic;
        }

        .testimonial-card .author {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .testimonial-card .author .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: 18px;
            flex-shrink: 0;
        }

        .testimonial-card .author .name {
            color: white;
            font-weight: 600;
            font-size: 15px;
        }

        .testimonial-card .author .role {
            color: #a0a0a0;
            font-size: 13px;
        }

        /* ==========================================
           CTA
        ========================================== */
        .cta-section {
            padding: 80px 40px;
            background: linear-gradient(135deg, #1a1a1a, #0d0d0d);
            border-top: 1px solid rgba(249, 115, 22, 0.1);
            border-bottom: 1px solid rgba(249, 115, 22, 0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 50%, rgba(249, 115, 22, 0.05) 0%, transparent 50%);
            animation: pulseGlow 10s ease-in-out infinite;
        }

        @keyframes pulseGlow {
            0%,
            100% {
                transform: scale(1);
                opacity: 0.5;
            }
            50% {
                transform: scale(1.2);
                opacity: 1;
            }
        }

        .cta-content {
            max-width: 700px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .cta-content h2 {
            font-family: 'Instrument Sans', sans-serif;
            font-size: 42px;
            color: white;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .cta-content h2 span {
            color: var(--orange);
        }

        .cta-content p {
            color: #a0a0a0;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .cta-content .btn-group {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .cta-section {
                padding: 60px 20px;
            }
            .cta-content h2 {
                font-size: 32px;
            }
        }

        /* ==========================================
           FOOTER
        ========================================== */
        .footer {
            padding: 40px 60px;
            max-width: 1400px;
            margin: 0 auto;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer .logo {
            font-size: 22px;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer .logo span {
            color: var(--orange);
        }

        .footer .logo .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
        }

        .footer .text {
            color: #a0a0a0;
            font-size: 13px;
        }

        .footer .social {
            display: flex;
            gap: 16px;
        }

        .footer .social a {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a0a0a0;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 18px;
        }

        .footer .social a:hover {
            color: var(--orange);
            background: rgba(249, 115, 22, 0.1);
            border-color: var(--orange);
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .footer {
                flex-direction: column;
                text-align: center;
                padding: 30px 20px;
            }
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--black);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--orange);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--orange-dark);
        }
    </style>
</head>
<body>

    <!-- ==========================================
         NAVEGACIÓN
    ========================================== -->
    <nav class="navbar" id="navbar">
        <a href="#" class="logo">
            <span class="logo-icon">🏠</span>
            Casa <span>Blanca</span>
        </a>
        <div class="nav-links" id="navLinks">
            <a href="#features">Instalaciones</a>
            <a href="#menu">Menú</a>
            <a href="#gallery">Galería</a>
            <a href="#testimonials">Testimonios</a>
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-nav">
                        <i class="fas fa-user"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-nav">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </a>
                @endauth
            @endif
        </div>
        <button class="menu-toggle" id="menuToggle" aria-label="Menú">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <!-- ==========================================
         HERO
    ========================================== -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <span class="badge"><i class="fas fa-crown"></i> Club Campestre</span>
                <h1>Bienvenido a<br><span class="highlight">Casa Blanca</span></h1>
                <p>
                    <i class="fas fa-swimmer" style="color:var(--orange);"></i> Disfruta de nuestra piscina,
                    <i class="fas fa-utensils" style="color:var(--orange);"></i> deléitate con nuestra gastronomía
                    y vive momentos inolvidables en familia.
                </p>
                <div class="btn-group">
                    <a href="#" class="btn-primary">
                        <i class="fas fa-calendar-check"></i> Reservar Mesa
                    </a>
                    <a href="#" class="btn-secondary">
                        <i class="fas fa-play-circle"></i> Ver Instalaciones
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-number">350<span>+</span></div>
                        <div class="stat-label">Familias Socias</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon"><i class="fas fa-utensils"></i></div>
                        <div class="stat-number">45</div>
                        <div class="stat-label">Platos en Menú</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                        <div class="stat-number">12</div>
                        <div class="stat-label">Eventos al Año</div>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-card big">
                    <div class="icon">🏊</div>
                    <div class="title">Piscina Principal</div>
                    <div class="desc">Climatizada con jacuzzi</div>
                </div>
                <div class="hero-card">
                    <div class="icon">🍽️</div>
                    <div class="title">Restaurante</div>
                    <div class="desc">Cocina casera</div>
                </div>
                <div class="hero-card">
                    <div class="icon">🌴</div>
                    <div class="title">Jardines</div>
                    <div class="desc">Áreas verdes</div>
                </div>
                <div class="hero-card">
                    <div class="icon">🍹</div>
                    <div class="title">Bar</div>
                    <div class="desc">Cócteles y snacks</div>
                </div>
                <div class="hero-card">
                    <div class="icon">🎪</div>
                    <div class="title">Eventos</div>
                    <div class="desc">Celebraciones</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================
         INSTALACIONES
    ========================================== -->
    <section id="features" class="section">
        <div class="section-header">
            <span class="label"><i class="fas fa-star"></i> Nuestras Instalaciones</span>
            <h2>Todo para tu <span>disfrute</span></h2>
            <p>Espacios diseñados para que pases momentos inolvidables en familia y con amigos.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="icon">🏊</div>
                <h3>Piscina</h3>
                <p>Piscina climatizada con área de chapoteo para niños, jacuzzi y zona de solárium con camastros.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🍽️</div>
                <h3>Restaurante</h3>
                <p>Cocina casera con los mejores ingredientes. Desde platos tradicionales hasta especialidades gourmet.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🌴</div>
                <h3>Jardines</h3>
                <p>Amplios jardines con zonas de descanso, ideal para paseos, lectura o simplemente relajarse.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🎪</div>
                <h3>Salón de Eventos</h3>
                <p>Espacio versátil para celebraciones familiares, cumpleaños, bodas y eventos empresariales.</p>
            </div>
            <div class="feature-card">
                <div class="icon">👨‍👩‍👧‍👦</div>
                <h3>Zona Infantil</h3>
                <p>Parque infantil con juegos seguros y monitores para que los más pequeños se diviertan.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🍹</div>
                <h3>Bar & Terraza</h3>
                <p>Bar con amplia variedad de cócteles, jugos naturales y snacks para disfrutar al aire libre.</p>
            </div>
        </div>
    </section>

    <!-- ==========================================
         MENÚ
    ========================================== -->
    <section id="menu" class="section" style="background:#0d0d0d; border-top:1px solid rgba(255,255,255,0.03); border-bottom:1px solid rgba(255,255,255,0.03);">
        <div class="section-header">
            <span class="label"><i class="fas fa-utensils"></i> Nuestro Menú</span>
            <h2>Delicias <span>gastronómicas</span></h2>
            <p>Platos preparados con ingredientes frescos y mucho amor.</p>
        </div>
        <div class="menu-grid">
            <div class="menu-item">
                <span class="emoji">🥩</span>
                <h4>Parrillada Mixta</h4>
                <p class="desc">Carnes a la parrilla con guarniciones</p>
                <span class="price">S/ 18.000</span>
            </div>
            <div class="menu-item">
                <span class="emoji">🐟</span>
                <h4>Pescado del Día</h4>
                <p class="desc">Fresco, a la plancha con salsa de mariscos</p>
                <span class="price">S/ 15.000</span>
            </div>
            <div class="menu-item">
                <span class="emoji">🍝</span>
                <h4>Pasta Alfredo</h4>
                <p class="desc">Fetuccini con salsa cremosa y pollo</p>
                <span class="price">S/ 12.000</span>
            </div>
            <div class="menu-item">
                <span class="emoji">🥗</span>
                <h4>Ensalada César</h4>
                <p class="desc">Lechuga, pollo, parmesano y aderezo</p>
                <span class="price">S/ 9.000</span>
            </div>
            <div class="menu-item">
                <span class="emoji">🍔</span>
                <h4>Hamburguesa Clásica</h4>
                <p class="desc">Con queso, lechuga, tomate y papas</p>
                <span class="price">S/ 10.000</span>
            </div>
            <div class="menu-item">
                <span class="emoji">🍰</span>
                <h4>Postres Caseros</h4>
                <p class="desc">Tortas, pasteles y helados artesanales</p>
                <span class="price">S/ 6.000</span>
            </div>
        </div>
    </section>

    <!-- ==========================================
         GALERÍA
    ========================================== -->
    <section id="gallery" class="section">
        <div class="section-header">
            <span class="label"><i class="fas fa-images"></i> Galería</span>
            <h2>Nuestros <span>Espacios</span></h2>
            <p>Cada rincón de Casa Blanca está pensado para tu bienestar.</p>
        </div>
        <div class="gallery-grid">
            <div class="gallery-item tall">
                <span class="emoji">🏊</span>
                <span class="label">Piscina Principal</span>
            </div>
            <div class="gallery-item wide">
                <span class="emoji">🍽️</span>
                <span class="label">Restaurante</span>
            </div>
            <div class="gallery-item">
                <span class="emoji">🌴</span>
                <span class="label">Jardines</span>
            </div>
            <div class="gallery-item">
                <span class="emoji">🍹</span>
                <span class="label">Terraza Bar</span>
            </div>
            <div class="gallery-item">
                <span class="emoji">🎪</span>
                <span class="label">Salón de Eventos</span>
            </div>
            <div class="gallery-item">
                <span class="emoji">👨‍👩‍👧‍👦</span>
                <span class="label">Zona Infantil</span>
            </div>
        </div>
    </section>

    <!-- ==========================================
         TESTIMONIOS
    ========================================== -->
    <section id="testimonials" class="section" style="background:#0d0d0d; border-top:1px solid rgba(255,255,255,0.03); border-bottom:1px solid rgba(255,255,255,0.03);">
        <div class="section-header">
            <span class="label"><i class="fas fa-quote-left"></i> Testimonios</span>
            <h2>Lo que dicen <span>nuestros socios</span></h2>
            <p>La experiencia de quienes ya disfrutan de Casa Blanca.</p>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="stars">★★★★★</div>
                <blockquote>"La piscina es increíble y la comida del restaurante es deliciosa. Mis hijos se divierten muchísimo en la zona infantil."</blockquote>
                <div class="author">
                    <div class="avatar">MG</div>
                    <div>
                        <div class="name">María González</div>
                        <div class="role">Socia desde 2022</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="stars">★★★★★</div>
                <blockquote>"Un lugar perfecto para compartir en familia. Los jardines son hermosos y el ambiente es muy acogedor."</blockquote>
                <div class="author">
                    <div class="avatar">JR</div>
                    <div>
                        <div class="name">Juan Rodríguez</div>
                        <div class="role">Socio desde 2021</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="stars">★★★★★</div>
                <blockquote>"La atención es excelente y los precios son muy accesibles. Sin duda el mejor club campestre de la zona."</blockquote>
                <div class="author">
                    <div class="avatar">AL</div>
                    <div>
                        <div class="name">Ana López</div>
                        <div class="role">Socia desde 2023</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================
         CTA
    ========================================== -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>¿Listo para vivir la <span>experiencia</span> Casa Blanca?</h2>
            <p><i class="fas fa-heart" style="color:var(--orange);"></i> Únete a nuestra familia y disfruta de días inolvidables junto a los tuyos.</p>
            <div class="btn-group">
                <a href="#" class="btn-primary">
                    <i class="fas fa-calendar-plus"></i> Reservar Mesa
                </a>
                <a href="#" class="btn-secondary">
                    <i class="fas fa-info-circle"></i> Conocer Más
                </a>
            </div>
        </div>
    </section>

    <!-- ==========================================
         FOOTER
    ========================================== -->
    <footer class="footer">
        <div>
            <div class="logo">
                <span class="logo-icon">🏠</span>
                Casa <span>Blanca</span>
            </div>
            <div class="text" style="margin-top:4px;">Club Campestre</div>
        </div>
        <div class="text">
            <i class="far fa-copyright"></i> {{ date('Y') }} Casa Blanca Club. Todos los derechos reservados.
        </div>
        <div class="social">
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
        </div>
    </footer>

    <!-- ==========================================
         SCRIPTS
    ========================================== -->
    <script>
        // Menú hamburguesa
        const menuToggle = document.getElementById('menuToggle');
        const navLinks = document.getElementById('navLinks');

        menuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('open');
            const icon = this.querySelector('i');
            if (navLinks.classList.contains('open')) {
                icon.className = 'fas fa-times';
            } else {
                icon.className = 'fas fa-bars';
            }
        });

        // Cerrar menú al hacer click en un enlace (móvil)
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    navLinks.classList.remove('open');
                    document.querySelector('.menu-toggle i').className = 'fas fa-bars';
                }
            });
        });
    </script>
</body>
</html>