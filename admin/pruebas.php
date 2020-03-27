<?php
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



?>