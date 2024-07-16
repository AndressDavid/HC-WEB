<?php
if(defined('HCW_NAME')){
	if(isset($_SESSION[HCW_NAME])){
			if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
				
				// Area del usuario
				$laSelectUsuarioAreaAux=explode('|',(isset($lcSelectUsuarioArea)?$lcSelectUsuarioArea:''));
				$laSelectUsuarioCargoAux[0]=(isset($laSelectUsuarioAreaAux[0])?$laSelectUsuarioAreaAux[0]:0);
				$laSelectUsuarioCargoAux[1]=(isset($laSelectUsuarioAreaAux[1])?$laSelectUsuarioAreaAux[1]:'');
				$laSelectUsuarioCargoAux[2]=(trim(strtolower(isset($laSelectUsuarioAreaAux[2])?$laSelectUsuarioAreaAux[2]:''))=='asistencial'?1001:1002);
				
				// Cargo del usuario
				$laSelectUsuarioCargo = explode('1',isset($lcSelectUsuarioCargo)?$lcSelectUsuarioCargo:'');
				
				// Precargado
				$loUsuarios->oCargos->cargar($laSelectUsuarioCargoAux[2],$laSelectUsuarioCargoAux[0]);
?>
<label for="usuarioCargo" class="required"><b>Cargo</b></label>
<select class="form-control form-control-sm" id="usuarioCargo" name="usuarioCargo" data-area="<?php print($laSelectUsuarioCargoAux[0]); ?>" data-tipo="<?php print($laSelectUsuarioCargoAux[2]); ?>">
	<option value=""></option>
	<?php
		foreach($loUsuarios->oCargos->aCargos as $lcCargo => $laCargo){
			$lcTipo=(intval($laCargo['TIPO'])==1001?"Asistencial":(intval($laCargo['TIPO'])==1002?"Administrativo":""));
			$lcCargo=sprintf("%s | %s | %s | %s",$laCargo['ID'],ucwords(strtolower($laCargo['NOMBRE'])),$lcTipo,ucwords(strtolower($laCargo['DEPARTAMENTO'])));
			$lcSelected=(count($laSelectUsuarioCargo)>0?(intval($laSelectUsuarioCargo[0])==intval($laCargo['ID'])?'selected':''):'');
			printf('<option value="%s" %s>%s</option>',$lcCargo,$lcSelected,$lcCargo);
		}
	?>
</select>
<?php 
			}
		}
	}
?>