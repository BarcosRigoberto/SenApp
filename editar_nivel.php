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

// Verificar que tengamos un nivel
if (!isset($_GET['nivel'])) {
    header("Location: admin_panel.php");
    exit();
}
$nivel = (int)$_GET['nivel'];
$mensaje = '';
$error = '';

// --- Lógica de Eliminación ---
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    $query_delete = "DELETE FROM ejercicio WHERE id_ej = ? AND nivel = ?";
    $stmt_delete = $conexion->prepare($query_delete);
    $stmt_delete->bind_param("ii", $id_eliminar, $nivel);
    
    if ($stmt_delete->execute()) {
        header("Location: editar_nivel.php?nivel=$nivel&msg=eliminado");
        exit();
    } else {
        $error = "Error al eliminar el ejercicio: " . $stmt_delete->error;
    }
    $stmt_delete->close();
}

// --- Lógica de Actualización ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_ejercicio'])) {
    $id_ej = (int)$_POST['id_ej'];
    $video = trim($_POST['video']);
    $rtaA = trim($_POST['rtaAcorrect']);
    $rtaB = trim($_POST['rtaB']);
    $rtaC = trim($_POST['rtaC']);
    $rtaD = trim($_POST['rtaD']);
    $tipo = $_POST['type'];

    // Validación
    if (empty($video) || empty($rtaA) || $id_ej <= 0) {
        $error = "Error: El video, la respuesta correcta y el ID son obligatorios.";
    } else {
        $query_update = "UPDATE ejercicio SET 
                            video = ?, 
                            rtaAcorrect = ?, 
                            rtaB = ?, 
                            rtaC = ?, 
                            rtaD = ?, 
                            type = ? 
                        WHERE id_ej = ? AND nivel = ?";
        
        $stmt_update = $conexion->prepare($query_update);
        $stmt_update->bind_param("ssssssii", $video, $rtaA, $rtaB, $rtaC, $rtaD, $tipo, $id_ej, $nivel);
        
        if ($stmt_update->execute()) {
            header("Location: editar_nivel.php?nivel=$nivel&msg=actualizado");
            exit();
        } else {
            $error = "Error al actualizar el ejercicio: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
}

// --- Obtener mensajes GET ---
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'eliminado') {
        $mensaje = "✅ Ejercicio eliminado exitosamente.";
    }
    if ($_GET['msg'] == 'actualizado') {
        $mensaje = "✅ Ejercicio actualizado exitosamente.";
    }
}

// --- Obtener ejercicios del nivel ---
$query_ejercicios = "SELECT * FROM ejercicio WHERE nivel = ? ORDER BY id_ej ASC";
$stmt_ejercicios = $conexion->prepare($query_ejercicios);
$stmt_ejercicios->bind_param("i", $nivel);
$stmt_ejercicios->execute();
$result_ejercicios = $stmt_ejercicios->get_result();
$ejercicios_nivel = [];
while ($row = $result_ejercicios->fetch_assoc()) {
    $ejercicios_nivel[] = $row;
}
$stmt_ejercicios->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editando Nivel <?php echo $nivel; ?> - SeñApp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>✏️ Editando Nivel <?php echo $nivel; ?></h1>
            <p style="margin-top: 10px; opacity: 0.9;">
                Modifica o elimina los ejercicios de este nivel.
            </p>
            <div style="margin-top: 15px;">
                <a href="admin_panel.php" class="button button-secondary" style="display: inline-block; width: auto; padding: 10px 25px;">
                    ← Volver al Panel
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
        
        <?php if (empty($ejercicios_nivel)): ?>
            <p style="text-align: center; color: #666; padding: 30px; background: #fff; border-radius: var(--border-radius);">
                No hay ejercicios en este nivel.
            </p>
        <?php else: ?>
            <?php foreach ($ejercicios_nivel as $ejercicio): ?>
                <div class="ejercicio-card">
                    <form method="POST" action="editar_nivel.php?nivel=<?php echo $nivel; ?>">
                        <input type="hidden" name="id_ej" value="<?php echo $ejercicio['id_ej']; ?>">
                        
                        <div class="ejercicio-header">
                            <span class="ejercicio-id">ID: <?php echo $ejercicio['id_ej']; ?></span>
                            <a href="?nivel=<?php echo $nivel; ?>&eliminar=<?php echo $ejercicio['id_ej']; ?>" 
                               class="btn-eliminar"
                               onclick="return confirm('¿Estás seguro de eliminar este ejercicio? Esta acción no se puede deshacer.')">
                                🗑️ Eliminar
                            </a>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group form-grid-full">
                                <label for="video_<?php echo $ejercicio['id_ej']; ?>">Video *</label>
                                <input type="text" 
                                       id="video_<?php echo $ejercicio['id_ej']; ?>" 
                                       name="video" 
                                       class="input-field" 
                                       required
                                       value="<?php echo htmlspecialchars($ejercicio['video']); ?>">
                            </div>
                            
                            <div class="form-group form-grid-full">
                                <label for="rtaA_<?php echo $ejercicio['id_ej']; ?>">Respuesta Correcta *</label>
                                <input type="text" 
                                       id="rtaA_<?php echo $ejercicio['id_ej']; ?>" 
                                       name="rtaAcorrect" 
                                       class="input-field" 
                                       required
                                       value="<?php echo htmlspecialchars($ejercicio['rtaAcorrect']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="rtaB_<?php echo $ejercicio['id_ej']; ?>">Opción B</label>
                                <input type="text" 
                                       id="rtaB_<?php echo $ejercicio['id_ej']; ?>" 
                                       name="rtaB" 
                                       class="input-field" 
                                       value="<?php echo htmlspecialchars($ejercicio['rtaB']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="rtaC_<?php echo $ejercicio['id_ej']; ?>">Opción C</label>
                                <input type="text" 
                                       id="rtaC_<?php echo $ejercicio['id_ej']; ?>" 
                                       name="rtaC" 
                                       class="input-field" 
                                       value="<?php echo htmlspecialchars($ejercicio['rtaC']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="rtaD_<?php echo $ejercicio['id_ej']; ?>">Opción D</label>
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
        
    </div>
</body>
</html>
