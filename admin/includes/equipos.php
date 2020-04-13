<?php
include 'config/db.php';
include 'funciones.php';
//var_dump($_POST);
ini_set('max_execution_time', 0); 
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
            echo '<progress id="escaneo" value="0" max="'.($ip_contador-2).'" style="width:100%;"></progress><br>';
            for ($i = 1; $i < $ip_contador - 1; $i++) {
                ob_flush();
                $ip = long2ip($ip_numero + $i);
                $resultado = exec("ping -n 1 $ip");
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
            $resultado = exec("ping -n 1 $red");
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
    if(filter_var($ip, FILTER_VALIDATE_IP)){
        $equipo_propiedades    = consulta( "SELECT * FROM equipos WHERE ip='$ip'");
        $id_equipo             = $equipo_propiedades[0][0];
        $id_comunidad          = consulta("SELECT id_comunidad FROM equipos, comunidades_equipos WHERE ".$id_equipo." = id_equipo");
        $id_wmi                = consulta("SELECT id_wmi FROM equipos, wmi_equipos WHERE ".$id_equipo." = id_equipo");
        if(empty($id_comunidad)){
            $lista_comunidades = consulta("SELECT * FROM comunidades");
            foreach($lista_comunidades as $value){
                if($value['version'] == 'SNMPv1'){
                    $descripcion = snmpget($ip,$value['usuario'],'1.3.6.1.2.1.1.1.0');
                    if(consultar_os($descripcion,$id_equipo,$value['id'])){
                        break;
                    }
                }elseif($value['version'] == 'SNMPv2c'){
                    $descripcion = snmp2_get($ip,$value['usuario'],'1.3.6.1.2.1.1.1.0');         
                    if(consultar_os($descripcion,$id_equipo,$value['id'])){
                        break;
                    }
                }else{
                    if($value['nivel_seguridad'] == 'authPriv'){
                        $descripcion = snmp3_get($ip, $value['usuario'], 'authPriv', $value['protocolo_autenticacion'], openssl_decrypt($value['pass_autenticacion'], 'aes128', $pass_cifrado), $value['protocolo_privacidad'],openssl_decrypt($value['pass_privacidad'], 'aes128', $pass_cifrado), '1.3.6.1.2.1.1.1.0');
                        if(consultar_os($descripcion,$id_equipo,$value['id'])){
                            break;
                        }
                    }elseif($value['nivel_seguridad'] == 'authNoPriv'){
                        $descripcion = snmp3_get($ip, $value['usuario'], 'authNoPriv', $value['protocolo_autenticacion'], openssl_decrypt($value['pass_autenticacion'], 'aes128', $pass_cifrado), '1.3.6.1.2.1.1.1.0');
                        if(consultar_os($descripcion,$id_equipo,$value['id'])){
                            break;
                        }
                    }else{
                        $descripcion = snmp3_get($ip, $value['usuario'], 'noAuthNoPriv', '1.3.6.1.2.1.1.1.0');
                        if(consultar_os($descripcion,$id_equipo,$value['id'])){
                            break;
                        }
                    }

                }
            }
        }
        if(empty($id_wmi) && $equipo_propiedades[0]['os'] == 'Windows'){
            $lista_wmi = consulta("SELECT * FROM wmi");
            foreach($lista_wmi as $value){
                try{
                    $WbemLocator = new COM ("WbemScripting.SWbemLocator");
                    if($value[3] == ''){
                        $WbemServices = $WbemLocator->ConnectServer($ip, 'root\\cimv2', $value[1], openssl_decrypt($value[2], 'aes128', $pass_cifrado));
                        consulta("INSERT INTO wmi_equipos VALUES ($id_equipo,".$value[0].")");
                    }else{
                        $WbemServices = $WbemLocator->ConnectServer($ip, 'root\\cimv2', $value[1].'@'.$value[3], openssl_decrypt($value[2], 'aes128', $pass_cifrado));
                        consulta("INSERT INTO wmi_equipos VALUES ($id_equipo,".$value[0].")");
                    }
                }catch (Exception $e){
                }
            }         
        }
        if(!empty($id_comunidad)){
            $comunidad_propiedades = consulta("SELECT * FROM comunidades WHERE id = ".$id_comunidad[0][0]."");
            if($comunidad_propiedades[0]['version'] == 'SNMPv1'){
                $nombre = snmpget($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.5.0');
                $descripcion = snmpget($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.1.0');
                $tiempo_encendido = snmpget($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.1.1.0');
                $contacto = snmpget($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.4.0');
                $localizacion = snmpget($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.6.0');
                $indices_discos = snmpget($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.2.3.1.1');
                
                
                    
            }elseif($comunidad_propiedades[0]['version'] == 'SNMPv2c'){
                $nombre = substr(snmp2_get($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.5.0'),9,-1);  
                $descripcion = substr(snmp2_get($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.1.0'),9,-1);       
                $tiempo_encendido = substr(snmp2_get($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.1.1.0'),19);
                $contacto = substr(snmp2_get($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.4.0'),9,-1);
                $localizacion = substr(snmp2_get($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.6.0'),9,-1);
                $ram = explode(':',snmp2_get($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.4.1.2021.4.5.0')); 
                $ram = round($ram[1] / 1024 / 1024);
                $mac = array_values(array_unique(explode(' ',exec('arp -a | findstr '.$ip.''))));
                $mac = Strtoupper(str_replace('-',':',$mac[2]));
                consulta("UPDATE equipos SET nombre = '$nombre', descripcion = '$descripcion', tiempo_encendido = '$tiempo_encendido',
                contacto = '$contacto', localizacion = '$localizacion', ram = $ram, mac = '$mac', actualizado = CURRENT_TIMESTAMP() WHERE id = $id_equipo");
                
                $descripciones_discos = snmp2_walk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.2.3.1.3');
                $espacio_discos = snmp2_walk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.2.3.1.5');
                $espacio_usado = snmp2_walk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.2.3.1.6');
                $allocation_units = snmp2_walk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.2.3.1.4');
                for($i = 0; $i < count($allocation_units); $i++){
                    $descripcion_disco = substr($descripciones_discos[$i],9,-1);
                    $unidades = explode(':', $allocation_units[$i]);
                    $espacio_disco = explode(':',$espacio_discos[$i]);
                    $espacio_disco = round(((int)$espacio_disco[1] * (int)$unidades[1]) / 1024 / 1024 / 1024,1);
                    var_dump($espacio_disco);
                }
                /*Linux
                $lista_linux = snmp2_walk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.4.1.8072.1.3.2.3.1.1');
                $cpu = substr($lista_linux[0],22,-1);
                $bios_version = substr($lista_linux[1],9,-1);
                $num_serie = substr($lista_linux[2],9,-1);
                $modelo = substr($lista_linux[3],9,-1);
                $os_version = substr($lista_linux[4],23,-3);
                $fabricante = substr($lista_linux[5],9,-1);
                $arquitectura = substr($lista_linux[6],9,-1);
                $ultimo_usuario = explode(' ',$lista_linux[7]);
                $ultimo_usuario = substr($ultimo_usuario[1],1);
                consulta("UPDATE equipos SET fabricante = '$fabricante',nmodelo = '$modelo', numero_de_serie = '$num_serie',
                version = '$os_version', procesador = '$cpu', ultimo_usuario = '$ultimo_usuario', bios = '$bios_version',
                arquitectura = '$arquitectura' WHERE id = $id_equipo");
                //consulta("INSERT INTO equipos VALUES('',$nombre,$descripcion)")
                /*LINUX */

                /*WINDOWS */

                /*WINDOWS */
            }else{
            }

        }
        
    }else{
        echo "<script>alert('No hagas cosas raras')</script>";
    }
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
        <th><form name="actualizar_equipos" action="index.php" method="POST" onclick="enviar('actualizar_equipos')"><span>Actualizar todos</span><input type="hidden" name="actualizar_equipos" value="todos"><input type="hidden" name="servicio" value="equipos"></form></t>
    </tr>
    <?php 
        $contador = 1;
        foreach($lista_equipos as $value){
            echo "<tr id=".ip2long($value[1]).">";
            echo "<td>$value[0]</td>";
            echo "<td>$value[1]</td>";
            echo "<td>$value[2]</td>";
            echo "<td>$value[3] $value[7]</td>";
            echo "<td>$value[4]</td>";
            echo "<td>$value[5]</td>";
            echo "<td>$value[6]</td>";
            echo '<td><form name="actualizar_equipo'.$contador.'" action="index.php" method="POST" onclick="enviar(\'actualizar_equipo'.$contador.'\')"><span>Actualizar</span><input type="hidden" name="actualizar_equipo" value="'.$value[1].'"><input type="hidden" name="servicio" value="equipos"></form></td>';
            echo "</tr>";
            $contador++;
        }
    ?>
</table>
</div>
<?php 
    foreach($lista_equipos as $value){
        $resultado = exec("ping -n 1 $value[1]");
        if (strlen($resultado) < 22){
            echo "<script>
            for (i = 0; i < 8; i++) {
                document.getElementById(".ip2long($value[1]).").childNodes[i].className = 'rojo';
            };
            </script>";
        }
    }
?>
