<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">

			<small>
			<h5><span class="fas fa-location-arrow" style="color: #17A2B8;"> </span> Consulta Direccionamientos</h5>

			<!-- FILTROS -->
			<form id="formFiltro" class="needs-validation" novalidate>
			<div id="divFiltro" class="row">
				<div class="container-fluid">

					<div class="row">
						<div class="col-md-6 col-lg-4 pb-1">
							<div class="row">
								<label for="txtFechaDesde" class="col-4"><b>Fecha Desde</b></label>
								<div class="input-group date col-8">
									<div class="input-group-prepend">
										<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
									</div>
									<input type="text" class="form-control form-control-sm mp-filtro" name="txtFechaDesde" id="txtFechaDesde" value="<?php print(date("Y-m-d")); ?>"><span class="input-group-addon"></span>
									<div class="invalid-tooltip">Fecha Desde obligatoria</div>
								</div>
							</div>
						</div>
						<div class="col-md-6 col-lg-4 pb-1">
							<div class="row">
								<label for="txtFechaHasta" class="col-4"><b>Fecha Hasta</b></label>
								<div class="input-group date col-8">
									<div class="input-group-prepend">
										<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
									</div>
									<input type="text" class="form-control form-control-sm mp-filtro" name="txtFechaHasta" id="txtFechaHasta" value="<?php print(date("Y-m-d")); ?>">
									<div class="invalid-tooltip">Fecha Hasta obligatoria</div>
								</div>
							</div>
						</div>
						<div class="col-md-6 col-lg-4 pb-1">
							<div class="row">
								<label for="selCodEps" class="col-4"><b>EPS</b></label>
								<div class="input-group col-8">
									<select name="selCodEps" id="selCodEps" class="form-control form-control-sm mp-filtro"></select>
								</div>
							</div>
						</div>
						<div class="col-md-6 col-lg-4 pb-1">
							<div class="row">
								<label for="txtPrescripcion" class="col-4"><b>Prescripción</b></label>
								<div class="input-group col-8">
									<input type="number" class="form-control form-control-sm mp-filtro" name="txtPrescripcion" id="txtPrescripcion" placeholder="" value="">
									<div class="invalid-tooltip">Prescripción debe tener 20 dígitos</div>
								</div>
							</div>
						</div>
						<div class="col-md-6 col-lg-4 pb-1">
							<div class="row">
								<label for="txtIngreso" class="col-4"><b>Ingreso</b></label>
								<div class="input-group col-8">
									<input type="number" class="form-control form-control-sm mp-filtro" name="txtIngreso" id="txtIngreso" min="0" max="99999999" placeholder="" value="">
									<div class="invalid-tooltip">Ingreso debe tener 7 dígitos</div>
								</div>
							</div>
						</div>
						<div class="col-md-6 col-lg-4 pb-1">
							<div class="row">
								<label for="selTipoDoc" class="col-4"><b>Doc. Paciente</b></label>
								<div class="input-group col-8">
									<select name="selTipoDoc" id="selTipoDoc" class="form-control form-control-sm mp-filtro"></select>
									<input type="text" class="form-control form-control-sm mp-filtro" name="txtNumeroDoc" id="txtNumeroDoc" placeholder="" value="">
									<div class="invalid-tooltip">Documento de paciente incorrecto</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row justify-content-end">
						<div class="col-auto">
							<button id="btnBuscar" type="button" class="form-control btn btn-secondary btn-sm" accesskey="B"><u>B</u>uscar</button>
						</div>
						<div class="col-auto">
							<button id="btnLimpiar" type="button" class="form-control btn btn-secondary btn-sm" accesskey="L"><u>L</u>impiar</button>
						</div>
					</div>

				</div>
			</div>
			</form>
			<div class="row"><div class="col"><div id="filtroInfo"></div></div></div>
			</small>
		</div>

		<!-- TABLA DE DIRECCIONAMIENTOS -->
		<div id ="divTabla" class="card-body">
			<small>
				<table id="tblDatos"></table>
			</small>
		</div>
	</div>
</div>

<!-- Propiedades Direccionamiento -->
<small>
<div id="divPrincipal" class="modal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header alert-dark">
				<h5 class="modal-title">Prescripción</h5>
			</div>
			<div class="modal-body" style="padding-bottom: 0px;">
				<div id="divContenidoPrincipal">
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>
</small>

<!-- Propiedades Acciones -->
<small>
<div id="divAcciones" class="modal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header alert-dark">
				<h5 class="modal-title">Prescripción</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div id="divContenidoAccion">
				</div>
			</div>
		</div>
	</div>
</div>
</small>

<!-- Formulario PUT -->
<small>
<div id="divFormPUT" class="modal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header alert-dark">
				<h5 class="modal-title">Acción PUT</h5>
			</div>
			<div class="modal-body">
				<!-- Controles de envío de datos -->
				<div class="row" id="divPUT" style="display: none">
					<div class="container-fluid" id="cntPUT"></div>
				</div>
				<!-- Mensaje PUT -->
				<div class="row">
					<div class="col">
						<div id="divInfo" class="container-fluid"></div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary btn-sm" id="btnEnviar" tipoacc="">Enviar</button>
				<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>
</small>



<link rel="stylesheet" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />

<script type="text/javascript" src="publico-complementos/table-export/1.10.16/tableExport.min.js"></script>
<script type="text/javascript" src="publico-complementos/table-export/1.10.16/libs/js-xlsx/xlsx.core.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/extensions/export/bootstrap-table-export.min.js"></script>

<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/localization/messages_es.min.js"></script>

<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="vista-mipres/js/funciones.js"></script>
<script type="text/javascript" src="vista-mipres/js/direccionamiento.js"></script>
