<?php
$glMenuIconStyle = 'TABS';

function menuFluido($taOpciones){
	$lcMenuFluido = '';
	foreach($taOpciones as $laBloque){
		$lcMenuFluido .= sprintf("<h5>%s</h5>",$laBloque['PROMPT']);
		$lcMenuFluido .= "<div class='row'>";
		foreach($laBloque as $laOpcion){
			$lcMenuFluido .= listarOpciones($laOpcion);
		}
		$lcMenuFluido .= "</div>";
	}
	return $lcMenuFluido;
}

function tabId($taOpcion){
	$lcTabId = '';
	if(is_array($taOpcion)){
		foreach(['APPID', 'MENU', 'MENUID'] as $laOpcion){
			$lcTabId .= (isset($taOpcion[$laOpcion])?ucfirst(strtolower(trim($taOpcion[$laOpcion]))):'');
		}
	}
	return $lcTabId;
}

function menuTabs($taOpciones){
	$lcMenuTabs = '';
	$lnBloque = 0;
	foreach($taOpciones as $laBloque){
		$lcBloqueId = tabId($laBloque);
		$lcBloqueActive = ($lnBloque==0?' active':'');
		$lcBloqueSelect = ($lnBloque==0?'true':'false');
		
		$lcMenuTabs .= sprintf('<li class="nav-item" role="presentation"><a class="nav-link%s" id="tabPag-%s" data-toggle="tab" href="#pag-%s" role="tab" aria-controls="pag-%s" aria-selected="%s">%s</a></li>',$lcBloqueActive,$lcBloqueId,$lcBloqueId,$lcBloqueId,$lcBloqueSelect,$laBloque['PROMPT']);
		$lnBloque += 1;
	}
	return $lcMenuTabs;
}

function menuTabPanels($taOpciones){
	$lcMenuTabPanels = '';
	$lnPanel = 0;
	foreach($taOpciones as $laPanel){
		$lcPanelId = tabId($laPanel);
		$lcPanelActive = ($lnPanel==0?'show active':'');
		$lcMenuTabPanels .= sprintf('<div class="tab-pane fade %s" id="pag-%s" role="tabpanel" aria-labelledby="tabPag-%s"><div class="row">',$lcPanelActive,$lcPanelId,$lcPanelId);

		foreach($laPanel as $laOpcion){
			$lcMenuTabPanels .= listarOpciones($laOpcion);
		}
		$lcMenuTabPanels .= "</div></div>";
		
		$lnPanel += 1;
	}
	return $lcMenuTabPanels;
}

function listarOpciones($taOpcion, $taAnterior=""){
	$lcCol = 'col-sm-6 col-md-4 col-lg-3 col-xl-2';
	$lcListaOpciones = '';
	if (is_array($taOpcion)==true){
		if (isset($taOpcion["MENUTYPE"]) && isset($taOpcion['MENUID']) && isset($taOpcion['PROMPT']) && isset($taOpcion['MENUID'])) {
			switch (trim($taOpcion["MENUTYPE"])) {
				case 'pad':
				case 'popup':
					foreach($taOpcion as $lvOpcion){
						if(is_array($lvOpcion))
							$lcListaOpciones .= listarOpciones($lvOpcion, 'pad');
					}
					break;

				case 'line':
					//
					break;

				default:
					$lcIcon = PerformanceIcon($taOpcion['PICTURE']);
					$lcCode = '<div class="%s menuOption">'
							. '<a href="%s" class="fa-stack fa-2x" aria-label="%s" data-tipo="%s" alt="%s">'
							. '<i class="fas fa-circle fa-stack-2x menuColorAlto"></i>'
							. '<i class="fas fa-%s fa-stack-1x fa-inverse"></i></a><br>%s</div>' . PHP_EOL;


					$lcListaOpciones .= sprintf($lcCode,
							$lcCol,
							strtolower(trim($taOpcion['CMD'])),
							trim($taOpcion['PROMPT']),
							$taAnterior,
							trim($taOpcion['PROMPT']),
							$lcIcon,
							trim($taOpcion['PROMPT']));

					break;
			}
		}
	}
	return $lcListaOpciones;
}

function PerformanceIcon($tcIcon = ""){
	$tcIcon = trim($tcIcon);
	if(!empty($tcIcon)){
		$tcIcon = str_replace("\\","/",str_replace('"', "", $tcIcon));
		$laIcon = pathinfo($tcIcon);
		$tcIcon = strtolower($laIcon['filename']);
	}else{
		$tcIcon = "window-maximize";
	}

	return $tcIcon;
}

if($glMenuIconStyle=='TABS'){
?>
<div class="container-fluid">
	<div id="divCard" class="card mt-3">
		<div class="card-header">
			<ul class="nav nav-pills card-header-pills font-weight-bolder" id="tabsMenuIcon" role="tablist">
				<?php print(menuTabs($_SESSION[HCW_NAME]->oUsuario->getOpcionesMenu())); ?>
			</ul>
		</div>
		<div id ="divLstDocumentos" class="card-body">
			<div class="tab-content">
				<?php print(menuTabPanels($_SESSION[HCW_NAME]->oUsuario->getOpcionesMenu())); ?>
			</div>
		</div>
	</div>
</div>

<?php
}else{
?>
<div class="container-fluid">
	<div id="divCard" class="card mt-3">
		<div id ="divLstDocumentos" class="card-body menuIcon">
			<?php print(menuFluido($_SESSION[HCW_NAME]->oUsuario->getOpcionesMenu())); ?>
		</div>
	</div>
</div>
<?php } ?>

