<?php
if(isset($_POST['id']) && $_POST['id'] != 1){
    consulta('DELETE FROM usuario WHERE id = '.$_POST['id'].'');
}
if(isset($_POST['nombre']) && isset($_POST['pass']) && isset($_POST['pass2']) &&
!empty($_POST['nombre']) && !empty($_POST['pass']) && !empty($_POST['pass2'])){
    if($_POST['pass'] == $_POST['pass2']){
        consulta('INSERT INTO usuario VALUES("","'.$_POST['nombre'].'","'.sha1(md5(sha1($_POST['pass']))).'","Normal","")');
    }else{
        echo '<script>alert("las contraseñas no coinciden")</script>';
    }
}
?>
<h1>Administrar usuarios</h1>
<div style="max-height:30%;overflow:scroll;overflow-x: hidden;overflow-y:auto;width:60%;">
    <table style="width:100%">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Tipo de Usuario</th>
            <th>Último inicio de sesión</th>
        </tr>
        <?php
            $lista_usuarios = consulta("SELECT * FROM usuario");
            foreach($lista_usuarios as $value){
                echo '<tr>';
                echo '<td>'.$value['id'].'</td>';
                echo '<td>'.limpia($value['usuario']).'</td>';
                echo '<td>'.$value['tipo_usuario'].'</td>';
                echo '<td>'.$value['ultima_sesion'].'</td>';
                if($value['id'] != 1){
                    echo '<td style="text-align:center;"><form action="index.php" method="POST"><input type="hidden" name="servicio" value="administrar_usuarios"></input><input type="hidden" name="id" value="'.$value['id'].'"></input><input type="submit" value="Borrar"></input></form> </td>';
                }
                echo '</tr>';
            }
        ?>
    </table>
</div>
<h2>Añadir usuario</h2>
<form action="index.php" method="POST">
<input type="hidden" name="servicio" value="administrar_usuarios"></input>
Nombre<br>
<input type="text" name="nombre" required></input><br>
Contraseña<br>
<input type="password" name="pass" required></input><br>
Repetir contraseña<br>
<input type="password" name="pass2" required></input><br>
<input type="submit" value="Añadir"></input><br>
</form>