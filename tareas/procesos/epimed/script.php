<?php
	$lvResult = null; 

	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionFunciones.php');	
	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionTareaManejador.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Db.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Epimed.php') ;

	$goAplicacionTareaManejador = new CRON\AplicacionTareaManejador(__FILE__,__DIR__);
	
	if($goAplicacionTareaManejador){
		try{
			$goAplicacionTareaManejador->evento("Procesando función principal");
			tareaProgramaPrincipal();
			$goAplicacionTareaManejador->evento("Función procesada correctamente");

		} catch (Exception $loError) {
			$goAplicacionTareaManejador->error($loError->getMessage());
		}
		unset($goAplicacionTareaManejador);
	}
	
	function tareaProgramaPrincipal(){
		global $goAplicacionTareaManejador;
		global $goDb;
		
		$lcNameSpaceWSA = 'http://www.w3.org/2005/08/addressing';
		$lcUrlSoapAction = 'http://tempuri.org/IEwsClient/SendXml_DynamicToken';
		$lcUrlEndPoint = "https://ewsclient.epimedmonitor.com/EwsClient.svc";
		$lcUrlWsdl = $lcUrlEndPoint."?wsdl";

		try {
			$lnProcesados = 0;
			$lnActualizados = 0;
			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lcFecha = $ltAhora->format("Ymd");
			$lcHora  = $ltAhora->format("His");
			$loAplicacionFunciones = new NUCLEO\AplicacionFunciones();
			$lcAhora = $loAplicacionFunciones->formatFechaHora('fechahora', $lcFecha.$lcHora, '-', ':', ('T'));
				
			if(isset($goDb)){	
				$goAplicacionTareaManejador->evento('Consultando ingresos por generar');
				$loEpimed = new NUCLEO\Epimed;
				$laEpimed = $loEpimed->consultaEpimed();
				$lcCodigoShaio = $goDb->obtenerTabmae1('trim(DE2TMA)', 'EPIMED', "CL1TMA='CODSHAIO' AND ESTTMA=''", null, '');
				
				if(isset($laEpimed)==true){
					if (count($laEpimed)>0) {
						foreach($laEpimed as $laDatosEpimed){
							$lnProcesados += 1;
							$lcFechaNacimiento = $loAplicacionFunciones->formatFechaHora('fecha', $laDatosEpimed['FECHA_NACIMIENTO'], '-');
							$lcFechaIngreso = $loAplicacionFunciones->formatFechaHora('fecha', $laDatosEpimed['FECHA_INGRESO'], '-');
							$lcFechaHoraEgreso = intval($laDatosEpimed['FECHA_EGRESO'])>0 ? ($loAplicacionFunciones->formatFechaHora('fechahora', strval($laDatosEpimed['FECHA_EGRESO']).strval($laDatosEpimed['HORA_EGRESO']), '-', ':', ('T'))) : '';
							$lcFechaHoraInternacion = $loAplicacionFunciones->formatFechaHora('fechahora', strval($laDatosEpimed['FECHA_ENTRADA']).strval($laDatosEpimed['HORA_ENTRADA']), '-', ':', ('T'));
							$lcFechaHoraRecibioAlta = $loAplicacionFunciones->formatFechaHora('fechahora', strval($laDatosEpimed['FECHA_SALIDA']).strval($laDatosEpimed['HORA_SALIDA']), '-', ':', ('T'));
							$lcFechaHoraRecibioAlta = $laDatosEpimed['ACCION']==='S' ? $lcFechaHoraRecibioAlta : '';
							
							$laDatos =['Itens' =>	
											['Item'=> [
														'MEDICALRECORD'=>$laDatosEpimed['NRO_IDE'],
														'DOCUMENTTYPECODE'=>'CO.'.$laDatosEpimed['HOM_TIPOIDE'],
														'DOCUMENTNUMBER'=>$laDatosEpimed['NRO_IDE'],
														'PATIENTNAME'=>$laDatosEpimed['NOMBRE_PACIENTE'],
														'RESPONSIBLENAME'=>'',
														'RESPONSIBLEDOCUMENTTYPECODE'=>'',
														'RESPONSIBLEDOCUMENT'=>'',
														'GENDER'=>$laDatosEpimed['GENERO'],
														'BIRTHDATE'=>$lcFechaNacimiento,
														'WEIGHT'=>'',
														'HEIGHT'=>'',
														'HOSPITALCODE'=>$lcCodigoShaio,
														'HOSPITALADMISSIONNUMBER'=>$laDatosEpimed['INGRESO'],
														'HOSPITALADMISSIONDATE'=>$lcFechaIngreso,
														'UNITCODE'=>$laDatosEpimed['SECCION'],
														'UNITADMISSIONNUMBER'=>$laDatosEpimed['INGRESO'],
														'UNITADMISSIONDATETIME'=>$lcFechaHoraInternacion,
														'BEDCODE'=>$laDatosEpimed['HABITACION'],
														'DISCHARGECAUSE'=>trim($laDatosEpimed['MOTIVO_SALIDA']),
														'HOSPITALDISCHARGEDATE'=>$lcFechaHoraEgreso,
														'HOSPITALHEALTHINSURANCECODE'=>'',
														'MEDICALDISCHARGEDATE'=>$lcFechaHoraRecibioAlta,
														'CREATEDATE'=>$lcAhora,
													]
											]
										];
							
							$laParametros = array(
								'SendXml_DynamicToken',
								'dynamicToken'=>'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1bmlxdWVfbmFtZSI6ImVhNTkxZDBiLWNlYjUtNDAwOS1iZWNjLWY0ZDE0YTRkNGFhZCIsImh0dHA6Ly9zY2hlbWFzLnhtbHNvYXAub3JnL3dzLzIwMDUvMDUvaWRlbnRpdHkvY2xhaW1zL3NpZCI6ImVhNTkxZDBiLWNlYjUtNDAwOS1iZWNjLWY0ZDE0YTRkNGFhZCIsImlzcyI6IkVwaW1lZCIsImF1ZCI6Imh0dHA6Ly93d3cuZXBpbWVkc29sdXRpb25zLmNvbSIsImV4cCI6MTYxNjQxNzk1MiwibmJmIjoxNjE2NDE3MzUyfQ.MtkfaTbK1vbnNk-Uu7aohYswtdP0PgmVu5kveyQNXc8',
								'integrationHospitalType'=>'e61d34c6-8527-4daf-86ff-199d96779f1d', 
								'xml'=>arrayToXml($laDatos)
							 );	
							
							$loSoapClient = new MSSoapClient($lcUrlWsdl, array(
							'soap_version' => SOAP_1_2,
							'trace' => 1,
							'location' => $lcUrlWsdl,
							'encoding' => 'UTF-8',
							'SOAPAction' => $lcUrlSoapAction));

							$laSoapHeader['Action'] = new SoapHeader($lcNameSpaceWSA, 'Action', $lcUrlSoapAction);
							$laSoapHeader['To'] = new SoapHeader($lcNameSpaceWSA, 'To', $lcUrlEndPoint);
							$loSoapClient->__setSoapHeaders($laSoapHeader);
			
							//fnEscribirLog(var_export($laParametros, true));
			
							try{
								$lvResult = $loSoapClient->SendXml_DynamicToken($laParametros);
								var_dump($lvResult); 
								
							}catch (SoapFault $exception){
								var_dump(get_class($exception));
								die($exception);
							}
							
							$llResultado = $loEpimed->actualizaEpimed($laDatosEpimed['CONSECUTIVO']);
							$lnActualizados+=($llResultado==true?1:0);
							$goAplicacionTareaManejador->evento(sprintf("%s el consecutivo %s del ingreso %s",($llResultado==true?"Actualizado":"No actualizado"),$laDatosEpimed['CONSECUTIVO'],$laDatosEpimed['INGRESO']));
						}
						$goAplicacionTareaManejador->evento(sprintf("Actualizados %s de %s",$lnActualizados,$lnProcesados));
					}else{
						$goAplicacionTareaManejador->evento("No hay registros para procesar.");
					}
				}else{
					$goAplicacionTareaManejador->evento("No hay registros para procesar..");
				}
			}
		}catch(Exception $loError){
			$goAplicacionTareaManejador->error($loError->getMessage());
		}				
	}
	
	function fnEscribirlog($tcMensaje, $tbEcho=false)
	{
		$lcRuta = __DIR__ . '/../logs/log_' . date('Ym');
		if (!is_dir($lcRuta)) { mkDir($lcRuta, 0777, true); }
		$lcFilelog = $lcRuta . '/logAccion_' . date('Ymd') . '.txt';
		$lcMensaje = date('y-m-d h:i:s') . ' | ' . $tcMensaje . "\n";

		$lnFile = fOpen($lcFilelog, 'a');
		fPuts($lnFile, $lcMensaje);
		fClose($lnFile);
		if ($tbEcho) { echo $lcMensaje; }
	}
	
	function arrayToXml($taArray, $tcRootKey = null, $toXml = null) {
		$loXml = $toXml;
		if ($loXml === null) {
			$loXml = new SimpleXMLElement($tcRootKey !== null ? $tcRootKey : '<Root/>');
		}
		foreach($taArray as $lcKey => $lvValue) {
			if (is_array($lvValue)) { 
				arrayToXml($lvValue, $lcKey, $loXml->addChild($lcKey));
			} else {
				$loXml->addChild($lcKey, $lvValue);
			}
		}
		return $loXml->asXML();
	}	
	
	class MSSoapClient extends SoapClient {
		private $cMsRequest = '';

		function __doRequest($tcRequest, $tcLocation, $tcAction, $tcVersion, $tlOneWay = NULL) {

			$tcRequest = str_replace('env:','soap:', $tcRequest);
			$tcRequest = str_replace(':env',':soap', $tcRequest);
			$tcRequest = str_replace('ns1','tem', $tcRequest);
			$tcRequest = str_replace('ns2','wsa', $tcRequest);
			$tcRequest = str_replace('root','Root', $tcRequest);
			
			$this->cMsRequest = $tcRequest;

			// parent call
			return parent::__doRequest($tcRequest, $tcLocation, $tcAction, $tcVersion, $tlOneWay);
		}
		
		public function getMsRequest(){
			return $this->cMsRequest;
		}
	}	
	