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
function actualizar_equipo($ip){
    if(filter_var($ip, FILTER_VALIDATE_IP)){
        $equipo_propiedades    = consulta( "SELECT * FROM equipos WHERE ip='$ip'");
        $id_equipo             = $equipo_propiedades[0][0];
        $id_comunidad          = consulta("SELECT id_comunidad FROM equipos, comunidades_equipos WHERE ".$id_equipo." = id_equipo");
        $id_wmi                = consulta("SELECT id_wmi FROM equipos, wmi_equipos WHERE ".$id_equipo." = id_equipo AND id = $id_equipo");
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
        $equipo_propiedades    = consulta( "SELECT * FROM equipos WHERE ip='$ip'");
        if(empty($id_wmi) && $equipo_propiedades[0]['os'] == 'Windows'){
            $lista_wmi = consulta("SELECT * FROM wmi");
            foreach($lista_wmi as $value){
                try{
                    $WbemLocator = new COM ("WbemScripting.SWbemLocator");
                    if($value[3] == ''){ //SI NO TIENE DOMINIO
                        $WbemServices = $WbemLocator->ConnectServer($ip, 'root\\cimv2', $value[1], openssl_decrypt($value[2], 'aes128', $GLOBALS['pass_cifrado']));
                        consulta("INSERT INTO wmi_equipos VALUES ($id_equipo,".$value[0].")");
                        break;
                    }else{ //SI TIENE DOMINIO
                        $WbemServices = $WbemLocator->ConnectServer($ip, 'root\\cimv2', $value[1].'@'.$value[3], openssl_decrypt($value[2], 'aes128', $GLOBALS['pass_cifrado']));
                        consulta("INSERT INTO wmi_equipos VALUES ($id_equipo,".$value[0].")");
                        break;
                    }
                }catch (Exception $error_De_los_webos){
                }
            }         
        }
        $id_wmi       = consulta("SELECT id_wmi FROM equipos, wmi_equipos WHERE ".$id_equipo." = id_equipo AND id = $id_equipo");
        $id_comunidad = consulta("SELECT id_comunidad FROM equipos, comunidades_equipos WHERE ".$id_equipo." = id_equipo");

        if(!empty($id_comunidad)){ // ESTO LO PUSE POR ALGUNA RAZON PERO NO LA RECUERDO
            $comunidad_propiedades = consulta("SELECT * FROM comunidades WHERE id = ".$id_comunidad[0][0]."");
            $posible_os            = snmpget($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.1.0'); // NO LO SACO DE LA BASE DE DATOS POR SI LA IP HA CAMBIADO
            if($posible_os == ''){
                return False;// ESTO LO HAGO PORQUE SI EL ORDENADOR ESTABA APAGADO TARDABA MUCHO EN ESTE PUNTO SEGUN XDEBUG(HEMOS REDUCIDO 20 SEGUNDOS DE CARGA).
            }                  //UN PING TARDA MUCHO AUNQUE SEA SOLO UN PAQUETE Y TAMPOCO PUEDO PONER UN TIMEOUT MUY BAJO
            if($comunidad_propiedades[0]['version'] == 'SNMPv1'){
                $nombre           = substr(snmpget($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.5.0'),9,-1);
                $descripcion      = substr(snmpget($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.1.0'),9,-1);   
                $tiempo_encendido = substr(snmpget($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.1.1.0'),19);
                $contacto         = substr(snmpget($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.4.0'),9,-1);
                $localizacion     = substr(snmpget($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.6.0'),9,-1);
                $ram              = explode(':',snmpget($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.4.1.2021.4.5.0')); 
                $ram              = round($ram[1] / 1024 / 1024);
                $mac              = array_values(array_unique(explode(' ',exec('arp -a | findstr '.$ip.''))));//ARRAY UNIQUE PARA QUITAR TODOS LOS ESPACIOS REPETIDOS
                $mac              = Strtoupper(str_replace('-',':',$mac[2]));
                consulta("UPDATE equipos SET nombre = '$nombre', descripcion = '$descripcion', tiempo_encendido = '$tiempo_encendido',
                contacto = '$contacto', localizacion = '$localizacion', ram = $ram, mac = '$mac', actualizado = CURRENT_TIMESTAMP() WHERE id = $id_equipo");
                //MUY DIFICIL IDENTIFICAR CADA DISCO SIN UN SNMP_WALK
                $descripciones_discos = snmpwalk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.2.3.1.3');
                $espacio_discos       = snmpwalk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.2.3.1.5');
                $espacio_usado        = snmpwalk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.2.3.1.6');
                $allocation_units     = snmpwalk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.2.3.1.4');
                $indices_discos       = snmpwalk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.2.3.1.1');
                actualizar_discos($id_equipo,$indices_discos,$descripciones_discos,$espacio_discos,$espacio_usado,$allocation_units);

                /* ACTUALIZACION POR SO */
                if(strpos($posible_os, 'Windows') > 0 && !empty($id_wmi)){
                    actualizar_windows($id_equipo,$id_wmi[0][0],$ip);
                }elseif(strpos($posible_os, 'Linux') > 0){
                    $lista_linux = snmpwalk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.4.1.8072.1.3.2.3.1.1');
                    actualizar_linux($id_equipo,$lista_linux);
                }
            }elseif($comunidad_propiedades[0]['version'] == 'SNMPv2c'){
                $nombre           = substr(snmp2_get($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.5.0'),9,-1);
                $descripcion      = substr(snmp2_get($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.1.0'),9,-1);   
                $tiempo_encendido = substr(snmp2_get($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.1.1.0'),19);
                $contacto         = substr(snmp2_get($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.4.0'),9,-1);
                $localizacion     = substr(snmp2_get($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.1.6.0'),9,-1);
                $ram              = explode(':',snmp2_get($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.4.1.2021.4.5.0')); 
                $ram              = round($ram[1] / 1024 / 1024);
                $mac              = array_values(array_unique(explode(' ',exec('arp -a | findstr '.$ip.''))));
                $mac              = Strtoupper(str_replace('-',':',$mac[2]));
                consulta("UPDATE equipos SET nombre = '$nombre', descripcion = '$descripcion', tiempo_encendido = '$tiempo_encendido',
                contacto = '$contacto', localizacion = '$localizacion', ram = $ram, mac = '$mac', actualizado = CURRENT_TIMESTAMP() WHERE id = $id_equipo");

                $descripciones_discos = snmp2_walk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.2.3.1.3');
                $espacio_discos       = snmp2_walk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.2.3.1.5');
                $espacio_usado        = snmp2_walk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.2.3.1.6');
                $allocation_units     = snmp2_walk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.2.3.1.4');
                $indices_discos       = snmp2_walk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.2.1.25.2.3.1.1');
                actualizar_discos($id_equipo,$indices_discos,$descripciones_discos,$espacio_discos,$espacio_usado,$allocation_units);

                if(strpos($posible_os, 'Windows') > 0 && !empty($id_wmi)){
                    actualizar_windows($id_equipo,$id_wmi[0][0],$ip);
                }elseif(strpos($posible_os, 'Linux') > 0){
                    $lista_linux = snmp2_walk($ip,$comunidad_propiedades[0]['usuario'],'1.3.6.1.4.1.8072.1.3.2.3.1.1');
                    actualizar_linux($id_equipo,$lista_linux);
                }

            }else{
                if($comunidad_propiedades[0]['nivel_seguridad'] == 'authPriv'){
                    $nombre           = substr(snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'authPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],$comunidad_propiedades[0]['protocolo_privacidad'],$comunidad_propiedades[0]['pass_privacidad'],'1.3.6.1.2.1.1.5.0'),9,-1);
                    $descripcion      = substr(snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'authPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],$comunidad_propiedades[0]['protocolo_privacidad'],$comunidad_propiedades[0]['pass_privacidad'],'1.3.6.1.2.1.1.1.0'),9,-1);   
                    $tiempo_encendido = substr(snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'authPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],$comunidad_propiedades[0]['protocolo_privacidad'],$comunidad_propiedades[0]['pass_privacidad'],'1.3.6.1.2.1.25.1.1.0'),19);
                    $contacto         = substr(snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'authPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],$comunidad_propiedades[0]['protocolo_privacidad'],$comunidad_propiedades[0]['pass_privacidad'],'1.3.6.1.2.1.1.4.0'),9,-1);
                    $localizacion     = substr(snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'authPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],$comunidad_propiedades[0]['protocolo_privacidad'],$comunidad_propiedades[0]['pass_privacidad'],'1.3.6.1.2.1.1.6.0'),9,-1);
                    $ram              = explode(':',snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'authPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],$comunidad_propiedades[0]['protocolo_privacidad'],$comunidad_propiedades[0]['pass_privacidad'],'1.3.6.1.4.1.2021.4.5.0')); 
                    $ram              = round($ram[1] / 1024 / 1024);
                    $mac              = array_values(array_unique(explode(' ',exec('arp -a | findstr '.$ip.''))));
                    $mac              = Strtoupper(str_replace('-',':',$mac[2]));
                    consulta("UPDATE equipos SET nombre = '$nombre', descripcion = '$descripcion', tiempo_encendido = '$tiempo_encendido',
                    contacto = '$contacto', localizacion = '$localizacion', ram = $ram, mac = '$mac', actualizado = CURRENT_TIMESTAMP() WHERE id = $id_equipo");

                    $descripciones_discos = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'authPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],$comunidad_propiedades[0]['protocolo_privacidad'],$comunidad_propiedades[0]['pass_privacidad'],'1.3.6.1.2.1.25.2.3.1.3');
                    $espacio_discos       = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'authPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],$comunidad_propiedades[0]['protocolo_privacidad'],$comunidad_propiedades[0]['pass_privacidad'],'1.3.6.1.2.1.25.2.3.1.5');
                    $espacio_usado        = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'authPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],$comunidad_propiedades[0]['protocolo_privacidad'],$comunidad_propiedades[0]['pass_privacidad'],'1.3.6.1.2.1.25.2.3.1.6');
                    $allocation_units     = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'authPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],$comunidad_propiedades[0]['protocolo_privacidad'],$comunidad_propiedades[0]['pass_privacidad'],'1.3.6.1.2.1.25.2.3.1.4');
                    $indices_discos       = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'authPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],$comunidad_propiedades[0]['protocolo_privacidad'],$comunidad_propiedades[0]['pass_privacidad'],'1.3.6.1.2.1.25.2.3.1.1');
                    actualizar_discos($id_equipo,$indices_discos,$descripciones_discos,$espacio_discos,$espacio_usado,$allocation_units);

                    if(strpos($posible_os, 'Windows') > 0 && !empty($id_wmi)){
                        actualizar_windows($id_equipo,$id_wmi[0][0],$ip);
                    }elseif(strpos($posible_os, 'Linux') > 0){
                        $lista_linux = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'authPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],$comunidad_propiedades[0]['protocolo_privacidad'],$comunidad_propiedades[0]['pass_privacidad'],'1.3.6.1.4.1.8072.1.3.2.3.1.1');
                        actualizar_linux($id_equipo,$lista_linux);
                    }

                }elseif($comunidad_propiedades[0]['nivel_seguridad'] == 'authNoPriv'){
                    $nombre           = substr(snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'authNoPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],'1.3.6.1.2.1.1.5.0'),9,-1);
                    $descripcion      = substr(snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'authNoPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],'1.3.6.1.2.1.1.1.0'),9,-1);   
                    $tiempo_encendido = substr(snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'authNoPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],'1.3.6.1.2.1.25.1.1.0'),19);
                    $contacto         = substr(snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'authNoPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],'1.3.6.1.2.1.1.4.0'),9,-1);
                    $localizacion     = substr(snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'authNoPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],'1.3.6.1.2.1.1.6.0'),9,-1);
                    $ram              = explode(':',snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'authNoPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],'1.3.6.1.4.1.2021.4.5.0')); 
                    $ram              = round($ram[1] / 1024 / 1024);
                    $mac              = array_values(array_unique(explode(' ',exec('arp -a | findstr '.$ip.''))));
                    $mac              = Strtoupper(str_replace('-',':',$mac[2]));
                    consulta("UPDATE equipos SET nombre = '$nombre', descripcion = '$descripcion', tiempo_encendido = '$tiempo_encendido',
                    contacto = '$contacto', localizacion = '$localizacion', ram = $ram, mac = '$mac', actualizado = CURRENT_TIMESTAMP() WHERE id = $id_equipo");
  
                    $descripciones_discos = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'authNoPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],'1.3.6.1.2.1.25.2.3.1.3');
                    $espacio_discos       = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'authNoPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],'1.3.6.1.2.1.25.2.3.1.5');
                    $espacio_usado        = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'authNoPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],'1.3.6.1.2.1.25.2.3.1.6');
                    $allocation_units     = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'authNoPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],'1.3.6.1.2.1.25.2.3.1.4');
                    $indices_discos       = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'authNoPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],'1.3.6.1.2.1.25.2.3.1.1');
                    actualizar_discos($id_equipo,$indices_discos,$descripciones_discos,$espacio_discos,$espacio_usado,$allocation_units);

                    if(strpos($posible_os, 'Windows') > 0 && !empty($id_wmi) ){
                        actualizar_windows($id_equipo,$id_wmi[0][0],$ip);
                    }elseif(strpos($posible_os, 'Linux') > 0){
                        $lista_linux = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'authNoPriv',$comunidad_propiedades[0]['protocolo_autenticacion'],$comunidad_propiedades[0]['pass_autenticacion'],'1.3.6.1.4.1.8072.1.3.2.3.1.1');
                        actualizar_linux($id_equipo,$lista_linux);
                    }

                }else{
                    $nombre           = substr(snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'noAuthNoPriv','1.3.6.1.2.1.1.5.0'),9,-1);
                    $descripcion      = substr(snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'noAuthNoPriv','1.3.6.1.2.1.1.1.0'),9,-1);   
                    $tiempo_encendido = substr(snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'noAuthNoPriv','1.3.6.1.2.1.25.1.1.0'),19);
                    $contacto         = substr(snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'noAuthNoPriv','1.3.6.1.2.1.1.4.0'),9,-1);
                    $localizacion     = substr(snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'noAuthNoPriv','1.3.6.1.2.1.1.6.0'),9,-1);
                    $ram              = explode(':',snmp3_get($ip,$comunidad_propiedades[0]['usuario'],'noAuthNoPriv','1.3.6.1.4.1.2021.4.5.0')); 
                    $ram              = round($ram[1] / 1024 / 1024);
                    $mac              = array_values(array_unique(explode(' ',exec('arp -a | findstr '.$ip.''))));
                    $mac              = Strtoupper(str_replace('-',':',$mac[2]));
                    consulta("UPDATE equipos SET nombre = '$nombre', descripcion = '$descripcion', tiempo_encendido = '$tiempo_encendido',
                    contacto = '$contacto', localizacion = '$localizacion', ram = $ram, mac = '$mac', actualizado = CURRENT_TIMESTAMP() WHERE id = $id_equipo");

                    $descripciones_discos = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'noAuthNoPriv','1.3.6.1.2.1.25.2.3.1.3');
                    $espacio_discos       = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'noAuthNoPriv','1.3.6.1.2.1.25.2.3.1.5');
                    $espacio_usado        = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'noAuthNoPriv','1.3.6.1.2.1.25.2.3.1.6');
                    $allocation_units     = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'noAuthNoPriv','1.3.6.1.2.1.25.2.3.1.4');
                    $indices_discos       = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'noAuthNoPriv','1.3.6.1.2.1.25.2.3.1.1');
                    actualizar_discos($id_equipo,$indices_discos,$descripciones_discos,$espacio_discos,$espacio_usado,$allocation_units);

                    if(strpos($posible_os, 'Windows') > 0 && !empty($id_wmi)){
                        actualizar_windows($id_equipo,$id_wmi[0][0],$ip);
                    }elseif(strpos($posible_os, 'Linux') > 0){
                        $lista_linux = snmp3_walk($ip,$comunidad_propiedades[0]['usuario'],'noAuthNoPriv','1.3.6.1.4.1.8072.1.3.2.3.1.1');
                        actualizar_linux($id_equipo,$lista_linux);
                    }

                }
            }

        }
        
    }else{
        echo "<script>alert('No hagas cosas raras')</script>";
    }
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

function actualizar_discos($id_equipo,$indices_discos,$descripciones_discos,$espacio_discos,$espacio_usado,$allocation_units){
    for($i = 0; $i < count($indices_discos); $i++){
        $descripcion_disco   = substr($descripciones_discos[$i],9,-1);
        $unidades            = explode(':', $allocation_units[$i]);
        $espacio_disco       = explode(':',$espacio_discos[$i]);
        $espacio_disco       = round(((int)$espacio_disco[1] * (int)$unidades[1]) / 1024 / 1024 / 1024,2);
        $espacio_usado_disco = explode(':',$espacio_usado[$i]);
        $espacio_usado_disco = round(((int)$espacio_usado_disco[1] * (int)$unidades[1]) / 1024 / 1024 / 1024,2);
        $indice_disco        = explode(':',$indices_discos[$i]);
        $id_disco            = consulta("SELECT indice_disco FROM discos WHERE id_equipo = $id_equipo AND indice_disco = ".$indice_disco[1]."");
        if(empty($id_disco)){
            consulta("INSERT INTO discos VALUES ($id_equipo,$indice_disco[1],'$descripcion_disco',$espacio_usado_disco,$espacio_disco)");
        }else{
            consulta("UPDATE discos SET descripcion = '$descripcion_disco', espacio_usado = '$espacio_usado_disco', espacio_total = $espacio_disco
            WHERE id_equipo = $id_equipo AND indice_disco = ".$indice_disco[1]."");
        }
    }
}

function actualizar_linux($id_equipo,$lista_linux){
    $cpu            = substr($lista_linux[0],22,-1);
    $bios_version   = substr($lista_linux[1],9,-1);
    $num_serie      = substr($lista_linux[2],9,-1);
    $modelo         = substr($lista_linux[3],9,-1);
    $os_version     = substr($lista_linux[4],23,-3);
    $fabricante     = substr($lista_linux[5],9,-1);
    $arquitectura   = substr($lista_linux[6],9,-1);
    $ultimo_usuario = explode(' ',$lista_linux[7]);
    $ultimo_usuario = substr($ultimo_usuario[1],1);
    consulta("UPDATE equipos SET fabricante = '$fabricante',modelo = '$modelo', numero_de_serie = '$num_serie', version = '$os_version',
    procesador = '$cpu', ultimo_usuario = '$ultimo_usuario', bios = '$bios_version', arquitectura = '$arquitectura', os = 'Linux' 
    WHERE id = $id_equipo");
}
function actualizar_windows($id_equipo,$id_wmi,$ip){
    $wmi = consulta("SELECT * FROM wmi WHERE id = $id_wmi");
    try{
        $WbemLocator = new COM ("WbemScripting.SWbemLocator");
        if($wmi[0][3] == ''){ //SI NO TIENE DOMINIO
            $WbemServices = $WbemLocator->ConnectServer("$ip", 'root\\cimv2', $wmi[0][1], openssl_decrypt($wmi[0][2], 'aes128', $GLOBALS['pass_cifrado']));
        }else{ //SI TIENE DOMINIO
            $WbemServices = $WbemLocator->ConnectServer("$ip", 'root\\cimv2', $wmi[0][1].'@'.$wmi[0][3], openssl_decrypt($wmi[0][2], 'aes128', $GLOBALS['pass_cifrado']));
        }
    }catch (Exception $error_De_los_webos){
        consulta("DELETE FROM wmi_equipos WHERE id_wmi = $id_wmi"); // SI NO FUNCIONA ESK NO FUNCIONA POR ESO BORRAMOS
        echo $e;
    }
    $WbemServices->Security_->ImpersonationLevel = 3;
    $cpu_info       = $WbemServices->ExecQuery("Select * from Win32_Processor");
    $bios_info      = $WbemServices->ExecQuery("Select * from Win32_BIOS");
    $computersystem = $WbemServices->ExecQuery("Select * from Win32_ComputerSystem");
    $os_info        = $WbemServices->ExecQuery("Select * from Win32_OperatingSystem");
    $mac_lista      = $WbemServices->ExecQuery("Select * from Win32_NetworkAdapter");
    //$ip_buena = $WbemServices->ExecQuery("Select * from Win32_NetworkAdapterConfiguration"); // SI EL ORDENADOR NO ESTA EN LA MISMA SUBRED ARP -A NO TIENE EFECTO POR ESO LO PONGO.EN SNMP ES MUY COMPLICADO
    $pillar_usuario = $WbemServices->ExecQuery("Select * from Win32_NetworkLoginProfile");// REVISAR
    foreach($cpu_info as $instancia){
        $cpu = $instancia->Name;
    }
    foreach($bios_info as $instancia){
        $bios_version = $instancia->Description;
        $num_serie    = $instancia->SerialNumber;

    }
    foreach($computersystem as $instancia){
        $modelo       = $instancia->Model;
        $fabricante   = $instancia->Manufacturer;
        $arquitectura = substr($instancia->SystemType,0,3);
    }
    foreach($os_info as $instancia){
        $os_version = explode('|',$instancia->Name);
        $os_version = $os_version[0];
    }
    
    foreach($mac_lista as $instancia){
        if($instancia->NetConnectionStatus == 2){
            $mac = $instancia->MACAddress;
        }
    }
    /* COMPROBAR IP ASOCIADA A LA MAC REVISAR
    
    foreach($ip_buena as $instancia){
        if($instancia->Index == $indice_interfaz){
            $lista = get_object_vars($instancia->IPAddress);
            var_dump($lista);
            echo $instancia->Index;
            $mac = $mac;
        }else{
            unset($mac);
        }
    }
    */
    /*
    foreach($pillar_usuario as $instancia){
        $hola = $instancia->Name;
        $fecha = $instancia->LastLogon;
        var_dump($fecha);
        var_dump($hola);
    }
    */
    consulta("UPDATE equipos SET mac = '$mac', fabricante = '$fabricante',modelo = '$modelo', numero_de_serie = '$num_serie', version = '$os_version',
    procesador = '$cpu', bios = '$bios_version', arquitectura = '$arquitectura', os = 'Windows' WHERE id = $id_equipo");
}
function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    // $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 
?>