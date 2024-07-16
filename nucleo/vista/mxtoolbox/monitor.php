<?php
	require_once (__DIR__ .'/../../publico/constantes.php') ;
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Db.php');
	
	$ltAhora = new DateTime($goDb->fechaHoraSistema());
	$lnFecha=$ltAhora->format("Ymd");
	$lcTabla = 'mxtlbx';
	$laTokenColor=['#9932cc','#679dff','#ffc107'];
	
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
			}
			.card{
			    background-color: transparent !important;
			}
			.icon-shape {
				display: inline-flex;
				padding: 8px;
				text-align: center;
				align-items: center;
				justify-content: center;
				width: 76px;
				height: 76px;
			}		
			.alertPanel{
				min-height: 300px;
			}
			.evento{
				font-size: 1.7em !important;
			}
			.status{
				font-size: 21px !important;
			}			
			.monitor{
				font-size: 3.7em !important;
			}
			.icono{
				font-size: 2em !important;
			}			
		</style>

	</head>
	<body class="h-100">
		<div class="container-fluid h-100">
			<div class="row">
				<div class="col-12 p-2">
					<div class="card border border-secondary">

						<?php
							$lcColorFondo="secondary";
							$lnFallas=0;
							$lnAlertas=0;
							$lnRegistros=0;
							$laDatosDia = $goDb->count('*', 'REGISTROS')
											  ->sum('FAIMXT', 'FALLAS')
											  ->sum('WARMXT', 'ALERTAS')
											  ->tabla($lcTabla)
											  ->where('FECMXT','=',$lnFecha)
											  ->groupBy('FECMXT')
											  ->get("array");	
							if(is_array($laDatosDia)){
								$lcColorFondo = ($laDatosDia['FALLAS']+$laDatosDia['ALERTAS']>0?'danger':'success');
								$lnRegistros=$laDatosDia['REGISTROS'];
								$lnFallas=$laDatosDia['FALLAS'];
								$lnAlertas=$laDatosDia['ALERTAS'];
							}
						?>
						<div class="card-header text-white bg-<?php print($lcColorFondo); ?>">
							<h2>Estado en listas Negra <div class="float-right"><small><span class="badge badge-dark">API Mxtoolbox | <?php printf("Registros del %s: %s, con listas: %s y alertas: %s",$ltAhora->format("Y-m-d"),$lnRegistros,$lnFallas,$lnAlertas); ?></span></small></div></h2>
						</div>
						<div class="card-body">	
							<div class="row">
								<?php
									$lcCampos = 'TOKMXT, ACTMXT';
									$laTokens = $goDb->select($lcCampos)
													  ->tabla($lcTabla)
													  ->where('FECMXT','=',$lnFecha)
													  ->groupBy($lcCampos)
													  ->orderBy($lcCampos)
													  ->getAll("array");
									if(is_array($laTokens)==true){
										foreach($laTokens as $lnToken => $laToken){
											$laRegistro = $goDb->select('*')
															   ->tabla($lcTabla)
															   ->where('FECMXT','=',$lnFecha)
															   ->where('TOKMXT','=',$laToken['TOKMXT'])
															   ->orderBy('HORMXT','DESC')
															   ->limit(1)
															   ->get("array");
															   
											if(is_array($laRegistro)){
												$lcIcono = ($lnToken==0 || $lnToken==1?'fas fa-envelope-open-text':($lnToken==2?'fas fa-globe':'fas fa-info'));
												$lcIconoStatus = ($laRegistro['FAIMXT']+$laRegistro['WARMXT']>0?'fas fa-bell':'fas fa-bell-slash');
												$lcColorFondo = ($laRegistro['FAIMXT']+$laRegistro['WARMXT']>0?'danger':'success');
												
												$ltRegistro = strtotime($laRegistro['LATMXT']);
												$ltCompara = strtotime(date("Ymd His"));
												$lcTiempo = (int)((($ltCompara-$ltRegistro)/60)/60);
												$lcTiempo = ($laRegistro['FAIMXT']+$laRegistro['WARMXT']>0?$lcTiempo.'<sup>+</sup> horas desde el evento':'&nbsp;');
												
								?>
								<div class="col-sm-12 col-md-6 col-lg-4 col-xl-4">
										<div class="alert alertPanel border border-<?php print($lcColorFondo); ?>">
											<div class="row">
												<div class="col text-<?php print($lcColorFondo); ?>" >
													<h4 class="card-title mb-0 monitor "><?php print(str_replace('blacklist:','',$laToken['ACTMXT'])); ?></h4>
													<span class="font-weight-bold mb-0 evento"><?php print("Eventos: ".($laRegistro['FAIMXT']+$laRegistro['WARMXT'])); ?></span>
													<p class="status"><?php print(trim($laRegistro['STAMXT'])); ?><br/><?php print($lcTiempo); ?></p>
												</div>
												<div class="col-auto">
													<div class="icon icon-shape text-white shadow" style="background-color:<?php print($laTokenColor[$lnToken])?>">
														<i class="icono <?php print($lcIcono); ?> fa-3x"></i>
													</div>
												</div>
											</div>
											<p class="mt-1 mb-0 text-muted text-sm">
												<span class="text-<?php print($lcColorFondo); ?> mr-2"><i class="<?php print($lcIconoStatus); ?>"></i> <?php print("Listas: ".($laRegistro['FAIMXT'].", Avisos: ".$laRegistro['WARMXT'])); ?></span><br/>
												<span class="text-nowrap text-<?php print($lcColorFondo); ?>"><b><?php print($laRegistro['FAIMXT']+$laRegistro['WARMXT']>0?'Reportado el ':'Normal desde el '); ?></b> <?php print(date("Y-m-d h:i a",strtotime($laRegistro['LATMXT']))); ?></span></br/>
												<span class="mr-2 text-<?php print($lcColorFondo); ?>">Ultima consulta <?php print($laRegistro['FECMXT']); ?> a las <?php print($laRegistro['HORMXT']); ?> horas.</span>

											</p>
										</div>

								</div>
								<?php
											}
										}
									}								
								?>								
							</div>
						</div>						
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-12 p-2">
					<div class="card border border-secondary">
						<div class="card-body">
							<canvas id="chart-general" height="60vh"></canvas>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php 
			$lnDiasAtras = 30;
			$ltAtras = new DateTime($goDb->fechaHoraSistema());
			$ltAtras->modify('- '.$lnDiasAtras.' days');
			$lnAtras=intval($ltAtras->format("Ymd"));
			
			$laDiasAtras = array();
			$laDiasAtrasValores = array();
			
			for($lnFecha=$lnAtras; $lnFecha< $lnAtras+$lnDiasAtras; $lnFecha++){
				$ltAtras->modify('+ 1 days');
				$laDiasAtras[]= $ltAtras->format("Y-m-d");
			}
			
			if(is_array($laTokens)==true){
				foreach($laTokens as $lnToken => $laToken){
					foreach($laDiasAtras as $lcDiaAtras){
						
						$lnDiaAtras = intval(str_replace('-','',$lcDiaAtras));
						$lcCampos = 'FAIMXT, WARMXT';
						$laDatosDia = $goDb->select($lcCampos)
										   ->tabla($lcTabla)
										   ->where('FECMXT','=',$lnDiaAtras)
										   ->where('TOKMXT','=',$laToken['TOKMXT'])
										   ->orderBy('HORMXT','DESC')
										   ->limit(1)
										   ->get("array");	
						if(is_array($laDatosDia)){
							$laDiasAtrasValores[$laToken['TOKMXT']]['FALLAS'][] = $laDatosDia['FAIMXT'];
							$laDiasAtrasValores[$laToken['TOKMXT']]['ALERTAS'][] = $laDatosDia['WARMXT'];
						}
					}
				}
			}
			
			
			
		?>
		<script>
			let ctxGeneral = document.getElementById('chart-general');
			let chartGeneral = new Chart(ctxGeneral,{
				"type":"line",
				"data":{
					"labels":<?php print(json_encode($laDiasAtras)); ?>,
					"datasets":[
						<?php foreach($laTokens as $lnToken => $laToken){ ?>
						{
							"label":"<?php print(trim($laToken['ACTMXT'])); ?>",
							"data":<?php print(json_encode($laDiasAtrasValores[$laToken['TOKMXT']]['FALLAS'])); ?>,
							"fill":true,
							"borderColor":"<?php print($laTokenColor[$lnToken])?>",
							"lineTension":0.1,
							"backgroundColor": "<?php print($laTokenColor[$lnToken])?>"
						},
						<?php } ?>						
					]					
				},	
				"options":{
					"legend": {
						"position": "bottom",
						"labels": {
							"fontColor": '#ffffff'
						}
					},
					"scales": {
						"yAxes": [{
							"gridLines": {
								"display": true,
								"color": 'rgb(108,117,125)'
							},							
							"ticks": {
								"beginAtZero": true,
								"stepSize": 1,
								"fontColor": 'rgb(255,255,255)'
							},
						}],
						"xAxes": [{
							"gridLines": {
								"display": true,
								"color": 'rgb(108,117,125)'
							},								
							"ticks": {
								"fontColor": 'rgb(255,255,255)'
							},
						}]						
					},
					"title": {
						"display": true,
						"text": 'Historico de estado en listas Negra, últimos <?php print($lnDiasAtras); ?> días',
						"fontColor": 'rgb(255,255,255)'
					},				
				},
			});	
		</script>
	</body>
</html>