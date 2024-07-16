<?php
if(defined('HCW_NAME')){
	if(isset($_SESSION[HCW_NAME])){
			if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
?>
<label for="usuarioDepartamento" class="required"><b>Departamento</b></label>
<select class="form-control form-control-sm" id="usuarioDepartamento" name="usuarioDepartamento">
	<?php
		foreach($loUsuarios->oDepartamentos->aDepartamentos as $lcDepartamento => $laDepartamento){
			$lcSelected=(trim($lcSelectUsuarioDepartamento)==trim($lcDepartamento)?'selected':'');
			printf('<option value="%s" %s>%s</option>',trim($lcDepartamento),$lcSelected,$laDepartamento['NOMBRE']);
		}
	?>
</select>
<?php 
			}
		}
	}
?>