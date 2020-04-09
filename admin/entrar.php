<?php
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
    $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $location);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <link href="https://fonts.googleapis.com/css?family=Sen&display=swap" rel="stylesheet">
    <title>Entrar</title>
</head>

<body style="background-color: #070707;">
    <div id="particles-js"></div>
    
    <div id="login">
        <form action="login.php" method="POST">
          <h3>Iniciar sesión</h3>
            <input type="text" name="usuario" placeholder="Usuario" required><br>
            <input type="password" name="pass" placeholder="Contraseña" required><br>
            <input type="submit" value="Entrar">
        </form>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.js"></script>
    <script>
    particlesJS.load('particles-js', 'js/particles/particles.json',
        function(){
            console.log("particles.json loaded...");
        });
    </script>
</body>
</html>