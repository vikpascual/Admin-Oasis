<?php
ob_implicit_flush(true);
ob_end_flush();
include 'includes/cabecera.php';
include 'includes/lateral.html';
?>
<main>
<?php
if(isset($_POST['servicio']) && $_POST['servicio'] == 'virtualbox'){
    include 'includes/virtualbox.php';
}elseif(isset($_POST['servicio']) && $_POST['servicio'] == 'ad'){
    include 'includes/ad.php';
}elseif(isset($_POST['servicio']) && $_POST['servicio'] == 'snmp'){
    include 'includes/snmp.php';
}elseif(isset($_POST['servicio']) && $_POST['servicio'] == 'equipos'){
    include 'includes/equipos.php';
}

?>
</main>
</body>
</html>
