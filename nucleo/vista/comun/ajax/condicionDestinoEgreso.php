<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion= (isset($_GET['accion'])?$_GET['accion']:(isset($_POST['accion'])?$_POST['accion']:''));
	$lcEstado= $_POST['estado'] ?? '';

	require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
	$loParametros = (new NUCLEO\ParametrosConsulta()) ;
	
	switch ($lcAccion) {
		case 'condicionDestinoEgreso':
			$loParametros->ObtenerCondicionDestinoEgreso($lcEstado);
			$laTipos = $loParametros->CondicionDestinoEgreso();
			$laRetorna['TIPOS']=$laTipos;
			unset($laTipos);
			break;
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
