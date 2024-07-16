<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.Texto_Informativo.php';
	$laRetorna['cTextoInfo']=(new NUCLEO\Texto_Informativo)->retornarTexto();

}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
