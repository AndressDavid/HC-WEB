<div class="container-fluid" oncontextmenu="return false"><!--oncontextmenu="return false"-->
	<div class="card mt-3">
		<div class="card-header">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-8 col-lg-11">
					<h5>Dispensación de Medicamentos</h5>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-2 col-lg-1">
					<button id="btnLimpiar" type="button" class="btn btn-secondary btn-sm" accesskey="L"><u><b>L</b></u>impiar</button>
				</div>
			</div>
			<div id="divFiltro" class="row">
				<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
					<label for="lnNumeroIngreso"><b>Ingreso</b></label>
					<input id="lnNumeroIngreso" type="number" class="form-control form-control-sm" name="lnNumeroIngreso" placeholder="Número de ingreso" value="" tabindex="1">
				</div>
				<div class="col-xs-12 col-sm-12 col-md-3 col-lg-2">
					<label class="control-label" for="lcFechaFormula"><b>Fecha</b></label>
					<input id="lcFechaFormula" name="lcFechaFormula" class="form-control form-control-sm" type="date" value="<?php echo date("Y-m-d"); ?>" title="Fecha de la formula" tabindex="2" align="right">
				</div>
				<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
					<label class="control-label" for="lcSeccion"><b>Sección</b></label>
					<select id="lcSeccion" class="form-control form-control-sm" name="lcSeccion" type="text" tabindex="3"></select>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-2 col-lg-3">
					<label class="control-label" for="lcVia"><b>Via Ingreso</b></label>
					<select id="lcVia" class="form-control form-control-sm" name="lcVia" type="text" tabindex="4"></select>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
					<label class="" for="btnBuscar">&nbsp;</label>
					<button id="btnBuscar" type="button" class="form-control form-control-sm btn btn-secondary btn-sm"  name="enviar" accesskey="B" tabindex="5"><u><b>B</b></u>uscar</button>
				</div>
			</div>
			<div id="divVolver" class=" " style="display: none;">
				<nav aria-label = "breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item" id="btnRegresar">
							<a href="#">
								<i class="far fa-building" accesskey="V">
								</i> <u><b>V</b></u>OLVER
							</a>
						</li>
						<li class="breadcrumb-item active" aria-current="page">DISPENSAR
						</li>
					</ol>
				</nav>
			</div>
			<div id="divDatosPaciente">
				<div class="row">
					<div id="infoPaciente" class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col"><br>
					<div id="divInfoAlert">
					</div>
				</div>
			</div>
		</div>
		<div class="row justify-content-center">
			<div id="divIconoEspera" class="fa-3x" style="display: none;">
				<i class="fas fa-circle-notch fa-xs fa-spin" style="color: #f00"></i>
			</div>
		</div>
		<div id="divBody" style="display: none;">
			<div id="divListaIngresosConFormula" class="card-body" style="display: none;">
					<center><h5 id="divFechaLista"></h5></center>
				<table id="tblListaIngresosConFormula" class="table table-sm table-condensed table-hover table-responsive-sm table-borderless">
					<thead bgcolor="#cccccc">
						<tr style="font: .80em arial;">
							<th style="text-align:center">INGRESO</th>
							<th style="text-align:center">NOMBRES Y APELLIDOS</th>
							<th style="text-align:center">UBICACIÓN</th>
							<th style="text-align:center">VÍA INGRESO</th>
						</tr>
					</thead>
					<tbody id="loListaIngresosConFormula" style="font: .80em arial;">
					</tbody>
				</table>
			</div>
			<!--div class="container-fluid"  		<div class="clearfix"></div>  -->
	
			<div id="divMedicamentosFormulados" class="card-body" style="display:none;">
				<center><h5 id="divFechaFormula"></h5></center>
				<!--table id="tblMedicamentosFormulados" class="table table-hover table-responsive" cellspacing="1" cellpadding="1"-->
				<table id="tblMedicamentosFormulados" class="table table-sm table-condensed table-hover table-responsive-sm table-borderless">
					<thead bgcolor="#CCCCCC">
						<tr style="font: .80em arial;">
							<th class="" style="text-align:center"><abbr title="CÓDIGO">CÓDIGO</abbr></th>
							<th class="" style="text-align:center"><abbr title="MEDICAMENTO">DESCRIPCIÓN</abbr></th>
							<th class="" style="text-align:center"><abbr title="DOSIS">DOSIS</abbr></th>
							<th class="" style="text-align:center"><abbr title="FRECUENCIA">FRECUENCIA</abbr></th>
							<th class="" style="text-align:center"><abbr title="VIA ADMIN">VIA_ADMIN</abbr></th>
							<th class="" style="text-align:center"><abbr title="DISPENSAR">DISPENSAR</abbr></th>
							<th class="" style="text-align:center" id="lcDispensando" ><abbr title="DISPENSANDO">DISPENSANDO</abbr></th>
							<th class="" style="text-align:center"><abbr title="TOTAL DISPENSADO">TOTAL_DISPE</abbr></th>
							<th class="" style="text-align:center"><abbr title="SALDO EN BODEGA">SALDO</abbr></th>
						</tr>
					</thead>
					<tbody id="loMedicamentosFormulados" style="font: .80em arial;">
					</tbody>
				</table>
			</div>
			<!--/div-->
			<div id="divBodegas" class="container-fluid" style="display: none;">
				<div  class="card border- Warning mb-3">
					<div class="row">
						<div class="col-lg-6">
							<?php
								$laBodegas = ($_SESSION[HCW_NAME]->oUsuario->getBodegas());
								$lcCenCosto = ($_SESSION[HCW_NAME]->oUsuario->getCentroCosto());
								if(is_array($laBodegas)==true){
									printf('<strong>BODEGA&nbsp;&nbsp;&#58;&nbsp;&nbsp;</strong><select class="custom-select custom-select-sm col-lg-8" id="lnBodega" name="lnBodega">');
									foreach($laBodegas as $lcBodegaId => $laBodega){
										$lcSelected = $laBodega['DEFAULT']==1 ? ' selected' : '';
										printf('<option value="%s"%s>%s</option>',$lcBodegaId,$lcSelected,($lcBodegaId.' - '.$laBodega['BODEGA']->cNombre));
									}
									printf('</select>');
								}
							?>
						</div>
						<div class="col-lg-4">
							<?php
								if(empty($lcCenCosto)==true){
									printf('<label class="control-label" for="lcCenCosto"><strong>C.COSTOS&nbsp;&nbsp;&#58;&nbsp;&nbsp;</strong></label>');
								}else{
									print('<label class="control-label" for="lcCenCosto"><strong>C.COSTOS&nbsp;&nbsp;&#58;&nbsp;&nbsp;</strong>'.$lcCenCosto->cId.'-'.trim(substr($lcCenCosto->cNombre, 0, 34)).'</label><input type="hidden" id ="lcCenCosto" value="'.$lcCenCosto->cId.'">');
								}
							?>
						</div>
						<label class="col-lg-2 control-label" id="lnDispensacion" for="lnDispensacion"><strong>DISPENSACIÓN</strong></label>
						<script>
							var vFechaHoy='<?php echo date('Y-m-d'); ?>';
							var vFechaAyer='<?php echo date ( 'Y-m-d' , strtotime ( '-1 day' , strtotime ( date('Y-m-d') ) ) ); ?>';
						</script>
					</div>
				</div>
			</div>
			<div id="divCodigoMedicamento" class="col-xs-12 col-sm-12 col-md-12" style="display: none;">
				<form class="" action="" method="post" id="frmConsultarCodigoQr">
					<div class="card border-success mb-2 text-white bg-secondary mb-3 text-center">
						<h5 class="card-header" style="padding: 1px;">Lectura Código QR</h5>
						<div class="card-body" style="padding: 1px;">
							<div class="input-group mb-3">
							  <div class="input-group-prepend">
								<label class="input-group-text" for="lnCodigoQr"><strong>CÓDIGO : </strong></label>
							  </div>
							  <input class="form-control" type="number" name="lnCodigoQr" id="lnCodigoQr" value="" tabindex="1" placeholder="Código Medicamento" autocomplete="off"  pattern="^[0-9]*$" min="11111" max="999999999999" maxlength="26" title="El dato ingresado debe contener solamente números" style="text-align:right">
							  <div class="input-group-prepend">
								<button type="submit" class="btn btn-default" name="enviar" id="btnDispensarMedicamento" value="btnDispensarMedicamento" tabindex="2" accesskey="L"><u><b>L</b></u>eer</button>
							  </div>
							</div>
						</div>
					</div>
				</form>
				<div class="col-xs-12">
					<button id="btnGuardarDispensacion" type="submit" class="btn btn-outline-secondary btn-lg btn-block" accesskey="G"><u><b>G</b></u>uardar</button>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="vista-medicamentos-dispensacion/js/scripts.js"></script>
<script src="vista-medicamentos/js/funciones.js"></script>