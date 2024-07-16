<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion= (isset($_GET['accion'])?$_GET['accion']:(isset($_POST['accion'])?$_POST['accion']:''));
	$lnNroIngreso= $_POST['lnIngreso'] ?? 0;
	
	require_once __DIR__ . '/../../../controlador/class.AplicacionFunciones.php';
	require_once __DIR__ . '/../../../controlador/class.OrdenesMedicas.php';
	$loOrdenesMedicas = new NUCLEO\OrdenesMedicas;
	
	switch ($lcAccion) {
		case 'verificarAlerta':
			$laRetorna['DATOS']=$loOrdenesMedicas->consultaAlertaMalNutricion($lnNroIngreso) ;
			break;
			
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
