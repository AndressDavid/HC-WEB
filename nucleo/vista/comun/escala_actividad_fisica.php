<div class="card card-block">
	<div class="card-header" id="headerDiagnostico">
		<a href="#Actividad_uno" class="card-link text-dark"><b>Actividad Física</b></a>
	</div>

	<div class="card-body">
		<form role="form" id="FormActividadFisica" name="FormActividadFisica" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
			
			<div class="form-row pt-2" id="divRealiza">
				<div class="col-12 col-sm-12 col-md-12 col-lg-4 col-xl-3">
					<label id="lblRealizaActividad" for="selRealizaActividad" class="control-label required">Realiza actividad física? </label>
				</div>
				<div class="col-12 col-sm-12 col-md-12 col-lg-8 col-xl-4">
					<select class="custom-select" id="selRealizaActividad" name="RealizaActividad">
					<option></option>
					</select>
				</div>
			</div>
			
			<div class="form-row pb-2">
				<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
					<div class="row">
						<div class="col-12 col-sm-12 col-md-12 col-lg-7 col-xl-6">
							<label id="lblTipoActividad" for="selTipoActividad">Tipo Actividad </label>
							<div class="form-group">
								<select class="custom-select d-block w-100" id="selTipoActividad" name="TipoActividad" disabled>
								<option></option>
								</select>
							</div>
						</div>

						<div class="col-12 col-sm-12 col-md-12 col-lg-5 col-xl-6">
							<label id="lblClaseActividad" for="selClaseActividad">Clase Actividad </label>
							<div class="form-group">
								<select class="custom-select d-block w-100" id="selClaseActividad" name="ClaseActividad" disabled>
								<option></option>
								</select>
							</div>
						</div>
						
						<div class="col-12 col-sm-12 col-md-6 col-lg-3 col-xl-3">
							<label id="lblFrecuenciaActividad" for="selFrecuenciaActividad">Frecuencia (días)</label>
							<input name="FrecuenciaActividad" type="number" id="selFrecuenciaActividad" class="form-control mr-sm-2" value="" min="1" max="31" disabled>
						</div>
						<div class="col-12 col-sm-12 col-md-6 col-lg-3 col-xl-3">
							<label id="lblTiempoActividad" for="selTiempoActividad">Tiempo (minutos)</label>
							<input name="TiempoActividad" type="number" id="selTiempoActividad" class="form-control mr-sm-2" value="" min="1" max="180" disabled>
						</div>
						
						<div class="col-12 col-sm-12 col-md-6 col-lg-3 col-xl-3">
							<label id="lblIntensidadActividad" for="selIntensidadActividad">Intensidad</label>
							<select class="custom-select d-block w-100" id="selIntensidadActividad" name="IntensidadActividad" disabled>
								<option></option>
							</select>
						</div>
						
						<div class="col-12 col-sm-12 col-md-6 col-lg-3 col-xl-3">
							<label for="btnAdicionar"> &nbsp; &nbsp; &nbsp; </label>
							<button id="adicionarActividad" class="btn btn-secondary btn-sl btn-block w-100" accesskey="A" disabled><u>A</u>dicionar</button>
						</div>
					</div>
				</div>
			</div>
		</form>
		<div class="table-responsive"> <table id="tblActividadHC"></table> </div>
	</div>
</div>
<script type="text/javascript" src="vista-comun/js/escala_actividad_fisica.js"></script>
