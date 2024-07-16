<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion = $_POST['accion'] ?? '';

	require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
	$loEvoluciones = new NUCLEO\ParametrosConsulta;

	switch ($lcAccion) {
	

	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
