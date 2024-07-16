<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<div class="row">
				<div class="col-12 col-md-8 col-lg-11">
					<h5>SOPORTES CM</h5>
				</div>
			</div>

			<div class="row" id="rowFiltros">
				<div class="col-12 mb-1">
					<label for="txtIngreso" class="mb-0">Ingreso</label>
					<input type="text" class="form-control form-control-sm" id="txtIngreso" min="0" max="99999999" placeholder="Número de ingreso" value="">
				</div>

				<div class="col-xs-12 col-sm-6 col-md-3 col-lg-2 mb-1">
					<label for="selVia" class="mb-0">Vía</label>
					<select class="custom-select custom-select-sm" id="selVia">
						<option value="">TODAS</option>
						<option value="05,06">CIRUGIA AMBU.+HOSPITALIZADO</option>
					</select>
				</div>

				<div class="col-xs-12 col-sm-6 col-md-3 col-lg-2 mb-1">
					<label for="selEntidad" class="mb-0">Entidad</label>
					<select class="custom-select custom-select-sm" id="selEntidad">
						<option value="">TODAS</option>
					</select>
				</div>

				<div class="col-xs-12 col-sm-6 col-md-3 col-lg-2 mb-1">
					<label for="txtFacturador" class="mb-0">Facturador</label>
					<input type="text" class="form-control form-control-sm" id="txtFacturador" placeholder="Facturador" value="">
				</div>

				<div class="col-xs-12 col-sm-6 col-md-3 col-lg-2 mb-1">
					<label for="selEstado" class="mb-0">Estado</label>
					<select class="custom-select custom-select-sm" id="selEstado">
						<option value="N">No Generados</option>
						<option value="G">Generados</option>
						<option value="T">TODOS</option>
					</select>
				</div>

				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-4 mb-1">
					<label for="selTipoFecha" class="mb-0">Fecha</label>
					<div class="form-inline row">
						<div class="col-3 pr-0">
							<select class="custom-select custom-select-sm" id="selTipoFecha">
								<option value="factura">Factura</option>
								<option value="soporte">Soporte</option>
							</select>
						</div>
						<div class="col-9 pr-0">
							<div class="form-inline row">
								<div class="form-group col-6 pr-0">
									<div class="input-group input-group-sm date w-100">
										<div class="input-group-prepend">
											<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
										</div>
										<input type="text" class="form-control" id="txtFechaIni" required="required" value="<?php print(date("Y-m-d")); ?>">
									</div>
								</div>
								<div class="form-group col-6 pl-1">
									<div class="input-group input-group-sm date w-100">
										<div class="input-group-prepend">
											<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
										</div>
										<input type="text" class="form-control" id="txtFechaFin" required="required" value="<?php print(date("Y-m-d")); ?>">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row justify-content-end mt-1">
				<div class="col-auto">
					<button id="btnBuscar" type="button" class="btn btn-secondary btn-sm w-100" accesskey="B"><u>B</u>uscar</button>
				</div>
				<div class="col-auto">
					<button id="btnLimpiar" type="button" class="btn btn-secondary btn-sm w-100" accesskey="L"><u>L</u>impiar</button>
				</div>
			</div>
		</div>

		<div class="card-body small">
			<table id="tblIngresos"></table>
		</div>

	</div>
</div>

<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />
<link rel="stylesheet" type="text/css" href="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />

<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/localization/messages_es.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="vista-comun/js/comun.js"></script>
<script type="text/javascript" src="vista-soportescm/js/script.js"></script>
<script type="text/javascript" src="vista-soportescm/js/consulta.js"></script>
<script type="text/javascript">
	var cFechaActual = '<?php print(date("Y-m-d")); ?>';
</script>
