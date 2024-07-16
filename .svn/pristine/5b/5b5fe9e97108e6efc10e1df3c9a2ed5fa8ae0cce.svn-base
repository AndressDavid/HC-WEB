<?php

	require_once __DIR__ .'/../../publico/constantes.php';
	
	$lnContinuar = 0;
	if (isset($_SESSION[HCW_NAME])){
		$laTiposUsuariosAutorizados = [1, 7, 9, 91];
		$lnTipoUsuario = $_SESSION[HCW_NAME]->oUsuario->getTipoUsuario();
		if(in_array($lnTipoUsuario, $laTiposUsuariosAutorizados)){
			$lnContinuar = 1;
		}else{
			echo 'El tipo de usuario no esta autorizado para acceder';
		}
	}
 	if($lnContinuar == 1 ){

?>	
	<div class="card mt-3" id="divPacientesTriage">
		<div class="col-6">
			<?php include __DIR__ . '../digiturno.php';?>
		</div>
		<div class="card-header" id="">
			<div class="row">
				<div class="col-2 col-sm-1" title="Imagen paciente"><img class="rounded img-responsive"
						width="50" src="nucleo/publico/imagenes/librohc/32x32/jpg/UserSetup.jpg">
				</div>				
				<div class="col-10 col-sm-11">
					<h6 class="font-weight-bold">Pacientes de Triage Urgencias</h6>
					<h6 class="">Control ingreso de pacientes en Urgencias para Triage</h6>
				</div>
			</div>
		</div>
		<div class="card-body " id="">
				<div class="card-header border">
					<ul class="nav nav-tabs card-header-tabs">
						<li class="nav-item "><a class="nav-link active" data-toggle="tab" href="#triagesActivos">Triages Activos</a></li>
						<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#especificos">Especificos</a></li>
					</ul>
				</div>
				<div class="card-body tab-content border">
					<div class="tab-pane active border " id="triagesActivos" >
						<div class="row">
							<div class="col-7">
								<h6>Resumen Triage</h6>
							</div>
							<div class="col-2">
								<h6>Pacientes por llamado MPP</h6>
							</div>
							<div class="col-2">
								<h6>Actualizar por los siguientes criterios</h6>
							</div>
						</div>
						<div class="row">
							<div class="col-12 col-sm-7">
								<table class="table table-sm  table-responsive-sm  border table-striped"> <!-- table-bordered  table-hover table-responsive table-sm  -->
									<thead class="thead-dark "> <!-- light -->
										<tr>
											<th scope="col">Clasificación</th>
											<th scope="col">Pacientes</th>
											<th scope="col">Primer paciente</th>
											<th scope="col">Paciente reciente</th>
											<th scope="col"></th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<th scope="row">TRIAGE 1</th>
											<td>.</td>
											<td>.</td>
											<td>.</td>
											<td>.</td>
										</tr>
										<tr>
											<th scope="row">TRIAGE 2</th>
											<td>.</td>
											<td>.</td>
											<td>.</td>
											<td>.</td>
										</tr>
										<tr>
											<th scope="row">TRIAGE 3</th>
											<td>.</td>
											<td>.</td>
											<td>.</td>
											<td>.</td>
										</tr>
										<tr>
											<th scope="row">TRIAGE 4</th>
											<td>.</td>
											<td>.</td>
											<td>.</td>
											<td>.</td>
										</tr>
										<tr>
											<th scope="row">TRIAGE 5</th>
											<td>.</td>
											<td>.</td>
											<td>.</td>
											<td>.</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="col-12 col-sm-2 ">
								<table class="table table-sm table-responsive-sm border " style="height:200px">
									<thead class="thead-dark">
										<tr>
											<th scope="col">Paciente</th>
											<th scope="col">Fecha</th>
											<th scope="col"></th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td></td>
											<td></td>
											<td></td>
										</tr>
										<tr>
											<td></td>
											<td></td>
											<td></td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="col-12 col-sm-2" id="divFecha">
								<div class="row">
									<label for="triagesDesde" class="col-12 col-sm-5" style="padding: 0px;">Triages desde</label> <!-- col-lg-3 col-md-3 col-sm-4 -- col-lg-4 col-md-6 col-sm-8-->
									<div class="input-group input-group-sm date col-12 col-sm-7" style="padding: 0px;"> 
										<div class="input-group-prepend" style="height: 31px;">
											<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
										</div>
										<input type="text" class="form-control" name="triagesDesde" id="triagesDesde" required="required" value="<?php print(date("Y-m-d"));?>">
									</div>
								</div>
								
								<div class="form-group form-check">
									<input type="checkbox" class="form-check-input" id="ultimoTriage">
									<label class="form-check-label" for="ultimoTriage">Mostrar sólo el último triage</label>
								</div>
							</div>
							<div class="col-1" >
								<button id="btnActualizar" type="button" class="btn btn-secondary" style="height:200px" accesskey="u">Act<u><b>u</b></u>alizar</button>
							</div>
						</div>
					</div> <!-- cierra la primer pestañas activas -->
					<div class="tab-pane" id="especificos" ><!-- Inicia la segunda pestaña-->
						<div class="col-6">
							<h6>Buscar registros por los siguientes criterios</h6>
						</div>
						<div class="row">
							<div class="col-12 col-md-3 pb-1">
								<div class="row">
									<label for="txtIngreso" class="col-3 text-right"><b>Ingreso</b></label>
									<div class="input-group col-6">
										<input type="number" class="form-control form-control-sm" name="txtIngreso" id="txtIngreso" min="0" max="99999999" placeholder="" value="" required="">
									</div>
								</div>
							</div>
							<div class="col-12 col-md-8 pb-1">
								<div class="row">
									<label for="cmbTipoDocumento" class="col-2 text-right"><b>Identidad</b></label>
									<div class="input-group col-10">
										<select id="cmbTipoDocumento" name="cmbTipoDocumento"  class="form-control  form-control-sm custom-select custom-select-sm col-2"
												title="Tipo de documento de identidad">
												<option selected>..</option>
												<option>...</option>
										</select>
										<input type="text" class="form-control form-control-sm col-4" name="txtNumeroDoc" id="txtNumeroDoc" placeholder="Número de documento" value="" required="">
										<input type="text" class="form-control form-control-sm col-6" name="txtNombres" id="txtNombres" placeholder="Nombres completos" value="" required="">
									</div>
								</div>
							</div>
							<div class="col-12 col-md-1 pb-1">
								<div class="col-1" >
									<button id="btnLimpiar" type="button" class="btn btn-secondary" style="height:50px; width:100px" accesskey="L"><u><b>L</b></u>impiar</button>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12 col-md-3 pb-1 ">
								<div class="row">
									<label for="Apartir" class="col-3 text-right"><b>A partir de</b></label>
									<div class="input-group col-6">
										<input type="date" class="form-control form-control-sm" name="Apartir" id="Apartir" value="" placeholder="A partir" required="">
									</div>
									<div class="form-group form-check">
										<input type="checkbox" class="form-check-input" id="ultimoTriage">
										<label class="form-check-label" for="ultimoTriage">Todas</label>
									</div>
								</div>			
							</div>
							<div class="col-12 col-md-8 pb-1 text-center ">
								<div class="col-12" >
									<div class="form-group form-check col-12">
										<input type="checkbox" class="form-check-input " id="ultimoTriage">
										<label class="form-check-label" for="ultimoTriage">Mostrar sólo el último triage</label>
									</div>
								</div>
							</div>
							<div class="col-12 col-md-1 pb-1 ">
								<div class="col-1" >
									<button id="btnActualizar" type="button" class="btn btn-secondary" style="height:50px; width:100px" accesskey="B"><u><b>B</b></u>uscar</button>
								</div>
							</div>
						</div>
					</div><!-- Cierra la segunda pestana -->
				</div><!-- Cierra el body secundario -->
				<div class="row">
					<div class="col-12">
						<h6>Pacientes Triage</h6>
							<table class="table table-sm  table-responsive-sm  border table-striped"> <!-- table-bordered  table-hover table-responsive table-sm  -->
								<thead class="thead-light "> <!-- dark -->
									<tr>
										<th scope="col">Clasificación</th>
										<th scope="col">Ingreso</th>
										<th scope="col">Con Ingreso</th>
										<th scope="col">Nombre</th>
										<th scope="col">Edad</th>
										<th scope="col">Fecha</th>
										<th scope="col">Hora</th>
										<th scope="col">Primer triage</th>
										<th scope="col">Estado</th>
										<th scope="col">Cantidad triage</th>
										<th scope="col">Turno</th>
										<th scope="col">Atiende</th>
										<th scope="col">Espec.</th>
										<th scope="col">Tipo turno</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<th scope="row">.</th>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
									</tr>
									<tr>
										<th scope="row">.</th>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
									</tr>
									<tr>
										<th scope="row">.</th>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
										<td>.</td>
									</tr>
								</tbody>
							</table>
					</div>
				</div>
		</div><!-- Cierra el body principal-->
		<div class="card-footer" id="">
			<div class="row d-flex justify-content-between" >
					<div class="col-6">
							<button id="btnValorarNuevamente" type="button" class="btn btn-secondary" style="width:200px" accesskey="V"><u><b>V</b></u>alorar Nuevamente</button>
					</div>
					<div class="col-3">
							<button id="btnNuevaValoracion" type="button" class="btn btn-secondary" style="width:200px" accesskey="N"><u><b>N</b></u>ueva valoración</button>
					</div>
					<div class="col-3">
							<button id="btnSalir" type="button" class="btn btn-secondary btn-md" style="width:200px" accesskey="S"><u><b>S</b></u>alir</button>
					</div>
				<div class="col-12">
				</div>
			</div>
		</div>
	</div>
	<script src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
	<script src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
	<script src="vista-triage/js/pacientesTriage.js"></script>
	<script src="vista-triage/js/funciones.js"></script>
	<?php
	}
	?>