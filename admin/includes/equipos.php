<?php
include 'config/db.php';
include 'funciones.php';
//var_dump($_POST);
ini_set('max_execution_time', 0); //LAS CONSULTAS SNMP Y WMI GASTAN MUCHO TIEMPO DE EJECUCION
if(isset($_POST['red'])){
    $red = limpia($_POST['red']);
    if(substr_count($red, '/') == 1){
        $separar = explode("/", $red);
        $ip = $separar[0];
        $subred = $separar[1];
        if (filter_var($ip, FILTER_VALIDATE_IP) && substr($ip, -2) == '.0' && $subred < 32) {
            $ip_contador = 1 << (32 - $subred);
            $ip_numero = ip2long($ip);
            echo "NO CIERRE NI CAMBIE ESTA VENTANA DURANTE EL ESCANEO<br>";
            echo '<progress id="escaneo" value="0" max="'.($ip_contador-2).'" style="width:100%;"></progress><br>'; //BARRA DE PROGRESO PARA HACER MAS AMENO GRACIAS A DESHABILITAR EL BUFFER
            for ($i = 1; $i < $ip_contador - 1; $i++) {
                ob_flush();
                $ip = long2ip($ip_numero + $i);
                $resultado = exec("ping -n 1 -w 2000 $ip");
                echo "<script>document.getElementById('escaneo').value += 1;</script>";
                if (strlen($resultado) > 22){
                    echo "El host $ip ha sido encontrado<br>";
                    consulta("INSERT INTO equipos(id,ip) VALUES('','$ip')");
                }
            }
            echo "<script>enviar('equipos')</script>";
        } else {
            echo "<script>alert('La dirección IP proporcionada no es válida')</script>";
        }
    }elseif(substr_count($red, '/') == 0){
        if (filter_var($red, FILTER_VALIDATE_IP)){
            $resultado = exec("ping -n 1 -w 2000$red");
            if (strlen($resultado) > 22){
                consulta("INSERT INTO equipos(id,ip) VALUES('','$red')");
                echo "<script>alert('Equipo registrado')</script>";
            }else{
                echo "<script>alert('Equipo no encontrado')</script>";
            }
        }else{
            echo "<script>alert('La dirección IP proporcionada no es válida')</script>";
        }
    }else{
        echo "<script>alert('Máscara De Red Incorrecta')</script>";
    }
}
if(isset($_POST['actualizar_equipo'])){
    $ip = limpia($_POST['actualizar_equipo']);
    actualizar_equipo($ip);
}elseif(isset($_POST['actualizar_equipos']) && $_POST['actualizar_equipos'] == 'todos') {
    $lista_ips = consulta('SELECT ip FROM equipos ORDER BY INET_ATON(ip)');
    echo "ESTO PUEDE TARDAR NO CIERRE LA VENTANA<br>";
    echo '<progress id="escaneo" value="0" max="'.count($lista_ips).'" style="width:100%;"></progress><br>'; //BARRA DE PROGRESO PARA HACER MAS AMENO GRACIAS A DESHABILITAR EL BUFFER
    foreach($lista_ips as $value){
        echo $value['ip'].'...<br>';
        actualizar_equipo($value['ip']);
        echo "<script>document.getElementById('escaneo').value += 1;</script>";
    }
    echo "<script>enviar('equipos')</script>";
}
?>
<?php
    $lista_equipos = consulta("SELECT nombre,ip,tipo_dispositivo,os,mac,modelo,actualizado,version FROM equipos ORDER BY INET_ATON(ip)");
