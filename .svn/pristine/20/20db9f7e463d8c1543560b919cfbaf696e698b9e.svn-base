<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion= $_REQUEST['accion']??'';
	$lcTipoMedicos = $_POST['tiposmedicos'] ?? '';
	$lcRegistroMedico = $_POST['registromedico'] ?? '';
	$lnNumeroIngreso = $_POST['lnNumeroIngreso'] ?? 0;

	switch ($lcAccion) {
		case 'consultarListados':
			require_once (__DIR__ .'/../../../controlador/class.Medicos.php');	
			$loMedicos = new NUCLEO\Medicos;

			require_once (__DIR__ .'/../../../controlador/class.Diagnostico.php');	
			$loDiagnostico = new NUCLEO\Diagnostico;
	
			require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
			$loParametrosConsulta = new NUCLEO\ParametrosConsulta();

			require_once __DIR__ . '/../../../controlador/class.CentrosServicios.php';
			$loCentroServicio = new NUCLEO\CentrosServicios();

			$laRetorna['centrosdeservicio'] = $loCentroServicio->aCentros;
			$loParametrosConsulta->ObtenerTipoCausa('', '','');
			$laRetorna['causasexternas']=$loParametrosConsulta->TiposCausa();
			$loParametrosConsulta->ObtenerFinalidad('P','');
			$laRetorna['finalidades']= $loParametrosConsulta->Finalidades();
			$laRetorna['tipodiagnostico']= $loDiagnostico->ClaseDiagnostico();
			$laRetorna['listadomedicos']= $loMedicos->buscarListaMedicos('',$lcTipoMedicos,false);
		break;

		case 'consultaringreso':
			require_once (__DIR__ .'/../../../controlador/class.Ingreso.php');	
			$loIngreso = new NUCLEO\Ingreso;
			$loIngreso->cargarIngreso($_POST['numeroingreso']);

		 	$laRetorna['DATOS'] = [
				'nombrepaciente' => $loIngreso->oPaciente->getNombreCompleto(),
				'tipoidentificacion'=>$loIngreso->oPaciente->aTipoId['TIPO'],
				'numeroidentificacion'=>$loIngreso->oPaciente->nId, 
				'generopaciente'=>$loIngreso->oPaciente->cSexo,
				'descripciongeneropaciente'=>$loIngreso->oPaciente->cDescSexo,
				'habitacionpaciente'=>$loIngreso->oHabitacion->cSeccion."-".$loIngreso->oHabitacion->cHabitacion,
				'codigoviaingreso'=>$loIngreso->cVia, 
				'descripcionviaingreso'=>$loIngreso->cDescVia, 
				'edadpaciente'=>($loIngreso->aEdad['y']." años, ".$loIngreso->aEdad['m']." meses, ".$loIngreso->aEdad['d']." días"),
				'estadoingreso'=>$loIngreso->cEstado, 
				'descripcionestadoingreso'=>$loIngreso->cDescripcioEstadoIngreso, 
				'edadpaciente'=>$loIngreso->aEdad, 
				'centrodeservicio'=>$loIngreso->cCentroDeServicio, 
			];
		break;	

		case 'cargarparametrosiniciales':
			require_once (__DIR__ .'/../../../controlador/class.GrabarProcedimientos.php');	
			$loGrabarProcedimiento = new NUCLEO\GrabarProcedimientos;
			$laRetorna['DATOS'] = $loGrabarProcedimiento->crearParametrosIniciales();
		break;	

		case 'consultarEspecialidades':
			require_once (__DIR__ .'/../../../controlador/class.Medico.php');	
			$loMedico = new NUCLEO\Medico;
			$laRetorna['DATOS']= $loMedico->consultarEspecialidadesPorMedico($lcRegistroMedico,$lcTipoMedicos);
		break;	

		case 'obtenerDiagnosticoPrincipal':
			require_once (__DIR__ .'/../../../controlador/class.Diagnostico.php');	
			$loDiagnostico = new NUCLEO\Diagnostico;
			$laRetorna['DATOS']= $loDiagnostico->consultarDiagnosticoPrincipal($lnNumeroIngreso);
		break;	

		case 'Verificar':
			require_once (__DIR__ .'/../../../controlador/class.GrabarProcedimientos.php');	
			$loGrabarProcedimiento = new NUCLEO\GrabarProcedimientos;
		
			$laDatos = $_POST['datos'];
			$laRetorna = $loGrabarProcedimiento->verificarDatosProcedimientos($laDatos);
			
			if($laRetorna['Valido']){
				$laRetorna = $loGrabarProcedimiento->guardarProcedimientos($laDatos);
			}
		break;

		case 'imprimir':
			require_once (__DIR__ .'/../../../controlador/class.Doc_GrabarCups.php');	
			$loImpresion = new NUCLEO\Doc_GrabarCups;
			$laDatosImprimir = $_POST['datosimprimir']??'';
			$loImpresion->obtenerDocumento($laDatosImprimir);
			exit();
		break;	

	}
}
include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
