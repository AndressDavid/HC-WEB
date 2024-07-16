<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion = $_POST['accion'] ?? '';

	if (isset($_POST['lcGenero'])) {
		$lcGeneroPaciente =  $_POST['lcGenero'] ;
	}
	
	require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
	$loEvoluciones = new NUCLEO\ParametrosConsulta;

	switch ($lcAccion) {

		case 'listasDiagnosticos':
			$loEvoluciones->ObtenerComplicacionesUci();
			$laRetorna['datos'] = $loEvoluciones->TiposComplicacionesUci();
			break;

	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
