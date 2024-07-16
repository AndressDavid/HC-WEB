<?php
require_once __DIR__ .'/../../../publico/constantes.php';
require_once __DIR__ .'/../../../controlador/class.UsuarioCargos.php';

$laRetorna = array('error'=>'', 'CARGOS' => array());

if(defined('HCW_NAME')){
	if(isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			$laCargos = (new NUCLEO\UsuarioCargos(isset($_POST['tipoCargo'])?intval($_POST['tipoCargo']):0,isset($_POST['areaCargo'])?intval($_POST['areaCargo']):0))->aCargos;
			
			if(count($laCargos)>0){
				foreach($laCargos as $lnCargo => $laCargo){
					$lcTipo=(intval($laCargo['TIPO'])==1001?"Asistencial":(intval($laCargo['TIPO'])==1002?"Administrativo":""));
					$lcCargo=sprintf("%s | %s | %s | %s",$laCargo['ID'],ucwords(strtolower(htmlentities(htmlspecialchars($laCargo['NOMBRE'])))),$lcTipo,ucwords(strtolower($laCargo['DEPARTAMENTO'])));
					$laRetorna['CARGOS'][$lnCargo] = $lcCargo;
				}
			}
		}else{$laRetorna['error']='El usuario no tiene sesión activa';}
	}else{$laRetorna['error']='Error en la sesión. Intente nuevamente.';}
}else{$laRetorna['error']='Error en la sesión. Intente nuevamente.';}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
