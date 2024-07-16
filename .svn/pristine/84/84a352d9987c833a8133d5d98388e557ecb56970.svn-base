<?php
	
	require_once (__DIR__ .'/../../controlador/class.AplicacionFunciones.php');
	require_once (__DIR__ .'/../../controlador/class.Bitacoras.php');
	
	// Cargando el ingreso para cuando aplique
	$loAplicacionFunciones = new NUCLEO\AplicacionFunciones();
	$lnIngreso = intval(isset($_POST['INGRESO'])?$_POST['INGRESO']:(isset($_POST['nIngreso'])?$_POST['nIngreso']:(isset($_GET['ingreso'])?$_GET['ingreso']:0)));
	$lnConsecutivo = intval(isset($_POST['CONSECUTIVO'])?$_POST['CONSECUTIVO']:(isset($_POST['nConsecutivo'])?$_POST['nConsecutivo']:(isset($_GET['nConsecutivo'])?$_GET['nConsecutivo']:0)));
	$llIngreso=false;
	
	
	$lcTipoBitacora = (isset($_GET['q'])?$_GET['q']:'UNDEFINE');
	$lcEstado = (isset($_GET['r'])?$_GET['r']:'');
	$lcModo = strtoupper(strval(isset($_GET['s'])?$_GET['s']:'PACIENTE'));
	
	$loBitacoras = new NUCLEO\Bitacoras($lcTipoBitacora, $lnConsecutivo, $lnIngreso);
	
	$laPermisos = $loBitacoras->permisos($_SESSION[HCW_NAME]->oUsuario->getUsuario());
	
	$lcMensajeRegistro = '';
	$lcMensajeIngreso = '';
	
	if($lnIngreso>0){
		if($loBitacoras->getIngreso()->nIngreso>0){
			$llIngreso=true;
			if($loBitacoras->getIngreso()->esActivoParaRegistros()==true){
				$llIngreso=true;
			}else{
				$lcMensajeIngreso='<i class="fa fa-exclamation-triangle"></i> NO esta activo el ingreso '.$lnIngreso;
			}
			$lcMensajeRegistro = $lcMensajeIngreso;
		}else{
			$lcMensajeIngreso='<i class="fa fa-exclamation-triangle"></i> Ingreso '.$lnIngreso.' no valido';
		}
	}else{
		$lcMensajeIngreso='<i class="fa fa-exclamation-triangle"></i> Ingreso '.$lnIngreso.' no valido';
	}
