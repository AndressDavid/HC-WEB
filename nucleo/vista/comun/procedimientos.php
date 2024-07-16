<?php
include __DIR__ . '/../comun/modalAyudaProcedimientos.php';
?>

<form id="FormCups" name="FormCups" novalidate="validate">
	<div class="form-row pb-2">
		<label for="cProcedimientoOM"><b>Procedimiento</b></label>
		<div class="input-group input-group-sm">
			<div class="input">
			</div>
			<input type="text" class="form-control form-control-sm font-weight-bold ignore col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" name="buscarProcedimiento" id="buscarProcedimiento" autocomplete="off" >
			<span class="input-group-text" id="seleccionarProcedimiento"><i class="fas fa-search"></i></span>
		</div>
		
		<div class="input-group input-group-sm">
			<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-1 col-xl-1" name="codigoProcedimiento" id="codigoProcedimiento" readonly="readonly">
			<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-9 col-xl-9" name="descripcionProcedimiento" id="descripcionProcedimiento" readonly="readonly">
			<label> &nbsp; &nbsp; </label><label for="idCantidadProcedimiento">Cantidad &nbsp; &nbsp; </label>
			<input type="number" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-2 col-xl-2" id="idCantidadProcedimiento" name="cantidadProcedimiento" min="0" max="90" value="" required>
		</div>
	</div>
	
	<div class="row">
		<div class="col-12">
			<label for="observacionProcedimiento">Observación</label>
			<textarea class="form-control" id="txtObservacionProcedimiento" rows="2"></textarea>
		</div>
	</div>
</form>		

<div class="row justify-content-between pt-2">
	<div class="col-12 col-sm-8 col-md-4 col-lg-3 col-xl-2">
		<button id="AdicionarProcedimiento" class="btn btn-secondary btn-sl btn-block w-100"><u>A</u>dicionar</button>
	</div>
	
	<div class="col-12 col-sm-8 col-md-4 col-lg-3 col-xl-2">
		<button id="eliminarProcedimientos" class="btn btn-secondary btn-sl btn-block w-100"><u>E</u>liminar</button>
	</div>
</div>				
<div class="table-responsive"><table id="tblProcedimiento"></table></div><br>

<script type="text/javascript" src="vista-comun/js/procedimientos.js"></script>	
