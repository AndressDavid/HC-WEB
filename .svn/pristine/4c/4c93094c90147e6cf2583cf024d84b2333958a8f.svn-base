<?php
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	$lcAccion = $_POST['accion'] ?? '';
	require_once __DIR__ .'/../../../controlador/class.Euroscore.php';
	$loEuroscore = new NUCLEO\Euroscore;
	
	require_once __DIR__ . '/../../../controlador/class.Paciente.php';
	$loPaciente = new NUCLEO\Paciente;


	switch ($lcAccion){

		case 'listasEuroscore':
			$laRetorna['datosEuroscore'] = $loEuroscore->cargarDatosEuroscore();
			$laRetorna['gruposEuroscore'] = $loEuroscore->cargarGruposEuroscore();
			$laRetorna['generospacientes'] = $loPaciente->consultarListaGeneros();

			unset($loEuroscore);
			break;
	}
}
include __DIR__ . '/../../../publico/headJSON.php';
echo json_encode($laRetorna);
