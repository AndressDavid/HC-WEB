<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	$lcTipoDsc = $_POST['descrip'] ?? 'T'; // A=abreviatura, D=descripción, T=todo (abreviatura + descripción),
	$lbHortiCero = $_POST['horti'] ?? 'T'; // A=abreviatura, D=descripción, T=todo (abreviatura + descripción),

	require_once __DIR__ . '/../../../controlador/class.TiposDocumento.php';
	$laTipos = (new NUCLEO\TiposDocumento($lbHortiCero))->aTipos;
	$laRetorna['TIPOS']=[];

	foreach($laTipos as $lcTipo=>$laTipo)
		$laRetorna['TIPOS'][$lcTipo] = $lcTipoDsc=='T' ? $laTipo['ABRV'].' - '.$laTipo['NOMBRE'] : 
									(  $lcTipoDsc=='D' ? $laTipo['NOMBRE'] : $laTipo['ABRV'] ) ;

	unset($laTipos);

}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
