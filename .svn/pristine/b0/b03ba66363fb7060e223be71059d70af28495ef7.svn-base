<div class="card card-block">
	<div class="card-header" id="headerDiagnostico">
		<a href="#Diagnostico_uno" class="card-link text-dark"><b>Diagnóstico</b></a>
	</div>

	<div class="card-body">
		<form role="form" id="FormDiagnostico" name="FormDiagnostico" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
			<div class="form-row pb-2">
				<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
					<div class="input-group input-group-sm">							
						<label id="lblCodigoCie" for="txtCodigoCie" class="required">Diagnóstico</label>
						<label> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </label>
						<input type="text" class="form-control form-control-sm font-weight-bold ignore col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" name="txtCodigoCie" id="txtCodigoCie" autocomplete="off">
					</div>
				</div> 	

				<div class="input-group input-group-sm">
					<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-1 col-xl-1" name="cCodigoCie" id="cCodigoCie" readonly="readonly">
					<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-11 col-xl-11" name="cDescripcionCie" id="cDescripcionCie" readonly="readonly">
				</div>
			</div>
				
			<div class="form-row pb-2">
				<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
					<div class="row">
						<div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-4">
							<label for="tipoDiagnostico"><b>Tipo</b> <span class="badge badge-info" id="botonAyudaTipo"><i class="far fa-question-circle"></i></span></label>
							<div class="form-group">
								<select class="custom-select d-block w-100" id="tipoDiagnostico" name="tipoDiagnostico">
								<option value=""></option></select>
							</div>
						</div>

						<div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-4">
							<label for="claseDiagnostico"><b>Clase</b> <span class="badge badge-info" id="botonAyudaClase"><i class="far fa-question-circle"></i></span></label>
							<select class="custom-select d-block w-100" id="claseDiagnostico" name="claseDiagnostico">
								<option value=""></option>
							</select>
						</div>

						<div class="col-12 col-sm-12 col-md-12 col-lg-4 col-xl-4">
							<label for="tratamientoDiagnostico"><b>Tratamiento</b> <span class="badge badge-info" id="botonAyudaTratamiento">
							<i class="far fa-question-circle"></i></span></label>
							<select class="custom-select d-block w-100" id="tratamientoDiagnostico" name="tratamientoDiagnostico">
								<option value=""></option>
							</select>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-12">
					<label for="cieObservaciones" ><b>Caracterización</b></label>
					<textarea class="form-control" id="cieObservaciones" rows="2" maxlength="200"></textarea>
				</div>
			</div>
			<div id="ayudaTipoDiagnostico" class="collapse"></div>
			<div id="ayudaClaseDiagnostico" class="collapse"></div>
			<div id="ayudaTratamientoDiagnostico" class="collapse"></div>
		</form>
		<div class="row justify-content-between pt-2">
			<div class="col-12 col-sm-6 col-md-5 col-lg-3 col-xl-3">
				<button id="btnConsultaDescarte" class="btn btn-secondary btn-sl btn-block w-100" accesskey="D"><u>D</u>escartados</button>
			</div>

			<div class="col-12 col-sm-6 col-md-5 col-lg-3 col-xl-3">
				<button id="AdcionarCie" class="btn btn-secondary btn-sl btn-block w-100" accesskey="A"><u>A</u>dicionar</button>
			</div>
		</div>

		<div class="table-responsive">
			<table id="tblCiePrincipal"></table>
		</div>
	</div>
</div>

<div class="modal fade" id="divDescartar" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title col-11 text-center" id="exampleModalLabel">DESCARTAR DIAGNÓSTICO</h5>
			</div>

			<div id="divCapturaAgendaBody" class="modal-body">
				<form id="FormDescartar" name="FormDescartar" class="needs-validation" novalidate>
					<div class="row">
						<div class="col-md-12">
							<label id="lbltipoDescarte" for="seltipoDescarte" class="required">Tipo descarte</label>
							<div class="form-group">
								<select class="custom-select d-block w-100" id="seltipoDescarte" name="tipoDescarte">
								<option value=""></option>
								</select>
							</div>
							
							<div class="form-group">
								<label id="lblJustificacionDescarte" for="txtJustificacionDescarte" class="required">Justificación</label>
								<textarea class="form-control" id="txtJustificacionDescarte" name="JustificacionDescarte" rows="5"></textarea>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarDescarte"><u>C</u>ancelar</button>
				<button type="button" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Guardar información" aria-pressed="true" id="btnGuardaDescarte"><u>G</u>uardar</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript" src="vista-comun/js/listadiagnosticos.js"></script>
<script type="text/javascript" src="vista-comun/js/diagnosticos.js"></script>
