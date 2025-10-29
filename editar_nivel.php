<?php
session_start();
include 'conn.php';

// --- Verificación de Admin (igual que admin_panel.php) ---
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$query_admin = "SELECT User_IsAdmin FROM usuarios WHERE User_ID = ?";
$stmt = $conexion->prepare($query_admin);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$result || $result['User_IsAdmin'] != 1) {
    header("Location: PagPrincipal.php");
    exit();
}
// --- Fin Verificación de Admin ---

// Verificar que tengamos nivel Y unidad
if (!isset($_GET['nivel']) || !isset($_GET['unidad'])) {
    header("Location: admin_panel.php");
    exit();
}
$nivel = (int)$_GET['nivel'];
$unidad = trim(urldecode($_GET['unidad'])); // NUEVO: Obtener la unidad de la URL

$mensaje = '';
$error = '';
$target_dir = "videos/";

// --- Lógica de Eliminación (Actualizada con Unidad en el redirect) ---
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    $query_delete = "DELETE FROM ejercicio WHERE id_ej = ? AND nivel = ? AND unidad = ?";
    $stmt_delete = $conexion->prepare($query_delete);
    // Cambiamos 'ii' a 'iis'
    $stmt_delete->bind_param("iis", $id_eliminar, $nivel, $unidad); 
    
    if ($stmt_delete->execute()) {
        $mensaje = "✅ Ejercicio eliminado exitosamente.";
    } else {
        $error = "Error al eliminar el ejercicio: " . $stmt_delete->error;
    }
    $stmt_delete->close();
    // Redireccionamos con la unidad para mantener el contexto
    header("Location: editar_nivel.php?nivel=" . $nivel . "&unidad=" . urlencode($unidad) . "&msg=eliminado");
    exit();
}
// --- Fin Lógica de Eliminación ---

// --- Lógica de Actualización de Ejercicio (Actualizada para manejar Unidad y subida de archivo) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_ejercicio'])) {
    $id_ej = (int)$_POST['id_ej'];
    $nuevo_nivel = (int)$_POST['nivel'];
    $nueva_unidad = trim($_POST['unidad']); // NUEVO: Capturar nueva unidad
    $rtaA = trim($_POST['rtaA']);
    $rtaB = trim($_POST['rtaB']);
    $rtaC = trim($_POST['rtaC']);
    $rtaD = trim($_POST['rtaD']);
    $type = $_POST['type'];
    $video_actual = $_POST['video_actual'];
    $video_filename = $video_actual; // Por defecto, se mantiene el video actual

    // Lógica de Subida de Archivo (Si se sube uno nuevo)
    if (isset($_FILES['video']) && $_FILES['video']['error'] == UPLOAD_ERR_OK) {
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $file_basename = basename($_FILES['video']['name']);
        $target_file = $target_dir . uniqid() . '_' . preg_replace("/[^a-zA-Z0-9.\-_]/", "", $file_basename);
        
        $allowed_types = ['image/gif', 'video/mp4', 'video/webm'];
        $file_type = mime_content_type($_FILES['video']['tmp_name']);
        
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES['video']['tmp_name'], $target_file)) {
                $video_filename = basename($target_file); 
                
                // Opcional: Eliminar el archivo de video anterior si existe
                if (!empty($video_actual) && file_exists($target_dir . $video_actual)) {
                    // unlink($target_dir . $video_actual); 
                }
            } else {
                $error = "Error al mover el nuevo archivo subido al directorio.";
            }
        } else {
            $error = "Tipo de archivo no permitido (solo GIF, MP4, WebM).";
        }
    }
    // Fin Lógica de Subida
    
    // Si no hubo error en la subida, proceder a la actualización de la BD
    if (empty($error)) {
        // CAMBIO: Se añade 'unidad' a la consulta UPDATE
        $query_update = "UPDATE ejercicio SET 
                            nivel = ?, 
                            unidad = ?, 
                            rtaAcorrect = ?, 
                            rtaB = ?, 
                            rtaC = ?, 
                            rtaD = ?, 
                            video = ?, 
                            type = ? 
                         WHERE id_ej = ?";
        
        $stmt_update = $conexion->prepare($query_update);
        // Nueva secuencia: isssssssi (int, string, 6 strings, int)
        $stmt_update->bind_param("isssssssi", 
            $nuevo_nivel, 
            $nueva_unidad, // NUEVO: valor
            $rtaA, $rtaB, $rtaC, $rtaD, 
            $video_filename, 
            $type, 
            $id_ej
        );
        
        if ($stmt_update->execute()) {
            // Si la unidad o nivel ha cambiado, redirigir al nuevo contexto
            if ($nueva_unidad !== $unidad || $nuevo_nivel !== $nivel) {
                 header("Location: editar_nivel.php?nivel=" . $nuevo_nivel . "&unidad=" . urlencode($nueva_unidad) . "&msg=actualizado");
                 exit();
            }
            $mensaje = "✅ Ejercicio actualizado exitosamente.";
        } else {
            $error = "Error al actualizar el ejercicio: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
}
// --- Fin Lógica de Actualización ---

// Obtener todos los ejercicios para la Unidad y Nivel actual
// CAMBIO: Se añade 'unidad' a la cláusula WHERE
$query_ejercicios = "SELECT * FROM ejercicio WHERE nivel = ? AND unidad = ? ORDER BY id_ej ASC";
$stmt_ej = $conexion->prepare($query_ejercicios);
$stmt_ej->bind_param("is", $nivel, $unidad);
$stmt_ej->execute();
$result_ejercicios = $stmt_ej->get_result();
$ejercicios = $result_ejercicios->fetch_all(MYSQLI_ASSOC);
$stmt_ej->close();


if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'eliminado') $mensaje = "✅ Ejercicio eliminado exitosamente.";
    if ($_GET['msg'] == 'actualizado') $mensaje = "✅ Ejercicio actualizado exitosamente.";
}

