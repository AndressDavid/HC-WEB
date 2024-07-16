<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../controlador/class.Bitacoras.php');
	
	$lcModo = strtoupper(strval(isset($_GET['p'])?$_GET['p']:'PACIENTE'));
	$lcTipoBitacora = (isset($_GET['q'])?$_GET['q']:'UNDEFINE');
	$lnIngreso = intval(isset($_GET['r'])?$_GET['r']:'0');
	$lnInicio = intval(isset($_GET['inicio'])?str_replace('-','',$_GET['inicio']):0);
	$lnFin = intval(isset($_GET['fin'])?str_replace('-','',$_GET['fin']):0);
	$lcEstado = strval(isset($_GET['estado'])?$_GET['estado']:'');	
		
	$loBitacoras = new NUCLEO\Bitacoras($lcTipoBitacora, 0, $lnIngreso);

	$laTabla=array();
	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			$laTabla=$loBitacoras->buscar($lcModo, $lnInicio, $lnFin, $lcEstado);
		}
	}

	include (__DIR__ .'/../../../publico/headJSON.php');
	print(json_encode($laTabla??''));

?>
