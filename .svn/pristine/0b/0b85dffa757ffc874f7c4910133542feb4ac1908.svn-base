<?php
	require_once (__DIR__ .'/../../publico/constantes.php') ;
	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionTarea.php') ;
	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionTareasManejador.php') ;
	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionFunciones.php');	
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Db.php');

	use CRON\AplicacionTareasManejador;
	use CRON\AplicacionTarea;
	use NUCLEO\AplicacionFunciones;	
	
	$ltAhora = new DateTime($goDb->fechaHoraSistema());
	$lnTimeMaxSeg=180;
	$lnFallas=0;
	$lcFallas='';
	$lcInactivas="";
	$lcTareasCarpeta = __DIR__ .'/../../../tareas/procesos/';
	$lcLogTareas=$lcTareasCarpeta.$ltAhora->format("Y-m").".log";
	$lcLogApp=__DIR__ .'/../../../error.log';
	$loAplicacionTareasManejador = new AplicacionTareasManejador($lcTareasCarpeta);	
	$loAplicacionTarea = new AplicacionTarea();	
	$lcHtml = '';
	$lcHtmlInactivas = '';
	
	// TAREAS PROGRAMADAS EN HCW
	$laTareas = $loAplicacionTareasManejador->obtenerTareas();
	foreach($laTareas as $lcTarea=>$laTarea){
		if(isset($laTarea['archivosIni']['configuracion'])){
			$laConfiguracion=$laTarea['archivosIni']['configuracion'];
			$laEjecucion=null;
			$llError=false;
			$lcClass='danger';
			
			if($laConfiguracion['cTareaActiva']=='SI'){
				if(isset($laTarea['archivosIni']['ejecucion'])==true){
					$laEjecucion=$laTarea['archivosIni']['ejecucion'];
					$llError=(($laEjecucion['cError']=='NO' || trim($laEjecucion['cError']==''))?false:true);
					$lcClass=($llError==true?'danger':'success');
					if($llError==true){
						$lnFallas+=1;
						$lcFallas .= sprintf('<li>%s</li>',$laConfiguracion['cTareaNombre']);
					}
				}else{
					$lnFallas+=1;
					$lcFallas .= sprintf('<li>r %s</li>',$laConfiguracion['cTareaNombre']);
				}
				
				$laConfiguracion['cTareaNombre'] = (isset($laConfiguracion['cTareaNombre']) ? $laConfiguracion['cTareaNombre'] : '');
				$laConfiguracion['cTareaDescripcion'] = (isset($laConfiguracion['cTareaDescripcion']) ? $laConfiguracion['cTareaDescripcion'] : '');
				$laConfiguracion['tInicio'] = (isset($laConfiguracion['tInicio']) ? $laConfiguracion['tInicio'] : '');
				$laConfiguracion['tFin'] = (isset($laConfiguracion['tFin']) ? $laConfiguracion['tFin'] : '');
				
				$lcHtml .= sprintf('%s', getHTMLmonitor($lcClass, $laConfiguracion['cTareaNombre'], $laConfiguracion['cTareaDescripcion'], strval($laEjecucion['tInicio']), strval($laEjecucion['tFin'])));
				
			}else{
				$lcInactivas.= (empty($lcInactivas)==true?"":"|").$laConfiguracion['cTareaNombre'];
			}
		}
	}
	// INACTIVAS
	if(empty($lcInactivas)==false){
		$laInactivas = explode("|",$lcInactivas);
		foreach($laInactivas as $lcInactiva){
			$lcHtmlInactivas .= sprintf('<div class="card card-monitor text-mute border-secondary"><div class="card-body p-2"><div class="card-title text-mute m-0 p-0"><ul class="list-unstyled m-0 p-0"><li class="media"><i class="fas fa-cog align-self-center mr-3"></i><div class="media-body"><span>%s</span></div></li></ul></div></div></div>',$lcInactiva);
		}
	}	
	
	
	// WEBSERVICE LABORATORIO
	$lnRegistros =0;
	$loResultado = $goDb->count('*','CUENTA')->from('LABRES')->where(['RESFEC'=>$ltAhora->format("Ymd")])->get('array');
	if (is_array($loResultado)) {
		$lnRegistros = (isset($loResultado['CUENTA'])==true?$loResultado['CUENTA']:0);
		if($lnRegistros<=0){
			$lnFallas+=1;
			$lcFallas .= sprintf('<li>%s</li>','WEBSERVICE Laboratorio');
		}
	}
	$lcClass=($lnRegistros<=0?'danger':'success');								
	$lcHtml .= sprintf('%s', getHTMLmonitor($lcClass, 'WEBSERVICE Laboratorio','Registros creados el día de hoy mediante interca por webservice', 0, 0, $lnRegistros));


	// ADMINISTRADOR DE TAREAS PROGRAMADAS
	$lcLogOld = substr($loAplicacionTarea->UltimaLineaLog($lcLogTareas),0,19);
	$ltLogOld = new DateTime(date("Y-m-d H:i:s",strtotime($lcLogOld)));
    $lnLogOld = 0;
	$lcLogAux = '<i class="fas fa-not-equal"></i>';
	
	if($ltLogOld->format("Y-m-d H:i:s")<>$ltAhora->format("Y-m-d H:i:s")){
		$loInterval = $ltAhora->diff($ltLogOld);	
		$lnLogDays = $loInterval->format('%a'); $lnLogOld += 24 * 60 * $lnLogDays;
		$lnLogHours = $loInterval->format('%H'); $lnLogOld += 60 * $lnLogHours;
		$lnLogOld += $loInterval->format('%i');	
	}else{
		$lcLogAux = '<i class="fas fa-equals"></i>';
	}
	
	$lcClass=($lnLogOld>10?'danger':'success');
	if($lnLogOld>10){
		$lnFallas+=1;
		$lcFallas .= sprintf('<li>%s</li>','Última comprobación');
	}	
	$lcHtml .= sprintf('%s', getHTMLmonitor($lcClass, sprintf('Última comprobación<small><sup>%s</sup></small>',$lcLogAux),'Fecha y hora de la ultima ejecución del administrador de tareas programadas.', 0, 0, 0, $ltLogOld->format("Y-m-d H:i:s"),$lnLogOld));

	//LOG DE ERRORES EN LA APLICACION
	$lcError='';	
	if(is_file($lcLogApp)==true){
		$lcError = $loAplicacionTarea->UltimaLineaLog($lcLogApp);
		$lcClass='secondary';
		
		if(!empty($lcError)){
			try{
				$loErrorDate = new DateTime(trim(substr($lcError,0,20)));
				$llErrorNow = ($loErrorDate->format("Y-m-d")==$ltAhora->format("Y-m-d"));
				if($llErrorNow==true){
					$lnFallas+=1;
					$lcFallas .= sprintf('<li>%s</li>','Errores aplicación ');
					$lcClass='danger';
				}
			} catch (Exception $loError) {
					$lnFallas+=1;
					$lcFallas .= sprintf('<li>%s</li>','Errores aplicación');
					$lcClass='danger';
			}
			
			if($lnFallas>0){
				$lcHtml .= sprintf('%s', getHTMLmonitor($lcClass, sprintf('Registro de errores<sup>%s</sup><ol>%s</ol>',$lnFallas,$lcFallas),'Fecha y hora de la ultima ejecución del administrador de tareas programadas.', 0, 0, 0));
			}
		}
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
		<style>
			body{
				background-color: #1f1d1d !important;
				color: #ffffff;
			}
			.card-monitor{
			    background-color: transparent !important;
			}
			.card-columns {
				column-count: 4;
			}
			.activos .card-title{
				font-size: 1.5em;
				font-weight: bold;
			}
		</style>		
	</head>
	<body class="h-100">
		<div class="container-fluid h-100">
			<div class="row h-100">
				<div class="container-fluid">
					<div class="card card-monitor mt-3 h-100">
						<div id="banner" class="card-header text-white <?php print($lnFallas>0?"bg-danger":"bg-success"); ?>">
							<h2>Tareas/Procesos/Errores<div class="float-right"><small><span class="badge badge-dark">HCW <?php print($ltAhora->format("Y-m-d H:i:s")); ?></span></small></div></h2>
						</div>
						<div class="card-body">
							<h3>Activos</h3>
							<div class="card-columns activos">
							<?php print($lcHtml); ?>
							</div>
							
							<h3>Inactivos</h3>
							<div class="card-columns">
								<?php print($lcHtmlInactivas); ?>
							</div>
							<div class="card-footer p-0">
								<?php if($lnFallas>0){ ?>
								<div class="card bg-danger text-white p-2 mt-1 mb-1"><h2>Existe tareas/procesos con error <span class="badge badge-secondary"><?php print($lnFallas); ?></span></h2><br><p><?php if(!empty($lcError)){printf('<b class="card-text">Hay errores registrados hoy</b><br/>%s',$lcError);}?></p></div>	
								<?php } ?>
							</div>		
						</div>
					</div>
				</div>
			</div>
		</div>		
		<div style="position: fixed; float:right; bottom: 0; right: 0; padding-right: 8px; padding-bottom: 8px;">
			<button id="cmdActualizar" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sync fa-spin"></i> <span class="messageActualizar"></span></button>
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

			});
		</script>
		<!-- Pie de pagina -->
	</body>
