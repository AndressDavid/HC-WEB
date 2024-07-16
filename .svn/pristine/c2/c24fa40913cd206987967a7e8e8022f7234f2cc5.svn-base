<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {
	
	$lnIngreso =0;
	$lcTipId ='';
	$lcNroId ='';
	
	if (isset($_POST['ingreso'])) {
		$lnIngreso =  $_POST['ingreso'] ;
	}
	
	if (isset($_POST['TipoDoc'])) {
		$lcTipoDoc =  $_POST['TipoDoc'] ;
	}
	
	if (isset($_POST['NroDoc'])) {
		$lcNroId =  $_POST['NroDoc'] ;
	}
	
	$lcTipoConsulta =  $_POST['TipoConsulta'] ?? 0;
	
	$laDatosCM=['ninand'=>$lnIngreso,
				'codand'=>'17',
				'op7and'=>0,
				'pgmand'=>'',
				'tidand'=>$lcTipoDoc,
				'nidand'=>$lcNroId,
			   ] ;
	
	$laRetorna['DATOS']=[];
	require_once __DIR__ . '/../../../controlador/class.Conciliacion.php';
	
	if($lcTipoConsulta>0){
		$loObjeto = (new NUCLEO\Conciliacion($laDatosCM,$lcTipoConsulta));
		$laRetorna['DATOS']=$loObjeto->getDatosConciliacion();
	}	
		
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
