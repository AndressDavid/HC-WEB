<?php
include_once __DIR__ . '/../comun/antecedentesConsulta.php';
?>
<div id="divRegistroCenso" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="divRegistroCensoTitulo">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="divRegistroCensoTitulo"><span class="fas fa-notes-medical" style="color: #17A2B8;"></span> Registro de Información para el Censo y la Bitácora de Urgencias</h5>
			</div>
			<div class="modal-body">
			<form id="frmCabecera">
			<small>

				<div class="row">
					<div class="col-md-6 col-lg-6 col-xl-4 pb-1">
						<div class="row">
							<label for="selUbicacion" id="lblUbicacion" class="col-12">Ubicación en urgencias</label>
							<div class="input-group col-12">
								<select name="selUbicacion" id="selUbicacion" class="form-control form-control-sm" style="display: none;"></select>
								<div class="dropdown dropdown-tree" id="divUbicacion" style="width: 100%"></div>
							</div>
						</div>
					</div>
					<div class="col-md-6 col-lg-6 col-xl-4 pb-1">
						<div class="row">
							<label for="selPlanAdmin" id="lblPlanAdmin" class="col-12">Plan de Manejo Admin</label>
							<div class="input-group col-12">
								<select name="selPlanAdmin" id="selPlanAdmin" class="form-control form-control-sm"></select>
							</div>
						</div>
					</div>
					<div class="col-md-6 col-lg-6 col-xl-4 pb-1">
						<div class="row">
							<label for="selPlanMedico" id="lblPlanMedico" class="col-12">Plan de Manejo Médico</label>
							<div class="input-group col-12">
								<select name="selPlanMedico" id="selPlanMedico" class="form-control form-control-sm"></select>
							</div>
						</div>
					</div>
					<div class="col-md-6 col-lg-6 col-xl-4 pb-1">
						<div class="row">
							<label for="txtMedico" id="lblMedico" class="col-12">Médico Tratante</label>
							<div class="input-group col-12">
								<input type="text" class="form-control form-control-sm" name="txtMedico" id="txtMedico">
							</div>
						</div>
					</div>
					<div class="col-md-6 col-lg-6 col-xl-4 pb-1">
						<div class="row">
							<label for="selDieta" id="lblDieta" class="col-12">Dieta</label>
							<div class="input-group col-12">
								<select name="selDieta" id="selDieta" class="form-control form-control-sm"></select>
							</div>
						</div>
					</div>
					<div class="col-md-6 col-lg-6 col-xl-4 pb-1">
						<div class="row">
							<label for="selExcluirBit" id="lblExcluirBit" class="col-12">Excluir Bitácora</label>
							<div class="input-group col-12">
								<select name="selExcluirBit" id="selExcluirBit" class="form-control form-control-sm">
									<option value="SI">SI</option>
									<option value="NO" selected="selected">NO</option>
								</select>
							</div>
						</div>
					</div>
				</div>

				<div class="row pt-3">
					<div class="col-12">

						<ul class="nav nav-tabs" id="tabOpcionesRegCenso">
							<li class="nav-item" role="tabpanel">
								<a class="nav-link nav-link-censo active" data-toggle="tab" id="tabOptObsAdmin" href="#tabObsAdmin"><b>Observaciones Administrativas</b></a>
							</li>
							<li class="nav-item" role="tabpanel">
								<a class="nav-link nav-link-censo" data-toggle="tab" id="tabOptObsAsist" href="#tabObsAsist"><b>Observaciones Asistencial</b></a>
							</li>
							<li class="nav-item" role="tabpanel">
								<a class="nav-link nav-link-censo" data-toggle="tab" id="tabOptDiagnosticos" href="#tabDiagnosticos"><b>Diagnósticos</b></a>
							</li>
							<li class="nav-item" role="tabpanel">
								<a class="nav-link nav-link-censo" data-toggle="tab" id="tabOptEvoluciones" href="#tabEvoluciones"><b>Evoluciones</b></a>
							</li>
						</ul>

						<div id="tabContent" class="tab-content">

							<!-- Observaciones Administrativas -->
							<div class="tab-pane fade show active" id="tabObsAdmin">
								<div class="container-fluid pt-1">
									<div class="row">
										<div class="col-12">
											<div id="txtObservacionesAdmin" class="overflow-auto divTextObs" style="height:240px"></div>
										</div>
									</div>
									<div class="row pt-2">
										<div class="col-12">
											<label for="txtObsAdmin">Escriba las observaciones. Recuerde que esta información no se reflejará en la HC del Paciente</label>
											<textarea name="txtObsAdmin" id="txtObsAdmin" class="form-control form-control-sm" rows="3"></textarea>
										</div>
									</div>
								</div>
							</div>

							<!-- Observaciones Asistencial -->
							<div class="tab-pane fade" id="tabObsAsist">
								<div class="container-fluid pt-1">
									<div class="row">
										<div class="col-12">
											<div id="txtObservacionesAsist" class="overflow-auto divTextObs" style="height:240px"></div>
										</div>
									</div>
									<div class="row pt-2">
										<div class="col-12">
											<label for="txtObsAsist">Escriba las observaciones. Recuerde que esta información no se reflejará en la HC del Paciente</label>
											<textarea name="txtObsAsist" id="txtObsAsist" class="form-control form-control-sm" rows="3"></textarea>
										</div>
									</div>
								</div>
							</div>

							<!-- Diagnósticos -->
							<div class="tab-pane fade" id="tabDiagnosticos">
								<div class="container-fluid pt-2">
									<div class="row">
										<div class="col-12">
											<div id="txtObservacionesDiagnos" class="overflow-auto divTextObs" style="height:169px"></div>
										</div>
									</div>
									<div class="row pt-1">
										<label for="selDxPrincipal" class="col-lg-2 col-md-3 col-sm-4 col-12">Dx Principal</label>
										<div class="input-group col-lg-10 col-md-9 col-sm-8 col-12">
											<!-- <select name="selDxPrincipal" id="selDxPrincipal" class="form-control form-control-sm dx-RegCenso"></select> -->
											<input type="text" name="selDxPrincipal" id="selDxPrincipal" class="form-control form-control-sm dx-RegCenso" >
											<input type="hidden" name="codDxPrincipal" id="codDxPrincipal" >
										</div>
									</div>
									<div class="row pt-1">
										<label for="selDxRelacionado1" class="col-lg-2 col-md-3 col-sm-4 col-12">Dx Relacionado 1</label>
										<div class="input-group col-lg-10 col-md-9 col-sm-8 col-12">
											<!-- <select name="selDxRelacionado1" id="selDxRelacionado1" class="form-control form-control-sm dx-RegCenso"></select> -->
											<input type="text" name="selDxRelacionado1" id="selDxRelacionado1" class="form-control form-control-sm dx-RegCenso" >
											<input type="hidden" name="codDxRelacionado1" id="codDxRelacionado1" >
										</div>
									</div>
									<div class="row pt-1">
										<label for="selDxRelacionado2" class="col-lg-2 col-md-3 col-sm-4 col-12">Dx Relacionado 2</label>
										<div class="input-group col-lg-10 col-md-9 col-sm-8 col-12">
											<!-- <select name="selDxRelacionado2" id="selDxRelacionado2" class="form-control form-control-sm dx-RegCenso"></select> -->
											<input type="text" name="selDxRelacionado2" id="selDxRelacionado2" class="form-control form-control-sm dx-RegCenso" >
											<input type="hidden" name="codDxRelacionado2" id="codDxRelacionado2" >
										</div>
									</div>
									<div class="row pt-1">
										<label for="selDxRelacionado3" class="col-lg-2 col-md-3 col-sm-4 col-12">Dx Relacionado 3</label>
										<div class="input-group col-lg-10 col-md-9 col-sm-8 col-12">
											<!-- <select name="selDxRelacionado3" id="selDxRelacionado3" class="form-control form-control-sm dx-RegCenso"></select> -->
											<input type="text" name="selDxRelacionado3" id="selDxRelacionado3" class="form-control form-control-sm dx-RegCenso" >
											<input type="hidden" name="codDxRelacionado3" id="codDxRelacionado3" >
										</div>
									</div>
									<div class="row pt-1">
										<label for="txtObsDiagnostico" class="col-lg-2 col-md-3 col-sm-4 col-12">Observación</label>
										<div class="input-group col-lg-10 col-md-9 col-sm-8 col-12">
											<textarea name="txtObsDiagnostico" id="txtObsDiagnostico" class="form-control form-control-sm" rows="1"></textarea>
										</div>
									</div>
								</div>
							</div>

							<!-- Evoluciones -->
							<div class="tab-pane fade" id="tabEvoluciones">
								<div class="container-fluid pt-1">
									<div class="row">
										<div class="col-12">
											<div id="txtEvoluciones" class="overflow-auto divTextObs" style="height:240px"></div>
										</div>
									</div>
									<div class="row pt-2">
										<div class="col-12">
											<label for="txtInterconsultas">Interconsultas</label>
											<textarea name="txtInterconsultas" id="txtInterconsultas" class="form-control form-control-sm" disabled="disabled" rows="3"></textarea>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

			</small>
			</form>
			</div>

			<div class="modal-footer">
			<div class="container">
				<div class="row justify-content-between">
					<div class="col-lg-9 col-12 text-left">
						<!-- <button type="button" class="btn btn-sm btn-secondary btnRegCensoConsulta" id="btnIngresosRegCenso">Consulta Ingresos</button> -->
						<button type="button" class="btn btn-sm btn-secondary btnRegCensoConsulta" id="btnAdmisionRegCenso">Consulta Admisión</button>
						<button type="button" class="btn btn-sm btn-secondary btnRegCensoConsulta" id="btnAntecedentesRegCenso">Dx Antecede.</button>
						<!-- <button type="button" class="btn btn-sm btn-secondary btnRegCensoConsulta" id="btnProcedimientosRegCenso">Procedimientos</button> -->
					</div>
					<div class="col-lg-3 col-12 text-right">
						<button type="button" class="btn btn-sm btn-primary" id="btnGuardarRegCenso">Guardar</button>
						<button type="button" class="btn btn-sm btn-secondary" id="btnCerrarRegCenso" data-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
			</div>

		</div>
	</div>
</div>

<link rel="stylesheet" type="text/css" media="screen" href="vista-censourg/css/registro.css" />
<script type="text/javascript" src="vista-censourg/js/registro.js"></script>

<!-- requeridos para funcionar correctamente -->
<!--
<link rel="stylesheet" type="text/css" media="screen" href="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />
<link rel="stylesheet" type="text/css" media="screen" href="publico-complementos/dropdowntree/1.1.1/dropdowntree.css" />

<script type="text/javascript" src="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="publico-complementos/dropdowntree/1.1.1/dropdowntree.min.js"></script>
<script type="text/javascript" src="vista-comun/js/comun.js"></script>
<script type="text/javascript" src="vista-comun/js/listadiagnosticos.js"></script>
<script type="text/javascript" src="vista-comun/js/listaMedicos.js"></script>
-->
<?php
// include __DIR__ . '/../comun/js/usuario.js.php';
?>
