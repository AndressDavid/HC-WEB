<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<h5>Valoraciones de signos vitales - Puntajes NEWS2</h5>
			<div id="filtroIngreso" class="row">
				<div class="col-lg-2 col-md-2 col-sm-12 col-12 pb-2">
					<label for="txtIngreso"><b>Ingreso</b></label>
					<input type="number" class="form-control form-control-sm" name="txtIngreso" id="txtIngreso" min="0" max="99999999" placeholder="" value="" required="">
				</div>
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
				<div class="col-lg-2 col-md-2 col-sm-6 col-6 pb-2">
					<label for="btnBuscar">&nbsp;</label>
					<button id="btnBuscar" type="button" class="form-control form-control-sm btn btn-secondary btn-sm" accesskey="B"><u>B</u>uscar</button>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-6 pb-2">
					<label for="btnLimpiar">&nbsp;</label>
					<button id="btnLimpiar" type="button" class="form-control form-control-sm btn btn-secondary btn-sm" accesskey="L"><u>L</u>impiar</button>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-12 col-12 pb-2">
					<label for="btnExportar">&nbsp;</label>
					<button id="btnExportar" type="button" class="form-control form-control-sm btn btn-secondary btn-sm" accesskey="X">E<u>x</u>portar</button>
				</div>
			</div>
			<div class="row"><div class="col"><div id="filtroInfo"></div></div></div>
		</div>
		<div id ="ingresoInfo" class="card-body">
			<div class="media">
				<div id="divIconoPac" class="align-self-center mr-3 mb-3"></div>
				<div class="media-body">
					<h6><span class="badge badge-success" id="nIngresoMostrar"></span> - <span id="cNombre"> - </span></h6>
					<span id="cTipoIdMostrar"></span> - <span id="nIdMostrar"></span><br/>
					<span id="cEdad"> - </span><br/>
					<span id="cUbicacion"> - </span>
				</div>
			</div>
		</div>
		<div id ="divTabla" class="card-body">
			<table class="table table-sm table-responsive-sm table-responsive-lg">
				<thead>
					<tr class="bg-light" id="trTitulos">
					</tr>
				</thead>
				<tbody class="table-main" id="tbodyData">
				</tbody>
			</table>
			<div class="btn-toolbar justify-content-between" role="toolbar" aria-label="Toolbar with button groups">
				<div class="btn-group btn-group-sm" role="group" aria-label="First group">
					<button type="button" id="PagUno" class="btn btn-secondary boton-pag"></button>
					<button type="button" id="PagAnt" class="btn btn-secondary boton-pag"></button>
					<button type="button" id="PagSig" class="btn btn-secondary boton-pag"></button>
					<button type="button" id="PagUlt" class="btn btn-secondary boton-pag"></button>
				</div>
				<div class="btn-group btn-group-sm">
					<span id="PagsDe"></span>
				</div>
				<div class="input-group-select">
					Regs : <select id="selRegPorPag">
						<option value="5">5</option>
						<option value="10">10</option>
						<option value="20">20</option>
						<option value="50">50</option>
						<option value="100">100</option>
						<option value="250" selected>250</option>
					</select>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- https://github.com/uxsolutions/bootstrap-datepicker -->
<script src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="vista-comun/js/modalVistaPrevia.js"></script>
<script src="vista-alerta-temprana/js/registroConsulta.js"></script>
