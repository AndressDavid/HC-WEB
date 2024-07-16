<?php
	require_once (__DIR__ .'/../../publico/constantes.php');
	require_once (__DIR__ .'/../../controlador/class.TiposDocumento.php');
	require_once (__DIR__ .'/../../controlador/class.SalasAperturaSimple.php');
	require_once (__DIR__ .'/../../controlador/class.CentrosServicios.php');	
	
	$lcMensaje = '';
	$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
	$lnFecha = $ltAhora->format("Ymd"); settype($lnFecha,"integer");
	$ltFuturo = new \DateTime( $goDb->fechaHoraSistema() );
	$ltFuturo->add(new DateInterval('P15D'));

	$ldFechaInicio = (isset($_GET['inicio'])?$_GET['inicio']:$ltAhora->format("Y-m-d"));
	$ldFechaFin = (isset($_GET['fin'])?$_GET['fin']:$ltFuturo->format("Y-m-d"));

	$loSalasAperturaSimple = new NUCLEO\SalasAperturaSimple();
	
	$laSalasPermitenAperturaSimple = $loSalasAperturaSimple->salasPermitenAperturaSimple();
	$laSalasAutorizadas = $loSalasAperturaSimple->salasAutorizadasUsuario($_SESSION[HCW_NAME]->oUsuario->getUsuario());
	$laSalasEstados=['0'=>'ACTIVO', '2'=>'BORRADO', '5'=>'FACTURADO', '4'=>'LIQUIDADO'];
	$laCentrosServicios = (new NUCLEO\CentrosServicios())->aCentros;
									
