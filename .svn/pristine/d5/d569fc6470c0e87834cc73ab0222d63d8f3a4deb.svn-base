<?php

/**********************************************************************/
/**********  ENVÍO DE DOCUMENTOS DE FACTURACIÓN ELECTRÓNICA  **********/
/**********************************************************************/

	/*	--- ¡ N O   M O D I F I C A B L E ! ---  */
	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionTareaManejador.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Db.php');

	$goAplicacionTareaManejador = new CRON\AplicacionTareaManejador(__FILE__,__DIR__);

	/*	--- CLASES Y OBJETOS ADICIONALES ---  */
/*
	ini_set('error_reporting', E_ALL);
	ini_set('log_errors',TRUE);
	ini_set('html_errors',TRUE);
	ini_set('error_log',(strtoupper(substr(PHP_OS, 0, 3))==="WIN"?'c:/temp/error.log':'/var/www/html/error.log'));
	ini_set('display_errors',FALSE);
	ini_set('register_argc_argv',TRUE);
*/
	ini_set('max_execution_time', 60*15); //15 minutos
	date_default_timezone_set('America/Bogota');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.FacturaElectronica.php');
	//	--- FIN DE CLASES Y OBJETOS ADICIONALES ---


	/*	--- ¡ N O   M O D I F I C A B L E ! ---  */
	//	El script solo debe ejecutar la función tareaProgramaPrincipal. Personalice esta según sus necesidades
	if($goAplicacionTareaManejador){
		try{
			$goAplicacionTareaManejador->evento('Procesando función principal');
			tareaProgramaPrincipal();
			$goAplicacionTareaManejador->evento('Función procesada correctamente');

		} catch (Exception $loError) {
			$goAplicacionTareaManejador->error($loError->getMessage());
		}
		unset($goAplicacionTareaManejador);
	}


	/*	--- FUNCIÓN PRINCIPAL ---  */
	function tareaProgramaPrincipal(){
		global $goAplicacionTareaManejador;
		global $goDb;

		try {
			$loFeEnviar = new NUCLEO\FacturaElectronica();
			$loFeEnviar->ejecutarDocumentosFE();
		} catch(Exception $loError){
			$goAplicacionTareaManejador->evento($loError->getMessage());
		}
	}

	//	--- FIN FUNCION PRINCIPAL --

	/*	--- FUNCIONES ADICIONALES ---  */
	//	--- FIN FUNCIONES ADICIONALES ---
