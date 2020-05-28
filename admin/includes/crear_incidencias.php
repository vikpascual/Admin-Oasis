<?php
if(isset($_POST['accion'])){
    if(isset($_POST['titulo'])){
        if(isset($_POST['mensaje'])){
            if(!empty($_POST['destinatarios'])){
                consulta('INSERT INTO incidencias VALUES("","'.$_POST['titulo'].'","No resuelto",CURRENT_TIMESTAMP(),"'.$_SESSION['id'].'")');
                
                $id_incidencia = consulta('SELECT id FROM incidencias WHERE remitente="'.$_SESSION['id'].'" ORDER BY fecha DESC');
                $id_incidencia = $id_incidencia[0]['id'];
                foreach($_POST['destinatarios'] as $value){
                    if($value == $_SESSION['id']){
                        continue;
                    }
                    consulta('INSERT INTO destinatarios_incidencias values ("'.$id_incidencia.'","'.$value.'")');
                }
                consulta('INSERT INTO mensajes VALUES("","'.limpia($_POST['mensaje']).'",CURRENT_TIMESTAMP(),"'.$_SESSION['id'].'","'.$id_incidencia.'")');
                $nombre_carpeta = $id_incidencia.$_POST['titulo'];
                $nombre_carpeta = md5(md5($nombre_carpeta));
                mkdir('./tmp/'.$nombre_carpeta);

                if(!empty($_FILES['fichero'])){
                    $total = count($_FILES['fichero']['name']);
                    //DETERMINAR TIPO ARCHIVO
                    $video                      = array("mov", "avi", "wmv", "mp4","mkv");
                    $imagen                     = array("jpg", "png", "jpeg", "gif", "tiff");
                    $extensiones_no_permitiadas = array("php", "phtml", "php3", "php4", "php5", "php7", "phps", "php-s", "pht","exe");
                    for( $i=0 ; $i < $total ; $i++ ) {
                        $tmpruta   = $_FILES['fichero']['tmp_name'][$i];
                        $extension = end(explode('.',$_FILES['fichero']['name'][$i]));
                        if(!in_array($extension,$extensiones_no_permitiadas)){
                            if(in_array($extension,$imagen)){
                                $tipo_archivo = 'imagen';
                            }elseif(in_array($extension,$video)){
                                $tipo_archivo = 'video';
                            }else{
                                $tipo_archivo = 'otro';
                            }
                        }else{
                            continue;
                        }
                        if ($tmpruta != ""){ //a veces da esta vacia no se pork
                            $md5 = md5(file_get_contents($_FILES['fichero']['tmp_name'][$i]));
                            $nuevaruta = "./tmp/" .$nombre_carpeta.'/'.limpia($_FILES['fichero']['name'][$i]);
                            if(move_uploaded_file($tmpruta, $nuevaruta)) {
                                if($tipo_archivo == 'imagen'){
                                    consulta('INSERT INTO mensajes VALUES("","<img class=\'imagen\' src=\''.$nuevaruta.' \'>",CURRENT_TIMESTAMP(),"'.$_SESSION['id'].'","'.$id_incidencia.'")');
                                }elseif($tipo_archivo == 'video'){
                                    consulta('INSERT INTO mensajes VALUES("","<video width=\'320\' height=\'240\' controls><source src=\''.$nuevaruta.'\' type=\'video/'.$extension.'\'>Tu navegador no soporta el video.</video>",CURRENT_TIMESTAMP(),"'.$_SESSION['id'].'","'.$id_incidencia.'")');
                                }else{
                                    consulta('INSERT INTO mensajes VALUES("","Fichero adjunto: <a href=\''.$nuevaruta.'\'>'.limpia($_FILES['fichero']['name'][$i]).'</a> ('.formatBytes($_FILES['fichero']['size'][$i]).')",CURRENT_TIMESTAMP(),"'.$_SESSION['id'].'","'.$id_incidencia.'")');
                                }
                            }else{
                                echo '<script>alert("El archivo '.limpia($_FILES['fichero']['name'][$i]).' no se ha podido adjuntar")</script>';
                            }
                        }
                    }
                }

                echo '<script>alert("La incidencia se ha reportado")</script>';
            }else{
                echo '<script>alert("Tienes que elegir al menos un destinatario")</script>';
            }
        }else{
            echo '<script>alert("Tienes que poner por lo menos un mensaje introduciendo el problema")</script>';
        }
    }else{
        echo '<script>alert("Tienes que poner un título")</script>';
    }
}

?>
<h1>Crear Incidencia</h1>
<form action="index.php" method="POST" id="incidencia" enctype="multipart/form-data">
    <input type="text" name="titulo" placeholder="TÍTULO" required></input>
    <textarea style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box; width:100%;height:70%" name="mensaje"></textarea>
    <div style="width:45%;float:left;">
        <h3>Adjuntar Archivos</h3>
        <input name="fichero[]" type="file" multiple="multiple" />
    </div>
    <div style="width:45%;float:right; overflow:scroll;overflow-x:hidden">
    <h3>Selecionar destinatarios:</h3>
    <?php
        $lista_usuarios = consulta('SELECT id,usuario FROM usuario ORDER BY usuario');
        $contador = 0;
        foreach($lista_usuarios as $value){
            if($value['id'] != $_SESSION['id']){
                echo '<input type="checkbox" name="destinatarios[]" value="'.$value['id'].'" id="'.$value['id'].'"/><label for="'.$value['id'].'"></label> <label> '.limpia($value['usuario']).'</label><br>';
            }
        }

    ?>
    </div>
    <br>
    <input type="hidden" name="servicio" value="crear_incidencia"></input>
    <input type="hidden" name="accion" value="crear_incidencia"></input>
    <input type="submit" value="Crear incidencia">
</form>