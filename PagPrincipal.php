<?php
include 'conn.php';
// Consulta niveles
$result = $conexion->query("SELECT id FROM niveles ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeñApp - Mapa de Niveles</title>
    
</head>
<body>
    <header>
        <h1>SeñApp</h1>
        <h2>Aprende Lenguaje de Señas</h2>
    </header>
    <button><a href="Login.html">Iniciar Sesión</a></button>
    <button><a href="Registro.php">Registrarse</a></button>
    <h2>Mapa de Niveles</h2>
    <div class="niveles-lista">
        <?php while($row = $result->fetch_assoc()): ?>
            <a class="nivel-btn" href="nivel.php?id=<?php echo $row['id']; ?>">
             <?php echo $row['id']; ?> <br> 
            </a>
        <?php endwhile; ?>
    </div>
</body>
</html>