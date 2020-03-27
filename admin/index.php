<?php
include 'includes/cabecera.php';
include 'includes/lateral.html';
?>
<main>
<?php
if($_GET['virtualbox'] == 1){
    include 'includes/virtualbox.php';
}
if($_GET['ad'] == 1){
    include 'includes/ad.php';
}
if($_GET['snmp'] == 1){
    include 'includes/snmp.php';
}
?>
</main>
</body>
</html>
