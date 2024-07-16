<?php
	require_once (__DIR__ .'/../../controlador/class.SignosNews.php');
	require_once (__DIR__ .'/../../controlador/class.SeccionesHabitacion.php');
	require_once (__DIR__ .'/../../controlador/class.EstadosHabitacion.php');
	require_once (__DIR__ .'/../../controlador/class.TiposAlerta.php');
	require_once (__DIR__ .'/../../controlador/class.Ingreso.php');
	require_once (__DIR__ .'/../../controlador/class.AplicacionFunciones.php');

	$llIngreso = false;
	$llSecciones = false;
	$lcMensaje = '';
	$lnIngreso = 0;
	$lcSeccion = '';
	$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
	$lnFecha = $ltAhora->format("Ymd"); settype($lnFecha,"integer");
	$loSignosNews = new NUCLEO\SignosNews();
	$laTiposAlerta = (new NUCLEO\TiposAlerta())->aTipos;
	$laEstadosHabitacion = (new NUCLEO\EstadosHabitacion())->aTipos;

	// Cargando el ingreso para cuando aplique
	$lnIngreso = $_SESSION[HCW_NAME]->getIngresoSmartRoom();
	$lbIngresoSmartRoom = $lnIngreso > 0;
	if (!$lbIngresoSmartRoom) {
		$lnIngreso = isset($_POST['ingreso']) ? $_POST['ingreso'] : (isset($_GET['ingreso']) ? $_GET['ingreso'] : 0);
		settype($lnIngreso,'Integer');
	}
	if($lnIngreso>0){
		$loIngreso = new NUCLEO\Ingreso;
		$loIngreso->cargarIngreso($lnIngreso);

		if($loIngreso->nIngreso>0){
			if($loIngreso->esActivoParaRegistros()==true){
				$llIngreso=true;
				$lcSeccion='';
			}else{
				$lcMensaje='<i class="fa fa-exclamation-triangle"></i> NO está activo el ingreso '.$lnIngreso;
			}
		}else{
			$lcMensaje='<i class="fa fa-exclamation-triangle"></i> No se encontró el número de ingreso '.$lnIngreso;
		}
	}

	// Cargando la sección que aplique
	if($llIngreso == false){
		$lcSeccion=(isset($_POST['seccion'])?$_POST['seccion']:(isset($_GET['seccion'])?$_GET['seccion']:''));
		settype($lnIngreso,'String'); $lcSeccion=trim($lcSeccion);
		if(!empty($lcSeccion)){
			$llSecciones = true;
		}
	}

