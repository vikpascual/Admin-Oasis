<?php
if(isset($_POST['destruir']) && $_POST['destruir'] == 'destruir'){
    unset($_SESSION['consola']);
}

if(isset($_SESSION['consola']['sesion_iniciada']) && $_SESSION['consola']['sesion_iniciada']){
    $conexion_shh = ssh2_connect($_SESSION['consola']['ip'], 22);
    if(ssh2_auth_password($conexion_shh, $_SESSION['consola']['usuario'], $_SESSION['consola']['pass'])){
        //usuario
        ?>
        <h1>Consola</h1> 
        <form action="index.php" style="float:right;"  method='POST'><input type="hidden" name='servicio' value="ssh"><input type="hidden" name='destruir' value="destruir"><input type="submit"  value="Cerrar sesión"></form>
        <form action="index.php" style="float:right; margin-right:1%"  method='POST'><input type="hidden" name='servicio' value="ssh"><input type="hidden" name='limpiar' value="limpiar"><input type="submit"  value="Limpiar terminal"></form>
        <div id="consola">
        <?php
        if(isset($_POST['limpiar']) && $_POST['limpiar'] == 'limpiar' || $_POST['orden'] == 'clear'){
            unset($_SESSION['consola']['salida']);
            $_SESSION['consola']['salida'][0] = '<span style="color:#00ff3c">'.$_SESSION['consola']['usuario'].'</span><span style="color:#1155cc">@</span><span style="color:#ffff00">'.$_SESSION['consola']['hostname'].'</span>:<span style="color:#1155cc">'.$_SESSION['consola']['directorio_actual'].'$ </span>';
        }
        if(isset($_SESSION['consola']['directorio_actual'])){
            $orden = 'cd '.$_SESSION['consola']['directorio_actual'];
            $stream = ssh2_exec($conexion_shh, $orden);
            usleep(500000);
        } else{
            $_SESSION['consola']['directorio_actual'] = '~';
        }
        //orden del usuario
        if(isset($_POST['orden'])){
            $orden = $_POST['orden'];
            array_push($_SESSION['consola']['salida'], '<span style="color:#00ff3c">'.$_SESSION['consola']['usuario'].'</span><span style="color:#1155cc">@</span><span style="color:#ffff00">'.$_SESSION['consola']['hostname'].'</span>:<span style="color:#1155cc">'.$_SESSION['consola']['directorio_actual'].'$ </span>'.$orden);
            if(substr($orden, 0, 3) == 'cd '){
                $orden_directorio = $orden.'; pwd';
                $stream = ssh2_exec($conexion_shh, $orden_directorio);
                usleep(250000);
                $directorio = stream_get_contents($stream);
                $_SESSION['consola']['directorio_actual'] = preg_split('/\r\n|\r|\n/', $directorio)[0];
            }elseif(substr($orden, 0, 5) == 'sudo '){
                $orden = 'echo '.$_SESSION['consola']['pass'].'| sudo -S '.$orden;
            }else{
                $orden = 'cd '.$_SESSION['consola']['directorio_actual'].'; '.$orden;
            }
            $stream = ssh2_exec($conexion_shh, $orden);
            usleep(500000);
            $resultado = stream_get_contents($stream);
            $resultado = preg_split('/\r\n|\r|\n/', $resultado);
            foreach($resultado as $value){
                array_push($_SESSION['consola']['salida'],$value);
            }
        }
        foreach($_SESSION['consola']['salida'] as $value){
            echo '<pre>'.$value.'</pre>';
        }
        ?>
        <form action="index.php" method="POST"><span style="color:#00ff3c;cursor:none;text-decoration:none"><?=$_SESSION['consola']['usuario']?></span><span style="color:#1155cc;cursor:none;text-decoration:none">@</span><span style="color:#ffff00;cursor:none;text-decoration:none"><?=$_SESSION['consola']['hostname']?></span>:<span style="color:#1155cc;cursor:none;text-decoration:none"><?=$_SESSION['consola']['directorio_actual']?>$ </span><input style="color:white;background:#070707;width:50%" autofocus type="text" name="orden"><input type="hidden" name='servicio' value="ssh"></form>
        </div>
        <?php
    }else{
        echo '<script>alert("La autenticación a fallado")</script>';
        echo '<script>enviar("ssh")</script>';
    }

}else{
    if(isset($_POST['ip']) && filter_var($_POST['ip'], FILTER_VALIDATE_IP) && isset($_POST['usuario']) && !empty($_POST['pass']) 
    && !empty($_POST['ip']) && !empty($_POST['usuario'])){
            
        $_SESSION['consola']['ip']      = $_POST['ip'];
        $_SESSION['consola']['usuario'] = $_POST['usuario'];
        $_SESSION['consola']['pass']    = $_POST['pass'];
    
        $conexion_shh = ssh2_connect($_SESSION['consola']['ip'], 22);
        if(ssh2_auth_password($conexion_shh, $_SESSION['consola']['usuario'], $_SESSION['consola']['pass'])){
            $_SESSION['consola']['sesion_iniciada'] = true;
            $shell = ssh2_shell($conexion_shh);
            usleep(500000);
            $stream = stream_get_contents($shell);
            $_SESSION['consola']['salida'] = preg_split('/\r\n|\r|\n/', $stream); //TELITA CON ESTO. SERIA PHP_EOL PERO NO FUNCIONABA
            /*
            foreach($_SESSION['consola']['salida'] as $value){
                echo '<pre>'.$value.'</pre>';
            }
            */
            $orden = 'hostname';
            $stream = ssh2_exec($conexion_shh, $orden);
            usleep(500000);
            $hostname = stream_get_contents($stream);
            $_SESSION['consola']['hostname'] = preg_split('/\r\n|\r|\n/', $hostname)[0];
            echo '<script>enviar("ssh")</script>';
        }else{
            echo '<script>alert("La autenticación a fallado")</script>';
            echo '<script>enviar("ssh")</script>';
        }
    
    }else{
        ?>
        <h1>Conexión SSH</h1>
        <form action="index.php" method="POST">
        Dirección IP:<br>
        <input type="text" name="ip"><br>
        Usuario:<br>
        <input type="text" name="usuario"><br>
        Contraseña:<br>
        <input type="password" name="pass"><br>
        <input type="hidden" name="servicio" value="ssh">
        <input type="submit" value="conectar">
        </form>
        <?php
    }
}

?>