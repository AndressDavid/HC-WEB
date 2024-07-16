<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion = (isset($_GET['accion'])?$_GET['accion']:(isset($_POST['accion'])?$_POST['accion']:''));
	$lcDiagnostico= $_POST['lcDiagnostico'] ?? '';
	$lcTitulo = $_POST['lcTitulo'] ?? '';
	
	require_once __DIR__ . '/../../../controlador/class.MedicamentoFormula.php';
	$loMedicamentoFormula = new NUCLEO\MedicamentoFormula;
	
	if (in_array($lcAccion, ['Reglas', 'Label'])) {
		$loMedicamentoFormula->objetosObligatoriosAntibioticos($lcTitulo);
		$laObjetos = $loMedicamentoFormula->ObjObligatoriosAntib() ;
	}
	
	switch ($lcAccion) {
		case 'consultaAntibioticos':
			$laRetorna['TIPOS']=$loMedicamentoFormula->consultarAntibioticos() ;
			break;
		
		case 'consultaAnexoCieInfeccioso':
			$laRetorna['TIPOS']=$loMedicamentoFormula->consultarAnexoCieInfeccioso($lcDiagnostico) ;
			break;
		
		case 'Reglas':
			$laRetorna['REGLAS'] = $laObjetos;
		break;
		
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
