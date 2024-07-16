<?php

require_once __DIR__ . '/../../controlador/class.Historia_Clinica_Ingreso.php';
require_once __DIR__ . '/../../vista/comun/modalPlanesPaciente.php';

$lcTipoIde = (isset($_POST['t'])?$_POST['t']:'');
$lnNroIde = intval(isset($_POST['n'])?$_POST['n']:0);
$lnIngreso = 0;

$laIngresoUltimo = $goDb
	->select('NIGING INGRESO')
	->from('RIAING')
	->where('TIDING', '=', $lcTipoIde)
	->where('NIDING', '=', $lnNroIde)
	->orderBy('NIGING DESC')
	->get('array');
if (is_array($laIngresoUltimo)) {
	$lnIngreso = $laIngresoUltimo['INGRESO'];
}

(new NUCLEO\Auditoria())->guardarAuditoria($lnIngreso, 0, 0, '', 'ORDAMB_WEB', 'INI_ORDAMB', 0, 'INGRESO ORDEN AMBULATORIA', 'ORDAMB', $lcTipoIde, $lnNroIde);

$loIngreso = new NUCLEO\Historia_Clinica_Ingreso() ;
$laRetorna = $loIngreso->datosIngreso($lnIngreso);
$laRetorna = array_merge($laRetorna,[
	'cCodigoPlan' => $laRetorna['cPlan'] ?? '',
	'cCodigoVia' => $laRetorna['cCodVia'] ?? '',
]);
unset($laIngresoUltimo);

include __DIR__ . '/../comun/modalEspera.php';
include __DIR__ . '/../comun/modalAlertaNopos.php';
include __DIR__ . '/../comun/modalAlertaNoposIntranet.php';

?>

<div class="container-fluid small">

	<?php include __DIR__ . '/../comun/cabecera.php'; ?>

	<div class="card mt-3">
		<div class="card-body container-fluid" id="divControlesOA">
			<form role="form" id="FormOrdAmbulatoriaPac" name="FormOrdAmbulatoriaPac" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
				<div class="form-row pb-2">
					<div class="col-12">
						<div class="input-group input-group-sm">
							<label id="lblcodigoCieOrdAmb" for="txtcodigoCieOrdAmb" class="required">Diagnóstico</label>
							<label> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </label>
							<input type="text" class="form-control form-control-sm font-weight-bold ignore col-12" name="txtcodigoCieOrdAmb" id="txtcodigoCieOrdAmb" autocomplete="off">
						</div>
					</div>

					<div class="input-group input-group-sm">
						<input type="text" class="form-control form-control-sm font-weight-bold col-4 col-sm-3 col-md-2  col-lg-1" name="cCodigoCieOrdAmb" id="cCodigoCieOrdAmb" readonly="readonly">
						<input type="text" class="form-control form-control-sm font-weight-bold col-8 col-sm-9 col-md-10 col-lg-11" name="cDescripcionCieOrdAmb" id="cDescripcionCieOrdAmb" readonly="readonly">
					</div>
				</div>

				<div class="form-row pb-2">
					<div class="col-12 col-lg-6 col-xl-6">
						<label id="lblPlanOrdAmb" for="selPlanOrdAmb">Plan </label>
						<div class="form-group">
							<select class="custom-select custom-select-sm d-block w-100" id="selPlanOrdAmb" name="PlanOrdAmb" disabled>
								<option value=""></option>
							</select>
						</div>
					</div>

					<div class="col-12 col-lg-3 col-xl-3">
						<label id="lblViaOrdAmb" for="selViaOrdAmb">Vía</label>
						<div class="form-group">
							<select class="custom-select custom-select-sm d-block w-100" id="selViaOrdAmb" name="ViaOrdAmb">
								<option value=""></option>
							</select>
						</div>
					</div>

					<div class="col-12 col-lg-3 col-xl-3">
						<label id="lblPrioridadAtencionOrdAmb" for="selPrioridadAtencionOrdAmb">Prioridad Atención </label>
						<div class="form-group">
							<select class="custom-select custom-select-sm d-block w-100" id="selPrioridadAtencionOrdAmb" name="PrioridadAtencionOrdAmb">
								<option value=""></option>
							</select>
						</div>
					</div>
				</div>

				<div class="form-row pb-1">
					<div class="col-12 col-lg-6 col-xl-4">
						<label id="lblModalidadPrestacion" for="selModalidadPrestacion">Modalidad Prestación</label>
						<div class="form-group mb-1">
							<select class="custom-select custom-select-sm d-block w-100" id="selModalidadPrestacion" name="ModalidadPrestacion">
								<option value=""></option>
							</select>
						</div>
					</div>
				</div>

			</form>
			<div class="row">
				<?php
					include __DIR__ .'/../comun/ambulatorios.php';
				?>
			</div>
		</div>

		<div class="card-footer text-muted fixed-bottom bg-light">
			<div class="row justify-content-between">
				<div class="col-auto">
					<button id="btnGuardarOrdenesAmb" type="button" class="btn btn-danger btn-sm" disabled="disabled" accesskey="G"><u>G</u>uardar</button>
				</div>
				<div class="col-auto">
					<div class="row justify-content-end">
						<div class="col-auto">
							<button id="btnVolverAmb" type="button" class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Volver"><i class="fas fa-arrow-left"></i></button>
							<button id="btnVerPdfAmb" type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Ver PDF" disabled="disabled"><i class="fas fa-file-pdf"></i></button>
							<button id="btnVistaPreviaAmb" type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Vista Previa" disabled="disabled"><i class="fas fa-eye"></i></button>
						</div>
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
<script type="text/javascript" src="publico-complementos/bootstrap-4-autocomplete/1.2.3/dist/bootstrap-4-autocomplete.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-autocomplete/2.3.7-dist/bootstrap-autocomplete.min.js"></script>
<script type="text/javascript" src="vista-comun/js/diagnosticos.js"></script>
<script type="text/javascript" src="vista-comun/js/tiposDocumentos.js"></script>
<script type="text/javascript" src="vista-comun/js/ambulatorios.js"></script>
<script type="text/javascript" src="vista-comun/js/listaPaquetes.js"></script>
<script type="text/javascript" src="vista-ordenes-ambulatorias/js/ordenes_ambulatorias.js"></script>
<script type="text/javascript" src="vista-historiaclinica/js/TiposCausa.js"></script>
<script type="text/javascript" src="vista-comun/js/modalVistaPrevia.js"></script>
<script type="text/javascript" src="vista-comun/js/micromedex.js"></script>

<script type="text/javascript">
	<?php echo 'var aDatosIngreso = btoObj(\'' . base64_encode(json_encode($laRetorna)) . '\');' . PHP_EOL;?>
</script>