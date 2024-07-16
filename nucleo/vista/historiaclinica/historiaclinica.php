<?php
include __DIR__ . '/../comun/modalEspera.php';
include __DIR__ . '/../comun/modalAlertaNopos.php';
include __DIR__ . '/../comun/antecedentesConsulta.php';
include __DIR__ . '/../comun/textoInformativo.php';
include __DIR__ . '/modal_irag.php';
include __DIR__ . '/../comun/modalPlanesPaciente.php';
include __DIR__ . '/../comun/modalAlertaNoposIntranet.php';

if ($laRetorna['cCodVia']=='02' || $lbCondicionCE) {
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
						<?php echo $lcMenu; ?>
					</div>
				</div>


				<div class="col-10" id="divControlesHC">
					<div class="tab-content" id="v-pills-tabContent">
					<?php
						foreach ($laObjetos as $lcObject) {
							echo $lcObject[0];
							foreach ($lcObject[1] as $lcInclude) {
								include __DIR__ . $lcInclude;
							}
							echo $lcObject[2];
						}
					?>
					</div>
				</div>
			</div>
		</div>

		<div class="card-footer text-muted fixed-bottom bg-light">
			<div class="row justify-content-between">
				<div class="col">
					<?php if ($lcTeleconsulta=='S') {?>
						<button id="btnTeleconsulta" type="button" class="btn btn-secondary btn-sm">Telemedicina</button>
					<?php } ?>
					<?php if ($lcVia=='01') {?>
						<button id="btnTriage" type="button" class="btn btn-secondary btn-sm">Triage</button>
					<?php } ?>
					<button id="btnAntecedentes" type="button" class="btn btn-secondary btn-sm">Antecedentes</button>
					<button id="btnTextoInf" type="button" class="btn btn-secondary btn-sm">Texto Pandemia</button>
					<button id="btnDatosPaciente" type="button" class="btn btn-secondary btn-sm">Datos Paciente</button>
					<button id="btnGuardarHC" type="button" class="btn btn-danger btn-sm" disabled="disabled">Guardar</button>
				</div>
				<div class="col-auto">
					<button id="btnVolver" type="button" class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Volver"><i class="fas fa-arrow-left"></i></button>
					<button id="btnVerPdfHC" type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Ver PDF" disabled="disabled"><i class="fas fa-file-pdf"></i></button>
					<button id="btnVistaPrevia" type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Vista Previa" disabled="disabled"><i class="fas fa-eye"></i></button>
					<button id="btnLibroHC" type="button" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Libro HC" disabled="disabled"><i class="fas fa-book-medical"></i></button>
				</div>
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/1.18.3-dist/bootstrap-table.min.css" />
<link rel="stylesheet" type="text/css" media="screen" href="publico-complementos/dropdowntree/1.1.1/dropdowntree.css" />
<link rel="stylesheet" type="text/css" href="vista-comun/css/historiaclinica.css" />

<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/localization/messages_es.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/1.18.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/1.18.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-4-autocomplete/1.2.3/dist/bootstrap-4-autocomplete.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-autocomplete/2.3.7-dist/bootstrap-autocomplete.min.js"></script>
<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="publico-complementos/dropdowntree/1.1.1/dropdowntree.min.js"></script>
<script type="text/javascript" src="vista-comun/js/nocopypaste.js"></script>
<script type="text/javascript" src="vista-comun/js/listadiagnosticos.js"></script>
<script type="text/javascript" src="vista-comun/js/listaPaquetes.js"></script>
<script type="text/javascript" src="vista-comun/js/modalVistaPrevia.js"></script>
<script type="text/javascript" src="vista-comun/js/modalDatosPaciente.js"></script>
<script type="text/javascript" src="vista-comun/js/modalPlanesPaciente.js"></script>
<script type="text/javascript" src="vista-comun/js/planespaciente.js"></script>
<script type="text/javascript" src="vista-comun/js/formulas.js"></script>
<script type="text/javascript" src="vista-comun/js/medicamentos.js"></script>
<script type="text/javascript" src="vista-comun/js/tiposGeneros.js"></script>
<script type="text/javascript" src="vista-historiaclinica/js/scripts.js"></script>
<script type="text/javascript" src="vista-historiaclinica/js/TiposCausa.js"></script>

<?php
	if ($laRetorna['Avalar']) {
		echo '<script type="text/javascript" src="vista-comun/js/Aval.js"></script>';
	}
	if ($lcVia=='01') {
		echo '<script type="text/javascript" src="vista-censourg/js/registro.js"></script>';
		echo '<script type="text/javascript" src="vista-comun/js/listaMedicos.js"></script>';
	}
	include __DIR__ . '/../comun/js/usuario.js.php';
	$laFechaHoy = array_map('intval',explode('-',date("Y-m-d")));
	echo '<script type="text/javascript">var gdFechaHoy = new Date('.$laFechaHoy[0].','.($laFechaHoy[1]-1).','.$laFechaHoy[2].');</script>';
?>

<?php if ($laAuditoria['lRequiereAval']): ?>
	<script type="text/javascript">
		$("#divCabDatosPac").prepend('<div class="row"><div class="col-12"><div class="alert-danger p-1" style="z-index:12" role="alert"><h6 class="text-center">ESTA HISTORIA CLINICA REQUIERE SER AVALADA</h6></div></div></div>')
	</script>
<?php endif; ?>