 <?php
	require_once (__DIR__ .'/../../controlador/class.Usuario.php') ;
	require_once (__DIR__ .'/../../controlador/class.Usuarios.php') ;
	require_once (__DIR__ .'/../../controlador/class.TiposDocumento.php');
	require_once (__DIR__ .'/../../controlador/class.AplicacionFunciones.php');

	use NUCLEO\Usuarios;
	use NUCLEO\TiposDocumento;
	use NUCLEO\AplicacionFunciones;
	$loUsuarios = new Usuarios();
	$loAplicacionFunciones = new AplicacionFunciones();


	// Cargando el usuario para cuando aplique
	$loUsuario = new NUCLEO\Usuario();
	$lcPerfiles = '';
	$llUusario = false;
	$lcIdUsuario = (isset($_POST['id'])?$_POST['id']:(isset($_GET['id'])?$_GET['id']:''));
	settype($lcIdUsuario,'String');
	if(!empty($lcIdUsuario)){
		$lcIdUsuario = $loUsuarios->desencriptar($lcIdUsuario);
		
		$loUsuario->cargar($lcIdUsuario);
		$llUusario = true;

		// Perfiles del usuario
		$laPerfiles = $loUsuario->getPerfiles('*','*');
		foreach($laPerfiles['PERFIL'] as $lnPefil=> $lcPerfil){
			$lcPerfiles .= (empty($lcPerfiles)?'':', ').sprintf("'%s'",$lcPerfil);
		}
	}
