<?php
	$lcEntorno = $goDb->obtenerEntorno();
	if(isset($_SESSION[HCW_NAME])==true){
		if(isset($_SESSION[HCW_NAME]->oUsuario)==true){
			require_once (__DIR__ .'/../../controlador/class.UsuarioRecordatorio.php');
			$loUsuarioRecordatorio = new NUCLEO\UsuarioRecordatorio($_SESSION[HCW_NAME]->oUsuario->getUsuario());
			$loUsuarioRecordatorio->cargar();			
?>
<nav class="navbar navbar-expand-lg <?php printf($lcEntorno=='desarrollo'?'navbar-light bg-warning':'navbar-dark bg-dark'); ?>">
	<div class="navbar-brand overflow-hidden">
		<img src="nucleo/publico/imagenes/logo/main-logo-mini.svg" alt="Logo"/> HC<sup>W</sup> | Historia Cl&iacute;nica Web
	</div>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	<div class="collapse navbar-collapse" id="navbarSupportedContent">
		<ul class="navbar-nav mr-auto">
			<?php
				$laEspecialidades =$_SESSION[HCW_NAME]->oUsuario->getEspecialidades();
								
				if(is_array($laEspecialidades)){
					if(count($laEspecialidades)>1){
						print('<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" id="navbarEspecialidad" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Especialidad</a><div class="dropdown-menu" aria-labelledby="navbarEspecialidad">');
						foreach($laEspecialidades as $lnEspecialidad => $laEspecialidad){
							$laEspecialidadFormat = ["link"=>"principal?cambioTipo=".$_SESSION[HCW_NAME]->oUsuario->encriptar(strval($laEspecialidad['TIPO']->nId))."&cambioEspecialidad=".$_SESSION[HCW_NAME]->oUsuario->encriptar(strval($laEspecialidad['ESPECIALIDAD']->cId)),
													 "especialidad" => $laEspecialidad['ESPECIALIDAD']->cNombre,
													 "tipo" => $laEspecialidad['TIPO']->cNombre,
													 "picture"=>''];
							
							if($laEspecialidad['TIPO']->nId==$_SESSION[HCW_NAME]->oUsuario->getTipoUsuario() && $laEspecialidad['ESPECIALIDAD']->nId==$_SESSION[HCW_NAME]->oUsuario->getEspecialidad()){
								$laEspecialidadFormat['especialidad'] = sprintf('<b>%s</b>',$laEspecialidadFormat['especialidad']);
								$laEspecialidadFormat['tipo'] = sprintf('<b>%s</b>',$laEspecialidadFormat['tipo']);
								$laEspecialidadFormat['picture'] = '<i class="fas fa-check pr-2"></i>';
							}
							
						printf('<a class="dropdown-item" href="%s" data-tipo="%s" data-especialidad="%s"><span>%s%s - %s</span></a>',$laEspecialidadFormat['link'],$laEspecialidad['TIPO']->nId,$laEspecialidad['ESPECIALIDAD']->cId,$laEspecialidadFormat['picture'],$laEspecialidadFormat['tipo'],$laEspecialidadFormat['especialidad']);
						}
						print('</div></li>');
					}
				}

				$laOpciones = $_SESSION[HCW_NAME]->oUsuario->getOpcionesMenu();
				$lcResultado = "";
				foreach($laOpciones as $laOpcion){
					if(is_array($laOpcion)==true){
						$lcResultado .= graficarOpcion($laOpcion);
					}
				}
				print($lcResultado);
			?>
		</ul>
	</div>
</nav>
<nav class="navbar navbar-light bg-light pt-1 pb-1">
	<div class="navbar-nav">
		<a class="btn btn-outline-secondary btn-sm" alt="Pagina principal" href="index" data-toggle="tooltip" data-placement="right" title="Ir a la pagina principal"><i class="fas fa-home" ></i> <?php print($_SESSION[HCW_NAME]->getServerName()); ?></a>
	</div>
	<div class="navbar-nav justify-content-end">
		<div class="btn-group" role="group" aria-label="Opciones de Usuario">
			<a type="button" class="btn btn-outline-secondary btn-sm" href="modulo-perfil"><i class="fas fa-user-circle"></i> <?php print($_SESSION[HCW_NAME]->oUsuario->getNombreCompleto()); ?></a>
			<?php if($loUsuarioRecordatorio->getCuentaRecordatorios()>=0){ ?>
			<a type="button" class="btn btn-outline-secondary btn-sm" href="#"><i class="fas fa-bell"></i> <?php print($loUsuarioRecordatorio->getCuentaRecordatorios()); ?></a>
			<?php } ?>
			<a type="button" class="btn btn-outline-secondary btn-sm" alt="Cerrar sesionn" href="salir"><i class="fas fa-power-off"></i> Cerrar sesi&oacute;n</a>
		</div>
	</div>
</nav>
<?php
			if(empty($lcMenuCambioPerfil)==false){
				?>
				<div class="alert alert-warning alert-dismissible fade show m-3" role="alert">					
					<?php print($lcMenuCambioPerfil); ?><br/>
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<?php
			}

			if($loUsuarioRecordatorio->getCuentaRecordatorios()>0){
				foreach($loUsuarioRecordatorio->getRecordatorios() as $laRecordatorio){
					?>
					<div class="alert alert-warning alert-dismissible fade show m-3" role="alert">					
						<strong><i class="fas fa-bell"></i> <?php print($laRecordatorio['TITULO']); ?></strong> <?php print($laRecordatorio['RECORDATORIO']); ?><a href="<?php print($laRecordatorio['LINK']); ?>" target="_blank"><?php print(trim($laRecordatorio['ALIAS'])); ?></a><br/>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<?php
				}
			}
		}
	}
	if ($lcEntorno=='desarrollo'){
		printf('<div id="inner-message" class="alert alert-danger" role="alert"><h6>Historia Clínica Web - Entorno de Pruebas - %s</h6></div>',isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:'Unkonow');
	}

	function graficarOpcion($laOpcion,$taAnterior=""){
		$lcResultado="";
		if(is_array($laOpcion)==true){
			if(isset($laOpcion["MENUTYPE"])==true && isset($laOpcion['MENUID'])==true && isset($laOpcion['PROMPT'])==true && isset($laOpcion['MENUID'])==true){
				switch(trim($laOpcion["MENUTYPE"])){
					case "pad":
						$lcResultado .= sprintf('<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" id="%s" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">%s</a><div class="dropdown-menu" aria-labelledby="%s">',trim($laOpcion['MENUID']),trim($laOpcion['PROMPT']),trim($laOpcion['MENUID']));
						foreach($laOpcion as $lvOpcion){
							if(is_array($lvOpcion)==true){
								$lcResultado .= graficarOpcion($lvOpcion,'pad');
							}
						}
						$lcResultado .= sprintf('</div></li>');
						break;

					case "popup":
						$lcResultado .= sprintf('<h6 class="dropdown-header">%s</h6>',trim($laOpcion['PROMPT']));
						foreach($laOpcion as $lvOpcion){
							if(is_array($lvOpcion)==true){
								$lcResultado .= graficarOpcion($lvOpcion,'popup');
							}
						}
						break;

					case "line":
						$lcResultado .= '<div class="dropdown-divider"></div>';
						break;

					default:
						$lcResultado .= sprintf('<a class="dropdown-item" href="%s" data-tipo="%s">%s</a>',strtolower(trim($laOpcion['CMD'])),$taAnterior,trim($laOpcion['PROMPT']));
						break;
				}
			}
		}
		return $lcResultado;
	}

?>