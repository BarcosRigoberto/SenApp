<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
    <div class="container">
        <h2>Iniciar Sesion</h2>
        <?php
        include ("conn.php");
        include ("control.php");
         ?>
        <div class="login-content">
            <form method="post" action="">
            <input type="text" placeholder="Correo" class="input" id="correo" >
            <input type="password" placeholder="Contraseña" class="input" id="contraseña">
            <input name="login-btn" type="submit" value="Iniciar Sesión" class="btn">
            <a href="Registro.html">¿No tienes usuario?</a>
            </form>
        </div>
    </div>
</body>
</html>