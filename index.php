<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header("Location: PagPrincipal.php");
    exit();
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Bienvenido a SeñApp</h1>
    <button><a href="Login.php">Iniciar sesion</a></button> <button><a href="Registro.php">Registrarse</a></button>
</body>
</html>