<div class="modal fade" id="divPlanesPaciente" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header text-center alert alert-secondary">
				<h5 class="modal-title col-11 text-center" id="titPlanPaciente"></h5>
			</div>

			<div id="divCapturaAgendaBody" class="modal-body">
				<form id="FormPlanesPaciente" name="FormPlanesPaciente" class="needs-validation" novalidate>
					<div class="row">
						<div class="col-md-12">
							<label id="lblPlanPacienteOA" for="selPlanPacienteOA" class="required">Plan</label>
							<div class="form-group">
								<select class="custom-select d-block w-100" id="selPlanPacienteOA" name="PlanPacienteOA">
								<option value=""></option>
								</select>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnAceptarPlan" disabled><u>A</u>ceptar</button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="vista-comun/js/modalPlanesPaciente.js"></script>
