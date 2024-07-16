<?php


require_once __DIR__ . '/verificasesion.php';

require_once __DIR__ . '/../../../controlador/class.Db.php';
$Especialidad = $_SESSION[HCW_NAME]->oUsuario->getEspecialidad();

include __DIR__ . '/../../../publico/headJSON.php';

echo json_encode($Especialidad);