<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	$lcTipo = intval($_POST['tipo'] ?? '0'); // 0 = desde TABMAE, 1 = desde PRMTAB

	require_once __DIR__ . '/../../../controlador/class.SeccionesHabitacion.php';
	$laRetorna['SECCIONES']=(new NUCLEO\SeccionesHabitacion($lcTipo))->aSecciones;

}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
