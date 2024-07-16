<?php
require_once (__DIR__ .'/../../webservice/complementos/PHPMailer/src/Exception.php');
require_once (__DIR__ .'/../../webservice/complementos/PHPMailer/src/PHPMailer.php');
require_once (__DIR__ .'/../../webservice/complementos/PHPMailer/src/SMTP.php');
  
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class EmailEnvio {
	var $aHeadersAdress	= array("TO"=>"","CC"=>"","BCC"=>"");
	var $cLanguage = "ES" ;
	var $nAuthMode = 1;
	var $cServer = "";
	var $nServerPort = 25;
	var $cUserName = "";
	var $cPassword = "";
	var $cDate = ""; // Fecha con formato » RFC 2822
	var $cFrom = "";
	var $cReplyTo = "";
	var $cTo = "";
	var $cCc = "";
	var $cBcc = "";
	var $cSubject = "";
	var $cSubjectSimple = "";
	var $cHtmlBody = "";
	var $nPriority = 0 ;		// Prioridad 	Between(-1,1) 	defa 0
	var $nImportance = 1 ; 		// Importancia 	Between(0,2) 	defa 1
	var $nDisposition = 0 ;		// Confirmar lectura 1=Si,0=No
	var $cOrganization = "Fundacion Clinica Shaio" ;
	var $cKeywords = "" ;
	var $cDescription = "" ;
	var $nSocket = 0;
	var $cFault = "";
	var $cRequest = "";
	var $cBoundary  = "";
	var $cContentType="text/html";
	var $cCharSet="ISO-8859-1";
	var $cXMailer = "UGhwSm9zZUd1aWxsZXJtb09ydGl6SGVybmFuZGV6V2ViU2VydmljZQ==";
	var $cXCreateBy = "";
	var $lParameters = false;
	var $cDefaultServer="";
	var $nDefaultServerPort=25;
	var $cEmailsOmit = "";
	var $lLog = true;
	var $lEventList = true;
	var $nAttachMaxSize = 8388608; // 8 Mb
	var $cAttachServerFilesPath="attachs/";
	var $cAttachServerFilesID="";
	var $cAttach = "";
	var $cAttachExclude = "";
	var $cMailAddons = "PHPMailer"; //PHPMailer
	var $oMailAddons = NULL;

	function __construct ($tcServer="", $tnPort=0, $tcUser="", $tcPass="", $tcFrom="", $tcTO="", $tcCC="", $tcBCC="", $tcSubject="", $tcBody="",$tnAuthMode=1,$tnPriority=0,$tnImportance=1,$tnDisposition=0,$tcOrganization="",$tcKeywords="",$tcDescription="",$tcAttachServerFilesID="") {
		$this->cServer = $tcServer;
		$this->nServerPort = $tnPort;
		$this->cDefaultServer = ini_get("SMTP");
		$this->nDefaultServerPort = ini_get("smtp_port");
		$this->cUserName = base64_encode($tcUser);
		$this->cPassword = base64_encode($tcPass);
		$this->cBoundary  = uniqid('------GO').".".md5(uniqid(time()));
		$this->cFrom = $tcFrom;
		$this->cReplyTo = $tcFrom;
		$this->cTo = str_replace(";",",",$tcTO);
		$this->cCc = str_replace(";",",",$tcCC);
		$this->cBcc = str_replace(";",",",$tcBCC);
		$this->cSubject =$this->EncodeIso88591($tcSubject);
		$this->cSubjectSimple = $tcSubject;
		$this->cHtmlBody = $this->SetFormatByStyle($tcBody);
		$this->nPriority = $tnPriority+0 ;	// Prioridad 	Between(-1,1) 	defa 0
		$this->nImportance = $tnImportance+0 ;	// Importancia 	Between(0,2) 	defa 1
		$this->nDisposition = $tnDisposition+0 ;	// Confirmar lectura 1=Si,0=No
		$this->cOrganization = (empty($tcOrganization)?$this->cOrganization:$tcOrganization);
		$this->cKeywords = $tcKeywords ;
		$this->cDescription = $tcDescription ;
		$this->nAuthMode = $tnAuthMode;
		$this->cDate = date("r");
		$this->cXMailer = base64_decode($this->cXMailer)." v.".phpversion();
		$this->cXCreateBy = $this->cXMailer;
		$this->cAttachServerFilesID=$tcAttachServerFilesID."";
		$this->SetHeadersAdress("TO",$this->cTo);
		$this->SetHeadersAdress("CC",$this->cCc);
		$this->SetHeadersAdress("BCC",$this->cBcc);
		
		switch ($this->cMailAddons) {
			case "PHPMailer":
				$this->setPhpMailerMasterSettings();
				$this->nAuthMode=0;
				$this->lParameters = $this->CheckParameters("cServer,cUserName,cPassword,cFrom,cTo,cHtmlBody");
				break;
			default:
				$this->lParameters = $this->CheckParameters("cServer,cUserName,cPassword,cFrom,cTo,cHtmlBody");
		}
	}
	function AttachMaxSize($tnEmpty=0){
		return $this->nAttachMaxSize;
	}
	private function setPhpMailerMasterSettings(){
		$this->oMailAddons = new PHPMailer();
		$this->oMailAddons->setLanguage(strtolower($this->cLanguage), __DIR__ .'/../../webservice/complementos/PHPMailer/language/');
		$this->oMailAddons->SMTPDebug = 2; //Enable SMTP debugging: 0 = off (for production use), 1 = client messages, 2 = client and server messages
		
		$this->oMailAddons->SMTPAuth = true;
		$this->oMailAddons->isSMTP();
		$this->oMailAddons->Debugoutput = 'html';
		$this->oMailAddons->Host = $this->cServer;
		
		$this->oMailAddons->Username = base64_decode($this->cUserName);
		$this->oMailAddons->Password = base64_decode($this->cPassword);
		$this->oMailAddons->SMTPSecure = 'tls';
		$this->oMailAddons->Port = $this->nServerPort;
		
		$this->oMailAddons->setFrom($this->cFrom);
		$this->oMailAddons->addReplyTo($this->cReplyTo);
		$this->oMailAddons->Subject = $this->cSubject;

		
		if($this->nPriority>=-1 && $this->nPriority<2){
			switch ($this->nPriority) {
				case -1:
					$this->oMailAddons->Priority=5;
					break;
				case 1:
					$this->oMailAddons->Priority=1;
					break;
				default:
					$this->oMailAddons->Priority=0;
			}
		}
		
		$this->cTo=$this->setPhpAddressValidate("bcc",$this->cTo);	
		$this->cCc=$this->setPhpAddressValidate("bcc",$this->cCc);		
		$this->cBcc=$this->setPhpAddressValidate("bcc",$this->cBcc);	

		try{
			if (!empty($this->cAttachServerFilesID)){
				if (is_dir($this->cAttachServerFilesPath)){				
					$laAttachServerFiles=explode(",",$this->cAttachServerFilesID);
					arsort($laAttachServerFiles);	
					foreach ($laAttachServerFiles as $lcAttachServerFile) {
						$lcAttachServerFile.="/";
						if(is_dir($this->cAttachServerFilesPath.$lcAttachServerFile)){
							$lnHandleAttachServer = opendir($this->cAttachServerFilesPath.$lcAttachServerFile); 
							while (($lcAttachServerFileName = readdir($lnHandleAttachServer)) !== false) {
								if ($lcAttachServerFileName <> '.' && $lcAttachServerFileName <> '..') {
									$lcAttachServerFileName=$this->cAttachServerFilesPath.$lcAttachServerFile.$lcAttachServerFileName;
									if(is_file($lcAttachServerFileName)) {
										if(filesize(dirname(__FILE__)."/".$lcAttachServerFileName)<=$this->nAttachMaxSize) {
											$this->oMailAddons->addAttachment($lcAttachServerFileName);
											$this->cAttach .= (empty($this->cAttach)?"":",") . $lcAttachServerFileName;	
										}else{
											$this->cAttachExclude.="<li>".basename($lcAttachServerFileName)." <i>(Archivo muy grande)</i> </li>";
										}
									}else{
										$this->cAttachExclude.="<li>".basename($lcAttachServerFileName)." <i>(Archivo no se encontro en el servidor)</i></li>";
									}
								}
							}
							closedir($lnHandleAttachServer);
						}
					}
				}
			}
		} catch (Exception $e) {
			$this->cFault=$e->getMessage();
		}
		
		$this->oMailAddons->isHTML(true);
		$this->oMailAddons->Body = $this->cHtmlBody.(empty($this->cAttachExclude)?"":"<br/><br/><small><b>No se adjuto:</b> <ul>".$this->cAttachExclude."</ul></small>");

	}
	private function setPhpAddressValidate($tcType='', $tcAddress=''){
		$lcAddress="";
		if (!empty($tcAddress)){
			$tcAddress=trim(str_replace(";",",",$tcAddress));
			if (strpos($tcAddress,",") === false) {
				if (filter_var($tcAddress, FILTER_VALIDATE_EMAIL)) {
					switch($tcType){
						case "to":
							$this->oMailAddons->addAddress($tcAddress);
							break;
						case "cc":
							$this->oMailAddons->addCC($tcAddress);
							break;
						case "bcc":
							$this->oMailAddons->addBCC($tcAddress);
							break;
					}
					$lcAddress=$tcAddress;
				}					
			}else{
				$laAddress=explode(",",str_replace(";",",",$tcAddress));
				arsort($laAddress);	
				foreach ($laAddress as $lcAddressAux) {
					if(!empty($lcAddressAux)){
						if (filter_var($lcAddressAux, FILTER_VALIDATE_EMAIL)) {	
							switch($tcType){
								case "to":
									$this->oMailAddons->addAddress($lcAddressAux);
									break;
								case "cc":
									if(!empty($lcAddressAux)){
										$this->oMailAddons->addCC($lcAddressAux);
									}
									break;
								case "bcc":
									if(!empty($lcAddressAux)){
										$this->oMailAddons->addBCC($lcAddressAux);
									}
									break;
							}
							$lcAddress.=(empty($lcAddress)?"":",").$lcAddressAux;
						}
					}
				}
			}
		}
		return $lcAddress;
	}
	private function setPhpMailerMasterClearAttach(){
		try{
			if (!empty($this->cAttachServerFilesID)){
				if (is_dir($this->cAttachServerFilesPath)){				
					$laAttachServerFiles=explode(",",$this->cAttachServerFilesID);
					arsort($laAttachServerFiles);	
					foreach ($laAttachServerFiles as $lcAttachServerFile) {
						$lcAttachServerFile.="/";
						if(is_dir($this->cAttachServerFilesPath.$lcAttachServerFile)){
							$lnHandleAttachServer = opendir($this->cAttachServerFilesPath.$lcAttachServerFile); 
							while (($lcAttachServerFileName = readdir($lnHandleAttachServer)) !== false) {
								if ($lcAttachServerFileName <> '.' && $lcAttachServerFileName <> '..') {
									$lcAttachServerFileName=$this->cAttachServerFilesPath.$lcAttachServerFile.$lcAttachServerFileName;
									if(is_file($lcAttachServerFileName)) {
										@unlink($lcAttachServerFileName);
									}
								}
							}
							closedir($lnHandleAttachServer);
							@rmdir($this->cAttachServerFilesPath.$lcAttachServerFile);
						}
					}
				}
			}
		} catch (Exception $e) {
			//Dummy
		}		
	}
	private function SetHeadersAdress($tcType,$tcValues){
		$tcType=trim(strtoupper($tcType));
		$tcValues=str_replace(";",",",$tcValues);
		if(!empty($tcType)){
			if (strpos("TO,CC,BCC",$tcType)!==false) {
				if(!empty($tcValues)){
					$laValues=explode(",",$tcValues);
					arsort($laValues);
					$this->aHeadersAdress[$tcType]="";
					foreach ($laValues as $lcValue) {
						if(!empty($lcValue)){
							if(strpos("CC,BCC",$tcType)!==false && strpos($this->cEmailsOmit,trim(strtolower($lcValue)))===false){
								$this->aHeadersAdress[$tcType]=$this->aHeadersAdress[$tcType].(empty($this->aHeadersAdress[$tcType])?"":",").$lcValue;
							}
						}
					}
				}
			}
		}
	}
	private function SetFormatByStyle($tcBody) {
		$lcBodyFooter = file_get_contents(__DIR__ .'/../../pifs/plantilla-firma-email.htm');
		$tcBody ="<span style='font-family: tahoma, new york, times, serif; font-size: 10pt; color: #000000'>".$tcBody;
		$tcBody.=$lcBodyFooter;
		$tcBody.="<br/><br/><hr/><i style=\"color:#666666;\">Este correo fue enviado de forma autom&aacute;tica ".$this->cOrganization." ".$this->cKeywords.". <small>MailWebServer</small></i>";
		$tcBody.="</span>";
		return $tcBody;
	}
	private function SmtpHeader() {
		if($this->nPriority<-1 || $this->nPriority>1) $this->nPriority=0;// Between(-1,1) decFault 0
		if($this->nImportance<0 || $this->nImportance>2) $this->nPriority=1;// Between(0,2) DecFault 1
		
		$lcHeaders  = "";
		$lcHeaders .= "Date:".$this->cDate. PHP_EOL;
		$lcHeaders .= "From:<".$this->cFrom.">". PHP_EOL;
		$lcHeaders .= "Reply-To: ".$this->cReplyTo	. PHP_EOL;
		$lcHeaders .= $this->SmtpHeaderAdress("TO");
		$lcHeaders .= $this->SmtpHeaderAdress("CC");
		$lcHeaders .= $this->SmtpHeaderAdress("BCC");
		$lcHeaders .= !empty($this->cLanguage)	? "Content-language:".$this->cLanguage. PHP_EOL	: "" ;	
		$lcHeaders .= !empty($this->cDescription)? "Content-Description:".ucfirst(strtolower($this->cDescription)). PHP_EOL : "" ;	
		$lcHeaders .= !empty($this->cDescription)? "Thread-Topic:".ucfirst(strtolower($this->cSubject)). PHP_EOL : "" ;	
		$lcHeaders .= !empty($this->cOrganization)? "Organization:".ucwords($this->cOrganization). PHP_EOL : "" ;
		$lcHeaders .= !empty($this->cKeywords) ? "Keywords:".ucwords(strtolower($this->cKeywords)). PHP_EOL : "" ;	
		$lcHeaders .= $this->nDisposition==1 ? "Return-Receipt-To:<".$this->cFrom.">". PHP_EOL : "" ;
		$lcHeaders .= $this->nDisposition==1 ? "Disposition-Notification-To:<".$this->cFrom.">". PHP_EOL : "" ;	
		$lcHeaders .= "PostingVersion:".phpversion(). PHP_EOL;
		$lcHeaders .= "Importance:".$this->nImportance	. PHP_EOL; 
		$lcHeaders .= "Priority:".$this->nPriority. PHP_EOL; 
		$lcHeaders .= "X-Priority:".$this->nPriority. PHP_EOL;
		$lcHeaders .= "X-Mailer: ".$this->cXMailer. PHP_EOL;
		$lcHeaders .= "X-CreateBy: ".$this->cXCreateBy. PHP_EOL;
		$lcHeaders .= "MIME-Version: 1.0" . PHP_EOL;
		$lcHeaders .= "Content-type: ".$this->cContentType."; charset=".$this->cCharSet. PHP_EOL;
		return $lcHeaders;
	}
	private function SmtpHeaderAdress($tcType) {
		$mails=$this->aHeadersAdress[$tcType];
		$mails=empty($mails) ? "" : trim(strtolower($tcType)).":".$mails.PHP_EOL;
		return $mails;
	}
	private function CheckParameters($tcParameters) {
		$llCorrect=true;
		if(!empty($tcParameters)) {
			if ($this->nAuthMode==1){
				$laParameters = explode(",", $tcParameters);
				foreach ($laParameters as $elento => $lcParameter) {
					$this->cFault.=empty($this->$lcParameter) ? "Error: ".$lcParameter."! No especificado. " : "";
					$llCorrect=false;
				}
			}
		} else {
			$this->cFault.="Error: No hay parametros a validar. ";
			$llCorrect=false;
		}	
		
		return $llCorrect;
	}
	private function SetSmtpParameter($tcAction="SWITCH"){
		$lcSMTP = ($tcAction=="SWITCH"?$this->cServer:$this->cDefaultServer);
		$lnSmtpPort = ($tcAction=="SWITCH"?$this->nServerPort:$this->nDefaultServerPort);
		$lcFrom = ($tcAction=="SWITCH"?$this->cFrom:""); 
		
		if(strtoupper($this->cDefaultServer)<>strtoupper($lcSMTP)){
			@ini_set("SMTP",$lcSMTP);
		}
		if($this->nDefaultServerPort<>$lnSmtpPort){
			@ini_set("smtp_port",$lnSmtpPort); 
		}
		@ini_set("sendmail_from",$lcFrom); 
	}
	private function EncodeIso88591($tcString){
		$tcStringIso = "=?iso-8859-1?q?";
		for($lnChar=0;$lnChar<strlen($tcString);$lnChar++){
			if(ord(substr($tcString,$lnChar,1))<1 || ord(substr($tcString,$lnChar,1))>127){
				$lnASCII  = ord($tcString[$lnChar]);
				$lcASCII  = strtoupper(dechex($lnASCII));
				$tcStringIso.="=".$lcASCII ;
			}else{
				$tcStringIso.=substr($tcString,$lnChar,1);
			}
		}
		$tcStringIso.="?= ";
		return($tcStringIso);
	}	
	function SendMail(){
		$llReturn = false;
		$lnInicio = microtime(true);
		$lcHeader="";
		if($this->lParameters==true){
			switch ($this->cMailAddons) {
				case "PHPMailer":
					$lcHeader=$this->SmtpHeader();
					$lcHeader.="Type: PHPMailer" . PHP_EOL;
					$lcHeader.="Files Attach: ".$this->cAttach . PHP_EOL;
					
					try {
						if (!empty($this->cTo)==true){
							if (strpos(strtoupper($this->cTo),"N/A") === false) {					
								if (!$this->oMailAddons->send()) {
									$this->cFault="oMailAddons->send ".$this->oMailAddons->ErrorInfo." ->".$this->cTo;
								}else{
									$llReturn=true;
								}
							}else{
								$llReturn=true;
							}
						}else{
							$this->cFault="Destinatario en blanco o con formato no valido";
						}
					}catch (Exception $loError){
						$llReturn = false;
						$this->cFault= $loError->getMessage();
					}								
					$this->setPhpMailerMasterClearAttach();
					break;
					
				default:
					$this->SetSmtpParameter("SWITCH");
					$lcHeader=$this->SmtpHeader();

					try{
						if (!empty($this->cTo)==true){
							if (strpos(strtoupper($this->cTo),"N/A") === false) {
								$llReturn = @mail($this->cTo,$this->cSubject,$this->cHtmlBody,$lcHeader);
								$laLastError=error_get_last();
							}else{
								$llReturn=true;
							}
						}else{
							$this->cFault="Destinatario en blanco";
						}
					}catch (Exception $loError){
						$llReturn = false;
						$this->cFault= $loError->getMessage();
					}
					$this->SetSmtpParameter("DEFAULT");
			}
			
			if ($llReturn==false){
				if(isset($laLastError)==true){
					$this->cFault="Error: ".$laLastError["type"].", ".$laLastError["message"].". Programa ".$laLastError["file"].", Linea ".$laLastError["line"];
					$this->cFault=str_replace(">","",str_replace("<","",$this->cFault));
				}
			}
		}
		$lnFin = microtime(true);
		$lnTiempoEmpleado=ceil($lnFin-$lnInicio);
		$lcLogTo=substr($this->cTo,0,strpos($this->cTo,"@"))."@dominio.secreto.com";
		$lcLogReturn=($llReturn==true?"Enviado":"Sin enviar");
		$lcDateTime=date("Y-m-d H:i:s");
		
		// Log de envios
		if($this->lLog==true){
			$lcFileName = "logs/log-".date("Ymd").".html";
			$lcCurrent  =(is_file($lcFileName)==true?"":"<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'><html><head><meta http-equiv='Content-Type' content='text/html; charset=ISO-8859-1' /><meta http-equiv='Content-Language' content='es' /><meta http-equiv='Pragma' content='no-cache' /><meta http-equiv='Cache-Control' content='no-cache' /><meta http-equiv='expires' content='-1' ><title>Log de envios</title><link href='../css/mailserver.css' rel='stylesheet' type='text/css' /></head><body>");
			$lcCurrent .= "<h1>".$this->cSubjectSimple."(".$lcDateTime.")</h1>";
			$lcCurrent .= "<h3>Destinatarios</h3><ul><li>To: ".$lcLogTo."</li><li>CC: ".$this->cCc."</li><li>BCC: ".$this->cBcc."</li></ul>";
			$lcCurrent .= "<h3>Cabecera</h3><pre>".str_replace(">","",str_replace("<","",str_replace($this->cXMailer,"cXMaile",$lcHeader)))."</pre>";
			$lcCurrent .= "<h3>Mensaje</h3><div>".$this->cHtmlBody."</div>";
			$lcCurrent .= "<h3>Falla/Error</h3><pre>".$this->cFault."</pre>";
			$lcCurrent .= "<h3>Tiempo empleado</h3><pre>".$lnTiempoEmpleado." seg. </pre>";
			$lcCurrent .= "<h3>Resultado</h3><pre>".$lcLogReturn."</pre><hr/><br/><br/>".PHP_EOL;
			@file_put_contents($lcFileName, $lcCurrent, FILE_APPEND | LOCK_EX);
		}
		
		// Registro de errores persdonalizados
		if(!empty($this->cFault)){
			@file_put_contents(ini_get('error_log'),"[".$lcDateTime."] WebServiceMail Excepcion : ".$this->cFault.PHP_EOL, FILE_APPEND | LOCK_EX);
		}
		
		// Log de eventos con formato
		if($this->lEventList==true){
			$lcCurrent = $lcDateTime." | ".htmlentities($lcLogTo)." | ".htmlentities($this->cSubjectSimple)." | ".$lcLogReturn." (".$lnTiempoEmpleado." seg.) | ".$this->cAttach." ".PHP_EOL;
			@file_put_contents("eventos/eventlist-".date('Ym').".log", $lcCurrent, FILE_APPEND | LOCK_EX);
		}
		return $llReturn;
	}
}
?>