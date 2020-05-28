<?php
    if(isset($_POST['accion']) && $_POST['accion'] == 'cambiar'){
        if(isset($_POST['pass_actual']) && !empty($_POST['pass_actual']) && isset($_POST['pass_nueva']) && !empty($_POST['pass_nueva']) &&
        isset($_POST['pass_nueva_2']) && !empty($_POST['pass_nueva_2']) ){
            $pass     = sha1(md5(sha1($_POST['pass_actual'])));
            $consulta = consulta('SELECT id FROM usuario WHERE id='.$_SESSION['id'].' AND password = "'.$pass.'"');
            if(!empty($consulta)){
                if($_POST['pass_nueva'] == $_POST['pass_nueva_2']){
                    if($_POST['pass_nueva'] != $_POST['pass_actual']){
                        $pass = sha1(md5(sha1($_POST['pass_nueva'])));
                        consulta('UPDATE usuario SET password = "'.$pass.'" WHERE id='.$_SESSION['id'].'');
                        echo '<script>alert("La contraseña se ha cambiado con exito")</script>';
                    }else{
                        echo '<script>alert("La nueva contraseña debe ser distinta a la actual")</script>';
                    }
                }else{
                    echo '<script>alert("Las contraseñas no coinciden")</script>';
                }
            }else{
                echo '<script>alert("La contraseña actual es errónea")</script>';

            }

        }else{
            echo '<script>alert("Faltan Datos")</script>';
        }

    }
    
?>
<h1>Cambiar contraseña</h1>
<form action="index.php" method="POST">
Contraseña actual: <br><input type="password" name="pass_actual" required></input><br>
Contraseña nueva: <br><input type="password" name="pass_nueva" required></input><br>
Repita la nueva contraseña: <br><input type="password" name="pass_nueva_2" required></input><br>
<input type="hidden" name="accion" value="cambiar"></input>
<input type="hidden" name="servicio" value="cambiar_pass"></input>
<input type="submit" value="CAMBIAR CONTRASEÑA"></input>

</form>