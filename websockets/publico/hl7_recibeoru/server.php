<?php
/*
 *	Abrir puerto a la escucha para la recepción de mensajes HL7
 *		@param string modelo: modelo que se usa para la conexión, tabla TABMAE con tiptma='HL7_PRM'
 *	Para ejecutarlo digitar en la consola
 *		php.exe server.php modelo=RAPID
 *		En Windows:
 *		1. Activar extension=pdo_odbc en el php.ini en la carpeta donde se aloja el php (por ejemplo C:\wamp64\bin\php\php7.4.0\php.ini)
 *		2. Ejecutar primero cambiando a la carpeta donde está el servidor
 *			> cd C:\wamp64\www\hcp-php-server-2022\websockets\publico\hl7_recibeoru
 *			> C:\wamp64\bin\php\php7.4.0\php.exe server.php modelo=RAPID
 *	Para ejecutarlo desde un navegador web, colocar la ruta y el modelo, por ejemplo
 *		https://hcwp.shaio.org/websockets/publico/hl7_recibeoru/server.php?modelo=RAPID
 *		Presenta problema al cerrarse la pestaña funciona solo unas horas
 *		Hay que evitar que la pestaña se auto refresque, al estar ya abierto el puerto genera error al intentar abrirlo de nuevo
 */

use Ratchet\Server\IoServer;
use NUCLEO_SOCKETSRV\HL7_ORU;
use NUCLEO_SOCKETSRV\Funciones;
use NUCLEO\HL7_RecibeORU;


// Recibe modelo  
$lcModelo = $_REQUEST['modelo']??'XX';
if ($lcModelo=='XX') {
	if ($_SERVER['argc']>1) {
		$laModelo = explode('=', $_SERVER['argv'][1]);
		if ($laModelo[0]=='modelo') {
			$lcModelo=$laModelo[1];
		}
	}
	if ($lcModelo=='XX') {
		echo 'Falta indicar modelo.';
		exit();
	}
}
$lcClase = __DIR__ ."/../../../nucleo/controlador/class.HL7_{$lcModelo}_RecibeORU.php";
if (is_file($lcClase)===false) {
	echo 'No se encuentra la clase correspondiente al modelo.';
	exit();
}


require __DIR__ .'/../constantes.php';
require __DIR__ . '/../../componentes/autoload.php';
require __DIR__ . '/HL7_ORU.php';
require __DIR__ . '/Funciones.php';
require $lcClase;


// Obtiene puerto por el cual escuchará peticiones
list($lcHost, $lcPuerto) = Funciones::fnHostPort($lcModelo);

if (empty($lcHost) || empty($lcPuerto)) {
	echo "Modelo: $lcModelo - Server: $lcHost - Puerto: $lcPuerto - Error con los datos o no se encontraron.";

} else {
	// Valida si el puerto está abierto
	$laPuerto = Funciones::puertoAbierto($lcHost, $lcPuerto);
	if ($laPuerto['abierto']=='NO') {
		//$loServer = IoServer::factory(new HL7_ORU($lcModelo), $lcPuerto);
		$loServer = IoServer::factory(new HL7_ORU($lcModelo), $lcPuerto, $lcHost);
		$loServer->run();

	} elseif ($laPuerto['abierto']=='SI') {
		echo "Modelo: $lcModelo - Server: $lcHost - Puerto: $lcPuerto - Ya se encuentra abierto.";

	} else {
		echo "Modelo: $lcModelo - Server: $lcHost - Puerto: $lcPuerto - No se pudo consultar si está abierto el puerto.";
	}
}

