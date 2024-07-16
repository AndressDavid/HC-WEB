<?php
if(defined('HCW_NAME')){
	if(isset($_SESSION[HCW_NAME])){
			if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
?>
<label for="usuarioEstado" class="required"><b>Estado</b></label>
<select class="form-control form-control-sm" id="usuarioEstado" name="usuarioEstado">
	<?php
		foreach($loUsuarios->oEstados->aEstados as $lcEstado => $laEstado){
			$lcSelected=(intval($lcSelectUsuarioEstado)==intval($lcEstado)?'selected':'');
			printf('<option value="%s" %s>%s</option>',trim($lcEstado),$lcSelected,$laEstado['DESESU']);
		}
	?>
</select>
<?php 
			}
		}
	}
?>