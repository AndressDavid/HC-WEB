<?php
namespace CRON;

class AplicacionTarea
{	
	protected $nProceso = 0;
	protected $cArchivo ="";
	protected $cArchivoHistorico = "";
	protected $cTareasCarpeta = "";
	protected $aConfiguracion = array ();
	protected $aHistorico = array();
	protected $cComandoShell = "";
	protected $tInicio = null;
	protected $tFin=null;
	protected $cConfiguracionObligatorios = "cTareaNombre,cTareaDescripcion,cTareaActiva,cArchivo,nIntentosMaximos";
	protected $cConfiguracionLapso = "cTareaMinuto,cTareaHora,cTareaDiaMes,cTareaMes,cTareaDiaSemana";
	protected $aConfiguracionValores = array("cTareaMinuto" => array(0,59), "cTareaHora" => array(0,23), "cTareaDiaMes" => array(1,31), "cTareaMes" => array(1,12), "cTareaDiaSemana" => array(0,6));
	
	// Constructores
    function __construct($tcArchivo=""){		
		$this->cComandoShell = (strtoupper(substr(PHP_OS, 0, 3))==="WIN"?"start php.exe -f ":"php -f ");		
		if(file_exists($tcArchivo)){
			$this->tInicio = time();
			$this->tFin = time();
			$this->nProceso = getmypid();
			$this->cArchivo=$tcArchivo;
			$this->cTareasCarpeta = dirname($tcArchivo)."/";
			$this->cArchivoHistorico = $this->cTareasCarpeta."ejecucion.ini";
			$this->aConfiguracion=@parse_ini_file($this->cArchivo,false,INI_SCANNER_RAW);
			$this->aHistorico=@parse_ini_file($this->cArchivoHistorico,false,INI_SCANNER_RAW);
		}
    }
	
	//Destructores
	function __destruct(){
	}
	
	// Realiza el llamado al script que contiene la tarea
	function procesarTarea(){
		if($this->validarConfiguracion()==true){
			if(strtoupper(trim($this->aConfiguracion["cTareaActiva"]))=="SI"){
				$this->cArchivo=$this->cTareasCarpeta.$this->aConfiguracion["cArchivo"];
				if(file_exists($this->cArchivo)){					
					if($this->validarLapso()==true){
						
						$lnIntentos=$this->getIntentos();
						$lnIntentosMaximos=$this->aConfiguracion["nIntentosMaximos"]+0;
						
						if($this->validarEjecucion()==true ||  $lnIntentos>=$lnIntentosMaximos ){
							$this->evento("Iniciando ".$this->aConfiguracion["cTareaNombre"]);
							$this->setIntentos(1);
							$lcCMD=$this->cComandoShell.$this->cArchivo;
							try {
								$this->evento("Ejecutando la tarea ".$this->aConfiguracion["cTareaNombre"]);
								$this->evento("Comando ".$lcCMD);
								$lnGestor=popen($lcCMD,"w");
								$lnResultado=pclose($lnGestor);
								$this->evento("Ejecutada la tarea ".$this->aConfiguracion["cTareaNombre"]);
								
							} catch (Exception $loError) {
								$this->evento("Error al ejecutar. "+$loError->getMessage());
							}
						}else{
							$this->evento("La tarea ".$this->aConfiguracion["cTareaNombre"]." esta en ejecución");
						}
					}else{
						$this->evento("No es necesario ejecutar ".$this->aConfiguracion["cTareaNombre"].", fuera de lapso", true);
					}
				}else{
					$this->evento("En la tarea ".$this->aConfiguracion["cTareaNombre"]." no existe el archivo ".$this->cArchivo);
				}
			}else{
				$this->evento("La tarea ".$this->aConfiguracion["cTareaNombre"]." esta inactiva");
			}
		}else{
			$this->evento("La configuración no es valida y no pudo continuar con la tarea ".$this->aConfiguracion["cTareaNombre"]);
		}
	}
	
