<?php

// http://localhost/hcp-php-server-2022/tareas/procesos/websockets/script.php

/********************************************************************/
/**********   ABRIR PUERTOS A LA ESCUCHA SI ES NECESARIO   **********/
/********************************************************************/

/*	--- ¡ N O   M O D I F I C A B L E ! ---  */
$lcRutaClass = __DIR__ .'/../../../nucleo/controlador/';
require_once $lcRutaClass .'class.AplicacionTareaManejador.php';
require_once $lcRutaClass .'class.Db.php';

$goAplicacionTareaManejador = new CRON\AplicacionTareaManejador(__FILE__,__DIR__);

/*	--- CLASES Y OBJETOS ADICIONALES ---  */
date_default_timezone_set('America/Bogota');
ini_set('max_execution_time', 10*60); // 10 min
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

	// Modelos
	$laModelos = [
		'RAPID',
		// 'ROCHEGLC',
	];
	foreach ($laModelos as $lcModelo) {

		try{
			// Dominio y Puerto a comprobar
			list($lcHost, $lcPuerto) = fnHostPort($lcModelo);

			// Valida si se ejecuta en el mismo servidor, no en el host parametrizado!!
			// Esto porque si no está abierto el puerto, ejecuta localmente el archivo que lo abre
			// $lcHost = $_SERVER['HTTP_HOST']=='localhost' ? gethostbyname(gethostname().'.SHAIO.COM') : $_SERVER['SERVER_ADDR'];

			$lnTiempoLimite = 10; // seg
			$lcMsg = "Modelo: {$lcModelo} - Server: {$lcHost} - Puerto: $lcPuerto - Tiempo Límite: $lnTiempoLimite seg";

			// *** Usando fsockopen ***
			$loCliente = fsockopen($lcHost,$lcPuerto,$lnError,$lcError,$lnTiempoLimite);

			// *** Usando stream_socket_client ***
			// $loCliente = stream_socket_client("tcp://{$lcHost}:{$lcPuerto}", $lnError, $lcError);

			if ($loCliente === false) {
				// Fallo al conectar
				$goAplicacionTareaManejador->evento("$lcMsg - Puerto cerrado, se intenta abrir de nuevo");
				executeAsyncShellCommand(__DIR__ . "/../../../websockets/publico/hl7_recibeoru/server.php modelo={$lcModelo}");

			} else {
				// Conexión realizada con éxito
				fclose($loCliente);
				$goAplicacionTareaManejador->evento("$lcMsg - Puerto abierto OK");
			}

		} catch (Exception $loError) {
			$goAplicacionTareaManejador->error($loError->getMessage());
		}
	}
	unset($goAplicacionTareaManejador);
}
//	--- FIN FUNCION PRINCIPAL --


/*	--- FUNCIONES ADICIONALES ---  */

function fnHostPort($tcModelo)
{
	global $goDb;
	// Valores por defecto
	$lcDefault = '172.20.10.102:51500';
	$laWhere = [
		'CL1TMA'=>'MODELO',
		'CL2TMA'=>$tcModelo,
		'CL3TMA'=>'SERVIDOR',
		'CL4TMA'=>'ORU',
		'ESTTMA'=>'',
	];
	$lcDatos = $goDb->obtenerTabMae1('DE2TMA', 'HL7_PRM', $laWhere, null, $lcDefault);
	$laReturn = explode(':', trim($lcDatos));

	return $laReturn;
}

/*
 *	Ejecuta un comando de consola en un entorno linux o windows, sin esperar el resultado.
 *	Útil para ejecutar tareas extensas.
 *	https://ourcodeworld.com/articles/read/207/how-to-execute-a-shell-command-using-php-without-await-for-the-result-asynchronous-in-linux-and-windows-environments
 *	@param String $comando comando a ejecutar
 */
function executeAsyncShellCommand($comando = null){
	global $goAplicacionTareaManejador;
	if(!$comando){
		throw new Exception("No command given");
	}
	// Si es windows utiliza system, en linux usa shell_exec
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		$lcCmd = 'C:\wamp64\bin\php\php8.1.0\php.exe '.$comando;
		$goAplicacionTareaManejador->evento("Ejecutando $lcCmd en system.");
		system($lcCmd.' > NUL');
	} else {
		// $lcCmd = "/usr/bin/nohup ".$comando." >/dev/null 2>&1 &";
		$lcCmd = "nohup php {$comando} >/dev/null 2>&1 &";
		$goAplicacionTareaManejador->evento("Ejecutando $lcCmd en shell_exec.");
		shell_exec($lcCmd);
	}
}

/*
 *	This will execute $cmd in the background (no cmd window) without PHP waiting for it to finish, on both Windows and Unix
 *	https://www.php.net/manual/en/function.exec.php#86329
 *	@param String $comando comando a ejecutar
 */
function execInBackground($comando) {
    //if (substr(php_uname(), 0, 7) == "Windows"){
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        pclose(popen("start /B ". $comando, "r"));
    }
    else {
        exec("php {$comando} > /dev/null &");
    }
}

//	--- FIN FUNCIONES ADICIONALES ---
