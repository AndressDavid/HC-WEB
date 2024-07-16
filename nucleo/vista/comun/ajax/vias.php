<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {
	require_once __DIR__ . '/../../../controlador/class.Via.php';
	$loVias = (new NUCLEO\Via);
    $laRetorna = $loVias->obtenerListaVias();
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);