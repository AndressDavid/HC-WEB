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
	
	$goAplicacionTareaManejador = new CRON\AplicacionTareaManejador(__FILE__,__DIR__);
	
	/*
	MODIFICABLE 
	--- CLASES Y OBJETOS ADICIONALES ----
	Utilice esta sección del bloque para incluir clases y declarar objetos adicionales*/;	
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
		
		$lcWebServiceUrl = "https://www.superfinanciera.gov.co/SuperfinancieraWebServiceTRM/TCRMServicesWebService/TCRMServicesWebService";
		$lcWebServiceUrlWsdl = $lcWebServiceUrl."?WSDL";
		
		$laMeses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");	
		
		try {
			$loSoapClient = new SoapClient($lcWebServiceUrlWsdl, array(
				'soap_version'   => SOAP_1_1,
				'trace' => 1,
				"location" => $lcWebServiceUrl,
			));
			
			try{
				$loResponse = @$loSoapClient->queryTCRM(array('tcrmQueryAssociatedDate' => date("Y-m-d")));
			} catch (SoapFault $loError) {
				$goAplicacionTareaManejador->evento($loError->getMessage());
			} catch(Exception $loError) {
				$goAplicacionTareaManejador->evento($loError->getMessage());
			}

			if(isset($loResponse)){
				if(is_object($loResponse)){
					$loResponse = $loResponse->return;
					if($loResponse->success){
						$loRow = new StdClass();
						$loRow->Column0 = date("Y");
						$loRow->Column1 = date("Y-m-d");
						$loRow->Column2 = $loResponse->value;
						$loRow->Column3 = date("d");
						$loRow->Column4 = $laMeses[date("n")-1];
						$loRow->Column5 = date("n");
						$laResultXml=array($loRow);
										
						// Guardando la información en la base de datos				
						if(isset($goDb)){
							if(isset($laResultXml)){
								foreach($laResultXml as $laRow){

									$lnAno=sprintf("%s",$laRow->Column0); settype($lnAno,"integer");
									$lnMes=sprintf("%s",$laRow->Column5); settype($lnMes,"integer");
									$lnDia=sprintf("%s",$laRow->Column3); settype($lnDia,"integer");
									$lcFecha=sprintf("%s",$laRow->Column1); settype($lcFecha,"string");
									$lcMesNombre=sprintf("%s",$laRow->Column4); settype($lcMesNombre,"string");
									$lnValor=sprintf("%s",$laRow->Column2); settype($lnValor,"double");
									
									$lcTabla = 'CTBTRM';
									$lcTipo = 'TRM';
									$lcWebService="Superfinanciera";
									$lcMoneda = "DOLAR";
									
									$loRegistros = $goDb->count('*','REGISTROS')
															  ->tabla($lcTabla)
															  ->where('TIPO', '=', $lcTipo)
															  ->where('ANO', '=', $lnAno)
															  ->where('MES', '=', $lnMes)
															  ->where('DIA', '=', $lnDia)
															  ->where('FECHA', '=', $lcFecha)->get('array');
									$laId = $goDb->max('ID','ID')->tabla($lcTabla)->get('array');
																					
									if(isset($loRegistros)==true && isset($laId)==true){
										if($loRegistros["REGISTROS"]<=0){
											$ltAhora = new DateTime($goDb->fechaHoraSistema());	
											$laDatos = array('ID'=>$laId["ID"]+1,
															 'TIPO'=>'TRM',
															 'ANO'=>$lnAno,
															 'MES'=>$lnMes,
															 'DIA'=>$lnDia,
															 'FECHA'=>$lcFecha,
															 'MES_NOMBRE'=>$lcMesNombre,
															 'VALOR'=>$lnValor,
															 'WEBSERVICE'=>$lcWebService,
															 'MONEDA'=>$lcMoneda,
															 'DETUSC'=>'SIJOSORT',
															 'DETFHC'=>$ltAhora->format("Ymd"),
															 'DETHRC'=>$ltAhora->format("His"));
																				
											$llRta = $goDb->tabla($lcTabla)->insertar($laDatos);
											$goAplicacionTareaManejador->evento(($llRta ==true?"Se":"No se")." adiciono el registro ".implode(", ",$laDatos));
											
										}else{
											$goAplicacionTareaManejador->evento("Ya existe un registro para la fecha ".$lcFecha);
										}
									}else{
										$goAplicacionTareaManejador->evento("El objeto loRegistros o laId no existe");
									}
								}
							}else{
								$goAplicacionTareaManejador->evento("El objeto laResultXml no existe");
							}
						}else{
							$goAplicacionTareaManejador->evento("El objeto goApp no existe");
						}
					}else{
						$goAplicacionTareaManejador->evento("No hay Response->success");
					}
				}else{
					$goAplicacionTareaManejador->evento("El loResponse no es un objeto");
				}
			}else{
				$goAplicacionTareaManejador->evento("El objeto loResponse no existe");
			}
		} catch (SoapFault $loError) {
			$goAplicacionTareaManejador->evento($loError->getMessage());
		} catch(ErrorException $loError){
			$goAplicacionTareaManejador->evento($loError->getMessage());
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