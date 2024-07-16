<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<div class="row">
				<div class="col-12 col-md-8 col-lg-11">
					<h5>PROGRAMAR SOPORTES CM</h5>
				</div>
			</div>

			<div class="row pl-4">
				<div class="col-auto">
					<label for="selTipoSoportes" class="my-1 mr-2">Tipo de soportes:</label>
				</div>
				<div class="col-auto">
					<select id="selTipoSoportes" name="selTipoSoportes" class="custom-select custom-select-sm my-1 mr-sm-2"></select>
				</div>
			</div>

			<div class="row pl-4 ctrls-adicionales">
				<div class="col pb-2">
					<label for="selTipoSoportes" class="my-1 mr-2">Seleccione soportes a generar:</label>
					<div class="form-check pb-2">
						<input id="chkTipoTodos" name="chkTipoTodos" class="form-check-input" type="checkbox" value="todos" checked>
						<label for="chkTipoTodos" class="form-check-label">Todos</label>
					</div>
					<div class="card small">
						<div class="card-title pl-3 pt-2">
							<div id="divSoportes" class="row"></div>
						</div>
					</div>
				</div>
			</div>

<!--
			<div class="row pl-4 ctrls-adicionales">
				<div class="col pb-2">
					<div class="form-check pb-2" id="divChkCarpetaGeneral">
						<input id="chkCarpetaGeneral" name="chkCarpetaGeneral" class="form-check-input" type="checkbox" value="todos" checked>
						<label for="chkCarpetaGeneral" class="form-check-label">Guardar en la Carpeta de CM</label>
					</div>
				</div>
			</div>

			<div class="row pl-4 ctrls-adicionales">
				<div class="col pb-2">
					<div class="form-check pb-2" id="divChkCarpetaTransf">
						<input id="chkCarpetaTransf" name="chkCarpetaTransf" class="form-check-input" type="checkbox" value="todos" checked>
						<label for="chkCarpetaTransf" class="form-check-label">Guardar en la carpeta de transfiriendo</label>
					</div>
				</div>
			</div>
-->

			<div class="row ctrls-adicionales pl-4">
				<div class="col-auto">
					<label for="txtIngreso" class="my-1 mr-2">Ingreso:</label>
				</div>
				<div class="col-auto">
					<input id="txtIngreso" name="txtIngreso" type="number" class="form-control form-control-sm my-1 mr-sm-2" />
				</div>
				<div class="col-auto">
					<button id="btnAddSoportesIngresoGenerar" type="button" class="btn btn-sm btn-info mt-1">Adicionar Ingreso para Generar Soportes</button>
				</div>
			</div>

		</div>

		<div class="card-body">
			<div id="divResultado" class="row">
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" type="text/css" href="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />

<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/localization/messages_es.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
<script type="text/javascript" src="vista-comun/js/comun.js"></script>
<script type="text/javascript" src="vista-soportescm/js/script.js"></script>
<script type="text/javascript" src="vista-soportescm/js/generar.js"></script>
