<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
	$lcViaIngreso = $_POST['viaingreso']??'';
	$lcOrigen = $_POST['origen']??'';
	$loTipoCausa = (new NUCLEO\ParametrosConsulta()) ;
	$loTipoCausa->ObtenerTipoCausa($lcViaIngreso, $lcOrigen);
	$laTipos = $loTipoCausa->TiposCausa();
	$laRetorna['TIPOS']=$laTipos;

	unset($laTipos);

}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
