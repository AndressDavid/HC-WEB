<?php
	require_once (__DIR__ .'/../../publico/constantes.php') ;
	require_once (__DIR__ .'/../../../nucleo/controlador/class.SignosNews.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.TiposAlerta.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Db.php');


	$lnFilasSignos=200;
	$lnFilasMonitoreo=200;
	$lnTimeMaxSeg=60;
	$laTableBody=array('','');
	$lcSeccion=trim(isset($_GET['seccion'])?$_GET['seccion']:'');
	$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
	$lnFecha = $ltAhora->format("Ymd"); settype($lnFecha,"integer");
	$lnHora = $ltAhora->format("His"); settype($lnHora,"integer");
	$lnPendientesTomaSignos = 0;
	$lnMonitoreo = 0;

	function flBetween($tnValue=0, $tnLow=0, $tnHight=0){
		return ($tnValue>$tnLow && $tnValue<=$tnHight);
	}

	function iniciales($nombre) {
		$notocar = Array('del','de');
		$trozos = explode(' ',$nombre);
		$iniciales = '';
		for($i=0;$i<count($trozos);$i++){
			if(in_array($trozos[$i],$notocar)) $iniciales .= $trozos[$i]." ";
			else $iniciales .= substr($trozos[$i],0,1);
		}
		return $iniciales;
	}

