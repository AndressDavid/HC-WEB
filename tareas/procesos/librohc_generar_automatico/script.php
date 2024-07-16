<?php

/*************************************************************/
/**********  GENERACION AUTOMÁTICA DEL LIBRO DE HC  **********/
/*************************************************************/

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
	ini_set('max_execution_time', 1*60*60); // 1 hr
	date_default_timezone_set('America/Bogota');
	require_once __DIR__ . '/../../../nucleo/controlador/class.LibroHC_Gen.php';
	//	--- FIN DE CLASES Y OBJETOS ADICIONALES ---

ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);
ini_set('display_errors',TRUE);


	/*	--- ¡ N O   M O D I F I C A B L E ! ---  */
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


		$loLibroHC = new NUCLEO\LibroHC_Gen();

		$goAplicacionTareaManejador->evento('Consultando ingresos por generar');
		$laListaIng = $loLibroHC->consultarIngresosPorGenerar();


		if (count($laListaIng)>0) {
			
			if ($loLibroHC->crearEstructuraCarpetas()) {

				foreach ($laListaIng as $lnIdx => $laIng) {

					$lnIng = $laIng['NIGDHC'];
					$laRta = $loLibroHC->generarLibroHcPdf($lnIng);

					if (empty($laRta['error'])) {
/*
						if (file_exists($laRta['rutac'])) {
							$goAplicacionTareaManejador->evento("Generado sin errores - Ingreso: {$lnIng}");
						} else {
							$goAplicacionTareaManejador->evento("Ocurrió un error y no se generó el archivo - Ingreso: {$lnIng}");
						}
*/
						$goAplicacionTareaManejador->evento("Fin proceso para Ingreso: {$lnIng}");

					} else {
						$goAplicacionTareaManejador->evento("{$laRta['error']} - Ingreso: {$lnIng}");

					}

				}

			} else {
				$goAplicacionTareaManejador->evento('No se pudo crear la carpeta, no se generaron los archivos');
			}

		} else {
			$goAplicacionTareaManejador->evento('No hay ingresos pendientes para generar');
		}

	}
	//	--- FIN FUNCION PRINCIPAL --

