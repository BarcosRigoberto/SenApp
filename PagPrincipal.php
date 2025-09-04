<?php
include 'conn.php';
// Consulta niveles
$result = $conexion->query("SELECT id FROM nivel ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>SeñApp - Mapa de Niveles</title>
    
</head>
<body>
    <header>
        <h1>SeñApp</h1>
        <h2>Aprende Lenguaje de Señas</h2>
    </header>
    <div class="niveles-lista">
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="nivel-btn">
                <a class="nivel-link" href="nivel.php?id=<?php echo $row['id']; ?>">
                    <?php echo $row['id']; ?>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>