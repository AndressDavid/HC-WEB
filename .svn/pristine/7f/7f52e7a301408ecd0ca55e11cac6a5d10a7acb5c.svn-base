<?php
	$lnConsecutivo = $_POST['consecutivo'];
	require_once __DIR__ .'/../../controlador/class.AgendaSalasCirugia.php';
	require_once __DIR__ .'/../../controlador/class.AplicacionFunciones.php';
	$loAgenda = new NUCLEO\AgendaSalasCirugia();
	$laDatos = $loAgenda->consultaDatosAgendamiento($lnConsecutivo);
	$laPermisos = $loAgenda->permisoRegistrar($_SESSION[HCW_NAME]->oUsuario->getUsuario());
	$lcPermiso = '';

	if(isset($laPermisos)){
		if(is_array($laPermisos) == true){
			foreach($laPermisos as $laDato){
				$lcPermiso= $laDato;
			}
		}	
	}
	
	$laFiltroAgenda = [
		'sala' => $_POST['filtroSala']??'',
		'fecini' => $_POST['filtroFini']??'',
		'fecfin' => $_POST['filtroFfin']??'',
	];

	include __DIR__ . '/../comun/modalEspera.php';

?>

<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
		</div>

		<div class="card-body container-fluid">
			<form id="FormAgendaCirugia" name="FormAgendaCirugia" class="needs-validation" novalidate>
				<div id="divCabDatosSc" class="row pb-4">
					<div class="col-12 col-sm-12 col-md-3 col-lg-3">
						<label id="lblSalaSeleccionada" for="txtSalaSeleccionada">Sala agendar</label>
						<input id="txtSalaSeleccionada" type="text" class="form-control form-control-sm" name="SalaSeleccionada" value="<?php echo $_POST['sala']??''; ?>" disabled>
					</div>

					<div class="col-12 col-sm-12 col-md-3 col-lg-3">
						<label id="lblFechaProgramadaSeleccionada" for="txtFechaProgramadaSeleccionada" class="control-label">Fecha agendar</label>
						<div class="input-group date">
							<div class="input-group-prepend">
								<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
							</div>
							<input id="txtFechaProgramadaSeleccionada" type="text" class="form-control form-control-sm" name="FechaProgramadaSeleccionada" value="<?php echo $_POST['fecha']??''; ?>" disabled>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-3 col-lg-3">
						<label id="lblHoraSeleccionada" for="txtHoraSeleccionada">Hora agendar</label>
						<input id="txtHoraSeleccionada" type="text" class="form-control form-control-sm" name="HoraSeleccionada" value="<?php echo $_POST['hora']??''; ?>" disabled>
					</div>
				</div>

				<div class="row">
					<div class="col-12 col-sm-12 col-md-8 col-lg-11">
						<h5>Datos del paciente</h5>
					</div>
				</div>

				<div class="row">
					<div class="col-12 col-sm-12 col-md-4 col-lg-4">
						<label id="lblIdentificacion" for="inpNumDoc" class="control-label required">Documento</label>
						<div class="input-group mb-3">
							<select id="selTipDocSala" class="custom-select custom-select-sm col-6" name="TipDocSala" ></select>
							<input  id="txtNumDocSala" type="number" maxlength="13" class="form-control form-control-sm col-6" name="NumDocSala" value="<?php echo $laDatos['NIDSAL']??0; ?>" disabled>
						</div>
					</div>
					<div class="col-12 col-sm-12 col-md-2 col-lg-2">
						<label id="lblIngreso" for="txtIngreso">Ingreso</label>
						<input id="txtIngreso" type="number" class="form-control form-control-sm" name="Ingreso" value="<?php echo $laDatos['NIGSAL']??0; ?>" disabled>
					</div>

					<div class="col-12 col-sm-12 col-md-3 col-lg-2">
						<label id="lblHabitacionSala" for="txtHabitacionSala" class="control-label">Habitación</label>
						<div class="input-group">
							<input id="txtHabitacionSala" type="text" class="form-control form-control-sm" name="HabitacionSala" value="<?php echo $laDatos['HACSAL']??0; ?>" disabled>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-12 col-sm-12 col-md-4 col-lg-3 col-xl-2">
						<label id="lblPrimerNombre" for="txtPrimerNombre">Primer Nombre</label>
						<div class="input-group">
							<input id="txtPrimerNombre" type="text" class="form-control form-control-sm" name="PrimerNombre" style="text-transform:uppercase;" value="<?php echo $laDatos['NM1PAL']??''; ?>" disabled>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-3 col-xl-2">
						<label id="lblSegundoNombre" for="txtSegundoNombre" class="control-label">Segundo Nombre</label>
						<div class="input-group">
							<input id="txtSegundoNombre" type="text" class="form-control form-control-sm" name="SegundoNombre" placeholder="" style="text-transform:uppercase;" value="<?php echo $laDatos['NM2PAL']??''; ?>" disabled>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-3 col-xl-2">
						<label id="lblPrimerApellido" for="txtPrimerApellido">Primer Apellido</label>
						<div class="input-group">
							<input id="txtPrimerApellido" type="text" class="form-control form-control-sm" name="PrimerApellido" placeholder="" style="text-transform:uppercase;" value="<?php echo $laDatos['AP1PAL']??''; ?>"  disabled>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-3 col-xl-2">
						<label id="lblSegundoApellido" for="txtSegundoApellido" class="control-label">Segundo Apellido</label>
						<div class="input-group">
							<input id="txtSegundoApellido" type="text" class="form-control form-control-sm" name="SegundoApellido" placeholder="" style="text-transform:uppercase;" value="<?php echo $laDatos['AP2PAL']??''; ?>" disabled>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-3 col-xl-2">
						<label id="lblFechaNacimiento" for="txtFechaNacimiento">Fecha Nacimiento</label>
						<div class="input-group date">
							<div class="input-group-prepend">
								<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
							</div>
							<input id="txtFechaNacimiento" type="text" class="form-control form-control-sm" name="FechaNacimiento" disabled value="<?php echo NUCLEO\AplicacionFunciones::formatFechaHora('fecha',$laDatos['FNAPAL']??''); ?>">
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-3 col-xl-2">
						<label id="lblGeneroSala" for="txtGeneroSala">Genero</label>
						<div class="input-group">
							<select id="txtGeneroSala" class="form-control form-control-sm" name="GeneroSala" disabled></select>
						</div>
					</div>
				</div>

				<div class="row pb-4">
					<div class="col-12 col-sm-12 col-md-4 col-lg-3">
						<label id="lblTelefonoSala" for="txtITelefonoSala">Teléfono</label>
						<input id="txtITelefonoSala" type="number" class="form-control form-control-sm" name="TelefonoSala" value="<?php echo $laDatos['TP1PAL']??''; ?>" disabled>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-3">
						<label id="lblEmailSala" for="txtEmailSala">E-mail</label>
						<div class="input-group">
							<input id="txtEmailSala" type="text" class="form-control form-control-sm" name="EmailSala" value="<?php echo $laDatos['MAIPAL']??''; ?>" disabled>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-6">
						<label id="lblEntidadSala" for="selEntidadSala">Entidad</label>
						<div class="input-group">
							<select id="selEntidadSala" class="form-control form-control-sm" name="EntidadSala" disabled></select>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-12 col-sm-12 col-md-8 col-lg-11">
						<h5>Datos del procedimiento</h5>
					</div>
				</div>

				<div class="row">
					<div class="col-12 col-sm-12 col-md-4 col-lg-3 col-xl-2 h-100 align-self-end">
						<label for="lblFechaSolicitudMedico" for="txtFechaSolicitudMedico">Fecha solicitud cirujano</label>
						<div class="input-group date">
							<div class="input-group-prepend">
								<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
							</div>
							<input id="txtFechaSolicitudMedico" type="text" class="form-control form-control-sm" name="FechaSolicitudMedico" disabled value="<?php echo NUCLEO\AplicacionFunciones::formatFechaHora('fecha',$laDatos['fechaSolicitud']??''); ?>">
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-3 col-xl-2 h-100 align-self-end">
						<label id="lblOrigenSala" for="selOrigenSala">Origen</label>
						<div class="input-group">
							<select id="selOrigenSala" class="form-control form-control-sm" name="OrigenSala" disabled></select>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-12 col-sm-12 col-md-4 col-lg-3 h-100 align-self-end">
						<label id="lblEspecialidadMedico" for="selEspecialidadMedico">Especialidad</label>
						<select id="selEspecialidadMedico" class="form-control form-control-sm" name="EspecialidadMedico" disabled></select>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-3 h-100 align-self-end">
						<label id="lblMedicoPrograma" for="selMedicoPrograma">Médico que programa</label>
						<select id="selMedicoPrograma" class="form-control form-control-sm" name="MedicoPrograma" disabled></select>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-3 h-100 align-self-end">
						<label id="lblTipoAnestesiaSala" for="selTipoAnestesiaSala">Tipo de anestesia</label>
						<div class="input-group">
							<select id="selTipoAnestesiaSala" class="form-control form-control-sm" name="TipoAnestesiaSala" disabled></select>
						</div>
					</div>
					
					<div class="col-12 col-sm-12 col-md-4 col-lg-3 h-100 align-self-end">
						<label id="lblAnestesiologo" for="selAnestesiologo">Anestesiólogo</label>
						<div class="input-group">
							<select id="selAnestesiologo" class="form-control form-control-sm" name="Anestesiologo" disabled></select>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-12 col-sm-12 col-md-4 col-lg-3 col-xl-3 h-100 align-self-end">
						<label id="lblAutorizada" for="selAutorizada">Autorización</label>
						<div class="input-group">
							<select id="selAutorizada" class="form-control form-control-sm" name="Autorizada" disabled>
								<option value=""></option>
								<option value="S" <?php echo ($laDatos['autorizada']??'')=='S'?'selected="selected"':''; ?>>SI</option>
								<option value="N" <?php echo ($laDatos['autorizada']??'')=='N'?'selected="selected"':''; ?>>NO</option>
							</select>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-3 col-xl-3 h-100 align-self-end">
						<label id="lblTipoProcedimientoSala" for="selTipoProcedimientoSala">Tipo procedimiento</label>
						<div class="input-group">
							<select id="selTipoProcedimientoSala" class="form-control form-control-sm" name="TipoProcedimientoSala" disabled></select>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-3 col-xl-3 h-100 align-self-end">
						<label id="lblTiempoCups" for="txtTiempoCupsAd">Tiempo procedimiento</label>
						<div class="input-group">
							<input id="txtTiempoCups" name="TiempoCups" type="number" style="height:30px;" class="form-control mr-sm-2" value="<?php echo $laDatos['tiempoCups']??''; ?>" disabled> hr &nbsp;&nbsp;
							<select id="selTiempoMinutos" class="form-control form-control-sm" name="TiempoMinutos" disabled>
								<option value=""></option>
								<option value="00" <?php echo (isset($laDatos['tiempoMinutosCups'])) ? ($laDatos['tiempoMinutosCups']=='00'?'selected':'') : ''; ?>>0</option>
								<option value="30" <?php echo (isset($laDatos['tiempoMinutosCups'])) ? ($laDatos['tiempoMinutosCups']=='30'?'selected':'') : ''; ?>>30</option>
							</select> min
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-12 col-sm-12 col-md-8 col-lg-6 col-xl-6">
						<input id="codigoCupsSala" type="hidden">
						<input id="descripcionCupsSala" type="hidden">
						<label id="lblbuscarCupsSala" for="buscarCupsSala">Procedimiento</label>
						<div class="input-group">
							<input type="text" class="form-control form-control-sm" id="buscarCupsSala" name="cupsSala" placeholder="Procedimiento" disabled>
							<button type="button" class="btn btn-sm btn-outline-secondary" id="btnAdicionarCup">Adicionar</button>
						</div>
					</div>

					<div class="col-12">
						<table id="tblCups"></table>
					</div>
				</div>

				<div class="row">
					<div class="col-12 col-sm-12 col-md-4 col-lg-3 col-xl-2 h-100 align-self-end">
						<label id="lblLateralidadSala" for="selLateralidadSala">Lado de la cirugia</label>
						<div class="input-group">
							<select id="selLateralidadSala" class="form-control form-control-sm" name="LateralidadSala" disabled></select>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-2 h-100 align-self-end">
						<label id="lblCirugiaContaminada" for="selCirugiaContaminada">Cirugía contaminada</label>
						<div class="input-group">
							<select id="selCirugiaContaminada" class="form-control form-control-sm" name="CirugiaContaminada" disabled>
								<option value=""></option>
								<option value="S" <?php echo (isset($laDatos['cirugiaContaminada'])) ? ($laDatos['cirugiaContaminada']=='S'?'selected':'') : ''; ?>>SI</option>
								<option value="N" <?php echo (isset($laDatos['cirugiaContaminada'])) ? ($laDatos['cirugiaContaminada']=='N'?'selected':'') : ''; ?>>NO</option>
							</select>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-2 h-100 align-self-end">
						<label id="lblReintervencion" for="selReintervencion">Reintervención</label>
						<div class="input-group">
							<select id="selReintervencion" class="form-control form-control-sm" name="Reintervencion" disabled>
								<option value=""></option>
								<option value="S" <?php echo (isset($laDatos['reintervencion'])) ? ($laDatos['reintervencion']=='S'?'selected':'') : ''; ?>>SI</option>
								<option value="N" <?php echo (isset($laDatos['reintervencion'])) ? ($laDatos['reintervencion']=='N'?'selected':'') : ''; ?>>NO</option>
							</select>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-2 h-100 align-self-end">
						<label id="lblEscalaMents" for="txtEscalaMents">Escala MeNTS</label>
						<div class="input-group">
							<input id="txtEscalaMents" type="number" class="form-control form-control-sm" name="EscalaMents" value="<?php echo $laDatos['escalaMents']??''; ?>" disabled>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-2 h-100 align-self-end">
						<label id="lblPruebaCovid19" for="selPruebaCovid19">Prueba Covid-19</label>
						<div class="input-group">
							<select id="selPruebaCovid19" class="form-control form-control-sm" name="PruebaCovid19" disabled>
								<option value=""></option>
								<option value="S" <?php echo (isset($laDatos['pruebaCovid'])) ? ($laDatos['pruebaCovid']=='S'?'selected':'') : ''; ?>>SI</option>
								<option value="N" <?php echo (isset($laDatos['pruebaCovid'])) ? ($laDatos['pruebaCovid']=='N'?'selected':'') : ''; ?>>NO</option>
							</select>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-12 col-sm-12 col-md-4 col-lg-4 h-100 align-self-end">
						<label id="lblDispositivosCardiacoSala" for="selDispositivosCardiacoSala">Dispositivos estimulación cardiaca</label>
						<div class="input-group">
							<select id="selDispositivosCardiacoSala" class="form-control form-control-sm" name="DispositivosCardiacoSala" disabled></select>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-2 h-100 align-self-end">
						<label id="lblRequerimientos" for="selRequerimientos">Requerimientos Especiales</label>
						<div class="input-group">
							<select id="selRequerimientos" class="form-control form-control-sm" name="Requerimientos" disabled></select>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-2 h-100 align-self-end">
						<label id="lblViaAcceso" for="selViaAcceso">Vía de acceso</label>
						<div class="input-group">
							<select id="selViaAcceso" class="form-control form-control-sm" name="ViaAcceso" disabled></select>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-2 h-100 align-self-end">
						<label id="lblReservaSangre" for="selReservaSangre">Reserva de sangre</label>
						<div class="input-group">
							<select id="selReservaSangre" class="form-control form-control-sm" name="ReservaSangre" disabled>
								<option value=""></option>
								<option value="S" <?php echo (isset($laDatos['reservaSangre'])) ? ($laDatos['reservaSangre']=='S'?'selected':'') : ''; ?>>SI</option>
								<option value="N" <?php echo (isset($laDatos['reservaSangre'])) ? ($laDatos['reservaSangre']=='N'?'selected':'') : ''; ?>>NO</option>
							</select>
						</div>
					</div>

					<div class="col-12 col-sm-12 col-md-4 col-lg-2 h-100 align-self-end">
						<label id="lblAyudanteQuirurgico" for="selAyudanteQuirurgico">Ayudante quirúrgico</label>
						<div class="input-group">
							<select id="selAyudanteQuirurgico" class="form-control form-control-sm" name="AyudanteQuirurgico" disabled>
								<option value=""></option>
								<option value="S" <?php echo (isset($laDatos['ayudanteQuirurgico'])) ? ($laDatos['ayudanteQuirurgico']=='S'?'selected':'') : ''; ?>>SI</option>
								<option value="N" <?php echo (isset($laDatos['ayudanteQuirurgico'])) ? ($laDatos['ayudanteQuirurgico']=='N'?'selected':'') : ''; ?>>NO</option>
							</select>
						</div>
					</div>
				</div>
			</form>

			<div class="row">
				<div class="col-12 col-sm-12 col-md-8 col-lg-11">
					<h5>Equipos especiales</h5>
				</div>
			</div>
			<div><small><table id="tblEquiposEspeciales"></table></small></div>

			<form id="FormObservacionesCirugia" name="FormObservacionesCirugia" class="needs-validation" novalidate>
				<div class="row">
					<div class="col-12">
						<label for="txtObservacionesSala">Observaciones</label>
						<textarea class="form-control" id="txtObservacionesSala" rows="3" name="ObservacionesSala" value="" disabled><?php echo $laDatos['observaciones']??''; ?></textarea>
					</div>
				</div>
			</form>

		</div>

		<div class="card-footer text-muted fixed-bottom bg-light">
			<div class="row justify-content-around">
				<div class="col-12 justify-content-end">
					<?php if($lcPermiso=='N'){ ?><button id="btnGuardarSala" class="btn btn-secondary btn-sm" accesskey="G" disabled><u>G</u>uardar</button><?php } ?>
					<button id="btnLimpiarSala" type="button" class="btn btn-secondary btn-sm" accesskey="L"><u>L</u>impiar</button>
					<button id="btnCerrar" type="button" class="btn btn-secondary btn-sm" accesskey="C"><u>C</u>errar</button>
				</div>
			</div>
		</div>

	</div>
