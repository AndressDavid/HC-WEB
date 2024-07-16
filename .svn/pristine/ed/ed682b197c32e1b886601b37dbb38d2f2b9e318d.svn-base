<?php
	require_once (__DIR__ .'/nucleo/publico/constantes.php');
	require_once (__DIR__ .'/nucleo/controlador/class.Aplicacion.php');
	require_once (__DIR__ .'/nucleo/controlador/class.AplicacionTareasManejador.php');
	use CRON\AplicacionTareasManejador;		
	
	// Definición de parámetros de control
	define("WEBTOKEN", "NenXsVz5VJwWUsiD2yrhJvc3r8YcIqfYzyFpaDfRYrHgK5rNZtuVDgTzzw2VXq36eBMnbztuUE6w5jA92kSg4xvWEFUNgtFXcFCKe7aLdjysgre64efjids6vuvYdtku");
	$llAccessPermit = false;
	$laAllowedServersHosts = array('hcwp.shaio.org', 'hcwd.shaio.org');
	$laAllowedClientsHosts = array('hcwp.shaio.org', 'hcwd.shaio.org');
	$laAllowedClientsIp = array('172.20.10.101', '172.20.10.102');
	$lcHostNameClient =(isset($_SERVER['REMOTE_ADDR'])?trim(strtolower(gethostbyaddr($_SERVER['REMOTE_ADDR']))):'');
	
	// Validación de permisos
	if(isset($_POST['token'])==true){
		$_POST['token'] = htmlentities($_POST['token']);
		if(trim(strtolower($_POST['token']))==trim(strtolower(WEBTOKEN))){
			if(in_array($lcHostNameClient, $laAllowedClientsHosts)==true) {
				if(in_array($_SERVER['HTTP_HOST'], $laAllowedServersHosts)==true) {
					if(in_array($_SERVER['REMOTE_ADDR'], $laAllowedClientsIp)==true) {
						$llAccessPermit=true;
					}
				}
			}
		}
	}
	
	// Acciones según permisos
	if ($llAccessPermit == false){
		header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
		$_GET['403']="Acceso denegado a ".$lcHostNameClient;
		include('error.php');
		exit;
	} else {
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Cron</title>
</head>
<body>
<?php
	printf("%s\n",date("Y-m-d H:i:s"));
	$loAplicacionTareasManejador = new AplicacionTareasManejador(__DIR__ .'/tareas/procesos/');
	$loAplicacionTareasManejador->procesar();
?>
</body>
</html>
<?php
	}
?>
