<?php

require_once __DIR__ . '/../../controlador/class.AplicacionFunciones.php';
require_once __DIR__ . '/../../controlador/class.Epicrisis_Ingreso.php';
require_once __DIR__ . '/../../vista/comun/modalAlertaNopos.php';
require_once __DIR__ . '/../../vista/comun/modalAlertaNoposIntranet.php';

use NUCLEO\Epicrisis_Ingreso;

$btnVolver = '<a class="btn btn-secondary" href="javascript: history.back()">Volver</a>';
$divMensaje = '<div class="container-fluid"><div class="row"><div class="col"><h4>%s</h4>%s</div></div></div>';

if (isset($_SESSION[HCW_DATA])) {
	$lnIngreso = $_SESSION[HCW_DATA]['ingreso'];
	unset($_SESSION[HCW_DATA]);
} else {
	$lnIngreso = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['ingreso'] ?? 0);
}

if ($lnIngreso > 0) {

	require_once __DIR__ . '/../../controlador/class.Historia_Clinica_Ingreso.php';

	$lnCnsCita = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['cita'] ?? '');
	$lnCnsCons = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['cons'] ?? '');
	$lnCnsEvol = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['evol'] ?? '');

	// verificar si ya tiene epicrisis
	$loObjEpi = new Epicrisis_Ingreso();
	$laExisteEpi = $loObjEpi->existeEpicrisis($lnIngreso);

	if ($laExisteEpi['Valido']){

		$laEstados = $loObjEpi->obtenerEstadosTodos($lnIngreso);
		if($laEstados['Estado'] == 1 || $laEstados['Estado'] == 2){

			// Datos para epicrisis
			$loHcIng = new NUCLEO\Historia_Clinica_Ingreso();
			$laRetorna = $loHcIng->datosIngreso($lnIngreso);
			$laRetorna['nConCita'] = $lnCnsCita;
			$laRetorna['nConCons'] = $lnCnsCons;
			$laRetorna['nConEvol'] = $lnCnsEvol;

			(new NUCLEO\Auditoria())->guardarAuditoria($lnIngreso, $lnCnsCons, $lnCnsCita, '', 'EPICRISIS_WEB', 'INICIO', 0, 'INGRESO EPICRISIS', 'EPICRISIS', $laRetorna['cTipId'], $laRetorna['nNumId']);

			// Vistas de Epicrisis
			include __DIR__ . '/epicrisis.php';
			echo '<script type="text/javascript">' . PHP_EOL;
			echo 'var aDatosIngreso = btoObj(\'' . base64_encode(json_encode($laRetorna)) . '\');' . PHP_EOL;
			echo 'var aEstados = btoObj(\'' . base64_encode(json_encode($laEstados)) . '\');' . PHP_EOL;
			echo '</script>' . PHP_EOL;

		}else{
			printf($divMensaje, "No existe SALIDA para el paciente con ingreso $lnIngreso", $btnVolver);
		}

	}else{
		printf($divMensaje, "Ya existe epicrisis para el paciente con ingreso $lnIngreso", $btnVolver);
	}

} else {
	printf($divMensaje, 'Datos insuficientes para acceder a la epicrisis', $btnVolver);
}
