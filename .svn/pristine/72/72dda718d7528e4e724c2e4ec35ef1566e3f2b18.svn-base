<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../controlador/class.Nutriciones.php');
	
	$loNutriciones = new NUCLEO\Nutriciones();

	$laTabla=array();
	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			$laTabla=[];			
		}
	}
	include (__DIR__ .'/../../../publico/headJSON.php');
?>
