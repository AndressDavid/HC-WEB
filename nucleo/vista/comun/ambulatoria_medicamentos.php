<form id="Formmedicamentos" name="Formmedicamentos" class="needs-validation" novalidate="validate">
	<div class="form-row pb-2">
		<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
			<div class="input-group input-group-sm">							
				<label id="lblcMedicamentoAM" for="cMedicamentoAM" class="required">Medicamento codificado</label>
				<label> &nbsp; &nbsp; &nbsp;</label>
				<input type="text" class="form-control form-control-sm font-weight-bold ignore col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" name="cMedicamentoAM" id="cMedicamentoAM" autocomplete="off" >
			</div>
		</div>
				
		<div class="input-group input-group-sm">
			<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-1 col-xl-1" name="cCodigoMedicamentoAM" id="cCodigoMedicamentoAM" readonly="readonly">
			<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-11 col-xl-11" name="cDescripcionMedicamentoAM" id="cDescripcionMedicamentoAM" readonly="readonly">
		</div>
	</div>
	
	<div class="row pb-2">
		<div class="col">
			<div class="input-group">
				<div class="col-12">
					<label id="lblMedicamentoNCAmb" for="txtMedicamentoNCAmb">Medicamento no codificado</label>
				</div>
				<div class="col-12 col-md-9 col-xl-10">
					<input placeholder="Medicamento sin código" class="form-control mr-sm-2" id="txtMedicamentoNCAmb" name="medicaNCAmb" style="text-transform:uppercase;">
				</div>
				<div class="col-12 col-md-3 col-xl-2 form-check mt-2 ml-3 ml-md-0">
					<input type="checkbox" class="form-check-input mr-sm-2" id="chkMedicamentoNCAmbNoPos" name="medicaNCAmbPos" disabled="disabled">
					<label class="form-check-label" for="chkMedicamentoNCAmbNoPos"> Es No POS</label>
				</div>
			</div>
		</div>
	</div>

	<div class="row pb-2">
		<div class="col-12 col-xl-6">
			<div class="input-group">
				<div class="col-12">
					<label id="lblTipoDosisAmb" for="selTipoDosisAmb">Dosis</label>
				</div>
				<div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-4">
					<input name="DosisAmb" type="number" id="txtDosisAmb" placeholder="Dosis" class="form-control mr-sm-2" value="" min="0">
				</div>
				<div class="col-12 col-sm-6 col-md-8 col-lg-9 col-xl-8">
					<select class="custom-select" id="selTipoDosisAmb" name="TdosisAmb"></select>
				</div>
			</div>
		</div>
		<div class="col-12 col-xl-6">
			<div class="input-group">
				<div class="col-12">
					<label id="lblFrecuenciaAmb" for="txtFrecuenciaAmb">Frecuencia</label>
				</div>
				<div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-4">
					<input name="FrecuenciaAmb" type="number" min="0" id="txtFrecuenciaAmb" placeholder="Frecuencia" class="form-control mr-sm-2">
				</div>
				<div class="col-12 col-sm-6 col-md-8 col-lg-9 col-xl-8">
					<select name="TFrecuenciaAmb" class="custom-select" id="selTipoFrecuenciaAmb"></select><br>
				</div>
			</div>
		</div>
	</div>

	<div class="row pb-2">
		<div class="col-12 col-xl-6">
			<div class="input-group">
				<div class="col-12">
					<label id="lblTipoDosisDiariaAmb" for="selTipoDosisDiariaAmb">Dosis diaria</label>
				</div>
				<div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-4">
					<input name="DosisdiariaAmb" type="number" min="0" id="txtDosisdiariaAmb" placeholder="Dosis diaria" class="form-control mr-sm-2">
				</div>
				<div class="col-12 col-sm-6 col-md-8 col-lg-9 col-xl-8">
					<select class="custom-select" id="selTipoDosisDiariaAmb" name="TdosisdiariaAmb"></select>
				</div>
			</div>
		</div>

		<div class="col-12 col-xl-6">
			<div class="input-group">
				<div class="col-12">
					<label id="lblTiempoTratamientoAmb" for="txtTiempoTratamientoAmb">Tiempo tratamiento</label>
				</div>
				<div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-4">
					<input name="TiempoTratamientoAmb" type="number" min="0" id="txtTiempoTratamientoAmb" placeholder="Tiempo tratamiento" class="form-control mr-sm-2" >
				</div>

				<div class="col-12 col-sm-6 col-md-8 col-lg-9 col-xl-8">
					<select id="selTiempoTratamientoAmb" name="TTiempoTratamientoAmb" class="custom-select">
						<option></option>
						<option>DIAS</option>
					</select>
				</div>

			</div>
		</div>
	</div>

	<div class="row pb-2">
		<div class="col-12 col-lg-6">
			<div class="input-group">
				<div class="col-12">
					<label id="lblCantidadAmb" for="txtCantidadAmb">Cantidad</label>
				</div>

				<div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4">
					<input name="CantidadAmb" type="number" min="0" id="txtCantidadAmb" placeholder="" class="form-control">
				</div>
				<div class="col-12 col-sm-6 col-md-8 col-lg-8 col-xl-8">
					<input name="textoCantidadAmb" type="text" id="txtTextoCantidadAmb" placeholder="" class="form-control" value="">
				</div>
			</div>
		</div>

		<div class="col-12 col-lg-6">
			<div class="input-group">
				<div class="col-12">
					<label id="lblTipoViaAmb" for="selTipoViaAmb">Vía</label>
				</div>
				<div class="col-12">
					<select name="ViaAmb" class="custom-select" id="selTipoViaAmb"></select><br>
				</div>
			</div>
		</div>
	</div>

	<div class="row pb-2">
		<div class="col">
			<div class="input-group">
				<div class="col-12">
					<label id="lblObservaAmb" for="edtObservaAmb">Observaciones</label>
				</div>

				<div class="col-12">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text"><i class="fas fa-info"></i></span>
						</div>
						<textarea type="textAmb" class="form-control" id="edtObservaAmb"></textarea>
					</div>
				</div>

			</div>
		</div>
	</div>
