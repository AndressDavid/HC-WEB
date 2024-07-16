<?php

	require_once __DIR__ .'/../../publico/constantes.php';

	$lnContinuar = 0;
	if (isset($_SESSION[HCW_NAME])){
		$laTiposUsuariosAutorizados = [1, 7,9,91];
		$lnTipoUsuario = $_SESSION[HCW_NAME]->oUsuario->getTipoUsuario();
		if(in_array($lnTipoUsuario, $laTiposUsuariosAutorizados)){
			$lnContinuar = 1;
		}else{
			echo 'El tipo de usuario no esta autorizado para acceder';
		}
	}
	if($lnContinuar == 1 ){
		if(isset($_GET['target'])){
			$lcPagina = __DIR__ .'/'.trim(strtolower($_GET['target'])).".php";
			include($lcPagina);
		}else{
?>
<div class="container-fluid">
	<div class="modal-dialog modal-md" id="divUbicacion"role="document">
		<div class="modal-content">
			<div class="modal-header">
				<div class="row">
					<div class="col-sm-12 col-md-12">
						<h4 class="modal-title text-white bg-dark text-center">Ubicación / modulo</h4>
					</div>
					<div class="col-2" title="Logo modulo"><img class="rounded img-responsive "
							 width="50px"  src="nucleo/publico/imagenes/librohc/32x32/jpg/Hospital.jpg">
					</div>
					<div class="col-10">
						<h6 class="font-weight-bold">Ubicación / Modulo</h6>
						<p class="">Modulo desde donde se hace el llamado a digiturno</p>
					</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<label class="control-label" for="txtUbicacion" >Ubicación</label>
						<select class="form-control form-control-sm" id="cmbUbicacion"  name="cmbUbicacion" type="text" tabindex="1">
						</select>
					</div>
				</div>
				<div class="container">
					<label for="txtUbicacion">Modulos</label>
					<div class="row border border-secondary">
						<div class="col-6">
							<div class="col-12 text-center" title="Logo triage A"><img class="rounded
								img-responsive" width="50px"  src="nucleo/publico/imagenes/librohc/32x32/jpg/PatientData.jpg">
								<label class="col-12 text-center" for="txtUbicacion" tabindex="2">TRIAGE A</label>
							</div>
						</div>
						<div class="col-6">
							<div class="col-12 text-center" title="Logo triage B"><img class="rounded
								img-responsive" width="50px"  src="nucleo/publico/imagenes/librohc/32x32/jpg/PatientData.jpg">
								<label class=" col-12 text-center" for="txtUbicacion" tabindex="3">TRIAGE B</label>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">

						<label for="txtUbicacion">Tipo</label>
						<select id="cmbUbicacion" class="form-control form-control-sm" tabindex="4">
							<option selected>POS</option>
							<option>PREPAGADA</option>
						</select>
					</div>
				</div>
				<div class="row">
					<div class="col-12 text-center">
						<label>(Usuario Tipo <?php echo $lnTipoUsuario?>)</label>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div class="d-flex row justify-content-between col-12">
					<div class="col-6">
							<button id="btnAceptar" type="button" class="btn btn-secondary" tabindex="5" accesskey="A"><u><b>A</b></u>ceptar</button>
					</div>
					<div class="col-6">
							<button id="" type="button" class="btn btn-secondary" tabindex="6" accesskey="C"><u><b>C</b></u>ancelar</button>
					</div>
				</div>
			</div>
		</div>
	<!--/div-->
</div>
<link rel="stylesheet" type="text/css" href="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />
<script type="text/javascript" src="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
<script type="text/javascript" src="vista-triage/js/script.js"></script>
<script type="text/javascript" src="vista-comun/js/comun.js"></script>
<?php
		}
	}
?>