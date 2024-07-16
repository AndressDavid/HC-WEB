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

	include __DIR__ .'/../../controlador/class.EscalasRiesgoSangrado.php';
	$goRiesgoSangrado = new NUCLEO\EscalasRiesgoSangrado;
	$loHasbled = $goRiesgoSangrado;
	$laHasbled = $loHasbled->cargarDatosEscHas();
	$laDxHasbled =[];
	foreach($laHasbled as $lnIndice => $laRegistro){
		if($laRegistro['CL2TMA'] == 'DX'){
			$laDxHasbled  = [
				'LISTADX' => explode(',', str_replace('\'', '', $laRegistro['DE2TMA'])),
				'EDADMIN' => $laRegistro['OP3TMA'],
			];
		}else if($laRegistro['CL2TMA'] == 'ELEM'){
			$laElemHasbled[] = $laRegistro;
		}else if($laRegistro['CL2TMA'] == 'INTERP'){
			$laInterHasbled[] = $laRegistro;
		}
	}
?>
<div id="acordion">
	<div class="card card-block">
		<div class="card-header">
			<a href="#escalaHasbled" class="card-link text-dark" data-toggle="collapse" data-parent="#acordion"><label>Escala HASBLED</label></a>
		</div>
		<form id="escalaHasbled" class="collapse show needs-validation" novalidate>
			<table id="tablaHasbled" class="table border">
				<thead>
					<tr class="text-center">
						<th width="20%">Valor</th>
						<th width="10%"></th>
						<th width="55%">Descripción</th>
						<th width="15%" class="text-left">Puntaje</th>
					</tr>
				</thead>
				<tbody id="tbHasbled">
					<?php
						$lcAux="";
						$lnIndex=0;
						$laPuntajeHasbled=[];
						$lcSelect='<select class="form-control form-control-sm selectHasbled w-100" id="cboSiNoeshas%s" data-descri="%s" data-letra="%s" data-id="%s" required ><option value="-1"></option><option value="1">SI</option><option value="0">NO</option></select>';
						$lcFila = implode('', [
							'<tr %s>',
								'<td>',
									'<select class="form-control form-control-sm selectHasbled w-100" id="cboSiNoeshas%s" data-descri="%s" data-letra="%s" data-id="%s" required>',
										'<option value="-1"></option>',
										'<option value="1">SI</option>',
										'<option value="0">NO</option>',
									'</select>',
								'</td>%s',
								'<td id="lbldscitemhasbled%s" >%s</td>',
								'<td class="border">',
									'<span class="form-control form-control-sm text-center font-weight-bolder txtPuntajeHasbled" id="txtPuntajeHasbled%s">%s</span>',
								'</td>',
							'</tr>'
						]);
						foreach($laElemHasbled as $lnClave =>$itemhasbled){
							if($itemhasbled['CL2TMA']== 'ELEM'){
								$lnFilas=strlen($itemhasbled['OP6TMA']);
								$lcLetraUno = substr($itemhasbled['OP6TMA'],0,1);
								if($lcAux==$lcLetraUno){
									$lcLetra = '';
								}else{
									$lcLetra = sprintf('<td rowspan="%s" class="border text-center align-middle"><b>%s</b></td>',$lnFilas,$lcLetraUno);
									$lnIndex += 1;
								}
								printf($lcFila,
									($lnIndex%2==0?'class="bg-light"':''),
									$lnClave,
									$itemhasbled['DE2TMA'],
									$itemhasbled['OP6TMA'],
									$lnClave,
									$lcLetra,
									$lnClave,
									$itemhasbled['DE2TMA'],
									$lnClave,
									"0"
								);
								$lcAux=$lcLetraUno;
								$laPuntajeHasbled[$lnClave] = [
									'seleccion' => false,
									'letra' => $itemhasbled['OP6TMA'],
									'descripcion' => $itemhasbled['DE2TMA'],
									'puntaje' => 0,
								];
							}
						}
					?>
				</tbody>
				<tfoot>
					<tr class="border">
						<th colspan="2">Interpretación</th>
						<th><span class="form-control form-control-sm text-center font-weight-bolder" id="eshasInterpretacion">--</span></th>
						<th><span class="form-control form-control-sm text-center font-weight-bolder" id="eshastotalpuntaje">0</span></th>
					</tr>
				</tfoot>
			</table>
		</form>
	</div>
</div>
<script>
	<?php
	echo 'var laPuntajeHasbled = '.json_encode($laPuntajeHasbled).';';
	echo 'var laDxHasbled = '.json_encode($laDxHasbled).';';
	?>
</script>
<script src="vista-comun/js/escala_hasbled.js"></script>
<?php
}
?>