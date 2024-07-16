<?php
	require_once (__DIR__ .'/../../publico/constantes.php');
	require_once (__DIR__ .'/../../vista/autenticacion/validacion.php');
	require_once (__DIR__ .'/../../controlador/class.Nutricion.php');

	$llPagina=false;
	if(isset($_GET)){
		if(isset($_GET['p'])){
			$lcPagina = trim(strtolower($_GET['p']));
			switch ($lcPagina) {
				case "listanutricionpacientes":
					$lcPagina="listaNutricionPacientes";
					break;
					
				case "registronutricionpacientes":
					$lcPagina="registroNutricionPacientes";
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
		$lcTipoMenu = 'LISTADO'; // LISTADO / ICONOS
		$laOpcionesMenu =	[
								[
									'url' => 'modulo-nutricion&p=listaNutricionPacientes',
									'icono' => 'fas fa-utensils',
									'titulo' => 'Nutrición de Pacientes',
									'texto' => 'Permite editar la nutrción de cada paciente',
								],
								[
									'url' => 'modulo-nutricion&p=listaTamizajeNutricional',
									'icono' => 'fab fa-nutritionix',
									'titulo' => 'Tamizaje Nutricional',
									'texto' => 'Indentificaci&oacute;n de las caracter&iacute;sticas que est&aacute;n asociadas con problemas alimentarios o nutricionales.',
								]
							];
?>
<div class="container-fluid">
    <div class="card mt-3">
        <div class="card-header">
            <ul class="nav nav-tabs  card-header-tabs" id="hcwTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="alerta-tab" data-toggle="tab" href="#alerta" role="tab"
                        aria-controls="menu" aria-selected="true">Nutrici&oacute;n</a>
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
                            <i class="<?php echo $laOpcMenu['icono']; ?> align-self-center mr-3"></i>
                            <div class="media-body">
                                <b><?php echo $laOpcMenu['titulo']; ?></b><br /><?php echo $laOpcMenu['texto']; ?>
                            </div>
                        </div>
                    </a>
                    <?php		endforeach; ?>
                </div>
                <?php	} elseif ($lcTipoMenu=='ICONOS') { ?>
                <div class="row">
                    <?php		foreach($laOpcionesMenu as $laOpcMenu): ?>
                    <div class="col-4 menuOption">
                        <a href="<?php echo $laOpcMenu['url']; ?>" class="fa-stack fa-2x" data-tipo="pad"
                            alt="<?php echo $laOpcMenu['titulo']; ?>" data-toggle="tooltip" data-placement="right"
                            title="<?php echo $laOpcMenu['texto']; ?>"
                            data-original-title="<?php echo $laOpcMenu['texto']; ?>">
                            <i class="fas fa-circle fa-stack-2x menuColorAlto"></i>
                            <i
                                class="fas fa-<?php echo $laOpcMenu['icono']; ?> fa-stack-1x fa-inverse"></i></a><br><?php echo $laOpcMenu['titulo']; ?>
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