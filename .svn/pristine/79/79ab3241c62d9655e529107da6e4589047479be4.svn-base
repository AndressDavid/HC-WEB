<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion = $_POST['accion'] ?? '';

	if (in_array($lcAccion, ['ordenes', 'ultimoIngreso', 'Verificar'])) {
		require_once __DIR__ . '/../../../controlador/class.DatosAmbulatorios.php';
		$loOrdenesAmb = new NUCLEO\DatosAmbulatorios;
	}

	if (in_array($lcAccion, ['Reglas', 'Label'])) {
		require_once (__DIR__ .'/../../../controlador/class.ParametrosConsulta.php');
		$loObjHC = new NUCLEO\ParametrosConsulta() ;
		$lcTituloC = $_POST['lcTitulo'] ;
		$loObjHC->ObjetosObligatoriosOA($lcTituloC);
		$laObjetos = $loObjHC->ObjObligatoriosOA() ;
	}

	switch ($lcAccion) {

		case 'Reglas':
			$laRetorna['REGLAS'] = $laObjetos;
			break;

		case 'paciente':
			require_once (__DIR__ .'/../../../controlador/class.Paciente.php');
			$loPaciente = new NUCLEO\Paciente();
			$loPaciente->cargarPaciente($_POST['tipoIde'], $_POST['numIde'],0);
			$laRetorna['nombrePaciente']=$loPaciente->getNombreCompleto();
			break;

		case 'ordenes':
			$loOrdenesAmb->ordenesAmbulatoriasPaciente($_POST['tipoIde'],$_POST['numIde']);
			$laRetorna['datos'] = $loOrdenesAmb->ListadoOrdenesAmbulatoriasPaciente();
			break;

		case 'planesDelPaciente':
			require_once __DIR__ . '/../../../controlador/class.PlanPaciente.php';
			$loPlanesPaciente = new NUCLEO\PlanPaciente;
			$loPlanesPaciente->planesPaciente($_POST['tipoIdentifica'],$_POST['numIdentifica']);
			$laRetorna['datos'] = $loPlanesPaciente->listaPlanesPaciente();
			break;

		case 'ultimoIngreso':
			$loOrdenesAmb->ingresoUltimoAmbulatorio($_POST['tipoIde'],$_POST['numIde']);
			$laRetorna['ingresoamb'] = $loOrdenesAmb->ConsultaUltimoIngresoAmb();
			break;

		case 'listaVias':
			require_once (__DIR__ .'/../../../controlador/class.Via.php');
			$loVias = new NUCLEO\Via;
			$laRetorna['datos'] = $loVias->aVias;
			break;

		case 'Verificar':
			$laDatos = $_POST['datos'];
			$laRetorna = $loOrdenesAmb->verificarOA($laDatos);
			if($laRetorna['Valido']){
				$laRetorna = $loOrdenesAmb->fGuardarOrdenes($laDatos);
			}
			break;
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