// Lista de unidades existentes para la sugerencia en el formulario
$query_unidades = "SELECT DISTINCT unidad FROM ejercicio ORDER BY unidad ASC";
$result_unidades = $conexion->query($query_unidades);
$unidades_existentes = [];
while ($row = $result_unidades->fetch_assoc()) {
    $unidades_existentes[] = htmlspecialchars($row['unidad']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Unidad "<?php echo htmlspecialchars($unidad); ?>" - Nivel <?php echo $nivel; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .ejercicio-card {
            border: 1px solid var(--grey-color);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            background-color: var(--light-grey);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .ejercicio-card h3 {
            margin-top: 0;
            color: var(--color-principal);
            border-bottom: 2px solid var(--grey-color);
            padding-bottom: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-eliminar {
            background-color: var(--red);
            color: white;
            padding: 8px 15px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-size: 0.9em;
            transition: opacity 0.2s;
        }
        .btn-eliminar:hover {
            opacity: 0.9;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .form-grid-full {
            grid-column: 1 / -1;
        }
        .btn-guardar {
            width: 100%;
            padding: 12px;
            background-color: var(--green);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-guardar:hover {
            background-color: var(--green-hover);
        }
        .video-preview {
            max-width: 100%;
            height: auto;
            display: block;
            margin-top: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <!-- CAMBIO: Muestra la Unidad en el título -->
            <h1> Editando Unidad: "<?php echo htmlspecialchars($unidad); ?>"</h1>
            <h2>Nivel <?php echo $nivel; ?></h2>
            <div class="button-container">
                <a href="admin_panel.php" class="button button-secondary">
                    ← Volver al Panel
                </a>
            </div>
        </div>
        
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje exito">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="mensaje error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($ejercicios)): ?>
            <p class="no-content-msg">
                No se encontraron ejercicios para la Unidad "<?php echo htmlspecialchars($unidad); ?>" y Nivel <?php echo $nivel; ?>.
            </p>
        <?php else: ?>
            <?php foreach ($ejercicios as $index => $ejercicio): ?>
                <div class="ejercicio-card">
                    <h3>
                        Ejercicio #<?php echo $index + 1; ?> (ID: <?php echo $ejercicio['id_ej']; ?>)
                        <a href="?nivel=<?php echo $nivel; ?>&unidad=<?php echo urlencode($unidad); ?>&eliminar=<?php echo $ejercicio['id_ej']; ?>" 
                           class="btn-eliminar"
                           onclick="return confirm('¿Seguro que quieres eliminar este ejercicio (ID: <?php echo $ejercicio['id_ej']; ?>)?');">
                            Eliminar
                        </a>
                    </h3>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_ej" value="<?php echo $ejercicio['id_ej']; ?>">
                        <input type="hidden" name="video_actual" value="<?php echo htmlspecialchars($ejercicio['video']); ?>">
                        
                        <div class="form-grid">
                            
                            <!-- NUEVO: Campo de Unidad -->
                            <div class="form-group">
                                <label for="unidad_<?php echo $ejercicio['id_ej']; ?>">Unidad *</label>
                                <input type="text" 
                                       list="unidades_list"
                                       id="unidad_<?php echo $ejercicio['id_ej']; ?>" 
                                       name="unidad" 
                                       class="input-field" 
                                       required
                                       value="<?php echo htmlspecialchars($ejercicio['unidad']); ?>">
                            </div>
                            <!-- Fin NUEVO: Campo de Unidad -->
                            
                            <div class="form-group">
                                <label for="nivel_<?php echo $ejercicio['id_ej']; ?>">Nivel *</label>
                                <input type="number" 
                                       id="nivel_<?php echo $ejercicio['id_ej']; ?>" 
                                       name="nivel" 
                                       class="input-field" 
                                       min="1" 
                                       required
                                       value="<?php echo htmlspecialchars($ejercicio['nivel']); ?>">
                            </div>
                            
                            <div class="form-group form-grid-full">
                                <label for="video_<?php echo $ejercicio['id_ej']; ?>">Reemplazar Video/GIF (Actual: <?php echo htmlspecialchars($ejercicio['video']); ?>)</label>
                                
                                <video class="video-preview" autoplay loop muted playsinline src="<?php echo $target_dir . htmlspecialchars($ejercicio['video']); ?>"></video>
                                
                                <input type="file" 
                                       id="video_<?php echo $ejercicio['id_ej']; ?>" 
                                       name="video" 
                                       class="input-field"
                                       accept="image/gif,video/mp4,video/webm">
                                <small>Deja vacío para mantener el archivo actual.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="rtaA_<?php echo $ejercicio['id_ej']; ?>">Respuesta correcta (A) *</label>
                                <input type="text" 
                                       id="rtaA_<?php echo $ejercicio['id_ej']; ?>" 
                                       name="rtaA" 
                                       class="input-field" 
                                       required
                                       value="<?php echo htmlspecialchars($ejercicio['rtaAcorrect']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="rtaB_<?php echo $ejercicio['id_ej']; ?>">Opción incorrecta B</label>
                                <input type="text" 
                                       id="rtaB_<?php echo $ejercicio['id_ej']; ?>" 
                                       name="rtaB" 
                                       class="input-field" 
                                       value="<?php echo htmlspecialchars($ejercicio['rtaB']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="rtaC_<?php echo $ejercicio['id_ej']; ?>">Opción incorrecta C</label>
                                <input type="text" 
                                       id="rtaC_<?php echo $ejercicio['id_ej']; ?>" 
                                       name="rtaC" 
                                       class="input-field" 
                                       value="<?php echo htmlspecialchars($ejercicio['rtaC']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="rtaD_<?php echo $ejercicio['id_ej']; ?>">Opción incorrecta D</label>
                                <input type="text" 
                                       id="rtaD_<?php echo $ejercicio['id_ej']; ?>" 
                                       name="rtaD" 
                                       class="input-field" 
                                       value="<?php echo htmlspecialchars($ejercicio['rtaD']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="tipo_<?php echo $ejercicio['id_ej']; ?>">Tipo *</label>
                                <select id="tipo_<?php echo $ejercicio['id_ej']; ?>" name="type" class="input-field" required>
                                    <option value="Elegir" <?php echo ($ejercicio['type'] == 'Elegir') ? 'selected' : ''; ?>>
                                        Elegir opción
                                    </option>
                                    <option value="Escribir" <?php echo ($ejercicio['type'] == 'Escribir') ? 'selected' : ''; ?>>
                                        Escribir respuesta
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" name="actualizar_ejercicio" class="btn-guardar">
                            💾 Guardar Cambios
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <datalist id="unidades_list">
            <?php foreach ($unidades_existentes as $u): ?>
                <option value="<?php echo $u; ?>">
            <?php endforeach; ?>
        </datalist>
    </div>
</body>
</html>
