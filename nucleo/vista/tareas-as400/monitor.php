<?php
	require_once (__DIR__ .'/../../publico/constantes.php') ;
	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionFunciones.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Db.php');

	use NUCLEO\AplicacionFunciones;

	$ltAhora = new DateTime($goDb->fechaHoraSistema());
	$lnTimeMaxSeg=180;
	$lnFallas=0;
	$lnCPU = 0;

	$laExcepciones = ['NAME'=>[], 'TYPE'=>[]];
	$laJobNameException = [];
	$laJobTypeException = ['WTR'];

	$laJobsType=[
					'ASJ'=>['KEY'=>'ASJ', 'ALIAS'=>'AUTOINICIO',    'NAME'=>'Autostart', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#3bd46a'],
					'BCH'=>['KEY'=>'BCH', 'ALIAS'=>'LOTE'     ,     'NAME'=>'Batch', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#23b34f'],
					'BCI'=>['KEY'=>'BCI', 'ALIAS'=>'LOTE INTERACT.','NAME'=>'Batch Immediate', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#23b34f'],
					'EVK'=>['KEY'=>'EVK', 'ALIAS'=>'PROCEDIMIENTO', 'NAME'=>'Started by a procedure start request', 'JOBS'=>0, 'VIEW'=>false, 'COLOR'=>'#23b34f'],
					'INT'=>['KEY'=>'INT', 'ALIAS'=>'INTERACTIVO',   'NAME'=>'Interactive', 'JOBS'=>0, 'VIEW'=>false, 'COLOR'=>'#c3d437'],
					'M36'=>['KEY'=>'M36', 'ALIAS'=>'AVANZADO',      'NAME'=>'Advanced 36 server job', 'JOBS'=>0, 'VIEW'=>false, 'COLOR'=>'#d4a437'],
					'MRT'=>['KEY'=>'MRT', 'ALIAS'=>'MULTIRESPUESTA','NAME'=>'Multiple requester terminal', 'JOBS'=>0, 'VIEW'=>false, 'COLOR'=>'#d4a437'],
					'PDJ'=>['KEY'=>'PDJ', 'ALIAS'=>'T.IMPRESORA',   'NAME'=>'Print driver job', 'JOBS'=>0, 'VIEW'=>false, 'COLOR'=>'#d437c8'],
					'PJ' =>['KEY'=>'PJ' , 'ALIAS'=>'TRAB.PREINI.',  'NAME'=>'Prestart job', 'JOBS'=>0, 'VIEW'=>false, 'COLOR'=>'#d437c8'],
					'RDR'=>['KEY'=>'RDR', 'ALIAS'=>'R.SPOOL',       'NAME'=>'Spool reader', 'JOBS'=>0, 'VIEW'=>false, 'COLOR'=>'#e865dc'],
					'SBS'=>['KEY'=>'SBS', 'ALIAS'=>'SUBSISTEMA',    'NAME'=>'Subsystem monitor', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#d6b5d3'],
					'SYS'=>['KEY'=>'SYS', 'ALIAS'=>'SISTEMA',       'NAME'=>'System', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#672a62'],
					'WTR'=>['KEY'=>'WTR', 'ALIAS'=>'SPOOL',         'NAME'=>'Spool writer', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#d437c8']
				];

	$laJobsStatusDanger = ['MSGW'];
	$laJobsStatus=[
					'CMNW'=>['KEY'=>'CMNW', 'ALIAS'=>'E/S', 'NAME'=>'Esperando la finalización de una operación de E/S a un dispositivo de comunicaciones.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#ecf1a4'],
					'CNDW'=>['KEY'=>'CNDW', 'ALIAS'=>'CONDICION', 'NAME'=>'Esperando una condición basada en el mango.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#d6dc84'],
					'DEQW'=>['KEY'=>'DEQW', 'ALIAS'=>'COLA', 'NAME'=>'Esperando la finalización de una operación de eliminación de cola.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#b9bf6a'],
					'DLYW'=>['KEY'=>'DLYW', 'ALIAS'=>'DELAY', 'NAME'=>'Debido al comando Delay Job (DLYJOB), el subproceso inicial del trabajo se retrasa mientras espera que finalice un intervalo de tiempo o una hora de finalización de retraso específica.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#e0e874'],
					'DSPW'=>['KEY'=>'DSPW', 'ALIAS'=>'PANTALLA', 'NAME'=>'Esperando entrada desde la pantalla de una estación de trabajo.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#c5ce51'],
					'END' =>['KEY'=>'END' , 'ALIAS'=>'FINALIZADO', 'NAME'=>'El trabajo ha finalizado con la opción * IMMED o su tiempo de retraso ha finalizado con la opción * CNTRLD.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#d8e440'],
					'EOJ' =>['KEY'=>'EOJ' , 'ALIAS'=>'F.FINALIZADO', 'NAME'=>'Finalizar por un motivo que no sea ejecutar el mandato Finalizar trabajo (ENDJOB) o Finalizar subsistema (ENDSBS).', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#e3f321'],
					'EVTW'=>['KEY'=>'EVTW', 'ALIAS'=>'EVENTO', 'NAME'=>'Esperando un evento.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#21f3e9'],
					'HLD' =>['KEY'=>'HLD' , 'ALIAS'=>'RETENIDO', 'NAME'=>'El trabajo está retenido.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#e47415'],
					'JVAW'=>['KEY'=>'JVAW', 'ALIAS'=>'JAVA', 'NAME'=>'Esperando la finalización de una operación de programa Java.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#ce670f'],
					'LCKW'=>['KEY'=>'LCKW', 'ALIAS'=>'BLOQUEO', 'NAME'=>'Esperando un candado.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#ffc107'],
					'LSPW'=>['KEY'=>'LSPW', 'ALIAS'=>'L.ACCESO', 'NAME'=>'Esperando que se adjunte un espacio de bloqueo.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#fbc113'],
					'MSGW'=>['KEY'=>'MSGW', 'ALIAS'=>'MENSAJE', 'NAME'=>'Esperando un mensaje de una cola de mensajes.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#dc3545'],
					'MTXW'=>['KEY'=>'MTXW', 'ALIAS'=>'MUTEX', 'NAME'=>'Esperando una mutex.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#f3752b'],
					'PSRW'=>['KEY'=>'PSRW', 'ALIAS'=>'ACCESO', 'NAME'=>'Un trabajo de prearranque esperando una solicitud de inicio de programa.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#f3e97a'],
					'RUN' =>['KEY'=>'RUN' , 'ALIAS'=>'CORRIENDO', 'NAME'=>'El trabajo se está ejecutando actualmente.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#2ee057'],
					'SEMW'=>['KEY'=>'SEMW', 'ALIAS'=>'SEMAFORO', 'NAME'=>'Esperando un semáforo.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#85f54c'],
					'THDW'=>['KEY'=>'THDW', 'ALIAS'=>'HILO', 'NAME'=>'Esperando que otro hilo complete una operación.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#26e4ca']
				];

	$laTareas=array();



	$laTareasObligatorias = [
					'ALPDTAQ' =>['KEY'=>'ALPDTAQ', 'NAME'=>'Cola de propósito general', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#ecf1a4'],
					'ALPDTAQB'=>['KEY'=>'ALPDTAQB', 'NAME'=>'Cola de propósito general', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#d6dc84'],
					'ALPDTAQC'=>['KEY'=>'ALPDTAQC', 'NAME'=>'Cola de propósito general', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#b9bf6a'],
					'ALPDTAQE'=>['KEY'=>'ALPDTAQE', 'NAME'=>'Cola de propósito general', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#e0e874'],
					'ALPINVA' =>['KEY'=>'ALPINVA', 'NAME'=>'Tarea de Inventarios.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#c5ce51'],
					'LABSINC' =>['KEY'=>'LABSINC', 'NAME'=>'Tarea de laboratorio.', 'JOBS'=>0, 'VIEW'=>true, 'COLOR'=>'#d8e440'],
				];


	if(isset($goDb)){
		
		//Uso CPU
		$lcSql ="SELECT SUM(ELAPSED_CPU_PERCENTAGE) AS CPU FROM TABLE(QSYS2.ACTIVE_JOB_INFO('%s','','','')) AS X";
		$laTareas = $goDb->query(sprintf($lcSql,'YES'));
		$laTareas = $goDb->query(sprintf($lcSql,'NO'));
		$lnCPU = floatval($laTareas[0]->CPU);

		// Listando tareas
		$lcSql ="SELECT * FROM TABLE(QSYS2.ACTIVE_JOB_INFO('NO','','','')) AS X";
		$laTareas = $goDb->query($lcSql);		

		if(is_array($laTareas)==true){
			if(count($laTareas)>0){
				$laProperties=array();
				foreach($laTareas as $lnTarea=>$laTarea){

					// Obteniendo solo el nombre
					$lcJobName = str_replace("/",";",$laTarea->JOB_NAME);
					$laJobName = explode(";",$lcJobName);
					if(is_array($laJobName)==true){
						array_map('trim', $laJobName);
						if(count($laJobName)==3){
							if(isset($laTareasObligatorias[$laJobName[2]])==true){
								$laTareasObligatorias[$laJobName[2]]['JOBS']+=1;
							}
							$lcJobName = trim($laJobName[2]);
						}
					}
					
					// Si el nombre no esta en excepcion
					if(in_array($lcJobName, $laJobNameException)==false){
						
						// Se cuenta el tipo de trabajo
						if(is_null($laTarea->JOB_TYPE)==false){
							if(isset($laJobsType[$laTarea->JOB_TYPE])==true){
								$laJobsType[$laTarea->JOB_TYPE]['JOBS']+=1;
							}
						}													

						// Si el tipo de trabajo no esta en exceocion para estado
						if(in_array($laTarea->JOB_TYPE, $laJobTypeException)==false){
							
							// Se cuenta el estado
							if(is_null($laTarea->JOB_STATUS)==false){
								if(isset($laJobsStatus[$laTarea->JOB_STATUS])==true){
									$laJobsStatus[$laTarea->JOB_STATUS]['JOBS']+=1;
								}
							}								
						}else{
							if(isset($laExcepciones['TYPE'][$laTarea->JOB_TYPE])==false){
								$laExcepciones['TYPE'][$laTarea->JOB_TYPE]=1;
							}else{
								$laExcepciones['TYPE'][$laTarea->JOB_TYPE]+=1;
							}
						}
						
					}else{
						if(isset($laExcepciones['NAME'][$lcJobName])==false){
							$laExcepciones['NAME'][$lcJobName]=1;
						}else{
							$laExcepciones['NAME'][$lcJobName]+=1;
						}
					}						
				}
			}
		}
	};
	$lnFallas = 0;
	$laFallas = array();
	foreach($laJobsStatusDanger as $lcJobStatusDanger){
		if($laJobsStatus[$lcJobStatusDanger]['JOBS']>0){
			$laFallas[$lcJobStatusDanger] = ['NAME'=>$laJobsStatus[$lcJobStatusDanger]['NAME'], 'JOBS'=>$laJobsStatus[$lcJobStatusDanger]['JOBS']];
			$lnFallas += $laJobsStatus[$lcJobStatusDanger]['JOBS'];
		}
	}

	$lnFallasObligatorias = 0;
	foreach($laTareasObligatorias as $laTareaObligatorias){
		if($laTareaObligatorias['JOBS']<=0){
			$lnFallasObligatorias += 1;
		}
	}
	$lnFallas += $lnFallasObligatorias;

	// Funciones especiales
	function getListFromArray($taDatos, $tvField, $tcPrefix='', $tcPostfix='', $tcType='', $tnMin=0, $tnMax=0){
		$lcList = '';
		$lcListAux = '';
		if(is_array($taDatos)==true){
			foreach($taDatos as $laDato){
				if(is_array($laDato)==true){

					if($laDato['VIEW']==true && $laDato['JOBS']>0){
						switch ($tcType) {
							default:
								$laFieldsKey=array();
								if(is_array($tvField)==true){
									$laFieldsKey = $tvField;
								}else{
									$laFieldsKey[] = $tvField;
								}
								
								$lcValueKey = '';
								foreach($laFieldsKey as $lcField){
									if(isset($laDato[$lcField])==true){
										$lcValueKey .= (empty($lcValueKey)==true?'':' - ').$laDato[$lcField];
									}
								}
									
								if(empty($lcValueKey)==false){
									$lcList .= (empty($lcList)==true?'':',').$tcPrefix.$lcValueKey.$tcPostfix;
								}else{
									$lcListAux = getListFromArray($laDato,$tvField,$tcPrefix,$tcPostfix);
									if(empty($lcListAux)==false){
										$lcList .= (empty($lcList)==true?'':',').$lcListAux;
									}
								}
								break;
						}						
					}
				}
			}
		}
		return $lcList;
	}

	function porcentaje($a, $b){
		return (($b*100)/$a);
		
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
			$lcInclude = str_replace("vista-comun","../vista-comun",$lcInclude);
			$lcInclude = str_replace("hcw-manifiest.json.php","../hcw-manifiest.json.php",$lcInclude);
			print($lcInclude);
		?>
		<script src="../publico-complementos/Chart.js/2.8.0/Chart.min.js"></script>

		<style>
			body{
				background-color: #1f1d1d !important;
				color: #ffffff;
			}
			.card{
			    background-color: transparent !important;
			}
			.anteriores{
				font-size: 2.8em !important;
			}
			.hoy{
				font-size: 2em !important;
			}
			.filaPrincipal th{
				width:25%;
			}
		</style>
	</head>
	<body class="h-100">
		<div class="container-fluid h-100">
			<div class="card h-100">
				<div id="banner" class="card-header text-white <?php print($lnFallas>0?"bg-danger":"bg-success"); ?> mt-3">
					<h2>Tareas <?php print(count($laTareas)); ?><div class="float-right"><small><span class="badge badge-dark">AS400 <?php print($ltAhora->format("Y-m-d H:i:s")); ?></span></small></div></h2>
				</div>			
				<div class="card-body h-100">
					<div class="row h-100">
						<div class="col-4 h-100">
							<div class="row h-50">
								<!-- TIPO -->
								<div class="col-12 pl-2 pt-2 pr-1 pb-0 h-100">
									<div class="card border-<?php print($lnFallas>0?'danger':'success'); ?> h-100">
										<div class="card-header bg-<?php print($lnFallas>0?'danger':'success'); ?>"><b>Tareas por tipo</b></span></div>
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
							</div>
							<div class="row h-50">
								<!-- ESTADO -->
								<div class="col-12 pl-2 pt-2 pr-1 pb-0 h-100">
									<div class="card border-<?php print($lnFallas>0?'danger':'success'); ?> h-100">
										<div class="card-header bg-<?php print($lnFallas>0?'danger':'success'); ?>"><b>Tareas por estado</b></span></div>
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
							</div>
						</div>
						<div class="col-8 h-100">
							<div class="row h-100">
							
								<!-- PROCESOS EN MSGW -->
								<div class="col-md-6 pl-2 pt-2 pr-1 pb-0 h-100">
									<div class="card border-<?php print($lnFallas>0?'danger':'success'); ?> h-100">
										<div class="card-header bg-<?php print($lnFallas>0?'danger':'success'); ?>"><b><?php print($lnFallas>0?'<i class="fas fa-exclamation-triangle mr-2"></i>':'<i class="fas fa-sync fa-spin align-self-center mr-2"></i>'); ?>Global</b> | <?php print($lnFallas>0?sprintf('Existen %s tarea(s) alarmada(s)', $lnFallas):'No hay tareas alarmadas'); ?></div>
										<div class="card-body">
											<h5 class="card-title">
												<?php print($lnFallas>0?'Existen':'No existen'); ?> tareas en estado
												<?php
													foreach($laJobsStatusDanger as $lcJobStatusDanger){
														printf(' <span class="badge badge-light">%s</span>', $lcJobStatusDanger);
													}
												?>
											</h5>
											<p class="border-top border-light">
												<h6>Excepciones por nombre de trabajo</h6>
												<?php
													foreach($laJobNameException as $lcJobNameException){
														printf(' <span class="badge badge-dark font-weight-light">%s: %s</span>', $lcJobNameException, (isset($laExcepciones['NAME'][$lcJobNameException])?$laExcepciones['NAME'][$lcJobNameException]:0));
													}
												?>
											</p>
											<p class="border-top border-light">
												<h6>Excepciones por tipo de trabajo</h6>
												<?php
													foreach($laJobTypeException as $lcJobTypeException){
														printf(' <span class="badge badge-dark font-weight-light">%s - %s: %s</span>', $lcJobTypeException, $laJobsType[$lcJobTypeException]['ALIAS'], (isset($laExcepciones['TYPE'][$lcJobTypeException])?$laExcepciones['TYPE'][$lcJobTypeException]:0));
													}
												?>
											</p>
										</div>
										<ul class="list-group list-group-flush">
											<?php
												if(is_array($laFallas)==true){
													if(count($laFallas)>0){
														foreach($laFallas as $lcFalla=>$laFalla){
															printf('<li class="list-group-item d-flex justify-content-between align-items-center bg-danger p-1"><span><b>%s</b> <small>%s</small></span><span class="badge badge-light badge-pill">%s</span></li>', $lcFalla, $laFalla['NAME'], $laFalla['JOBS']);
														}
													}
												}
											?>
										</ul>
										<div class="card-footer text-white font-weight-bolder">
											<div class="progress">
												<div class="progress-bar progress-bar-striped progress-bar-animated <?php print($lnCPU>90?'bg-danger':($lnCPU>70?'bg-warning':($lnCPU>50?'bg-primary':'bg-sucess'))); ?>" role="progressbar" style="width: <?php print(intval($lnCPU)); ?>%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">CPU: <?php print($lnCPU); ?>%</div>
											</div>
											<?php if($lnFallas>0){ ?>
												<iframe src="bell-ringing-01.mp3" allow="autoplay" id="audio" style="display: none"></iframe>
												<audio id="player" autoplay loop>
													<source src="bell-ringing-01.mp3" type="audio/mp3">
												</audio>									
											<?php } ?>										
										</div>
									</div>
								</div>

								<!-- TAREAS OBLIGATORIAS -->
								<div class="col-6 pl-2 pt-2 pr-1 pb-0 h-100">
									<div class="card border-<?php print($lnFallasObligatorias>0?'danger':'success'); ?> h-100">
										<div class="card-header bg-<?php print($lnFallasObligatorias>0?'danger':'success'); ?>"><b><?php print($lnFallasObligatorias>0?'<i class="fas fa-exclamation-triangle mr-2"></i>':'<i class="fas fa-sync fa-spin align-self-center mr-2"></i>'); ?>Obligatorias</b> | <?php print($lnFallasObligatorias>0?sprintf('Existen %s tarea(s) obligatoria(s) que no iniciada(s)', $lnFallasObligatorias):'Las tareas obligatorias est&aacute;n iniciadas'); ?></div>
										<div class="card-body">
											<h5 class="card-title">
												<?php print($lnFallasObligatorias>0?'Tareas obligatorias no iniciadas':'Todas las tareas obligatorias iniciadas'); ?>
												<?php
													foreach($laTareasObligatorias as $laTareaObligatorias){
														if($laTareaObligatorias['JOBS']<=0){
															printf(' <span class="badge badge-light">%s</span>', $laTareaObligatorias['KEY']);
														}
													}
												?>
											</h5>
										</div>
										<ul class="list-group list-group-flush">
											<?php
												if(is_array($laTareasObligatorias)==true){
													if(count($laTareasObligatorias)>0){
														foreach($laTareasObligatorias as $laTareaObligatorias){
															printf('<li class="list-group-item d-flex justify-content-between align-items-center bg-%s p-1"><span><b>%s</b> <small>%s</small></span><span class="badge badge-light badge-pill">%s</span></li>', ($laTareaObligatorias['JOBS']<=0?'danger':'success'), $laTareaObligatorias['KEY'], $laTareaObligatorias['NAME'], $laTareaObligatorias['JOBS']);
														}
													}
												}
											?>
										</ul>
										<div class="card-footer text-white font-weight-bolder">
											<div class="progress">
												<div class="progress-bar progress-bar-striped progress-bar-animated <?php print($lnFallasObligatorias>0?'bg-danger':'bg-success'); ?>" role="progressbar" style="width: <?php print(intval(porcentaje(count($laTareasObligatorias),count($laTareasObligatorias)-$lnFallasObligatorias))); ?>%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">Tareas obligatorias: <?php print(intval(porcentaje(count($laTareasObligatorias),count($laTareasObligatorias)-$lnFallasObligatorias))); ?>%</div>
											</div>										
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- <div class="card-footer"></div> -->
			</div>
		</div>
		<div style="position: fixed; float:right; bottom: 0; right: 0; padding-right: 8px; padding-bottom: 8px;">
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

				var ctx1 = document.getElementById('chart-area-1').getContext('2d');
				var chart1 = new Chart(ctx1, {
					type: 'pie',
					data: {
						labels: [<?php print(getListFromArray($laJobsType,['KEY','ALIAS'],"'","'")); ?>],
						datasets: [{
							borderWidth: 1,
							borderColor: '#ffffff',
							label: 'JOBS (Type)',
							backgroundColor: [<?php print(getListFromArray($laJobsType,'COLOR',"'","'")); ?>],
							data: [<?php print(getListFromArray($laJobsType,'JOBS',"'","'")); ?>],
						}]
					},
					options: {
						title: {
							display: false,
						},
						animation: {
							duration: 3000,
							easing: 'easeInOutElastic',
						},
						legend: {
							display: true,
							position: 'bottom',
							labels: {
								fontColor: '#ffffff'
							}
						}						
					}
				});


				var ctx2 = document.getElementById('chart-area-2').getContext('2d');
				var chart2 = new Chart(ctx2, {
					type: 'radar',
					data: {
						labels: [<?php print(getListFromArray($laJobsStatus,['KEY','ALIAS'],"'","'")); ?>],
						datasets: [{
							borderWidth: 1,
							borderColor: '#fff',
							backgroundColor: 'rgba(255,255,255,0.5)',					
							label: 'JOBS (Status)',
							fontColor:'#000',
							pointBackgroundColor: [<?php print(getListFromArray($laJobsStatus,'COLOR',"'","'")); ?>],
							pointBorderColor: [<?php print(getListFromArray($laJobsStatus,'COLOR',"'","'")); ?>],
							data: [<?php print(getListFromArray($laJobsStatus,'JOBS',"'","'")); ?>]
						}]
					},
					options: {
						title: {
							display: false,
						},
						animation: {
							duration: 3000,
							easing: 'easeInOutElastic',
						},
						legend: {
							display: true,
							position: 'bottom',
							labels: {
								fontColor: '#ffffff'
							}
						},
						scale: {
							gridLines: {
								color: '<?php print($lnFallas<=0?'#28a745':'#dc3545'); ?>'
							},
							angleLines: {
								color: 'rgba(255, 255, 255, .5)'
							}
						},	  
					}
				});



			});
		</script>
		<!-- Pie de pagina -->
	</body>
</html>