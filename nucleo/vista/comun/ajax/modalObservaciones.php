<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.Observaciones.php';
	$loObjC = new NUCLEO\Observaciones() ;
	$lcTipo = $_POST['TipoConsulta'] ;

	switch($lcTipo)
	{
		case 'Urgencias':
			$lnIngreso = $_POST['ingreso'] ;
			$laRetorna['DATOS'] = $loObjC->retornarDocumento($lcTipo, $lnIngreso);
			break;
		
		case 'Verificar':
			$laDatos = $_POST['datos'];
			$laRetorna = $loObjC->verificarObs($laDatos);
			if($laRetorna['Valido']){
				$laRetorna = $loObjC->GuardarObs($laDatos);
			}
			break;
	}

	unset($loObjC);
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);




