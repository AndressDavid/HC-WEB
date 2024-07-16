<?php
	require_once (__DIR__ .'/nucleo/publico/constantes.php');
	require_once (__DIR__ .'/nucleo/vista/autenticacion/validacion.php');
	require_once (__DIR__ . '/nucleo/controlador/class.Auditoria.php');
	require_once (__DIR__ . '/nucleo/controlador/class.ApiAutenticacion.php');

	$llMenuModulo=false;
	$lcMenuCambioPerfil='';
	$lcMenuModuloCargado='MENU';
	(new ApiAutenticacion())->extenderTiempoToken();

	if(isset($_GET)){
		if(count($_GET)>0){
			$laMenuModulo=array_keys($_GET);

			if(isset($_GET['cambioTipo']) && isset($_GET['cambioEspecialidad'])){
				$lnTipoUsuario = intval($_SESSION[HCW_NAME]->oUsuario->desencriptar($_GET['cambioTipo']));
				$lcEspecialidad = $_SESSION[HCW_NAME]->oUsuario->desencriptar($_GET['cambioEspecialidad']);

				if($_SESSION[HCW_NAME]->oUsuario->validarCambioEspecialidad($lnTipoUsuario, $lcEspecialidad)==true){
					if($_SESSION[HCW_NAME]->oUsuario->cargar($_SESSION[HCW_NAME]->oUsuario->getUsuario(), $lnTipoUsuario, $lcEspecialidad)==true){
						$lcMenuCambioPerfil = sprintf('Se realizo el cambio la especialidad. Se cargo el modo <b>%s - %s</b>',$_SESSION[HCW_NAME]->oUsuario->getTipoUsuario(true)->cNombre,$_SESSION[HCW_NAME]->oUsuario->getEspecialidad(true)->cNombre);
					}
				}
			}
		}
	}
	$loUsuHCWeb=[
		'usuario'=>$_SESSION[HCW_NAME]->oUsuario->getUsuario(),
	//	'nombre'=>$_SESSION[HCW_NAME]->oUsuario->getNombres(),
	//	'apellido'=>$_SESSION[HCW_NAME]->oUsuario->getApellidos(),
	//	'registro'=>$_SESSION[HCW_NAME]->oUsuario->getRegistro(),
		'tipo'=>$_SESSION[HCW_NAME]->oUsuario->getTipoUsuario(false),
		'especialidad'=>$_SESSION[HCW_NAME]->oUsuario->getEspecialidad(false),
	];

	$loWeb=[
		'coockie'=>base64_encode('coockie=/x'.$_SESSION['token'].'y/')
	];

	// Para cambio de contraseña
	$lcMensajeCambioContrasena = '';
	if ($_SESSION[HCW_NAME]->oUsuario->getCambiarClave()==true){
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			$_SESSION[HCW_NAME]->oUsuario->fCambiarContrasena($_POST, $lcMensajeCambioContrasena);
		}
	}

