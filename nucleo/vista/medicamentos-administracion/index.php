<div class="container-fluid" oncontextmenu="return false">
	<div class="card mt-3">
		<div class="card-header">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-8 col-lg-11">
					<h5>Administración de Medicamentos</h5>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-2 col-lg-1">
					<button id="btnLimpiar" type="button" class="btn btn-secondary btn-sm" accesskey="L"><u><b>L</b></u>impiar</button>
				</div>
			</div>
			<div id="divFiltro">
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-3 col-lg-2 col-12 pb-2">
						<label for="lnNumeroIngreso"><b>Número Ingreso</b></label>
						<input class="form-control form-control-sm" type="number" name="lnNumeroIngreso" id="lnNumeroIngreso" placeholder="Número de Ingreso" pattern="^[0-9]*$" min="1111" max="99999999" maxlength="13" tabindex="1" autocomplete="off" value="" required="">
					</div>

				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-3 col-lg-2 col-12 pb-2">
						<button id="btnBuscar" type="button" class="btn btn-secondary btn-sm" accesskey="B"><u><b>B</b></u>uscar</button>
					</div>
				</div>
			</div>
			<div id="divDatosPaciente">
				<div class="row">
					<div id="infoPaciente" class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					</div>
				</div>
				<div class="row">
					<div id="infoAlergias" class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
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
					<i class="fas fa-circle-notch fa-xs fa-spin" style="color:#f00"></i>
				</div>
		</div>
		<div id="divBody" style="display: none;">
			<div id="divMedicamentosPaciente" class="card-body" style="display:none;">
				<div class="row" id="divMedicamentos" style="display: none;">
					<table  class="table table-responsive table-condensed table-hover" border="2"  cellspacing="0" cellpadding="0" ><!--style="width:100px;"-->
						<thead class="" bgcolor="#CCCCCC">
							<tr>
								<td>FECHA</td>
								<td style="padding: 12px 2px 0px 2px;">HORA</td>
								<td>CODIGO</td>
								<td>MEDICAMENTOS</td>
								<td>DOSIS</td>
								<td>FRECUENCIA</td>
								<td>VIA_ADMIN</td>
								<td>OBSERVACIONES_MEDICO</td>
								<td>OBSERVACIONES_ADMINISTRACIÓN</td>
							</tr>
						</thead>
						<tbody class="" id="loMedicamentosNoProgramados" style="font: .80em arial;">
						</tbody>
						<tbody class="" id="loMedicamentosProgramados" style="font: .80em arial;">
						</tbody>
					</table>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-12" id="divCodigoMedicamento" style="display: none;">
					<form class="" action="" method="post" id="frmConsultarCodigoQr">
						<div class="card border-success mb-2 text-white bg-secondary mb-3 text-center">
							<h5 class="card-header" style="padding: 1px;">Lectura Código QR</h5>
							<div class="card-body" style="padding: 1px;">
								<div class="input-group mb-3">
								  <div class="input-group-prepend">
									<label class="input-group-text" for="lnCodigoQr"><strong>CÓDIGO : </strong></label>
								  </div>
								  <input class="form-control" type="number" name="lnCodigoQr" id="lnCodigoQr" value="" tabindex="1" placeholder="Código Medicamento" autocomplete="off"  pattern="^[0-9]*$" min="11111" max="999999999999" maxlength="26" title="El dato ingresado debe contener solamente números" style="text-align:right">
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="row" id="divMedicamentosQueDiluyen" style="display:none;">
					<table  class="table table-hover table-condensed" border="1"  cellspacing="5" cellpadding="5" id="tblListaDiluyentes" >
						<thead bgcolor="#CCCCCC">
							<tr>
								<th>CODIGO</th>
								<th><center>MEDICAMENTO DILUYENTE</center></th>
								<th>CANTIDAD</th>
							</tr>
						</thead>
						<tbody id="loMedicamentosQueDiluyen" bgcolor="#C0DCFE">
						</tbody>
						<input type="hidden" class="" name="lnCodigoQrDiluyente" id="lnCodigoQrDiluyente" />
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="vista-medicamentos-administracion/js/scripts.js"></script>
<script src="vista-medicamentos/js/funciones.js"></script>