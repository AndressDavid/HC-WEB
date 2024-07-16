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
	require_once (__DIR__ .'/../../../nucleo/controlador/class.ApiMxtoolbox.php');

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
		
		$lcApiMxtoolboxKey='db40cfef-7ca3-4210-9ac3-400cf5e88f3c,c9093bcd-e833-46d2-b2bf-8d7c121f7f53,d9303766-4ad1-4e5b-a7a3-669dfa15b87d';
		$lnDiasLimpiar=365;

		try {
			if(isset($goDb)){
				$lcEntorno = $goDb->obtenerEntorno();
				$ltAhora = new DateTime($goDb->fechaHoraSistema());
				$lnFecha=$ltAhora->format("Ymd");
				$lnHora=$ltAhora->format("H");
				$lcTabla = 'MXTLBX';

				
				// INSERTANDO REGISTRO 
				if ($lcEntorno!='desarrollo'){
					$loRegistros = $goDb->count('*','REGISTROS')
											  ->tabla($lcTabla)
											  ->where('FECMXT', '=', $lnFecha)
											  ->where('HORMXT', '=', $lnHora)
											  ->get('array');
					if(isset($loRegistros)==true){
						if($loRegistros["REGISTROS"]<=0){
							$loApiMxtoolbox = new NUCLEO\ApiMxtoolbox($lcApiMxtoolboxKey);
							$laInfo = $loApiMxtoolbox->getInfo();

							foreach($laInfo as $lnToken => $laToken){
								if(is_array($laToken)==true){
									if(isset($laToken['usage']['data']) && isset($laToken['monitor']['data'])){
										if(is_object($laToken['usage']['data']) && is_array($laToken['monitor']['data'])){
											foreach($laToken['monitor']['data'] as $lnMonitor => $laMonitor){
												if(is_object($laMonitor)==true){
													
													$lcMonitorStatusSummary = trim(substr($laMonitor->StatusSummary,0,250));
													$lnMonitorFailing = count($laMonitor->Failing);
													$lnMonitorWarnings = count($laMonitor->Warnings);
													
													if($lnMonitorFailing>0 && $lnMonitorWarnings==0){
														if(!empty($lcMonitorStatusSummary)){
															$laMSS = array();
															$laMonitorStatusSummary = explode( ',', $lcMonitorStatusSummary );
															if(is_array($laMonitorStatusSummary)==true){
																if(count($laMonitorStatusSummary)>0){
																	foreach($laMonitorStatusSummary as $lnKeyMSS => $lcKeyMSS){ 
																		$lcKeyMSS = trim($lcKeyMSS);
																		$lcFileMSS = __DIR__ . '/' .strtoupper(str_replace(' ', '_',trim($lcKeyMSS)));
																		
																		if(file_exists($lcFileMSS)==false){
																			array_push($laMSS,$lcKeyMSS);
																			$goAplicacionTareaManejador->evento(sprintf("No existe exclusion para la lista %s, se esperaba %s ",$lcKeyMSS,$lcFileMSS));
																		}else{
																			$goAplicacionTareaManejador->evento(sprintf("Existe una exclusion para la lista %s, se esperaba %s ",$lcKeyMSS,$lcFileMSS));
																		}
																	}
																}
															}
															
															$lnMonitorFailing = count($laMSS);
															$lcMonitorStatusSummary = ($lnMonitorFailing==0?'Not Blacklisted*':implode(',', $laMSS));
														}
													}
													
													
													$laDatos = array('FECMXT' => $lnFecha,
																	 'HORMXT' => $lnHora ,
																	 'TOKMXT' => $lnToken,
																	 'DNRMXT' => $laToken['usage']['data']->DnsRequests,
																	 'DNMMXT' => $laToken['usage']['data']->DnsMax,
																	 'DNOMXT' => $laToken['usage']['data']->DnsOverageErrors,
																	 'NERMXT' => $laToken['usage']['data']->NetworkRequests,
																	 'NEMMXT' => $laToken['usage']['data']->NetworkMax,
																	 'NEOMXT' => $laToken['usage']['data']->NetworkOverageErrors,
																	 'MONMXT' => $lnMonitor,
																	 'UIDMXT' => $laMonitor->MonitorUID,
																	 'ACTMXT' => $laMonitor->ActionString,
																	 'LATMXT' => $laMonitor->LastTransition,
																	 'LACMXT' => $laMonitor->LastChecked,
																	 'RECMXT' => $laMonitor->RecordCount,
																	 'FAIMXT' => $lnMonitorFailing,
																	 'WARMXT' => $lnMonitorWarnings,
																	 'STAMXT' => $lcMonitorStatusSummary);

													$llRta = $goDb->tabla($lcTabla)->insertar($laDatos);
													$goAplicacionTareaManejador->evento(($llRta ==true?"Se":"No se")." adiciono el registro ".implode(", ",$laDatos));
												}
											}
										}
									}
								}
							}
						}else{
							$goAplicacionTareaManejador->evento("Ya existe un registro para la fecha ".$lnFecha." - ".$lnHora);
						}
					}else{
						$goAplicacionTareaManejador->evento("El objeto loRegistros o laId no existe");
					}
				}
				// FIN: INSERTANDO REGISTRO 
				
				
				
				
				// LIMPIANDO REGISTROS ANTIGUOS
				$ltAhora->modify('- '.$lnDiasLimpiar.' days');
				$lnFecha=$ltAhora->format("Ymd");
				$llRta = $goDb->from($lcTabla)
							  ->where('FECMXT', '<=', $lnFecha)
							  ->eliminar();
				$goAplicacionTareaManejador->evento(($llRta ==true?"Se":"No se")." limpiaron registro anteriores a ".($lnFecha)." dias");
				// FIN: LIMPIANDO REGISTROS ANTIGUOS
				
			}else{
				$goAplicacionTareaManejador->evento("El objeto goApp no existe");
			}
		} catch(Exception $loError){
			$goAplicacionTareaManejador->error($loError->getMessage());
		}

	}
	//	--- FIN FUNCION PRINCIPAL --

	/*
	--- FUNCIONES ADICIONALES ---
	En este bloque escriba las funciones adicionales que requiera. Recuerde que estas se encuentra disponibles únicamente en el entorno CRON el cual se ejecuta por una tubería distinta a la principal.*/
	// --- FIN FUNCIONES ADICIONALES ---
?>