?><!doctype html>
<html lang="en" class="h-100">
	<head>
		<!-- Cabecera -->
		<?php
			$lcInclude = file_get_contents("../../publico/head.php");
			$lcInclude = str_replace("publico-complementos","../publico-complementos",$lcInclude);
			$lcInclude = str_replace("publico-css","../publico-css",$lcInclude);
			$lcInclude = str_replace("publico-ico","../publico-ico",$lcInclude);
			$lcInclude = str_replace("hcw-manifiest.json.php","../hcw-manifiest.json.php",$lcInclude);
			$lcInclude = str_replace('<script type="text/javascript" src="vista-comun/js/modalSesionHCWeb.js"></script>',"",$lcInclude);
			$lcInclude = str_replace('<script type="text/javascript" src="vista-comun/js/comun.js"></script>',"",$lcInclude);
			print($lcInclude);
		?>
		<style>
			<?php if($_SESSION[HCW_NAME]->oUsuario->getDeviceType()<>'tablet' && $_SESSION[HCW_NAME]->oUsuario->getDeviceType()<>'phone'){ ?>
			body{
				background-color: #000;
			}
			.alerta{
				font-size: 2.7em !important;
			}
			.registroComplementario{
				font-size: 2.5em !important;
			}
			.iconoTipo {
				width: 38px;
				font-size: 22px !important;
			}
			.iconoTiempo {
				font-size: 0.5em !important;
			}
			.table-main{
				font-size: 1.5em;
				font-weight: bold;
			}
			.font-size-50{
				font-size:50px;
			}
			.font-size-120{
				font-size:120px;
			}
			.font-size-30{
				font-size:30px;
			}
			.font-size-20{
				font-size:20px;
			}
			<?php } ?>
			.parpadea {
				animation-name: parpadeo;
				animation-duration: 1s;
				animation-timing-function: linear;
				animation-iteration-count: infinite;

				-webkit-animation-name:parpadeo;
				-webkit-animation-duration: 1s;
				-webkit-animation-timing-function: linear;
				-webkit-animation-iteration-count: infinite;
			}

			@-moz-keyframes parpadeo{
				0% { opacity: 1.0; }
				50% { opacity: 0.0; }
				100% { opacity: 1.0; }
			}

			@-webkit-keyframes parpadeo {
				0% { opacity: 1.0; }
				50% { opacity: 0.0; }
				100% { opacity: 1.0; }
			}

			@keyframes parpadeo {
				0% { opacity: 1.0; }
				50% { opacity: 0.0; }
				100% { opacity: 1.0; }
			}
			.badge-light {
				color: #212529;
				background-color: #e8e8e8;
			}
			.bg-dark {
				background-color: #000000!important;
			}
			.text-success {
				color: #00ff3a!important;
			}
			.text-dark{
				color: #000000!important;
			}
		</style>
	</head>
	<body class="h-100">
		<div class="container-fluid h-100">
			<?php
				$laRegistros=[];
				if(isset($goDb)){
					$laTiposAlerta = (new NUCLEO\TiposAlerta())->aTipos;
					$laRespuestas = (new NUCLEO\SignosNews())->getRespuestas();

					// Consulta de alertas
					$lcWhere = "A.ESTADO=0 AND (D.ESTING = '2' OR D.FEEING=0 OR ((D.ESTING = '3' OR D.ESTING = '4') AND (D.FEEING = {$lnFecha})))";
					$lcWhere = empty($lcSeccion) ? $lcWhere : "{$lcWhere} AND C.SECHAB='{$lcSeccion}' AND (C.ESTHAB='1' OR C.ESTHAB='9')";
					$laRegistros = $goDb
						->select([
							'A.NIGING',
							"IFNULL(C.SECHAB,'') SECHAB",
							"IFNULL(C.NUMHAB,'') NUMHAB",
							'A.RESFEA',
							'A.ESTADO',
							'A.CLASIF',
							'A.VALFEA',
							'A.VALHOA',
							'A.VAR26N',
							'A.VAR29N',
							'A.VAR30N',
							"IFNULL(D.ESTING,'') ESTING"
						])
						->tabla('ALETEMP A')
						->leftJoin('FACHAB C', 'A.NIGING = C.INGHAB AND C.TIDHAB=A.TIDING AND C.NIDHAB=A.NIDING')
						->leftJoin('RIAING D', 'A.NIGING = D.NIGING')
						->where($lcWhere)
						->orderBy('A.CLASIF DESC,A.NIGING, A.VALFEA ASC, A.VALHOA ASC')
						->getAll('array');
					if(is_array($laRegistros??0)){
						foreach($laRegistros as $lnFila=>$laRegistro){
							$lnClasificacion = (integer) $laRegistro['CLASIF'];
							$lcColor="light";
							$lcColorFont="text-dark";
							$lcColorTipo="table";
							$lcIcono="fa fa-clock";
							$lnTiempo = 0;
							$lcParpadea="";

							if(isset($laRespuestas[$lnClasificacion])){
								$lcColor = $laRespuestas[$lnClasificacion]['color'];
								$lcIcono = "fa ".$laRespuestas[$lnClasificacion]['icono'];
								$lnTiempo = $laRespuestas[$lnClasificacion]['decision']['tiempo']+0;
							}

							$ltRegistro = strtotime($laRegistro['VALFEA']." ".str_pad(trim($laRegistro['VALHOA']), 6, "0", STR_PAD_LEFT));
							$ltAhora = strtotime(date("Ymd His"));

							if($laRegistro['RESFEA']>0){
								$lcTiempo = 'Valoraci&oacute;n';
								$lcIcono = 'fas fa-eye';
							}else{
								$lcTiempo = (int)(($ltAhora-$ltRegistro)/60);
								$lcTeimpoPrefijo='<i class="far fa-arrow-alt-circle-up"></i>';

								if($lnTiempo>0){
									if($lcTiempo<=$lnTiempo){
										$lcTeimpoPrefijo='<i class="fas fa-clock"></i>';
										$lcTiempo=($lnTiempo-$lcTiempo);
									}else{
										$lcParpadea="parpadea";
										$lcColorTipo="bg";
									}
								}
								$lcTiempo = sprintf('%s%s<sup class="iconoTiempo">%s</sup>',number_format($lcTiempo,0,",","."),"&quot", $lcTeimpoPrefijo);
							}
							$lcColorFont = ($lcColorTipo=='bg'?($lcColor=='danger'?'text-white':'text-dark'):'text-dark');



							$lcRegistro = sprintf('%s',date("H:i",$ltRegistro));
							$lcUbicacion = trim($laRegistro['SECHAB'])." ".(!empty($laRegistro['NUMHAB'])?"<sup><b>".$laRegistro['NUMHAB']."</b></sup>":"");
							$lnAlertaTipo=$laRegistro['VAR29N']; settype($lnAlertaTipo,'integer');
							$lcIconoTipo = sprintf('<span class="badge badge-light iconoTipo %s"><i class="fas fa-%s"></i></span>',($laRegistro['VAR26N']>0?'bg-warning':''),$laTiposAlerta[$lnAlertaTipo]['ICONOW']);
							$lcHtml=sprintf('<tr class="%s-%s %s">
										<td class="text-center %s"><i class="%s"></i></td>
										<td>%s</td>
										<td>%s</td>
										<td class="text-right">%s</td>
										<td class="text-right" data-registro="%s"></td>
										<td class="text-right">%s</td>
									</tr>',$lcColorTipo,$lcColor,$lcColorFont,$lcParpadea,$lcIcono,$laRegistro['NIGING'],$lcUbicacion,$lcTiempo,$lcRegistro,$lcIconoTipo);
							$laTableBody[$lnFila%2].=$lcHtml;
						}
					}

					if(empty($laTableBody[0])==true && empty($laTableBody[1])==true){
					}
				}
			?>

			<?php if(!empty($laTableBody[0]) || !empty($laTableBody[1])){ ?>
			<div id="div-paneles-superiores" class="row h-50">
				<div class="col-6 pl-2 pt-2 pr-1 pb-0 h-100">
					<div class="card h-100 border-danger bg-dark text-white">
						<div class="card-header bg-dark text-white">
							<div class="row">
								<div class="col-4 text-left">
									Valorar <span class="badge badge-light"><?php print(count($laRegistros)); ?></span> <span id="timeCount" class="badge badge-light"><?php print(date("H:i")); ?></span>
								</div>
								<div class="col-8 text-right">
									<small class="text-white">
									<?php
										for($lnAlertaTipo=0; $lnAlertaTipo<count($laTiposAlerta);$lnAlertaTipo++){
											printf('%s <i class="fas fa-%s"></i> | ',$laTiposAlerta[$lnAlertaTipo]['NOMBRE'],$laTiposAlerta[$lnAlertaTipo]['ICONOW']);
										}
									?>
									</small>
								</div>
							</div>
						</div>
						<div class="table-responsive card-body p-0">
							<table class="table table-sm">
								<thead>
									<tr class="bg-dark text-white">
										<th style="width: 47px;"></th>
										<th>Ingreso</th>
										<th>Ubicaci&oacute;n</th>
										<th class="text-right">Estado</th>
										<th class="text-right"></th>
										<th class="text-right">Tipo</th>
									</tr>
								</thead>
								<tbody class="table-main alerta">
									<?php print($laTableBody[0]); ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="col-6 pl-1 pt-2 pr-2 pb-0 h-100">
					<div class="card h-100 border-danger bg-dark text-white">
						<div class="card-header bg-dark text-white">
							<div class="row">
								<div class="col-12 text-right">
									&nbsp;<span id="seccionCount" class="badge badge-light"><?php print((empty($lcSeccion)?'Todos':'Secci&oacute;n ').$lcSeccion); ?></span>
								</div>
							</div>
						</div>
						<div class="table-responsive card-body p-0">
							<table class="table table-sm">
								<thead>
									<tr class="bg-dark text-white">
										<th style="width: 47px;"></th>
										<th>Ingreso</th>
										<th>Ubicaci&oacute;n</th>
										<th class="text-right">Estado</th>
										<th class="text-right"></th>
										<th class="text-right">Tipo</th>
									</tr>
								</thead>
								<tbody class="table-main alerta">
									<?php print($laTableBody[1]); ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<?php } else{ ?>
			<div id="div-paneles-superiores" class="row h-50">
				<div class="col-12 pl-2 pt-2 pr-1 pb-0 h-100">
					<div class="card h-100 border-success bg-dark text-white">
						<div class="card-body text-center text-success badge-time bg-dark">
							<span class="font-size-50"><b><?php print((empty($lcSeccion)?'Todos':'Secci&oacute;n ').$lcSeccion); ?></b></span><br/>
							<span class="font-size-120"><?php print(date("Y-m-d")); ?>   <b><?php print(date("H:i")); ?></b></span><br/>
							<span class="font-size-30">Todas las valoraciones atendidas, no hay pendientes.</span><br/>
							<span class="font-size-20">Esta ventana se actualizara en <b class="messageActualizar"></b> segundos</span><br/>
						</div>
					</div>
				</div>
			</div>
			<?php }  ?>

			<div id="div-paneles-inferiores" class="row h-50">
				<div id="div-pendientes-toma-signos" class="col-6 pl-2 pt-2 pr-1 pb-2 h-100">
					<div class="card h-100 border-primary bg-dark text-white">
						<?php
							$laRegistros=array();
							if(isset($goDb)){
								$laIngresos = array();
								$laRespuestas = (new NUCLEO\SignosNews())->getRespuestas();
								$lcWhere='A.ESTADO<9 AND A.VALFES='.date("Ymd").' AND A.VAR29N=0';
								$lcWhere=(empty($lcSeccion)?$lcWhere:$lcWhere." AND C.SECHAB='".$lcSeccion."'");

								$laRegistros = $goDb
									->select([
										'A.NIGING',
										'A.CLASIF',
										"TRIM(IFNULL(B.NM1PAC,''))||' '||TRIM(IFNULL(NM2PAC,''))||' '||TRIM(IFNULL(B.AP1PAC,''))||' '||TRIM(IFNULL(B.AP2PAC,'')) NOMBRE",
										"IFNULL(C.SECHAB,'') SECHAB",
										"IFNULL(C.NUMHAB,'') NUMHAB",
										'VALFES',
										'VALHOS',
									])
									->tabla('ALETEMP A')
									->leftJoin('RIAPAC B', 'A.TIDING = B.TIDPAC AND A.NIDING = B.NIDPAC')
									->leftJoin('FACHAB C', 'A.NIGING = C.INGHAB AND C.TIDHAB=A.TIDING AND C.NIDHAB=A.NIDING')
									->where($lcWhere)
									->between('A.VALHOS', date('H').'0000', date('H').'5959')
									->orderBy('A.VALFES DESC, A.VALHOS ASC')
									->limit($lnFilasSignos)
									->getAll('array');
							}
						?>
						<div class="card-header bg-transparent text-white font-weight-bold border-bottom border-primary">
							<h2><i class="fas fa-heartbeat"></i> Toma de Signos <span class="badge badge-light"><?php print(count($laRegistros)); ?></span></h2>
						</div>
						<div id="div-responsive-pendientes-toma-signos" class="table-responsive card-body p-0">
							<table id="tab-pendientes-toma-signos" class="table table-sm table-borderless text-white">
								<thead>
									<tr>
										<th style="width: 47px;"></th>
										<th>Ingreso</th>
										<th class="text-right">Ubicaci&oacute;n</th>
										<th class="text-right">Hora</th>
									</tr>
								</thead>
								<tbody class="registroComplementario">
									<?php
										if(isset($goDb)){
											if(is_array($laRegistros??0)){
												foreach($laRegistros as $lnFila=>$laRegistro){
													if(in_array($laRegistro['NIGING'],$laIngresos,true)==false){
														$lnPendientesTomaSignos += 1;
														$laIngresos[]=$laRegistro['NIGING'];
														$lnClasificacion = (integer) $laRegistro['CLASIF'];
														$lcColor="light";
														$lcColorTipo="table";
														$lcIcono="fa fa-clock";
														$lnTiempo = 0;

														$ltSiguiente = strtotime($laRegistro['VALFES']." ".str_pad(trim($laRegistro['VALHOS']), 6, "0", STR_PAD_LEFT));
														$lcSiguiente = sprintf('<b>%s</b>',date("H:i",$ltSiguiente));

														if(isset($laRespuestas[$lnClasificacion])){
															$lcColor = $laRespuestas[$lnClasificacion]['color'];
															$lcIcono = "fa ".$laRespuestas[$lnClasificacion]['icono'];
															$lnTiempo = $laRespuestas[$lnClasificacion]['decision']['tiempo']+0;
														}

														$lcUbicacion = trim($laRegistro['SECHAB'])." ".(!empty($laRegistro['NUMHAB'])?"<sup><b>".$laRegistro['NUMHAB']."</b></sup>":"");
														printf('<tr class="text-%s">
																	<td class="text-center"><i class="%s"></i></td>
																	<td>%s</td>
																	<td class="text-right">%s</td>
																	<td class="text-right"><b>%s</b></td>
																</tr>',$lcColor,$lcIcono,$laRegistro['NIGING'],$lcUbicacion,$lcSiguiente);
													}
												}
											}
										}
									?>
								</tbody>
								<tfoot>
									<tr>
										<th style="width: 47px;" class="font-weight-light"></th>
										<th class="font-weight-light">Ingreso</th>
										<th class="font-weight-light text-right">Ubicaci&oacute;n</th>
										<th class="font-weight-light text-right">Hora</th>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
				<div id="div-en-monitoreo" class="col-6 pl-1 pt-2 pr-2 pb-2 h-100 ">
					<div class="card h-100 border-primary bg-dark text-white">
						<?php
							$laRegistros=array();
							if(isset($goDb)){
								$laRespuestas = (new NUCLEO\SignosNews())->getRespuestas();
								$lcWhere='A.ESTADO>1 AND A.ESTADO<7 AND A.VAR29N=0';
								$lcWhere=(empty($lcSeccion)?$lcWhere:$lcWhere." AND C.SECHAB='".$lcSeccion."'");
								$laRegistros = $goDb
									->select([
										'A.NIGING',
										'A.CLASIF',
										"TRIM(IFNULL(B.NM1PAC,''))||' '||TRIM(IFNULL(NM2PAC,''))||' '||TRIM(IFNULL(B.AP1PAC,''))||' '||TRIM(IFNULL(B.AP2PAC,'')) NOMBRE",
										"IFNULL(C.SECHAB,'') SECHAB",
										"IFNULL(C.NUMHAB,'') NUMHAB",
									])
									->tabla('ALETEMP A')
									->leftJoin('RIAPAC B', 'A.TIDING = B.TIDPAC AND A.NIDING = B.NIDPAC')
									->leftJoin('FACHAB C', 'A.NIGING = C.INGHAB AND C.TIDHAB=A.TIDING AND C.NIDHAB=A.NIDING')
									->where($lcWhere)
									->orderBy('A.CLASIF','DESC')
									->limit($lnFilasMonitoreo)
									->getAll('array');
							}
						?>
						<div class="card-header bg-transparent text-white font-weight-bold border-bottom border-primary">
							<div class="row">
								<div class="col-sm-6">
									<h2><i class="fas fa-user-md"></i> En monitoreo <span class="badge badge-light"><?php print(count($laRegistros)); ?></h2>
								</div>
								<div class="col-sm-6 text-right">
									<span class="badge badge-dark"><?php print($_SESSION[HCW_NAME]->oUsuario->getIP()); ?> | <?php print($_SESSION[HCW_NAME]->oUsuario->getDeviceType()); ?></span>
								</div>
							</div>
						</div>
						<div  id="div-responsive-en-monitoreo" class="table-responsive card-body p-0">
							<table  id="tab-en-monitoreo" class="table table-sm table-borderless text-white">
								<thead>
									<tr>
										<th style="width: 47px;"></th>
										<th>Ingreso</th>
										<th class="text-right">Ubicaci&oacute;n</th>
									</tr>
								</thead>
								<tbody class="registroComplementario">
									<?php
										if(is_array($laRegistros??0)){
											foreach($laRegistros as $lnFila=>$laRegistro){
												$lnMonitoreo += 1;
												$lnClasificacion = (integer) $laRegistro['CLASIF'];
												$lcColor="light";
												$lcColorTipo="table";
												$lcIcono="fa fa-clock";
												$lnTiempo = 0;

												if(isset($laRespuestas[$lnClasificacion])){
													$lcColor = $laRespuestas[$lnClasificacion]['color'];
													$lcIcono = "fa ".$laRespuestas[$lnClasificacion]['icono'];
													$lnTiempo = $laRespuestas[$lnClasificacion]['decision']['tiempo']+0;
												}

												$lcUbicacion = trim($laRegistro['SECHAB'])." ".(!empty($laRegistro['NUMHAB'])?"<sup><b>".$laRegistro['NUMHAB']."</b></sup>":"");
												printf('<tr class="text-%s">
															<td class="text-center"><i class="%s"></i></td>
															<td>%s</td>
															<td class="text-right"><b>%s</b></td>
														</tr>',$lcColor,$lcIcono,$laRegistro['NIGING'],$lcUbicacion);
											}
										}
									?>
								</tbody>
								<tfoot>
									<tr>
										<th style="width: 47px;" class="font-weight-light"></th>
										<th class="font-weight-light">Ingreso</th>
										<th  class="font-weight-light text-right">Ubicaci&oacute;n</th>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div style="position: absolute; float:right; bottom: 0; right: 0; padding-right: 8px; padding-bottom: 8px;">
			<button id="cmdActualizar" class="btn btn-sm btn-secondary"><i class="fas fa-sync fa-spin"></i> <span class="messageActualizar"></span></button>
		</div>

		<script type="text/javascript">
			$(document).ready(function(){
				"use strict";
				var lnTime=0;
				var lnTimeMaxSeg = <?php print($lnTimeMaxSeg); ?>;

				setTimeout(function(){
					location.reload(true);
				}, 1000 *lnTimeMaxSeg);

				setInterval(function() {
					lnTime+=1;
					$('.messageActualizar').html((lnTimeMaxSeg-lnTime));
				}, 1000);

				$( "#cmdActualizar" ).click(function() {
					location.reload(true);
				});

				$(function () {
					$("#div-responsive-en-monitoreo").animate({scrollTop: $('#tab-en-monitoreo').get(0).scrollHeight }, 50000);
					$("#div-responsive-pendientes-toma-signos").animate({scrollTop: $('#tab-pendientes-toma-signos').get(0).scrollHeight }, 50000);

					<?php if($lnPendientesTomaSignos == 0){ ?>

					$("#div-pendientes-toma-signos").remove();
					$("#div-en-monitoreo").removeClass( "col-6" ).addClass( "col-12" );

					<?php } ?>
					<?php if($lnMonitoreo == 0){ ?>

					$("#div-en-monitoreo").remove();
					$("#div-pendientes-toma-signos").removeClass( "col-6" ).addClass( "col-12" );

					<?php } ?>

					<?php if($lnPendientesTomaSignos == 0 && $lnMonitoreo == 0){ ?>

					$("#div-paneles-inferiores").remove();
					$("#div-paneles-superiores").removeClass( "h-50" ).addClass( "h-100" );

					<?php } ?>

				});
			});
		</script>

		<!-- Pie de pagina -->

	</body>
</html>