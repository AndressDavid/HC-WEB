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
			<h5>Consulta PROA</h5>
			<form id="frmFiltros" class="small">
				<div id="divFiltro" class="row">
					<div class="col-md-2 pb-2">
						<label for="Ingreso">Ingreso</label>
						<input type="number" id="Ingreso" name="Ingreso" class="form-control form-control-sm" min="0"
							max="99999999" placeholder="" value="">
					</div>

					<div class="col-md-3 col-lg-2 pb-2">
						<label for="FechaDesde">Fecha Desde</label>
						<div class="input-group date" id="grpFechaDesde">
							<div class="input-group-prepend">
								<span class="input-group-addon input-group-text"><i
										class="fas fa-calendar-alt"></i></span>
							</div>
							<input type="text" id="FechaDesde" name="FechaDesde" class="form-control form-control-sm"
								value="" disabled>
						</div>
					</div>
					<div class="col-md-3 col-lg-2 pb-2">
						<label for="FechaHasta">Fecha Hasta</label>
						<div class="input-group date" id="grpFechaHasta">
							<div class="input-group-prepend">
								<span class="input-group-addon input-group-text"><i
										class="fas fa-calendar-alt"></i></span>
							</div>
							<input type="text" id="FechaHasta" name="FechaHasta" class="form-control form-control-sm"
								value="" disabled></span>
						</div>
					</div>

					<div class="col-md-3 col-lg-2 pb-2">
						<label for="btnBuscar">&nbsp;</label>
						<button id="btnBuscar" type="button"
							class="form-control form-control-sm btn btn-secondary btn-sm" accesskey="B"
							disabled><u>B</u>uscar</button>
					</div>
					<div class="col-md-3 col-lg-2 pb-2">
						<label for="btnLimpiar">&nbsp;</label>
						<button id="btnLimpiar" type="button"
							class="form-control form-control-sm btn btn-secondary btn-sm"
							accesskey="L"><u>L</u>impiar</button>
					</div>

					<div class="input-group">
						<div class="col-lg-4 col-md-4 col-sm-12 col-12">
							<label id="lblMedicamentoInf" for="cMedicamentoInf">Medicamento</label>
						</div>
						<div class="col-12">
							<input type="text"
								class="form-control form-control-sm font-weight-bold ignore col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12"
								name="cMedicamentoInf" id="cMedicamentoInf" autocomplete="off" disabled>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
						<div class="input-group">
							<input type="text"
								class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-1 col-xl-1"
								name="cCodigoMedicamentoInf" id="cCodigoMedicamentoInf" readonly="readonly">
							<input type="text"
								class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-11 col-xl-11"
								name="cDescripcionMedicamentoInf" id="cDescripcionMedicamentoInf" readonly="readonly">
						</div>
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
					<div id="divFiltroInfo"></div>
				</div>
			</div>
		</div>
		<div class="card-body" id="divInfectologia">
		</div>
	</div>

	<div class="card-footer text-muted fixed-bottom bg-light small">
		<div class="row justify-content-between">
			<div class="col-auto">
				<button id="btnLibroHC" type="button" class="btn btn-primary btn-sm" data-toggle="tooltip"
					data-placement="top" title="Libro HC" disabled><i class="fas fa-book-medical"></i></button>
				<button id="btnLibroExcel" type="button" class="btn btn-success btn-sm" data-placement="top"
					title="Descargar Excel" onclick="ExportToExcel('xlsx', 'infconsulta')" disabled><i
						class="fas fa-file-excel"></i></button>
			</div>
		</div>
	</div>

</div>

<link rel="stylesheet" type="text/css" href="vista-comun/css/modalVistaPrevia.css" />

<script type="text/javascript"
	src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript"
	src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.min.js"></script>
<script type="text/javascript"
	src="publico-complementos/jquery-validation/1.17.0/localization/messages_es.min.js"></script>
<script type="text/javascript"
	src="publico-complementos/bootstrap-autocomplete/2.3.7-dist/bootstrap-autocomplete.min.js"></script>
<script type="text/javascript" src="vista-comun/js/medicamentos.js"></script>
<script type="text/javascript" src="vista-infconsulta/js/script.js"></script>
<script type="text/javascript" src="vista-comun/js/cabecera.js"></script>
<script type="text/javascript" src="vista-comun/js/comun.js"></script>
<script type="text/javascript" src="publico-complementos/xlsx/xlsx.full.min.js"></script>
<script type="text/javascript">
	var gnIngreso = '<?= $lnIngreso ?>';
</script>