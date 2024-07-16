<?php

/****************************************************************/
/**********  GENERAR Y ENVIAR SOPORTES DE LAB PARA CM  **********/
/****************************************************************/

	/*	--- ¡ N O   M O D I F I C A B L E ! ---  */
	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionTareaManejador.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Db.php');

	$goAplicacionTareaManejador = new CRON\AplicacionTareaManejador(__FILE__,__DIR__);

	/*	--- CLASES Y OBJETOS ADICIONALES ---  */
	ini_set('max_execution_time', 60*60*2);
	date_default_timezone_set('America/Bogota');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.DocumentosCM.php');
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
			$goDb->from('CMSPRT')->where("TIPCMS='TRANSFIR' AND CDCCMS='LAB' AND ESTCMS='GS'")->actualizar(['ESTCMS'=>'00']);
			$loDocumentosCM = new NUCLEO\DocumentosCM();
			$loDocumentosCM->generarSoportesPendientes(0, ['LAB']);

		} catch(Exception $loError){
			$goAplicacionTareaManejador->evento($loError->getMessage());
		}
	}

	//	--- FIN FUNCION PRINCIPAL --

	/*	--- FUNCIONES ADICIONALES ---  */
	//	--- FIN FUNCIONES ADICIONALES ---
