<?php
	require_once __DIR__ . '/../../controlador/class.SalasCirugia.php';

	$laSalas = (new NUCLEO\SalasCirugia())->aSalasCirugia;
	$lnBloqueInicio = intval(isset($_GET['nBloqueInicio'])?$_GET['nBloqueInicio']:0);
	$lnBloquePaso = 6;
	$lnBloqueSalas = 0;
	$lnTimeMaxSeg=60;
	
	$laSalaEstilos =['estado-1', 'estado-2', 'estado-3', 'estado-4', 'estado-5'];
	
?><!doctype html>
<html lang="en" class="h-100">
	<head>
		<!-- Cabecera -->
		<?php
			$lcInclude = file_get_contents("../../publico/head.php");
			$lcInclude = str_replace("publico-complementos","../publico-complementos",$lcInclude);
			$lcInclude = str_replace("publico-css","../publico-css",$lcInclude);
			$lcInclude = str_replace("publico-ico","../publico-ico",$lcInclude);
			$lcInclude = str_replace("hcw-manifiest.json.php","../hcw-manifiest.json.php",$lcInclude);
			print($lcInclude);
		?>
		<link rel="stylesheet" type="text/css" href="css/dashboard.css" />		
	</head>
	<body class="h-100">
		<div class="container-fluid h-100 pl-1 pt-1 pr-1 pb-4">
			<div class="card h-100">
				<div class="card-body">
					<div class="row h-100">
						<?php 
							$lnBloqueSalas = 0;
							foreach($laSalas as $lnSala => $laSala){
								if($lnSala>=$lnBloqueInicio && $lnBloqueSalas<$lnBloquePaso){
									$lnBloqueSalas+=1;
									$lnSalaEstado = rand(0, count($laSalaEstilos)-1);
									
									$lcSalaEstilo = $laSalaEstilos[$lnSalaEstado];
						?>
						<div class="col-md-4 h-50 p-1">
							<div class="card h-100 border-<?php print($lcSalaEstilo); ?>">
								<div class="card-header bg-<?php print($lcSalaEstilo); ?>">
									<h5><?php printf('%s - <b>%s</b><sup><small>%s/%s</small></sup>', $laSala['SECHAB'], $laSala['NUMHAB'] ,$lnSala+1, count($laSalas)); ?></h5>
								</div>							
								<div class="card-body">
									<div class="media">
										<i class="far fa-hospital fa-4x mr-3"></i>
										<div class="media-body">
											<h5 class="mt-0">Estado</h5>
											<p>Datos</p>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php
								}
							}
							$lnBloqueInicio = ($lnBloqueInicio+$lnBloqueSalas<count($laSalas)?$lnBloqueInicio+$lnBloqueSalas:0);
						?>
					</div>
				</div>
			</div>
		</div>
		<div style="position: absolute; float:right; bottom: 0; right: 0; padding-right: 8px; padding-bottom: 8px;">
			<button id="cmdActualizar" class="btn btn-sm btn-outline-primary"><i class="fas fa-sync fa-spin"></i> <span class="messageActualizar"></span></button>
		</div>
		<script type="text/javascript" src="js/dashboard.js?nBloqueInicio=<?php print($lnBloqueInicio); ?>&nTimeMaxSeg=<?php print($lnTimeMaxSeg); ?>"></script>	
	</body>
</html>