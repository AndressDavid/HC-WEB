<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.ConsultaInfectologia.php';
	$loConsultaInf = new NUCLEO\ConsultaInfectologia();

	// Acción a ejecutar
	$lcAccion = $_POST['accion'] ?? '';
	switch ($lcAccion) {

		// Retorna los datos para el ingreso consultado
		case 'ingreso':
			require_once __DIR__ . '/../../../controlador/class.Historia_Clinica_Ingreso.php';
			$loHcIng = new NUCLEO\Historia_Clinica_Ingreso();
			$laRetorna['datos'] = $loHcIng->datosIngreso(intval($_POST['ingreso']??'0'));
			break;

		// Retorna trazabilidad de Infectologia del ingreso consultado
		case 'Infectologia':
			$laRetorna = $loConsultaInf->ConsultaInfectologia(
				intval($_POST['Ingreso']??'0'),
				$_POST['FechaDesde']??'',
				$_POST['FechaHasta']??'',
				$_POST['cCodigoMedicamentoInf']??''
			);
			break;

	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
