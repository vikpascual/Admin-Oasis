<?php
#exec($cmd . " > /dev/null &");
/*
    $pc = "."; 
    $obj = new COM ("winmgmts:\\\\".$pc."\\root\\cimv2");

    $disks =  $obj->ExecQuery("Select * from Win32_LogicalDisk");
    foreach ($disks as $d)
    {
        $str=sprintf("%s (%s) %s bytes, %4.1f%% free<br>", $d->Name,$d->VolumeName,number_format($d->Size,0,'.',','), $d->FreeSpace/$d->Size*100.0);

        echo $str;
    }

$a = snmp2_walk("192.168.43.167", "prueba", "");
foreach ($a as $val) {
    echo "$val<br>";
}
echo $a[4];
*/
/*
for($i=1;$i<11;$i++){
    $result = exec("ping -n 1 192.168.4.$i");
    if (strlen($result) > 18){
        echo "El host 192.168.4.$i is up";
    }

}
*/
echo "<h1>SNMP</h1>";
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
/*$a = snmp2_real_walk("192.168.43.167", "prueba", "1.3.6.1.4.1.2021.1.5");*/
/*
$a = snmp2_real_walk("192.168.43.167", "prueba", "1.3.6.1.2.1.25");
foreach ($a as $val) {
    echo "$val<br>";
}

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
/*INICIO SESION
$ldapconn = ldap_connect('192.168.4.8', 389) or die("Could not connect");
if ($ldapconn) {

    // realizando la autenticación
    $ldapbind = ldap_bind($ldapconn, 'CN=Victor Pascual,OU=Usuarios,OU=Valencia,DC=ODECGANDIA,DC=ES', '1234567a.');

    // verificación del enlace
    if ($ldapbind) {
        echo "LDAP bind successful...";
    } else {
        echo "LDAP bind failed...";
    }

}
*/
$hola = openssl_encrypt('hola k ase', 'aes128', 'abastos',456);
$hola2 = openssl_decrypt($hola, 'aes128', 'abastos',456);
print_r($hola);
print_r($hola2);
?>