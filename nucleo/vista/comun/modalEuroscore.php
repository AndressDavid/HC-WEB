<div class="modal fade" id="divEuroscore" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<!--<div class="modal-dialog" role="document">-->
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title col-11 text-center" id="exampleModalLabel">CALCULO EUROSCORE</h5>
			</div>

			<div id="divCapturaAgendaBody" class="modal-body">
				<form id="FormEuroscore" name="FormEuroscore" class="needs-validation">
					<div class="row">
						<div class="col-12 col-sm-12 col-md-12 col-lg-5 col-xl-5" style="border: 1px solid #9C9C9C; margin-left: 15px; margin-right: 25px">
						
						
							<label type="hidden" id="lblFactoresPaciente" style="background-color: #2196F3;">FACTORES DEL PACIENTE</label>
							<div class="input-group">
								<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6">
									<label id="lblEdadEuroscore" for="txtEdadEuroscore">Edad (años)</label>
								</div>
								<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6">
									<input id="txtEdadEuroscore" name="EdadEuroescoreUci" type="number" class="form-control"  data-id="1" disabled>
								</div>
							</div>
							
							<div class="input-group">
								<div class="col-12 col-sm-12 col-md-12 col-lg-4 col-xl-4">
									<label type="hidden" id="lblSexoEuroscore" for="selSexoEuroscore">Sexo</label>
								</div>
								<div class="col-12 col-sm-12 col-md-12 col-lg-8 col-xl-8">
									<select id="selSexoEuroscore" name="InfeccionUci" class="custom-select" data-id="2" disabled></select>
								</div>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input check-euroscore" id="chkEnfPulCroUci" name="EnfPulCroUci" data-id="3">
									<label class="custom-control-label" for="chkEnfPulCroUci">Enfermedad pulmonar crónica</label>
								</div>
							</div>
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input check-euroscore" id="chkArtExtUci" name="ArtExtUci" data-id="4">
									<label class="custom-control-label" for="chkArtExtUci">Arteriopatía extracardiaca</label>
								</div>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input check-euroscore" id="chkDisNeuUci" name="DisNeuUci" data-id="5">
									<label class="custom-control-label" for="chkDisNeuUci">Disfunción neurológica</label>
								</div>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input check-euroscore" id="chkCirCarPreUci" name="CirCarPreUci" data-id="6">
									<label class="custom-control-label" for="chkCirCarPreUci">Cirugía cardiaca previa</label>
								</div>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input check-euroscore" id="chkCreSericaUci" name="CreSericaUci" data-id="7">
									<label class="custom-control-label" for="chkCreSericaUci">Creatinina Sérica</label>
								</div>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input check-euroscore" id="chkEndoActUci" name="EndoActUci" data-id="8">
									<label class="custom-control-label" for="chkEndoActUci">Endocarditis activa</label>
								</div>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input check-euroscore" id="chkSitPreCriUci" name="SitPreCriUci" data-id="9">
									<label class="custom-control-label" for="chkSitPreCriUci">Situación preoperatoria crítica</label>
								</div>
							</div>
						</div>
						
						<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6" style="border: 1px solid #9C9C9C; margin-right: 5px">
							<label type="hidden" id="lblFactoresPaciente" style="background-color: #2196F3;">FACTORES CARDIACOS</label>
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input check-euroscore" id="chkAngInesUci" name="AngInesUci" data-id="10">
									<label class="custom-control-label" for="chkAngInesUci">Angina inestable</label>
								</div>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input check-euroscore" id="chkFac3050Uci" name="Fac3050Uci" data-id="11">
									<label class="custom-control-label" for="chkFac3050Uci">30-50%</label>
								</div>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<label for="chkFracEyeccUci">Fracción de eyección de V.I.</label>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input check-euroscore" id="chkMenos30Uci" name="Menos30Uci" data-id="12">
									<label class="custom-control-label" for="chkMenos30Uci">< 30%</label>
								</div>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input check-euroscore" id="chkIamRecUci" name="IamRecUci" data-id="13">
									<label class="custom-control-label" for="chkIamRecUci">I.A.M. reciente</label>
								</div>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input check-euroscore" id="chkHiperPulmUci" name="HiperPulmUci" data-id="14">
									<label class="custom-control-label" for="chkHiperPulmUci">Hipertensión pulmonar</label>
								</div>
							</div>
				
							<label type="hidden" id="lblFactoresOperatorios" style="background-color: #2196F3;">FACTORES OPERATORIOS</label>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input check-euroscore" id="chkEmergenUci" name="EmergenUci" data-id="15">
									<label class="custom-control-label" for="chkEmergenUci">Emergencia</label>
								</div>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input check-euroscore" id="chkCirCoronUci" name="CirCoronUci" data-id="16">
									<label class="custom-control-label" for="chkCirCoronUci">Cirugía distinta a coronaria aislada</label>
								</div>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input check-euroscore" id="chkCirAortaUci" name="CirAortaUci" data-id="17">
									<label class="custom-control-label" for="chkCirAortaUci">Cirugía sobre la aorta torácica</label>
								</div>
							</div>
							
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input check-euroscore" id="chkRotSeptalUci" name="RotSeptalUci" data-id="18">
									<label class="custom-control-label" for="chkRotSeptalUci">Rotura septal post-infarto</label>
								</div>
							</div>
							
						</div>
					</div><br>
			
					<div class="row">
						<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6">
							<div class="input-group">
								<div class="col-12 col-sm-12 col-md-12 col-lg-7 col-xl-7">
									<label id="lblEuroScoreAditivo" for="txtEuroScoreAditivo">EuroScore Aditivo:</label>
								</div>
								<div class="col-12 col-sm-12 col-md-12 col-lg-5 col-xl-5">
									<input id="txtEuroScoreAditivo" name="EuroScoreAditivo" type="text" class="form-control" disabled>
								</div>
								
								<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6">
									<label id="lblGrupoEuroScore" for="txtGrupoEuroScore">Grupo:</label>
								</div>
								<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6">
									<input id="txtGrupoEuroScore" name="GrupoEuroScore" type="text" class="form-control" disabled>
								</div>
								
								<div class="col-12 col-sm-12 col-md-12 col-lg-7 col-xl-7">
									<label id="lblMortalidadEsperadaEuroScore" for="txtMortalidadEsperadaEuroScore">Mortalidad esperada:</label>
								</div>
								<div class="col-12 col-sm-12 col-md-12 col-lg-5 col-xl-5">
									<input id="txtMortalidadEsperadaEuroScore" name="MortalidadEsperadaEuroScore" type="text" class="form-control" disabled>
								</div>
							</div>
						</div>
						
						<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6">
							<div class="row">
								<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6">
									<label id="lblEuroScoreLogistico" for="txtEuroScoreLogistico">EuroScore Logístico:</label>
								</div>
								<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6">
									<input id="txtEuroScoreLogistico" name="EuroScoreLogistico" type="text" class="form-control" disabled>
								</div>
							</div><br>
							
							<div class="row justify-content-between">
								<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
									<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Aceptar" aria-pressed="true" id="btnAceptarEuroscore"><u>A</u>ceptar</button>
									<button type="button" class="btn btn-secondary" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnSalirEuroscore"> &nbsp; &nbsp;<u>S</u>alir &nbsp; </button>
								</div>
							</div>
						</div>
					</div>		
				</form>
			</div>
			
		</div>
	</div>
</div>
<script type="text/javascript" src="vista-comun/js/modalEuroscore.js"></script>
