<?php

//Requeridos
require_once (__DIR__ .'/../controlador/class.Aplicacion.php');
require_once (__DIR__ .'/../controlador/class.Db.php');

// Mostrar advertencia si se usa una versión de PHP por debajo de 7.2, esto tiene que suceder aquí porque se usará la sintaxis en referencia	
if (version_compare(PHP_VERSION, '7.2') === -1) {
	http_response_code(500);
	$lcError = 'Esta versi&oacute;n requiere al menos PHP 7.2<br/>Usted est&aacute; ejecutando actualmente '. PHP_VERSION .'. Por favor actualice su versión de PHP.<br/>';
	die($lcError);
}

// Establece cuáles errores de PHP son notificados; error_reporting(E_ALL);
error_reporting(E_ERROR | E_WARNING | E_PARSE);	

// Estableciendo los valores de directiva de configuración requeridos
ini_set('error_reporting', E_ERROR | E_WARNING | E_PARSE);
ini_set('log_errors',TRUE);
ini_set('html_errors',TRUE);
ini_set('error_log',(strtoupper(substr(PHP_OS, 0, 3))==='WIN'?'c:/temp/error-'.date("Y-m").'.log':'/var/www/html/logs/error-'.date("Y-m").'.log'));
ini_set('display_errors',TRUE);
ini_set('register_argc_argv',TRUE);
ini_set('post_max_size','500M');
ini_set('upload_max_filesize','1024M');
ini_set('include_path', __DIR__);
ini_set("session.cache_expire",16*60*60);
ini_set("session.cookie_lifetime",16*60*60);
ini_set('session.gc_maxlifetime',16*60*60); // Inactividad 16 Horas
ini_set('default_charset', 'utf-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Establece la zona horaria predeterminada usada por todas las funciones de fecha/hora en un script.
date_default_timezone_set('America/Bogota');

//Constantes
define('HCW_NAME', 'HistoriaClinicaWeb');
define('HCW_DATA', 'HistoriaClinicaWebDatos');

// Sesión
if(isset($_SESSION)==false) { session_start(); }
if(!isset($_SESSION[HCW_NAME])){ $_SESSION[HCW_NAME] = new NUCLEO\Aplicacion(); }

// Información se aplicacion y securidad por SSL
if(isset($_SESSION[HCW_NAME])){ 
	$_SESSION[HCW_NAME]->update();
}
?>