?>
	<div class="container-fluid">
		<div class="card mt-3">
			<div class="card-header">
				<h5>Apertura de Salas (Simple)</h5>
				<div id="filterlistaAperturaSalas" >
					<div class="row">
						<div class="col-md-6 col-xl-3 pb-2">
							<label>Ingreso</label>
							<input type="number" class="form-control form-control-sm" name="nIngreso" id="nIngreso" min="0" max="99999999" placeholder="" value="<?php print(isset($_GET['nIngreso'])?intval($_GET['nIngreso']):''); ?>">
						</div>
						<div class="col-md-6 col-xl-3 pb-2">
							<label>Documento</label>
							<div id="documento" class="input-group">
								<select class="custom-select custom-select-sm col-6 font-weight-bold" id="cDocumento" name="cDocumento" disabled>
									<option></option>
									<?php
										$laTiposDocumento = (new NUCLEO\TiposDocumento())->aTipos;
										
										foreach($laTiposDocumento as $lcTipoDocumento => $laTipoDocumento){
											$lcSelected = ((isset($_GET['cDocumento'])?$_GET['cDocumento']:'')==$lcTipoDocumento?'selected="selected"':'');											
											printf('<option value="%s" %s>%s - %s</option>',$lcTipoDocumento,$lcSelected,$laTipoDocumento['ABRV'],$laTipoDocumento['NOMBRE']);
										}
									?>
								</select>
								<input type="text" id="nDocumento" name="nDocumento" class="form-control form-control-sm col-6 font-weight-bold" value="<?php print(isset($_GET['nDocumento'])?intval($_GET['nDocumento']):''); ?>" disabled>
							</div>
						</div>
						<div class="col-md-6 pb-2">
							<label for="cPaciente">Paciente</label>
							<input type="text" class="form-control form-control-sm font-weight-bold" name="cPaciente" id="cPaciente" placeholder="" value="" disabled="disabled">
						</div>						
						<div class="col-md-6 col-xl-3 pb-2">
							<label>Tipo de Sala</label>
							<select class="custom-select custom-select-sm" id="cSala" name="cSala">
								<?php
									foreach($laSalasAutorizadas as $laSalaAutorizada){
										if(in_array($laSalaAutorizada['SALA'], $laSalasPermitenAperturaSimple)==true){
											$lcSelected = ((isset($_GET['cSala'])?$_GET['cSala']:'')==$laSalaAutorizada['SALA']?'selected="selected"':'');											
											printf('<option value="%s" %s>%s (%s)</option>',$laSalaAutorizada['SALA'],$lcSelected,$laSalaAutorizada['NOMBRE'],$laSalaAutorizada['SALA']);
										}
									}
								?>								
							</select>
						</div>							
						<div class="col-md-6 col-lg-4 col-xl-3 pb-2">
							<label>Periodo</label>
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
						<div class="col-md-6 col-lg-4 col-xl-3 pb-2">
							<label>Centro de Servicio</label>
							<select class="custom-select custom-select-sm" id="cCentroServicio" name="cCentroServicio">
								<option value="">TODOS</option>
								<?php 
									foreach($laCentrosServicios as $laCentroServicio){
										$lcSelected = ((isset($_GET['cCentroServicio'])?$_GET['cCentroServicio']:'') == strval($laCentroServicio['CODIGO'])?'selected="selected"':'');
										$lcSelected = $lcSelected.($laCentroServicio['ID']>0?'':' disabled');
										printf('<option value="%s" %s>%s</option>', $laCentroServicio['CODIGO'], $lcSelected, $laCentroServicio['NOMBRE']);
									}									
								?>							
							</select>
						</div>
						<div class="col-md-12 col-lg-4 col-xl-3 pb-2">
							<label>Estado</label>
							<select class="custom-select custom-select-sm" id="cEstado" name="cEstado">
								<?php 
									foreach($laSalasEstados as $lcSalaEstado => $lcSalaEstadoNombre){
										$lcSelected = ((isset($_GET['cEstado'])?$_GET['cEstado']:'') == strval($lcSalaEstado)?'selected="selected"':'');											
										printf('<option value="%s" %s>%s</option>', $lcSalaEstado, $lcSelected, $lcSalaEstadoNombre);
									}									
								?>							
							</select>
						</div>	
						<div class="col-12">
							<div class="row justify-content-end">
								<div class="col-md-6 col-lg-4 col-xl-3 pb-2">
									<div class="row align-items-end pt-3">
										<div class="col-12 col-sm-6 pb-2 pb-md-0">
											<button id="btnBuscar"  type="button" class="btn btn-secondary btn-sm w-100" accesskey="B"><u>B</u>uscar</button>
										</div>
										<div class="col-12 col-sm-6 pb-2 pb-md-0">
											<a class="btn btn-secondary btn-sm w-100" accesskey="L" href="modulo-apertura-salas&p=listaAperturaSalas"><u>L</u>impiar</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row"><div class="col"><div id="ingresoInfo"><?php print($lcMensaje); ?></div></div></div>
			</div>
			<div id ="registrosAperturaSala" class="card-body">
				<div id="toolbarlistaAperturaSalas">
					<div class="form-inline">
						<button id="btnAgregar"  type="button" class="btn btn-success" data-toggle="modal" data-target="#agregarAperturaSala" accesskey="R">Realizar apertura de Sala</button>	
					</div>
				</div>
				<table
				  id="tableListaAperturaSalas"
				  data-show-export="true"
				  data-toolbar="#toolbarlistaAperturaSalas"
				  data-show-refresh="true"
				  data-click-to-select="true"
				  data-show-export="false"
				  data-show-columns="true"
				  data-show-columns-toggle-all="true"
				  data-minimum-count-columns="5"
				  data-pagination="false"
				  data-query-params="queryParams"
				  data-row-style="rowStyle"
				  data-url="vista-apertura-salas/ajax/listaAperturaSalas.ajax" >
				</table>
			</div>
			

			
			
			<div class="card-footer text-muted">
				<p>Para ver una apertura haga doble clic en la fila que corresponda. Si por el contrario desea crear una nueva apertura, haga clic en el bot&oacute;n &quot;Realizar apertura de sala&quot;.</p>
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
			<script type="text/javascript" src="vista-apertura-salas/js/listaAperturaSalas.js?nIngreso=<?php print(isset($_GET['nIngreso'])?$_GET['nIngreso']:0); ?>"></script>

		</div>
	</div>