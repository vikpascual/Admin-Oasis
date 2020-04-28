<aside>
    <script>
        function enviar(nombre) {
            document.forms[nombre].submit();
        }
    </script>
<form name="equipos" action="index.php" method="POST" onclick="enviar('equipos')"><span>Equipos</span><input type="hidden" name="servicio" value="equipos"></form>
<form name="snmp" action="index.php" method="POST" onclick="enviar('snmp')"><span>SNMP / WMI</span><input type="hidden" name="servicio" value="snmp"></form>
<form name="ad" action="index.php" method="POST" onclick="enviar('ad')"><span>Active Directory</span><input type="hidden" name="servicio" value="ad"></form>
<form name="virtualbox" action="index.php" method="POST" onclick="enviar('virtualbox')"><span>Virtualbox</span><input type="hidden" name="servicio" value="virtualbox"></form>
<form name="ssh" action="index.php" method="POST" onclick="enviar('ssh')"><span>SSH</span><input type="hidden" name="servicio" value="ssh"></form>
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

</aside>