<?php
include 'includes/config/db.php';
include 'includes/funciones.php';
$usuario      = $_POST['usuario'];
$pass         = sha1(md5(sha1($_POST['pass'])));
$consulta     = consulta("SELECT * FROM usuario WHERE usuario = '$usuario' AND password = '$pass'");
var_dump($consulta);
if(!empty($consulta)){
    session_start();
    $_SESSION['logueado']      = TRUE;
    $_SESSION['id']            = $consulta[0]['id'];
    $_SESSION['tipo_usuario']  = $consulta[0]['tipo_usuario'];
    consulta('UPDATE usuario SET ultima_sesion = TIMESTAMP(now()) WHERE id='.$consulta[0]['id'].';');
    header('Location: index.php');
}else{
    header('Location: entrar.php');
}
/*
if($numero_de_columnas == 1){
    session_start();
    $_SESSION['logueado'] = TRUE;
    header('Location: index.php');
} else {
    header('Location: entrar.php');
}
*/
?>