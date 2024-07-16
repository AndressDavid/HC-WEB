<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {


	$lnIndiceR = $_POST['lnIndice'] ;
	$lcTipo = $_POST['lcTipoC'] ;
			
	require_once __DIR__ . '/../../../controlador/class.Doc_NIHSS.php';
	$loObjeto = (new NUCLEO\Doc_NIHSS());
	$loObjeto->CargarNihss();
	$laRetorna['TIPOS']=[];
	
	switch($lcTipo)
	{
		case 'R' :
			
			$laRespuestas = $loObjeto->RespuestasNihss();
			foreach($laRespuestas as $lcTipo=>$laTipo){
				$laTipo = array_map('trim',$laTipo);
				if (intval($laTipo['CL2TMA'])==$lnIndiceR)
				{
					$laRetorna['TIPOS'][$laTipo['CL3TMA']] = [ $laTipo['PUNTAJE'], $laTipo['DESCRIP'] ];
				}
			}
			unset($laRespuestas);
			break;
			
		case 'P' :
			$laPregunta = $loObjeto->PreguntaNihss($lnIndiceR);
			$laRetorna['TIPOS']['1']= $laPregunta ;
			unset($laPregunta);
			break;
	
	}

}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
