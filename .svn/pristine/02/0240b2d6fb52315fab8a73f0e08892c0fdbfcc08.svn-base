<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {
	
	$lnIngreso =0;
	$lnConsec ='';
		
	if (isset($_POST['ingreso'])) {
		$lnIngreso =  $_POST['ingreso'] ;
	}
	
	if (isset($_POST['Consec'])) {
		$lnConsec =  $_POST['Consec'] ;
	}
	
	$lcTipoConsulta =  $_POST['TipoConsulta'] ?? '';
	$laRetorna['DATOS']=[];
	require_once __DIR__ . '/../../../controlador/class.ConsultaAval.php';
	
	$loObjeto = (new NUCLEO\ConsultaAval);
	if($lcTipoConsulta=='HC'){
		$laRetorna['DATOS']=$loObjeto->ConsultaAvalHCDetalle($lnIngreso, $lcTipoConsulta, $lnConsec);
	}else{
		$laRetorna['DATOS']=$loObjeto->ConsultaAvalEVDetalle($lnIngreso, $lcTipoConsulta, $lnConsec);
	}
		
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);