<?php
	require_once (__DIR__ .'/../../publico/constantes.php');
	require_once (__DIR__ .'/../../controlador/class.AplicacionFunciones.php');

	$lcMensaje='';
	$lcMensajeClass = 'alert-danger';
	
	$llRecuperada = false;
	$llBloqueoIntetos = false;
	
	$lcKeyMaskForgotFor = strtolower('0'.md5('LOGINFOR-'.date('YmdH')).'x');
	$lcKeyMaskUserName = strtolower('1'.md5('PASSWORD-'.date('YmdH')).'y');
	$lcKeyMaskDocument = strtolower('2'.md5('USERNAME-'.date('YmdH')).'z');
	$lcKeyMaskCaptcha = strtolower('3'.md5('CAPTCHA-'.date('YmdH')).'w'); 

	$lcBrowser = NUCLEO\AplicacionFunciones::getBrowserName(isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'*');
	$laBrowserCompiatibleNames = ['Chrome', 'Edge', 'Firefox', 'Safari'];
	$laBrowserCompiatible = [
								['icon'=>'fa-chrome', 'name'=>'Chrome'],
								['icon'=>'fa-edge', 'name'=>'Edge'],
								['icon'=>'fa-firefox', 'name'=>'Firefox' ],
								['icon'=>'fa-safari', 'name'=>'Safari']
							];
	if (isset($_SESSION[HCW_NAME])){

		$llBloqueoIntetos = $_SESSION[HCW_NAME]->oUsuario->fForgotIntentos();

		// Acciones por metodo
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			if ($llBloqueoIntetos==false){
				if(isset($_POST[$lcKeyMaskUserName]) && isset($_POST[$lcKeyMaskDocument]) && isset($_POST[$lcKeyMaskCaptcha])){
					$lcOlvidoPasswordStatus = '';
					
					if ($_SESSION[HCW_NAME]->oUsuario->olvidoPassword($_POST[$lcKeyMaskUserName],$_POST[$lcKeyMaskDocument],$_POST[$lcKeyMaskCaptcha], $lcOlvidoPasswordStatus)==true){
						$lcMensajeClass = 'alert-success';
						$lcMensaje = $lcOlvidoPasswordStatus;
						$llRecuperada = true ;
					}else{
						$lcMensaje = $lcOlvidoPasswordStatus;
					}
				}else{
					$lcMensaje='Información incompleta para recuperar la contraseña';
				}
			}
		}
		// Control sesión previa
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			header('Location: principal');
			die();
		}
	}

	$lcEntorno = $goDb->obtenerEntorno();
