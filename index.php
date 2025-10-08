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
</head>
<body>
    <div class="welcome-layout">
        <!-- Sección de contenido (izquierda) -->
        <div class="content-section">
            <div class="bienvenida">
                <h1>Bienvenido a SeñApp</h1>
                <p class="app-description">
                    Aprende lenguaje de señas de forma divertida y efectiva.
                    ¡Comienza tu viaje de aprendizaje hoy mismo!
                </p>
            </div>
            
            <div class="button-section">
                <button class="btn-comenzar" onclick="toggleDropdown()">Comenzar</button>
                <div class="dropdown-menu" id="dropdownMenu">
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
    
    <div class="overlay" id="overlay"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('overlay').addEventListener('click', function() {
                closeDropdown();
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeDropdown();
                }
            });
        });

        function toggleDropdown() {
            const dropdown = document.getElementById('dropdownMenu');
            const overlay = document.getElementById('overlay');
            dropdown.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        function closeDropdown() {
            const dropdown = document.getElementById('dropdownMenu');
            const overlay = document.getElementById('overlay');
            dropdown.classList.remove('show');
            overlay.classList.remove('show');
        }
    </script>
</body>
</html>