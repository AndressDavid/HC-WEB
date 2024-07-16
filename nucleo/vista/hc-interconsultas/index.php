<?php
include __DIR__ . '/../comun/textoInformativo.php';
include __DIR__ . '/../comun/modalEspera.php';
require_once __DIR__ . '/../../controlador/class.Historia_Clinica_Ingreso.php';
require_once __DIR__.'/../proce-control/controller/cabecera.php';
require_once __DIR__ . '/../../controlador/class.Doc_Interconsulta.php';
require_once __DIR__ .'/../comun/ajax/verificasesion.php';
require_once __DIR__.'/../../controlador/class.ConsultaExterna.php';


$btnVolver = '<a class="btn btn-secondary" href="index">Volver</a>';
$divMensaje = '<div class="container-fluid"><div class="card mt-3"><div class="card-header alert-danger"><div class="row"><div class="col"><h5>%s</h5>%s</div></div></div></div></div>';

if (isset($_SESSION[HCW_DATA])) {
	$laInterc = $_SESSION[HCW_DATA];
	unset($_SESSION[HCW_DATA]);
} else {
	$laInterc = $_POST;
}

if(!isset($laInterc['NINORD']) || !isset($laInterc['CCIORD']) || !isset($laInterc['CODCUP']) ){
	printf($divMensaje, 'No se pudieron obtener los datos del paciente. Por favor intente nuevamente ingresar a la interconsulta.', $btnVolver);
	return false;
}	


(new NUCLEO\Auditoria())->guardarAuditoria($laInterc['NINORD'], $laInterc['CCOORD']??0, $laInterc['CCIORD'], $laInterc['CODCUP'], 'RTAINTERC_WEB', 'INICIO', 0, 'INGRESO INTERCONSULTA '.($laInterc['DESESP']??''), 'RTAINTERC', $laInterc['TIDORD']??'', $laInterc['NIDORD']??0);

$cabeza = new Cabecera;
$cabeza->setIngreso($laInterc["NINORD"]);

$oValidarestado = new NUCLEO\Doc_Interconsulta();

$bEstado= $oValidarestado->validarEstadoInterconsulta($laInterc['NINORD'],$laInterc['CCIORD']);

?>
<script type="text/javascript">
	lbestado = <?php  $retVal = ($bEstado) ? "true" :  "false" ; echo $retVal; ?>;
	oModalEspera.nIntervalo = 3;
	oModalEspera.mostrar('Se est&aacute; preparando el entorno de trabajo', 'Espere por favor');
</script>


<?php 
include __DIR__ . '/../comun/cabecera.php';
?>
<div class="container-fluid small">
	
