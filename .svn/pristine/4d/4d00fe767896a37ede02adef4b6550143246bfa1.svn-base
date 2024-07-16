<div class="container-fluid">
	<div id="divCard" class="card mt-3">
		<div class="card-header">
			<div class="row">
				<div class="col-8 col-lg-8 col-md-8 col-sm-8 col-xs-8">
					<h5>Libro de Historia Clínica Últimas 24hr</h5>
				</div>
			</div>
			<div class="row">
				<div class="col">
					<div id="divIngresoInfo"></div>
				</div>
			</div>
			<div id="filtroIngreso" class="form-inline">
				<label class="mr-sm-2" for="inpTxtIngreso"><b>Ingreso</b></label>
				<input type="number" class="form-control form-control-sm mr-sm-2" name="inpTxtIngreso" id="inpTxtIngreso" placeholder="" value="" required="">
				<button id="btnBuscar" type="button" class="btn btn-secondary btn-sm mr-sm-2" accesskey="B"><u>B</u>uscar</button>
			</div>
		</div>

		<div id ="divLstDocumentos" class="card-body" style="display: none;">
			<div class="row">
				<div id="divInfoPaciente" class="col-12 col-lg-12 col-md-12 col-sm-12">
					<span id="infoPaciente"></span>
					<button id="btnLimpiar" type="button" class="btn btn-outline-secondary btn-sm mb-2 mr-sm-2" accesskey="L"><u>L</u>impiar</button>
				</div>
			</div>
			<div class="row" id ="divIconoEspera" style="display: none;">
				<div class="col">
					<span class="badge badge-inform"> Espere mientras se realiza la consulta</span> <span class="fas fa-2x fa-circle-notch fa-xs fa-spin" style="color:#f00"></span>
				</div>
			</div>
			<hr>
			<div id="wrpLstDocumentos" class="container-fluid" style="display: none;">
				<div class="row">
					<div id="divContenidoLibro" class="col-12">
					</div>
				</div>
			</div>
		</div>

		<div class="card-footer text-muted">
			<p><span id="spnNumReg">Libro de Historia Clínica Últimas 24hr</span></p>
		</div>
	</div>
</div>

<link rel="stylesheet" type="text/css" media="screen" href="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />
<link rel="stylesheet" type="text/css" media="screen" href="vista-documentos/custom.css" />
<link rel="stylesheet" type="text/css" media="screen" href="vista-comun/css/modalVistaPrevia.css" />

<script type="text/javascript" src="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
<script type="text/javascript" src="vista-comun/js/comun.js"></script>
<script type="text/javascript" src="vista-documentos/js/libro24h.js"></script>

<script type="text/javascript">
	var gcHcwIngreso = <?php echo $_SESSION[HCW_DATA]['ingreso']??0; ?>;
</script>
<?php
	unset($_SESSION[HCW_DATA]);
?>