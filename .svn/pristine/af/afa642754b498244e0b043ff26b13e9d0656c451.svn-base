<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion = (isset($_GET['accion'])?$_GET['accion']:(isset($_POST['accion'])?$_POST['accion']:''));
	
	require_once __DIR__ . '/../../../controlador/class.OrdenesMedicas.php';
	$loOrdenesMedicas = new NUCLEO\OrdenesMedicas;
	
	switch ($lcAccion) {
		case 'caracteresJustificacion':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaCaracteresJustificacion() ;
			break;
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
