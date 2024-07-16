<?php
include __DIR__ . '/../comun/modalEuroscore.php';
?>

<div class="card card-block">
	<div class="card-header" id="headerProcedimientoUci">
		<a href="#procedimientoUci" class="card-link text-dark"><b>Procedimiento</b></a>
	</div>

	<div class="card-body">
		<form role="form" id="FormProcedimientoUci" name="FormProcedimientoUci" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
			<div class="form-row pb-2">
				
				<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
					<div class="input-group input-group-sm">							
						<label for="buscarProcedimientoUci">Diagnóstico de procedimiento</label>
						<label> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </label>
						<input type="text" class="form-control form-control-sm font-weight-bold ignore col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" name="buscarProcedimientoUci" id="buscarProcedimientoUci" autocomplete="off" >
					</div>
				</div>
				
				<div class="input-group input-group-sm">
					<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-1 col-xl-1" name="codigoProcedimientoUci" id="codigoProcedimientoUci" readonly="readonly">
					<input type="text" class="form-control form-control-sm font-weight-bold col-12 col-sm-12 col-md-12 col-lg-11 col-xl-11" name="descripcionProcedimientoUci" id="descripcionProcedimientoUci" readonly="readonly">
				</div>
			</div>
	
			<div class="row pb-2">
				<div class="col-12 col-sm-5 col-md-5 col-lg-3 col-xl-2">
					<label id="lblInvacionUci" for="txtInvacionUci">Invasión</label>
				</div>
				
				<div class="col-12 col-sm-5 col-md-5 col-lg-1 col-xl-1">
					<div class="custom-control custom-checkbox custom-control-inline">
						<input type="checkbox" class="custom-control-input" id="chkTotUci" name="TotUci" value="1">
						<label class="custom-control-label" for="chkTotUci">TOT</label>
					</div>
				</div>

				<div class="col-12 col-sm-5 col-md-5 col-lg-1 col-xl-1">
					<div class="custom-control custom-checkbox custom-control-inline">
						<input type="checkbox" class="custom-control-input" id="chkCvcUci" name="CvcUci" value="1">
						<label class="custom-control-label" for="chkCvcUci">CVC</label>
					</div>
				</div>
				
				<div class="col-12 col-sm-5 col-md-5 col-lg-1 col-xl-1">
					<div class="custom-control custom-checkbox custom-control-inline">
						<input type="checkbox" class="custom-control-input" id="chkSvUci" name="sVtUci" value="1">
						<label class="custom-control-label" for="chkSvUci">SV</label>
					</div>
				</div>

				<div class="col-12 col-sm-6 col-md-6 col-lg-1 col-xl-1">
					<div class="custom-control custom-checkbox custom-control-inline">
						<input type="checkbox" class="custom-control-input" id="chkNingunoUci" name="ningunoUci" value="1">
						<label class="custom-control-label" for="chkNingunoUci">Ninguno</label>
					</div>
				</div>
			</div>
			
			<div class="row pb-2">
				<div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4">
					<label id="lblInfeccionUci" for="selInfeccionUci">Infección</label>
					<select id="selInfeccionUci" name="InfeccionUci" class="custom-select"><option></option><option>Si</option><option>No</option></select>
				</div>

				<div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4">
					<label id="lblNefroproteccionUci" for="selNefroproteccionUci">Nefroprotección</label>
					<select id="selNefroproteccionUci" name="NefroproteccionUci" class="custom-select"><option></option><option>Si</option><option>No</option></select>
				</div>
				
				<div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
					<label id="lblProfilaxisUci" for="selProfilaxisUci">Profilaxis FA</label>
					<select id="selProfilaxisUci" name="ProfilaxisUci" class="custom-select"><option></option><option>Si</option><option>No</option></select>
				</div>
			</div>

			<div class="row pb-2">	
				<div class="col-12 col-sm-6 col-md-4 col-lg-2 col-xl-2">
					<label id="lblEuroescoreUci" for="txtEuroescoreUci">Euroescore</label>
					<input id="txtEuroescoreUci" name="EuroescoreUci" type="number" class="form-control w-100">
				</div>
				
				<div class="col-12 col-sm-6 col-md-4 col-lg-2 col-xl-2">
					<label id="lblBotonEuroscore" for="btnEuroscore"> &nbsp; &nbsp; </label>
					<button id="btnEuroscore" type="button" class="btn btn-secondary btn-sm w-100" accesskey="B"><u>E</u>uroscore</button>
				</div>
				
				<div class="col-12 col-sm-6 col-md-4 col-lg-8 col-xl-8">
					<label id="lblResultadoEuroescoreUci" for="txtResultadoEuroescoreUci"> &nbsp; &nbsp; </label>
					<input id="txtResultadoEuroescoreUci" name="ResultadoEuroescoreUci" type="text" class="form-control w-100" disabled>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<h5>Complicaciones</h5>
				</div>
				
				<div class="col-12">
					<div class="custom-control custom-checkbox custom-control-inline">
						<input type="checkbox" class="custom-control-input" id="chkSinComplicaciones" name="SinComplicaciones" value="1">
						<label class="custom-control-label" for="chkSinComplicaciones">Sin Complicaciones</label>
					</div>
				</div>
			</div>
			
			<div><table id="tblComplicacionesUci"></table></div>
			
		</form>
	</div>
</div>
<script type="text/javascript" src="vista-evoluciones/js/diagnosticoProcedimiento.js"></script>
<script type="text/javascript" src="vista-comun/js/modalEuroscore.js"></script>
