<?php
include 'conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

// Obtener el nivel del que viene el usuario
$nivel = isset($_GET['nivel']) ? (int)$_GET['nivel'] : 1;

// Obtener estadísticas del nivel
$query = "SELECT 
    nivel,
    COUNT(*) as total_ejercicios
FROM ejercicio 
WHERE nivel = ?
GROUP BY nivel";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $nivel);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();

// Obtener nivel actual del usuario
$query_user = "SELECT User_Lvl FROM usuarios WHERE User_ID = ?";
$stmt_user = $conexion->prepare($query_user);
$stmt_user->bind_param("i", $_SESSION['user_id']);
$stmt_user->execute();
$user_level = $stmt_user->get_result()->fetch_assoc()['User_Lvl'];

// Calcular estadísticas
$total_ejercicios = $stats['total_ejercicios'];
$nivel_completado = $user_level > $nivel;
$porcentaje = $nivel_completado ? 100 : 0;

// Si el usuario está en este nivel, actualizarlo al siguiente
if (!$nivel_completado && $user_level == $nivel) {
    $update = "UPDATE usuarios 
               SET User_Lvl = ? + 1
               WHERE User_ID = ? AND User_Lvl = ?";
    $stmt = $conexion->prepare($update);
    $stmt->bind_param("iii", $nivel, $_SESSION['user_id'], $nivel);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $nivel_completado = true;
        $porcentaje = 100;
    }
}

// Ya no necesitamos tracking de tiempo sin la tabla progreso_ejercicio
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados Nivel <?php echo $nivel; ?> - SeñApp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="contenedor-resultados">
        <div class="mensaje-nivel-completado">
            <h1>¡Nivel <?php echo $nivel; ?> Completado!</h1>
            <div class="puntos-ganados">
                <span class="puntos">+<?php echo $total_ejercicios * 10; ?></span>
                <span class="texto-puntos">puntos</span>
            </div>

        <div class="botones-navegacion">
            <a href="nivel.php?nivel=<?php echo $nivel + 1; ?>&leccion=1" class="button button-primary">
                Siguiente Nivel
            </a>
        </div>
    </div>
</body>
</html>