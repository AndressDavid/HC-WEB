<?php
if(defined('HCW_NAME')){
	if(isset($_SESSION[HCW_NAME])){
			if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
?>
<label for="documentoNumero" class="required"><b>Documento</b></label>
<div id="documento" class="input-group mb-3">
	<select class="custom-select custom-select-sm col-6" id="documentoTipo" name="documentoTipo" <?php print(isset($laSelectTiposDocumento['TIPO'])?(!empty($laSelectTiposDocumento['TIPO'])?'disabled':''):''); ?>>
		<?php
			$laSelectTiposDocumentoAux = (new NUCLEO\TiposDocumento())->aTipos;
			
			foreach($laSelectTiposDocumentoAux as $lcSelectedTipoDocumento => $laSelectTipoDocumento){
				$lcSelected = (isset($laSelectTiposDocumento['TIPO'])?
								trim($laSelectTiposDocumento['TIPO'])==trim($lcSelectedTipoDocumento)?
									'selected':'':'');
				
				printf('<option value="%s" %s>%s - %s</option>',$lcSelectedTipoDocumento,$lcSelected,$laSelectTipoDocumento['ABRV'],$laSelectTipoDocumento['NOMBRE']);
			}
		?>
	</select>
	<input type="text" id="documentoNumero" name="documentoNumero" class="form-control form-control-sm col-6" <?php print(isset($laSelectTiposDocumento['NUMERO'])?(!empty($laSelectTiposDocumento['NUMERO'])?'disabled':''):''); ?> value="<?php print(isset($laSelectTiposDocumento['NUMERO'])?$laSelectTiposDocumento['NUMERO']:''); ?>">
</div>
<?php 
			}
		}
	}
?>