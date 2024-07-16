<?php
	$lcTipo= trim(strtolower($_GET['cp']));
	include __DIR__."/title.php";
	$title = title($lcTipo);
?>

<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-8 col-lg-11">
					<h5>
						Pacientes <?php echo $title; ?>
					</h5>
				</div>
			</div>
			<form id="frmFiltros">
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-4">
						<label for="txtIngreso">Ingreso</label>
						<input type="number" class="form-control form-control-sm" id="txtIngreso" min="0" max="99999999" placeholder="Número de ingreso" value="">
					</div>
					<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-4">
						<label for="txtNumDoc">Documento</label>
						<div id="documento" class="input-group">
							<select class="custom-select custom-select-sm col-6" id="selTipDoc" name="selTipDoc" ></select>
							<input type="text" class="form-control form-control-sm col-6" id="txtNumDoc" placeholder="Número documento" value="">
						</div>
					</div>

					<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-4">
						<label for="txtFechaIni">Fecha</label>
						<div class="form-inline row">
							<div class="form-group col-4 col-md-5 pr-0">
								<div class="input-group input-group-sm date w-100">
									<div class="input-group-prepend">
										<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
									</div>
									<input type="text" class="form-control" id="txtFechaIni" required="required" value="<?php print(date("Y-m-d")); ?>">
								</div>
							</div>
							<div class="form-group col-4 col-md-5 pl-1">
								<div class="input-group input-group-sm date w-100">
									<div class="input-group-prepend">
										<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
									</div>
									<input type="text" class="form-control" id="txtFechaFin" required="required" value="<?php print(date("Y-m-d")); ?>">
								</div>
							</div>
							<div class="form-group col-2 pl-1">
								<div>
									<div class="col mb-0 form-group form-check" style="margin: 0 auto;">
										<input type="checkbox" class="form-check-input" id="allCheck">
										Todas
									</div>
							</div>
						</div>
					</div>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-4">
						<label for="selEstado">Estado</label>
						<select class="custom-select custom-select-sm" id="selEstado"></select>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-3">
						<label for="selEspecialidad">Especialidad</label>
						<select class="custom-select custom-select-sm" id="selEspecialidad"></select>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-3">
						<label for="selMedico">Médico</label>
						<select class="custom-select custom-select-sm" id="selMedico">
							<option></option>
						</select>
					</div>
				
					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 col-xl-1">
						<label for="btnBusca" class="control-label"> &nbsp;  &nbsp;  &nbsp; &nbsp;  &nbsp;  &nbsp; </label>
						<button id="btnBuscar" type="button" class="btn btn-secondary btn-sm w-100" accesskey="B"><u>B</u>uscar</button>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 col-xl-1">
						<label for="btnLimpia" class="control-label"> &nbsp;  &nbsp;  &nbsp; &nbsp;  &nbsp;  &nbsp; </label>
						<button id="btnLimpiar" type="button" class="btn btn-secondary btn-sm w-100" accesskey="L"><u>L</u>impiar</button>
					</div>
				</div>
			</form>
		</div>

		<div class="card-body">
			<div class="row">
				<div class="col-12">
					<small>
						<table id="tblPacientes"></table>
					</small>
				</div>
			</div>
			<div class="row">
				<div class="col">
					<div class="row justify-content-end">
						<div class="col-12 col-sm-6 col-lg-4 col-xl-2 pt-2">
							<button id="btnConvencion" type="button" class="btn btn-warning btn-sm w-100">Convención</button>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>

<div id="modalConsultas" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">DATOS DE CONSULTA EXTERNA</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row"><div id="divPacienteConsultas" class="col"></div></div>
				<small>
					<table id="tblConsultas"></table>
				</small>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" id="btnNuevaConsulta">Nueva Consulta</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>



<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/1.18.3-dist/bootstrap-table.min.css" />
<link rel="stylesheet" type="text/css" media="screen" href="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />
<script type="text/javascript" src="publico-complementos/bootstrap-table/1.18.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/1.18.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="publico-complementos/moment-develop/2.29.1-dist/moment.min.js"></script>
<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="vista-comun/js/comun.js"></script>
<script type="text/javascript" src="vista-comun/js/tiposDocumentos.js"></script>
<script type="text/javascript" src="vista-historiaclinica/js/validarpacientehc.js"></script>
<script type="text/javascript" src="vista-hc-cons-externa/js/script.js"></script>
<?php include __DIR__ . '/../comun/js/usuario.js.php'; ?>
