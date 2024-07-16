<?php
include __DIR__ . '/../comun/modalEspera.php';
if($laEstados['Estado']==1){
	include __DIR__ . '/../comun/modalOrdAmbPDF.php';
}
?>

<div class="container-fluid small">

	<?php include __DIR__ . '/../comun/cabecera.php'; ?>

    <div class="card mt-3">
		<div class="card-body container-fluid">
			<div class="row">
				<div class="col-2">
					<div class="nav flex-sm-column nav-pills sticky-top" id="HC_MenuTabs" role="tablist" aria-orientation="vertical">
						<div class="pt-2"> </div>

						<a class="nav-link active text-dark" id="tabAnalisis" data-toggle="pill" href="#contentAnalisis" role="tab" aria-controls="contentAnalisis" aria-selected="true" data-focus="#edtAnalisis">
							<span class="fas fa-share-square"></span> Análisis Epicrisis
						</a>

						<a class="nav-link text-dark" id="tabDiagnostico" data-toggle="pill" href="#contentDiagnostico" role="tab" aria-controls="contentDiagnostico" data-focus="#txtCodigoCie">
							<span class="fas fa-file-alt"></span> Diagnóstico
						</a>

						<?php if($laEstados['Estado']==1){ ?>
						<a class="nav-link text-dark" id="tabOrdenes" data-toggle="pill" href="#contentOrdenes" role="tab" aria-controls="contentOrdenes" data-focus="#buscarMedicaAmb">
							<span class="fas fa-file-prescription"></span> Ordenes Ambulatorias
						</a>
						<?php } ?>

						<a class="nav-link text-dark" id="tabDatosEgreso" data-toggle="pill" href="#contentDatosEgreso" role="tab" aria-controls="contentDatosEgreso" data-focus="#edtCondiciones">
							<span class="fas fa-notes-medical"></span> Datos de Egreso
						</a>

					</div>
				</div>

				<div class="col-10" id="divControlesEPI">
					<div class="tab-content" id="v-pills-tabContent">

						<div class="tab-pane show active" id="contentAnalisis" role="tabpanel" aria-labelledby="tabAnalisis">
							<?php include __DIR__ .'/Analisis.php'; ?>
						</div>

						<div class="tab-pane" id="contentDiagnostico" role="tabpanel" aria-labelledby="tabDiagnostico">
							<?php include __DIR__ .'/../comun/diagnostico.php'; ?>
						</div>

						<div class="tab-pane" id="contentOrdenes" role="tabpanel" aria-labelledby="tabOrdenes">
						<?php
							if($laEstados['Estado']==1){
								include __DIR__ . '/../comun/ambulatorios.php';
							}
						?>
						</div>

						<div class="tab-pane $lcActiveo" id="contentDatosEgreso" role="tabpanel" aria-labelledby="tabDatosEgreso">
							<?php include __DIR__ .'/Egreso.php'; ?>
						</div>


					</div>
				</div>
			</div>
		</div>

		<div class="card-footer text-muted fixed-bottom bg-light">
			<div class="row justify-content-between">
				<div class="col">
					<button id="btnGuardarEPI" type="button" class="btn btn-danger btn-sm" accesskey="G" disabled="disabled"><u><b>G</b></u>uardar</button>
				</div>
				<div class="col-auto">
					<button id="btnVerPdfEPI" type="button" class="btn btn-danger btn-sm" accesskey="P" disabled="disabled">Ver <u><b>P</b></u>DF</button>
					<button id="btnVistaPrevia" type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Vista Previa" disabled="disabled"><i class="fas fa-eye"></i></button>
					<button id="btnLibroHC" type="button" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Libro HC"><i class="fas fa-book-medical"></i></button>
				</div>
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/1.18.3-dist/bootstrap-table.min.css" />
<link rel="stylesheet" type="text/css" href="vista-comun/css/historiaclinica.css" />

<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/localization/messages_es.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/1.18.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/1.18.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-4-autocomplete/1.2.3/dist/bootstrap-4-autocomplete.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-autocomplete/2.3.7-dist/bootstrap-autocomplete.min.js"></script>
<script type="text/javascript" src="vista-comun/js/modalVistaPrevia.js"></script>
<script type="text/javascript" src="vista-comun/js/listaPaquetes.js"></script>
<script type="text/javascript" src="vista-epicrisis/js/script.js"></script>
<script type="text/javascript" src="vista-comun/js/modalPlanesPaciente.js"></script>
<script type="text/javascript" src="vista-historiaclinica/js/TiposCausa.js"></script>
<script type="text/javascript" src="vista-comun/js/planespaciente.js"></script>
