<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
	
	$lcEstado = $_POST['lcTipoDato'] ;
	$loEstadosSalida = (new NUCLEO\ParametrosConsulta()) ;
	$loEstadosSalida->ObtenerEstadoEpicrisis();
	$laTipos = $loEstadosSalida->EstadosEpicrisis();
	$laRetorna['ESTADOS']=[];

	foreach($laTipos as $lcTipo=>$laTipo)
		if($laTipo['tipo']==$lcEstado){
			$laRetorna['ESTADOS'][$lcTipo] = $laTipo['desc'] ;
		}

	unset($laTipos);

}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
