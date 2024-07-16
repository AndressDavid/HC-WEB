<?php
	if(isset($_SESSION[HCW_NAME])==true){
		if(isset($_SESSION[HCW_NAME]->oUsuario)==true){
			if($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){

			/*session.gc_maxlifetime
			Este valor (predeterminado 1440 segundos) define cuánto tiempo se mantendrá viva una sesión PHP no utilizada. Por ejemplo: un usuario inicia sesión, navega por su aplicación o sitio web, 
			durante horas, durante días. No hay problema. Siempre que el tiempo entre sus clics nunca exceda los 1440 segundos. Es un valor de tiempo de espera. El recolector de basura de sesión de 
			PHP se ejecuta con una probabilidad definida por session.gc_probability dividida por session.gc_divisor . De forma predeterminada, es 1/100, lo que significa que el valor de tiempo de 
			espera anterior se verifica con una probabilidad de 1 en 100*/
?>
<div id="warningSession" class="alert alert-danger fixed-top m-3 d-none text-center" data-maxlifetime="<?php print(intval(ini_get("session.gc_maxlifetime"))); ?>" data-lifetime="0" data-lifetimestatus="0">Atenci&oacute;n, su sesi&oacute;n a caducado hace <span id="warningSessionBody"></span>, Presione a la vez las teclas <kbd>CTRL</kbd> y <kbd>F5</kbd> en su teclado. Para ir al inicio haga <a alt="Inicio" href="index" class="text-danger font-weight-bold" data-toggle="tooltip" data-placement="right" title="Ir al Inicio">clic Aqu&iacute;</a></div>
<?php
			}else{
				print('<!-- Sesión sin iniciar -->');
			}
		}
	}
?>
<script>
	$(function () { 
		$('[data-toggle="tooltip"]').tooltip(); 
		$('form').attr('autocomplete', 'off'); 	

		setPageMaxLifeTime();

		setInterval(function(){
			setPageMaxLifeTime();
		}, 1000);


		function setPageMaxLifeTime(){
			if($("#warningSession").length){
				var lnLifeTimeStatus = parseInt($('#warningSession').data("lifetimestatus"));
				var lnMaxLifeTime = parseInt($('#warningSession').data("maxlifetime"));
				
				if(lnMaxLifeTime>0){
					var lnLifeTime = parseInt($('#warningSession').data("lifetime"));
					var lnExpiro = parseInt(lnLifeTime/60);
					
					$('#warningSession').data("lifetime",lnLifeTime+1);
					if(lnMaxLifeTime<=lnLifeTime){
						$('#warningSession').data("lifetimestatus",1);
						$('#warningSession').removeClass("d-none").addClass("d-block");
						$('#warningSessionBody').html(lnExpiro+" minutos");
					}
				}
			}
		}
	
	});
	
	/*if ('serviceWorker' in navigator) {
	  navigator.serviceWorker.register('publico-js/hcw-service-worker.js');
	}*/	
</script>