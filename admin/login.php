<?php
include 'includes/config/db.php';
$usuario      = $_POST['usuario'];
$pass         = sha1(md5(sha1($_POST['pass'])));
$consulta     = "SELECT count(*) FROM usuario WHERE usuario = '$usuario' AND password = '$pass'";
$resultado    = $conexion->prepare($consulta);
$resultado->execute();
$numero_de_columnas = $resultado->fetchColumn();
if($numero_de_columnas == 1){
    session_start();
    $_SESSION['logueado'] = TRUE;
    header('Location: index.php');
} else {
    header('Location: entrar.php');
}
?>