<?php if( isset($laInterc['lsInterfaz']) ) { 
		if(strtoupper($laInterc['lsInterfaz']) =='AVAL' ){
	?>
	<div class="header container-fluid alert alert-danger" role="alert">
		<h6 class="text-center text-black font-weight-bold pt-15" >RESPUESTA INTERCONSULTA POR AVAL</h6>
	</div>
<?php }} ?>


	<div class="header container-fluid mt-4">
		<h5 class="title text-center" id="txtTituloEspecialidadConsultada">SOLICITUD DE INTERCONSULTA PARA ESPECIALIDAD: </h5>
	</div>

	<form id="interconsultas"  action="POST">
		<input type="hidden" value='guardar' id='globalGuardar'>
		<div class="container-fluid  border border-10 rounded-sm">
				<div class="mt-2">
					<h6 class="modal-title">SOLICITANTE: <span id="txtMedicoSolicitud"></span>&nbsp;-&nbsp;<span id="txtEspecialidadSolicitud"></span></h6>
				</div>

				<div class="px-0">
					<label id="lblSolicitud" for="txtSolicitud" >Solicitud: </label>
					<textarea class="form-control" id="txtSolicitud" name="Solicitud" rows="6" disabled></textarea>
				</div>

				<div class="row px-0 mt-2 pb-4">
					<div class="col-sm">
						<label id="lblNuevoSeguimiento" for="txtNuevoSeguimiento" >Nuevo Seguimiento: </label>
						<textarea class="form-control" id="txtNuevoSeguimiento" name="NuevoSeguimiento" rows="7" disabled></textarea>
					</div>
					<div class="col-sm">
						<label id="lblSeguimientos" for="txtSeguimientos" >Seguimientos: </label>
						<textarea class="form-control" id="txtSeguimientos" name="Seguimientos" rows="7" disabled></textarea>
					</div>
				</div>

		</div>

		<div class="container-fluid  border border-10 rounded-sm mt-3">
			<div class="mt-2">
				<h6 class="modal-title">RESPUESTA: <span id="txtMedicoInterconsultado"></span>&nbsp;-&nbsp;<span id="txtEspecialidadConsultada"></span></h6>
			</div>

			<div class="row px-0 mt-2 pb-4">
				<div class="col col-lg-3">
					<label for="txtPrioridad">Prioridad:</label><span class="pl-2"id="txtPrioridad"></span>   &nbsp; &nbsp; &nbsp;	<label for="txtProposito" >Propósito: </label><span class="pl-2" id="txtProposito"></span>
				</div>
				<div class="col-sm" id="divAceptaTrasladoPaciente">
					<div class="row">
						<div class="col col-lg-2">
							<label for="selAceptaTrasladoPaciente" class="required" >Acepta Traslado Paciente: </label>
						</div>
						<div class="col col-lg-3">
							<select name="AceptaTrasladoPaciente" id="selAceptaTrasladoPaciente" class="form-control form-control-sm w-35" class="required" disabled>
								<option value="" selected></option>
								<option value="NO">No</option>
								<option value="SI">Sí</option>
							</select>
						</div>
					</div>
				</div>
			</div>

			<div class="mb-2">
				<label for="txtCodigoCie" class="required">Diagnóstico Principal</label>
				<div class="row">
					<div class="col-sm">
						<input type="text" id="txtCodigoCie" name="codDiagPrin" class="form-control form-control-sm" autocomplete="off" disabled>
					</div>
				</div>
				<div class="row mt-3">
					<div class="col col-lg-2">
						<input type="text" class="form-control form-control-sm" id="cCodigoCie" required disabled>
					</div>
					<div class="col-sm">
						<input required type="text" class="form-control form-control-sm" id="cDescripcionCie" disabled>
					</div>
				</div>
			</div>

			<div class="mb-2">
				<div class="row">
					<div class="col-sm">
						<label for="claseDiagnostico" class="required">Tipo de Diagnostico Principal</label>
						<select class="custom-select custom-select-sm col-16" name="tipoDiagnostico" id="tipoDiagnostico" disabled required>
							<option value=''></option>
						</select>
					</div>
				</div>
			</div>
			
			<div class="row px-0 mt-2 pb-4">
				<div class="col-sm">
					<label for="txtRespuestaInterconsulta" class="required" >Respuesta a Interconsulta: </label>
					<textarea class="form-control" id="txtRespuestaInterconsulta" name="txtRespuestaInterconsulta" rows="10" disabled required ></textarea>
				</div>
				<div class="col-sm">
					<label for="txtAnalisisEpicrisis" class="required">Análisis para Epicrisis: </label>
					<textarea class="form-control" id="txtAnalisisEpicrisis" name="txtAnalisisEpicrisis" rows="10" disabled required></textarea>
				</div>
			</div>

		</div>
		
	</form>
	
<div class="card-footer text-muted fixed-bottom bg-light">
		<div class="row justify-content-between">
			<div class="col-auto">
				<button id="btnEvolucionesOM" type="button" class="btn btn-secondary btn-sm" tabindex="1" accesskey="V">E<u><b>v</b></u>oluciones Anteriores</button>
				<button id="btnLaboratorios" type="button" class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Ir a laboratorios"><u>L</u>aboratorios</button>
				<button id="btnAgility" type="button" class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Ir a Agility"><u>A</u>gility</button>
				<button id="btnTextoInf" type="button" class="btn btn-secondary btn-sm" >Texto Pandemia</button>
				<button type="button" class="btn btn-danger btn-sm" <?php if(!$bEstado){ echo 'id="btnGuardarResultadosInterConsulta"';}else{ echo "disabled=true"; } ?>>Guardar</button>
			</div>
			<div class="col-auto">
				<button id="btnVolver" type="button" class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Volver"><i class="fas fa-arrow-left"></i></button>
				<button id="btnVerPdfHC" type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Ver PDF" disabled="disabled"><i class="fas fa-file-pdf"></i></button>
				<button id="btnVistaPrevia" type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Vista Previa" disabled="disabled"><i class="fas fa-eye"></i></button>
				<button id="btnLibroHC" type="button" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Libro HC"><i class="fas fa-book-medical"></i></button>
			</div>
		</div>
	</div>
</div>


<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/1.18.3-dist/bootstrap-table.min.css" />
<link rel="stylesheet" type="text/css" media="screen" href="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />

<script type="text/javascript" src="publico-complementos/bootstrap-4-autocomplete/1.2.3/dist/bootstrap-4-autocomplete.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-autocomplete/2.3.7-dist/bootstrap-autocomplete.min.js"></script>
<script type="text/javascript" src="vista-comun/js/diagnosticos.js"></script>

<script type="text/javascript">
	var goFilaSelInter = <?= json_encode($laInterc) ?>;
	var aDatosIngreso = <?php print_r($cabeza->getDatosCabeza())?>;
</script>

<script type="text/javascript" src="publico-complementos/bootstrap-table/1.18.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/1.18.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="publico-complementos/moment-develop/2.29.1-dist/moment.min.js"></script>
<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="vista-comun/js/nocopypaste.js"></script>
<script type="text/javascript" src="vista-comun/js/comun.js"></script>
<script type="text/javascript" src="vista-comun/js/modalVistaPrevia.js"></script>

<script type="text/javascript" src="vista-hc-interconsultas/js/script.js"></script>

<script>
	interconsultas.inicializar();
</script>