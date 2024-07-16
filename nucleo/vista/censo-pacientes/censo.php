<?php
	$lcTipo= trim(strtolower($_GET['tcen']??'urg'));
	$laRetorna['cTipId'] = '';
	$laRetorna['nNumId'] = 0;
	$laAuditoria['cUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getUsuario();
	$laAuditoria['cEspUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getEspecialidad();
	$laAuditoria['cTipopUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getTipoUsuario(false);
	$laAuditoria['cEspUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getEspecialidad();

	include __DIR__ . '/../comun/antecedentesConsulta.php';
	include __DIR__ . '/../comun/modalObservaciones.php';
	include __DIR__ . '/../comun/modalTrasladosPacientes.php';
?>

<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<div class="row">
				<div class="col">
					<h5 id="idTituloCenso" >Censo <?php echo ($lcTipo=='urg' ? 'Urgencias' : 'Hospitalización'); ?></h5>
				</div>
			</div>
			<form id="frmFiltros">
				<div id="divFiltro" class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 col-xl-2">
						<label for="txtIngreso"><b>Ingreso</b></label>
						<input id="txtIngreso" type="number" class="form-control form-control-sm" name="txtIngreso" maxlength="8" min="0" max="99999999" placeholder="" value="<?php echo $lnIngreso??0; ?>">
					</div>

					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-5 col-xl-4">
						<label for="selSeccionCU" class="control-label">Sección</label>
						<select name="SeccionCU" id="selSeccionCU" class="form-control form-control-sm"><option value=""></option></select>
					</div>

					<div id="divTextoUrgencias" class="col-xs-12 col-sm-12 col-md-12 col-lg-3 col-xl-2" style="display: none;">
						<label for="selSeccionUrgencias" class="control-label">Ubicación</label>
						<select name="SeccionCU" id="selSeccionUrgencias" class="form-control form-control-sm"><option value=""></option></select>
					</div>

					<div id="divSinHabitacion" class="col-xs-12 col-sm-12 col-md-12 col-lg-3 col-xl-2" style="display: none;">
						<label for="selSinHabitacionUrgencias" class="control-label">Sin hab.</label>
						<select name="SinHabitacionUrgencias" id="selSinHabitacionUrgencias" class="form-control form-control-sm">
							<option value=""></option>
							<option value="S">SI</option>
						</select>
					</div>

					<div id="divSinHabitacion" class="col-xs-12 col-sm-12 col-md-12 col-lg-3 col-xl-2">
						<label for="selGeneroUrgencias" class="control-label">Género</label>
						<select name="GeneroUrgencias" id="selGeneroUrgencias" class="form-control form-control-sm">
							<option value=""></option>
						</select>
					</div>

					<div id="divPacienteTipo" class="col-xs-12 col-sm-12 col-md-12 col-lg-3 col-xl-3" style="display: none;">
						<label for="selPacienteTipoUrgencias" class="control-label">Tipo paciente</label>
						<select name="PacienteTipoUrgencias" id="selPacienteTipoUrgencias" class="form-control form-control-sm">
							<option value=""></option>
						</select>
					</div>

					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-6 col-xl-4">
						<label for="txtFechaIni">Fecha</label>
						<div class="form-inline row">
							<div class="form-group col-6 pr-0">
								<div class="input-group input-group-sm date w-100">
									<div class="input-group-prepend">
										<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
									</div>
									<input type="text" class="form-control" id="txtFechaIni" required="required" value="<?php print($ldFechaInicio); ?>">
								</div>
							</div>
							<div class="form-group col-6 pl-1">
								<div class="input-group input-group-sm date w-100">
									<div class="input-group-prepend">
										<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
									</div>
									<input type="text" class="form-control" id="txtFechaFin" required="required" value="<?php print($ldFechaFin); ?>">
								</div>
							</div>
						</div>
					</div>
					<div class="col-xs-6 col-md-4 col-lg-2 col-xl-1" style="padding-left: 1px;padding-right: 1px;">
						<label for="btnBusca" class="control-label"> &nbsp;  &nbsp;  &nbsp; &nbsp;  &nbsp;  &nbsp; </label>
						<button id="btnBuscar" type="button" class="form-control-sm btn btn-secondary btn-sm w-100" accesskey="B"><u>B</u>uscar</button>
					</div>
					<div class="col-xs-6 col-md-4 col-lg-2 col-xl-1" style="padding-left: 1px;padding-right: 1px;">
						<label for="btnConvencionCen" class="control-label"> &nbsp;  &nbsp;  &nbsp; &nbsp; </label>
						<button id="btnConvencionCenso" type="button" class="form-control-sm btn btn-warning btn-sm w-100">Convención</button>
					</div>

				</div>
			</form>
		</div>

		<div class="card-body">
			<div class="row">
				<div class="col-12">
					<small><table id="tblCensoPacientes"></table></small>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="divCensoUrgencias" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header badge-light">
				<h5 class="modal-title col-11 text-center" id="modalCensoUrgencias">CENSO URGENCIAS</h5>
			</div>

			<div id="divCapturaAgendaBody" class="modal-body">
				<form id="FormCensoUrgencias" name="FormCensoUrgencias" class="needs-validation" novalidate>
					<div class="row">
						<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
							<label id="lblHistoricoCenso" for="txtHistoricoCenso">Registros anteriores</label>
							<textarea class="form-control" id="txtHistoricoCenso" name="HistoricoCenso" rows="8" disabled></textarea>
						</div>
					</div>

					<div class="row">
						<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
							<label id="lblRegistrarCenso" for="txtRegistrarCenso" >Registrar</label>
							<textarea class="form-control" id="txtRegistrarCenso" name="RegistrarCenso" rows="4" autofocus disabled></textarea>
						</div>
					</div>

				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Guardar información" aria-pressed="true" id="btnGuardaCensoUrgencias" style="display: none;" disabled>Guardar</button>
				<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarCensoUrgencias">Cancelar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="divAlertaTempranaCensoU" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header badge-light">
				<h5 class="modal-title col-11 text-center" id="modalCensoUrgenciasAlertaT">CENSO URGENCIAS</h5>
			</div>

			<div id="divCapturaAgendaBody" class="modal-body">
				<form id="FormAlertaTempranaCensoU" name="FormAlertaTempranaCensoU" class="needs-validation" novalidate>
					<div class="form-row pb-2">
						<div class="col-12 col-sm-12 col-md-12 col-lg-2 col-xl-2 mt-2">
							<label id="lblselAltaTempranaCensoU" for="selAltaTempranaCensoU">Alta temprana: </label>
						</div>

						<div class="col-12 col-sm-12 col-md-12 col-lg-10 col-xl-10">
							<select id="selAltaTempranaCensoU" name="AltaTempranaCensoU" class="custom-select" required disabled>
							  <option value=""></option>
							</select>
						</div>
					</div>

					<div class="form-row">
						<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
							<div class="row">
								<div class="col-12 col-sm-12 col-md-8 col-lg-10 col-xl-10">
									<textarea class="form-control" id="txtAltaTempranaCensoUrg" name="AltaTempranaCensoUrg" rows="2" disabled></textarea>
								</div>
								<div class="col-12 col-sm-12 col-md-4 col-lg-2 col-xl-2 col align-self-end">
									<button id="AdicionarAltaTempranaU" class="btn btn-secondary btn-sl btn-block" accesskey="A" disabled><u>A</u>dicionar</button>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="card-body small">
				<table id="tblAltasTempranas"></table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Guardar información" aria-pressed="true" id="btnGuardaAlertaTemCenso" disabled>Guardar</button>
				<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarAlertaTemCenso">Cancelar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="divSeccionesUrgenciasCensoU" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="card-body small">
				<table id="tblSeccionUrgencias"></table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Selección secciones" aria-pressed="true" id="btnSelSeccionesUrgencias">Seleccionar</button>
				<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarSeccionesUrgencias">Cancelar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="divSeccionesMedicosUrg" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header badge-light">
				<h5 class="modal-title col-11 text-center" id="modalSeccionesMedicosUrg">UBICACION PACIENTE</h5>
			</div><br>

			<div id="divCapturaAgendaBody" class="modal-body">
				<div class="form-row pb-2">
					<div class="col-12 col-sm-12 col-md-6 col-lg-1 col-xl-1">
						<label id="lblUbicacionActual" for="selUbicacionActualMed">Actual</label>
					</div>
					<div class="col-12 col-sm-12 col-md-6 col-lg-11 col-xl-11">
						<select id="selUbicacionActualMed" name="UbicacionActualMed" class="custom-select" required disabled>
						  <option value=""></option>
						</select>
					</div>
				</div>

				<div class="form-row pb-2">
					<div class="col-12 col-sm-12 col-md-6 col-lg-1 col-xl-1">
						<label id="lblNuevaUbicacionMed" for="selNuevaUbicacionMed">Nueva</label>
					</div>
					<div class="col-12 col-sm-12 col-md-6 col-lg-11 col-xl-11">
						<select id="selNuevaUbicacionMed" name="selNuevaUbicacionMed" class="custom-select" required>
						  <option value=""></option>
						</select>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Selección secciones" aria-pressed="true" id="btnGuardarSeccionMedico">Guardar</button>
					<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarSeccionMedico">Cancelar</button>
				</div>
			</div>

		</div>
	</div>
</div>

<div class="modal fade" id="divTipoPacienteCensoUrg" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header badge-light">
				<h5 class="modal-title col-11 text-center" id="modalTipoPacienteCensoUrg">Tipo de Paciente en <?php echo ($lcTipo=='urg' ? 'Urgencias' : 'Hospitalización'); ?></h5>
			</div><br>

			<div id="divCapturaAgendaBody" class="modal-body">
				<div class="form-row pb-2">

					<div class="col-12 col-sm-12 col-md-6 col-lg-12 col-xl-12">
						<select id="selTipoPacienteCenso" name="TipoPacienteCenso" class="custom-select" required>
						  <option value=""></option>
						</select>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Selección secciones" aria-pressed="true" id="btnGuardarTipoPaciente">Guardar</button>
					<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarSeccionMedico">Cancelar</button>
				</div>
			</div>

		</div>
	</div>
</div>

<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css"/>
<link rel="stylesheet" type="text/css" media="screen" href="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />
<script type="text/javascript" src="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
<script type="text/javascript" src="publico-complementos/table-export/1.10.16/tableExport.min.js"></script>
<script type="text/javascript" src="publico-complementos/table-export/1.10.16/libs/js-xlsx/xlsx.core.min.js"></script>
<script type="text/javascript" src="publico-complementos/table-export/1.10.16/libs/js-xlsx/xlsx.core.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/extensions/export/bootstrap-table-export.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="publico-complementos/moment-develop/2.29.1-dist/moment.min.js"></script>
<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="vista-comun/js/tiposGeneros.js"></script>
<script type="text/javascript" src="vista-comun/js/tiposDocumentos.js"></script>
<script type="text/javascript" src="vista-historiaclinica/js/validarpacientehc.js"></script>
<script type="text/javascript" src="vista-historiaclinica/js/menuOpciones.js"></script>
<script type="text/javascript" src="vista-censo-pacientes/js/script.js"></script>
<script type="text/javascript" src="vista-comun/js/modalVistaPrevia.js"></script>
<script type="text/javascript" src="vista-comun/js/modalRespCup.js"></script>
<script type="text/javascript" src="vista-comun/js/modalObservaciones.js"></script>

<script type="text/javascript">
	var aDatosCenso		= btoObj('<?= base64_encode(json_encode($lcTipo)) ?>');
	var aDatosIngreso	= btoObj('<?= base64_encode(json_encode($laRetorna)) ?>');
	var aAuditoria		= btoObj('<?= base64_encode(json_encode($laAuditoria)) ?>');
</script>