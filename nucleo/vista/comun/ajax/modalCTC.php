<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	$lcTipoCTC = $_POST['tipoDato'] ;

	switch($lcTipoCTC)
	{
		case 'Medicamento':
			$lcCodigoMed = $_POST['Codigo'] ;
			require_once __DIR__ . '/../../../controlador/class.FormulacionParametros.php';
			$loObjCTC = new NUCLEO\FormulacionParametros() ;
			$laRetorna['DATOS'] = $loObjCTC->DatosMedicamentoNOPOS($lcCodigoMed);
			break;

		case 'MedicaNOPOS':
			require_once __DIR__ . '/../../../controlador/class.FormulacionParametros.php';
			$loObjCTC = new NUCLEO\FormulacionParametros() ;
			$laRetorna['DATOS'] = $loObjCTC->ListaMedicamentosNOPOS();
			break;

		case 'ubicacionProcCTC':
			require_once __DIR__ . '/../../../controlador/class.Procedimientos.php';
			$loAmbulatorios = new NUCLEO\Procedimientos() ;
			$loAmbulatorios->obtenerUbicacion();
			$laTipos = $loAmbulatorios->getUbicaciones();
			foreach($laTipos as $lcTipo=>$laTipo)
				$laRetorna['TIPOS'][$lcTipo] = $laTipo['desc'] ;
			break;

		case 'objetivosProcCTC':
			require_once __DIR__ . '/../../../controlador/class.Procedimientos.php';
			$loAmbulatorios = new NUCLEO\Procedimientos() ;
			$loAmbulatorios->obtenerObjetivos();
			$laTipos = $loAmbulatorios->getObjetivos();
			foreach($laTipos as $lcTipo=>$laTipo)
				$laRetorna['TIPOS'][$lcTipo] = $laTipo['desc'] ;
			break;

		case 'TiposRiesgo':
			require_once __DIR__ . '/../../../controlador/class.FormulacionParametros.php';
			$loObjCTC = new NUCLEO\FormulacionParametros() ;
			$laRetorna['DATOS'] = $loObjCTC->ObtenerRiesgoCTC();
			break;

	}
	unset($loObjCTC);
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
