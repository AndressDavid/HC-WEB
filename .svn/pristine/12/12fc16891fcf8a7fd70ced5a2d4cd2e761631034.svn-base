<?php
if (isset($_GET['q'])) {
	$lcPagina = __DIR__ .'/'.trim(strtolower($_GET['q'])).".php";
	include($lcPagina);

} else {
	(new NUCLEO\Auditoria())->guardarAuditoria(0, 0, 0, '', 'LIBROHC_WEB', 'INICIO', 0, 'INGRESO LIBRO HC', 'LIBROHC', '', 0);
?>
<div class="container-fluid">
	<div id="divCard" class="card mt-3">
		<div class="card-header">
			<div class="row">
				<div class="col">
					<h5>Libro de Historia Clínica</h5>
				</div>
				<div class="col-auto" style="padding: 0;">
					<button id="btnConsultaPDF" type="button" class="btn btn-secondary btn-sm" style="display:none" title="Consulta LibroHC PDF Generados">Consulta PDF Generados</button>
					<button id="btnCalidadGPC" type="button" class="btn btn-secondary btn-sm" style="display:none" title="Ver documentos Calidad-GPC">Documentos Calidad-GPC</button>
					<button id="btnLimpiar" type="button" class="btn btn-secondary btn-sm" accesskey="L"><u>L</u>impiar</button>
				</div>
			</div>
			<div id="filtroIngreso">
			<div class="row">
				<div class="col-md-3 col-lg-2 pb-2">
					<label for="inpTxtIngreso"><b>Ingreso</b></label>
					<input type="number" class="form-control form-control-sm" name="inpTxtIngreso" id="inpTxtIngreso" placeholder="" value="" required="">
				</div>
				<div class="col-md-6 col-lg-5 pb-2">
					<label for="inpNumDoc"><b>Documento</b></label>
					<div class="input-group mb-3">
						<select id="selTipDoc" class="custom-select custom-select-sm col-6"></select>
						<input type="number" id="inpNumDoc" name="inpNumDoc" class="form-control form-control-sm col-6" placeholder="" value="" required="">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-2 col-lg-1 pb-2">
					<button id="btnBuscar" type="button" class="btn btn-secondary btn-sm" accesskey="B"><u>B</u>uscar</button>
				</div>
			</div>
			</div>
			<div class="row">
				<div class="col">
					<div id="divIngresoInfo"></div>
				</div>
			</div>
		</div>


		<div id ="divLstDocumentos" class="card-body" style="display: none;">
			<div class="row">
				<div id="infoPaciente" class="col">
				</div>
			</div>
			<div class="row" id ="divIconoEspera" style="display: none;">
				<div class="fa-3x">
					<i class="fas fa-circle-notch fa-xs fa-spin" style="color:#f00"></i>
				</div>
			</div>
			<div id="wrpLstDocumentos" class="wrapper row" style="display: none;">

				<nav id="sidebar" class="col-auto mt-3">
					<div id="cardTree" class="card">
<!--
						<div class="card-header">
							<span id="btnViewAll" class="badge badge-secondary badge-btn" >Quitar Filtros</span>
						</div>
-->
						<div id="divTree" class="card-body fancytree-colorize-hover fancytree-fade-expander" style="padding:0;">
						</div>
						<div class="card-footer">
							<span id="btnExpandAll" class="badge badge-secondary badge-btn">Abrir Todo</span>
							<span id="btnCollapseAll" class="badge badge-secondary badge-btn">Cerrar Todo</span>
						</div>
					</div>
				</nav>

				<div id="content" class="col">
					<div class="container-fluid">
						<div class="row" id="divFiltros" style="display: none;">
							<div class="col-md-6 col-xl-4 pb-2">
								<label for="filtroTipoDoc" id="lblfiltroTipoDoc">Tipo</label>
								<div class="input-group col-12 p-0">
									<div class="dropdown dropdown-tree" id="filtroTipoDoc" style="width: 100%"></div>
								</div>
							</div>
							<div class="col-md-6 col-xl-4 pb-2">
								<label for="filtroFechaIni">Fecha</label>
								<div class="form-inline row">
									<div class="form-inline col-9 pr-0">
										<div class="form-group col-6 pl-1 pr-0">
											<div class="input-group input-group-sm date w-100">
												<div class="input-group-prepend">
													<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
												</div>
												<input type="text" class="form-control form-control-sm" id="filtroFechaIni" required="required" value="<?php print(date("Y-m-d")); ?>">
											</div>
										</div>
										<div class="form-group col-6 pl-1 pr-0">
											<div class="input-group input-group-sm date w-100">
												<div class="input-group-prepend">
													<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
												</div>
												<input type="text" class="form-control form-control-sm" id="filtroFechaFin" required="required" value="<?php print(date("Y-m-d")); ?>">
											</div>
										</div>
									</div>
									<div class="col-3 pl-1 pr-0">
										<div class="form-group col-12">
											<input type="checkbox" class="form-check-input" id="filtroFechaTodas" checked>
											<label class="form-check-label" for="filtroFechaTodas">Todas</label>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6 col-xl-4 pb-2">
								<label for="filtroMedico" id="lblfiltroMedico">Profesional</label>
								<input type="text" class="form-control form-control-sm" Id="filtroMedico" autocomplete="off">
							</div>
							<div class="col-md-6 col-xl-4 pb-2">
								<label for="filtroVia" id="lblfiltroVia">Vía Ingreso</label>
								<div class="input-group col-12 p-0">
									<div class="dropdown dropdown-tree" id="filtroVia" style="width: 100%"></div>
								</div>
							</div>
							<div class="col-md-6 col-xl-4 pb-2">
								<label for="filtroDescrip" id="lblfiltroDescrip">Descripción</label>
								<input type="text" id="filtroDescrip" class="form-control form-control-sm" />
							</div>
							<div class="col-md-6 col-xl-4 pb-2 mt-4 text-right">
								<button id="btnAplicarFiltros" class="btn btn-secondary btn-sm">Aplicar Filtro</button>
								<button id="btnQuitarFiltros"  class="btn btn-secondary btn-sm">Quitar Filtro</button>
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<small>
									<div id="toolBarLst">
										<button id="btnVerTreeView" type="button" class="btn btn-secondary btn-sm" title="Mostrar/Ocultar TreeView"></button>
										<button id="btnDatosPaciente" type="button" class="btn btn-secondary btn-sm" title="Ver datos del paciente">Datos Pac</button>
										<button id="btnLibroPDF" type="button" class="btn btn-secondary btn-sm" title="Exportar documentos visibles a PDF">Exportar PDF</button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="checkbox" class="form-check-input" id="chkConAdjuntos">
										<label class="form-check-label" for="chkConAdjuntos" id="lblConAdjuntos">Incluir Adjuntos</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<button id="btnVerFiltros" type="button" class="btn btn-secondary btn-sm" title="Mostrar/Ocultar Filtros">Ver Filtros</button>
									</div>
									<table id="tblLstDocumentos"></table>
								</small>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="card-footer text-muted">
			<p><span id="spnNumReg">Libro de Historia Clínica</span></p>
		</div>
	</div>
</div>

<link rel="stylesheet" type="text/css" media="screen" href="publico-complementos/bootstrap-table/1.18.3-dist/bootstrap-table.min.css" />
<link rel="stylesheet" type="text/css" media="screen" href="publico-complementos/fancytree/2.30.0-dist/skin-awesome/ui.fancytree.css" />
<link rel="stylesheet" type="text/css" media="screen" href="publico-complementos/dropdowntree/1.1.1/dropdowntree.css" />
<link rel="stylesheet" type="text/css" media="screen" href="vista-documentos/custom.css" />

<script type="text/javascript" src="publico-complementos/bootstrap-table/1.18.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/1.18.3-dist/bootstrap-table-locale-all.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-autocomplete/2.3.7-dist/bootstrap-autocomplete.min.js"></script>
<script type="text/javascript" src="publico-complementos/fancytree/2.30.0-dist/jquery.fancytree-all-deps.min.js"></script>
<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="publico-complementos/dropdowntree/1.1.1/dropdowntree.min.js"></script>
<script type="text/javascript" src="vista-documentos/js/scripts.js"></script>
<script type="text/javascript" src="vista-comun/js/tiposDocumentos.js"></script>
<script type="text/javascript" src="vista-comun/js/listaMedicos.js"></script>
<script type="text/javascript" src="vista-comun/js/modalVistaPrevia.js"></script>
<script type="text/javascript" src="vista-comun/js/modalDatosPaciente.js"></script>
<script type="text/javascript" src="vista-comun/js/tiposGeneros.js"></script>

<script type="text/javascript">
	var gcHcwIngreso = <?= $_SESSION[HCW_DATA]['ingreso']??($_POST['ingreso']??0) ?>;
	var gcHcwTipDoc = <?= '"'.($_SESSION[HCW_DATA]['tipdoc']??($_POST['tipdoc']??'')).'"' ?>;
	var gcHcwNumDoc = <?= $_SESSION[HCW_DATA]['numdoc']??($_POST['numdoc']??0) ?>;
</script>
<?php
	unset($_SESSION[HCW_DATA]);
}
?>