<?php
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {
	require_once __DIR__ . '/../../../controlador/class.Ingreso.php';
	$loIngreso = new NUCLEO\Ingreso();
	$laRetorna['TIPOS']=[];

	$laRetorna['TIPOS']=$loIngreso->listadoEstadosIngreso();
	unset($loIngreso);
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
