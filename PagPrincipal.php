<?php
include 'conn.php';
// Consulta unidades únicas y sus niveles
$result = $conexion->query("SELECT DISTINCT unidad FROM ejercicio ORDER BY unidad ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SeñApp - Niveles</title>
</head>
<body>
    <header>
        <h1>SeñApp</h1>
        <h2>Aprende Lenguaje de Señas</h2>
    </header>
    
    <?php while($unidad = $result->fetch_assoc()): 
        // Obtener niveles para esta unidad
        $niveles = $conexion->query("SELECT DISTINCT nivel FROM ejercicio WHERE unidad = {$unidad['unidad']} ORDER BY nivel ASC");
    ?>
        <div class="unidad-container">
            <h3 class="unidad-titulo">Unidad <?php echo $unidad['unidad']; ?></h3>
            <div class="niveles-lista">
                <?php while($nivel = $niveles->fetch_assoc()): ?>
                    <div class="nivel-btn">
                        <a class="nivel-link" href="nivel.php?nivel=<?php echo $nivel['nivel']; ?>">
                            <?php echo $nivel['nivel']; ?>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endwhile; ?>


</html></body></body>
</html>