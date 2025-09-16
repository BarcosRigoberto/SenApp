<?php
session_start();
include("conn.php");

$error = '';
$success = '';

// Mensajes del sistema
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'registered':
            $success = "¡Registro exitoso! Ya puedes iniciar sesión.";
            break;
        case 'logout':
            $success = "Has cerrado sesión correctamente.";
            break;
    }
}

// Procesar el login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login-btn'])) {
    $email = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($email)) {
        $error = "El correo electrónico es obligatorio.";
    } elseif (empty($password)) {
        $error = "La contraseña es obligatoria.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } else {
    // incluir User_Lvl para establecer el nivel del usuario en la sesión
    $stmt = $conexion->prepare("SELECT User_ID, User_Name, User_Mail, User_Pass, User_Lvl FROM usuarios WHERE User_Mail = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['User_Pass'])) {
                    // establecer claves de sesión usadas en otras páginas
                    $_SESSION['user_id'] = $user['User_ID'];
                    $_SESSION['user_name'] = $user['User_Name'];
                    $_SESSION['user_email'] = $user['User_Mail'];
                    $_SESSION['logged_in'] = true;
                    // clave que usan otras páginas en el proyecto
                    $_SESSION['usuario'] = $user['User_Name'];
                    // almacenar nivel del usuario si existe
                    if (isset($user['User_Lvl'])) {
                        $_SESSION['User_Lvl'] = (int) $user['User_Lvl'];
                    }

                    // redirigir al mapa principal después de iniciar sesión
                    header("Location: PagPrincipal.php");
                    exit();
                } else {
                    $error = "Correo electrónico o contraseña incorrectos.";
                }
            } else {
                $error = "Correo electrónico o contraseña incorrectos.";
            }
            
            $stmt->close();
        } else {
            $error = "Error en el servidor. Inténtalo más tarde.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Iniciar Sesión</h2>
        
        <?php if (!empty($success)): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input 
                type="email" 
                name="correo" 
                placeholder="Correo electrónico" 
                class="input" 
                required
                value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>"
            >
            
            <input 
                type="password" 
                name="password" 
                placeholder="Contraseña" 
                class="input" 
                required
            >
            
            <input type="submit" name="login-btn" value="Iniciar Sesión" class="btn">
        </form>
        
        <div class="register-link">
            <a href="Registro.php">¿No tienes usuario? Regístrate aquí</a>
        </div>
    </div>
</body>
</html>