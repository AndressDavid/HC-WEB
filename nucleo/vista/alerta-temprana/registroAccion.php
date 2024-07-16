<?php
	require_once (__DIR__ .'/../../publico/constantes.php') ;
	require_once (__DIR__ .'/../../controlador/class.SignosNews.php') ;
	$loSignosNews = new NUCLEO\SignosNews();
	
	$llActualizado = false;
	$lnIngreso = (isset($_POST['ingreso'])?$_POST['ingreso']+0:0); settype($lnIngreso,'Integer');
	$lcAccion = (isset($_POST['accion'])?trim(ltrim($_POST['accion']."")):'');

	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			if (!empty($lcAccion)){
				switch($lcAccion){
					case "marcar":
						$llActualizado = $loSignosNews->llegadaERR($lnIngreso, $_SESSION[HCW_NAME]->oUsuario->getUsuario());
						break;
						
					case "llamar":
						$llActualizado = $loSignosNews->llamarERR($lnIngreso, $_SESSION[HCW_NAME]->oUsuario->getUsuario());
						break;						
				}
			}
		}
	}
	echo $llActualizado;	
?>
