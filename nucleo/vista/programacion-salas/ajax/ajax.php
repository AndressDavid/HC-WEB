<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion = $_POST['accion'] ?? '';
	$lcCodigoEspecialidad = $_POST['lcCodigoEnviar'] ?? '';

	require_once __DIR__ . '/../../../controlador/class.AgendaSalasCirugia.php';
	$loAgendaSalas = new NUCLEO\AgendaSalasCirugia;

	require_once __DIR__ . '/../../../controlador/class.Paciente.php';
	$loPaciente = new NUCLEO\Paciente;
			
	switch ($lcAccion) {

		case 'listadosPrincipal':
			require_once __DIR__ . '/../../../controlador/class.SalasCirugia.php';
			$laRetorna['salas'] = (new NUCLEO\SalasCirugia())->aSalasCirugia;
			break;

		case 'listados':
			require_once __DIR__ . '/../../../controlador/class.EspecialidadesSalas.php';
			$laRetorna['especialidades'] = (new NUCLEO\EspecialidadesSalas())->aEspecialidades;
			require_once __DIR__ . '/../../../controlador/class.Usuarios.php';
			$laRetorna['anestesiologos'] = (new NUCLEO\Usuarios('ESTRGM=1 AND TPMRGM=6','','NOMMED',0,false))->aUsuarios;
			require_once __DIR__ . '/../../../controlador/class.Planes.php';
			$laRetorna['planes'] = (new NUCLEO\Planes())->aPlanes;
			$laRetorna['origensala'] = $loAgendaSalas->OrigenSalas();
			$laRetorna['tipoprocedimientosala'] = $loAgendaSalas->NaturalezaSalas();
			$laRetorna['lateralidadsala'] = $loAgendaSalas->LateralidadSalas();
			$laRetorna['tipoanestesia'] = $loAgendaSalas->TipoAnestesiaSala();
			$laRetorna['dispositivoscardiaco'] = $loAgendaSalas->DispositivosCardiacaSalas();
			$laRetorna['requerimientosespec'] = $loAgendaSalas->RequerimientosEspeciales();
			$laRetorna['viasacceso'] = $loAgendaSalas->ViasAcceso();
			$laRetorna['equiposespeciales'] = $loAgendaSalas->EquiposEspecialesSala();
			$laRetorna['generospacientes'] = $loPaciente->consultarListaGeneros();
			break;

		case 'tipoCancelacion':
			$laRetorna['datos'] = $loAgendaSalas->TiposCancelacion($_POST['lcTipoCancela']);
			break;

		case 'listaanestesiologos':
			require_once __DIR__ . '/../../../controlador/class.Usuarios.php';
			$laRetorna['anestesiologos'] = (new NUCLEO\Usuarios("ESTRGM=1 AND TPMRGM=6 AND REGMED<>'{$_POST['lcAnestesiologoActual']}'",'','NOMMED',0,false))->aUsuarios;
			break;

		case 'listaespecialidades':
			require_once __DIR__ . '/../../../controlador/class.EspecialidadesSalas.php';
			$laRetorna['especialidades'] = (new NUCLEO\EspecialidadesSalas())->aEspecialidades;
			break;
				
		case 'motivoCancelacion':
			$laRetorna['datos'] = $loAgendaSalas->MotivosCancelacion($_POST['lcTipoCancela'],$_POST['lcTipoMotivo']);
			break;

		case 'actualizarCancelacion':
			$laRetorna['datos'] = $loAgendaSalas->guardarCancelacion($_POST['lnConsecutivo'],$_POST['lcEstadoCancela'],$_POST['lcDescCancelacion']);
			break;

		case 'actualizarAnestesiologo':
			$laRetorna['datos'] = $loAgendaSalas->guardarAnestesiologo($_POST['lnConsecutivo'],$_POST['lcEnviaAnestesiologo']);
			break;

		case 'actualizarCirujano':
			$laRetorna['datos'] = $loAgendaSalas->guardarCirujano($_POST['lnConsecutivo'],$_POST['lcEnviaCirujano'],$_POST['lcEnviaEspecialidad']);
			break;

		case 'reagendarCita':
			$laRetorna['datos'] = $loAgendaSalas->guardarReagendar($_POST['lnConsecutivo'],$_POST['lcDescCancelacion'],$_POST['lcDatos']);
			break;

		case 'Reglas':
			$lcTituloC = $_POST['lcTitulo'];
			$loAgendaSalas->ObjetosObligatoriosSC($lcTituloC);
			$laRetorna['REGLAS'] = $loAgendaSalas->ObjObligatoriosSC();
			break;

		case 'medicosespecialidad':
			require_once __DIR__ . '/../../../controlador/class.OrdenHospitalizacion.php';
			$loPlanManejo = new NUCLEO\OrdenHospitalizacion;
			$laRetorna['MEDICO'] = $loPlanManejo->ObtenerMedicosOrdenHospitaliza($lcCodigoEspecialidad);			
			break;

		case 'registromedicosolicita':
			require_once __DIR__ . '/../../../controlador/class.Usuarios.php';
			$laUsuario = (new NUCLEO\Usuarios("REGMED='{$_POST['lcRegistroMedico']}'",'','NOMMED',0,false))->aUsuarios;
			foreach ($laUsuario as $laUser) {
				$laRetorna['datos'] = [
					'cRegistro'=>$laUser['REGISTRO']??'',
					'cNombre'=>$laUser['NOMBRE']??'',
					'cEspecialidad'=>$lcCodigoEspecialidad, // adiciona la especialidad a los resultados	
				];
				break;
			}
			break;

		case 'paciente':
			$laRetorna['datos'] = $loAgendaSalas->consultaPaciente($_POST['tipoId'],$_POST['numId']);
			break;

		case 'fechahorasistema':
			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lcFecCre = $ltAhora->format('Ymd');
			$lcHorCre = $ltAhora->format('His');
			$laRetorna['datos'] = ['fecha'=>$lcFecCre,'hora'=>$lcHorCre];
			break;

		case 'agendaPaciente':
			$laRetorna['datos'] = $loAgendaSalas->consultaAgendaPaciente($_POST['tipoSala'],$_POST['fechaInicio'],$_POST['fechaFinal']);
			break;
			
		case 'obtieneDiasSolicitud':
			$laRetorna['datos'] = $loAgendaSalas->diasDiferenciaSolicitud();;
			break;

		case 'Validar':
			$laDatos = $_POST['datos'];
			$laRetorna = $loAgendaSalas->verificarSC($laDatos);
			if($laRetorna['Valido']){
				$laRetorna = $loAgendaSalas->GuardarSC($laDatos);
			}
			break;
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
