<?php
require_once (__DIR__ .'/../../../publico/constantes.php');
require_once (__DIR__ .'/../../../controlador/class.AplicacionFunciones.php');
require_once (__DIR__ .'/../../../controlador/class.Usuarios.php') ;
require_once (__DIR__ .'/../../../controlador/class.UsuarioBitacora.php') ;
require_once (__DIR__ .'/../../../controlador/class.UsuarioPerfiles.php') ;
require_once (__DIR__ .'/../../../controlador/class.UsuarioTipos.php') ;
require_once (__DIR__ .'/../../../controlador/class.UsuarioPropiedades.php') ;


use NUCLEO\AplicacionFunciones;
use NUCLEO\Usuarios;
use NUCLEO\UsuarioBitacora;


$lcTipo = (isset($_GET['p'])?$_GET['p']:'p');
$laBuscar = (isset($_GET['q'])?$_GET['q']:'q');
$lnOffSet = intval(isset($_GET['offset'])?$_GET['offset']:0);
$lnLimit = $lnOffSet+intval(isset($_GET['limit'])?$_GET['limit']:100);
$lnOffSet+=1;
$laData = array("total" => 0, "totalNotFiltered" => 0, "rows" => array(), "content" => "");

switch ($lcTipo)
{
	case 'usuarios':
		$laBuscar = base64_decode($laBuscar);		
		if(!empty($laBuscar)){
			$laBuscar=json_decode($laBuscar,true);			
			if(is_array($laBuscar)){
				if(defined('HCW_NAME')){
					if (isset($_SESSION[HCW_NAME])){
						if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
							$loUsuarios = new Usuarios();
							$lnUsuarios = $loUsuarios->contar((isset($laBuscar['registro'])?$laBuscar['registro']:''),
															  (isset($laBuscar['usuario'])?$laBuscar['usuario']:''),
															  (isset($laBuscar['apellidos'])?$laBuscar['apellidos']:''),
															  (isset($laBuscar['nombres'])?$laBuscar['nombres']:''),				
															  (isset($laBuscar['documentoTipo'])?$laBuscar['documentoTipo']:''),
															  (isset($laBuscar['documentoNumero'])?intval($laBuscar['documentoNumero']):0),
															  (isset($laBuscar['especialidad'])?$laBuscar['especialidad']:''),
															  (isset($laBuscar['tipo'])?$laBuscar['tipo']:''),
															  (isset($laBuscar['estado'])?intval($laBuscar['estado']):0),
															  (isset($laBuscar['accesoIni'])?intval($laBuscar['accesoIni']):0), 
															  (isset($laBuscar['accesoFin'])?intval($laBuscar['accesoFin']):0), 
															  (isset($laBuscar['vigenciaIni'])?intval($laBuscar['vigenciaIni']):0), 
															  (isset($laBuscar['vigenciaFin'])?intval($laBuscar['vigenciaFin']):0));
							
							$laData = $loUsuarios->cargar((isset($laBuscar['registro'])?$laBuscar['registro']:''),
															  (isset($laBuscar['usuario'])?$laBuscar['usuario']:''),
															  (isset($laBuscar['apellidos'])?$laBuscar['apellidos']:''),
															  (isset($laBuscar['nombres'])?$laBuscar['nombres']:''),				
															  (isset($laBuscar['documentoTipo'])?$laBuscar['documentoTipo']:''),
															  (isset($laBuscar['documentoNumero'])?intval($laBuscar['documentoNumero']):0),
															  (isset($laBuscar['especialidad'])?$laBuscar['especialidad']:''),
															  (isset($laBuscar['tipo'])?$laBuscar['tipo']:''),
															  (isset($laBuscar['estado'])?intval($laBuscar['estado']):'1'),
															  (isset($laBuscar['accesoIni'])?intval($laBuscar['accesoIni']):0), 
															  (isset($laBuscar['accesoFin'])?intval($laBuscar['accesoFin']):0), 
															  (isset($laBuscar['vigenciaIni'])?intval($laBuscar['vigenciaIni']):0), 
															  (isset($laBuscar['vigenciaFin'])?intval($laBuscar['vigenciaFin']):0),
															  $lnOffSet,$lnLimit);
							$laData = array("total" => $lnUsuarios, "totalNotFiltered" => ($lnUsuarios-count($laData)), "rows" => $laData);
						}
					}
				}
			}
		}
		break;
	
	case 'usuarioTipos':
		if(defined('HCW_NAME')){
			if (isset($_SESSION[HCW_NAME])){
				if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
					$laData = (new NUCLEO\UsuarioTipos())->aTipos;
					$laDataAux = array();
					foreach($laData as $lcDatalKey => $laRecord){
						$laDataAux[] =  $laRecord;
					}
					$laData = array("total" => count($laDataAux), "totalNotFiltered" => count($laDataAux), "rows" => $laDataAux);
				}
			}
		}		
		break;

	case 'usuarioPropiedades':
		if(defined('HCW_NAME')){
			if (isset($_SESSION[HCW_NAME])){
				if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
					$laData = (new NUCLEO\UsuarioPropiedades())->aOpciones;
					$laDataAux = array();
					foreach($laData as $lcDatalKey => $laRecord){
						$laDataAux[] =  $laRecord;
					}
					$laData = array("total" => count($laDataAux), "totalNotFiltered" => count($laDataAux), "rows" => $laDataAux);
				}
			}
		}		
		break;		
	
	case 'perfiles':
		if(defined('HCW_NAME')){
			if (isset($_SESSION[HCW_NAME])){
				if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
					$laData = (new NUCLEO\UsuarioPerfiles())->aPerfiles;
					$laDataAux = array();
					foreach($laData as $lcDatalKey => $laRecord){
						$laDataAux[] =  $laRecord;
					}
					$laData = array("total" => count($laDataAux), "totalNotFiltered" => count($laDataAux), "rows" => $laDataAux);
				}
			}
		}
		break;
	
	case 'bitacora':
		$laBuscar = base64_decode($laBuscar);		
		if(!empty($laBuscar)){
			$laBuscar=json_decode($laBuscar,true);			
			if(is_array($laBuscar)){
				if(defined('HCW_NAME')){
					if (isset($_SESSION[HCW_NAME])){
						if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
							$lcUsuario=(isset($laBuscar['usuario'])?$laBuscar['usuario']:'');
							if(!empty($lcUsuario)){
								$loUsuario = new UsuarioBitacora();
								$lcBitacora = $loUsuario->historico($lcUsuario);
								$laData = array("content" => $lcBitacora);
							}
						}
					}
				}
			}
		}
		break;	

	case 'lista-CREUSUCU':
		if(defined('HCW_NAME')){
			if (isset($_SESSION[HCW_NAME])){
				if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
					$laRows=array();
					$laCampos=array('DESACCI AS NOMBRE', 'CODACCI AS ID');
					$laData = $goDb
							->select($laCampos)
							->tabla('AMACCI ')
							->orderBy('DESACCI')
							->getAll('array');

					if (is_array($laData)) {
						if (count($laData) > 0) {
							foreach ($laData as $laRow) {
								$laRow = array_map('trim', $laRow);
								$laRows[] = $laRow;
							}
						}
					}		
					$laData = array("total" => count($laRows), "totalNotFiltered" => count($laRows), "rows" => $laRows);

				}
			}
		}
		break;			
}
include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laData??''); ?>