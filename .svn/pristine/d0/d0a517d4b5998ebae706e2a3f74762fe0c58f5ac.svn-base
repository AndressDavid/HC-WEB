<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion = (isset($_GET['accion'])?$_GET['accion']:(isset($_POST['accion'])?$_POST['accion']:''));
	$lnNroIngreso= $_POST['lnIngreso'] ?? 0;
	$lcCodigoMed= $_POST['lcCodigoMedicamento'] ?? '';

	require_once __DIR__ . '/../../../controlador/class.Diagnostico.php';
	$loDiagnosticos = new NUCLEO\Diagnostico;
	
	require_once __DIR__ . '/../../../controlador/class.MedicamentoFormula.php';
	$loMedicamento = new NUCLEO\MedicamentoFormula;
	
	switch ($lcAccion) {
		case 'diagnosticosPaciente':
			$laRetorna['TIPOS']=$loDiagnosticos->consultaDiagnosticosPaciente($lnNroIngreso);
			break;
			
		case 'consultarMedicamento':
			$laRetorna['TIPOS']=$loMedicamento->consultaListaMedicamentos('',$lcCodigoMed,false);
			break;
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