?>
<div class="titulo"><h1>Equipos(<?=count($lista_equipos)?>)</h1></div>
<div class="opciones"><form name="red" action="index.php" method="POST" ><input type="text" placeholder="Ex:192.168.1.0/24" name="red"><input type="hidden" name="servicio" value="equipos"></form><span onclick="enviar('red')">Escanear red o IP</span></div>
<div style="height:70%;overflow:scroll;width:100%">
<table id="equipos">
    <tr>
        <th>Nombre</th>
        <th>IP</th>
        <th>Tipo Equipo</th>
        <th>OS</th>
        <th>MAC Address</th>
        <th>Modelo</th>
        <th>Última actualizacón</th>
        <th><form name="actualizar_todos" action="index.php" method="POST" onclick="enviar('actualizar_todos')"><span>Actualizar todos</span><input type="hidden" name="actualizar_equipos" value="todos"><input type="hidden" name="servicio" value="equipos"></form></t>
    </tr>
    <?php 
        foreach($lista_equipos as $value){
            echo "<tr id=".ip2long($value[1])." onclick=\"enviar('actualizar_equipo".ip2long($value[1])."') \" style='cursor:pointer;'>";
            echo "<td>$value[0]</td>";
            echo "<td>$value[1]</td>";
            echo "<td>$value[2]</td>";
            echo "<td>$value[3] $value[7]</td>";
            echo "<td>$value[4]</td>";
            echo "<td>$value[5]</td>";
            echo "<td>$value[6]</td>";
            echo '<td><form name="actualizar_equipo'.ip2long($value[1]).'" action="index.php" method="POST" onclick="enviar(\'actualizar_equipo'.ip2long($value[1]).'\')"><span>Actualizar</span><input type="hidden" name="actualizar_equipo" value="'.$value[1].'"><input type="hidden" name="servicio" value="equipos"></form></td>';
            echo "</tr>";
        }
    ?>
