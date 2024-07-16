<?php
require_once __DIR__ .'/../../publico/constantes.php';
$laRetorna['error'] = '';
$nSalir = 0;
if(isset($_SESSION[HCW_NAME])){
	if($_SESSION[HCW_NAME]->oUsuario->getSesionActiva() !== true){
		$laRetorna['error'] = 'El usuario no tiene sesión activa';
		$nSalir = 1;
	}
}else{
	$laRetorna['error'] = 'Error en la sesión. Intente nuevamente.';
	$nSalir = 1;
}

if($nSalir == 0){
	$lcUsuario = ($_SESSION[HCW_NAME]->oUsuario->getUsuario());
	$lnIngreso = (isset($_POST['ingreso']) ? intval($_POST['ingreso']) : 0);
	$lcAccion = (isset($_POST['accion']) ? $_POST['accion'] : '');
	require_once __DIR__ .'/../../controlador/class.Ingreso.php';
	$loIngreso = new NUCLEO\Ingreso;
	require_once __DIR__ .'/../../controlador/class.MedicamentoFormula.php';
	$loMediFor = new NUCLEO\MedicamentoFormula;

	switch ($lcAccion){
		case 'cargarMedicamentosProgramados':
			$laRetorna = $loMediFor->consultarMedicamentosProgramados($lnIngreso);
			unset($loMediFor);
			break;

		case 'cargarMedicamentosNoProgramados':
			$laRetorna = $loMediFor->consultarMedicamentosNoProgramados($lnIngreso);
			unset($loMediFor);
			break;

		case 'administrarMedicamentosPaciente':
			$lnCodigoQr = (isset($_POST['CodigoQr']) ? $_POST['CodigoQr'] : 0);
			$lnCodigoQrDiluyente = isset($_POST['CodigoQrDiluyente']) ?  $_POST['CodigoQrDiluyente'] : '';
			$laRetorna = $loMediFor->administrarMedicamentosPaciente($lnIngreso, $lnCodigoQr, $lnCodigoQrDiluyente, $lcUsuario);
			unset($loMediFor);
			break;
	}
}
include __DIR__ .'/../../publico/headJSON.php';
echo json_encode($laRetorna);
