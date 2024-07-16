<?php
include __DIR__ . '/../comun/textoInformativo.php';
include __DIR__ . '/../comun/modalEspera.php';
require_once __DIR__ . '/../../vista/comun/modalAlertaFallece.php';
require_once __DIR__ . '/../../vista/comun/modalOrdenHospitalizacion.php';
require_once __DIR__ . '/../../vista/comun/modalDiagnosticosDescartados.php';
?>

<div class="container-fluid small">

	<?php include __DIR__ . '/../comun/cabecera.php'; ?>

	<div class="card mt-3">

		<div class="card-body container-fluid">

			<div class="row">

				<div class="col-2">
					<?php
						$lcDxPPal = $lcDxOtros = '' ;
						$lcSL = "\n";
						foreach ($laRetorna['DxPpal'] as $laDx) {
							if ($laDx['TIPO']=='1'){
								$lcDxPPal = $laDx['DIAGNOSTICO'] . ' - ' . $laDx['DESCRIPCION_CIE'] ;
							}else{
								$lcDxOtros .= ' * ' . $laDx['DIAGNOSTICO'] . ' - ' . $laDx['DESCRIPCION_CIE'] . '<br>'. '<br>' ;								
							}
						}
					?>
					<div class="nav flex-sm-column nav-pills sticky-top" id="EV_MenuTabs" role="tablist" aria-orientation="vertical">
						<div class="pt-2"> </div><br>
						<?php echo $lcMenu; ?><br>
						<div class="card-header" style="z-index:12" role="alert"><h6 class="text">Diagnósticos</h6></div>
						<div class="card-header" style="z-index:12" role="alert"><h6 class="text"><?php echo $lcDxPPal?></h6></div>
						<?php if ($lcDxOtros !== ''): ?>
							<div class="card-header" style="z-index:12" role="alert"><class="text"><?php echo $lcDxOtros?></div>
						<?php endif; ?>
					</div><br>
				</div>

				<div class="col-10" id="divControlesEV">
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
				<div class="col-auto">
					<button id="btnEvoluciones" type="button" class="btn btn-secondary btn-sm" tabindex="1" accesskey="A"><u><b>E</b></u>voluc. Anteriores</button>
					<button id="btnTextoInf" type="button" class="btn btn-secondary btn-sm" tabindex="1" accesskey="I"><u><b>T</b></u>exto Pandemia</button>
					<button id="btnDatosPacienteEV" type="button" class="btn btn-secondary btn-sm">Datos Paciente</button>
					<button id="btnGuardarEV" type="button" class="btn btn-danger btn-sm" tabindex="3" accesskey="G" disabled="disabled"><u><b>G</b></u>uardar</button>
				</div>

				<div class="col-auto">
					<div class="row justify-content-end">
						<div class="col-auto">
							<button id="btnVolverEV" type="button" class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Volver"><i class="fas fa-arrow-left"></i></button>
							<button id="btnVerPdfEV" type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Ver PDF" disabled="disabled"><i class="fas fa-file-pdf"></i></button>
							<button id="btnVistaPreviaEV" type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Vista Previa" disabled="disabled"><i class="fas fa-eye"></i></button>
							<button id="btnLibroEV" type="button" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Libro HC" ><i class="fas fa-book-medical"></i></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />
<link rel="stylesheet" type="text/css" href="vista-comun/css/historiaclinica.css" />

<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/localization/messages_es.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-4-autocomplete/1.2.3/dist/bootstrap-4-autocomplete.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-autocomplete/2.3.7-dist/bootstrap-autocomplete.min.js"></script>
<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="publico-complementos/dropdowntree/1.1.1/dropdowntree.min.js"></script>
<script type="text/javascript" src="vista-comun/js/modalVistaPrevia.js"></script>
<script type="text/javascript" src="vista-comun/js/modalDatosPaciente.js"></script>
<script type="text/javascript" src="vista-comun/js/nocopypaste.js"></script>
<script type="text/javascript" src="vista-evoluciones/js/script.js"></script>
<script type="text/javascript" src="vista-comun/js/procedimientos.js"></script>
<script type="text/javascript" src="vista-comun/js/medicamentos.js"></script>
<script type="text/javascript" src="vista-ordenes-medicas/js/ordenes_interconsultas.js"></script>
<script type="text/javascript" src="vista-comun/js/tiposGeneros.js"></script>

<?php if ($laRetorna['Avalar']): ?>
	<script type="text/javascript" src="vista-comun/js/Aval.js"></script>
<?php endif; ?>

<?php if ($laAuditoria['lRequiereAval']): ?>
	<script type="text/javascript">
		$("#divCabDatosPac").prepend('<div class="row"><div class="col-12"><div class="alert-danger p-1" style="z-index:12" role="alert"><h6 class="text-center">ESTA EVOLUCION REQUIERE SER AVALADA</h6></div></div></div>')
	</script>
<?php endif; ?>
