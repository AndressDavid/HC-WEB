<?php
	require_once (__DIR__ .'/../../../nucleo/publico/constantes.php') ;
	require_once (__DIR__ .'/../../complementos/nusoap-php7/0.95/lib/nusoap.php') ;

	$lcTitulo = "MailServerWSLD";
	$lcNombreEspacio = 'MailServerWSLD';
	define("WSTOKEN", "2qvd4B9qDpAVOjGtnBVorlNcLR7M0CmI8PeQAc6GlMl7ZaBjhUNZRolRaoxzg70dn4kETGySs9cjtl4G6oY7yufvuvYdtku1N4F8YrieYo9qJ0SbgvHr10j6zHmiGPzu");

	/*
	MODIFICABLE
	--- CLASES Y OBJETOS ADICIONALES ----
	Utilice esta sección del bloque para incluir clases y declarar objetos adicionales*/
	require_once (__DIR__ .'/config/constants.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Mail.php') ;
	// --- FIN DE CLASES Y OBJETOS ADICIONALES ---



	/*
	N O   M O D I F I C A B L E
	A continuación se realiza la configuración del servicio, si se cambia el urn o namespace el cliente debe cambiarlo al utilizarlo
	*/
	$loSoapServer = new soap_server();
	if(isset($loSoapServer)==true){

		/*
		N O   M O D I F I C A B L E
		A continuación se realiza la configuración del servicio, si se cambia el urn o namespace el cliente debe cambiarlo al utilizarlo
		*/
		$loSoapServer->configureWSDL($lcTitulo, 'urn:'.$lcNombreEspacio);

		/*
		MODIFICABLE
		 ######  ####### ####### ### #     # ###  #####  ### ####### #     #
		 #     # #       #        #  ##    #  #  #     #  #  #     # ##    #
		 #     # #       #        #  # #   #  #  #        #  #     # # #   #
		 #     # #####   #####    #  #  #  #  #  #        #  #     # #  #  #
		 #     # #       #        #  #   # #  #  #        #  #     # #   # #
		 #     # #       #        #  #    ##  #  #     #  #  #     # #    ##
		 ######  ####### #       ### #     # ###  #####  ### ####### #     #

		REGISTRO DE FUNCIONES Y DECLARACIÓN DE LAS MISMAS
		Las funciones se deben registrar en el webService para su uso, luego se deben declarar. Tenga en cuenta los parámetros de entrada y salida.
		*/
		// ---------------------------------------------------------------------------------------------------------------------------------------

		// REGISTRO DE FUNCIONES
		$lcNombreFuncion = 'SendMail';
		$loSoapServer->register($lcNombreFuncion,
									array('tcServer' => 'xsd:string',
										  'tnPort' => 'xsd:integer',
										  'tcUser' => 'xsd:string',
										  'tcPass' => 'xsd:string',
										  'tcFrom' => 'xsd:string',
										  'tcTO' => 'xsd:string',
										  'tcCC' => 'xsd:string',
										  'tcBCC' => 'xsd:string',
										  'tcSubject' => 'xsd:string',
										  'tcBody' => 'xsd:string',
										  'tnAuthMode' => 'xsd:integer',
										  'tnPriority' => 'xsd:integer',
										  'tnImportance' => 'xsd:integer',
										  'tnDisposition' => 'xsd:integer',
										  'tcOrganization' => 'xsd:string',
										  'tcKeywords' => 'xsd:string',
										  'tcDescription' => 'xsd:string'),
									array('return' => 'xsd:string'),
									'urn:'.$lcNombreEspacio,
									'urn:'.$lcNombreEspacio.'#'.$lcNombreFuncion,
									'rpc',
									'encoded',
									'Envia el e-mail y retorna el resultado');

		$lcNombreFuncion = 'SendMailAttach';
		$loSoapServer->register($lcNombreFuncion,									
									array('tcServer' => 'xsd:string',
										  'tnPort' => 'xsd:integer',
										  'tcUser' => 'xsd:string',
										  'tcPass' => 'xsd:string',
										  'tcFrom' => 'xsd:string',
										  'tcTO' => 'xsd:string',
										  'tcCC' => 'xsd:string',
										  'tcBCC' => 'xsd:string',
										  'tcSubject' => 'xsd:string',
										  'tcBody' => 'xsd:string',
										  'tnAuthMode' => 'xsd:integer',
										  'tnPriority' => 'xsd:integer',
										  'tnImportance' => 'xsd:integer',
										  'tnDisposition' => 'xsd:integer',
										  'tcOrganization' => 'xsd:string',
										  'tcKeywords' => 'xsd:string',
										  'tcDescription' => 'xsd:string',
										  'tcAttachServerFilesID' => 'xsd:string'),
									array('return' => 'xsd:string'),
									'urn:'.$lcNombreEspacio,
									'urn:'.$lcNombreEspacio.'#'.$lcNombreFuncion,
									'rpc',
									'encoded',
									'Envia el e-mail y retorna el resultado');

		$lcNombreFuncion = 'AttachMaxSize';
		$loSoapServer->register($lcNombreFuncion,									
									array('tnEmpty' => 'xsd:integer'),
									array('return' => 'xsd:integer'),
									'urn:'.$lcNombreEspacio,
									'urn:'.$lcNombreEspacio.'#'.$lcNombreFuncion,
									'rpc',
									'encoded',
									'Retorna el tamaño maximo permitido de adjuntos');		

		// DECLARACIÓN DE FUNCIONES
		// Se debe especificar un TOKEN para validar que el cliente este autorizado
		function SendMail($tcServer="", $tnPort, $tcUser, $tcPass, $tcFrom, $tcTO, $tcCC, $tcBCC, $tcSubject, $tcBody,$tnAuthMode,$tnPriority,$tnImportance,$tnDisposition,$tcOrganization,$tcKeywords,$tcDescription) {
			$lcFault="";
			try {
				$loSmtpMail = new SendMailWebServiceClass ($tcServer,$tnPort,$tcUser,$tcPass,$tcFrom,$tcTO,$tcCC,$tcBCC,$tcSubject,$tcBody,$tnAuthMode,$tnPriority,$tnImportance,$tnDisposition,$tcOrganization,$tcKeywords,$tcDescription,"",__DIR__);
				$lcResultado = ($loSmtpMail->SendMail()==false?'0':'1');
				$lcFault=$loSmtpMail->cFault;
			}catch (Exception $loError){
				$lcResultado="0";
				$lcFault=$loError->getMessage();
			}
			@file_put_contents(__DIR__ ."/calls/calls-".date('Ym').".log", "[".date("Y-m-d H:i:s")."] ".formatEmailLog($tcTO)." | ".$tcSubject." | ".$lcResultado." | ".$lcFault.PHP_EOL, FILE_APPEND | LOCK_EX);
			$lcFault="";
			return "<RESULT>".$lcResultado."</RESULT><FAULT>".$lcFault."</FAULT>";
		}
		function SendMailAttach($tcServer, $tnPort=0, $tcUser, $tcPass, $tcFrom, $tcTO, $tcCC, $tcBCC, $tcSubject, $tcBody,$tnAuthMode,$tnPriority,$tnImportance,$tnDisposition,$tcOrganization,$tcKeywords,$tcDescription,$tcAttachServerFilesID) {
			$lcFault="";
			try {
				$loSmtpMail = new SendMailWebServiceClass ($tcServer,$tnPort,$tcUser,$tcPass,$tcFrom,$tcTO,$tcCC,$tcBCC,$tcSubject,$tcBody,$tnAuthMode,$tnPriority,$tnImportance,$tnDisposition,$tcOrganization,$tcKeywords,$tcDescription,$tcAttachServerFilesID,__DIR__);
				$loSmtpMail->nAttachMaxSize = AttachMaxSize(0);
				$lcResultado = ($loSmtpMail->SendMail()==false?'0':'1');	
			}catch (Exception $loError){
				$lcResultado="0";
				$lcFault=$loError->getMessage();
			}
			@file_put_contents(__DIR__ ."/calls/calls-".date('Ym').".log", "[".date("Y-m-d H:i:s")."] ".formatEmailLog($tcTO)." | ".$tcSubject." | ".$lcResultado." | ".$lcFault.PHP_EOL, FILE_APPEND | LOCK_EX);
			$lcFault="";
			return "<RESULT>".$lcResultado."</RESULT><FAULT>".$lcFault."</FAULT>";
		}
		function AttachMaxSize($tnEmpty=0) {
			$lnAttachMaxSize = 8388608;
			return $lnAttachMaxSize;
		}
		// ---------------------------------------------------------------------------------------------------------------------------------------
		function formatEmailLog($tcEmailFormatLog=''){
			$tcEmailFormatLog = mb_strtolower(trim(str_replace(' ','',str_replace(';',',',$tcEmailFormatLog))));
			$laEmailFormatLog = explode(',',$tcEmailFormatLog);
			$lcEmailReturnLog = "";
			arsort($laEmailFormatLog);	
			foreach ($laEmailFormatLog as $lcEmailFormatLog) {					
				if(strlen($lcEmailFormatLog)>0){
					$lnRemplaza=rand(2,3);
					$lcEmailFormatLog=substr($lcEmailFormatLog,0,strpos($lcEmailFormatLog,"@")-$lnRemplaza).str_repeat("*",$lnRemplaza)."@dominio.secreto.com";
					$lcEmailReturnLog = $lcEmailReturnLog.(empty($lcEmailReturnLog )?'':', ').$lcEmailFormatLog;
				}
			}
			return trim($lcEmailReturnLog);
		}

		/*
		N O   M O D I F I C A B L E
		INICIO DEL SERVICIO
		Las instruccione abajo contenidas inician el servicio y procesan la solicitud
		*/
		$loSoapServer->service(file_get_contents("php://input"));
	}else{
		http_response_code(500);
	}
	exit();
?>