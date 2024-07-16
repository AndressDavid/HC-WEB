<div class="modal fade" id="divUsoAntibiotico" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="card-header">
				<?php include __DIR__ . '/../comun/cabecera.php'; ?>
			</div>

			<div id="divCapturaAgendaBody" class="modal-body">
					<form id="FormUsoAntibiotico" name="FormUsoAntibiotico" class="needs-validation" novalidate>
						<label id="lblNombreUsoAntibiotico" for="txtNombreUsoAntibiotico" style="font-weight: 400; font-weight: bold;"></label>
						<div class="form-row pb-2">
							<div class="col-12 col-sm-12 col-md-12 col-lg-2 col-xl-2">
								<label id="lblCieUsoAntibiotico" for="txtCieUsoAntibiotico" style="font-weight: 400;">Diagnóstico Infeccioso:</label>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-5 col-xl-5">
								<div class="input-group input-group-sm">
									<select class="custom-select d-block" id="selCieUsoInfeccioso" name="CieUsoInfeccioso">
									  <option value=""></option>
									</select>
								</div>
							</div>

							<div class="col-12 col-sm-12 col-md-12 col-lg-5 col-xl-5">
								<div class="input-group input-group-sm">
									<select class="custom-select d-block" id="selCieUsoAntInfecto" name="CieUsoAntInfecto" style="visibility: hidden;">
									  <option value=""></option>
									</select>
								</div>
							</div>
						</div>
						
						<div class="form-row pb-2">
							<div class="col-12 col-sm-12 col-md-12 col-lg-2 col-xl-2">
								<label id="lblOtrosDiagnosticosAntibiotico" for="txtOtrosDiagnosticosAntibiotico" style="font-weight: 400;">Otros Diagnósticos: </label>
							</div>
							<div class="col-12 col-sm-12 col-md-12 col-lg-10 col-xl-10">	
								<textarea class="form-control" id="txtOtrosDiagnosticosAntibiotico" name="OtrosDiagnosticosAntibiotico" rows="2" maxlength="500"></textarea>
							</div>
						</div>
						
						<div class="form-row pb-2">
							<div class="col-12 col-sm-12 col-md-12 col-lg-2 col-xl-2">
								<label id="lblTipoTratamientoUsoAntibiotico" for="txtTipoTratamientoUsoAntibiotico" style="font-weight: 400;">Tipo de tratamiento:</label>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-4 col-xl-4">
								<div class="input-group input-group-sm">
									<select class="custom-select d-block" id="selTipoTratamientoUsoAntibiotico" name="TipoTratamientoUsoAntibiotico" style="display: block;">
									  <option value=""></option>
									</select>
								</div>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-2 col-xl-2 d-flex align-items-end flex-column">
								<label id="lblAjustesUsoAntibiotico" for="txtAjustesUsoAntibiotico" style="font-weight: 400;">Ajustes:</label>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-4 col-xl-4">
								<div class="input-group input-group-sm">
									<select class="custom-select d-block" id="selAjustesUsoAntibiotico" name="AjustesUsoAntibiotico" style="display: block;">
									  <option value=""></option>
									</select>
								</div>
							</div>
						</div>
						
						<div class="form-row pb-2">
							<div class="col-12 col-sm-12 col-md-12 col-lg-2 col-xl-2">
								<label id="lblObservacionesUsoAntibiotico" for="txtObservacionesUsoAntibiotico" style="font-weight: 400;">Observaciones: </label>
							</div>
							<div class="col-12 col-sm-12 col-md-12 col-lg-10 col-xl-10">	
								<textarea class="form-control" id="txtObservacionesUsoAntibiotico" name="ObservacionesUsoAntibiotico" rows="2" maxlength="500"></textarea>
							</div>
						</div>
						<label id="lblResultadoUsoAntibiotico" for="txResultadoUsoAntibiotico" style="font-weight: 400; font-weight: bold;">RESULTADO DE MICROBIOLOGIA</label>
						<div class="form-row pb-2">
							<div class="col-12 col-sm-12 col-md-12 col-lg-2 col-xl-2">
								<label id="lblOrigenMuestraUsoAntibiotico" for="selOrigenMuestraUsoAntibiotico" style="font-weight: 400;">Origen/Tipo de muestra:</label>
							</div>
							<div class="col-12 col-sm-12 col-md-12 col-lg-4 col-xl-4">
								<div class="input-group input-group-sm">
									<select class="custom-select d-block" id="selOrigenMuestraUsoAntibiotico" name="OrigenMuestraUsoAntibiotico" style="display: block;">
									  <option value=""></option>
									</select>
								</div>
							</div>
						</div>
						<div class="form-row pb-2">
							<div class="col-12 col-sm-12 col-md-12 col-lg-2 col-xl-2">
								<label id="lblResultadosUsoAntibiotico" for="txtResultadosUsoAntibiotico" style="font-weight: 400;">Resultado: </label>
							</div>
							<div class="col-12 col-sm-12 col-md-12 col-lg-10 col-xl-10">	
								<textarea class="form-control" id="txtResultadosUsoAntibiotico" name="ResultadosUsoAntibiotico" rows="2" maxlength="500"></textarea>
							</div>
						</div>
					</form>
					<div class="table-responsive"><table id="tblUsoAntibioiticosOM"></table></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Guardar información" aria-pressed="true" id="btnGuardaUsoAntibiotico"><u>G</u>uardar</button>
				<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnSalirUsoAntibiotico"><u>S</u>alir</button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="vista-comun/js/modalJustificacionUsoAntibiotico.js"></script>

