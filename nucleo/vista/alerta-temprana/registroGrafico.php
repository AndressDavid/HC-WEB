<?php
	require_once (__DIR__ .'/../../controlador/class.SignosNews.php') ;

	$loSignosNews = new NUCLEO\SignosNews();

?>
<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<h5>Gr&aacute;fica de observaci&oacute;n para NEWS</h5>
			<div id="filtroIngreso" class="row">
				<div class="col-lg-2 col-md-3 col-sm-12 col-12 pb-2">
					<label for="numeroIngreso"><b>Ingreso</b></label>
					<input type="number" class="form-control form-control-sm" name="numeroIngreso" id="numeroIngreso" placeholder="" value="" required="">
				</div>
			</div>
			<div class="row"><div class="col"><div id="ingresoInfo"></div></div></div>
		</div>
		<div id ="registroAlerta" class="card-body">
			<p>Paciente Ingreso No. <span class="badge badge-success" id="nIngresoMostrar"></span></p>
			<input type="hidden" id="nIngreso" name="nIngreso" value="0">
			<input type="hidden" id="cTipoId" name="cTipoId" value="">
			<input type="hidden" id="nId" name="nId" value="0">
			<input type="hidden" id="nEdad" name="nEdad" value="0">
			<div class="media">
				<div class="align-self-center mr-3 mb-3">
					<i class="fas fa-heartbeat fa-5x"></i>
				</div>
				<div class="media-body">
					<h4><span id="cNombre"> - </span></h4><span id="cTipoIdMostrar"></span> - <span id="nIdMostrar"></span><br/><span id="cEdad"> - </span><br/><span id="cUbicacion"> - </span>
				</div>
			</div>
		</div>
		<table class="table table-sm card-body">
			<thead>
				<tr class="bg-light">
					<th style="width: 47px;">Fecha</th>
					<?php 
						foreach($loSignosNews->getSignos() as $lcSigno => $laSigno){
							if(is_array($laSigno)){
								printf('<th>%s</th>',$laSigno['titulo']);
							}
						}
					?>
					<th>NEWS</th>
				</tr>
			</thead>
			<tbody class="table-main">
				<?php 
					for($lnRow=1;$lnRow<35;$lnRow++){		
						print('<tr><td></td>');
						foreach($loSignosNews->getSignos() as $lcSigno => $laSigno){
							if(is_array($laSigno)){
								switch ($laSigno['tipo']){
									case 'select':
										printf('<td></td>');
										break;
									default:
										printf('<td>%s</td>',rand($laSigno['min']+0,$laSigno['max']+0));
										break;
								}
							}
						}
						print('<td></td></tr>');
					}
				?>			
			</tbody>
		</table>
	</div>
</div>