	// Recupera del archivo de intentos el ultimo numero de inetntos
	function getIntentos(){
		$lnIntento=0;
		if(is_dir($this->cTareasCarpeta) && is_readable($this->cTareasCarpeta)){
			if(is_file($this->cTareasCarpeta."intentos.ini")){
				$laIntentos=@parse_ini_file($this->cTareasCarpeta."intentos.ini",false,INI_SCANNER_RAW);
				if($laIntentos){
					if(isset($laIntentos['nIntentos'])){
						$lnIntento=$laIntentos['nIntentos']+0;
					}
				}
			}
		}
		
		$lnIntento+=1;
		$this->setIntentos($lnIntento);
	
		return $lnIntento;
	}
	
	// Guarda en el archivo de intentos el numero enviado
	function setIntentos($tnIntento=0){
		if(is_dir($this->cTareasCarpeta) && is_writable($this->cTareasCarpeta)==true){
			file_put_contents($this->cTareasCarpeta."intentos.ini", "nIntentos=".$tnIntento);
		}
	}	
	
	// Valida que la configuración tenga definidos todos slos campos obligatorios
	function validarConfiguracion(){
		$llValido=true;
		$laObligatorios=explode(",",$this->cConfiguracionObligatorios);
		
		if($this->aConfiguracion!==false){
			for($lnCampo=0;$lnCampo<count($laObligatorios);$lnCampo++){
				if(isset($this->aConfiguracion[$laObligatorios[$lnCampo]])===false){
					$llValido=false;
				}else{
					if(empty($this->aConfiguracion[$laObligatorios[$lnCampo]])){
						$llValido=false;
					}
				}
			}			
		}
		return $llValido;
	}
	
	// Retorna falso o verdadero dependiendo si la tara se esta ejecutando
	function validarEjecucion(){
		$llValido=true;
		
		if($this->aHistorico!==false){
			if(isset($this->aHistorico['cEjecutando'])==true){
				if(trim(strtoupper($this->aHistorico['cEjecutando']))=="SI"){
					$llValido=false;
				}
			}
		}
		return $llValido;
	}
	
