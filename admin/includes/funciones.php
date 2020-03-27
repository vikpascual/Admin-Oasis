<?php
function limpia($texto){
    $limpio = htmlspecialchars($texto);
    return $limpio;
}
?>