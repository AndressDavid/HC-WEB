<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	// Acción a ejecutar
	$lcAccion = $_POST['accion'] ?? '';
	switch ($lcAccion) {

		// Retorna los datos para el ingreso consultado
		case 'ingreso':
			require_once __DIR__ . '/../../../controlador/class.Historia_Clinica_Ingreso.php';
			$loHcIng = new NUCLEO\Historia_Clinica_Ingreso();
			$laRetorna['datos'] = $loHcIng->datosIngreso(intval($_POST['ingreso']??'0'));
			break;

		// Retorna datos generales para aval
		case 'Aval':
			require_once __DIR__ . '/../../../controlador/class.ConsultaAval.php';
			$loConsultaAval = new NUCLEO\ConsultaAval();
			$laRetorna = $loConsultaAval->ConsultaAvalHCGeneral(
				intval($_POST['Ingreso']??'0'));
			break;

	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);