</html><?php
	function getHTMLmonitor($tcClass='success', $tcTareaNombre='',$tcTareaDescripcion='', $ttInicio=0, $ttFin=0, $tnRegistros=0, $tcDateLog='',$tnInterval=0){
		$lcHTMLmonitor = "";
		$lcHTMLmonitor .= sprintf('<div class="card card-monitor text-%s border-%s">',$tcClass,$tcClass);
		$lcHTMLmonitor .= sprintf('<div class="card-body p-2">');
		$lcHTMLmonitor .= sprintf('<div class="card-title text-%s">',$tcClass);
		$lcHTMLmonitor .= sprintf('<ul class="list-unstyled m-0">');
		$lcHTMLmonitor .= sprintf('<li class="media">');
		$lcHTMLmonitor .= sprintf('<i class="fas fa-sync fa-spin align-self-center mr-3"></i>');
		$lcHTMLmonitor .= sprintf('<div class="media-body">');
		$lcHTMLmonitor .= sprintf('<span>%s</span>', $tcTareaNombre);
		$lcHTMLmonitor .= sprintf('</div></li></ul></div>');
		$lcHTMLmonitor .= sprintf('<!-- <p class="card-text ml-4 pl-3">%s</p> -->',$tcTareaDescripcion);
		
		if($ttInicio>0 && $ttFin>0){ 
			$interval = ceil(($ttFin-$ttInicio)/60);			
			$lcHTMLmonitor .= sprintf('<p class="card-text ml-4 pl-3"><span class="badge badge-%s">%s</span> <span class="badge badge-%s">%s&quot;</span></p>',$tcClass,date("Y-m-d H:i",$ttInicio),$tcClass,$interval);
		}else if($tnRegistros>0) {
			$lcHTMLmonitor .= sprintf('<p class="card-text ml-4 pl-3"><span class="badge badge-%s">Registros %s</span></p>',$tcClass,$tnRegistros);
		}else if(!empty($tcDateLog)){
			$lcHTMLmonitor .= sprintf('<p class="card-text ml-4 pl-3"><span class="badge badge-%s">%s</span> <span class="badge badge-%s">%s</span></p>',$tcClass,$tcDateLog,$tcClass,$tnInterval);
		}			
		$lcHTMLmonitor .= sprintf('</div></div>');
		
		return $lcHTMLmonitor;
	}
?>