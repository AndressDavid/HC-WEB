<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion = $_POST['accion'] ?? '';
	
	require_once __DIR__ . '/../../../controlador/class.PlanPaciente.php';
	$loPlanesPaciente = new NUCLEO\PlanPaciente;

	
	switch ($lcAccion) {
		
		case 'planesdelpaciente':
			$loPlanesPaciente->planesPaciente($_POST['tipoIdentifica'],$_POST['numIdentifica']);
			$laRetorna['datos'] = $loPlanesPaciente->listaPlanesPaciente();
		break;
			
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
