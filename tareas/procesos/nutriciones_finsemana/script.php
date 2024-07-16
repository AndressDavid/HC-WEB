<?php

/*******************************************************************/
/**********  ENVÍO DE MENSAJES DE DIETAS A MEDIREST DIET  **********/
/*******************************************************************/

	/*	--- ¡ N O   M O D I F I C A B L E ! ---  */
	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionTareaManejador.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Db.php');

	$goAplicacionTareaManejador = new CRON\AplicacionTareaManejador(__FILE__,__DIR__);

	/*	--- CLASES Y OBJETOS ADICIONALES ---  */
	ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
	date_default_timezone_set('America/Bogota');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.NutricionConsulta.php');
	//	--- FIN DE CLASES Y OBJETOS ADICIONALES ---


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
		global $goDb;
		$loNut = new NUCLEO\NutricionConsulta();
		$lbEsFestivo = false;

		// Validar si es sábado o domingo
		$lnDia = date('N');
		if (in_array($lnDia, [6,7])) {
			$lbEsFestivo = true;
		} else {
			// Validar si la fecha actual es un festivo
			$lcFecha = date('Ymd');
			$laTemp = $goDb->from('UNIFEC')->where("DFEDFE=$lcFecha")->get('array');
			$lbEsFestivo = $goDb->numRows()>0;
		}
		$goAplicacionTareaManejador->evento($lbEsFestivo ? 'Envío de Cena automática': 'No se envía');

		if ($lbEsFestivo) {

			// Consultar los pisos que se deben enviar
			$laSecciones = explode('|', trim($goDb->obtenerTabmae1('DE2TMA || OP5TMA','NUTRICIO',"CL1TMA='DIETAS' AND CL2TMA='PISOSAUT' AND ESTTMA=''", null, '')));
			$goAplicacionTareaManejador->evento('Secciones a enviar: '.json_encode($laSecciones));

			// Enviar piso por piso
			foreach ($laSecciones as $laSeccion) {
				try {
					$laRta = $loNut->generarEnviarDatosMedirest('SECCION', $laSeccion);
					$lcMsj = (is_array($laRta) && isset($laRta['message'])) ? '- '.$laRta['message'] : '';
					$goAplicacionTareaManejador->evento("Envío de sección $laSeccion $lcMsj");
				} catch(Exception $loError){
					$goAplicacionTareaManejador->evento($loError->getMessage());
				}
			}
		}

	}

	//	--- FIN FUNCION PRINCIPAL --
