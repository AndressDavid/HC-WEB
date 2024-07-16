<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../controlador/class.Bitacoras.php');
	
	$lcConsecutivo = strtoupper(strval(isset($_GET['p'])?$_GET['p']:'PACIENTE'));
	$lcTipoBitacora = (isset($_GET['q'])?$_GET['q']:'UNDEFINE');
	$lnIngreso = intval(isset($_GET['r'])?$_GET['r']:'0');
		
	$loBitacoras = new NUCLEO\Bitacoras($lcTipoBitacora, $lcConsecutivo, $lnIngreso);

	$laTabla=array();
	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			$laTabla=$loBitacoras->buscarDetalles();
		}
	}

	include (__DIR__ .'/../../../publico/headJSON.php');
	print(json_encode($laTabla??''));

?>
