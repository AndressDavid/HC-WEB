<?php
require_once __DIR__ . '/../../vista/comun/modalMedicamentoCTC.php';
require_once __DIR__ . '/../../vista/comun/modalProcedimientosCTC.php';
?>


<div class="container-fluid">
	<div class="card mt-3">
			<div class="card-body">
				<div class="card-title">
					<div class="row edicion">
						<div class="col">
							<ul class="nav nav-pills" id="tabOpcionesAmbulatorios" role="tablist">
								<li class="nav-item" role="presentation">
									<a class="text-dark nav-link active" id="tabOptOrdMedicamento" data-toggle="tab" href="#tabOrdenMedicamento" role="tab" aria-controls="a" aria-selected="true">Medicamentos</a>
								</li>
								<li class="nav-item" role="presentation">
									<a class="text-dark nav-link" id="tabOptOrdCups" data-toggle="tab" href="#tabOrdenCups" role="tab" aria-controls="b" aria-selected="false">Procedimientos</a>
								</li>
								
								<li class="nav-item" role="presentation">
									<a class="text-dark nav-link" id="tabOptOrdInsumos" data-toggle="tab" href="#tabOrdenInsumos" role="tab" aria-controls="c" aria-selected="false">Insumos</a>
								</li>
								
								<li class="nav-item" role="presentation">
									<a class="text-dark nav-link" id="tabOptOrdInterconsultas" data-toggle="tab" href="#tabOrdenInterconsultas" role="tab" aria-controls="d" aria-selected="false">Interconsultas</a>
								</li>
								
								<li class="nav-item" role="presentation">
									<a class="text-dark nav-link" id="tabOptOrdDieta" data-toggle="tab" href="#tabOrdenDieta" role="tab" aria-controls="e" aria-selected="false">Dietas</a>
								</li>
								
								<li class="nav-item" role="presentation">
									<a class="text-dark nav-link" id="tabOptOrdIncapacidad" data-toggle="tab" href="#tabOrdenIncapacidad" role="tab" aria-controls="f" aria-selected="false">Incapacidad</a>
								</li>
								
								<li class="nav-item" role="presentation">
									<a class="text-dark nav-link" id="tabOptOrdRecomendacion" data-toggle="tab" href="#tabOrdenRecomendacion" role="tab" aria-controls="g" aria-selected="false">Recomendación</a>
								</li>
								
								<li class="nav-item" role="presentation">
									<a class="text-dark nav-link" id="tabOptOrdOtras" data-toggle="tab" href="#tabOrdenOtras" role="tab" aria-controls="h" aria-selected="false">Otras</a>
								</li>
							</ul>

							<div class="card border-top-1">
								<div class="tab-content" id="TabPropiedadesAmb">
									<div class="tab-pane fade show active" id="tabOrdenMedicamento" role="tabpanel" aria-labelledby="tabOptOrdMedicamento">
										<div class="card-body">
											<?php
												include __DIR__ .'/../comun/ambulatoria_medicamentos.php'; 
											?>
										</div>
									</div>
								
									<div class="tab-pane fade" id="tabOrdenCups" role="tabpanel" aria-labelledby="tabOptOrdCups">
										<div class="card-body">
											<?php
												include __DIR__ .'/../comun/ambulatoria_procedimientos.php'; 
											?>
										</div>
									</div>
									
									<div class="tab-pane fade" id="tabOrdenInsumos" role="tabpanel" aria-labelledby="tabOptOrdInsumos">
										<div class="card-body">
											<?php
												include __DIR__ .'/../comun/ambulatoria_insumos.php'; 
											?>
										</div>
									</div>
									
									<div class="tab-pane fade" id="tabOrdenInterconsultas" role="tabpanel" aria-labelledby="tabOptOrdInterconsultas">
										<div class="card-body">
											<?php
												include __DIR__ .'/../comun/ambulatoria_interconsulta.php'; 
											?>
										</div>
									</div>
									
									<div class="tab-pane fade" id="tabOrdenDieta" role="tabpanel" aria-labelledby="tabOptOrdDieta">
										<div class="card-body">
											<?php
												include __DIR__ .'/../comun/ambulatoria_dieta.php'; 
											?>
										</div>
									</div>
									
									<div class="tab-pane fade" id="tabOrdenIncapacidad" role="tabpanel" aria-labelledby="tabOptOrdIncapacidad">
										<div class="card-body">
											<?php
												include __DIR__ .'/../comun/ambulatoria_incapacidad.php'; 
											?>
										</div>
									</div>
									
									<div class="tab-pane fade" id="tabOrdenRecomendacion" role="tabpanel" aria-labelledby="tabOptOrdRecomendacion">
										<div class="card-body">
											<?php
												include __DIR__ .'/../comun/ambulatoria_recomendacion.php'; 
											?>
										</div>
									</div>
									
									<div class="tab-pane fade" id="tabOrdenOtras" role="tabpanel" aria-labelledby="tabOptOrdOtras">
										<div class="card-body">
											<?php
												include __DIR__ .'/../comun/ambulatoria_otras.php'; 
											?>
										</div>
									</div>
								</div>
									
							</div>
						</div>
					</div>
				</div>
			</div>
				
	
	</div>
</div>
<link rel="stylesheet" type="text/css" media="screen" href="vista-comun/css/ambulatorios.css" />
<script type="text/javascript" src="vista-comun/js/planespaciente.js"></script>
<script type="text/javascript" src="vista-comun/js/medicamentos.js"></script>
