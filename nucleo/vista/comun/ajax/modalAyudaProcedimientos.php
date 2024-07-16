<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	$lcAccion = $_POST['accion'] ?? '';
	require_once __DIR__ .'/../../../controlador/class.DatosAmbulatorios.php';
	$loProcedimientos = new NUCLEO\DatosAmbulatorios;

	switch ($lcAccion){

		case 'listaProcedimientos':
			$loProcedimientos->TablaProcedimientos();
			$laRetorna['TIPOS'] = $loProcedimientos->ListadoProcedimientos();
			unset($loProcedimientos);
			break;
			
		case 'consultaTextoPaquetes':
			$laRetorna['TIPOS'] = $loProcedimientos->consultarTextoPaquetes();
			unset($loProcedimientos);
			break;
	}
	
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
