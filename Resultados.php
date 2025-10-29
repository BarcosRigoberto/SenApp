<?php
include 'conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$nivel = isset($_GET['nivel']) ? (int)$_GET['nivel'] : 1;
$unidad = isset($_GET['unidad']) ? trim($_GET['unidad']) : '';
$user_id = $_SESSION['user_id'];
$todos_completados = isset($_GET['todos_completados']) ? true : false;

// Validar que tengamos unidad
if (empty($unidad)) {
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

// Obtener progreso del usuario
$progreso_usuario = obtenerProgreso($conexion, $user_id);

// Obtener todos los ejercicios de la unidad y nivel específico
$query_ejercicios = "SELECT id_ej FROM ejercicio WHERE nivel = ? AND unidad = ?";
$stmt_ej = $conexion->prepare($query_ejercicios);
$stmt_ej->bind_param("is", $nivel, $unidad);
$stmt_ej->execute();
$result_ej = $stmt_ej->get_result();

$ejercicios_nivel = [];
while ($row = $result_ej->fetch_assoc()) {
    $ejercicios_nivel[] = $row['id_ej'];
}
$stmt_ej->close();

// Contar completados del nivel
$total_completados = 0;
foreach ($ejercicios_nivel as $ej_id) {
    if (in_array($ej_id, $progreso_usuario)) {
        $total_completados++;
    }
}

$total_nivel = count($ejercicios_nivel);
$ejercicios_incompletos = $total_nivel - $total_completados;
$nivel_completado = ($total_completados >= $total_nivel && $total_nivel > 0);
$porcentaje = ($total_nivel > 0) ? round(($total_completados / $total_nivel) * 100) : 0;

// Obtener nivel actual del usuario y puntos actuales
$query_user = "SELECT User_Lvl, User_Points FROM usuarios WHERE User_ID = ?";
$stmt_user = $conexion->prepare($query_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();
$user_level = $user_data['User_Lvl'];
$puntos_actuales = $user_data['User_Points'];
$stmt_user->close();

// Calcular puntos ganados en esta sesión (10 por ejercicio completado)
$puntos_ejercicios = $total_completados * 10;

// Solo dar bonus si es la primera vez que completa la unidad
$bonus_nivel = 0;
$subio_nivel = false;

if ($nivel_completado && $user_level == $nivel) {
    $bonus_nivel = 50;
    
    // Verificar si hay más unidades en este nivel antes de subir
    $query_check_unidades = "SELECT COUNT(DISTINCT unidad) as total_unidades,
                             COUNT(DISTINCT CASE WHEN id_ej IN (" . implode(',', array_fill(0, count($progreso_usuario), '?')) . ") THEN unidad END) as unidades_completas
                             FROM ejercicio WHERE nivel = ?";
    $stmt_check = $conexion->prepare($query_check_unidades);
    
    $types = str_repeat('i', count($progreso_usuario)) . 'i';
    $params = array_merge($progreso_usuario, [$nivel]);
    $stmt_check->bind_param($types, ...$params);
    $stmt_check->execute();
    $unidades_data = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();
    
    // Solo subir de nivel si completó TODAS las unidades del nivel
    if ($unidades_data['total_unidades'] == $unidades_data['unidades_completas']) {
        $update = "UPDATE usuarios SET User_Lvl = ?, User_Points = User_Points + ? WHERE User_ID = ?";
        $stmt_update = $conexion->prepare($update);
        $new_level = $nivel + 1;
        $stmt_update->bind_param("iii", $new_level, $bonus_nivel, $user_id);
        $stmt_update->execute();
        $stmt_update->close();
        
        $subio_nivel = true;
        $puntos_actuales += $bonus_nivel;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados - <?php echo htmlspecialchars($unidad); ?> - SeñApp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="contenedor-resultados">
        <div class="mensaje-nivel-completado">
            <?php if ($nivel_completado): ?>
                <h1>🎉 ¡Unidad Completada!</h1>
                <h2><?php echo htmlspecialchars($unidad); ?> - Nivel <?php echo $nivel; ?></h2>
            <?php else: ?>
                <h1>Progreso en <?php echo htmlspecialchars($unidad); ?></h1>
                <h2>Nivel <?php echo $nivel; ?></h2>
            <?php endif; ?>
            
            <div class="progreso-bar">
                <div class="progreso-fill" style="width: <?php echo $porcentaje; ?>%">
                    <?php echo $porcentaje; ?>%
                </div>
            </div>
        </div>

        <div class="estadisticas">
            
        </div>

        <?php if ($subio_nivel): ?>
            <div class="felicitaciones">
                <h2>🏆 ¡Subiste de Nivel!</h2>
                <p style="font-size: 1.2em; margin: 10px 0;">Ahora estás en el Nivel <?php echo $new_level; ?></p>
            </div>
            
            <div class="puntos-ganados">
                <span class="puntos">+<?php echo $bonus_nivel; ?></span>
                <span class="texto-puntos">puntos de bonus por completar el nivel</span>
            </div>
        <?php endif; ?>
        
        <div class="puntos-totales">
            <p><strong>Puntos totales:</strong> <?php echo $puntos_actuales; ?> pts</p>
            <p><strong>Ejercicios completados:</strong> <?php echo $total_completados; ?>/<?php echo $total_nivel; ?></p>
        </div>

        <?php if (!$nivel_completado && $ejercicios_incompletos > 0): ?>
            <div class="incompletos-warning">
                <strong>⚠️ Aún tienes <?php echo $ejercicios_incompletos; ?> ejercicio<?php echo $ejercicios_incompletos > 1 ? 's' : ''; ?> sin completar</strong>
                <p style="margin-top: 10px;">Completa los ejercicios restantes para ganar más puntos</p>
            </div>
        <?php endif; ?>

        <div class="botones-navegacion">
            <?php if ($nivel_completado): ?>
                <?php 
                // CORRECCIÓN: Obtener la siguiente unidad/nivel
                // Ordenamos PRIMERO por unidad, LUEGO por nivel (para mantener unidades juntas)
                $query_all_units = "SELECT DISTINCT unidad, nivel FROM ejercicio ORDER BY unidad ASC, nivel ASC";
                $result_all = $conexion->query($query_all_units);
                
                $todas_unidades = [];
                while ($row = $result_all->fetch_assoc()) {
                    $todas_unidades[] = $row;
                }
                
                // Buscar la posición actual
                $posicion_actual = -1;
                for ($i = 0; $i < count($todas_unidades); $i++) {
                    if ($todas_unidades[$i]['nivel'] == $nivel && $todas_unidades[$i]['unidad'] == $unidad) {
                        $posicion_actual = $i;
                        break;
                    }
                }
                
                // Obtener la siguiente unidad/nivel
                $siguiente = null;
                if ($posicion_actual >= 0 && $posicion_actual < count($todas_unidades) - 1) {
                    $siguiente = $todas_unidades[$posicion_actual + 1];
                }
                ?>
                
                <?php if ($siguiente): ?>
                    <a href="nivel.php?nivel=<?php echo $siguiente['nivel']; ?>&unidad=<?php echo urlencode($siguiente['unidad']); ?>" class="button button-primary">
                        Siguiente Nivel →
                    </a>
                <?php endif; ?>
                <a href="nivel.php?nivel=<?php echo $nivel; ?>&unidad=<?php echo urlencode($unidad); ?>" class="button button-reset">
                    Reiniciar Nivel
                </a>
            <?php endif; ?>
            <a href="PagPrincipal.php" class="button button-secondary">
                Volver al mapa
            </a>
        </div>
    </div>
</body>
</html>