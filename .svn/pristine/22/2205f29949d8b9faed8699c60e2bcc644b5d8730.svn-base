<?php

require_once __DIR__ .'/../../publico/constantes.php';

$laRetorna['error'] = '';

$nSalir = 0;
if (isset($_SESSION[HCW_NAME])){
	if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()!==true){
		$laRetorna['error'] = 'El usuario no tiene sesión activa';
		$nSalir = 1;
	}
} else {
	$laRetorna['error'] = 'Error en la sesión. Intente nuevamente.';
	$nSalir = 1;
}

if ($nSalir == 0) {
	$loParametrosUci = new NUCLEO\ParametrosConsulta;
	$laParametrosUci = $loParametrosUci->ObtenerParametrosUci('');
	$laParametrosUci = $loParametrosUci->TiposParametrosUci();

	foreach($laParametrosUci as $lnIndice => $laRegistro){
		$laDatosParametrosUci[] = $laRegistro;
	}

}
?>

<div class="card card-block">
	<div class="card-header" id="headerRecomendacionesUcc">
		<a href="#recomendacionesUcc" class="card-link text-dark"><b>Recomendaciones</b></a>
	</div>

	<div class="card-body">
		<ul class="nav nav-tabs" id="myTab" role="tablist">
			<li class="nav-item" role="presentation">
				<a class="nav-link active" id="tabParametros" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">PARÁMETROS</a>
			</li>
			<li class="nav-item" role="presentation">
				<a class="nav-link" id="tabGrupoMedica" data-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">GRUPO DE MEDICAMENTOS</a>
			</li>
		</ul>

		<div class="card border-top-1">
			<div class="tab-content" id="tabRecomendaciones">
				<div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="tabParametros">
					<div class="card-body">
						<form role="form" id="FormRecomendacionesUCC" name="FormRecomendacionesUCC" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
							<div class="row">
								<table id="tblRecomendacionesUCC" class="table table-sm table-bordered table-striped table-responsive">
									<thead>
										<tr>
											<td class="text-center align-middle" style="width:45%"><b>PARÁMETROS A MODIFICAR</b></td>
											<td class="text-center align-middle" style="width:15%"><b>VALOR</b></td>
											<td class="text-center align-middle" style="width:40%"><b>META</b></td>
										</tr>
									</thead>

									<tbody>
										<?php
										foreach($laDatosParametrosUci as $lnClave =>$itemParametrosUci){
											$lcDescripcion1 = $itemParametrosUci['DE1TMA'];
											$lcDescripcion2 = trim($itemParametrosUci['METTMA']);
											$lcOpcional1 = trim($itemParametrosUci['OP6TMA']);
											$lcCodigo = trim($itemParametrosUci['OP2TMA']);

											if($lcOpcional1=='N'){
												$lcControl = '<input type="number" class="form-control form-control-sm" name="'.$lcCodigo.'" id="'.$lcCodigo.'" value="" data-id="'.$lnClave.'">';
											}else{
												$lcControl = '<input type="checkbox" name="'.$lcCodigo.'" id="'.$lcCodigo.'" value="checked" data-id="'.$lnClave.'">';
											}

											$lcFila = '<tr>'
													  ."<td style='width:45%' id='lblparametro".intval($lcCodigo)."' > $lcDescripcion1</td>"
													  ."<td class='text-center align-middle' style='width:15%'>$lcControl</td>"
													  ."<td style='width:40%'>$lcDescripcion2</td>"
													  .'</tr>';
											print($lcFila);
										}
										?>
									</tbody>
								</table>
							</div>
						</form>
					</div>
				</div>
				<div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="tabGrupoMedica">
					<div class="card-body">
						<form role="form" id="FormGrupoUCC" name="FormGrupoUCC" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
							<div class="row pb-2">
								<div class="input-group">
									<div class="col-12 col-sm-5 col-md-5 col-lg-2 col-xl-2">
										<label id="lblgrupoMedicamentoUci" for="selGrupoMedicamentoUci">Grupo</label>
									</div>
									<div class="col-12 col-sm-5 col-md-5 col-lg-4 col-xl-4">
										<select id="selGrupoMedicamentoUci" class="form-control form-control-sm" name="selGrupoMedicamentoUci"></select>
									</div>
								</div>
							</div>

							<div class="row pb-2">
								<div class="input-group">
									<div class="col-12 col-sm-5 col-md-5 col-lg-2 col-xl-2">
										<label id="lblMedicamentoUci" for="selMedicamentosUci">Medicamento</label>
									</div>
									<div class="col-12 col-sm-5 col-md-5 col-lg-4 col-xl-4">
										<select id="selMedicamentosUci" class="form-control form-control-sm" name="selMedicamentosUci"></select>
									</div>

									<div class="col-12 col-sm-5 col-md-5 col-lg-4 col-xl-6">
										<input id="medicamentoDescripcionUci" name="medicamentoDescripcionUci" type="text" class="form-control form-control-sm">
									</div>
								</div>
							</div>

							<div class="row pb-2">
								<div class="input-group">
									<div class="col-12 col-sm-5 col-md-5 col-lg-2 col-xl-2">
										<label id="lblIndicadoParaUci" for="indicadoParaUci">Indicado para</label>
									</div>

									<div class="col-12 col-sm-5 col-md-5 col-lg-8 col-xl-10">
										<input id="indicadoParaUci" name="indicadoParaUci" type="text" class="form-control form-control-sm">
									</div>

								</div>
							</div>

							<div class="row justify-content-between pt-2">
								<div class="col-md-2"></div>

								<div class="col-12 col-sm-6 col-md-5 col-lg-3 col-xl-3">
									<button id="adicionarGrupoMedicamento" class="btn btn-secondary btn-sl btn-block w-100" accesskey="A"><u>A</u>dicionar</button>
								</div>
							</div>
							<div class="col-12">
								<table id="tblgrupoMedicamentoUnidad"></table>
							</div>

							<div class="row pb-2">
								<div class="col-12">
									<label id="lblRecomendacionesucc" for="edtRecomendacionesUCC">Recomendaciones</label>
									<textarea class="form-control" id="edtRecomendacionesUCC" name="edtRecomendacionesUCC" rows="5"></textarea>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript" src="vista-evoluciones/js/recomendaciones.js"></script>
