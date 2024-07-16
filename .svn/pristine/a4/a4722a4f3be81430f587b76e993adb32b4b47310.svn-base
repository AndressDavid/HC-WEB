<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.EvolucionesConsulta.php';
	$loEvoConsulta = new NUCLEO\EvolucionesConsulta();

	// Acción a ejecutar
	$lcAccion = $_POST['accion'] ?? '';
	switch ($lcAccion) {

		// Retorna los datos para el ingreso consultado
		case 'ingreso':
			require_once __DIR__ . '/../../../controlador/class.Historia_Clinica_Ingreso.php';
			$loHcIng = new NUCLEO\Historia_Clinica_Ingreso();
			$laRetorna['datos'] = $loHcIng->datosIngreso(intval($_POST['ingreso']??'0'));
			break;

		// Retorna las evoluciones del ingreso consultado
		case 'evoluciones':
			require_once __DIR__ . '/../../../controlador/class.Auditoria.php';
			$lnIngreso = intval($_POST['Ingreso']??'0');
			$lnFechaDesde = $_POST['FechaDesde']??'';
			$lnFechaHasta = $_POST['FechaHasta']??'';
			$lcFechasTodas = $_POST['TodasFechas']??'NO';
			(new NUCLEO\Auditoria())->guardarAuditoria($lnIngreso, 0, 0, '', 'CONSULTA_EVO_WEB', 'CONSULTA', 0, 'CONSULTA EVOLUCIONES '.($lcFechasTodas=='NO' ? $lnFechaDesde.' '.$lnFechaHasta : 'TODAS LAS FECHAS'), 'EVOCONSUL', '', 0);
			$laRetorna = $loEvoConsulta->consultaEvoluciones(
				$lnIngreso,
				$lnFechaDesde,
				$lnFechaHasta,
				$lcFechasTodas
			);
			break;

		// Obtener lista de documentos para exportar
		case 'listadoc':
			$lnIngreso = intval($_POST['ingreso']??'0');
			$lcTipo = $_POST['tipo']??'EVOLUCION';
			$laRetorna['lista'] = $loEvoConsulta->listaDocumentos($lnIngreso, $lcTipo);
			$laRetorna['numdoc'] = count($laRetorna['lista']);
			break;

		// Consulta y retorna parámetros de consulta
		case 'paramconsulta':
			$laRetorna['par'] = $loEvoConsulta->obtenerParametros();
			break;

	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
