<?php
error_reporting(0);
require_once __DIR__ . '/../../comun/ajax/verificasesion.php';

if (/*$lnContinuar*/true) {
	require_once __DIR__ . '/../../../controlador/class.DashBoardFE.php';

	$laRetornaDatos = [];
	setlocale(LC_ALL, "es_ES");
	date_default_timezone_set("America/Bogota");
	$ldfecha = date("Y-m-d");
	$lcAccion = $_POST['accion'] ?? '';
	
	$ldfechaConsulta = '';
	$loInformacion = new NUCLEO\DashBoardFE;

	$ldfechaInicial = str_replace("-", "",$_POST['fechaIni'] ?? $ldfecha);
	$ldfechaFinal = str_replace("-", "",$_POST['fechaFin'] ?? '');

	if (strval($ldfechaInicial) === strval($ldfechaFinal)) {
		$ldfechaConsulta = "=$ldfechaInicial";
	} else {
		$ldfechaConsulta = "BETWEEN $ldfechaInicial AND $ldfechaFinal";
	}
	
	switch ($lcAccion) {
		case 'error':
			$laRetornaDatos = $loInformacion->obtenerDocumentosError($ldfechaConsulta);
			break;
		case 'pendientes':
			$laRetornaDatos = $loInformacion->obtenerDocumentosPendientes($ldfechaConsulta);
			break;
		case 'enviar':
			$laRetornaDatos = $loInformacion->obtenerDocumentosPorEnviar($ldfechaConsulta);
			break;
		case 'exitosos':
			$laRetornaDatos = $loInformacion->obtenerDocumentosExitosos($ldfechaConsulta);
			break;
		case 'docGenerados':
			$laRetornaDatos = $loInformacion->obtenerCantidadDocumentosGenerados($ldfechaConsulta);
			break;
		case 'disEstados':
			$laRetornaDatos = $loInformacion->obtenerDistribucionEstados($ldfechaConsulta);
			break;
		case 'parametrica':
			$laRetornaDatos = $loInformacion->obtenerDatosPrametrica();
			break;
		default:
			$laRetornaDatos = [
				"error" => "",
				"informacionDoc" => []
			];
			break;
	}
}
echo json_encode($laRetornaDatos);
