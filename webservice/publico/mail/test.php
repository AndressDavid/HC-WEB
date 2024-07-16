<?php
	require_once (__DIR__ .'/../../../nucleo/publico/constantes.php') ;
	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionFunciones.php') ;
	
	include('config/constants.php');
	include('config/functions.php');
	$laFunciones = new NUCLEO\AplicacionFunciones();

	$lcIp=$laFunciones->localIp();
	if($laFunciones->isSearchStrInStr($lcIp,'172.20.30')==true || $lcIp=='::1'){
		include('../../complementos/nusoap-php7/0.95/lib/nusoap.php');
		$lcUrlWsdl="http://hcwp.shaio.org/webservice/publico/mail/webservice.php?wsdl"; //url del servicio
		$lcDateTime=date("Y-m-d H:i:s");
		$laSettings=array('tcServer' => 'mail.shaio.org',
						  'tnPort' => 25,
						  'tcUser' => 'soporte@shaio.org',
						  'tcPass' => '********',
						  'tcFrom' => 'soporte@shaio.org ',
						  'tcTO' => 'jose.ortiz@shaio.org',
						  'tcCC' => '',
						  'tcBCC' => '',
						  'tcSubject' => 'Prueba cliente php '.$lcDateTime,
						  'tcBody' => 'Prueba',
						  'tnAuthMode' => 0,
						  'tnPriority' => 0,
						  'tnImportance' => 0,
						  'tnDisposition' => 0,
						  'tcOrganization' => 'Fundacion Clincia Shaio',
						  'tcKeywords' => 'prueba',
						  'tcDescription' => 'prueba');


		$loClient = new nusoap_client($lcUrlWsdl,'wsdl');
		$lcError = $loClient->getError();
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
				<!-- inicio -->
					<h1>WebMailService<br/></h1>
					<p>Esta pagina envi&aacute; un correo de prueba usando los par&aacute;metros abajo descritos y utilizando el <a href="<?php print($lcUrlWsdl); ?>" target="_blank"><?php print($lcUrlWsdl); ?></a></p>
					<h3>Resultado del envi&oacute;</h3>
					<div>
						<div class="row">
							<div class="col-md-6">
								<div class="card">
									<div class="card-header"><h4>Par&aacute;metros</h4></div>
									<div class="card-body">
										<table class="table table-sm table-striped" id="logEnviado">
											<thead>
												<tr>
													<th class='text-right'>PROPIEDAD</th>
													<th>VALOR</th>
												</tr>
											</thead>
											<tbody>
												<?php
												foreach($laSettings as $lcKey=>$lcValue){
													printf("<tr><td class='text-right'><b>%s</b> : </td><td>%s</td></tr>",$lcKey,($lcKey=="tcPass"?"*********":$lcValue));
												}
												?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="card">
									<div class="card-header"><h4>Resultado</h4></div>
									<div class="card-body">
										<table class="table table-sm table-striped" id="logEnviado">
											<thead>
												<tr>
													<th class='text-right'>PROPIEDAD</th>
													<th>VALOR</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td class='text-right'><b>Respuesta</b> : </td>
													<td>
														<pre style="    padding: 0px; border: none; text-align: left;"><?php
															$lcResult="";
															if ($lcError) {
																printf("Constructor error: %s",$lcError);
															}else{
																$lcResult = $loClient->call('SendMail',$laSettings);
																print(trim(htmlentities($lcResult)));
															}
														?></pre>
													</td>
												</tr>
												<tr>
													<td class='text-right'><b>Resultado</b> : </td>
													<td>
														<b><?php print(trim(getInbetweenStrings($lcResult,"<RESULT>","</RESULT>"))=="1"?"Enviado":"No se envio"); ?></b>
													</td>
												</tr>
												<tr>
													<td class='text-right'><b>Log</b> : </td>
													<td>
														<?php
															$lcArchivo = __DIR__ ."/test/test-".date('Ym').".log";
															$lcTexto="[".date("Y-m-d H:i:s")."] Prueba ";
															file_put_contents($lcArchivo,$lcTexto.PHP_EOL, FILE_APPEND | LOCK_EX);
															print($lcArchivo);
														?>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				<!-- fin -->
				</div>
			</div>
		</div>
	</body>
</html><?php
	}else{
		http_response_code(401);
	}
?>