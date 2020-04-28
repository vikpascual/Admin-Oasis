<?php
/* 
DESHABILITAMOS EL BUFFER
LO DESHABILITO PORQUE ALGUNAS CONSULTAS SON LENTAS(PING,SNMP,WMI...) Y PARECE QUE LA PAGINA SE HA QUEDADO BLOQUEADA Y NO RESPONDA
PHP GENERA TEXTO QUE LO VA GUARDANDO EN EL BUFFER DE SALIDA Y CUANDO ESTA COMPLETO LO ENVIA. EL BUFFER POR DEFECTO SON 4KB.
POR LO QUE AL DESHABILITARLO SE ENVIARÁ LO QUE SUPESTAMENTE SE IBA A VOLCAR EN BUFFER DESPUES DE CADA LLAMADA DE SALIDA,ES DECIR, SE IRÁ 
ENVIANDO PARCIALMENTE EL CONTENIDO DEL DOCUMENTO HTML CONFORME SE VAYA GENERANDO AL NAVEGADOR DEL CLIENTE.
EL PRINCIPAL PROBLEMA DE ESTO ES, QUE EL MOMENTO EN EL QUE SE ENVIA UNA PARTE DEL DOCUMENTO AL NAVEGADOR DEL CLIENTE LA FUNCION HEADER() NO FUNCIONARÁ
DEBIDO A QUE NO PUEDES ENVIAR EL CONTENIDO Y DESPUES CAMBIAR LA CABECERA. POR ESO ALGUNAS REDIRECCIONES SE HACEN CON JAVASCRIPT.
*/
ob_implicit_flush(true); // UTILIZO ESTA FUNCION PARA NO USAR LA FUNCION FLUSH() CADA 2 POR 3
ob_end_flush();// ES NECESARIO DESHABILITARLO
include 'includes/cabecera.php';
include 'includes/lateral.php';
?>
<main>
<?php //NO ME GUSTAN LOS GET
if(isset($_POST['servicio']) && $_POST['servicio'] == 'virtualbox'){
    include 'includes/virtualbox.php';
}elseif(isset($_POST['servicio']) && $_POST['servicio'] == 'ad'){
    include 'includes/ad.php';
}elseif(isset($_POST['servicio']) && $_POST['servicio'] == 'snmp'){
    include 'includes/snmp.php';
}elseif(isset($_POST['servicio']) && $_POST['servicio'] == 'equipos'){
    include 'includes/equipos.php';
}elseif(isset($_POST['servicio']) && $_POST['servicio'] == 'paginas'){
    include 'includes/paginas.php';
}elseif(isset($_POST['servicio']) && $_POST['servicio'] == 'ssh'){
    include 'includes/ssh.php';
}

?>
</main>
</body>
</html>
