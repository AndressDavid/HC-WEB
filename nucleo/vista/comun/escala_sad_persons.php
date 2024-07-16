<?php

require_once __DIR__ .'/../../publico/constantes.php';

$laRetorna['error'] = '';

$nSalir = 0;
if (isset($_SESSION[HCW_NAME])){
	if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()!==true){
		$laRetorna['error'] = 'El usuario no tiene sesión activa';
		$nSalir = 1;
	}
} else {
	$laRetorna['error'] = 'Error en la sesión. Intente nuevamente.';
	$nSalir = 1;
}

if ($nSalir == 0) {
	include __DIR__ .'/../../controlador/class.EscalaSadPersons.php';
	$loEscalasSadpersons = new NUCLEO\EscalaSadPersons;
	$laSadpersons = $loEscalasSadpersons->cargarDatosSadPersons();
	$laDxSanPersons =[];
	$laTiposDiagnostico =[];
	$laRangoEdad ='';
	
	foreach($laSadpersons as $lnIndice => $laRegistro){
		
		switch(true){
			case $laRegistro['CL2TMA'] == 'DX' :
				foreach(explode(',', str_replace('\'', '', $laRegistro['DE2TMA'] .$laRegistro['OP5TMA'])) as $lcDato){
					if (!empty($lcDato)){
						$laDxSanPersons[] = $lcDato;
					}	
				}
			break;
			
			case $laRegistro['CL2TMA'] == 'TIPCIE' :
				foreach(explode(',', str_replace('\'', '', $laRegistro['DE2TMA'])) as $lcDato){
					if (!empty($lcDato)){
						$laTiposDiagnostico[] = $lcDato;
					}	
				}
			break;
			
			case $laRegistro['CL2TMA'] == 'ELEM' :
				$laDatosSadpersons[] = $laRegistro;
			break;

			case $laRegistro['CL2TMA'] == 'DX' :
				$laPuntuacion[] = $laRegistro;
			break;
			
			case $laRegistro['CL2TMA'] == 'RANGEDAD' :
				$laRangoEdad = $laRegistro['DE2TMA'];
			break;
		}
	}
?>
	<script type="text/javascript" src="vista-comun/js/escala_sad_persons.js"></script>
	<div class="card card-block" id="headerSadPeson">
		<div class="card-header">
			<a href="#escalaSadPersons" class="card-link text-dark"><b>Escala SadPersons</b></a>
		</div>
		
		<div class="card-body">
			<form id="FormSadPersons" name="FormSadPersons" novalidate="validate">
				<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
					<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
							<table id="tablaSadPersons" class="table table-bordered table-striped">
								<thead>
									<tr>
										<td class="text-center align-middle" style="width:35%"><b>Criterio</b></td>
										<td class="text-center align-middle" style="width:40%"><b>Puntución</b></td>
										<td class="text-center align-middle" style="width:25%"><b>Total puntaje</b></td>
									</tr>
								</thead>
						
								<tbody id="tbSadPersons">
									<?php
										$lnIndex=0;
										$laPuntajeSadperson=[];

										foreach($laDatosSadpersons as $lnClave =>$itemSadPersons){
											$lcDescripcion1 = trim($itemSadPersons['DE1TMA']);
											$lcDescripcion2 = trim($itemSadPersons['DE2TMA']);
											$lcOpcional1 = trim($itemSadPersons['OP1TMA']);
											$lcCodigo = trim($itemSadPersons['CL3TMA']);
											
											if($lcOpcional1=='N'){
												$lcControl = '<select class="form-control form-control-sm selectSadPersons w-100" id="cboSiNoesad'.$lcCodigo.'" data-codigo="'.$lcCodigo.'" data-id="'.$lnClave.'" required>
												<option value="-1"></option><option value="0">SI</option><option value="1">NO</option></select>';
											}else{
												$lcControl = '<select class="form-control form-control-sm selectSadPersons w-100" id="cboSiNoesad'.$lcCodigo.'" data-codigo="'.$lcCodigo.'" data-id="'.$lnClave.'" required>
												<option value="-1"></option><option value="1">SI</option><option value="0">NO</option></select>';
											}	

											$lcFila = '<tr>'
													  ."<td style='width:35%' id='lblSiNoesad".intval($lcCodigo)."' >$lcDescripcion1</td>"	
													  ."<td style='width:40%'>$lcDescripcion2</td>"
													  ."<td style='width:25%'>$lcControl</td>"
													  .'</tr>';
											print($lcFila);
											
											$laPuntajeSadperson[$lnClave] = [
											'seleccion' => false,
											'puntaje' => 0,
											'codigoesc' => $lcCodigo,
											];
										}

									?>
								</tbody>
								<tfoot>
									<tr class="border">
										<th >Interpretación</th>
										<th id="escSadPersonsInterpretacion"></th>
										<th id="escSadPersonstotalpuntaje">0</th>
									</tr>
								</tfoot>
				
							</table>
					</div>
				</div>
			</form>
		</div>
	</div>

<script>
	<?php
	echo 'var laDxSanPersons = '.json_encode($laDxSanPersons).';';
	echo 'var laTiposDiagnostico = '.json_encode($laTiposDiagnostico).';';
	echo 'var laRangoEdad = '.json_encode($laRangoEdad).';';
	echo 'var laPuntajeSadperson = '.json_encode($laPuntajeSadperson).';';
	?>
</script>

<?php
}
?>