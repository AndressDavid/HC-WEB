<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.ConsultaUrgencias.php';

	// Acción a ejecutar
	$lcAccion = $_POST['accion'] ?? '';
	switch ($lcAccion) {

		case 'pacientes':
			$laRetorna['datos'] = (new NUCLEO\ConsultaUrgencias())
				->consultaPacientes(
					str_replace('-','',$_POST['fecha']),
					$_POST['estado'],
					$_POST['ingreso'],
					$_POST['tipoId'],
					$_POST['numId'],
					$_POST['seccion']
				);
			$ltAhora = new \DateTime($goDb->fechaHoraSistema());
			$laRetorna['fechahora'] = str_replace(' ','T',$ltAhora->format('Y-m-d H:i:s'));
			break;

		case 'estadosConsulta':
			$laRetorna['datos'] = (new NUCLEO\ConsultaUrgencias())->estadosConsulta();
			break;

		case 'tiposTriage':
			$laRetorna['datos'] = (new NUCLEO\ConsultaUrgencias())->tiposTriage();
			break;

		case 'validaTipoUsu':
			$laRetorna = (new NUCLEO\ConsultaUrgencias())->tipoUsuarioValido();
			break;

		case 'bloquearIngreso':
			$lnIngreso = $_POST['ingreso'];
			$laRetorna = (new NUCLEO\ConsultaUrgencias())->bloquearIngreso($lnIngreso);
			break;

		case 'desbloquearIngreso':
			$lnIngreso = $_POST['ingreso'];
			$laRetorna = (new NUCLEO\ConsultaUrgencias())->desbloquearIngreso($lnIngreso);
			break;

		case 'triagePendientesIngresoAdmin':
			$laRetorna += (new NUCLEO\ConsultaUrgencias())->triagePendientesIngresoAdmin();
			break;

	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