</div>

<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />
<link rel="stylesheet" type="text/css" href="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/localization/messages_es.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-4-autocomplete/1.2.3/dist/bootstrap-4-autocomplete.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="vista-comun/js/comun.js"></script>
<script type="text/javascript" src="vista-comun/js/tiposDocumentos.js"></script>

<script type="text/javascript">
	var gnConsecutivo = '<?php echo trim($laDatos['CONSAL']??0); ?>';
	var gcTipoIdentificacion = '<?php echo trim($laDatos['TIDSAL']??''); ?>';
	var gcEntidad = '<?php echo trim($laDatos['PLASAL']??''); ?>';
	var gcOrigen = '<?php echo trim($laDatos['origen']??''); ?>';
	var gcEspecialidadSolicita = '<?php echo trim($laDatos['especialidadSolicita']??''); ?>';
	var gRegistroSolicita = '<?php echo trim($laDatos['medicoSolicita']??''); ?>';
	var gcTipoProcedimiento = '<?php echo trim($laDatos['tipoProcedimiento']??''); ?>';
	var gcLateralidad = '<?php echo trim($laDatos['lateralidad']??''); ?>';
	var gcAnestesiologo = '<?php echo trim($laDatos['anestesiologo']??''); ?>';
	var gcTipoAnestesia = '<?php echo trim($laDatos['tipoAnestesia']??''); ?>';
	var gcDispositivoCardiaca = '<?php echo trim($laDatos['dispositivoCardiaca']??''); ?>';
	var gcRequerimientosEspec = '<?php echo trim($laDatos['requerimientos']??''); ?>';
	var gcViaAcceso = '<?php echo trim($laDatos['viaacceso']??''); ?>';
	var gcEquiposEspeciales = '<?php echo trim($laDatos['equiposEspeciales']??''); ?>';
	var gcCups = '<?php echo trim($laDatos['cups']??''); ?>';
	var gcGeneroPaciente = '<?php echo trim($laDatos['SEXPAL']??''); ?>';
	var gaFiltroAgenda = btoObj( <?php echo '\'' . base64_encode(json_encode($laFiltroAgenda)) . '\''; ?> );
</script>

<script type="text/javascript" src="vista-programacion-salas/js/datosCirugia.js"></script>
