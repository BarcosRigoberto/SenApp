<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header("Location: PagPrincipal.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="style.css">
    <title>SeñApp</title>
</head>
<body>
    <div class="bienvenida">
        <h1>Bienvenido a SeñApp</h1>
         
    </div>
    <div class="button-container">
        <button class="log"><a href="Login.php">Iniciar sesion</a></button>
        <button class="log"><a href="Registro.php">Registrarse</a></button>
    </div>
</body>
</html>