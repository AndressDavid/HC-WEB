<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.NivelesConciencia.php';
	$laTipos = (new NUCLEO\NivelesConciencia())->aNiveles;
	$laRetorna['TIPOS']=[];

	foreach($laTipos as $lcTipo=>$laTipo)
		$laRetorna['TIPOS'][$lcTipo] = $laTipo['NOMBRE'] ;

	unset($laTipos);

}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
