<?php
namespace CRON;

class AplicacionTareaManejador
{
	protected $cArchivo ="";
	protected $cArchivoEjecucion ="";
	protected $cTareasCarpeta = "";	
	protected $cScriptCarpeta = "";
	protected $cError="";
	protected $aHistorico = array("nProceso"=>0,"cEjecutando"=>"", "tInicio"=>"", "tFin"=>"", "cError"=>"");
	
	// Constructores
    function __construct($tcArchivo="", $tcScriptCarpeta=""){
		$this->cArchivo=$tcArchivo;
		$this->cTareasCarpeta = dirname($tcArchivo)."/";
		$this->cScriptCarpeta = $tcScriptCarpeta."/";
		$this->cArchivoEjecucion = $this->cTareasCarpeta.'ejecucion.ini';
		$this->matarProcesoAnterior();
		
		$this->aHistorico["nProceso"] = getmypid();
		$this->aHistorico["cEjecutando"]="SI";
		$this->aHistorico["tInicio"]=time();
		
		$this->guardarEjecucion();
    }
	
	//Destructores
	function __destruct(){
		$this->aHistorico["nProceso"]=0;
		$this->aHistorico["tFin"]=time();
		$this->aHistorico["cEjecutando"]="NO";
		$this->aHistorico["cError"]=(empty($this->cError)?"NO":"SI");
		
		$this->guardarEjecucion();
	}

	
	// Este método se encarga de "matar" el proceso anterior relacionado en la ultima ejecución
	function matarProcesoAnterior(){
		$lcSistemaOperativo = strtoupper(substr(PHP_OS, 0, 3));
		$lcComandoTaskKill = ($lcSistemaOperativo==="WIN"?"start taskkill /f /pid ":"kill ");
		
		if(is_dir($this->cTareasCarpeta) && is_readable($this->cTareasCarpeta)){
			if(is_file($this->cArchivoEjecucion)){
				$laHistorico=@parse_ini_file($this->cArchivoEjecucion,false,INI_SCANNER_RAW);
				if($laHistorico){
					if(isset($laHistorico['nProceso'])){
						$lnProceso=$laHistorico['nProceso']+0;
						if (!empty($lnProceso)){
							$lcCMD=$lcComandoTaskKill.$lnProceso;
							pclose(popen($lcCMD,"w"));
							$this->evento("---- Terminando ejecución anterior con ".$lcCMD);
						}
					}
				}
			}
		}	
	}
	
	// Guarda la información de la ejecución
	function guardarEjecucion(){
		$this->putArchivoEjecucion($this->aHistorico);
	}

	// Guarda la inflacionario de la ejecución
	function putArchivoEjecucion($taArray, $tnNivel = 0){
		$lcContenido="";
		foreach ($taArray as $tcKey => $tcValue){
			if (is_array($tcValue)){
				$lcContenido.=str_repeat(" ",$tnNivel*2)."[$tcKey]".PHP_EOL; 
				$lcContenido.=putArchivoEjecucion("",$tcValue, $tnNivel+1);
			}else{
				$lcContenido.=str_repeat(" ",$tnNivel*2)."$tcKey = $tcValue".PHP_EOL; 
			}
		}
		if($this->cArchivoEjecucion){
			if(is_dir($this->cTareasCarpeta) && is_writable($this->cTareasCarpeta)==true){
				return file_put_contents($this->cArchivoEjecucion,$lcContenido);
			}else{
				return 0;
			}
		}else{
			return $lcContenido;
		}
	}	

	// Este método escribe el error
	function error($tcError=""){
		$this->cError = strval($tcError);
		$this->evento("Error:".$this->cError);
	}		

	// Este método escribe el texto enviado haciendo referencia a un evento como comando
	function evento($tcLog=""){
		$lclog = date("Y-m-d H:i:s")."  AplicacionTareaManejador , ".$tcLog.PHP_EOL;
		if(is_dir($this->cTareasCarpeta) && is_writable($this->cTareasCarpeta)==true){
			$lclog=mb_convert_encoding($lclog,"UTF-8");
			file_put_contents($this->cTareasCarpeta.date("Y-m").".log", $lclog, FILE_APPEND);
		}
		echo $lclog;
	}	
}
?>