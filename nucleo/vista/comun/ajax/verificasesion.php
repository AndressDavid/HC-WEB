<?php
try {
	require_once __DIR__ .'/../../../publico/constantes.php';

	$laRetorna=['error'=>'','error_sesion'=>false];

	$lnContinuar = true;
	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()!==true){
			$laRetorna['error']='-- El usuario no tiene sesión activa. --';
		}
	} else {
		$laRetorna['error']='-- Error en la sesión. Intente nuevamente. --';
	}
} catch (Exception $e) {
	$laRetorna['error']='-- Error al validar la sesión. --';
} finally {
	if(!empty($laRetorna['error'])){
		$laRetorna['error_sesion']=true;
		$lnContinuar=false;
	}
}
