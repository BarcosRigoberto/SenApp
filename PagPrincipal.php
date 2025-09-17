
<?php
//Pagina principal (mapa de niveles)
include 'conn.php';
session_start();

// verificar sesion del usuario
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener nivel actual del usuario
$nivel_usuario = isset($_SESSION['user_id']) ? 1 : 1; // Default a nivel 1
$stmt = $conexion->prepare("SELECT User_Lvl FROM usuarios WHERE User_ID = ?");
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $nivel_usuario = $row['User_Lvl'];
    }
    $stmt->close();
}

// verificar niveles disponibles en la BD y mostrarlos
$result = $conexion->query("SELECT DISTINCT nivel FROM ejercicio ORDER BY nivel ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeñApp Niveles</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
    <header>
        <div class="header-content">
            <div class="header-titles">
                <h1>SeñApp</h1>
                <h2>Aprende Lenguaje de Señas</h2>
            </div>
            <div class="user-menu">
                <button class="user-button" onclick="toggleMenu()">
                    <?php echo $_SESSION['usuario']; ?>
                </button>
                <div class="user-dropdown" id="userDropdown">
                    <a href="logout.php" class="dropdown-item">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </header>
    
    <script>
        function toggleMenu() {
            document.getElementById('userDropdown').classList.toggle('show');
        }

        // Cerrar el menú si el usuario hace clic fuera de él
        window.onclick = function(event) {
            if (!event.target.matches('.user-button')) {
                var dropdowns = document.getElementsByClassName("user-dropdown");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
    
    <div id="contenido">
        <div class="niveles-lista">
            <?php while($nivel = $result->fetch_assoc()): ?>
                <div class="nivel-btn">
                    <a class="nivel-link" href="nivel.php?nivel=<?php echo $nivel['nivel']; ?>&leccion=1">
                        <?php echo $nivel['nivel']; ?>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>