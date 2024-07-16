<?php
	require_once __DIR__ .'/../../controlador/class.AgendaSalasCirugia.php';
	require_once __DIR__ .'/../../controlador/class.AplicacionFunciones.php';
	$loAgenda = new NUCLEO\AgendaSalasCirugia();
	$laPermisos = $loAgenda->permisoRegistrar($_SESSION[HCW_NAME]->oUsuario->getUsuario());
	$lcPermiso = '';
	
	if(isset($laPermisos)){
		if(is_array($laPermisos) == true){
			foreach($laPermisos as $laDato){
				$lcPermiso= $laDato;
			}
		}	
	}
	
?>

	<div class="container-fluid">
		<div class="card mt-3">
	<?php if($lcPermiso!='N'){ ?>
			<div class="card-header">
				<h5>Módulo programación salas</h5>
			</div>
			<div class="card-body">
				<div class="alert alert-warning" role="alert">
				El modulo al que hace referencia no existe. Regrese al inicio e ingrese nuevamente. 
				Si la Falla persiste reportela al &aacute;rea de Tecnolog&iacute;a e Informaci&oacute;n</div>			
			</div>			
	<?php } else { ?>
				<div class="card-header">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-8 col-lg-11">
							<h5>Agenda de Salas Cirug&iacute;a</h5>
						</div>
					</div>
					<form id="FrmFiltrosSalas">
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-3 col-lg-4 pb-2">
								<label for="selSalasC">Sala</label>
								<select id="selSalasC" class="form-control form-control-sm"></select>
							</div>
							
							<div class="col-lg-2 col-md-3 col-sm-12 col-12 pb-2">
								<label for="txtFechaDesdeSala" class="control-label"><b>Fecha desde</b></label>
								<div class="input-group date">
									<div class="input-group-prepend">
										<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
									</div>
									<input id="txtFechaDesdeSala" type="text" class="form-control form-control-sm" required="required" value="<?php echo $laFiltro['fecini']; ?>">
								</div>
							</div>
							
							<div class="col-lg-2 col-md-3 col-sm-12 col-12 pb-2">
								<label for="txtFechaHastaSala" class="control-label"><b>Fecha hasta</b></label>
								<div class="input-group date">
									<div class="input-group-prepend">
										<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
									</div>
									<input id="txtFechaHastaSala" type="text" class="form-control form-control-sm" required="required" value="<?php echo $laFiltro['fecfin']; ?>">
								</div>
							</div>
							
							<div class="col-lg-2 col-md-2 col-sm-6 col-6 pb-2">
								<label for="btnBuscarSala">&nbsp;</label>
								<button id="btnBuscarSala" type="button" class="form-control form-control-sm btn btn-secondary btn-sm" accesskey="B"><u>B</u>uscar</button>
							</div>
							<div class="col-lg-2 col-md-2 col-sm-6 col-6 pb-2">
								<label for="btnLimpiarFiltros">&nbsp;</label>
								<button id="btnLimpiarFiltros" type="button" class="form-control form-control-sm btn btn-secondary btn-sm" accesskey="L"><u>L</u>impiar</button>
							</div>
						</div>
					</form>
			</div>

			<div class="card-body">
				<small><table id="tblSalasCirugia"></table></small>
			</div>
		</div>
	</div>

	<div class="modal fade" id="divCancelaAgendaSc" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header badge-info">
					<h5 class="modal-title col-11 text-center" id="exampleModalLabel">AGENDA SALAS</h5>
				</div>

				<div id="divCapturaAgendaBody" class="modal-body">
					<form id="FormCancelaAgendaSc" name="FormCancelaAgendaSc" class="needs-validation" novalidate>
						<div class="row">
							<div class="col-md-12">
								<label id="lblTipoCancelacionSc" for="selTipoCancelacionSc" class="required">Tipo</label>
								<div class="form-group">
									<select class="custom-select d-block w-100" id="selTipoCancelacionSc" name="TipoCancelacionSc">
									<option value=""></option>
									</select>
								</div>
								
								<label id="lblMotivoCancelacionSc" for="selMotivoCancelacionSc" class="required">Motivo</label>
								<div class="form-group">
									<select class="custom-select d-block w-100" id="selMotivoCancelacionSc" name="MotivoCancelacionSc" disabled>
									<option value=""></option>
									</select>
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarAgendaSc"><u>C</u>ancelar</button>
					<button type="button" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Guardar información" aria-pressed="true" id="btnGuardaAgendaSc"><u>G</u>uardar</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="divModificarAnestesiologo" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header badge-info">
					<h5 class="modal-title col-11 text-center" id="exampleModalLabel">MODIFICAR ANESTESIOLOGO</h5>
				</div>
				<div id="divModificarAnestesiologoBody" class="modal-body">
					<form id="FormModificarAnestesiologo" name="FormModificarAnestesiologo" class="needs-validation" novalidate>
						<div class="row">
							<div class="col-md-12">
								<label id="lblAnestesiologoActual" for="txtAnestesiologoActual" class="required">Anestesiologo actual</label>
								<div class="form-group">
									<input id="txtAnestesiologoActual" type="text" class="form-control form-control-sm" name="AnestesiologoActual" style="text-transform:uppercase;" disabled>
								</div>								
								<label id="lblAnestesiologoNuevo" for="selAnestesiologoNuevo" class="required">Anestesiologo nuevo</label>
								<div class="form-group">
									<select class="custom-select d-block w-100" id="selAnestesiologoNuevo" name="AnestesiologoNuevo">
									<option value=""></option>
									</select>
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarAnestesiologo"><u>C</u>ancelar</button>
					<button type="button" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Modificar información" aria-pressed="true" id="btnGuardaAnestesiologo"><u>M</u>odificar</button>
				</div>
			</div>
		</div>
	</div>


	<div class="modal fade" id="divModificarCirujano" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header badge-info">
					<h5 class="modal-title col-11 text-center" id="exampleModalLabel">MODIFICAR CIRUJANO</h5>
				</div>
				<div id="divModificarCirujanoBody" class="modal-body">
					<form id="FormModificarCirujano" name="FormModificarCirujano" class="needs-validation" novalidate>
						<div class="row">
							<div class="col-md-12">
							<label id="lblCirujanoActual" for="txtCirujanogoActual" class="needs-validation">Cirujano actual</label>
								<div class="form-group">
									<input id="txtCirujanoActual" type="text" class="form-control form-control-sm" name="CirujanoActual" style="text-transform:uppercase;" disabled>
									<input id="codCirujanoActual" type="hidden" name="codCirujanoActual"  >
								</div>								
								<label id="lblEspecialidad" for="txtEspecialidad" class="required">Especialidad Nueva</label>
								<div class="form-group">
									<select class="custom-select d-block w-100" id="selEspecialidadNuevo" name="Especialidad" >
									<option value=""></option>
									</select>
								</div>					
								<label id="lblCirujanologoNuevo" for="selCirujanoNuevo" class="required">Cirujano nuevo</label>
								<div class="form-group">
									<select class="custom-select d-block w-100" id="selCirujanoNuevo" name="CirujanoNuevo"  >
									<option value=""></option>
									</select>
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarCirujano"><u>C</u>ancelar</button>
					<button type="button" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Modificar información" aria-pressed="true" id="btnGuardaCirujano"><u>M</u>odificar</button>
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
		var gcFiltroSala = '<?php echo $laFiltro['sala']; ?>';
	</script>
	<script type="text/javascript" src="vista-programacion-salas/js/programacionSalas.js"></script>
	<?php include (__DIR__ . '/../comun/js/usuario.js.php'); ?>

<?php } ?>
