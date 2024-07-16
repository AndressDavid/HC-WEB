<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
	
	$loEstadoSalida = (new NUCLEO\ParametrosConsulta()) ;
	$loEstadoSalida->ObtenerEstadoSalida();
	$laTipos = $loEstadoSalida->EstadosSalida();
	$laRetorna['ESTADOS']=[];

	foreach($laTipos as $lcTipo=>$laTipo)
		{
			$laRetorna['ESTADOS'][$lcTipo] = $laTipo['desc'] ;
		}

	unset($laTipos);

}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
