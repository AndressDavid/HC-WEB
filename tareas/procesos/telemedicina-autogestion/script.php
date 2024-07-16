<?php
	/*
	N O   M O D I F I C A B L E !
    #######    #    ######  #######    #
       #      # #   #     # #         # #
       #     #   #  #     # #        #   #
       #    #     # ######  #####   #     #
       #    ####### #   #   #       #######
       #    #     # #    #  #       #     #
       #    #     # #     # ####### #     #
	El objetivo de esta plantilla es definir el script base para la ejecución de una tarea programada. NO modifique el nombre de la función principal y la ejecución del mismo.
	Usted puede personalizar el bloque DEFINICION agregando librería de objetos, modificando la función tareaProgramaPrincipal y adicionando las funciones que requiera.
	*/
	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionTareaManejador.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Db.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionFunciones.php');

	$goAplicacionTareaManejador = new CRON\AplicacionTareaManejador(__FILE__,__DIR__);

	/*
	MODIFICABLE
	--- CLASES Y OBJETOS ADICIONALES ----
	Utilice esta sección del bloque para incluir clases y declarar objetos adicionales*/
	// --- FIN DE CLASES Y OBJETOS ADICIONALES ---


	/*
	N O   M O D I F I C A B L E
	El script solo debe ejecutar la función tareaProgramaPrincipal. Personalice esta según sus necesidades
	*/
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


	/*
	MODIFICABLE
	 ######  ####### ####### ### #     # ###  #####  ### ####### #     #
	 #     # #       #        #  ##    #  #  #     #  #  #     # ##    #
	 #     # #       #        #  # #   #  #  #        #  #     # # #   #
	 #     # #####   #####    #  #  #  #  #  #        #  #     # #  #  #
	 #     # #       #        #  #   # #  #  #        #  #     # #   # #
	 #     # #       #        #  #    ##  #  #     #  #  #     # #    ##
	 ######  ####### #       ### #     # ###  #####  ### ####### #     #

	FUNCIONA PRINCIPAL
	Haga uso de la función principal según el objetivo del script.
	*/
	function tareaProgramaPrincipal(){
		global $goAplicacionTareaManejador;
		global $goDb;
		
		$laAcciones = ['ACTUALIZACION'=>true, 'NOTIFICACIONES'=>false, 'RECORDATORIOS'=>false];

		try {
			$laRegistros=array();

			// Parametros
			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lcFecha = $ltAhora->format("Ymd");
			$lnFecha = intval($lcFecha);
			
			$ltRecordatorio = new \DateTime( $goDb->fechaHoraSistema() );
			$ltRecordatorio->add(new DateInterval('P2D'));
			$lcRecordatorio = $ltRecordatorio->format("Ymd");
			$lnRecordatorio = intval($lcRecordatorio);
			
			$loAplicacionFunciones = new NUCLEO\AplicacionFunciones();

			$laMailSettings= ['tcServer' => 'mail.shaio.org',
							  'tnPort' => 25,
							  'tcUser' => 'no-respoder@shaio.org',
							  'tcPass' => '********',
							  'tcFrom' => 'no-respoder@shaio.org',
							  'tcTO' => '',
							  'tcCC' => '',
							  'tcBCC' => '',
							  'tcSubject' => '',
							  'tcBody' => '',
							  'tnAuthMode' => 0,
							  'tnPriority' => 1,
							  'tnImportance' => 1,
							  'tnDisposition' => 0,
							  'tcOrganization' => 'Fundacion Clincia Shaio',
							  'tcKeywords' => 'Fundacion Clincia Shaio, Telemedicina',
							  'tcDescription' => 'Fundacion Clincia Shaio, Telemedicina'];
							  
			$laMailPifContent = [
									'notificacion'=>['bodyFile'=>'telemedicina.htm', 'appendFile'=>'telemedicina-registro.htm'],
									'recordatorio'=>['bodyFile'=>'telemedicina-recordatorio.htm']
								];


			if(isset($goDb)){

				$laCampos = [
								'J.TIDCIT','J.NIDCIT','J.CCICIT','J.CCOCIT','J.EVOCIT','J.NINCIT','J.FRLCIT','J.NOTFEC','J.RECENV','J.RECFEC',
								'J.CD2CIT CLASE','J.COACIT PRO_CODIGO','J.FRLCIT CITA_FECHA','J.HOCCIT CITA_HORA',
								'E.CODESP ESP_CODIGO','E.DESESP ESPECIALIDAD',
								'C.CODCUP CUP_CODIGO','C.DESCUP CUP_NOMBRE',
								'D.ESTORD',
								"TRIM(IFNULL((SELECT TRIM(DE1TMA) FROM TABMAE WHERE TIPTMA ='ESTPRORD' AND INT(CL1TMA)=D.ESTORD), '')) ESTADO_ORDEN",
								"TRIM(P.NM1PAC) || ' ' || TRIM(P.NM2PAC) || ' ' || TRIM(P.AP1PAC) || ' ' || TRIM(P.AP2PAC) PACIENTE", 'TRIM(P.MAIPAC) EMAIL1', 
								"TRIM(IFNULL(X.CORREO,'UNREGISTER')) EMAIL2",
							];
							
				/* ACTUALIZACION DE ESTADOS */
				if($laAcciones['ACTUALIZACION']==true){
					$laEstados = [
									"3"=>["CODIGO"=>"1","NOMBRE"=>"ATENDIDO"],
									"6"=>["CODIGO"=>"2","NOMBRE"=>"CANCELADO"],
								];
					$lnPorProcesar = 0;
					$lnActualizados = 0;
					
					$laRegistros = $goDb->select($laCampos)
										->tabla('JTMCIT J')
										->leftJoin('RIACUP C', 'C.CODCUP=J.COACIT', null)
										->leftJoin('RIAORD D', 'J.TIDCIT=D.TIDORD AND J.NIDCIT=D.NIDORD AND J.CCICIT=D.CCIORD', null)
										->leftJoin('RIAESPE E', 'E.CODESP=J.CODCIT', null)
										->leftJoin('RIAPAC P', 'J.TIDCIT=P.TIDPAC AND J.NIDCIT=P.NIDPAC', null)
										->leftJoin('PACMENSEG X', 'X.IDTIPO=J.TIDCIT AND X.IDNUME=J.NIDCIT', null)
										->where('J.ESTADO', '=' , '') 
										->in('D.ESTORD', [3, 6])
										->getAll('array');
										// ->where('J.FRLCIT', '<' , $lnFecha)


					if(isset($laRegistros)==true){
						if(is_array($laRegistros)==true){
							
							$lnPorProcesar = count($laRegistros);
							if($lnPorProcesar>0){
								foreach($laRegistros as $lnFila=>$laRegistro){
									$lcEstadoOrden=strval($laRegistro['ESTORD']);
									if(isset($laEstados[$lcEstadoOrden])==true){
										$laDatos = ['ESTADO'=>$laEstados[$lcEstadoOrden]['CODIGO']];
										$goDb->tabla('JTMCIT')
											->where('TIDCIT','=',$laRegistro['TIDCIT'])
											->where('NIDCIT','=',$laRegistro['NIDCIT'])
											->where('CCICIT','=',$laRegistro['CCICIT'])
											->where('CCOCIT','=',$laRegistro['CCOCIT'])
											->where('EVOCIT','=',$laRegistro['EVOCIT'])
											->actualizar($laDatos);	
										$lnActualizados+=1;									
									}
								}
								$goAplicacionTareaManejador->evento("Se actualizaron ".$lnActualizados." estados de ".$lnPorProcesar." de forma automatica");
							}else{
								$goAplicacionTareaManejador->evento("No hay registros estados para procesar");
							}
						}else{
							$goAplicacionTareaManejador->evento("No hay registros estados para procesar");
						}
					}else{
						$goAplicacionTareaManejador->evento("No hay registros de estados para procesar");
					}
				}else{
						$goAplicacionTareaManejador->evento("Accion de ACTUALIZACION deshabilitada");
				}
				/* FIN DE ACTUALIZACION DE ESTADOS */							
							
							
				/* NOTIFICACIONES PENDIENTES POR ENVIÓ */
				if($laAcciones['NOTIFICACIONES']==true){
					$lnPorProcesar = 0;
					$lnActualizados = 0;
					
					$laRegistros = $goDb->select($laCampos)
										->tabla('JTMCIT J')
										->leftJoin('RIACUP C', 'C.CODCUP=J.COACIT', null)
										->leftJoin('RIAORD D', 'J.TIDCIT=D.TIDORD AND J.NIDCIT=D.NIDORD AND J.CCICIT=D.CCIORD', null)
										->leftJoin('RIAESPE E', 'E.CODESP=J.CODCIT', null)
										->leftJoin('RIAPAC P', 'J.TIDCIT=P.TIDPAC AND J.NIDCIT=P.NIDPAC', null)
										->leftJoin('PACMENSEG X', 'X.IDTIPO=J.TIDCIT AND X.IDNUME=J.NIDCIT', null)
										->where('J.ESTADO', '=' , '')
										->where('J.NOTFEC', '=' ,  0)
										->where('D.ESTORD', '<>',  3)
										->where('D.ESTORD', '<>',  6)
										->where('P.MAIPAC', '<>', '')
										->getAll('array');


					if(isset($laRegistros)==true){
						if(is_array($laRegistros)==true){
							
							$lnPorProcesar = count($laRegistros);
							if($lnPorProcesar>0){
								foreach($laRegistros as $lnFila=>$laRegistro){
									// El paciente no esta registrado en el portal pacientes
									$llRegistrado = ($laRegistro['EMAIL2']=='UNREGISTER'?false:true);

										
									$laRegistro['PACIENTE'] = ucwords(trim(strtolower(strval($laRegistro['PACIENTE']))));
									$laRegistro['CITA_FECHA'] = $loAplicacionFunciones->formatFechaHora('fecha', $laRegistro['CITA_FECHA']);
									$laRegistro['CITA_HORA'] = $loAplicacionFunciones->formatFechaHora('hora', $laRegistro['CITA_HORA']);
									$laRegistro['fromMail'] = $laMailSettings['tcFrom'];

									require_once (__DIR__ .'/../../../webservice/complementos/nusoap-php7/0.95/lib/nusoap.php');
									$lcUrlWsdl="http://hcwp.shaio.org/webservice/publico/mail/webservice.php?wsdl"; //url del servicio
									$lcDateTime=date("Y-m-d H:i:s");
									
									$laMailSettings['tcSubject'] = 'Cita por Telemedicina | '.$laRegistro['ESPECIALIDAD'];
									$laMailSettings['tcTO'] = $laRegistro['EMAIL1'];
									$laMailSettings['tcBody'] = obtenerCuerpoMensaje($laMailPifContent['notificacion'], $laRegistro, !$llRegistrado, $laRegistro['PACIENTE']);

									if(!empty($laMailSettings['tcBody'])){
										$loClient = new nusoap_client($lcUrlWsdl,'wsdl');
										$lcError = $loClient->getError();

										$lcResult="";
										if ($lcError) {
											$goAplicacionTareaManejador->evento(sprintf("Constructor error: %s",$lcError));
										}else{
											$lcResult = $loClient->call('SendMail',$laMailSettings);
											if (preg_match('/<RESULT>(.*?)<\/RESULT>/', $lcResult, $laMatch) == 1) {
												if(intval($laMatch[1])>0){
													$laDatos = ['NOTFEC'=>$lcFecha];
													$goDb->tabla('JTMCIT')
														->where('TIDCIT','=',$laRegistro['TIDCIT'])
														->where('NIDCIT','=',$laRegistro['NIDCIT'])
														->where('CCICIT','=',$laRegistro['CCICIT'])
														->where('CCOCIT','=',$laRegistro['CCOCIT'])
														->where('EVOCIT','=',$laRegistro['EVOCIT'])
														->actualizar($laDatos);	
													$lnActualizados+=1;
												}else{
													$goAplicacionTareaManejador->evento(sprintf("No se envio el correo electronico de la cita %s%s %s",$laRegistro['TIDCIT'],$laRegistro['NIDCIT'],$laRegistro['CCICIT']));
												}
											}else{
												$goAplicacionTareaManejador->evento("No se pudo validar el resultado de ".$lcResult);
											}
										}
									}else{
										$goAplicacionTareaManejador->evento("No hay cuerpo del mensaje para enviar");
									}
								}
								$goAplicacionTareaManejador->evento("Se enviaron ".$lnActualizados." notificaciones de ".$lnPorProcesar);
							}else{
								$goAplicacionTareaManejador->evento("No hay notificaciones por enviar");
							}
						}else{
							$goAplicacionTareaManejador->evento("No hay registros para procesar");
						}
					}else{
						$goAplicacionTareaManejador->evento("No hay notificaciones pendientes por enviar");
					}
				}else{
					$goAplicacionTareaManejador->evento("Accion de NOTIFICACIONES deshabilitada");
				}
				/* FIN NOTIFICACIONES PENDIENTES POR ENVIÓ */
				
				
				
				/* RECORDATORIOS PENDIENTES POR ENVIÓ */
				if($laAcciones['RECORDATORIOS']==true){
					$lnPorProcesar = 0;
					$lnActualizados = 0;
					
					$laRegistros = $goDb->select($laCampos)
										->tabla('JTMCIT J')
										->leftJoin('RIACUP C', 'C.CODCUP=J.COACIT', null)
										->leftJoin('RIAORD D', 'J.TIDCIT=D.TIDORD AND J.NIDCIT=D.NIDORD AND J.CCICIT=D.CCIORD', null)
										->leftJoin('RIAESPE E', 'E.CODESP=J.CODCIT', null)
										->leftJoin('RIAPAC P', 'J.TIDCIT=P.TIDPAC AND J.NIDCIT=P.NIDPAC', null)
										->leftJoin('PACMENSEG X', 'X.IDTIPO=J.TIDCIT AND X.IDNUME=J.NIDCIT', null)
										->where('J.ESTADO', '=' , '')
										->where('J.RECFEC', '=' ,  0)
										->where('J.FRLCIT', '=' ,  $lnRecordatorio)
										->where('D.ESTORD', '<>',  3)
										->where('D.ESTORD', '<>',  6)
										->where('P.MAIPAC', '<>', '')
										->getAll('array');


					if(isset($laRegistros)==true){
						if(is_array($laRegistros)==true){
							
							$lnPorProcesar = count($laRegistros);
							if($lnPorProcesar>0){
								foreach($laRegistros as $lnFila=>$laRegistro){
									// El paciente no esta registrado en el portal pacientes
									$llRegistrado = ($laRegistro['EMAIL2']=='UNREGISTER'?false:true);

										
									$laRegistro['PACIENTE'] = ucwords(trim(strtolower(strval($laRegistro['PACIENTE']))));
									$laRegistro['CITA_FECHA'] = $loAplicacionFunciones->formatFechaHora('fecha', $laRegistro['CITA_FECHA']);
									$laRegistro['CITA_HORA'] = $loAplicacionFunciones->formatFechaHora('hora', $laRegistro['CITA_HORA']);
									$laRegistro['fromMail'] = $laMailSettings['tcFrom'];

									require_once (__DIR__ .'/../../../webservice/complementos/nusoap-php7/0.95/lib/nusoap.php');
									$lcUrlWsdl="http://hcwp.shaio.org/webservice/publico/mail/webservice.php?wsdl"; //url del servicio
									$lcDateTime=date("Y-m-d H:i:s");
									
									$laMailSettings['tcSubject'] = 'Recordatorio de cita por Telemedicina | '.$laRegistro['ESPECIALIDAD'];
									$laMailSettings['tcTO'] = $laRegistro['EMAIL1'];
									$laMailSettings['tcBody'] = obtenerCuerpoMensaje($laMailPifContent['recordatorio'], $laRegistro, !$llRegistrado, $laRegistro['PACIENTE']);

									if(!empty($laMailSettings['tcBody'])){
										$loClient = new nusoap_client($lcUrlWsdl,'wsdl');
										$lcError = $loClient->getError();

										$lcResult="";
										if ($lcError) {
											$goAplicacionTareaManejador->evento(sprintf("Constructor error: %s",$lcError));
										}else{
											$lcResult = $loClient->call('SendMail',$laMailSettings);
											if (preg_match('/<RESULT>(.*?)<\/RESULT>/', $lcResult, $laMatch) == 1) {
												if(intval($laMatch[1])>0){
													$laDatos = ['RECFEC'=>$lcFecha, 'RECENV'=>(intval($laRegistro['RECENV'])+1)];
													$goDb->tabla('JTMCIT')
														->where('TIDCIT','=',$laRegistro['TIDCIT'])
														->where('NIDCIT','=',$laRegistro['NIDCIT'])
														->where('CCICIT','=',$laRegistro['CCICIT'])
														->where('CCOCIT','=',$laRegistro['CCOCIT'])
														->where('EVOCIT','=',$laRegistro['EVOCIT'])
														->actualizar($laDatos);	
													$lnActualizados+=1;
												}else{
													$goAplicacionTareaManejador->evento(sprintf("No se envio el correo electronico de la cita %s%s %s",$laRegistro['TIDCIT'],$laRegistro['NIDCIT'],$laRegistro['CCICIT']));
												}
											}else{
												$goAplicacionTareaManejador->evento("No se pudo validar el resultado de ".$lcResult);
											}
										}
									}else{
										$goAplicacionTareaManejador->evento("No hay cuerpo del mensaje para enviar");
									}
								}
								$goAplicacionTareaManejador->evento("Se enviaron ".$lnActualizados." recordatorios de ".$lnPorProcesar." para el ".$lcRecordatorio);
							}else{
								$goAplicacionTareaManejador->evento("No hay recordatorios por enviar");
							}
						}else{
							$goAplicacionTareaManejador->evento("No hay registros para procesar");
						}
					}else{
						$goAplicacionTareaManejador->evento("No hay notificaciones pendientes por enviar");
					}
				}else{
					$goAplicacionTareaManejador->evento("Accion de RECORDATORIOS deshabilitada");
				}
				/* FIN RECORDATORIOS PENDEINTES POR ENVIO*/				
				
				

			}else{
				$goAplicacionTareaManejador->evento("No se tiene acceso a la base de datos");
			}

		} catch(Exception $loError){
			$goAplicacionTareaManejador->error($loError->getMessage());
		}

	}
	//	--- FIN FUNCION PRINCIPAL --

	/*
	--- FUNCIONES ADICIONALES ---
	En este bloque escriba las funciones adicionales que requiera. Recuerde que estas se encuentra disponibles únicamente en el entorno CRON el cual se ejecuta por una tubería distinta a la principal.*/
	function obtenerCuerpoMensaje($taPifContent=array(), $taDatos=array(), $tlAppend=true, $tcNombrePaciente=''){
		$lcContent = '';
		if(is_array($taPifContent)==true){
			
			// Cuerpo del mensaje
			if(isset($taPifContent['bodyFile'])==true){
				$lcFileAux = __DIR__ .'/../../../pifs/'.$taPifContent['bodyFile'];
				if(is_file($lcFileAux)==true){
					$lcContent = file_get_contents($lcFileAux);
				}
			}
		
			// Bloque paraadiciones especiales
			$lcAppend='';
			if($tlAppend==true){
				if(isset($taPifContent['appendFile'])==true){
					$lcFileAux = __DIR__ .'/../../../pifs/'.$taPifContent['appendFile'];
					if(is_file($lcFileAux)==true){					
						$lcAppend=file_get_contents($lcFileAux);
					}
				}
			}
			$lcContent = str_replace("{appendFile}", $lcAppend, $lcContent);
			
			if(is_array($taDatos)==true){
				$laFields = array_keys($taDatos);
				foreach($laFields as $lcFiled){
					if(isset($taDatos[$lcFiled])==true){
						$lcContent = str_replace(sprintf("{%s}",$lcFiled), htmlentities($taDatos[$lcFiled]), $lcContent);
					}
				}
			}
		}

		return $lcContent;
	}
	// --- FIN FUNCIONES ADICIONALES ---
?>