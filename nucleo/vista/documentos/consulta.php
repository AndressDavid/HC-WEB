<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">

			<small>
			<h5><span class="fas fa-book-medical" style="color: #17A2B8;"> </span> Consulta Libros de HC en PDF Generados</h5>

			<!-- FILTROS -->
			<div id="divFiltro" class="row">
				<div class="container-fluid">

					<div class="row">
						<div class="col-lg-3 col-md-3 col-sm-12 col-12 pb-1">
							<div class="row">
								<label for="selCodEntidad" class="col-3"><b>Entidad</b></label>
								<div class="input-group col-9">
									<select name="selCodEntidad" id="selCodEntidad" class="form-control form-control-sm"></select>
								</div>
							</div>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-12 col-12 pb-1">
							<div class="row">
								<label for="selFechaTipo" class="col-4"><b>Fecha</b></label>
								<div class="input-group col-8">
									<select id="selFechaTipo" name="selFechaTipo" class="form-control form-control-sm">
										<option value=''>Sin filtro por Fecha</option>
										<option value='egreso'>Fecha egreso paciente</option>
										<option value='ingreso'>Fecha ingreso paciente</option>
										<option value='factura'>Fecha facturación</option>
										<option value='documento'>Fecha generado PDF</option>
									</select>
								</div>
							</div>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-12 col-12 pb-1">
							<div class="row">
								<label for="txtFechaDesde" class="col-3"><b>Entre</b></label>
								<div class="input-group date col-9">
									<div class="input-group-prepend">
										<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
									</div>
								  <input type="text" class="form-control form-control-sm" name="txtFechaDesde" id="txtFechaDesde" required="required" value="<?php print(date("Y-m-d")); ?>"><span class="input-group-addon"></span>
								</div>
							</div>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-12 col-12 pb-1">
							<div class="row">
								<label for="txtFechaHasta" class="col-2"><b>y</b></label>
								<div class="input-group date col-10">
									<div class="input-group-prepend">
										<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
									</div>
								  <input type="text" class="form-control form-control-sm" name="txtFechaHasta" id="txtFechaHasta" required="required" value="<?php print(date("Y-m-d")); ?>"><span class="input-group-addon"></span>
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-lg-3 col-md-3 col-sm-12 col-12 pb-1">
							<div class="row">
								<label for="txtIngreso" class="col-3"><b>Ingreso</b></label>
								<div class="input-group col-9">
									<input type="number" class="form-control form-control-sm" name="txtIngreso" id="txtIngreso" min="0" max="99999999" placeholder="" value="" required="">
								</div>
							</div>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-12 col-12 pb-1">
							<div class="row">
								<label for="selTipoDoc" class="col-4"><b>Doc.Paciente</b></label>
								<div class="input-group col-8">
									<!-- <select name="selTipoDoc" id="selTipoDoc" class="form-control-sm"></select> -->
									<select name="selTipoDoc" id="selTipoDoc" class="form-control form-control-sm custom-select custom-select-sm col-4"></select>
									<input type="text" class="form-control form-control-sm" name="txtNumeroDoc" id="txtNumeroDoc" placeholder="" value="" required="">
								</div>
							</div>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-12 col-12 pb-1" id="divEstado" style="display:none">
							<div class="row">
								<label for="selEstado" class="col-3"><b>Estado</b></label>
								<div class="input-group col-9">
									<select name="selEstado" id="selEstado" class="form-control form-control-sm">
										<option value=''>Todas</option>
										<option value='0'>Pendientes</option>
										<option value='P'>En Proceso</option>
										<option value='G' selected="selected">Generadas</option>
									</select>
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-12 text-right">
							<button id="btnBuscar" type="button" class="btn btn-secondary btn-sm" accesskey="B"><u>B</u>uscar</button>
							<button id="btnLimpiar" type="button" class="btn btn-secondary btn-sm" accesskey="L"><u>L</u>impiar</button>
						</div>
					</div>

				</div>
			</div>
			<div class="row"><div class="col"><div id="filtroInfo"></div></div></div>
			</small>
		</div>

		<!-- TABLA CON LIBROS -->
		<div id ="divTabla" class="card-body">
			<small>
				<table id="tblDatos"></table>
			</small>
		</div>
	</div>
</div>



<link rel="stylesheet" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />
<link rel="stylesheet" type="text/css" media="screen" href="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />

<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/extensions/export/bootstrap-table-export.min.js"></script>

<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="vista-comun/js/comun.js"></script>
<script type="text/javascript" src="vista-documentos/js/consulta.js"></script>