?><!doctype html>
<html lang="es">
	<head>
		<!-- Cabecera -->
		<?php include('nucleo/publico/head.php'); ?>

		<!-- Para cambio de contraseña -->
		<?php if ($_SESSION[HCW_NAME]->oUsuario->getCambiarClave()==true){ ?><link rel="stylesheet" href="vista-autenticacion/css/style.css"><?php } ?>
	</head>
	<!-- para cambio de contraseña -->
	<?php if ($_SESSION[HCW_NAME]->oUsuario->getCambiarClave()==true){ ?>
	<body id="main-app-panel" name="main-app-panel" class="h-100">
		<?php include(__DIR__ .'/nucleo/vista/autenticacion/login-change-pass.php'); ?>
	</body>
	<?php } else { ?>
	<!-- menu normal -->
	<body id="main-app-panel" name="main-app-panel" class="h-100">

		<!-- menu -->
		<?php
			include(__DIR__ .'/nucleo/vista/menu/barra.php');

			if(isset($laMenuModulo)==true){
				foreach($laMenuModulo as $lcMenuModulo){
					$lcMenuModuloPath= __DIR__ . '/nucleo/vista/'.$lcMenuModulo.'/';
					if(is_dir($lcMenuModuloPath) && is_readable($lcMenuModuloPath)){
						if(is_file($lcMenuModuloPath.'/index.php')){
							if ($_SESSION[HCW_NAME]->oUsuario->validarOpcion($lcMenuModulo)==true){
								include($lcMenuModuloPath.'/index.php');
								$lcMenuModuloCargado = $lcMenuModulo;
								$llMenuModulo=true;
							}else{
		?>
		<div class="modal" id="modalAccesoDenegado" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-dialog-centered"" role="document">
				<div class="modal-content">
					<div class="modal-header bg-warning">
						<h1 class="modal-title"><i class="fas fa-th-list"></i> Acceso denegado</h1>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<p>Su perfil de usuario <b>No</b> cuenta con suficientes privilegios en la plataforma para ejecutar el modulo <b><?php print(strtoupper($lcMenuModulo)); ?></b></p>
						<hr>
						<p class="mb-0"><small>Si el problema persiste póngase en contacto con soporte t&eacute;cnico.</small></p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-warning" data-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
		</div>
		<script>$('#modalAccesoDenegado').modal('show');</script>
		<?php
								$llMenuModulo=true;
							}
						}
					}
				}
			}

			if($llMenuModulo==false){
				$lcEstilo = isset($_SESSION[HCW_NAME]->oEstiloOpcInicio) ? $_SESSION[HCW_NAME]->oEstiloOpcInicio : 'OPCIONES';
				$laOpciones = $_SESSION[HCW_NAME]->oUsuario->getOpcionesMenu();
				$llOpciones = false;
				if(is_array($laOpciones)){
					if(count($laOpciones)>0){
						$llOpciones=true;
						switch ($lcEstilo){
							case 'OPCIONES':
								include(__DIR__ .'/nucleo/vista/menu/opciones.php');
								break;
							case 'ICONOS':
								include(__DIR__ .'/nucleo/vista/menu/menuicon.php');
								break;
						}
					}
				}

				if($llOpciones==false){
		?>
		<div class="modal" id="modalControlPerfil" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="modal-title"><i class="fas fa-th-list"></i> Sin opciones</h1>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<p>Su perfil de usuario <b>No</b> cuenta con opci&oacute;n alguna para ejecutar en la plataforma.</p>
						<hr>
						<p class="mb-0"><small>Si el problema persiste póngase en contacto con soporte t&eacute;cnico.</small></p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
		</div>
		<script>$('#modalControlPerfil').modal('show')</script>
		<?php
				}
			}

			// -- Pie de pagina --
			include('nucleo/publico/footer.php');

		?>
		<!-- Html -->
		<footer class="mt-4 p-4 bg-light">
			<div class="row justify-content-center">
				<div class="col-sm-12 col-md-6 text-center text-md-left"><b><?php echo date('Y'); ?> &copy; Fundación Clínica Shaio</b><br/><small>Grupo de Desarrollo, TI. 2018 | IP Clíente <?php print(isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getIP():'Sin identificar'); ?> | HOST <?php if(isset($_SERVER['SERVER_NAME'])){ print($_SERVER['SERVER_NAME']); } else {print('Unkonow');} ?> | <?php print(strtoupper($lcMenuModuloCargado)); ?></small></div>
				<div class="col-sm-12 col-md-6 text-center text-md-right">soporte@shaio.org<br/>+57-1-5938210 ext 2173</div>
			</div>
		</footer>
		<script>
			let gnIngresoSmartRoom = <?= $_SESSION[HCW_NAME]->getIngresoSmartRoom() ?>;
			sessionStorage.setItem('userhcweb', <?php echo '\'' . base64_encode(json_encode($loUsuHCWeb)) . '\''; ?>,);
			sessionStorage.setItem("coockie", <?php  echo "'".$loWeb['coockie']."'"; ?>,);
		</script>
	</body>
	<?php } ?>
</html>