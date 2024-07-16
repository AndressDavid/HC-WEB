<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	$lcTipDoc = $_POST['tipdoc'] ?? '';
	$lcNumDoc = $_POST['numdoc'] ?? '0';

	require_once __DIR__ . '/../../../controlador/class.AntecedentesConsulta.php';
	$laRetorna['datos']=(new NUCLEO\AntecedentesConsulta($lcTipDoc, $lcNumDoc))->aAntecedentes;

}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
