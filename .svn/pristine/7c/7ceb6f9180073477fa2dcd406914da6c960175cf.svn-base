<?php
	require_once (__DIR__ .'/../../publico/constantes.php');
	require_once (__DIR__ .'/../../controlador/class.AplicacionFunciones.php');
	require_once (__DIR__ .'/../../controlador/class.TiposDocumento.php');
	require_once (__DIR__ .'/../../controlador/class.SalasAperturaSimple.php');
	require_once (__DIR__ .'/../../controlador/class.SalaApertura.php');
	require_once (__DIR__ .'/../../controlador/class.CentrosServicios.php');

	$loAplicacionFunciones = new NUCLEO\AplicacionFunciones();
	$loSalasAperturaSimple = new NUCLEO\SalasAperturaSimple();
	$loSalaApertura = new NUCLEO\SalaApertura();

	$laSalasPermitenAperturaSimple = $loSalasAperturaSimple->salasPermitenAperturaSimple();
	$laSalasAutorizadas = $loSalasAperturaSimple->salasAutorizadasUsuario($_SESSION[HCW_NAME]->oUsuario->getUsuario());

	$lcGet = '';
	foreach($_GET as $lcGetKey => $lcGetValue){
		if($lcGetKey<>'p'){
			$lcGet .= (!empty($lcGet)?'&':'').$lcGetKey."=".$lcGetValue;
		}
	}


	if(isset($_POST['INGRESO']) && isset($_POST['CONSECUTIVO'])){
		$loSalaApertura->cargarCirugia(intval($_POST['INGRESO']), INTVAL($_POST['CONSECUTIVO']));
	}

