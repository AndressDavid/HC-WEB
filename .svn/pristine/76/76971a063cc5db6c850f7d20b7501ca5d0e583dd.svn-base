<?php
	require_once (__DIR__ .'/../../controlador/class.AplicacionTareasManejador.php') ;
	require_once (__DIR__ .'/../../controlador/class.AplicacionFunciones.php');	

	use CRON\AplicacionTareasManejador;
	use NUCLEO\AplicacionFunciones;	

	$lcTareasCarpeta = __DIR__ .'/../../../tareas/procesos/';
	$loAplicacionTareasManejador = new AplicacionTareasManejador($lcTareasCarpeta);

?>
	<div class="container-fluid">
		<div class="card mt-3">
			<div class="card-header">
				<h5>Administración de Tareas programadas</h5>
			</div>
			<div class="card-body">
				
			</div>
			<table  id ="tareas" class="table table-sm table-bordered table-striped card-body">
				<thead>
					<tr>
						<th style="width:24px;"></th>
						<th scope="col">Tarea</th>
					</tr>
				</thead>
				<tbody>
				<?php
					$laTareas = $loAplicacionTareasManejador->obtenerTareas();
					foreach($laTareas as $lcTarea=>$laTarea){
						if(isset($laTarea['archivosIni']['configuracion'])){
							$laConfiguracion=$laTarea['archivosIni']['configuracion'];
				?>
					<!-- Inicio --->
					<tr <?php print($laConfiguracion['cTareaActiva']<>'SI'?'class="text-muted"':''); ?>>
						<td><i class="fas fa-sync <?php print($laConfiguracion['cTareaActiva']=='SI'?'fa-spin':''); ?>"></i></td>
						<td><b><?php print($laConfiguracion['cTareaNombre']); ?></b><br/><small><?php print($laConfiguracion['cTareaDescripcion']); ?></small></td>
					</tr>
					<!--- Fin --->
				<?php
						}
					}
				?>
				</tbody>
			</table>
			<div class="p-3"><div id="tareasInfo"></div></div>
			<div class="card-footer text-muted">
				<p>Info</p>
			</div>
		</div>
	</div>
	<script src="vista-tareas/js/scripts.js"></script>
