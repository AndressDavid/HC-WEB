<?php
	require_once (__DIR__ .'/../../controlador/class.TiposDocumento.php');
	require_once (__DIR__ .'/../../controlador/class.Especialidades.php');
	require_once (__DIR__ .'/../../controlador/class.Cita.php');
	
	$lcMensaje = '';
	$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
	$lnFecha = $ltAhora->format("Ymd"); settype($lnFecha,"integer");
	$ltFuturo = new \DateTime( $goDb->fechaHoraSistema() );
	$ltFuturo->add(new DateInterval('P15D'));

	$ldFechaInicio = (isset($_GET['inicio'])?$_GET['inicio']:$ltAhora->format("Y-m-d"));
	$ldFechaFin = (isset($_GET['fin'])?$_GET['fin']:$ltFuturo->format("Y-m-d"));
	
	$loCita = new NUCLEO\Cita('JTM');

	$laCondicionesEspeciales = ['ARCOCUSIN'=>'Archivos, ocultar registros sin archivos cargados',
								'ARCMOSSIN'=>'Archivos, mostrar registros sin archivos cargados',
								'INGMOSSIN'=>'Ingreso, mostrar registros sin ingreso',
								'INGOCUSIN'=>'Ingreso, ocultar registros sin ingreso'];
?>
	<div class="container-fluid">
		<div class="card mt-3">
			<div class="card-header">
				<h5>Gesti&oacute;n de Citas por Telemedicina</h5>
				<div id="filterlistaCitasTelemedicina" >
					<div class="row">
						<div class="col-md-6 col-lg-4 col-xl-3 pb-2">
							<label>Ingreso</label>
							<input type="number" class="form-control form-control-sm" name="nIngreso" id="nIngreso" min="0" max="99999999" placeholder="" value="<?php print(isset($_GET['nIngreso'])?intval($_GET['nIngreso']):''); ?>">
						</div>
						<div class="col-md-6 col-lg-4 col-xl-3 pb-2">
							<label>Documento</label>
							<div id="documento" class="input-group">
								<select class="custom-select custom-select-sm col-6" id="cDocumento" name="cDocumento" >
									<option></option>
									<?php
										$laTiposDocumento = (new NUCLEO\TiposDocumento())->aTipos;
										
										foreach($laTiposDocumento as $lcTipoDocumento => $laTipoDocumento){
											$lcSelected = ((isset($_GET['cDocumento'])?$_GET['cDocumento']:'')==$laTipoDocumento['ABRV']?'selected="selected"':'');											
											printf('<option value="%s" %s>%s - %s</option>',$lcTipoDocumento,$lcSelected,$laTipoDocumento['ABRV'],$laTipoDocumento['NOMBRE']);
										}
									?>
								</select>
								<input type="text" id="nDocumento" name="nDocumento" class="form-control form-control-sm col-6" value="<?php print(isset($_GET['nDocumento'])?intval($_GET['nDocumento']):''); ?>">
							</div>
						</div>
						<div class="col-md-6 col-lg-4 col-xl-3 pb-2">
							<label>Especialidad</label>
							<select class="custom-select custom-select-sm" id="cEspecialidad" name="cEspecialidad">
								<option></option>
								<?php
									$laEspecialidades = (new NUCLEO\Especialidades('DESESP', true))->aEspecialidades;
										
									foreach($laEspecialidades as $lcEspecialidadCodigo => $lcEspecialidadNombre){
										$lcSelected = ((isset($_GET['cEspecialidad'])?$_GET['cEspecialidad']:'')==$lcEspecialidadCodigo?'selected="selected"':'');												
										printf('<option value="%s" %s>%s</option>',$lcEspecialidadCodigo,$lcSelected,$lcEspecialidadNombre);
									}
								?>
							</select>
						</div>	
						<div class="col-md-6 col-lg-4 col-xl-3 pb-2">
							<label>Procedimiento</label>
							<select class="custom-select custom-select-sm" id="cProcedimiento" name="cProcedimiento">
								<option></option>															
							</select>
						</div>							
						<div class="col-md-6 col-lg-4 col-xl-3 pb-2">
							<label>Periodo</label>
							<div class="form-inline row">
								<div class="form-group col-6 pr-0">
									<div class="input-group input-group-sm date w-100">
										<div class="input-group-prepend">
											<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
										</div>
										<input id="inicio" name="inicio" type="text" class="form-control" required="required" value="<?php print($ldFechaInicio); ?>">
									</div>
								</div>
								<div class="form-group col-6 pl-1">
									<div class="input-group input-group-sm date w-100">
										<div class="input-group-prepend">
											<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
										</div>
										<input id="fin" name="fin" type="text" class="form-control" required="required" value="<?php print($ldFechaFin); ?>">
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6 col-lg-4 col-xl-3 pb-2">
							<label>Estado</label>
							<select class="custom-select custom-select-sm" id="cEstado" name="cEstado">
								<option value="TODOS">TODOS</option>
								<?php 
									foreach($loCita->obtenerEstados() as $laEstado){
										$lcSelect = ((isset($_GET['cEstado'])?'a'.$_GET['cEstado']:'')=='a'.$laEstado['CODIGO']?'selected="seleted"':'');
										printf('<option value="%s" %s>%s</option>',$laEstado['CODIGO'], $lcSelect, $laEstado['DESCRIPCION']);
									}
								?>							
							</select>
						</div>	
						<div class="col-md-6 col-lg-4 col-xl-3 pb-2">
							<label>Condiciones especiales</label>
							<select class="custom-select custom-select-sm" id="cEspeciales" name="cEspeciales">
								<option></option>
								<?php
									foreach($laCondicionesEspeciales as $lcConEspCodigo => $lcConEspNombre){
										$lcSelected = ((isset($_GET['cEspeciales'])?$_GET['cEspeciales']:'')==$lcConEspCodigo?'selected="selected"':'');												
										printf('<option value="%s" %s>%s</option>',$lcConEspCodigo,$lcSelected,$lcConEspNombre);
									}
								?>								
							</select>
						</div>	
						<div class="col-md-6 col-lg-4 col-xl-3 pb-2">
							<label>M&eacute;dico (Agendado/Atiende)</label>
							<select class="custom-select custom-select-sm" id="cMedico" name="cMedico">
								<option></option>							
							</select>
						</div>	
						<div class="col-12">
							<div class="row justify-content-end">
								<div class="col-md-6 col-lg-4 col-xl-3 pb-2">
									<div class="row align-items-end pt-3">
										<div class="col-12 col-sm-6 pb-2 pb-md-0">
											<button id="btnBuscar"  type="button" class="btn btn-secondary btn-sm w-100" accesskey="B"><u>B</u>uscar</button>
										</div>
										<div class="col-12 col-sm-6 pb-2 pb-md-0">
											<a class="btn btn-secondary btn-sm w-100" accesskey="L" href="modulo-citas-telemedicina&p=listaCitasTelemedicina"><u>L</u>impiar</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row"><div class="col"><div id="ingresoInfo"><?php print($lcMensaje); ?></div></div></div>
			</div>
			<div id ="registrosCitaTelemedicina" class="card-body">
				<div id="toolbarlistaCitasTelemedicina">
					<div class="form-inline">
						<button id="btnAgregar"  type="button" class="btn btn-success" data-toggle="modal" data-target="#agregarIngresoTelemdicina" accesskey="A">Pasar cita a Telemedicina</button>	
					</div>
				</div>
				<table
				  id="tableListaCitasTelemedicina"
				  data-show-export="true"
				  data-toolbar="#toolbarlistaCitasTelemedicina"
				  data-show-refresh="true"
				  data-click-to-select="true"
				  data-show-export="false"
				  data-show-columns="true"
				  data-show-columns-toggle-all="true"
				  data-minimum-count-columns="5"
				  data-pagination="false"
				  data-query-params="queryParams"
				  data-row-style="rowStyle"
				  data-url="vista-citas-telemedicina/ajax/listaCitasTelemedicina.ajax" >
				</table>
			</div>
			
			<!-- Modal -->
			<div class="modal fade" id="agregarIngresoTelemdicina" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered modal-xl">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel">Pasar cita a Telemedicina</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<div class="alert alert-info" role="alert">Ingrese el tipo y numero de documento de identidad del paciente, luego seleccione la cita presente o futura disponible (citas anteriores al d&iacute;a de hoy no se pueden pasar, ademas se debieron programar previamente por el modulo de programación de citas), despu&eacute;s de verificar haga clic en el bot&oacute;n <b>Pasar cita a Telemedicina</b> para realizar dicha acci&oacute;n.</div>
							<h5>Paciente</h5>
							<div id="filterAgregarListaCitasTelemedicina" >
								<div class="row">
									<div class="col-6 pb-2">
										<label>Documento</label>
										<div id="documento" class="input-group">
											<select class="custom-select custom-select-sm col-6" id="cDocumentoAgregar" name="cDocumentoAgregar" >
												<option></option>
												<?php
													$laTiposDocumento = (new NUCLEO\TiposDocumento())->aTipos;
													foreach($laTiposDocumento as $lcTipoDocumento => $laTipoDocumento){									
														printf('<option value="%s">%s - %s</option>',$lcTipoDocumento,$laTipoDocumento['ABRV'],$laTipoDocumento['NOMBRE']);
													}
												?>
											</select>
											<input type="text" id="nDocumentoAgregar" name="nDocumentoAgregar" class="form-control form-control-sm col-6">
										</div>
									</div>
									<div class="col-6 pb-2">
										<label>Nombre</label>
										<div id="cNombreAgregar" name="cNombreAgregar" class="form-control form-control-sm overflow-hidden" disabled="disabled"></div>
									</div>						
								</div>
							</div>
							
							<div class="row">
									<div id ="agregarRegistrosCitaTelemedicina" class="col-12">
										<div id="toolbarAgregarListaCitasTelemedicina">
											<div class="form-inline">
												<button id="btnBuscarAgregar"  type="button" class="btn btn-secondary" accesskey="B"><u>B</u>uscar</button>
											</div>
										</div>
										<table
										  id="tableAgregarListaCitasTelemedicina"
										  data-toolbar="#toolbarAgregarListaCitasTelemedicina"
										  data-show-refresh="true"
										  data-single-select="true"
										  data-click-to-select="true"
										  data-query-params="queryParamsAgregar"
										  data-url="vista-citas-telemedicina/ajax/listaCitasTelemedicina.ajax?accion=listar-citas-no-telemedicina" >
										</table>
									</div>					
								</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
							<button type="button" class="btn btn-success" id="btnAgregarTelemedicina">Pasar cita a Telemedicina</button>
						</div>
					</div>
				</div>
			</div>			
			
			
			<div class="card-footer text-muted">
				<p>Si desea ver en detalle una cita, haga doble clic sobre la respectiva fila.</p>
			</div>
			
			<!-- Bootstrap Table -->
			<link rel="stylesheet" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />
			<script type="text/javascript" src="publico-complementos/jquery-tableexport/tableExport.min.js"></script>
			<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
			<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table-locale-all.min.js"></script>
			<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/extensions/export/bootstrap-table-export.min.js"></script>
			<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
			<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
			
			
			<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
			<script type="text/javascript" src="vista-comun/js/comun.js"></script>	
			<script type="text/javascript" src="vista-citas-telemedicina/js/listaCitasTelemedicina.js"></script>

		</div>
	</div>