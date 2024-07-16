<?php

include __DIR__ . '/../comun/modalOrdAmbPDF.php';

if (isset($_GET['q'])) {
	$lcPagina = __DIR__ .'/'.trim(strtolower($_GET['q'])).".php";
	include($lcPagina);

} else {
	(new NUCLEO\Auditoria())->guardarAuditoria(0, 0, 0, '', 'ORDAMB_WEB', 'INICIO', 0, 'INGRESO ORDENES AMBULATORIAS', 'ORDAMB', '', 0);
?>
<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-8 col-lg-11">
					<h5>Ordenes ambulatorias</h5>
				</div>
			</div>
			<form id="frmFiltrosOrdenesAmb">
				<div id="divFiltroOrdAmb" class="row">
					<div class="col-sm-12 col-md-12 col-lg-4 col-xl-4">
						<label for="inpNumDocOrdAmb" class="control-label"><b>Documento</b></label>
						<div class="input-group mb-3">
							<select id="selTipDocOrdAmb" class="custom-select custom-select-sm col-6"></select>
							<input  id="txtNumDocOrdAmb" type="text" class="form-control form-control-sm col-6" placeholder="Número documento" value="">
							<span class="input-group-text" id="btnAyudaOrdenesAmb"><i class="fas fa-search"></i></span>
						</div>
					</div>
					<div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
						<label id="lblNombrePacienteOrdAmb" for="txtNombrePacienteOrdAmb">Paciente</label>
						<input id="txtNombrePacienteOrdAmb" type="text" class="form-control form-control-sm font-weight-bold" name="NombrePacienteOrdAmb"  placeholder="" disabled="disabled">
					</div>

					<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 col-xl-1 pb-1">
						<label for="btnIngresarOrdenesAmb">&nbsp;</label>
						<button id="btnIngresarOrdenesAmb" type="button" class="form-control form-control-sm btn btn-secondary btn-sm" accesskey="N"><u>N</u>uevo</button>
					</div>

					<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 col-xl-1 pb-1">
						<label for="btnLimpiarOrdenesAmb">&nbsp;</label>
						<button id="btnLimpiarOrdenesAmb" type="button" class="form-control form-control-sm btn btn-secondary btn-sm" accesskey="L"><u>L</u>impiar</button>
					</div>
				</div>
			</form>
		</div>
		<div class="card-body">	<small>	<table id="tblOrdenesAmbulatorias"></table>	</small></div>

		<div class="d-flex">
			<div class="col-xs-12 col-sm-12 col-md-3 col-lg-2 col-xl-2 ml-auto p-2">
				<label for="btnSalirOrdenesAmb">&nbsp;</label>
				<button id="btnSalirOrdenesAmb" type="button" class="form-control form-control-sm btn btn-secondary btn-sm" accesskey="S"><u>S</u>alir</button>
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/1.18.3-dist/bootstrap-table.min.css" />
<link rel="stylesheet" type="text/css" media="screen" href="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />
<script type="text/javascript" src="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/1.18.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/1.18.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="vista-comun/js/comun.js"></script>
<script type="text/javascript" src="vista-comun/js/tiposDocumentos.js"></script>
<script type="text/javascript" src="vista-ordenes-ambulatorias/js/script.js"></script>
<script type="text/javascript" src="vista-comun/js/modalVistaPrevia.js"></script>

<?php } ?>