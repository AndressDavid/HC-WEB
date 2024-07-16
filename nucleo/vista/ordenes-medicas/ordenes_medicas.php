<?php
include __DIR__ . '/../comun/modalEspera.php';
include __DIR__ . '/../comun/modalAlertaNopos.php';
include __DIR__ . '/../comun/modalAlertaMipres.php';					// contiene cabecera.php
include __DIR__ . '/../comun/modalAlertaNoposIntranet.php';				// contiene cabecera.php
include __DIR__ . '/../comun/modalObservacionesCups.php';
include __DIR__ . '/../comun/modalOrdAmbPDF.php';
include __DIR__ . '/../comun/modalJustificacionPos.php';
include __DIR__ . '/../comun/modalHemocomponentes.php';
include __DIR__ . '/../comun/antecedentesConsulta.php';
include __DIR__ . '/../comun/modalMedicamentoControlado.php';			// contiene cabecera.php
include __DIR__ . '/../comun/modalJustificacionInmediato.php';
include __DIR__ . '/../comun/modalJustificacionUsoAntibiotico.php';		// contiene cabecera.php
include __DIR__ . '/../comun/modalMedicamentosSuspendidos.php';
?>

<div class="container-fluid small">

	<?php include __DIR__ . '/../comun/cabecera.php'; ?>

	<div class="form-row pb-2 pt-2">
		<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
			<div class="input-group input-group-sm">
				<label id="lblCieOrdenMedica" for="txtCieOrdenMedica" class="required">Diagnóstico Principal</label>
				<label> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </label>
				<input type="text" class="form-control form-control-sm font-weight-bold ignore col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" name="txtCieOrdenMedica" id="txtCieOrdenMedica" autocomplete="off">
			</div>
		</div>

		<div class="input-group input-group-sm">
			<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-1 col-xl-1" name="cCodigoCieOrdenMedica" id="cCodigoCieOrdenMedica" readonly="readonly">
			<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-11 col-xl-11" name="cDescripcionCieOrdenMedica" id="cDescripcionCieOrdenMedica" readonly="readonly">
		</div>
	</div>

	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
			<label for="txtOrdMedAntAlergicos">Antecedentes Alérgicos</label>
			<textarea class="form-control" id="txtOrdMedAntAlergicos" name="OrdMedAntAlergicos" rows="2" disabled></textarea>
		</div>

		<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
			<label for="txtOrdMedAntClinicoPat">Antecedentes Clínicos Patológicos</label>
			<textarea class="form-control" id="txtOrdMedAntClinicoPat" name="OrdMedAntClinicoPat" rows="2" disabled></textarea>
		</div>
	</div>

	<div class="card mt-3" id="divControlesOM">
		<div class="col">
			<ul class="nav nav-pills" id="tabOpcionesOrdenesMedicas" role="tablist">
				<li class="nav-item" role="presentation">
					<a class="text-dark nav-link active" id="tabOptOrdMedOxigeno" data-toggle="tab" href="#tabOrdenMedOxigeno" role="tab" aria-controls="a" aria-selected="true">Oxígeno - Glucometría</a>
				</li>
				<li class="nav-item" role="presentation">
					<a class="text-dark nav-link" id="tabOptOrdMedMedicamentos" data-toggle="tab" href="#tabOrdenMedMedicamentos" role="tab" aria-controls="a" aria-selected="true">Medicamentos</a>
				</li>
				<li class="nav-item" role="presentation">
					<a class="text-dark nav-link" id="tabOptOrdMedProcedimientos" data-toggle="tab" href="#tabOrdenMedProcedimientos" role="tab" aria-controls="b" aria-selected="false">Procedimientos</a>
				</li>

				<li class="nav-item" role="presentation">
					<a class="text-dark nav-link" id="tabOptOrdMedInterconsultas" data-toggle="tab" href="#tabOrdenMedInterconsultas" role="tab" aria-controls="d" aria-selected="false">Interconsultas</a>
				</li>

				<li class="nav-item" role="presentation">
					<a class="text-dark nav-link" id="tabOptOrdMedDieta" data-toggle="tab" href="#tabOrdenMedDieta" role="tab" aria-controls="e" aria-selected="false">Dietas</a>
				</li>

				<li class="nav-item" role="presentation">
					<a class="text-dark nav-link" id="tabOptOrdMedEnfermeria" data-toggle="tab" href="#tabOrdenMedEnfermeria" role="tab" aria-controls="h" aria-selected="false">Ordenes de Enfermería</a>
				</li>
			</ul>

			<div class="card border-top-1">
				<div class="tab-content" id="TabPropiedadesAmb">
					<div class="tab-pane fade show active" id="tabOrdenMedOxigeno" role="tabpanel" aria-labelledby="tabOptOrdMedOxigeno">
						<div class="card-body">
							<?php
								include __DIR__ .'/ordenes_oxigeno_glucomeria.php';
							?>
						</div>
					</div>

					<div class="tab-pane fade" id="tabOrdenMedMedicamentos" role="tabpanel" aria-labelledby="tabOptOrdMedMedicamentos">
						<div class="card-body">
							<?php
								include __DIR__ .'/ordenes_medicamentos.php';
							?>
						</div>
					</div>

					<div class="tab-pane fade" id="tabOrdenMedProcedimientos" role="tabpanel" aria-labelledby="tabOptOrdMedProcedimientos">
						<div class="card-body">
							<?php
								include __DIR__ .'/ordenes_procedimientos.php';
							?>
						</div>
					</div>

					<div class="tab-pane fade" id="tabOrdenMedInterconsultas" role="tabpanel" aria-labelledby="tabOptOrdMedInterconsultas">
						<div class="card-body">
							<?php
								include __DIR__ .'/ordenes_interconsulta.php';
							?>
						</div>
					</div>

					<div class="tab-pane fade" id="tabOrdenMedDieta" role="tabpanel" aria-labelledby="tabOptOrdMedDieta">
						<div class="card-body">
							<?php
								include __DIR__ .'/ordenes_dieta.php';
							?>
						</div>
					</div>

					<div class="tab-pane fade" id="tabOrdenMedEnfermeria" role="tabpanel" aria-labelledby="tabOptOrdMedEnfermeria">
						<div class="card-body">
							<?php
								include __DIR__ .'/ordenes_enfermeria.php';
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="card-footer text-muted fixed-bottom bg-light">
		<div class="row justify-content-between">
			<div class="col-auto">
				<button id="btncontrolGlucometriaOM" type="button" class="btn btn-secondary btn-sm" accesskey="L">G<u><b>l</b></u>ucometrías</button>
				<button id="btnAntecedentesOM" type="button" class="btn btn-secondary btn-sm" accesskey="A"><u><b>A</b></u>ntecedentes</button>
				<button id="btnEvolucionesOM" type="button" class="btn btn-secondary btn-sm" tabindex="1" accesskey="V">E<u><b>v</b></u>oluciones Anteriores</button>
				<button id="btnDatosPacienteOM" type="button" class="btn btn-secondary btn-sm" accesskey="T">Da<u><b>t</b></u>os Paciente</button>
				<button id="btnGuardarOrdenesMedicas" type="button" class="btn btn-danger btn-sm" disabled="disabled" accesskey="G"><u><b>G</b></u>uardar</button>
			</div>
			<div class="col-auto">
				<div class="row justify-content-end">
					<div class="col-auto">
						<button id="btnVolverOrdenesMed" type="button" class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Volver"><i class="fas fa-arrow-left"></i></button>
						<button id="btnVerPdfOrdenesMed" type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Ver PDF" disabled="disabled"><i class="fas fa-file-pdf"></i></button>
						<button id="btnVistaPreviaOrdenesMed" type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Vista Previa" disabled="disabled"><i class="fas fa-eye"></i></button>
						<button id="btnLibroMed" type="button" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Libro HC" ><i class="fas fa-book-medical"></i></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />
