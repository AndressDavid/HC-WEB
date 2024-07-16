<?php
	require_once (__DIR__ .'/../../publico/constantes.php');
	require_once (__DIR__ .'/../../controlador/class.AplicacionFunciones.php');
	require_once (__DIR__ .'/../../controlador/class.Cita.php');

	$lcGet = '';
	foreach($_GET as $lcGetKey => $lcGetValue){
		if(!empty($lcGetValue) && $lcGetKey<>'p'){
			$lcGet .= (!empty($lcGet)?'&':'').$lcGetKey."=".$lcGetValue;
		}
	}

	$laWebResoucesConfig	= require __DIR__ . '/../../privada/webResoucesConfig.php';

	$lnIngreso=0;
	$lcId = (isset($_POST['DOCUMENTO_TIPO'])?$_POST['DOCUMENTO_TIPO']:'');
	$lnId = (isset($_POST['DOCUMENTO'])?$_POST['DOCUMENTO']:0);
	$lnCita = (isset($_POST['CITA'])?$_POST['CITA']:0);
	$lnConsulta = (isset($_POST['CONSULTA'])?$_POST['CONSULTA']:0);
	$lnEvolucion = (isset($_POST['EVOLUCION'])?$_POST['EVOLUCION']:0);

	$loAplicacionFunciones = new NUCLEO\AplicacionFunciones();
	$loCita = new NUCLEO\Cita('JTM',$lcId, $lnId, $lnCita, $lnConsulta, $lnEvolucion);
	if(empty($loCita->getIdTipo())==false && empty($loCita->getIdNumero())==false){
		$lnIngreso = $loCita->getIngreso(true)->nIngreso;
?>
	<div class="container-fluid">
		<div class="card mt-3">
			<div class="card-header">
				<h5>Gestión de Cita por Telemedicina</h5>
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="modulo-citas-telemedicina&p=listaCitasTelemedicina&<?php print($lcGet); ?>"><i class="fas fa-video"></i> Volver</a></li>
						<li class="breadcrumb-item active" aria-current="page"></li>
					</ol>
				</nav>
				<ul class="nav nav-tabs card-header-tabs" id="pgfRegistro" role="tablist">
					<li class="nav-item" role="presentation"><a class="nav-link active" id="tabPagCita" data-toggle="tab" href="#pagCita" role="tab" aria-controls="pagCita" aria-selected="true">Cita</a></li>
				</ul>
			</div>
			<div id ="registroCitasTelemedicina" class="card-body">
				<div>
					<p>Paciente Ingreso No. <?php print(empty($loCita->getIngreso())?'<span class="badge badge-danger">Sin ingreso</span>':sprintf('<span class="badge badge-success">%s</span>',$loCita->getIngreso())); ?></span></p>
					<div class="media">
						<div class="align-self-center mr-3 mb-3">
							<i class="fas fa-video fa-5x text-secondary"></i>
						</div>
						<div class="media-body">
							<h4><span><?php print($loCita->getIngreso(true)->oPaciente->getNombreCompleto());?></span></h4>
							<span><?php print($loCita->getIngreso(true)->oPaciente->aTipoId['ABRV']); ?></span> - <span><?php print($loCita->getIngreso(true)->oPaciente->nId); ?></span><br/>
							<span><?php print($loCita->getIngreso(true)->oPaciente->getEdad()); ?></span><br/>
						</div>
					</div>
				</div>

				<div class="tab-content" id="pgfRegistroContent">
					<div class="tab-pane fade show active" id="pagCita" role="tabpanel" aria-labelledby="tabPagCita">
						<div class="row">
							<div class="col-12 col-md-6 pb-4">
								<div class="list-group text-muted">
									<div class="list-group-item">
										<div class="d-flex w-100 justify-content-between">
											<h6 class="mb-1"><small class="text-uppercase"><?php printf('%s - %s',$loCita->getViaIngresoNombre(),$loCita->getEspecialidadNombre()); ?></small><br/><?php printf('%s',$loCita->getProcedimientoNombre()); ?></h6>
										</div>
										<small class="font-weight-bolder text-nowrap"><?php print($loCita->getCitaFechaHora()); ?> <?php print($loCita->getPortalPacientesLlaveCita()); ?></small>
									</div>

									<div class="list-group-item">
										<div class="d-flex w-100 justify-content-between">
											<h6 class="mb-1">Informaci&oacute;n de contacto</h6>
											<small class="text-muted"></small>
										</div>
										<p class="mb-1 pl-5"><small>
											<b>Direcci&oacute;n</b>
												<?php
													$lcDireccion = '';
													for($lnDireccion=1;$lnDireccion<=3;$lnDireccion++){
														if(isset($_POST['DIRECCION'.$lnDireccion])){
															if($loAplicacionFunciones->isSearchStrInStr($lcDireccion,trim($_POST['DIRECCION'.$lnDireccion]))==false){
																$lcDireccion .= (empty($lcDireccion)?'':', ').trim($_POST['DIRECCION'.$lnDireccion]);
															}
														}
													}
													print($lcDireccion)
												?><br/>
											<b>Tel&eacute;fono</b>
												<?php
													$lcTelefono = '';
													for($lnTelefono=1;$lnTelefono<=4;$lnTelefono++){
														if(isset($_POST['TELEFONO'.$lnTelefono])){
															if($loAplicacionFunciones->isSearchStrInStr($lcTelefono,trim($_POST['TELEFONO'.$lnTelefono]))==false){
																$lcTelefono .= (empty($lcTelefono)?'':', ').trim($_POST['TELEFONO'.$lnTelefono]);
															}
														}
													}
													printf('<b>%s</b>',$lcTelefono)
												?><br/>
											<b>e-mail</b>
												<span class="text-lowercase"><?php
													$lcEmail = '';
													for($lnEmail=1;$lnEmail<=2;$lnEmail++){
														if(isset($_POST['EMAIL'.$lnEmail])){
															if($loAplicacionFunciones->isSearchStrInStr($lcEmail,trim($_POST['EMAIL'.$lnEmail]))==false){
																$lcEmail .= (empty($lcEmail)?'':', ').trim($_POST['EMAIL'.$lnEmail]);
															}
														}
													}
													print($lcEmail)
												?></span><br/>
										</small></p>
										<small class="text-warning font-weight-bolder">Incluye datos autoregistrados por el paciente si existen.</small>
									</div>


								</div>
							</div>
							<div class="col-12 col-md-6 pb-4">
								<div class="list-group text-muted h-100">
									<div class="list-group-item h-100">
										<div class="d-flex w-100 justify-content-between">
											<h6 class="mb-1">Archivos cargados por el paciente</h6>
											<small class="text-muted"></small>
										</div>
										<table
										  id="tableListaArchivosCitasTelemedicina"
										  data-show-refresh="true"
										  data-url="vista-citas-telemedicina/ajax/registroCitasTelemedicina.ajax?p=<?php print($lcId); ?>&q=<?php print($lnId); ?>&r=<?php print($lnCita); ?>&s=<?php print($lnConsulta); ?>&t=<?php print($lnEvolucion); ?>" >
										</table>
									</div>
								</div>
							</div>
							<div class="col-12 pb-4">
								<?php
								if($loCita->getIngreso()>0){
									if($loCita->getIngreso(true)->esActivo()==true){ ?>
								<div class="list-group text-muted">
									<div class="list-group-item">											
										<div class="d-flex w-100 justify-content-between">
											<h6 class="mb-1">Consulta por telemedicina</h6>
										</div>											
										<p class="mb-1">
											<small>Para que el paciente pueda unirse a la consulta debe ingresar al portal <a href="<?php print($laWebResoucesConfig['pp']['url']); ?>" target="blank"><?php print($laWebResoucesConfig['pp']['url']); ?></a>, buscar en la sección "Mis Citas" esta Cita y abrirla, luego debera hacer clic en el bot&oacute;n <i>&quot;Entrar a la Cita&quot;</i>. Tenga en cuenta que el bot&oacute;n se habilita una vez tenga el ingreso administrativo.<?php if($loCita->getIngreso()>0){ ?><h5 class="text-success">El ID de conferencia: <b><?php print($loCita->getReunionId()); ?></b>, Contrase&ntilde;a: <b><?php print($loCita->getReunionKey()); ?></b></h5>
											<button id="btnTeleconsulta" type="button" class="btn btn-success font-weight-bolder w-100 d-block">Entrar a la Cita</button>
											<p class="text-muted"><small>Recuerde, la entrada a la conferencia <b><?php print($loCita->getReunionId()); ?></b> del paciente sera autorizada por el anfitri&oacute;n m&eacute;dico, o de ser necesario por el gestor del ingreso administrativo, el paciente deber&aacute; unirse y permanecer en la sala de espera para ser admitido.</small></p><?php } ?></small>
										</p>
									</div>
								</div>										
								<?php
									}else{
										print('<div class="alert alert-warning" role="alert"><b>Consulta por Telemedicina</b> El ingreso ya no esta activo, por tal motivo no se muestra informaci&oacute;n de de conexi&oacute;n para la cita.</div>');
									}
								} else {
									print('<div class="alert alert-danger" role="alert"><b>Consulta por Telemedicina</b> No hay ingreso administrativo, por tal motivo no se muestra informaci&oacute;n de de conexi&oacute;n para la cita.</div>');
								}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
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
	<script type="text/javascript" src="vista-citas-telemedicina/js/registroCitasTelemedicina.js?p=<?php print($lcId); ?>&q=<?php print($lnId); ?>&r=<?php print($lnCita); ?>&s=<?php print($lnConsulta); ?>&t=<?php print($lnEvolucion); ?>&ingreso=<?php print($lnIngreso); ?>"></script>
<?php
	}else{
?>
<div class="alert alert-warning" role="alert">
  <h4 class="alert-heading">Cita no encontrada!</h4>
  <p>No se tiene registrada la cita a la que esta intentando ingresar para la identificaci&oacute;n <?php printf('%s-%s',$_SESSION[SLP_NAME]->oUsuario->aTipoId['TIPO'],$_SESSION[SLP_NAME]->oUsuario->nId); ?>. Por favor ingrese nuevamente a la secci&oacute;n &quot;Mis citas&quot; y haga clic en la asignaci&oacute;n.</p>
  <hr>
  <p class="mb-0"><?php printf('<span class="badge badge-light mr-1">%s-%s</span><span class="badge badge-light">%s-%s-%s</span>',$_SESSION[SLP_NAME]->oUsuario->aTipoId['TIPO'],$_SESSION[SLP_NAME]->oUsuario->nId, str_pad($lnCita, 10, "0", STR_PAD_LEFT), str_pad($lnConsulta, 10, "0", STR_PAD_LEFT), str_pad($tlEvolucion, 10, "0", STR_PAD_LEFT)); ?></p>
</div>
<?php
	}
?>