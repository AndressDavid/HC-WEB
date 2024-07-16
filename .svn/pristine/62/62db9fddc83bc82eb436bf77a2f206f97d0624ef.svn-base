<?php
if(defined('HCW_NAME')){
	if(isset($_SESSION[HCW_NAME])){
			if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
?>
<label for="especialidad" class="required"><b>Especialidad</b></label>
<div id="especialidad" class="input-group mb-3">
	<select class="custom-select custom-select-sm" id="especialidad" name="especialidad">
		<?php
			$laSelectEspecialidadesAux = (new NUCLEO\Especialidades('DESESP',(isset($lcSelectEspecialidadActivos)?$lcSelectEspecialidadActivos:false)))->aEspecialidades;
			
			foreach($laSelectEspecialidadesAux as $lcSelectedEspecialidadId => $lcSelectEspecialidadNombre){
				$lcSelected = (isset($lcSelectEspecialidad)?
								trim($lcSelectEspecialidad)==trim($lcSelectEspecialidadNombre)?
									'selected':'':'');
				printf('<option value="%s" %s>%s</option>',$lcSelectedEspecialidadId,$lcSelected,$lcSelectEspecialidadNombre);
			}
		?>
	</select>
</div>
<?php 
			}
		}
	}
?>