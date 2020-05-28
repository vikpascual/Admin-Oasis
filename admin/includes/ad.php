<?php
if(isset($_SESSION['ldap']['conectado']) && $_SESSION['ldap']['conectado']){
    ?>
    <h1>Arbol LDAP</h1>
    <div id="arbol">
    <?php
    //INICIO SESION
    $ldapconn = ldap_connect($_SESSION['ldap']['ip'], $_SESSION['ldap']['puerto']);

    if ($ldapconn) {
        /*ESTO HA DADO MUCHOS PROBLEMAS FUNCIONA COMO LE DA LA GANA EN PRINCIPIO HAY QUE PONERLO ENTRE EL CONNECT Y EL BIND PERO A VECES NO FUNCIONA Y CAMBIANDOLO DE LUGAR FUNCIONA */
        ldap_set_option ($ldapconn, LDAP_OPT_REFERRALS, 0); //https://stackoverflow.com/questions/6222641/how-to-php-ldap-search-to-get-user-ou-if-i-dont-know-the-ou-for-base-dn
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3); // https://stackoverflow.com/questions/6222641/how-to-php-ldap-search-to-get-user-ou-if-i-dont-know-the-ou-for-base-dn
        // realizando la autenticación
        $ldapbind = ldap_bind($ldapconn, $_SESSION['ldap']['usuario'], $_SESSION['ldap']['pass']);

        // verificación del enlace
        if ($ldapbind) {
            //$busqueda = ldap_search($ldapconn,'DC=ABASTOS,DC=ES','(|(objectClass=Container)(objectClass=OrganizationalUnit))');
            $busqueda   = ldap_list($ldapconn,'DC=ABASTOS,DC=ES','(|(objectClass=Container)(objectClass=OrganizationalUnit))');
            $entry      = ldap_get_entries($ldapconn, $busqueda);
            $estructura = [];
            foreach($entry as $value){
                if($value['distinguishedname'][0] != ''){
                    $estructura[$value['distinguishedname'][0]] = '';         
                }
            }
            
            //aqui va la funcion
            ?>
            <script src="js/jquery.js"></script>
            <script>
                function mostrar(id) {
                    $('.carpeta').hide();
                    $('.OU').hide();
                    $('.usuario').hide();
                    $('.grupo').hide();
                    $('#'+id).show();
                }
            </script>
            <?php 
            //LIMPIAMOS DATOS DE SESION
            unset($_SESSION['ou']);
            unset($_SESSION['carpetas']);
            unset($_SESSION['usuarios']);
            unset($_SESSION['grupos']);
            recorrer($ldapconn,$estructura,true);
            echo ("</div>");
            //CREAMOS LAS FICHAS DE LAS CARPETAS
            foreach($_SESSION['carpetas'] as $value){
                $leer=ldap_read($ldapconn, $value, 'objectClass=*');
                $leer=ldap_get_entries($ldapconn,$leer);
                ?>
                <div class="carpeta" style="display:none" id="<?=str_replace(' ', '_',substr(ldap_explode_dn($leer[0]['distinguishedname'][0],0)[0],3) )?>">
                    <div class="izquierda">
                        <img src="img/carpeta.png">
                    </div>
                    <div class="derecha">
                        <h2><?=ldap_dn2ufn($leer[0]['distinguishedname'][0])?></h2>
                    </div>
                    <hr style="width:100%">
                    <form action="index.php" method="POST"> 
                    <div class="izquierda">
                        Descripción: 
                    </div>
                    <div class="derecha">
                        <input type="text" value="<?=$leer[0]['description'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                    </div>
                    <div class="derecha">
                            <input type="hidden" name="servicio" value="ad"></input>
                            <input type="hidden" name="accion" value="modificar_carpeta"></input>
                            <input type="hidden" name="dn_carpeta" value="<?=$value?>"></input>
                            <input type="submit" value="Modificar Carpeta"></input>
                        </div>
                        </form>

                    <hr style="width:100%">
                    <h4>CREAR UNIDAD ORGANIZATIVA EN ESTE CONTENEDOR</h4>
                    <form action="index.php" method="POST">
                        <input type="hidden" name="servicio" value="ad"></input>
                        <input type="hidden" name="acción" value="crear_ou"></input>
                        <input type="hidden" name="dn_ruta" value="<?=$value?>"></input>
                        Nombre de la OU: <input type="text" name="nombre_ou" style="border:1px solid black;"></input><input type="submit" value="CREAR"></input>
                    </form>
                    <hr style="width:100%">
                    <h4>CREAR USUARIO EN ESTE CONTENEDOR</h4>
                    <form action="index.php" method="POST">
                        <div class="izquierda">
                            Nom. Inicio Sesión: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="nombre_inicio" style="border:1px solid black; width:80%;margin-left:10%"></input><br>
                        </div>
                        <div class="izquierda">
                            Nom. de pila: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="pila" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Iniciales: <br>
                        </div>
                        <div class="derecha">
                                <input type="text"  name="iniciales" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Apellidos: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="apellidos" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Nom. a mostrar: <br>
                        </div>
                        <div class="derecha">
                                <input type="text"  name="nombre_mostrar" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Descripción: <br>
                        </div>
                        <div class="derecha">
                                <input type="text"  name="descripcion" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Oficina: <br>
                        </div>
                        <div class="derecha">
                                <input type="text"  name="oficina" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <hr style="width:100%">
                        <div class="izquierda">
                            Teléfono: <br>
                        </div>
                        <div class="derecha">
                                <input type="text"  name="telefono" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Correo electrónico: <br>
                        </div>
                        <div class="derecha">
                                <input type="text"  name="mail" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Web: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="web" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <hr style="width:100%">
                        <div class="izquierda">
                            Calle: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="calle" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>

                        <div class="izquierda">
                            Ciudad: 
                        </div>
                        <div class="derecha">
                                <input type="text"  name="ciudad" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>

                        <div class="izquierda">
                            Provincia: 
                        </div>
                        <div class="derecha">
                                <input type="text" name="provincia" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            C.Postal: 
                        </div>
                        <div class="derecha">
                                <input type="text" name="cpostal" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <input type="hidden" name="servicio" value="ad"></input>
                        <input type="hidden" name="acción" value="crear_usuario"></input>
                        <input type="hidden" name="dn_ruta" value="<?=$value?>"></input>
                        <input type="submit" value="Crear"></input>
                    </form>
                    <hr style="width:100%">
                    <h4>CREAR GRUPO EN ESTE CONTENEDOR</h4>
                    <form action="index.php" method="POST">
                        <div class="izquierda">
                            Nombre de grupo: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="nombre_grupo" value="Esta es la descripcion" style="border:1px solid black; width:80%;margin-left:10%"></input><br>
                        </div>
                        <div class="izquierda">
                            Descripción: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="descripcion" value="Esta es la descripcion" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Correo electronico: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="mail" value="Esta es la descripcion" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <input type="hidden" name="servicio" value="ad"></input>
                        <input type="hidden" name="acción" value="crear_grupo"></input>
                        <input type="hidden" name="dn_ruta" value="<?=$value?>"></input>
                        <input type="submit" value="Crear"></input>
                    </form>
                </div>
                <?php
            }//AQUI ACABAN LAS CARPETAS
            //CREAMOS LAS FICHAS DE LAS OUS
            foreach($_SESSION['ou'] as $value){
                $leer=ldap_read($ldapconn, $value, 'objectClass=*');
                $leer=ldap_get_entries($ldapconn,$leer);
                ?>
                <div class="OU" style="display:none;" id="<?=str_replace(' ', '_',substr(ldap_explode_dn($leer[0]['distinguishedname'][0],0)[0],3) )?>">
                    <div class="izquierda">
                        <img src="img/ou.png">
                    </div>
                    <div class="derecha">
                        <h2><?=ldap_dn2ufn($leer[0]['distinguishedname'][0])?></h2>
                    </div>
                    <hr style="width:100%">
                    <form action="index.php" method="POST">
                        <div class="izquierda">
                            Descripción: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="descripcion" value="<?=$leer[0]['description'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>

                        <div class="izquierda">
                            Calle: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="calle" value="<?=$leer[0]['street'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>

                        <div class="izquierda">
                            Ciudad: 
                        </div>
                        <div class="derecha">
                                <input type="text" name="ciudad" value="<?=$leer[0]['l'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>

                        <div class="izquierda">
                            Provincia: 
                        </div>
                        <div class="derecha">
                                <input type="text" name="provincia" value="<?=$leer[0]['st'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            C.Postal: 
                        </div>
                        <div class="derecha">
                                <input type="text" name="cpostal" value="<?=$leer[0]['postalcode'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="derecha">
                            <input type="hidden" name="servicio" value="ad"></input>
                            <input type="hidden" name="accion" value="modificar_ou"></input>
                            <input type="hidden" name="dn_ou" value="<?=$value?>"></input>
                            <input type="submit" value="Modificar Unidad Organizativa"></input>
                        </div>
                    </form>
                    <hr style="width:100%">
                    <h4>CREAR UNIDAD ORGANIZATIVA EN ESTA UNIDAD ORGANIZATIVA</h4>
                    <form action="index.php" method="POST">
                        <input type="hidden" name="servicio" value="ad"></input>
                        <input type="hidden" name="acción" value="crear_ou"></input>
                        <input type="hidden" name="dn_ruta" value="<?=$value?>"></input>
                        Nombre de la OU: <input type="text" name="nombre_ou" style="border:1px solid black;"></input><input type="submit" value="CREAR"></input>
                    </form>
                    <hr style="width:100%">
                    <h4>CREAR USUARIO EN ESTA UNIDAD ORGANIZATIVA</h4>
                    <form action="index.php" method="POST">
                        <div class="izquierda">
                            Nom. Inicio Sesión: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="nombre_inicio" style="border:1px solid black; width:80%;margin-left:10%"></input><br>
                        </div>
                        <div class="izquierda">
                            Nom. de pila: <br>
                        </div>
                        <div class="derecha">
                                <input type="text"  name="pila" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Iniciales: <br>
                        </div>
                        <div class="derecha">
                                <input type="text"  name="iniciales" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Apellidos: <br>
                        </div>
                        <div class="derecha">
                                <input type="text"  name="apellidos" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Nom. a mostrar: <br>
                        </div>
                        <div class="derecha">
                                <input type="text"  name="nombre_mostrar" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Descripción: <br>
                        </div>
                        <div class="derecha">
                                <input type="text"  name="descripcion" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Oficina: <br>
                        </div>
                        <div class="derecha">
                                <input type="text"  name="oficina" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <hr style="width:100%">
                        <div class="izquierda">
                            Teléfono: <br>
                        </div>
                        <div class="derecha">
                                <input type="text"  name="telefono" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Correo electrónico: <br>
                        </div>
                        <div class="derecha">
                                <input type="text"  name="mail" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Web: <br>
                        </div>
                        <div class="derecha">
                                <input type="text"  name="web" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <hr style="width:100%">
                        <div class="izquierda">
                            Calle: <br>
                        </div>
                        <div class="derecha">
                                <input type="text"  name="calle" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>

                        <div class="izquierda">
                            Ciudad: 
                        </div>
                        <div class="derecha">
                                <input type="text"  name="ciudad" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>

                        <div class="izquierda">
                            Provincia: 
                        </div>
                        <div class="derecha">
                                <input type="text" name="provincia" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            C.Postal: 
                        </div>
                        <div class="derecha">
                                <input type="text" name="cpostal" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <input type="hidden" name="servicio" value="ad"></input>
                        <input type="hidden" name="acción" value="crear_usuario"></input>
                        <input type="hidden" name="dn_ruta" value="<?=$value?>"></input>
                        <input type="submit" value="Crear"></input>
                    </form>
                    <hr style="width:100%">
                    <h4>CREAR GRUPO EN ESTA UNIDAD ORGANIZATIVA</h4>
                    <form action="index.php" method="POST">
                        <div class="izquierda">
                            Nombre de grupo: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="nombre_grupo" value="Esta es la descripcion" style="border:1px solid black; width:80%;margin-left:10%"></input><br>
                        </div>
                        <div class="izquierda">
                            Descripción: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="descripcion" value="Esta es la descripcion" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Correo electronico: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="mail" value="Esta es la descripcion" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <input type="hidden" name="servicio" value="ad"></input>
                        <input type="hidden" name="acción" value="crear_grupo"></input>
                        <input type="hidden" name="dn_ruta" value="<?=$value?>"></input>
                        <input type="submit" value="Crear"></input>
                    </form>
                </div>

                <?php
            }
            //CREAMOS LAS FICHAS DE LOS USUARIOS
            foreach($_SESSION['usuarios'] as $value){
                $leer=ldap_read($ldapconn, $value, 'objectClass=*');
                $leer=ldap_get_entries($ldapconn,$leer);
                ?>
                <div class="usuario" style="display:none;" id="<?=str_replace(' ', '_',$leer[0]['cn'][0])?>">
                    <div class="izquierda">
                        <img src="img/usuario.png">
                    </div>
                    <div class="derecha">
                        <h2><?=$leer[0]['cn'][0]?></h2>
                    </div>
                    <hr style="width:100%">
                    <form action="index.php" method="POST">
                        <div class="izquierda">
                            Nom. Inicio Sesión: <br>
                        </div>
                        <div class="derecha">
                            <input type="text" name="nombre_inicio" value="<?=$leer[0]['samaccountname'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input><br>
                        </div>
                        <div class="izquierda">
                            Nom. de pila: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="pila" value="<?=$leer[0]['givenname'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Iniciales: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="iniciales" value="<?=$leer[0]['initials'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Apellidos: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="apellidos" value="<?=$leer[0]['sn'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Nom. a mostrar: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="nombre_mostrar" value="<?=$leer[0]['displayname'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Descripción: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="descripcion" value="<?=$leer[0]['description'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Oficina: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="oficina" value="<?=$leer[0]['physicaldeliveryofficename'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <hr style="width:100%">
                        <div class="izquierda">
                            Teléfono: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="telefono" value="<?=$leer[0]['telephonenumber'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Correo electrónico: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="mail" value="<?=$leer[0]['mail'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Web: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="web" value="<?=$leer[0]['wwwhomepage'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <hr style="width:100%">
                        <div class="izquierda">
                            Calle: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="calle" value="<?=$leer[0]['streetaddress'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>

                        <div class="izquierda">
                            Ciudad: 
                        </div>
                        <div class="derecha">
                                <input type="text" name="ciudad" value="<?=$leer[0]['l'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>

                        <div class="izquierda">
                            Provincia: 
                        </div>
                        <div class="derecha">
                                <input type="text" name="provincia" value="<?=$leer[0]['st'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            C.Postal: 
                        </div>
                        <div class="derecha">
                                <input type="text" name="cpostal" value="<?=$leer[0]['postalcode'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                            Miembro de:<br> 
                        <div style="border: 1px solid black;width:100%">
                        <?php
                            unset($leer[0]['memberof']['count']);
                            foreach($leer[0]['memberof'] as $grupo){
                                echo '-'.$grupo.'<br>';
                            }

                        ?>
                        </div>
                        <div class="derecha">
                            <input type="hidden" name="servicio" value="ad"></input>
                            <input type="hidden" name="accion" value="modificar_usuario"></input>
                            <input type="submit" value="Modificar Datos de Usuario"></input>
                            <input type="hidden" name="dn_usuario" value="<?=$value?>"></input>
                        </div>
                    </form>
                    <hr style="width:100%">
                        <h4>Borrar este usuario</h4>
                        <form action="index.php" method="POST">
                            <input type="hidden" name="servicio" value="ad"></input>
                            <input type="hidden" name="acción" value="borrar_usuario"></input>
                            <input type="hidden" name="dn_usuario" value="<?=$value?>"></input>
                            <input type="submit" style="background:red" value="BORRAR EL USUARIO"></input>
                        </form>
                </div>
                <?php
            }
            //CREAMOS LAS FICHAS DE LOS GRUPOS
            foreach($_SESSION['grupos'] as $value){
                $leer=ldap_read($ldapconn, $value, 'objectClass=*');
                $leer=ldap_get_entries($ldapconn,$leer);
                ?>
                <div class="grupo" style="display:none" id="<?=str_replace(' ', '_',$leer[0]['cn'][0])?>" >
                    <div class="izquierda">
                        <img src="img/grupo.png">
                    </div>
                    <div class="derecha">
                        <h2><?=$leer[0]['cn'][0]?></h2>
                    </div>
                    <hr style="width:100%">
                    <form action="index.php" method="POST">
                        <div class="izquierda">
                            Nombre de grupo: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="nombre_grupo" value="<?=$leer[0]['cn'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input><br>
                        </div>
                        <div class="izquierda">
                            Descripción: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="descripcion" value="<?=$leer[0]['description'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        <div class="izquierda">
                            Correo electronico: <br>
                        </div>
                        <div class="derecha">
                                <input type="text" name="mail" value="<?=$leer[0]['mail'][0]?>" style="border:1px solid black; width:80%;margin-left:10%"></input>
                        </div>
                        Miembros<br>
                        <div style="width:100%;border:1px solid black;">
                        <?php
                            unset($leer[0]['member']['count']);
                            foreach($leer[0]['member'] as $miembro){
                                echo '-'.$miembro.'<br>';
                            }
                        ?>
                        </div>
                            <input type="hidden" name="servicio" value="ad"></input>
                            <input type="hidden" name="acción" value="modificar_grupo"></input>
                            <input type="hidden" name="dn_grupo" value="<?=$value?>"></input>
                            <input type="submit" value="MODIFICAR EL GRUPO"></input>
                        </form>
                        <hr>
                        <h4>Añadir usuario existente a este grupo</h4>
                        <form action="index.php" method="POST">
                            <input type="hidden" name="servicio" value="ad"></input>
                            <input type="hidden" name="acción" value="add_usuario_grupo"></input>
                            <input type="hidden" name="dn_grupo" value="<?=$value?>"></input>
                            DN del usuario existente: <input style="border:1px solid black;width:50%" type="text" name="dn_usuario"></input><input type="submit" value="añadir"></input>
                        </form>
                        <hr>
                        <h4>Borrar usuario de este grupo</h4>
                        <form action="index.php" method="POST">
                            <input type="hidden" name="servicio" value="ad"></input>
                            <input type="hidden" name="acción" value="borrar_usuario_grupo"></input>
                            <input type="hidden" name="dn_grupo" value="<?=$value?>"></input>
                            DN del usuario existente: <input style="border:1px solid black;width:50%" type="text" name="dn_usuario"></input><input type="submit" style="background:red" value="Borrar"></input>
                        </form>
                        <hr>
                        <h4>Borrar este grupo</h4>
                        <form action="index.php" method="POST">
                            <input type="hidden" name="servicio" value="ad"></input>
                            <input type="hidden" name="acción" value="borrar_grupo"></input>
                            <input type="hidden" name="dn_grupo" value="<?=$value?>"></input>
                            <input type="submit" style="background:red" value="BORRAR EL GRUPO"></input>
                        </form>
                        
                        
                    </form>
                </div>
                <?php
            }
        } else {
            echo '<script>alert("No se puede iniciar sesion")</script>';
            echo '<script>enviar("ad")</script>';
        }
    }
    ?>
    <?php
    
}else{
    if(isset($_POST['accion']) && $_POST['accion'] == 'conectar' ){
        if(isset($_POST['ip']) && !empty($_POST['ip']) && isset($_POST['puerto']) && !empty($_POST['puerto']) 
        && isset($_POST['usuario']) && !empty($_POST['usuario'])){
            if(filter_var($_POST['ip'], FILTER_VALIDATE_IP)){
                $puerto = (int)$_POST['puerto'];
                if(filter_var($puerto, FILTER_VALIDATE_INT) && $puerto > 0 && $puerto < 65536 ){
                    $ldapconn = ldap_connect($_POST['ip'], $puerto);
                    if ($ldapconn) {
                        /*ESTO HA DADO MUCHOS PROBLEMAS FUNCIONA COMO LE DA LA GANA EN PRINCIPIO HAY QUE PONERLO ENTRE EL CONNECT Y EL BIND PERO A VECES NO FUNCIONA Y CAMBIANDOLO DE LUGAR FUNCIONA */
                        ldap_set_option ($ldapconn, LDAP_OPT_REFERRALS, 0); 
                        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3); 
                        // realizando la autenticación
                        $ldapbind = ldap_bind($ldapconn, $_POST['usuario'], $_POST['pass']);

                        // verificación del enlace
                        if ($ldapbind) {
                            $_SESSION['ldap']['conectado'] = TRUE;
                            $_SESSION['ldap']['ip']        = $_POST['ip'];
                            $_SESSION['ldap']['puerto']    = $_POST['puerto'];
                            $_SESSION['ldap']['usuario']   = $_POST['usuario'];
                            $_SESSION['ldap']['pass']      = $_POST['pass'];
                            echo '<script>enviar("ad")</script>';
                        } else {
                            echo '<script>alert("Usuario o contraseña incorrectos")</script>';
                        }
                    }else{
                        echo '<script>alert("No se puede conectar con el servidor")</script>'; 
                    }
                }else{
                    echo '<script>alert("Debe introducir un puerto válido")</script>';
                }

            }else{
                echo '<script>alert("Debe introducir una dirección IP válida")</script>';
            }
        }else{
            echo '<script>alert("Faltan datos")</script>';
        }
    }
    ?>
    <h1>Conexión LDAP</h1>
    <form action="index.php" method="POST">
        IP SERVIDOR LDAP:<br>
        <input type="text" name="ip" required></input><br>
        PUERTO SERVIDOR LDAP:<br>
        <input type="number" name="puerto" min="1" max="65535" value="389" required></input><br>
        USUARIO (DEBE SER CON FORMATO DN):<br>
        <input type="text" name="usuario" placeholder="CN=Administrador,CN=Users,DC=ABASTOS,DC=ES" required></input><br>
        CONTRASEÑA:<br>
        <input type="password" name="pass"></input><br>
        <input type="hidden" name="servicio" value="ad"></input>
        <input type="hidden" name="accion" value="conectar"></input>
        <input type="submit" value="Conectar"></input>
    </form>
    <?php
}
?>