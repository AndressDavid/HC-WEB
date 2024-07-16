<?php
namespace CRON;

require_once (__DIR__ .'/../publico/constantes.php');
require_once (__DIR__ .'/class.AplicacionFunciones.php');
require_once (__DIR__ .'/class.AplicacionTarea.php');

use NUCLEO\AplicacionFunciones;
use CRON\AplicacionTarea;

class AplicacionTareasManejador
{
    protected $cTareasCarpeta = "";
	protected $oFunciones = NULL;
	
    
    function __construct($tcTareasCarpeta=""){
        $this->cTareasCarpeta = $tcTareasCarpeta;
		$this->oFunciones = new AplicacionFunciones();
    }
    
	// Este método es el encargado de procesar los archivos de definición de tareas
    function procesar(){
		$laFiles=array();
		if(is_dir($this->cTareasCarpeta) && is_readable($this->cTareasCarpeta)){
			$laFiles=$this->explorarDirectorio($this->cTareasCarpeta,$laFiles);
			if($laFiles){
				if(count($laFiles)>0){
					$llExisteArchivoConfiguracion=false;
					foreach($laFiles as $lcFile){
						if($this->oFunciones->isSearchStrInStr($lcFile,".ini")==true){
							if(strtolower(basename($lcFile))=="configuracion.ini"){
								$llExisteArchivoConfiguracion=true;
								
								$this->evento("Procesando el archivo ".$lcFile);
								$loAplicacionTarea = new AplicacionTarea($lcFile);
								$loAplicacionTarea->procesarTarea();
								unset($loAplicacionTarea);
							}
						}
					}
					if($llExisteArchivoConfiguracion==false){
						$this->evento("La carpeta ".$this->cTareasCarpeta." no contiene archivo de configuración");
					}
				}
			}else{
				$this->evento("La carpeta ".$this->cTareasCarpeta." no existe o no se cuenta con acceso");
			}
		}else{
			$this->evento("La carpeta ".$this->cTareasCarpeta." no existe o no se cuenta con acceso");
		}
    }
	
	// Con este método se exploran los subdirectorios en busca de las definiciones de tareas
	function explorarDirectorio($tcPath, $taFiles=array(), $tcFind="", $tlOnlyFolders=false) {
		$laHiddent = array(".", "..", ".htaccess", ".htpasswd");
		$laPath = scandir($tcPath);
		if($tlOnlyFolders==true){
			$taFiles[] = $tcPath;
		}
		foreach($laPath as $lcPathKey => $lcPathContent) {
			$lcPathFull = $tcPath.$lcPathContent;
			if(!in_array($lcPathContent, $laHiddent)) {
				if(is_file($lcPathFull) && is_readable($lcPathFull)) {
					if ($tlOnlyFolders==false){
						if(empty($tcFind)==false){
							if ($this->oFunciones->isSearchStrInStr($lcPathContent, $tcFind)==true){
								$taFiles[] = $lcPathFull;
							}
						}else{
							$taFiles[] = $lcPathFull;
						}
					}
				}elseif(is_dir($lcPathFull) && is_readable($lcPathFull)) {
					$taFiles = $this->explorarDirectorio($lcPathFull.'/', $taFiles, $tcFind,  $tlOnlyFolders);
				}
			}
		}
		return $taFiles;
	}
	
	// Array con las tareas validas del aplicativo
	function obtenerTareas($tcPath=''){
		$tcPath = (empty($tcPath)?$this->cTareasCarpeta:$tcPath); 
		$laTareas=array();
		if(is_dir($tcPath) && is_readable($tcPath)){
			$laFiles=$this->explorarDirectorio($tcPath,array(),"configuracion.ini");
			foreach($laFiles as $lcFile){
				$lcFile = realpath($lcFile);
				if(file_exists($lcFile)){
					$laFile = pathinfo($lcFile);
					
					$laFilesTask=$this->explorarDirectorio($laFile['dirname']."/",array());
					$laFilesIni=array();
					foreach($laFilesTask as $lcFileTask){
						$lcFileTask = realpath($lcFileTask);
						if($this->oFunciones->isSearchStrInStr($lcFileTask,".ini")==true){
							if(file($lcFileTask)){
								$laFileTask = pathinfo($lcFileTask);
								$laFileIni=@parse_ini_file($lcFileTask,false,INI_SCANNER_RAW);
								$laFilesIni[$laFileTask['filename']]=$laFileIni;
							}
						}
					}
					
					$laTareas[] = array(
										"carpeta"=>realpath($laFile['dirname']),
										"archivo"=>$laFile['basename'],
										"archivoNombre"=>$laFile['filename'],
										"archivos"=>$laFilesTask,
										"archivosIni"=>$laFilesIni);
				}
			}			
		}
		return $laTareas;
	}
	
	// Este metodo escribe el texto enviado haciedo referencia a un evento del desencadenante
	function evento($tcLog=""){
		$lclog = date("Y-m-d H:i:s")."  AplicacionTareasManejador, ".$tcLog.PHP_EOL;
		if(is_dir($this->cTareasCarpeta) && is_writable($this->cTareasCarpeta)==true){
			$lclog=mb_convert_encoding($lclog,"UTF-8");
			file_put_contents($this->cTareasCarpeta.date("Y-m").".log", $lclog, FILE_APPEND);
		}
		echo $lclog;
	}	
}
?>