?>
	<div class="container-fluid">
		<div class="card mt-3">
			<div class="card-header">
				<h5>Registro de Acciones para Alertas Tempranas</h5>
				<?php
				if($llIngreso==false){
				?>
				<form role="form" id="registroSignos" name="registroSignos" method="POST" enctype="application/x-www-form-urlencoded" action="modulo-alerta-temprana&p=registroAlerta">
					<div id="filtro" class="row">
						<div class="col-md-3 pb-2">
							<label for="ingreso"><b>Ingreso</b></label>
							<input type="number" class="form-control form-control-sm" name="ingreso" id="ingreso" min="0" max="99999999" placeholder="" value="">
						</div>
						<div class="col-md-7 pb-2">
							<label for="seccion"><b>Secci&oacute;n</b></label>
							<?php
								$laSecciones = (new NUCLEO\SeccionesHabitacion())->aSecciones;
								if(is_array($laSecciones)==true){
									printf('<select class="custom-select custom-select-sm" id="seccion" name="seccion">');
									printf('<option></option>');
									foreach($laSecciones as $lcSeccionId => $laSeccion){
										if($laSeccion['UBICACION']=='P' && $laSeccion['SALA']<>'S'){
										// if(!empty($laSeccion['PANTALLA'])){
											$lcSelected = ($lcSeccion==$lcSeccionId?' selected':'');
											printf('<option value="%s"%s>%s</option>',$lcSeccionId,$lcSelected,$laSeccion['NOMBRE']);
										}
									}
									printf('</select>');
								}
							?>
						</div>
						<div class="col-md-2 pb-2">
							<label for="btnBuscar">&nbsp;</label>
							<button id="btnBuscar"  type="submit" class="form-control form-control-sm btn btn-secondary btn-sm" accesskey="B"><u>B</u>uscar</button>
						</div>
					</div>
				</form>
				<?php }else{ ?>
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="modulo-alerta-temprana&p=registroAlerta<?php print(isset($_GET['seccion'])==true?'&seccion='.$_GET['seccion']:''); ?>"><i class="far fa-building"></i> Volver <?php print(isset($_GET['seccion'])?$_GET['seccion']:''); ?></a></li>
						<li class="breadcrumb-item active" aria-current="page"><?php print($lnIngreso); ?></li>
					</ol>
				</nav>
				<?php } ?>
				<div class="row"><div class="col"><div id="ingresoInfo"><?php print($lcMensaje); ?></div></div></div>
			</div>
			<div id ="registroSignos" class="card-body">
				<?php
				if($llSecciones==true){
				?>
				<div class="row">
					<div class="col-6 text-left">
					</div>
					<div class="col-6 text-right">
						<small class="text-secondary">
						<?php
							foreach($laTiposAlerta as $laTipoAlerta){
								printf('%s <i class="fas fa-%s"></i> | ',$laTipoAlerta['NOMBRE'],$laTipoAlerta['ICONOW']);
							}
						?>
						</small>
					</div>
				</div>
				<table class="table table-sm table-striped table-hover">
					<thead>
						<tr>
							<th>Piso</th>
							<th>Cama</th>
							<th>Ingreso</th>
							<th>Documento</th>
							<th>Paciente</th>
							<th class="text-right">Estado Habitaci&oacute;n</th>
							<th class="text-right">Tipo</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$laCampos = ['A.SECHAB',
										 'A.NUMHAB',
										 'A.INGHAB',
										 'A.TIDHAB',
										 'A.NIDHAB',
										 'A.ESTHAB',
										 "TRIM(IFNULL(B.NM1PAC,''))||' '||TRIM(IFNULL(NM2PAC,''))||' '||TRIM(IFNULL(B.AP1PAC,''))||' '||TRIM(IFNULL(B.AP2PAC,'')) NOMBRE",
										 '(SELECT COUNT(*) FROM ALETEMP AS D WHERE D.NIGING=A.INGHAB AND D.ESTADO=0) AS ALERTAS',
										 'C.ESTING'];

							$lcWhere = sprintf("A.SECHAB = '%s' AND A.INGHAB>0 AND (C.ESTING = '2' OR C.FEEING=0 OR ((C.ESTING = '3' OR C.ESTING = '4') AND (C.FEEING = %s)))",$lcSeccion,$lnFecha);

							$laHabitaciones = $goDb->select($laCampos)
											  ->from('FACHAB A')
											  ->leftJoin('RIAPAC B', 'A.TIDHAB = B.TIDPAC AND A.NIDHAB = B.NIDPAC')
											  ->leftJoin('RIAING C', 'A.INGHAB = C.NIGING')
											  ->where($lcWhere)
											  ->orderBy('A.INGHAB')
											  ->getAll("array");

							if(is_array($laHabitaciones)==true){
								foreach($laHabitaciones as $laHabitacion){
									if($laHabitacion['ALERTAS']>0){
										$laAlertasTipo = $goDb->select('A.VAR29N TIPO')
															  ->from('ALETEMP A')
															  ->where('A.NIGING','=',$laHabitacion['INGHAB'])
															  ->where('A.ESTADO','=','0')
															  ->groupBy('A.VAR29N')
															  ->orderBy('A.VAR29N')
															  ->getAll("array");

										$lcIconoTipo = '';
										foreach($laAlertasTipo as $laAlertaTipo){
											$lnAlertaTipo = $laAlertaTipo['TIPO']; settype($lnAlertaTipo,'integer');
											$lcIconoTipo .= sprintf('<span class="badge badge-light"><i class="fas fa-%s"></i></span> ',$laTiposAlerta[$lnAlertaTipo]['ICONOW']);
										}

										$lcEstadoHabitacion="";
										if(isset($laEstadosHabitacion[$laHabitacion['ESTHAB']])==true){
											$lcEstadoHabitacion=sprintf('<small>%s <span class="badge" style="background-color: %s;width: 13px;height: 13px;">&nbsp;</span></small>',$laEstadosHabitacion[$laHabitacion['ESTHAB']]['NOMBRE'],$laEstadosHabitacion[$laHabitacion['ESTHAB']]['COLOR']);
										}
						?>
						<tr style="cursor:pointer;">
							<td class='ingresoHabitacion' data-ingreso='<?php print($laHabitacion['INGHAB']); ?>' data-seccion='<?php print($lcSeccion); ?>'><?php print($laHabitacion['SECHAB']); ?></td>
							<td class='ingresoHabitacion' data-ingreso='<?php print($laHabitacion['INGHAB']); ?>' data-seccion='<?php print($lcSeccion); ?>'><?php print($laHabitacion['NUMHAB']); ?></td>
							<td class='ingresoHabitacion' data-ingreso='<?php print($laHabitacion['INGHAB']); ?>' data-seccion='<?php print($lcSeccion); ?>'><?php print($laHabitacion['INGHAB']); ?></td>
							<td class='ingresoHabitacion' data-ingreso='<?php print($laHabitacion['INGHAB']); ?>' data-seccion='<?php print($lcSeccion); ?>'><?php print($laHabitacion['TIDHAB'].' - '.$laHabitacion['NIDHAB']); ?></td>
							<td class='ingresoHabitacion' data-ingreso='<?php print($laHabitacion['INGHAB']); ?>' data-seccion='<?php print($lcSeccion); ?>'><?php print($laHabitacion['NOMBRE']); ?></td>
							<td class='ingresoHabitacion text-right' data-ingreso='<?php print($laHabitacion['INGHAB']); ?>' data-seccion='<?php print($lcSeccion); ?>'><?php print($lcEstadoHabitacion); ?></td>
							<td class='ingresoHabitacion text-right' data-ingreso='<?php print($laHabitacion['INGHAB']); ?>' data-seccion='<?php print($lcSeccion); ?>'><?php print($lcIconoTipo); ?></td>
						</tr>
						<?php
									}
								}
							}
						?>
					</tbody>
				</table>
				<?php
				}
				?>
				<?php
				if($llIngreso==true){
					$laAlertas = $goDb->select('COUNT(*) ALERTAS')
									  ->tabla('ALETEMP A')
									  ->where('A.NIGING','=',$loIngreso->nIngreso)
									  ->where('A.ESTADO','=','0')
									  ->get("array");

					$laAlertasTipo = $goDb->select('A.VAR29N TIPO')
										  ->from('ALETEMP A')
										  ->where('A.NIGING','=',$loIngreso->nIngreso)
										  ->where('A.ESTADO','=','0')
										  ->groupBy('A.VAR29N')
										  ->orderBy('A.VAR29N')
										  ->getAll("array");

					if($laAlertas['ALERTAS']>0){
				?>
				<form role="form" id="registroAlertaForm" name="registroAlertaForm" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
					<p>Paciente Ingreso No. <span class="badge badge-success" id="nIngresoMostrar"><?php echo $loIngreso->nIngreso; ?></span></p>
					<div class="media">
						<div class="align-self-center mr-3 mb-3">
							<i class="fas fa-heartbeat fa-5x"></i>
						</div>
						<div class="media-body">
							<h4><span id="cNombre"><?php echo $loIngreso->oPaciente->getNombreCompleto();?></span></h4>
							<span id="cTipoIdMostrar"><?php echo $loIngreso->oPaciente->aTipoId["TIPO"];?></span> - <span id="nIdMostrar"><?php echo $loIngreso->oPaciente->nId; ?></span><br/>
							<span id="cEdad"><?php echo $loIngreso->oPaciente->getEdad(); ?></span><br/>
							<span id="cUbicacion"><?php echo $loIngreso->oHabitacion->cUbicacion; ?></span>
						</div>
					</div>
					<input type="hidden" id="nIngreso" name="nIngreso" value="<?php echo $loIngreso->nIngreso; ?>">
					<input type="hidden" id="cTipoId" name="cTipoId" value="<?php echo $loIngreso->oPaciente->aTipoId["TIPO"]; ?>">
					<input type="hidden" id="nId" name="nId" value="<?php echo $loIngreso->oPaciente->nId; ?>">
					<input type="hidden" id="nEdad" name="nEdad" value="<?php echo $loIngreso->oPaciente->getEdad(date('Y-m-d'),'%y'); ?>">
					<!-- <div class="row align-items-end"> -->
					<?php
						// carga últimos signos
						$laUltimos=$loSignosNews->consultarUltimos($loIngreso->nIngreso,$loIngreso->oPaciente->aTipoId["TIPO"],$loIngreso->oPaciente->nId);
						$loSignosNews->medir($laUltimos);
						$laSignos=$loSignosNews->getSignos();
						$laRtas=$loSignosNews->getRespuestas();
						$lcColor='alert alert-'.$laRtas[$laUltimos['CLASIFICACION']]['color'];
					?>
					<p><br>
						<a class="btn btn-secondary btn-sm" data-toggle="collapse" href="#collapsePuntaje" role="button" aria-expanded="false" aria-controls="collapseExample">
							Puntaje de alerta temprana</a>
					</p>
					<div class="collapse" id="collapsePuntaje">
						<div class="card">
							<div class="card-body">
								<h5 class="<?php echo $lcColor; ?> card-title">Puntaje de alerta temprana
									<small>(<?php echo NUCLEO\AplicacionFunciones::formatFechaHora('fechahora',$laUltimos['FECHA'].$laUltimos['HORA']); ?>)</small></h5>
								<table class="table table-bordered table-hover table-sm">
									<thead>
										<tr class="table-light">
											<th colspan="2" class="text-center">Par&aacute;metro fisiol&oacute;gico</th>
											<th colspan="2" class="text-center">Puntajes</th>
										</tr>
										<tr class="table-light">
											<th>Tipo</th>
											<th class="text-right">Medici&oacute;n</th>
											<th class="text-right">NEWS</th>
											<th class="text-right">qSOFA</th>
										</tr>
									</thead>
									<tbody>
										<?php
											foreach($laSignos as $lcSigno => $laSigno){
										?>
											<tr <?php print($laSigno['puntaje']>0?'style="font-weight: bold;"':'')?>>
												<td><?php print($laSigno['titulo']); ?></td>
												<td class="text-right"><?php print($laSigno['tipo']=='select'?$laSigno['valores'][$laSigno['valor']]['NOMBRE']:$laSigno['valor']); ?></td>
												<td class="text-right"><?php print($laSigno['puntaje']); ?></td>
												<td class="text-right"><?php print(isset($laSigno['puntajeQSOFA'])?$laSigno['puntajeQSOFA']:'--'); ?></td>
											</tr>
										<?php
											}
										?>
									</tbody>
									<tfoot>
										<tr>
											<th colspan="2"></th>
											<th class="text-right"><?php print($laUltimos['PUNTAJE']); ?><sup><small>puntos</small></sup></th>
											<th class="text-right"><?php printf("%1.0f",$laUltimos['PUNTAJEQSOFA']); ?><sup><small>puntos</small></sup></th>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
					</div>

					<div class="row align-items-end">
						<div class="col-md-12">
							<label for="cEquipo"><b>Integrantes del equipo de respuesta r&aacute;pida</b></label>
							<input id="cEquipo" name="cEquipo" type="text" class="form-control" autocomplete="off" required="required">
						</div>
					</div>
					<div class="row align-items-end">
						<div class="col-md-12">
							<label for="cAccion"><b>Conducta a seguir con la alerta</b></label>
							<select class="form-control" id="cAccion" name="cAccion" required="required">
								<option></option>
								<?php
									$laConductas = $loSignosNews->getConductas();
									foreach ($laConductas as $laConducta) {
										echo '<option value="'.$laConducta['TIPO'].'">'.$laConducta['NOMBRE'].'</option>';
									}
								?>
							</select>
						</div>
					</div>
					<div class="row align-items-end">
						<div class="col-md-12">
							<label for="cAccion"><b>Tipo de alerta a responder </b></label>
							<?php
							$lcChecked=(count($laAlertasTipo)==1?'checked="checked"':'');
							foreach($laAlertasTipo as $laAlertaTipo){
								$lnAlertaTipo = $laAlertaTipo['TIPO']; settype($lnAlertaTipo,'integer');
							?>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="checkbox" id="cTipoAlerta[<?php print($lnAlertaTipo); ?>]" name="cTipoAlerta[]" value="<?php print($lnAlertaTipo); ?>" <?php print($lcChecked); ?>>
								<label class="form-check-label" for="cTipoAlerta[<?php print($lnAlertaTipo); ?>]"><?php print($laTiposAlerta[$lnAlertaTipo]['NOMBRE']); ?></label>
							</div>
							<?php
							}
							?>
							<input type="hidden" id="nTiposAlerta" name="nTiposAlerta" value="0">
						</div>
					</div>
					<div class="row align-items-end">
						<div class="col-md-12">
							<label for="cDescripcion"><b>Descripci&oacute;n</b></label>
							<textarea id="cDescripcion" name="cDescripcion" class="form-control" rows="3" maxlength="510" required="required"></textarea>
						</div>
					</div>
					<hr/>
					<div class="form-group row">
						<div class="col-12">
							<button type="submit" class="btn btn-outline-secondary btn-lg btn-block">Guardar</button>
						</div>
					</div>
				</form>
				<?php
					}else{
				?>
				<div class="alert alert-info" role="alert">
					No hay alerta para responder
				</div>
				<?php
					}
				}
				?>
			</div>
			<div class="p-3"><div id="registroAlertaInfo"></div></div>
			<div class="card-footer text-muted">
				<p>La informaci&oacute;n aqu&iacute; registrada se usara para determina el grado de enfermedad de un paciente para la detecci&oacute;n temprana del deterioro cl&iacute;nico y la posible necesidad de un mayor nivel de atenci&oacute;n.</p>
			</div>
		</div>
	</div>

	<script src="publico-complementos/bootstrap-tagsinput/2.3.2-dist/js/typeahead.bundle.min.js"></script>
	<script src="publico-complementos/bootstrap-tagsinput/2.3.2-dist/js/bootstrap-tagsinput-angular.min.js"></script>
	<script src="publico-complementos/bootstrap-tagsinput/2.3.2-dist/js/bootstrap-tagsinput.min.js"></script>
	<script src="vista-alerta-temprana/js/scripts.js.php"></script>