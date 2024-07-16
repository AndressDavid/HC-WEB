<?php

require_once __DIR__ .'/../../nucleo/controlador/class.Db.php';

date_default_timezone_set('America/Bogota');
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);
ini_set('log_errors',TRUE);
ini_set('html_errors',TRUE);
ini_set('error_log',(strtoupper(substr(PHP_OS, 0, 3))==='WIN'?'c:/temp/error-'.date('Y-m').'.log':'/var/www/html/logs/error-'.date('Y-m').'.log'));
ini_set('display_errors',FALSE);
ini_set('register_argc_argv',TRUE);
ini_set('post_max_size','50M');
ini_set('upload_max_filesize','50M');
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// evitar la interrupción del script
set_time_limit(0);

//Constantes
define('HCW_NAME', 'HistoriaClinicaWeb');