?><!doctype html>
<html lang="en">
	<head>
		<!-- Cabecera -->
		<?php
			$lcInclude = file_get_contents("../../publico/head.php");
			$lcInclude = str_replace("publico-complementos","../publico-complementos",$lcInclude);
			$lcInclude = str_replace("publico-css","../publico-css",$lcInclude);
			$lcInclude = str_replace("publico-ico","../publico-ico",$lcInclude);
			$lcInclude = str_replace("hcw-manifiest.json.php","../hcw-manifiest.json.php",$lcInclude);
			$lcInclude = str_replace('<script type="text/javascript" src="vista-comun/js/modalSesionHCWeb.js"></script>',"",$lcInclude);
			$lcInclude = str_replace('<script type="text/javascript" src="vista-comun/js/comun.js"></script>',"",$lcInclude);
			print($lcInclude);
		?>

		<!-- Codigop header -->
		<link rel="stylesheet" href="css/style.css">

	</head>
	<body id="main-app-panel" name="main-app-panel">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6 pt-5">
					<?php
						if ($lcEntorno=='desarrollo'){
							printf('<div id="inner-message" class="alert alert-danger" role="alert"><h6>Historia Clínica Web - Entorno de Pruebas - %s</h6></div>',isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:'Unkonow');
						}
					?>
					<div class="form-top p-3<?php printf($lcEntorno=='desarrollo'?' bg-warning':''); ?>">
						<div class="form-top-left">
							<h1>HC<sup>W</sup></h1>
							<p><b>&iquest;Olvid&oacute; su contrase&ntilde;a?</b><br/>Para recibir una nueva contraseña diligencie la información solicitada</p>
						</div>
					</div>
					<div class="form-bottom p-3">
						<?php if(in_array($lcBrowser,$laBrowserCompiatibleNames)==true){ ?>
						
						<?php if($llRecuperada==false && $llBloqueoIntetos==false){ ?>
						<form role="form" class="login-form" id="<?php print($lcKeyMaskForgotFor); ?>" name="<?php print($lcKeyMaskForgotFor); ?>" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" autocomplete="off">
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<label for="<?php print($lcKeyMaskUserName); ?>">Usuario<br/><small>Se procede a generar una nueva contraseña para el usuario</small></label>
										<input type="text" autocorrect="off" autocapitalize="off" autocomplete="<?php print($lcKeyMaskUserName); ?>" spellcheck="false" id="<?php print($lcKeyMaskUserName); ?>" name="<?php print($lcKeyMaskUserName); ?>" class="form-username form-control form-control-lg" maxlength="10">
									</div>
								</div>
							</div>
							
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<label for="<?php print($lcKeyMaskDocument); ?>">Número de identificación<br/><small>Ingrese su numero de identificación correspondiente a su documento de identidad</small></label>
										<input type="text" autocorrect="off" autocapitalize="off" autocomplete="<?php print($lcKeyMaskDocument); ?>" spellcheck="false" id="<?php print($lcKeyMaskDocument); ?>" name="<?php print($lcKeyMaskDocument); ?>" class="form-password form-control form-control-lg">
									</div>
								</div>
							</div>
							
							<div class="row justify-content-md-center mb-3">
								<div class="col-12">
									<label class="required">C&oacute;digo CAPTCHA de confirmaci&oacute;n<br/><small>Escriba las <?php print($_SESSION[HCW_NAME]->oUsuario->getCaptcha()->getCaptchaLen()); ?> letras rojas de la imagen.</small></label>
									<div class="row">
										<div class="col-md-8">
											<div class="input-group text-center pb-2 w-100">
												<div class="form-control" style="height:<?php print($_SESSION[HCW_NAME]->oUsuario->getCaptcha()->getImgHeight()+15); ?>px !important">
													<img src="imagenes/captcha/captcha" alt="CAPTCHA" class="captcha">
												</div>
												<div class="input-group-append">
													<button id="captcha-reload" class="btn btn-outline-secondary" type="button" id="button-addon2"><i class="fas fa-redo refresh-captcha"></i></button>
												</div>													
											</div>
										</div>
										<div class="col-md-4">
											<div class="input-group mb-3">
												<input type="text" autocorrect="off" autocapitalize="off" autocomplete="<?php print($lcKeyMaskCaptcha); ?>" spellcheck="false" id="<?php print($lcKeyMaskCaptcha); ?>" name="<?php print($lcKeyMaskCaptcha); ?>" class="form-control" placeholder="Escriba aquí" aria-label="Captcha" aria-describedby="captcha-reload" style="height:<?php print($_SESSION[HCW_NAME]->oUsuario->getCaptcha()->getImgHeight()+15); ?>px !important">
											</div>
										</div>
									</div>
								</div>
							</div>
										
							<div class="row">
								<div class="col">
									<button type="submit" class="btn btn-success w-100"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Recuperar contrase&ntilde;a</button>
								</div>
							</div>
						</form>
						<?php } else { ?>
						<a href="/">Regresar</a>
						<?php } ?>
						
						<?php 
							if($llBloqueoIntetos==true){
								$lcMensajeClass = 'alert-danger';
								$lcMensaje = $_SESSION[HCW_NAME]->oUsuario->fForgotIntentosBloqueoMensaje();
							}
						?>
						
						
						<div class="row">
							<div class="col">
								<div id="report-status" name="report-status"><?php print(!empty($lcMensaje)?sprintf('<hr/><div class="alert %s" role="alert">%s</div>',$lcMensajeClass,$lcMensaje):''); ?></div>
							</div>
						</div>


						<?php } else { ?>
						<div class="row">
							<div class="col text-center">
								<p><i class="fas fa-exclamation-circle fa-5x text-warning"></i><br/>Detectamos que se encuentra usando el navegador <span class="font-weight-bolder"><?php print($lcBrowser); ?></span>, que no es compatible con este aplicativo web. Por favor use uno de estos:</p>
								<p>
								<?php
									foreach($laBrowserCompiatible as $laBrowserCompatible){
										printf('<span class="badge badge-success p-2"><i class="fab fa-2x %s"></i><br/>%s</span> ', $laBrowserCompatible['icon'], $laBrowserCompatible['name']);
									}
								?>
								</p>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>


		<!-- Opciones para el login -->
		<script src="js/jquery.backstretch.js"></script>
		<script>
			$.ajaxSetup({
				cache: false
			});

			$(document).ready(function() {

				$('#captcha-reload').click(function() {
					$('.captcha').attr('src','imagenes/captcha/captcha?token=' + Date.now());
				});					
				
				$.backstretch("imagenes/background/background.jpg");
				$('#<?php print($lcKeyMaskForgotFor); ?> input[type="text"]').on('focus', function() {
					$(this).removeClass('input-error');
				});
				
				$('#<?php print($lcKeyMaskForgotFor); ?>').on('submit', function(e) {
					$(this).find('input[type="text"]').each(function(){
						if( $(this).val() == "" ) {
							e.preventDefault();
							$(this).addClass('input-error');
						}
						else {
							$(this).removeClass('input-error');
						}
					});
				});

				$("#<?php print($lcKeyMaskUserName); ?>").focus();

				sessionStorage.setItem('userhcweb', '');

			});
		</script>
	</body>
</html>