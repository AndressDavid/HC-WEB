<?php
	$laRetorna['cTipId'] = '';
	$laRetorna['nNumId'] = 0;
	$laAuditoria['cUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getUsuario();
	$laAuditoria['cEspUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getEspecialidad();
	$laAuditoria['cTipopUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getTipoUsuario(false);
	$laAuditoria['cEspUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getEspecialidad();
?>

<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<div class="row">
				<div class="col">
					<h5 id="idTituloCenso" >RIPS - Resolución 2806</h5>
				</div>
			</div>
			<form id="frmFiltros">
				<div id="divFiltro" class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 col-xl-2">
						<label for="txtFactura">Número Factura/Nota</label>
						<input id="txtFactura" type="number" class="form-control form-control-sm" name="txtFactura" maxlength="8" min="0" max="99999999" placeholder="" value="">
					</div>
					
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 col-xl-2">
						<label for="selTipoDocumentoR" class="control-label">Tipo documento</label>
						<select name="TipoDocumentoR" id="selTipoDocumentoR" class="form-control form-control-sm"><option value=""></option></select>
					</div>
					
					<div class="col-xs-6 col-md-4 col-lg-2 col-xl-1" style="padding-left: 1px;padding-right: 1px;">
						<label for="btnBusca" class="control-label"> &nbsp;  &nbsp;  &nbsp; &nbsp;  &nbsp;  &nbsp; </label>
						<button id="btnBuscarFactura" type="button" class="form-control-sm btn btn-secondary btn-sm w-100" accesskey="B"><u>B</u>uscar</button>
					</div>
				</div>
			</form>
		</div>
		<small>
		<div class="container-fluid">
			<div class="card mt-3">
				<div class="card-body">
					<div class="card-title">
						<div class="row edicion">
							<div class="col">
								<ul class="nav nav-pills" id="tabOpcionesRips" role="tablist">
									<li class="nav-item" role="presentation">
										<a class="text-dark nav-link active" id="tabOptTransaccion" data-toggle="tab" href="#tabRipsTransaccion" role="tab" aria-controls="a" aria-selected="true">Transacción</a>
									</li>
																		
									<li class="nav-item" role="presentation">
										<a class="text-dark nav-link" id="tabOptUsuario" data-toggle="tab" href="#tabRipsUsuario" role="tab" aria-controls="b" aria-selected="false">Usuarios</a>
									</li>
									<li class="nav-item" role="presentation">
										<a class="text-dark nav-link" id="tabOptConsultas" data-toggle="tab" href="#tabRipsConsultas" role="tab" aria-controls="c" aria-selected="false">Consultas</a>
									</li>
									
									<li class="nav-item" role="presentation">
										<a class="text-dark nav-link" id="tabOptdMedicamentos" data-toggle="tab" href="#tabRipsMedicamentos" role="tab" aria-controls="d" aria-selected="false">Medicamentos</a>
									</li>
									
									<li class="nav-item" role="presentation">
										<a class="text-dark nav-link" id="tabOptProcedimientos" data-toggle="tab" href="#tabRipsProcedimientos" role="tab" aria-controls="e" aria-selected="false">Procedimientos</a>
									</li>
									
									<li class="nav-item" role="presentation">
										<a class="text-dark nav-link" id="tabOptUrgencias" data-toggle="tab" href="#tabRipsUrgencias" role="tab" aria-controls="f" aria-selected="false">Urgencias</a>
									</li>
									
									<li class="nav-item" role="presentation">
										<a class="text-dark nav-link" id="tabOptHospitalizacion" data-toggle="tab" href="#tabRipsHospitalizacion" role="tab" aria-controls="g" aria-selected="false">Hospitalización</a>
									</li>
									
									<li class="nav-item" role="presentation">
										<a class="text-dark nav-link" id="tabOptRecien" data-toggle="tab" href="#tabRipsRecien" role="tab" aria-controls="h" aria-selected="false">Recien nacido</a>
									</li>

									<li class="nav-item" role="presentation">
										<a class="text-dark nav-link" id="tabOptOtrosServicios" data-toggle="tab" href="#tabRipsOtrosServicios" role="tab" aria-controls="h" aria-selected="false">Otros Servicios</a>
									</li>
								</ul>

								<div class="card border-top-1">
									<div class="tab-content" id="TabPropiedadesAmb">
										<div class="tab-pane fade show active" id="tabRipsTransaccion" role="tabpanel" aria-labelledby="tabOptTransaccion">
											<div class="table-responsive"><table id="tblRipsTransaccion"></table></div>
										</div>
										<div class="tab-pane fade" id="tabRipsUsuario" role="tabpanel" aria-labelledby="tabOptUsuario">
											<div class="table-responsive"><table id="tblRipsUsuario"></table></div>
										</div>
										
										<div class="tab-pane fade" id="tabRipsConsultas" role="tabpanel" aria-labelledby="tabOptConsultas">
											<div class="table-responsive"><table id="tblRipsConsulta"></table></div>
										</div>
										
										<div class="tab-pane fade" id="tabRipsMedicamentos" role="tabpanel" aria-labelledby="tabOptdMedicamentos">
											<div class="table-responsive"><table id="tblRipsMedicamentos"></table></div>
										</div>
										
										<div class="tab-pane fade" id="tabRipsProcedimientos" role="tabpanel" aria-labelledby="tabOptProcedimientos">
											<div class="table-responsive"><table id="tblRipsProcedimientos"></table></div>
										</div>
										
										<div class="tab-pane fade" id="tabRipsUrgencias" role="tabpanel" aria-labelledby="tabOptUrgencias">
											<div class="table-responsive"><table id="tblRipsUrgencias"></table></div>
										</div>
										
										<div class="tab-pane fade" id="tabRipsHospitalizacion" role="tabpanel" aria-labelledby="tabOptHospitalizacion">
											<div class="table-responsive"><table id="tblRipsHospitalizacion"></table></div>
										</div>
										
										<div class="tab-pane fade" id="tabRipsRecien" role="tabpanel" aria-labelledby="tabOptRecien">
											<div class="table-responsive"><table id="tblRipsRecienNacido"></table></div>
										</div>
										
										<div class="tab-pane fade" id="tabRipsOtrosServicios" role="tabpanel" aria-labelledby="tabOptOtrosServicios">
											<div class="table-responsive"><table id="tblRipsOtrosServicios"></table></div>
										</div>
									</div>
										
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		</small>

	</div>
</div>
<link rel="stylesheet" type="text/css" media="screen" href="vista-facturacion/css/rips.css" />


<script type="text/javascript">
<?php

	//echo 'var aDatosCenso = btoObj(\'' . base64_encode(json_encode($lcTipo)) . '\');' . PHP_EOL;
	//echo 'var aDatosIngreso = btoObj(\'' . base64_encode(json_encode($laRetorna)) . '\');' . PHP_EOL;
	//echo 'var aAuditoria = btoObj(\'' . base64_encode(json_encode($laAuditoria)) . '\');' . PHP_EOL;
?>
</script>
