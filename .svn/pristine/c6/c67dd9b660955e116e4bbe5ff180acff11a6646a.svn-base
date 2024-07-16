<?php
	require_once __DIR__ . '/../../controlador/class.Db.php';

	$lnIngreso = '';
	if (isset($_SESSION[HCW_DATA])) {
		if (isset($_SESSION[HCW_DATA]['ingreso'])) {
			$lnIngreso = $_SESSION[HCW_DATA]['ingreso'];
		}
		unset($_SESSION[HCW_DATA]);
	}
	if (empty($lnIngreso)) {
		$lnIngreso = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['ingreso'] ?? '');
	}

?>
<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<h5>Consulta registro aval HC</h5>
			<form id="frmFiltros" class="small">
				<div id="divFiltro" class="row">
					<div class="col-md-2 pb-2">
						<label for="Ingreso">Ingreso</label>
						<input type="number" id="Ingreso" name="Ingreso"  class="form-control form-control-sm"min="0" max="99999999" placeholder="" value="">
					</div>
					
					<div class="col-md-3 col-lg-2 pb-2">
						<label for="btnBuscar">&nbsp;</label>
						<button id="btnBuscar" type="button" class="form-control form-control-sm btn btn-secondary btn-sm" accesskey="B" disabled><u>B</u>uscar</button>
					</div>
					<div class="col-md-3 col-lg-2 pb-2">
						<label for="btnLimpiar">&nbsp;</label>
						<button id="btnLimpiar" type="button" class="form-control form-control-sm btn btn-secondary btn-sm" accesskey="L"><u>L</u>impiar</button>
					</div>
				</div>
			</form>
			<hr class="mt-1 mb-1">
			<div class="row">
				
				<div class="col">
					<div id="divIngresoInfo" class="small"></div>
				</div>
			</div>
			<div class="row">
				<div class="col">
					<div id="divFiltroInfo">
					
					</div>
				</div>
			</div>
		</div>
		<div class="card-body small">
			<table id="tblMedicoAval"></table>
			<div class="row" id="divAval">
			</div>
		</div>
	</div>

	<div class="card-footer text-muted fixed-bottom bg-light small">
		<div class="row justify-content-between">
			<div class="col-auto">
				<button id="btnLibroHC" type="button" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Libro HC" disabled><i class="fas fa-book-medical"></i></button>
			</div>
		</div>
	</div>

</div>

<link rel="stylesheet" type="text/css" href="vista-comun/css/modalVistaPrevia.css" />

<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/localization/messages_es.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/1.18.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/1.18.3-dist/locale/bootstrap-table-es-ES.min.js"></script>

<script type="text/javascript" src="vista-consultaaval/js/script.js"></script>
<script type="text/javascript" src="vista-comun/js/cabecera.js"></script>

<script type="text/javascript">
	var gnIngreso='<?= $lnIngreso ?>';
</script>