?>
	<div class="container-fluid">
		<div class="card mt-3">
			<div class="card-header">
				<h5>Apertura de Salas (Simple)</h5>
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="modulo-apertura-salas&p=listaAperturaSalas&<?php print($lcGet); ?>"><i class="fas fa-video"></i> Volver</a></li>
						<li class="breadcrumb-item active" aria-current="page" id="recordArea"><?php print($loSalaApertura->getIngreso()>0?'Ingreso: '.$loSalaApertura->getIngreso().", Cirug&iacute;a: ".$loSalaApertura->getConsecutivo():'Apertura sin guardar'); ?></li>
					</ol>
				</nav>
			</div>
			<div id ="registroAperturaSalas" class="card-body">
				<form id="aperturaForm">
					<div class="row">
						<div class="col-md-6 col-xl-2 pb-2">
							<label>Ingreso</label>
							<input type="number" class="form-control form-control-sm font-weight-bold" name="nIngreso" id="nIngreso" min="0" max="99999999" placeholder="" value="<?php print($loSalaApertura->getIngreso()>0?$loSalaApertura->getIngreso():''); ?>" <?php print($loSalaApertura->getIngreso()>0?'disabled':''); ?> autofocus>
						</div>
						<div class="col-md-6 col-xl-2 pb-2">
							<label for="cIngresoFecha">Ingreso Fecha</label>
							<input type="text" class="form-control form-control-sm font-weight-bold" name="cIngresoFecha" id="cIngresoFecha" placeholder="" value="<?php print($loSalaApertura->getIngreso()>0?$loAplicacionFunciones->formatFechaHora('fecha',$loSalaApertura->getIngreso(true)->nIngresoFecha):''); ?>" disabled="disabled">
						</div>
						<div class="col-md-6 col-xl-3 pb-2">
							<label>Documento</label>
							<div id="documento" class="input-group">
								<select class="custom-select custom-select-sm col-6 font-weight-bold" id="cDocumento" name="cDocumento" disabled>
									<option></option>
									<?php
										$laTiposDocumento = (new NUCLEO\TiposDocumento())->aTipos;

										foreach($laTiposDocumento as $lcTipoDocumento => $laTipoDocumento){
											$lcSelected = ($loSalaApertura->getIngreso()>0?($lcTipoDocumento==$loSalaApertura->getIngreso(true)->cId?'selected="selected"':''):'');
											printf('<option value="%s" %s>%s - %s</option>',$lcTipoDocumento,$lcSelected,$laTipoDocumento['ABRV'],$laTipoDocumento['NOMBRE']);
										}
									?>
								</select>
								<input type="text" id="nDocumento" name="nDocumento" class="form-control form-control-sm col-6 font-weight-bold" value="<?php print($loSalaApertura->getIngreso()>0?$loSalaApertura->getIngreso(true)->nId:''); ?>" disabled>
							</div>
						</div>
						<div class="col-md-6 col-xl-5 pb-2">
							<label for="cPaciente">Paciente</label>
							<input type="text" class="form-control form-control-sm font-weight-bold" name="cPaciente" id="cPaciente" placeholder="" value="<?php print($loSalaApertura->getIngreso()>0?$loSalaApertura->getIngreso(true)->oPaciente->getNombreCompleto():''); ?>" disabled="disabled">
						</div>
						<div class="col-md-6 col-xl-2 pb-2">
							<label for="cPacienteNacimiento">Fecha nacimiento</label>
							<input type="text" class="form-control form-control-sm font-weight-bold" name="cPacienteNacimiento" id="cPacienteNacimiento" placeholder="" value="<?php print($loSalaApertura->getIngreso()>0?$loAplicacionFunciones->formatFechaHora('fecha',$loSalaApertura->getIngreso(true)->oPaciente->nNacio):''); ?>" disabled="disabled">
						</div>
						<div class="col-md-6 col-xl-2 pb-2">
							<label for="cEdad">Edad</label>
							<input type="text" class="form-control form-control-sm font-weight-bold" name="cEdad" id="cEdad" placeholder="" value="<?php print($loSalaApertura->getIngreso()>0?$loSalaApertura->getIngreso(true)->aEdad['y']." a&ntilde;os, ".$loSalaApertura->getIngreso(true)->aEdad['m']." meses, ".$loSalaApertura->getIngreso(true)->aEdad['d']." d&iacute;as":''); ?>" disabled="disabled">
						</div>
						<div class="col-md-6 col-xl-2 pb-2">
							<label for="cGenero">Genero</label>
							<input type="text" class="form-control form-control-sm font-weight-bold" name="cGenero" id="cGenero" placeholder="" value="<?php print($loSalaApertura->getIngreso()>0?$loSalaApertura->getIngreso(true)->oPaciente->getGenero():''); ?>" disabled="disabled">
						</div>					
						<div class="col-md-6 col-xl-3 pb-2">
							<label for="cHabitacion">Habitaci&oacute;n</label>
							<input type="text" class="form-control form-control-sm font-weight-bold" name="cHabitacion" id="cHabitacion" placeholder="" value="<?php print($loSalaApertura->getIngreso()>0?$loSalaApertura->getIngreso(true)->oHabitacion->cSeccion.'-'.$loSalaApertura->getIngreso(true)->oHabitacion->cHabitacion:''); ?>" disabled="disabled">
						</div>					

						<?php if($loSalaApertura->getIngreso()>0){ ?>
						<div class="col-md-6 col-xl-3 pb-2">
							<label for="cSala">Sala para procedimiento</label>
							<input type="text" class="form-control form-control-sm font-weight-bold" name="cSala" id="cSala" placeholder="" value="<?php print($loSalaApertura->getIngreso()>0?$loSalaApertura->getSala():''); ?>" disabled="disabled">
						</div>
						<?php } else { ?>
						<div class="col-md-6 col-xl-3 pb-2">
							<label for="nSalasAbiertas">Abiertas previamente</label>
							<input type="text" class="form-control form-control-sm font-weight-bold" name="nSalasAbiertas" id="nSalasAbiertas" placeholder="" readonly="readonly">
						</div>					

						<div class="col-md-6 col-xl-4 pb-2">
							<label>Tipo de Sala</label>
							<select class="custom-select custom-select-sm" id="cSala" name="cSala" disabled>
								<option value=""></option>
								<?php
									foreach($laSalasAutorizadas as $laSalaAutorizada){
										if(in_array($laSalaAutorizada['SALA'], $laSalasPermitenAperturaSimple)==true){
											$lcSelected = ((isset($_GET['cSala'])?$_GET['cSala']:'')==$laSalaAutorizada['SALA']?'selected="selected"':'');
											printf('<option value="%s" %s>%s (%s)</option>',$laSalaAutorizada['SALA'],$lcSelected, $laSalaAutorizada['NOMBRE'], $laSalaAutorizada['SALA']);
										}
									}
								?>
							</select>
						</div>
						<div class="col-md-6 col-xl-4 pb-2">
							<label>Sala Numero</label>
							<select class="custom-select custom-select-sm" id="cSalaNumero" name="cSalaNumero" disabled>
							</select>
						</div>
						<div class="col-md-6 col-xl-4 pb-2">
							<label>Centro Servicios</label>
							<div class="input-group input-group-sm mb-3">
								<div class="input-group-prepend">
									<span class="input-group-text font-weight-bolder" id="cCentroServicioNombre" name="cCentroServicioNombre"></span>
								</div>							
								<input type="text" class="form-control" id="cCentroServicio" name="cCentroServicio" readonly>
								<div class="input-group-append">
									<span class="input-group-text fas fa-hospital"></span>
								</div>								
							</div>
						</div>
						<div class="col-12 pb-2" id="abiertasArea">
							<label>Salas abiertas para el tipo de sala seleccionando</label>
							<div id="salasAbiertas">&nbsp;</div>
						</div>						
						<div class="col-12" id="commandArea">
							<div class="row justify-content-end mt-3">
								<div class="col-md-12 col-lg-3 pb-2">
									<button id="btnGuardar"  type="button" class="btn btn-secondary btn-sm w-100" accesskey="G" disabled><u>G</u>uardar</button>
								</div>
							</div>
						</div>					
						<?php } ?>
					</div>
				</form>
			</div>
		</div>
	</div>

	<link rel="stylesheet" type="text/css" href="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />
	<script type="text/javascript" src="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>

	<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
	<script type="text/javascript" src="vista-comun/js/comun.js"></script>

	<script type="text/javascript" src="vista-apertura-salas/js/registroAperturaSalas.js?nIngreso=<?php print(isset($_GET['nIngreso'])?$_GET['nIngreso']:0); ?>&nPrevio=<?php print($loSalaApertura->getIngreso()>0?$loSalaApertura->getIngreso():0); ?>"></script>