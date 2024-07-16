<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	// Acción a ejecutar
	$lcAccion = $_POST['accion'] ?? '';
	switch ($lcAccion) {

		// Retorna elementos que contiene la orden ambulatoria
		case 'conordamb':
			require_once __DIR__ .'/../../../controlador/class.DatosAmbulatorios.php';
			$loOrdAmb = new NUCLEO\DatosAmbulatorios();
			$laRetorna['datos'] = $loOrdAmb->consultaContenido($_POST['ingreso']??0, $_POST['cnsord']??0);
			break;

		// Items cuando se selecciona TODO
		case 'todos':
			require_once __DIR__ .'/../../../controlador/class.DatosAmbulatorios.php';
			$loOrdAmb = new NUCLEO\DatosAmbulatorios();
			$laRetorna['datos'] = $loOrdAmb->consultaItemsTodo();
			break;

	}
}
include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
