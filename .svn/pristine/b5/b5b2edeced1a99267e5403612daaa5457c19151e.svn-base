<div class="card card-block">
	<div class="card-header" id="headerPlanManejo">
		<a href="#Manejo_plan" class="card-link text-dark"><b>Plan de manejo</b></a>
	</div>

	<div class="card-body">
		<form role="form" id="FormPlanManejo" name="FormPlanManejo" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
			<div class="form-row">
				<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3">
					<label id="lblTuvoElectrocar" for="lblTuvoElectro">¿Tuvo electrocardiograma? </label>
				</div>
				<div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
					<select id="SeltuvoElectro" name="tuvoElectro" class="custom-select">
						<option></option>
						<option>Si</option>
						<option>No</option>
					</select>
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12">
					<label id="lblTuvoElectrocardiograma" for="edtTuvoElectrocardiograma">Interpretación Tuvo electrocardiograma</label>
					<textarea type="text" class="form-control <?= $lcCopyPaste ?>" id="txtTuvoElectrocardiograma" name="TuvoElectrocardiograma" rows="4"></textarea>
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12 col-sm-9 col-md-8 col-lg-6 col-xl-4" id="divConductaSeguir">
					<label id="lblConductaSeguirPlan" for="lblConductaSeguir">Conducta a seguir </label>
					<div class="form-group">
						<select class="custom-select d-block w-100" id="selConductaSeguir" name="conductaSeguir">
						</select>
					</div>
				</div>

				<div class="col-12 col-sm-9 col-md-8 col-lg-6 col-xl-4" id="divestadoSalidaPlan">
					<label id="lblestadoSalidaPlan" for="lblestadoSalidaPlan" class="required">Estado Salida</label>

					<div class="form-group">
						<select class="custom-select d-block w-100" id="selEstadoSalidaPlan" name="estadoSalidaPlan">
						</select>
					</div>
				</div>
			</div>

			<div class="form-row" id="divReingresoMismaCausa">
				<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3">
					<label id="lblReingreso" for="lblReingresoMisma" class="required">Reingreso por la misma causa? </label>
				</div>
				<div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
					<select id="SelReingreso" name="Reingreso" class="custom-select">
						<option value=""></option><option value="S">Si</option><option value="N">No</option>
					</select>
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12">
					<label id="lblAnalisisPlan" for="lblAnalisisManejo">Análisis y plan de manejo</label>
					<textarea class="form-control <?= $lcCopyPaste ?>" id="txtAnalisisPlan" name="analisisPlan" rows="5"></textarea>
				</div>
			</div>

			<div class="row">
				<div class="col-12">
					<label class="w-100">SE DA INFORMACIÓN Y EDUCACIÓN AL PACIENTE Y SU FAMILIA SOBRE: DIAGNÓSTICO Y TRATAMIENTO, PRONÓSTICO Y SE ACLARAN DUDAS</label>
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12 col-sm-9 col-md-8 col-lg-6 col-xl-4">
					<label id="lblDoctorInforma" for="lblDoctorInformapaciente">¿El doctor informa al paciente? </label>
					<div class="form-group">
						<select class="custom-select d-block w-100" id="SelDoctorInforma" name="doctorInforma">
						<option value=""></option>
						<option value="S">Si</option>
						<option value="N">No</option>
						</select>
					</div>
				</div>

				<div class="col-12 col-sm-9 col-md-8 col-lg-6 col-xl-4">
					<label id="lblModalidadGrupo" for="lblModalidadGrupoServicio">Modalidad grupo servicio</label>

					<div class="form-group">
						<select id="SelModalidadGrupo" name="ModalidadGrupo" class="custom-select">
						</select>
					</div>
				</div>

				<div class="col-12 col-sm-9 col-md-8 col-lg-6 col-xl-4" id="divAtencionDomiciliaria">
					<label id="lblAtencionDomiciliaria" for="lblDervivadoAtencionDomiciliaria" class="required">Atención derivada de domicilio?</label>

					<div class="form-group">
						<select id="SelAtencionDomiciliaria" name="AtencionDomiciliaria" class="custom-select">
							<option value=""></option>
							<option value="S">Si</option>
							<option value="N">No</option>
						</select>
					</div>
				</div>
			</div>

		</form>
	</div>
</div>

<script type="text/javascript" src="vista-historiaclinica/js/plan_manejo.js"></script>
<script type="text/javascript" src="vista-comun/js/conductaSeguir.js"></script>
<script type="text/javascript" src="vista-comun/js/modalOrdenHospitalizacion.js"></script>
<script type="text/javascript" src="vista-comun/js/estadoSalida.js"></script>
<script type="text/javascript" src="vista-comun/js/modalidadGrupoServicio.js"></script>
