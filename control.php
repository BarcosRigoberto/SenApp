<?php

if (!empty($_POST['login-btn'])) {
    if (empty($_POST["correo"]) || empty($_POST["contraseña"])){
        echo "<div>Los campos no pueden estar vacios</div>";
    }else{
        $correo = $_POST["correo"];
        $contraseña = $_POST["contraseña"];
        // Buscar usuario por correo
        $sql = $conexion->query("SELECT * FROM usuarios WHERE email = '$correo'");
        if ($datos = $sql->fetch_object()){
            // Verificar contraseña hasheada
            if (password_verify($contraseña, $datos->pass)) {
                header("Location: PagPrincipal.php");
                exit;
            } else {
                echo "<div>Usuario o contraseña incorrectos</div>";
            }
        }else{
            echo "<div>Usuario o contraseña incorrectos</div>";
        }
    }
}
?>