<div class="card card-block">
	<div class="card-header" id="headerConciliacion">
		<a href="#Concilia_uno" class="card-link text-dark"><b>Conciliación de Medicamentos</b></a>
	</div>

	<div class="card-body">
		<form role="form" id="FormConcilia1" name="FormConcilia1" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
			<div class="row pb-2">
				<div class="col-sm-6 col-12">
					<div class="input-group">
						<div class="col-6 p-0">
							<label id="lblConsume" for="optConsume">¿Consume medicamentos? </label>
						</div>
						<div class="col-6">
							<select id="selConsume" name="Consume" class="custom-select">
								<option></option>
								<option>Si</option>
								<option>No</option>
							</select>
						</div>
					</div>
				</div>
				
				<div class="col-sm-6 col-12" id="divNoConsume">
					<div class="row">
						<div class="col-4">
							<label id="lblNoConsume" for="lblNoConsume">Motivo No consume</label>
						</div>
						<div class="col-8">
							<select id="selNoConsume" name="NoConsume" class="custom-select"></select>
						</div>
					</div>
				</div>
			</div>
		</form>
		
		<form role="form" id="FormConcilia2" name="FormConcilia2" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
			<div class="row pb-2">
				<div class="input-group">
					<div class="col-lg-4 col-md-4 col-sm-12 col-12">
						<label id="lblMedicamentoNC" for="txtMedicamentoNC">Medicamento no codificado</label>
					</div>
					<div class="col-12">
						<input placeholder="MedicamentoNC" class="form-control mr-sm-2" id="txtMedicamentoNC" name="medicaNC">
					</div>
				</div>
				
				<div class="input-group">
					<div class="col-lg-4 col-md-4 col-sm-12 col-12">
						<label id="lblMedicamentoC" for="cMedicamentoConc">Medicamento codificado</label>
					</div>
					<div class="col-12">
						<input type="text" class="form-control form-control-sm font-weight-bold ignore col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" name="cMedicamentoConc" id="cMedicamentoConc" autocomplete="off" >
					</div>
				</div>
				
				<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">			
					<div class="input-group">
						<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-1 col-xl-1" name="cCodigoMedicamentoConc" id="cCodigoMedicamentoConc" readonly="readonly">
						<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-11 col-xl-11" name="cDescripcionMedicamentoConc" id="cDescripcionMedicamentoConc" readonly="readonly">
					</div>
				</div>
	
				<div class="row">
					<div class="col-lg-6">
						<div class="input-group">
							<div class="col-12">
								<label id="lblTipoDosis" for="selTipoDosis">Dosis</label>
							</div>
							<div class="col-lg-4 col-md-4 col-sm-8 col-12">
								<input name="Dosis" type="number" min="0" max="9999999" id="txtDosis" placeholder="Dosis" class="form-control mr-sm-2">
							</div>
							<div class="col-lg-8 col-md-8 col-sm-12 col-12">
								<select class="custom-select" id="selTipoDosis" name="Tdosis"></select>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="input-group">
							<div class="col-12">
								<label id="lblFrecuencia" for="txtFrecuencia">Frecuencia</label>
							</div>
							<div class="col-lg-4 col-md-4 col-sm-8 col-12">
								<input name="Frecuencia" type="number"  min="0" max='99'id="txtFrecuencia" placeholder="Frecuencia" class="form-control mr-sm-2">
							</div>
							<div class="col-lg-8 col-md-8 col-sm-12 col-12">
								<select name="TFrecuencia" class="custom-select" id="selTipoFrecuencia"></select><br>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="input-group">
							<div class="col-12">
								<label id="lblTipoVia" for="selTipoVia">Vía Administración</label>
							</div>
							<div class="col-12">
								<select name="Via" class="custom-select" id="selTipoVia"></select><br>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="input-group">
							<div class="col-12">
								<label id="lblConducta" for="selTipoConducta">Conducta a seguir ?</label>
							</div>
							<div class="col-12">
								<select name="Conducta" class="custom-select" id="selTipoConducta">
									<option></option>
									<option>Continua</option>
									<option>Suspende</option>
									<option>Modifica</option>
								</select><br>
							</div>
						</div>
					</div>
				</div>

				<div class="col-12">
					<label id="lblObserva" for="edtObserva">Observaciones</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text"><i class="fas fa-info"></i></span>
						</div>
						<textarea type="text" class="form-control" id="edtObserva"></textarea>
					</div>
				</div>
			</div>

			<div class="form-row justify-content-between pt-2">
				<div class="col-md-2 offset-md-10">
					<button id="btnAdicionarM" class="btn btn-secondary btn-block">Adicionar</button>
				</div>
			</div>
			
			<div class="col-12">
				<table id="tblMedica"></table>
			</div>
		</form>
		
		<form role="form" id="FormConcilia3" name="FormConcilia3" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
			<div class="form-row">
				<small>
					<label for="lblInformación"><b>LA FUNDACION CLINICA SHAIO NO SE HACE RESPONSABLE DE LA ADMINISTRACIÓN DE MEDICAMENTOS NATURALES, HOMEOPATICOS TERAPIAS ALTERNATIVASO FARMACOLOGIA VEGETAL DURANTE LA ESTANCIA EN LA INSTITUCION DEL PACIENTE.</b></label>
				</small>

				<div class="col-12" id="divInformante">
					<label id="lblInformante" for="txtInformante">Informante paciente pediátrico</label>		
					<input id="txtInformante" placeholder="Informante" name="Informante" class="form-control mr-sm-2">
				</div>

				<div class="col-lg-3 col-md-5 col-sm-12 col-12">
					<br><label id="lblInforma" for="optInforma">¿ El Dr. informa al paciente ? </label>
				</div>
				<div class="col-lg-2 col-md-3 col-sm-6 col-6"><br>
					<select id="selInforma" name="Informa" class="custom-select">
						<option></option>
						<option>Si</option>
						<option>No</option>
					</select>
				</div>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript" src="vista-comun/js/tiposMedica.js"></script>
<script type="text/javascript" src="vista-comun/js/Conciliacion.js"></script>
