<?php
if(isset($_POST['destruir']) && $_POST['destruir'] == 'destruir'){
    unset($_SESSION['sftp']);
}
if(isset($_SESSION['sftp']['sesion_iniciada']) && $_SESSION['sftp']['sesion_iniciada']){
    
    $conexion_sftp = ssh2_connect($_SESSION['sftp']['ip'], $_SESSION['sftp']['puerto']);
    if(ssh2_auth_password($conexion_sftp, $_SESSION['sftp']['usuario'], $_SESSION['sftp']['pass'])){
        $sftp             = ssh2_sftp($conexion_sftp);
        $sftp_fd          = intval($sftp);
        if(!isset($_POST['directorio'])){
            $_POST['directorio'] = './';
        }
        $ruta             = $_SESSION['sftp']['directorio_actual'].$_POST['directorio'];
        $abrir_directorio = @opendir("ssh2.sftp://$sftp_fd".$ruta);// el @ es para que no salgan avisos
        if($abrir_directorio){
            $_SESSION['sftp']['directorio_actual'] = $_SESSION['sftp']['directorio_actual'].$_POST['directorio'];
            $_SESSION['sftp']['directorio_actual'] = ssh2_sftp_realpath($sftp, $_SESSION['sftp']['directorio_actual']).'/';
            if(isset($_POST['accion']) && $_POST['accion'] == 'borrar' && isset($_POST['fichero'])){
                $nombre_archivo = $_POST['fichero'];
            }elseif(isset($_POST['accion']) && $_POST['accion'] == 'subir' && isset($_FILES['fichero'])){
                $sftp_subir_archivo = @fopen('ssh2.sftp://'.$sftp.$_SESSION['sftp']['directorio_actual'].$_FILES['fichero']['name'], 'w'); //EXPERIMENTO 4
                $contenido_archivo = file_get_contents($_FILES['fichero']['tmp_name']);
                fwrite($sftp_subir_archivo, $contenido_archivo);
                fclose($sftp_subir_archivo);
            }
            ?>
            <h1>SFTP</h1> 
                <form action="index.php" style="float:right;"  method='POST'><input type="hidden" name='servicio' value="sftp"><input type="hidden" name='destruir' value="destruir"><input type="submit"  value="Cerrar sesión"></form>
                <div style="width:100%: color:black; background: rgb(153, 180,209)"><span style="color:black;"><?=$_SESSION['sftp']['directorio_actual']?></span></div>
                <table id="sftp">
                <tr>
                    <th>Nombre</th>
                    <th>Tamaño</th>
                    <th>Modificado</th>
                    <th>Permisos</th>
                    <th>Propietario</th>
                </tr>
            <?php
            $ficheros         = [];
            while (false != ($fichero = readdir($abrir_directorio))){
                if($fichero == $nombre_archivo){
                    /*
                    echo $_SESSION['sftp']['directorio_actual'].$fichero.'<br>';
                    ssh2_sftp_unlink($sftp, $_SESSION['sftp']['directorio_actual'].$fichero);
                    echo "ssh2.sftp://$sftp_fd".$_SESSION['sftp']['directorio_actual'].$fichero;
                    unlink("ssh2.sftp://$sftp_fd".$_SESSION['sftp']['directorio_actual'].$fichero);
                    */
                    // COMO LO DE ARRIBA NO FUNCIONA ME TOCA HACERLO MANUALMENTE :(
                    ssh2_exec($conexion_sftp, 'rm "'.$_SESSION['sftp']['directorio_actual'].$fichero.'"');
                    continue;
                }
                $stat = ssh2_sftp_stat($sftp, $ruta.$fichero); 
                while($stat == false){
                    $stat_normal         = ssh2_sftp_stat($sftp, $ruta.$fichero);
                    $stat_link_simbolico = ssh2_sftp_lstat($sftp, $ruta.$fichero);
                    if($stat_normal){
                        $stat = $stat_normal;
                    }elseif($stat_link_simbolico){
                        $stat = $stat_link_simbolico;
                    }
                }
                //setear usuario
                $stream = ssh2_exec($conexion_sftp, "getent passwd ".$stat['uid']." | cut -d: -f1");
                usleep(50000); // no puedo poner mucho sino tarda demasiado este valor es un poco justo
                $datos                             = stream_get_contents($stream);
                $ficheros[$fichero]['propietario'] = preg_split('/\r\n|\r|\n/', $datos)[0];
                //setear permisos y tipo archivo
                $octal_tipo_archivo = substr(decoct($stat['mode']), 0,2);
                if($octal_tipo_archivo == '10'){
                    $extension_3 = substr($fichero, -3);
                    $extension_4 = substr($fichero, -4);
                    $ficheros[$fichero]['tipo_archivo'] = 'fichero';
                    $ficheros[$fichero]['size']         = formatBytes($stat['size']); //esta función no la he creado yo link en funciones.php
                    if($stat == $stat_link_simbolico){
                        $ficheros[$fichero]['tipo_archivo'] = 'fichero_link';
                    }elseif($extension_3 == 'cfg' || $extension_3 == 'ini' || $extension_3 == 'cnf' || $extension_4 == 'conf'){
                        $ficheros[$fichero]['tipo_archivo'] = 'fichero_configuracion';
                    }elseif($extension_3 == 'mp4' || $extension_3 == 'avi' || $extension_3 == 'mkv' || $extension_4 == 'webm'){
                        $ficheros[$fichero]['tipo_archivo'] = 'fichero_video';
                    }elseif($extension_3 == 'mp3' || $extension_3 == 'wav' || $extension_3 == 'm4a' || $extension_4 == 'flac'){
                        $ficheros[$fichero]['tipo_archivo'] = 'fichero_audio';
                    }elseif($extension_3 == 'jpg' || $extension_3 == 'png' || $extension_3 == 'gif' || $extension_4 == 'jpeg'){
                        $ficheros[$fichero]['tipo_archivo'] = 'fichero_imagen';
                    }elseif($extension_3 == 'php' || $extension_3 == '.py' || $extension_3 == 'css' || $extension_3 == '.js' || $extension_4 == 'html' || $extension_4 == 'java'){
                        $ficheros[$fichero]['tipo_archivo'] = 'fichero_codigo';
                    }elseif($extension_3 == 'zip' || $extension_3 == 'rar' || $extension_3 == 'tar' || $extension_3 == '.7z' || $extension_3 == 'iso'){
                        $ficheros[$fichero]['tipo_archivo'] = 'fichero_comprimido';
                    }
                    
                }elseif($octal_tipo_archivo == '40'){
                    $ficheros[$fichero]['tipo_archivo'] = 'directorio';
                    $ficheros[$fichero]['size']         = '';
                }
                $octal       = substr(decoct($stat['mode']), -3);
                $octal_lista = str_split($octal);
                $lista   = [ '7'=>'rwx', '6'=>'rw-', '5'=>'r-x', '4'=>'r--', '3'=>'-wx', '2'=>'-w-', '1'=>'--x','0'=>'---'];
                $permisos = '';
                foreach($octal_lista as $value){
                    $permisos .= $lista[$value];
                }
                $ficheros[$fichero]['permisos']   = $permisos;
                //setear fecha
                $ficheros[$fichero]['modificado'] = date("Y-m-d H:i:s", $stat['mtime']) ;
            }
            ksort($ficheros);
            foreach($ficheros as $key => $value){
                echo '<tr>';
            if($value['tipo_archivo'] == 'directorio'){
                echo '<form action="index.php" method="POST" name="directorio_'.limpia($key).'"><input type="hidden" name="servicio" value="sftp"><input type="hidden" name="directorio" value="'.limpia($key).'/"></input></form>';
                echo '<td style="cursor:pointer;" onclick="enviar(\'directorio_'.limpia($key).'\')"><img src="img/'.$value['tipo_archivo'].'.png" style="width:25px;"> '.limpia($key).'</td>';
            }else{
                echo '<form action="index.php" method="POST" name="fichero_'.limpia($key).'"><input type="hidden" name="servicio" value="descargar"><input type="hidden" name="fichero" value="'.limpia($key).'"></input></form>';
                echo '<td style="cursor:pointer;" onclick="enviar(\'fichero_'.limpia($key).'\')"><img src="img/'.$value['tipo_archivo'].'.png" style="width:25px;"> '.limpia($key).'</td>';
            }
            echo '<td>'.$value['size'].'</td>';
            echo '<td>'.$value['modificado'].'</td>';
            echo '<td>'.$value['permisos'].'</td>';
            echo '<td>'.limpia($value['propietario']).'</td>';
            echo '</tr>';
            }
            echo '</table>';

        }else{
            echo '<script>alert("El directorio no existe")</script>';
            echo '<script>enviar("sftp")</script>';
        }
       
        
    }else{
        echo '<script>alert("La autenticación ha fallado")</script>';
        echo '<script>enviar("sftp")</script>';
    }
    ?>
    <hr>
    <h1>Borrar archivo del directorio <?=$_SESSION['sftp']['directorio_actual']?></h1>
    <form action="index.php" method="POST">
        <input type="hidden" name="servicio" value="sftp"></input>
        <input type="hidden" name="accion" value="borrar"></input>
        Nombre del archivo: <input type="text" name="fichero"></input> <input type="submit" value="Borrar"></input>
    </form>
    <hr>
    <h1>Subir archivo al directorio <?=$_SESSION['sftp']['directorio_actual']?></h1>
    <form action="index.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="servicio" value="sftp"></input>
        <input type="hidden" name="accion" value="subir"></input>
        <input type="file" name="fichero"></input><br>
        <input type="submit" value="Subir"></input>
    </form>
    <?php

}elseif(isset($_POST['ip']) && filter_var($_POST['ip'], FILTER_VALIDATE_IP) && isset($_POST['usuario']) && !empty($_POST['pass']) 
&& !empty($_POST['ip']) && !empty($_POST['usuario']) && isset($_POST['puerto']) && filter_var($_POST['puerto'], FILTER_VALIDATE_INT)){
    ?>
    <h1>SFTP</h1> 
        <form action="index.php" style="float:right;"  method='POST'><input type="hidden" name='servicio' value="sftp"><input type="hidden" name='destruir' value="destruir"><input type="submit"  value="Cerrar sesión"></form>
        <div style="width:100%: color:black; background: rgb(153, 180,209)"><span style="color:black;">/</span></div>
        <table id="sftp">
        <tr>
            <th>Nombre</th>
            <th>Tamaño</th>
            <th>Modificado</th>
            <th>Permisos</th>
            <th>Propietario</th>
        </tr>
    <?php
    $_SESSION['sftp']['ip']                 = $_POST['ip'];
    $_SESSION['sftp']['usuario']            = $_POST['usuario'];
    $_SESSION['sftp']['pass']               = $_POST['pass'];
    $_SESSION['sftp']['puerto']             = $_POST['puerto'];
    $_SESSION['sftp']['directorio_actual']  = '/';
    $conexion_sftp = ssh2_connect($_SESSION['sftp']['ip'], $_SESSION['sftp']['puerto']);
    if(ssh2_auth_password($conexion_sftp, $_SESSION['sftp']['usuario'], $_SESSION['sftp']['pass'])){
        $_SESSION['sftp']['sesion_iniciada'] = true;
        $sftp             = ssh2_sftp($conexion_sftp);
        $sftp_fd          = intval($sftp);
        $abrir_directorio = @opendir("ssh2.sftp://$sftp_fd".$_SESSION['sftp']['directorio_actual']);
        $ficheros         = [];
        while (false != ($fichero = readdir($abrir_directorio))){
            $stat = ssh2_sftp_stat($sftp, $_SESSION['sftp']['directorio_actual'].$fichero); 
            while($stat == false){//BUG ENORME PHP LAS CONSULTAS PARES DE SFTP STAT SIEMPRE DEVUELVEN FALSE
                $stat_normal         = ssh2_sftp_stat($sftp, $_SESSION['sftp']['directorio_actual'].$fichero);
                $stat_link_simbolico = ssh2_sftp_lstat($sftp, $_SESSION['sftp']['directorio_actual'].$fichero);
                if($stat_normal){
                    $stat = $stat_normal;
                }elseif($stat_link_simbolico){
                    $stat = $stat_link_simbolico;
                }
            }
            
            //setear usuario
            $stream = ssh2_exec($conexion_sftp, "getent passwd ".$stat['uid']." | cut -d: -f1");
            usleep(20000); // no puedo poner mucho sino tarda demasiado este valor es un poco justo
            $datos                             = stream_get_contents($stream);
            $ficheros[$fichero]['propietario'] = preg_split('/\r\n|\r|\n/', $datos)[0];
            //setear permisos
            $octal_tipo_archivo = substr(decoct($stat['mode']), 0,2);
            if($octal_tipo_archivo == '10'){
                $extension_3 = substr($fichero, -3);
                $extension_4 = substr($fichero, -4);
                $ficheros[$fichero]['tipo_archivo'] = 'fichero';
                $ficheros[$fichero]['size']         = formatBytes($stat['size']); //esta función no la he creado yo link en funciones.php
                if($stat == $stat_link_simbolico){
                    $ficheros[$fichero]['tipo_archivo'] = 'fichero_link';
                }elseif($extension_3 == 'cfg' || $extension_3 == 'ini' || $extension_3 == 'cnf' || $extension_4 == 'conf'){
                    $ficheros[$fichero]['tipo_archivo'] = 'fichero_configuracion';
                }elseif($extension_3 == 'mp4' || $extension_3 == 'avi' || $extension_3 == 'mkv' || $extension_4 == 'webm'){
                    $ficheros[$fichero]['tipo_archivo'] = 'fichero_video';
                }elseif($extension_3 == 'mp3' || $extension_3 == 'wav' || $extension_3 == 'm4a' || $extension_4 == 'flac'){
                    $ficheros[$fichero]['tipo_archivo'] = 'fichero_audio';
                }elseif($extension_3 == 'jpg' || $extension_3 == 'png' || $extension_3 == 'gif' || $extension_4 == 'jpeg'){
                    $ficheros[$fichero]['tipo_archivo'] = 'fichero_imagen';
                }elseif($extension_3 == 'php' || $extension_3 == '.py' || $extension_3 == 'css' || $extension_3 == '.js' || $extension_4 == 'html' || $extension_4 == 'java'){
                    $ficheros[$fichero]['tipo_archivo'] = 'fichero_codigo';
                }elseif($extension_3 == 'zip' || $extension_3 == 'rar' || $extension_3 == 'tar' || $extension_3 == '.7z' || $extension_3 == 'iso'){
                    $ficheros[$fichero]['tipo_archivo'] = 'fichero_comprimido';
                }
                
            }elseif($octal_tipo_archivo == '40'){
                $ficheros[$fichero]['tipo_archivo'] = 'directorio';
                $ficheros[$fichero]['size']         = '';
            }
            $octal       = substr(decoct($stat['mode']), -3);
            $octal_lista = str_split($octal);
            $lista   = [ '7'=>'rwx', '6'=>'rw-', '5'=>'r-x', '4'=>'r--', '3'=>'-wx', '2'=>'-w-', '1'=>'--x','0'=>'---'];
            $permisos = '';
            foreach($octal_lista as $value){
                $permisos .= $lista[$value];
            }
            $ficheros[$fichero]['permisos']   = $permisos;
            //setear fecha
            $ficheros[$fichero]['modificado'] = date("Y-m-d H:i:s", $stat['mtime']) ;
        }
        ksort($ficheros);
        foreach($ficheros as $key => $value){
            echo '<tr>';
            if($value['tipo_archivo'] == 'directorio'){
                echo '<form action="index.php" method="POST" name="directorio_'.limpia($key).'"><input type="hidden" name="servicio" value="sftp"><input type="hidden" name="directorio" value="'.limpia($key).'/"></input></form>';
                echo '<td style="cursor:pointer;" onclick="enviar(\'directorio_'.limpia($key).'\')"><img src="img/'.$value['tipo_archivo'].'.png" style="width:25px;"> '.limpia($key).'</td>';
            }else{
                echo '<form action="index.php" method="POST" name="fichero_'.limpia($key).'"><input type="hidden" name="servicio" value="descargar"><input type="hidden" name="fichero" value="'.limpia($key).'"></input></form>';
                echo '<td style="cursor:pointer;" onclick="enviar(\'fichero_'.limpia($key).'\')"><img src="img/'.$value['tipo_archivo'].'.png" style="width:25px;"> '.limpia($key).'</td>';
            }
            echo '<td>'.$value['size'].'</td>';
            echo '<td>'.$value['modificado'].'</td>';
            echo '<td>'.$value['permisos'].'</td>';
            echo '<td>'.limpia($value['propietario']).'</td>';
            echo '</tr>';
        }
        echo '</table>';
        
    }else{
        echo '<script>alert("La autenticación ha fallado")</script>';
        echo '<script>enviar("sftp")</script>';
    }
    ?>
    <hr>
    <h1>Borrar archivo del directorio <?=$_SESSION['sftp']['directorio_actual']?></h1>
    <form action="index.php" method="POST">
        <input type="hidden" name="servicio" value="sftp"></input>
        <input type="hidden" name="accion" value="borrar"></input>
        Nombre del archivo: <input type="text" name="fichero"></input> <input type="submit" value="Borrar"></input>
    </form>
    <hr>
    <h1>Subir archivo al directorio <?=$_SESSION['sftp']['directorio_actual']?></h1>
    <form action="index.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="servicio" value="sftp"></input>
        <input type="hidden" name="accion" value="subir"></input>
        <input type="file" name="fichero"></input><br>
        <input type="submit" value="Subir"></input>
    </form>
    <?php
}else{
    ?>
    <h1>Conectarse a equipo(require ssh)</h1>
    <form action="index.php" method="POST">
        Dirección IP:<br>
        <input type="text" name="ip" required><br>
        Puerto:<br>
        <input type="number" name="puerto" min="1" max="65535" value="22" required><br>
        Usuario:<br>
        <input type="text" name="usuario" required><br>
        Contraseña:<br>
        <input type="password" name="pass"><br>
        <input type="hidden" name="servicio" value="sftp">
        <input type="submit" value="conectar">
        </form>
    
    <?php
}


?>