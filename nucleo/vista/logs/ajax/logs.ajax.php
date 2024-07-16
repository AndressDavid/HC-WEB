<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../controlador/class.AplicacionLogsManejador.php') ;
		
	$loAplicacionLogsManejador = new NUCLEO\AplicacionLogsManejador(false);	
	$lcAccion = strval(isset($_GET['accion'])?$_GET['accion']:'');
	$lcLogBaseName = strval(isset($_GET['cBaseName'])?$_GET['cBaseName']:'');

	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			switch ($lcAccion){
				case "cargar":
					if(!empty($lcLogBaseName)){
						$loAplicacionLogsManejador->echoLogContents($lcLogBaseName);
					}
					break;
					
				case "descargar":
					if(!empty($lcLogBaseName)){
						$loAplicacionLogsManejador->downloadLogContents($lcLogBaseName);
					}
					break;				
			}
		}
	}
?>
