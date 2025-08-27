<?php
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $pass = $_POST['pass'];

    // Validación básica
    if (empty($nombre) || empty($email) || empty($pass)) {
        echo "Todos los campos son obligatorios.";
        exit;
    }

    // Hash seguro de la contraseña
    $passHash = password_hash($pass, PASSWORD_DEFAULT);

    // Insertar en la base de datos con los nombres de columna correctos
    $stmt = $conexion->prepare("INSERT INTO usuarios (User_Name, User_Mail, User_Pass) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $email, $passHash);
    if ($stmt->execute()) {
        // Registro exitoso, redirigir a PagPrincipal.php
        header("Location: PagPrincipal.php");
        exit;
    } else {
        echo "Error al registrar: " . $stmt->error;
    }
    $stmt->close();
    $conexion->close();
}
?>
