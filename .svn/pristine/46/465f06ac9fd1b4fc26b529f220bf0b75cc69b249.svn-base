<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	// Acción a ejecutar
	$lcAccion = $_POST['accion'] ?? '';
	switch ($lcAccion) {
		case 'pacientes':
			require_once __DIR__ . '/../../../controlador/class.ConsultaHospitalizados.php';
			$laRetorna['datos'] = (new NUCLEO\ConsultaHospitalizados())
				->consultaPacientes($_POST['ingreso'], $_POST['seccion']);
			break;
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
