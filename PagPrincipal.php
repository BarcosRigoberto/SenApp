<?php
include 'conn.php';
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Consultar niveles disponibles
$result = $conexion->query("SELECT DISTINCT nivel FROM ejercicio ORDER BY nivel ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeñApp - Niveles</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>SeñApp</h1>
        <h2>Aprende Lenguaje de Señas</h2>
    </header>
    
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