</table>
</div>
<?php 
if(isset($_POST['actualizar_equipo'])){
    if(isset($_POST['notas'])){
        $notas = limpia($_POST['notas']);
        consulta("UPDATE equipos set notas='$notas' WHERE ip = '$ip'");
    }
    if(isset($_POST['id']) && filter_var($_POST['id'], FILTER_VALIDATE_INT) ){
        $id_servicio = limpia($_POST['id']);
        consulta("DELETE FROM servicios WHERE id = $id_servicio");
    }
    if(isset($_POST['nombre_servicio']) && !empty($_POST['nombre_servicio']) && filter_var($_POST['puerto_servicio'], FILTER_VALIDATE_INT) && $_POST['puerto_servicio'] > 0 && $_POST['puerto_servicio'] < 65535 ){
        $nombre_servicio = limpia($_POST['nombre_servicio']);
        $puerto          = limpia($_POST['puerto_servicio']);
        consulta("INSERT INTO servicios VALUES('','$ip','$nombre_servicio','$puerto')");
    }
    $lista_equipo    = consulta("SELECT * FROM equipos WHERE ip = '$ip'");
    $lista_equipo    = $lista_equipo[0];
    $lista_discos    = consulta("SELECT * FROM discos WHERE id_equipo = ".$lista_equipo['id']."");
    $lista_servicios = consulta("SELECT * FROM servicios WHERE ip = '$ip'");
?>
<div id="detalles">
    <h1>Detalles Del Dispositivo</h1>
    <table style="width:100%">
        <th>
            <h3><?=$lista_equipo['nombre']?></h3>
            <span><?=$lista_equipo['fabricante']?> / <?=$lista_equipo['modelo']?></span><br>
            <span><img src="img/no_user_silhouette.png"> <?=$lista_equipo['ultimo_usuario']?></span>
        </th>

        <th>
            <span><img src="img/processor_gray.png"> <?=$lista_equipo['procesador']?></span><br>
            <span><img src="img/bullet_orange.png"> <?=$lista_equipo['os']?> <?=$lista_equipo['version']?></span><br>
            <span><img src="img/ram.png"> <?=$lista_equipo['ram']?> GB</span>
        </th>

        <th>
            <span><img src="img/online_lan.png"> <?=$lista_equipo['ip']?></span><span style="font-size:0.65vw;" id="estado"> (Online)</span><br>
        </th>
    </table>
</div>
<div id="lista">
    <ul>
        <li id="Info_General" class="activado">Info General</li>
        <li id="servicios">Servicios</li>
        <li id="notas">Notas</li>
    </ul>
</div>
<div id="info_general">
    <table class="no_bordes">
        <tr>
            <td>Fabricante:</td>
            <td><?=$lista_equipo['fabricante']?></td>
        <tr>
        <tr>
            <td>Descripcion:</td>
            <td><?=$lista_equipo['descripcion']?></td>
        <tr>
        <tr>
            <td>Tipo de Equipo:</td>
            <td><?=$lista_equipo['tipo_dispositivo']?></td>
        <tr>
        <tr>
            <td>Modelo:</td>
            <td><?=$lista_equipo['modelo']?></td>
        <tr>
        <tr>
            <td>Número de serie:</td>
            <td><?=$lista_equipo['numero_de_serie']?></td>
        <tr>
        <tr>
            <td>MAC:</td>
            <td><?=$lista_equipo['mac']?></td>
        <tr>
        <tr>
            <td>Localización:</td>
            <td><?=$lista_equipo['localizacion']?></td>
        <tr>
        <tr>
            <td>Contacto:</td>
            <td><?=$lista_equipo['contacto']?></td>
        <tr>
        
    </table>
    <table class="no_bordes">
        <tr>
            <td>Procesador:</td>
            <td><?=$lista_equipo['procesador']?></td>
        <tr>
        <tr>
            <td>Memoria:</td>
            <td><?=$lista_equipo['ram']?> GB</td>
        <tr>
        <tr>
            <td>OS:</td>
            <td><?=$lista_equipo['os']?></td>
        <tr>
        <tr>
            <td>Version:</td>
            <td><?=$lista_equipo['version']?></td>
        <tr>
        <tr>
            <td>Ultimo usuario:</td>
            <td><?=$lista_equipo['ultimo_usuario']?></td>
        <tr>
        <tr>
            <td>BIOS:</td>
            <td><?=$lista_equipo['bios']?></td>
        <tr>
        <tr>
            <td>Arquitectura:</td>
            <td><?=$lista_equipo['arquitectura']?></td>
        <tr>
        <tr>
            <td>Tiempo encendido:</td>
            <td><?=$lista_equipo['tiempo_encendido']?></td>
        <tr>
        <tr>
            <td>Última actualización:</td>
            <td><?=$lista_equipo['actualizado']?></td>
        <tr>
        <tr>
            <td>Fecha de registro:</td>
            <td><?=$lista_equipo['creado']?></td>
        <tr>
        <tr>
            <td><br><br><br><br></td>
        <tr>
    </table>
    <div id="discos">
        <?php
            $total_usado     = 0;
            $capacidad_total = 0;
            foreach($lista_discos as $value){
                $total_usado     += $value['espacio_usado'];
                $capacidad_total += $value['espacio_total'];
            }
        ?>
        <span>Total De Almacenamiento Usado: <?=round(($total_usado * 100) / $capacidad_total,2)?>% (<?=$total_usado?> GB) </span><span style="margin-left:5%">Capacidad Total De Almacenamiento: <?=$capacidad_total?> GB</span>
        <hr>
        <table class="no_bordes" style="width:100%">
            <tr>
                <th>Nombre Volumen/Disco</th>
                <th>Almacenamiento Gráfico</th>
                <th>Espacio Libre</th>
                <th>Capacidad Total Del Volumen/Disco</th>
            </tr>
            <?php
                foreach($lista_discos as $value){
                    $espacio_sin_usar = $value['espacio_total'] - $value['espacio_usado'];
                    $clase      = '';
                    if(round(($value['espacio_usado'] * 100) / $value['espacio_total']) >= 90){
                        $clase = 'class="peligro"';
                    }elseif(round(($value['espacio_usado'] * 100) / $value['espacio_total']) >= 70){
                        $clase = 'class="naranja"';
                    }
                    echo "<tr>";
                    echo "<td $clase>".$value['descripcion']."</td>";
                    echo "<td $clase><progress value='".round(($value['espacio_usado'] * 100) / $value['espacio_total'])."' max=100 $clase></progress> ".round(($espacio_sin_usar * 100) / $value['espacio_total'],2)."% Libre</td>";
                    echo "<td $clase>".$espacio_sin_usar." GB</td>";
                    echo "<td $clase>".$value['espacio_total']." GB</td>";
                    echo "</tr>";
                }
            ?>
        </table>
    </div>
</div>
<div id="Servicios">
        <table class="no_bordes" style="width:100%">
            <tr>
                <th>Id</th>
                <th>Nombre</th>
                <th>Puerto</th>
                <th>Estado</th>
            </tr>
            <?php
                foreach($lista_servicios as $value){
                    echo "<tr>";
                    ?>
                    <form method="POST" action="index.php" z>
                        <input type="hidden" name="servicio" value="equipos">
                        <input type="hidden" name="actualizar_equipo" value='<?=$ip?>'>
                        <input type="hidden" name="id" value="<?=$value['id']?>">
                    <?php
                     
                    echo "<td>".$value['id']."</td>";
                    echo "<td>".$value['nombre']."</td>";
                    echo "<td>".$value['puerto']."</td>";
                    $ping = fsockopen($value['ip'], $value['puerto'], $errno, $errstr, 2);
                     if (!$ping){
                        echo "<td style='color:red'>Cerrado</td>";
                     }else{
                        echo "<td>Abierto</td>";
                     }
                    
                    echo "<td><input type='submit' value='Borrar'></input></td>";
                    echo "</form></tr>";
                }
            ?>
            <tr>
            <form method="POST" action="index.php" z>
                <input type="hidden" name="servicio" value="equipos">
                <input type="hidden" name="actualizar_equipo" value='<?=$ip?>'>
                <td>Automático</td>
                <td><input type="text" name="nombre_servicio"></td>
                <td><input type="number" min="1" max="65535" name="puerto_servicio"></td>
                <td>Ninguno</td>
                <td><input type="submit" value="Añadir Servicio"></input></td>
            </form>
            </tr>
            
        </table>
    
</div>
<div id="Notas">
    <h1>Notas</h1>
    <form method="POST" action="index.php">
        <input type="hidden" name="servicio" value="equipos">
        <input type="hidden" name="actualizar_equipo" value='<?=$ip?>'>
        <textarea style="width:100%;height:300px" name="notas"><?=$lista_equipo['notas']?></textarea>
        <input type="submit" value="Guardar">
    </form>
</div>
<?php
}
?>
<script src="js/jquery.js"></script>
<script>
    $(document).ready(function(){
        $("#Info_General").on("click",function(){
            $("#info_general").show();
            $("#Servicios").hide();
            $("#Notas").hide();
            $("#Info_General").addClass('activado');
            $("#servicios").removeClass('activado');
            $("#notas").removeClass('activado');
        });
        $("#servicios").on("click",function(){
            $("#info_general").hide();
            $("#Servicios").show();
            $("#Notas").hide();
            $("#Info_General").removeClass('activado');
            $("#servicios").addClass('activado');
            $("#notas").removeClass('activado');
        });
        $("#notas").on("click",function(){
            $("#info_general").hide();
            $("#Servicios").hide();
            $("#Notas").show();
            $("#Info_General").removeClass('activado');
            $("#servicios").removeClass('activado');
            $("#notas").addClass('activado');
        });
    });

</script>
<footer>
<?php
foreach($lista_equipos as $value){
    $resultado = exec("ping -n 1 -w 2000 $value[1]");
    if (strlen($resultado) < 22){
        echo "<script>
        for (i = 0; i < 8; i++) {
            document.getElementById(".ip2long($value[1]).").childNodes[i].className = 'rojo';
        };
        </script>";
    }else{
        $lista_servicios = consulta("SELECT * FROM servicios WHERE ip = '$value[1]'");
        if(!empty($lista_servicios)){
            $num_servicios = count($lista_servicios);
            $contador      = 0;
            foreach($lista_servicios as $servicio){
                $ping = fsockopen($value[1], $servicio['puerto'], $errno, $errstr, 2);
                if (!$ping){
                    echo "<script>
                    for (i = 0; i < 8; i++) {
                        document.getElementById(".ip2long($value[1]).").childNodes[i].className = 'naranja';
                    };
                    </script>";
                break;
                }
            }
        }
    }
}
?>
</footer>