<form id="FormmedicamentosOM" name="FormmedicamentosOM" novalidate="validate">

	<h6 style="text-align: center">Formulación</h6>
	<div class="form-row pb-2">
		<div class="col-12">
			<div class="input-group input-group-sm">
				<label id="lblcProcedimientoOM" for="cMedicamentoOM" class="required">Medicamento</label>
				<label> &nbsp; &nbsp; &nbsp;</label>
				<input type="text" class="form-control form-control-sm font-weight-bold ignore" name="cMedicamentoOM" id="cMedicamentoOM" autocomplete="off" >
			</div>
		</div>

		<div class="input-group input-group-sm">
			<input type="text" class="form-control form-control-sm font-weight-bold" name="cCodigoMedicamentoOM" id="cCodigoMedicamentoOM" readonly="readonly">
			<input type="text" class="form-control form-control-sm font-weight-bold" name="cDescripcionMedicamentoOM" id="cDescripcionMedicamentoOM" readonly="readonly">
		</div>
	</div>

	<div class="row pb-2">
		<div class="col-6 col-lg-4 col-xl-3 p-0">
			<div class="input-group">
				<div class="col-12">
					<label id="lblTipoDosisOM" for="selTipoDosisOM">Dosis</label>
				</div>
				<div class="input-group col-12">
					<input name="DosisOM" type="number" id="txtDosisOM" class="form-control" value="" min="0" step="0.01">
					<select class="custom-select form-control" id="selTipoDosisOM" name="tDosisOM"></select>
				</div>
			</div>
		</div>

		<div class="col-6 col-lg-4 col-xl-3 p-0">
			<div class="input-group">
				<div class="col-12">
					<label id="lblFrecuenciaOM" for="selTipoFrecuenciaOM">Frecuencia</label>
				</div>
				<div class="input-group col-12">
					<input name="FrecuenciaAmb" type="number" min="0" id="txtFrecuenciaOM" class="form-control">
					<select class="custom-select form-control" id="selTipoFrecuenciaOM" name="TFrecuenciaOM" >
					 <option value=""></option>
					</select>
				</div>
			</div>
		</div>

		<div class="col-6 col-lg-4 col-xl-3 p-0">
			<div class="input-group">
				<div class="col-12">
					<label id="lblTipoViaOM" for="selTipoViaOM">Vía</label>
				</div>
				<div class="col-12">
					<select name="ViaOM" class="custom-select" id="selTipoViaOM"></select><br>
				</div>
			</div>
		</div>

		<div id="antibioticoOM" class="col-6 col-lg-4 col-xl-3 p-0">
			<div class="input-group">
				<div class="col-12">
					<label id="lblDiasUsoAntibioticoOM" for="txtDiasUsoAntibioticoOM">Dias de uso del Antibiótico</label>
				</div>
				<div class="col-12">
					<input name="DiasUsoAntibioticoOM" type="number" id="txtDiasUsoAntibioticoOM" class="form-control mr-sm-2">
				</div>

			</div>
		</div>
	</div>

	<div class="row pb-2">
		<div class="col-12">
			<label id="lblObservacionesOM" for="edtObservacionesOM">Observaciones</label>
		</div>

		<div class="col-12">
			<div class="input-group">
				<div class="input-group-prepend">
					<span class="input-group-text"><i class="fas fa-info"></i></span>
				</div>
				<textarea type="textOM" class="form-control" id="edtObservacionesOM"></textarea>
			</div>
		</div>
	</div>
</form>

<div class="row justify-content-between pt-2">
	<div class="col-6 col-md-4 col-lg-3 col-xl-2">
		<button id="AdicionarMedicamentoOM" class="btn btn-secondary btn-sl btn-block w-100">Adicionar</button>
	</div>
	<div class="col-6 col-md-4 col-lg-3 col-xl-3 d-none d-md-block">
		<button id="btnConciliacionMed" class="btn btn-secondary btn-sl btn-block w-100">Conciliación</button>
	</div>
	<div class="col-6 col-md-4 col-lg-3 col-xl-3">
		<button id="btnMedSuspendidosOM" class="btn btn-secondary btn-sl btn-block w-100">Med suspendidos</button>
	</div>
</div>

<div class="table-responsive">
	<table id="tblMedicamentosOM"></table>
</div>

<div id="divModalConciliaMed" class="d-none">
	<h6 style="text-align: center">Conciliación de Medicamentos</h6>
	<small><table id="tblConciliacionMed"></table></small>
</div>

<div class="modal fade" id="divSuspensionAntibiotico" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header badge-danger">
				<h5 class="modal-title col-11 text-center" id="exampleModalLabel">SUSPENDER ANTIBIOTICO</h5>
			</div>

			<div id="divCapturaAgendaBody" class="modal-body">
				<form id="FormSuspensionAntibiotico" name="FormSuspensionAntibiotico" class="needs-validation" novalidate>
					<div class="row">
						<div class="col-md-12">
							<label id="lblTipoSuspenderAntibiotico" for="selTipoSuspenderAntibiotico" class="required">Motivo para suspender el antibiótico</label>
							<div class="form-group">
								<select class="custom-select d-block w-100" id="selTipoSuspenderAntibiotico" name="TipoSuspenderAntibiotico">
								<option value=""></option>
								</select>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Guardar información" aria-pressed="true" id="btnGuardaSuspAntibiotico">Guardar</button>
				<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarSuspAntibiotico">Cancelar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="divModificarAntibiotico" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header badge-danger">
				<h5 class="modal-title col-11 text-center" id="exampleModalLabel">MODIFICAR ANTIBIOTICO</h5>
			</div>

			<div id="divCapturaAgendaBody" class="modal-body">
				<form id="FormModificarAntibiotico" name="FormModificarAntibiotico" class="needs-validation" novalidate>
					<div class="row">
						<div class="col-md-12">
							<label id="lblTipoModificacionAntibiotico" for="selTipoModificacionAntibiotico" class="required">Motivo para modificar el antibiótico</label>
							<div class="form-group">
								<select class="custom-select d-block w-100" id="selTipoModificacionAntibiotico" name="TipoModificacionAntibiotico">
								<option value=""></option>
								</select>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Guardar información" aria-pressed="true" id="btnGuardaModifAntibiotico">Guardar</button>
				<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarModifAntibiotico">Cancelar</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript" src="vista-ordenes-medicas/js/ordenes_medicamentos.js"></script>
<script type="text/javascript" src="vista-comun/js/tiposMedica.js"></script>
