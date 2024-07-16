<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {
	require_once __DIR__ .'/../../../controlador/class.AplicacionFunciones.php';
	require_once __DIR__ .'/../../../controlador/class.Medicos.php';

	$lcAccion = $_REQUEST['accion'] ?? '';
	$laRetorna['medicos'] = [];

	$lcTiposUsu = NUCLEO\AplicacionFunciones::fnSanitizar($_REQUEST['tipos'] ?? '1,3,4,6,10,11,12,13');
	$lbActivos = NUCLEO\AplicacionFunciones::fnSanitizar($_REQUEST['activos'] ?? '1')=='1';

	switch ($lcAccion) {

		// consultar médicos por nombre
		case 'medicosNombre':
			$lcNombre = NUCLEO\AplicacionFunciones::fnSanitizar(trim($_REQUEST['nombre']??''));
			$lcRegMed = '';
			if(!empty($lcNombre)){
				$lcNombre = explode(' ',$lcNombre);
			}
			break;

		// consultar médico por registro
		case 'medicoRegistro':
			$lcNombre = '';
			$lcRegMed = NUCLEO\AplicacionFunciones::fnSanitizar(trim($_REQUEST['regmed']??''));
			break;

	}
	if(!empty($lcNombre) || !empty($lcRegMed)){
		$laRetorna['medicos'] = (new NUCLEO\Medicos())->buscarListaMedicosNombreRegistro($lcNombre,$lcRegMed,'*',$lcTiposUsu,!$lbActivos);
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
