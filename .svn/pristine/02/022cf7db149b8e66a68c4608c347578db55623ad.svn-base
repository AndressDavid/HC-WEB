<?php
require_once (__DIR__ .'/../../publico/constantes.php');

$laRetorna = array('error'=>'', 'RESULTADO'=>'');

if(defined('HCW_NAME')){
	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){

			$lcFirma = "--->".(isset($_POST['firma'])?base64_decode($_POST['firma']):'');
			$lcFirma = "--->".(isset($_POST['firma'])?$_POST['firma']:'');
			
			$laRetorna['RESULTADO']=$lcFirma." -> Guardado";
		}
	}
}

include __DIR__ .'/../../publico/headJSON.php';
echo json_encode($laRetorna??''); ?>