<?php
include 'conn.php';

$nivel = isset($_GET['nivel']) ? $_GET['nivel'] : 1;
$leccion = isset($_GET['leccion']) ? $_GET['leccion'] : 1;

// Obtener total de lecciones para este nivel
$query_total = "SELECT COUNT(*) as total FROM ejercicio WHERE nivel = ?";
$stmt_total = $conexion->prepare($query_total);
$stmt_total->bind_param("i", $nivel);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total = $total_result->fetch_assoc()['total'];

// Obtener ejercicio actual
$query = "SELECT * FROM ejercicio WHERE nivel = ? LIMIT ?, 1";
$stmt = $conexion->prepare($query);
$offset = $leccion - 1;
$stmt->bind_param("ii", $nivel, $offset);
$stmt->execute();
$result = $stmt->get_result();
$ejercicio = $result->fetch_assoc();

// Procesar respuesta tipo Escribir
if(isset($_POST['respuesta']) && $ejercicio['type'] == 'Escribir') {
    $respuesta_usuario = strtolower(trim($_POST['respuesta']));
    $respuesta_correcta = strtolower(trim($ejercicio['rtaAcorrect']));
    
    $respuesta_usuario = iconv('UTF-8', 'ASCII//TRANSLIT', $respuesta_usuario);
    $respuesta_correcta = iconv('UTF-8', 'ASCII//TRANSLIT', $respuesta_correcta);
    
    $esCorrecta = ($respuesta_usuario === $respuesta_correcta);

    // Si es correcta, registrar progreso (marcar ejercicio como completado)
    if ($esCorrecta && isset($_SESSION['user_id'])) {
        $insert_prog = "INSERT INTO progreso_ejercicio (user_id, id_ej, completado, fecha)
                        VALUES (?, ?, 1, NOW())
                        ON DUPLICATE KEY UPDATE completado = 1, fecha = NOW()";
        $pst = $conexion->prepare($insert_prog);
        if ($pst) { $pst->bind_param('ii', $_SESSION['user_id'], $ejercicio['id_ej']); $pst->execute(); $pst->close(); }
    }
}

// Procesar respuesta tipo Elegir
if(isset($_POST['opcion']) && $ejercicio['type'] == 'Elegir') {
    $esCorrecta = ($_POST['opcion'] === $ejercicio['rtaAcorrect']);
    if ($esCorrecta && isset($_SESSION['user_id'])) {
        $insert_prog = "INSERT INTO progreso_ejercicio (user_id, id_ej, completado, fecha)
                        VALUES (?, ?, 1, NOW())
                        ON DUPLICATE KEY UPDATE completado = 1, fecha = NOW()";
        $pst = $conexion->prepare($insert_prog);
        if ($pst) { $pst->bind_param('ii', $_SESSION['user_id'], $ejercicio['id_ej']); $pst->execute(); $pst->close(); }
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nivel <?php echo $nivel; ?> - SeñApp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="header-nav">
            <a href="PagPrincipal.php" class="btn-volver">&larr; Volver al mapa</a>
            <div class="progreso">Lección <?php echo $leccion; ?>/<?php echo $total; ?></div>
        </div>
        <h1>Nivel <?php echo $nivel; ?></h1>
    </header>

    <div class="contenedor-nivel">
        <div class="gif-container">
            <img src="videos/<?php echo $ejercicio['video']; ?>" alt="Seña animada" class="gif-seña">
        </div>

        <?php if($ejercicio['type'] == 'Escribir'): ?>
            <div class="ejercicio-container">
                <form method="POST" class="form-respuesta">
                    <input type="text" 
                           name="respuesta" 
                           placeholder="Escribe tu respuesta" 
                           required 
                           class="input-respuesta">
                    <button type="submit" class="btn-responder">Responder</button>
                </form>
                
                <?php if(isset($esCorrecta)): ?>
                    <div class="mensaje-resultado <?php echo $esCorrecta ? 'correcto' : 'incorrecto'; ?>">
                        <?php echo $esCorrecta ? '¡Correcto!' : 'Incorrecto...'; ?>
                    </div>
                    <div class="navegacion">
                        <?php if($leccion < $total): ?>
                            <a href="nivel.php?nivel=<?php echo $nivel; ?>&leccion=<?php echo $leccion + 1; ?>" 
                               class="btn-siguiente">
                                Siguiente lección &rarr;
                            </a>
                        <?php else: ?>
                            <a href="PagPrincipal.php" class="btn-siguiente">
                                Volver al mapa de niveles
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
                                    value="<?php echo $opcion; ?>" 
                                    class="btn-opcion">
                                <?php echo $opcion; ?>
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
                                <?php echo $opcion; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="mensaje-resultado <?php echo $esCorrecta ? 'correcto' : 'incorrecto'; ?>">
                        <?php echo $esCorrecta ? '¡Correcto!' : 'Incorrecto...'; ?>
                    </div>
                    <div class="navegacion">
                        <?php if($leccion < $total): ?>
                            <a href="nivel.php?nivel=<?php echo $nivel; ?>&leccion=<?php echo $leccion + 1; ?>" 
                               class="btn-siguiente">
                                Siguiente lección &rarr;
                            </a>
                        <?php else: ?>
                            <a href="Resultados.php" class="btn-siguiente">
                                Ver resultados
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>