?>
	<!-- especificos ara usuarios -->
	<link rel="stylesheet" href="vista-usuarios/css/style.css" />

	<!-- Bootstrap Table -->
	<link rel="stylesheet" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />
	<script src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
	<script src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table-locale-all.min.js"></script>

	<div class="container-fluid">
		<div class="card mt-3">
			<div class="card-header">
				<h5>Usuarios</h5><p>Est&eacute; opci&oacute;n le permite realizar la parametrizaci&oacute;n de los usuarios del sistema</p>
				<?php
				?>
			</div>

			<div class="card-body">
				<div class="card-title">
					<div class="row">
						<div class="col-md-4 col-xl-3">
							<div class="form-group">
								<label for="registroMedico" class="required">Registro m&eacute;dico</label>
								<input type="text" class="form-control form-control-sm" id="registroMedico" value="<?php print(isset($loUsuario)?$loUsuario->getRegistro():''); ?>" <?php print(isset($loUsuario)?(!empty($loUsuario->getRegistro())?'disabled':''):''); ?>>
							</div>
						</div>
						<div class="col-md-4 col-xl-3">
							<div class="form-group">
								<?php
									$laSelectTiposDocumento['TIPO']=$loUsuario->aTipoId['TIPO'];
									$laSelectTiposDocumento['NUMERO']=$loUsuario->getId();
									include (__DIR__ . '/../comun/selectTipoDocumentos.php');
								?>
							</div>
						</div>
						<div class="col-md-4 col-xl-3">
							<div class="form-group">
								<label for="email" class="required">e-mail</label>
								<input type="email" class="form-control form-control-sm" id="email" value="<?php print($loUsuario->getEmail()); ?>">
							</div>
						</div>
						<div id="vigencia" class="col-md-8 col-xl-3">
							<div class="row">
								<div class="col-6">
									<label for="txtFechaDesde" class="required"><b>Fecha Desde</b></label>
									<div class="input-group date">
										<div class="input-group-prepend">
											<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
										</div>
									  <input type="text" class="form-control form-control-sm" name="txtFechaDesde" id="txtFechaDesde" required="required" value="<?php print(!empty($loUsuario->getVigenciaIni())?date("Y-m-d",strtotime($loUsuario->getVigenciaIni())):''); ?>">

									</div>
								</div>
								<div class="col-6">
									<label for="txtFechaHasta" class="required"><b>Fecha Hasta</b></label>
									<div class="input-group date">
										<div class="input-group-prepend">
											<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
										</div>
										<input type="text" class="form-control form-control-sm" name="txtFechaHasta" id="txtFechaHasta" required="required" value="<?php print(!empty($loUsuario->getVigenciaFin())?date("Y-m-d",strtotime($loUsuario->getVigenciaFin())):''); ?>">
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-4 col-xl-3">
							<div class="form-group">
								<label for="usuario" class="required">Usuario</label>
								<input type="text" class="form-control form-control-sm" id="usuario" value="<?php print($loUsuario->getUsuario()); ?>"  data-q="<?php print(base64_encode(json_encode(['usuario'=>$loUsuario->getUsuario()]))); ?>">
							</div>
						</div>
						<div class="col-md-4 col-xl-3">
							<div class="form-group">
								<label for="apellidos" class="required">Apellidos</label>
								<input type="text" class="form-control form-control-sm" id="apellidos" value="<?php print($loUsuario->getApellidos()); ?>">
							</div>
						</div>
						<div class="col-md-4 col-xl-3">
							<div class="form-group">
								<label for="nombres" class="required">Nombres</label>
								<input type="text" class="form-control form-control-sm" id="nombres" value="<?php print($loUsuario->getNombres()); ?>" required>
							</div>
						</div>
						<div class="col-md-4 col-xl-3">
							<div class="form-group">
								<?php
									$lcSelectUsuarioEstado=$loUsuario->getEstado();
									include (__DIR__ . '/../comun/selectUsuarioEstado.php');
								?>
							</div>
						</div>
						<div class="col-md-4 col-xl-3">
							<div class="form-group">
								<?php
									$lcSelectUsuarioDepartamento=$loUsuario->getDepartamento();
									include (__DIR__ . '/../comun/selectUsuarioDepartamento.php');
								?>
							</div>
						</div>
						<div class="col-md-4 col-xl-3">
							<div class="form-group">
								<?php
									$lcSelectUsuarioArea=$loUsuario->getArea();
									include (__DIR__ . '/../comun/selectUsuarioArea.php');
								?>
							</div>
						</div>
						<div class="col-md-4 col-xl-3">
							<div class="form-group">
								<?php
									$lcSelectUsuarioCargo=$loUsuario->getCargo();
									include (__DIR__ . '/../comun/selectUsuarioCargo.php');
								?>
							</div>
						</div>
					</div>
					<div class="row edicion">
						<div class="col">
							<ul class="nav nav-tabs" id="propiedades" role="tablist">
								<li class="nav-item" role="presentation"><a class="text-dark nav-link active" id="general-tab" data-toggle="tab" href="#generalTab" role="tab" aria-controls="general" aria-selected="true">General</a></li>
								<li class="nav-item" role="presentation"><a class="text-dark nav-link" id="perfil-tab" data-toggle="tab" href="#perfilTab" role="tab" aria-controls="perfil" aria-selected="false">Perfil</a></li>
								<li class="nav-item" role="presentation"><a class="text-dark nav-link" id="notificaciones-tab" data-toggle="tab" href="#notificacionesTab" role="tab" aria-controls="opciones" aria-selected="false">Notificaciones</a></li>
								<li class="nav-item" role="presentation"><a class="text-dark nav-link" id="bitacora-tab" data-toggle="tab" href="#bitacoraTab" role="tab" aria-controls="bitacora" aria-selected="false">Bitácora</a></li>
							</ul>
							<div class="card border-top-0">
								<div class="tab-content" id="TabPropiedades">
									<div class="tab-pane fade show active" id="generalTab" role="tabpanel" aria-labelledby="general-tab">
										<div class="card-body">
											<div class="row">
												<div class="col">
													<div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
														<div class="nav btn-group w-100" id="pillsGeneral" role="group">
															<a class="nav-item btn btn-outline-secondary active" id="pillsTipoUsuario" data-toggle="pill" href="#tipoTab" role="tab" aria-controls="v-pills-home" aria-selected="true"><i class="fas fa-user-cog"></i> Tipo</a>
															<a class="nav-item btn btn-outline-secondary" id="pillsFoto" data-toggle="pill" href="#fotoTab" role="tab" aria-controls="v-pills-profile" aria-selected="false"><i class="far fa-images"></i> Foto</a>
															<a class="nav-item btn btn-outline-secondary" id="pillsFirma" data-toggle="pill" href="#firmaTab" role="tab" aria-controls="v-pills-messages" aria-selected="false"><i class="fas fa-code"></i> HTML</a>
														</div>
													</div><hr/>
												</div>
											</div>
											<div class="row">
												<div class="col">
													<div class="tab-content" id="TabPropiedadesGenerales">
														<div class="tab-pane fade show active" id="tipoTab" role="tabpanel" aria-labelledby="tipoUsuario-tab">
															<div class="row">
																<div class="col-md-12 col-lg-6">
																	<div class="row">
																		<div class="col">
																			<div class="row">
																				<div class="col">
																					<div id="toolbarUsuarioTipos" class="btn-toolbar justify-content-between" role="toolbar" >
																						<div class="btn-group btn-group-sm" role="group">
																							<select class="form-control form-control-sm" id="tipoUsuario" name="tipoUsuario" data-tipo="<?php print($loUsuario->getTipoUsuario()); ?>"></select>
																						</div>
																					</div>
																				</div>
																			</div>
																			<div class="row">
																				<div class="col h-100">
																					<table
																					  id="tableUsuarioTipos"
																					  data-locale="es-ES"
																					  data-toolbar="#toolbarUsuarioTipos"
																					  data-toolbar-align="left"
																					  data-click-to-select="true"
																					  data-cache="false"
																					  data-height="320"
																					  data-pagination="false"
																					  data-id-field="ID"
																					  data-select-item-name="ID"
																					  data-search="true"
																					  data-show-search-clear-button="true"
																					  data-url="vista-usuarios/consultas_json?p=usuarioTipos"
																					  class="table table-sm table-striped table-hover">
																					</table>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
																<div class="col-md-12 col-lg-6">
																	<div class="row">
																		<div class="col">
																			<div class="row">
																				<div class="col">
																					<div id="toolbarUsuarioEspecialidades" class="btn-toolbar justify-content-between" role="toolbar" >
																						<div class="btn-group btn-group-sm" role="group">
																							<button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#windowEspecialidadAgregar">Agregar</button>
																							<button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#confirmEspecialidadQuitar"><i class="fas fa-trash-alt"></i> Quitar</button>
																						</div>
																					</div>
																				</div>
																			</div>
																			<div class="row">
																				<div class="col">
																					<table
																					  id="tableUsuarioEspecialidades"
																					  data-locale="es-ES"
																					  data-toolbar="#toolbarUsuarioEspecialidades"
																					  data-cache="false"
																					  data-height="320"
																					  data-pagination="false"
																					  data-search="true"
																					  data-show-search-clear-button="true"
																					  class="table table-sm table-striped table-hover">
																						<thead>
																							<tr>
																								<th data-checkbox="true"></th>
																								<th data-field="ESPECIALIDADNOMBRE">Especialidades</th>
																								<th data-field="ESPECIALIDADID" data-visible="false">Especialidad Id</th>
																								<th data-field="NIVEL">Tipo</th>
																								<th data-field="TIPONOMBRE">Tipo Usuario</th>
																								<th data-field="TIPOID" data-visible="false">Tipo Id</th>
																							</tr>
																						</thead>
																					</table>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="tab-pane fade" id="fotoTab" role="tabpanel" aria-labelledby="foto-tab">
															<div class="row">
																<div class="col-md-12 col-lg-6">
																	<div class="row">
																		<div class="col">
																			<label>Foto</label>
																			<div class="input-group mb-3">
																				<div class="input-group-prepend">
																					<button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#windowFoto"><i class="fas fa-camera"></i> Captura</button>
																					<button type="button" class="btn btn-outline-secondary"><i class="fas fa-trash-alt"></i> Quitar</button>
																				</div>
																				<div class="custom-file">
																					<input type="file" class="custom-file-input" id="customFile">
																					<label class="custom-file-label" for="customFile">Choose file</label>
																				</div>
																			</div>
																		</div>
																	</div>																	
																	<div class="row">
																		<div class="col">
																			<div class="fotoPreview text-center bg-light p-2 mb-2">
																				<?php
																					$laUsuarioRed = $goDb->configServer('SERVIDOR');
																					$lcResult = $loUsuario->getFoto();
																					if(!empty($lcResult)){
																						print($loAplicacionFunciones->obtenerRemoto($lcResult,1,$lnStatus,$lcMIME,null,$laUsuarioRed['user'],$laUsuarioRed['pass']));
																					}
																				?>
																				<input id="foto" name="foto" type="hidden">
																			</div>
																		</div>
																	</div>
																</div>
																<div class="col-md-12 col-lg-6">
																	<div class="row">
																		<div class="col">
																			<label>Firma</label>
																			<div class="input-group mb-3">
																				<div class="input-group-prepend">
																					<button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#windowFirma"><i class="fas fa-camera"></i> Captura</button>
																					<button type="button" class="btn btn-outline-secondary"><i class="fas fa-trash-alt"></i> Quitar</button>
																				</div>
																				<div class="custom-file">
																					<input type="file" class="custom-file-input" id="customFile">
																					<label class="custom-file-label" for="customFile">Choose file</label>
																				</div>
																			</div>
																		</div>
																	</div>
																	<div class="row">
																		<div class="col">
																			<div class="firmaPadPreview text-center bg-light p-2 mb-2">
																				<?php
																					$laUsuarioRed = $goDb->configServer('SERVIDOR');
																					$lcResult = $loUsuario->getFirma();
																					if(!empty($lcResult)){
																						print($loAplicacionFunciones->obtenerRemoto($lcResult,1,$lnStatus,$lcMIME,null,$laUsuarioRed['user'],$laUsuarioRed['pass']));
																					}
																				?>
																				<input id="firma" name="firma" type="hidden">
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="tab-pane fade" id="firmaTab" role="tabpanel" aria-labelledby="firma-tab">
															<div class="row">
																<div class="col-md-12 col-lg-6">
																	<div class="row pt-2 pb-2">
																		<div class="col">
																			<label>C&oacute;digo</label>
																			<div class="btn-toolbar" role="toolbar">
																				<div class="btn-group btn-group-sm mr-2" role="group">
																					<button type="button" class="firmaHtmlCopiar btn btn-outline-secondary"><i class="fas fa-copy"></i> Copiar</button>
																					<button type="button" class="firmaHtmlExportar btn btn-outline-secondary"><i class="fas fa-file-export"></i> Exportar</button>
																				</div>
																			</div>
																		</div>
																	</div>																
																	<div class="row">
																		<div class="col">
																			<div class="firmaHtmlModelo overlay pr-4">
																				<div class="form-group"><table border='0' cellpadding='0' cellspacing='0' ><tr><td style='vertical-align: text-top; width: 56px;'><img src='http://www.shaio.org/firma/imglogoshaiox.png' /></td><td><div style='padding-left:10px;font-family:Arial; font-size: 16px; color:rgb(97, 97, 97); border-bottom: 2px solid rgb(204, 0, 0); ' ><div style='padding-bottom:10px;'><span style='color:rgb(57, 57, 57); font-weight: bold; '>{!Nombre}</span><br/>{!Cargo}<br/><span style='font-size: 12px; '>Dg 115A # 70C-75 Bogot&aacute;, Colombia<br/>PBX +571 593 8210<br/>http://www.shaio.org/</span></div></div></td></tr><tr><td></td><td style='height:55px;'><img src='http://www.shaio.org/firma/imghospitalverdex.png' /></td></tr><tr><td></td><td><div style='font-family:Arial; font-size: 12px; color:rgb(97, 97, 97); text-align:justify' ><br/><b>AVISO PUBLICO EN CORREO ELECTR&Oacute;NICO SOBRE CUMPLIMIENTO DE LA LEY DE HABEAS DATA:</b><br/>"Este mensaje y sus archivos adjuntos van dirigidos exclusivamente a su destinatario pudiendo contener informaci&#243;n confidencial sometida a secreto profesional. No est&#225; permitida su reproducci&#243;n o distribuci&#243;n sin la autorizaci&#243;n expresa de la Fundaci&#243;n Abood Shaio. Si usted no es el destinatario final por favor elimine este mensaje. La Instituci&#243;n informa que da cumplimiento a lo establecido en la Ley de Protecci&#243;n de Datos Personales y su Decreto Reglamentario y que conforme a ello ofrece mecanismos para que el titular de los datos personales otorgue consentimiento para el tratamiento de sus datos, as&#237; como ejercer sus derechos de consulta y reclamo sobre sus datos, visitando nuestra p&#225;gina web www.shaio.org o mediante correo electr&#243;nico a info@shaio.org."</div></td></tr></table></div>
																			</div>
																		</div>
																	</div>																
																</div>
																<div class="col-md-12 col-lg-6">
																	<div class="row pt-2 pb-2">
																		<div class="col">
																			<label>Preliminar</label>
																			<div class="btn-toolbar" role="toolbar">
																				<div class="btn-group btn-group-sm mr-2" role="group">
																					<button type="button" class="firmaHtmlCopiar btn btn-outline-secondary"><i class="fas fa-copy"></i> Copiar</button>
																					<button type="button" class="firmaHtmlExportar btn btn-outline-secondary"><i class="fas fa-file-export"></i> Exportar</button>
																				</div>
																			</div>
																		</div>
																	</div>
																	<div class="row">
																		<div class="col">
																			<div class="firmaHtmlPreview">
																				<div class="form-group h-100">
																					<textarea class="form-control h-100 text-monospace p-3" disabled></textarea>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									<div class="tab-pane fade" id="perfilTab" role="tabpanel" aria-labelledby="perfil-tab">
										<div class="card-body">
											<div class="row">
												<div class="col-md-6">
													<div class="row">
														<div class="col">
															<table
															  id="tablePerfiles"
															  data-click-to-select="true"
															  data-cache="false"
															  data-height="320"
															  data-search="true"
															  data-show-search-clear-button="true"
															  data-pagination="false"
															  data-id-field="ID"
															  data-url="vista-usuarios/consultas_json?p=perfiles"
															  class="table table-sm table-striped table-hover">
															</table>
														</div>
													</div>
													<div class="row">
														<div class="col m-2">
															<div id="seleccionPerfiles"></div>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="row">
														<div class="col">
															<table
															  id="tableUsuarioOpciones"
															  data-click-to-select="true"
															  data-cache="false"
															  data-height="320"
															  data-pagination="false"
															  data-id-field="ID"
															  data-select-item-name="ID"
															  data-search="true"
															  data-show-search-clear-button="true"
															  data-url="vista-usuarios/consultas_json?p=usuarioPropiedades"
															  class="table table-sm table-striped table-hover">
															</table>
														</div>
													</div>
													<div class="row">
														<div class="col m-2">
															<div id="seleccionPerfiles"></div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									<div class="tab-pane fade" id="notificacionesTab" role="tabpanel" aria-labelledby="notificaciones-tab">
										<div class="card-body">
											<div class="table-responsive">
												<table id="tableNotificaciones"class="table table-sm table-striped table-hover">
													<thead>
														<tr>
															<th>Especialidad</th>
															<th>e-mail</th>
															<th>Estado</th>
														</tr>
													</thead>
													<tbody>
													<?php
														foreach($loUsuario->getNotificaciones() as $lnNotificacion => $laNotificacion){
															$loEspecialidad = new NUCLEO\Especialidad($laNotificacion['ESPECIALIDAD']);
															if($laNotificacion['ESTADO']<>'I'){
																printf('<tr><td>%s - %s</td><td>%s</td><td>%s</td></tr>',$laNotificacion['ESPECIALIDAD'],$loEspecialidad->cNombre,$laNotificacion['EMAIL'],$laNotificacion['ESTADO']);
															}
														}
													?>
													</tbody>
												</table>
											</div>
										</div>
									</div>

									<div class="tab-pane fade" id="bitacoraTab" role="tabpanel" aria-labelledby="bitacora-tab">
										<div class="card-body">
											<div class="row pb-2">
												<div class="col">
													<div id="bitacoraHistory" data-loaded="" class="bitacoraPreview overlay p-2 mb-2"></div>
												</div>
											</div>
											<div class="row">
												<div class="col-11">											
													<div class="form-group h-100">
														<label for="bitacora">Nuevo registro</label>
														<textarea class="form-control h-100" id="bitacora"></textarea>
													</div>
												</div>
												<div class="col-1">											
													<div class="form-group h-100">
														<label for="bitacoraPuntaje">Puntaje</label>
														<select multiple class="form-control h-100" id="bitacoraPuntaje">
															<?php for($lnStar=1; $lnStar<=5; $lnStar++){ printf('<option value="%s">%s</option>',$lnStar,$lnStar); } ?>
														</select>
													</div>
												</div>
											</div>											
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<hr/>
				<div class="row">
					<div class="col">
						<label>Auditoria</label>
						<p>
						<?php
							$laAuditoria = $loUsuario->getAuditoria();
							foreach($laAuditoria as $lcAuditoriaId => $laAuditoriaRegistro){
								foreach($laAuditoriaRegistro as $lcKey => $laValue){
									printf('<span class="badge badge-secondary" data-toggle="tooltip" title="%s en %s a las %s">Reistro %s %s por %s</span> ',(empty($laValue['PROGRAMA'])?'':'Con ').$laValue['PROGRAMA'],$laValue['FECHA'],$laValue['HORA'],$lcAuditoriaId, strtolower($lcKey), $laValue['USUARIO']);
								}
							}
						?>					
						</p>
					</div>
				</div>
				<hr/>
				<div class="form-group row">
					<div class="col-12">
						<button id="buttonUsuarioGuardar" class="btn btn-outline-secondary btn-lg btn-block">Guardar</button>
					</div>
				</div>
			</div>

			<div class="modal fade" id="windowEspecialidadAgregar" tabindex="-1" role="dialog" aria-labelledby="windowEspecialidadAgregar" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header"><label>Agregar especialidad</label></div>
						<div class="modal-body">
							<div class="row">
								<div class="col-12">
									<label>Tipo de Usuario</label>
									<div class="input-group">
										<select class="form-control form-control-sm" id="tipoUsuarioAgregar" name="tipoUsuarioAgregar"></select>
									</div>
								</div>
								<div class="col-12">
									<label>Nivel</label>
									<div class="input-group">
										<select class="form-control form-control-sm" id="tipoUsuarioNivelAgregar" name="tipoUsuarioNivelAgregar">
											<option value="PRINCIPAL">PRINCIPAL</option>
											<option value="SECUNDARIA">SECUNDARIA</option>
											<option value="ADICIONAL">ADICIONAL</option>
										</select>
									</div>
								</div>
								<div class="col-12">
									<?php
										$lcSelectEspecialidadActivos = true;
										include (__DIR__ . '/../comun/selectUsuarioEspecialidades.php');
									?>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cancelar</button>
							<button id="buttonModalEspecialidadAgregar" type="button" class="btn btn-sm btn-success btn-ok text-white"><i class="fas fa-plus"></i> Agregar</button>
						</div>
					</div>
				</div>
			</div>

			<div class="modal fade" id="windowLista" tabindex="-1" role="dialog" aria-labelledby="windowLista" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered modal-lg">
					<div class="modal-content">
						<div class="modal-body">
							<div class="row">
								<div class="col">
									<div class="form-group">
										<label></label>
										<select class="form-control"></select>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col">
									<table
									  id="tableLista"
									  data-locale="es-ES"
									  data-cache="false"
									  data-height="320"
									  data-pagination="false"
									  data-search="true"
									  data-show-search-clear-button="true"
									  class="table table-sm table-striped table-hover">
										<thead>
											<tr>
												<th data-checkbox="true"></th>
												<th data-field="ID" data-visible="false">Id</th>
												<th data-field="NOMBRE">Nombre</th>
												<th data-field="VALOR" data-visible="false">Valor</th>
											</tr>
										</thead>
									</table>
								</div>
							</div>							
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Cancelar</button>
							<button id="buttonModalListaGuardar" class="btn btn-sm btn-success btn-ok text-white">Aceptar</button>
						</div>
					</div>
				</div>
			</div>
			
			<div class="modal fade" id="windowFoto" tabindex="-1" role="dialog" aria-labelledby="windowFoto" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered modal-lg">
					<div class="modal-content">
						<div class="modal-body text-center">
							<div></div>
							<div class="form-group">
								<select id="listaDeDispositivos" name="listaDeDispositivos" class="form-control"></select>
								<p id="estado"></p>
							</div>
							<video muted="muted" id="video"></video>
							<canvas id="canvas" style="display: none; width: 148px; height:196px;"></canvas>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Cancelar</button>
							<button id="buttonModalFotoGuardar" class="btn btn-sm btn-success btn-ok text-white"><i class="fas fa-camera"></i> Tomar foto</button>
						</div>
					</div>
				</div>
			</div>

			<div class="modal fade" id="windowFirma" tabindex="-1" role="dialog" aria-labelledby="windowFirma" aria-hidden="true">
				<div class="modal-dialog modal-sm modal-dialog-centered">
					<div id="signArea" class="modal-content" style="width: 413px;">
						<div class="modal-body">
							<div class="row">
								<div class="col text-center">
									<canvas class="sign-pad" id="sign-pad" width="375" height="250"></canvas>
								</div>
							</div>
							<div id="capture"></div>
						</div>
						<div class="modal-footer">
							<div class="btn-toolbar justify-content-between w-100 sigNav" role="toolbar" aria-label="Toolbar with button groups">
								<div class="btn-group mr-2" role="group">
									<button type="button" class="btn btn-outline-secondary clearButton"><i class="fas fa-trash-alt"></i> Quitar</button>
								</div>
								<div class="btn-group" role="group">
									<button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Cancelar</button>
									<button id="buttonModalFirmaGuardar" class="btn btn-sm btn-success btn-ok text-white"><i class="fas fa-plus"></i> Guardar</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="modal fade" id="confirmEspecialidadQuitar" tabindex="-1" role="dialog" aria-labelledby="confirmEspecialidadQuitar" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-body">¿Desea quitar las especialidades seleccionadas?</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cancelar</button>
							<button id="buttonModalEspecialidadQuitar" type="button" class="btn btn-sm btn-danger btn-ok text-white"><i class="fas fa-trash-alt"></i> Quitar</button>
						</div>
					</div>
				</div>
			</div>

			<script>
				var laPerfiles=[<?php print($lcPerfiles); ?>];
				var laUsuarioTipos=<?php print(json_encode($loUsuario->getTiposUsuario()));?>;
				var laEspecialidades=<?php print(json_encode($loUsuario->getEspecialidades('array'))); ?>;
				var laUsuarioOpciones=<?php print(json_encode($loUsuario->getPropiedades('array'))); ?>;
			</script>
			
			<?php var_dump($loUsuario->getPropiedades('array')); ?>

			<script src="vista-comun/js/tiposDocumentos.js"></script>
			<script src="vista-comun/js/selectUsuarioCargo.js"></script>
			<script src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
			<script src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
			<script src="publico-complementos/signature-pad/2.5.2/jquery.signaturepad.min.js"></script>
			<script src="publico-complementos/html2canvas/0.4.1/html2canvas.min.js"></script>
			<script src="vista-usuarios/js/scripts_foto.js"></script>
			<script src="vista-usuarios/js/scripts_usuario.js"></script>
			
			<div class="console"></div>		


			<div class="card-footer text-muted">
				<p>info</p>
			</div>
		</div>
	</div>