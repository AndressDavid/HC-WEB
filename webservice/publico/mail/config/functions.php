<?php
	function getListLogFiles($tcDirectorio, $tcExt='HTML'){
		$loHandleDir	= @opendir($tcDirectorio);
		$lcArchivos		= "";
		$tcExt=strtoupper($tcExt);
		if($loHandleDir){
			while (($lcArchivo = readdir($loHandleDir)) !== false) {
				if($lcArchivo<>'.' and $lcArchivo<>'..') {
					$lcArchivoHijo=strtolower($tcDirectorio.$lcArchivo);
					
					if(is_dir($lcArchivoHijo)==false) {
						if(is_file($lcArchivoHijo)==true){
							$laPathInfo = pathinfo($lcArchivoHijo);
							$lcArchivoHijo = (strtoupper($laPathInfo['extension'])==$tcExt?$lcArchivoHijo:"");
						}else{
							$lcArchivoHijo="";
						}
					}					
					$lcArchivos.=(empty($lcArchivos)==false && empty($lcArchivoHijo)==false?",":"").$lcArchivoHijo;
				}
			}
			closedir($loHandleDir);
		}			
		return $lcArchivos;
	}
	function getFileSize($tcFile){
		$lnFileSize = ($tcFile && @is_file($tcFile)) ? @filesize($tcFile) : 0;
		return $lnFileSize;
	}
	function getFileSizeWithUnit($tcFile, $tnUnit = null, $tlDecimals=false, $tlSizeBytes=false, $tnFileSize=0 ){
		$laSizeUnit = array(" bytes"," Kb"," Mb"," Gb"," Tb"," Pb"," Eb"," Zb"," Yb");
		$tnFileSize = ($tnFileSize==0?getFileSize($tcFile):$tnFileSize);
		$lcFileSize = $tnFileSize.$laSizeUnit[0];
		
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
	function getInbetweenStrings($str,$from,$to){
		$sub = substr($str, strpos($str,$from)+strlen($from),strlen($str));
		return substr($sub,0,strpos($sub,$to));
	}	
?>