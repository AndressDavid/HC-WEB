<?php

require_once __DIR__ .'/verificasesion.php';

require_once __DIR__ . '/../../../controlador/class.Db.php';

 $sesionServidor = $_SESSION[HCW_NAME]->getServerName();

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($sesionServidor);
