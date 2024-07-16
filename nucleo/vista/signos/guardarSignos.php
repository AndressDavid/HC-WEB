<?php
	require_once (__DIR__ .'/../../publico/constantes.php') ;
	require_once (__DIR__ .'/../../controlador/class.SignosNews.php') ;

	$lcMensaje = "";

	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			if(isset($_POST)){
				if(isset($_POST['nIngreso'])==true){

					$loSignosNews = new NUCLEO\SignosNews();
					if(isset($_POST)==true){$loSignosNews->medir($_POST);}
					
					//NEWS
					$lnResultado=$loSignosNews->getResultado();
					$laRespuesta=$loSignosNews->getRespuesta();
					$laSignos = $loSignosNews->getSignos();
					$lnPuntajeNews = $loSignosNews->getPuntaje();
					
					//QSOFA
					$lnPuntajeQSOFA = $loSignosNews->getPuntajeQSOFA();
					$laRespuestaQSOFA=$loSignosNews->getRespuestaQSOFA();

					$llAdulto;

					// Guardando la información en la base de datos
					if($loSignosNews->insertar($_POST['nIngreso'],$_POST['cTipoId'],$_POST['nId'],$_SESSION[HCW_NAME]->oUsuario->getUsuario())){
?>
<div class="modal fade" id="modalSignosGuardar" tabindex="-1" role="dialog" aria-labelledby="modalSignosGuardar" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered  modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Seguimiento de Signos vitales No. <?php print($loSignosNews->nConsecutivo); ?> Ingreso <?php print($_POST['nIngreso']); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<h4><span id="cPaciente"><?php print($_POST['cPaciente']);?></span></h4>
				<?php
					$lcBtnColor = "default";
					if($loSignosNews->oIngreso->oPaciente->esAdulto()==true){
						$lcBtnColor = $laRespuesta["color"];
				?>
				<h6>Decisi&oacute;n cl&iacute;nica</h6>
				<?php
					if(!empty($laRespuesta["decision"]["tiempo"]) && !empty($laRespuesta["decision"]["area"]) && !empty($laRespuesta["decision"]["nivel"])){
						$lcTiempo = ($laRespuesta["decision"]["tiempo"]>0?sprintf("Respuesta en menos de %s minutos",$laRespuesta["decision"]["tiempo"]):'');
						printf('<div class="alert alert-%s" role="alert"><i class="fas %s"></i> <b>%s</b><br/>%s, %s, Nivel de atenci&oacute;n: %s, %s.</div>',$laRespuesta["color"],$laRespuesta["icono"],$laRespuesta["decision"]["respuesta"],$lcTiempo, $laRespuesta["decision"]["area"], $laRespuesta["decision"]["nivel"],$laRespuestaQSOFA["decision"]["respuesta"]);
					}else{
						printf('<div class="alert alert-%s" role="alert"><i class="fas %s"></i> %s, %s.</div>',$laRespuesta["color"],$laRespuesta["icono"],$laRespuesta["decision"]["respuesta"],$laRespuestaQSOFA["decision"]["respuesta"]);
					}
				?>
				<h6>Puntaje de alerta temprana</h6>
				<table class="table table-bordered table-hover table-sm">
					<thead>
						<tr class="table-light">
							<th colspan="2" class="text-center">Par&aacute;metro fisiol&oacute;gico</th>
							<th colspan="2" class="text-center">Puntajes</th>
						</tr>
						<tr class="table-light">
							<th>Tipo</th>
							<th class="text-right">Medici&oacute;n</th>
							<th class="text-right">NEWS</th>
							<th class="text-right">qSOFA</th>
						</tr>
					</thead>
					<tbody>
						<?php
							foreach($laSignos as $lcSigno => $laSigno){
						?>
						<tr <?php print($laSigno['puntaje']>0?'style="font-weight: bold;"':'')?>>
							<td><?php print($laSigno['titulo']); ?></td>
							<td class="text-right"><?php print($laSigno['tipo']=='select'?$laSigno['valores'][$laSigno['valor']]['NOMBRE']:$laSigno['valor']); ?></td>
							<td class="text-right"><?php print($laSigno['puntaje']); ?></td>
							<td class="text-right"><?php print(isset($laSigno['puntajeQSOFA'])?$laSigno['puntajeQSOFA']:'--'); ?></td>
						</tr>
						<?php
							}
						?>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="2"></th>
							<th class="text-right"><?php print($lnPuntajeNews); ?><sup><small>puntos</small></sup></th>
							<th class="text-right"><?php print($lnPuntajeQSOFA); ?><sup><small>puntos</small></sup></th>
						</tr>
					</tfoot>
				</table>
				<br/>
				<?php } ?>
				<div class="alert alert-secondary" role="alert">Registro guardado<?php if($loSignosNews->nProxima>0){ printf('<br/>Pr&oacute;ximo monitoreo sugerido <i class="fas fa-calendar-alt"></i> %s <i class="fas fa-clock"></i> %s',date('Y-m-d',$loSignosNews->tProxima),date('H:i',$loSignosNews->tProxima)); } ?></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-<?php print($lcBtnColor); ?> btn-block" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>
<?php
					}else{
						$lcMensaje="No fue posible guardar la informaci&oacute;n";
					}
				}else{
					$lcMensaje="La informaci&oacute;n recibida no esta completa";
				}
			}else{
				$lcMensaje="No hay informaci&oacute;n";
			}
		}else{
			$lcMensaje="Sesi&oacute;n caduco";
		}
	}else{
		$lcMensaje = "No hay sesi&oacute;n";
	}

	if(empty($lcMensaje)==false){
		printf('<div class="alert alert-warning m-3" role="alert">%s</div>',$lcMensaje);
	}
?>