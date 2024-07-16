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

	// Parámetros de búsqueda de usuarios
	$laBuscar = array('registro' => '', 'usuario' => '', 'documentoTipo' => '', 'documentoNumero' => 0, 'nombres' => '', 'apellidos' => '', 'usuarioEstado' => 1, 'especialidad' => '', 'tipo' => '', 'accesoIni' => 0, 'accesoFin' => 0, 'vigenciaIni' => 0, 'vigenciaFin' => 0);
	if(isset($_POST)==true){
		$laBuscar['registro'] = (isset($_POST['registro'])?$_POST['registro']:'');
		$laBuscar['usuario'] = (isset($_POST['usuario'])?trim(strval(strtoupper($_POST['usuario']))):'');
		$laBuscar['documentoTipo'] = (isset($_POST['documentoTipo'])?trim(strval(strtoupper($_POST['documentoTipo']))):'');
		$laBuscar['documentoNumero'] = (isset($_POST['documentoNumero'])?(intval($_POST['documentoNumero'])>0?intval($_POST['documentoNumero']):''):'');
		$laBuscar['nombres'] = (isset($_POST['nombres'])?trim(strval(strtoupper($_POST['nombres']))):'');
		$laBuscar['apellidos'] = (isset($_POST['apellidos'])?trim(strval(strtoupper($_POST['apellidos']))):'');
		$laBuscar['estado'] = (isset($_POST['usuarioEstado'])?intval($_POST['usuarioEstado']):1);
	}
	$lcBuscar=base64_encode(json_encode($laBuscar));



?>
	<!-- especificos ara usuarios -->
	<link rel="stylesheet" href="vista-usuarios/css/style.css" />

	<!-- Bootstrap Table -->
	<link rel="stylesheet" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />
	<script type="text/javascript" src="publico-complementos/jquery-tableexport/tableExport.min.js"></script>
	<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
	<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table-locale-all.min.js"></script>
	<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/extensions/export/bootstrap-table-export.min.js"></script>
	<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
	<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>

	<div class="container-fluid">
		<div class="card mt-3">
			<div class="card-header">
				<h5>Usuarios</h5><p>Est&eacute; opci&oacute;n le permite realizar la parametrizaci&oacute;n de los usuarios del sistema</p>

				<form role="form" id="buscarUsuarioForm" name="buscarUsuarioForm" method="POST" enctype="application/x-www-form-urlencoded" action="modulo-usuarios&p=listaUsuarios" autocomplete="off">
					<div class="row">
						<div class="col-md-3 pb-2">
							<label for="registro"><b>Registro</b></label>
							<input type="number" class="form-control form-control-sm" id="registro" name="registro" placeholder="Numero de registro" value="<?php print($laBuscar['registro']); ?>">
						</div>
						<div class="col-md-3 pb-2">
							<label for="usuario"><b>Usuario</b></label>
							<input type="text" class="form-control form-control-sm" id="usuario" name="usuario" placeholder="Usuario" value="<?php print($laBuscar['usuario']); ?>">
						</div>
						<div class="col-md-6 pb-2">
							<label for="documentoNumero" class="required"><b>Documento</b></label>
							<div id="documento" class="input-group mb-3">
								<select class="custom-select custom-select-sm col-6" id="documentoTipo" name="documentoTipo" <?php print(isset($laBuscar['documentoTipo'])?(!empty($laBuscar['documentoTipo'])?'disabled':''):''); ?>>
									<?php
										$laSelectTiposDocumentoAux = (new NUCLEO\TiposDocumento())->aTipos;
										
										foreach($laSelectTiposDocumentoAux as $lcSelectedTipoDocumento => $laSelectTipoDocumento){
											$lcSelected = (isset($laBuscar['documentoTipo'])?
															trim($laBuscar['documentoTipo'])==trim($lcSelectedTipoDocumento)?
																'selected':'':'');
											
											printf('<option value="%s" %s>%s - %s</option>',$lcSelectedTipoDocumento,$lcSelected,$laSelectTipoDocumento['ABRV'],$laSelectTipoDocumento['NOMBRE']);
										}
									?>
								</select>
								<input type="text" id="documentoNumero" name="documentoNumero" class="form-control form-control-sm col-6" <?php print(isset($laBuscar['documentoNumero'])?(!empty($laBuscar['documentoNumero'])?'disabled':''):''); ?> value="<?php print(isset($laBuscar['documentoNumero'])?$laBuscar['documentoNumero']:''); ?>">
							</div>							
						</div>
					</div>
					<div class="row">
						<div class="col-md-3 pb-2">
							<label for="nombres"><b>Nombres</b></label>
							<input type="text" class="form-control form-control-sm" id="nombres" name="nombres" placeholder="Nombres" value="<?php print($laBuscar['nombres']); ?>">
						</div>
						<div class="col-md-3 pb-2">
							<label for="apellidos"><b>Apellidos</b></label>
							<input type="text" class="form-control form-control-sm" id="apellidos" name="apellidos" placeholder="Apellidos" value="<?php print($laBuscar['apellidos']); ?>">
						</div>
						<div class="col-md-3 pb-2">
							<label for="usuarioEstado" class="required"><b>Estado</b></label>
							<select class="form-control form-control-sm" id="usuarioEstado" name="usuarioEstado">
								<option value="">TODOS</option>
								<?php
									foreach($loUsuarios->oEstados->aEstados as $lcEstado => $laEstado){
										$lcSelected=(intval($laBuscar['estado'])==intval($lcEstado)?'selected':'');
										printf('<option value="%s" %s>%s</option>',trim($lcEstado),$lcSelected,$laEstado['DESESU']);
									}
								?>
							</select>							
						</div>
						
						<div class="col-md-3">
							<div class="row align-items-end h-100">
								<div class="col-md-6 pb-2">
									<button id="btnBuscar" type="submit" class="form-control form-control-sm btn btn-secondary btn-sm w-100" accesskey="B"><u>B</u>uscar</button>
								</div>
								<div class="col-md-6 pb-2">
									<a class="btn btn-secondary btn-sm w-100" accesskey="L" href="modulo-usuarios&p=listaUsuarios"><u>L</u>impiar</a>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="card-body table-responsive">
				<div id="toolbarlistaUsuarios">
					<div class="form-inline">
						<a href="modulo-usuarios&p=registroUsuario" id="btnNuevoUsuario" class="btn btn-success">Nuevo Usuario</a>
					</div>					
				</div>			
				<table
				  id="tableUsuarios"
				  data-show-export="true"
				  data-toolbar="#toolbarlistaUsuarios"
				  data-show-refresh="true"
				  data-click-to-select="true"
				  data-show-columns="true"
				  data-show-columns-toggle-all="true"
				  data-minimum-count-columns="5"
				  data-row-style="rowStyle"
				  data-pagination="true"
				  data-id-field="USUARI"
				  data-page-list="[10, 50, 100, 1000, 10000]"
				  data-page-size="100";
				  data-side-pagination="server"
				  data-url="vista-usuarios/ajax/listaUsuarios.ajax?p=usuarios&q=<?php print($lcBuscar); ?>"
				  class="table table-sm table-striped table-hover row-select">
				</table>
			</div>
			
			<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
			<script type="text/javascript" src="vista-comun/js/comun.js"></script>				
			<script type="text/javascript" src="vista-comun/js/tiposDocumentos.js"></script>
			<script type="text/javascript" src="vista-usuarios/js/listaUsuarios.js"></script>
			<div class="card-footer text-muted">
				<p>info</p>
			</div>
		</div>
	</div>