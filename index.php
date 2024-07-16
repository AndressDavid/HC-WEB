<?php
	require_once (__DIR__ .'/nucleo/publico/constantes.php');
	require_once (__DIR__ .'/nucleo/controlador/class.AplicacionFunciones.php');

	$laAccessWan = [
		'hcwe.shaio.org',
		'186.115.216.244',
	];
	$llAccessWan = in_array(trim(strtolower($_SESSION[HCW_NAME]->getServerName())), $laAccessWan);

	$lcMensaje='';
	$lcKeyMaskLoginFor = strtolower('0'.md5('LOGINFOR-'.date('YmdH')).'x');
	$lcKeyMaskUserName = strtolower('1'.md5('PASSWORD-'.date('YmdH')).'y');
	$lcKeyMaskPassword = strtolower('2'.md5('USERNAME-'.date('YmdH')).'z');

	$lcBrowser = NUCLEO\AplicacionFunciones::getBrowserName(isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'*');
	$laBrowserCompiatibleNames = ['Chrome', 'Edge', 'Firefox', 'Safari'];
	$laBrowserCompiatible = [
								['icon'=>'fa-chrome', 'name'=>'Chrome'],
								['icon'=>'fa-edge', 'name'=>'Edge'],
								['icon'=>'fa-firefox', 'name'=>'Firefox' ],
								['icon'=>'fa-safari', 'name'=>'Safari']
							];
	if($llAccessWan==false){
		if (isset($_SESSION[HCW_NAME])){

			// Acciones por metodo
			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				if(isset($_POST[$lcKeyMaskUserName]) && isset($_POST[$lcKeyMaskPassword])){
					if ($_SESSION[HCW_NAME]->oUsuario->iniciarSesion($_POST[$lcKeyMaskUserName],$_POST[$lcKeyMaskPassword])==true){
						header('Location: principal');
						die();
					}else{
						$lcMensaje='Las credenciales de usuario proporcionadas no son correctas. '.$_SESSION[HCW_NAME]->oUsuario->getError();
					}
				}else{

					if(isset($_POST['token']) && isset($_POST['user']) && isset($_POST['modulo'])){
						if($_SESSION[HCW_NAME]->oUsuario->iniciarSesion($_POST['user'], $_POST['token'], true)==true){
							if(isset($_POST['tipusu']) && isset($_POST['codesp'])){
								if($_SESSION[HCW_NAME]->oUsuario->validarCambioEspecialidad($_POST['tipusu'], $_POST['codesp'])){
									$_SESSION[HCW_NAME]->oUsuario->cargar($_POST['user'], $_POST['tipusu'], $_POST['codesp']);
								}
							}
							$lcModulo = $_POST['modulo'];
							$lcDatos = trim($_POST['datos']);
							if(!empty($lcDatos)){
								try{
									$laDatos = json_decode(utf8_encode(base64_decode($lcDatos)),true);
									if(is_array($laDatos)){
										foreach($laDatos as $lcClave=>$lcValor){
											$_SESSION[HCW_DATA][$lcClave]=$lcValor;
										}
									} else {
										$lcMsgErr =
											' | RECIBIR DATOS DE HC'. PHP_EOL .
											(isset($_SESSION[HCW_NAME])?"\t- Usuario: ".$_SESSION[HCW_NAME]->oUsuario->getUsuario(). PHP_EOL :'').
											"\t- POST: " . str_replace("\r", '', str_replace("\n", '', var_export($_POST, true)));
										error_log($lcMsgErr);
									}
								}catch(Exception $loError){
									$lcMsgErr =
										' | RECIBIR DATOS DE HC'. PHP_EOL .
										(isset($_SESSION[HCW_NAME])?"\t- Usuario: ".$_SESSION[HCW_NAME]->oUsuario->getUsuario(). PHP_EOL :'').
										"\t- POST: " . str_replace("\r", '', str_replace("\n", '', var_export($_POST, true))).
										"\t- Error: ".str_replace("\r", '', str_replace("\n", '', var_export($loError, true)));
									error_log($lcMsgErr);
									header('Location: '.$lcModulo);
									die();
								}
							}
							header('Location: '.$lcModulo);
							die();
						}else{
							$lcMensaje='La credenciales de usuario proporcionadas no son correctas. '.$_SESSION[HCW_NAME]->oUsuario->getError();
						}

					} else {
						$lcMensaje='La credenciales y nombres de campo de usuario proporcionadas no son correctas. '.$_SESSION[HCW_NAME]->oUsuario->getError();
					}
				}
			}
			if($_SERVER['REQUEST_METHOD'] == 'GET') {
				if(count($_GET)>0){
					$_SESSION[HCW_NAME]->oUsuario->cerrarSesion();
					$lcMensaje='Sesión cerrada';
				}
			}

			// Control sesión previa
			if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
				header('Location: principal');
				die();
			}
		}
	}else{
		header("Location: error?423", TRUE, 301);
		exit();
	}

	$lcEntorno = $goDb->obtenerEntorno();
