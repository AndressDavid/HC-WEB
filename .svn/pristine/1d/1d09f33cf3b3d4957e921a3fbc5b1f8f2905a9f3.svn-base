<?php


require_once __DIR__ . '/verificasesion.php';

require_once __DIR__ . '/../../../controlador/class.Db.php';
$Usuario = $_SESSION[HCW_NAME]->oUsuario->getUsuario();

include __DIR__ . '/../../../publico/headJSON.php';

echo json_encode($Usuario);