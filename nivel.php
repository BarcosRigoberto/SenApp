<?php
session_start();
include 'conn.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$nivel = isset($_GET['nivel']) ? (int)$_GET['nivel'] : 1;
$unidad = isset($_GET['unidad']) ? trim($_GET['unidad']) : '';
$user_id = $_SESSION['user_id'];
$ejercicio_actual = isset($_GET['ejercicio']) ? (int)$_GET['ejercicio'] : 0;

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

// Función para actualizar el JSON de progreso y dar puntos SOLO la primera vez
function actualizarProgresoJSON($conexion, $user_id, $ejercicio_id) {
    $progreso = obtenerProgreso($conexion, $user_id);
    
    // Verificar si el ejercicio NO estaba completado antes
    if (!in_array($ejercicio_id, $progreso)) {
        $progreso[] = $ejercicio_id;
        
        $json_progreso = json_encode($progreso);
        
        // Actualizar JSON y sumar 10 puntos
        $update = "UPDATE usuarios SET User_Progress = ?, User_Points = User_Points + 10 WHERE User_ID = ?";
        $stmt_update = $conexion->prepare($update);
        $stmt_update->bind_param("si", $json_progreso, $user_id);
        $stmt_update->execute();
        $stmt_update->close();
        
        return true; // Nuevo ejercicio completado, se otorgaron 10 puntos
    }
    
    return false; // Ya estaba completado, no se otorgan puntos
}

// Obtener progreso del usuario
$progreso_usuario = obtenerProgreso($conexion, $user_id);

// Obtener TODOS los ejercicios del nivel y unidad específica (sin filtrar)
$query_todos = "SELECT e.id_ej, e.nivel, e.unidad, e.rtaAcorrect, e.rtaB, e.rtaC, e.rtaD, e.video, e.type
                FROM ejercicio e
                WHERE e.nivel = ? AND e.unidad = ?
                ORDER BY e.id_ej ASC";
$stmt_todos = $conexion->prepare($query_todos);
$stmt_todos->bind_param("is", $nivel, $unidad);
$stmt_todos->execute();
$result_todos = $stmt_todos->get_result();

$todos_ejercicios = [];
while ($row = $result_todos->fetch_assoc()) {
    $todos_ejercicios[] = $row;
}
$stmt_todos->close();

if (empty($todos_ejercicios)) {
    echo "No hay ejercicios disponibles para este nivel y unidad.";
    exit;
}

$total_ejercicios = count($todos_ejercicios);

// Validar índice del ejercicio actual
if ($ejercicio_actual < 0 || $ejercicio_actual >= $total_ejercicios) {
    $ejercicio_actual = 0;
}

// Obtener el ejercicio actual
$ejercicio = $todos_ejercicios[$ejercicio_actual];

// Contar ejercicios ya completados
$total_completados = 0;
foreach ($todos_ejercicios as $ej) {
    if (in_array($ej['id_ej'], $progreso_usuario)) {
        $total_completados++;
    }
}

// Variable para controlar si mostrar resultado correcto
$mostrar_correcto = false;
$puntos_otorgados = false;

// Procesar respuesta tipo Escribir
if(isset($_POST['respuesta']) && $ejercicio['type'] == 'Escribir') {
    $respuesta_usuario = strtolower(trim($_POST['respuesta']));
    $respuesta_correcta = strtolower(trim($ejercicio['rtaAcorrect']));
    
    // Normalizar caracteres especiales
    $respuesta_usuario = iconv('UTF-8', 'ASCII//TRANSLIT', $respuesta_usuario);
    $respuesta_correcta = iconv('UTF-8', 'ASCII//TRANSLIT', $respuesta_correcta);
    
    $esCorrecta = ($respuesta_usuario === $respuesta_correcta);

    if ($esCorrecta) {
        // Actualizar progreso (solo da puntos si es primera vez)
        $puntos_otorgados = actualizarProgresoJSON($conexion, $user_id, $ejercicio['id_ej']);
        $mostrar_correcto = true;
    }
}

// Procesar respuesta tipo Elegir
if(isset($_POST['opcion']) && $ejercicio['type'] == 'Elegir') {
    $esCorrecta = ($_POST['opcion'] === $ejercicio['rtaAcorrect']);
    
    if ($esCorrecta) {
        // Actualizar progreso (solo da puntos si es primera vez)
        $puntos_otorgados = actualizarProgresoJSON($conexion, $user_id, $ejercicio['id_ej']);
        $mostrar_correcto = true;
    }
}

// Preparar opciones para tipo Elegir
if($ejercicio['type'] == 'Elegir') {
    $opciones = array();
    if(!empty($ejercicio['rtaAcorrect'])) $opciones[] = $ejercicio['rtaAcorrect'];
    if(!empty($ejercicio['rtaB'])) $opciones[] = $ejercicio['rtaB'];
    if(!empty($ejercicio['rtaC'])) $opciones[] = $ejercicio['rtaC'];
    if(!empty($ejercicio['rtaD'])) $opciones[] = $ejercicio['rtaD'];
    shuffle($opciones);
}