?><!doctype html>
<html lang="en">
	<head>
		<!-- Cabecera -->
		<?php include("nucleo/publico/head.php"); ?>

		<!-- Codigop header -->
		<link rel="stylesheet" href="vista-autenticacion/css/style.css">

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
							<p>Historia Clínica Web<br/><small class="font-weight-bolder"><?= $_SESSION[HCW_NAME]->getServerName() ?></small></p>
						</div>
						<div class="form-top-right">
							<img src="publico-imagenes/logo/main-logo-red.svg" alt="Logo"/>
						</div>
					</div>
					<div class="form-bottom p-3">
						<?php if(in_array($lcBrowser,$laBrowserCompiatibleNames)==true){ ?>
						<p><small>Para ingresar a la plataforma escriba sus credenciales del aplicativo <b>&quot;Historia Cl&iacute;nica&quot;</b></small></p>
						<form role="form" class="login-form" id="<?php print($lcKeyMaskLoginFor); ?>" name="<?php print($lcKeyMaskLoginFor); ?>" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" autocomplete="off">
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="sr-only" for="<?php print($lcKeyMaskUserName); ?>">Usuario</label>
										<input type="text" autocorrect="off" autocapitalize="off" autocomplete="<?php print($lcKeyMaskUserName); ?>" spellcheck="false" id="<?php print($lcKeyMaskUserName); ?>" name="<?php print($lcKeyMaskUserName); ?>" placeholder="Usuario HC" class="form-username form-control form-control-lg" maxlength="10">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="sr-only" for="<?php print($lcKeyMaskPassword); ?>">Contraseña</label>
										<input type="text" autocorrect="off" autocapitalize="off" autocomplete="<?php print($lcKeyMaskPassword); ?>" spellcheck="false" id="<?php print($lcKeyMaskPassword); ?>" name="<?php print($lcKeyMaskPassword); ?>" placeholder="Contrase&ntilde;a" class="form-password form-control form-control-lg">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col">
									<button type="submit" class="btn btn-danger w-100"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Entrar</button>
								</div>
							</div>
						</form>
						<div class="row">
							<div class="col">
								<div id="report-status" name="report-status"><?php print(!empty($lcMensaje)?sprintf('<hr/><div class="alert alert-danger" role="alert">%s</div>',$lcMensaje):''); ?></div>
							</div>
						</div>
						<div class="row text-center mt-2 mb-2">
							<div class="col">
								<a href="vista-autenticacion/forgot"style="color: #000000; font-weight: bold;">¿Olvidó la contraseña?</a>
							</div>
						</div>
						<div class="row text-center">
							<div class="col-12 mb-2"><b><?php echo date("Y"); ?> &copy; Fundación Clínica Shaio</b> Grupo de Desarrollo, TI. 2018 - <?php print(date('Y')); ?></div>
							<div class="col-12"><span id="siteseal"><script async type="text/javascript" src="https://seal.godaddy.com/getSeal?sealID=VqcA88PktUeEAocM1X63z3gfPudrztRjcXnBeG6bPuwOGeotVClyaoFxnRs2"></script></span></div>
						</div>
						<div class="row">
							<div class="col">
								<!-- <button id="btnSave" name="btnSave" class="btn btn-light w-100">Instalar como App</button> -->
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

		<!-- Pie de pagina -->
		<?php include("nucleo/publico/footer.php"); ?>

		<!-- Opciones para el login -->
		<script src="vista-autenticacion/js/jquery.backstretch.js"></script>
		<script>
			$.ajaxSetup({
				cache: false
			});

			$(document).ready(function() {
				$.backstretch("nucleo/publico/imagenes/background/<?php print($lcEntorno=='desarrollo'?'login-desarrollo.jpg':'login.jpg'); ?>");
				$('#<?php print($lcKeyMaskLoginFor); ?> input[type="text"], #<?php print($lcKeyMaskLoginFor); ?> input[type="password"], #<?php print($lcKeyMaskLoginFor); ?> textarea').on('focus', function() {
					$(this).removeClass('input-error');
				});
				$('#<?php print($lcKeyMaskLoginFor); ?>').on('submit', function(e) {
					$(this).find('input[type="text"], input[type="password"], textarea').each(function(){
						if( $(this).val() == "" ) {
							e.preventDefault();
							$(this).addClass('input-error');
						}
						else {
							$(this).removeClass('input-error');
						}
					});
				});

				$("#<?php print($lcKeyMaskPassword); ?>").prop("type","password");
				$("#<?php print($lcKeyMaskUserName); ?>").focus();

				sessionStorage.setItem('userhcweb', '');

			});
		</script>
	</body>
</html>