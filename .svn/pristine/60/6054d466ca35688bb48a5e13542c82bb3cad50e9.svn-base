<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	$lcTipoC = $_POST['lcTipo'] ;

	if (in_array($lcTipoC, ['Reglas', 'Label'])) {
	}

	switch($lcTipoC)
	{
		case 'Reglas':
			require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
			$loObjEPI = new NUCLEO\ParametrosConsulta();
			$lcTituloC = $_POST['lcTitulo'];
			$loObjEPI->ObjetosObligatoriosEPI($lcTituloC);
			$laRetorna = $loObjEPI->ObjObligatoriosEPI();
			break;

		case 'verificaPlanPaciente':
			require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
			$loParCon = new NUCLEO\ParametrosConsulta();
			$laRetorna['datos']=$loParCon->fMostrarPlanesPaciente('PAREPWEB');
			break;

		case 'Verificar':
			$laDatos = $_POST['toData'];
			require_once __DIR__ . '/../../../controlador/class.Epicrisis.php';
			$loObjEPI = new NUCLEO\Epicrisis();
			$laRetorna = $loObjEPI->verificarEPI($laDatos);
			if($laRetorna['Valido']){
				$laRetorna = $loObjEPI->GuardarEPI($laDatos);
				$laRetorna['habGuardar']=false;
			}else{
				$laRetorna['habGuardar']=true;
			}
			break;

		case 'rutaruaf':
			$laRetorna['URL'] = trim($goDb->obtenerTabMae1('DE2TMA', 'RUAF', "cl1tma='URL' AND esttma=''", 'cl2tma DESC', ''));
			break;

		default:
			$laRetorna['TIPOS']=$laTipo;

	}
	unset($laObjetos);
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
