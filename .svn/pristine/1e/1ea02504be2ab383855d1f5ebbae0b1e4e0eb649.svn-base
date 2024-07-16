<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	$lcAccion = $_POST['accion'] ?? '';
	require_once __DIR__ .'/../../../controlador/class.EscalasRiesgoSangrado.php';
	$loChadsvas = new NUCLEO\EscalasRiesgoSangrado;

	switch ($lcAccion){

		case 'cargarDatosEsChad':
			$laRetorna = $loChadsvas->cargarDatosEsChadsvas();
			unset($loChadsvas);
			break;

		case 'interpretarEsChad':
			$laRetorna = $loChadsvas->interpretarEsChadsvas();
			unset($loChadsvas);
			break;
			
		case 'ConsultarEscala':
			$lnIngreso = $_POST['ingreso'] ?? '';
			$laRetorna['DATOS'] = $loChadsvas->ConsultarEscalaSangrado($lnIngreso, 2);
			unset($loChadsvas);
			break;	
	}
}
include __DIR__ . '/../../../publico/headJSON.php';
echo json_encode($laRetorna);
