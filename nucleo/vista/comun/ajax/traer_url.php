<?php

require_once __DIR__ .'/verificasesion.php';
require_once __DIR__ . '/../../../controlador/class.Db.php';
if ($lnContinuar) {
    $traerUrl = $goDb->obtenerTabMae1('TRIM(DE2TMA)', 'HCWEB', "CL1TMA ='OPEN' AND CL2TMA ='SERVER'", null, '');
}
include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($traerUrl);
