<?php
	require_once (__DIR__ .'/../../controlador/class.AplicacionFunciones.php');

	$lcTipoIngreso = (isset($_GET['q'])?$_GET['q']:'DEFAULT');
	
	$lcMensaje = '';

	$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
	$loIntervalo = new \DateInterval('P1D');
	$loIntervalo->invert = 1;

	$ltAyer = new \DateTime( $goDb->fechaHoraSistema() );
	$ltAyer->add($loIntervalo);

	$ldFechaInicio = (isset($_POST['inicio'])?$_POST['inicio']:$ltAyer->format("Y-m-d"));
	$ldFechaFin = (isset($_POST['fin'])?$_POST['fin']:$ltAhora->format("Y-m-d"));

?>
	<div class="container-fluid">
		<div class="card mt-3">

			<div class="card-header">
				<h5>Ingresos</h5>
				<form role="form" id="registroIngreso" name="registroIngreso" method="POST" enctype="application/x-www-form-urlencoded" action="modulo-ingresos&p=listaIngresos&q=<?php print($lcTipoIngreso); ?>">
					<div id="filtro" class="row">
						<div class="col-md-2 pb-2">
							<label for="ingreso">Ingreso</label>
							<input type="number" class="form-control form-control-sm" name="ingreso" id="ingreso" min="0" max="99999999" placeholder="" value="<?php print(isset($loIngreso)?$loIngreso->nIngreso:''); ?>">
						</div>
						<div class="col-md-4 col-lg-2 pb-2">
							<label for="identificacion"><b>Identificaci&oacute;n</b></label>
							<div class="input-group input-group-sm">
								<div class="input-group-prepend">
									<div class="input-group-text font-weight-bold"><i class="fas fa-address-card"></i></div>
								</div>
								<input type="text" class="form-control font-weight-bold" name="identificacion" id="identificacion" placeholder="" value="" disabled="disabled">
							</div>
						</div>
						<div class="col-md-6 col-lg-5 pb-2">
							<label for="ingreso">Paciente</label>
							<input type="text" class="form-control form-control-sm font-weight-bold" name="paciente" id="paciente" placeholder="" value="" disabled="disabled">
						</div>
						<div class="col-md-6 col-lg-3 pb-2">
							<label class="label">Buscar en este periodo</label>							
							<div class="form-inline row">
								<div class="form-group col-6 pr-0">
									<div class="input-group input-group-sm date w-100">
										<div class="input-group-prepend">
											<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
										</div>
										<input id="inicio" name="inicio" type="text" class="form-control" required="required" value="<?php print($ldFechaInicio); ?>">
									</div>
								</div>
								<div class="form-group col-6 pl-1">
									<div class="input-group input-group-sm date w-100">
										<div class="input-group-prepend">
											<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
										</div>
										<input id="fin" name="fin" type="text" class="form-control" required="required" value="<?php print($ldFechaFin); ?>">
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6 col-lg-4 pb-2">
							<label>Estado</label>
							<select class="custom-select custom-select-sm" id="estado" name="estado">
							</select>
						</div>
						<div class="col-sm-12 col-md-6 col-lg-4 pb-2">
							<div class="row align-items-end h-100">
								<div class="col-12 col-sm-6 pb-2 pb-md-0">
									<button id="btnBuscar"  type="submit" class="btn btn-secondary btn-sm w-100" accesskey="B"><u>B</u>uscar</button>
								</div>
								<div class="col-12 col-sm-6 pb-2 pb-md-0">
									<a class="btn btn-secondary btn-sm w-100" accesskey="L" href="modulo-ingresos&p=listaIngresos&q=<?php print($lcTipoIngreso); ?>"><u>L</u>impiar</a>
								</div>
							</div>
						</div>
					</div>
				</form>
				<div class="row"><div class="col"><div id="ingresoInfo"><?php print($lcMensaje); ?></div></div></div>
			</div>
			<div id ="registroIngreso" class="card-body">
				<div id="toolbarlistaIngresos">
					<div class="form-inline">
						<button id="btnAgregar"  type="button" class="btn btn-success" data-toggle="modal" data-target="#agregarIngreso" accesskey="R">Nuevo</button>	
					</div>
				</div>
				<table
				  id="tableListaIngresos"
				  data-show-export="true"
				  data-toolbar="#toolbarlistaIngresos"
				  data-show-refresh="true"
				  data-click-to-select="true"
				  data-show-export="false"
				  data-show-columns="true"
				  data-show-columns-toggle-all="true"
				  data-minimum-count-columns="5"
				  data-pagination="false"
				  data-id-field="CONSECUTIVO"
				  data-query-params="queryParams"
				  data-row-style="rowStyle"
				  data-url="vista-ingresos/ajax/listaIngresos.ajax?q=<?php print($lcTipoIngreso); ?>" >
				</table>
			</div>
			<div class="card-footer text-muted">info</div>
			
			<!-- Bootstrap Table -->
			<link rel="stylesheet" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />
			<script type="text/javascript" src="publico-complementos/jquery-tableexport/tableExport.min.js"></script>
			<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
			<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table-locale-all.min.js"></script>
			<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/extensions/export/bootstrap-table-export.min.js"></script>
			<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
			<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
			
			
			<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
			<script type="text/javascript" src="vista-comun/js/comun.js"></script>	
			<script type="text/javascript" src="vista-ingresos/js/listaIngresos.js?q=<?php print($lcTipoIngreso); ?>"></script>
	
		</div>
	</div>