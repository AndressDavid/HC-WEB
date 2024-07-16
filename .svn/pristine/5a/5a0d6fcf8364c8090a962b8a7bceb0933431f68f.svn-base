<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../controlador/class.AplicacionFunciones.php');
	require_once (__DIR__ .'/../../../controlador/class.EstadosCiviles.php');
	require_once (__DIR__ .'/../../../controlador/class.Ingreso.php');
	require_once (__DIR__ .'/../../../controlador/class.Medicos.php');
	require_once (__DIR__ .'/../../../controlador/class.NivelesEducativos.php');
	require_once (__DIR__ .'/../../../controlador/class.Paciente.php');
	require_once (__DIR__ .'/../../../controlador/class.Parentescos.php');
	require_once (__DIR__ .'/../../../controlador/class.PertenenciasEtnicas.php');
	require_once (__DIR__ .'/../../../controlador/class.Plan.php');
	require_once (__DIR__ .'/../../../controlador/class.Planes.php');	
	require_once (__DIR__ .'/../../../controlador/class.SeccionesHabitacion.php');
	require_once (__DIR__ .'/../../../controlador/class.TiposDocumento.php');
	require_once (__DIR__ .'/../../../controlador/class.Trabajos.php');
	require_once (__DIR__ .'/../../../controlador/class.Triage.php');
	require_once (__DIR__ .'/../../../controlador/class.UbicacionesGeograficas.php');

	$lcTipoIngreso = (isset($_GET['q'])?$_GET['q']:'DEFAULT');
	$lcAccion = (isset($_GET['accion'])?$_GET['accion']:(isset($_POST['accion'])?$_POST['accion']:''));
	$laTabla=array();

	switch($lcAccion){
		case 'medicosTratantes':
			$lvNombre = trim(strval(isset($_GET['nombre'])?$_GET['nombre']:(isset($_POST['nombre'])?$_POST['nombre']:'')));
			if(!empty($lvNombre)){
				$lvNombre = NUCLEO\AplicacionFunciones::fnSanitizar($lvNombre);
				$lvNombre = explode(' ',$lvNombre);
				$laTabla = (new NUCLEO\Medicos())->buscarListaMedicosNombreRegistro($lvNombre,'','*','1, 3, 4, 6, 10, 11, 12, 13, 91');
			}
			break;
		
		case 'tiposDocumentos':
			foreach(((new NUCLEO\TiposDocumento())->aTipos) as $lcTipo=>$laTipo){
				$laTabla[] = ['CODIGO'=>$lcTipo, 'NOMBRE'=>$laTipo['NOMBRE']];
			}
			break;

		case 'SeccionesHabitacion':
			$loSeccionesHabitacion = new NUCLEO\SeccionesHabitacion();
			$loSeccionesHabitacion->consultaSeccionesPrmtabHabilitadas();
			$laTabla = $loSeccionesHabitacion->aSecciones;
			break;

		case 'habitacionDisponible':
			$lcSeccion = trim(strval(isset($_GET['seccion'])?$_GET['seccion']:(isset($_POST['seccion'])?$_POST['seccion']:'')));
			$laTabla = (new NUCLEO\SeccionesHabitacion())->habitacionesSeccionEstado($lcSeccion);
			break;

		case 'obtenerMedicoTratante':
			$lnIngreso = intval(isset($_GET['ingreso'])?$_GET['ingreso']:(isset($_POST['ingreso'])?$_POST['ingreso']:0));
			$laTabla = (new NUCLEO\Ingreso())->obtenerMedicoTratante($lnIngreso);
			break;

		case 'triageListaClasificaciones':
			$laTabla = (new NUCLEO\Triage())->listaClasificacionesTriage();
			break;

		case 'triageClasificacion':
			$lnIngreso = intval(isset($_GET['ingreso'])?$_GET['ingreso']:(isset($_POST['ingreso'])?$_POST['ingreso']:0));
			$laTabla = (new NUCLEO\Triage())->obtenerTriageClasificacion($lnIngreso);
			break;

		case 'triageEnfermedad':
			$lnIngreso = intval(isset($_GET['ingreso'])?$_GET['ingreso']:(isset($_POST['ingreso'])?$_POST['ingreso']:0));
			$laTabla = (new NUCLEO\Triage())->obtenerTriageEnfermedad($lnIngreso);
			break;
			
		case 'planes':
			$lvNombre = trim(strval(isset($_GET['nombre'])?$_GET['nombre']:(isset($_POST['nombre'])?$_POST['nombre']:'')));
			if(!empty($lvNombre)){
				$lvNombre = NUCLEO\AplicacionFunciones::fnSanitizar($lvNombre);
				$lvNombre = explode(' ',$lvNombre);
				$laTabla = (new NUCLEO\Planes())->buscarListaPlanesNombreCodigo($lvNombre);
			}			
			break;

		case 'plan':
			$lcPan = (isset($_GET['plan'])?$_GET['plan']:(isset($_POST['plan'])?$_POST['plan']:''));
			$laTabla = (new NUCLEO\Plan($lcPan))->getPlan();
			break;

		case 'estadosCiviles':
			$laTabla = (new NUCLEO\EstadosCiviles())->getEstados();
			break;

		case 'pertenenciasEtnicas':
			$laTabla = (new NUCLEO\PertenenciasEtnicas())->getPertenencias();
			break;

		case 'nivelesEducativos':
			$laTabla = (new NUCLEO\NivelesEducativos())->getNiveles();
			break;

		case 'trabajos':
			$laTabla = (new NUCLEO\Trabajos())->getTrabajos();
			break;

		case 'parentescos':
			$laTabla = (new NUCLEO\Parentescos())->getParentescos();
			break;

		case 'paises':
			$laTabla = (new NUCLEO\UbicacionesGeograficas())->getPaises();
			break;

		case 'departamentos':
			$lcPais = (isset($_GET['pais'])?$_GET['pais']:(isset($_POST['pais'])?$_POST['pais']:''));
			$laTabla = (new NUCLEO\UbicacionesGeograficas())->getDepartamentos($lcPais);
			break;

		case 'ciudades':
			$lcPais = (isset($_GET['pais'])?$_GET['pais']:(isset($_POST['pais'])?$_POST['pais']:''));
			$lcDepartamento = (isset($_GET['departamento'])?$_GET['departamento']:(isset($_POST['departamento'])?$_POST['departamento']:''));
			$laTabla = (new NUCLEO\UbicacionesGeograficas())->getCiudades($lcPais, $lcDepartamento);
			break;

		case 'localidades':
			$laTabla = (new NUCLEO\UbicacionesGeograficas())->getLocalidades();
			break;

		case 'paciente':
			$lcDocumentoTipo = (isset($_GET['tipo'])?$_GET['tipo']:(isset($_POST['tipo'])?$_POST['tipo']:''));
			$lnDocumentoNumero = intval(isset($_GET['numero'])?$_GET['numero']:(isset($_POST['numero'])?$_POST['numero']:0));
			$lnIngreso = intval(isset($_GET['ingreso'])?$_GET['ingreso']:(isset($_POST['ingreso'])?$_POST['ingreso']:0));
			$laTabla = (new NUCLEO\Paciente())->validarDocumento($lcDocumentoTipo, $lnDocumentoNumero, true, true, true, $lnIngreso);
			break;
			
		case 'guardar':
			$laIngreso = (isset($_GET['ingreso'])?$_GET['ingreso']:(isset($_POST['ingreso'])?$_POST['ingreso']:array()));
			$laPlanes = (isset($_GET['planes'])?$_GET['planes']:(isset($_POST['planes'])?$_POST['planes']:array()));
			$laTabla = (new NUCLEO\Ingreso())->guadar($laIngreso, $laPlanes);
			break;			

	}


	include (__DIR__ .'/../../../publico/headJSON.php');
	print(json_encode($laTabla??''));
?>
