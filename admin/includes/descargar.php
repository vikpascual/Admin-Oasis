<?php
if(isset($_SESSION['sftp']['sesion_iniciada']) && $_SESSION['sftp']['sesion_iniciada']){
    $conexion_sftp = ssh2_connect($_SESSION['sftp']['ip'], $_SESSION['sftp']['puerto']);
    if(ssh2_auth_password($conexion_sftp, $_SESSION['sftp']['usuario'], $_SESSION['sftp']['pass'])){
        $sftp             = ssh2_sftp($conexion_sftp);
        $sftp_fd          = intval($sftp);
        $fichero          = $_POST['fichero'];
        $lista_ficheros         = glob('tmp/*'); // vaciamos tmp
        $lista_ficheros_ocultos = glob('tmp/{,.}*', GLOB_BRACE);
        foreach($lista_ficheros as $nombre){ 
            unlink($nombre);
        }
        foreach($lista_ficheros_ocultos as $nombre){ 
            unlink($nombre);
        }
        //$contenido        = file_get_contents("ssh2.sftp://$sftp".$_SESSION['sftp']['directorio_actual'].$fichero); FALLO EN EL FORMATO DEL ARCHIVO REMOTO FUNCIONA UNICAMENTE CON FICHEROS DE TEXTO NORMALES
        
        //$sftp_subir_archivo = @fopen('ssh2.sftp://'.$sftp.$_SESSION['sftp']['directorio_actual'].$fichero, 'r'); //EXPERIMENTO 4 
        if(!@copy("ssh2.sftp://$sftp_fd".$_SESSION['sftp']['directorio_actual'].$fichero,'./tmp/'.$fichero)){
            echo '<script>alert("Error al descargar el fichero")</script>';
        }else{
            $tipo_contenido = mime_content_type('./tmp/'.$fichero); //BASTANTES PROBLEMAS CON ESTO, NO TIENE NINGUN SENTIDO
            $peso           = filesize('./tmp/'.$fichero);
            header("Content-Length: ".$peso);
            header('Content-Type: '.$tipo_contenido);
            header("Content-Transfer-Encoding: Binary"); 
            header("Content-disposition: attachment; filename=\"".$fichero."\""); 
            readfile('./tmp/'.$fichero);
            exit(); 
        }
        /* FALLO EN EL FORMATO DEL ARCHIVO REMOTO FUNCIONA UNICAMENTE CON FICHEROS DE TEXTO NORMALES
        if(file_put_contents("tmp/$fichero", $contenido)){
            $url = 'https://'.$_SERVER['HTTP_HOST'].'/admin/tmp/'.$fichero ;
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary"); 
            header("Content-disposition: attachment; filename=\"".$fichero."\""); 
            readfile($url);
            $lista_ficheros         = glob('tmp/*'); // vaciamos tmp
            $lista_ficheros_ocultos = glob('tmp/{,.}*', GLOB_BRACE);
            foreach($lista_ficheros as $nombre){ 
                unlink($nombre);
            }
            foreach($lista_ficheros_ocultos as $nombre){ 
                 unlink($nombre);
            }
        }else{
            echo '<script>alert("Error al descargar el fichero")</script>';
            echo '<script>enviar("sftp")</script>';
        } 
        */
    }
}
?>