</form>

<div class="row pt-2">
	<div class="col-12 col-sm-6 col-md-4 col-lg-2 col-xl-2">
		<button id="AdicionarMedAmb" class="btn btn-secondary btn-block">Adicionar</button>
	</div>

	<div class="col-12 col-sm-6 col-md-4 col-lg-2 col-xl-2">
		<button id="btnEliminarMedicamentosAmb" class="btn btn-secondary btn-block">Eliminar</button>
	</div>

	<div class="col-12 col-sm-12 col-md-12 col-lg-4 col-xl-5"></div>

	<div class="col-12 col-sm-12 col-md-8 col-lg-4 col-xl-3">
		<button id="btnMedicamentosAnteriores" class="btn btn-secondary btn-block">Medicamentos Anteriores</button>
	</div>
</div>

<div class="table-responsive">
	<table id="tblMedicaAmb">
	</table>
</div>

<form id="FormPreguntasmed" name="FormPreguntasmed" class="needs-validation" novalidate>
	<div class="row pt-2">
		<div class="col-12 col-sm-8 col-md-9 col-lg-10 col-xl-10">
			<label id="lblRealizoFormulacion" for="selRealizoFormulacion">Realizó formulacion de egreso teniendo en cuenta los medicamentos registrados en
			la conciliación de medicamentos al ingreso del paciente?.</label>
		</div>

		<div class="col-6 col-sm-4 col-md-3 col-lg-2 col-xl-2">
			<select id="selRealizoFormulacion" name="RealizoFormulacion" class="custom-select">
				<option></option>
				<option>Si</option>
				<option>No</option>
			</select>
		</div>
	</div>

	<div class="row pt-2">
		<div class="col-12 col-sm-8 col-md-9 col-lg-10 col-xl-10">
			<label id="lblBrindoInformacion" for="selBrindoInformacion">El médico le brindó al paciente información sobre el uso correcto de los medicamentos
			que deberá tomar en casa?  fue clara y entendida?.</label>
		</div>

		<div class="col-6 col-sm-4 col-md-3 col-lg-2 col-xl-2">
			<select id="selBrindoInformacion" name="BrindoInformacion" class="custom-select">
				<option></option>
				<option>Si</option>
				<option>No</option>
			</select>
		</div>
	</div><br>
</form>

<div class="modal fade" id="divMedicamentosAnteriores" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header bg-info">
				<h5 class="modal-title col-11 text-center" id="exampleModalLabel">MEDICAMENTOS ANTERIORES</h5>
			</div>
			<div id="divCapturaAgendaBody" class="modal-body">
				<form id="FormMedicamentosAnteriores" name="FormMedicamentosAnteriores" class="needs-validation" novalidate>
					<div class="row">
						<div class="col-md-12">

							<div class="table-responsive">
								<table id="tblMedicaAnteriores"></table>
							</div>

						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnAceptarMedAnteriores"><u>A</u>ceptar</button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="vista-comun/js/tiposMedica.js"></script>
<script type="text/javascript" src="vista-comun/js/Conciliacion.js"></script>
