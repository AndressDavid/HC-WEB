<?php

namespace NUCLEO;

require_once ('class.Db.php');
require_once ('class.Usuario.php');
require_once ('class.AplicacionFunciones.php');
require_once ('class.Habitaciones.php');

class Aplicacion
{
	// Privadas
	private $cServerAddr = '';
	private $cServerName = '';
	private $cRemoteAddr = '';
	private $cCurrentServerPath = '';
	private $cCurrentScriptWeb = '';
	private $cCurrentScriptFileName = '';
	private $cCurrentAbsoluteSelf = '';
	private $cCurentRequestUri = '';
	private $lIsSecure = false;
	private $cPropertiesUpdate = '';
	private $cHashServer = '';
	private $nIngresoSmartRoom = -1;

	// Publicas
	public $oUsuario = null;
	public $oEstiloOpcInicio = 'OPCIONES';

	function __construct(){
		global $goDb;

		$this->oUsuario = new Usuario();

		$oTabmae = $goDb->ObtenerTabMae('DE1TMA', 'LIBROHC', ['CL1TMA'=>'WEB','CL2TMA'=>'ESTHOME',]);
		$this->oEstiloOpcInicio = trim(AplicacionFunciones::getValue($oTabmae, 'DE1TMA', $this->oEstiloOpcInicio));
		$this->update();
	}

	public function update(){
		$this->cServerAddr =  AplicacionFunciones::serverIp();
		$this->cServerName = trim(strtolower(strval(AplicacionFunciones::serverProperty('SERVER_NAME'))));
		$this->cRemoteAddr = AplicacionFunciones::localIp();
		$this->cCurrentServerPath = AplicacionFunciones::getAbsolutePath(getcwd());
		$this->cCurrentScriptWeb = AplicacionFunciones::getAbsolutePath((isset($_SERVER['SCRIPT_NAME'])?$_SERVER['SCRIPT_NAME']:''),($this->cServerName=='localhost'?'nucleo':''));
		$this->cCurrentScriptFileName = AplicacionFunciones::getAbsolutePath(AplicacionFunciones::serverProperty('SCRIPT_FILENAME'));
		$this->cCurrentAbsoluteSelf = AplicacionFunciones::getAbsolutePath(AplicacionFunciones::serverProperty('PHP_SELF'));
		$this->cCurentRequestUri = trim(AplicacionFunciones::serverProperty('REQUEST_URI'));
		$this->lIsSecure = $this->isSecure();
		$this->cHashServer = base64_encode(openssl_encrypt($this->cServerAddr.$this->cServerName, 'aes-256-cbc', 'b43f06ae4a409702102d01b0a39d2c06', false, '6451117dcff3fe2b'));
		$this->cPropertiesUpdate  = date("Y-m-d H:i:s");

		$loHabita = new Habitaciones();
		$laSmartRoom = $loHabita->buscarHabitacionPorIP($this->cRemoteAddr);
		$this->nIngresoSmartRoom = count($laSmartRoom)>0 ? $laSmartRoom['INGRESO'] : -1;

		$this->setSecure();
		$this->validateSesionActiva();
	}

	public function setSecure(){
		$laWebResoucesConfig = require (__DIR__ .'/../privada/webResoucesConfig.php');
		$laExcludePages = $laWebResoucesConfig['secure']['exclude-ssl'];

		if($this->cServerName!='localhost' && $this->lIsSecure==false){
			if(in_array($this->cCurrentAbsoluteSelf, $laExcludePages)==false){
				header("Location: https://".$this->cServerName. $this->cCurentRequestUri);
				die();
			}
		}
	}

	public function validateSesionActiva(){
		$laWebResoucesConfig = require (__DIR__ .'/../privada/webResoucesConfig.php');
		$laExcludePages = $laWebResoucesConfig['secure']['exclude-sesion-pages'];
		$laExcludeServers=$laWebResoucesConfig['secure']['exclude-sesion-servers'];

		if ($this->oUsuario->getSesionActiva()==false){
			if(in_array($this->cServerName,$laExcludeServers)==false && in_array($this->cCurrentAbsoluteSelf, $laExcludePages)==false){
				header("Location: https://".$this->cServerName.'/error?404');
				die();
			}
		}
	}

	public function getHashServer(){
		return $this->cHashServer();
	}

	public function isSecure() {
		return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT'])?$_SERVER['SERVER_PORT'] == 443:false);
	}

	public function getServerAddr(){
		return $this->cServerAddr;
	}

	public function getServerName(){
		return $this->cServerName;
	}

	public function getRemoteAddr(){
		return $this->cRemoteAddr;
	}

	public function getCurrentServerPath(){
		return $this->cCurrentServerPath;
	}

	public function getCurrentScriptWeb(){
		return $this->cCurrentScriptWeb;
	}

	public function getCurrentScriptFileName(){
		return $this->cCurrentScriptFileName;
	}

	public function getCurrentAbsoluteSelf(){
		return $this->cCurrentAbsoluteSelf;
	}

	public function getCurrentRequestUri(){
		return $this->cCurentRequestUri;
	}

	public function getIsSecure(){
		return $this->lIsSecure;
	}

	public function getIngresoSmartRoom(){
		return $this->nIngresoSmartRoom;
	}
}