<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/3.1.3-dist/extensions/filter-control/bootstrap-table-filter-control.css">
<link rel="stylesheet" type="text/css" href="vista-comun/css/historiaclinica.css" />

<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/extensions/filter-control/bootstrap-table-filter-control.js"></script>

<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-autocomplete/2.3.7-dist/bootstrap-autocomplete.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-4-autocomplete/1.2.3/dist/bootstrap-4-autocomplete.min.js"></script>
<script type="text/javascript" src="vista-comun/js/listaPaquetes.js"></script>
<script type="text/javascript" src="vista-comun/js/modalVistaPrevia.js"></script>
<script type="text/javascript" src="vista-comun/js/diagnosticos.js"></script>
<script type="text/javascript" src="vista-comun/js/procedimientos.js"></script>
<script type="text/javascript" src="vista-comun/js/medicamentos.js"></script>
<script type="text/javascript" src="vista-ordenes-medicas/js/script.js"></script>
<script type="text/javascript" src="vista-comun/js/modalDatosPaciente.js"></script>
<script type="text/javascript" src="vista-comun/js/modalRespCup.js"></script>
<script type="text/javascript" src="vista-comun/js/alertaMalNutricion.js"></script>
<script type="text/javascript" src="vista-comun/js/tiposGeneros.js"></script>
<script type="text/javascript" src="vista-comun/js/micromedex.js"></script>
