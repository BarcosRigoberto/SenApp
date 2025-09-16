<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header("Location: PagPrincipal.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="style.css">
    <title>SeñApp</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            font-family: Arial, sans-serif;
        }

        .welcome-layout {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .content-section {
            flex: 1;
            background-color: white;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 400px;
            max-width: 500px;
        }

        .logo-section {
            flex: 1;
            background: linear-gradient(135deg, #f46a13 0%, #b44704 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .logo-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .giant-logo {
            width: 300px;
            height: 300px;
            z-index: 1;
            filter: drop-shadow(0 20px 40px rgba(0,0,0,0.3));
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .bienvenida h1 {
            color: var(--primary-color);
            font-size: 3em;
            margin: 0 0 20px 0;
            font-weight: bold;
        }

        .app-description {
            color: #666;
            font-size: 1.2em;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .button-section {
            position: relative;
        }

        .btn-comenzar {
            width: 100%;
            padding: 18px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(244, 106, 19, 0.3);
        }

        .btn-comenzar:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(244, 106, 19, 0.4);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
            margin-top: 15px;
        }

        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: block;
            padding: 18px 25px;
            color: var(--text-color);
            text-decoration: none;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            font-size: 16px;
            font-weight: 500;
        }

        .dropdown-item:last-child {
            border-bottom: none;
            border-radius: 0 0 8px 8px;
        }

        .dropdown-item:first-child {
            border-radius: 8px 8px 0 0;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--primary-color);
            transform: translateX(5px);
        }

        .dropdown-item::after {
            content: '→';
            float: right;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .dropdown-item:hover::after {
            opacity: 1;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: transparent;
            z-index: 999;
            display: none;
        }

        .overlay.show {
            display: block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .welcome-layout {
                flex-direction: column;
            }
            
            .content-section {
                min-width: auto;
                max-width: none;
                padding: 40px 30px;
                order: 2;
            }
            
            .logo-section {
                order: 1;
                min-height: 300px;
            }
            
            .giant-logo {
                width: 200px;
                height: 200px;
            }
            
            .bienvenida h1 {
                font-size: 2.2em;
            }
            
            .app-description {
                font-size: 1.1em;
            }
        }

        @media (max-width: 480px) {
            .content-section {
                padding: 30px 20px;
            }
            
            .bienvenida h1 {
                font-size: 1.8em;
            }
            
            .giant-logo {
                width: 150px;
                height: 150px;
            }
            
            .btn-comenzar {
                padding: 15px;
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="welcome-layout">
        <!-- Sección de contenido (izquierda) -->
        <div class="content-section">
            <div class="bienvenida">
                <h1>Bienvenido a SeñApp</h1>
            </div>
            
            <p class="app-description">
                Aprende lenguaje de señas de manera interactiva y divertida. 
                Desarrolla nuevas habilidades de comunicación y conecta con la comunidad sorda.
                ¡Comienza tu aventura de aprendizaje hoy mismo!
            </p>
            
            <div class="button-section">
                <button class="btn-comenzar" id="btnComenzar">
                    Comenzar
                </button>
                
                <div class="dropdown-menu" id="dropdown">
                    <a href="Login.php" class="dropdown-item">Iniciar Sesión</a>
                    <a href="Registro.php" class="dropdown-item">Registrarse</a>
                </div>
            </div>
        </div>
        
        <!-- Sección del logo (derecha) -->
        <div class="logo-section">
            <img src="logoblanco.svg" alt="SeñApp Logo" class="giant-logo">
        </div>
    </div>
    
    <div class="overlay" id="overlay" onclick="closeDropdown()"></div>

    <script>
        // Función para mostrar/ocultar dropdown
        function toggleDropdown() {
            console.log('Botón clickeado'); // Debug
            const dropdown = document.getElementById('dropdown');
            const overlay = document.getElementById('overlay');
            
            if (dropdown && overlay) {
                if (dropdown.classList.contains('show')) {
                    closeDropdown();
                } else {
                    dropdown.classList.add('show');
                    overlay.classList.add('show');
                    console.log('Dropdown mostrado'); // Debug
                }
            } else {
                console.error('Elementos no encontrados');
            }
        }

        // Función para cerrar dropdown
        function closeDropdown() {
            const dropdown = document.getElementById('dropdown');
            const overlay = document.getElementById('overlay');
            
            if (dropdown && overlay) {
                dropdown.classList.remove('show');
                overlay.classList.remove('show');
                console.log('Dropdown cerrado'); // Debug
            }
        }

        // Asegurar que el DOM esté cargado
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM cargado');
            
            // Agregar event listener al botón
            const button = document.getElementById('btnComenzar');
            if (button) {
                button.addEventListener('click', toggleDropdown);
                console.log('Event listener agregado al botón');
            } else {
                console.error('Botón no encontrado');
            }
        });

        // Cerrar con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDropdown();
            }
        });
    </script>
</body>
</html>