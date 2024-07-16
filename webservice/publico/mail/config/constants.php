<?php
	date_default_timezone_set('America/Bogota');
	ini_set('error_reporting', E_ALL);
	error_reporting(E_ALL);
	ini_set('log_errors',TRUE);
	ini_set('html_errors',FALSE);
	ini_set('error_log','errores/error-'.date('Ym').'.log');
	ini_set('display_errors',FALSE);
	ini_set('register_argc_argv',TRUE);
	ini_set('post_max_size','50M');
	ini_set('upload_max_filesize','50M');
?>