<?php
require_once __DIR__ .'/../../../publico/constantes.php';

$laRetorna=['error'=>'','sesion'=>false];

if (isset($_SESSION[HCW_NAME])){
	if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
		if(isset($_POST['usrhcw'])){
			$laUsrLocal=json_decode(base64_decode($_POST['usrhcw']),true);
			$lcUsuario=$_SESSION[HCW_NAME]->oUsuario->getUsuario();
			$lnTipUsu=$_SESSION[HCW_NAME]->oUsuario->getTipoUsuario(false);
			$lcCodEsp=$_SESSION[HCW_NAME]->oUsuario->getEspecialidad(false);
			if(isset($laUsrLocal['usuario'])&&isset($laUsrLocal['tipo'])&&isset($laUsrLocal['especialidad'])){
				if($lcUsuario==$laUsrLocal['usuario']){
					if($lnTipUsu==$laUsrLocal['tipo'] && $lcCodEsp==$laUsrLocal['especialidad']){
						$laRetorna['sesion']=true;
					}else{
						if($_SESSION[HCW_NAME]->oUsuario->cargar($lcUsuario, $laUsrLocal['tipo'], $laUsrLocal['especialidad'])){
							$laRetorna['error']='Cambio de tipo y/o especialidad.';
							$laRetorna['sesion']=true;
						}else{
							$laRetorna['error']='Error al validar sesión<br>Tipo y/o Especialidad no válidos para el usuario.';
						}
					}
				}else{
					$laRetorna['error']='Error al validar sesión<br>Usuario de sesión diferente en el servidor.';
				}
			}else{
				$laRetorna['error']='Error al validar sesión<br>Datos de Usuario local incorrectos para validar.';
			}
		}else{
			$laRetorna['error']='Error al validar sesión<br>Faltan datos para validar sesión.';
		}
	}else{
		$laRetorna['error']='Error al validar sesión<br>No hay una sesión activa.';
	}
}else{
	$laRetorna['error']='Error al validar sesión<br>Objeto de sesión no existe.';
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
