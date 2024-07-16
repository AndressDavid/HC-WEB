<?php
	require_once (__DIR__ .'/../../../nucleo/publico/constantes.php') ;
	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionFunciones.php') ;
	
	include('config/constants.php');
	include('config/functions.php');
	$laFunciones = new NUCLEO\AplicacionFunciones();
	
	$lcIp=$laFunciones->localIp();
	if($laFunciones->isSearchStrInStr($lcIp,'172.20.30')==true || $lcIp=='::1'){	
		$lcContenido = "A un no se ha creado el archivo";
		$lnLineas = 0;
		$lcFileOpen=base64_decode(isset($_GET["file"])?$_GET["file"]:'');
		if(is_file($lcFileOpen)){
			$lcContenido = utf8_encode(file_get_contents($lcFileOpen));
			$lnLineas = substr_count($lcContenido,PHP_EOL);
		}

?><!doctype html>
<html lang="en" class="h-100">
	<head>
		<!-- Cabecera -->
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">

		<!-- Tags de Información -->
		<meta name="description" content="HCW">
		<meta name="google" content="notranslate">
		<meta http-equiv="Content-Language" content="en" />
		<meta http-equiv="Last-Modified" content="<?php echo gmdate('D, d M Y H:i:s \G\M\T', time()); ?>" />
		<meta http-equiv="cache-control" content="no-store, no-cache, must-revalidate" />
		<meta http-equiv="cache-control" content="post-check=0, pre-check=0" />
		<meta http-equiv="cache-control" content="max-age=0" />
		<meta http-equiv="cache-control" content="no-cache" />
		<meta http-equiv="pragma" content="no-cache" />
		<meta http-equiv="expires" content="<?php echo gmdate('D, d M Y H:i:s \G\M\T', time()); ?>" >
		<meta http-equiv="expires" content="-1" >

		<!-- Meta Data -->
		<meta name="AUTHOR" content="FCS"/>
		<meta name="COPYRIGHT" content="FCS"/>
		<meta name="DC.CREATOR" content="FCS"/>
		<meta name="PUBLISHER" content="FCS"/>
		<meta name="CREATOR" content="FCS"/>

		<!-- Meta data dinámicos -->
		<meta name="ROBOTS" content="INDEX FOLLOW">
		<meta name="GOOGLEBOT" content="INDEX FOLLOW"/>
		<meta name="AUDIENCE" content="ALL"/>
		<meta name="RATING" content="GENERAL"/>
		<meta name="DISTRIBUTION" content="GLOBAL"/>
		<title>HCW WebMailService</title>
		<link rel="icon" href="../../../nucleo/publico/ico/favicon.ico"/>

		<!-- CSS -->
		<link rel="stylesheet" href="../../../nucleo/publico/css/style.css">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="../../../nucleo/publico/complementos/bootstrap/4.1.3-dist/css/bootstrap.min.css">

		<!-- Fuentes -->
		<link rel="stylesheet" href="../../../nucleo/publico/complementos/fontawesome/5.3.1-web/css/all.min.css">

		<!-- Scripts -->
		<script src="../../../nucleo/publico/complementos/jquery/3.3.1/jquery-3.3.1.min.js"></script>

		<!-- Script -->
		<script src="../../../nucleo/publico/complementos/bootstrap/4.1.3-dist/js/bootstrap.min.js"></script>
	</head>
	<body class="h-100">
		<div class="container-fluid h-100">
			<div class="row h-100">
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-12">
							<h1>Visor WebMailService<br/></h1>
							<p>Esta pagina muestra el detalle del tipo de registro seleccionado. </p>
						</div>
					</div>
					<div class="row mb-2">
						<div class="col-md-12">					
							<div class="card">
								<div class="card-header"><h4>Log <span class="badge badge-secondary">Lineas <?php print($lnLineas); ?></span></h4></div>
								<div class="card-body">
									<pre><?php print($lcContenido); ?></pre>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html><?php
	}else{
		http_response_code(401);
	}
?>