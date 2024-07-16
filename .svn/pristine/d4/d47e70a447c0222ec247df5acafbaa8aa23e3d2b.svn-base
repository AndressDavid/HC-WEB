<?php

require_once __DIR__ . '/verificasesion.php';
require_once __DIR__ . '/../../../controlador/class.Db.php';

require_once (__DIR__ .'/../../../controlador/class.UsuarioRecordatorio.php');
$loUsuarioRecordatorio = new NUCLEO\UsuarioRecordatorio($_SESSION[HCW_NAME]->oUsuario->getUsuario());
$loUsuarioRecordatorio->cargar();

if ($loUsuarioRecordatorio->getCuentaRecordatorios() >= 0) {
    $recordatorios = $loUsuarioRecordatorio->getCuentaRecordatorios();
}

include __DIR__ . '/../../../publico/headJSON.php';
echo json_encode($recordatorios);
