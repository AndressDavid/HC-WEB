<?php
	require_once (__DIR__ .'/nucleo/publico/constantes.php');

	$llLoad=false;
	$laLoad=array();
	$lcFile='';
	$lcSeccion='';
	$lcUrlDefault='';
	$lnStepsSecods=60;

	if(isset($_GET['especial'])){
		$lcFile='nucleo/vista/pantalla/'.trim($_GET['especial']).'.php';
	
	}elseif(isset($_GET['dashboard-dsat'])){
		$lcFile='nucleo/vista/pantalla/dashboard-dsat.php';
		
	}elseif(isset($_GET['seccion'])){
		$lcSeccion=$_GET['seccion'];
		$lcFile='nucleo/vista/pantalla/seccion.php';
	}
	
	
	
	function flBetween($tnValue=0, $tnLow=0, $tnHight=0){
		return ($tnValue>$tnLow && $tnValue<=$tnHight);
	}	
	
	$llLoad=(is_file($lcFile));
	$lcEntorno = $goDb->obtenerEntorno();
?><!doctype html>
<html lang="en" class="h-100">
	<head>
		<!-- Cabecera -->
		<?php
			$lcInclude = file_get_contents(__DIR__ .'/nucleo/publico/head.php');
			$lcInclude = str_replace('<script type="text/javascript" src="vista-comun/js/modalSesionHCWeb.js"></script>',"",$lcInclude);
			$lcInclude = str_replace('<script type="text/javascript" src="vista-comun/js/comun.js"></script>',"",$lcInclude);
			print($lcInclude);
		?>
		<!-- Estilos especificos -->
		<link href="vista-pantalla/css/style.css" rel="stylesheet">	
		
	</head>
	<body class="h-100">
		<div class="progress-bar-tv">
			<div class="progress">
				<div id="progressStep" class="progress-bar progress-bar-striped bg-success progress-bar-animated" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
			</div>
		</div>
		<div id="urlFrame" style="position: fixed; background-color: black; color: white; margin-bottom: 5px; margin-left: 5px; font-size: 0.9em; bottom: 0px; padding-left: 5px; padding-right: 5px;"></div>

		<?php if($llLoad==false){ ?>
		<!-- Profile menú list -->
		<div class="container h-100">
			<div class="row h-100">
				<div class="col-md-12 pt-2">
					<?php
						if ($lcEntorno=='desarrollo'){
							printf('<div id="inner-message" class="alert alert-danger" role="alert"><h6>Historia Clínica Web - Entorno de Pruebas - %s</h6></div>',isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:'Unkonow');
						}
					?>
					<div class="pricing-header px-3 py-3 pt-md-5 mx-auto text-center">
						<img src="publico-imagenes/logo/main-logo-red.svg">
					</div>

					<footer id="main" class="pt-4" data-step="0" data-steps="<?php print($lnStepsSecods); ?>" data-loaded="0">
						<div class="row" style="font-size: 1.2em;">
							<div class="col-md-6">
								<h5 class="border-bottom p-2">Tableros alertas tempranas en pisos</h5>
								<ul class="list-unstyled text-small">
									<?php
										require_once (__DIR__ .'/nucleo/controlador/class.SeccionesHabitacion.php');
										require_once (__DIR__ .'/nucleo/controlador/class.AplicacionFunciones.php');

										$laSecciones = (new NUCLEO\SeccionesHabitacion())->aSecciones;
										$laFunciones = new NUCLEO\AplicacionFunciones();
										$lcIp=$laFunciones->localIp();

										if(is_array($laSecciones)==true){
											foreach($laSecciones as $lcSeccion => $laSeccion){
												if($laSeccion['UBICACION']=='P'){
													if($laSeccion['SALA']<>'S'){
														$lcMarca='<i class="far fa-hospital"></i>';
														if($laFunciones->isSearchStrInStr($laSeccion['MONITOREO'], $lcIp)==true){
															$lcUrlDefault=sprintf('?seccion=%s',$lcSeccion);
															$lcMarca='<i class="fas fa-sync fa-spin"></i>';
														}
														printf('<li><a class="btn btn-light btn-block text-left" href="?seccion=%s"> %s %s <b>%s</b></a></li>',$lcSeccion,$lcMarca,$laSeccion['NOMBRE'],$lcSeccion);
													}
												}
											}
										}
									?>
									<li><a class="btn btn-light btn-block text-left" href="?seccion"><i class="far fa-hospital"></i> TODOS LAS SECCIONES <b>**</b></a></li>
								</ul>
							</div>
							<div class="col-md-6">
								<h5 class="border-bottom p-2">Tableros especiales</h5>
								<ul class="list-unstyled text-small">
									<?php
										if($laFunciones->isSearchStrInStr($lcIp,'172.20.30')==true || $lcIp=='::1'){
											print('<li><a class="btn btn-light btn-block text-left" href="http://hcwp.shaio.org/pantalla.php?especial=ti"><i class="fas fa-filter"></i> DASHBOARD TI</a></li>');
										}
									?>
									<li><a class="btn btn-light btn-block text-left" href="?dashboard-dsat"><i class="fas fa-tachometer-alt"></i> DASHBOARD DSAT</a></li>
									<li><a class="btn btn-light btn-block text-left" href="vista-programacion-salas/dashboard"><i class="fas fa-tachometer-alt"></i> DASHBOARD Salas de Cirugía</a></li>
									<li><a class="btn btn-light btn-block text-left" href="vista-programacion-salas/monitor"><i class="fas fa-tachometer-alt"></i> Programación Salas de Cirugía</a></li>
								</ul>
							</div>
						</div>
						<p class="text-center">Haga clic en uno de los perfiles. <?php if(!empty($lcUrlDefault)){ ?>Si no selecciona uno en <span id="stepsShow"><?php print($lnStepsSecods); ?></span> segundos se cargara el perfil por defecto.<?php } ?></p>
					</footer>

				</div>
			</div>
		</div>
		<?php if(empty($lcUrlDefault)==false){ ?>
		<script type="text/javascript">
			$(document).ready(function(){
				setInterval(function(){
					setPage();
				}, 1000);

				setTimeout(function(){
					location.reload(true);
				}, 1000 * 60 *10);

				function setPage(){
					lcUrl='<?php print($lcUrlDefault); ?>';
					lnStep=parseInt($('#main').data("step"));
					lnUrlStep=parseInt($('#main').data("steps"));

					if(lnStep>=lnUrlStep){
						window.location = lcUrl;
					}else{
						lnStep+=1;
						$('#main').data("step",lnStep);
					}
					$('#progressStep').css('width',parseInt((lnStep*100)/lnUrlStep)+'%');
					$('#stepsShow').html(parseInt(lnUrlStep-lnStep));
				}
			});
		</script>
	<?php
			}
		}else{
	?>
		<!-- Load iframes -->
		<div class="h-100">
	<?php
		if ($lcEntorno=='desarrollo'){
			printf('<div id="inner-message" class="alert alert-danger" role="alert"><h6>Historia Clínica Web - Entorno de Pruebas - %s</h6></div>',isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:'Unkonow');
		}
	?>
			<div id="main" data-index="0" data-step="0" data-steps="0" data-loaded="0" class="h-100">
			</div>
		</div>

		<script type="text/javascript">
			$(document).ready(function(){
				"use strict";
				var laPaginas = JSON.parse('<?php if(is_file($lcFile)==true){include($lcFile);};?>');

				setPage();

				setInterval(function(){
					setPage();
				}, 1000);

				setTimeout(function(){
					location.reload(true);
				}, 1000 * 60 *10);

				function setPage(){
					var lnIndex=parseInt($('#main').data("index"));

					<?php if($lcFile=='nucleo/vista/pantalla/ti.php'){ ?>
					$('#urlFrame').html(laPaginas[lnIndex][0]);
					<?php } ?>

					if(parseInt($('#main').data("loaded"))==0){
						$('#main').html('<iframe name="viewPage" src="'+laPaginas[lnIndex][0]+'" width="100%" height="100%" frameborder="0">Tu navegador no soporta iframes</iframe>');
						$('#main').data("step",0);
						$('#main').data("steps",laPaginas[lnIndex][1]);
						$('#main').data("loaded",1);
					}
					var lnStep=parseInt($('#main').data("step"));
					var lnUrlStep=parseInt($('#main').data("steps"));


					if(lnStep>=lnUrlStep){
						lnIndex = (lnIndex+1<laPaginas.length?lnIndex+1:0);
						$('#main').html('<iframe name="viewPage" src="'+laPaginas[lnIndex][0]+'" width="100%" height="100%" frameborder="0">Tu navegador no soporta iframes</iframe>');
						$('#main').data("index",lnIndex);
						$('#main').data("step",0);
						$('#main').data("steps",laPaginas[lnIndex][1]);
					}else{
						lnStep+=1;
						$('#main').data("step",lnStep);
					}
					$('#progressStep').css('width',parseInt((lnStep*100)/lnUrlStep)+'%');
				}
			});
		</script>
		<?php } ?>

		<!-- Pie de pagina -->
		<?php //include(__DIR__ .'/nucleo/publico/footer.php'); ?>
	</body>
</html>