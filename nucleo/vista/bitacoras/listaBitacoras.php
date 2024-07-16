<?php
	require_once (__DIR__ .'/../../controlador/class.Ingreso.php');
	require_once (__DIR__ .'/../../controlador/class.AplicacionFunciones.php');
	require_once (__DIR__ .'/../../controlador/class.Bitacoras.php');

	$lcTipoBitacora = (isset($_GET['q'])?$_GET['q']:'UNDEFINE');
	$loBitacoras = new NUCLEO\Bitacoras($lcTipoBitacora);
	
	$laPermisos = $loBitacoras->permisos($_SESSION[HCW_NAME]->oUsuario->getUsuario());
	
	$llIngreso = false;
	$llSecciones = false;
	$lcMensaje = '';
	$lnIngreso = 0;
	$lcSeccion = '';
	$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
	$lnFecha = $ltAhora->format("Ymd"); settype($lnFecha,"integer");

	$loIntervalo = new \DateInterval('P1D');
	$loIntervalo->invert = 1;

	$ltAyer = new \DateTime( $goDb->fechaHoraSistema() );
	$ltAyer->add($loIntervalo);

	$ldFechaInicio = (isset($_POST['inicio'])?$_POST['inicio']:$ltAyer->format("Y-m-d"));
	$ldFechaFin = (isset($_POST['fin'])?$_POST['fin']:$ltAhora->format("Y-m-d"));

	$laModos = ['tramite'=>'POR TRAMITE', 'paciente'=>'POR PACIENTE'];
	$lcModo = (isset($_POST['modo'])?$_POST['modo']:(isset($_GET['modo'])?$_GET['modo']:''));
	$lcEstado = (isset($_POST['estado'])?$_POST['estado']:(isset($_GET['estado'])?$_GET['estado']:''));


	// Cargando el ingreso para cuando aplique
	$lnIngreso = (isset($_POST['ingreso'])?$_POST['ingreso']:(isset($_GET['ingreso'])?$_GET['ingreso']:0));
	settype($lnIngreso,'Integer');
	if($lnIngreso>0){
		$loIngreso = new NUCLEO\Ingreso;
		$loIngreso->cargarIngreso($lnIngreso);

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
		$lcSeccion=(isset($_POST['seccion'])?$_POST['seccion']:(isset($_POST['seccion'])?$_POST['seccion']:''));
		settype($lnIngreso,'String'); $lcSeccion=trim($lcSeccion);
		if(!empty($lcSeccion)){
			$llSecciones = true;
		}
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
				<h5>Registro de seguimientos para <b><?php print($loBitacoras->getTipoBitacora()['TITULO']); ?></b></h5>
				<form role="form" id="registroBitacora" name="registroBitacora" method="POST" enctype="application/x-www-form-urlencoded" action="modulo-bitacoras&p=listaBitacoras&q=<?php print($loBitacoras->getTipoBitacora()['CODIGO']); ?>">
					<div id="filtro" class="row">
						<div class="col-md-2 pb-2">
							<label for="ingreso">Ingreso</label>
							<input type="number" class="form-control form-control-sm" name="ingreso" id="ingreso" min="0" max="99999999" placeholder="" value="<?php print(isset($loIngreso)?$loIngreso->nIngreso:''); ?>">
						</div>
						<div class="col-md-4 col-lg-2 pb-2">
							<label for="identificacion"><b>Identificaci&oacute;n</b></label>
							<div class="input-group input-group-sm">
								<div class="input-group-prepend">
									<div class="input-group-text font-weight-bold"><?php print(isset($loIngreso)?$loIngreso->cId:'<i class="fas fa-address-card"></i>'); ?></div>
								</div>
								<input type="text" class="form-control font-weight-bold" name="identificacion" id="identificacion" placeholder="" value="<?php print(isset($loIngreso)?$loIngreso->nId:''); ?>" disabled="disabled">
							</div>
						</div>
						<div class="col-md-6 col-lg-5 pb-2">
							<label for="ingreso">Paciente</label>
							<input type="text" class="form-control form-control-sm font-weight-bold" name="paciente" id="paciente" placeholder="" value="<?php print(isset($loIngreso)?$loIngreso->oPaciente->getNombreCompleto():''); ?>" disabled="disabled">
						</div>
						<div class="col-md-6 col-lg-3 pb-2">
							<div class="form-group form-check mb-2">
								<input type="checkbox" class="form-check-input" id="periodo" name="periodo" <?php print(isset($_POST['periodo'])?'checked="checked"':''); ?>>
								<label class="form-check-label" for="periodo">Buscar en este periodo</label>
							</div>							
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
						<div class="col-md-6 col-lg-4 pb-2">
							<label>Estado</label>
							<select class="custom-select custom-select-sm" id="estado" name="estado">
								<?php
									foreach($loBitacoras->getEstados() as $laEstado){
										$lcEstado = (empty($lcEstado)?$laEstado['CODIGO']:$lcEstado);
										printf('<option value="%s"%s>%s</option>',$laEstado['CODIGO'],$lcEstado==$laEstado['CODIGO']?' selected':'',$laEstado['DESCRIPCION']);
									}
								?>
							</select>
						</div>
						<div class="col-md-6 col-lg-4 pb-2">
							<label>Visualizaci&oacute;n</label>
							<select class="custom-select custom-select-sm" id="modo" name="modo">
								<?php
									foreach($loBitacoras->getModosVisualizar() as $laModo){
										$lcModo = (empty($lcModo)?$laModo['CODIGO']:$lcModo);
										printf('<option value="%s"%s>%s</option>',$laModo['CODIGO'],$lcModo==$laModo['CODIGO']?' selected':'',$laModo['DESCRIPCION']);
									}
								?>
							</select>
						</div>
						<div class="col-sm-12 col-md-6 col-lg-4 pb-2">
							<div class="row align-items-end h-100">
								<div class="col-12 col-sm-6 pb-2 pb-md-0">
									<button id="btnBuscar"  type="submit" class="btn btn-secondary btn-sm w-100" accesskey="B"><u>B</u>uscar</button>
								</div>
								<div class="col-12 col-sm-6 pb-2 pb-md-0">
									<a class="btn btn-secondary btn-sm w-100" accesskey="L" href="modulo-bitacoras&p=listaBitacoras&q=<?php print($loBitacoras->getTipoBitacora()['CODIGO']); ?>"><u>L</u>impiar</a>
								</div>
							</div>
						</div>
					</div>
				</form>
				<div class="row"><div class="col"><div id="ingresoInfo"><?php print($lcMensaje); ?></div></div></div>
			</div>
			<div id ="registroBitacora" class="card-body">
				<div id="toolbarlistaBitacoras">
					<div class="form-inline">
						<?php if(isset($_POST['periodo'])==true){ ?>
						<input id="inicio" name="inicio" type="hidden" value="<?php print($ldFechaInicio); ?>">
						<input id="fin" name="fin" type="hidden" value="<?php print($ldFechaFin); ?>">
						<?php } ?>
						<input id="estado" name="estado" type="hidden" value="<?php print($lcEstado); ?>">
						<?php if(in_array('N',$laPermisos)==true){ ?><button id="btnNuevoRegistro" class="btn btn-success" data-ingreso="<?php print(isset($loIngreso)?$loIngreso->nIngreso:0); ?>" <?php print((isset($loIngreso)?$loIngreso->nIngreso>0:false)==false?'disabled="disabled"':''); ?>>Nuevo registro</button><?php } ?>
					</div>					
				</div>
				<table
				  id="tableListaBitacoras"
				  data-show-export="true"
				  data-toolbar="#toolbarlistaBitacoras"
				  data-show-refresh="true"
				  data-click-to-select="true"
				  data-show-export="false"
				  data-show-columns="true"
				  data-show-columns-toggle-all="true"
				  data-minimum-count-columns="5"
				  data-pagination="false"
				  data-id-field="CONSECUTIVO"
				  data-query-params="queryParams"
				  data-row-style="rowStyle"
				  data-url="vista-bitacoras/ajax/listaBitacoras.ajax?p=<?php print($lcModo); ?>&q=<?php print($lcTipoBitacora); ?>&r=<?php print(isset($loIngreso)?$loIngreso->nIngreso:0); ?>" >
				</table>
			</div>
			<div class="card-footer text-muted">
				<ul>
					<?php if(in_array('N',$laPermisos)==true){ ?><li>Para crear una nueva bit&aacute;cora haga clic en el bot&oacute;n <small><kbd class="bg-success">Nuevo registro</kbd></small>. El bot&oacute;n para <b>Nuevo registro</b> se habilita al buscar un ingreso valido.</li><?php } ?>
					<li>Si desea ver un seguimiento a un registro existente haga doble clic sobre la respectiva fila.</li>
				</ul>
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
			<script type="text/javascript" src="vista-bitacoras/js/listaBitacoras.js?q=<?php print($lcTipoBitacora); ?>&r=<?php print($lcEstado); ?>&s=<?php print($lcModo); ?>"></script>
	
			<?php } ?>
		</div>
	</div>