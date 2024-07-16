<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion = $_POST['accion'] ?? '';
	
	require_once __DIR__ . '/../../../controlador/class.DatosAmbulatorios.php';
	$loOrdenesAmb = new NUCLEO\DatosAmbulatorios;
	
	switch ($lcAccion) {
		case 'textotitulo':
			$laRetorna['TIPOS']=$loOrdenesAmb->consultaTituloPlan() ;
			break;
			
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
