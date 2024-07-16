<?php
	require_once (__DIR__ .'/../../constantes.php');
	if (isset($_SESSION[HCW_NAME])){
		$_SESSION[HCW_NAME]->oUsuario->getCaptcha()->generar(dirname(__FILE__).'/../../fonts/');
		$_SESSION[HCW_NAME]->oUsuario->getCaptcha()->getImage();
	}
?>