<?php
	require_once (__DIR__ .'/../../publico/constantes.php') ;
	require_once (__DIR__ .'/../../../nucleo/controlador/class.SignosNews.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.TiposAlerta.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Db.php');

	//ini_set('memory_limit', '200M')

	$lcError = "";
	$lnTimeMaxSeg=60;
	$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
	$lnFecha = $ltAhora->format("Ymd"); settype($lnFecha,"integer");
	$lnHora = $ltAhora->format("His"); settype($lnHora,"integer");
	$loSignosNews = new NUCLEO\SignosNews();

	$lcTabla = 'ALETEMP';

	$laDatosConductaTotal= array(0);
	$laDatosConductas = $loSignosNews->getConductas();

	$lnDatosGruposTotal = 0;
	$laDatosGrupoTotal= array(0,0,0);
	$laMeses = array('enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre');
	$laDatosGrupo = array(
						array('name'=>'Normal', 'value' => 0, 'expired' => 0, 'actives' => 0, 'answered'=>0 ,'answered_time'=>0,'answered_time_min'=>0,'answered_time_max'=>0, 'color' => '#e9ecef', 'class' => 'light'),
						array('name'=>'Criterio Enfermería', 'value' => 0, 'expired' => 0, 'actives' => 0, 'answered'=>0 ,'answered_time'=>0,'answered_time_min'=>0,'answered_time_max'=>0, 'color' => '#007bff', 'class' => 'primary'),
						array('name'=>'Alerta Amarilla', 'value' => 0, 'expired' => 0, 'actives' => 0, 'answered'=>0 ,'answered_time'=>0,'answered_time_min'=>0,'answered_time_max'=>0, 'color' => '#ffc107', 'class' => 'warning'),
						array('name'=>'Alerta Roja', 'value' => 0, 'expired' => 0, 'actives' => 0, 'answered'=>0 ,'answered_time'=>0,'answered_time_min'=>0,'answered_time_max'=>0, 'color' => '#dc3545', 'class' => 'danger')
					);

	// Aletar por clasificacion
	try{
		$laDatosDia = $goDb->select('count(*) REGISTROS, CLASIF CLASIFICACION')
						  ->tabla($lcTabla)
						  ->where('VALFEA','=',$lnFecha)
						  ->groupBy('CLASIF')
						  ->getAll("array");
	} catch (Exception $e) {
		$lcError .= $e->getMessage()."&nbsp;";
	}
	if(isset($laDatosDia)){
		if(is_array($laDatosDia)){
			foreach($laDatosDia as $laDatoDia){
				if(isset($laDatosGrupo[$laDatoDia['CLASIFICACION']])==true){
					$laDatosGrupo[$laDatoDia['CLASIFICACION']]['value']+=$laDatoDia['REGISTROS'];
					$lnDatosGruposTotal += $laDatoDia['REGISTROS'];
					if($laDatoDia['CLASIFICACION']>=2){
						if($laDatoDia['REGISTROS']>0){
							// Respuestas por clasificación
							try{
								$laDatosRespuestasDia = $goDb->count('*', 'REGISTROS')
															 ->tabla($lcTabla)
															 ->where('VALFEA','=',$lnFecha)
															 ->where('CLASIF','=',$laDatoDia['CLASIFICACION'])
															 ->where('ACCION','=',0)
															 ->where('ESTADO','=',9)
															 ->get("array");
							} catch (Exception $e) {
								$lcError .= $e->getMessage()."&nbsp;";
							}
							if(isset($laDatosRespuestasDia)==true){
								if(is_array($laDatosRespuestasDia)==true){
									$laDatosGrupo[$laDatoDia['CLASIFICACION']]['expired'] = $laDatosRespuestasDia['REGISTROS'];
								}
							}

							// Alertas activas
							try{
								$laDatosRespuestasDia = $goDb->count('*', 'REGISTROS')
															 ->tabla($lcTabla)
															 ->where('VALFEA','=',$lnFecha)
															 ->where('CLASIF','=',$laDatoDia['CLASIFICACION'])
															 ->where('ACCION','=',0)
															 ->where('ESTADO','=',0)
															 ->get("array");
							} catch (Exception $e) {
								$lcError .= $e->getMessage()."&nbsp;";
							}
							if(isset($laDatosRespuestasDia)==true){
								if(is_array($laDatosRespuestasDia)==true){
									$laDatosGrupo[$laDatoDia['CLASIFICACION']]['actives'] = $laDatosRespuestasDia['REGISTROS'];
								}
							}

							// Conteo por acciones
							try{
								$lnRegistros=0;
								$lcSql  = "count(*) REGISTROS ";
								$lcWhere = "VALFEA=".$lnFecha." AND CLASIF=".$laDatoDia['CLASIFICACION']." AND ACCION>0";
								$laDatosRespuestasDiaAux = $goDb->select($lcSql)
															 ->tabla($lcTabla)
															 ->where($lcWhere)
															 ->get("array");
								if(isset($laDatosRespuestasDiaAux)==true){
									if(is_array($laDatosRespuestasDiaAux)==true){
										$lnRegistros = $laDatosRespuestasDiaAux['REGISTROS'];
									}
								}
								if($lnRegistros>0){
									$lcSql  = "count(*) REGISTROS, ";
									$lcSql .= "SUM(timestampdiff(4 ,char(TO_DATE(CHAR((ACCFEC*1000000)+ACCHOR),'YYYYMMDDHH24MISS') - TO_DATE(CHAR((VALFEA*1000000)+VALHOA),'YYYYMMDDHH24MISS')))) TRASNCURRIDO, ";
									$lcSql .= "MAX(timestampdiff(4 ,char(TO_DATE(CHAR((ACCFEC*1000000)+ACCHOR),'YYYYMMDDHH24MISS') - TO_DATE(CHAR((VALFEA*1000000)+VALHOA),'YYYYMMDDHH24MISS')))) MAXIMO, ";
									$lcSql .= "MIN(timestampdiff(4 ,char(TO_DATE(CHAR((ACCFEC*1000000)+ACCHOR),'YYYYMMDDHH24MISS') - TO_DATE(CHAR((VALFEA*1000000)+VALHOA),'YYYYMMDDHH24MISS')))) MINIMO ";
									$laDatosRespuestasDia = $goDb->select($lcSql)
																 ->tabla($lcTabla)
																 ->where($lcWhere)
																 ->get("array");
									if(isset($laDatosRespuestasDia)==true){
										if(is_array($laDatosRespuestasDia)==true){
											$laDatosGrupo[$laDatoDia['CLASIFICACION']]['answered'] = $laDatosRespuestasDia['REGISTROS'];
											$laDatosGrupo[$laDatoDia['CLASIFICACION']]['answered_time'] = $laDatosRespuestasDia['TRASNCURRIDO'];
											$laDatosGrupo[$laDatoDia['CLASIFICACION']]['answered_time_max'] = $laDatosRespuestasDia['MAXIMO'];
											$laDatosGrupo[$laDatoDia['CLASIFICACION']]['answered_time_min'] = $laDatosRespuestasDia['MINIMO'];
										}
									}
								}

							} catch (Exception $e) {
								$lcError .= $e->getMessage()."&nbsp;";
							}
						}
					}
				}
			}
		}
	}

	// Alertas del mes
	try{
		$lcSql  = "VALFEA FECHA, 0 DIA, 0 AA, 0 AR,";
		$lcSql .= "(SELECT COUNT(*) FROM ALETEMP AS B WHERE B.VALFEA=A.VALFEA AND B.CLASIF>=2 AND B.ACCION<=0) SIN, ";
		$lcSql .= "(SELECT COUNT(*) FROM ALETEMP AS C WHERE C.VALFEA=A.VALFEA AND C.CLASIF>=2 AND C.ACCION>0) CON, ";
		$lcSql .= "(SELECT COUNT(*) FROM ALETEMP AS D WHERE D.VALFEA=A.VALFEA AND D.CLASIF= 2 AND D.ACCION>0) A_ANSWERS, ";
		$lcSql .= "0 A_ANSWERED_TIME, ";
		$lcSql .= "(SELECT COUNT(*) FROM ALETEMP AS F WHERE F.VALFEA=A.VALFEA AND F.CLASIF=3 AND F.ACCION>0) B_ANSWERS, ";
		$lcSql .= "0 B_ANSWERED_TIME ";
		$laDatosAcumuladosRespuestasMes = $goDb->select($lcSql)
									 ->tabla($lcTabla." A")
									 ->where('A.VALFEA','>',intval($lnFecha/100)*100)
									 ->where('A.VALFEA','<=',(intval($lnFecha/100)*100)+31)
									 ->where('A.CLASIF','>=',2)
									 ->groupBy('A.VALFEA')
									 ->getAll("array");

		if(isset($laDatosAcumuladosRespuestasMes)==true){
			if(is_array($laDatosAcumuladosRespuestasMes)==true){
				if(count($laDatosAcumuladosRespuestasMes)>0){
					for($lnDatoDia=0;$lnDatoDia<count($laDatosAcumuladosRespuestasMes);$lnDatoDia++){
						$lcSql="SUM(timestampdiff(4 ,char(TO_DATE(CHAR((E.ACCFEC*1000000)+E.ACCHOR),'YYYYMMDDHH24MISS') - TO_DATE(CHAR((E.VALFEA*1000000)+E.VALHOA),'YYYYMMDDHH24MISS')))) A_ANSWERED_TIME";
						$laDatosMesAux = $goDb->select($lcSql)
											  ->tabla($lcTabla." E")
											  ->where('E.VALFEA','=',$laDatosAcumuladosRespuestasMes[$lnDatoDia]['FECHA'])
											  ->where('E.VALFEA','<=',(intval($lnFecha/100)*100)+31)
											  ->where('E.CLASIF','=',2)
											  ->where('E.ACCION','>',0)
											  ->groupBy('E.VALFEA')
											  ->get("array");
						$laDatosAcumuladosRespuestasMes[$lnDatoDia]['A_ANSWERED_TIME']=$laDatosMesAux['A_ANSWERED_TIME'];

						$lcSql="SUM(timestampdiff(4 ,char(TO_DATE(CHAR((G.ACCFEC*1000000)+G.ACCHOR),'YYYYMMDDHH24MISS') - TO_DATE(CHAR((G.VALFEA*1000000)+G.VALHOA),'YYYYMMDDHH24MISS')))) B_ANSWERED_TIME";
						$laDatosMesAux = $goDb->select($lcSql)
											  ->tabla($lcTabla." G")
											  ->where('G.VALFEA','=',$laDatosAcumuladosRespuestasMes[$lnDatoDia]['FECHA'])
											  ->where('G.VALFEA','<=',(intval($lnFecha/100)*100)+31)
											  ->where('G.CLASIF','=',3)
											  ->where('G.ACCION','>',0)
											  ->groupBy('G.VALFEA')
											  ->get("array");
						$laDatosAcumuladosRespuestasMes[$lnDatoDia]['B_ANSWERED_TIME']=$laDatosMesAux['B_ANSWERED_TIME'];
					}
				}
			}
		}
	} catch (Exception $e) {
		$lcError .= $e->getMessage()."&nbsp;";
	}
	if(isset($laDatosAcumuladosRespuestasMes)==true){
		if(is_array($laDatosAcumuladosRespuestasMes)==true){
			foreach($laDatosAcumuladosRespuestasMes as $lnDatosAcumuladoRespuestasMes => $laDatosAcumuladoRespuestasMes){
				$lnFecha = intval($laDatosAcumuladoRespuestasMes['FECHA']);
				$laDatosAcumuladosRespuestasMes[$lnDatosAcumuladoRespuestasMes]['DIA'] = ($lnFecha - (intval($lnFecha/100)*100));
				$lnTotal = intval($laDatosAcumuladosRespuestasMes[$lnDatosAcumuladoRespuestasMes]['A_ANSWERS']);
				$laDatosAcumuladosRespuestasMes[$lnDatosAcumuladoRespuestasMes]['AA'] = ($lnTotal>0?intval(intval($laDatosAcumuladosRespuestasMes[$lnDatosAcumuladoRespuestasMes]['A_ANSWERED_TIME'])/$lnTotal):0);
				$lnTotal = intval($laDatosAcumuladosRespuestasMes[$lnDatosAcumuladoRespuestasMes]['B_ANSWERS']);
				$laDatosAcumuladosRespuestasMes[$lnDatosAcumuladoRespuestasMes]['AR'] = ($lnTotal>0?intval(intval($laDatosAcumuladosRespuestasMes[$lnDatosAcumuladoRespuestasMes]['B_ANSWERED_TIME'])/$lnTotal):0);
			}
		}
	}
	// Ultima alerta del día
	try{
		$laDatosClasificacionDia = $goDb->select('CLASIF, COUNT(*) REGISTROS, MAX(VALHOA) HORA')
						  ->tabla($lcTabla)
						  ->where('VALFEA','=',$lnFecha)
						  ->where('CLASIF','>',1)
						  ->groupBy('CLASIF')
						  ->getAll("array");
	} catch (Exception $e) {
		$lcError .= $e->getMessage()."&nbsp;";
	}

	// Alertas por conducta
	if(isset($laDatosConductas)==true){
		if(is_array($laDatosConductas)==true){
			foreach($laDatosConductas as $lnDatoConducta => $laDatoConducta){
				$laDatosConductas[$lnDatoConducta]['value']=0;
			}
			$laDatosConductas[6]['color']="#8feaf9";
			$laDatosConductas[7]['color']="#a2ecb3";
			$laDatosConductas[8]['color']="#f3b487";
			$laDatosConductas[6]['NOMBRE']="Continúa en monitoreo";

			$laDatosDia = $goDb->select('count(*) REGISTROS, ACCION')
							  ->tabla($lcTabla)
							  ->where('VALFEA','=',$lnFecha)
							  ->where('ACCION','>',0)
							  ->groupBy('ACCION')
							  ->getAll("array");
			if(is_array($laDatosDia)){
				foreach($laDatosDia as $laDatoDia){
					if(isset($laDatosConductas[$laDatoDia['ACCION']])==true){
						$laDatosConductas[$laDatoDia['ACCION']]['value']=$laDatoDia['REGISTROS'];
					}
				}
			}
		}
	}

	// Consulta de registros
	try{
		$laDatosRespuestasDia = $goDb->count('*', 'REGISTROS')
									 ->tabla($lcTabla)
									 ->where('VALFEA','=',$lnFecha)
									 ->where('CLASIF','>=',2)
									 ->where('ACCION','=',0)
									 ->where('ESTADO','=',9)
									 ->get("array");
	} catch (Exception $e) {
		$lcError .= $e->getMessage()."&nbsp;";
	}
	if(isset($laDatosRespuestasDia)==true){
		if(is_array($laDatosRespuestasDia)==true){
			$lnDatoConductaExpiro = $laDatosRespuestasDia['REGISTROS'];
		}
	}

	// Top 5 de respuesta
	$laTopEquipoRespuestasDia = $goDb->select('EQUIPO, COUNT(*) REGISTROS')
								 ->tabla($lcTabla)
								 ->where('VALFEA','=',$lnFecha)
								 ->where('CLASIF','>=',2)
								 ->where('ACCION','>',0)
								 ->groupBy('EQUIPO')
								 ->orderBy('REGISTROS DESC')
								 ->limit(5)
								 ->getAll("array");

	// Funciones especiales
	function getListFromArray($taDatos,$tcField, $tcPrefix='', $tcPostfix=''){
		$lcList = '';
		$lcListAux = '';
		if(is_array($taDatos)==true){
			foreach($taDatos as $laDato){
				if(is_array($laDato)==true){
					if(isset($laDato[$tcField])==true){
						$lcList .= (empty($lcList)==true?'':',').$tcPrefix.$laDato[$tcField].$tcPostfix;
					}else{
						$lcListAux = getListFromArray($laDato,$tcField,$tcPrefix,$tcPostfix);
						if(empty($lcListAux)==false){
							$lcList .= (empty($lcList)==true?'':',').$lcListAux;
						}
					}
				}
			}
		}
		return $lcList;
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
		<script src="../publico-complementos/highcharts/7.2.0/code/highcharts.js"></script>
		<script src="../publico-complementos/highcharts/7.2.0/code/modules/bullet.js"></script>
		<script src="../publico-complementos/Chart.js/2.8.0/Chart.min.js"></script>

		<style>
			<?php if($_SESSION[HCW_NAME]->oUsuario->getDeviceType()<>'tablet' && $_SESSION[HCW_NAME]->oUsuario->getDeviceType()<>'phone'){ ?>
			.table tbody, tfoot{
				font-size: 2em !important;
			}
			.table {
				table-layout: fixed;
			}
			.alerta{
				font-size: 1.8em !important;
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
			.w-td-100{
				width: 100px;
			}
			.w-td-80{
				width: 80px;
			}
			.container-bar {
				height: 62px;
				margin: 0px;
				margin-top: 5px;
			}
			.hc-cat-title {
				font-size: 13px;
				font-weight: bold;
			}

		</style>
	</head>
	<body class="h-100">
		<div class="container-fluid h-100">
			<!-- Metricas -->
			<div class="row">
				<!-- ALERTAS -->
				<div class="col-md-6 pl-2 pt-2 pr-1 pb-0 h-100">
					<div class="card">
						<div class="card-header">
							<div class="row">
								<div class="col-sm-6">
									M&eacute;trica de alertas
								</div>
								<div class="col-sm-6 text-right">
									<span class="badge badge-dark"><?php print($_SESSION[HCW_NAME]->oUsuario->getIP()); ?> | <?php print($_SESSION[HCW_NAME]->oUsuario->getDeviceType()); ?></span>
								</div>
							</div>
						</div>
						<div class="card-body p-1">
							<table class="table table-sm mb-0">
								<thead>
									<tr>
										<th scope="col" class="align-middle text-right">Clasificaci&oacute;n</th>
										<th scope="col" class="align-middle text-right w-td-100">Registros</th>
										<th scope="col" class="align-middle text-right w-td-100">Activos</th>
										<th scope="col" class="align-middle text-right w-td-100">Expiraron</th>
										<th scope="col" class="align-middle text-right w-td-100">Respuesta</th>
									</tr>
								</thead>
								<tbody>
									<?php
										foreach($laDatosGrupo as $lnDatoDSAT => $laDatoDSAT){
									?>
									<tr class="table-<?php print($laDatoDSAT['class']); ?>">
										<td class="text-right"><?php print($laDatoDSAT['name']); ?></td>
										<td class="text-right"><?php print($laDatoDSAT['value']>0?$laDatoDSAT['value']:'-'); ?></td>
										<td class="text-right"><?php print($laDatoDSAT['actives']>0?sprintf('<span class="parpadea">%s</span>',$laDatoDSAT['actives']):'-'); ?></td>
										<td class="text-right"><?php print($laDatoDSAT['expired']>0?$laDatoDSAT['expired']:'-'); ?></td>
										<td class="text-right"><?php print($laDatoDSAT['answered']>0?sprintf('%s&quot;',intval($laDatoDSAT['answered_time']/$laDatoDSAT['answered'])):''); ?></td>
									</tr>
									<?php
											$laDatosGrupoTotal[0]+=$laDatoDSAT['value'];
											$laDatosGrupoTotal[1]+=$laDatoDSAT['actives'];
											$laDatosGrupoTotal[2]+=$laDatoDSAT['expired'];
										}
									?>
								</tbody>
								<tfoot>
									<tr>
										<td></td>
										<?php
											foreach($laDatosGrupoTotal as $lnSumRowsIndex => $lnSumRows){
												if($lnSumRowsIndex==1){
													printf('<th scope="col" class="text-right">%s</th>',($lnSumRows>0?sprintf('<span class="parpadea">%s</span>',$lnSumRows):'-'));
												}else{
													printf('<th scope="col" class="text-right">%s</th>',($lnSumRows>0?$lnSumRows:'-'));
												}
											}
										?>
										<td></td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>

				<!-- ACCIONES -->
				<div class="col-md-6 pl-2 pt-2 pr-1 pb-0 h-100">
					<div class="card">
						<div class="card-header">
							<div class="row">
								<div class="col-sm-6">
									M&eacute;trica de acciones
								</div>
								<div class="col-sm-6 text-right">
									<span class="badge badge-dark"><?php print($ltAhora->format("Y-m-d H:i:s")); ?></span>
								</div>
							</div>
						</div>
						<div class="card-body p-1">
							<table class="table table-sm mb-0">
								<thead>
									<tr>
										<th style="width:15px;"></th>
										<th scope="col" class="align-middle text-right">Acci&oacute;n</th>
										<th scope="col" class="align-middle text-right w-td-100">Registros</th>
									</tr>
								</thead>
								<tbody>
									<tr class="table-secondary">
										<td>&nbsp;</td>
										<td class="text-right">Expiraron sin acción</td>
										<td class="text-right"><?php print($lnDatoConductaExpiro>0?sprintf('<span class="parpadea">%s</span>',$lnDatoConductaExpiro):'-'); ?></td>
									</tr>
									<?php
										foreach($laDatosConductas as $lnDatoConducta => $laDatoConducta){
									?>
									<tr style="color: #000000; background-color: <?php print($laDatoConducta['color']);?>;">
										<td style="color: #000000; background-color: #8bc34a;">&nbsp;</td>
										<td class="text-right"><?php print($laDatoConducta['NOMBRE']); ?></td>
										<td class="text-right"><?php print($laDatoConducta['value']>0?sprintf('<span>%s</span>',$laDatoConducta['value']):'-'); ?></td>
									</tr>
									<?php
											$laDatosConductaTotal[0]+=$laDatoConducta['value'];
										}
										$laDatosConductaTotal[0]+=$lnDatoConductaExpiro;
									?>
								</tbody>
								<tfoot>
									<tr>
										<td></td>
										<td></td>
										<?php
											foreach($laDatosConductaTotal as $lnSumRows){
												printf('<td class="text-right"><b>%s</b></td>',($lnSumRows>0?$lnSumRows:'-'));
											}
										?>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>

			<!-- estadisticas -->
			<div class="row">
				<!-- ALERTAS -->
				<div class="col-md-4 pl-2 pt-2 pr-1 pb-0 h-100">
					<div class="card h-100">
						<div class="card-body h-100">
							<div class="row justify-content-md-center h-100">
								<div class="col-md-12 h-100">
									<div id="canvas-holder-1" style="width:100%; height:100%; min-height:100%;">
										<canvas id="chart-area-1"></canvas>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- ACCIONES -->
				<div class="col-md-4 pl-2 pt-2 pr-1 pb-0 h-100">
					<div class="card h-100">
						<div class="card-body h-100">
							<div class="row justify-content-md-center h-100">
								<div class="col-md-12 h-100">
									<div id="canvas-holder-2" style="width:100%; height:100%; min-height:100%;">
										<canvas id="chart-area-2"></canvas>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- ACCIONES -->
				<div class="col-md-4 pl-2 pt-2 pr-1 pb-0 h-100">
					<div class="card h-100">
						<div class="card-body h-100">
							<div class="row justify-content-md-center h-100">
								<div class="col-md-12 h-100">
									<div id="canvas-holder-3" style="width:100%; height:100%; min-height:100%;">
										<canvas id="chart-area-3"></canvas>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- estadisticas -->
			<div class="row">
				<div class="col-md-4 pl-2 pt-2 pr-1 pb-0 h-100">
					<div class="card h-100">
						<div class="card-body text-center p-1">
							<?php
								$lnDatoGrupoTimeMax = 0;
								foreach($laDatosGrupo as $lnDatoGrupo => $laDatoGrupo){
									if($lnDatoGrupo>=2){
										$lnDatoGrupoTimeMax = ($lnDatoGrupoTimeMax<$laDatoGrupo['answered_time_max']?$laDatoGrupo['answered_time_max']:$lnDatoGrupoTimeMax);
							?>
							<div id="container<?php print($lnDatoGrupo); ?>" class="container-bar"></div>
							<?php
									}
								}
							?>

							<script type="text/javascript">
								<?php
									foreach($laDatosGrupo as $lnDatoGrupo => $laDatoGrupo){
										if($lnDatoGrupo>=2){
											$laResultado = $loSignosNews->getRespuestaClasificacion($lnDatoGrupo);
											//bullet-graph
								?>
								Highcharts.chart('container<?php print($lnDatoGrupo); ?>', {
									chart: {
										marginTop: 0,
										inverted: true,
										marginLeft: 150,
										type: 'bullet'
									},
									title: {
										text: null
									},
									legend: {
										enabled: false
									},
									xAxis: {
										categories: ['<b><?php print($laDatoGrupo['name']); ?></b><br/>Max. respuesta <?php print($laResultado['decision']['tiempo']); ?>&quot;']
									},
									yAxis: {
										gridLineWidth: 0,
										plotBands: [{
											from: 0,
											to: <?php print($laDatoGrupo['answered_time_min']); ?>,
											color: '#dddddd'
										}, {
											from: <?php print($laDatoGrupo['answered_time_min']); ?>,
											to: <?php print($laDatoGrupo['answered_time_max']); ?>,
											color: '#<?php print($lnDatoGrupo==2?'ffc107':'dc3545'); ?>'
										}, {
											from: <?php print($laDatoGrupo['answered_time_max']); ?>,
											to: <?php print($lnDatoGrupoTimeMax+100); ?>,
											color: '#dddddd'
										}],
										title: null,
										max: <?php print($lnDatoGrupoTimeMax+1); ?>,
										min: 0,
									},
									plotOptions: {
										line: {
											dataLabels: {
												enabled: true
											},
											enableMouseTracking: false
										},
										series: {
											pointPadding: 0.25,
											borderWidth: 0,
											color: '#000',
											targetOptions: {
												width: '300%',
												color: '#28a745'
											}
										}
									},
									series: [{
										data: [{
											y: <?php print($laDatoGrupo['answered']>0?(intval($laDatoGrupo['answered_time']/$laDatoGrupo['answered'])):0); ?>,
											target: <?php print($laResultado['decision']['tiempo']); ?>,
										}]
									}],
									tooltip: {
										pointFormat: 'Tiempo promedio de respuesta <b>{point.y}</b>&quot; (objetivo {point.target}&quot;)'
									},
									<?php if($lnDatoGrupo==3){ ?>
									credits:{
										enabled:true,
										href:'#',
										style:{"cursor": "pointer", "color": "#000000", "fontSize": "10px"},
										text:'<b>Información del día <?php print($ltAhora->format("d")); ?> de <?php print($laMeses[intval($ltAhora->format("m"))-1]); ?></b>'
									},
									<?php } else { ?>
									credits:{
										enabled:false,
									},
									<?php } ?>
									exporting: {
										enabled: false
									}
								});
								<?php
										}
									}
								?>
							</script>

						</div>
					</div>
				</div>
				<div class="col-md-4 pl-2 pt-2 pr-1 pb-0 h-100">
					<div class="card h-100">
						<div class="card-body text-center p-1">
							<?php if(isset($laDatosAcumuladosRespuestasMes)==true){ ?>
							<div id="containerc" style="height:142px;"></div>
							<script type="text/javascript">
								Highcharts.chart('containerc', {
									title: {
										text: null
									},
									subtitle: {
										text: null
									},
									xAxis: {
										categories: [<?php print(getListFromArray($laDatosAcumuladosRespuestasMes,'DIA', "'", "'")); ?>],
										title: {
											text: 'Día'
										}
									},
									yAxis: {
										title: {
											text: 'Tiempo'
										}
									},
									legend: {
										layout: 'vertical',
										align: 'left',
										verticalAlign: 'middle'
									},
									plotOptions: {
										line: {
											dataLabels: {
												enabled: true
											},
											enableMouseTracking: false
										}
									},
									series: [{
										name: 'Alerta Amarilla',
										data: [<?php print(getListFromArray($laDatosAcumuladosRespuestasMes,'AA', "", "")); ?>],
										color: '#ffc107'
									}, {
										name: 'Alerta Roja',
										data: [<?php print(getListFromArray($laDatosAcumuladosRespuestasMes,'AR', "", "")); ?>],
										color: '#dc3545'
									}],
									responsive: {
										rules: [{
											condition: {
												maxWidth: 500
											},
											chartOptions: {
												legend: {
													layout: 'horizontal',
													align: 'center',
													verticalAlign: 'bottom'
												}
											}
										}]
									},
									credits:{
										enabled:true,
										href:'#',
										style:{"cursor": "pointer", "color": "#000000", "fontSize": "10px"},
										text:'<b>Información del mes de <?php print($laMeses[intval($ltAhora->format("m"))-1]); ?></b>'
									},
								});
							</script>
							<?php } else { ?>
								<div class="alert alert-danger" role="alert">No se ejecuto la consulta laDatosAcumuladosRespuestasMes</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<div class="col-md-4 pl-2 pt-2 pr-1 pb-0 h-100">
					<div class="card h-100">
						<div class="card-body text-center p-1">
							<?php if(isset($laDatosAcumuladosRespuestasMes)==true){ ?>
							<div id="containerd" style="height:142px;"></div>
							<script type="text/javascript">
								Highcharts.chart('containerd', {
									title: {
										text: null
									},
									subtitle: {
										text: null
									},
									xAxis: {
										categories: [<?php print(getListFromArray($laDatosAcumuladosRespuestasMes,'DIA', "'", "'")); ?>],
										title: {
											text: 'Día'
										}
									},
									yAxis: {
										title: {
											text: 'Alertas'
										}
									},
									legend: {
										layout: 'vertical',
										align: 'left',
										verticalAlign: 'middle'
									},
									plotOptions: {
										line: {
											dataLabels: {
												enabled: true
											},
											enableMouseTracking: false
										}
									},
									series: [{
										name: 'Sin acción',
										data: [<?php print(getListFromArray($laDatosAcumuladosRespuestasMes,'SIN', "", "")); ?>],
										color: '#9e9e9e'
									}, {
										name: 'Con acción',
										data: [<?php print(getListFromArray($laDatosAcumuladosRespuestasMes,'CON', "", "")); ?>],
										color: '#8bc34a'
									}],
									responsive: {
										rules: [{
											condition: {
												maxWidth: 500
											},
											chartOptions: {
												legend: {
													layout: 'horizontal',
													align: 'center',
													verticalAlign: 'bottom'
												}
											}
										}]
									},
									credits:{
										enabled:true,
										href:'#',
										style:{"cursor": "pointer", "color": "#000000", "fontSize": "10px"},
										text:'<b>Información del mes de <?php print($laMeses[intval($ltAhora->format("m"))-1]); ?></b>'
									},
								});
							</script>
							<?php } else { ?>
								<div class="alert alert-danger" role="alert">No se ejecuto la consulta laDatosAcumuladosRespuestasMes</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<?php if(is_array($laDatosClasificacionDia)==true){ ?>
				<div class="col-md-4 pl-2 pt-2 pr-1 pb-0 h-100">
					<div class="card h-100">
						<div class="card-body text-right p-1">
							<h4>Hora de las &uacute;ltimas alertas</h4>
							<?php
								if(count($laDatosClasificacionDia)>0){
									foreach($laDatosClasificacionDia as $lnDatoClasificacionDia => $laDatoClasificacionDia){
										$lcHora = str_pad($laDatoClasificacionDia['HORA'], 6, "0", STR_PAD_LEFT);
										$lcHora = $ltAhora->format("Y/m/d")." ".substr($lcHora,0,2).":".substr($lcHora,2,2).":".substr($lcHora,4,2) ;

										$ltHora = strtotime($lcHora);
										$lcFecha = date("Ymd",$ltHora);
										$lcHora  = date("h:i:s a",$ltHora);

										$lcParpadea="";
										if($laDatoClasificacionDia['CLASIF']==2){
											$lcParpadea=($laDatosGrupo[2]['actives']>0?'parpadea':'');
										}else{
											$lcParpadea=($laDatosGrupo[3]['actives']>0?'parpadea':'');
										}

							?> <span class="btn btn-<?php print($laDatoClasificacionDia['CLASIF']==2?'warning':'danger'); ?> ml-1 p-1 <?php print($lcParpadea); ?>"><?php print(($laDatoClasificacionDia['CLASIF']==2?'Amarilla':'Roja')." <b>".$lcHora."</b>"); ?></span> <?php
									}
								}else{
							?> <span  class="btn btn-secondary">No hay información para el d&iacute;a</span>
							<?php
								}
							?>
						</div>
					</div>
				</div>
				<?php } ?>
				<div class="col-md-4 pl-2 pt-2 pr-1 pb-0 h-100">
					<div class="card h-100">
						<div class="card-body text-right p-1">
							<h4>"Top" 5 de respuestas por equipo</h4>
							<?php
								if(isset($laTopEquipoRespuestasDia)==true){
									if(is_array($laTopEquipoRespuestasDia)==true){
										if(count($laTopEquipoRespuestasDia)>0){
											foreach($laTopEquipoRespuestasDia as $laTopEquipoRespuestaDia){
												printf('<span class="btn btn-outline-secondary ml-1 p-1">%s <sup class="badge badge-secondary">%s</sup></span>',trim($laTopEquipoRespuestaDia['EQUIPO']),$laTopEquipoRespuestaDia['REGISTROS']);
											}
										}else{
											print('<span  class="btn btn-outline-secondary">No hay información para el d&iacute;a</span>');
										}
									}else{
										print('<span  class="btn btn-outline-secondary">No hay información para el d&iacute;a</span>');
									}
								}else{
									print('<span  class="btn btn-outline-secondary">No hay información para el d&iacute;a</span>');
								}
							?>
						</div>
					</div>
				</div>
			</div>

		</div>


		<?php if($lnDatosGruposTotal==0){ ?>
		<div class="modal" tabindex="-1" role="dialog" id="noData">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-body text-center">
						<h1 class="text-success"><i class="fas fa-info-circle fa-5x"></i><br/>No hay alertas registradas para el día <br/><b><?php print($ltAhora->format("Y-m-d")); ?></b><h1>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>

		<div style="position: fixed; float:left; bottom: 5px; left: 0; padding-right: 8px; padding-left: 8px; padding-bottom: 8px; width:100%;">
			<?php if($laDatosGrupo[2]['actives']>0) { ?><div class="btn btn-warning mb-0 text-center text-wrap w-100" role="alert"><span class="parpadea alerta"><b><i class="fas fa-exclamation-circle"></i> ATENCIÓN! <?php print($laDatosGrupo[2]['actives']); ?> alerta(s) amarilla(s)</b> por responder</span></div><?php } ?>
			<?php if($laDatosGrupo[3]['actives']>0) { ?><div class="btn btn-danger mb-0 mt-2 text-center text-wrap w-100" role="alert"><span class="parpadea alerta"><b><i class="fas fa-exclamation-triangle"></i> ATENCIÓN! <?php print($laDatosGrupo[3]['actives']); ?> alerta(s) roja(s)</b> por responder</span></div><?php } ?>
			<?php if(!empty($lcError)){?><div class="btn btn-light mb-0 mt-2 text-center text-wrap w-100" role="alert"><i class="fas fa-exclamation-triangle text-danger"></i> Error en actualizaci&oacute;n: <?php print($lcError); ?></div><?php } ?>
		</div>
		<div style="position: absolute; float:right; bottom: 0; right: 0; padding-right: 8px; padding-bottom: 8px;">
			<button id="cmdActualizar" class="btn btn-sm btn-outline-primary"><i class="fas fa-sync fa-spin"></i> <span class="messageActualizar"></span></button>
		</div>

		<script>
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
			});

			var ctx1 = document.getElementById('chart-area-1').getContext('2d');
			var chart1 = new Chart(ctx1, {
				type: 'doughnut',
				data: {
					labels: [<?php print(getListFromArray($laDatosGrupo,'name',"'","'")); ?>],
					datasets: [{
						borderWidth: 1,
						borderColor: '#343a40',
						label: 'DSAT',
						backgroundColor: [<?php print(getListFromArray($laDatosGrupo,'color',"'","'")); ?>],
						data: [<?php print(getListFromArray($laDatosGrupo,'value',"'","'")); ?>]
					}]
				},
				options: {
					title: {
						display: true,
						text: 'Distribución de Alertas',
						fontColor: '#000000',
					},
					animation: {
						duration: 3000,
						easing: 'easeInOutCubic',
					},
					legend: {
						display: true,
						position: 'bottom',
						labels: {
							fontColor: '#000000'
						}
					}
				},
			});

			var ctx2 = document.getElementById('chart-area-2').getContext('2d');
			var chart2 = new Chart(ctx2, {
				type: 'doughnut',
				data: {
					labels: [<?php print(getListFromArray($laDatosConductas,'NOMBRE',"'","'")); ?>,'Expiraron'],
					datasets: [{
						borderWidth: 1,
						borderColor: '#343a40',
						label: 'DSAT',
						backgroundColor: [<?php print(getListFromArray($laDatosConductas,'color',"'","'").",'#9e9e9e'"); ?>],
						data: [<?php print(getListFromArray($laDatosConductas,'value',"'","'").",".$lnDatoConductaExpiro); ?>]
					}]
				},
				options: {
					title: {
						display: true,
						text: 'Distribución de acciones',
						fontColor: '#000000',
					},
					animation: {
						duration: 3000,
						easing: 'easeInOutCubic',
					},
					legend: {
						display: true,
						position: 'bottom',
						labels: {
							fontColor: '#000000'
						}
					}
				},
			});

			var ctx3 = document.getElementById('chart-area-3').getContext('2d');
			var chart3 = new Chart(ctx3, {
				type: 'doughnut',
				data: {
					labels: ['Con Acción','Sin acción'],
					datasets: [{
						borderWidth: 1,
						borderColor: '#343a40',
						label: 'DSAT',
						backgroundColor: ['#8bc34a','#9e9e9e'],
						data: [<?php print($laDatosConductaTotal[0]-$lnDatoConductaExpiro); ?>,<?php print($lnDatoConductaExpiro); ?>]
					}]
				},
				options: {
					title: {
						display: true,
						text: 'Distribución de respuestas (<?php print($laDatosGrupo[2]['name']); ?> y <?php print($laDatosGrupo[3]['name']); ?>)',
						fontColor: '#000000',
					},
					animation: {
						duration: 3000,
						easing: 'easeInOutCubic',
					},
					legend: {
						display: true,
						position: 'bottom',
						labels: {
							fontColor: '#000000'
						}
					}
				},
			});
			<?php if($lnDatosGruposTotal==0){ ?>
			$('#noData').modal('show');
			<?php } ?>
		</script>
	</body>
</html>