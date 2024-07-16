<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion= (isset($_GET['accion'])?$_GET['accion']:(isset($_POST['accion'])?$_POST['accion']:''));
	$lnIngreso = $_POST['lnIngreso'] ?? 0;;
	$laDatos= $_POST['datos'] ?? '';
	$lcViaIngreso= $_POST['lcViaIngreso'] ?? '';
	$lcSeccion= $_POST['lcSeccion'] ?? '';
	$lcModulo= $_POST['lcModulo'] ?? '';
	$lcTiposMedicos= $_POST['lcTiposMedicos'] ?? '';
	$lcSignosNews= $_POST['lcSignosNews'] ?? '';

	require_once __DIR__ . '/../../../controlador/class.TrasladosPacientes.php';
	$loTraslados = new NUCLEO\TrasladosPacientes();

	require_once __DIR__ . '/../../../controlador/class.EspecialidadesSalas.php';
	$loEspecialidad = new NUCLEO\EspecialidadesSalas();
	
	require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
	$loConductaSeguir = (new NUCLEO\ParametrosConsulta()) ;

	require_once __DIR__ . '/../../../controlador/class.Medicos.php';
	$loMedicos = new NUCLEO\Medicos();

	require_once __DIR__ . '/../../../controlador/class.SignosNews.php';
	$loSignosNews = new NUCLEO\SignosNews();

	require_once __DIR__ . '/../../../controlador/class.ConsultasEnfermeria.php';
	$loEnfermeria = new NUCLEO\ConsultasEnfermeria();
	
	switch($lcAccion)
	{
			
		case 'consultarAreastrasladar':
			$loConductaSeguir->ObtenerConductaSeguir($lnIngreso,$lcViaIngreso,$lcSeccion,$lcModulo,'C');
			$laTipos = $loConductaSeguir->ConductasSeguir();
			$laRetorna['datos']=$laTipos;
			break;

		case 'consultarListados':
			$laRetorna['especialidades'] = $loEspecialidad->aEspecialidades;
			$laRetorna['medicos'] = $loMedicos->buscarListaMedicos('',$lcTiposMedicos,false) ;
			$laRetorna['caracteresjustificacion'] = $loConductaSeguir->cantidadJustificacionTraslado() ;
			$laRetorna['valormaximosignonews'] = $loConductaSeguir->valorMaximoNews() ;
			$laRetorna['escaladolor'] = $loEnfermeria->paramEnfermeria(14) ;
			break;
			
		case 'consultartraslados':
			$laRetorna['datos']=$loTraslados->consultaRegistrosTraslado($lnIngreso) ;
			break;

		case 'consultarTiposMedicos':
			$laRetorna['datos']=$loConductaSeguir->consultaMedicosTraslados() ;
			break;	
		
		case 'consultaNews':
			$loSignosNews->medir($lcSignosNews,$lnIngreso);
			$laRetorna['datos']=$loSignosNews->getPuntaje();
			break;
			
		case 'Verificar':
			$laDatos = json_decode($laDatos, true);
			$laRetorna = $loTraslados->verificarTraslado($laDatos);
			if($laRetorna['Valido']){
				$laRetorna['datos']=$loTraslados->guardarDatos($laDatos);
			} 
			break;
	}
	unset($loTraslados);
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);

