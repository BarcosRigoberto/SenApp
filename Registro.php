<?php
include 'conn.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pass = isset($_POST['pass']) ? $_POST['pass'] : '';

    // Validación básica
    if (empty($nombre)) {
        $error = "El nombre es obligatorio.";
    } elseif (empty($email)) {
        $error = "El correo electrónico es obligatorio.";
    } elseif (empty($pass)) {
        $error = "La contraseña es obligatoria.";
    } elseif (strlen($pass) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } else {
        // Verificar si el email ya existe
        $checkEmail = $conexion->prepare("SELECT User_ID FROM usuarios WHERE User_Mail = ?");
        if ($checkEmail) {
            $checkEmail->bind_param("s", $email);
            $checkEmail->execute();
            $result = $checkEmail->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Este correo electrónico ya está registrado.";
            } else {
                // Hash seguro de la contraseña
                $passHash = password_hash($pass, PASSWORD_DEFAULT);

                // Insertar en la base de datos
                $stmt = $conexion->prepare("INSERT INTO usuarios (User_Name, User_Mail, User_Pass) VALUES (?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sss", $nombre, $email, $passHash);
                    
                    if ($stmt->execute()) {
                        $success = "¡Registro exitoso! Puedes iniciar sesión ahora.";
                        // Limpiar campos después del éxito
                        $nombre = $email = $pass = '';
                    } else {
                        $error = "Error al crear la cuenta. Inténtalo de nuevo.";
                    }
                    $stmt->close();
                } else {
                    $error = "Error en el servidor. Inténtalo más tarde.";
                }
            }
            $checkEmail->close();
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
    <title>Registro de Usuario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #007bff;
            outline: none;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        
        button:hover {
            background-color: #218838;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: #007bff;
            text-decoration: none;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Crear Cuenta</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre completo</label>
                <input 
                    type="text" 
                    id="nombre" 
                    name="nombre" 
                    required
                    value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="pass">Contraseña (mínimo 6 caracteres)</label>
                <input 
                    type="password" 
                    id="pass" 
                    name="pass" 
                    required
                    minlength="6"
                >
            </div>
            
            <button type="submit">Registrarse</button>
        </form>
        
        <div class="login-link">
            <p>¿Ya tienes una cuenta? <a href="Login.php">Inicia sesión aquí</a></p>
        </div>
    </div>
</body>
</html>