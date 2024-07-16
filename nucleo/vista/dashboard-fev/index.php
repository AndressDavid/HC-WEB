<div class="container-fluid">

	<div class="row">
		<div class="col-11 col-md-9 col-lg-7 p-0 pb-2 pt-2 mx-auto border rounded-lg mt-2">
			<div id="formFiltro" class="form-inline row justify-content-center p-0 my-auto">
				<div class="form-check col-2 col-md-3 my-auto">
					<input class="form-check-input my-auto" type="checkbox" value="" id="checkFiltroFechas">
					<label class="form-check-label my-auto" for="checkFiltroFechas">
						Rango de fechas
					</label>
				</div>
				<div id="blkFechaIni" class="form-group col-3 pr-0 my-auto">
					<div class="input-group input-group-sm date w-100">
						<div class="input-group-prepend">
							<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
						</div>
						<input id="inicio" name="inicio" type="text" class="form-control inicio">
					</div>
				</div>

				<div class="form-group col-3 pl-1 my-auto">
					<div class="input-group input-group-sm date w-100">
						<div id="finCal" class="input-group-prepend">
							<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
						</div>
						<input id="fin" name="fin" type="text" class="form-control">
					</div>
				</div>
				<div class="col-3 col-lg-3 form-group text-center my-auto">
					<div class="input-group-sm mx-auto">
						<button type="button" class="btn btn-outline-secondary pt-1 pb-1 form-control" style="box-shadow: none;" onclick="fechaFiltro()">Consultar</button>
						<button type="button" class="btn btn-outline-secondary pt-1 pb-1 form-control" style="box-shadow: none;" onclick="fechaActual()">Hoy</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="principal" class="row mt-3 justify-content-around">
		<div class="col-12 col-lg-5 border rounded-lg shadow pr-3">
			<div id="containerCanvasReg">
				<canvas id="docRegistrados" class="mx-auto"></canvas>
			</div>
			<hr>
			<div class="row">
				<div class="col-6 border border-secondary rounded-lg mx-auto mb-1 my-auto d-none">
					<div class="row">
						<div class="col-10">
							<h6 class="text-des p-0 m-0">Facturas</h6>
							<hr class="m-0">
							<h6 class="text-des p-0 m-0">Notas crédito</h6>
							<hr class="m-0">
							<h6 class="text-des p-0 m-0">Notas débito</h6>
							<hr class="m-0">
							<h6 class="text-des p-0 m-0">Documento soporte</h6>
							<hr class="m-0">
							<h6 class="text-des p-0 m-0">Nota Ajuste DS</h6>
						</div>
						<div class="col-1">
							<h6 id="docFE" class="text-des-op p-0 m-0"></h6>
							<hr class="m-0">
							<h6 id="docNC" class="text-des-op p-0 m-0"></h6>
							<hr class="m-0">
							<h6 id="docND" class="text-des-op p-0 m-0"></h6>
							<hr class="m-0">
							<h6 id="docSO" class="text-des-op p-0 m-0"></h6>
							<hr class="m-0">
							<h6 id="docNA" class="text-des-op p-0 m-0"></h6>
						</div>
					</div>
				</div>
				<div class="col-4 border border-secondary rounded-lg mx-auto my-auto pt-1 pb-1 mb-3 mb-lg-1">
					<h6 class="text-center m-0 p-0">Total documentos</h6>
					<h3 id="totalDocumentos" class="text-center m-0 p-0"></h3>
				</div>
			</div>

		</div>
		<div class="col-12 col-lg-5 mt-4 mt-lg-0 border rounded-lg shadow">
			<div id="containerCanvasDis">
				<canvas id="disEstados" class="mx-auto"></canvas>
			</div>

			<hr>
			<table class="table table-borderless p-0">
				<tbody class="text-center">
					<tr>
						<td class="p-0 pb-2"><button class="btnExitosos btn btn-success pt-0 pb-0 my-auto m-0 shadow-sm ">Exitosos</button></td>
						<td class="p-0 pb-2"><button class="btnEnviar btn btn-info pt-0 pb-0 shadow-sm">Por enviar</button></td>
						<td class="p-0 pb-2"><button id="btnPendientes" class="btnPendientes btn btn-orange pt-0 pb-0 shadow-sm ">Pendientes</button></td>
						<td class="p-0 pb-2"><button id="btnErrores" class="btnErrores btn btn-danger pt-0 pb-0 m-0 shadow-sm parpadea mx-auto">Errores</button></td>
					</tr>
					<tr class="my-auto bg-light">
						<td class="p-0 text-des">
							<h5 id="estExi">0</h5>
						</td>
						<td class="p-0 text-des">
							<h5 id="estPen">0</h5>
						</td>
						<td class="p-0 text-des">
							<h5 id="estEnv">0</h5>
						</td>
						<td class="p-0 text-des numErrores">
							<h5 id="estErr">0</h5>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<div class="mb-2" style="position: absolute; float:right; top: 0; right: 0; padding-right: 1rem; padding-top: 7rem;">
		<button id="cmdActualizar" class="btn btn-sm btn-outline-primary"><i class="fas fa-sync fa-spin"></i> <span class="messageActualizar"></span></button>
	</div>

	<div id="infoAlertas" class="container footer mt-4 p-0"></div>

	<div class="modal fade" id="modalInformacionDocumentos" tabindex="-1" role="dialog">
		<div id="modalDialog" class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<img src="nucleo/publico/imagenes/logo/main-logo-mini.svg" class="mr-3" width="30">
					<h4 class="modal-title my-auto" id="tipoInformacion"></h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="container-fluid ">
						<table id="tablaInfoDash" data-search="true"></table>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Aceptar</button>
				</div>
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/1.22.6-dist/bootstrap-table.min.css" />

<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap/4.5.0-dist/css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />

<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/localization/messages_es.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap/4.5.0-dist/js/bootstrap.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/1.22.6-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/1.22.6-dist/extensions/export/bootstrap-table-export.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/1.22.6-dist/extensions/export/bootstrap-table-export.min.js"></script>
<script type="text/javascript" src="publico-complementos/Chart.js/2.8.0/Chart.min.js"></script>
<script type="text/javascript" src="publico-complementos/chartjs-plugin-datalabels/chartjs-plugin-datalabels.js"></script>
<script type="text/javascript" src="vista-dashboard-fev/js/script.js"></script>

<style>
	.text-des {
		font-size: 12px !important;
		font-weight: bold;
	}

	.text-des-op {
		font-size: 12px !important;
	}

	.modal-xl {
		max-width: 90% !important;
	}

	.parpadea {
		animation-name: parpadeo;
		animation-duration: 4s;
		animation-timing-function: linear;
		animation-iteration-count: infinite;

		-webkit-animation-name: parpadeo;
		-webkit-animation-duration: 4s;
		-webkit-animation-timing-function: linear;
		-webkit-animation-iteration-count: infinite;
	}

	.ui-dialog-title {
		width: 50rem !important;
		color: black !important;

	}

	.btn-orange {
		background-color: #F39C12 !important;
		color: white !important;
	}

	@-moz-keyframes parpadeo {
		0% {
			opacity: 1.0;
		}

		50% {
			opacity: 0.1;
		}

		100% {
			opacity: 1.0;
		}
	}

	@-webkit-keyframes parpadeo {
		0% {
			opacity: 1.0;
		}

		50% {
			opacity: 0.1;
		}

		100% {
			opacity: 1.0;
		}
	}

	@keyframes parpadeo {
		0% {
			opacity: 1.0;
		}

		50% {
			opacity: 0.1;
		}

		100% {
			opacity: 1.0;
		}
	}
</style>