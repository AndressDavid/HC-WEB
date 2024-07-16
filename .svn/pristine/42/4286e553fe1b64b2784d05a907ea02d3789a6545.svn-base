<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';


if ($lnContinuar) {
	$lcAccion = (isset($_GET['accion'])?$_GET['accion']:(isset($_POST['accion'])?$_POST['accion']:''));
	$lcProcedimiento = $_POST['lcProcedimiento'] ?? '';
	$lcDatosPaciente = $_REQUEST['lcDatosPacientes']??[];

	
	require_once __DIR__ . '/../../../controlador/class.Procedimientos.php';
	$loProcedimientos = new NUCLEO\Procedimientos;
	
	switch ($lcAccion) {
			
		case 'consultarProcedimientos':
			$lcNombre = NUCLEO\AplicacionFunciones::fnSanitizar(trim($_REQUEST['nombre']??''));
			if(!empty($lcNombre)){
				$lcNombre = NUCLEO\AplicacionFunciones::fnSanitizar($lcNombre);
				$lcNombre = str_replace("'", "", $lcNombre);
				$lcNombre = explode(' ',$lcNombre);
			}
			$laRetorna = (new NUCLEO\Procedimientos())->consultaListaProcedimientos($lcNombre,'',false,$lcDatosPaciente);
			break;
	
		case 'consultarCupsPos':
			$laRetorna['TIPOS']=$loProcedimientos->consultaProcedimientoPos($lcProcedimiento) ;
			break;
	
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
