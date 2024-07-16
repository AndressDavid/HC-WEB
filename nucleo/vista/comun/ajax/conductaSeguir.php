<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
	$loConductaSeguir = (new NUCLEO\ParametrosConsulta()) ;
	
	if (isset($_POST['modulo'])) {
		$lcModulo =  $_POST['modulo'] ;
	}
	
	$loConductaSeguir->ObtenerConductaSeguir($_POST['ingreso'], $_POST['via'], $_POST['seccion'], $lcModulo,'');
	$laTipos = $loConductaSeguir->ConductasSeguir();
	$laRetorna['TIPOS']=$laTipos;
	unset($laTipos);

}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
