<?php
include 'conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$nivel = isset($_GET['nivel']) ? (int)$_GET['nivel'] : 1;
$user_id = $_SESSION['user_id'];
$todos_completados = isset($_GET['todos_completados']) ? true : false;

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

// Obtener todos los ejercicios del nivel
$query_ejercicios = "SELECT id_ej FROM ejercicio WHERE nivel = ?";
$stmt_ej = $conexion->prepare($query_ejercicios);
$stmt_ej->bind_param("i", $nivel);
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
$nivel_completado = ($total_completados >= $total_nivel);
$porcentaje = $nivel_completado ? 100 : round(($total_completados / $total_nivel) * 100);

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

// Solo actualizar nivel y dar bonus si es la primera vez que completa el nivel
$bonus_nivel = 0;
$subio_nivel = false;

if ($nivel_completado && $user_level == $nivel) {
    $bonus_nivel = 50;
    
    $update = "UPDATE usuarios SET User_Lvl = ?, User_Points = User_Points + ? WHERE User_ID = ?";
    $stmt_update = $conexion->prepare($update);
    $new_level = $nivel + 1;
    $stmt_update->bind_param("iii", $new_level, $bonus_nivel, $user_id);
    $stmt_update->execute();
    $stmt_update->close();
    
    $subio_nivel = true;
    $puntos_actuales += $bonus_nivel;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados Nivel <?php echo $nivel; ?> - SeñApp</title>
    <link rel="stylesheet" href="style.css">
</head>
</head>
<body>
    <div class="contenedor-resultados">
        <div class="mensaje-nivel-completado">
            <?php if ($nivel_completado): ?>
                <h1>🎉 ¡Nivel <?php echo $nivel; ?> Completado!</h1>
            <?php else: ?>
                <h1>Progreso en Nivel <?php echo $nivel; ?></h1>
            <?php endif; ?>
            
            <div class="progreso-bar">
                <div class="progreso-fill" style="width: <?php echo $porcentaje; ?>%">
                    <?php echo $porcentaje; ?>%
                </div>
            </div>
        </div>

        <div class="estadisticas">
            <div class="stat-item">
                <div class="stat-number"><?php echo $total_completados; ?></div>
                <div class="stat-label">Ejercicios Completados</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $ejercicios_incompletos; ?></div>
                <div class="stat-label">Ejercicios Pendientes</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $total_nivel; ?></div>
                <div class="stat-label">Total del Nivel</div>
            </div>
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
            <?php if ($nivel_completado): ?>
                <p style="font-size: 0.9em; color: #666; margin-top: 10px;">
                    (<?php echo $puntos_ejercicios; ?> pts por ejercicios + <?php echo $bonus_nivel; ?> pts bonus = <?php echo $puntos_ejercicios + $bonus_nivel; ?> pts ganados)
                </p>
            <?php endif; ?>
        </div>

        <?php if (!$nivel_completado && $ejercicios_incompletos > 0): ?>
            <div class="incompletos-warning">
                <strong>⚠️ Aún tienes <?php echo $ejercicios_incompletos; ?> ejercicio<?php echo $ejercicios_incompletos > 1 ? 's' : ''; ?> sin completar</strong>
                <p style="margin-top: 10px;">Completa los ejercicios restantes para ganar más puntos (10 pts c/u)</p>
            </div>
        <?php endif; ?>

        <div class="botones-navegacion">
            <a href="PagPrincipal.php" class="button button-secondary">
                Volver al mapa
            </a>
            
            <?php if ($nivel_completado): ?>
                <?php 
                // Verificar si existe el siguiente nivel
                $query_next = "SELECT COUNT(*) as existe FROM ejercicio WHERE nivel = ?";
                $stmt_next = $conexion->prepare($query_next);
                $next_nivel = $nivel + 1;
                $stmt_next->bind_param("i", $next_nivel);
                $stmt_next->execute();
                $existe_siguiente = $stmt_next->get_result()->fetch_assoc()['existe'] > 0;
                $stmt_next->close();
                ?>
                
                <?php if ($existe_siguiente): ?>
                    <a href="nivel.php?nivel=<?php echo $nivel + 1; ?>" class="button button-primary">
                        Siguiente Nivel →
                    </a>
                <?php else: ?>
                    <div class="mensaje-info">
                        🎊 ¡Felicitaciones! Has completado todos los niveles disponibles
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <a href="nivel.php?nivel=<?php echo $nivel; ?>" class="button button-primary">
                    Reintentar ejercicios →
                </a>
            <?php endif; ?>
        </div>
        
        <?php if ($todos_completados): ?>
            <div class="mensaje-info" style="margin-top: 20px;">
                ✨ Ya completaste todos los ejercicios de este nivel anteriormente
            </div>
        <?php endif; ?>
    </div>
</body>
</html>