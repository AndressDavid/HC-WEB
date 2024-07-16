<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
<?php
	if(isset($_SESSION[HCW_NAME])==true){
		if(isset($_SESSION[HCW_NAME]->oUsuario)==true){
			$laBarras = $_SESSION[HCW_NAME]->oUsuario->getOpcionesBarra();
			if(is_array($laBarras)==true){
?>
			<ul class="nav nav-tabs  card-header-tabs" id="hcwTab" role="tablist">
<?php
				$lnItem=0;
				foreach($laBarras as $lcBarra=>$laBarra){
					$lnItem+=1;
?>
				<li class="nav-item">
					<a class="nav-link<?php print($lnItem==1?' active':''); ?>" id="<?php print(trim($lcBarra)); ?>-tab" data-toggle="tab" href="#<?php print(trim($lcBarra)); ?>" role="tab" aria-controls="<?php print($lcBarra); ?>" aria-selected="true"><?php print($laBarra["NOMBRE"]); ?></a>
				</li>
<?php
				}
?>
			</ul>
		</div>
		<div class="tab-content card-body" id="hcwTabContent">
<?php
				$lnItem=0;
				foreach($laBarras as $lcBarra=>$laBarra){
					$lnItem+=1;
?>
			<div class="tab-pane fade<?php print($lnItem==1?' show active':''); ?>" id="<?php print(trim($lcBarra)); ?>" role="tabpanel" aria-labelledby="<?php print($lcBarra); ?>">
				<div class="list-group">
<?php
					foreach($laBarra["OPCIONES"] as $laOpcion){
						$lcMensaje=(!empty(trim($laOpcion["MESSAGE"]))?sprintf("<br/>%s",trim($laOpcion["MESSAGE"])):"");
						printf('<a href="%s" aria-label="%s" class="list-group-item list-group-item-action border-0"><div class="media"><i class="fas fa-notes-medical align-self-center mr-3"></i><div class="media-body"><b>%s</b>%s</div></div></a>',strtolower(trim($laOpcion["CMD"])),trim($laOpcion["PROMPT"]),trim($laOpcion["PROMPT"]),$lcMensaje);
					}
?>
				</div>
			</div>
<?php
				}
?>
		</div>
<?php
			}
		}
	}
?>
	</div>
</div>