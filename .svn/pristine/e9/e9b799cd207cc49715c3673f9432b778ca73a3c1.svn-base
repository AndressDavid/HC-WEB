<?php
	require_once (__DIR__ .'/../../controlador/class.SignosNews.php') ;
	require_once (__DIR__ .'/../../controlador/class.SeccionesHabitacion.php') ;
	require_once (__DIR__ .'/../../controlador/class.EstadosHabitacion.php') ;
	require_once (__DIR__ .'/../../controlador/class.Ingreso.php') ;

	$llIngreso = false;
	$llSecciones = false;
	$lcMensaje = '';
	$lnIngreso = 0;
	$lcSeccion = '';
	$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
	$lnFecha = $ltAhora->format("Ymd"); settype($lnFecha,"integer");

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
		$loSignosNews = new NUCLEO\SignosNews();

		if($loIngreso->nIngreso>0){
			if($loIngreso->esActivoParaRegistros()==true){
				$llIngreso=true;
				$lcSeccion='';
			}else{
				$lcMensaje='<i class="fa fa-exclamation-triangle"></i> NO esta activo el ingreso '.$lnIngreso;
			}
		}else{
			$lcMensaje='<i class="fa fa-exclamation-triangle"></i> No se enontro el numero de ingreso '.$lnIngreso;
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
				<h5>Signos vitales</h5>
				<?php
				if($llIngreso==false){
				?>
				<form role="form" id="buscarSignosForm" name="buscarSignosForm" method="POST" enctype="application/x-www-form-urlencoded" action="modulo-signos">
					<div id="filtro" class="row">
						<div class="col-md-3 pb-2">
							<label for="ingreso"><b>Ingreso</b></label>
							<input type="number" class="form-control form-control-sm" name="ingreso" id="ingreso" min="0" max="99999999" placeholder="" value="">
						</div>
						<div class="col-md-7 pb-2">
							<label for="ingreso"><b>Secci&oacute;n</b></label>
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
						<li class="breadcrumb-item"><a href="modulo-signos<?php print(isset($_GET['seccion'])==true?'&seccion='.$_GET['seccion']:''); ?>"><i class="far fa-building"></i> Volver <?php print(isset($_GET['seccion'])?$_GET['seccion']:''); ?></a></li>
						<li class="breadcrumb-item active" aria-current="page"><?php print($lnIngreso); ?></li>
					</ol>
				</nav>
				<?php } ?>
				<div class="row"><div class="col"><div id="ingresoInfo"><?php print($lcMensaje); ?></div></div></div>
			</div>
			<div id ="registroSignos" class="card-body">
				<?php
				if($llSecciones==true){
					$laEstadosHabitacion = (new NUCLEO\EstadosHabitacion())->aTipos;
				?>
				<table class="table table-sm table-striped table-hover">
					<thead>
						<tr>
							<th>Piso</th>
							<th>Cama</th>
							<th>Ingreso</th>
							<th>Documento</th>
							<th>Paciente</th>
							<th class="text-right">Estado Habitaci&oacute;n</th>
							<th></th>
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
										 '(SELECT COUNT(*) FROM ALETEMP AS D WHERE D.NIGING=A.INGHAB AND D.ESTADO=0 AND D.RESFEA=0 AND D.VAR29N=0) AS ALERTAS',
										 '(SELECT COUNT(*) FROM ALETEMP AS D WHERE D.NIGING=A.INGHAB AND D.ESTADO>0 AND D.VAR29N=0) AS ATENDIDAS',
										 'C.ESTING'];

							$lcWhere = sprintf("A.SECHAB = '%s' AND A.INGHAB>0 AND (C.ESTING = '2' OR C.FEEING=0 OR ((C.ESTING = '3' OR C.ESTING = '4') AND (C.FEEING = %s)))",$lcSeccion,$lnFecha);

							$laHabitaciones = $goDb->select($laCampos)
											  ->tabla('FACHAB A')
											  ->leftJoin('RIAPAC B', 'A.TIDHAB = B.TIDPAC AND A.NIDHAB = B.NIDPAC')
											  ->leftJoin('RIAING C', 'A.INGHAB = C.NIGING')
											  ->where($lcWhere)
											  ->getAll("array");

							$laRegistrables=['1','9'];
							if(is_array($laHabitaciones)==true){
								foreach($laHabitaciones as $laHabitacion){

									$lcEstadoHabitacion="";
									$lcRegistrable=sprintf("class='ingresoHabitacion' data-ingreso='%s' data-seccion='%s'",$laHabitacion['INGHAB'],$lcSeccion);

									if(isset($laEstadosHabitacion[$laHabitacion['ESTHAB']])==true){
										$lcEstadoHabitacion=sprintf('<small>%s <span class="badge" style="background-color: %s;width: 13px;height: 13px;">&nbsp;</span></small>',$laEstadosHabitacion[$laHabitacion['ESTHAB']]['NOMBRE'],$laEstadosHabitacion[$laHabitacion['ESTHAB']]['COLOR']);
										$lcRegistrable=(in_array($laHabitacion['ESTHAB'],$laRegistrables)?$lcRegistrable:'');
									}
						?>
						<tr <?php print(!empty($lcRegistrable)?'style="cursor:pointer;"':''); ?>>
							<td <?php print($lcRegistrable); ?>><?php print($laHabitacion['SECHAB']); ?></td>
							<td <?php print($lcRegistrable); ?>><?php print($laHabitacion['NUMHAB']); ?></td>
							<td <?php print($lcRegistrable); ?>><?php print($laHabitacion['INGHAB']); ?></td>
							<td <?php print($lcRegistrable); ?>><?php print($laHabitacion['TIDHAB'].' - '.$laHabitacion['NIDHAB']); ?></td>
							<td <?php print($lcRegistrable); ?>><?php print($laHabitacion['NOMBRE']); ?></td>
							<td <?php print($lcRegistrable); ?> style="text-align: right!important;"><?php print($lcEstadoHabitacion); ?></td>
							<td class="text-right">
								<?php
									if($laHabitacion['ATENDIDAS']>0 && $laHabitacion['ALERTAS']<1){
										print('<a id="llamar-'.$laHabitacion['INGHAB'].'" class="btnAccionERR btn btn-sm btn-danger" href="#" data-accion="llamar" data-ingreso="'.$laHabitacion['INGHAB'].'" data-nombre="'.$laHabitacion['NOMBRE'].'" data-toggle="tooltip" data-placement="bottom"title="Llamar ERR"><i class="fas fa-bell"></i></a>');
									}
								?>
								<?php
									if($laHabitacion['ALERTAS']>0){
										print('<a id="marcar-'.$laHabitacion['INGHAB'].'" class="btnAccionERR btn btn-sm btn-secondary" href="#" data-accion="marcar" data-ingreso="'.$laHabitacion['INGHAB'].'" data-nombre="'.$laHabitacion['NOMBRE'].'" data-toggle="tooltip" data-placement="bottom" title="Llegada ERR"><i class="fas fa-stopwatch"></i></a>');
									}
								?>
							</td>
						</tr>
						<?php
								}
							}
						?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="6">
								<small>
									<span class="badge badge-danger p-2"><i class="fas fa-bell"></i></span> Actvaci&oacute;n manual del ERR<br/>
									<span class="badge badge-secondary p-2"><i class="fas fa-stopwatch"></i></span> Marca la fecha y hora de llegada del ERR (Equipo de respuesta r&aacute;pida)
								</small>
							</td>
						</tr>
					</tfoot>
				</table>
				<?php
				}
				?>
				<?php
				if($llIngreso==true){
				?>
				<form role="form" id="registroSignosForm" name="registroSignosForm" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
					<p>Paciente Ingreso No. <span class="badge badge-success" id="nIngresoMostrar"></span></p>
					<input type="hidden" id="cSeccion" name="cSeccion" value="<?php print(isset($_GET['seccion'])==true?$_GET['seccion']:''); ?>">
					<input type="hidden" id="cHabitacion" name="cHabitacion" value="<?php print($loIngreso->oHabitacion->cHabitacion);?>">
					<input type="hidden" id="nIngreso" name="nIngreso" value="<?php print($loIngreso->nIngreso); ?>">
					<input type="hidden" id="cTipoId" name="cTipoId" value="<?php print($loIngreso->oPaciente->aTipoId["TIPO"]); ?>">
					<input type="hidden" id="nId" name="nId" value="<?php print($loIngreso->oPaciente->nId); ?>">
					<input type="hidden" id="nEdad" name="nEdad" value="<?php print($loIngreso->oPaciente->getEdad(date('Y-m-d'),'%y')); ?>">
					<input type="hidden" id="cPaciente" name="cPaciente" value="<?php print($loIngreso->oPaciente->getNombreCompleto());?>">
					<div class="media">
						<div class="align-self-center mr-3 mb-3">
							<i class="fas fa-heartbeat fa-5x"></i>
						</div>
						<div class="media-body">
							<h4><span id="cNombre"><?php print($loIngreso->oPaciente->getNombreCompleto());?></span></h4>
							<span id="cTipoIdMostrar"><?php print($loIngreso->oPaciente->aTipoId["TIPO"]);?></span> - <span id="nIdMostrar"><?php print($loIngreso->oPaciente->nId); ?></span><br/>
							<span id="cEdad"><?php print($loIngreso->oPaciente->getEdad()); ?></span><br/>
							<span id="cUbicacion"><?php print($loIngreso->oHabitacion->cUbicacion); ?></span>
						</div>
					</div>
					<div class="row align-items-end">
						<?php
							foreach($loSignosNews->getSignos() as $lcSigno => $laSigno){
								if(is_array($laSigno)){
									printf('<div class="col-lg-3 col-md-3 col-sm-6 col-12">');
									printf('<div class="form-group">');
									printf('<label for="lastName"><b>%s</b></label>',$laSigno['titulo']);

									switch ($laSigno['tipo']){
										case 'select':
											printf('<select class="form-control form-control-lg" id="%s" name="%s" required="">',$lcSigno,$lcSigno);
											printf('<option></option>');
											if(isset($laSigno['valores'])==true){
												$laSelectValores=$laSigno['valores'];
												foreach($laSelectValores as $lnKey=>$laValue){
													printf('<option value="%s">%s</option>',$lnKey,$laValue["NOMBRE"]);
												}
											}
											printf('</select>');
											break;

										default:
											printf('<input type="number" class=" form-control form-control-lg" id="%s" name="%s" placeholder="" value="" required="">',$lcSigno,$lcSigno);
											break;
									}
									printf('</div>');
									printf('</div>');
								}
							}
						?>
					</div>
					<hr/>
					<div class="form-group row">
						<div class="col-12">
							<button id="btnGuardar" type="submit" class="btn btn-outline-secondary btn-lg btn-block">Guardar</button>
						</div>
					</div>
				</form>
				<script>
					$( document ).ready( function () {
						$( "#registroSignosForm" ).validate( {
							rules: {
								ingreso: {
									required: true,
									digits: true
								},
								<?php
									if(isset($loSignosNews)==true){
										foreach($loSignosNews->getSignos() as $lcSigno => $laSigno){
											if(is_array($laSigno)){
												if(isset($laSigno['max'])==true && isset($laSigno['min'])==true){
													switch($laSigno['dato']){
														case "integer":
															printf('%s: {required: true, digits: true, min: %s, max: %s},',$lcSigno,$laSigno['min'],$laSigno['max']);
															break;
														case "float":
															$lcStep='';
															if($lcSigno='t'){
																$lcStep=', step: 0.01';
															}
															printf('%s: {required: true, range: [%s, %s]%s},',$lcSigno,$laSigno['min']+0.00,$laSigno['max']+0.00,$lcStep);
															break;
													}
												}
											}
										}
									}
								?>
							},
							errorElement: "div",
							errorPlacement: function ( error, element ) {
								// Add the `help-block` class to the error element
								error.addClass( "invalid-tooltip" );

								if ( element.prop( "type" ) === "checkbox" ) {
									error.insertAfter( element.parent( "label" ) );
								} else {
									error.insertAfter( element );
								}
							},
							highlight: function ( element, errorClass, validClass ) {
								$( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
							},
							unhighlight: function (element, errorClass, validClass) {
								$( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
							},
							submitHandler: function () {
								$("#btnGuardar").attr("disabled",  true);
								$.ajax({
									type: 'POST',
									url: "vista-signos/guardarSignos",
									data: $("#registroSignosForm").serialize()
								})
								.done(function(response) {
									$('#registroSignosInfo').html(response);
									$("#modalSignosGuardar").on("hidden.bs.modal", function () {
										$('#registroSignosInfo').html('').removeClass("alert").removeClass("alert-warning").removeAttr("role");
										$("#registroSignosForm")[0].reset();
										$(location).attr('href', 'modulo-signos<?php print(isset($_GET['seccion'])==true?'&seccion='.$_GET['seccion']:''); ?>');
									});
									$('#modalSignosGuardar').modal('show');
								})
								.fail(function(data) {
									$('#registroSignosInfo').html('<i class="fa fa-exclamation-triangle"></i> Se presento un error al guardar el ingreso').addClass("alert").addClass("alert-danger").attr("role","alert");
								});
							}
						} );
					} );
				</script>
				<?php
				}else{
				?>
				<!-- login -->
				<div id="formAccionErr" class="modal fade formAccionErr" tabindex="-1" role="dialog" aria-labelledby="formAccionErr">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">
									Ingreso # <b><span id="ingresoAccionERR"></span></b><br/>
									Paciente <b><span id="nombreAccionERR"></span></b><br/>
								</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<p><span id="textoAccionERR"></span></p>
								<span id='infoAccionERR'></span>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
								<button id="btnAccionERR" data-objeto="" data-ingreso="0" data-accion="" type="button" class="btn btn-primary"><span id="AccionERR"></span></button>
							</div>
						</div>
					</div>
				</div>
				<?php
				}
				?>
			</div>
			<div class="p-3"><div id="registroSignosInfo"></div></div>
			<div class="card-footer text-muted">
				<p>La informaci&oacute;n aqu&iacute; registrada se usara para determina el grado de enfermedad de un paciente para la detecci&oacute;n temprana del deterioro cl&iacute;nico y la posible necesidad de un mayor nivel de atenci&oacute;n.</p>
			</div>
		</div>
	</div>
	<script src="vista-signos/js/scripts.js"></script>
