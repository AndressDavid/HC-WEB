<?php
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	$lcAccion = $_POST['accion'] ?? '';
	$lcViaIngresoAF = $_REQUEST['lcViaIngreso']??'';
	$lnIngresoAF = $_REQUEST['lnIngreso']??0;

	require_once __DIR__ .'/../../../controlador/class.EscalaActividadFisica.php';
	$loActividadFisica = new NUCLEO\EscalaActividadFisica;

	switch ($lcAccion){
		
		case 'consultarListados':
			$laRetorna['TIPOS']=$loActividadFisica->cargarListasActividad($lcViaIngresoAF);
			break;
			
		case 'consultarViaIngreso':
			$laRetorna['TIPOS']=$loActividadFisica->consultaViaIngresoActividad($lcViaIngresoAF);
			break;
			
		case 'verificarActividad':
			$laRetorna['TIPOS']=$loActividadFisica->consultaRegistroActividad($lnIngresoAF);
			break;
	}
	unset($loActividadFisica);

}
include __DIR__ . '/../../../publico/headJSON.php';
echo json_encode($laRetorna);
