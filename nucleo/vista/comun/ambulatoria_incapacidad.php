<?php
	$ltAhora = new DateTime( $goDb->fechaHoraSistema() );
	$lcAhora = $ltAhora->format("Y-m-d");
?>
<form id="FormIncapacidad" name="FormIncapacidad" class="needs-validation" novalidate>

	<div class="form-row pb-2">
		<div class="col-12">
			<div class="input-group input-group-sm">
				<label id="lblcodigoCieOrdAmbR" for="txtCodigoCieOrdAmbR" class="col-4 col-md-3 col-lg-2 col-xl-2 p-0">Dx Relacionado</label>
				<input type="text" class="form-control form-control-sm font-weight-bold ignore col-auto" name="txtCodigoCieOrdAmbR" id="txtCodigoCieOrdAmbR" autocomplete="off">
				<button type="button" id="btnLimpiarDxRel" class="btn btn-danger btn-sm" data-placement="top" title="Limpiar Diagnóstico Relacionado"><i class="fas fa-trash"></i></button>
			</div>
		</div>

		<div class="input-group input-group-sm col-12">
			<input type="text" class="form-control form-control-sm font-weight-bold col-4 col-md-3 col-lg-2 col-xl-2" name="cCodigoCieOrdAmbR" id="cCodigoCieOrdAmbR" readonly="readonly">
			<input type="text" class="form-control form-control-sm font-weight-bold col-auto" name="cDescripcionCieOrdAmbR" id="cDescripcionCieOrdAmbR" readonly="readonly">
		</div>
	</div>

	<div class="row pb-2">
		<div class="col-sm-3 col-md-3">
			<label id="lblTipoIncapacidad" for="selTipoIncapacidad">Tipo de Incapacidad</label>
			<select class="custom-select custom-select-sm d-block w-100" id="selTipoIncapacidad" name="TipoIncapacidad">
				<option value=""></option>
				<option value="AMB">Incapacidad</option>
				<option value="PRO">Prórroga de Incapacidad</option>
				<option value="RET">Incapacidad Retroactiva</option>
			</select>
		</div>
		<div class="col-sm-3 col-md-3">
			<label id="lblOrigenIncapacidad" for="selOrigenIncapacidad">Presunto Origen Incapacidad</label>
			<select class="custom-select custom-select-sm d-block w-100 ordamb-incap" id="selOrigenIncapacidad" name="OrigenIncapacidad" disabled="disabled">
				<option value=""></option>
			</select>
		</div>
		<div class="col-sm-3 col-md-4">
			<label id="lblCausaAtencion" for="selCausaAtencion">Causa que Motiva la Atención</label>
			<select class="custom-select custom-select-sm d-block w-100 ordamb-incap" id="selCausaAtencion" name="CausaAtencion" disabled="disabled">
				<option value=""></option>
			</select>
		</div>
		<div class="col-sm-3 col-md-2 col-lg-2">
			<label id="lblProrroga" for="selProrroga">Es prórroga</label>
			<select class="custom-select custom-select-sm d-block w-100 ordamb-incap" id="selProrroga" name="Prorroga" disabled="disabled">
				<option value=""></option><option value="S">Si</option><option value="N">No</option>
			</select>
		</div>
	</div>

	<div class="row pb-2">
		<div class="col-sm-8 col-md-6">
			<label id="lblFechaIncapacidad" for="txtFechaIniRetroactiva">Rango de Fechas</label>
			<div class="input-group input-daterange input-daterange-incapacidad">
				<div class="input-group-prepend"><span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span></div>
				<input type="text" class="form-control form-control-sm fecha-incapacidad ordamb-incap" id="txtFechaDesde" name="FechaDesde" value="<?= $lcAhora ?>" disabled="disabled">
				<div class="input-group-addon"><label class="pl-2 pt-1 pr-2 m-0">hasta</label></div>
				<div class="input-group-prepend"><span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span></div>
				<input type="text" class="form-control form-control-sm fecha-incapacidad ordamb-incap" id="txtFechaHasta" name="FechaHasta" value="<?= $lcAhora ?>" disabled="disabled">
			</div>
		</div>

		<div class="col-sm-4 col-md-3 col-lg-2">
			<label id="lblDiasIncapacidad" for="txtDiasIncapacidad">Días incapacidad</label>
			<input type="number" class="form-control form-control-sm ordamb-incap" id="txtDiasIncapacidad" name="DiasIncapacidad" min="0" max="360" value="" disabled="disabled">
		</div>
	</div>

	<div class="row" id="divIncapacidadRetroactiva">
		<div class="col-sm-6">
			<label id="lblIncapacidadRetroactiva" for="selIncapacidadRetroactiva">Incapacidad Retroactiva</label>
			<div class="form-group">
				<select class="custom-select custom-select-sm d-block w-100 ordamb-incap" id="selIncapacidadRetroactiva" name="IncapacidadRetroactiva" disabled="disabled">
					<option value=""></option>
				</select>
			</div>
		</div>
		<div class="col-sm-6">
			<label id="lblFechaIniRetroactiva" for="txtFechaIniRetroactiva">Rango Incapacidad Retroactiva</label>
			<div class="input-group input-daterange input-daterange-retroactiva">
				<div class="input-group-prepend"><span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span></div>
				<input type="text" class="form-control form-control-sm fecha-retroactiva ordamb-incap" id="txtFechaIniRetroactiva" name="FechaIniRetroactiva" value="" disabled="disabled">
				<div class="input-group-addon"><label class="pl-2 pt-1 pr-2 m-0">hasta</label></div>
				<div class="input-group-prepend"><span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span></div>
				<input type="text" class="form-control form-control-sm fecha-retroactiva ordamb-incap" id="txtFechaFinRetroactiva" name="FechaFinRetroactiva" value="" disabled="disabled">
			</div>
		</div>
	</div>

	<div class="row pb-2">
		<div class="col">
			<label id="lblobservacionesIncapacidad" for="observacionesIncapacidad"><b>Observaciones</b></label>
			<textarea class="form-control form-control-sm ordamb-incap" id="txtobservacionesIncapacidad" name="ObservacionesIncapacidad" rows="8" disabled="disabled"></textarea>
		</div>
	</div>

	<div class="row" id="divIncapacidadHospitalaria">
		<div class="col-9 col-sm-8 col-md-5 col-lg-4 col-xl-3">
			<label id="lblIncapacidadHospitalaria" for="selIncapacidadHospitalaria">¿Requiere Incapacidad Hospitalaria? </label>
		</div>
		<div class="col-3 col-sm-4 col-md-2 col-lg-2 col-xl-1">
			<select id="selIncapacidadHospitalaria" name="IncapacidadHospitalaria" class="custom-select ordamb-incap">
				<option value=""></option><option value="S">Si</option><option value="N">No</option>
			</select>
		</div>
	</div>
</form>

<script src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="vista-comun/js/modalidadGrupoServicio.js"></script>
<script type="text/javascript">
	var gnFechaActual = '<?= $lcAhora ?>';
</script>
