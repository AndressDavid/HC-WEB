<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');

	$lcTipoIngreso = (isset($_GET['q'])?$_GET['q']:'DEFAULT');

	$laTabla=array();

	include (__DIR__ .'/../../../publico/headJSON.php');
	print(json_encode($laTabla??''));
?>
