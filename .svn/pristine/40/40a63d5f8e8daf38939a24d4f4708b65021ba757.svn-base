<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {
	$lcTipo = $_POST['tipoDato'];
	$lcOtros = $_POST['otros'] ?? [];

	require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
	$laRetorna['datos'] = (new NUCLEO\ParametrosConsulta())->ConsultaAutocompletar($lcTipo, $lcOtros);

	unset($laTipos);
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
