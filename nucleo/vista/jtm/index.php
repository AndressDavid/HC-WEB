<?php
	require_once (__DIR__ .'/../../publico/constantes.php');
	require_once (__DIR__ .'/../../controlador/class.Cita.php');

	$lnIngreso = intval($_GET['p']);
	$loCita =null;
	$llGestor = (isset($_GET['gestor']));

	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			$loCita = new NUCLEO\Cita('JTM',$lnIngreso);
		}
	}

	$laWebResoucesConfig	= require __DIR__ . '/../../privada/webResoucesConfig.php';

?><!doctype html>
<html lang="en" class="h-100">
	<head>
		<!-- Cabecera -->
		<?php
			$lcInclude = file_get_contents("../../publico/head.php");
			$lcInclude = str_replace("publico-complementos","../publico-complementos",$lcInclude);
			$lcInclude = str_replace("publico-css","../publico-css",$lcInclude);
			$lcInclude = str_replace("publico-ico","../publico-ico",$lcInclude);
			$lcInclude = str_replace("hcw-manifiest.json.php","../hcw-manifiest.json.php",$lcInclude);
			print($lcInclude);
		?>
		<link rel="stylesheet" type="text/css" href="../publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />
		<script type="text/javascript" src="../publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>

		<script src="../publico-complementos/clipboard/2.0.6-dist/clipboard.min.js"></script>

		<link rel="stylesheet" href="../publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />
		<script type="text/javascript" src="../publico-complementos/jquery-tableexport/tableExport.min.js"></script>
		<script type="text/javascript" src="../publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
		<script type="text/javascript" src="../publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table-locale-all.min.js"></script>
		<script type="text/javascript" src="../publico-complementos/bootstrap-table/3.1.3-dist/extensions/export/bootstrap-table-export.min.js"></script>

		<link rel="stylesheet" type="text/css" href="../publico-complementos/bootstrap-star-rating/v4.0.6-dist/css/star-rating.min.css" media="all" />
		<script type="text/javascript" src="../publico-complementos/bootstrap-star-rating/v4.0.6-dist/js/star-rating.min.js" ></script>

		<script type="text/javascript" src="../vista-comun/js/comun.js"></script>

		<script src="https://jtm.shaio.org/external_api.js"></script>
	</head>
	<body id="main-app-panel" name="main-app-panel" class="w-100 h-100">
		<div class="card h-100">
			<div class="card-header">
				<div class="row align-items-center">
					<div class="col-6">
						<h6 class="text-dark">Telemedicina<?php print($loCita->getIngreso()>0?sprintf(' | Ingreso %s',$loCita->getIngreso()):''); ?><br/><b><?php print($loCita->getIngreso(true)->oPaciente->getNombreCompleto()); ?></b></h6>
					</div>
					<?php if((is_null($loCita)?false:$loCita->getIngreso()>0 && $loCita->getIngreso(true)->esActivo()==true)==true){ ?>
					<div class="col-6 text-right">
						<div class="btn-group btn-group-sm" role="group" aria-label="Anfitrión">
							<div class="btn-group mr-2" role="group" aria-label="Primer group">
								<button type="button" class="btn btn-copy btn-outline-dark" data-toggle="tooltip" data-placement="bottom" title="Clic para copiar el usuario anfitrión" data-clipboard-text="<?php print($_SESSION[HCW_NAME]->oUsuario->getUsuario()); ?>"><i class="fas fa-user mr-1"></i></button>
								<button type="button" class="btn btn-copy btn-outline-dark" data-toggle="tooltip" data-placement="bottom" title="Clic para copiar la clave de usuario anfitri&oacute;n" data-clipboard-text="<?php print($_SESSION[HCW_NAME]->oUsuario->getProsodyJtmPassword()); ?>"><i class="fas fa-key mr-1"></i></button>
							</div>
							<div class="btn-group" role="group" aria-label="Segundo group">
								<button type="button" class="btn btn-copy btn-outline-dark" data-toggle="tooltip" data-placement="bottom" title="Clic para copiar la clave de reuni&oacute;n" data-clipboard-text="<?php print($loCita->getReunionKey()); ?>"><i class="fas fa-video"></i></button>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
				<ul class="nav nav-tabs card-header-tabs" id="pgfRegistro" role="tablist">
					<li class="nav-item" role="presentation"><a class="nav-link active" id="tabPagCita" data-toggle="tab" href="#pagCita" role="tab" aria-controls="pagCita" aria-selected="true">Cita</a></li>
					<?php if($llGestor==false && $loCita->getIngreso()>0 && $loCita->getIngreso(true)->esActivo()==true){ ?><li class="nav-item" role="presentation"><a class="nav-link" id="tabPagArchivos" data-toggle="tab" href="#pagArchivos" role="tab" aria-controls="pagArchivos" aria-selected="false">Archivos</a></li><?php } ?>
				</ul>
			</div>
			<div class="card-body p-0 m-0 w-100 h-100">
				<?php if((is_null($loCita)?false:$loCita->getIngreso()>0)==true){ ?>
				<div class="tab-content w-100 h-100" id="pgfRegistroContent">
					<div class="tab-pane fade show active w-100 h-100" id="pagCita" role="tabpanel" aria-labelledby="tabPagCita">

						<div class="container-fluid p-0  m-0 w-100 h-100">
							<?php if((is_null($loCita)?false:$loCita->getIngreso(true)->esActivo()==true)==true){ ?>
							<div id="jitsi-meet-conf-container" class="w-100 h-100"></div>
							<?php } else { ?>
							<div class="alert alert-danger m-4" role="alert">
								<h4 class="alert-heading">Ingreso inactivo</h4>
								<p>El ingreso solicitado se encuentra inactivo</p>
							</div>
							<?php } ?>
						</div>
					</div>
					<?php if($llGestor==false){ ?>
					<div class="tab-pane fade w-100 h-100" id="pagArchivos" role="tabpanel" aria-labelledby="tabPagArchivos">
						<?php if((is_null($loCita)?false:$loCita->getIngreso())==true){ ?>
						<div class="p-3">
							<table
							  id="tableListaArchivosCitasTelemedicina"
							  data-show-refresh="true"
							  data-url="../vista-citas-telemedicina/ajax/registroCitasTelemedicina.ajax?p=<?php print($loCita->getIdTipo()); ?>&q=<?php print($loCita->getIdNumero()); ?>&r=<?php print($loCita->getCita()); ?>&s=<?php print($loCita->getConsulta()); ?>&t=<?php print($loCita->getEvolucion()); ?>" >
							</table>
						</div>
						<?php } else { ?>
						<div class="alert alert-danger m-4" role="alert">
							<h4 class="alert-heading">Cita no encontrada</h4>
							<p>No hay informaci&oacute;n para los datos actuales</p>
						</div>
						<?php } ?>
					</div>
					<?php } ?>
				</div>
				<?php } else { ?>
				<div class="alert alert-danger m-4" role="alert">
					<h4 class="alert-heading">Telemedicina</h4>
					<p>No hay informaci&oacute;n para los datos actuales</p>
				</div>
				<?php } ?>
			</div>
			<div class="card-footer">
				<?php if((is_null($loCita)?false:$loCita->getIngreso()>0 && $loCita->getIngreso(true)->esActivo()==true)==true && $llGestor==false){ ?>
					<div class="text-left w-100">
						<form class="w-100">
							<div class="form-row align-items-center">
								<div class="col-12 col-md-3">
									<select id="cEstado" name="cEstado" class="form-control form-control-sm" data-toggle="tooltip" data-placement="bottom" title="Resultado/Estado de la consulta">
										<?php
											foreach($loCita->obtenerEstados() as $laEstado){
												if(!empty($laEstado['CODIGO'])){
													$lcSelect = ((isset($_GET['cEstado'])?'a'.$_GET['cEstado']:'')=='a'.$laEstado['CODIGO']?'selected="seleted"':'');
													printf('<option value="%s" %s>%s</option>',$laEstado['CODIGO'], $lcSelect, $laEstado['DESCRIPCION']);
												}
											}
										?>
									</select>
								</div>
								<div class="col-12 col-md-4 col-lg-6">
									<input id="cObservacion" name="cObservacion" type="text" class="form-control form-control-sm" id="inputObservation" placeholder="Observaci&oacute;n" data-toggle="tooltip" data-placement="bottom" title="Observaciones" value="<?php print(trim($loCita->getObservacionMedico())); ?>" maxlength="500">
								</div>
								<div class="col-12 col-md-3 col-lg-2">
									<input id="nValoracion" value="<?php print($loCita->getValoracionMedico()>0?$loCita->getValoracionMedico():''); ?>" type="text" class="rb-rating" title="" data-container-class="border rounded text-right h-100 mh-100">
								</div>
								<div class="col-12 col-md-2 col-lg-1">
									<button type="button" id="btnTeleconsultaGuardar" class="btn btn-dark btn-sm w-100">Guardar</button>
								</div>
							</div>
						</form>
					</div>
				<?php } else { ?>
				<div class="row justify-content-center">
					<div class="col-12 text-center text-md-left"><b>2018 - <?php echo date("Y"); ?> &copy; Fundación Clínica Shaio</b></div>
				</div>
				<?php } ?>

			</div>
		</div>

		<?php if((is_null($loCita)?false:$loCita->getIngreso()>0)==true && $loCita->getIngreso(true)->esActivo()==true){ ?>
		<script>
			function nombreFormatter(value, row, index) {
				return ('<a class="btn btn-secondary mr-1 btn-sm" target="_blank" href="<?php printf('%sdownload-private',$laWebResoucesConfig['pp']['url']); ?>?accion=paciente-archivo&paciente='+row.source+'&nombre='+row.name+'" role="button" ><i class="far fa-file-archive"></i></a><b class="text-uppercase text-muted">'+row.name+'</b>');
			}

			function disableObjects(tlDisabled){
				if(tlDisabled==false){
					$('#btnTeleconsultaGuardar').removeAttr("disabled");
					$('#cEstado').removeAttr("disabled");
					$('#cObservacion').removeAttr("disabled");
				}else{
					$('#btnTeleconsultaGuardar').attr("disabled","disabled");
					$('#cEstado').attr("disabled","disabled");
					$('#cObservacion').attr("disabled","disabled");
				}
			}

			var apiObj = null;
			var lModerador = false;

			function StartMeeting(){
				<?php if(is_null($loCita)==false){ ?>
				const domain = '<?php print($laWebResoucesConfig['jtm']['domain']); ?>';
				const options = {
					roomName: '<?php print($loCita->getReunionId()); ?>',
					width: '100%',
					height: '100%',
					parentNode: document.querySelector('#jitsi-meet-conf-container'),
					userInfo: {
						displayName: '<?php print($_SESSION[HCW_NAME]->oUsuario->getNombreCompleto()); ?>'
					},
					configOverwrite:{
						disableDeepLinking: true,
						prejoinPageEnabled: true,
						closePageEnable: true,
						defaultLanguage: 'es',
					},
					interfaceConfigOverwrite: {
						APP_NAME: '<?php print($laWebResoucesConfig['jtm']['domain']); ?>',
						SHOW_JITSI_WATERMARK: false,
						SHOW_WATERMARK_FOR_GUESTS: false,
						DEFAULT_REMOTE_DISPLAY_NAME: '<?php print($_SESSION[HCW_NAME]->oUsuario->getNombreCompleto()); ?>',
						MOBILE_APP_PROMO: false,
					},
					onload: function () {
						$('#joinMsg').show();
						$('#container').show();
						$('#toolbox').show();
					}
				};
				apiObj = new JitsiMeetExternalAPI(domain, options);
				apiObj.addEventListener('participantRoleChanged', function(event) {
					if (event.role === "moderator") {
						lModerador = true;
						apiObj.executeCommand('password', '<?php print($loCita->getReunionKey()); ?>');
						apiObj.executeCommand('toggleLobby', true);
					}
				});
				apiObj.addEventListener('readyToClose', function(event) {
					if(lModerador==true){
						fnAlert('Recuerde diligenciar la informaci&oacute;n abajo solicitada, relacionada con la consulta por Telemedicina', 'Atenci&oacute;n', 'far fa-check-circle', 'blue', false);
					}
				});
				apiObj.on('passwordRequired', function (){
					api.executeCommand('password', '<?php print($loCita->getReunionKey()); ?>');
				});
				<?php } ?>
			}

			$(function () {
				StartMeeting();
				$('[data-toggle="tooltip"]').tooltip();
				new ClipboardJS('.btn-copy');
				<?php if($llGestor==false){ ?>
				$('#tableListaArchivosCitasTelemedicina').bootstrapTable('destroy').bootstrapTable({
					locale: 'es-ES',
					classes: 'table table-bordered table-hover table-sm table-responsive-sm table-striped',
					theadClasses: 'thead-light',
					columns: [
								[
									{field: 'name', title: 'Nombre', sortable: true, visible: true, class: 'text-nowrap', formatter: nombreFormatter},
									{field: 'date', title: 'Subido', sortable: true, visible: true, class: 'text-nowrap'},
									{field: 'size', title: 'Tama&ntilde;o', sortable: true, visible: true, class: 'text-nowrap'},
								]
							]
				});
				<?php } ?>

				$('#btnTeleconsultaGuardar').on('click', function () {
					disableObjects(true);
					$.ajax({
						type: 'POST',
						url: "../vista-citas-telemedicina/ajax/registroCitasTelemedicina.ajax?accion=guardar&p=<?php print($loCita->getIdTipo()); ?>&q=<?php print($loCita->getIdNumero()); ?>&r=<?php print($loCita->getCita()); ?>&s=<?php print($loCita->getConsulta()); ?>&t=<?php print($loCita->getEvolucion()); ?>",
						data: {ESTADO: $('#cEstado').val(), VALORACION: $('#nValoracion').val(), OBSERVACION: $('#cObservacion').val(),  FECHA_REALIZA: <?php print(intval(date('Ymd'))); ?>, HORA_REALIZA: <?php print(intval(date('Hms'))); ?>}
					})
					.done(function(response) {
						if(typeof response.error !== "undefined"){
							if(response.error==false){
								fnAlert(response.status, 'Guardado', 'far fa-check-circle', 'green', false);
							}else{
								fnAlert(response.status, 'No guardado', 'far fa-check-circle', 'red', false);
							}
						}else{
							fnAlert('No se realizo el llamado con existo', 'No guardado', 'far fa-check-circle', 'red', false);
						}
						disableObjects(false);
					})
					.fail(function(data) {
						fnAlert('Se guardo la bit&aacute;cora '+response, 'Guardado', 'far fa-check-circle', 'green', false);
						disableObjects(false);
					});
				});

				$('#nValoracion').rating({
					showCaption: false,
					stars: 5,
					min: 0,
					max: 5,
					step: 1,
					size: 'xs',
					starCaptions: {1:'Muy Insatisfecho', 2:'Insatisfecho', 3:'Satisfecho', 4:'Muy Satisfecho', 5:'Increiblemente satisfecho'},
					filledStar: '<i class="fas fa-star"></i>',
					emptyStar: '<i class="far fa-star"></i>',
					showClear: false,
					clearCaption: 'Sin valorar'
				});

			});
		</script>
		<?php } ?>
	</body>
</html>