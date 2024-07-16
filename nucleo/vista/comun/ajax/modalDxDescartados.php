<?php
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {
	$lnNroIngreso= $_POST['lnIngreso'] ?? 0;

	require_once __DIR__ . '/../../../controlador/class.Diagnostico.php';
	$loDiagnosticos = new NUCLEO\Diagnostico;
	$laRetorna['TIPOS']=$loDiagnosticos->consultaDxDescartados($lnNroIngreso) ;

}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
