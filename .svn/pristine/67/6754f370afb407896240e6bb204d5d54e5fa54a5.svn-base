<?php
	$lcEntorno = $goDb->obtenerEntorno();
	
	$lcKeyChngPsswdFor = strtolower('0'.md5('CHGPSSW-'.date('YmdH')).'x');
	$lcKeyChngPsswdCurrent = strtolower('1'.md5('PASSWORC-'.date('YmdH')).'y');
	$lcKeyChngPsswdNew = strtolower('2'.md5('PASSWORN-'.date('YmdH')).'z');
	$lcKeyChngPsswdNewConfirm = strtolower('3'.md5('PASSWORK-'.date('YmdH')).'w'); 	
	$lcMensaje = (isset($lcMensajeCambioContrasena) ? $lcMensajeCambioContrasena : '');
	
	if(isset($_SESSION[HCW_NAME])==true){
		if(isset($_SESSION[HCW_NAME]->oUsuario)==true){	
?>
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6 pt-5">
					<?php
						if ($lcEntorno=='desarrollo'){
							printf('<div id="inner-message" class="alert alert-danger" role="alert"><h6>Historia Clínica Web - Entorno de Pruebas - %s</h6></div>',isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:'Unkonow');
						}
					?>
					<div class="form-top p-3<?php printf($lcEntorno=='desarrollo'?' bg-warning':''); ?>">
						<div class="form-top-left">
							<h1>HC<sup>W</sup></h1>
							<p><b>Cambio de contrase&ntilde;a</b><br/>Condiciones que deben cumplir todas las contrase&ntilde;as para su asignación o cambio</p>
						</div>
					</div>
					<div class="form-bottom p-3">
						
						<form role="form" class="login-form" id="<?php print($lcKeyChngPsswdFor); ?>" name="<?php print($lcKeyChngPsswdFor); ?>" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" autocomplete="off">
							
							<div class="row">
								<div class="col-md-12">
									<label>Nombre completo</label>
									<h6><?php print($_SESSION[HCW_NAME]->oUsuario->getNombreCompleto()); ?></h6>
								</div>
							</div>
							
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="<?php print($lcKeyChngPsswdCurrent); ?>">Contraseña actual<br/><small>Ingrese la contrase&ntilde;a actual</small></label>
										<input type="password" autocorrect="off" autocapitalize="off" autocomplete="<?php print($lcKeyChngPsswdCurrent); ?>" spellcheck="false" id="<?php print($lcKeyChngPsswdCurrent); ?>" name="<?php print($lcKeyChngPsswdCurrent); ?>" class="form-username form-control form-control-lg">
									</div>
								</div>
							</div>
							
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="<?php print($lcKeyChngPsswdNew); ?>">Contraseña nueva<br/><small>Ingrese la nueva contrase&ntilde;a</small></label>
										<input type="password" autocorrect="off" autocapitalize="off" autocomplete="<?php print($lcKeyChngPsswdNew); ?>" spellcheck="false" id="<?php print($lcKeyChngPsswdNew); ?>" name="<?php print($lcKeyChngPsswdNew); ?>" class="form-password form-control form-control-lg">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="<?php print($lcKeyChngPsswdNewConfirm); ?>">Confirmación<br/><small>Repita la nueva contrase&ntilde;a</small></label>
										<input type="password" autocorrect="off" autocapitalize="off" autocomplete="<?php print($lcKeyChngPsswdNewConfirm); ?>" spellcheck="false" id="<?php print($lcKeyChngPsswdNewConfirm); ?>" name="<?php print($lcKeyChngPsswdNewConfirm); ?>" class="form-password form-control form-control-lg">
									</div>
								</div>

							</div>
							<div class="row">
								<div class="col-12">
									<label for="<?php print($lcKeyChngPsswdNew); ?>">La nueva contrase&ntilde;a debe cumplir con los siguientes requisitos</label>
									<small><ol>
									<?php
										foreach($_SESSION[HCW_NAME]->oUsuario->getPwdReq() as $lcReq => $laReq){
											if(!empty($laReq['value'])){
												printf('<li id="%s" class="valid-data-req" data-name="%s" data-value="%s">%s: %s</li>', $lcReq, $lcReq, $laReq['value'], $laReq['name'], $laReq['value']);
											}
										}
									?>
									</ol></small>
								</div>
							</div>
							
										
							<div class="row">
								<div class="col-12 col-md-8">
									<button type="submit" class="btn btn-success w-100"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Cambiar contrase&ntilde;a</button>
								</div>
								<div class="col-12 col-md-4">
									<a type="button" class="btn btn-outline-secondary w-100" alt="Salir" href="salir"><i class="fas fa-power-off"></i> Salir</a>
								</div>
							</div>
						</form>

						
						
						<div class="row">
							<div class="col">
								<div id="report-status" name="report-status"><?php print(!empty($lcMensaje)?sprintf('<hr/><div class="alert %s" role="alert">%s</div>',$lcMensajeClass,$lcMensaje):''); ?></div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>


		<!-- Opciones para el login -->
		<script src="vista-autenticacion/js/jquery.backstretch.js"></script>
		<script>
			$.ajaxSetup({
				cache: false
			});

			$(document).ready(function() {
				
				$.backstretch("vista-autenticacion/imagenes/background/background.jpg");
				$('#<?php print($lcKeyChngPsswdFor); ?> input[type="text"]').on('focus', function() {
					$(this).removeClass('input-error');
				});
				
				$('#<?php print($lcKeyChngPsswdFor); ?>').on('submit', function(e) {

					let cStatus = "";
					let cCheckPsw = $('#<?php print($lcKeyChngPsswdNew); ?>').val();
					let laChars = {'M':0, 'm':0, 'N':0, 'e':0};
					let lErrCheckPsw = false;
					
					$(this).find('input[type="text"], input[type="password"]').each(function(){
						if( $(this).val() == "" ) {
							e.preventDefault();
							$(this).addClass('input-error');
							lErrCheckPsw = true;
						}
						else {
							$(this).removeClass('input-error');
							lErrCheckPsw = false;
						}
					});
					
					if(lErrCheckPsw==false){
						if($('#<?php print($lcKeyChngPsswdNew); ?>').val() == $('#<?php print($lcKeyChngPsswdNewConfirm); ?>').val()){
							for (var i = 0; i< cCheckPsw.length; i++) {
								var caracter = cCheckPsw.codePointAt(i);
								if(caracter>=65 && caracter<=90){
									laChars['M']+=1;
								}else if(caracter>=97 && caracter<=122){
									laChars['m']+=1;
								}else if(caracter>=48 && caracter<=57){
									laChars['N']+=1;	
								}else{
									laChars['e']+=1;	
								}
							}
									
							
							$(this).find('.valid-data-req').each(function(){
								let cReqValue = $(this).data('value');
								switch($(this).data('name')) {
									case 'nPswdLargoMinimo':
										if(cCheckPsw.length < parseInt(cReqValue)){
											cStatus += "La contraseña debe tener al menos "+ cReqValue + " caracteres. ";
											$('#'+$(this).data('name')).addClass('text-danger');
										}else{
											$('#'+$(this).data('name')).removeClass('text-danger');
										}
										break;
										
									case 'nPswdLargoMaximo':
										if(cCheckPsw.length > parseInt(cReqValue)){
											cStatus += "La contraseña debe tener maximo "+ cReqValue + " caracteres";
											$('#'+$(this).data('name')).addClass('text-danger');
										}else{
											$('#'+$(this).data('name')).removeClass('text-danger');
										}
										break;
										
									case 'nPswdMinNumeros':
										if (laChars['N'] < parseInt(cReqValue)) {
											cStatus += "La contraseña debe tener mínimo "+ cReqValue + " digito(s). ";
											$('#'+$(this).data('name')).addClass('text-danger');
										}else{
											$('#'+$(this).data('name')).removeClass('text-danger');
										}
										break;
										
									case 'nPswdMinEspeciales':
										if (laChars['e'] < parseInt(cReqValue)) {
											cStatus += "La contraseña debe tener mínimo "+ cReqValue + " caracter(es) especial(es). ";
											$('#'+$(this).data('name')).addClass('text-danger');
										}else{
											$('#'+$(this).data('name')).removeClass('text-danger');
										}
										break;	
								}
									
							});
						}else{
							cStatus += "La contraseña nueva debe ser igual a la confirmación. ";
						}
						
						if (cStatus !== ""){
							e.preventDefault();
							alert(cStatus);
						}					
					}
				});

				$("#<?php print($lcKeyChngPsswdCurrent); ?>").focus();


			});
		</script>

<?php
		}
	}
?>