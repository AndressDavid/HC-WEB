<?php
	require_once __DIR__ . '/../../controlador/class.MiPresFunciones.php';
	NUCLEO\MiPresFunciones::getUsuarioPermisos();
	$laPermisos = NUCLEO\MiPresFunciones::$aPermisos;
	$lbPuedeUsarPUT = in_array('usarput',$laPermisos);


	if (isset($_GET['q'])) {
		$lcPagina = __DIR__ .'/'.trim(strtolower($_GET['q'])).".php";
		include($lcPagina);

	} else {



		$lcClassLabel = 'col-sm-6 col-lg-4';
		$lcClassCtrol = 'col-sm-6 col-lg-4';
		$llPagina=false;
		$laPrmPags = include( __DIR__ . '/paginas.php');

		if (isset($_GET['p'])) {
			$lcPag = trim($_GET['p']);
			if (in_array($lcPag, array_keys($laPrmPags['paginas']))) {
				$laPrmPag = $laPrmPags['paginas'][$lcPag];
				$llPagina = true;
			} elseif (in_array($lcPag, array_keys($laPrmPags['condensados']))) {
				$laPrmPag = $laPrmPags['condensados'][$lcPag];
				$llPagina = true;
			}

			if ($llPagina) {

				// ToolBar con vínculo a cada página
				$lcActive = 'info';
?>
		<small>
		<nav class="navbar navbar-expand-lg navbar-light bg-light">
			<a class="navbar-brand" href="#"> </a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarMenuMiPres" aria-controls="navbarMenuMiPres" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarMenuMiPres">
				<ul class="navbar-nav mr-auto">
					<li class="nav-item dropdown" style="padding-right:2px; padding-bottom:1px;">
						<!-- <a class="nav-link dropdown-toggle btn btn-sm btn-<?php echo $lcActive; ?>" style="color:#fff;" href="#" id="dropdownMenuButtonPags" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">MiPres</a>  -->
						<!-- <div class="dropdown-menu" aria-labelledby="dropdownMenuButtonPags">  -->

<?php
				foreach ($laPrmPags['paginas'] as $lcClave=>$lcValor){
					if ($lcClave==$lcPag) {
						$lcActiveBtn = 'active';
						$lcActiveLnk = 'outline-'.$lcActive;
						$lcActiveStl = '';
					} else {
						$lcActiveBtn = '';
						$lcActiveLnk = $lcActive.' btnToolBarDir';
						$lcActiveStl = 'style="color:#fff;"';
					}
					echo "<li class=\"nav-item {$lcActiveBtn}\" style=\"padding-right:2px; padding-bottom:1px;\">
							<a class=\"nav-link btn btn-sm btn-{$lcActiveLnk}\" href=\"#\" {$lcActiveStl} pagina=\"modulo-mipres&p={$lcClave}\">
							<span class=\"fas {$lcValor['icono']} align-self-center mr-1\"></span> {$lcValor['titulo']}</a></li>";
/*
					echo "<a class=\"dropdown-item btnToolBarDir\" id=\"pag{$lcClave}\" href=\"#\" pagina=\"modulo-mipres&p={$lcClave}\">
							<i class=\"fas {$lcValor['icono']}\" aria-hidden=\"true\"></i> {$lcValor['titulo']}</a>";
*/
				}

				// Condensados
				if($lbPuedeUsarPUT){
?>
						<!-- </div> -->
					</li>

					<li class="nav-item dropdown" style="padding-right:2px; padding-bottom:1px;">
						<a class="nav-link dropdown-toggle btn btn-sm btn-<?php echo $lcActive; ?>" style="color:#fff;" href="#" id="dropdownMenuButtonCondns" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Condensados</a>
						<div class="dropdown-menu" aria-labelledby="dropdownMenuButtonCondns">
<?php
					foreach ($laPrmPags['condensados'] as $lcClave=>$laLink){
						echo '<a class="dropdown-item btnToolBarDir" id="'.$lcClave.'" href="#" pagina="modulo-mipres&p='.$lcClave.'">
								<i class="fas '.$laLink['icono'].'" aria-hidden="true"></i> '.$laLink['titulo'].'</a>';
					}
?>
						</div>
					</li>
<?php
				}

				// Links
?>
					<li class="nav-item dropdown" style="padding-right:2px; padding-bottom:1px;">
						<a class="nav-link dropdown-toggle btn btn-sm btn-<?php echo $lcActive; ?>" style="color:#fff;" href="#" id="dropdownMenuButtonLinks" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Vínculos</a>
						<div class="dropdown-menu" aria-labelledby="dropdownMenuButtonLinks">
<?php
					foreach ($laPrmPags['links'] as $lcClave=>$laLink){
						if ($laLink['enabled'] && ( $laLink['permiso']=='' || in_array($laLink['permiso'], $laPermisos) )) {
							echo '<a class="dropdown-item" id="'.$lcClave.'" href="'.$laLink['href'].(empty($laLink['target']) ? '' : '" target="'.$laLink['target']).'">
									<i class="fas '.$laLink['icon'].'" aria-hidden="true"></i> '.$laLink['caption'].'</a>';
						}
					}
?>
						</div>
					</li>
				</ul>
			</div>
		</nav>
		</small>
<?php
			}
		}

		if (!$llPagina) {
?>

		<div class="container-fluid">
			<div class="card mt-3">
				<div class="card-header">
					<div class="media">
						<i class="fas fa-notes-medical align-self-center mr-3"></i>
						<div class="media-body"><h5>Módulos MiPRES</h5></div>
					</div>
				</div>
				<div class="card-body" id="hcwTabContent">
					<div class="tab-pane fade show active" id="alerta" role="tabpanel" aria-labelledby="alerta">
						<div class="list-group">
				<?php
					// Crear opciones de la página principal
					foreach ($laPrmPags['paginas'] as $lcClave=>$lcValor){
						echo '<a href="modulo-mipres&p='.$lcClave.'" class="list-group-item list-group-item-action border-0">'
								.'<div class="media">'
									.'<i class="fas '.$lcValor['icono'].' align-self-center mr-3"></i>'
									.'<div class="media-body">'
										.'<b>'.$lcValor['titulo'].'</b><br/>'.$lcValor['descrp'].'</div></div></a>';
					}
				?>
						</div>
					</div>
				</div>
			</div>
		</div>
<?php
		} else {
?>
	<!-- pag_enc.php -->
		<div class="container-fluid">
			<div id="divCard" class="card mt-3">
				<div class="card-header">
					<div class="row">
						<div class="col-11 col-lg-11 col-md-8 col-sm-8">
							<div class="media">
								<i class="fas <?php echo $laPrmPag['icono']; ?> fa-lg align-self-center mr-4"></i>
								<div class="media-body"><h3 id="hTituloPag"><?php echo $laPrmPag['titulo']; ?></h3></div>
							</div>
						</div>
						<div class="col-1 col-lg-1 col-md-2 col-sm-2">
							<button id="btnLimpiar" type="button" class="btn btn-secondary btn-sm">Limpiar</button>
						</div>
						<div class="col-11 col-lg-11 col-md-8 col-sm-12">
							<hr />
						</div>
					</div>

	<?php
			if (!in_array($lcPag, array_keys($laPrmPags['condensados']))) {
	?>

					<!-- Controles de consulta -->
					<div class="row">
						<label for="selConsulta" class="<?php echo $lcClassLabel; ?>"><b>Tipo de Consulta</b></label>
						<select class="form-control form-control-sm <?php echo $lcClassCtrol; ?>" name="selConsulta" id="selConsulta" placeholder="" disabled></select>
					</div>
					<div class="row" id="divGET">
						<div class="container-fluid">
							<div class="row">
								<label for="txtFecha" class="<?php echo $lcClassLabel; ?>"><b>Fecha</b></label>
								<div class="input-group input-group-sm date <?php echo $lcClassCtrol; ?>" style="padding: 0px;">
									<div class="input-group-prepend" style="height: 31px;">
										<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
									</div>
								  <input type="text" class="form-control clsConsulta" name="txtFecha" id="txtFecha" required="required" value="<?php print(date("Y-m-d")); ?>" disabled>
								</div>
							</div>
							<div class="row">
								<label for="selTipoDoc" class="<?php echo $lcClassLabel; ?>"><b>Tipo documento</b></label>
								<select class="form-control form-control-sm <?php echo $lcClassCtrol; ?> clsConsulta" name="selTipoDoc" id="selTipoDoc" disabled></select>
							</div>
							<div class="row">
								<label for="txtNumDoc" class="<?php echo $lcClassLabel; ?>"><b>Número documento</b></label>
								<input type="text" class="form-control form-control-sm <?php echo $lcClassCtrol; ?> clsConsulta" name="txtNumDoc" id="txtNumDoc" placeholder="" disabled>
							</div>
							<div class="row">
								<label for="txtNumPres" class="<?php echo $lcClassLabel; ?>"><b>No Prescripción</b></label>
								<input type="text" class="form-control form-control-sm <?php echo $lcClassCtrol; ?> clsConsulta" name="txtNumPres" id="txtNumPres" placeholder="" disabled>
							</div>
					<?php if ($laPrmPag['tipo']=='disprv') { ?>
							<div class="row">
								<label for="txtIdAnular" class="<?php echo $lcClassLabel; ?>"><b>ID <?php echo $laPrmPag['titulo']; ?></b></label>
								<input type="text" class="form-control form-control-sm <?php echo $lcClassCtrol; ?> clsConsulta" name="txtIdAnular" id="txtIdAnular" placeholder="" disabled>
							</div>
					<?php } ?>
							<div class="row">
								<div class="col-lg-1 col-md-2 col-sm-12 col-12 pb-2">
									<button id="btnConsultar" type="button" class="btn btn-secondary btn-sm" accesskey="J">E<u>j</u>ecutar</button>
								</div>
							</div>
						</div>
					</div>

		<?php } ?>

					<!-- Controles de envío de datos -->
					<div class="row" id="divPUT" style="display: none">
						<div class="container-fluid" id="cntPUT">
						</div>
						<div class="container-fluid" id="divErrPUT">
						</div>
						<div class="col-lg-1 col-md-2 col-sm-12 col-12 pb-2">
							<button id="btnEnviar" type="button" class="btn btn-secondary btn-sm" accesskey="V">En<u>v</u>iar</button>
						</div>
					</div>

					<div class="row">
						<div class="col">
							<div id="divInfo" class="container-fluid"></div>
						</div>
					</div>

	<!-- pag_pie.php -->
				</div>
				<div id ="divResultado" class="card-body" style="display: none;">
				<div class="container-fluid">
					<div class="row">
						<div id="infoConsulta" class="col-12 col-lg-12 col-md-12 col-sm-12"></div>
					</div>
					<hr>
					<div class="row">
						<div class="col-12 col-lg-12 col-md-12 col-sm-12">
							<div id="divIconoEspera" class="fa-3x" style="display: none;">
								<i class="fas fa-circle-notch fa-xs fa-spin" style="color:#f00"></i>
							</div>
							<div id="divResFinal" class="accordion" style="display: none;"></div>
						</div>
					</div>
				</div>
				</div>

				<div class="card-footer text-muted">
					<p><span id="spnNumReg"><?php echo $laPrmPag['titulo']; ?></span></p>
				</div>
			</div>
		</div>



		<link rel="stylesheet" type="text/css" media="screen" href="publico-complementos/datatables/1.10.18/DataTables/css/jquery.dataTables.min.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="publico-complementos/datatables/1.10.18/Buttons/css/buttons.bootstrap4.min.css" />

		<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.min.js"></script>
		<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/localization/messages_es.min.js"></script>
		<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
		<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
		<script type="text/javascript" src="publico-complementos/datatables/1.10.18/DataTables/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="publico-complementos/datatables/1.10.18/DataTables/js/dataTables.bootstrap4.min.js"></script>
		<script type="text/javascript" src="publico-complementos/datatables/1.10.18/Buttons/js/dataTables.buttons.min.js"></script>
		<script type="text/javascript" src="publico-complementos/datatables/1.10.18/Buttons/js/buttons.html5.min.js"></script>
		<script type="text/javascript" src="publico-complementos/datatables/1.10.18/Buttons/js/buttons.bootstrap4.min.js"></script>

		<script type="text/javascript">
		<?php
			echo "var lcClassLabel = '$lcClassLabel',\n	lcClassCtrol = '$lcClassCtrol';";
		?>
		</script>

		<script src="vista-mipres/js/script.js"></script>
		<script src="vista-mipres/js/<?php echo $lcPag; ?>_script.js"></script>

<?php
		}
	}
?>
