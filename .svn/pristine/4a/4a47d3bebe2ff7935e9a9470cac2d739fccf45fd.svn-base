<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	// Acción a ejecutar
	$lcAccion = $_POST['accion'] ?? '';
	switch ($lcAccion)
	{
		case 'existeHC':
			require_once __DIR__ . '/../../../controlador/class.Historia_Clinica_Ingreso.php';
			$laRetorna['datos'] = (new NUCLEO\Historia_Clinica_Ingreso())
				->validaExisteHC($_POST['ingreso'], $_POST['via']);
			break;

		case 'validaTipoUsu':
			require_once __DIR__ . '/../../../controlador/class.ConsultaUrgencias.php';
			$laRetorna = (new NUCLEO\ConsultaUrgencias())->tipoUsuarioValido();
			break;

		case 'mensajesHC':
			require_once __DIR__ . '/../../../controlador/class.ConsultaUrgencias.php';
			$lnIngreso = $_POST['ingreso'] ?? 0;
			$loConsUrg = new NUCLEO\ConsultaUrgencias();
			$laRetorna['mensajes'] = [
				'atendidoRgr' => $loConsUrg->atendidoReingreso($lnIngreso),
				'alertaCvd19' => $loConsUrg->mensajeCovid19($lnIngreso),
			];
			break;

		case 'pacienteUrgencias':
			require_once __DIR__ . '/../../../controlador/class.ConsultaUrgencias.php';
			$loObjHC = new NUCLEO\ConsultaUrgencias();
			$laRetorna['pacienteUrg'] = $loObjHC->esPacienteUrgencias($_POST['ingreso'], $_POST['via'],$_POST['seccion']);
			break;

		case 'Epicrisis':
			require_once __DIR__ . '/../../../controlador/class.Epicrisis_Ingreso.php';
			$loObjHC = new NUCLEO\Epicrisis_Ingreso();
			$laRetorna['datos'] = $loObjHC->existeEpicrisis($_POST['ingreso']);
			$laRetorna['estado'] = $loObjHC->estadoPaciente($_POST['ingreso'], false);
			break;

		case 'opcionesMenu':
			$lcTipo = $_POST['menu'] ?? 'hospitalizado';
			$lcMenu = $lcTipo == 'hospitalizado' ? 'MENUHOSP' : ($lcTipo == 'urgencias' ? 'MENUURGE' : 'MENUHOSP');
			require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
			$laRetorna['datos'] = (new NUCLEO\ParametrosConsulta())->opcionesMenu($lcMenu);
			break;

		case 'verificarIngresoUrg':
			$lnIngreso = $_POST['ingreso'] ?? 0;
			require_once __DIR__ . '/../../../controlador/class.ConsultaUrgencias.php';
			$loConsUrg = new NUCLEO\ConsultaUrgencias();
			$laRetorna['valido'] = $loConsUrg-> PuedeEvolucionarUrg($lnIngreso);
			break;
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
