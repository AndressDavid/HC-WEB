<div class="card card-block">
	<div class="card-header" id="headerFinalidad">
		<a href="#divEgreso" class="card-link text-dark"><b>Datos de Egreso</b></a>
	</div>

	<div class="card-body" id="divEgreso" >
		<form role="form" id="FormEgreso" name="FormEgreso" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
			
			<div class="form-row pb-2" id="divFallece">
				<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
					<div class="input-group input-group-sm">							
						<label id="lblDxFallece" for="buscarDxFallece" class="required">Diagnóstico Fallece</label>
						<label> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </label>
						<input type="text" class="form-control form-control-sm font-weight-bold ignore col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" name="buscarDxFallece" id="buscarDxFallece" autocomplete="off">
					</div>
				</div>
				
				<div class="input-group input-group-sm">
					<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-1 col-xl-1" name="cCodigoDxFallece" id="cCodigoDxFallece" readonly="readonly">
					<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-11 col-xl-11" name="cDescripcionDxFallece" id="cDescripcionDxFallece" readonly="readonly">
				</div>
			</div>
			
			<div class="row pb-2">
				<div class="col-lg-12 col-md-4 col-sm-12 col-12">
					<label id="lblCondiciones" for="edtCondiciones">Condiciones Generales</label>
				</div>
				<div class="col-12">
					<textarea type="text" class="form-control" id="edtCondiciones" name="Condicion"></textarea>
				</div>
				<div class="col-lg-12 col-md-4 col-sm-12 col-12">
					<label id="lblManejo" for="edtManejo">Plan de Manejo</label>
				</div>
				<div class="col-12">
					<textarea type="text" class="form-control" id="edtManejo" name="Manejo"></textarea>
				</div>
				<div class="col-lg-3 col-md-6 col-sm-12">
					<div class="row pb-2">
						<div class="col-12">
							<label id="lblFechaE" for="lcfechaEgreso">Fecha Egreso</label>
						</div>
						<div class="col-12">
							<input id="lcFechaEgreso" name="FechaEgreso" class="form-control form-control" type="date" value="<?php echo date("Y-m-d"); ?>" title="Fecha de Egreso" tabindex="2" align="right" readonly>
						</div>
					</div>
				</div>

				<div class="col-lg-4 col-md-6 col-sm-12">
					<div class="row pb-2">
						<div class="col-12">
							<label id="lblEstado" for="selEstado">Estado de Salida</label>
						</div>
						<div class="col-12">
							<select id="selEstado" name="SelEstado" class="custom-select">
							</select>
						</div>
						<Input id="txtEstado" name="Estado" type="hidden"/>
					</div>
				</div>

				<div class="col-lg-3 col-md-6 col-sm-12" id="divFecha">
					<div class="row pb-2">
						<div class="col-12">
							<label id="lblFechaFallece" for="FechaFallece">Fecha Fallece</label>
						</div>
						<div class="col-12">
							<input id="FechaFallece" name="FechaFallece" class="form-control form-control" type="date" value="<?php echo date("Y-m-d"); ?>" title="Fecha Fallece" tabindex="2" align="right">
						</div>
					</div>
				</div>

				<div class="col-lg-2 col-md-6 col-sm-12" id="divHora">
					<div class="row pb-2">
						<div class="col-12">
							<label id="lblHoraFallece" for="HoraFallece">Hora Fallece</label>
						</div>
						<div class="col-12">
							<input id="HoraFallece" name="HoraFallece" class="form-control form-control" type="time" value="" title="Hora Fallece" tabindex="2" align="right">
						</div>
					</div>
				</div>
			</div>
			
			<div class="form-row pb-2" id="divMuerteEncefalica" style="display: none;">
				<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
					<div class="input-group input-group-sm">
						<label id="lblMuerteEncefalica" for="MuerteEncefalica">¿El paciente tuvo signos de muerte encefálica? </label>
						<div class="col-12 col-sm-12 col-md-6 col-lg-3 col-xl-2">
							<select id="selMuerteEncefalica" name="MuerteEncefalica" class="custom-select">
								<option value=""></option>
								<option value="N">No</option>
								<option value="S">Si</option>
							</select>
							
						</div>
					</div>
				</div>
			</div>
		
			<div class="form-row pb-2" id="divCondicionDestinoUusario">
				<div class="col-12 col-sm-12 col-md-12 col-lg-8 col-xl-6">
					<div class="input-group">
						<label id="lblCondicionEgreso" for="CondicionEgreso">Condición destino egreso </label>
							<select class="custom-select d-block w-100" id="selCondicionEgreso" name="CondicionEgreso" required>
							</select>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript" src="vista-epicrisis/js/EstadosSalida.js"></script>
<script type="text/javascript" src="vista-epicrisis/js/Egreso.js"></script>
<script type="text/javascript" src="vista-comun/js/condicionDestinoEgreso.js"></script>