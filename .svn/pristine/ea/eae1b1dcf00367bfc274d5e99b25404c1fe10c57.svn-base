<?php
	$llPagina=false;
	if(isset($_GET)){
		if(isset($_GET['p'])){
			$lcPagina = trim(strtolower($_GET['p']));
			switch ($lcPagina) {
				case "registroalerta":
					$lcPagina="registroAlerta";
					break;
					
				case "registroconsulta":
					$lcPagina="registroConsulta";
					break;
			}
			
			if(!empty($lcPagina)){
				$lcPagina = __DIR__ .'/'.$lcPagina.".php";
				if(is_file($lcPagina)){
					include($lcPagina);
					$llPagina=true;
				}
			}
		}
	}

	if($llPagina==false){

		$lcTipoMenu = 'ICONOS'; // LISTADO / ICONOS

		$laOpcionesMenu = [
			'Alerta' =>[
				'url' => 'modulo-alerta-temprana&p=registroAlerta',
				'icono' => 'mobile-alt',
				'titulo' => 'Registro de Acciones para Alertas Tempranas',
				'texto' => 'En esta opción se ingresan los integrantes del grupo de respuesta rápida que participaron en la atención y la Conducta a seguir con la alerta.',
			],
/*
			'Grafico' =>[
				'url' => 'modulo-alerta-temprana&p=registroGrafico',
				'icono' => 'heartbeat',
				'titulo' => 'Valoraciones de pacientes mediante el puntaje NEWS',
				'texto' => 'Muestra el gráfico de comportamiento de las valoraciones y puntajes NEWS en un lapso determinado.',
			],
*/
			'Consulta' =>[
				'url' => 'modulo-alerta-temprana&p=registroConsulta',
				'icono' => 'table',
				'titulo' => 'Consulta de valoraciones de signos vitales',
				'texto' => 'Consulta las valoraciones y puntaje NEWS para un paciente',
			],
			'Estudio' =>[
				'url' => 'modulo-alerta-temprana&p=consulta_estudio',
				'icono' => 'procedures',
				'titulo' => 'Consulta para Análisis',
				'texto' => 'Consulta en un rango de fechas los datos de pacientes, signos y registros de acción, para su estudio y análisis estadístico',
			],
		];

?>
<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<ul class="nav nav-tabs  card-header-tabs" id="hcwTab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="alerta-tab" data-toggle="tab" href="#alerta" role="tab" aria-controls="menu" aria-selected="true">Alertas tempranas (DSAT)</a>
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