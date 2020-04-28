<?php
if(isset($_POST['ver']) && !empty($_POST['ver'])){
    $pagina = consulta('SELECT * FROM paginas WHERE nombre = "'.$_POST['ver'].'"');
    echo '<h1>'.limpia($pagina[0][0]).'</h1>';
    echo '<iframe src="'.limpia($pagina[0][1]).'" width="100%" height="80%"></iframe>';
}
if(isset($_POST['accion']) && $_POST['accion'] == 'add' && isset($_POST['nombre']) && !empty($_POST['nombre']) && isset($_POST['url']) && !empty($_POST['url'])){
    consulta('INSERT INTO paginas VALUES ("'.$_POST['nombre'].'","'.$_POST['url'].'")');
}
if(isset($_POST['accion']) && $_POST['accion'] == 'borrar' && isset($_POST['nombre']) && !empty($_POST['nombre'])){
    consulta('DELETE FROM paginas WHERE nombre = "'.$_POST['nombre'].'"');
}
if(isset($_POST['accion']) && $_POST['accion'] == 'add'){
?>
<h1>Añadir página al lateral</h1>
<form action='index.php' method='POST'>
    Nombre<br>
    <input type='text' name='nombre' required></input><br>
    Url<br>
    <input type='text' name='url' required></input>
    <input type='hidden' name='accion' value='add'>
    <input type='hidden' name='servicio' value='paginas'><br>
    <input type='submit' value='Enviar'>
</form>
<?php
}elseif(isset($_POST['accion']) && $_POST['accion'] == 'borrar'){
?>
<h1>Eliminar página del lateral</h1>
<form action='index.php' method='POST'>
    Nombre
    <input type='text' name='nombre' required></input><br>
    <input type='hidden' name='accion' value='borrar'>
    <input type='hidden' name='servicio' value='paginas'><br>
    <input type='submit' value='Enviar'>
</form>
<?php
}
?>