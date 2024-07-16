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
	$loChadsvas = $goRiesgoSangrado;
	$laChadsvas = $loChadsvas->cargarDatosEsChadsvas();
	$laDxChadsvas = [];
	foreach($laChadsvas as $lnIndice => $laRegistro){
		if($laRegistro['CL2TMA']== 'DX'){
			$laDxChadsvas  = [
				'LISTADX' => explode(',', str_replace('\'', '', $laRegistro['DE2TMA'])),
				'EDADMIN' => $laRegistro['OP3TMA'],
			];
		}else if($laRegistro['CL2TMA'] == 'ELEM'){
			$laElemChadsvas[] = $laRegistro;
		}else if($laRegistro['CL2TMA'] == 'INTERP'){
			$laInterChadsvas[] = $laRegistro;
		}
	}
}
?>
<div id="acordion">
	<div class="card card-block">
		<div class="card-header">
			<a href="#escalaChadsvas" class="card-link text-dark" data-toggle="collapse" data-parent="#acordion"><label>Escala CHA<sub>2</sub>DS<sub>2</sub>VAS</label></a>
		</div>
		<form id="escalaChadsvas" class="collapse show needs-validation" novalidate>
			<table id="tablaChadsvas" class="table border">
				<thead>
					<tr class="text-center">
						<th width="20%">Valor</th>
						<th width="10%"></th>
						<th width="55%">Descripción</th>
						<th width="15%" class="text-left">Puntaje</th>
					</tr>
				</thead>
				<tbody id="tbEsChadsvas">
					<?php
						$lcAux="";
						$lnIndex=0;
						$laPuntajeChadsvas=[];
						$lcFila = implode('',[
							'<tr %s>',
								'<td>',
									'<select class="form-control form-control-sm selectChadsvas w-100" id="cboSiNoeschads%s" data-letra="%s" data-id="%s" required>',
										'<option value="-1"></option>',
										'<option value="%s">SI</option>',
										'<option value="%s">NO</option>',
									'</select>',
								'</td>',
								'<td rowspan="" class="border text-center align-middle"><b>%s</b></td>',
								'<td id="lbldscitemChadsvas%s">%s</td>',
								'<td class="border">',
									'<span class="form-control form-control-sm text-center font-weight-bolder txtPuntajeChadsvas" id="txtPuntajeChadsvas%s">%s</span>',
								'</td>',
							'</tr>'
						]);

						foreach($laElemChadsvas as $lnClave =>$itemChadsvas){
							$lcLetraUno = substr($itemChadsvas['OP6TMA'],0,2);
							$lcLetra = substr($lcLetraUno,1,1)=='2' ? (substr($lcLetraUno,0,1).'<sub>2</sub>') : $lcLetraUno;
							$lnIndex += 1;
							printf($lcFila,
								($lnIndex%2==0?'class="bg-light"':''),
								$lnClave,
								$itemChadsvas['OP6TMA'],
								$lnClave,
								$itemChadsvas['OP3TMA'],
								$itemChadsvas['OP4TMA'],
								$lcLetra,
								$lnClave,
								$itemChadsvas['DE2TMA'],
								$lnClave,
								"0"
							);
							$laPuntajeChadsvas[$lnClave] = [
								'seleccion' => false,
								'letra' => $itemChadsvas['OP6TMA'],
								'descripcion' => $itemChadsvas['DE2TMA'],
								'puntaje' => 0,
							];
						}
					?>
				</tbody>
				<tfoot>
					<tr class="border">
						<th colspan="2">Interpretación</th>
						<th><span class="form-control form-control-sm text-center font-weight-bolder" id="esChadInterpretacion">--</span></th>
						<th><span class="form-control form-control-sm text-center font-weight-bolder" id="esChadTotalPuntaje">0</span></th>
					</tr>
				</tfoot>
			</table>
		</form>
	</div>
</div>
<script>
	<?php
		echo 'var laPuntajeChadsvas = '.json_encode($laPuntajeChadsvas).';';
		echo 'var laDxChadsvas = '.json_encode($laDxChadsvas).';'
	?>
</script>
<script src="vista-comun/js/escala_chadsvas.js"></script>