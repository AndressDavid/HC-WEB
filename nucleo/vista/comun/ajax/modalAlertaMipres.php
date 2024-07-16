<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	$lcAccion = $_POST['accion'] ?? '';
	require_once __DIR__ .'/../../../controlador/class.DatosAmbulatorios.php';
	$loAmbulatorios = new NUCLEO\DatosAmbulatorios;

	switch ($lcAccion){
		case 'consultaparametros':
			$laRetorna['TIPOS'] = $loAmbulatorios->consultarMipresIntranet();
			unset($loAmbulatorios);
			break;
	}
	
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
