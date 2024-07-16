<div class="modal fade" id="divOrdenHospitaliza" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title col-11 text-center" id="exampleModalLabel">SOLICITUD ORDEN HOSPITALIZACIÓN</h5>
			</div>

			<div id="divCapturaAgendaBody" class="modal-body">
				<form id="FormOrdenHospitalizacion" name="FormOrdenHospitalizacion" class="needs-validation" novalidate>
					<div class="row">
						<div class="col-md-12">
							
							<div class="form-group" style="display: none;">
								<label type="hidden" id="lblEstadoOrden" for="selEstadoOrden">Estado orden</label>
								<input type="hidden" value="1" id="EstadoOrden" name="EstadoOrden"/>
							</div>
							
							<label id="lblEspecialidadOrden" for="selEspecialidadOrden" class="required">Especialidad tratante</label>
							<div class="form-group">
								<select class="custom-select d-block w-100" id="selEspecialidadOrden" name="EspecialidadOrden">
								<option value=""></option>
								</select>
							</div>
							
							<label id="lblmedicoOrden" for="selMedicoOrden" class="required">Médico tratante</label>
							<div class="form-group">
								<select class="custom-select d-block w-100" id="selMedicoOrden" name="medicoOrden">
								<option value=""></option>
								</select>
							</div>
							
							<label id="lblAreaTrasladar" for="selAreaTrasladar" class="required">Área a trasladar</label>
							<div class="form-group">
								<select class="custom-select d-block w-100" id="selAreaTrasladar" name="AreaTrasladar">
								<option value=""></option>
								</select>
							</div>
							
							<label id="lblUbicacionTrasladar" for="selUbicacionTrasladar" class="required">Ubicación a trasladar</label>
							<div class="form-group">
								<select class="custom-select d-block w-100" id="selUbicacionTrasladar" name="selUbicacionTrasladar">
								<option value=""></option>
								</select>
							</div>
							
							<div class="form-group">
								<label id="lblJustificacionordenHos" for="txtJustificacionordenHos">Justificación</label>
								<textarea class="form-control" id="txtJustificacionordenHos" name="JustificacionordenHos" rows="5"></textarea>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarOrdenHos"><u>C</u>ancelar</button>
				<button type="button" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Guardar información" aria-pressed="true" id="btnGuardaOrdenHos"><u>G</u>uardar</button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="vista-comun/js/modalOrdenHospitalizacion.js"></script>
