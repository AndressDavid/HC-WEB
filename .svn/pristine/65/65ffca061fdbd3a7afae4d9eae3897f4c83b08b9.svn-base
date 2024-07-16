<?php
	require_once __DIR__ . '/../../controlador/class.Db.php';

	$lnIngreso = '';
	if (isset($_SESSION[HCW_DATA])) {
		if (isset($_SESSION[HCW_DATA]['ingreso'])) {
			$lnIngreso = $_SESSION[HCW_DATA]['ingreso'];
		}
		unset($_SESSION[HCW_DATA]);
	}
	if (empty($lnIngreso)) {
		$lnIngreso = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['ingreso'] ?? '');
	}
	if (isset($_SESSION[HCW_NAME])) {
		$lcUsuario = $_SESSION[HCW_NAME]->oUsuario->getUsuario();
		$lbPuedeExportar = !empty($goDb->obtenerTabMae1('CL3TMA', 'PDFUSU', "CL1TMA='EV0050' AND CL2TMA='USUARIOS' AND CL3TMA='$lcUsuario' AND ESTTMA=''", null, ''));
	} else {
		$lcUsuario = '';
		$lbPuedeExportar = false;
	}

	(new NUCLEO\Auditoria())->guardarAuditoria(intval($lnIngreso), 0, 0, '', 'CONSULTA_EVO_WEB', 'INICIO', 0, 'CONSULTA EVOLUCIONES INGRESO', 'EVOCONSUL', '', 0);
