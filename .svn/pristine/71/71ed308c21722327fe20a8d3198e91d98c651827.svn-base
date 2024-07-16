<?php

/**********************************************************************/
/**********  OBTENER TOKEN TEMPORAL MIPRES PROV-DISPENSADOR  **********/
/**********************************************************************/

	/*	--- ¡ N O   M O D I F I C A B L E ! ---  */
	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionTareaManejador.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Db.php');

	$goAplicacionTareaManejador = new CRON\AplicacionTareaManejador(__FILE__,__DIR__);

	/*	--- CLASES Y OBJETOS ADICIONALES ---  */
	date_default_timezone_set('America/Bogota');
	//require_once __DIR__ . '/../../../nucleo/controlador/class.MiPresCurlRequest.php';
	require_once __DIR__ . '/../../../nucleo/controlador/class.MiPresFunciones.php';
	//	--- FIN DE CLASES Y OBJETOS ADICIONALES ---


	/*	--- ¡ N O   M O D I F I C A B L E ! ---  */
	//	El script solo debe ejecutar la función tareaProgramaPrincipal. Personalice esta según sus necesidades
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


	/*	--- FUNCIÓN PRINCIPAL ---  */
	function tareaProgramaPrincipal(){
		global $goAplicacionTareaManejador;

		try {
			$goAplicacionTareaManejador->evento('Obteniendo token temporal dispensación');
			$lcRetorna = NUCLEO\MiPresFunciones::fcGenerarTokenTmp('tokentmp');
			$goAplicacionTareaManejador->evento($lcRetorna);

			$goAplicacionTareaManejador->evento('Obteniendo token temporal facturación');
			$lcRetorna = NUCLEO\MiPresFunciones::fcGenerarTokenTmp('tokenfactmp');
			$goAplicacionTareaManejador->evento($lcRetorna);

		} catch(Exception $loError){
			$goAplicacionTareaManejador->error($loError->getMessage());
		}
	}
	//	--- FIN FUNCION PRINCIPAL --

	/*	--- FUNCIONES ADICIONALES ---  */
	//	--- FIN FUNCIONES ADICIONALES ---
