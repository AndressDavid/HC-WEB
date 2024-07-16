<?php
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {
	$lcAccion = $_POST['accion'] ?? '';
	
	require_once __DIR__ . '/../../../controlador/class.Paciente.php';
	$loPaciente = new NUCLEO\Paciente;
	
	switch ($lcAccion) {
		
		case 'consultarGeneros':
			$laRetorna['datos']=$loPaciente->consultarListaGeneros() ;
			break;

	}	
	unset($loPaciente); 
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
