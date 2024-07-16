<?php
	require_once (__DIR__ . '/../../controlador/class.AplicacionFunciones.php');
	use NUCLEO\AplicacionFunciones;

	if(isset($_SESSION[HCW_NAME])==true){
		if(isset($_SESSION[HCW_NAME]->oUsuario)==true){
			if($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){

				$lnStatus=0;
				$lcMIME = '';

				global $goDb;
				$loTabmae = $goDb->ObtenerTabMae('OP5TMA', 'FIRMADIG', ['CL1TMA'=>'RUTA','ESTTMA'=>'']);
				$lcSrv = trim(AplicacionFunciones::getValue($loTabmae, 'OP5TMA', ''));
				$laConfigServer = $goDb->configServer($lcSrv);
?>
	<div class="container-fluid">
		<div class="card mt-3">
			<div class="card-header">
				<h5>Su cuenta</h5>
				<div class="row">
					<div class="col">
						<div class="container">
							<div class="row">
								<div class="col-md-3 mb-3">
									<div class="card text-center">
										<div class="card-header">
											<div class="avatar pt-3 pb-3 d-none d-md-block">
												<?php
													$lcImgFoto = AplicacionFunciones::obtenerRemoto($_SESSION[HCW_NAME]->oUsuario->getFoto(),1,$lnStatus,$lcMIME,null,$laConfigServer['user'],$laConfigServer['pass']);
													print($lcImgFoto?$lcImgFoto:'<img src="publico-imagenes/avatar/defecto.png"  alt="avatar">');
												?>
											</div>
											</hr>
											<i class="fas fa-user"></i> <?php print($_SESSION[HCW_NAME]->oUsuario->getUsuario()); ?>
										</div>
										<div class="card-body"><i class="fas fa-at"></i> <?php print($_SESSION[HCW_NAME]->oUsuario->getEmail()); ?></div>
									</div>
								</div><!--/col-3-->

								<div class="col-md-9">
									<div class="card">
										<div class="card-header">Informaci&oacute;n general</div>
										<div class="card-body">
											Nombre completo
											<h6><?php print($_SESSION[HCW_NAME]->oUsuario->getNombreCompleto()); ?></h6>
											Identificaci&oacute;n
											<h6><?php print($_SESSION[HCW_NAME]->oUsuario->aTipoId["NOMBRE"]." - ".$_SESSION[HCW_NAME]->oUsuario->nId); ?></h6>
											Vigencia
											<h6><?php print(date("Y-m-d",strtotime($_SESSION[HCW_NAME]->oUsuario->getVigenciaIni()))." - ".date("Y-m-d",strtotime($_SESSION[HCW_NAME]->oUsuario->getVigenciaFin()))); ?></h6>
											jtm.shaio.org
											<h6>Anfitri&oacute;n <b><?php print($_SESSION[HCW_NAME]->oUsuario->getUsuario()); ?></b>, contrase&ntilde;a:<b><?php print($_SESSION[HCW_NAME]->oUsuario->getProsodyJtmPassword()); ?></b>, vigente durante:<b><?php print($_SESSION[HCW_NAME]->oUsuario->getProsodyJtmDiaVigenciaPassword()); ?></b></h6>													
											<hr/>
											<?php
												if(is_array($_SESSION[HCW_NAME]->oUsuario->getBodegas())){
													if(count($_SESSION[HCW_NAME]->oUsuario->getBodegas())>0){
											?>
											Bodegas autorizadas
											<div class="alert alert-dark" role="alert">
												<?php
													foreach($_SESSION[HCW_NAME]->oUsuario->getBodegas() as $lnBodega=>$laBodega){
														$loBodega=$laBodega['BODEGA'];
														$lcBodega = sprintf(($laBodega['DEFAULT']==true?'<b>%s</b>':'%s'),$loBodega->cNombre);
															printf('<span class="badge badge-primary"><b>%s</b> %s</span> ', $loBodega->cId, ucwords(trim(strtolower($lcBodega))));
													}
												?>
											</div>
											<?php
													}
												}

												if(is_object($_SESSION[HCW_NAME]->oUsuario->getBodega())){
											?>
											Bodega predeterminada
											<div class="alert alert-dark" role="alert"><h6><?php printf("<b>%s</b> - %s", ($_SESSION[HCW_NAME]->oUsuario->getBodega())->cId, ($_SESSION[HCW_NAME]->oUsuario->getBodega())->cNombre); ?></h6></div>
											<?php
												}

												if(is_object($_SESSION[HCW_NAME]->oUsuario->getCentroCosto())){
											?>
											Centro de Costos
											<div class="alert alert-dark" role="alert"><h6><?php printf("<b>%s</b> - %s", ($_SESSION[HCW_NAME]->oUsuario->getCentroCosto())->cId, ($_SESSION[HCW_NAME]->oUsuario->getCentroCosto())->cNombre); ?></h6></div>
											<?php
												}
											?>
											M&oacute;dulos a los que tiene acceso
											<div class="alert alert-dark" role="alert">
											<?php
												$laOpciones = explode("]",str_replace("-"," ",str_replace("[","",$_SESSION[HCW_NAME]->oUsuario->getOpcionesUsuario())));
												if(is_array($laOpciones)){
													foreach($laOpciones as $lcOpcion){
														printf('<span class="badge badge-primary">%s</span> ', ucwords(trim(str_replace("modulo","",strtolower($lcOpcion)))));
													}
												}
											?>
											</div>
											Entidades a las que tiene acceso
											<div class="alert alert-dark" role="alert">
											<?php
												$laEntidadesConsultaLibroHc = $_SESSION[HCW_NAME]->oUsuario->getEntidadesConsultaLibroHc();
												if(is_array($laEntidadesConsultaLibroHc)){
													if(count($laEntidadesConsultaLibroHc)>0){
														foreach($laEntidadesConsultaLibroHc as $laEntidadConsultaLibroHc){
															if($laEntidadConsultaLibroHc['NIT']==0 && $laEntidadConsultaLibroHc['PLAN']="*"){
																print('<span class="badge badge-primary">TODAS</span>');
															}else{
																printf('<span class="badge badge-primary">%s|%s|%s</span> ', $laEntidadConsultaLibroHc['NIT'], $laEntidadConsultaLibroHc['PLAN'], $laEntidadConsultaLibroHc['NOMBRE']);
															}
														}
													}else{
														print('<span class="badge badge-primary">NINGUNA</span>');
													}
												}
											?>
											</div>
											Firma
											<div class="alert alert-dark" role="alert">
											<?php
												$lcImgFirma = AplicacionFunciones::obtenerRemoto($_SESSION[HCW_NAME]->oUsuario->getFirma(),1,$lnStatus,$lcMIME,null,$laConfigServer['user'],$laConfigServer['pass']);
												print(!empty($lcImgFirma)?$lcImgFirma:'<b>No existe firma asociada</b>');
											?>
											</div>
										</div>
									</div>
								</div><!--/col-9-->
							</div><!--/row-->
						</div>
					</div>
				</div>
			</div>
			<div class="card-footer text-muted">
				<?php //var_dump($_SESSION[HCW_NAME]->oUsuario); ?>
			</div>
		</div>
	</div>
<?php
			}
		}
	}
?>