<?php
	require_once (__DIR__ .'/../../publico/constantes.php') ;
	require_once (__DIR__ . '/../../controlador/class.AplicacionFunciones.php');
	require_once (__DIR__ . '/../../controlador/class.SalasCirugia.php');
	require_once __DIR__ .'/../../controlador/class.AgendaSalasCirugia.php';
	$loAgenda = new NUCLEO\AgendaSalasCirugia();
	$laPermisos = $loAgenda->permisoRegistrar($_SESSION[HCW_NAME]->oUsuario->getUsuario());
	$lcPermiso = '';
	
	if(isset($laPermisos)){
		if(is_array($laPermisos) == true){
			foreach($laPermisos as $laDato){
				$lcPermiso= $laDato;
			}
		}	
	}
	
	$llPagina=false;
	$lcPagina = '';
	
	if(isset($_GET)){
		if(isset($_GET['q'])){
			include __DIR__ . '/../comun/modalEspera.php';
			
			$lcPagina = trim(strtolower($_GET['q']));
			switch ($lcPagina) {
				case "programacionsalas":
					$laFiltro = ['sala' => $_POST['sala']??'', 'fecini' => $_POST['fecini']??date("Y-m-d"), 'fecfin' => $_POST['fecfin']??date("Y-m-d")];
					$lcPagina="programacionSalas";
					break;
					
				case "reporte":
					$loIntervalo = new \DateInterval('P1D');
					$loIntervalo->invert = 1;
					$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
					$ltAyer = new \DateTime( $goDb->fechaHoraSistema() );
					$ltAyer->add($loIntervalo);
					$ldFechaInicio = $ltAyer->format("Y-m-d");
					$ldFechaFin = $ltAhora->format("Y-m-d");
					$loListaSalas = (new NUCLEO\SalasCirugia())->aSalasCirugia;				
					$lcPagina="reporte";
					$lcRepPrefijoAjax='vista-programacion-salas/';				
					$lcRepPrefijoComplementos='publico-complementos/';
					$lcRepPrefijoComponentes='vista-programacion-salas/';
					break;
					
				case "datoscirugia":
					$lcPagina="datosCirugia";
					break;
			}
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
		$lcTipoMenu = 'ICONOS'; // LISTADO / ICONOS

		if($lcPermiso == 'N'){
			$laOpcionesMenu = [
				'programacion' =>[
					'url' => 'modulo-programacion-salas&q=programacionSalas',
					'icono' => 'procedures',
					'titulo' => 'Registro en la Agenda de Salas cirug&iacute;a',
					'texto' => 'En esta opción se ingresan los registro en la Agenda de Salas Cirug&iacute;a.',
				],

				'reporte' =>[
					'url' => 'modulo-programacion-salas&q=reporte',
					'icono' => 'table',
					'titulo' => 'Consulta de programaci&oacute;n de salas',
					'texto' => 'Consulta la programaci&oacute;n de salas',
				],
			];
		}else{
			$laOpcionesMenu = [
				'reporte' =>[
					'url' => 'modulo-programacion-salas&q=reporte',
					'icono' => 'table',
					'titulo' => 'Consulta de programaci&oacute;n de salas',
					'texto' => 'Consulta la programaci&oacute;n de salas',
				],
			];
		}

?>
<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<ul class="nav nav-tabs card-header-tabs" id="hcwTab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="alerta-tab" data-toggle="tab" href="#alerta" role="tab" aria-controls="menu" aria-selected="true">Programaci&oacute;n de Salas</a>
				</li>
			</ul>
		</div>
		<div class="tab-content card-body" id="hcwTabContent">
			<div class="tab-pane fade show active" id="alerta" role="tabpanel" aria-labelledby="alerta">
<?php	if ($lcTipoMenu=='LISTADO'){ ?>
				<div class="list-group">
<?php		foreach($laOpcionesMenu as $laOpcMenu): ?>
					<a href="<?php echo $laOpcMenu['url']; ?>" class="list-group-item list-group-item-action border-0">
						<div class="media">
							<i class="fas fa-notes-medical align-self-center mr-3"></i>
							<div class="media-body">
								<b><?php echo $laOpcMenu['titulo']; ?></b><br/><?php echo $laOpcMenu['texto']; ?>
							</div>
						</div>
					</a>
<?php		endforeach; ?>
				</div>
<?php	} elseif ($lcTipoMenu=='ICONOS') { ?>
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
		} //endif;
	} //endif;
?>
			</div>
		</div>
	</div>
</div>