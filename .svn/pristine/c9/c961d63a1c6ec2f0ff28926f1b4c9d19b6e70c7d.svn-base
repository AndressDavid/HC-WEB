<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	$lcAccion = $_POST['accion'] ?? '';
	
	switch ($lcAccion){

		case 'interpretarEscSadPersons':
			require_once __DIR__ .'/../../../controlador/class.EscalaSadPersons.php';
			$loSadPersons = new NUCLEO\EscalaSadPersons;
			$laRetorna = $loSadPersons->interpretarEscalaSadPersons();
			unset($loSadPersons);
			break;

		case 'ConsultarEscala':
			require_once __DIR__ .'/../../../controlador/class.EscalasRiesgoSangrado.php';
			$loSadPersons = new NUCLEO\EscalasRiesgoSangrado;
			$lnIngreso = $_POST['ingreso'] ?? '';
			$laRetorna['DATOS'] = $loSadPersons->ConsultarEscalaSangrado($lnIngreso, 4);
			unset($loSadPersons);
			break;	
	}
}
include __DIR__ . '/../../../publico/headJSON.php';
echo json_encode($laRetorna);
