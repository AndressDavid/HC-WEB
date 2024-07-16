<?php
require_once __DIR__ .'/../../../publico/constantes.php';

$laRetorna = ['error'=>'','sesion'=>0];

if($_SERVER['REQUEST_METHOD']=='POST'){

	$lcFecha = date('YmdH');
	$lcKeyMaskLoginFor = strtolower('0'.md5('LOGINFOR-'.$lcFecha).'x');
	$lcKeyMaskUserName = strtolower('1'.md5('PASSWORD-'.$lcFecha).'y');
	$lcKeyMaskPassword = strtolower('2'.md5('USERNAME-'.$lcFecha).'z');
	$lcKeyMaskTipoUser = strtolower('3'.md5('USERTYPE-'.$lcFecha).'u');
	$lcKeyMaskEspecCod = strtolower('4'.md5('USERESPC-'.$lcFecha).'e');

	if(isset($_POST[$lcKeyMaskUserName]) && isset($_POST[$lcKeyMaskPassword])){
		if ($_SESSION[HCW_NAME]->oUsuario->iniciarSesion($_POST[$lcKeyMaskUserName],$_POST[$lcKeyMaskPassword])==true){
			$laRetorna['sesion']=1;
			if(isset($_POST[$lcKeyMaskTipoUser]) && isset($_POST[$lcKeyMaskEspecCod])){
				$lnTipUsu=$_SESSION[HCW_NAME]->oUsuario->getTipoUsuario(false);
				$lcCodEsp=$_SESSION[HCW_NAME]->oUsuario->getEspecialidad(false);
				if($lnTipUsu==$_POST[$lcKeyMaskTipoUser] && $lcCodEsp==$_POST[$lcKeyMaskEspecCod]){
					$laRetorna['sesion']=2;
				}else{
					if($_SESSION[HCW_NAME]->oUsuario->cargar($_POST[$lcKeyMaskUserName], $_POST[$lcKeyMaskTipoUser], $_POST[$lcKeyMaskEspecCod])){
						$laRetorna['sesion']=2;
					}else{
						$_SESSION[HCW_NAME]->oUsuario->cerrarSesion();
						$laRetorna['error']='El tipo de usuario y/o la especialidad no son correctas.';
					}
				}
			}
		}else{
			$laRetorna['error']='Los datos proporcionados no son correctos.<br>'.$_SESSION[HCW_NAME]->oUsuario->getError();
		}
	}else{
		$laRetorna['error']='Los datos proporcionados no son correctos';
	}
}else{
	$laRetorna['error']='Error en el método de envío.';
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);