<?php

namespace NUCLEO;

require_once ('class.Db.php') ;
require_once ('class.Persona.php');
require_once ('class.UsuarioBitacora.php');
require_once ('class.UsuarioTipo.php');
require_once ('class.UsuarioPropiedades.php');
require_once ('class.Especialidad.php');
require_once ('class.Bodega.php');
require_once ('class.CentroCosto.php');
require_once ('class.AplicacionFunciones.php');
require_once ('class.AplicacionMobileDetect.php');
require_once ('class.Captcha.php');
require_once ('class.ApiAutenticacion.php');


use NUCLEO\Db;
use NUCLEO\Persona;
use NUCLEO\UsuarioBitacora;
use NUCLEO\UsuarioTipo;
use NUCLEO\UsuarioPropiedades;
use NUCLEO\Especialidad;
use NUCLEO\Bodega;
use NUCLEO\CentroCosto;
use NUCLEO\AplicacionFunciones;
use NUCLEO\AplicacionMobileDetect;

class Usuario
    extends Persona {

    protected $lSesionActiva = false;
    protected $lPssOch = false;
	protected $cUsuario = '';
	protected $cRegistro = '';
	protected $aPerfilesMenu = array();
	protected $aOpcionesMenu = array();
	protected $aOpcionesBarra = array();
	protected $aPropiedades = array();
	protected $cOpcionesUsuario = '';
	protected $nTipoUsuario = 0;
	protected $oTipoUsuario = null;
	protected $nTipoUsuarioProcedimientosConsultas = 0;
	protected $cEspecialidad = '';
	protected $oEspecialidad = null;
	protected $aEspecialidades = array();
	protected $cIP = '';
	protected $cId = '';
	protected $lCambiarClave = false;
	protected $cAlgrth = 'CRC';
	protected $cPsswrd = '';
	protected $lRequiereAval=false;
	protected $aNotificaciones = array();
	protected $nVigenciaIni = 0;
	protected $nVigenciaFin = 0;
	protected $nVigencia = 0;
	protected $cError = '';
	protected $oBodega = null;
	protected $aBodegas = array();
	protected $oCentroCosto = null;
	protected $cDeviceType = '';
	protected $aEntidadesConsultaLibroHc = array();
	protected $nEntidadesConsultaLibroHc = 0;
	protected $cEncriptadoMetodo = '';
	protected $cEncriptadoClave = '';
	protected $cEncriptadoIV = '';
	protected $nEstado = 0;
	protected $cDepartamento = '';
	protected $cArea = '';
	protected $cCargo = '';
	protected $cFoto = '';
	protected $cFirma = '';
	protected $aAuditoria = array();
	protected $lRetirado = false;
	protected $nProsodyJtmEstado = 0;
	protected $cProsodyJtmPassword = '';
	protected $nProsodyJtmDiaVigenciaPassword = 0;
	protected $cMailUrlWsdl = 'https://hcwp.shaio.org/webservice/publico/mail/webservice.php?wsdl';
	protected $cMailServer = 'mail.shaio.org';
	protected $nMailPort = 25;
	protected $cMailUser = 'no-responder@shaio.org';
	protected $cMailFrom = 'no-responder@shaio.org';
	protected $cMailPass = '********'; // No se requiere
	protected $oCaptcha = null;
	protected $nForgotIntentos = 0;
	protected $nForgotIntentosMaximosPermitidos = 5;
	protected $nForgotIntentosBloqueoMinutos = 5;
	protected $cForgotIntentosBloqueoFecha = '';
	protected $oBitacora = null;
	protected $nPswdLargoMinimo=8;
	protected $nPswdLargoMaximo=10;
	protected $nPswdMinMayusculas=0;
	protected $nPswdMinMinusculas=0;
	protected $nPswdMinNumeros=0;
	protected $nPswdMinEspeciales=0;
	protected $nPswdMinFuerza=0;
	protected $cPswdClavesAnteriores="";


    function __construct(){
		$this->cEncriptadoMetodo = 'aes-256-cbc';
		$this->cEncriptadoClave = 'b43f06ae4a409702102d01b0a39d2c06';
		$this->cEncriptadoIV = '6451117dcff3fe2b';
		$this->cIP = AplicacionFunciones::LocalIp();
		$this->cDeviceType = $this->getClientDeviceType();
		$this->oCaptcha = new Captcha(dirname(__FILE__).'/../publico/fonts/');


		// Requisitos de contraseña
		global $goDb;
		if(isset($goDb)){
			$this->nPswdLargoMinimo=intval($goDb->ObtenerTabMae1("DE2TMA", "SISMENST", "CL1TMA='01010501'", null, "8"));
			$this->nPswdLargoMaximo=intval($goDb->ObtenerTabMae1("DE2TMA", "SISMENST", "CL1TMA='01010502'", null, "10"));
			$this->nPswdMinMayusculas=intval($goDb->ObtenerTabMae1("DE2TMA", "SISMENST", "CL1TMA='01010503'", null, "0"));
			$this->nPswdMinMinusculas=intval($goDb->ObtenerTabMae1("DE2TMA", "SISMENST", "CL1TMA='01010504'", null, "0"));
			$this->nPswdMinNumeros=intval($goDb->ObtenerTabMae1("DE2TMA", "SISMENST", "CL1TMA='01010505'", null, "0"));
			$this->nPswdMinEspeciales=intval($goDb->ObtenerTabMae1("DE2TMA", "SISMENST", "CL1TMA='01010506'", null, "0"));
			$this->nPswdMinFuerza=intval($goDb->ObtenerTabMae1("DE2TMA", "SISMENST", "CL1TMA='01010507'", null, "0"));
			$this->cPswdClavesAnteriores=strtoupper(strval($goDb->ObtenerTabMae1("DE2TMA", "SISMENST", "CL1TMA='01010508'", null, "")));
		}

		// Bitácora
		$this->oBitacora = new UsuarioBitacora();
    }

    public function cargar($tcUsuario='', $tnTipoUsuario=0, $tcEspecialidad=''){
		$llCargado=false;

		global $goDb;
		if(isset($goDb)){
			if(!empty($tcUsuario)){

				// Buscando registro de usuario
				try{
					$laSisMenSeg = @$goDb->from('SISMENSEG')->where('USUARI', '=', $tcUsuario)->get('array');
				} catch (\Exception $e) {
					$laSisMenSeg = NULL;
				}
				if(is_array($laSisMenSeg)==true){
					if(count($laSisMenSeg)>0){

						// Buscando datos de seguridad del usuario
						$laRiaRgmn = $goDb->from('RIARGMN')->where('USUARI', '=', $tcUsuario)->get('array');
						if(is_array($laSisMenSeg)==true){
							if(count($laSisMenSeg)>0){

								// Validando la especialidad
								if($this->ValidarEspecialidad($laRiaRgmn)==true){

									// Validando tipo de usuario
									if($this->ValidarTipoUsuario($laRiaRgmn['TPMRGM'])==true){

										// Cargar tipo de Usuario y especilidad
										$lnTipoUsuario = intval($laRiaRgmn['TPMRGM']);
										$lcEspecialidad = trim($laRiaRgmn['CODRGM']);

										if($this->validarCambioEspecialidad($tnTipoUsuario, $tcEspecialidad)==true){
											$lnTipoUsuario=intval($tnTipoUsuario);
											$lcEspecialidad = trim(strval($tcEspecialidad));
										}
										$this->oTipoUsuario = new UsuarioTipo($lnTipoUsuario);
										$this->oEspecialidad = new Especialidad($lcEspecialidad);


										// Cargando basicas
										$this->setTipoId(trim(strtoupper($laRiaRgmn['TIDRGM'])));
										$this->nId = $laRiaRgmn['NIDRGM']+0;
										$this->NombresApellidos(trim($laRiaRgmn['NNOMED']),trim($laRiaRgmn['NOMMED']));
										$this->cUsuario = trim(strtoupper($laRiaRgmn['USUARI']));
										$this->cRegistro = trim($laRiaRgmn['REGMED']);
										$this->nTipoUsuario = $lnTipoUsuario;
										$this->nTipoUsuarioProcedimientosConsultas = intval($laRiaRgmn['EO4RGM']);
										$this->cEspecialidad = $lcEspecialidad;
										$this->cAlgrth = trim(strtoupper($laSisMenSeg['ALGRTH']));
										$this->cEmail = $this->Notificaciones($this->cUsuario);
										$this->nEntidadesConsultaLibroHc = $this->ValidarEntidadesConsultaLibroHc($this->cUsuario);
										$this->nVigenciaIni = $laRiaRgmn['FVDRGM'];
										$this->nVigenciaFin = $laRiaRgmn['FVHRGM'];
										$this->nEstado = intval($laRiaRgmn['ESTRGM']);
										$this->cDepartamento = trim(empty($laSisMenSeg['DPARTM'])?trim(substr(trim($this->cUsuario),0,2)):$laSisMenSeg['DPARTM']);
										$this->cArea = trim($laSisMenSeg['TAGCH1']);
										$this->cCargo = trim($laSisMenSeg['TAGCH2']);
										$this->nId = intval($laRiaRgmn['NIDRGM']);
										$this->setTipoId(trim(strval($laRiaRgmn['TIDRGM'])));
										$this->cFoto = trim($laSisMenSeg['IMAGE1']);
										$this->cFirma = trim($laSisMenSeg['IMAGE2']);
										$this->nProsodyJtmEstado = intval($laSisMenSeg['PRDYES']);
										$this->cProsodyJtmPassword = trim(strval($laSisMenSeg['PRDYPW']));
										$this->nProsodyJtmDiaVigenciaPassword = intval($laSisMenSeg['PRDYFE']);
										$this->lRetirado = $laRiaRgmn['EO2RGM'];


										// Desde la prmtab tipo ava para aval
										$laAval = $goDb->count('*','REGISTROS')->from('PRMTAB04')->where('TABTIP', '=', 'AVA')->where('TABCOD', '=', $this->nTipoUsuario)->get('array');

										$this->lRequiereAval=false;
										if(is_array($laAval)==true){
											if(count($laAval)>0){
												$this->lRequiereAval=($laAval['REGISTROS']>0);
											}
										}

										// Auditoria
										$this->aAuditoria['MEDICO']['CREADO'] = array('USUARIO'=>$laRiaRgmn['USRRGM'], 'PROGRAMA'=>$laRiaRgmn['PGMRGM'], 'FECHA'=>$laRiaRgmn['FECRGM'], 'HORA'=>$laRiaRgmn['HORRGM']); //flag_flyaway_green;
										$this->aAuditoria['MEDICO']['MODIFICADO'] = array('USUARIO'=>$laRiaRgmn['UMORGM'], 'PROGRAMA'=>$laRiaRgmn['PMORGM'], 'FECHA'=>$laRiaRgmn['FMORGM'], 'HORA'=>$laRiaRgmn['HMORGM']); //flag_flyaway_pointed;
										$this->aAuditoria['SEGURIDAD']['CREADO'] = array('USUARIO'=>$laSisMenSeg['UCRMEN'], 'PROGRAMA'=>$laSisMenSeg['PCRMEN'], 'FECHA'=>$laSisMenSeg['FCRMEN'], 'HORA'=>$laSisMenSeg['HCRMEN']); //flag_flyaway_green;
										$this->aAuditoria['SEGURIDAD']['MODIFICADO'] = array('USUARIO'=>$laSisMenSeg['UMOMEN'], 'PROGRAMA'=>$laSisMenSeg['PMOMEN'], 'FECHA'=>$laSisMenSeg['FMOMEN'], 'HORA'=>$laSisMenSeg['HMOMEN']); //flag_flyaway_pointed;
										$this->aAuditoria['ALPHIL']['CREADO'] = array('USUARIO'=>'', 'PROGRAMA'=>'', 'FECHA'=>'', 'HORA'=>''); //flag_flyaway_green;
										$this->aAuditoria['ALPHIL']['MODIFICADO'] = array('USUARIO'=>'', 'PROGRAMA'=>'', 'FECHA'=>'', 'HORA'=>''); //flag_flyaway_pointed;

										$laSegUsr = $goDb->from('SEGUSR')->where('USRUSR', '=', $tcUsuario)->get('array');
										if(is_array($laSegUsr)==true){
											if(count($laSegUsr)>0){
												if(isset($laSegUsr['USRUSC'])){
													$this->aAuditoria['ALPHIL']['CREADO'] = array('USUARIO'=>$laSegUsr['USRUSC'], 'PROGRAMA'=>'', 'FECHA'=>$laSegUsr['USRFHC'], 'HORA'=>$laSegUsr['USRHRC']); //flag_flyaway_green;
													$this->aAuditoria['ALPHIL']['MODIFICADO'] = array('USUARIO'=>$laSegUsr['USRUSM'], 'PROGRAMA'=>'', 'FECHA'=>$laSegUsr['USRFHM'], 'HORA'=>$laSegUsr['USRHRM']); //flag_flyaway_pointed;
												}
											}
										}

										//Metodos de control
										$this->bodegasCentroCosto();
										$this->propiedades();

										// Usuario cargado
										$llCargado = true;
									}
								}
							}
						}
					}
				}
			}
		}

		return $llCargado;
    }

	public function iniciarSesion($tcUsuario='', $tcClave='', $tbConToken=false){
		$this->lSesionActiva=false;
		$this->cError = '';

		$tcUsuario=substr(trim(strtoupper($tcUsuario)),0,10);
		$tcClave=trim($tcClave);

		global $goDb;
		if(isset($goDb)){
			if(!empty($tcUsuario) && !empty($tcClave)){

				// Buscando registro de usuario
				try{
					$laSisMenSeg = @$goDb->from('SISMENSEG')->where('USUARI', '=', $tcUsuario)->get('array');
				} catch (\Exception $e) {
					$laSisMenSeg = NULL;
				}
				if(is_array($laSisMenSeg)==true){
					if(count($laSisMenSeg)>0){

						// Buscando datos de seguridad del usuario
						$laRiaRgmn = $goDb->from('RIARGMN')->where('USUARI', '=', $laSisMenSeg['USUARI'])->get('array');
						if(is_array($laSisMenSeg)==true){
							if(count($laSisMenSeg)>0){

								// Validando inicio
								if(isset($laSisMenSeg['USUARI'])==true && isset($laRiaRgmn['REGMED'])==true){

									// Validando el usuario
									if(empty($laSisMenSeg['USUARI'])==false && trim(strtoupper($tcUsuario))==trim(strtoupper($laSisMenSeg['USUARI']))){

										// Usuario no retirado
										if(intval($laRiaRgmn['EO2RGM'])==0){

											// Usuario activo
											if(intval($laRiaRgmn['ESTRGM'])==1){

												// Usuario bloqueado
												if(intval($laSisMenSeg['LOCKED'])==0){

													// Vigencia
													$this->nVigenciaIni = date('Ymd');
													$this->nVigenciaFin = $laRiaRgmn['FVHRGM'];
													$this->nVigencia=$this->nVigenciaFin-$this->nVigenciaIni+1 ;

													if($this->nVigencia>0 || $laSisMenSeg['PSSNEX']==1){
														$lbCargarUser=false;

														if($tbConToken){
															// Consulta Token
															$lcLocalIp=AplicacionFunciones::localIp();
															$laDatToken = $goDb
																->select('ID_TKW,IP_TKW,TKNTKW,FECTKW,HORTKW, TKSTKW')
																->from('TKNHCW')
																->where(['USUTKW'=>$tcUsuario])
																->getAll('array');
															if(is_array($laDatToken)){
																if(count($laDatToken)){
																	$lbTokenCorrecto=false;
																	foreach($laDatToken as $laData){
																		//$lcIP=($lcLocalIp=='127.0.0.1')?trim($laData['IP_TKW']):$lcLocalIp;
																		$lcIP=trim($laData['IP_TKW']);
																		$lcTk=trim($laData['TKNTKW']);
																		$lcTks=trim($laData['TKSTKW']);
																		$lcFc=$laData['FECTKW'];
																		$lcHr=str_pad($laData['HORTKW'],6,'0',STR_PAD_LEFT);
																		$lcTknMd5 = strtoupper(md5("mn_{$lcIP}_{$lcTk}_{$lcFc}_{$lcHr}"));

																		if($lcTknMd5==$tcClave){
																			$lbCargarUser=true;
																			$_SESSION['token'] = $lcTks;
																			// Eliminar el registro
																			try{
																				$goDb->from('TKNHCW')->where("ID_TKW='{$laData['ID_TKW']}'")->eliminar();
																			}catch(\Exception $e){
																				// $e->getMessage()
																			}
																			break;
																		}
																	}
																	if(!$lbCargarUser){
																		$this->cError = 'Token no válido';
																	}
																}else{
																	$this->cError = 'No se encuentra token';
																}
															}else{
																$this->cError = 'No se pudo consultar el token';
															}
															$lcPsswrd = '';

														}else{
															// Contraseña
															$lcPsswrd=trim(strtoupper($this->fCodificarContrasena(trim(strtoupper($laSisMenSeg['ALGRTH'])),$tcClave)));
															if($lcPsswrd==trim(strtoupper($laSisMenSeg['PSSWRD']))){
																$lbCargarUser=true;
															}else{
																$this->cError = 'El nombre de usuario o contraseña no es valido';
															}
														}

														if($lbCargarUser){
															$this->lSesionActiva = $this->cargar($tcUsuario);
															if($this->lSesionActiva==true){

																// Propiedades especifics
																$this->cIP = AplicacionFunciones::LocalIp();
																$this->aPerfilesMenu = $this->PerfilesMenu($this->cUsuario, 'HCW', 'MENPRI');
																$this->aOpcionesMenu = $this->OpcionesMenu($this->cUsuario, $this->aOpcionesBarra, $this->aPerfilesMenu['MENUID'], 'HCW', 'MENPRI');
																$this->cOpcionesUsuario = $this->OpcionesUsuario($this->aPerfilesMenu['MENUID']);
																$this->cPsswrd = $lcPsswrd;
																$this->lCambiarClave = (intval($laSisMenSeg['PSSOCH'])==1);
																$this->nForgotIntentos = 0;
																if(!$tbConToken){
																	$auth = new \ApiAutenticacion();
																	$auth->generarToken($tcUsuario, $tcClave);
																}
															}
														}

													}else{
														$this->cError = 'Contraseña expiro';
													}
												}else{
													$this->cError = 'Usuario bloqueado';
												}
											}else{
												$this->cError = 'Usuario inactivo';
											}
										}else{
											$this->cError = 'Usuario retirado';
										}
									}else{
										$this->cError = 'Usuario no valido';
									}
								}
							}
						}else{
							$this->cError = 'No existe el registro';
						}
					}
				}else{
					$this->cError = 'No existen credenciales';
				}
			}else{
				$this->cError = 'El nombre de usuario o contraseña no es valido';
			}
		}else{
			$this->cError = 'No hay conexión con la base de datos';
		}

		return $this->lSesionActiva;
	}

	public function cerrarSesion(){
		$this->lSesionActiva = false;
		$this->cUsuario = '';
		$this->cRegistro = '';
		$this->aPerfilesMenu = array();
		$this->aOpcionesMenu = array();
		$this->aOpcionesBarra = array();
		$this->aPropiedades = array();
		$this->cOpcionesUsuario = '';
		$this->nTipoUsuario = 0;
		$this->nTipoUsuarioProcedimientosConsultas = 0;
		$this->cEspecialidad = '';
		$this->aEspecialidades = array();
		$this->cIP = '';
		$this->lCambiarClave = false;
		$this->cAlgrth = 'CRC';
		$this->cPsswrd = '';
		$this->lRequiereAval=false;
		$this->aNotificaciones = array();
		$this->cError = '';
		$this->cId = '';
		$this->nId = '';
		$this->cIdLugarExpedicion = '';
		$this->cNombre1 = '';
		$this->cNombre2 = '';
		$this->cApellido1 = '';
		$this->cApellido2 = '';
		$this->cEmail = '';
		$this->nNacio = 0;
		$this->nVigenciaIni = 0;
		$this->nVigenciaFin = 0;
		$this->nVigencia = 0;
		$this->oBodega = null;
		$this->aBodegas = array();
		$this->oCentroCosto = null;
		$this->aEntidadesConsultaLibroHc = array();
		$this->nEntidadesConsultaLibroHc = 0;
		$this->cDepartamento = '';
		$this->cArea = '';
		$this->cCargo = '';
		$this->cFoto = '';
		$this->cFirma = '';
		$this->aAuditoria = array();
		$this->lRetirado = false;
		$this->oTipoUsuario = null;
		$this->oEspecialidad = null;
		$this->nProsodyJtmEstado = 0;
		$this->cProsodyJtmPassword = '';
		$this->nProsodyJtmDiaVigenciaPassword = 0;
		return $this->lSesionActiva;
	}

	public function validarOpcion($tcOpcion=''){
		$tcOpcion=strtoupper($tcOpcion);
		$tcOpcion='['.trim((substr($tcOpcion,0,7)=='MODULO-'?$tcOpcion:'MODULO-'.$tcOpcion)).']';
		$lcOpcion=str_replace ('MODULO-','',$tcOpcion);
		$loAplicacionFunciones = new AplicacionFunciones();
		$llExiste=($loAplicacionFunciones->isSearchStrInStr($this->cOpcionesUsuario, $tcOpcion) || $loAplicacionFunciones->isSearchStrInStr($this->cOpcionesUsuario, $lcOpcion));
		return $llExiste;
	}

	private function fCodificarContrasena($tcAlgoritmo, $tcClave){
		switch(trim(strtoupper($tcAlgoritmo))){
			case 'CRC':
				return str_pad(intval( $this->getCRC($tcClave) * 1.79), 10, '0', STR_PAD_LEFT);
			case 'MD5':
				return md5($tcClave);
			case 'SHA1':
				return sha1($tcClave);
		}
	}
	private static function getCRC($tcString)
	{
		$nCRC = 0xFFFF;
		for ($lnCaracter = 0; $lnCaracter < strlen($tcString); $lnCaracter++){
			$lnValor = (($nCRC >> 8) ^ ord($tcString[$lnCaracter])) & 0xFF;
			$lnValor ^= $lnValor >> 4;
			$nCRC = (($nCRC << 8) ^ ($lnValor << 12) ^ ($lnValor << 5) ^ $lnValor) & 0xFFFF;
		}
		return $nCRC;
	}

	private function Notificaciones($tcUsuario=''){
		$lcEmail = '';

		if(!empty($tcUsuario)){
			global $goDb;
			if(isset($goDb)){
				$laCampos = ['CL1TMA','DE1TMA','ESTTMA'];
				$laNotificaciones = $goDb->select($laCampos)->from('TABMAE')->where('TIPTMA', '=', 'MAIMED')->where('CL2TMA', '=', $tcUsuario)->getAll('array');
				if(is_array($laNotificaciones)==true){
					if(count($laNotificaciones)>0){
						foreach($laNotificaciones as $laNotificacion){
							$this->aNotificaciones[] = array(
								'ESPECIALIDAD'=>trim($laNotificacion['CL1TMA']),
								'EMAIL'=>trim($laNotificacion['DE1TMA']),
								'ESTADO'=>trim($laNotificacion['ESTTMA'])
							);
							if(trim(strtoupper($laNotificacion['ESTTMA']))<>'I'){
								$lcEmail = (empty($lcEmail)?trim($laNotificacion['DE1TMA']):$lcEmail);
							}
						}
					}
				}
			}
		}
		return $lcEmail;
	}

	private function NombresApellidos($tcNombres='', $tcApellidos=''){
		$this->cNombre1='';
		$this->cNombre2='';
		$this->cApellido1='';
		$this->cApellido2='';
		$laNombresApellidos=array(explode(' ',$tcNombres),explode(' ',$tcApellidos));

		foreach ($laNombresApellidos as $lnValor=>$laValores) {
			foreach ($laValores as $lnItem => $lcItem) {
				if (is_null($lcItem) || empty($lcItem)) unset($laNombresApellidos[$lnValor][$lcItem]);
			}
		}

		$this->cNombre1=$laNombresApellidos[0][0];
		for($lnItem=1;$lnItem<count($laNombresApellidos[0]);$lnItem++){
			$this->cNombre2.=trim(ucwords(strtolower($laNombresApellidos[0][$lnItem])));
		}
		$this->cApellido1=$laNombresApellidos[1][0];
		for($lnItem=1;$lnItem<count($laNombresApellidos[1]);$lnItem++){
			$this->cApellido2.=trim(ucwords(strtolower($laNombresApellidos[1][$lnItem])));
		}
	}

	private function ValidarEspecialidad($taRiaRgmn){
		$lcEspecialidad = '';
		$this->aEspecialidades=array();

		if(is_array($taRiaRgmn)==true){
			if(isset($taRiaRgmn['CODRGM'])==true){

				// Especialidad por defecto
				$lcEspecialidad = $taRiaRgmn['CODRGM'];

				// Usuario con multiples especialidades
				global $goDb;
				if(isset($goDb)){

					$laCampos = ['A.TUSTUS','A.ESPTUS','A.OP2TUS','B.DESESP'];
					$laEspecialidades = $goDb
						->select($laCampos)
						->from('MEDTUSL01 A')
						->leftJoin('RIAESPEL01 B', 'A.ESPTUS', '=', 'B.CODESP')
						->where('A.REGTUS', '=', trim($taRiaRgmn['REGMED']))
						->where('B.CODESP', '<>', '')
						->getAll('array');
					if(is_array($laEspecialidades)==true){
						if(count($laEspecialidades)>0){
							foreach($laEspecialidades as $laEspecialidad){
								$this->aEspecialidades[] = array('TIPO'=>new UsuarioTipo(trim($laEspecialidad['TUSTUS'])+0),'ESPECIALIDAD'=> new Especialidad($laEspecialidad['ESPTUS']), 'NIVEL'=>$laEspecialidad['OP2TUS']);
							}
						}
					}

					if(empty($this->aEspecialidades)){
						$this->aEspecialidades[] = array('TIPO'=>new UsuarioTipo($this->nTipoUsuario),'ESPECIALIDAD'=> new Especialidad($lcEspecialidad), 'NIVEL'=>'PRINCIPAL');
					}
				}
			}
		}
		return $lcEspecialidad;
	}

	private function ValidarEntidadesConsultaLibroHc($tcUsuario){
		$tcUsuario = trim(strval($tcUsuario));
		$this->aEntidadesConsultaLibroHc=array();

		if(!empty($tcUsuario)){

			global $goDb;
			if(isset($goDb)){

				$laCampos = ['A.ENTENT','A.PLAENT','SUBSTR(TRIM(B.TE1SOC),1,30) DSCCON'];
				$laEntidadesConsultaLibroHc = $goDb
					->select($laCampos)
					->from('SISMENENT A')
					->leftJoin('PRMTE107 B', 'B.TE1COD', '=', "RIGHT(REPEAT('0', 13) || A.ENTENT, 13)")
					->where([
						'A.USUARI'=>$tcUsuario,
						'A.ESTENT'=>'A',
					])
					->getAll('array');
				if(is_array($laEntidadesConsultaLibroHc)==true){
					if(count($laEntidadesConsultaLibroHc)>0){
						foreach($laEntidadesConsultaLibroHc as $laEntidad){
							$this->aEntidadesConsultaLibroHc[] = array('NIT'=>strval($laEntidad['ENTENT']),'PLAN'=> strval($laEntidad['PLAENT']), 'NOMBRE'=>strval($laEntidad['DSCCON']));
						}
					}
				}
			}
		}

		return count($this->aEntidadesConsultaLibroHc);
	}

	private static function ValidarTipoUsuario($tcTipoUsuario=''){
		$llResultado=false;
		if(!empty($tcTipoUsuario)){
			global $goDb;
			if(isset($goDb)){
				$laTipoUsuario = $goDb
					->count('TABCOD', 'REGISTROS')
					->from('PRMTAB02')
					->where('TABTIP','=','TUS')
					->where('TABCOD','=',$tcTipoUsuario)
					->get('array');
				if(is_array($laTipoUsuario)==true){
					if(count($laTipoUsuario)>0){
						$llResultado=($laTipoUsuario['REGISTROS']>0);
					}
				}
			}
		}
		return $llResultado;
	}

	private static function PerfilesMenu($tcUsuario='', $tcApp='HCW', $tcMenu='MENPRI'){
		$tcUsuario=trim(strtoupper($tcUsuario));
		$laPerfiles=array('APPID'=>array(), 'PERFIL'=>array(), 'MENU'=>array(), 'MENUID'=>array());

		if(!empty($tcUsuario)){
			global $goDb;
			if(isset($goDb)){
				$laCampos = ['A.PERFIL','B.APPID','B.MENU','B.PERTYPE','B.MENUID','C.ESTTMA'];
				$laWhere = [
					'A.APPID'=>'*',
					'A.MENU'=>'*',
					'A.PERTYPE'=>'UPROFILE',
					'A.USUARI'=>$tcUsuario,
					'A.STATE'=>'A',
					'B.PERTYPE'=>'OPTION',
					'B.STATE'=>'A',
					'C.ESTTMA'=>'A',
				];

				if($tcApp!='*'){$laWhere['B.APPID']=$tcApp;}
				if($tcMenu!='*'){$laWhere['B.MENU']=$tcMenu;}

				$laPerfilesUsuario= $goDb
					->select($laCampos)
					->from('SISMENPER A')
					->leftJoin('SISMENPER B', 'A.PERFIL', '=', 'B.PERFIL')
					->leftJoin('SISMENPAR C', "C.CL4TMA = A.PERFIL  AND C.CL3TMA = 'PROFILE' AND C.TIPTMA ='MENPER'")
					->where($laWhere)
					->getAll('array');

				if(is_array($laPerfilesUsuario)==true){
					if(count($laPerfilesUsuario)>0){
						foreach($laPerfilesUsuario as $laOpcion){
							$laKeys=array_keys($laPerfiles);
							foreach($laKeys as $lcKey){
								$lcKey=trim(strtoupper($lcKey));
								if(!empty($lcKey)){
									if(in_array(trim($laOpcion[$lcKey]),$laPerfiles[$lcKey])==false){
										$laPerfiles[$lcKey][]=trim($laOpcion[$lcKey]);
									}
								}
							}
						}
					}
				}
			}
		}
		return $laPerfiles	;
	}

	private static function OpcionesMenu($tcUsuario, &$taOpcionesBarra=[], $taPerfiles=[], $tcApp='HCW', $tcMenu='MENPRI'){
		$tcUsuario=trim(strtoupper($tcUsuario));
		$taOpcionesBarra = array();
		$laMenus=array();

		if(!empty($tcUsuario)){
			global $goDb;
			if(isset($goDb)){
				$laCampos = array('APPID', 'MENU', 'MENUID', 'MENUOF', 'MENUTYPE', 'MENUORD', 'CMD', 'PROMPT', 'TOOLBAR', 'MESSAGE', 'TOOLBAR', 'PICTURE');
				$laDefinicion= $goDb
					->select($laCampos)
					->from('SISMENDEF')
					->where(['APPID'=>$tcApp, 'MENU'=>$tcMenu])
					->orderBy('APPID, MENU, MENUORD, MENUID')
					->getAll('array');

				if(is_array($laDefinicion)==true){
					if(count($laDefinicion)>0){
						foreach($laDefinicion as $laOpcion){
							if(in_array(trim($laOpcion['MENUID']),$taPerfiles)==true){
								$laNivles = str_split(trim($laOpcion['MENUID']),2);
								$laAsignar = &$laMenus;

								for($lnId=0;$lnId<count($laNivles); $lnId++){
									if($lnId==(count($laNivles)-1)){
										$laAsignar[$laNivles[$lnId]]=$laOpcion;
										if(!empty($laOpcion['TOOLBAR'])){
											$laToolBar = explode('-',$laOpcion['TOOLBAR']);
											if(!empty($laToolBar[0]) && !empty($laToolBar[1])){
												if(isset($taOpcionesBarra[$laToolBar[0]])==false){
													$taOpcionesBarra[$laToolBar[0]]=array('NOMBRE'=>trim($laToolBar[1]),'OPCIONES'=>array());
												}
												$taOpcionesBarra[$laToolBar[0]]['OPCIONES'][]=$laOpcion;
											}
										}
									}else{
										$laAsignar=&$laAsignar[$laNivles[$lnId]];
									}
								}
							}
						}
					}
				}
			}
		}
		return $laMenus;
	}

	private static function OpcionesUsuario($taPerfiles){
		$lcOpciones = '[PERFIL][AYUDA]';
		$loAplicacionFunciones = new AplicacionFunciones();

		global $goDb;
		if(isset($goDb)){
			$laCampos = array('MENUID', 'CMD');
			$laDefinicion= $goDb
				->select($laCampos)
				->from('SISMENDEF')
				->where(['APPID'=>'HCW', 'MENU'=>'MENPRI', 'MENUTYPE'=>'bar' ])
				->orderBy('MENUID, MENUTYPE, CMD')
				->getAll('array');

			if(is_array($laDefinicion)==true){
				if(count($laDefinicion)>0){
					foreach($laDefinicion as $laOpcion){
						if(in_array(trim($laOpcion['MENUID']),$taPerfiles)==true){
							$lcOpcion=$laOpcion['CMD'];
							$lcOpcion='['.trim(strpos($lcOpcion,'&')>0?substr($lcOpcion,0,strpos($lcOpcion,'&')):$lcOpcion).']';
							if($loAplicacionFunciones->isSearchStrInStr($lcOpciones,$lcOpcion)==false){
								$lcOpciones.=$lcOpcion;
							}
						}
					}
				}
			}
		}
		return $lcOpciones;
	}


	private function bodegasCentroCosto(){
		$lcDefault='';
		$llDefault=false;

		if(!empty($this->cUsuario)){
			global $goDb;
			if(isset($goDb)){
				// Bodega y Centro de costo por defecto
				$laCampos = ['USRBOD','USRCTR'];
				$laSeguridad = $goDb
					->select($laCampos)
					->from('SEGUSR')
					->where('USRUSR', '=', $this->cUsuario)
					->get('array');

				if(is_array($laSeguridad)==true){
					if(count($laSeguridad)>0){
						$laDatos=array('BODEGA'=>trim($laSeguridad['USRBOD']),'CENTRO'=>trim($laSeguridad['USRCTR']));
						$lcDefault=(empty($laDatos['BODEGA'])==false?$laDatos['BODEGA']:'');

						$this->oBodega = new Bodega($laDatos['BODEGA']);
						$this->oCentroCosto = new CentroCosto($laDatos['CENTRO']);
					}
				}

				// Bodegas autorizadas
				$laCampos = ['AUTCOD'];
				$laBodegas = $goDb
					->select($laCampos)
					->from('SEGAUT')
					->where('AUTTIP', '=', 7)
					->where('AUTUSR', '=', $this->cUsuario)
					->getAll('array');

				if(is_array($laBodegas)==true){
					if(count($laBodegas)>0){
						$this->aBodegas=null;
						foreach($laBodegas as $laBodega){
							$loBodega = new Bodega($laBodega['AUTCOD']);
							if(is_null($loBodega->cEstado)==false){
								if(empty($loBodega->cEstado)==true){
									$llDefault=($lcDefault==$loBodega->cId);
									$this->aBodegas[$loBodega->cId]=array('BODEGA'=>$loBodega,'DEFAULT'=>$llDefault);
								}
							}
						}
					}
				}
			}
		}
	}

	private function propiedades(){
		// Cargando propiedades
		//--.Authen=IIF(.lAuthenDefault==.f.,IIF(EMPTY(.Authen),.cAuthenDefault,.Authen),.cAuthenDefault) && Si el metodo es obligatorio asigna el "Por defecto"
		//--.Algrth=IIF(.lAlgrthDefault==.f.,IIF(EMPTY(.Algrth),.cAlgrthDefault,.Algrth),.Algrth)&& Si el algoritmo es obligatorio asigna el "Por defecto"
		//--.Attmpt=0
		//--.PsswrdByMail=""

		$this->aPropiedades = (new UsuarioPropiedades())->aOpciones;

		global $goDb;
		if (isset($goDb)) {

			// Cuentas medicas
			if(isset($this->aPropiedades['CREUSUCU'])==true){
				$laCampos=array('TIPUSU', 'ACIUSU');
				$laOpciones = $goDb
					->select($laCampos)
					->tabla('AMUSU')
					->where('USUUSU', '=', $this->cUsuario)
					->getAll('array');

				if (is_array($laOpciones)) {
					if (count($laOpciones) > 0) {
						$lcOpcionTipo='';
						$laOpcionesAux=array();
						foreach ($laOpciones as $laOpcion) {
							$lcOpcionTipo=trim($laOpcion['TIPUSU']);
							$laOpcionesAux[]=trim($laOpcion['ACIUSU']);
						}
						$this->aPropiedades['CREUSUCU']['ASSIGNED']=array('TIPO'=>$lcOpcionTipo, 'PERMISOS'=>$laOpcionesAux);
					}
				}
			}

			// Opciones de usuario para trasladar consumos entre pacientes de trasplantes
			if(isset($this->aPropiedades['OPCUSUPA'])==true){
				$laCampos=array('OPCMIT OPCION');
				$laOpciones = $goDb->distinct()
					->select($laCampos)
					->tabla('FACSMIT')
					->where('PGMMIT', '=', 'FRMINGRESO')
					->where('USRMIT', '=', $this->cUsuario)
					->getAll('array');

				if (is_array($laOpciones)) {
					if (count($laOpciones) > 0) {
						$laOpcionesAux=array();
						foreach ($laOpciones as $laOpcion) {
							$laOpcionesAux[]=$laOpcion['OPCION'];
						}
						$this->aPropiedades['OPCUSUPA']['ASSIGNED']=$laOpcionesAux;
					}
				}
			}

			//Usuario retirado revisar!
			if(isset($this->aPropiedades['USURETCL'])==true){
				$this->aPropiedades['OPCUSUPA']['ASSIGNED']=$this->lRetirado;
			}

			//Usuario procedimientos y consultas revisar!
			if(isset($this->aPropiedades['OPTTIPPR'])==true){
				if($this->nTipoUsuarioProcedimientosConsultas>0){
					$this->aPropiedades['OPTTIPPR']['ASSIGNED']=$this->nTipoUsuarioProcedimientosConsultas;
				}
			}

			// Salas de procedimiento autorizadas
			if(isset($this->aPropiedades['CONPERSA'])==true){
				$laCampos=array('PGMMIT OPCION');
				$laOpciones = $goDb->distinct()
					->select($laCampos)
					->tabla('FACSMIT')
					->where('OPCMIT', '=', '9')
					->where('USRMIT', '=', $this->cUsuario)
					->getAll('array');

				if (is_array($laOpciones)) {
					if (count($laOpciones) > 0) {
						$laOpcionesAux=array();
						foreach ($laOpciones as $laOpcion) {
							$laOpcionesAux[]=trim($laOpcion['OPCION']);
						}
						$this->aPropiedades['CONPERSA']['ASSIGNED']=$laOpcionesAux;
					}
				}
			}

			// Usuario Cajero en la clínica
			if(isset($this->aPropiedades['USUCAJCL'])==true){
				$laCampos=array('TCACIU', 'TCACAJ');
				$laOpciones = $goDb
					->select($laCampos)
					->tabla('TESCA')
					->where('TCAUSR', '=', $this->cUsuario)
					->getAll('array');

				if (is_array($laOpciones)) {
					if (count($laOpciones) > 0) {
						$laOpcionesAux=array();
						foreach ($laOpciones as $laOpcion) {
							$laOpcionesAux[trim($laOpcion['TCACIU'])]=trim($laOpcion['TCACAJ']);
						}
						$this->aPropiedades['USUCAJCL']['ASSIGNED']=$laOpcionesAux;
					}
				}
			}


			// Parámetros de usuario
			$laOpciones = $goDb
				->from('SISMENSEG')
				->where('USUARI', '=', $this->cUsuario)
				->get('array');
			if (is_array($laOpciones)) {
				if (count($laOpciones) > 0) {
					foreach(['LOCKED','PSSOCH','PSSNCH','PSSNEX','ALPHIL','LGGNEX'] as $lcOpcion){
						if(isset($this->aPropiedades['US'.trim($lcOpcion)])==true){
							if(intval($laOpciones[$lcOpcion])<>0){
								$this->aPropiedades['US'.trim($lcOpcion)]['SISMENSEG']=true;
								$this->aPropiedades['US'.trim($lcOpcion)]['ASSIGNED']=$laOpciones[$lcOpcion];
							}
						}
					}
				}
			}

			// Opciones en tTabMae (Por continuidad)
			$laCampos=array('CL2TMA', 'CL3TMA');
			$laOpciones = $goDb
				->select($laCampos)
				->tabla('TABMAE')
				->where('TIPTMA', '=', 'OPCUSU')
				->where('ESTTMA', '=', '')
				->where('CL1TMA', '=', $this->cUsuario)
				->getAll('array');

			if (is_array($laOpciones)) {
				if (count($laOpciones) > 0) {
					$laOpcionesAux=array();
					foreach ($laOpciones as $laOpcion) {
						if(isset($this->aPropiedades['OP'.trim($laOpcion['CL2TMA'])])==true){
							$this->aPropiedades['OP'.trim($laOpcion['CL2TMA'])]['TABMAE']=true;
							$this->aPropiedades['OP'.trim($laOpcion['CL2TMA'])]['ASSIGNED']=trim($laOpcion['CL3TMA']);
						}
					}
				}
			}

			// Permisos de usuario tabla CUESAU, Se crea por continuidad de permisos para opciones de métodos no invasivos. Especilidad 124 - Cardiolgia no invasiva
			if(isset($this->aPropiedades['OPCUESAU'])==true){
				$laOpciones = $goDb
					->tabla('CUESAU')
					->where('USUCEA', '=', $this->cUsuario)
					->getAll('array');

				if (is_array($laOpciones)) {
					if (count($laOpciones) > 0) {
						foreach ($laOpciones as $laOpcion) {
							$laOpcionesAux=array();
							for($lnCampo=1; $lnCampo<=8; $lnCampo++){
								$lcCampo='CL'.strval($lnCampo).'CEA';
								if(trim($laOpcion[$lcCampo])=='1'){
									$laOpcionesAux[$lnCampo-1]=$lcCampo;
								}
							}
							$this->aPropiedades['OPCUESAU']['ASSIGNED']=['ESPECIALIDAD'=>trim($laOpcion['ESPCEA']),'OPCIONES'=>$laOpcionesAux];
						}
					}
				}
			}


			// Especialidades permitidas para entrega de resultados
			if(isset($this->aPropiedades['OPPROACT'])==true){
				$laCampos=array('DE2TMA ESPECIALIDADES');
				$laOpciones = $goDb
					->select($laCampos)
					->tabla('TABMAEL01')
					->where('TIPTMA', '=', 'PROACT')
					->where('CL1TMA', '=', 'USUESP')
					->where('ESTTMA', '=', '')
					->where('CL2TMA', '=', $this->cUsuario)
					->getAll('array');

				if (is_array($laOpciones)) {
					if (count($laOpciones) > 0) {
						foreach ($laOpciones as $laOpcion) {
							$this->aPropiedades['OPPROACT']['ASSIGNED']=trim($laOpcion['ESPECIALIDADES']);
						}
					}
				}
			}

			// Opciones de usuario para consulta de entidades
			if(isset($this->aPropiedades['OPEAPCLH'])==true){
				$laCampos=array('ENTENT', 'PLAENT');
				$laOpciones = $goDb
					->select($laCampos)
					->tabla('SISMENENT')
					->where('ESTENT', '=', 'A')
					->where('USUARI', '=', $this->cUsuario)
					->getAll('array');

				if (is_array($laOpciones)) {
					if (count($laOpciones) > 0) {
						foreach ($laOpciones as $laOpcion) {
							if(is_null($laOpcion['ENTENT'])==false){
								if(intval($laOpcion['ENTENT'])==0 && trim($laOpcion['PLAENT'])=='*'){

									$llAdd=true;
									if(is_array($this->aPropiedades['OPEAPCLH']['ASSIGNED'])){
										if(in_array('*',$this->aPropiedades['OPEAPCLH']['ASSIGNED'])==true){
											$llAdd=false;
										}
									}
									if($llAdd==true){
										$this->aPropiedades['OPEAPCLH']['ASSIGNED'][]='*';
									}
								}else{
									$this->aPropiedades['OPEAPCLH']['ASSIGNED'][]=trim(strval($laOpcion['ENTENT']));
								}
							}
						}
					}
				}
			}

			// Opciones de usuario para Exportar Archivos AS400
			if(isset($this->aPropiedades['OPEXPFIL'])==true){
				$laCampos=array('OP2TMA', 'DE2TMA || OP5TMA AS PERMISOS');
				$laOpciones = $goDb
					->select($laCampos)
					->tabla('TABMAE')
					->where('TIPTMA', '=', 'EXPFILES')
					->where('CL1TMA', '=', 'USUARIOS')
					->where('CL2TMA', '=', $this->cUsuario)
					->where('CL3TMA', '<>', '')
					->getAll('array');

				if (is_array($laOpciones)) {
					if (count($laOpciones) > 0) {
						foreach ($laOpciones as $laOpcion) {
							$this->aPropiedades['OPEXPFIL']['ASSIGNED'][]=array('TIPO'=>(empty(trim($laOpcion['OP2TMA']))?'X~':'ADMIN~'),'PERMISOS'=>trim($laOpcion['PERMISOS']));
						}
					}
				}
			}
/*

			// Propiedades de usuario desde opciones
			*.AlpPrF=.cUSALPPRF
			*.AlpPrS=.cUSALPPRS

			// FACTURADOR AS/400
			.cOpcionesFacturacion='NOFACTURA,0,N'
			.cOpcionesFacturacionUsuario=''
			.cOpcionesFacturacionClave=''
			IF loListView.FindNode('OPCUSUFC')==.t.
				lcSql="SELECT CL1TMA,CL2TMA,DE1TMA,DE2TMA,OP1TMA FROM TABMAEL01 WHERE TIPTMA='FACSMIT' ORDER BY CL1TMA, CL2TMA"
				lnResultado = fSqlexec(oVar.nConHan,lcSql,'curTabMaeL01')
				lnResultado = IIF(lnResultado==1, 1, oVar.cMenErr(1466, lcSql, LINENO(1), PROGRAM()))
				IF lnResultado==1
					IF USED('curTabMaeL01')
						SELECT 'curTabMaeL01'
						SCAN
							.cOpcionesFacturacion=.cOpcionesFacturacion+;
												  IIF(EMPTY(.cOpcionesFacturacion),'',';')+;
												  ALLTRIM(curTabMaeL01.CL1TMA)+','+;
												  ALLTRIM(curTabMaeL01.CL2TMA)+','+;
												  ALLTRIM(curTabMaeL01.OP1TMA)
						ENDSCAN
						USE IN SELECT('curTabMaeL01')
					ENDIF

					FOR lnOpcion=1 TO GETWORDCOUNT(.cOpcionesFacturacion,';')
						lcOpcion=ALLTRIM(GETWORDNUM(.cOpcionesFacturacion,lnOpcion,';'))
						lcOpcionPrograma=ALLTRIM(UPPER(GETWORDNUM(lcOpcion,1,',')))
						lcOpcionValor=ALLTRIM(UPPER(GETWORDNUM(lcOpcion,2,',')))
						lcOpcionKey=lcOpcionPrograma+','+lcOpcionValor

						TEXT TO lcSql TEXTMERGE NOSHOW PRETEXT 7+8
							SELECT CLAMIT
								FROM FACSMIT
									WHERE USRMIT='<<ALLTRIM(.Usuari)>>' AND
										  PGMMIT='<<lcOpcionPrograma>>' AND
										  OPCMIT='<<lcOpcionValor>>'
						ENDTEXT
						lnResultado = fSqlexec(oVar.nConHan,lcSql,'curTabMaeL01Aux')
						lnResultado = IIF(lnResultado==1, 1, oVar.cMenErr(1466, lcSql, LINENO(1), PROGRAM()))
						IF lnResultado==1
							IF USED('curTabMaeL01Aux')
								SELECT 'curTabMaeL01Aux'
								IF !EOF() AND !BOF()
									.cOpcionesFacturacionClave=IIF(EMPTY(.cOpcionesFacturacionClave),ALLTRIM(curTabMaeL01Aux.CLAMIT),.cOpcionesFacturacionClave)
									.cOpcionesFacturacionUsuario=.cOpcionesFacturacionUsuario+;
																  IIF(EMPTY(.cOpcionesFacturacionUsuario),'',';')+;
																  ALLTRIM(lcOpcionPrograma)+','+;
																  ALLTRIM(lcOpcionValor)+','+;
																  PADL(ALLTRIM(curTabMaeL01Aux.CLAMIT),8,' ')
								ENDIF
								USE IN SELECT('curTabMaeL01Aux')
							ENDIF
						ENDIF
					ENDFOR
				ENDIF
				loListView.ListItems('OPCUSUFC').Checked=!EMPTY(.cOpcionesFacturacionUsuario)
			ENDIF
*/

			// Permisos para egreso hospitalario
			if(isset($this->aPropiedades['OPCGPPEH'])==true){
				$laCampos=array('CONPRI ID');
				$laOpciones = $goDb->distinct()
					->select($laCampos)
					->tabla('PARRIP')
					->where('INDPRI', '=', 1981)
					->where('DESPRI', '=', $this->cUsuario)
					->getAll('array');

				if (is_array($laOpciones)) {
					if (count($laOpciones) > 0) {
						foreach ($laOpciones as $laOpcion) {
							$laOpcionesNode = $goDb
								->select('DESPRI')
								->tabla('PARRIP')
								->where('CONPRI', '=', $laOpcion['ID'])
								->where('INDPRI', '=', 1981)
								->in('TRIM(OP5PRI)', ['AREA', 'OPCIONES'])
								->orderBy('CONPRI, INDPRI, CLIPRI, OP5PRI')
								->getAll('array');

							if (is_array($laOpcionesNode)) {
								if (count($laOpcionesNode) > 0) {
									foreach ($laOpcionesNode as $laOpcionNode) {
										$this->aPropiedades['OPCGPPEH']['ASSIGNED'][]=trim($laOpcionNode['DESPRI']);
									}
								}
							}
						}
					}
				}
			}


			$laPropiedadesAux = array(
				'IMPFACIN'=>array('TABLE'=>'PRMTAB02',  'FIELD'=>NULL, 'COUNT'=>'TABTIP', 'TABTIP'=>'IFA', 'TABCOD'=>$this->cUsuario, 'NAME' => 'Indica que el usuario puede imprimir facturas internacionales. Parámetro (IFA)'),
				'EXPPDFEV'=>array('TABLE'=>'TABMAEL01', 'FIELD'=>NULL, 'COUNT'=>'TIPTMA', 'TIPTMA'=>'PDFUSU', 'CL1TMA'=>'EV0050', 'CL2TMA'=>'USUARIOS', 'CL3TMA'=>$this->cUsuario, 'ESTTMA'=>'', 'NAME' => 'Puede exportar a PDF evoluciones de pacientes'),
				'INFGERHO'=>array('TABLE'=>'TABMAEL01', 'FIELD'=>NULL, 'COUNT'=>'TIPTMA', 'TIPTMA'=>'INFGER', 'CL1TMA'=>'INFGERHO', 'CL2TMA'=>'', 'CL3TMA'=>$this->cUsuario, 'ESTTMA'=>'', 'NAME' => 'Informes de gerencia'),
				'INFGERSE'=>array('TABLE'=>'TABMAEL01', 'FIELD'=>NULL, 'COUNT'=>'TIPTMA', 'TIPTMA'=>'INFGER', 'CL1TMA'=>'INFGERSE', 'CL2TMA'=>'', 'CL3TMA'=>$this->cUsuario, 'ESTTMA'=>'', 'NAME' => 'Informes de gerencia'),
				'LHCEXPDF'=>array('TABLE'=>'TABMAEL01', 'FIELD'=>NULL, 'COUNT'=>'TIPTMA', 'TIPTMA'=>'LIBROHC', 'CL1TMA'=>'PDFUSU', 'CL2TMA'=>'USUARIOS', 'CL3TMA'=>$this->cUsuario, 'ESTTMA'=>'', 'NAME' => 'Puede exportar a PDF desde el Libro de HC'),
				'OPAGFAUT'=>array('TABLE'=>'TABMAEL01', 'FIELD'=>NULL, 'COUNT'=>'TIPTMA', 'TIPTMA'=>'PROACT', 'CL1TMA'=>'AGFAUT', 'CL2TMA'=>$this->cUsuario, 'ESTTMA'=>'', 'NAME' => 'Usuarios autorizados para cancelar orden, con tarea en Agility'),
				'OPCDATPA'=>array('TABLE'=>'TABMAEL01', 'FIELD'=>NULL, 'COUNT'=>'TIPTMA', 'TIPTMA'=>'DATPAC', 'CL1TMA'=>'DATOS', 'CL2TMA'=>$this->cUsuario, 'ESTTMA'=>'', 'NAME' => 'Modificar datos del paciente'),
				'OPDEVURG'=>array('TABLE'=>'TABMAEL01', 'FIELD'=>NULL, 'COUNT'=>'TIPTMA', 'TIPTMA'=>'DEVURG', 'CL1TMA'=>$this->cUsuario, 'ESTTMA'=>'', 'NAME' => 'Permiso de hacer reversión de la salida médica en urgencia'),
				'OPFRPURG'=>array('TABLE'=>'TABMAEL01', 'FIELD'=>NULL, 'COUNT'=>'TIPTMA', 'TIPTMA'=>'FRPURG', 'CL1TMA'=>$this->cUsuario, 'ESTTMA'=>'', 'NAME' => 'Opción para acceso a HC de remisión rol administrativo'),
				'OPIMPAGE'=>array('TABLE'=>'TABMAEL01', 'FIELD'=>NULL, 'COUNT'=>'TIPTMA', 'TIPTMA'=>'IMPAGE', 'CL1TMA'=>'PERMISOS', 'CL2TMA'=>'USUARIOS', 'CL3TMA'=>$this->cUsuario, 'NAME' => 'Indica que el usuario puede imprimir asignaciones de citas.'),
				'OPCENUSU'=>array('TABLE'=>'TABMAEL01', 'FIELD'=>'DE2TMA', 'COUNT'=>NULL, 'TIPTMA'=>'CENUSU', 'CL1TMA'=>'PERMISOS', 'CL2TMA'=>'USUARIOS', 'CL3TMA'=>$this->cUsuario, 'ESTTMA'=>'', 'NAME' => 'Opciones de usuario para censo y bitacora de urgencias'),
				'OPNCENSE'=>array('TABLE'=>'TABMAEL01', 'FIELD'=>'DE2TMA', 'COUNT'=>NULL, 'TIPTMA'=>'CONSUMOS', 'CL1TMA'=>'OPCUSU', 'CL2TMA'=>'PERMISOS', 'CL3TMA'=>$this->cUsuario, 'ESTTMA'=>'', 'NAME' => 'Opciones de usuario, tipo de consumos cambiar centro de servicio'),
				'OPNPSMPR'=>array('TABLE'=>'TABMAEL01', 'FIELD'=>'DE2TMA', 'COUNT'=>NULL, 'TIPTMA'=>'NOPOS', 'CL1TMA'=>'OPCUSU', 'CL2TMA'=>'PERMISOS', 'CL3TMA'=>$this->cUsuario, 'ESTTMA'=>'', 'NAME' => 'Opciones de usuario para NoPOS y MiPres'),
				'OPORDHOS'=>array('TABLE'=>'TABMAEL01', 'FIELD'=>'DE2TMA', 'COUNT'=>NULL, 'TIPTMA'=>'ORDHOS', 'CL1TMA'=>'PERMISOS', 'CL2TMA'=>'USUARIOS', 'CL3TMA'=>$this->cUsuario, 'ESTTMA'=>'', 'NAME' => 'Opciones de usuario para Ordenes hospitalarias modulo de usuarios'),
			);

			foreach($laPropiedadesAux as $lcPropiedadAux => $laPropiedadAux){
				if(isset($this->aPropiedades[$lcPropiedadAux])==true){

					$llPropiedadesAux = false;
					if(!empty($laPropiedadAux['FIELD'])==true){
						$goDb->select($laPropiedadAux['FIELD']);
						$llPropiedadesAux = true;
					}else if(!empty($laPropiedadAux['COUNT'])){
						$goDb->count($laPropiedadAux['COUNT'], 'REGISTROS');
						$llPropiedadesAux = true;
					}

					if($llPropiedadesAux == true){
						$goDb->tabla($laPropiedadAux['TABLE']);
						foreach($laPropiedadAux as $lcKey => $lcValue){ if(in_array($lcKey,['NAME','TABLE','FIELD', 'COUNT'])==false){ $goDb->where($lcKey, '=', $lcValue); }}
						$laOpciones = $goDb->getAll('array');

						if (is_array($laOpciones)) {
							if (count($laOpciones) > 0) {
								foreach ($laOpciones as $laOpcion) {
									if(isset($laOpcion['REGISTROS'])){
										$this->aPropiedades[$lcPropiedadAux]['ASSIGNED']=$laOpcion['REGISTROS'];
									}else{
										if(isset($laOpcion[$laPropiedadAux['FIELD']])){
											$this->aPropiedades[$lcPropiedadAux]['ASSIGNED']=trim($laOpcion[$laPropiedadAux['FIELD']]);
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	private function isFountValueArrayMulti($taLista=array(), $tcValor='', $tcCampoCodigo='', $tcCampoDescripcion='', $tlReturnDescripcion=false){
		if(is_array($taLista)){
			if(count($taLista)>0){
				if(!empty($tcValor) && !empty($tcCampoCodigo) && !empty($tcCampoDescripcion)){
					$lnKey=intval(array_search($tcValor,array_column($taLista,$tcCampoCodigo)));

					if(isset($taLista[$lnKey])){
						if(is_object($taLista)==true){
							if($tlReturnDescripcion==true){
								if(isset($taLista[$lnKey]->$$tcCampoDescripcion)==true){
									return $taLista[$lnKey]->$$tcCampoDescripcion;
								}else{
									return '?';
								}
							}else{
								return true;
							}
						}else{
							if($tlReturnDescripcion==true){
								if(isset($taLista[$lnKey][$tcCampoDescripcion])){
									return $taLista[$lnKey][$tcCampoDescripcion];
								}else{
									return '?';
								}
							}else{
								return true;
							}
						}
					}
				}
			}
		}
		return false;
	}

	private function getClientDeviceType(){
		$loMobileDetect = new AplicacionMobileDetect;
		$lcDeviceType = ($loMobileDetect->isMobile() ? ($loMobileDetect->isTablet() ? 'tablet' : 'phone') : 'computer');
		return $lcDeviceType;
	}


	public function encriptar($tcValor) {
		return base64_encode(openssl_encrypt(strval($tcValor), $this->cEncriptadoMetodo, $this->cEncriptadoClave, false, $this->cEncriptadoIV));
	}

	public function desencriptar($tcValor){
		$tcValor = base64_decode(strval($tcValor));
		return openssl_decrypt($tcValor, $this->cEncriptadoMetodo, $this->cEncriptadoClave, false, $this->cEncriptadoIV);
	}

	public function validarCambioEspecialidad($tnTipoUsuario=0, $tcEspecialidad=''){
		$llValido = false;
		$tnTipoUsuario = intval($tnTipoUsuario);
		$tcEspecialidad = trim(strval($tcEspecialidad));

		if($tnTipoUsuario>0 && !empty($tcEspecialidad)){
			$laTiposUsuario = array_column($_SESSION[HCW_NAME]->oUsuario->getEspecialidades(),'TIPO');
			$laEspecialidadesUsuario = array_column($_SESSION[HCW_NAME]->oUsuario->getEspecialidades(),'ESPECIALIDAD');

			if($this->isFountValueArrayMulti($laTiposUsuario, $tnTipoUsuario, 'nId', 'cNombre')==true){
				if($this->isFountValueArrayMulti($laEspecialidadesUsuario, $tcEspecialidad, 'cId', 'cNombre')==true){
					$llValido = true;
				}
			}
		}

		return $llValido;
	}

	public function buscarMails($tcUsuario=''){

		$tcUsuario=trim(strtoupper(strval($tcUsuario)));
		$lcMails='';

		global $goDb;

		if (!empty($tcUsuario)){

			$laEmails = $goDb
							->select(['CL2TMA USUARIO', 'ESTTMA ESTADO', 'DE1TMA EMAIL'])
							->tabla('TABMAE')
							->where('TIPTMA', 'MAIMED')
							->where('ESTTMA ','<>', 'I')
							->where('CL2TMA', $tcUsuario)
							->getAll('array');

			if (is_array($laEmails)) {
				if (count($laEmails) > 0) {
					foreach ($laEmails as $laEmail) {
						if(isset($laEmail['EMAIL'])){
							if(!empty($laEmail['EMAIL'])){
								$lcMails .= (empty($lcMails)?'':',').trim($laEmail['EMAIL']);
							}
						}
					}
				}
			}
		}

		return $lcMails;
	}

	public function fGenerarContrasena($tcKey1 = '', $tcKey2 = '', $tcKey3 = '', $tnType = 0){
		$tnType = intval($tnType);
		$tnType = ($tnType>=0 && $tnType<4 ? $tnType : 0);

		$permitted_chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

		$laKeys['a']=(is_string($tcKey1)==true ? trim(strtoupper($tcKey1)): substr(str_shuffle($permitted_chars), 0, 9));
		$laKeys['b']=(is_string($tcKey2)==true ? trim(strtoupper($tcKey2)): substr(str_shuffle($permitted_chars), 0, 9));
		$laKeys['c']=(is_string($tcKey3)==true ? strval(intval($tcKey3))  : substr(str_shuffle($permitted_chars), 0, 9));
		$lcKeyNew="";


		switch ($tnType) {
			case 1: // ALEATORIO Genera la contraseña a partir de valores aleatorios entre numeros y letras en mayusculas
			case 2: // SE LE SOLICITA AL USUARIO Le solicita al usuario la nueva contraseña
				$lcKeyNew = substr(substr(str_shuffle($permitted_chars), 0, 9),-3);

				for($lnKey = 1; $lnKey <= 6; $lnKey++) {
					$lcKey = ($lnKey%2==0 ? chr(rand(65,90)) : chr(rand(48, 57)));
					$lcKeyNew .= $lcKey;
				}
				$lcKeyNew = str_replace(['O'],['0'],trim(strtoupper($lcKeyNew)));
				break;

			case 3: // SE GENERA UNA CONTRASEÑA FIJA POR DIA Con los Key se genera una fija
				$lcKeyNew = 'T';
				$lcKeyNew.= trim(str_pad(substr($laKeys['a'],0,1),1,'A',STR_PAD_LEFT));
				$lcKeyNew.= trim(str_pad(substr($laKeys['b'],0,1),1,'B',STR_PAD_LEFT));
				$lcKeyNew.= trim(str_pad(substr($laKeys['c'],0,1),1,'C',STR_PAD_LEFT));
				$lcKeyNew.= trim(str_pad(strval($this->getCRC($laKeys['a'] . $laKeys['b'] . $laKeys['c'] . date('Ymd'))),6,'9'));
				$lcKeyNew = substr($lcKeyNew,0,9);
				$lcKeyNew = strtoupper(str_replace(['0','1','5','6','7','8','9','O','I'], ['X','U','P','M','V','Z','S','K','F'], $lcKeyNew));
				break;

			default: // ALEATORIO POR LAS LLAVES Segun las llaves enviadas se genera una contraseña aleatoria
				for ($lnKey=1; count($laKeys); $lnKey++){
					$lcKey=chr(rand(65,90));
					$lcKeyNew.=$lcKey;
				}
		}

		return $lcKeyNew;
	}


	public function  fEnviarContrasena($tcEmail='', $tcNombre='', $tcUsuario='', $tcContrasena='', $tnRestablece=''){
		$tcEmail=trim(strval($tcEmail));
		$tcNombre=trim(strtoupper(strval($tcNombre)));
		$tcUsuario=trim(strtoupper(strval($tcUsuario)));
		$tcContrasena=trim(strval($tcContrasena));
		$tnRestablece=intval($tnRestablece);
		$llResultado = false;

		$lcMailTitle = 'Acceso a Historia Clinica - Nueva clave de acceso';

		$lcMailBody = file_get_contents(__DIR__ .'/../../pifs/contrasena olvido.htm', FALSE);

		$lcMailBody = str_replace('[[Usuario]]',$tcUsuario,$lcMailBody);
		$lcMailBody = str_replace('[[Contrasena]]',$tcContrasena,$lcMailBody);
		$lcMailBody = str_replace('[[Nombre]]',$tcNombre,$lcMailBody);
		$lcMailBody = str_replace('[[IP]]',$this->cIP,$lcMailBody);
		$lcMailBody = str_replace('[[Maquina]]',"WEB - ".$this->cIP,$lcMailBody);


		$laSettings=array('tcServer' => $this->cMailServer,
						'tnPort' => $this->nMailPort,
						'tcUser' => $this->cMailUser,
						'tcPass' => $this->cMailPass,
						'tcFrom' => $this->cMailFrom,
						'tcTO' => $tcEmail,
						'tcCC' => '',
						'tcBCC' => ($tcEmail==trim(strtolower($this->cEmail))?'':trim(strtolower($this->cEmail))),
						'tcSubject' => $lcMailTitle,
						'tcBody' => $lcMailBody,
						'tnAuthMode' => 0,
						'tnPriority' => 0,
						'tnImportance' => 0,
						'tnDisposition' => 0,
						'tcOrganization' => 'Fundacion Clincia Shaio',
						'tcKeywords' => 'CLAVE,PORTAL,PACIENTES',
						'tcDescription' => 'Recuperar clave de acceso para el Portal Pacientes de la FCS');


		$loClient = new \nusoap_client($this->cMailUrlWsdl,'wsdl');
		$lcError = $loClient->getError();

		if (!empty($lcError)) {
			$lcResult = "constructor-error";
		}else{
			$lcResult = (trim(AplicacionFunciones::getInbetweenStrings($loClient->call('SendMail',$laSettings),"<RESULT>","</RESULT>"))=="1"?"send":"no-send");
			if($lcResult=="no-send"){
				error_log($loClient->getError());
			}else{
				$llResultado = true;
			}
		}

		return $llResultado;
	}

	public function fForgotIntentos($tlSuma=false){
		$this->nForgotIntentos+= ($tlSuma==true ? 1 : 0);
		$llBloqueoIntetos = false;
		if($this->nForgotIntentos >= $this->nForgotIntentosMaximosPermitidos){

			global $goDb;


			$this->nForgotIntentosBloqueoMinutos+=($this->nForgotIntentos-$this->nForgotIntentosMaximosPermitidos>0?1:0);

			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$this->cForgotIntentosBloqueoFecha = (empty($this->cForgotIntentosBloqueoFecha)?$ltAhora->format("Y-m-d H:i:s"):$this->cForgotIntentosBloqueoFecha);
			$ltBloqueo = new \DateTime($this->cForgotIntentosBloqueoFecha);
			$loBloqueoDiff = $ltBloqueo->diff($ltAhora);
			$lnBloqueoTranscurrido = intval((($loBloqueoDiff->days*24)*60)+($loBloqueoDiff->i));

			if($lnBloqueoTranscurrido>=$this->nForgotIntentosBloqueoMinutos){
				$this->nForgotIntentos = 1;
				$this->cForgotIntentosBloqueoFecha = '';
			}else{
				$llBloqueoIntetos = true;
			}
		}

		return $llBloqueoIntetos;
	}

	public function fForgotIntentosBloqueoMensaje(){
		return 'Tiene '.$this->nForgotIntentos.' intentos de '.$this->nForgotIntentosMaximosPermitidos.' permitidos, por seguridad la sesión actual de su navegador fue bloqueada por '.$this->nForgotIntentosBloqueoMinutos.' minuto(s) a partir de '.$this->cForgotIntentosBloqueoFecha.'.';
	}

	public function fCambiarContrasena($taEntrada=array(), &$tcStatus=''){
		$llResultado = false;
		$tcStatus = $tcStatus;
		$lcKeyChngPsswdFor = strtolower('0'.md5('CHGPSSW-'.date('YmdH')).'x');
		$lcKeyChngPsswdCurrent = strtolower('1'.md5('PASSWORC-'.date('YmdH')).'y');
		$lcKeyChngPsswdNew = strtolower('2'.md5('PASSWORN-'.date('YmdH')).'z');
		$lcKeyChngPsswdNewConfirm = strtolower('3'.md5('PASSWORK-'.date('YmdH')).'w');

		$laNoPermitidos = ["'",'"'];
		$laNombres = explode(" ", $this->getNombreCompleto());
		foreach($laNombres as $lcNombre){
			if(!empty($lcNombre)){
				$laNoPermitidos[] = $lcNombre;
			}
		}

		global $goDb;
		$loAplicacionFunciones = new AplicacionFunciones();

		if(isset($taEntrada[$lcKeyChngPsswdCurrent]) && isset($taEntrada[$lcKeyChngPsswdNew]) && isset($taEntrada[$lcKeyChngPsswdNewConfirm])){
			if(!empty($taEntrada[$lcKeyChngPsswdCurrent]) && !empty($taEntrada[$lcKeyChngPsswdNew]) && !empty($taEntrada[$lcKeyChngPsswdNewConfirm])){
				$taEntrada[$lcKeyChngPsswdCurrent] = trim($taEntrada[$lcKeyChngPsswdCurrent]);
				$taEntrada[$lcKeyChngPsswdNew] = trim($taEntrada[$lcKeyChngPsswdNew]);
				$taEntrada[$lcKeyChngPsswdNewConfirm] = trim($taEntrada[$lcKeyChngPsswdNewConfirm]);

				if(trim($taEntrada[$lcKeyChngPsswdNew])==trim($taEntrada[$lcKeyChngPsswdNewConfirm])){
					if(trim(strtolower($this->cUsuario))!==trim(strtolower($taEntrada[$lcKeyChngPsswdNew]))){
						$lnNoPermitidos = 0;
						foreach($laNoPermitidos as $lcNoPermitido){
							if($loAplicacionFunciones->isSearchStrInStr($taEntrada[$lcKeyChngPsswdNew], $lcNoPermitido)==true){
								$lnNoPermitidos+=1;
								$tcStatus .= (empty($tcStatus)?"":",") . "No se permite el uso de [".$lcNoPermitido."] en la contraseña.";
							}
						}

						$laChars = ['M'=>0, 'm'=>0, 'N'=>0, 'e'=>0];
						for ($i = 0; $i< strlen($taEntrada[$lcKeyChngPsswdNew]); $i++) {
							$caracter = ord(substr($taEntrada[$lcKeyChngPsswdNew],$i,1));
							if($caracter>=65 && $caracter<=90){
								$laChars['M']+=1;
							}else if($caracter>=97 && $caracter<=122){
								$laChars['m']+=1;
							}else if($caracter>=48 && $caracter<=57){
								$laChars['N']+=1;
							}else{
								$laChars['e']+=1;
							}
						}
						if($laChars['M'] >= $this->nPswdMinMayusculas){
							if($laChars['m'] >= $this->nPswdMinMinusculas){
								if($laChars['N'] >= $this->nPswdMinNumeros){
									if($laChars['e'] >= $this->nPswdMinEspeciales){

										if($lnNoPermitidos == 0){
											if(strlen(trim($taEntrada[$lcKeyChngPsswdNew]))>=$this->nPswdLargoMinimo && strlen(trim($taEntrada[$lcKeyChngPsswdNew]))<=$this->nPswdLargoMaximo){
												if(trim(strtolower($taEntrada[$lcKeyChngPsswdCurrent]))!==trim(strtolower($taEntrada[$lcKeyChngPsswdNew]))){
													$laUsuario = $goDb->select(['PSSOL3', 'PSSOL2', 'PSSOL1', 'PSSWRD'])->tabla('SISMENSEG')->where('USUARI', $this->cUsuario)->get('array');

													//Validando credenciales
													if(is_array($laUsuario)){
														if(count($laUsuario)>0){
															$lcPsswrd=trim(strtoupper($laUsuario['PSSWRD']));
															$lcAlgrthDefault = trim($goDb->ObtenerTabMae1('DE2TMA', 'SISMENST', "CL1TMA='01010201'", null, "CRC"));
															$lcPsswrdActual=$this->fCodificarContrasena($lcAlgrthDefault,$taEntrada[$lcKeyChngPsswdCurrent]);
															$lcPsswrdNueva=$this->fCodificarContrasena($lcAlgrthDefault,$taEntrada[$lcKeyChngPsswdNew]);

															$llClaveAnterior = false;
															if(trim(strtoupper($this->cPswdClavesAnteriores))=="NO"){
																$llClaveAnterior = (trim(strtoupper($lcPsswrdNueva)) == trim(strtoupper($laUsuario['PSSWRD'])) || trim(strtoupper($lcPsswrdNueva)) == trim(strtoupper($laUsuario['PSSOL1'])) || trim(strtoupper($lcPsswrdNueva)) == trim(strtoupper($laUsuario['PSSOL2'])) || trim(strtoupper($lcPsswrdNueva)) == trim(strtoupper($laUsuario['PSSOL3'])));
															}

															if($llClaveAnterior==false){
																if($lcPsswrd==$lcPsswrdActual){
																	// Actualizando contraseña y estado
																	$lnTempMethodGeneratePassword = intval($goDb->ObtenerTabMae1('DE2TMA', 'SISMENST', "CL1TMA='01010401'", null, "0")); // Método para generar contraseña
																	if($lnTempMethodGeneratePassword==3){
																		$laUsuarioRegistro = $goDb->select(['NNOMED', 'NOMMED', 'REGMED', 'ESTRGM'])
																								  ->tabla('riargmn')
																								  ->where('USUARI', $this->cUsuario)
																								  ->get('array');

																		$lcTempPsswrdDecrypt = $this->fGenerarContrasena($laUsuarioRegistro['NOMMED'],$laUsuarioRegistro['NNOMED'],$laUsuarioRegistro['REGMED'], $lnTempMethodGeneratePassword);
																		$lcTempPsswrd=$this->fCodificarContrasena($lcAlgrthDefault,$lcTempPsswrdDecrypt);

																		if(trim(strtoupper($laUsuario['PSSWRD']))==trim(strtoupper($lcTempPsswrd))){
																			$laUsuario['PSSWRD'] = $laUsuario['PSSOL1'];
																			$laUsuario['PSSOL1'] = $laUsuario['PSSOL2'];
																			$laUsuario['PSSOL2'] = $laUsuario['PSSOL3'];
																		}
																	}


																	// Actualizando la nueva clave
																	$laDatos = ['PSSOL3' => $laUsuario['PSSOL2'],
																				'PSSOL2' => $laUsuario['PSSOL1'],
																				'PSSOL1' => $laUsuario['PSSWRD'],
																				'PSSWRD' => $lcPsswrdNueva,
																				'ALGRTH' => $lcAlgrthDefault,
																				'PSSOCH' => 0,
																				];

																	if($goDb->tabla('SISMENSEG')->where('USUARI',$this->cUsuario)->actualizar($laDatos)==true){
																		$this->cPsswrd = $lcPsswrdNueva;
																		$this->cAlgrth = trim(strtoupper($lcAlgrthDefault));
																		$this->lPssOch=false;
																		$this->lCambiarClave=false;

																		// Registrando en la bitacora el cambio de contraseña
																		$this->oBitacora->insertarEntrada($this->cUsuario,"CAMBIO-CLAVE-USUARIO",0,"","Se cambio la contraseña de acceso a la aplicación desde ".$this->oBitacora->getInstancia(),$this->cUsuario);

																		$llResultado = true;
																	}else{
																		$tcStatus = "No se cambio la contraseña";
																	}
																}else{
																	$tcStatus = "Atención: La contraseña que se ingreso como actual no es correcta";
																}
															}else{
																$tcStatus = "Atención: La clave fue usada previamente.";
															}
														}else{
															$tcStatus = "Atención: No se encontró el usuario";
														}
													}else{
														$tcStatus = "Atención: No se encontró el usuario";
													}
												}else{
													$tcStatus = "La contraseña actual no puede ser igual a la nueva";
												}
											}else{
												$tcStatus = sprintf("La contraseña debe tener entre %s y %s caracteres",$this->nPswdLargoMinimo,$this->nPswdLargoMaximo);
											}
										}

									}else{
										$tcStatus = sprintf("La contraseña debe tener mínimo %s carácter(es) especial(es)", $this->nPswdMinEspeciales);
									}
								}else{
									$tcStatus = sprintf("La contraseña debe tener mínimo %s digito(s)", $this->nPswdMinNumeros);
								}
							}else{
								$tcStatus = sprintf("La contraseña debe tener mínimo %s minúscula(s)", $this->nPswdMinMinusculas);
							}
						}else{
							$tcStatus = sprintf("La contraseña debe tener mínimo %s mayúscula(s)", $this->nPswdMinMayusculas);
						}
					}else{
						$tcStatus = "La Nueva clave es igual al usuario";
					}
				}else{
					$tcStatus = "La nueva clave y su confirmación son distintas";
				}
			}else{
				$tcStatus = "Atención: Información de entrada en blanco";
			}
		}else{
			$tcStatus = "Atención: Información de entrada incompleta";
		}

		return $llResultado;
	}

	public function olvidoPassword($tcUsuario='', $tcDocumento='', $tcCaptcha='', &$tcStatus=''){
		require_once (__DIR__ .'/../../webservice/complementos/nusoap-php8/1.124/nusoap.php');

		$llResult = false;
		$tcUsuario = trim(strtoupper(empty($tcUsuario)?$this->cUsuario:$tcUsuario));
		$tcDocumento = trim(empty($tcDocumento)?strval($this->nId):$tcDocumento);
		$tcDocumento = intval(strval($tcDocumento));
		$tcCaptcha = trim(strtolower(empty($tcCaptcha)?'':$tcCaptcha));

		global $goDb;

		$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
		$lcFecha = $ltAhora->format("Ymd");
		$lcHora  = $ltAhora->format("His");

		$lcMailTitle = 'Acceso a Historia Clinica - Nueva clave de acceso';
		$lcMailSubTitle = 'Acceso a Historia Clinica - Nueva clave de acceso - '.$ltAhora->format("Y-m-d H:i:s");
		$lcMailBody = file_get_contents(__DIR__ .'/../../pifs/contrasena olvido.htm', FALSE);

		if(!empty($tcUsuario) && !empty($tcUsuario) && !empty($tcDocumento) && !empty($tcCaptcha)){

			$laUsuarioRegistro = $goDb
							->select(['NNOMED', 'NOMMED', 'REGMED', 'ESTRGM'])
							->tabla('riargmn')
							->where('USUARI', $tcUsuario)
							->where('NIDRGM', $tcDocumento)
							->get('array');
			if((is_array($laUsuarioRegistro)?count($laUsuarioRegistro)>0:false)==true){

				//Bloqueo en tiempo por intentos
				$llBloqueoIntetos = $this->fForgotIntentos(true);

				if($llBloqueoIntetos==false){
					if($laUsuarioRegistro['ESTRGM']==1){
						$lcAlgrthDefault = trim($goDb->ObtenerTabMae1('DE2TMA', 'SISMENST', "CL1TMA='01010201'", null, "CRC"));
						$lcMails = $this->buscarMails($tcUsuario);

						if (!empty($laUsuarioRegistro['NOMMED']) && !empty($laUsuarioRegistro['NNOMED']) && !empty($laUsuarioRegistro['REGMED'])){
							if($this->oCaptcha->validCaptcha($tcCaptcha)==true){
								if (!empty($lcMails)){
									$lnMethodGeneratePassword = intval($goDb->ObtenerTabMae1('DE2TMA', 'SISMENST', "CL1TMA='01010401'", null, "0")); // Método para generar contraseña
									$lcPsswrdDecrypt = $this->fGenerarContrasena($laUsuarioRegistro['NOMMED'],$laUsuarioRegistro['NNOMED'],$laUsuarioRegistro['REGMED'], $lnMethodGeneratePassword); //Genera la nueva contraseña
									$lcPsswrd=$this->fCodificarContrasena($lcAlgrthDefault,$lcPsswrdDecrypt);

									if (!empty($lcPsswrdDecrypt)){
										$laDatos = ['EO3RGM' => 1];
										if($goDb->tabla('RIARGMN')->where('USUARI',$tcUsuario)->where('NIDRGM',$tcDocumento)->actualizar($laDatos)==true){

											// Controlando claves anteriores
											$laUsuario = $goDb->select(['PSSOL3', 'PSSOL2', 'PSSOL1', 'PSSWRD'])->tabla('SISMENSEG')->where('USUARI', trim($tcUsuario))->get('array');
											if(is_array($laUsuario)){
												if(count($laUsuario)>0){
													if(trim(strtoupper($lcPsswrd))!=trim(strtoupper($laUsuario['PSSWRD']))){
														$laDatos = ['PSSOL3' => $laUsuario['PSSOL2'],
																	'PSSOL2' => $laUsuario['PSSOL1'],
																	'PSSOL1' => $laUsuario['PSSWRD'],
																	'PSSWRD' => $lcPsswrd,
																	'ALGRTH' => $lcAlgrthDefault,
																	'PSSOCH' => 0,
																	];
														$goDb->tabla('SISMENSEG')->where('USUARI',trim($tcUsuario))->actualizar($laDatos);
													}
												}
											}

											// Actualizando la contraseña
											$laDatos = ['ALGRTH' => $lcAlgrthDefault,
														'PSSWRD' => $lcPsswrd,
														'PSSOCH' => 1,
														'LOCKED' => 0
														];
											if($goDb->tabla('SISMENSEG')->where('USUARI',$tcUsuario)->actualizar($laDatos)==true){
												$llResult = true;
												$tcStatus = "Se genero una nueva contraseña.";

												$lnMailsSend = 0;
												foreach(explode(',', $lcMails) as $lcMail){
													$lnMailsSend += ($this->fEnviarContrasena($lcMail,trim($laUsuarioRegistro['NNOMED'])." ".trim($laUsuarioRegistro['NOMMED']),trim($tcUsuario), $lcPsswrdDecrypt)==true ? 1 : 0);
												}
												$tcStatus .= sprintf(" Se envio a %s correo(s)", $lnMailsSend);

												// Registrando en la bitacora
												$this->oBitacora->insertarEntrada($tcUsuario,"OLVIDO-CLAVE",0,"","Solicito nueva clave de acceso desde la opción olvido clave y ".($lnMailsSend>0?"se envio por e-mail ":"no se envio por e-mail ")."a ".$lcMails.". Desde ".$this->oBitacora->getInstancia(),$tcUsuario);
											}
										}else{
											$tcStatus = "No se pudo actualizar la información. Por favor comuníquese con el área de sistemas";
										}
									}else{
										$tcStatus = "No se genero nueva contraseña, puede salir e intentarlo nuevamente";
									}
								}else{
									$tcStatus = "El usuario ".$tcUsuario." no tiene una cuenta e correo registrada. Por favor comuníquese con el área de sistemas";
								}
							}else{
								$tcStatus = "El Captcha ingresado no es correcto";
							}
						}else{
							$tcStatus = "El usuario ".$tcUsuario."  tiene información incompleta. Por favor comuníquese con el área de sistemas";
						}
					}else{
						$tcStatus = "El estado del usuario no permite generar nueva contraseña. Por favor comuníquese con el área de sistemas";
					}
				}else{
					$tcStatus = $this->fForgotIntentosBloqueoMensaje();
				}
			}else{
				$tcStatus = 'La información que se proporciono no es suficiente o correcta. Por favor verifique el nombre de usuario y el numero de identificación.';
			}
		}else{
			$tcStatus = 'La información que se proporciono no es suficiente o correcta. Por favor verifique el nombre de usuario y el numero de identificación.';
		}

		return $llResult;
	}


	// METODOS GET
	public function getPwdReq(){
		return [
				'nPswdLargoMinimo' =>		['name' => 'Largo mínimo', 'value' => $this->nPswdLargoMinimo],
				'nPswdLargoMaximo' =>		['name' => 'Largo máximo', 'value' => $this->nPswdLargoMaximo],
				'nPswdMinMayusculas' => 	['name' => 'Numero mínimo de mayúsculas', 'value' => $this->nPswdMinMayusculas],
				'nPswdMinMinusculas' => 	['name' => 'Numero mínimo de minúsculas', 'value' => $this->nPswdMinMinusculas],
				'nPswdMinNumeros' => 		['name' => 'Cantidad mínima de dígitos', 'value' => $this->nPswdMinNumeros],
				'nPswdMinEspeciales' => 	['name' => 'Cantidad mínima de caracteres especiales', 'value' => $this->nPswdMinEspeciales],
				'nPswdMinFuerza' => 		['name' => 'Fuerza mínima', 'value' => $this->nPswdMinFuerza],
				'cPswdClavesAnteriores' =>	['name' => 'Claves anteriores', 'value' => $this->cPswdClavesAnteriores],
			];
	}
	public function getError(){
		return $this->cError;
	}
	public function getSesionActiva(){
		return $this->lSesionActiva;
	}
	public function getUsuario(){
		return $this->cUsuario;
	}
	public function getRegistro(){
		return $this->cRegistro;
	}
	public function getOpcionesMenu(){
		return $this->aOpcionesMenu;
	}
	public function getOpcionesBarra(){
		return $this->aOpcionesBarra;
	}
	public function getOpcionesUsuario(){
		return $this->cOpcionesUsuario;
	}
	public function getTipoUsuario($tlObject=false){
		if($tlObject==false){
			return $this->nTipoUsuario;
		}else{
			return $this->oTipoUsuario;
		}
	}
	public function getTiposUsuario(){
		$laTiposUsuario=array();
		foreach($this->aEspecialidades as $laEspecialidad){
			if(in_array($laEspecialidad['TIPO']->nId,$laTiposUsuario)==false){
				$laTiposUsuario[]=$laEspecialidad['TIPO']->nId;
			}
		}
		return $laTiposUsuario;
	}
	public function getEspecialidad($tlObject=false){
		if($tlObject==false){
			return $this->cEspecialidad;
		}else{
			return $this->oEspecialidad;
		}
	}
	public function getIP(){
		return $this->cIP;
	}
	public function getCambiarClave(){
		return $this->lCambiarClave;
	}
	public function getAlgrth(){
		return $this->cAlgrth;
	}
	public function getRequiereAval(){
		return $this->lRequiereAval;
	}
	public function getEspecialidades($tcTipo=''){
		$laEspecialidades = array();
		$lnEspecialidad = 0;
		if($tcTipo=='array'){
			foreach($this->aEspecialidades as $laEspecialidadAux){
				$lnEspecialidad += 1;
				$laEspecialidades[]=array(
					'ID' => $lnEspecialidad,
					'TIPOID' => $laEspecialidadAux['TIPO']->nId,
					'TIPONOMBRE' => $laEspecialidadAux['TIPO']->cNombre,
					'ESPECIALIDADID' => $laEspecialidadAux['ESPECIALIDAD']->nId,
					'ESPECIALIDADCODIGO' => $laEspecialidadAux['ESPECIALIDAD']->cId,
					'ESPECIALIDADNOMBRE' => $laEspecialidadAux['ESPECIALIDAD']->cNombre,
					'NIVEL' => $laEspecialidadAux['NIVEL']
				);
			}
		}else{
			$laEspecialidades = $this->aEspecialidades;
		}
		return $laEspecialidades;
	}
	public function getNotificaciones(){
		return $this->aNotificaciones;
	}
	public function getEmail(){
		return $this->cEmail;
	}
	public function getVigenciaIni(){
		return $this->nVigenciaIni;
	}
	public function getVigenciaFin(){
		return $this->nVigenciaFin;
	}
	public function getBodega(){
		return $this->oBodega;
	}
	public function getBodegas(){
		return $this->aBodegas;
	}
	public function getCentroCosto(){
		return $this->oCentroCosto;
	}
	public function getDeviceType(){
		return $this->cDeviceType;
	}
	public function getPerfiles($tcApp='HCW', $tcMenu='MENPRI'){
		return $this->PerfilesMenu($this->cUsuario,$tcApp, $tcMenu);
	}
	public function getEstado(){
		return $this->nEstado;
	}
	public function getDepartamento(){
		return $this->cDepartamento;
	}
	public function getArea(){
		return $this->cArea;
	}
	public function getCargo(){
		return $this->cCargo;
	}
	public function getIdTipo(){
		return $this->cId;
	}
	public function getId(){
		return $this->nId;
	}
	public function getFoto(){
		return $this->cFoto;
	}
	public function getFirma(){
		return $this->cFirma;
	}
	public function getEntidadesConsultaLibroHc(){
		return $this->aEntidadesConsultaLibroHc;
	}
	public function getAuditoria(){
		return $this->aAuditoria;
	}
	public function getProsodyJtmEstado(){
		return $this->nProsodyJtmEstado;
	}
	public function getProsodyJtmPassword(){
		return $this->cProsodyJtmPassword;
	}
	public function getProsodyJtmDiaVigenciaPassword(){
		return $this->nProsodyJtmDiaVigenciaPassword;
	}
	public function getPropiedades($tcTipo=''){
		$laPropiedades=array();
		if($tcTipo=='array'){
			foreach($this->aPropiedades as $lcPropiedad => $laPropiedad){
				if(is_null($laPropiedad['ASSIGNED'])==false){
					$laPropiedades[]=array('ID'=>$lcPropiedad, 'VALUE'=>$laPropiedad['ASSIGNED']);
				}
			}
		}else{
			$laPropiedades=$this->aPropiedades;
		}
		return $laPropiedades;
	}

	public function getCaptcha(){
		return $this->oCaptcha;
	}

	public function getBitacoraKey(){
		return $this->oBitacora->getKey();
	}
}
