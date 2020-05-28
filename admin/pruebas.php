<?php
 ob_implicit_flush(true); // UTILIZO ESTA FUNCION PARA NO USAR LA FUNCION FLUSH() CADA 2 POR 3
 ob_end_flush();
session_start();
include 'includes/funciones.php';
include 'includes/config/db.php';

/*
ob_flush();
$result = exec("nmap -sP 192.168.43.0/24");
var_dump($result);

echo "<h1>SNMP</h1>";
$connection = ssh2_connect('192.168.4.217', 22);
ssh2_auth_password($connection, 'localadmin', '1234567a.');

$sftp = ssh2_sftp($connection);
$sftp_fd = intval($sftp);
var_dump($sftpd_fd);
$handle = opendir("ssh2.sftp://$sftp_fd/etc");
echo "Directory handle: $handle\n";
echo "Entries:\n";
while (false != ($entry = readdir($handle))){
    echo "$entry<br>";
}
#en el punto de mira(discos) 1.3.6.1.2.1.25
#nombre 1.3.6.1.2.1.1.5.0
#ip...
#mac ¿arp -a?
#descripcion 1.3.6.1.2.1.1.1.0
#fabricante 1.3.6.1.2.1.47.1.1.1.1.12
#modelo 1.3.6.1.2.1.47.1.1.1.1.13
#numero de serie 1.3.6.1.2.1.47.1.1.1.1.11
#ram total 1.3.6.1.2.1.25.2.2.0
#os del description 2 opciones WINDOWS/LINUX
#version wmi o a mano
#tiempo encendido 1.3.6.1.2.1.25.1.1.0 
#procesador lo saco por aqui $a = snmp2_real_walk("192.168.43.167", "prueba", "1.3.6.1.2.1.25");
#ultimo usuario solo windows o manual
#bios ¿wmi? o manual
#arquitectura ¿wmi? manual o del description
#tipo de equipo wmi o manual
#localización
#storage index(mirar hermanos) 1.3.6.1.2.1.25.2.3.1.1.1

#1.3.6.1.2.1.25.3.3.1.2 Uso cpu
#.3.6.1.2.1.4.20.1.1.192.168.4.197 interfaz
#WINDOWS DISCO C C:\=[round(((((oid("1.3.6.1.2.1.25.2.3.1.5.1")-oid("1.3.6.1.2.1.25.2.3.1.6.1"))*oid("1.3.6.1.2.1.25.2.3.1.4.1"))/1024)/1024)/1024)]/[round(((oid("1.3.6.1.2.1.25.2.3.1.5.1")*oid("1.3.6.1.2.1.25.2.3.1.4.1")/1024)/1024)/1024)] GB
/*
$a = snmp2_get("192.168.43.197", "prueba", "1.3.6.1.2.1.1.1.0");
var_dump($a);

$a = snmp2_real_walk("192.168.4.197", "privateodecvlc", "1.3.6.1.2.1.25");
foreach ($a as $val) {
    echo "$val<br>";
}
*//*
if (!isset($_SESSION['consola']['sesion_iniciada'])){
    $conexion_shh = ssh2_connect('192.168.4.217', 22);
    var_dump($conexion_shh);
    $usuario    = 'localadmin';
    if(ssh2_auth_password($conexion_shh, $usuario, '1234567a.')){
        $shell = ssh2_shell($conexion_shh);
        sleep(1);
        $stream = stream_get_contents($shell);
        $_SESSION['consola']['sesion_iniciada'] = true;
        $_SESSION['consola']['salida'] = preg_split('/\r\n|\r|\n/', $stream); //TELITA CON ESTO
        foreach($_SESSION['consola']['salida'] as $value){
            echo '<pre>'.$value.'</pre>';
        }
        //usuario
        $orden = 'hostname';
        $stream = ssh2_exec($conexion_shh, $orden);
        sleep(1);
        $hostname = stream_get_contents($stream);
        $_SESSION['consola']['hostname'] = preg_split('/\r\n|\r|\n/', $hostname)[0];
    }
} else {
    
    $conexion_shh = ssh2_connect('192.168.4.217', 22);
    ssh2_auth_password($conexion_shh, 'localadmin', '1234567a.');
    //usuario
    $orden = 'whoami';
    $stream = ssh2_exec($conexion_shh, $orden);
    sleep(1);
    $usuario = stream_get_contents($stream);
    $usuario = preg_split('/\r\n|\r|\n/', $usuario)[0];
    //orden del usuario
    $orden = 'cd /etc';
    array_push($_SESSION['consola']['salida'], $usuario.'@'.$_SESSION['consola']['hostname'].'$ '.$orden);
    $stream = ssh2_exec($conexion_shh, $orden);
    sleep(1);
    $resultado = stream_get_contents($stream);
    $resultado = preg_split('/\r\n|\r|\n/', $resultado);
    foreach($resultado as $value){
        array_push($_SESSION['consola']['salida'],$value);
    }
    foreach($_SESSION['consola']['salida'] as $value){
        echo '<pre>'.$value.'</pre>';
    }
    var_dump($_SESSION['consola']['salida']);
    
    */
    //
    
    /*
    $orden = 'cat /etc/passwd';
    $_SESSION['consola']['salida'] .= $usuario.'@'.$_SESSION['consola']['hostname'].': '.$orden.PHP_EOL;
    $stream = ssh2_exec($conexion_shh, $orden);
    sleep(1);
    $_SESSION['consola']['salida'] .= stream_get_contents($stream);
    echo '<pre>'. $_SESSION['consola']['salida'].'</pre>';
    */
    

