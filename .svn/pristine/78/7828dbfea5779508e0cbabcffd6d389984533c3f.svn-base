<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	$lcTipoC = $_POST['lcTipo'];

	switch($lcTipoC)
	{
		case 'Antecedente':
			$lcTipDocPac = $_POST['lcTipDocPac']??'';
			$lnNumDocPac = $_POST['lnNumDocPac']??0;
			require_once __DIR__ . '/../../../controlador/class.AntecedentesConsulta.php';
			$laRetorna['DATOS'] = (new NUCLEO\AntecedentesConsulta())->ultimoAntecedente($lcTipDocPac, $lnNumDocPac);
			break;

		case 'ParVacCov19':
			require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
			$laRetorna['datos'] = (new NUCLEO\ParametrosConsulta())->parVacunaCovid19();
			break;
			
		case 'rangospesoreciennacido':
			require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
			$laRetorna['datos'] = (new NUCLEO\ParametrosConsulta())->rangopesoreciennacido();
			break;

		case 'NoVisible':
			require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
			$loParCon = new NUCLEO\ParametrosConsulta();
			$loParCon->ObjetosNoVisibles();
			$laObjetos = $loParCon->ObjNoVisibles();
			$laRetorna['NOVISIBLES'] = $laObjetos;
			break;

		case 'Reglas':
			require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
			$loParCon = new NUCLEO\ParametrosConsulta();
			$loParCon->ObjetosObligatoriosHC($_POST['lcTitulo']??'');
			$laRetorna['REGLAS'] = $loParCon->ObjObligatoriosHC();
			break;

		case 'finalidad':
			$lcTipoFinalidad = $_POST['tipofin']??'';
			$laDatosPaciente = $_POST['laPacienteDatos'];
			require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
			$loParCon = new NUCLEO\ParametrosConsulta();
			$loParCon->ObtenerFinalidad($lcTipoFinalidad,$laDatosPaciente);
			$laFinalidades = $loParCon->Finalidades();
			$laRetorna['finalidades'] = [];
			foreach($laFinalidades as $lcCodigo=>$laFinalidad) {
				$laRetorna['finalidades'][$lcCodigo] = $laFinalidad['desc'];
			}
			break;

		case 'verificaPlanPaciente':
			require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
			$loParCon = new NUCLEO\ParametrosConsulta();
			$laRetorna['datos']=$loParCon->fMostrarPlanesPaciente('PARHCWEB');
			break;
			
		case 'listadoViasCenso':
			require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
			$loParCon = new NUCLEO\ParametrosConsulta();
			$laRetorna['datos']=$loParCon->consultarViasCenso();
			break;
			
		case 'listadoSeccionesHd':
			require_once __DIR__ . '/../../../controlador/class.Historia_Clinica.php';
			$loObjHCD = new NUCLEO\Historia_Clinica();
			$laRetorna['datos']=$loObjHCD->consultaSeccionesHd();
			break;

		case 'Verificar':
			$laDatos = $_POST['datos'];
			require_once __DIR__ . '/../../../controlador/class.Historia_Clinica.php';
			$loObjHC = new NUCLEO\Historia_Clinica();
			$laRetorna = $loObjHC->verificarHC($laDatos);
			if($laRetorna['Valido']){
				$laRetorna = $loObjHC->GuardarHC($laDatos);
				$laRetorna['habGuardar']=false;
			}else{
				$laRetorna['habGuardar']=true;
			}
			break;
	}
	unset($laObjetos);
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
