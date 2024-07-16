<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<h5>Consulta de Signos - Alertas Tempranas para Estudio</h5>
			<div id="filtroIngreso" class="row">
				<div class="col-lg-2 col-md-3 col-sm-12 col-12 pb-2">
					<label for="txtFechaDesde"><b>Fecha Desde</b></label>
					<div class="input-group date">
						<div class="input-group-prepend">
							<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
						</div>
					  <input type="text" class="form-control form-control-sm" name="txtFechaDesde" id="txtFechaDesde" required="required" value="<?php print(date("Y-m-d")); ?>"><span class="input-group-addon"></span>
					</div>
				</div>
				<div class="col-lg-2 col-md-3 col-sm-12 col-12 pb-2">
					<label for="txtFechaHasta"><b>Fecha Hasta</b></label>
					<div class="input-group date">
						<div class="input-group-prepend">
							<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
						</div>
						<input type="text" class="form-control form-control-sm" name="txtFechaHasta" id="txtFechaHasta" required="required" value="<?php print(date("Y-m-d")); ?>"></span>
					</div>
				</div>

				<div class="col-lg-2 col-md-2 col-sm-12 col-12 pb-2">
					<label for="btnExportar">&nbsp;</label>
					<button id="btnExportar" type="button" class="form-control form-control-sm btn btn-secondary btn-sm" accesskey="X">E<u>x</u>portar</button>
				</div>
			</div>
			<div class="row"><div class="col"><div id="filtroInfo"></div></div></div>
		</div>
		<div id ="divInfo" class="card-body">
			<div class="media">
				<div class="media-body">
					Se obtiene un archivo de Excel con dos hojas:<br/>
					<ul>
						<li><b>Registros Signos:</b> Datos de cada ingreso en el rango de fechas indicado</li>
						<li><b>Descripción Campos:</b> Descripción de cada una de las columnas de la primer hoja</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- https://github.com/uxsolutions/bootstrap-datepicker -->
<script src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script src="vista-alerta-temprana/js/consulta_estudio.js"></script>
