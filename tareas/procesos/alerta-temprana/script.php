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
			
			$lnHoras = 6;
			$lnProcesados = 0;
			$lnActualizados = 0;
			
			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lcFecha = $ltAhora->format("Ymd");
			$lcHora  = $ltAhora->format("His");
			$lcAhora = $lcFecha.$lcHora;			
			
			
			if(isset($goDb)){
				$lcTabla="ALETEMP A";
				$lcWhere=sprintf("A.ESTADO=0 AND (%s -((A.VALFEA *1000000) + A.VALHOA))>%s ",$lcAhora,($lnHoras*10000));
				$lcWhere="(".$lcWhere.") OR (A.SECCIN='1C' AND A.ESTADO=0)";
				$lcWhere="(".$lcWhere.") OR (A.ESTADO<9 AND B.ESTING<>'2' AND (B.FEEING>0 AND B.FEEING<".$lcFecha."))";
 
				$laRegistros = $goDb->select("A.CONALE, A.NIGING, A.TIDING, A.NIDING, A.VALORA")
									->tabla($lcTabla)
									->leftJoin('RIAING B', 'A.NIGING', '=', 'B.NIGING')
									->where($lcWhere)
									->orderBy('A.CONALE ASC')
									->getAll('array');
				if(isset($laRegistros)==true){
					if(is_array($laRegistros)==true){
						foreach($laRegistros as $lnFila=>$laRegistro){			
							$laDatos = ['ESTADO'=>'9',
										'USMALE'=>'SERVIDOR',
										'PGMALE'=>substr(pathinfo(__FILE__, PATHINFO_FILENAME),0,10),
										'FEMALE'=>$lcFecha,
										'HOMALE'=>$lcHora
										];
							$llResultado = $goDb->tabla($lcTabla)->where('CONALE', '=', $laRegistro['CONALE'])->where('NIGING', '=', $laRegistro['NIGING'])->actualizar($laDatos);	
							$lnProcesados += 1;
							$lnActualizados+=($llResultado==true?1:0);
							$goAplicacionTareaManejador->evento(sprintf("%s el registro %s del ingreso %s",($llResultado==true?"Actualizado":"No actualizado"),$laRegistro['CONALE'],$laRegistro['NIGING']));
						}
						$goAplicacionTareaManejador->evento(sprintf("Actualizados %s de %s",$lnActualizados,$lnProcesados));
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
	// --- FIN FUNCIONES ADICIONALES ---
?>