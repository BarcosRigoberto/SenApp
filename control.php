<?php

if (!empty($_POST['login-btn'])) {
    if (empty($_POST["correo"]) and empty($_POST["contraseña"])){
        echo "<div>Los campos no pueden estar vacios</div>";
    }else{
        $correo = $_POST["correo"];
        $contraseña = $_POST["contraseña"];
        $sql=$conexion->query("SELECT * FROM usuarios WHERE User_Mail = '$correo' and User_Pass= '$contraseña'");
        if ($datos=$sql->fetch_object()){
            header("location: PagPrincipal.php");
        }else{
            echo "<div>Usuario o contraseña incorrectos</div>";
        }
    }

?>