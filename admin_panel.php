<?php
session_start();
include 'conn.php';

// Verificar sesión y permisos de admin
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Verificar si el usuario es admin
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

// Procesar formulario de nuevo ejercicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_ejercicio'])) {
    $nivel = (int)$_POST['nivel'];
    $tipo = $_POST['tipo'];
    $rtaA = trim($_POST['rtaA']);
    $rtaB = trim($_POST['rtaB']);
    $rtaC = trim($_POST['rtaC']);
    $rtaD = trim($_POST['rtaD']);
    $video = trim($_POST['video']);
    
    // Validación básica
    if ($nivel <= 0) {
        $error = "El nivel debe ser mayor a 0.";
    } elseif (empty($rtaA)) {
        $error = "La respuesta correcta es obligatoria.";
    } elseif (empty($video)) {
        $error = "El nombre del video es obligatorio.";
    } elseif ($tipo != 'Elegir' && $tipo != 'Escribir') {
        $error = "Tipo de ejercicio no válido.";
    } else {
        // Insertar ejercicio
        $query_insert = "INSERT INTO ejercicio (nivel, rtaAcorrect, rtaB, rtaC, rtaD, video, type, estado_completado) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt_insert = $conexion->prepare($query_insert);
        $stmt_insert->bind_param("issssss", $nivel, $rtaA, $rtaB, $rtaC, $rtaD, $video, $tipo);
        
        if ($stmt_insert->execute()) {
            $mensaje = "✅ Ejercicio creado exitosamente (ID: " . $conexion->insert_id . ")";
            // Limpiar formulario
            $_POST = array();
        } else {
            $error = "Error al crear el ejercicio: " . $stmt_insert->error;
        }
        $stmt_insert->close();
    }
}

// Obtener todos los ejercicios
$query_ejercicios = "SELECT * FROM ejercicio ORDER BY nivel ASC, id_ej ASC";
$result_ejercicios = $conexion->query($query_ejercicios);

$ejercicios = [];
while ($row = $result_ejercicios->fetch_assoc()) {
    $ejercicios[] = $row;
}

// Agrupar por nivel
$ejercicios_por_nivel = [];
foreach ($ejercicios as $ej) {
    $ejercicios_por_nivel[$ej['nivel']][] = $ej;
}

// Mensajes de la página de edición
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'eliminado') {
        $mensaje = "✅ Ejercicio eliminado exitosamente.";
    }
    if ($_GET['msg'] == 'actualizado') {
        $mensaje = "✅ Ejercicio actualizado exitosamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - SeñApp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>🛠️ Panel de Administración</h1>
            <p style="margin-top: 10px; opacity: 0.9;">Gestión de ejercicios y niveles</p>
            <div style="margin-top: 15px;">
                <a href="PagPrincipal.php" class="button button-secondary" style="display: inline-block; width: auto; padding: 10px 25px;">
                    ← Volver al mapa
                </a>
            </div>
        </div>
        
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje exito" style="margin-bottom: 20px;">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="mensaje error" style="margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Formulario para crear ejercicio -->
        <div class="admin-section">
            <h2>➕ Crear Nuevo Ejercicio</h2>
            <form method="POST">
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
                        <label for="video">Nombre del video (ej: SeñaA.gif) *</label>
                        <input type="text" 
                               id="video" 
                               name="video" 
                               class="input-field" 
                               required
                               placeholder="SeñaHola.gif"
                               value="<?php echo isset($_POST['video']) ? htmlspecialchars($_POST['video']) : ''; ?>">
                        <small style="color: #666;">El archivo debe estar en la carpeta /videos/</small>
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
        
        <!-- INICIO CAMBIO: Lista de niveles para editar -->
        <div class="admin-section">
            <h2>✏️ Editar Niveles Existentes</h2>
            
            <?php if (empty($ejercicios_por_nivel)): ?>
                <p style="text-align: center; color: #666; padding: 30px;">
                    No hay ejercicios creados todavía.
                </p>
            <?php else: ?>
                <div class="nivel-editor-grid">
                    <?php foreach ($ejercicios_por_nivel as $nivel => $ejercicios_nivel): ?>
                        <a href="editar_nivel.php?nivel=<?php echo $nivel; ?>" class="nivel-editor-btn" title="Editar Nivel <?php echo $nivel; ?> (<?php echo count($ejercicios_nivel); ?> ejercicios)">
                            <!-- Solo mostramos el número -->
                            <span class="nivel-editor-titulo"><?php echo $nivel; ?></span>
                            
                            <!-- Mantenemos los otros spans pero ocultos por CSS, por si se quieren reactivar -->
                            <span class="nivel-editor-stats">
                                <?php echo count($ejercicios_nivel); ?> ejercicio<?php echo count($ejercicios_nivel) > 1 ? 's' : ''; ?>
                            </span>
                            <span class="nivel-editor-cta">Editar →</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <!-- FIN CAMBIO -->
    </div>
    
    <script>
        function toggleRespuestas() {
            const tipo = document.getElementById('tipo').value;
            const opcionesDiv = document.getElementById('opcionesAdicionales');
            
            if (tipo === 'Escribir') {
                opcionesDiv.style.display = 'none';
                // Limpiar los campos opcionales
                document.getElementById('rtaB').value = '';
                document.getElementById('rtaC').value = '';
                document.getElementById('rtaD').value = '';
            } else {
                opcionesDiv.style.display = 'grid';
            }
        }
        
        // Ejecutar al cargar la página
        toggleRespuestas();
    </script>
</body>
</html>

