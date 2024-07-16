<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	$lcTipo = $_POST['lcTipoDato'] ;
	require_once __DIR__ . '/../../../controlador/class.FormulacionParametros.php';
	$loObjeto = new NUCLEO\FormulacionParametros();
	$laRetorna['TIPOS']=[];

	switch($lcTipo)
	{
		case 'Dosis' :
			$loObjeto->obtenerUnidadesDosis();
			$laTipos = $loObjeto->unidadesDosis();
			foreach($laTipos as $lcTipo=>$laTipo){
				$laRetorna['TIPOS']['0'.$lcTipo] = $laTipo['desc'];
			}
			break;
		
		case 'Frecuencia' :
			$loObjeto->obtenerFrecuencias();
			$laRetorna['TIPOS']=$loObjeto->frecuencias() ;
			break;
			
		case 'Via' :
			$loObjeto->obtenerViasAdmin();
			$laTipos = $loObjeto->viasAdmin();
			$laRetorna['TIPOS']=$laTipos;
			break;

		case 'NoConsume' :
			$loObjeto->obtenerNoConsume();
			$laRetorna['TIPOS']=$loObjeto->NoConsume();
			break;
	}

	unset($laTipos);
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
