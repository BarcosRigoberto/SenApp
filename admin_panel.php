<?php
session_start();
include 'conn.php';

// Verificación de Admin
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

$mensaje = '';
$error = '';
$target_dir = "videos/";

// Procesar formulario de nuevo ejercicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_ejercicio'])) {
    $nivel = (int)$_POST['nivel'];
    $unidad = trim($_POST['unidad']);
    $tipo = $_POST['tipo'];
    $rtaA = trim($_POST['rtaA']);
    $rtaB = trim($_POST['rtaB']);
    $rtaC = trim($_POST['rtaC']);
    $rtaD = trim($_POST['rtaD']);
    
    $video_filename = '';
    $upload_error = '';

    // Lógica de Subida de Archivo
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
            } else {
                $upload_error = "Error al mover el archivo subido al directorio.";
            }
        } else {
            $upload_error = "Tipo de archivo no permitido (solo GIF, MP4, WebM). Tipo detectado: " . $file_type;
        }
    } else {
        switch ($_FILES['video']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $upload_error = "El archivo es demasiado grande (límite del servidor).";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $upload_error = "El archivo es demasiado grande (límite del formulario).";
                break;
            case UPLOAD_ERR_NO_FILE:
                $upload_error = "No se seleccionó ningún archivo.";
                break;
            default:
                $upload_error = "Error desconocido al subir el archivo.";
        }
    }

    // Validación
    if ($nivel <= 0) {
        $error = "El nivel debe ser mayor a 0.";
    } elseif (empty($unidad)) {
        $error = "La unidad es obligatoria.";
    } elseif (empty($rtaA)) {
        $error = "La respuesta correcta es obligatoria.";
    } elseif (empty($video_filename)) {
        $error = $upload_error;
    } elseif ($tipo != 'Elegir' && $tipo != 'Escribir') {
        $error = "Tipo de ejercicio no válido.";
    } else {
        // Insertar ejercicio con unidad
        $query_insert = "INSERT INTO ejercicio (nivel, unidad, rtaAcorrect, rtaB, rtaC, rtaD, video, type) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conexion->prepare($query_insert);
        $stmt_insert->bind_param("isssssss", $nivel, $unidad, $rtaA, $rtaB, $rtaC, $rtaD, $video_filename, $tipo);
        
        if ($stmt_insert->execute()) {
            $mensaje = "✅ Ejercicio creado exitosamente (ID: " . $conexion->insert_id . ")";
            $_POST = array();
        } else {
            $error = "Error al crear el ejercicio: " . $stmt_insert->error;
        }
        $stmt_insert->close();
    }
}

// Obtener ejercicios agrupados por unidad y luego por nivel
$query_ejercicios = "SELECT * FROM ejercicio ORDER BY unidad ASC, nivel ASC, id_ej ASC";
$result_ejercicios = $conexion->query($query_ejercicios);
$ejercicios_agrupados = [];
while ($row = $result_ejercicios->fetch_assoc()) {
    $ejercicios_agrupados[$row['unidad']][$row['nivel']][] = $row;
}

