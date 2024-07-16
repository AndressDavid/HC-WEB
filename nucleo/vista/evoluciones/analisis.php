<form role="form" id="FormAnalisis" name="FormAnalisis" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">

	<div class="form-row">
		<div class="form-row pb-2">
			<span class="input-group-text; width: 150px" id="btnRecordPlanManejo"><i class="fas fa-microphone"></i></span> &nbsp;
			<label id="lblManejo" for="edtManejo">Plan de Manejo</label>
		</div>
		<div class="col-12">
			<textarea rows="5" type="text" class="form-control <?= $lcCopyPaste ?>" id="edtManejo" name="Manejo"></textarea>
		</div>
	</div>

	<div class="form-row pt-2" id="divAnalisis">
		<span class="input-group-text; width: 150px" id="btnRecordAnalisisEpicrisis"><i class="fas fa-microphone"></i></span> &nbsp;
		<label id="lblAnalisis" for="edtAnalisis">Analisis para Epicrisis</label>
		<div class="col-12">
			<textarea rows="8" type="text" class="form-control <?= $lcCopyPaste ?>" id="edtAnalisis" name="Analisis"></textarea>
		</div>
	</div>

	<div class="form-row pt-2">
		<div class="col-3" id="divConducta">
			<label id="lblSeguir" for="selConductaSeguir">Conducta a seguir</label>
			<select id="selConductaSeguir" name="Seguir" class="custom-select">
			</select>
		</div>

		<div class="col-3" id="divEstadoSalida">
			<label id="lblEstado" for="selEstadoSalida">Estado de Salida</label>
			<select id="selEstadoSalida" name="Estado" class="custom-select">
			</select>
		</div>

		<div class="col-3" id="divFecha">
			<label id="lblFechaFallece" for="lcfechaFallece">Fecha Fallece</label>
			<input id="lcfechaFallece" name="FechaFallece" class="form-control form-control" type="date" value="<?php echo date("Y-m-d"); ?>" title="Fecha Fallece" tabindex="2" align="right">
		</div>

		<div class="col-3" id="divHora">
			<label id="lblHoraFallece" for="lcHoraFallece">Hora Fallece</label>
			<input id="lcHoraFallece" name="HoraFallece" class="form-control form-control" type="time" value="" title="Hora Fallece" tabindex="2" align="right">
		</div>
	</div>

	<div class="form-row pb-2" id="divFallece">
		<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
			<div class="input-group input-group-sm">
				<label id="lblDxFallece" for="buscarDxFallece" class="required">Diagnóstico</label>
				<label> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </label>
				<input type="text" class="form-control form-control-sm font-weight-bold ignore col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" name="buscarDxFallece" id="buscarDxFallece" autocomplete="off">
			</div>
		</div>

		<div class="input-group input-group-sm">
			<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-1 col-xl-1" name="cCodigoDxFallece" id="cCodigoDxFallece" readonly="readonly">
			<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-11 col-xl-11" name="cDescripcionDxFallece" id="cDescripcionDxFallece" readonly="readonly">
		</div>
	</div>
</form>
<script type="text/javascript" src="vista-evoluciones/js/analisis.js"></script>
<script type="text/javascript" src="vista-comun/js/estadoSalida.js"></script>
<script type="text/javascript" src="vista-comun/js/conductaSeguir.js"></script>
<script type="text/javascript" src="vista-comun/js/dxFallece.js"></script>
<script type="text/javascript" src="publico-complementos/moment-develop/2.29.1-dist/moment.min.js"></script>
