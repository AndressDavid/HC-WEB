<?php
	require_once (__DIR__ .'/../../publico/constantes.php') ;
	require_once (__DIR__ . '/../../controlador/class.AplicacionFunciones.php');
	
	$llPagina=false;
	$lcPagina = '';
	$lcTipo='urg';
	$lnIngreso='';
	$loIntervalo = new \DateInterval('P1D');
	$loIntervalo->invert = 1;
	$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
	$ldFechaInicio = $ltAhora->format("Y-m-d");
	$lnNumDiasAnterior = intval(trim($goDb->obtenerTabMae1('DE2TMA', 'CENPAC', "CL1TMA='DIASANT ' AND ESTTMA=''", null, 8)));
	$ldFechaInicio = date("Y-m-d",strtotime($ldFechaInicio."- $lnNumDiasAnterior days")); 
	$ldFechaFin = $ltAhora->format("Y-m-d");
	
	if(isset($_GET)){
		if(isset($_GET['q'])){
			$lcPagina = trim(strtolower($_GET['q']));
			$lcTipo= trim(strtolower($_GET['tcen']??'urg'));
			$lnIngreso= $_GET['tingreso']??'';
		}
	}


	
	
	if(!empty($lcPagina)){
		$lcPagina = __DIR__ .'/'.$lcPagina.".php";
		if(is_file($lcPagina)){
			include($lcPagina);
			$llPagina=true;
		}
	}	

	if($llPagina==false){
		$laOpcionesMenu = [
			'urgencias' =>[
				'url' => "modulo-censo-pacientes&q=censo&tcen=urg&tingreso=".$lnIngreso,
				'icono' => 'procedures',
				'titulo' => 'Censo urgencias',
				'texto' => 'Consulta y registro censo urgencias.',
			],

			'hospitalizacion' =>[
				'url' => 'modulo-censo-pacientes&q=censo&tcen=hos',
				'icono' => 'table',
				'titulo' => 'Censo hospitalización',
				'texto' => 'Consulta y registro censo hospitalización',
			],
		];
?>

<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<ul class="nav nav-tabs card-header-tabs" id="hcwTab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="alerta-tab" data-toggle="tab" href="#alerta" role="tab" aria-controls="menu" aria-selected="true">Censo pacientes</a>
				</li>
			</ul>
		</div>
		<div class="tab-content card-body" id="hcwTabContent">
			<div class="tab-pane fade show active" id="alerta" role="tabpanel" aria-labelledby="alerta">
				<div class="row">
				<?php		foreach($laOpcionesMenu as $laOpcMenu): ?>
									<div class="col-4 menuOption">
										<a href="<?php echo $laOpcMenu['url']; ?>" class="fa-stack fa-2x" data-tipo="pad" alt="<?php echo $laOpcMenu['titulo']; ?>" data-toggle="tooltip" data-placement="right" title="<?php echo $laOpcMenu['texto']; ?>" data-original-title="<?php echo $laOpcMenu['texto']; ?>">
										<i class="fas fa-circle fa-stack-2x menuColorAlto"></i>
										<i class="fas fa-<?php echo $laOpcMenu['icono']; ?> fa-stack-1x fa-inverse"></i></a><br><?php echo $laOpcMenu['titulo']; ?>
									</div>
				<?php		endforeach; ?>
				</div>
<?php
		
	}
?>
			</div>
		</div>
	</div>
</div>