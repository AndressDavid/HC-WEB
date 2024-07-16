<?php

/*******************************************************************/
/**********  OBTENER PROCESOS DE MIPRES PROV-DISPENSADOR  **********/
/*******************************************************************/

/*	--- ¡ N O   M O D I F I C A B L E ! ---  */
require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionTareaManejador.php');
require_once (__DIR__ .'/../../../nucleo/controlador/class.Db.php');

$goAplicacionTareaManejador = new CRON\AplicacionTareaManejador(__FILE__,__DIR__);

/*	--- CLASES Y OBJETOS ADICIONALES ---  */
date_default_timezone_set('America/Bogota');
ini_set('max_execution_time', 2*60*60); // 2 hr
require_once __DIR__ . '/../../../nucleo/controlador/class.MiPresConsultaGuardar.php';
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
function tareaProgramaPrincipal()
{
	global $goAplicacionTareaManejador;
	global $goDb;

	$lcFechaProc = date('Y-m-d');
	$loMiPres = new NUCLEO\MiPresConsultaGuardar();
	$laRtaP = $loMiPres->prescripciones($lcFechaProc);
	$laRtaN = $loMiPres->novedades($lcFechaProc);
}
//	--- FIN FUNCION PRINCIPAL --

/*	--- FUNCIONES ADICIONALES ---  */

//	--- FIN FUNCIONES ADICIONALES ---
