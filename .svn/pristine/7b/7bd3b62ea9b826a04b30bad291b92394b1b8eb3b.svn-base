<?php
	require_once (__DIR__ .'/../../publico/constantes.php');
	require_once (__DIR__ .'/../../controlador/class.AplicacionLogsManejador.php') ;
		
	$loAplicacionLogsManejador = new NUCLEO\AplicacionLogsManejador();	
?>
<link rel="stylesheet" href="vista-logs/css/style.css">
<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<h5>Administración de Logs</h5>
		</div>
		<div class="card-body">
			<?php if($loAplicacionLogsManejador->getCountLogs()>0){ ?>
				<div class="drive-wrapper drive-grid-view">
					<div class="grid-items-wrapper">
						<?php foreach($loAplicacionLogsManejador->getLogs() as $laLog){ ?>
						<div class="drive-item module text-center">
							<div class="drive-item-inner module-inner">
								<div class="font-weight-bolder text-uppercase text-sencondary font-weight-lighter"><?php print($laLog['filename']); ?></div>
								<div class="drive-item-thumb">
									<i class="far fa-file-code fa-2x pt-2 text-muted"></i>
									<p class="mt-2">
										<span class="badge badge-light font-weight-lighter" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Tama&ntilde;o"><i class="fas fa-equals mr-2"></i><?php print($laLog['size-unit']); ?></span>
										<span class="badge badge-light font-weight-lighter" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="&Uacute;ltima edici&oacute;n"><i class="far fa-edit mr-2"></i><?php print($laLog['edited']); ?></span>
									</p>
								</div>
							</div>
							<div class="drive-item-footer module-footer">
								<button type="button" class="btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#modalVerLog" data-mime="<?php print($laLog['mime']); ?>" data-size-unit="<?php print($laLog['size-unit']); ?>" data-basename="<?php print($laLog['basename']); ?>" data-ctime-date="<?php print($laLog['ctime-date']); ?>" data-mtime-date="<?php print($laLog['mtime-date']); ?>" ><i class="fas fa-eye"></i></button>
								<button type="button" class="btn btn-outline-secondary btn-sm descargar" data-basename="<?php print($laLog['basename']); ?>" ><i class="fas fa-cloud-download-alt"></i></button>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
				<!-- -->
				<!-- Modal -->
				<div class="error-view">
					<div class="modal fade" id="modalVerLog" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="modalVerLogLabel" aria-hidden="true">
						<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
							<div class="modal-content">
								<div class="modal-header">
									<div class="modal-title" id="modalVerLogLabel">
										 
										<div class="media mr-3">
											<i class="far fa-file-code fa-2x pt-2 align-self-center text-black-50 mr-3"></i>
											<div class="media-body">
												<h5 class="mt-0"><span id="cBaseName" class="text-uppercase text-secondary"></span><br/><small id="cMime"></small><br/><small id="cSizeUnit"></small></h5>
											</div>
										</div>								
									</div>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body"><div id="cargando" class="pb-2"><i class="fas fa-circle-notch fa-spin"></i> Cargando...</div><pre><code class="errores" id="cLog"></code></pre></div>
								<div class="modal-footer">
									<div class="row w-100">
										<div class="col-12 col-md-6 pl-0"><small class="text-muted">Creado: <span id="cCreateTimeDate" class="font-weight-bolder"></span>, Modificado: <span id="cModifyTimeDate" class="font-weight-bolder"></span></small></div>
										<div class="col-12 col-md-6 pr-0 text-right"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php } else { ?>
				<div class="alert alert-success" role="alert">
					<h4 class="alert-heading">No hay archivos!</h4>
					<p>Actualmente no hay archivos en la ruta especificada para logs.</p>
					<hr>
					<p class="mb-0"><small>Al parecer todo macha de maravillas, si tiene dudas contacte al administrador del sistema.</small></p>
				</div>			
			<?php } ?>
		</div>
		<div class="card-footer text-muted">
			<div class="row">
				<div class="col-12 col-md-6 mb-3"><span class="mr-3"><i class="fas fa-equals mr-1"></i>Tama&ntilde;o</span><span class="mr-3"><i class="far fa-edit mr-1"></i>Ultima modificaci&oacute;n</span><span class="mr-3"><i class="fas fa-eye mr-1"></i>Muestra los últimos Kb del archivo con estilos</span><span class="mr-3"><i class="fas fa-cloud-download-alt mr-1"></i>Descarga el archivo</span></div>
				<div class="col-12 col-md-6 text-md-right">Se encontraron <?php print($loAplicacionLogsManejador->getCountLogs()); ?> log(s), con un tama&ntilde;o total de <?php print($loAplicacionLogsManejador->getTotalSize(true)); ?></div>
			</div>
		</div>
	</div>
</div>

<link rel="stylesheet" href="publico-complementos/highlight/v10.5.0-dist/styles/default.css" />
<link rel="stylesheet" href="vista-logs/css/error.css" />
<script type="text/javascript" src="publico-complementos/highlight/v10.5.0-dist/highlight.pack.js"></script>
<script src="vista-logs/js/scripts.js"></script>