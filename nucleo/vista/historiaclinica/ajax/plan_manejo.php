<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	$lcAccion = $_POST['lcConductaSeguir'] ;
	
	if (isset($_POST['lcDatosRecibeGuardar'])) {
		$lcDatosPlanGuarda = $_POST['lcDatosRecibeGuardar'] ;
	}	
	
	if (isset($_POST['lcCodigoEnviar'])) {
		$lcCodigoAEnvia =  $_POST['lcCodigoEnviar'] ;
	}
	
	require_once __DIR__ . '/../../../controlador/class.DatosPlanManejo.php';
	$loPlanManejo = new NUCLEO\DatosPlanManejo;
	$retornaValor = true;
		
	switch($lcAccion)
	{

		case 'consultarPlanManejo':
			$laTipos = $loPlanManejo->ConsultaPlanManejo($lnIngreso);	
		break;

		case 'validarPlanManejo':
			$laTipos = $loPlanManejo->validacion($lcDatosPlanGuarda);		
		break;	

		case 'validaReingreso':
			$laTipos = $loPlanManejo->validarReingreso();		
		break;
	}
	unset($loPlanManejo);
		
	if ($retornaValor){
		$laRetorna['TIPOS']=[];

		switch($lcAccion)
		{
			case 'consultarPlanManejo':
				$laRetorna['TIPOS']=$laTipos;
			break;
			
			case 'validarPlanManejo':
				$laRetorna['TIPOS']=$laTipos;
			break;
			
			case 'validaReingreso':
				$laRetorna['TIPOS']=$laTipos;
			break;

			default:
			foreach($laTipos as $lcTipo=>$laTipo)
				$laRetorna['TIPOS'][$lcTipo] = $laTipo['desc'] ;
		}		
		
		unset($laTipos);		
	}	
	
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
