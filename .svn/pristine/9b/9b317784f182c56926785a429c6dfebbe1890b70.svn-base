<?php
namespace NUCLEO;

require_once (__DIR__ .'/../publico/constantes.php');
require_once (__DIR__ .'/class.AplicacionFunciones.php');

use NUCLEO\AplicacionFunciones;

class AplicacionLogsManejador {
	protected $cErrorFileCurrent = "";
	protected $aErrorFileCurrent = array();
    protected $cLogsCarpeta = "";
	protected $oFunciones = NULL;
	protected $aLogs = array();
	protected $aFileStads = ["dev" => "Número de dispositivo",
							"ino" => "Número de i-nodo *",
							"mode" => "Modo de protección del i-nodo",
							"nlink" => "Número de enlaces",
							"uid" => "ID de usuario del propietario *",
							"gid" => "ID de grupo del propietario *",
							"rdev" => "Tipo de dispositivo, si es un dispositivo i-nodo",
							"size" => "Tamaño en bytes",
							"atime" => "Momento del último acceso (tiempo Unix)",
							"mtime" => "Momento de la última modificación (tiempo Unix)",
							"ctime" => "Momento de la última modificación del i-nodo (tiempo Unix)",
							"blksize" => "Tamaño del bloque E/S del sistema de ficheros **",
							"blocks" => "Número de bloques de 512 bytes asignados **"];
	protected $cTotalSize = '0 bytes';
	protected $nTotalSize = 0 ;
	protected $cLogExtension = 'log';
	
    
    function __construct($tlCargar=true){
		$this->oFunciones = new AplicacionFunciones();
		$this->cErrorFileCurrent = ini_get('error_log');
		$this->cLogsCarpeta = dirname($this->cErrorFileCurrent)."/";

		if(is_file($this->cErrorFileCurrent)==true){
			$this->aErrorFileCurrent =  pathinfo($this->cErrorFileCurrent);
		}
		
		if($tlCargar==true){
			$this->cargar();
		}
    }
    
    private function cargar(){
		$laFiles=array();
		if(is_dir($this->cLogsCarpeta) && is_readable($this->cLogsCarpeta)){
			$laFiles=$this->explorarDirectorio($this->cLogsCarpeta,$laFiles);
			if($laFiles){
				if(count($laFiles)>0){
					foreach($laFiles as $lcFile){
						if($this->oFunciones->isSearchStrInStr($lcFile,".log")==true){
							$laFileInfo = pathinfo($lcFile);
							
							if(trim(mb_strtolower($laFileInfo['extension']))==trim(mb_strtolower($this->cLogExtension))){
								$laFileStat = stat($lcFile);
								$lnFileSize = $this->getFileSize($lcFile);
								
								$laLog["fullpath"] = $lcFile;
								$laLog["mime"] = mime_content_type($lcFile);
								$laLog["size"] = $lnFileSize;
								$laLog["size-unit"] = $this->getFileSizeWithUnit($lcFile);
								$laLog["dirname"] = $laFileInfo['dirname'];
								$laLog["basename"] = $laFileInfo['basename'];
								$laLog["extension"] = $laFileInfo['extension'];
								$laLog["filename"] = $laFileInfo['filename'];
								$laLog["edited"] = date("Y-m-d H:i:s", filemtime($lcFile));
								
								foreach($this->aFileStads as $lcFileStadKey => $lcFileStadName){
									$laLog[$lcFileStadKey] = $laFileStat[$lcFileStadKey];
									if(in_array($lcFileStadKey,['atime', 'mtime', 'ctime'])==true){
										$laLog[$lcFileStadKey."-date"] = date("Y-m-d H:i:s",$laFileStat[$lcFileStadKey]);
									}
								}
													
								$this->nTotalSize += $lnFileSize;
								$this->aLogs[] = $laLog;
							}
						}
					}
					$this->cTotalSize = $this->getFileSizeWithUnit("",  null, false, false, $this->nTotalSize);
					
				}
			}
		}
    }
	

