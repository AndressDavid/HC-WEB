<?php
	// Establece la zona horaria predeterminada usada por todas las funciones de fecha/hora en un script.
	date_default_timezone_set('America/Bogota');

	//Horario básico
	$laWorkTimes = array();
	$lnWorkTimes = mktime(0,0,0);
	$laWorkTimes[] = mktime(date("H"),date("i"),date("s"))-$lnWorkTimes ;
	$laWorkTimes[] = mktime(7,0,0)-$lnWorkTimes ;
	$laWorkTimes[] = mktime(17,35,0)-$lnWorkTimes ;
	
	// Configuración de lista a selecionar
	$webResoucesDashboardTI = require __DIR__ . '/../../privada/webResoucesDashboardTI.php';

	$lcList = (($laWorkTimes[0]>$laWorkTimes[1] && $laWorkTimes[0]<=$laWorkTimes[2]) ? 'list-a' : 'list-b' );
	$laUrl = $webResoucesDashboardTI[$lcList];

	echo json_encode($laUrl);
?>