?>
<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<h5>Consulta Evoluciones</h5>
			<form id="frmFiltros" class="small">
				<div id="divFiltro" class="row">
					<div class="col-md-2 pb-2">
						<label for="Ingreso">Ingreso</label>
						<input type="number" id="Ingreso" name="Ingreso"  class="form-control form-control-sm"min="0" max="99999999" placeholder="" value="">
					</div>
					<div class="col-md-3 col-lg-2 pb-2">
						<label for="FechaDesde">Fecha Desde</label>
						<div class="input-group date" id="grpFechaDesde">
							<div class="input-group-prepend">
								<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
							</div>
							<input type="text" id="FechaDesde" name="FechaDesde" class="form-control form-control-sm" value=""  disabled>
						</div>
					</div>
					<div class="col-md-3 col-lg-2 pb-2">
						<label for="FechaHasta">Fecha Hasta</label>
						<div class="input-group date" id="grpFechaHasta">
							<div class="input-group-prepend">
								<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
							</div>
							<input type="text" id="FechaHasta" name="FechaHasta" class="form-control form-control-sm" value="" disabled></span>
						</div>
					</div>
					<div class="col-md-3 col-lg-2 pb-2">
						<label for="TodasFechas">Todas las fechas</label>
						<select id="TodasFechas" name="TodasFechas" class="form-control form-control-sm"  disabled>
							<option value="NO">NO</option>
							<option value="SI">SI</option>
						</select>
					</div>
					<div class="col-6 col-md-2 pb-2">
						<label for="btnBuscar">&nbsp;</label>
						<button id="btnBuscar" type="button" class="form-control form-control-sm btn btn-secondary btn-sm" accesskey="B" disabled><u>B</u>uscar</button>
					</div>
					<div class="col-6 col-md-2 pb-2">
						<label for="btnLimpiar">&nbsp;</label>
						<button id="btnLimpiar" type="button" class="form-control form-control-sm btn btn-secondary btn-sm" accesskey="L"><u>L</u>impiar</button>
					</div>
				</div>
			</form>
			<hr class="mt-1 mb-1">
			<div class="row">
				<div class="col">
					<div id="divIngresoInfo" class="small"></div>
				</div>
			</div>
			<div class="row">
				<div class="col">
					<div id="divFiltroInfo"></div>
				</div>
			</div>
		</div>

		<div class="card-body small">
			<nav>
				<div class="nav nav-tabs" id="tabResultado" role="tablist">
					<a class="text-dark nav-link active" id="pagEvolucionesTab" data-toggle="tab" href="#pagEvoluciones" role="tab" aria-controls="pagEvoluciones" aria-selected="true"><b>Evoluciones</b></a>
					<a class="text-dark nav-link" id="pagEscalasTab" data-toggle="tab" href="#pagEscalas" role="tab" aria-controls="pagEscalas" aria-selected="false"><b>Escalas</b></a>
				</div>
			</nav>

			<div class="tab-content" id="tabResultadoContent">

				<!-- CONSULTA EVOLUCIONES -->
				<div class="tab-pane fade show active" id="pagEvoluciones" role="tabpanel" aria-labelledby="pagEvolucionesTab">
					<div class="overflow-auto" id="divEvoluciones">
					</div>
				</div>


				<!-- ESCALAS -->
				<div class="tab-pane fade" id="pagEscalas" role="tabpanel" aria-labelledby="pagEscalasTab">

					<nav>
						<div class="nav nav-tabs mt-2" id="tabEscalas" role="tablist">
							<a class="text-dark nav-link active" id="pagHasbledTab" data-toggle="tab" href="#pagHasbled" role="tab" aria-controls="pagHasbled" aria-selected="true"><b>HASBLED</b></a>
							<a class="text-dark nav-link" id="pagChadsvasTab" data-toggle="tab" href="#pagChadsvas" role="tab" aria-controls="pagChadsvas" aria-selected="false"><b>CHA<sub>2</sub>DS<sub>2</sub>VAS</b></a>
							<a class="text-dark nav-link" id="pagCrusadeTab" data-toggle="tab" href="#pagCrusade" role="tab" aria-controls="pagCrusade" aria-selected="false"><b>CRUSADE</b></a>
						</div>
					</nav>

					<div class="tab-content" id="tabEscalasContent">

						<div class="tab-pane fade show active" id="pagHasbled" role="tabpanel" aria-labelledby="pagHasbledTab">
							<div class="container-fluid mt-3 mb-5" id="divEscalaHasbled">
								<?php include  __DIR__ . '/../comun/escala_hasbled.php'; ?>
							</div>
						</div>

						<div class="tab-pane fade" id="pagChadsvas" role="tabpanel" aria-labelledby="pagChadsvasTab">
							<div class="container-fluid mt-3 mb-5" id="divEscalaChadsvas">
								<?php include  __DIR__ . '/../comun/escala_chadsvas.php'; ?>
							</div>
						</div>

						<div class="tab-pane fade" id="pagCrusade" role="tabpanel" aria-labelledby="pagCrusadeTab">
							<div class="container-fluid mt-3 mb-5" id="divEscalaCrusade">
								<?php include  __DIR__ . '/../comun/escala_crusade.php'; ?>
							</div>
						</div>
					</div>

				</div>

			</div>
		</div>
	</div>

	<div class="card-footer text-muted fixed-bottom bg-light small">
		<div class="row justify-content-between">
			<div class="col">
				<div class="dropdown">
					<?php if ($lbPuedeExportar): ?>
						<button id="btnMenuExportar" class="btn btn-secondary dropdown-toggle btn-sm" type="button" data-toggle="dropdown" aria-expanded="false" disabled>Exportar PDF</button>
						<div class="dropdown-menu" aria-labelledby="btnMenuExportar">
							<a class="dropdown-item btn-exportar" data-tipo="EVOLUCION" href="#">Evoluciones</a>
							<a class="dropdown-item btn-exportar" data-tipo="ENFNOTAS"  href="#">Notas Enfermería</a>
							<a class="dropdown-item btn-exportar" data-tipo="ENFBALLIQ" href="#">Balance de Líquidos</a>
							<a class="dropdown-item btn-exportar" data-tipo="ENFADMMED" href="#">Administración Medicamentos</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="col-auto">
				<button id="btnVerPdf" type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Evolución por Número" disabled="disabled"><i class="fas fa-file-pdf"></i> PDF EVolución</button>
				<button id="btnLibroHC" type="button" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Libro HC" disabled><i class="fas fa-book-medical"></i></button>
			</div>
		</div>
	</div>

</div>

<div class="position-fixed top-0 left-0 p-3" style="left: 0; top: 0;">
	<div class="toast" id="toastEvo" data-delay="5000">
		<div class="toast-header">
			<strong class="mr-auto"><b>Consulta Evoluciones</b></strong>
			<button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="toast-body">
			<b>Predeterminado se consulta los últimos 7 días</b>
		</div>
	</div>
</div>



<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />
<link rel="stylesheet" type="text/css" href="vista-comun/css/modalVistaPrevia.css" />
<style>
	.nav-tabs .nav-link.active {
		background-color: lightgrey;
	}
</style>


<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/localization/messages_es.min.js"></script>
<script type="text/javascript" src="vista-evoconsulta/js/script.js"></script>
<script type="text/javascript" src="vista-comun/js/cabecera.js"></script>
<script type="text/javascript" src="vista-comun/js/comun.js"></script>

<script type="text/javascript">
	var gnIngreso='<?= $lnIngreso ?>';
</script>
