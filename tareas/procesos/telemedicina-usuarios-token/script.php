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
				
		try {
			$laRegistros=array();
			
			// Parametros
			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lcFecha = $ltAhora->format("Ymd");
			$lnFecha = intval($lcFecha);
		

			$lnProcesados = 0;
			$lnActualizados = 0;
			$lnSinRegistroMedico = 0;
			
			if(isset($goDb)){
				
				$laCampos = ['A.USUARI', "IFNULL(B.ESTRGM,0) ESTRGM", "IFNULL(B.USUARI,'') CONTROL" ];
				
				// Estado 0:Nuevo, 1:Activo, 8:Para eliminar, 9:Eliminado
				$laRegistros = $goDb->select($laCampos)
									->tabla('SISMENSEG A')
									->leftJoin('RIARGMN B', 'B.USUARI', '=', 'A.USUARI')
									->where('A.PRDYFE', '<', $lnFecha)
									->in('A.PRDYES', [0, 1])									
									->getAll('array');
									
				//$goAplicacionTareaManejador->evento($goDb->getQuery());
				if(isset($laRegistros)==true){
					if(is_array($laRegistros)==true){
						foreach($laRegistros as $lnFila=>$laRegistro){
							$laDatos = [
										'PRDYES'=>8,
							            'PRDYPW'=>'*',
							            'PRDYFE'=>$lnFecha
										];
							
							if($laRegistro['ESTRGM']==1){
								$laDatos['PRDYES'] = 1;
								$laDatos['PRDYPW'] = obtenerTokenParaProsodyCtl('',3,$lcFecha);
							}

							$llResultado = $goDb
											->tabla('SISMENSEG')
											->where('USUARI', '=', trim($laRegistro['USUARI']))
											->actualizar($laDatos);	
													
							$lnProcesados += 1;
							$lnActualizados += ($llResultado==true?1:0);
							$lnSinRegistroMedico += (empty($laRegistro['CONTROL'])?1:0);

						}
						$goAplicacionTareaManejador->evento(sprintf("Actualizados %s de %s %s",$lnActualizados,$lnProcesados,($lnSinRegistroMedico>0?sprintf('%s no existen en la tabla de registros medicos',$lnSinRegistroMedico):'')));
						
					}else{
						$goAplicacionTareaManejador->evento("No hay registros para procesar");
					}
					
				}else{
					$goAplicacionTareaManejador->evento("No hay registros para procesar");
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
	function obtenerTokenParaProsodyCtl($tcPrefix='', $tnFragmentos=3, $tcPostfix=''){
		$lcAlfabeto='23456789ACDEFHJKLMNPRTWXYZ';
		$lcToken='';
		$tcPrefix=trim(strval($tcPrefix));
		$tcPostfix=trim(strval($tcPostfix));
		$tnFragmentos=intval($tnFragmentos);
		
		for($lnFragmento=0; $lnFragmento<$tnFragmentos; $lnFragmento++){
			$lcToken .= (empty($lcToken)==false?'-':'');
			for($lnCaracter=0; $lnCaracter<3; $lnCaracter++){
				$lcToken .= substr($lcAlfabeto,rand(0,strlen($lcAlfabeto)-1),1);
			}
		}
		$lcToken = (!empty($tcPrefix)?$tcPrefix.'-':'').$lcToken;
		$lcToken = $lcToken.(!empty($tcPostfix)?'-'.$tcPostfix:'');
		return $lcToken;
	}
	
	// --- FIN FUNCIONES ADICIONALES ---
?>