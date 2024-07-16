<?php

require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {
    
	require_once __DIR__ . '/../../../controlador/class.ConsultaExterna.php';
		$laRetorna['estados'] = (new NUCLEO\ConsultaExterna())->estadosProcedimientos();

}
include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);