///}
/*
$conexion_shh = ssh2_connect('192.168.4.217', 22);
ssh2_auth_password($conexion_shh, 'localadmin', '1234567a.');
$shell=ssh2_shell($conexion_shh);
fwrite( $shell, 'ls /home'.PHP_EOL);
sleep(1);
fwrite( $shell, 'ls -a'.PHP_EOL);
sleep(1);
 // si no no d tiempo a que se genere el resultado
$salida = stream_get_contents($shell);
$_SESSION['consola']['sesion_iniciada']
echo '<pre>'.$salida.'</pre>';

fclose($shell);
*/

/*
function ping($host,$port=80,$timeout=6)
{
        $fsock = fsockopen($host, $port, $errno, $errstr, $timeout);
        if ( ! $fsock )
        {
                return FALSE;
        }
        else
        {
                return TRUE;
        }
}
*/

/*LDAP */

echo "<h1>LDAP</h1>";
//INICIO SESION
$ldapconn = ldap_connect('192.168.43.100', 389) or die("Could not connect");

if ($ldapconn) {
    /*ESTO HA DADO MUCHOS PROBLEMAS FUNCIONA COMO LE DA LA GANA EN PRINCIPIO HAY QUE PONERLO ENTRE EL CONNECT Y EL BIND PERO A VECES NO FUNCIONA Y CAMBIANDOLO DE LUGAR FUNCIONA */
    ldap_set_option ($ldapconn, LDAP_OPT_REFERRALS, 0); //https://stackoverflow.com/questions/6222641/how-to-php-ldap-search-to-get-user-ou-if-i-dont-know-the-ou-for-base-dn
    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3); // https://stackoverflow.com/questions/6222641/how-to-php-ldap-search-to-get-user-ou-if-i-dont-know-the-ou-for-base-dn
    // realizando la autenticación
    $ldapbind = ldap_bind($ldapconn, 'CN=Administrador,CN=users,DC=ABASTOS,DC=ES', 'Ba66age');

    // verificación del enlace
    if ($ldapbind) {
        echo "LDAP bind successful...";
        $sr=ldap_read($ldapconn, 'CN=Administrador,CN=Users,DC=ABASTOS,DC=ES',"(objectclass=*)",$justthese);

        //$busqueda = ldap_search($ldapconn,'DC=ABASTOS,DC=ES','(|(objectClass=Container)(objectClass=OrganizationalUnit))');
        $busqueda = ldap_list($ldapconn,'DC=ABASTOS,DC=ES','(|(objectClass=Container)(objectClass=OrganizationalUnit))');
        $entry = ldap_get_entries($ldapconn, $busqueda);
        $estructura = [];
        foreach($entry as $value){
            if($value['distinguishedname'][0] != ''){
                $estructura[$value['distinguishedname'][0]] = '';         
            }
        }
        
        //aqui va la funcion
        recorrer($ldapconn,$estructura,true);
        //aqui termina
        
        var_dump($estructura,$ldapconn,$busqueda,$entry);
    } else {
        echo "LDAP bind failed...";
    }

}
/*
set_time_limit(30);
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);

// config
$ldapserver = '192.168.43.100';
$ldapuser      = 'CN=Administrador,CN=users,DC=ABASTOS,DC=ES'; 
$ldappass     = 'Ba66age';
$ldaptree    = "DC=abastos,DC=es";

// connect
$ldapconn = ldap_connect($ldapserver,389) or die("Could not connect to LDAP server.");

if($ldapconn) {
    // binding to ldap server
    $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass) or die ("Error trying to bind: ".ldap_error($ldapconn));
    // verify binding
    if ($ldapbind) {
        echo "LDAP bind successful...<br /><br />";
       
       
        $result = ldap_search($ldapconn,$ldaptree, "(cn=*)") or die ("Error in search query: ".ldap_error($ldapconn));
        $data = ldap_get_entries($ldapconn, $result);
       
        // SHOW ALL DATA
        echo '<h1>Dump all data</h1><pre>';
        print_r($data);   
        echo '</pre>';
       
       
        // iterate over array and print data for each entry
        echo '<h1>Show me the users</h1>';
        for ($i=0; $i<$data["count"]; $i++) {
            //echo "dn is: ". $data[$i]["dn"] ."<br />";
            echo "User: ". $data[$i]["cn"][0] ."<br />";
            if(isset($data[$i]["mail"][0])) {
                echo "Email: ". $data[$i]["mail"][0] ."<br /><br />";
            } else {
                echo "Email: None<br /><br />";
            }
        }
        // print number of entries found
        echo "Number of entries found: " . ldap_count_entries($ldapconn, $result);
    } else {
        echo "LDAP bind failed...";
    }

}

// all done? clean up
ldap_close($ldapconn);
/*
$hola = openssl_encrypt('hola k ase', 'aes128', 'abastos',456);
$hola2 = openssl_decrypt($hola, 'aes128', 'abastos',456);
print_r($hola);
print_r($hola2);

$prefix = 24;
$ip_count = 1 << (32 - $prefix);
print_r($ip_count);
echo '<br>';
$start = ip2long('192.168.4.0');
print_r($start);
echo '<br>';
for ($i = 0; $i < $ip_count; $i++) {
    $ip = long2ip($start + $i);
    echo $ip;
}
*/
?>