// Verificar si este ejercicio ya fue completado antes
$ya_completado = in_array($ejercicio['id_ej'], $progreso_usuario);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nivel <?php echo $nivel; ?> - <?php echo htmlspecialchars($unidad); ?> - SeñApp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="header-nav">
            <a href="PagPrincipal.php" class="btn-volver">&larr; Volver al mapa</a>
            <div class="progreso-detallado">
                <div class="progreso">
                    Ejercicio <?php echo ($ejercicio_actual + 1); ?>/<?php echo $total_ejercicios; ?>
                </div>
            </div>
        </div>
        <h1>Nivel <?php echo $nivel; ?> - <?php echo htmlspecialchars($unidad); ?></h1>
        <div class="progreso-info" style="text-align: center;">
            <?php 
            $porcentaje = round(($total_completados / $total_ejercicios) * 100);
            ?>
            Progreso: <?php echo $total_completados; ?>/<?php echo $total_ejercicios; ?> (<?php echo $porcentaje; ?>%)
        </div>
    </header>

    <div class="contenedor-nivel">
        <?php if ($mostrar_correcto): ?>
            <div class="mensaje-transitorio" style="background-color: var(--green); margin-bottom: 20px;">
                ¡Correcto!
            </div>
            
            <?php if ($puntos_otorgados): ?>
                <div class="mensaje-puntos">
                    🎉 ¡+10 puntos! 🎉
                </div>
            <?php else: ?>
                
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="gif-container">
            <img src="videos/<?php echo $ejercicio['video']; ?>" alt="Seña animada" class="gif-seña">
        </div>

        <?php if ($mostrar_correcto): ?>
            <!-- Mostrar botón para continuar -->
            <div class="navegacion" style="margin-top: 30px;">
                <?php if ($ejercicio_actual >= $total_ejercicios - 1): ?>
                    <!-- Es el último ejercicio, ir a resultados -->
                    <a href="Resultados.php?nivel=<?php echo $nivel; ?>&unidad=<?php echo urlencode($unidad); ?>" 
                       class="btn-siguiente">
                        Ver Resultados 🎯
                    </a>
                <?php else: ?>
                    <!-- Ir al siguiente ejercicio -->
                    <a href="nivel.php?nivel=<?php echo $nivel; ?>&unidad=<?php echo urlencode($unidad); ?>&ejercicio=<?php echo ($ejercicio_actual + 1); ?>" 
                       class="btn-siguiente">
                        Continuar →
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Mostrar formulario si no ha respondido correctamente -->
            <?php if($ejercicio['type'] == 'Escribir'): ?>
                <div class="ejercicio-container">
                    <form method="POST" class="form-respuesta">
                        <input type="text" 
                               name="respuesta" 
                               placeholder="Escribe tu respuesta" 
                               required 
                               class="input-respuesta"
                               autocomplete="off">
                        <button type="submit" class="btn-responder">Responder</button>
                    </form>
                    
                    <?php if(isset($esCorrecta) && !$esCorrecta): ?>
                        <div class="mensaje-resultado incorrecto">
                            ❌ Incorrecto. Intenta de nuevo.
                        </div>
                        <div class="navegacion">
                            <a href="nivel.php?nivel=<?php echo $nivel; ?>&unidad=<?php echo urlencode($unidad); ?>&ejercicio=<?php echo $ejercicio_actual; ?>" class="btn-siguiente secundario">
                                ↻ Intentar de nuevo
                            </a>
                            <?php if ($ejercicio_actual < $total_ejercicios - 1): ?>
                                <a href="nivel.php?nivel=<?php echo $nivel; ?>&unidad=<?php echo urlencode($unidad); ?>&ejercicio=<?php echo ($ejercicio_actual + 1); ?>" class="btn-siguiente">
                                    Saltar ejercicio →
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if($ejercicio['type'] == 'Elegir'): ?>
                <div class="ejercicio-container">
                    <?php if(!isset($esCorrecta)): ?>
                        <form method="POST" class="form-opciones">
                            <?php foreach($opciones as $opcion): ?>
                                <button type="submit" 
                                        name="opcion" 
                                        value="<?php echo htmlspecialchars($opcion); ?>" 
                                        class="btn-opcion">
                                    <?php echo htmlspecialchars($opcion); ?>
                                </button>
                            <?php endforeach; ?>
                        </form>
                    <?php else: ?>
                        <div class="form-opciones">
                            <?php foreach($opciones as $opcion): 
                                $esLaCorrecta = ($opcion === $ejercicio['rtaAcorrect']);
                                $clase = $esLaCorrecta ? 'correcta' : 'incorrecta';
                            ?>
                                <button class="btn-opcion <?php echo $clase; ?>" disabled>
                                    <?php echo htmlspecialchars($opcion); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <div class="mensaje-resultado incorrecto">
                            ❌ Incorrecto. La respuesta correcta era: <strong><?php echo htmlspecialchars($ejercicio['rtaAcorrect']); ?></strong>
                        </div>
                        <div class="navegacion">
                            
                            <?php if ($ejercicio_actual < $total_ejercicios - 1): ?>
                                <a href="nivel.php?nivel=<?php echo $nivel; ?>&unidad=<?php echo urlencode($unidad); ?>&ejercicio=<?php echo ($ejercicio_actual + 1); ?>" class="btn-siguiente">
                                    Saltar ejercicio →
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if ($ya_completado && !$mostrar_correcto): ?>
            <div style="text-align: center; margin-top: 20px;">
                <p style="color: #666; font-size: 0.9em;">
                    ℹ Ya completaste este ejercicio anteriormente
                </p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>