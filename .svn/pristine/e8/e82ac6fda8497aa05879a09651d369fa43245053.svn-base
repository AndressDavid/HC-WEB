<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../controlador/class.Nutricion.php');

	$lcAccion = trim(strval(isset($_GET['accion'])?$_GET['accion']:''));
	
	$loNutricion = new NUCLEO\Nutricion();

	$laTabla=array();
	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			switch($lcAccion){
				default:
					$lnIngreso = intval(isset($_GET['nIngreso'])?strval($_GET['nIngreso']):'');

					$lcInicio = (isset($_GET['inicio'])?strval($_GET['inicio']):'');
					$lnInicio = intval(str_replace('-','',$lcInicio));
					
					$lcFin = (isset($_GET['fin'])?strval($_GET['fin']):'');
					$lnFin = intval(str_replace('-','',$lcFin));

					$lcEstado = (isset($_GET['cEstado'])?strval($_GET['cEstado']):'');

					$laTabla = $loNutricion->buscar($lnIngreso, $lnInicio, $lnFin, $lcEstado);
					break;
			}	
		}
	}

	include (__DIR__ .'/../../../publico/headJSON.php');
	print(json_encode($laTabla??''));

?>
