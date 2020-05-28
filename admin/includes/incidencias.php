<?php
if(isset($_POST['incidencia_id']) && !empty($_POST['incidencia_id']) ){
    if(isset($_POST['accion']) && $_POST['accion'] == 'resolver' ){
        consulta('UPDATE incidencias SET estado="Resuelto" WHERE id='.$_POST['incidencia_id'].'');
    }
    $datos_incidencia = consulta('SELECT * FROM incidencias WHERE id='.$_POST['incidencia_id'].'');

/*ENVIAR MENSAJE */
    if(isset($_POST['mensaje']) && !empty($_POST['mensaje'])|| !empty($_FILES)){        
        consulta('INSERT INTO mensajes VALUES("","'.limpia($_POST['mensaje']).'",CURRENT_TIMESTAMP(),"'.$_SESSION['id'].'","'.$_POST['incidencia_id'].'")');
        $nombre_carpeta .= $_POST['incidencia_id'];
        $nombre_carpeta .= $datos_incidencia[0]['titulo'];
        $nombre_carpeta = md5(md5($nombre_carpeta));
        
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
                            consulta('INSERT INTO mensajes VALUES("","<img class=\'imagen\' src=\''.$nuevaruta.' \'>",CURRENT_TIMESTAMP(),"'.$_SESSION['id'].'","'.$_POST['incidencia_id'].'")');
                        }elseif($tipo_archivo == 'video'){
                            consulta('INSERT INTO mensajes VALUES("","<video width=\'320\' height=\'240\' controls><source src=\''.$nuevaruta.'\' type=\'video/'.$extension.'\'>Tu navegador no soporta el video.</video>",CURRENT_TIMESTAMP(),"'.$_SESSION['id'].'","'.$_POST['incidencia_id'].'")');
                        }else{
                            consulta('INSERT INTO mensajes VALUES("","Fichero adjunto: <a href=\''.$nuevaruta.'\'>'.limpia($_FILES['fichero']['name'][$i]).'</a> ('.formatBytes($_FILES['fichero']['size'][$i]).')",CURRENT_TIMESTAMP(),"'.$_SESSION['id'].'","'.$_POST['incidencia_id'].'")');
                        }
                    }else{
                        echo '<script>alert("El archivo '.limpia($_FILES['fichero']['name'][$i]).' no se ha podido adjuntar")</script>';
                    }
                }
            }
        }
    }


    $mensajes         = consulta('SELECT * FROM mensajes WHERE incidencia='.$_POST['incidencia_id'].'');
    if($datos_incidencia[0]['estado'] == 'No resuelto'){
        echo '<h1>'.limpia($datos_incidencia[0]['titulo']).' <span style="color:red;">('.$datos_incidencia[0]['estado'].')</span></h1><form style="float:right;margin-top:-5%;font-size=2vw" action="index.php" method="POST"><input type="hidden" name="servicio" value="incidencias"></input><input type="hidden" name="accion" value="resolver"></input><input type="hidden" name="incidencia_id" value="'.$_POST['incidencia_id'].'"></input><input type="submit" value="MARCAR COMO RESUELTA"></input></form>';
    }else{
        echo '<h1>'.limpia($datos_incidencia[0]['titulo']).' ('.$datos_incidencia[0]['estado'].')</h1>';
    }
    ?>
    <div id='chat'>
        <?php 
            foreach($mensajes as $value){
                $nombre_usuario = consulta('SELECT usuario FROM usuario where id='.$value['usuario'].'');
                if($value['usuario'] == $_SESSION['id']){
                    echo '<div class="bocadillo oscuro">';
                }else{
                    echo '<div class="bocadillo">';
                }
                echo '<img src="./img/avatar.png" alt="Avatar">';
                echo nl2br('<strong>'.limpia($nombre_usuario[0][0]).'</strong><p>'.$value['contenido'].'</p>');
                if($value['usuario'] == $_SESSION['id']){
                    echo '<span class="time-left">'.$value['fecha'].'</span>';
                }else{
                    echo '<span class="time-right">'.$value['fecha'].'</span>';
                }
                echo '</div>';
            }
        
        ?>
    </div>
    <form action="index.php" method="POST" enctype="multipart/form-data">
        <textarea style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box; width:95%;height:auto" placeholder="Escribe un mensaje..." name="mensaje"></textarea>
        <input style="float:right;" type="submit" value="enviar">
        <div style="width:45%;float:left;">
            <h3>Adjuntar Archivos</h3>
            <input name="fichero[]" type="file" multiple="multiple" />
        </div>
        <input type="hidden" name="servicio" value="incidencias"></input>
        <input type="hidden" name="incidencia_id" value="<?=$_POST['incidencia_id']?>"></input>
    </form>
    <?php
}else{
?>
<h1>Incidencias</h1>
</form>
<div style="width:100%;overflow:scroll;overflow-x:hidden;max-height:70%;overflow-y:auto">
    <table style="width:100%">
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Estado</th>
            <th>Creada por</th>
            <th>Fecha de creación</th>
        </tr>
        <?php
            $id_incidencias      = consulta('SELECT id_incidencia FROM destinatarios_incidencias WHERE id_destinatario='.$_SESSION['id'].' UNION SELECT id FROM incidencias WHERE remitente = '.$_SESSION['id'].'');
            // para ordenar
            $consulta = 'SELECT * FROM incidencias WHERE ID IN (';
            foreach($id_incidencias as $value){
                $consulta .= $value[0].',';
            }
            $consulta = rtrim($consulta, ",").') ORDER BY estado ASC,fecha DESC';
            $consulta = consulta($consulta); //muy originl con los nombres
            foreach($consulta as $value){
                echo '<tr>';
                echo '<td>'.$value['id'].'</td>';
                echo '<td>'.limpia($value['titulo']).'</td>';
                if($value['estado'] == 'No resuelto'){
                    echo '<td style="color:red;">'.$value['estado'].'</td>';
                }else{
                    echo '<td>'.$value['estado'].'</td>';
                }
                
                if($value['remitente'] == $_SESSION['id']){
                    echo '<td>Tú</td>';
                }else{
                    $usuario = consulta('SELECT usuario FROM usuario WHERE id='.$value['remitente'].'');
                    echo '<td>'.limpia($usuario[0][0]).'</td>';
                }
                echo '<td>'.$value['fecha'].'</td>';
                echo '<th><center><form action="index.php" method="POST" style="width:100%;"><input type="hidden" name="servicio" value="incidencias"></input><input type="hidden" name="incidencia_id" value="'.$value['id'].'"></input><input type="submit" value="ver"></input></center></form></th>';
                echo '</tr>';
            }          
        ?>
    </table>
</div>
<?php
}
?>