	private function explorarDirectorio($tcPath, $taFiles=array(), $tcFind="", $tlOnlyFolders=false) {
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

	private function getFileSize($tcFile){
		$lnFileSize = ($tcFile && @is_file($tcFile)) ? @filesize($tcFile) : 0;
		return $lnFileSize;
	}
	
	private function getFileSizeWithUnit($tcFile, $tnUnit = null, $tlDecimals=false, $tlSizeBytes=false, $tnFileSize=0 ){
		$laSizeUnit = array(" bytes"," Kb"," Mb"," Gb"," Tb"," Pb"," Eb"," Zb"," Yb");
		$tnFileSize = ($tnFileSize==0?$this->getFileSize($tcFile):$tnFileSize);
		$lcFileSize = $tnFileSize.$laSizeUnit[0];
		$tnUnit = (is_null($tnUnit)?-1:intval($tnUnit));
		
		// If filesize() fails (with larger files), try to get the size from unix command line.
		if (!$tnFileSize || $tnFileSize < 0) {
			$tnFileSize=exec("ls -l '$tcFile' | awk '{print $5}'");
		}
		
		// Check Unit
		if ($tnFileSize>0){
			if($tnUnit==-1){
				$lcFileSize = number_format($tnFileSize/pow(1024, $lnLogarithm=floor(log($tnFileSize, 1024))), ($tlDecimals==true) ? 2 : 0) .$laSizeUnit[$lnLogarithm];
			} elseif ($tnUnit ==0) {
				$lcFileSize = number_format($tnFileSize).$laSizeUnit[$tnUnit];
			} else {
				$lcFileSize = number_format($tnFileSize/pow(1024, $tnUnit), ($tlDecimals==true) ? 2 : 0).$laSizeUnit[$tnUnit];
			}
		}
		
		// Add Bytes
		if ($tlSizeBytes==true && $tnUnit<>0){
			$lcFileSize.=" (".number_format($tnFileSize,0).$laSizeUnit[0].")"; 
		}
		return $lcFileSize;
	}
				
	private function writeDownloadFile($tcContentType='txt', $tcFileSource='unknown.txt', $tcFileOut='unknown.txt', $tnFileSize=0, $tcFileContent=''){	
		header(sprintf('Content-Type: %s',$tcContentType));
		header(sprintf("Content-Disposition: attachment; filename=log-%s-%s", date("Ymdhis"), $tcFileOut));
		header('Pragma: no-cache');
		header(sprintf('Content-Length: %s', $tnFileSize));
		if($tcFileContent<>'*'){
			echo $tcFileContent;
		}else{
			if(file_exists($tcFileSource)==true){
				$loFile = fopen($tcFileSource,"r");
				while (!feof($loFile)) {
				   echo fread($loFile, 1024);
				}
				fclose($loFile);
			}else{
				echo 'unknown';
			}
		}
	}				
				
	public function echoLogContents($tcLog=''){
		$tcLog = strval($tcLog);
		$lcLog = $this->cLogsCarpeta.$tcLog;
		$lnLimitBytes = 1*1024*1024;
				
		if(is_file($lcLog)==true){
			echo "---> Inicio ".basename($lcLog)." últimos ".$this->getFileSizeWithUnit("",  null, false, false, $lnLimitBytes)." <---\n\n";

			$loLog = fopen($lcLog, "r");
			$lnFileSize = filesize($lcLog);
			
			$loFile = fopen($lcLog,"r");
			fseek($loFile,-1*$lnLimitBytes,SEEK_END);
			while (!feof($loFile)) {
			   echo fread($loFile, 1024);
			}
			fclose($loFile);
			
			echo "\n---> Fin últimos ".$this->getFileSizeWithUnit("",  null, false, false, $lnLimitBytes)." <---\n";
			
		}else{
			echo "No existe ".$tcLog;
		}
	}
	
	public function downloadLogContents($tcLog=''){
		$tcLog = strval($tcLog);
		$lcLog = $this->cLogsCarpeta.$tcLog;
		if(file_exists($lcLog)==true){
			$this->writeDownloadFile(mime_content_type($lcLog), $lcLog, basename($lcLog), filesize($lcLog), '*');
		}else{
			$this->writeDownloadFile('txt', 'portal-hcw.txt', 'portal-hcw.txt', 0, $lcError);
		}
	}
	
	public function getLogContents($tcLog=''){
		$tcLog = strval($tcLog);
		$lcLog = $this->cLogsCarpeta.$tcLog;
		if(is_file($lcLog)==true){
			$loLog = fopen($lcLog, "r");
			$lcContent = fread($loLog, filesize($lcLog));
			fclose($loLog);			
			return $lcContent;
		}
		return "";
	}
	
	public function getErrorFileCurrent($llFilePath=true){
		if($llFilePath==true){
			return $this->cErrorFileCurrent;
		}
		return $this->aErrorFileCurrent;
	}
	
	public function getLogsCarpeta(){
		return $this->cLogsCarpeta;
	}
	
	public function getFileStads(){
		return $this->aFileStads;
	}
							
	public function getLogs(){
		return $this->aLogs;
	}
	
	public function getCountLogs(){
		return count($this->aLogs);
	}
	
	public function getTotalSize($tlString=true){	
		return ($tlString==true?$this->cTotalSize:$this->nTotalSize);
	}
	
	public function getLogExtension(){
		return $this->cLogExtension;
	}
}
?>
