<?php
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {
	$lnNroIngreso= $_POST['lnIngreso'] ?? 0;

	require_once __DIR__ . '/../../../controlador/class.OrdenesMedicas.php';
	$loOrdenesMedicas = new NUCLEO\OrdenesMedicas;
	$laRetorna['TIPOS']=$loOrdenesMedicas->consultaMedicamentosSuspendidos($lnNroIngreso) ;

}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