	//Este método retorna falso o verdadero dependiendo si la hora y día actual están en los parámetros de ejecución del CRON
	function validarLapso(){
		$llValido=true;
		$laActual=array("cTareaMinuto" => date("i",$this->tInicio)+0, "cTareaHora" => date("H",$this->tInicio)+0, "cTareaDiaMes" => date("j",$this->tInicio)+0, "cTareaMes" => date("n",$this->tInicio)+0, "cTareaDiaSemana" => date("w",$this->tInicio)+0);
		$laCampos=explode(",",$this->cConfiguracionLapso);
		
		if($this->validarConfiguracion()==true){
			for($lnCampo=0; $lnCampo<count($laCampos);$lnCampo++){
				if(isset($this->aConfiguracion[$laCampos[$lnCampo]])===true){
					
					// Inicializando control de valores
					$laValoresCheck=array();
					$laCampoValor=explode(",",$this->aConfiguracion[$laCampos[$lnCampo]]);
					
					// Validando por campo
					for($lnCampoValor=0;$lnCampoValor<count($laCampoValor);$lnCampoValor++){
						
						// Se inicia por rango, este se define con -. Ejemplo: 1-2
						$laCampoValorRango=explode("-",$laCampoValor[$lnCampoValor],2);
						if(count($laCampoValorRango)==2){
							for($lnRango=$laCampoValorRango[0]+0;$lnRango<=$laCampoValorRango[1]+0;$lnRango++){
								if($lnRango>=$this->aConfiguracionValores[$laCampos[$lnCampo]][0]){
									if($lnRango<=$this->aConfiguracionValores[$laCampos[$lnCampo]][1]){								
										if(in_array($lnRango,$laValoresCheck)==false){
											$laValoresCheck[]=$lnRango;
										}
									}
								}
							}
						}else{
							for($lnRango=0;$lnRango<count($laCampoValorRango);$lnRango++){
								// Se inicia validación por paso, este se define con /. Ejemplo: */2 cada dos pasos
								if(strpos($laCampoValorRango[$lnRango],"/")>0){
									$laRango= explode("/",$laCampoValorRango[$lnRango],2);
									if(count($laRango)==2){
										for($lnRangoAux=$this->aConfiguracionValores[$laCampos[$lnCampo]][0];$lnRangoAux<=$this->aConfiguracionValores[$laCampos[$lnCampo]][1];$lnRangoAux++){
											if($lnRangoAux%$laRango[1]==0){
												if($lnRangoAux>=$this->aConfiguracionValores[$laCampos[$lnCampo]][0]){
													if($lnRangoAux<=$this->aConfiguracionValores[$laCampos[$lnCampo]][1]){												
														if(in_array($lnRangoAux,$laValoresCheck)==false){
															$laValoresCheck[]=$lnRangoAux;
														}
													}
												}
											}
										}								
									}
								}else{									
									// Se inicia validación general, ete se define con *. Ejemplo: *
									if($laCampoValorRango[$lnRango]=="*"){
										for($lnRangoAux=$this->aConfiguracionValores[$laCampos[$lnCampo]][0];$lnRangoAux<=$this->aConfiguracionValores[$laCampos[$lnCampo]][1];$lnRangoAux++){
											if(in_array($lnRangoAux,$laValoresCheck)==false){
												$laValoresCheck[]=$lnRangoAux;
											}									
										}
									}else{
										// Valores fijos
										if($laCampoValorRango[$lnRango]+0>=$this->aConfiguracionValores[$laCampos[$lnCampo]][0]){
											if($laCampoValorRango[$lnRango]+0<=$this->aConfiguracionValores[$laCampos[$lnCampo]][1]){
												if(in_array($laCampoValorRango[$lnRango]+0,$laValoresCheck)==false){
													$laValoresCheck[]=$laCampoValorRango[$lnRango]+0;
												}
											}
										}
									}
								}
							}
						}
					}
					
					// Control cuando no existe parámetro
					if(empty($laValoresCheck)){
						$laValoresCheck[]=0;
					}
					
					// Valida si el parámetro esta en el rango
					sort($laValoresCheck);
					if(in_array($laActual[$laCampos[$lnCampo]],$laValoresCheck)==false){
						$llValido=false;
					}
				}
			}			
		}
		return $llValido;
	}
	
	// Este metodo escribe el texto enviado haciedo referencia a un evento del desencadenante
	function evento($tcLog="", $tlNoRepetir=false){
		$lcLogFile=$this->cTareasCarpeta.date("Y-m").".log";
		$lcLogOld=$this->UltimaLineaLog($lcLogFile);
		
		$lclog = date("Y-m-d H:i:s")."  AplicacionTarea          , ".$tcLog;
		$lclog = mb_convert_encoding($lclog,"UTF-8");
		$lclog = $lclog.PHP_EOL;
		
		if(is_dir($this->cTareasCarpeta) && is_writable($this->cTareasCarpeta)==true){
			file_put_contents($lcLogFile, $lclog, FILE_APPEND);
		}
			
		echo $lclog;
	}
	// Leer la ultima linea del log  
	function UltimaLineaLog($lcLogLeer=''){
		$lcLinea = '';
		if(is_file($lcLogLeer)==true){
			$loArchivo = fopen($lcLogLeer, 'r');
			fseek($loArchivo, -1, SEEK_END);
			$lnPosicion = ftell($loArchivo);
			while (fgetc($loArchivo) === "\n") {
				fseek($loArchivo, $lnPosicion--, SEEK_END);
			}
			$lcLinea = fgetc($loArchivo);
			while ((($lcChar= fgetc($loArchivo)) !== "\n") && $lnPosicion>=0) {
				$lcLinea = $lcChar . $lcLinea;
				fseek($loArchivo, $lnPosicion--);
			}
			fclose($loArchivo);
		}
		$laSearch  = str_split(PHP_EOL,1);
		$lcReplace = '';

		return trim(str_replace($laSearch, $lcReplace, $lcLinea));		
	}	
}
?>