<div class="container-fluid h-100">
	<div class="row mh-100">
		<div class="col-12 p-2 mh-100">
			<div class="card mh-100">
				<div class="card-header">
					<div class="row align-items-center">
						<div class="col-12 col-md-8">
							<h5>Consulta de programaci&oacute;n de salas</h5>
						</div>
						<div class="col-12 col-md-4 text-right">
							<span class="text-danger">Contaminada <i class="fas fa-biohazard"></i></span> | <span class="text-primary">Ayudante <i class="fas fa-hand-holding-medical"></i></span>
						</div>						
					</div>
					<div id="formProgramacionSalas" class="row">
						<div class="col-sm-12 col-md-3 mb-1">
							<label for="selSalasConsulta">Sala</label>
							<select class="custom-select custom-select-sm" id="selSalasConsulta" name="selSalasConsulta">
								<?php
									printf('<option value=""></option>');
									foreach($loListaSalas as $laSalaC){
										$lcSala = (empty($lcSala)?$laSalaC['SECHAB'].' - '.$laSalaC['NUMHAB']:$lcSala);
										printf('<option value="%s"%s>%s</option>',$laSalaC['SECHAB'].' - '.$laSalaC['NUMHAB'],$lcSala==$laSalaC['SECHAB'].' - '.$laSalaC['NUMHAB']?' ':'',$laSalaC['SECHAB'].' - '.$laSalaC['NUMHAB']);
									}
								?>
							</select>
						</div>

						<div class="col-sm-12 col-md-3 mb-1">
							<label for="inicio">Fecha Desde</label>
							<div class="input-group w-100 date">
								<div class="input-group-prepend">
									<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
								</div>
								<input id="inicio" name="inicio" type="text" class="form-control form-control-sm" required="required" value="<?php print($ldFechaInicio); ?>">
							</div>
						</div>
						<div class="col-sm-12 col-md-3 mb-1">
							<label for="fin">Fecha Hasta</label>
							<div class="input-group w-100 date">
								<div class="input-group-prepend">
									<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
								</div>
								<input id="fin" name="fin" type="text" class="form-control  form-control-sm" required="required" value="<?php print($ldFechaFin); ?>">
							</div>
						</div>
						<div class="col-sm-12 col-md-3 mb-1">
							<label for="btnProgramacionSalasBuscar">&nbsp;</label>
							<button id="btnProgramacionSalasBuscar" type="submit" class="btn btn-secondary btn-sm w-100">Buscar</button>
						</div>
					</div>
				</div>
				<small>
					<div class="card-body">
						<div id="toolbarProgramacionSalas"></div>
						<table
						  id="tableProgramacionSalas"
						  data-toolbar="#toolbarProgramacionSalas"
						  data-show-refresh="true"
						  data-click-to-select="false"
						  data-show-export="true"
						  data-show-columns="true"
						  data-show-columns-toggle-all="true"
						  data-minimum-count-columns="10"
						  data-pagination="false"
						  data-id-field="CONSECUTIVO"
						  data-query-params="queryParams"
						  data-url="<?php print($lcRepPrefijoAjax); ?>ajax/monitor.ajax" >
						</table>
					</div>
				</small>
				<div class="card-footer pb-5">
					<div class="row text-right">
						<div class="col-12">
							<span class="text-danger">Contaminada <i class="fas fa-biohazard"></i></span> | <span class="text-primary">Ayudante <i class="fas fa-hand-holding-medical"></i></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" href="<?php print($lcRepPrefijoComplementos); ?>bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />
<script type="text/javascript" src="<?php print($lcRepPrefijoComplementos); ?>jquery-tableexport/tableExport.min.js"></script>
<script type="text/javascript" src="<?php print($lcRepPrefijoComplementos); ?>clipboard/2.0.6-dist/clipboard.min.js"></script>
<script type="text/javascript" src="<?php print($lcRepPrefijoComplementos); ?>bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="<?php print($lcRepPrefijoComplementos); ?>bootstrap-table/3.1.3-dist/bootstrap-table-locale-all.min.js"></script>
<script type="text/javascript" src="<?php print($lcRepPrefijoComplementos); ?>bootstrap-table/3.1.3-dist/extensions/export/bootstrap-table-export.min.js"></script>
<script type="text/javascript" src="<?php print($lcRepPrefijoComplementos); ?>bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="<?php print($lcRepPrefijoComplementos); ?>bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>		
<script src="<?php print($lcRepPrefijoComponentes); ?>js/monitor.js"></script>