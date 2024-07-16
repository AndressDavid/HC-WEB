<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.Auditoria.php';
	(new NUCLEO\Auditoria())->guardarAuditoria($_POST['ingreso'], 0, 0, '', 'DATOSPAC_WEB', 'DATOSPAC', 0, 'CONSULTA DATOS PACIENTE WEB', 'DATOSPAC', '', 0);

	require_once __DIR__ . '/../../../controlador/class.Ingreso.php';


	$lcObtenerPlanMedico = $_POST['obtenerPlanMedico'] ?? 'N';


	$loIngreso = new NUCLEO\Ingreso;
	$loIngreso->cargarIngresoPorFecha($_POST['ingreso'],$_POST['fechahoraingreso'], $lcObtenerPlanMedico);
	$laRetorna['DATOS']=$loIngreso;
	unset($laTipos);
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
