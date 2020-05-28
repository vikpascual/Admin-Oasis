<?php
if(isset($_POST['servicio']) && $_POST['servicio'] != 'descargar' || empty($_POST)){
    ob_implicit_flush(true); 
    ob_end_flush();  
}else{
    include 'includes/descargar.php';
    exit();
}

include 'includes/cabecera.php';
include 'includes/lateral.php';
?>
<main>
<?php
if($_SESSION['tipo_usuario'] == 'Administrador'){
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
    }elseif(isset($_POST['servicio']) && $_POST['servicio'] == 'sftp'){
        include 'includes/sftp.php';
    }elseif(isset($_POST['servicio']) && $_POST['servicio'] == 'administrar_usuarios'){
        include 'includes/administrar_usuarios.php';
    }elseif(isset($_POST['servicio']) && $_POST['servicio'] == 'incidencias'){
        include 'includes/incidencias.php';
    }elseif(isset($_POST['servicio']) && $_POST['servicio'] == 'crear_incidencia'){
        include 'includes/crear_incidencias.php';
    }elseif(isset($_POST['servicio']) && $_POST['servicio'] == 'cambiar_pass'){
        include 'includes/cambiar_pass.php';
    }
}else{
    if(isset($_POST['servicio']) && $_POST['servicio'] == 'crear_incidencia'){
        include 'includes/crear_incidencias.php';
    }elseif(isset($_POST['servicio']) && $_POST['servicio'] == 'incidencias'){
        include 'includes/incidencias.php';
    }elseif(isset($_POST['servicio']) && $_POST['servicio'] == 'cambiar_pass'){
        include 'includes/cambiar_pass.php';
    }
}



?>
</main>
</body>
</html>
