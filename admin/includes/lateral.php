<?php
if($_SESSION['tipo_usuario'] == 'Administrador'){
?>
    <aside>
        <form name="equipos" action="index.php" method="POST" onclick="enviar('equipos')"><span>Equipos</span><input type="hidden" name="servicio" value="equipos"></form>
        <form name="snmp" action="index.php" method="POST" onclick="enviar('snmp')"><span>SNMP / WMI</span><input type="hidden" name="servicio" value="snmp"></form>
        <form name="ad" action="index.php" method="POST" onclick="enviar('ad')"><span>LDAP</span><input type="hidden" name="servicio" value="ad"></form>
        <form name="virtualbox" action="index.php" method="POST" onclick="enviar('virtualbox')"><span>Virtualbox</span><input type="hidden" name="servicio" value="virtualbox"></form>
        <form name="ssh" action="index.php" method="POST" onclick="enviar('ssh')"><span>SSH</span><input type="hidden" name="servicio" value="ssh"></form>
        <form name="sftp" action="index.php" method="POST" onclick="enviar('sftp')"><span>SFTP</span><input type="hidden" name="servicio" value="sftp"></form>
        <hr style="width: 90%; margin-right: 10%;">
        <?php
        $lista_paginas = consulta('SELECT * FROM paginas');
        $contador      = 0;
        foreach($lista_paginas as $link){
            echo '<form name="pagina'.$contador.'" action="index.php" method="POST" onclick="enviar(\'pagina'.$contador.'\')"><span>'.limpia($link['nombre']).'</span><input type="hidden" name="servicio" value="paginas"><input type="hidden" name="ver" value="'.limpia($link['nombre']).'"></form>';
            $contador++;
        }
        ?>
        <hr style="width: 90%; margin-right: 10%;">
        <form name="add_pagina" action="index.php" method="POST" onclick="enviar('add_pagina')"><span>Añadir página +</span><input type="hidden" name="servicio" value="paginas"><input type="hidden" name="accion" value="add"></input></form>
        <form name="borrar_pagina" action="index.php" method="POST" onclick="enviar('borrar_pagina')"><span>Quitar página -</span><input type="hidden" name="servicio" value="paginas"><input type="hidden" name="accion" value="borrar"></input></form>
        <hr style="width: 90%; margin-right: 10%;">
        <form name="incidencias" action="index.php" method="POST" onclick="enviar('incidencias')"><span>Incidencias</span><input type="hidden" name="servicio" value="incidencias"></form>
        <form name="crear_incidencia" action="index.php" method="POST" onclick="enviar('crear_incidencia')"><span>Crear Incidencia</span><input type="hidden" name="servicio" value="crear_incidencia"></form>
        <form name="administrar_usuarios" action="index.php" method="POST" onclick="enviar('administrar_usuarios')"><span>Admin. usuarios</span><input type="hidden" name="servicio" value="administrar_usuarios"></form>
        <hr style="width: 90%; margin-right: 10%;">
        <form name="cambiar_pass" action="index.php" method="POST" onclick="enviar('cambiar_pass')"><span>Cambiar contraseña</span><input type="hidden" name="servicio" value="cambiar_pass"></input></form>
    </aside>
<?php
}else{
    ?>
    <aside>
        <form name="incidencias" action="index.php" method="POST" onclick="enviar('incidencias')"><span>Incidencias</span><input type="hidden" name="servicio" value="incidencias"></form>
        <form name="crear_incidencia" action="index.php" method="POST" onclick="enviar('crear_incidencia')"><span>Crear Incidencia</span><input type="hidden" name="servicio" value="crear_incidencia"></form>
        <form name="cambiar_pass" action="index.php" method="POST" onclick="enviar('cambiar_pass')"><span>Cambiar contraseña</span><input type="hidden" name="servicio" value="cambiar_pass"></input></form>

    </aside>
    <?php
}
?>
