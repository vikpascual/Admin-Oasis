<?php
function limpia($texto){
    $limpio = htmlspecialchars($texto);
    return $limpio;
}
function consulta($conexion,$consulta){
        $resultado    = $conexion->prepare($consulta);
        $resultado->execute();
        $tabla = $resultado->fetchAll();
        return $tabla;
}
?>