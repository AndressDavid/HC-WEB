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
				
		try {
			$laRegistros=array();

			// Con este script se busca:
			// 1. Bloquear los usuarios que no ingresen a la aplaición en N días
			// 2. Inactivar a los usuarios que no ingresen en N dias. En este caso no paracerian en las listas que consulten la RIARGMN con el campo ESTRGM=1
			// 3. Forzar cambio de clave a los N dias
			// La regla es, primero bloquear y luego inactivar, es decir que los dias para inactivacion deben ser mayores a los dias para bloqueo.
			//
			// nDiasBloqueo < nDiasForzarCambioClave < nDiasInactivacion
			
			// PARAMETROS
			$lnDiasBloqueo = 15;
			$lnDiasForzarCambioClave = 60;
			$lnDiasInactivacion = 90;
			$lcPrograma = substr(pathinfo(__FILE__, PATHINFO_FILENAME),0,10);


			// INTERVALOS
			// Intervalo para bloqueo
			$loIntervaloBloqueo = new \DateInterval(sprintf('P%sD',$lnDiasBloqueo));
			$loIntervaloBloqueo->invert = 1;

			// Intervalo para forzar cambio de contraseña
			$loIntervaloForzarCambioClave = new \DateInterval(sprintf('P%sD',$lnDiasForzarCambioClave-$lnDiasBloqueo));
			$loIntervaloForzarCambioClave->invert = 1;

			// Intervalo para inactivacion
			$loIntervaloInactivar = new \DateInterval(sprintf('P%sD',$lnDiasInactivacion-$lnDiasForzarCambioClave));
			$loIntervaloInactivar->invert = 1;

			
			// FECHAS
			// Hoy
			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lcHoy = $ltAhora->format("Ymd");
			$lcHoyHora = $ltAhora->format("His");
			$lnHoy = intval($lcHoy);
			$lnHoyHora = intval($lcHoyHora);

			// Fecha para bloquear
			$ltFechaBloqueo = $ltAhora;
			$ltFechaBloqueo->add($loIntervaloBloqueo);
			$lcFechaBloqueo = $ltFechaBloqueo->format("Ymd");
			$lnFechaBloqueo = intval($lcFechaBloqueo);

			// Fecha para forzar cambio de contraseña
			$ltFechaForzarCambioClave  = $ltAhora;
			$ltFechaForzarCambioClave ->add($loIntervaloForzarCambioClave );
			$lcFechaForzarCambioClave  = $ltFechaForzarCambioClave ->format("Ymd");
			$lnFechaForzarCambioClave  = intval($lcFechaForzarCambioClave );

			// Fecha para inactivar
			$ltFechaInactivar = $ltAhora;
			$ltFechaInactivar->add($loIntervaloInactivar);
			$lcFechaInactivar = $ltFechaInactivar->format("Ymd");
			$lnFechaInactivar = intval($lcFechaInactivar);

			// Acumuladores
			$lnProcesados = 0;
			$lnBloqueados = 0;
			$lnBloqueos = 0;
			$lnInactivados = 0;
			$lnInactivos = 0;
			$lnNormales = 0;
			$lnCanditadosBloqueo = 0;
			$lnCanditadosInactivar = 0;
			$lnForzadosCambioClave = 0;

			$lcSinRegistroMedico = '';
			$lnSinRegistroMedico = 0;

			$lcSinRegistroSeguridad = '';
			$lnSinRegistroSeguridad = 0;

			$lcUsuariosBloqueados = '';
			$lcUsuariosInactivos = '';
			$lcUsuariosForzarCambioClave = '';
			
			if(isset($goDb)){
				
				// #######    #     #####  #######       #   
				// #         # #   #     # #            ##   
				// #        #   #  #       #           # #   
				// #####   #     #  #####  #####         #   
				// #       #######       # #             #   
				// #       #     # #     # #             #   
				// #       #     #  #####  #######     ##### 
				//
				// Validando usuarios. Se bloquea o inactiva dependeindo del tiempo transcurrido 
				// desde la ultima vez que ingreso al aplicativo
				
				$goAplicacionTareaManejador->evento(sprintf('Boquear entre %s y %s, Inactivar desde antes y hasta el %s. Forzar cambio de clave antes de %s',$lcFechaBloqueo ,$lcFechaInactivar , $lcFechaInactivar, $lcFechaForzarCambioClave));
				

				// Se buscan todos los usuarios, no se filtran por estado para detectar posibles usuarios en la SISMENSEG que no esten en la RIARGMN
				$laCampos = ['A.USUARI', "IFNULL(B.ESTRGM,0) ESTRGM", 'A.LOCKED', "IFNULL(B.USUARI,'') CONTROL", 'A.ACCFEC ACCESO_FECHA', 'A.ACCHOR ACCESO_HORA', 'A.ACCPC ACCESO_PC', 'A.ACCIP ACCESO_IP', 'A.FCRMEN CREADO', 'A.FMOMEN MODIFICADO' ];
				$laRegistros = $goDb->select($laCampos)
									->tabla('SISMENSEG A')
									->leftJoin('RIARGMN B', 'B.USUARI', '=', 'A.USUARI')
									->getAll('array');
									
				//$goAplicacionTareaManejador->evento($goDb->getQuery());
				if(isset($laRegistros)==true){
					if(is_array($laRegistros)==true){
						foreach($laRegistros as $lnFila=>$laRegistro){
							$laRegistro['USUARI'] = trim($laRegistro['USUARI']);
							if(!empty($laRegistro['USUARI'])){
								switch(intval($laRegistro['ESTRGM'])){
									case 0:  // Usuario sin registro en la RIARGMN
										
										// Bloquear	
										if($laRegistro['LOCKED']==0){
											if(bloquearUsuarios($laRegistro['USUARI'], 'HCWEB', $lcPrograma, $lnHoy, $lnHoyHora)==true){
												$lnBloqueados += 1;
												$lcUsuariosBloqueados .= trim($laRegistro['USUARI']).", ";
												$lcComentario = '*Bloqueado el '.strval($lcHoy).' de forma automatica por el sistema debido a inactividad. No tiene registro en la tabla RIARGMN. '.(intval($laRegistro['ACCESO_FECHA'])>0 ? 'Ultimo inicio de sesion '.strval($laRegistro['ACCESO_FECHA']):'Nunca inicio sesion').', La fecha de corte fue el '.$lcFechaBloqueo;
												$llResultado = insertEntradaBitacora(trim($laRegistro['USUARI']), 'BLOQUEO-HC-CRON', 0, '', $lcComentario, 'HCWEB', $lcPrograma, $lnHoy, $lnHoyHora);
											}
										}else{
											$lnBloqueos += 1;
										}
										break;

									case 1:  // Usuario con estado de registro 1: Activo
										if(intval($laRegistro['ACCESO_FECHA'])<=$lnFechaInactivar){
										
											// Se inactivaran siempre y cuando no se hayan creado/modifiado antes de la fecha de inactivacion
											if(intval($laRegistro['CREADO'])<=$lnFechaInactivar && intval($laRegistro['MODIFICADO'])<=$lnFechaInactivar){ 
												if(inactivarUsuarios($laRegistro['USUARI'], 'HCWEB', $lcPrograma, $lnHoy, $lnHoyHora)==true){
													$lnInactivados += 1;
													$lcUsuariosInactivos .= trim($laRegistro['USUARI']).", ";
													$lcComentario = 'Inactivado el '.strval($lcHoy).' de forma automatica por el sistema debido a inactividad. '.(intval($laRegistro['ACCESO_FECHA'])>0 ? 'Ultimo inicio de sesion '.strval($laRegistro['ACCESO_FECHA']):'Nunca inicio sesion').', La fecha de corte fue el '.$lcFechaInactivar;
													$llResultado = insertEntradaBitacora(trim($laRegistro['USUARI']), 'INACTIVO-HC-CRON', 0, '', $lcComentario, 'HCWEB', $lcPrograma, $lnHoy, $lnHoyHora);
												}
											}else{
												$lnCanditadosInactivar += 1;
											}
		
										}else if(intval($laRegistro['ACCESO_FECHA'])<=$lnFechaBloqueo){
											
											// Bloquear	
											if(intval($laRegistro['LOCKED'])==0){

												// Se bloquearan siempre y cuando no se hayan creado/modificado antes de la fecha de bloqueo
												if(intval($laRegistro['CREADO'])<=$lnFechaBloqueo && intval($laRegistro['MODIFICADO'])<=$lnFechaBloqueo){ 
													if(bloquearUsuarios($laRegistro['USUARI'], 'HCWEB', $lcPrograma, $lnHoy, $lnHoyHora)==true){
														$lnBloqueados += 1;
														$lcUsuariosBloqueados .= trim($laRegistro['USUARI']).", ";
														$lcComentario = 'Bloqueado el '.strval($lcHoy).' de forma automatica por el sistema debido a inactividad. '.(intval($laRegistro['ACCESO_FECHA'])>0 ? 'Ultimo inicio de sesion '.strval($laRegistro['ACCESO_FECHA']):'Nunca inicio sesion').', La fecha de corte fue el '.$lcFechaBloqueo;
														$llResultado = insertEntradaBitacora(trim($laRegistro['USUARI']), 'BLOQUEO-HC-CRON', 0, '', $lcComentario, 'HCWEB', $lcPrograma, $lnHoy, $lnHoyHora);
													}
												}else{
													$lnCanditadosBloqueo += 1;
												}												
											}else{
												$lnBloqueos += 1;
											}
										}else{
											$lnNormales += 1;
										}
										break;
									
									case 2: // Usuario con estado de registro 2: Inactivo
										$lnInactivos += 1;
										break;

								}

								$lnProcesados+=1;
								if(empty($laRegistro['CONTROL'])==true){
									$lnSinRegistroMedico += 1;
									$lcSinRegistroMedico .= trim($laRegistro['USUARI']).", ";
								}
							}
						}						
					}else{
						$goAplicacionTareaManejador->evento("No hay registros para procesar");
					}
					
				}else{
					$goAplicacionTareaManejador->evento("No hay registros para procesar");
				}

				
				// #######    #     #####  #######     #####  
				// #         # #   #     # #          #     # 
				// #        #   #  #       #                # 
				// #####   #     #  #####  #####       #####  
				// #       #######       # #          #       
				// #       #     # #     # #          #       
				// #       #     #  #####  #######    ####### 
				//
				// Validando usuarios. Inactiva los que existan en la RIARGMN pero no en la SISMENSEG 
								
				// Se buscan todos los usuarios activos en la RIARGMN y se valida que exista el registro en la SISMENSEG
				$laCampos = ['A.USUARI', 'A.ESTRGM', "IFNULL(B.USUARI,'') CONTROL" ];
				$laRegistros = $goDb->select($laCampos)
									->tabla('RIARGMN A')
									->leftJoin('SISMENSEG B', 'B.USUARI', '=', 'A.USUARI')
									->where('A.ESTRGM', '=', 1)
									->getAll('array');
									
				//$goAplicacionTareaManejador->evento($goDb->getQuery());
				if(isset($laRegistros)==true){
					if(is_array($laRegistros)==true){
						foreach($laRegistros as $lnFila=>$laRegistro){
							$laRegistro['USUARI'] = trim($laRegistro['USUARI']);
							if(!empty($laRegistro['USUARI'])){							
								// Actualizando el campo de estado
								if(empty(trim($laRegistro['CONTROL']))==true){
									if(inactivarUsuarios($laRegistro['USUARI'], 'HCWEB', $lcPrograma, $lnHoy, $lnHoyHora)==true){
										$lnInactivados += 1;
										$lcUsuariosInactivos .= trim($laRegistro['USUARI']).", ";
										$lcComentario = 'Inactivado el '.strval($lcHoy).' de forma automatica por el sistema debido a que no tiene registro de seguridad.';
										$llResultado = insertEntradaBitacora(trim($laRegistro['USUARI']), 'INACTIVO-HC-CRON', 0, '', $lcComentario, 'HCWEB', $lcPrograma, $lnHoy, $lnHoyHora);
									}
								}
								
								$lnProcesados+=1;
								if(empty($laRegistro['CONTROL'])){
									$lnSinRegistroSeguridad += 1;
									$lcSinRegistroSeguridad .= trim($laRegistro['USUARI']).", ";
								}
							}
						}
					}else{
						$goAplicacionTareaManejador->evento("No hay registros para procesar");
					}
					
				}else{
					$goAplicacionTareaManejador->evento("No hay registros para procesar");
				}							

				


				// #######    #     #####  #######     #####  
				// #         # #   #     # #          #     # 
				// #        #   #  #       #                # 
				// #####   #     #  #####  #####       #####  
				// #       #######       # #                # 
				// #       #     # #     # #          #     # 
				// #       #     #  #####  #######     #####  
				// Se buscan todos los usuarios activos en la RIARGMN y se valida cuando fue el ultimo cmabio de contraseña
				$laCampos = ['A.USUARI', 'B.ESTRGM', "IFNULL(B.USUARI,'') CONTROL" ];
				$laRegistros = $goDb->select($laCampos)
									->tabla('SISMENSEG A')
									->leftJoin('RIARGMN B', 'B.USUARI', '=', 'A.USUARI')
									->where('B.ESTRGM', '=', 1)
									->where('A.PSSOCH', '=', 0)
									->where('A.PSSNCH', '=', 0)
									->where('A.PSSNEX', '=', 0)									
									->getAll('array');
									
				//$goAplicacionTareaManejador->evento($goDb->getQuery());
				if(isset($laRegistros)==true){
					if(is_array($laRegistros)==true){
						foreach($laRegistros as $lnFila=>$laRegistro){
							$laRegistro['USUARI'] = trim($laRegistro['USUARI']);
							if(!empty($laRegistro['USUARI'])){		

								// Actualizando el campo de estado
								if(empty(trim($laRegistro['CONTROL']))==false){

									$laCampos = ['A.USUARI', 'A.BITKEY', 'A.FCRBIT', 'A.HCRBIT'];
									$laUltimoCambioClave = $goDb->select($laCampos)
														->tabla('SISMENBIT A')
														->where('A.USUARI', '=', $laRegistro['USUARI'])
														->where('A.BITKEY', '=', 'CAMBIO-CLAVE-USUARIO')
														->orderBy('A.FCRBIT', 'DESC')
														->get('array');
									
									$lnUltimoCambio = (is_array($laUltimoCambioClave)==true ? (isset($laUltimoCambioClave['FCRBIT'])==true ? intval($laUltimoCambioClave['FCRBIT']) : 0 ) : 0);

									if($lnUltimoCambio<=$lnFechaForzarCambioClave){
										if(forzarCambioClave($laRegistro['USUARI'], 'HCWEB', $lcPrograma, $lnHoy, $lnHoyHora)==true){
											$lnForzadosCambioClave += 1;
											$lcUsuariosForzarCambioClave .= trim($laRegistro['USUARI']).", ";
											$lcComentario = 'Forzado a cambio de clave el '.strval($lcHoy).' de forma automatica por el sistema debido a que cambio clave antes del '.$lnFechaForzarCambioClave.'.';
											$llResultado = insertEntradaBitacora(trim($laRegistro['USUARI']), 'FORZAR-CAMBIO-CLAVE', 0, '', $lcComentario, 'HCWEB', $lcPrograma, $lnHoy, $lnHoyHora);
										}
									}
									
								}
								
								$lnProcesados+=1;
							}
						}
					}else{
						$goAplicacionTareaManejador->evento("No hay registros para procesar");
					}
					
				}else{
					$goAplicacionTareaManejador->evento("No hay registros para procesar");
				}							


				// #######     #      #####   #######      #       
				// #          # #    #     #  #            #    #  
				// #         #   #   #        #            #    #  
				// #####    #     #   #####   #####        #    #  
				// #        #######        #  #            ####### 
				// #        #     #  #     #  #                 #  
				// #        #     #   #####   #######           #  
                                                  
				// LIMPIANDO REGISTROS ANTIGUOS
				$lnDiasLimpiar = 365;
				$ltAhora = new DateTime($goDb->fechaHoraSistema());
				$ltAhora->modify('- '.$lnDiasLimpiar.' days');
				$lnFecha=$ltAhora->format("Ymd");
				
				$lcAuditoriaResultado = "";
				$laAuditorias = [
									["TABLA"=>"AUDMOD", "CAMPO"=>"FECAMO", "ACTIVO"=>true],
									["TABLA"=>"TARSER", "CAMPO"=>"FECTSE", "ACTIVO"=>true]
								];

				foreach($laAuditorias as $lnFila=>$laAuditoria){				
					if(!empty($laAuditoria["TABLA"]) && !empty($laAuditoria["CAMPO"])){
						if($laAuditoria["ACTIVO"]==true){
							$laRegistros = $goDb->count('*','REGISTROS')->tabla($laAuditoria["TABLA"])->where($laAuditoria["CAMPO"], '<', $lnFecha)->get('array');
							$lnRegistros = intval($laRegistros['REGISTROS']);
							
							$llResultado = $goDb->from($laAuditoria["TABLA"])->where($laAuditoria["CAMPO"], '<', $lnFecha)->eliminar();
							$goAplicacionTareaManejador->evento(sprintf("Auditoria de %s, %s limpiaron %s registro anteriores al %s", $laAuditoria["TABLA"], ($llResultado==true?"Se":"No se"), $lnRegistros, $lnFecha));
						}else{
							$goAplicacionTareaManejador->evento(sprintf("Auditoria de %s inactiva", $laAuditoria["TABLA"]));
						}
					}
				}

                              
				// ###### # #    #   ##   #      
				// #      # ##   #  #  #  #      
				// #####  # # #  # #    # #      
				// #      # #  # # ###### #      
				// #      # #   ## #    # #      
				// #      # #    # #    # ###### 
				//

				// Información final de la ejecución
				$goAplicacionTareaManejador->evento(sprintf("Bloqueados %s / %s. ", $lnBloqueados, $lnProcesados));
				$goAplicacionTareaManejador->evento(sprintf("Bloqueos anteriores %s / %s. ", $lnBloqueos, $lnProcesados));
				$goAplicacionTareaManejador->evento(sprintf("Inactivados %s / %s. ", $lnInactivados, $lnProcesados));
				$goAplicacionTareaManejador->evento(sprintf("Inactivos previamente %s / %s. ",  $lnInactivos, $lnProcesados));
				$goAplicacionTareaManejador->evento(sprintf("Sin requerir accion alguna %s / %s. ", $lnNormales, $lnProcesados));
				$goAplicacionTareaManejador->evento(sprintf("Forzados a cambio de clave: %s. ", $lnForzadosCambioClave));
				$goAplicacionTareaManejador->evento(sprintf("Candidatos para inactivar: %s. ", $lnCanditadosInactivar));
				$goAplicacionTareaManejador->evento(sprintf("Hay %s que no existen en la tabla de registros medicos. ", $lnSinRegistroMedico));
				$goAplicacionTareaManejador->evento(sprintf("Hay %s que no existen en la tabla de seguridad. ", $lnSinRegistroSeguridad));


				if(!empty($lcUsuariosBloqueados)){
					$goAplicacionTareaManejador->evento(sprintf("Bloqueados: %s", $lcUsuariosBloqueados));
				}

				if(!empty($lcUsuariosInactivos)){
					$goAplicacionTareaManejador->evento(sprintf("Inactivados: %s", $lcUsuariosInactivos));
				}

				if(!empty($lcUsuariosForzarCambioClave)){
					$goAplicacionTareaManejador->evento(sprintf("Forzados a cambio de clave: %s", $lcUsuariosForzarCambioClave));
				}					

				if(!empty($lcSinRegistroSeguridad)){
					$goAplicacionTareaManejador->evento(sprintf("Sin resgistro en la tabla de seguridad: %s", $lcSinRegistroSeguridad));
				}
				
				if(!empty($lcSinRegistroMedico)){
					$goAplicacionTareaManejador->evento(sprintf("Sin registro en la tabla de medicos: %s", $lcSinRegistroMedico));
				}	


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

	function forzarCambioClave($tcUsuarioBloquear='', $tcUsuario='', $tcPrograma='', $tnFecha=0, $tnHora=0 ){
		$tcUsuarioBloquear = trim(strval($tcUsuarioBloquear));
		$tcUsuario = trim(strval($tcUsuario));
		$tcPrograma = trim(strval($tcPrograma));
		$tnFecha = intval($tnFecha);
		$tnHora = intval($tnHora);
		$llResultado = false;

		global $goDb;

		if (!empty($tcUsuarioBloquear) && !empty($tcUsuario) && !empty($tcPrograma) && !empty($tnFecha)){
			$laDatos = ['PSSOCH'=>1, 'UMOMEN'=>$tcUsuario, 'PMOMEN'=>$tcPrograma, 'FMOMEN'=>$tnFecha, 'HMOMEN'=>$tnHora]; // 
			$llResultado = $goDb->tabla('SISMENSEG')->where('USUARI', '=', $tcUsuarioBloquear)->actualizar($laDatos);		
		}

		return $llResultado;
	}
	
	function inactivarUsuarios($tcUsuarioBloquear='', $tcUsuario='', $tcPrograma='', $tnFecha=0, $tnHora=0 ){
		$tcUsuarioBloquear = trim(strval($tcUsuarioBloquear));
		$tcUsuario = trim(strval($tcUsuario));
		$tcPrograma = trim(strval($tcPrograma));
		$tnFecha = intval($tnFecha);
		$tnHora = intval($tnHora);
		$llResultado = false;

		global $goDb;

		if (!empty($tcUsuarioBloquear) && !empty($tcUsuario) && !empty($tcPrograma) && !empty($tnFecha)){
			$laDatos = ['ESTRGM'=>2, 'UMORGM'=>$tcUsuario, 'PMORGM'=>$tcPrograma, 'FMORGM'=>$tnFecha, 'HMORGM'=>$tnHora]; // 
			$llResultado = $goDb->tabla('RIARGMN')->where('USUARI', '=', $tcUsuarioBloquear)->actualizar($laDatos);		
		}

		return $llResultado;
	}

	function bloquearUsuarios($tcUsuarioBloquear='', $tcUsuario='', $tcPrograma='', $tnFecha=0, $tnHora=0 ){
		$tcUsuarioBloquear = trim(strval($tcUsuarioBloquear));
		$tcUsuario = trim(strval($tcUsuario));
		$tcPrograma = trim(strval($tcPrograma));
		$tnFecha = intval($tnFecha);
		$tnHora = intval($tnHora);
		$llResultado = false;

		global $goDb;

		if (!empty($tcUsuarioBloquear) && !empty($tcUsuario) && !empty($tcPrograma) && !empty($tnFecha)){
			$laDatos = ['LOCKED'=>1, 'UMOMEN'=>$tcUsuario, 'PMOMEN'=>$tcPrograma, 'FMOMEN'=>$tnFecha, 'HMOMEN'=>$tnHora]; // 
			$llResultado = $goDb->tabla('SISMENSEG')->where('USUARI', '=', $tcUsuarioBloquear)->actualizar($laDatos);		
		}

		return $llResultado;
	}

	function insertEntradaBitacora($tcEntradaUsuario='', $tcEntradaKey='', $tnEntradaRating=0, $tcEntradaRegistro='', $tcEntradaComentario='', $tcUsuario='', $tcPrograma='', $tnFecha=0, $tnHora=0){
		$tcEntradaUsuario = trim(strval($tcEntradaUsuario));
		$tcEntradaKey = trim(strval($tcEntradaKey));
		$tcEntradaRegistro = trim(strval($tcEntradaRegistro));
		$tcEntradaComentario = trim(strval($tcEntradaComentario));
		$tcUsuario = trim(strval($tcUsuario));
		$tcPrograma = trim(strval($tcPrograma));
		$tnEntradaRating = intval($tnEntradaRating);
		$tnFecha = intval($tnFecha);
		$tnHora = intval($tnHora);
		$llResultado = false;

		global $goDb;

		if (!empty($tcEntradaUsuario) && !empty($tcEntradaKey) && !empty($tcEntradaComentario) && !empty( $tcUsuario) && !empty($tcPrograma) && !empty($tnFecha)){
			$laDatos = [
				'USUARI'=>$tcEntradaUsuario,
				'BITKEY'=>$tcEntradaKey,
				'RATING'=>$tnEntradaRating,
				'BINNAC'=>$tcEntradaRegistro,
				'COMMEN'=>$tcEntradaComentario,
				'UCRBIT'=>$tcUsuario,
				'PCRBIT'=>$tcPrograma,
				'FCRBIT'=>$tnFecha,
				'HCRBIT'=>$tnHora
			];
			$llResultado = $goDb->tabla('SISMENBIT')->insertar($laDatos);
		}

		return $llResultado;
	}
	// --- FIN FUNCIONES ADICIONALES ---
?>