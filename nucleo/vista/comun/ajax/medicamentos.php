<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';


if ($lnContinuar) {
	$lcAccion = (isset($_GET['accion'])?$_GET['accion']:(isset($_POST['accion'])?$_POST['accion']:''));
	$lcMedicamento= $_POST['lcMedicamento'] ?? '';
	$lnIngreso= $_POST['lnNroIngreso'] ?? 0;

	require_once __DIR__ . '/../../../controlador/class.MedicamentoFormula.php';
	$loMedicamento = new NUCLEO\MedicamentoFormula;
	
	switch ($lcAccion) {
			
		case 'consultarMedicamentos':
			$lcNombre = NUCLEO\AplicacionFunciones::fnSanitizar(trim($_REQUEST['nombre']??''));
			if(!empty($lcNombre)){
				$lcNombre = NUCLEO\AplicacionFunciones::fnSanitizar($lcNombre);
				$lcNombre = str_replace("'", "", $lcNombre);
				$lcNombre = explode(' ',$lcNombre);
			}
			$laRetorna = (new NUCLEO\MedicamentoFormula())->consultaListaMedicamentos($lcNombre,'',false);
			break;
		
		case 'consultarAntibioticos':
			$lcNombre = NUCLEO\AplicacionFunciones::fnSanitizar(trim($_REQUEST['nombre']??''));
			if(!empty($lcNombre)){
				$lcNombre = NUCLEO\AplicacionFunciones::fnSanitizar($lcNombre);
				$lcNombre = str_replace("'", "", $lcNombre);
				$lcNombre = explode(' ',$lcNombre);
			}
			$laRetorna = $loMedicamento->consultarListaAntibioticos($lcNombre);
			break;
			
		case 'consultarIndicacionesInvima':
			$laRetorna['TIPOS']=$loMedicamento->consultaIndicacionesInvima($lcMedicamento);
			break;

		case 'consultarAlertaInr':
			$laRetorna['TIPOS']=$loMedicamento->consultaAlertaInr($lnIngreso,$lcMedicamento);
			break;
			
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
