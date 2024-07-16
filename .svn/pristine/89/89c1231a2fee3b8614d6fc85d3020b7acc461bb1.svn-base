<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {
	$laListaCups = $_POST['lcPaquete'] ?? [];
	$lcGeneroPaciente = $_POST['lcGenero'] ?? [];
	$lnEdadAnos = $_POST['lnEdadaños'] ?? 0;
	
	require_once __DIR__ . '/../../../controlador/class.OrdenesMedicas.php';
	$laRetorna['datos'] = (new NUCLEO\OrdenesMedicas())->ObtenerPaquetesCups($laListaCups,$lcGeneroPaciente,$lnEdadAnos);
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
