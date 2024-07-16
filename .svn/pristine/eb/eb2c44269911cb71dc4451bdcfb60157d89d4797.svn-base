<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lnNroIngreso= $_POST['ingreso'] ?? 0;
	$lnTiporegistro= $_POST['tiporegistro'] ?? 0;
	$laDatos= $_POST['datos'] ?? '';

	require_once __DIR__ . '/../../../controlador/class.ConsultaUrgencias.php';
	$loConsultaUrgencias = new NUCLEO\ConsultaUrgencias;
	require_once (__DIR__ .'/../../../controlador/class.SeccionesHabitacion.php');
	$loSeccionesHabitacion = new NUCLEO\SeccionesHabitacion();
	
	require_once (__DIR__ .'/../../../controlador/class.Paciente.php');
	$loPaciente = new NUCLEO\Paciente();
	
	require_once (__DIR__ .'/../../../controlador/class.Bitacoras.php');

	// Acción a ejecutar
	$lcAccion = $_POST['accion'] ?? '';
	switch ($lcAccion) {

		case 'pacientes':
			$laRetorna['datos'] = (new NUCLEO\ConsultaUrgencias())
				->consultaCensoUrgencias(
					str_replace('-','',$_POST['fechaini']),
					str_replace('-','',$_POST['fechafin']),
					$_POST['ingreso'],
					$_POST['seccion'],
					$_POST['tipocenso'],
					$_POST['ubicacionmed'],
					$_POST['sinhabitacion'],
					$_POST['generopac'],
					$_POST['pacientetipo']
				);
			$ltAhora = new \DateTime($goDb->fechaHoraSistema());
			$laRetorna['fechahora'] = str_replace(' ','T',$ltAhora->format('Y-m-d H:i:s'));
			break;
		
		case 'SeccionesUrgencias':
			$loSeccionesHabitacion->consultaSeccionesPrmtabHabilitadas('URGENCIAS');
			$laRetorna['datos'] = $loSeccionesHabitacion->aSecciones;
			break;
			
		case 'SeccionesHospitalizados':
			$loSeccionesHabitacion->consultaSeccionesHospitalizados();
			$laRetorna['datos'] = $loSeccionesHabitacion->aSecciones;
			break;

		case 'SeccionesUrgenciasMedicos':
			$laRetorna['datos']=$loSeccionesHabitacion->consultaSeccionesMedicos() ;
			break;

		case 'procesosalidacenso':
			$laRetorna['datos']=$loConsultaUrgencias->excluirSalidaCenso($laDatos) ;
			break;
			
		case 'consultaparametros':
			$laRetorna['datos']=$loConsultaUrgencias->consultarparametros() ;
			break;

		case 'consultarregistrocenso':
			$laRetorna['datos']=$loConsultaUrgencias->consultaRegistroCenso($laDatos) ;
			break;

		case 'consultaListaAltasTempranas':
			$laRetorna['datos']=$loConsultaUrgencias->consultaAltasTempranas($laDatos) ;
			break;

		case 'guardaAltasTempranas':
			$laRetorna['datos']=$loConsultaUrgencias->registrarInformacion($laDatos) ;
			break;

		case 'consultarDatosAltasTempranas':
			$laRetorna['datos']=$loConsultaUrgencias->consultaDatosAltasTempranas($laDatos) ;
			break;

		case 'consultarUsuariosRegistros':
			$laRetorna['datos']=$loConsultaUrgencias->consultarAutorizacionUsuarios() ;
			break;

		case 'consultarEstados':
			$laRetorna['datos']=$loConsultaUrgencias->estadosCenso($laDatos) ;
			break;
		case 'consultaAutorizacionUsuarioEspecifico':
			$laRetorna['datos']=$loConsultaUrgencias->consultaAutorizacionUsuarioEspecifico($laDatos);
			break;	
		case 'guardaBitacora':
			$laRetorna['datos']=$loConsultaUrgencias->crearBitacora($laDatos) ;
			break;

		case 'procesosCambiarUbicacion':
			$laRetorna['datos']=$loConsultaUrgencias->cambiarUbicacionPaciente($laDatos) ;
			break;
			
		case 'Verificar':
			$laRetornar = $loConsultaUrgencias->verificarDatos($laDatos);

			if($laRetornar['Valido']){
				$laRetorna['datos']=$loConsultaUrgencias->registrarInformacion($laDatos) ;
			} 
			break;

	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
