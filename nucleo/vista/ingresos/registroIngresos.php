<?php
	require_once (__DIR__ .'/../../publico/constantes.php');
	require_once (__DIR__ .'/../../controlador/class.AplicacionFunciones.php');
	require_once (__DIR__ .'/../../controlador/class.Estratos.php');
	require_once (__DIR__ .'/../../controlador/class.InstitutosPrestadoresSalud.php');
	require_once (__DIR__ .'/../../controlador/class.Ocupaciones.php');
	require_once (__DIR__ .'/../../controlador/class.PlanesTipo.php');
	require_once (__DIR__ .'/../../controlador/class.PlanesTipoAfiliado.php');
	require_once (__DIR__ .'/../../controlador/class.SeccionesHabitacion.php');
	require_once (__DIR__ .'/../../controlador/class.UbicacionesZonas.php');
	require_once (__DIR__ .'/../../controlador/class.Via.php');

	$lcTipoIngreso = (isset($_GET['q'])?$_GET['q']:'DEFAULT');
	$loAplicacionFunciones = new NUCLEO\AplicacionFunciones();

	$lcGet = '';
	foreach($_GET as $lcGetKey => $lcGetValue){
		if($lcGetKey<>'p'){
			$lcGet .= (!empty($lcGet)?'&':'').$lcGetKey."=".$lcGetValue;

		}
	}

	$laVias = array();
	foreach((new NUCLEO\Via())->aVias as $laVia){
		$laVias[] = ['CODIGO'=>$laVia['CODVIA'], 'NOMBRE'=>$laVia['DESVIA']];
	}
	$laZonas = (new NUCLEO\UbicacionesZonas())->getUbicacionesZonas();
	$laPlanesTipo = (new NUCLEO\PlanesTipo())->getPlanesTipo();
	$laPlanesTipoAfiliado = (new NUCLEO\PlanesTipoAfiliado())->getPlanesTipoAfiliado();
	$laEstratos = (new NUCLEO\Estratos())->getEstratos();

	$loSeccionesHabitacion = new NUCLEO\SeccionesHabitacion();
	$loSeccionesHabitacion->consultaSeccionesPrmtabHabilitadas();
	$laSeccionesHabitacion = $loSeccionesHabitacion->aSecciones;

