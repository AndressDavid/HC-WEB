<div id="acordionAnalisis">
	<div class="card card-block">
		<div class="card-header">
			<a href="#divAnalisis" class="card-link text-dark" data-toggle="collapse" data-parent="#acordionAnalisis"><b>Analisis Epicrisis</b></a>
		</div>
		<div id="divAnalisis" class="collapse show">
			<div class="card-body">
				<form role="form" id="FormAnalisis" name="FormAnalisis" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
					<div class="form-row">
						<label id="lblAnalisis" for="edtAnalisis">Analisis para Epicrisis</label>
						<div class="col-12">
							<textarea rows="15" type="text" class="form-control" id="edtAnalisis" name="Analisis"></textarea>
						</div>
					</div>
					<div class="form-row">
						<label id="lblInterpreta" for="edtInterpreta">Interpretaciones de resultados de apoyo diagnósticos</label>
						<div class="col-12">
							<textarea rows="15" type="text" class="form-control" id="edtInterpreta" name="Interpreta" readonly></textarea>
						</div>
					</div>
				</form>
			</div>
		</div>
		
		<div id="divHeaderFibrilacion" class="card-header" style="display: none;">
			<a href="#divFibrilacion" class="card-link text-dark" data-toggle="collapse" data-parent="#acordionAnalisis"><b>Fibrilación Auricular</b></a>
		</div>
		<div id="divFibrilacion" class="collapse">
			<div class="card-body">
				<form role="form" id="FormFibrilacion" name="FormFibrilacion" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
					<div class="row">
						<div class="col aling-self-start"></div>
						<div class="col aling-self-center">
							<small>
								<table id="tblFibrilacion" class="table table-sm table-bordered table-striped table-responsive">

									<thead>
										<tr>
											<td class="text-center align-middle" style="width:70%"><b>DESCRIPCION</b></td>
											<td class="text-center align-middle" style="width:30%"><b>VALOR</b></td>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td id="lblPregunta01">Fibrilación Auricular POP</td>
											<td>
												<select id="selRespuesta01" name="Respuesta01" class="custom-select">
													<option></option>
													<option>Si</option>
													<option>No</option>
												</select>
											</td>
										</tr>

										<tr>
											<td id="lblPregunta02">Beta Bloqueo Preop</td>
											<td>
												<select id="selRespuesta02" name="Respuesta02" class="custom-select">
													<option></option>
													<option>Si</option>
													<option>No</option>
												</select>
											</td>
										</tr>

										<tr>
											<td id="lblPregunta03" >Hidrocortisona</td>
											<td style="width:20%">
												<select id="selRespuesta03" name="Respuesta03" class="custom-select">
													<option></option>
													<option>Si</option>
													<option>No</option>
												</select>
											</td>
										</tr>

										<tr>
											<td id="lblPregunta04" >Amiodarona</td>
											<td >
												<select id="selRespuesta04" name="Respuesta04" class="custom-select">
													<option></option>
													<option>Si</option>
													<option>No</option>
												</select>
											</td>
										</tr>

										<tr>
											<td id="lblPregunta05" >Cardioversión Eléctrica</td>
											<td >
												<select id="selRespuesta05" name="Respuesta05" class="custom-select">
													<option></option>
													<option>Si</option>
													<option>No</option>
												</select>
											</td>
										</tr>

										<tr>
											<td id="lblPregunta06" >Anticoagulación</td>
											<td >
												<select id="selRespuesta06" name="Respuesta06" class="custom-select">
													<option></option>
													<option>Si</option>
													<option>No</option>
												</select>
											</td>
										</tr>

										<tr>
											<td id="lblPregunta07" >Beta Bloqueo POP</td>
											<td >
												<select id="selRespuesta07" name="Respuesta07" class="custom-select">
													<option></option>
													<option>Si</option>
													<option>No</option>
												</select>
											</td>
										</tr>

										<tr>
											<td id="lblPregunta08" >Egreso con Fibrilación Auricular</td>
											<td >
												<select id="selRespuesta08" name="Respuesta08" class="custom-select">
													<option></option>
													<option>Si</option>
													<option>No</option>
												</select>
											</td>
										</tr>
									</tbody>
								</table>
							</small>
						</div>
						<div class="col aling-self-end"></div>
					</div>
				</form>
			</div>
		</div>

	</div>
</div>

<script type="text/javascript" src="vista-epicrisis/js/Analisis.js"></script>
<script type="text/javascript" src="publico-complementos/moment-develop/2.29.1-dist/moment.min.js"></script>