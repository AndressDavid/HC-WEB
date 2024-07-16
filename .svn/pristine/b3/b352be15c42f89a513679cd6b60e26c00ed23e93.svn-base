<?php
	include __DIR__ . '/../comun/modalTrasladosPacientes.php';
?>

<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-8 col-lg-11">
					<h5>Pacientes Hospitalizados</h5>
				</div>
			</div>
			<form id="frmFiltros">
				<div id="divFiltro" class="row">
					<div class="col-xs-12 col-sm-6 col-md-6 col-lg-2 col-xl-2">
						<label for="txtIngreso"><b>Ingreso</b></label>
						<input id="txtIngreso" type="number" class="form-control form-control-sm" name="txtIngreso" placeholder="Número de ingreso" value="">
					</div>
					<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
						<label for="selSeccion" class="control-label"><b>Sección</b></label>
						<select id="selSeccion" class="form-control form-control-sm"></select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-6 col-lg-2 col-xl-2">
						<label for="btnBusca" class="control-label"> &nbsp;  &nbsp;  &nbsp; &nbsp;  &nbsp;  &nbsp; </label>
						<button id="btnBuscar" type="button" class="form-control-sm btn btn-secondary btn-sm w-100" accesskey="B"><u>B</u>uscar</button>
					</div>
					<div class="col-xs-12 col-sm-6 col-md-6 col-lg-2 col-xl-2">
						<label for="btnLimpia" class="control-label"> &nbsp;  &nbsp;  &nbsp; &nbsp;  &nbsp;  &nbsp; </label>
						<button id="btnLimpiar" type="button" class="form-control-sm btn btn-secondary btn-sm w-100" accesskey="L"><u>L</u>impiar</button>
					</div>
				</div>
			</form>
		</div>

		<div class="card-body">
			<small>
				<table id="tblPacientes"></table>
			</small>
		</div>
	</div>
</div>

<div class="modal fade" id="modalPreview" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="modalPreviewLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered"> <!-- modal-xl modal-lg modal-sm modal-dialog-centered modal-dialog-scrollable -->
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalPreviewLabel">Previsualización</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="modalPreviewBody">
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />

<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="vista-comun/js/secciones.js"></script>
<script type="text/javascript" src="vista-historiaclinica/js/validarpacientehc.js"></script>
<script type="text/javascript" src="vista-historiaclinica/js/menuOpciones.js"></script>
<script type="text/javascript" src="vista-comun/js/modalVistaPrevia.js"></script>
<script type="text/javascript" src="vista-comun/js/modalRespCup.js"></script>
<script type="text/javascript" src="vista-comun/js/tiposGeneros.js"></script>
<script type="text/javascript" src="vista-hc-hospitalizado/js/script.js"></script>

<?php
	$lbObtenerVias=true;
	include __DIR__ . '/../comun/js/usuario.js.php';
?>
