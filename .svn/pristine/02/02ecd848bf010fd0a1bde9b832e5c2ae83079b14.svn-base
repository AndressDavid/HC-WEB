<?php
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {
	$lcAccion = $_POST['cAccion'] ;

	require_once __DIR__ . '/../../../controlador/class.DatosAmbulatorios.php';

	switch($lcAccion)
	{
		case 'interconsulta':
			$laTipos = (new NUCLEO\DatosAmbulatorios())->PrioridadInterconsulta();
			foreach($laTipos as $lcTipo=>$laTipo)
				$laRetorna['TIPOS'][$laTipo['CODIGO']] = $laTipo['DESCRIPCION'];
			break;

		case 'tabladieta':
			$laTipos = (new NUCLEO\DatosAmbulatorios())->TablaDietas();
			foreach($laTipos as $lcTipo=>$laTipo)
				$laRetorna['TIPOS'][$laTipo['CODIGO']] = $laTipo['DESCRIPCION'];
			break;

		case 'tablaMotivoIncapacidad':
			$laTipos = (new NUCLEO\DatosAmbulatorios())->TablaMotivoIncapacidad();
			foreach($laTipos as $lcTipo=>$laTipo)
				$laRetorna['TIPOS'][$laTipo['CODIGO']] = $laTipo['DESCRIPCION'];
			break;

		case 'modalidadprest':
			$laRetorna['TIPOS'] = (new NUCLEO\DatosAmbulatorios())->obtenerModalidadesPrestacion();
			break;

		case 'incapacidad':
			$laRetorna['TIPOS'] = (new NUCLEO\DatosAmbulatorios())->obtenerParametrosIncapacidad();
			break;

		case 'valoresMedicamento':
			$lcCodigoSelConsumo = $_POST['lcCodigoConsumo'] ?? [];
			$laRetorna['TIPOS'] = (new NUCLEO\DatosAmbulatorios())->datosMedicamentosSeleccionado($lcCodigoSelConsumo);
			break;

		case 'noposRevisar':
			$lcListaMedicamentos = $_POST['lcListadoMedicamentos'] ?? [];
			$lcListaCups = $_POST['lcListadoCups'] ?? [];
			$laRetorna['TIPOS'] = (new NUCLEO\DatosAmbulatorios())->consultarNopos($lcListaCups,$lcListaMedicamentos);
			break;

		case 'medicamentosAnteriores':
			$lcTipoIdentificacion =  $_POST['lcTipoIden'] ?? '';
			$lcNumeroIdentificacion =  $_POST['lnNroIden'] ?? '';
			$loAmbulatorios = new NUCLEO\DatosAmbulatorios() ;
			$loAmbulatorios->consultaMedicamentosAnteriores($lcTipoIdentificacion,$lcNumeroIdentificacion);
			$laRetorna['TIPOS']=$loAmbulatorios->fnMedicamentosAnteriores();
			break;

		case 'ultimaFormula':
			$lnIngreso =  $_POST['lnNroIngreso'] ?? 0;
			$loAmbulatorios = new NUCLEO\DatosAmbulatorios() ;
			$loAmbulatorios->consultaUltimosMedicamentos($lnIngreso);
			$laRetorna['TIPOS']=$loAmbulatorios->consultarUltimaFormula() ;
			break;

		case 'caracteresObservaciones':
			$loAmbulatorios = new NUCLEO\DatosAmbulatorios() ;
			$laRetorna['TIPOS']=$loAmbulatorios->cantidadCaracteresObservacioneMed();
			break;

		case 'validaPlan':
			$laRetorna['TIPOS']=[];
			if (isset($_POST['lcCodigoPlan'])) {
				$lcCodigoPlan =  $_POST['lcCodigoPlan'] ;
				require_once __DIR__ . '/../../../controlador/class.NoPosFunciones.php';
				$loAmbulatorios = new NUCLEO\NoPosFunciones() ;
				$laRetorna['TIPOS']=$loAmbulatorios->entidadMipres($lcCodigoPlan);
			}
			break;

		case 'incapacidades':
			$laRetorna['DATOS']=[];
			if (isset($_POST['cTipoDoc']) && isset($_POST['nNumDoc'])) {
				$laRetorna['DATOS'] = (new NUCLEO\DatosAmbulatorios())->ConsultaIncapacidades($_POST['cTipoDoc'], $_POST['nNumDoc']);
			}
			break;
	}
}


include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
