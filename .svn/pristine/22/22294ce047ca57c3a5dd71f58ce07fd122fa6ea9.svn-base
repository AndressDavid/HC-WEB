<?php
require_once __DIR__ .'/../../publico/constantes.php';

$lcFecha = date('YmdH');
$lcKeyMaskLoginFor = strtolower('0'.md5('LOGINFOR-'.$lcFecha).'x');
$lcKeyMaskUserName = strtolower('1'.md5('PASSWORD-'.$lcFecha).'y');
$lcKeyMaskPassword = strtolower('2'.md5('USERNAME-'.$lcFecha).'z');
$lcKeyMaskTipoUser = strtolower('3'.md5('USERTYPE-'.$lcFecha).'u');
$lcKeyMaskEspecCod = strtolower('4'.md5('USERESPC-'.$lcFecha).'e');


?>
<div class="container">
	<div class="row justify-content-center">
		<div class="col pt-2">
			<div class="form-bottom p-3">
				<p><small>Para continuar escriba su contraseña del aplicativo <b>&quot;Historia Clínica&quot;</b></small></p>
				<form role="form" class="login-form" id="<?php echo $lcKeyMaskLoginFor; ?>" name="<?php echo $lcKeyMaskLoginFor; ?>" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" autocomplete="off">
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label class="sr-only" for="<?php echo $lcKeyMaskUserName; ?>">Usuario</label>
								<input type="text" autocorrect="off" autocapitalize="off" autocomplete="<?php echo $lcKeyMaskUserName; ?>" spellcheck="false" id="<?php echo $lcKeyMaskUserName; ?>" name="<?php echo $lcKeyMaskUserName; ?>" placeholder="Usuario HC" class="form-username form-control form-control-lg" maxlength="10" readonly>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<label class="sr-only" for="<?php echo $lcKeyMaskPassword; ?>">Contraseña</label>
								<input type="password" autocorrect="off" autocapitalize="off" autocomplete="<?php echo $lcKeyMaskPassword; ?>" spellcheck="false" id="<?php echo $lcKeyMaskPassword; ?>" name="<?php echo $lcKeyMaskPassword; ?>" placeholder="Contrase&ntilde;a" class="form-password form-control form-control-lg">
								<input type="hidden" id="<?php echo $lcKeyMaskTipoUser; ?>" name="<?php echo $lcKeyMaskTipoUser; ?>" class="form-password form-control form-control-lg">
								<input type="hidden" id="<?php echo $lcKeyMaskEspecCod; ?>" name="<?php echo $lcKeyMaskEspecCod; ?>" class="form-password form-control form-control-lg">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<button type="submit" class="btn btn-danger w-100" id="btnSesionHcWeb"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Entrar</button>
						</div>
					</div>
				</form>
				<div class="row">
					<div class="col">
						<div id="divReportStatusSesionHcWeb"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	loUserHcWeb=getUsuarioSesionHcWeb();
	loUserHcWeb.usuario.trim();
	if(loUserHcWeb.usuario.length>0){
		$("#<?php echo $lcKeyMaskUserName; ?>").val(loUserHcWeb.usuario).attr("readonly",true);
	}else{
		$("#<?php echo $lcKeyMaskUserName; ?>").val("").attr("readonly",false);
	}
	$("#<?php echo $lcKeyMaskTipoUser; ?>").val(loUserHcWeb.tipo);
	$("#<?php echo $lcKeyMaskEspecCod; ?>").val(loUserHcWeb.especialidad);
	delete loUserHcWeb;
	$("#btnSesionHcWeb").on("click", function(e){
		e.preventDefault();
		aceptarSesionHcWeb($("#<?php echo $lcKeyMaskLoginFor; ?>"),$("#<?php echo $lcKeyMaskPassword; ?>"));
	});
	$("#<?php echo $lcKeyMaskPassword; ?>").focus();
</script>