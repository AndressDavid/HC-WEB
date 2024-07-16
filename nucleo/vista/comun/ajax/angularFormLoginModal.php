<?php
require_once __DIR__ .'/../../../publico/constantes.php';

$laRetorna = ['error'=>'','sesion'=>0];

if($_SERVER['REQUEST_METHOD']=='POST'){

	if(isset($_POST['usuario']) && isset($_POST['password'])){
		
		if ($_SESSION[HCW_NAME]->oUsuario->iniciarSesion($_POST['usuario'],$_POST['password'])==true){

			$laRetorna['sesion']=1;
			if(isset($_POST['tipo']) && isset($_POST['especialidad'])){

				$lnTipUsu=$_SESSION[HCW_NAME]->oUsuario->getTipoUsuario(false);
				$lcCodEsp=$_SESSION[HCW_NAME]->oUsuario->getEspecialidad(false);

				if($lnTipUsu==$_POST['tipo'] && $lcCodEsp==$_POST['especialidad']){
					$laRetorna['sesion']=2;

				}else{
					if($_SESSION[HCW_NAME]->oUsuario->cargar($_POST['usuario'], $_POST['tipo'], $_POST['especialidad'])){
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