<?php
	require_once (__DIR__ .'/nucleo/publico/constantes.php');
	require_once (__DIR__ .'/nucleo/publico/headJSON.php');

	$lcManifiestHost=$_SERVER['SERVER_NAME'];
	$lcManifiestPath = trim(dirname($_SERVER['PHP_SELF']));
	$lcManifiestURL = (isset($_SERVER['HTTPS'])?'http':'http')."://".$lcManifiestHost.$lcManifiestPath.(substr($lcManifiestPath,-1)=='/'?'':'/').'index.php';
	$lcManifiestName = 'Historia Clinica WEB';
	$lcManifiestShortName = 'HCW';
	$lcManifiestDescription = "Fundacion Clinica Shaio | Historia Clinica WEB";
	
	if(isset($goDb)){
		$lcEntorno = $goDb->obtenerEntorno();
		$lcManifiestName .= ($lcEntorno=='desarrollo'?' | '.$lcManifiestHost.(empty($lcManifiestHost)?'':' | ').'Pruebas':'');
		$lcManifiestShortName .= ($lcEntorno=='desarrollo'?' | '.$lcManifiestHost.(empty($lcManifiestHost)?'':' | ').'Pruebas':'');
		$lcManifiestDescription .= ($lcEntorno=='desarrollo'?' | '.$lcManifiestHost.(empty($lcManifiestHost)?'':' | ').'Pruebas':'');
	}
	
	$laSettingsManifiest = [
								"name" => $lcManifiestName,
								"short_name" => $lcManifiestShortName,
								"description" => $lcManifiestDescription,
								"lang" => "es-ES",
								"display" => "fullscreen",
								"theme_color" => "#ec3a3b",
								"background_color" => "#ec3a3b",
								"start_url" => "index.php",
								"prefer_related_applications" => false,
								"related_applications" => [["platform" => "web", "url" => $lcManifiestURL]],
								"icons" =>	[
												["src" => "publico-imagenes/logo/main-logo-app-white-background48.png", "sizes" => "48x48","type" => "image/png"],
												["src" => "publico-imagenes/logo/main-logo-app-white-background72.png", "sizes" => "72x72","type" => "image/png"],
												["src" => "publico-imagenes/logo/main-logo-app-white-background96.png", "sizes" => "96x96","type" => "image/png"],
												["src" => "publico-imagenes/logo/main-logo-app-white-background144.png", "sizes" => "144x144","type" => "image/png"],
												["src" => "publico-imagenes/logo/main-logo-app-white-background168.png", "sizes" => "168x168","type" => "image/png"],
												["src" => "publico-imagenes/logo/main-logo-app-white-background192.png", "sizes" => "192x192","type" => "image/png"],
												["src" => "publico-imagenes/logo/main-logo-app-white-background512.png", "sizes" => "512x512","type" => "image/png"]
											]
							];
							
	$lcSettingsManifiest = json_encode($laSettingsManifiest, JSON_UNESCAPED_SLASHES);
	print($lcSettingsManifiest);
?>