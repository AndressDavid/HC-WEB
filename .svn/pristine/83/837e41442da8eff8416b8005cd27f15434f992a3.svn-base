<div class="container-fluid">
	<div class="card mt-3">

		<div class="card-header">
			<div class="row">
				<div class="col">
					<h5>Censo urgencias</h5>
				</div>
			</div>
			<form id="frmFiltros">
				<div id="divFiltro" class="row">
					<div class="col-xs-6 col-md-4 col-xl-2">
						<label for="txtIngreso"><b>Ingreso</b></label>
						<input id="txtIngreso" type="number" class="form-control form-control-sm" name="txtIngreso" placeholder="" value="">
					</div>
					
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-6 col-xl-6">
						<label for="selSeccionCU" class="control-label"><b>Sección</b></label>
						<select name="SeccionCU" id="selSeccionCU" class="form-control form-control-sm"><option value=""></option></select>
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
			<table id="tblCensoUrgencias"></table>
		</div>

	</div>
</div>

<div class="modal fade" id="divCensoUrgencias" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header badge-light">
				<h5 class="modal-title col-11 text-center" id="modalCensoUrgencias">CENSO URGENCIAS</h5>
			</div>

			<div id="divCapturaAgendaBody" class="modal-body">
				<form id="FormCensoUrgencias" name="FormCensoUrgencias" class="needs-validation" novalidate>
					
					<div class="row">
						<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
							<label id="lblHistoricoCenso" for="txtHistoricoCenso">Registros anteriores</label>
							<textarea class="form-control" id="txtHistoricoCenso" name="HistoricoCenso" rows="8" disabled></textarea>
						</div>
					</div>

					<div class="row">
						<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
							<label id="lblRegistrarCenso" for="txtRegistrarCenso" >Registrar</label>
							<textarea class="form-control" id="txtRegistrarCenso" name="RegistrarCenso" rows="4"></textarea>
						</div>
					</div>
	
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Guardar información" aria-pressed="true" id="btnGuardaCensoUrgencias">Guardar</button>
				<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarCensoUrgencias">Cancelar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="divAlertaTempranaCensoU" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header badge-light">
				<h5 class="modal-title col-11 text-center" id="modalCensoUrgenciasAlertaT">CENSO URGENCIAS</h5>
			</div>

			<div id="divCapturaAgendaBody" class="modal-body">
				<form id="FormAlertaTempranaCensoU" name="FormAlertaTempranaCensoU" class="needs-validation" novalidate>
					
					<div class="row">
						<div class="col-12 col-sm-12 col-md-6 col-lg-3 col-xl-32">
							
							<select id="selAlertaTempranaCensoU" name="AlertaTempranaCensoU" class="custom-select" required>
								<option></option> <option>Si</option><option>No</option>
							</select>
							
						</div>
					</div>
					
	
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Guardar información" aria-pressed="true" id="btnGuardaAlertaTemCenso">Guardar</button>
				<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarAlertaTemCenso">Cancelar</button>
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
<script type="text/javascript" src="vista-censo-urgencias/js/script.js"></script>
<script type="text/javascript" src="vista-comun/js/modalVistaPrevia.js"></script>

