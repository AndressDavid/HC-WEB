<?php
	require_once (__DIR__ .'/../../../nucleo/publico/constantes.php') ;
	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionFunciones.php') ;

	include('config/constants.php');
	include('config/functions.php');
	$lnFilesLogSize=0;
	$laFunciones = new NUCLEO\AplicacionFunciones();
	
	$lcIp=$laFunciones->localIp();
	if($laFunciones->isSearchStrInStr($lcIp,'172.20.30')==true || $lcIp=='::1'){	
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
							<h1>WebMailService<br/></h1>
							<p>Esta pagina contiene el listado de logs del WebService para env&iacute;o de correos electr&oacute;nicos, los archivos se generan de forma autom&aacute;tica agrupados por a&ntilde;o, mes y d&iacute;a.<br />
							Definición del <a href="webservice.php?WSDL" target="_blank">WebService</a></p>
						</div>
					</div>
					<div class="row mb-2">
						<div class="col-md-6">
							<div class="card">
								<div class="card-header"><h3>Resumen por d&iacute;a</h3></div>
								<div class="card-body">
									<h4>Log de correos enviados</h4>
									<table class="table table-sm table-striped" id="logEnviados">
									  <thead>
										<tr>
										  <td>Enlace al log</th>
										  <th class="text-right">Tama&ntilde;o</th>
										  <th class="text-right">Ultima modificaci&oacute;n</th>
										</tr>
									  </thead>
									  <tbody>
									<?php
										$lnFiles=0;
										$lnFilesSize=0;
										$lcPath="logs/";
										$lcListFiles=getListLogFiles($lcPath);

										if(!empty($lcListFiles)){
											$laListFiles=explode(",",$lcListFiles);
											arsort($laListFiles);
											foreach ($laListFiles as $lcLogFile) {
												if(is_file($lcLogFile)==true){
													$laLogFileInfo = pathinfo($lcLogFile);
													$lnFiles+=1;
													$lnFilesSize+=getFileSize($lcLogFile);
									?>
										<tr>
										  <td><a href='visor.php?file=<?php print(base64_encode($lcLogFile)); ?>' target='_blank'><?php print(strtoupper($laLogFileInfo['filename']));?></a></td>
										  <td class="text-right"><?php print(getFileSizeWithUnit($lcLogFile,-1,true));?></td>
										  <td class="text-right"><?php print(date("Y-m-d h:i:s a", filemtime($lcLogFile))); ?></td>
										</tr>
										<?php
													}
											}
										}
										$lnFilesLogSize+=$lnFilesSize;
									?>
									  </tbody>
									  <tfoot>
										<tr>
										  <th>Logs listados <?php print($lnFiles); ?></th>
										  <th class="text-right"><?php print(getFileSizeWithUnit("", 2, true, false, $lnFilesSize));?></th>
										  <th>&nbsp;</th>
										</tr>
									  </tfoot>
									</table>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card">
								<div class="card-header"><h3>Resumen por mes</h3></div>
								<div class="card-body">
									<h4>Log de errores del WebService</h4>
									<table class="table table-sm table-striped" id="logErrores">
									  <thead>
										<tr>
										  <th>Enlace al log</th>
										  <th class="text-right">Tama&ntilde;o</th>
										  <th class="text-right">Ultima modificaci&oacute;n</th>
										</tr>
									  </thead>
									  <tbody>
									<?php
										$lnFiles=0;
										$lnFilesSize=0;
										$lcPath="errores/";
										$lcListFiles=getListLogFiles($lcPath,"log");
										print("<!--".$lcListFiles."-->");
										if(!empty($lcListFiles)){
											$laListFiles=explode(",",$lcListFiles);
											arsort($laListFiles);
											foreach ($laListFiles as $lcLogFile) {
												if(is_file($lcLogFile)==true){
													$laLogFileInfo = pathinfo($lcLogFile);
													$lnFiles+=1;
													$lnFilesSize+=getFileSize($lcLogFile);
									?>
										<tr>
										  <td><a href='visor.php?file=<?php print(base64_encode($lcLogFile)); ?>' target='_blank'><?php print(strtoupper($laLogFileInfo['filename']));?></a></td>
										  <td class="text-right"><?php print(getFileSizeWithUnit($lcLogFile,-1,true));?></td>
										  <td class="text-right"><?php print(date("Y-m-d h:i:s a", filemtime($lcLogFile))); ?></td>
										</tr>
										<?php
													}
											}
										}
										$lnFilesLogSize+=$lnFilesSize;
									?>
									  </tbody>
									  <tfoot>
										<tr>
										  <th>Logs listados <?php print($lnFiles); ?></th>
										  <th class="text-right"><?php print(getFileSizeWithUnit("", 2, true, false, $lnFilesSize));?></th>
										  <th>&nbsp;</th>
										</tr>
									  </tfoot>
									</table>
									<h4>Lista de eventos</h4>
									<table class="table table-sm table-striped" id="logEventos">
									  <thead>
										<tr>
										  <td>Enlace al log</td>
										  <td class="text-right">Tama&ntilde;o</td>
										  <td class="text-right">Ultima modificaci&oacute;n</td>
										</tr>
									  </thead>
									  <tbody>
									<?php
										$lnFiles=0;
										$lnFilesSize=0;
										$lcPath="eventos/";
										$lcListFiles=getListLogFiles($lcPath,"log");
										print("<!--".$lcListFiles."-->");
										if(!empty($lcListFiles)){
											$laListFiles=explode(",",$lcListFiles);
											arsort($laListFiles);
											foreach ($laListFiles as $lcLogFile) {
												if(is_file($lcLogFile)==true){
													$laLogFileInfo = pathinfo($lcLogFile);
													$lnFiles+=1;
													$lnFilesSize+=getFileSize($lcLogFile);
									?>
										<tr>
										  <td><a href='visor.php?file=<?php print(base64_encode($lcLogFile)); ?>' target='_blank'><?php print(strtoupper($laLogFileInfo['filename']));?></a></td>
										  <td class="text-right"><?php print(getFileSizeWithUnit($lcLogFile,-1,true));?></td>
										  <td class="text-right"><?php print(date("Y-m-d h:i:s a", filemtime($lcLogFile))); ?></td>
										</tr>
										<?php
													}
											}
										}
										$lnFilesLogSize+=$lnFilesSize;
									?>
									  </tbody>
									  <tfoot>
										<tr>
										  <th>Logs listados <?php print($lnFiles); ?></th>
										  <th class="text-right"><?php print(getFileSizeWithUnit("", 2, true, false, $lnFilesSize));?></th>
										  <th>&nbsp;</th>
										</tr>
									  </tfoot>
									</table>
									<h4>Pruebas</h4>
									<table class="table table-sm table-striped" id="logEventos">
									  <thead>
										<tr>
										  <td>Enlace al log</td>
										  <td class="text-right">Tama&ntilde;o</td>
										  <td class="text-right">Ultima modificaci&oacute;n</td>
										</tr>
									  </thead>
									  <tbody>
									<?php
										$lnFiles=0;
										$lnFilesSize=0;
										$lcPath="test/";
										$lcListFiles=getListLogFiles($lcPath,"log");
										print("<!--".$lcListFiles."-->");
										if(!empty($lcListFiles)){
											$laListFiles=explode(",",$lcListFiles);
											arsort($laListFiles);
											foreach ($laListFiles as $lcLogFile) {
												if(is_file($lcLogFile)==true){
													$laLogFileInfo = pathinfo($lcLogFile);
													$lnFiles+=1;
													$lnFilesSize+=getFileSize($lcLogFile);
									?>
										<tr>
										  <td><a href='visor.php?file=<?php print(base64_encode($lcLogFile)); ?>' target='_blank'><?php print(strtoupper($laLogFileInfo['filename']));?></a></td>
										  <td class="text-right"><?php print(getFileSizeWithUnit($lcLogFile,-1,true));?></td>
										  <td class="text-right"><?php print(date("Y-m-d h:i:s a", filemtime($lcLogFile))); ?></td>
										</tr>
										<?php
													}
											}
										}
										$lnFilesLogSize+=$lnFilesSize;
									?>
									  </tbody>
									  <tfoot>
										<tr>
										  <th>Logs listados <?php print($lnFiles); ?></th>
										  <th class="text-right"><?php print(getFileSizeWithUnit("", 2, true, false, $lnFilesSize));?></th>
										  <th>&nbsp;</th>
										</tr>
									  </tfoot>
									</table>									
									<h4>General</h4>
									<p>Espacio total usado por los distintos logs <?php print(getFileSizeWithUnit("", 2, true, false, $lnFilesLogSize)); ?></p>
									<p>Realizar prueba con <a href="test.php" target="_blank">cliente PHP</a></p>
									<p>Realizar prueba con <a href="test-secure.php" target="_blank">cliente PHP con https</a></p>	
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