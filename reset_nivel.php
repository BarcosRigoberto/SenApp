<?php
session_start();
include 'conn.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$nivel = isset($_GET['nivel']) ? (int)$_GET['nivel'] : 0;

if ($nivel <= 0) {
    header("Location: PagPrincipal.php");
    exit();
}

// Función para obtener progreso del usuario
function obtenerProgreso($conexion, $user_id) {
    $query = "SELECT User_Progress FROM usuarios WHERE User_ID = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $progreso = $result['User_Progress'] ? json_decode($result['User_Progress'], true) : [];
    return is_array($progreso) ? $progreso : [];
}

// Procesar el reset si se confirma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    // Obtener todos los ejercicios del nivel
    $query_ejercicios = "SELECT id_ej FROM ejercicio WHERE nivel = ?";
    $stmt_ej = $conexion->prepare($query_ejercicios);
    $stmt_ej->bind_param("i", $nivel);
    $stmt_ej->execute();
    $result_ej = $stmt_ej->get_result();
    
    $ejercicios_ids = [];
    while ($row = $result_ej->fetch_assoc()) {
        $ejercicios_ids[] = $row['id_ej'];
    }
    $stmt_ej->close();
    
    if (!empty($ejercicios_ids)) {
        // Obtener progreso actual
        $progreso = obtenerProgreso($conexion, $user_id);
        
        // Filtrar los ejercicios del nivel que se está reseteando
        $progreso_nuevo = array_filter($progreso, function($ej_id) use ($ejercicios_ids) {
            return !in_array($ej_id, $ejercicios_ids);
        });
        
        // Re-indexar el array
        $progreso_nuevo = array_values($progreso_nuevo);
        
        // Actualizar en la base de datos
        $json_progreso = json_encode($progreso_nuevo);
        $update = "UPDATE usuarios SET User_Progress = ? WHERE User_ID = ?";
        $stmt_update = $conexion->prepare($update);
        $stmt_update->bind_param("si", $json_progreso, $user_id);
        $stmt_update->execute();
        $stmt_update->close();
    }
    
    // Redirigir al nivel
    header("Location: nivel.php?nivel=$nivel&mensaje=reset");
    exit();
}

// Obtener información del nivel
$query_info = "SELECT COUNT(*) as total_ejercicios FROM ejercicio WHERE nivel = ?";
$stmt_info = $conexion->prepare($query_info);
$stmt_info->bind_param("i", $nivel);
$stmt_info->execute();
$total_ejercicios = $stmt_info->get_result()->fetch_assoc()['total_ejercicios'];
$stmt_info->close();

// Obtener ejercicios completados del nivel
$query_ej_nivel = "SELECT id_ej FROM ejercicio WHERE nivel = ?";
$stmt_ej = $conexion->prepare($query_ej_nivel);
$stmt_ej->bind_param("i", $nivel);
$stmt_ej->execute();
$result_ej = $stmt_ej->get_result();

$ejercicios_nivel = [];
while ($row = $result_ej->fetch_assoc()) {
    $ejercicios_nivel[] = $row['id_ej'];
}
$stmt_ej->close();

// Contar completados
$progreso_usuario = obtenerProgreso($conexion, $user_id);
$ejercicios_completados = 0;
foreach ($ejercicios_nivel as $ej_id) {
    if (in_array($ej_id, $progreso_usuario)) {
        $ejercicios_completados++;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reiniciar Nivel <?php echo $nivel; ?> - SeñApp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="reset-container">
        <div class="warning-icon">⚠️</div>
        <h1>Reiniciar Nivel <?php echo $nivel; ?></h1>
        <p>
            ¿Estás seguro de que deseas reiniciar este nivel?
        </p>
        
        <div class="reset-info">
            <h3>Esta acción:</h3>
            <ul>
                <li>Borrará tu progreso actual en este nivel</li>
                <li>Perderas todos los puntos que hayas ganado en este nivel</li>
            </ul>
            <p class="nota-irreversible">
                <strong>Nota:</strong> Esta acción es irreversible.
            </p>
        </div>
        
        <form method="POST">
            <div class="reset-buttons">
                <a href="PagPrincipal.php" class="btn-reset btn-cancelar">
                    ← Cancelar
                </a>
                <button type="submit" name="confirmar" class="btn-reset btn-confirmar">
                    Sí, reiniciar nivel
                </button>
            </div>
        </form>
    </div>
</body>
</html>

