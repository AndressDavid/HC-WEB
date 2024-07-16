<?php
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {
	require_once __DIR__ . '/../../../controlador/class.Via.php';
	$laTipos = (new NUCLEO\Via())->aVias;
	$laRetorna['TIPOS']=[];

	$laRetorna['TIPOS']=$laTipos;
	unset($laTipos);
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
