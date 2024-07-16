<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion= (isset($_GET['accion'])?$_GET['accion']:(isset($_POST['accion'])?$_POST['accion']:''));
	$laDatos= $_POST['datos'] ?? '';
	$laProcedimientos= $_POST['laProcedimientos'] ?? '';
	$laMedicamentos= $_POST['laMedicamentos'] ?? '';
	$lcTipoIde= $_POST['tipdoc'] ?? '';
	$lnNroIngreso= $_POST['lnIngreso'] ?? 0;
	$lnIdentificacion= $_POST['numdoc'] ?? 0;
	$lnCantidad= $_POST['lnCantidad'] ?? 0;
	$lnValorDosis= $_POST['lnValorDosis'] ?? 0;
	$lnDiasAntibiotico= $_POST['lnDiasAntibiotico'] ?? 0;
	$lnFechaFormulacion= $_POST['lnFechaFormula'] ?? 0;
	$lcEspecialidad= $_POST['lcEspecialidad'] ?? '';
	$lcViaIngreso= $_POST['lcViaIngreso'] ?? '';
	$lcSeccion= $_POST['lcSeccion'] ?? '';
	$lcProcedimiento= $_POST['lcProcedimiento'] ?? '';
	$lcMedicamento= $_POST['lcMedicamento'] ?? '';
	$lcEspHemocomponente= $_POST['lcEspHemocomponente'] ?? '';
	$lcCodigoPlan= $_POST['lcCodigoPlan']  ?? '';
	$lcMedicamentoActual= $_POST['lcMedActual'] ?? '';
	$lnConsecutivoFormula= $_POST['lnConsecFormula'] ?? '';
	$lcMedicamentoCambiar= $_POST['lcMedCambiar'] ?? '';
	$lcViaaCambiar= $_POST['lcViaCambiar'] ?? '';
	
	require_once __DIR__ . '/../../../controlador/class.AplicacionFunciones.php';
	require_once __DIR__ . '/../../../controlador/class.OrdenesMedicas.php';
	$loOrdenesMedicas = new NUCLEO\OrdenesMedicas;
	
	require_once __DIR__ . '/../../../controlador/class.DatosAmbulatorios.php';
	$loDatosAmbulatorios = new NUCLEO\DatosAmbulatorios;
	
	require_once __DIR__ . '/../../../controlador/class.Procedimientos.php';
	$loProcedimientos = new NUCLEO\Procedimientos;
	
	require_once __DIR__ . '/../../../controlador/class.Diagnostico.php';
	$loDiagnostico = new NUCLEO\Diagnostico;
	
	require_once __DIR__ . '/../../../controlador/class.NoPosFunciones.php';
	$loNopos = new NUCLEO\NoPosFunciones() ;
	
	require_once __DIR__ . '/../../../controlador/class.MedicamentoFormula.php';
	$loMedicamentos = new NUCLEO\MedicamentoFormula();
	
	require_once __DIR__ . '/../../../controlador/class.FormulacionParametros.php';
	$loFormulacionParametros = new NUCLEO\FormulacionParametros();
	
	require_once __DIR__ . '/../../../controlador/class.Ingreso.php';
	$loIngreso = new NUCLEO\Ingreso();
	
	require_once __DIR__ . '/../../../controlador/class.AntecedentesConsulta.php';
	$loAntecedente = new NUCLEO\AntecedentesConsulta();
	
	switch ($lcAccion) {
		case 'ultimoAntecedente':
			$laRetorna['TIPOS']=$loAntecedente->ultimoAntecedenteIngreso($lcTipoIde,$lnIdentificacion,$lnNroIngreso) ;
			break;
			
		case 'tablaMetodoOxigeno':
			$laRetorna['TIPOS']=$loOrdenesMedicas->listaMetodosOxigeno() ;
			break;
			
		case 'tablaMetodoGlucometria':
			$laRetorna['TIPOS']=$loOrdenesMedicas->listaMetodosGlucometria() ;
			break;
			
		case 'consultaGlucometriasDiarias':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaGlucometriaDia($lnNroIngreso) ;
			break;
		
		case 'consultaCupsGlucometria':
			$laRetorna['TIPOS']=$loOrdenesMedicas->listaCupsGlucometria('GLUCORD') ;
			break;
			
		case 'tabladieta':
			$laRetorna['TIPOS']=$loDatosAmbulatorios->TablaDietas() ;
			break;

		case 'tablaInterconsultas':
			$loDatosAmbulatorios->TablaInterconsultas('I');
			$laRetorna['TIPOS']=$loDatosAmbulatorios->ListadoInterconsultas() ;
			break;
		
		case 'prioridadInterconsultas':
			$laRetorna['TIPOS']=$loOrdenesMedicas->TablaPrioridadInterconsultas() ;
			break;
		
		case 'especFisioterapia':
			$laRetorna['TIPOS']=$loOrdenesMedicas->cupsInterconsultaFisioterapia() ;
			break;
		
		case 'consInterSisResponder':
			$laRetorna['TIPOS']=$loOrdenesMedicas->interconsultasSinResponder($lnNroIngreso,$lcEspecialidad) ;
			break;
		
		case 'consUltForOxigeno':
			$laRetorna['TIPOS']=$loOrdenesMedicas->ultimaFormulaOxigeno($lnNroIngreso) ;
			break;
			
		case 'consultarCobroOxigeno':
			$laRetorna['TIPOS']=$loOrdenesMedicas->cobrarOxigeno($lcViaIngreso,$lcSeccion) ;
			break;
		
		case 'parametroentidad':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaParametrosEntidad($lnNroIngreso,$lcCodigoPlan) ;
			break;
			
		case 'parametrosCups':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaParametrosCups() ;
			break;
			
		case 'parametrosCantidades':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaParametrosCantidades($lcEspecialidad) ;
			break;
			
		case 'planDeManejo':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaPlanManejo($lnNroIngreso) ;
			break;	

		case 'pacienteUrgencias':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaPacienteUrgencias($lcViaIngreso,$lcSeccion) ;
			break;
			
		case 'consultaCupsUrgencias':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaProcedimientoUrgencias($lcProcedimiento) ;
			break;
			
		case 'consultarEspecialidadPediatria':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaEspecialidadPediatria($lcProcedimiento) ;
			break;
			
		case 'consultarCupsPos':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaProcedimientoPos($lcProcedimiento) ;
			break;
		
		case 'consultarHemocomponente':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaHemocomponentes($lcProcedimiento,$lcEspHemocomponente) ;
			break;
			
		case 'consultaCupsNoInvasivos':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaNuclearNoInvasiva($lcProcedimiento) ;
			break;
		
		case 'validaPlan':
			$laRetorna['TIPOS']=$loNopos->entidadMipres($lcCodigoPlan);
			break;

		case 'justificacionMipres':
			$laRetorna['TIPOS']=$loOrdenesMedicas->justificarmipres($lnNroIngreso,$laProcedimientos,$laMedicamentos);
			break;
			
		case 'datosEnfermeria':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaDatosEnfermeria($lnNroIngreso);
			break;
			
		case 'parametrosIngreso':
			$laRetorna['TIPOS']=$loMedicamentos->consultarParametrosIniciales();
			break;
			
		case 'consultaDosisMedicamento':
			$laRetorna['TIPOS']=$loMedicamentos->consultaListaDosis($lcMedicamento);
			break;
			
		case 'consultaViaAdministracionMedicamento':
			$laRetorna['TIPOS']=$loMedicamentos->consultaListaViaAdministracion($lcMedicamento);
			break;
			
		case 'consultaParametrosMedicamento':
			$laRetorna['TIPOS']=$loMedicamentos->consultarParametrosMedicamento($lcMedicamento);
			break;
			
		case 'consultaMezclaMedicamento':
			$laRetorna['TIPOS']=$loMedicamentos->consultarMezclaMedicamento($lcMedicamento,$lnValorDosis);
			break;
			
		case 'ultimaFormulaMedicamentos':
			$laRetorna['TIPOS']=$loMedicamentos->consultaUltimaFormulaIngreso($lnNroIngreso);
			break;

		case 'consultaCambioMedicamento':
			$laRetorna['TIPOS']=$loMedicamentos->consultarMedicamentoCambio($lnNroIngreso,$lcMedicamentoActual,$lnConsecutivoFormula,$lcMedicamentoCambiar,$lcViaaCambiar);
			break;		
		
		case 'verificausoantibiotico':
			$laRetorna['TIPOS']=$loMedicamentos->consultarUsoAntibiotico($lnNroIngreso,$lcMedicamento,$lnDiasAntibiotico);
			break;			
		
		case 'consultaListaFrecuencias':
			$loFormulacionParametros->obtenerFrecuencias();
			$laRetorna['TIPOS']=$loFormulacionParametros->frecuencias();
			break;

		case 'verificarAlertaAntibioticos':
			$laRetorna['TIPOS']=$loMedicamentos->consultarAlertaAntibiotico($lnNroIngreso,$lcMedicamento,$lnFechaFormulacion,$lnDiasAntibiotico);
			break;
			
		case 'verificarEstadoIngreso':
			$laRetorna['TIPOS']=$loIngreso->consultaEstadoIngreso($lnNroIngreso);
			break;		
			
		case 'Verificar':
			$laDatos = json_decode($laDatos, true);
			$laRetorna = $loOrdenesMedicas->verificarOM($laDatos);
			if($laRetorna['Valido']){
				$laRetorna = $loOrdenesMedicas->GuardarOrdenesMedicas($laDatos);
			} 
			break;
	}
}
include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
