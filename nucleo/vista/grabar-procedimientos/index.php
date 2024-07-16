<?php
	
	//require_once __DIR__ . '/../../controlador/class.AplicacionFunciones.php';

	$laAuditoria['cUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getUsuario();
	$laAuditoria['cEspUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getEspecialidad();
	$laAuditoria['cTipopUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getTipoUsuario(false);
	$laAuditoria['cEspUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getEspecialidad();

	$laRetorna = [
		'cSexo' => '',
		'aEdad' => '',
	];
?>

<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-body container-fluid" id="divGrabacion">
			<div class="row">
				<div class="col">
					<h5 id="idTituloGrabarProcedimientos" >Grabar procedimientos</h5>
				</div>
			</div>

			<form role="form" id="frmGrabarProcedimiento" name="frmGrabarProcedimiento" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
				<div id="divrabarProcedimiento" class="row">
					<div class="col-sm-12 col-md-12 col-lg-3 col-xl-2">
						<label id="lblNumeroIngreso" for="txtNumeroIngreso" class="control-label required">Número ingreso</label>
						<div class="input-group mb-3">
						<input id="txtNumeroIngreso" type="number" class="form-control form-control-sm" name="numeroIngreso" maxlength="8" min="0" max="99999999" placeholder="" value="">
						</div>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 col-xl-2">
						<label id="lblIdentificacioPaciente" for="txtIdentificacioPaciente">Identificación</label>
						<input id="txtIdentificacioPaciente" type="text" class="form-control form-control-sm" name="identificacioPaciente" value="" disabled>
					</div>

					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 col-xl-4">
						<label id="lblNombrePaciente" for="txtNombrePaciente">Nombre Paciente</label>
						<input id="txtNombrePaciente" type="text" class="form-control form-control-sm" name="nombrePaciente" value="" disabled>
					</div>
					
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 col-xl-2">
						<label id="lblGeneroPaciente" for="txtGeneroPaciente">Género</label>
						<input id="txtGeneroPaciente" type="text" class="form-control form-control-sm" name="generoPaciente" value="" disabled>
					</div>
					
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 col-xl-2">
						<label  id="lblViaIngreso" for="txtViaIngreso">Vía de ingreso</label>
						<input id="txtViaIngreso" type="text" class="form-control form-control-sm" name="viaIngreso" value="" disabled>
					</div>
					
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
						<label for="cProcedimientoBuscar" class="required">Procedimiento</label>
						<div class="input-group input-group-sm">
							<div class="input">
							</div>
							<input type="text" class="form-control form-control-sm font-weight-bold ignore" name="cProcedimientoBuscar" id="cProcedimientoBuscar" autocomplete="off" disabled>
						</div>
						
						<div class="input-group input-group-sm">
							<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-1 col-xl-1" name="codigoProcedimiento" id="codigoProcedimiento" readonly="readonly">
							<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-9 col-xl-9" name="descripcionProcedimiento" id="descripcionProcedimiento" readonly="readonly">
							<label> &nbsp; &nbsp; </label><label for="cantidadProcedimiento" class="required">Cantidad</label>
							<input type="number" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-2 col-xl-2" id="cantidadProcedimiento" name="cantidadProcedimiento" min="0" max="90" value="" required disabled>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-6 col-lg-3 col-xl-4">
						<label id="lblCentroServicioCups" for="selCentroServicioCups" class="required">Centro de Servicio</label>
						<div class="form-group">
							<select class="custom-select d-block w-100" id="selCentroServicioCups" name="centroServicioCups" required disabled>
							</select>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-6 col-lg-3 col-xl-4">
						<label id="lblCausaExternaCups" for="selCausaExternaCups" class="required">Causa Externa</label>
						<div class="form-group">
							<select class="custom-select d-block w-100" id="selCausaExternaCups" name="causaexternacups" required disabled>
							</select>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-6 col-lg-3 col-xl-4">
						<label id="lblFinalidadCups" for="selFinalidadCups" class="required">Finalidad procedimiento</label>
						<div class="form-group">
							<select class="custom-select d-block w-100" id="selFinalidadCups" name="finalidadcups" required disabled>
							</select>
						</div>
					</div>

					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
						<label id="lblCieOrdenMedica" for="txtDiagnosticoPaciente" class="required">Diagnóstico Principal</label>
						<div class="input-group input-group-sm">
							<input type="text" class="form-control form-control-sm font-weight-bold ignore col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" name="txtDiagnosticoPaciente" id="txtDiagnosticoPaciente" autocomplete="off" disabled>
						</div>

						<div class="input-group input-group-sm">
							<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-1 col-xl-1" name="cCodigoDiagnosticoPaciente" id="cCodigoDiagnosticoPaciente" readonly="readonly">
							<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-11 col-xl-11" name="cDescripcionDiagnosticoPaciente" id="cDescripcionDiagnosticoPaciente" readonly="readonly">
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-6 col-lg-3 col-xl-4">
						<label id="lblTipoDiagnosticoCups" for="selTipoDiagnosticoCups" class="required">Tipo diagnóstico</label>
						<div class="form-group">
							<select class="custom-select d-block w-100" id="selTipoDiagnosticoCups" name="tipodiagnosticocups" required disabled>
							</select>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-6 col-lg-3 col-xl-4">
						<label id="lblMedicoRealizaCups" for="selMedicoRealizaCups" class="required">Medico realiza procedimiento</label>
						<div class="form-group">
							<select class="custom-select d-block w-100" id="selMedicoRealizaCups" name="medicorealizacups" required disabled>
							</select>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-6 col-lg-3 col-xl-4">
						<label id="lblEspecialidadRealizaCups" for="selEspecialidadRealizaCups" class="required">Especialidad realiza procedimiento</label>
						<div class="form-group">
							<select class="custom-select d-block w-100" id="selEspecialidadRealizaCups" name="especialidadrealizacups" required disabled>
							</select>
						</div>
					</div>

					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
						<label id="lblInformacionClinica" for="txtInformacionClinica">Información Clínica</label>
						<textarea class="form-control" id="txtInformacionClinica" name="informacionClinica" rows="2" disabled></textarea>
					</div>
				</div>
			</form>

			<div class="row justify-content-between pt-2">
				<div class="col-12 col-sm-12 col-md-6 col-lg-3 col-xl-3">
					<button id="btnAdicionarGrabarCups" class="btn btn-secondary btn-sl btn-block w-100" accesskey="I" disabled>Ad<u>i</u>cionar</button>
				</div>
			</div>
		</div>
		<small>
			<div class="table-responsive"><table id="tblGrabarProcedimientos"></table></div><br>
		</small>
	</div>

	<div class="card-footer text-muted fixed-bottom bg-light">
		<div class="row justify-content-between">
			<div class="col-auto">
				<label id="txtCantidadProcedimientos" for="txtCantidadProcedimientos"></label>
			</div>

			<div class="col-auto">
				<div class="row justify-content-end">
					<button id="btnLimpiarProcedimientos" type="button" class="btn btn-secondary  btn-sm" accesskey="L"><u><b>L</b></u>impiar</button>
					<button id="btnGuardarProcedimientos" type="button" class="btn btn-danger btn-sm" disabled="disabled" accesskey="G"><u><b>G</b></u>uardar</button>
					<button id="btnSalirProcedimientos" type="button" class="btn btn-secondary btn-sm" accesskey="S"><u><b>S</b></u>alir</button>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="divCentrosDeCosto" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title col-11 text-center" id="tituloCentroCosto">SELECCIONE EL CENTROS DE COSTO CORRESPONDIENTE</h4>
			</div>

			<div id="txtProcedimientoCentroCosto" class="alert alert-danger" role="alert" style="text-align: left; font-size: 14px;"></div>

			<small><div class="table-responsive"><table id="tblCentrosCostos"></table></div></small>
			<div class="modal-footer">
				<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarCentroCosto"><u>C</u>ancelar</button>
				<button type="button" class="btn btn-primary" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnAceptarCentroCosto"><u>A</u>ceptar</button>
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />
<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/3.1.3-dist/extensions/filter-control/bootstrap-table-filter-control.css">
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-4-autocomplete/1.2.3/dist/bootstrap-4-autocomplete.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-autocomplete/2.3.7-dist/bootstrap-autocomplete.min.js"></script>
<script type="text/javascript" src="vista-grabar-procedimientos/js/script.js"></script>
<script type="text/javascript" src="vista-comun/js/procedimientos.js"></script>	
<script type="text/javascript" src="vista-comun/js/diagnosticos.js"></script>	

<script type="text/javascript">
	<?php echo 'var aDatosIngreso = btoObj(\'' . base64_encode(json_encode($laRetorna)) . '\');' . PHP_EOL;
		  echo 'var aAuditoria = btoObj(\'' . base64_encode(json_encode($laAuditoria)) . '\');' . PHP_EOL;?>
</script>
