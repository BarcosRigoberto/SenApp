<?php
//Pagina principal (mapa de niveles con unidades)
include 'conn.php';
session_start();

// verificar sesion del usuario
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Verificar si es admin
$es_admin = false;
$stmt_admin = $conexion->prepare("SELECT User_IsAdmin FROM usuarios WHERE User_ID = ?");
if ($stmt_admin) {
    $stmt_admin->bind_param("i", $user_id);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();
    if ($row_admin = $result_admin->fetch_assoc()) {
        $es_admin = ($row_admin['User_IsAdmin'] == 1);
    }
    $stmt_admin->close();
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

// Obtener nivel actual del usuario
$nivel_usuario = 1;
$stmt = $conexion->prepare("SELECT User_Lvl FROM usuarios WHERE User_ID = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $nivel_usuario = $row['User_Lvl'];
    }
    $stmt->close();
}

// Obtener progreso del usuario
$progreso_usuario = obtenerProgreso($conexion, $user_id);

// Obtener todos los niveles y unidades agrupados
$query = "SELECT DISTINCT nivel, unidad FROM ejercicio ORDER BY nivel ASC, unidad ASC";
$result_niveles = $conexion->query($query);

$unidades_con_niveles = [];
while ($row = $result_niveles->fetch_assoc()) {
    $nivel_num = $row['nivel'];
    $unidad = $row['unidad'];
    
    // Obtener ejercicios de esta combinación nivel-unidad
    $query_ej = "SELECT id_ej FROM ejercicio WHERE nivel = ? AND unidad = ?";
    $stmt_ej = $conexion->prepare($query_ej);
    $stmt_ej->bind_param("is", $nivel_num, $unidad);
    $stmt_ej->execute();
    $result_ej = $stmt_ej->get_result();
    
    $ejercicios_unidad = [];
    while ($ej = $result_ej->fetch_assoc()) {
        $ejercicios_unidad[] = $ej['id_ej'];
    }
    $stmt_ej->close();
    
    // Contar completados
    $completados = 0;
    foreach ($ejercicios_unidad as $ej_id) {
        if (in_array($ej_id, $progreso_usuario)) {
            $completados++;
        }
    }
    
    // Agrupar por unidad primero, luego agregar niveles
    if (!isset($unidades_con_niveles[$unidad])) {
        $unidades_con_niveles[$unidad] = [];
    }
    
    $unidades_con_niveles[$unidad][] = [
        'nivel' => $nivel_num,
        'total_ejercicios' => count($ejercicios_unidad),
        'ejercicios_completados' => $completados
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeñApp Niveles</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .unidad-titulo {
            color: var(--text-color);
            font-size: 1.2em;
            font-weight: 700;
            margin: 30px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--grey-color);
        }
    </style>
</head>
<body>
    <header>
        <div class="header-titles">
            <h1>SeñApp</h1>
        </div>

        <div class="menu-inferior">
            <div class="ranking-button-left">
                <a href="ranking.php" class="btn-ranking">
                    <img src="iconos/trofeo.svg" class="trofeo-svg" alt="Ranking">
                    Ranking
                </a>
            </div>

            <div class="user-menu">
                <button class="user-button" onclick="toggleMenu()">
                    <img src="iconos/usuario.svg" alt="Usuario" class="usuario-icon">
                    <?php echo htmlspecialchars($_SESSION['usuario']); ?>
                </button>
                <div class="user-dropdown" id="userDropdown">
                    <?php if ($es_admin): ?>
                        <a href="admin_panel.php">🛠️ Panel Admin</a>
                    <?php endif; ?>
                    <a href="">Editar informacion</a>
                    <a href="">Configuracion</a>
                    <a href="">Creditos</a>
                    <a href="logout.php">Cerrar Sesión</a>
                    
                </div>
            </div>
        </div>
    </header>
    
    <script>
        function toggleMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        window.addEventListener('click', function(event) {
            if (!event.target.closest('.user-menu')) {
                const dropdown = document.getElementById('userDropdown');
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        });
    </script>
    
    <div id="contenido">
        <div class="niveles-lista">
            <?php foreach($unidades_con_niveles as $unidad => $niveles): ?>
                <h3 class="unidad-titulo"><?php echo htmlspecialchars($unidad); ?></h3>
                
                <?php foreach($niveles as $nivel_data): 
                    $nivel_num = $nivel_data['nivel'];
                    $total = $nivel_data['total_ejercicios'];
                    $completados = $nivel_data['ejercicios_completados'];
                    $porcentaje = $total > 0 ? round(($completados / $total) * 100) : 0;
                    $nivel_completado = ($completados >= $total);
                    $nivel_bloqueado = ($nivel_num > $nivel_usuario);
                    
                    // Determinar clase
                    if ($nivel_bloqueado) {
                        $clase = 'bloqueado';
                        $contenido = '<img src="iconos/candado.svg" alt="Bloqueado" class="icono-candado">';
                    } elseif ($nivel_completado) {
                        $clase = 'completado';
                        $contenido = $nivel_num;
                    } else {
                        $clase = 'incompleto';
                        $contenido = $nivel_num;
                    }
                ?>
                    <?php if ($nivel_bloqueado): ?>
                        <div class="nivel-btn <?php echo $clase; ?>">
                            <span class="nivel-numero"><?php echo $contenido; ?></span>
                        </div>
                    <?php else: ?>
                        <a href="nivel.php?nivel=<?php echo $nivel_num; ?>&unidad=<?php echo urlencode($unidad); ?>" class="nivel-btn <?php echo $clase; ?>">
                            <span class="nivel-numero"><?php echo $contenido; ?></span>
                            
                            <?php if ($nivel_completado): ?>
                                <span class="nivel-check">✓</span>
                            <?php endif; ?>
                            
                            <?php if (!$nivel_completado && $completados > 0): ?>
                                <span class="nivel-progreso-mini"><?php echo $porcentaje; ?>%</span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>