?>
	<!-- modal de espera mientras se carga la pagina y los ajax -->
	<div class="modal fade" id="divEsperaModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="divEsperaTitulo" style="display: none;" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered border-0">
			<div class="modal-content bg-transparent border-0">
				<div class="modal-body text-center text-white" style="text-shadow: 0px 0px 4px #000000;">
					<i class="fas fa-circle-notch fa-spin fa-5x"></i>
					<h1 class="modal-title w-100" id="divEsperaTitulo"></h1>
					<p id="divEsperaMensaje"></p>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript" src="vista-comun/js/modalEspera.js"></script>
	<script type="text/javascript">oModalEspera.mostrar('Se est&aacute; preparando el entorno de trabajo', 'Espere por favor');</script>

	<!-- contenido propio del modulo -->
	<div class="container-fluid">
		<div class="card mt-3">
			<div class="card-header">
				<nav aria-label="breadcrumb">
					<div class="row">
						<div class="col-12 col-md-6 col-lg-7">
							<ol class="breadcrumb m-1">
								<li class="breadcrumb-item"><a id="aLinkVolver" href="modulo-ingresos&p=listaIngresos&<?php print($lcGet); ?>"><i class="fas fa-address-card mr-2"></i>Volver</a></li>
								<li class="breadcrumb-item active" aria-current="page" id="recordArea">Ingreso</li>
							</ol>
						</div>
						<div class="col-12 col-md-6 col-lg-5">
							<ol class="breadcrumb font-weight-bolder m-1">
								<li class="breadcrumb-item active" aria-current="page"><span><i class="fas fa-address-card mr-2"></i>Paciente</span></li>
								<li class="breadcrumb-item" id="nHistoriaAyuda">*</li>
								<li class="breadcrumb-item" id="nConsultaAyuda">*</li>
								<li class="breadcrumb-item" id="nCitaAyuda">*</li>
							</ol>
						</div>
					</div>
				</nav>
			</div>
			<div class="card-body">
				<form id="formPacienteInformacion" data-requerido-guardar="si" data-requerido-copiar="si" data-alias="paciente" data-title="Identificaci&oacute;n del Paciente" autocomplete="off">
					<div class="row">
						<fieldset class="col">
							<legend class="h6 text-muted bg-light p-1"><i class="fas fa-id-card pr-1"></i>Identificaci&oacute;n del Paciente</legend>

							<!-- linea A -->
							<div class="form-row">
								<div class="form-group col-md-6 col-lg-2">
									<label for="nIngreso" class="required">Ingreso</label>
									<input type="number" class="form-control form-control-sm font-weight-bold" id="nIngreso" name="nIngreso" aria-describedby="nIngresoAyuda" readonly="readonly" autocomplete="off" autofocus>
									<small id="nIngresoAyuda" class="form-text text-muted">Consecutivo de atenci&oacute;n.</small>
								</div>
								<div class="form-group col-md-6 col-lg-4">
									<label for="nPacienteId" class="required"><b>Documento</b></label>
									<div class="input-group input-group-sm">
										<div class="input-group-prepend">
											<button class="btn btn-outline-secondary" type="button" id="btnLeerCedulaPaciente" aria-label="Leer código de barras" data-toggle="modal" data-target="#modalLeerCedula" data-callback="lecturaCedulaPaciente"><i class="fas fa-barcode"></i></button>
										</div>
										<select class="custom-select font-weight-bold tipoDocumento" id="cPacienteId" name="cPacienteId" placeholder="Tipo de documento" autocomplete="off"></select>
										<input type="number" class="form-control form-control-sm font-weight-bold confirmar" data-label="Identificaci&oacute;n" id="nPacienteId" name="nPacienteId" aria-describedby="nIdAyuda" autocomplete="off">
									</div>
									<small id="nIdAyuda" class="form-text text-muted">Documento num&eacute;rico</small>
								</div>
								<div class="form-group col-md-6 col-lg-3">
									<label for="cPasaporte">Pasaporte</label>
									<input type="text" class="form-control form-control-sm font-weight-bold confirmar" data-label="Pasaporte" id="cPasaporte" name="cPasaporte" aria-describedby="cPasaporteAyuda" readonly="readonly">
									<small id="cPasaporteAyuda" class="form-text text-muted">Alfanum&eacute;rico</small>
								</div>
								<div class="form-group col-md-6 col-lg-3">
									<label for="cPacienteLugarExpedicion" class="required">Expedici&oacute;n</label>
									<input type="text" class="form-control form-control-sm font-weight-bold" id="cPacienteLugarExpedicion" name="cPacienteLugarExpedicion" aria-describedby="cLugarExpedicionAyuda" data-toggle="tooltip" data-placement="bottom" title="Lugar de expedición como aparece en el documento" autocomplete="off" maxlength="30">
									<small id="cPacienteLugarExpedicionAyuda" class="form-text text-muted">Lugar (Ciudad/Municipio)</small>
								</div>
							</div>

							<!-- linea B -->
							<div class="form-row">
								<div class="form-group col-md-6 col-lg-3">
									<label for="cPacienteNombre1" class="required">Primer Nombre</label>
									<input type="text" class="form-control form-control-sm font-weight-bold confirmar" data-label="Primer Nombre" name="cPacienteNombre1" id="cPacienteNombre1" placeholder="" value="" autocomplete="off" maxlength="40">
								</div>
								<div class="form-group col-md-6 col-lg-3">
									<label for="cPacienteNombre2">Segundo Nombre</label>
									<input type="text" class="form-control form-control-sm font-weight-bold confirmar" data-label="Segundo Nombre" name="cPacienteNombre2" id="cPacienteNombre2" placeholder="" value="" autocomplete="off" maxlength="40">
								</div>
								<div class="form-group col-md-6 col-lg-3">
									<label for="cPacienteApellido1" class="required">Primer Apellido</label>
									<input type="text" class="form-control form-control-sm font-weight-bold confirmar" data-label="Primer Apellido" name="cPacienteApellido1" id="cPacienteApellido1" placeholder="" value="" autocomplete="off" maxlength="40">
								</div>
								<div class="form-group col-md-6 col-lg-3">
									<label for="cPacienteApellido2">Segundo Apellido</label>
									<input type="text" class="form-control form-control-sm font-weight-bold confirmar" data-label="Segundo Apellido" name="cPacienteApellido2" id="cPacienteApellido2" placeholder="" value="" autocomplete="off" maxlength="40">
								</div>
							</div>


							<!-- linea C -->
							<div class="form-row">
								<div class="form-group col-md-6 col-lg-3">
									<label for="nNacimiento" class="required">Fecha nacimiento</label>
									<div class="input-group input-group-sm date">
										<div class="input-group-prepend">
											<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
										</div>
										<input type="text" class="form-control form-control-sm font-weight-bold confirmar" data-label="Fecha nacimiento" name="nNacimiento" id="nNacimiento" placeholder="" value="" autocomplete="off">
										<div class="input-group-append">
											<span class="input-group-text" id="cEdad">Edad</span>
										</div>
									</div>
									<small id="cNacimientonAyuda" class="form-text text-muted">Fecha en formato AAAA-MM-DD</small>
								</div>
								<div class="form-group col-md-6 col-lg-7">
									<label for="nEdad" class="required">Lugar de nacimiento</label>
									<div class="input-group input-group-sm ubicacionGeografica" id="ubicacionNacimientoPaciente">
										<select class="custom-select lugarPais" name="nNacioPais" id="nNacioPais" autocomplete="off"></select>
										<select class="custom-select lugarDepartamento" name="nNacioDepartamento" id="nNacioDepartamento" autocomplete="off" disabled="disabled"></select>
										<select class="custom-select lugarCiudad" name="nNacioCiudad" id="nNacioCiudad" autocomplete="off" disabled="disabled"></select>
									</div>
									<small id="ubicacionNacimientoPacienteAyuda" class="form-text text-muted">Pa&iacute;s / Departamento / Ciudad</small>
								</div>
								<div class="form-group col-md-6 col-lg-1">
									<label for="cPacienteGenero" class="required">G&eacute;nero</label>
									<select class="form-control form-control-sm font-weight-bold" name="cPacienteGenero" id="cPacienteGenero" autocomplete="off">
										<option value=""></option>
										<option value="M">Masculino</option>
										<option value="F">Femenino</option>
									</select>
								</div>
								<div class="form-group col-md-6 col-lg-1">
									<label for="cPacienteGrupoRH">G.RH</label>
									<select class="form-control form-control-sm font-weight-bold" name="cPacienteGrupoRH" id="cPacienteGrupoRH">
										<option value=""></option>
										<option value="O+">O+</option>
										<option value="O-">O-</option>
										<option value="A+">A+</option>
										<option value="A-">A-</option>
										<option value="B+">B+</option>
										<option value="B-">B-</option>
										<option value="AB+">AB+</option>
										<option value="AB-">AB-</option>
									</select>
								</div>
							</div>

							<!-- linea D -->
							<div class="form-row">
								<div class="form-group col-md-2">
									<label for="nFechaIngreso" class="required">Fecha / Hora</label>
									<div class="input-group input-group-sm">
										<div class="input-group-prepend"><span class="input-group-text" id="basic-addon1"><i class="fas fa-search"></i></span></div>
										<input type="text" class="form-control form-control-sm font-weight-bold" name="nFechaIngreso" id="nFechaIngreso" placeholder="" value="<?php print(date('Y-m-d')); ?>" autocomplete="off" readonly="readonly">
										<input type="text" class="form-control form-control-sm font-weight-bold" name="nHoraIngreso" id="nHoraIngreso" placeholder="" value="<?php print(date('H:i:s')); ?>" autocomplete="off" readonly="readonly">
									</div>
									<small id="cFechaIngresoAyuda" class="form-text text-muted">De ingreso</small>
								</div>
								<div class="form-group col-md-6">
									<label for="cIngresoVia" class="required">V&iacute;a</label>
									<select class="form-control form-control-sm font-weight-bold" name="cIngresoVia" id="cIngresoVia" autocomplete="off">
										<option value=""></option>
										<?php
										foreach($laVias as $laVia){
											printf('<option value="%s">%s</option>', $laVia['CODIGO'], $laVia['NOMBRE']);
										}
										?>
									</select>
								</div>
								<div class="form-group col-md-2">
									<label for="cSeccion">Secci&oacute;n</label>
									<select class="form-control form-control-sm font-weight-bold ingresoVia" name="cSeccion" id="cSeccion" autocomplete="off" disabled="disabled">
										<option value=""></option>
										<?php
										array_multisort(array_column($laSeccionesHabitacion, "DESCRIP"), SORT_ASC, $laSeccionesHabitacion );
										foreach($laSeccionesHabitacion as $laSeccionHabitacion){
											printf('<option value="%s">%s</option>', $laSeccionHabitacion['CODPISO'], $laSeccionHabitacion['DESCRIP']);
										}
										?>
									</select>
								</div>
								<div class="form-group col-md-2">
									<label for="cHabitacion">Habitaci&oacute;n</label>
									<select class="form-control form-control-sm font-weight-bold ingresoVia" name="cHabitacion" id="cHabitacion" autocomplete="off" disabled="disabled"></select>
								</div>
							</div>
						</fieldset>
					</div>
										
					<input id="cCapturaDistinta" name="cCapturaDistinta" type="hidden" value="">
					<input id="cCapturaManual" name="cCapturaManual" type="hidden" value="">
					<input id="cCodigoProcedimiento" name="cCodigoProcedimiento" type="hidden" value="">
					<input id="cConsultaPasaporte" name="cConsultaPasaporte" type="hidden" value="">
					<input id="cDiagnostico" name="cDiagnostico" type="hidden" value="">
					<input id="cIngresoViaAnterior" name="cIngresoViaAnterior" type="hidden" value="">
					<input id="cMetodo" name="cMetodo" type="hidden" value="INGRESO">
					<input id="cPacienteIdCapturaMetodo" name="cPacienteIdCapturaMetodo" type="hidden" value="MANUAL">
					<input id="cPacienteIdInicial" name="cPacienteIdInicial" type="hidden" value="">
					<input id="cPacienteRecideCiudadDisplay" name="cPacienteRecideCiudadDisplay" type="hidden" value="">
					<input id="cPacienteRespira" name="cPacienteRespira" type="hidden" value="">
					<input id="cResponsableGenero" name="cResponsableGenero" type="hidden" value="">
					<input id="cResponsableRecideCiudadDisplay" name="cResponsableRecideCiudadDisplay" type="hidden" value="">
					<input id="nCita" name="nCita" type="hidden" value="">
					<input id="nConsulta" name="nConsulta" type="hidden" value="">
					<input id="nFechaOrdenadoProcedimiento" name="nFechaOrdenadoProcedimiento" type="hidden" value="">
					<input id="nHistoria" name="nHistoria" type="hidden" value="">
					<input id="nPacienteIdInicial" name="nPacienteIdInicial" type="hidden" value="">
					<input id="nRaza" name="nRaza" type="hidden" value="">
					
				</form>

				<!-- linea E -->
				<div class="row mt-3 mb-5">
					<div class="col-12">
						<ul class="nav nav-tabs" id="propiedades" role="tablist">
							<li class="nav-item" role="presentation"><a class="text-dark nav-link font-weight-bolder active" id="paciente-tab" data-toggle="tab" href="#pacienteTab" role="tab" aria-controls="paciente" aria-selected="true"><i class="fas fa-hospital-user pr-2"></i>Paciente</a></li>
							<li class="nav-item" role="presentation"><a class="text-dark nav-link font-weight-bolder" id="responsable-tab" data-toggle="tab" href="#responsableTab" role="tab" aria-controls="responsable" aria-selected="false"><i class="fas fa-user-friends pr-3"></i>Responsable</a></li>
							<li class="nav-item" role="presentation"><a class="text-dark nav-link font-weight-bolder" id="servicio-tab" data-toggle="tab" href="#servicioTab" role="tab" aria-controls="servicio" aria-selected="false"><i class="fas fa-file-medical-alt pr-3"></i>Servicio</a></li>
							<li class="nav-item" role="presentation"><a class="text-dark nav-link font-weight-bolder" id="datos-tab" data-toggle="tab" href="#datosTab" role="tab" aria-controls="datos" aria-selected="false"><i class="fas fa-file-medical pr-3"></i>Datos Ingreso</a></li>
						</ul>
						<div class="card border-top-0">
							<div class="tab-content" id="TabPropiedades">
								<div class="tab-pane fade show active" id="pacienteTab" role="tabpanel" aria-labelledby="paciente-tab">
									<div class="card-body">

										<!-- linea PACIENTE 1 -->
										<form id="formPacienteGeneral" data-requerido-guardar="si" data-alias="general" data-title="Informaci&oacute;n general del paciente"  autocomplete="off">
											<div class="form-row">
												<fieldset class="col">
													<legend class="h6 text-muted bg-light p-1"><i class="fas fa-id-card pr-1"></i>Informaci&oacuten general</legend>
													<div class="row">
														<div class="form-group col-md-6 col-lg-4 col-xl-2">
															<label for="cPacienteEstadoCivil" class="required">Estado Civil</label>
															<select class="form-control form-control-sm font-weight-bold estadoCivil" name="cPacienteEstadoCivil" id="cPacienteEstadoCivil" autocomplete="off"></select>
														</div>
														<div class="form-group col-md-6 col-lg-4 col-xl-2">
															<label for="cEpicrisisEmail" class="required">Epicrisis</label>
															<select class="form-control form-control-sm font-weight-bold" name="cEpicrisisEmail" id="cEpicrisisEmail" autocomplete="off">
																<option value=""></option>
																<option value="SI">Si</option>
																<option value="NO">No</option>
															</select>
															<small id="cEpicrisisEmailAyuda" class="form-text text-muted">&iquest;Enviar por e-mail?</small>
														</div>
														<div class="form-group col-md-6 col-lg-4 col-xl-2">
															<label for="cPacientePermisoPermanencia" class="required">Permiso <small>de permanencia</small></label>
															<div class="input-group input-group-sm">
																<div class="input-group-prepend">
																	<select class="form-control form-control-sm font-weight-bold" name="cPacientePermisoPermanenciaTiene" id="cPacientePermisoPermanenciaTiene" disabled="disabled" autocomplete="off">
																		<option value=""></option>
																		<option value="SI">SI</option>
																		<option value="NO">NO</option>
																	</select>
																</div>
																<input type="text" class="form-control form-control-sm font-weight-bold confirmar" data-label="Permiso de permanencia" name="cPacientePermisoPermanencia" id="cPacientePermisoPermanencia" readonly="readonly">
															</div>
															<small id="cPacientePermisoPermanenciaAyuda" class="form-text text-muted">¿Tiene permiso especial?</small>
														</div>
														<div class="form-group col-md-6 col-lg-6 col-xl-3">
															<label for="cPacientePertenenciaEtnica" class="required">Pertenencia &Eacute;tnica</label>
															<select class="form-control form-control-sm font-weight-bold pertenenciaEtnica" name="cPacientePertenenciaEtnica" id="cPacientePertenenciaEtnica" autocomplete="off"></select>
														</div>
														<div class="form-group col-md-6 col-lg-6 col-xl-3">
															<label for="cPacienteNivelEducativo" class="required">Nivel Educativo</label>
															<select class="form-control form-control-sm font-weight-bold nivelEducativo" name="cPacienteNivelEducativo" id="cPacienteNivelEducativo" autocomplete="off"></select>
														</div>
													</div>
												</fieldset>
											</div>
										</form>

										<!-- linea PACIENTE 2 -->
										<form id="formPacienteRecide" data-requerido-guardar="si" data-requerido-copiar="si" data-alias="residencia" data-title="Lugar de residencia del paciente" autocomplete="off">
											<div class="form-row">
												<fieldset class="col">
													<legend class="h6 text-muted bg-light p-1"><i class="fas fa-house-user pr-1"></i>Lugar de residencia</legend>
													<div class="row">
														<div class="form-group col-lg-6 col-xl-4">
															<label for="nPacienteRecidePais" class="required">Pa&iacute;s / Departamento / Ciudad</label>
															<div class="input-group input-group-sm ubicacionGeografica" id="ubicacionResidenciaPaciente" data-callback="localidadResidenciaPaciente">
																<select class="custom-select lugarPais" name="nPacienteRecidePais" id="nPacienteRecidePais" autocomplete="off"></select>
																<select class="custom-select lugarDepartamento" name="nPacienteRecideDepartamento"id="nPacienteRecideDepartamento" autocomplete="off" disabled="disabled"></select>
																<select class="custom-select lugarCiudad" name="nPacienteRecideCiudad"id="nPacienteRecideCiudad" autocomplete="off" disabled="disabled"></select>
															</div>
														</div>
														<div class="form-group col-lg-6 col-xl-4">
															<label for="nPacienteRecideLocalidad" class="required">Localidad / Barrio / Zona</label>
															<div class="input-group input-group-sm">
																<select class="custom-select lugarLocalidad" name="nPacienteRecideLocalidad" id="nPacienteRecideLocalidad" autocomplete="off" disabled="disabled"></select>
																<input type="text" class="form-control form-control-sm font-weight-bold" name="cPacienteRecideBarrio" id="cPacienteRecideBarrio" autocomplete="off" maxlength="30">
																<select class="custom-select lugarZona" id="nPacienteRecideZona" name="nPacienteRecideZona" autocomplete="off">
																	<option></option>
																	<?php
																	foreach($laZonas as $laZona){
																		printf('<option value="%s">%s</option>', $laZona['CODIGO'], $laZona['NOMBRE']);
																	}
																	?>
																</select>
															</div>
														</div>
														<div class="form-group col-lg-12 col-xl-4">
															<label for="cPacienteRecideDireccion" class="required">Direcci&oacute;n</label>
															<input type="text" class="form-control form-control-sm font-weight-bold" name="cPacienteRecideDireccion" id="cPacienteRecideDireccion" maxlength="30" autocomplete="off">
															<small id="cPacienteRecideDireccionAyuda" class="form-text text-muted">Utilice n&uacute;meros y letras.</small>
														</div>
													</div>
												</fieldset>
											</div>
										</form>

										<!-- linea PACIENTE 3 -->
										<form id="formPacienteContacto" data-requerido-guardar="si" data-requerido-copiar="si" data-alias="contacto" data-title="Datos de contacto del paciente" autocomplete="off">
											<div class="form-row">
												<fieldset class="col">
													<legend class="h6 text-muted bg-light p-1"><i class="fas fa-mobile-alt pr-1"></i>Datos de contacto</legend>
													<div class="row">
														<div class="form-group col-md-6 col-lg-4 col-xl-3">
															<label for="cPacienteTelefono1" class="required">Tel&eacute;fono</label>
															<input type="number" class="form-control form-control-sm font-weight-bold" name="cPacienteTelefono1" id="cPacienteTelefono1" data-toggle="tooltip" data-placement="bottom" title="Escriba entre 7 y 15 caracteres num&eacute;ricos" autocomplete="off">
															<small id="cPacienteTelefono1Ayuda" class="form-text text-muted">Principal</small>
														</div>
														<div class="form-group col-md-6 col-lg-4 col-xl-2">
															<label for="cPacienteTelefono2">Tel&eacute;fono</label>
															<input type="number" class="form-control form-control-sm font-weight-bold" name="cPacienteTelefono2" id="cPacienteTelefono2" data-toggle="tooltip" data-placement="bottom" title="Escriba 7 caracteres num&eacute;ricos" autocomplete="off">
															<small id="cPacienteTelefono2Ayuda" class="form-text text-muted">Alternativo</small>
														</div>
														<div class="form-group col-md-6 col-lg-4 col-xl-2">
															<label for="cPacienteTelefono3" class="required">Celular</label>
															<input type="number" class="form-control form-control-sm font-weight-bold" name="cPacienteTelefono3" id="cPacienteTelefono3" data-toggle="tooltip" data-placement="bottom" title="Escriba entre 10 y 15 caracteres num&eacute;ricos" autocomplete="off">
															<small id="cPacienteTelefono3Ayuda" class="form-text text-muted">Principal</small>
														</div>
														<div class="form-group col-md-6 col-lg-4 col-xl-2">
															<label for="cPacienteTelefono4">Celular</label>
															<input type="number" class="form-control form-control-sm font-weight-bold" name="cPacienteTelefono4" id="cPacienteTelefono4" data-toggle="tooltip" data-placement="bottom" title="Escriba 10 caracteres num&eacute;ricos" autocomplete="off">
															<small id="cPacienteTelefono4Ayuda" class="form-text text-muted">Alternativo</small>
														</div>
														<div class="form-group col-md-6 col-lg-4 col-xl-3">
															<label for="cPacienteEmail" class="required">e-mail</label>
															<div class="input-group input-group-sm">
																<div class="input-group-prepend">
																	<select class="form-control form-control-sm font-weight-bold" name="cPacienteTieneEmail" id="cPacienteTieneEmail" autocomplete="off">
																		<option value=""></option>
																		<option value="SI">SI</option>
																		<option value="NO">NO</option>
																	</select>
																</div>
																<input type="email" class="form-control form-control-sm font-weight-bold confirmar" data-label="e-mail Paciente" name="cPacienteEmail" id="cPacienteEmail" data-toggle="tooltip" data-placement="bottom" title="Aseg&uacute;rese de escribirlo correctamente" autocomplete="off" readonly="readonly">
																<small id="cPacienteEmailAyuda" class="form-text text-muted">Los dominios m&aacute;s populares se escriben gmail.com, hotmail.com, icloud.com, outlook.com, yahoo.com, yahoo.es</small>
															</div>
														</div>
													</div>
												</fieldset>
											</div>
										</form>

										<!-- linea PACIENTE 4 -->
										<form id="formPacienteLaboral" data-requerido-guardar="si" data-requerido-copiar="si" data-alias="laboral" data-title="Informaci&oacute;n laboral del paciente" autocomplete="off">
											<div class="form-row">
												<fieldset class="col">
													<legend class="h6 text-muted bg-light p-1"><i class="fas fa-building pr-1"></i>Informaci&oacute;n laboral</legend>
													<div class="row">
														<div class="form-group col-md-6 col-lg-3">
															<label for="nPacienteLaboralOcupacion" class="required">Ocupaci&oacute;n</label>
															<select class="form-control form-control-sm font-weight-bold" name="nPacienteLaboralOcupacion" id="nPacienteLaboralOcupacion" autocomplete="off">
																<option value=""></option>
																<?php
																foreach((new NUCLEO\Ocupaciones())->getOcupaciones() as $laOcupacion){
																	printf('<option value="%s">%s</option>', $laOcupacion['CODIGO'], $laOcupacion['NOMBRE']);
																}
																?>
															</select>
														</div>
														<div class="form-group col-md-6 col-lg-3">
															<label for="cPacienteLaboralTrabajo" class="required">Trabajo</label>
															<select class="form-control form-control-sm font-weight-bold trabajo" name="cPacienteLaboralTrabajo" id="cPacienteLaboralTrabajo" autocomplete="off"></select>
														</div>
														<div class="form-group col-md-6 col-lg-3">
															<label for="cPacienteLaboralEmpresa">Lugar (Empresa)</label>
															<input type="text" class="form-control form-control-sm font-weight-bold pacienteTrabajo" name="cPacienteLaboralEmpresa" id="cPacienteLaboralEmpresa" readonly="readonly" autocomplete="off" maxlength="60">
														</div>
														<div class="form-group col-md-6 col-lg-3">
															<label for="cPacienteLaboralCargo">Cargo/Actividad</label>
															<input type="text" class="form-control form-control-sm font-weight-bold pacienteTrabajo" name="cPacienteLaboralCargo" id="cPacienteLaboralCargo" readonly="readonly" autocomplete="off" maxlength="30">
														</div>
													</div>
													<div class="row">
														<div class="form-group col-md-6 col-lg-4">
															<label for="cPacienteLaboralAntiguedad">Antig&uuml;edad</label>
															<input type="text" class="form-control form-control-sm font-weight-bold pacienteTrabajo" name="cPacienteLaboralAntiguedad" id="cPacienteLaboralAntiguedad" readonly="readonly" autocomplete="off" maxlength="30">
														</div>
														<div class="form-group col-md-6 col-lg-4">
															<label for="cPacienteLaboralDireccion">Direcci&oacute;n</label>
															<input type="text" class="form-control form-control-sm font-weight-bold pacienteTrabajo" name="cPacienteLaboralDireccion" id="cPacienteLaboralDireccion" readonly="readonly" autocomplete="off" maxlength="60">
															<small id="cPacienteLaboralDireccionAyuda" class="form-text text-muted">Utilice n&uacute;meros y letras.</small>
														</div>
														<div class="form-group col-md-6 col-lg-4">
															<label for="cPacienteLaboralTelefono">Tel&eacute;fono</label>
															<input type="number" class="form-control form-control-sm font-weight-bold pacienteTrabajo" name="cPacienteLaboralTelefono" id="cPacienteLaboralTelefono" readonly="readonly" autocomplete="off">
														</div>
													</div>
												</fieldset>
											</div>
										</form>

										<!-- linea PACIENTE 5 -->
										<form id="formPacienteReferencia" data-requerido-guardar="si" data-requerido-copiar="si" data-alias="referencia" data-title="Referencia familiar del paciente" autocomplete="off">
											<div class="form-row">
												<fieldset class="col">
													<legend class="h6 text-muted bg-light p-1"><i class="fas fa-people-arrows pr-1"></i>Referencia familiar</legend>
													<div class="row">
														<div class="form-group col-md-6 col-lg-4">
															<label for="cPacienteReferenciaNombre">Nombres y Apellidos</label>
															<input type="text" class="form-control form-control-sm font-weight-bold" name="cPacienteReferenciaNombre" id="cPacienteReferenciaNombre" autocomplete="off" maxlength="30">
														</div>
														<div class="form-group col-md-6 col-lg-4">
															<label for="cPacienteReferenciaDireccion">Direcci&oacute;n</label>
															<input type="text" class="form-control form-control-sm font-weight-bold" name="cPacienteReferenciaDireccion" id="cPacienteReferenciaDireccion" autocomplete="off" maxlength="60">
															<small id="cPacienteReferenciaDireccionAyuda" class="form-text text-muted">Utilice n&uacute;meros y letras.</small>
														</div>
														<div class="form-group col-md-6 col-lg-4">
															<label for="cPacienteReferenciaTelefono">Tel&eacute;fono</label>
															<input type="number" class="form-control form-control-sm font-weight-bold" name="cPacienteReferenciaTelefono" id="cPacienteReferenciaTelefono" autocomplete="off">
														</div>
													</div>
												</fieldset>
											</div>
										</form>
									</div>
								</div>

								<div class="tab-pane fade" id="responsableTab" role="tabpanel" aria-labelledby="responsable-tab">
									<div class="card-body">

										<!-- linea RESPONSABLE 1 -->
										<form id="formResponsableInformacion" data-requerido-guardar="si" data-alias="responsable" data-title="Informaci&oacute;n del responsable" autocomplete="off">
											<div class="form-row">
												<fieldset class="col">
													<legend class="h6 text-muted bg-light p-1"><i class="fas fa-id-card pr-1"></i>Informaci&oacute;n del responsable</legend>
													<div class="row">
														<div class="form-group col-md-4 col-lg-2">
															<label for="cResponsablePaciente" class="required">Responsable</label>
															<div class="input-group input-group-sm">
																<select class="form-control form-control-sm font-weight-bold" name="cResponsablePaciente" id="cResponsablePaciente" autocomplete="off">
																	<option value=""></option>
																	<option value="PACIENTE">PACIENTE</option>
																	<option value="OTRO">OTRO</option>
																</select>
																<div class="input-group-append">
																	<button id="responsableCpy" class="btn btn-outline-secondary dropdown-toggle " type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" disabled="disabled"><i class="fas fa-copy"></i></button>
																	<div class="dropdown-menu">
																		<li><h6 class="dropdown-header">Copiar</h6></li>
																		<div class="dropdown-divider"></div>
																		<button class="dropdown-item btnCpy" type="button" data-target="residencia"><i class="fas fa-house-user pr-1" style="width: 24px; text-align: center;"></i>Lugar de residencia</button>
																		<button class="dropdown-item btnCpy" type="button" data-target="contacto"><i class="fas fa-mobile-alt pr-1" style="width: 24px; text-align: center;"></i>Datos de contacto</button>
																		<button class="dropdown-item btnCpy" type="button" data-target="laboral"><i class="fas fa-building pr-1" style="width: 24px; text-align: center;"></i>Informaci&oacute;n laboral</button>
																		<button class="dropdown-item btnCpy" type="button" data-target="referencia"><i class="fas fa-people-arrows pr-1" style="width: 24px; text-align: center;"></i>Referencia familiar</button>
																		<div class="dropdown-divider"></div>
																		<button class="dropdown-item btnCpy" type="button" data-target="residencia contacto laboral referencia"><i class="fas fa-check-double pr-1" style="width: 24px; text-align: center;"></i><b>Todos los anteriores</b></button>
																		<div class="dropdown-divider"></div>
																		<p class="p-4 text-muted"><small class="font-weight-lighter">Haga clic en la opci&oacute;n para copiar de la informaci&oacute;n del paciente al responsable.</small></p>
																	</div>
																</div>
															</div>
															<small id="cResponsablePacienteAyuda" class="form-text text-muted">Es el mismo paciente</small>
														</div>
														<div class="form-group col-md-8 col-lg-3">
															<label for="cResponsableParentesco" class="required">Parentesco</label>
															<select class="form-control form-control-sm font-weight-bold parentesco" name="cResponsableParentesco" id="cResponsableParentesco" autocomplete="off" disabled="disabled"></select>
														</div>
														<div class="form-group col-md-6 col-lg-4">
															<label for="nResponsableId" class="required"><b>Identificaci&oacute;n</b></label>
															<div class="input-group input-group-sm">
																<div class="input-group-prepend">
																	<button class="btn btn-outline-secondary" type="button" id="btnLeerCedulaResponsable" name="btnLeerCedulaResponsable" aria-label="Leer código de barras" data-toggle="modal" data-target="#modalLeerCedula" data-callback="lecturaCedulaResponsable" disabled="disabled"><i class="fas fa-barcode"></i></button>
																</div>
																<select class="custom-select font-weight-bold tipoDocumento" id="cResponsableId" name="cResponsableId" placeholder="Tipo de documento" autocomplete="off" disabled="disabled"></select>
																<input type="number" class="form-control form-control-sm font-weight-bold confirmar" data-label="Identificaci&oacute (Responsable)" id="nResponsableId" name="nResponsableId" aria-describedby="nResponsableIdAyuda" readonly="readonly" autocomplete="off">
															</div>
															<small id="nResponsableIdAyuda" class="form-text text-muted">Documento num&eacute;rico</small>
														</div>
														<div class="form-group col-md-6 col-lg-3">
															<label for="cResponsableLugarExpedicion" class="required">Expedici&oacute;n</label>
															<input type="text" class="form-control form-control-sm font-weight-bold" id="cResponsableLugarExpedicion" name="cResponsableLugarExpedicion" autocomplete="off" maxlength="30" readonly="readonly">
															<small id="cResponsableLugarExpedicionAyuda" class="form-text text-muted">Lugar</small>
														</div>
													</div>
													<div class="row">
														<div class="form-group col-md-6 col-lg-3">
															<label for="cResponsableNombre1" class="required">Primer nombre</label>
															<input type="text" class="form-control form-control-sm font-weight-bold confirmar" data-label="Primer Nombre (Responsable)" name="cResponsableNombre1" id="cResponsableNombre1" autocomplete="off" maxlength="40" readonly="readonly">
														</div>
														<div class="form-group col-md-6 col-lg-3">
															<label for="cResponsableNombre2">Segundo nombre</label>
															<input type="text" class="form-control form-control-sm font-weight-bold confirmar" data-label="Segundo Nombre (Responsable)" name="cResponsableNombre2" id="cResponsableNombre2" autocomplete="off" maxlength="40" readonly="readonly">
														</div>
														<div class="form-group col-md-6 col-lg-3">
															<label for="cResponsableApellido1" class="required">Primer Apellido</label>
															<input type="text" class="form-control form-control-sm font-weight-bold confirmar" data-label="Primer Apellido (Responsable)" name="cResponsableApellido1" id="cResponsableApellido1" autocomplete="off" maxlength="40" readonly="readonly">
														</div>
														<div class="form-group col-md-6 col-lg-3">
															<label for="cResponsableApellido2">Segundo nombre</label>
															<input type="text" class="form-control form-control-sm font-weight-bold confirmar" data-label="Segundo Apellido (Responsable)" name="cResponsableApellido2" id="cResponsableApellido2" autocomplete="off" maxlength="40" readonly="readonly">
														</div>
													</div>
												</fieldset>
											</div>
										</form>

										<!-- linea RESPONSABLE 2 -->
										<form id="formResponsableRecide" data-requerido-guardar="si" data-alias="residencia-responsable" data-title="Lugar de residencia del responsable" autocomplete="off">
											<div class="form-row">
												<fieldset class="col">
													<legend class="h6 text-muted bg-light p-1"><i class="fas fa-house-user pr-1"></i>Lugar de residencia</legend>
													<div class="row">
														<div class="form-group col-lg-6 col-xl-4">
															<label for="nResponsableRecidePais" class="required">Pa&iacute;s / Departamento / Ciudad</label>
															<div class="input-group input-group-sm ubicacionGeografica" id="ubicacionResidenciaResponsable">
																<select class="custom-select lugarPais" name="nResponsableRecidePais" id="nResponsableRecidePais" autocomplete="off"></select>
																<select class="custom-select lugarDepartamento" name="nResponsableRecideDepartamento"id="nResponsableRecideDepartamento" autocomplete="off" disabled="disabled"></select>
																<select class="custom-select lugarCiudad" name="nResponsableRecideCiudad"id="nResponsableRecideCiudad" autocomplete="off" disabled="disabled"></select>
															</div>
														</div>
														<div class="form-group col-lg-6 col-xl-4">
															<label for="cResponsableRecideBarrio" class="required">Barrio</label>
															<input type="text" class="form-control form-control-sm font-weight-bold" name="cResponsableRecideBarrio" id="cResponsableRecideBarrio" autocomplete="off" maxlength="30">
														</div>
														<div class="form-group col-lg-6 col-xl-4">
															<label for="cResponsableRecideDireccion" class="required">Direcci&oacute;n</label>
															<input type="text" class="form-control form-control-sm font-weight-bold" name="cResponsableRecideDireccion" id="cResponsableRecideDireccion" autocomplete="off" maxlength="30">
															<small id="cResponsableRecideDireccionDireccionAyuda" class="form-text text-muted">Utilice n&uacute;meros y letras.</small>
														</div>
													</div>
												</fieldset>
											</div>
										</form>

										<!-- linea RESPONSABLE 3 -->
										<form id="formResponsableContacto" data-requerido-guardar="si" data-alias="contacto-responsable" data-title="Datos de contacto del responsable" autocomplete="off">
											<div class="form-row">
												<fieldset class="col">
													<legend class="h6 text-muted bg-light p-1"><i class="fas fa-mobile-alt pr-1"></i>Datos de contacto</legend>
													<div class="row">
														<div class="form-group col-md-6 col-lg-4 col-xl-3">
															<label for="cResponsableTelefono1" class="required">Tel&eacute;fono</label>
															<input type="number" class="form-control form-control-sm font-weight-bold" name="cResponsableTelefono1" id="cResponsableTelefono1" data-toggle="tooltip" data-placement="bottom" title="Escriba entre 7 y 15 caracteres num&eacute;ricos" autocomplete="off">
															<small id="cResponsableTelefono1Ayuda" class="form-text text-muted">Principal</small>
														</div>
														<div class="form-group col-md-6 col-lg-4 col-xl-2">
															<label for="cResponsableTelefono2">Tel&eacute;fono</label>
															<input type="number" class="form-control form-control-sm font-weight-bold" name="cResponsableTelefono2" id="cResponsableTelefono2" data-toggle="tooltip" data-placement="bottom" title="Escriba 7 caracteres num&eacute;ricos" autocomplete="off">
															<small id="cResponsableTelefono2Ayuda" class="form-text text-muted">Alternativo</small>
														</div>
														<div class="form-group col-md-6 col-lg-4 col-xl-2">
															<label for="cResponsableTelefono3" class="required">Celular</label>
															<input type="number" class="form-control form-control-sm font-weight-bold" name="cResponsableTelefono3" id="cResponsableTelefono3" data-toggle="tooltip" data-placement="bottom" title="Escriba entre 10 y 15 caracteres num&eacute;ricos" autocomplete="off">
															<small id="cResponsableTelefono3Ayuda" class="form-text text-muted">Principal</small>
														</div>
														<div class="form-group col-md-6 col-lg-4 col-xl-2">
															<label for="cResponsableTelefono4">Celular</label>
															<input type="number" class="form-control form-control-sm font-weight-bold" name="cResponsableTelefono4" id="cResponsableTelefono4" data-toggle="tooltip" data-placement="bottom" title="Escriba 10 caracteres num&eacute;ricos" autocomplete="off">
															<small id="cResponsableTelefono4Ayuda" class="form-text text-muted">Alternativo</small>
														</div>
														<div class="form-group col-md-6 col-lg-4 col-xl-3">
															<label for="cResponsableEmail" class="required">e-mail</label>
															<div class="input-group input-group-sm">
																<div class="input-group-prepend">
																	<select class="form-control form-control-sm font-weight-bold" name="cResponsableTieneEmail" id="cResponsableTieneEmail" autocomplete="off">
																		<option value=""></option>
																		<option value="SI">SI</option>
																		<option value="NO">NO</option>
																	</select>
																</div>
																<input type="email" class="form-control form-control-sm font-weight-bold confirmar" name="cResponsableEmail" id="cResponsableEmail" data-toggle="tooltip" data-placement="bottom" title="Aseg&uacute;rese de escribirlo correctamente" autocomplete="off" readonly="readonly">
																<small id="cResponsableEmailAyuda" class="form-text text-muted">Los dominios m&aacute;s populares se escriben gmail.com, hotmail.com, icloud.com, outlook.com, yahoo.com, yahoo.es</small>
															</div>
														</div>
													</div>
												</fieldset>
											</div>
										</form>

										<!-- linea RESPONSABLE 4 -->
										<form id="formResponsableLaboral" data-requerido-guardar="si" data-alias="laboral-responsable" data-title="Informaci&oacute;n laboral del responsable" autocomplete="off">
											<div class="form-row">
												<fieldset class="col">
													<legend class="h6 text-muted bg-light p-1"><i class="fas fa-building pr-1"></i>Informaci&oacute;n laboral</legend>
													<div class="row">
														<div class="form-group col-md-6 col-lg-4">
															<label for="cResponsableLaboralTrabajo" class="required">Trabajo</label>
															<select class="form-control form-control-sm font-weight-bold trabajo" name="cResponsableLaboralTrabajo" id="cResponsableLaboralTrabajo" autocomplete="off"></select>
														</div>
														<div class="form-group col-md-6 col-lg-4">
															<label for="cResponsableLaboralEmpresa">Lugar (Empresa)</label>
															<input type="text" class="form-control form-control-sm font-weight-bold responsableTrabajo" name="cResponsableLaboralEmpresa" id="cResponsableLaboralEmpresa" readonly="readonly" autocomplete="off" maxlength="60">
														</div>
														<div class="form-group col-md-6 col-lg-4">
															<label for="cResponsableLaboralCargo">Cargo/Actividad</label>
															<input type="text" class="form-control form-control-sm font-weight-bold responsableTrabajo" name="cResponsableLaboralCargo" id="cResponsableLaboralCargo" readonly="readonly" autocomplete="off" maxlength="30">
														</div>
													</div>
													<div class="row">
														<div class="form-group col-md-6 col-lg-4">
															<label for="cResponsableLaboralAntiguedad">Antiguedad</label>
															<input type="text" class="form-control form-control-sm font-weight-bold responsableTrabajo" name="cResponsableLaboralAntiguedad" id="cResponsableLaboralAntiguedad" readonly="readonly" autocomplete="off" maxlength="30">
														</div>
														<div class="form-group col-md-6 col-lg-4">
															<label for="cResponsableLaboralDireccion">Direcci&oacute;n</label>
															<input type="text" class="form-control form-control-sm font-weight-bold responsableTrabajo" name="cResponsableLaboralDireccion" id="cResponsableLaboralDireccion" readonly="readonly" autocomplete="off" maxlength="30">
															<small id="cResponsableEmpresaDireccionAyuda" class="form-text text-muted">Utilice n&uacute;meros y letras.</small>
														</div>
														<div class="form-group col-md-6 col-lg-4">
															<label for="cResponsableLaboralTelefono">Tel&eacute;fono</label>
															<input type="number" class="form-control form-control-sm font-weight-bold responsableTrabajo" name="cResponsableLaboralTelefono" id="cResponsableLaboralTelefono" readonly="readonly">
														</div>
													</div>
												</fieldset>
											</div>
										</form>

										<!-- linea RESPONSABLE 5 -->
										<form id="formResponsableReferencia" data-requerido-guardar="si" data-alias="familiar-responsable" data-title="Referencia familiar del responsable" autocomplete="off">
											<div class="form-row">
												<fieldset class="col">
													<legend class="h6 text-muted bg-light p-1"><i class="fas fa-people-arrows pr-1"></i>Referencia familiar</legend>
													<div class="row">
														<div class="form-group col-md-6 col-lg-4">
															<label for="cResponsableReferenciaNombre">Nombres y Apellidos</label>
															<input type="text" class="form-control form-control-sm font-weight-bold" name="cResponsableReferenciaNombre" id="cResponsableReferenciaNombre" autocomplete="off" maxlength="60">
														</div>
														<div class="form-group col-md-6 col-lg-4">
															<label for="cResponsableReferenciaDireccion">Direcci&oacute;n</label>
															<input type="text" class="form-control form-control-sm font-weight-bold" name="cResponsableReferenciaDireccion" id="cResponsableReferenciaDireccion" autocomplete="off" maxlength="60">
															<small id="cResponsableReferenciaDireccionAyuda" class="form-text text-muted">Utilice n&uacute;meros y letras.</small>
														</div>
														<div class="form-group col-md-6 col-lg-4">
															<label for="cResponsableReferenciaTelefono">Tel&eacute;fono</label>
															<input type="number" class="form-control form-control-sm font-weight-bold" name="cResponsableReferenciaTelefono" id="cResponsableReferenciaTelefono" autocomplete="off">
														</div>
													</div>
												</fieldset>
											</div>
										</form>
									</div>
								</div>

								<div class="tab-pane fade" id="servicioTab" role="tabpanel" aria-labelledby="servicio-tab">
									<div class="card-body">

										<!-- linea SERVICIO 1 -->
										<form id="formPacienteRemision" data-requerido-guardar="si" data-alias="remision" data-title="Informaci&oacute;n de remisi&oacute;n" autocomplete="off">
											<div class="form-row">
												<fieldset class="col">
													<legend class="h6 text-muted bg-light p-1">Informaci&oacute;n de remisi&oacute;n</legend>
													<div class="row">
														<div class="form-group col-md-6 col-lg-2">
															<label for="cRemitido">¿Es remitido?</label>
															<select class="form-control form-control-sm font-weight-bold" name="cRemitido" id="cRemitido" autocomplete="off">
																<option></option>
																<option value="SI">SI</option>
																<option value="NO">NO</option>
															</select>
														</div>
														<div class="form-group col-md-6 col-lg-5">
															<label for="cRemiteEntidad">Prestador remitente</label>
															<select class="form-control form-control-sm font-weight-bold" name="cRemiteEntidad" id="cRemiteEntidad" autocomplete="off" disabled="disabled">
																<option value=""></option>
																<?php
																foreach((new NUCLEO\InstitutosPrestadoresSalud())->getIPSs() as $laIPSs){
																	printf('<option value="%s">%s</option>', $laIPSs['CODIGO'], $laIPSs['NOMBRE']);
																}
																?>
															</select>
														</div>
														<div class="form-group col-md-12 col-lg-5">
															<label for="nResponsableRecidePais" class="required">Pa&iacute;s / Departamento / Ciudad</label>
															<div class="input-group input-group-sm ubicacionGeografica">
																<select class="custom-select lugarPais" name="cRemitePais" id="cRemitePais" autocomplete="off" disabled="disabled"></select>
																<select class="custom-select lugarDepartamento" name="cRemiteDepartamento"id="cRemiteDepartamento" autocomplete="off" disabled="disabled"></select>
																<select class="custom-select lugarCiudad" name="cRemiteCiudad"id="cRemiteCiudad" autocomplete="off" disabled="disabled"></select>
															</div>
														</div>
													</div>
												</fieldset>
											</div>
										</form>

										<!-- Linea SERVICIO 2 -->
										<form id="formPacientePlan" data-requerido-guardar="si" data-alias="plan-general" data-title="Planes (Informaci&oacute;n general)" autocomplete="off">
											<div class="form-row">
												<fieldset class="col">
													<legend class="h6 text-muted bg-light p-1">Planes (Informaci&oacute;n general)</legend>
													<div class="row">
														<div class="form-group col-12 col-md-4 col-lg-3">
															<label for="cPacienteNoEPS">Paciente indica NO E.P.S.</label>
															<select class="form-control form-control-sm font-weight-bold" name="cPacienteNoEPS" id="cPacienteNoEPS" autocomplete="off">
																<option value="SI">SI</option>
																<option value="NO" selected="selected">NO</option>
															</select>
														</div>
														<div class="form-group col-12 col-md-4 col-lg-3">
															<label for="cPacienteCarnet">N&uacute;mero de carnet</label>
															<input type="text" class="form-control form-control-sm font-weight-bold" name="cPacienteCarnet" id="cPacienteCarnet" autocomplete="off" maxlength="20">
														</div>
													</div>
												</fieldset>
											</div>
										</form>

										<div class="form-row">
											<fieldset class="col">
												<legend class="h6 text-muted bg-light p-1">Planes (Entidades)</legend>
												<div class="row" id ="entidades" class="row">
													<div class="col-12">
														<div id="toolbarEentidades">
															<button id="btnAgregarEntidad" type="button" class="btn btn-secondary" accesskey="A">Agregar</button>
															<button id="btnQuitarEntidad" type="button" class="btn btn-secondary" accesskey="Q">Quitar</button>
														</div>
														<table
														  id="tableEntidades"
														  data-toolbar="#toolbarEentidades"
														  data-click-to-select="true"
														  data-show-columns="true"
														  data-show-columns-toggle-all="false"
														  data-minimum-count-columns="7"
														  data-pagination="false"
														  data-unique-id="PLAPLA"
														  data-id-field="PLAPLA"
														  data-query-params="queryParams"
														  data-row-style="entidadesRowStyle"
														  data-url="vista-ingresos/ajax/registroIngresos.ajax?q=<?php print($lcTipoIngreso); ?>" >
														</table>
													</div>
												</div>
											</fieldset>
										</div>

										<!-- linea SERVICIO 3 -->
										<form id="formServicioPlanUsar" data-requerido-guardar="si" data-alias="servicio" data-title="Plan a usar" autocomplete="off">
											<div class="form-row mt-2">
												<fieldset class="col">
													<legend class="h6 text-muted bg-light p-1">Informaci&oacute;n del plan a usar</legend>
													<div class="row">
														<div class="form-group col-md-12">
															<label for="cPlanUsar"><b>Plan</b></label>
															<div class="input-group input-group-sm">
																<input id="cPlanUsar" name="cPlanUsar" class="form-control font-weight-bolder" type="text" value="" readonly="readonly">
																<input id="cPlanUsarDescripcion" name="cPlanUsarDescripcion" class="form-control font-weight-bolder" type="text" value="" readonly="readonly">
																<input id="cPlanUsarEntidadTipo" name="cPlanUsarEntidadTipo" class="form-control" type="text" value="" readonly="readonly">									
																<input id="nPlanUsarAfiliadoTipo" name="nPlanUsarAfiliadoTipo" class="form-control" type="text" value="" readonly="readonly">
																<input id="nPlanUsarContrato" name="nPlanUsarContrato" class="form-control" type="text" value="" readonly="readonly">
																<input id="nPlanUsarEntidad" name="nPlanUsarEntidad" class="form-control" type="text" value="" readonly="readonly">
																<input id="nPlanUsarEstrato" name="nPlanUsarEstrato" class="form-control" type="text" value="" readonly="readonly">
																<input id="nPlanUsarRegional" name="nPlanUsarRegional" class="form-control" type="text" value="" readonly="readonly">
																<input id="nPlanUsarTipo" name="nPlanUsarTipo" class="form-control" type="text" value="" readonly="readonly">
																<input id="nMapla" name="nMapla" class="form-control" type="text" value="" readonly="readonly">
															</div>
															<small id="cPlanUsarAyuda" class="form-text text-muted">Para cambiar de plan a usar utilice el bot&oacute;n &quot;USAR&quot; en la tabla de la secci&oacute;n &quot;Planes (Entidades)&quot;.</small>
														</div>
													</div>
												</fieldset>
											</div>
										</form>										

										<!-- linea SERVICIO 4 -->
										<form id="formServicioInformacion" data-requerido-guardar="si" data-alias="servicio" data-title="Informaci&oacute;n adicional del servicio" autocomplete="off">
											<div class="form-row mt-2">
												<fieldset class="col">
													<legend class="h6 text-muted bg-light p-1">Informaci&oacute;n adicional del servicio</legend>
													<div class="row">
														<div class="form-group col-md-8">
															<label for="cTriage">Clasifiaci&oacute;n Triage</label>
															<select class="form-control form-control-sm font-weight-bold triageListaClasificaciones" name="cTriage" id="cTriage" autocomplete="off" disabled="disabled"></select>
														</div>
														<div class="form-group col-md-4">
															<label for="cTriage">Consecutivo Triage</label>
															<input type="text" class="form-control form-control-sm font-weight-bold ignore" name="nTriageId" id="nTriageId" autocomplete="off" readonly="readonly">
														</div>														
														<div class="form-group col-md-12">
															<label for="cMedicoTratante"><b>M&eacute;dico tratante</b></label>
															<div class="input-group input-group-sm">
																<div class="input-group-prepend"><span class="input-group-text" id="basic-addon1"><i class="fas fa-search"></i></span></div>
																<input type="text" class="form-control form-control-sm font-weight-bold ignore" name="cMedicoTratante" id="cMedicoTratante" autocomplete="off" disabled="disabeld">
																<input type="text" class="form-control form-control-sm font-weight-bold" name="cMedicoTratanteNombre" id="cMedicoTratanteNombre" readonly="readonly">
																<input type="text" class="form-control form-control-sm font-weight-bold col-4" name="cMedicoTratanteId" id="cMedicoTratanteId" readonly="readonly">
															</div>
															<small id="cMedicoTratanteIdAyuda" class="form-text text-muted">Informaci&oacute;n del m&eacute;dico tratante. (Puede utilizar % en la casilla de b&uacute;squeda para indicar cualquier letra)</small>
														</div>
													</div>
													<div class="row">
														<div class="form-group col-md-12">
															<label for="cEnfermedadActual">Enfermedad actual</label>
															<textarea class="form-control" name="cEnfermedadActual" id="cEnfermedadActual" rows="3" disabled="disabled"></textarea>
														</div>
													</div>
												</fieldset>
											</div>
										</form>
										
									</div>
								</div>
								

								<div class="tab-pane fade" id="datosTab" role="tabpanel" aria-labelledby="datos-tab">
									<div class="card-body">

										<!-- linea DATOS 1 -->
										<form id="formDatosIngreso" data-requerido-guardar="si" data-alias="datos-ingreso" data-title="Datos del Ingreso" autocomplete="off">
											<div class="form-row">
												<fieldset class="col-6">
													<legend class="h6 text-muted bg-light p-1">Fechas</legend>
													<div class="row">
														<div class="form-group col-md-6">
															<label for="cFechaIngresoGuardada">Ingreso</label>
															<input type="text" class="form-control form-control-sm font-weight-bold" name="cFechaIngresoGuardada" id="cFechaIngresoGuardada" placeholder="" value="" readonly="readonly">
														</div>
														<div class="form-group col-md-6">
															<label for="cFechaEgreso">Egreso</label>
															<input type="number" class="form-control form-control-sm font-weight-bold" name="cFechaEgreso" id="cFechaEgreso" placeholder="" value="" readonly="readonly">
														</div>
													</div>
												</fieldset>
												<fieldset class="col-6">
													<legend class="h6 text-muted bg-light p-1">Estados</legend>
													<div class="row">
														<div class="form-group col-md-6">
															<label for="cEstadoFacturacion">Facturaci&oacute;n</label>
															<input type="text" class="form-control form-control-sm font-weight-bold" name="cEstadoFacturacion" id="cEstadoFacturacion" placeholder="" value="" readonly="readonly">
														</div>
														<div class="form-group col-md-6">
															<label for="cEstadoIngreso">Ingreso</label>
															<input type="number" class="form-control form-control-sm font-weight-bold" name="cEstadoIngreso" id="cEstadoIngreso" placeholder="" value="0" readonly="readonly">
															<input id="cEstadoIngresoAnterior" name="cEstadoIngresoAnterior" type="hidden" value="0">
														</div>
													</div>
												</fieldset>
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- linea F -->
				<div class="form-row">
				</div>

			</div>

			<div class="card-footer modal-footer text-muted">
				<button id="btnGuardarIngreso" class="btn btn-primary">Guardar</button>
				<button id="btnDocumentoGarantia" class="btn btn-primary" disabled="disabled">Documento garant&iacute;a</button>
				<button id="btnImprimir" class="btn btn-primary" disabled="disabled">Imprimir</button>
				<button id="btnManilla" class="btn btn-primary" disabled="disabled">Manilla</button>
				<button id="btnLinkVolver" class="btn btn-primary">Cerrar</button>
			</div>
		</div>

		<!-- Modal confirma dato-->
		<div class="modal fade" id="modalConfirmarEntrada" tabindex="-1"  data-backdrop="static" aria-labelledby="modalConfirmarEntradaLabel" aria-hidden="true"  data-keyboard="false">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-body">
						<div class="form-group">
							<label id="modalConfirmarEntradaLabel" for="vModalConfirmarEntrada"></label>
							<input type="text" class="form-control font-weight-bolder" id="vModalConfirmarEntrada" autofocus>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-warning" id="btnConfirmarEntradaAceptar">Aceptar</button>
						<button type="button" class="btn btn-secondary" id="btnConfirmarEntradaCancelar">Cancelar</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Modal -->
		<div class="modal fade" id="modalServicioEditarEntidad" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
				<div class="modal-content border-dark">
					<div class="modal-header bg-light">
						<div class="modal-title">
							<h4>Plan <span id="nSegumientoConsecutivoBage"></span></h4>
						</div>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<div class="modal-body">
						<form id="formServicioEditarEntidad" autocomplete="off">
							<div class="form-row">
								<div class="form-group col-12">
									<label for="cServicioEditarPlan"><b>Buscar el Plan por nombre</b></label>
									<div class="input-group input-group-sm">
										<div class="input-group-prepend"><span class="input-group-text" id="basic-addon1"><i class="fas fa-search"></i></span></div>
										<input type="text" class="form-control form-control-sm font-weight-bold ignore" name="cServicioEditarPlan" id="cServicioEditarPlan" autocomplete="off">
										<small id="cServicioEditarPlanAyuda" class="form-text text-muted">Escriba parte del nombre del plan y seleccione el que corresponda de la lista resultante. (Puede utilizar % en la casilla de b&uacute;squeda para indicar cualquier letra)</small>
									</div>
								</div>
								<div class="form-group col-12">
									<label for="cServicioEditarPlanNombre" class="required"><b>Plan seleccionado (C&oacute;digo, Nombre)</b></label>
									<div class="input-group input-group-sm">
										<div class="input-group-prepend"><span class="input-group-text" id="basic-addon1"><i class="fas fa-briefcase-medical"></i></span></div>
										<input type="text" class="form-control form-control-sm font-weight-bold" name="cServicioEditarPlanNombre" id="cServicioEditarPlanNombre" readonly="readonly">
										<input type="text" class="form-control form-control-sm font-weight-bold col-3" name="cServicioEditarPlanId" id="cServicioEditarPlanId" readonly="readonly">
									</div>
									<div id="cServicioEditarPlanIdAyuda"></div>
								</div>
								<div class="form-group col-md-6 col-lg-6">
									<label for="cServicioEditarPlanTipo" class="required">Tipo de usuario</label>
									<select class="form-control form-control-sm font-weight-bold" name="cServicioEditarPlanTipo" id="cServicioEditarPlanTipo" autocomplete="off">
										<option value=""></option>
										<?php
										foreach($laPlanesTipo as $laPlanTipo){
											printf('<option value="%s">%s</option>', $laPlanTipo['CODIGO'], $laPlanTipo['NOMBRE']);
										}
										?>
									</select>
								</div>
								<div class="form-group col-md-6 col-lg-6">
									<label for="cServicioEditarPlanAfiliadoTipo" class="required">Tipo de afiliado</label>
									<select class="form-control form-control-sm font-weight-bold" name="cServicioEditarPlanAfiliadoTipo" id="cServicioEditarPlanAfiliadoTipo" autocomplete="off">
										<option value=""></option>
										<?php
										foreach($laPlanesTipoAfiliado as $laPlaneTipoAfiliado){
											printf('<option value="%s">%s</option>', $laPlaneTipoAfiliado['CODIGO'], $laPlaneTipoAfiliado['NOMBRE']);
										}
										?>
									</select>
								</div>
								<div class="form-group col-md-6 col-lg-6">
									<label for="cServicioEditarPlanAfiliadoEstrato" class="required">Estrato</label>
									<select class="form-control form-control-sm font-weight-bold" name="cServicioEditarPlanAfiliadoEstrato" id="cServicioEditarPlanAfiliadoEstrato" autocomplete="off">
										<option value=""></option>
										<?php
										foreach($laEstratos as $laEstrato){
											printf('<option value="%s">%s</option>', $laEstrato['CODIGO'], $laEstrato['NOMBRE']);
										}
										?>
									</select>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button id="btnServicioEditarEntidadGuardar" type="button" class="btn btn-secondary">Guardar</button>
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Modal Cedula-->
		<div class="modal fade" id="modalLeerCedula" tabindex="-1"  data-backdrop="static" aria-labelledby="modalLeerCedulaLabel" aria-hidden="true" data-keyboard="false">
			<div class="modal-dialog modal-dialog-centered modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<div class="media">
							<i class="fas fa-barcode fa-3x pr-3"></i>
							<div class="media-body">
								<h6 class="modal-title">Lectura de identificaci&oacute;n personal</h6>
								<small>Realice la lectura del c&oacute;digo de barras del documento de identidad para obtener la informaci&oacute;n de identificaci&oacute;n.</small>
							</div>
						</div>
					</div>
					<div class="modal-body">
						<div class="row d-none pdf417-rw">
							<div class="col-12">
								<div class="form-group">
									<label for="cBufferReader">Lectura de código de barras</label>
									<input type="text" class="form-control form-control-sm text-muted pdf417-buffer" autocomplete="off" autofocus>
									<small id="cModalCedulaBufferAyuda" class="form-text text-muted">Ub&iacute;quese en esta casilla para realizar la lectura del c&oacute;digo de barras del documento de identidad.</small>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12 col-lg-4">
								<div class="form-group">
									<label for="nModalCedulaID">Numero</label>
									<input type="number" class="form-control form-control-sm font-weight-bolder pdf417-id" readonly="readonly">
								</div>
							</div>
							<div class="col-md-12 col-lg-8">
								<div class="row">
									<div class="col-12 col-md-4">
										<div class="form-group">
											<label for="cModalCedulaFN">Fecha nacimiento</label>
											<input type="text" class="form-control form-control-sm font-weight-bolder pdf417-nacio" readonly="readonly">
										</div>
									</div>
									<div class="col-6 col-md-4">
										<div class="form-group">
											<label for="cModalCedulaGS">G.S./RH</label>
											<input type="text" class="form-control form-control-sm font-weight-bolder pdf417-gsrh" readonly="readonly">
										</div>
									</div>
									<div class="col-6 col-md-4">
										<div class="form-group">
											<label for="nModalCedulaSX">Sexo</label>
											<input type="text" class="form-control form-control-sm font-weight-bolder pdf417-genero" readonly="readonly">
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12 col-lg-6">
								<div class="form-group">
									<label>Apellidos</label>
									<div class="input-group input-group-sm">
										<input type="text" class="form-control form-control-sm font-weight-bolder pdf417-apellido1" readonly="readonly">
										<input type="text" class="form-control form-control-sm font-weight-bolder pdf417-apellido2" readonly="readonly">
									</div>
								</div>
							</div>
							<div class="col-md-12 col-lg-6">
								<div class="form-group">
									<label>Nombres</label>
									<div class="input-group input-group-sm">
										<input type="text" class="form-control form-control-sm font-weight-bolder pdf417-nombre1" readonly="readonly">
										<input type="text" class="form-control form-control-sm font-weight-bolder pdf417-nombre2" readonly="readonly">
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer border-top">
						<button type="button" class="btn btn-warning pdf417-bt" readonly="readonly">Aceptar</button>
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
					</div>
				</div>
			</div>
		</div>

		<template id="templateConfirmCard">
			<div class="card border-0">
				<div class="card-header text-center border-0">
					Lea atentamente la informaci&oacute;n que se despleg&oacute; en pantalla, verifique los datos.
				</div>
				<div class="card-body">
					<div class="row">%DATA%</div>
				</div>
				<div class="card-footer text-center border-0">
					&iquest;Verifico la informaci&oacute;n?. El e-mail es muy importante tanto para comunicaciones como para el ingreso al portal pacientes.
				</div>
			</div>
		</template>

		<template id="templateConfirmCol">
			<div class="d-block m-2"><small class="d-block">%TITLE%</small><b>%VALUE%</b></div>
		</template>


		<link rel="stylesheet" type="text/css" href="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />
		<script type="text/javascript" src="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>

		<!-- Bootstrap Table -->
		<link rel="stylesheet" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />
		<script type="text/javascript" src="publico-complementos/jquery-tableexport/tableExport.min.js"></script>
		<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
		<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table-locale-all.min.js"></script>
		<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/extensions/export/bootstrap-table-export.min.js"></script>
		<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
		<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>

		<!-- Bootstrap 4 Autocomplete -->
		<script type="text/javascript" src="publico-complementos/bootstrap-autocomplete/2.3.7-dist/bootstrap-autocomplete.min.js"></script>

		<!-- comunes -->
		<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
		<script type="text/javascript" src="vista-comun/js/comun.js"></script>

		<!-- script del modulo -->
		<script type="text/javascript" src="vista-ingresos/js/registroIngresos.js?q=<?php print($lcTipoIngreso); ?>"></script>

	</div>
