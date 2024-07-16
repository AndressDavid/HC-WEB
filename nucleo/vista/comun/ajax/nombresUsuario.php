<?php


require_once __DIR__ . '/verificasesion.php';

require_once __DIR__ . '/../../../controlador/class.Db.php';
$nombresUsuario = $_SESSION[HCW_NAME]->oUsuario->getNombreCompleto();

include __DIR__ . '/../../../publico/headJSON.php';

echo json_encode($nombresUsuario);