?>
	<div class="container-fluid">
		<div class="card mt-3">
			<?php if(empty($loBitacoras->getTipoBitacora())){ ?>
			<div class="card-header">
				<h5>Tipo de bit&aacute;cora no encontrado</h5>
			</div>
			<div class="card-body">
				<div class="alert alert-warning" role="alert">El modulo al que hace referencia no existe. Regrese al inicio e ingrese nuevamente. Si la Falla persiste reportela al &aacute;rea de Tecnolog&iacute;a e Informaci&oacute;n</div>			
			</div>			
			<?php } else { ?>		
			<div class="card-header">
				<h5>Registro de bit&aacute;cora para <b><?php print($loBitacoras->getTipoBitacora()['TITULO']); ?></b></h5>
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="modulo-bitacoras&p=listaBitacoras&q=<?php print($lcTipoBitacora); print($lnIngreso>0?'&ingreso='.$lnIngreso:''); ?>&estado=<?php print($lcEstado); ?>&modo=<?php print($lcModo); ?>"><i class="far fa-building"></i> Volver</a></li>
						<li class="breadcrumb-item active" aria-current="page"><?php printf('Ingreso %s, Bit&aacute;cora %s',$lnIngreso,($loBitacoras->getConsecutivo()<=0?'Sin guardar':$loBitacoras->getConsecutivo())); ?></li>
					</ol>
				</nav>
				<div id="registroCabecera"></div>
				<ul class="nav nav-tabs card-header-tabs" id="pgfRegistro" role="tablist">
					<li class="nav-item" role="presentation"><a class="nav-link <?php print($loBitacoras->getConsecutivo()>0?'':'active'); ?>" id="tabPagGeneral" data-toggle="tab" href="#pagGeneral" role="tab" aria-controls="pagGeneral" aria-selected="true">General</a></li>
					<?php if($loBitacoras->getConsecutivo()>0){ ?><li class="nav-item" role="presentation"><a class="nav-link <?php print($loBitacoras->getConsecutivo()>0?'active':''); ?>" id="tabPagSeguimiento" data-toggle="tab" href="#pagSeguimiento" role="tab" aria-controls="pagSeguimiento" aria-selected="false">Seguimiento</a></li><?php } ?>
				</ul>
			</div>
			<div id ="registroDetalleBitacora" class="card-body">
				<?php if($llIngreso==true){ ?>
				<div>
					<p>Paciente Ingreso No. <span class="badge badge-success"><?php print($loBitacoras->getIngreso()->nIngreso); ?></span></p>
					<div class="media">
						<div class="align-self-center mr-3 mb-3">
							<i class="far fa-comment-alt fa-5x text-secondary"></i>
						</div>
						<div class="media-body">
							<h4><span><?php print($loBitacoras->getIngreso()->oPaciente->getNombreCompleto());?></span></h4>
							<span><?php print($loBitacoras->getIngreso()->cId); ?></span> - <span><?php print($loBitacoras->getIngreso()->nId); ?></span><br/>
							<span><?php print($loBitacoras->getIngreso()->oPaciente->getEdad()); ?></span><br/>
							<span><?php print($loBitacoras->getIngreso()->oHabitacion->cUbicacion); ?></span>
						</div>
					</div>
				</div>
				<div class="tab-content" id="pgfRegistroContent">
					<div class="tab-pane fade <?php print($loBitacoras->getConsecutivo()>0?'':'show active'); ?>" id="pagGeneral" role="tabpanel" aria-labelledby="tabPagGeneral">
						<?php if(in_array('N',$laPermisos)==true){ ?>
						<form id="cabeceraForm">
											<input id="nIngreso" name="nIngreso" type="hidden" value="<?php print($loBitacoras->getIngreso()->nIngreso); ?>">
											<input id="nConsecutivo" name="nConsecutivo" type="hidden" value="<?php print($loBitacoras->getConsecutivo()); ?>">
											<input id="cIdentificacion" name="cIdentificacion" type="hidden" value="<?php print($loBitacoras->getIngreso()->cId); ?>">
											<input id="nIdentificacion" name="nIdentificacion" type="hidden" value="<?php print($loBitacoras->getIngreso()->nId); ?>">
											
							<?php if($loBitacoras->getConsecutivo()<1){ ?><div class="alert alert-primary" role="alert">Para poder adicionar seguimientos debe primero crear el registro base de <b><?php print($loBitacoras->getTipoBitacora()['TITULO']); ?></b>. Ingrese la informaci&oacute;n solicitada y despu&eacute;s de verificar haga clic en <kbd>Guardar</kbd>.</div><?php } ?>	
							<?php if(!empty($lcMensajeRegistro)) { ?><div class="alert alert-primary" role="alert"><?php printf($lcMensajeRegistro); ?></div><?php } ?>							
							<div class="row">
								<div class="col-md-12">
									<h5>Informaci&oacute;n general</h5>
									<div class="row pb-4">
										<div class="col-md-12 pb-2">
											<label class="control-label required">Estado</label>
											<select id="cEstado" name="cEstado" class="form-control" required="required">
												<?php
													foreach($loBitacoras->getEstados() as $laEstado){
														printf('<option value="%s"%s>%s</option>',$laEstado['CODIGO'],$loBitacoras->getEstado()==$laEstado['CODIGO']?' selected':'',$laEstado['DESCRIPCION']);
													}
												?>
											</select>
										</div>
										<div class="col-md-12">
											<label>Observaci&oacute;n</label>
											<textarea id="cObservacion" name="cObservacion" class="form-control" rows="3" maxlength="510"></textarea>
											<small id="emailcObservacion" class="form-text text-muted">Las observaciones se guardan de forma independiente, consulte observaciones previas en el final.</small>
										</div>
									</div>
								</div>
							</div>
						</form>
						<div class="form-group row">
							<div class="col-12">
								<button id="btnCabeceraGuardar" class="btn btn-secondary btn-block" >Guardar</button>
							</div>
						</div>						
						<?php } ?>
					</div>
					
					<!-- Tramites -->
					<?php if($loBitacoras->getConsecutivo()>0){ ?>
					<div class="tab-pane fade <?php print($loBitacoras->getConsecutivo()>0?'show active':''); ?>" id="pagSeguimiento" role="tabpanel" aria-labelledby="tabPagSeguimiento">				
						<?php if(!empty($lcMensajeRegistro)) { ?><div class="alert alert-primary" role="alert"><?php printf($lcMensajeRegistro); ?></div><?php } ?>	
						<div id ="listaRegistroDetalleBitacora">
							<div id="toolbarlistaBitacoras">
								<div class="form-inline">
									<?php if(in_array('N',$laPermisos)==true){ ?><button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#seguimientoModal">Nuevo</button><?php } ?>
								</div>
							</div>
							<table
							  id="tableDetalleBitacora"
							  data-show-export="true"
							  data-toolbar="#toolbarlistaBitacoras"
							  data-show-refresh="true"
							  data-click-to-select="true"
							  data-show-export="false"
							  data-show-columns="true"
							  data-show-columns-toggle-all="true"
							  data-minimum-count-columns="10"
							  data-pagination="false"
							  data-id-field="CONSECUTIVO"
							  data-row-style="rowStyle"
							  data-url="vista-bitacoras/ajax/listaRegistroBitacoras.ajax?p=<?php print($loBitacoras->getConsecutivo()); ?>&q=<?php print($lcTipoBitacora); ?>&r=<?php print(isset($loIngreso)?$loIngreso->nIngreso:0); ?>" >
							</table>
							<div id="registroAlertaInfo" class="mt-2"></div>
						</div>
					</div>

					<!-- Modal Seguimiento-->
					<div class="modal fade" id="seguimientoModal" tabindex="-1" aria-labelledby="seguimientoModalLabel" aria-hidden="true">
						<div class="modal-dialog modal-xl modal-dialog-centered">
							<div class="modal-content">
								<div class="modal-header bg-light">
									<div class="modal-title" id="seguimientoModalLabel">
										<div class="media">
											<div class="align-self-center mr-3">
												<i class="far fa-comment-alt fa-4x text-secondary"></i>
											</div>
											<div class="media-body">
												<h4>Seguimiento <span id="nSegumientoConsecutivoBage"></span></h4>
												<p class="p-0 m-0 font-weight-bolder"><?php printf('Paciente Ingreso No.%s | %s - %s %s',$loBitacoras->getIngreso()->nIngreso,$loBitacoras->getIngreso()->cId, $loBitacoras->getIngreso()->nId, $loBitacoras->getIngreso()->oPaciente->getNombreCompleto());?></p>
											</div>
										</div>									
									</div>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body">
									<form id="seguimientoForm">
										<input id="nSegumientoConsecutivo" name="nSegumientoConsecutivo" type="hidden" value="0">
										<div class="row">
											<div class="col-6 col-md-4 col-lg-3 pb-2">
												<label>Ubiaci&oacute;n</label>
												<div class="input-group">
													<div class="input-group-prepend">
														<span id="cSeguimientoSeccion" class="input-group-text" id="basic-addon1"><?php print($loBitacoras->getIngreso()->oHabitacion->cSeccion); ?></span>
													</div>
													<div id="cSeguimientoHabitacion" class="form-control bg-light"><?php print($loBitacoras->getIngreso()->oHabitacion->cHabitacion); ?></div>
												</div>
											</div>	
											<div class="col-6 col-md-4 col-lg-3 pb-2">
												<label>Egreso</label>
												<div class="input-group">
													<div id="cSeguimientoEgreso" class="form-control bg-light"><?php printf('%s %s',($loBitacoras->getIngreso()->nEgresoFecha>0?$loAplicacionFunciones->formatFechaHora('fecha',$loBitacoras->getIngreso()->nEgresoFecha):'Sin egreso'),($loBitacoras->getIngreso()->nEgresoFecha>0?$loAplicacionFunciones->formatFechaHora('hora',$loBitacoras->getIngreso()->nEgresoHora):'')); ?></div>
												</div>											
											</div>												
											<div class="col-12 col-md-4 col-lg-6 pb-2">
												<label class="control-label required">Entidad</label>
												<select id="cSegumientoEntidad" name="cSegumientoEntidad" class="form-control" required="required">
													<?php 
														$laPlanes = $loBitacoras->getIngreso()->oPaciente->aPlanes;
														foreach($laPlanes as $loPlan){
															if(!empty($loPlan->oEntidad->cId)){
																printf('<option value="%s">%s</option>',$loPlan->oEntidad->cId,$loPlan->oEntidad->cNombre);
															}
														}
													?>														
												</select>
											</div>
											<div class="col-md-6 pb-2">
												<label class="control-label required">Tipo</label>
												<select id="cSegumientoTipo" name="cSegumientoTipo" class="form-control" required="required">
													<option></option>
													<?php 
														foreach($loBitacoras->getTiposDetalleBitacora() as $laTipoDetalle){
															printf('<option value="%s">%s</option>',$laTipoDetalle['CODIGO'],$laTipoDetalle['DESCRIPCION']);
														}
													?>	
												</select>
											</div>
										
											<div class="col-md-6 pb-2">
												<label>Proveedor</label>
												<div class="input-group">
													<select id="cSegumientoProveedor" name="cSegumientoProveedor" class="form-control" id="cProveedor" name="cProveedor" <?php print(in_array('N',$laPermisos)==false?'disabled="disabled"':''); ?>>
														<option></option>
														<?php 
															foreach($loBitacoras->getProveedores() as $laProveedor){
																printf('<option value="%s">%s (%s)</option>',$laProveedor['CODIGO'],$laProveedor['DESCRIPCION'],$laProveedor['CODIGO']);
															}
														?>	
													</select>
													<?php if(in_array('N',$laPermisos)==true){ ?><div class="input-group-append">
														<button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#proveedorModal"><i class="fas fa-plus"></i></button>
													</div><?php } ?>
												</div>
											</div>

											<div class="col-md-12 col-lg-4 pb-2">
												<label class="control-label required">Estado</label>
												<select id="cSegumientoEstado" name="cSegumientoEstado" class="form-control" required="required" <?php print(in_array('N',$laPermisos)==false?'disabled="disabled"':''); ?>>
													<?php
														foreach($loBitacoras->getEstadosDetalle() as $laEstado){
															printf('<option value="%s">%s</option>',$laEstado['CODIGO'],$laEstado['DESCRIPCION']);
														}
													?>	
												</select>
											</div>

											<div class="col-md-6 col-lg-4 pb-2">
												<div class="form-group">
													<label class="control-label required">Inicio</label>
													<div class="input-group date">
														<div class="input-group-prepend">
															<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
														</div>
														<input id="cSegumientoInicio" name="cSegumientoInicio" type="text" class="form-control" required="required" value="<?php print(date("Y-m-d H:i:s")); ?>">
													</div>
												</div>
											</div>
											<div class="col-md-6 col-lg-4 pb-2">
												<div class="form-group">
													<label>Confirmaci&oacute;n</label>
													<div class="input-group date">
														<div class="input-group-prepend">
															<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
														</div>
														<input id="cSegumientoConfirmacion" name="cSegumientoConfirmacion" type="text" class="form-control" value="" <?php print(in_array('N',$laPermisos)==false?'disabled="disabled"':''); ?>>
													</div>
												</div>
											</div>										
										</div>

										<div class="row">
											<div class="col-md-12 pb-2">
												<label class="control-label required">Seguimiento</label>
												<textarea id="cSegumientoObservacion" name="cSegumientoObservacion" class="form-control" rows="3" maxlength="510" required="required" <?php print(in_array('N',$laPermisos)==false?'disabled="disabled"':''); ?>></textarea>
											</div>
										</div>
									</form>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
									<?php if(in_array('N',$laPermisos)==true){ ?><button id="btnSeguimientoGuardar" type="button" class="btn btn-secondary">Guardar</button><?php } ?>
								</div>
							</div>
						</div>
					</div>
					
					<!-- Modal Proveedor Agregar -->
					<div class="modal fade" id="proveedorModal" tabindex="-1" aria-labelledby="proveedorModalLabel" aria-hidden="true">
						<div class="modal-dialog modal-lg modal-dialog-centered ">
							<div class="modal-content border-dark">
								<div class="modal-header"><h5 class="modal-title" id="proveedorModalLabel">Proveedor</h5></div>
								<div class="modal-body">
									<form id="proveedorForm">
										<div class="row">
											<div class="col-md-5 pb-2">
												<div class="form-group">
													<label class="control-label required">C&oacute;digo</label>
													<input id="cProveedorCodigo" name="cProveedorCodigo" type="number" class="form-control" required="required" value="">
												</div>
											</div>
											<div class="col-md-7 pb-2">
												<div class="form-group">
													<label class="control-label required">Nombre</label>
													<input id="cProveedorNombre" name="cProveedorNombre" type="text" class="form-control" value="">
												</div>
											</div>
										</div>
									</form>
									<div id="proveedorRegistro"></div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
									<button id="btnProveedorGuardar" type="button" class="btn btn-secondary">Guardar</button>
								</div>
							</div>
						</div>
					</div>					
					<?php } ?>
				</div>

				<link rel="stylesheet" type="text/css" href="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />
				<script type="text/javascript" src="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>				
				
				<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
				<script type="text/javascript" src="vista-comun/js/comun.js"></script>	
			
				<link rel="stylesheet" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />
				<script type="text/javascript" src="publico-complementos/jquery-tableexport/tableExport.min.js"></script>
				<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
				<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table-locale-all.min.js"></script>
				<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/extensions/export/bootstrap-table-export.min.js"></script>
	
				<link rel="stylesheet" href="publico-complementos/bootstrap-datetimepicker/4.17.47-dist/css/bootstrap-datetimepicker.min.css" />
				<script type="text/javascript" src="publico-complementos/moment-develop/2.29.1-dist/moment.min.js"></script>
				<script type="text/javascript" src="publico-complementos/moment-develop/2.29.1-dist/locale/es.min.js"></script>
				<script type="text/javascript" src="publico-complementos/bootstrap-datetimepicker/4.17.47-dist/js/bootstrap-datetimepicker.min.js"></script>
				<script type="text/javascript" src="vista-bitacoras/js/registroBitacoras.js?p=<?php print($loBitacoras->getConsecutivo()); ?>&q=<?php print($loBitacoras->getTipoBitacora()['CODIGO']); ?>&r=<?php print($loBitacoras->getIngreso()->nIngreso); ?>"></script>
			
	
				<?php }else{ ?>
				<div class="alert alert-danger" role="alert"><?php print($lcMensajeIngreso); ?></div>
				<?php } ?>
			</div>
			<div class="card-footer text-muted">
				<?php if($llIngreso==true){ ?>
				<div class="row">
					<?php $laObservaciones = $loBitacoras->getObservaciones(); ?>
					<div class="col-md-<?php print(is_array($laObservaciones)?(count($laObservaciones)>0?4:12):12); ?>">
						<h4 class="d-flex justify-content-between align-items-center mb-3">
							<span class="text-muted">Resumen</span>
						</h4>
						<ul class="list-group mb-3">
							<li class="list-group-item d-flex justify-content-between lh-condensed">
								<div>
									<h6 class="my-0">Seguimiento(s)</h6>
									<small class="text-muted">Numero de seguimientos registrados</small>
								</div>
								<span class="text-muted"><?php print($loBitacoras->getRegistros()); ?></span>
							</li>
							<li class="list-group-item d-flex justify-content-between lh-condensed">
								<div>
									<h6 class="my-0">Pendientes por confirmaci&oacute;n</h6>
									<small class="text-muted">Numero de seguimientos que no tienen fecha de confirmaci&oacute;n</small>
								</div>
								<span class="text-muted"><?php print($loBitacoras->getRegistrosSinConfirmar()); ?></span>
							</li>							
						</ul>
					</div>	
					<?php if((is_array($laObservaciones)?count($laObservaciones)>0:false)==true){ ?>
					<div class="col-md-8">
						<h4 class="d-flex justify-content-between align-items-center mb-3">
							<span class="text-muted">Observaciones</span>
						</h4>
						<ul class="list-group mb-3">
							<?php
								foreach($laObservaciones as $laObservacion){
									printf('<li class="list-group-item d-flex justify-content-between lh-condensed"><div><h6 class="my-0">%s %s %s</h6><small class="text-muted">%s</small></div><span class="badge badge-secondary">%s</span></li>',$laObservacion['USUARIO'],$loAplicacionFunciones->formatFechaHora('fecha',$laObservacion['FECHA']),$loAplicacionFunciones->formatFechaHora('hora',$laObservacion['HORA']),$laObservacion['OBSERVACION'],$laObservacion['CONSECUTIVO']);
								}
							?>						
						</ul>					
					</div>
					<?php } ?>
				</div>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
	</div>