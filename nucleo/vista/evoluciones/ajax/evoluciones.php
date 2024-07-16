<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	$lcTipoC = $_POST['lcTipo'] ;

	if (in_array($lcTipoC, ['Reglas', 'Label'])) {
		require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
		$loObjEV = new NUCLEO\ParametrosConsulta() ;
		$lcTituloC = $_POST['lcTitulo'] ;
		$loObjEV->ObjetosObligatoriosEV($lcTituloC);
		$laObjetos = $loObjEV->ObjObligatoriosEV() ;
	}

	require_once __DIR__ . '/../../../controlador/class.Evoluciones.php';
	$loObjEV = new NUCLEO\Evoluciones() ;

	switch($lcTipoC)
	{
		case 'NoVisible':
			require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
			$loObjEV = new NUCLEO\ParametrosConsulta() ;
			$loObjEV->ObjetosNoVisiblesEV();
			$laObjetos = $loObjEV->ObjNoVisiblesEV() ;
			$laRetorna['NOVISIBLES'] = $laObjetos;
			break;

		case 'consultartextofallece':
			$laRetorna['TIPOS']=$loObjEV->consultatextofallece() ;
			break;
			
		case 'datosreconocimiento':
			$laRetorna['TIPOS']=$loObjEV->consultaReconocimiento() ;
			break;
		
		
		case 'Reglas':
			$laRetorna['REGLAS'] = $laObjetos;
			break;
			
		case 'Verificar':
			//$laDatos = $_POST['datos'];
			$laDatos = json_decode($_POST['datos'], true);
			$laRetorna = $loObjEV->verificarEvolucion($laDatos);
			if($laRetorna['Valido']){
				$laRetorna['dataEV'] = $loObjEV->GuardarEV($laDatos);
			}
			break;
		
		case 'AntecedentesUCI':
			$lnIngreso = $_POST['Ingreso'] ;
			$laRetorna['Antecedentes'] = $loObjEV->ConsultarAntecedentesUCI($lnIngreso);
			break;
		
		case 'verificarOH':
				$lnIngreso = $_POST['Ingreso'] ;
				$laRetorna['Antecedentes'] = $loObjEV->ConsultarAntecedentesUCI($lnIngreso);
				break;

	}
	unset($laObjetos);
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
