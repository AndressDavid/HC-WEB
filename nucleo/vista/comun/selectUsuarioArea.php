<?php
if(defined('HCW_NAME')){
	if(isset($_SESSION[HCW_NAME])){
			if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
				$laSelectUsuarioArea = explode('|',(isset($lcSelectUsuarioArea)?$lcSelectUsuarioArea:''));
?>
<label for="usuarioArea" class="required"><b>&Aacute;rea</b></label>
<select class="form-control form-control-sm" id="usuarioArea" name="usuarioArea">
	<?php
		foreach($loUsuarios->oAreas->aAreas as $lcArea => $laArea){
			$lcTipo=(intval($laArea['TIPO'])==1001?"Asistencial":(intval($laArea['TIPO'])==1002?"Administrativo":""));
			$lcArea=sprintf("%s | %s | %s",$laArea['ID'],ucwords(strtolower(htmlentities(htmlspecialchars($laArea['NOMBRE'])))),$lcTipo);
			$lcSelected=(count($laSelectUsuarioArea)>0?(intval($laSelectUsuarioArea[0])==intval($laArea['ID'])?'selected':''):'');
			printf('<option data-id="%s" data-tipo="%s" value="%s" %s>%s</option>',$laArea['ID'],intval($laArea['TIPO']),$lcArea,$lcSelected,$lcArea);
		}
	?>
</select>
<?php 
			}
		}
	}
?>