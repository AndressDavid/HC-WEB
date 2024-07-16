<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	$lcAccion = $_POST['accion'] ?? '';
	require_once __DIR__ .'/../../../controlador/class.EscalasRiesgoSangrado.php';
	$loHasbled = new NUCLEO\EscalasRiesgoSangrado;

	switch ($lcAccion){

		case 'cargarDatosEscHas':
			$laRetorna = $loHasbled->cargarDatosEscHas();
			unset($loHasbled);
			break;

		case 'interpretarEscHas':
			$laRetorna = $loHasbled->interpretarEscHas();
			unset($loHasbled);
			break;
		
		case 'ConsultarEscala':
			$lnIngreso = $_POST['ingreso'] ?? '';
			$laRetorna['DATOS'] = $loHasbled->ConsultarEscalaSangrado($lnIngreso, 1);
			unset($loHasbled);
			break;		
	}
}
include __DIR__ . '/../../../publico/headJSON.php';
echo json_encode($laRetorna);
