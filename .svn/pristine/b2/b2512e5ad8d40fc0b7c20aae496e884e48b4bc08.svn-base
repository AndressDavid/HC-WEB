<?php
include __DIR__ . '/../comun/modalAyudaProcedimientos.php';
?>

<form id="FormCupsOM" name="FormCupsOM" novalidate="validate">
	<div class="row pb-1">
		<div class="input-group">
			<div id="textoAlertaLaboratorios" style="text-align: center; font-size: 14px;" 
			class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 alert alert-danger" role="alert">
			SI REQUIERE QUE SE TOMEN LABORATORIOS DE CONTROL PARA EL DÍA SIGUIENTE POR FAVOR REGISTRARLOS EN UNA ORDEN MÉDICA APARTE EN EL SISTEMA*
			</div>
		</div>
	</div>
				
	<div class="form-row pb-2">
		<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
			<div class="input-group input-group-sm">							
				<label id="lblcProcedimientoOM" for="cProcedimientoOM" class="required">Procedimiento</label>
				<label> &nbsp; &nbsp; &nbsp; &nbsp; </label>
				<input type="text" class="form-control form-control-sm font-weight-bold ignore col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" name="cProcedimientoOM" id="cProcedimientoOM" autocomplete="off" >
				<span class="input-group-text" id="btnAyudaProcedimiento"><i class="fas fa-search"></i></span>
			</div>
		</div>
				
		<div class="input-group input-group-sm">
			<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-1 col-xl-1" name="cCodigoProcedimientoOM" id="cCodigoProcedimientoOM" readonly="readonly">
			<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-11 col-xl-11" name="cDescripcionProcedimientoOM" id="cDescripcionProcedimientoOM" readonly="readonly">
		</div>
	</div>
		
	<div class="form-row pb-2">	
		<div class="col-3 col-sm-3 col-md-3 col-lg-2 col-xl-1">
			<label id="lblCantidadCupsOM" for="inpCantidadCupsOM">Cantidad</label>
		</div>

		<div class="col-3 col-sm-3 col-md-3 col-lg-2 col-xl-2">
			<input type="number" class="form-control" id="inpCantidadCupsOM" name="CantidadCupsOM" min="0" max="10" value="">
		</div>

		<!--
		<div class="col-0 col-sm-0 col-md-0 col-lg-1 col-xl-1">
			<label id="lblBlancoOM" for="inpBlancoOM"></label>
		</div>
		-->
		
		<div class="col-3 col-sm-3 col-md-3 col-lg-2 col-xl-1 mt-2">
			<label id="lblFrecuenciaCupsOM" for="inpFrecuenciaCupsOM">Frecuencia(hrs)</label>
		</div>

		<div class="col-3 col-sm-3 col-md-3 col-lg-2 col-xl-2">
			<input type="number" class="form-control mr-sm-2" id="inpFrecuenciaCupsOM" name="FrecuenciaCupsOM" min="0" max="24" value="">
		</div>

		
		<div class="custom-control custom-checkbox custom-control-inline mt-2 ml-3 ml-md-0">
			<input type="checkbox" class="form-check-input mr-sm-2" id="chkPortatilCupsOM" name="portatilCupsOM">
			<label id="lblPortatilCupsOM" class="form-check-label" for="chkPortatilCupsOM"> Portatil</label>
		</div>
		
		<div class="col-12 col-sm-12 col-md-6 col-lg-3 col-xl-1 mt-2">
			<label id="lblServicioRealizaOM" for="selServicioRealizaOM">Servicio realiza</label>
		</div>

		<div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-4">
			<select class="custom-select d-block w-100" id="selServicioRealizaOM" name="ServicioRealizaOM">
			  <option value=""></option>
			</select>
		</div>
		
		
	</div>
	<div class="row">
		<div class="col-12">
			<label id="lblInformacionClinicaCups" for="txtInformacionClinicaCups">Información Clínica</label>
			<textarea class="form-control" id="txtInformacionClinicaCups" name="txtInformacionClinicaCups" rows="2"></textarea>
		</div>
	</div>
</form>		

<div class="row justify-content-between pt-2">
	<div class="col-4 col-sm-4 col-md-4 col-lg-3 col-xl-2">
		<button id="AdicionarProcedimientoOM" class="btn btn-secondary btn-sl btn-block w-100" accesskey="C">Adi<u>c</u>ionar</button>
	</div>
	
	<div class="col-4 col-sm-4 col-md-4 col-lg-3 col-xl-2">
		<button id="eliminarProcedimientosOM" class="btn btn-secondary btn-sl btn-block w-100"><u>E</u>liminar</button>
	</div>
</div>		

<div class="table-responsive"><table id="tblProcedimientoOM"></table></div><br>
<script type="text/javascript" src="vista-ordenes-medicas/js/ordenes_procedimientos.js"></script>	
