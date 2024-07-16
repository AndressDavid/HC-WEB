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
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Laboratorio.php') ;
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
			$lcHora  = $ltAhora->format("His");
			$lcAhora = $lcFecha.$lcHora;			
			
			
			if(isset($goDb)){

			}else{
				$goAplicacionTareaManejador->evento("No se tiene acceso a la base de datos");
			}
		} catch(Exception $loError){
			$goAplicacionTareaManejador->evento($loError->getMessage());
		}
		
	}
	//	--- FIN FUNCION PRINCIPAL --
	
	/*
	--- FUNCIONES ADICIONALES ---
	En este bloque escriba las funciones adicionales que requiera. Recuerde que estas se encuentra disponibles únicamente en el entorno CRON el cual se ejecuta por una tubería distinta a la principal.*/
	// --- FIN FUNCIONES ADICIONALES ---
?>