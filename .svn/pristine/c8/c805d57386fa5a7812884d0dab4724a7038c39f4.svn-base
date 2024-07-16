<div class="container-fluid">
	<div class="card mt-3">

		<div class="card-header">
			<div class="row">
				<div class="col">
					<h5>Consulta Pacientes Urgencias</h5>
				</div>
			</div>
			<form id="frmFiltros">
				<div id="divFiltro" class="row">
					<div class="col-xs-6 col-md-4 col-xl-2">
						<label for="txtIngreso"><b>Ingreso</b></label>
						<input id="txtIngreso" type="number" class="form-control form-control-sm" name="txtIngreso" placeholder="Número de ingreso" value="">
					</div>
					<div class="col-xs-6 col-md-8 col-xl-4">
						<label for="inpNumDoc" class="control-label"><b>Documento</b></label>
						<div class="input-group mb-3">
							<select id="selTipDoc" class="custom-select custom-select-sm col-6"></select>
							<input  id="txtNumDoc" type="text" class="form-control form-control-sm col-6" placeholder="Número documento" value="">
						</div>
					</div>
					<div class="col-xs-6 col-md-4 col-xl-2">
						<label for="lcFechaFormula" class="control-label"><b>Fecha</b></label>
						<div class="input-group date">
							<div class="input-group-prepend">
								<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
							</div>
							<input id="txtFecha" type="text" class="form-control form-control-sm" required="required" value="<?php print(date("Y-m-d")); ?>">
						</div>
					</div>
					<div class="col-xs-6 col-md-4 col-xl-2">
						<label for="selEstado" class="control-label"><b>Estado</b></label>
						<select id="selEstado" class="form-control form-control-sm"></select>
					</div>
					<div class="col-xs-6 col-md-2 col-xl-1">
						<label for="btnBusca" class="control-label"> &nbsp;  &nbsp;  &nbsp; &nbsp;  &nbsp;  &nbsp; </label>
						<button id="btnBuscar" type="button" class="form-control-sm btn btn-secondary btn-sm w-100" accesskey="B"><u>B</u>uscar</button>
					</div>
					<div class="col-xs-6 col-md-2 col-xl-1">
						<label for="btnLimpia" class="control-label"> &nbsp;  &nbsp;  &nbsp; &nbsp;  &nbsp;  &nbsp; </label>
						<button id="btnLimpiar" type="button" class="form-control-sm btn btn-secondary btn-sm w-100" accesskey="L"><u>L</u>impiar</button>
					</div>
				</div>
			</form>
		</div>

		<div class="card-body small">
			<table id="tblPacientes"></table>
			<hr>
			<div class="row">
				<div class="col-12">
					Valoraciones de TRIAGE pendientes por Ingreso administrativo
				</div>
				<div class="col-12" id="divPendientesEnTriage">
				</div>
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
<script type="text/javascript" src="vista-comun/js/tiposDocumentos.js"></script>
<script type="text/javascript" src="vista-historiaclinica/js/validarpacientehc.js"></script>
<script type="text/javascript" src="vista-historiaclinica/js/menuOpciones.js"></script>
<script type="text/javascript" src="vista-hc-urgencias/js/script.js"></script>
<script type="text/javascript" src="vista-comun/js/modalVistaPrevia.js"></script>
<script type="text/javascript" src="vista-comun/js/modalRespCup.js"></script>
<script type="text/javascript" src="vista-comun/js/tiposGeneros.js"></script>

<?php
	include __DIR__ . '/../comun/js/usuario.js.php';
	include __DIR__ . '/../comun/modalObservaciones.php';
?>