<?php
function limpia($texto){
    $limpio = htmlspecialchars($texto);
    return $limpio;
}
function consulta($consulta){
    $resultado    = $GLOBALS['conexion']->prepare($consulta);
    $resultado->execute();
    $tabla = $resultado->fetchAll();
    return $tabla;
}
function consultar_os($descripcion,$id_equipo,$id_comunidad){
    if(strpos($descripcion, 'Windows') > 0){
        consulta("UPDATE equipos set os = 'Windows' WHERE id=".$id_equipo."");
        consulta("INSERT INTO comunidades_equipos VALUES(".$id_comunidad.",".$id_equipo.")");
        return True;
    }elseif(strpos($descripcion, 'Linux') > 0){
        consulta("UPDATE equipos set os = 'Linux' WHERE id='$id_equipo'");
        consulta("INSERT INTO comunidades_equipos VALUES(".$id_comunidad.",".$id_equipo.")");
        return True;
    }else{
        return False;
    }
}

?>