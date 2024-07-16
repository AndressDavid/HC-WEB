<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
	$loConductaSeguir = (new NUCLEO\ParametrosConsulta()) ;
	
	$loConductaSeguir->ObtenerModalidadGrupoServicio();
	$laTipos = $loConductaSeguir->ModalidadGrupoServicio();
	$laRetorna['TIPOS']=$laTipos;
	unset($laTipos);

}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