// Obtener lista de unidades existentes para el datalist
$query_unidades = "SELECT DISTINCT unidad FROM ejercicio ORDER BY unidad ASC";
$result_unidades = $conexion->query($query_unidades);
$unidades_existentes = [];
while ($row = $result_unidades->fetch_assoc()) {
    $unidades_existentes[] = htmlspecialchars($row['unidad']);
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'eliminado') $mensaje = "✅ Ejercicio eliminado exitosamente.";
    if ($_GET['msg'] == 'actualizado') $mensaje = "✅ Ejercicio actualizado exitosamente.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - SeñApp</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .unidad-grupo {
            margin-bottom: 15px;
            padding: 15px;
            background-color: var(--light-grey);
            border-radius: var(--border-radius-small);
            border: 1px solid var(--grey-color);
        }
        
        .unidad-grupo h4 {
            color: var(--color-principal);
            margin-bottom: 10px;
            font-size: 1em;
        }
        
        .nivel-editor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 12px;
        }
        
        .nivel-editor-btn {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 15px;
            border-radius: var(--border-radius);
            background-color: var(--white);
            border: 2px solid var(--grey-color);
            box-shadow: 0px 4px 0px 0px var(--grey-color);
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.2s ease-in-out;
            text-align: center;
        }
        
        .nivel-editor-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0px 6px 0px 0px var(--grey-color);
            background-color: #f0f0f0;
            color: var(--color-principal);
        }
        
        .nivel-editor-titulo {
            font-size: 1.5em;
            font-weight: 800;
            margin-bottom: 5px;
        }
        
        .nivel-editor-unidad {
            font-size: 0.9em;
            font-weight: 600;
            color: var(--darker-grey-color);
            margin-bottom: 8px;
        }
        
        .nivel-editor-stats {
            font-size: 0.75em;
            color: var(--darker-grey-color);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>🛠️ Panel de Administración</h1>
            <p>Gestión de ejercicios y niveles</p>
            <div class="button-container">
                <a href="PagPrincipal.php" class="button button-secondary">
                    ← Volver al mapa
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
        
        <!-- Formulario para crear ejercicio -->
        <div class="admin-section">
            <h2>➕ Crear Nuevo Ejercicio</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nivel">Nivel *</label>
                        <input type="number" 
                               id="nivel" 
                               name="nivel" 
                               class="input-field" 
                               min="1" 
                               required
                               value="<?php echo isset($_POST['nivel']) ? $_POST['nivel'] : '1'; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="unidad">Unidad *</label>
                        <input type="text" 
                               id="unidad" 
                               name="unidad" 
                               class="input-field" 
                               list="unidades_list"
                               required
                               placeholder="Ej: Saludos, Animales, etc."
                               value="<?php echo isset($_POST['unidad']) ? htmlspecialchars($_POST['unidad']) : ''; ?>">
                        <small>Escribe el nombre de la unidad o selecciona una existente</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo">Tipo de ejercicio *</label>
                        <select id="tipo" name="tipo" class="input-field" required onchange="toggleRespuestas()">
                            <option value="Elegir" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'Elegir') ? 'selected' : ''; ?>>
                                Elegir opción
                            </option>
                            <option value="Escribir" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'Escribir') ? 'selected' : ''; ?>>
                                Escribir respuesta
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-group form-grid-full">
                        <label for="video">Archivo de video/GIF *</label>
                        <input type="file" 
                               id="video" 
                               name="video" 
                               class="input-field" 
                               required
                               accept="image/gif,video/mp4,video/webm">
                        <small>El archivo se guardará en la carpeta /videos/.</small>
                    </div>
                    
                    <div class="form-group form-grid-full">
                        <label for="rtaA">Respuesta correcta *</label>
                        <input type="text" 
                               id="rtaA" 
                               name="rtaA" 
                               class="input-field" 
                               required
                               placeholder="Ej: Hola"
                               value="<?php echo isset($_POST['rtaA']) ? htmlspecialchars($_POST['rtaA']) : ''; ?>">
                    </div>
                    
                    <div id="opcionesAdicionales">
                        <div class="form-group">
                            <label for="rtaB">Opción incorrecta B</label>
                            <input type="text" 
                                   id="rtaB" 
                                   name="rtaB" 
                                   class="input-field"
                                   placeholder="Ej: Chau"
                                   value="<?php echo isset($_POST['rtaB']) ? htmlspecialchars($_POST['rtaB']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="rtaC">Opción incorrecta C</label>
                            <input type="text" 
                                   id="rtaC" 
                                   name="rtaC" 
                                   class="input-field"
                                   placeholder="Ej: Gracias"
                                   value="<?php echo isset($_POST['rtaC']) ? htmlspecialchars($_POST['rtaC']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="rtaD">Opción incorrecta D</label>
                            <input type="text" 
                                   id="rtaD" 
                                   name="rtaD" 
                                   class="input-field"
                                   placeholder="Ej: Por favor"
                                   value="<?php echo isset($_POST['rtaD']) ? htmlspecialchars($_POST['rtaD']) : ''; ?>">
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="crear_ejercicio" class="button">
                    ✅ Crear Ejercicio
                </button>
            </form>
        </div>
        
        <!-- Lista de niveles y unidades para editar -->
        <div class="admin-section">
            <h2>✏️ Editar Niveles y Unidades Existentes</h2>
            
            <?php if (empty($ejercicios_agrupados)): ?>
                <p class="no-content-msg">
                    No hay ejercicios creados todavía.
                </p>
            <?php else: ?>
                <?php foreach ($ejercicios_agrupados as $unidad => $niveles): ?>
                    <div class="unidad-grupo">
                        <h3 style="color: var(--color-principal); margin-bottom: 15px; font-size: 1.3em;">
                            📚 <?php echo htmlspecialchars($unidad); ?>
                        </h3>
                        <div class="nivel-editor-grid">
                            <?php foreach ($niveles as $nivel => $ejercicios_nivel): ?>
                                <a href="editar_nivel.php?nivel=<?php echo $nivel; ?>&unidad=<?php echo urlencode($unidad); ?>" 
                                   class="nivel-editor-btn" 
                                   title="Editar '<?php echo htmlspecialchars($unidad); ?>' - Nivel <?php echo $nivel; ?>">
                                    <span class="nivel-editor-titulo">Nivel <?php echo $nivel; ?></span>
                                    <span class="nivel-editor-stats"><?php echo count($ejercicios_nivel); ?> ejercicio<?php echo count($ejercicios_nivel) != 1 ? 's' : ''; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Datalist con unidades existentes -->
        <datalist id="unidades_list">
            <?php foreach ($unidades_existentes as $u): ?>
                <option value="<?php echo $u; ?>">
            <?php endforeach; ?>
        </datalist>
    </div>
    
    <script>
        function toggleRespuestas() {
            const tipo = document.getElementById('tipo').value;
            const opcionesDiv = document.getElementById('opcionesAdicionales');
            
            if (tipo === 'Escribir') {
                opcionesDiv.style.display = 'none';
                document.getElementById('rtaB').value = '';
                document.getElementById('rtaC').value = '';
                document.getElementById('rtaD').value = '';
            } else {
                opcionesDiv.style.display = 'grid';
            }
        }
        toggleRespuestas();
    </script>
</body>
</html>