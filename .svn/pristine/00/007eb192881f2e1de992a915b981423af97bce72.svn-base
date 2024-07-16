<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.Covid19.php';
	$loCovid = new NUCLEO\Covid19();
	$lcAccion = $_POST['accion'] ;

	switch($lcAccion)
	{
		case 'config':
			$laRetorna['config'] = $loCovid->cargarParametros();
		break;

		case 'validar':
			$lnIngreso = $_POST['ingreso'];
			$laRetorna['valida'] = false;
			if (!$loCovid->validarPaciente($lnIngreso)){
				$laDiagnos = explode(',', $_POST['dxPrincipal'] . (empty($_POST['dxOtros']) ? '' : ','.$_POST['dxOtros']) );
				if ($loCovid->ValidaDx($laDiagnos)){
					$laRetorna['valida'] = true;
				}
			}
		break;

		case 'guardar':
			$laResult = $loCovid->guardaPaciente($_POST['ingreso'], $_POST['dxPrincipal'], $_POST['dxOtros'], $_POST['aplica'], $_POST['programa']);
		break;
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
