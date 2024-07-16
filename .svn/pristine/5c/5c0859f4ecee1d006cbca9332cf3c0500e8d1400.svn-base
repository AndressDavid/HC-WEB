<div class="modal fade" id="divMedicamentoControlado" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="card-header">
				<?php include __DIR__ . '/../comun/cabecera.php'; ?>
			</div>
			<h5 class="modal-title col-11 text-center" id="exampleModalLabel">Solicitud Medicamento Controlado</h5>

			<div id="divCapturaAgendaBody" class="modal-body">
				<form id="FormMedicamentoControlado" name="FormMedicamentoControlado" class="needs-validation" novalidate>
					<div class="input-group input-group-sm">
						<label id="lblMedicamentoControlado" for="txtMedicamentoControlado">Medicamento:</label>
						<label> &nbsp; &nbsp; &nbsp;</label>
						<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-1 col-xl-1" name="cCodigoMedicamentoControlado" id="cCodigoMedicamentoControlado" readonly="readonly">
						<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-11 col-xl-11" name="cDescripcionControlado" id="cDescripcionControlado" readonly="readonly">
					</div><br>

					<div class="form-row pb-2">
						<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
							<div class="input-group input-group-sm">
								<label id="lblCieControlado" for="txtCodigoControlado" class="required">Diagnóstico</label>
								<label> &nbsp; &nbsp; </label>
								<input type="text" class="form-control form-control-sm font-weight-bold ignore col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" style="display: none;" name="txtCodigoControlado" id="txtCodigoControlado" autocomplete="off">
								<select class="form-control form-control-sm font-weight-bold ignore col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" id="selCieControladoPaciente" name="CieControladoPaciente" style="display: block;">
								  <option value=""></option>
								</select>
							</div>
						</div>

						<div class="input-group input-group-sm">
							<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-1 col-xl-1" name="cCodigoCieControlado" id="cCodigoCieControlado" readonly="readonly">
							<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-11 col-xl-11" name="cDescripcionCieControlado" id="cDescripcionCieControlado" readonly="readonly">
						</div>
					</div>

					<div class="form-row pb-2">
						<div class="col-12 col-sm-12 col-md-12 col-lg-3 col-xl-3">
							<div class="input-group input-group-sm">
								<label id="lblCantidadControlado" for="txtCantidadControlado" class="required">Cantidad:</label>
								<label> &nbsp; &nbsp; &nbsp; &nbsp; </label>
								<input type="number" class="form-control" id="inpCantidadControlado" name="CantidadControlado" value="">

							</div>
						</div>
						<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-2 mt-2">
							<div id="textoUnidadControlado"></div>
						</div>

						<div class="custom-control custom-checkbox custom-control-inline mt-2 ml-3 ml-md-0">
							<input type="checkbox" class="form-check-input mr-sm-2" id="chkSoloDiagnosticoPaciente" name="SoloDiagnosticoPaciente">
							<label id="lblSoloDiagnosticoPaciente" class="form-check-label" for="chkSoloDiagnosticoPaciente"> Solo diagnósticos del paciente</label>
						</div>
					</div>

					<div class="form-group">
						<label id="lblObservacionesControlado" for="txtObservacionesControlado">Observaciones:</label>
						<textarea class="form-control" id="txtObservacionesControlado" name="ObservacionesControlado" rows="5" maxlength="500"></textarea>
					</div>

				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Guardar información" aria-pressed="true" id="btnGuardaControlado"><u>G</u>uardar</button>
				<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarControlado"><u>C</u>ancelar</button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="vista-comun/js/modalMedicamentoControlado.js"></script>
