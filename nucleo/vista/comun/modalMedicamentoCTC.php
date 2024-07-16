<div id="divMedicamentosCTC" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="divMedicamentosCTC" data-backdrop="static">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title col-11 text-center"  id="divMedicamentosCTC">JUSTIFICACION MEDICAMENTOS NO POS</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">

				<ul class="nav nav-tabs" id="myTab" role="tablist">
					<li class="nav-item" role="presentation">
						<a class="nav-link active" id="tabPagGeneral" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">General</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link" id="tabPagEfectosyB" data-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">Efectos y Bibliografia</a>
					</li>
				</ul>

				<div class="card border-top-1">
					<div class="tab-content" id="tabMedicaNP">
						<div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="tabPagGeneral">
							<div class="card-body">

								<form role="form" id="FormMedicamentoCTC1" name="FormMedicamentoCTC1" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">

									<div class="row pb-1" >
										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-lg-3 col-md-3 col-sm-12 col-12">
													<label id="lblNOPOS" for="NOPOS">NO POS</label>
												</div>
												<div class="col-lg-9 col-md-9 col-sm-12 col-12">
													<input class="form-control" id="NOPOS" name="nopos" type="text" placeholder="Medicamento No Pos" value="" readonly>
												</div>
											</div>
										</div>

										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-4">
													<input class="form-control" id="PresentaMed" name="PresentaMed" type="text" placeholder="Presentación" value="" readonly>
												</div>
												<div class="col-4">
													<input class="form-control" id="ConcentraMed" name="ConcentraMed" type="text" placeholder="Concentración" value="" readonly>
												</div>
												<div class="col-4">
													<input class="form-control" id="UnidadMed" name="UnidadMed" type="text" placeholder="Unidad" value="" readonly>
												</div>
											</div>
										</div>
									</div>

									<div class="row pb-1">
										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblTipoDosisNP" for="DosisNP">Dosis</label>
												</div>
												<div class="col-4">
													<input name="DosisNP" type="number" min="0" max="9999999" id="DosisNP" placeholder="Dosis" class="form-control mr-sm-2" readonly>
												</div>
												<div class="col-5">
													<select class="custom-select" id="selTipoDosisNP" name="TdosisNP" disabled></select>
												</div>
											</div>
										</div>
										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblFrecuenciaNP" for="FrecuenciaNP">Frecuencia</label>
												</div>
												<div class="col-4">
													<input name="FrecuenciaNP" type="number"  min="0" max='99'id="FrecuenciaNP" placeholder="Frecuencia" class="form-control mr-sm-2" readonly>
												</div>
												<div class="col-5">
													<select name="TFrecuencia" class="custom-select" id="selTipoFrecuenciaNP" disabled></select><br>
												</div>
											</div>
										</div>
									</div>

									<div class="row pb-1">
										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblTipoDosisDNP" for="DosisDNP">D. Diaria</label>
												</div>
												<div class="col-4">
													<input name="DosisDNP" type="number" min="0" max="9999999" id="DosisDNP" placeholder="Dosis Diaria" class="form-control mr-sm-2" readonly>
												</div>
												<div class="col-5">
													<select class="custom-select" id="selTipoDosisDNP" name="TdosisDNP" disabled></select>
												</div>
											</div>
										</div>
										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblTTratamientoNP" for="TTratamientoNP">Tiempo</label>
												</div>
												<div class="col-4">
													<input name="TTratamientoNP" type="number"  min="0" max='999'id="TTratamientoNP" placeholder="Tratamiento" class="form-control mr-sm-2" readonly>
												</div>
												<div class="col-5">
													<select name="TTratamiento" class="custom-select" id="selTTratamiento" disabled>
														<option></option>
														<option>DIAS</option>
													</select><br>
												</div>
											</div>
										</div>
									</div>

									<div class="row pb-1">
										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblCantNP" for="CantNP">Cantidad</label>
												</div>
												<div class="col-4">
													<input name="CantNP" type="number" min="0" max="9999999" id="CantNP" placeholder="Cantidad" class="form-control mr-sm-2" readonly>
												</div>
												<div class="col-5">
													<input class="form-control" id="TcantidadNP" name="TcantidadNP" type="text" placeholder="Tipo Cantidad" value="" readonly>
												</div>
											</div>
										</div>

										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblViaNP" for="selTipoViaNP">Vía</label>
												</div>
												<div class="col-9">
													<select name="ViaNP" class="custom-select" id="selTipoViaNP" placeholder="Vía" disabled></select>
												</div>
											</div>
										</div>
									</div>

									<div class="row pb-1">
										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblGrupoT" for="GrupoT">Grupo Terap.</label>
												</div>
												<div class="col-9">
													<input class="form-control" id="GrupoT" name="GrupoT" type="text" placeholder="Grupo" value="" readonly>
												</div>
											</div>
										</div>

										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblTiempoR" for="TiempoR">Respuesta</label>
												</div>
												<div class="col-9">
													<input class="form-control" id="TiempoR" name="TiempoR" type="text" placeholder="Tiempo" value="" readonly>
												</div>
											</div>
										</div>
									</div>

									<div class="row pb-1">
										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblRiesgoI" for="RiesgoI" class="required">Riesgo</label>
												</div>
												<div class="col-9">
													<select name="RiesgoI" class="custom-select" id="RiesgoI">
													</select><br>
												</div>
											</div>
										</div>

										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblInvima" for="Invima">Reg. Invima</label>
												</div>
												<div class="col-9">
													<input class="form-control" id="Invima" name="Invima" type="text" placeholder=" Reg. Invima" value="" readonly>
												</div>
											</div>
										</div>
									</div>

									<div class="row pb-1">
										<div class="col-12">
											<label id="lblResumenNP" for="ResumenNP">Resumen de HC que justifique el uso del servicio médico NO POS</label>
										</div>
									</div>
									<div class="row pb-1">
										<div class="col-12">
											<textarea type="text" class="form-control" id="ResumenNP" name="ResumenNP" rows="2" ></textarea>
										</div>
									</div><hr>

								</form>

								<form role="form" id="FormMedicamentoCTC2" name="FormMedicamentoCTC2" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">

									<div class="row pb-1">
										<div class="col-12">
											<div class="form-group">
												<div class="custom-control custom-checkbox custom-control-inline">
													<input type="checkbox" class="custom-control-input" id="chkExistePOS" name="chkExistePOS" value="0">
													<label class="custom-control-label" for="chkExistePOS">Existe Medicamento POS</label>
												</div>
											</div>
										</div>
									</div>

									<div class="row pb-1">
										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblPOS" for="buscarMedicaP">POS</label>
												</div>
												<div class="col-9">
													<input class="form-control" id="buscarMedicaP" name="buscarMedicaP" type="text" placeholder="Buscar Medicamento" value="" >
													<input id="codigoMedicaP" type="hidden">
													<input id="descripcionMedicaP" type="hidden">
												</div>
											</div>
										</div>

										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-4">
													<input class="form-control" id="PresentaMedP" name="PresentaMedP" type="text" placeholder="Presentación" value="" readonly>
												</div>
												<div class="col-4">
													<input class="form-control" id="ConcentraMedP" name="ConcentraMedP" type="text" placeholder="Concentración" value="" readonly>
												</div>
												<div class="col-4">
													<input class="form-control" id="UnidadMedP" name="UnidadMedP" type="text" placeholder="Unidad" value="" readonly>
												</div>
											</div>
										</div>
									</div>

									<div class="row pb-1">
										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblTipoDosisP" for="txtDosisP">Dosis</label>
												</div>
												<div class="col-4">
													<input name="DosisP" type="number" min="0" max="9999999" id="txtDosisP" placeholder="Dosis" class="form-control mr-sm-2">
												</div>
												<div class="col-5">
													<select class="custom-select" id="selTipoDosisP" name="TdosisP"></select>
												</div>
											</div>
										</div>
										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblFrecuenciaP" for="txtFrecuenciaP">Frecuencia</label>
												</div>
												<div class="col-4">
													<input name="FrecuenciaP" type="number" min="0" max="999" id="txtFrecuenciaP" placeholder="Frecuencia" class="form-control mr-sm-2">
												</div>
												<div class="col-5">
													<select name="TFrecuenciaP" class="custom-select" id="selTipoFrecuenciaP"></select><br>
												</div>
											</div>
										</div>
									</div>

									<div class="row pb-1">
										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblTipoDosisDP" for="txtDosisDP">D. Diaria</label>
												</div>
												<div class="col-4">
													<input name="DosisDP" type="number" min="0" max="9999999" id="txtDosisDP" placeholder="Dosis Diaria" class="form-control mr-sm-2">
												</div>
												<div class="col-5">
													<select class="custom-select" id="selTipoDosisDP" name="TdosisDP"></select>
												</div>
											</div>
										</div>
										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblTTratamientoP" for="txtTTratamientoP">Tiempo</label>
												</div>
												<div class="col-4">
													<input name="TratamientoP" type="number" min="0" max="999" id="txtTTratamientoP" placeholder="Tratamiento" class="form-control mr-sm-2">
												</div>
												<div class="col-5">
													<select name="TTratamientoP" class="custom-select" id="selTTratamientoP">
														<option></option>
														<option>DIAS</option>
													</select><br>
												</div>
											</div>
										</div>
									</div>

									<div class="row pb-1">
										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblCantP" for="txtCantP">Cantidad</label>
												</div>
												<div class="col-4">
													<input name="CantP" type="number" min="0" max="9999999" id="txtCantP" placeholder="Cantidad" class="form-control mr-sm-2">
												</div>
												<div class="col-5">
													<input class="form-control" id="txtTipoCantidadP" name="txtTipoCantidadP" type="text" value="">
												</div>
											</div>
										</div>
										<div class="col-12 col-lg-6">
											<div class="input-group">
												<div class="col-3">
													<label id="lblViaP" for="selTipoViaP">Vía</label>
												</div>
												<div class="col-9">
													<select name="ViaP" class="custom-select" id="selTipoViaP" placeholder="Vía"></select><br>
												</div>
											</div>
										</div>
									</div>
								</form>
							</div>
					  </div>

					  <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="tabPagEfectosyB">

						<div class="card-body">

							<form role="form" id="FormMedicamentoCTC3" name="FormMedicamentoCTC3" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
								<div class="row pb-1">
									<div class="col-12">
										<label id="lblEfectoNP" for="edtEfectoNP">Efecto deseado al tratamiento</label>
									</div>
								</div>

								<div class="row pb-1">
									<div class="col-12">
										<textarea type="text" class="form-control" id="edtEfectoNP" name="Efecto" rows="4" readonly></textarea>
									</div>
								</div>

								<div class="row pb-1">
									<div class="col-12">
										<label id="lblEfectosSNP" for="edtEfectoSNP">Efectos secundarios y posibles riesgos al tratamiento</label>
									</div>
								</div>

								<div class="row pb-1">
									<div class="col-12">
										<textarea type="text" class="form-control" id="edtEfectoSNP" name="EfectoS" rows="4" readonly></textarea>
									</div>
								</div>

								<div class="row pb-1">
									<div class="col-12">
										<label id="lblBibliografiaNP" for="edtBibliografiaNP">Bibliografia</label>
									</div>
								</div>

								<div class="row pb-1">
									<div class="col-12">
										<textarea type="text" class="form-control" id="edtBibliografiaNP" name="Bibliografia" rows="4" ></textarea>
									</div>
								</div>

							</form>
						</div>
					  </div>
					</div>
				</div>
			</div>

			<div class="modal-footer">
				<div class="row pb-1">
					<div class="col-12">
						<div class="form-group">
							<form role="form" id="FormMedicamentoCTC4" name="FormMedicamentoCTC4" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkPaciente" name="chkPaciente" value="1">
									<label class="custom-control-label" for="chkPaciente">El paciente esta informado de efectos secundarios y posibles riesgos</label>
								</div>
							</form>
						</div>
					</div>
				</div>
				<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelaMedCTC"><u>C</u>ancelar</button>
				<button type="button" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Guardar información" aria-pressed="true" id="btnGuardaMedCTC"><u>G</u>uardar</button>
			</div>

		</div>
	</div>
</div>
<script type="text/javascript" src="vista-comun/js/modalMedicamentoCTC.js"></script>
<script type="text/javascript" src="vista-comun/js/tiposRiesgo.js"></script>