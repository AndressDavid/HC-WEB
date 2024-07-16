<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	$lcTituloC = $_POST['lcTitulo'] ;
	$lcTipoC = $_POST['lcTipo'] ;
	require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
	$loObjHC = new NUCLEO\ParametrosConsulta() ;
	$loObjHC->ObjetosObligatoriosHC();
	$laObjetos = $loObjHC->ObjObligatoriosHC() ;
	
	switch($lcTipoC)
	{
		case '01' :
			$lcRetorna = "{ " ;
			foreach($laObjetos as $lcTipo=>$laTipo){
				if($laTipo['TIPO']==$lcTituloC){
					$lcRetorna .= $laTipo['REGLAS']." " ;
				}
			}
			$lcRetorna .= "} " ;
			break;
			
		case '02' :
			$laRetorna['OBJETOS']=[];
			foreach($laObjetos as $lcTipo=>$laTipo){
				$laRetorna['OBJETOS'][$lcTipo] = $laTipo['OBJETO'] ;
			}
			$lcRetorna = json_encode($laRetorna);
			break;
	}
	unset($laObjetos);
}

include __DIR__ .'/../../../publico/headJSON.php';
echo ($lcRetorna);
