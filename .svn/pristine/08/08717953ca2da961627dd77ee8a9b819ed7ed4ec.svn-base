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
	$loCrusade = $goRiesgoSangrado;
	$laCrusade = $loCrusade->cargarDatosEsCrusade();
	$laDxCrusade = [];
	if (is_array($laCrusade)) {
		foreach($laCrusade as $lnIndice => $laRegistro){
			if(($laRegistro['CL2TMA']??'') == 'DX'){
				$laDxCrusade = [
					'LISTADX' => explode(',', str_replace('\'', '', $laRegistro['DE2TMA'])),
					'EDADMIN' => $laRegistro['OP3TMA'],
				];
			}else if(($laRegistro['CL2TMA']??'') == 'INTERP'){
				$laInterCrusade[] = $laRegistro;
			}
		}
	}
?>

<div id="acordion">
	<div class="card card-block">
		<div class="card-header">
			<a href="#escalaCrusade" class="card-link text-dark" data-toggle="collapse" data-parent="#acordion"><label>Escala CRUSADE</label></a>
		</div>
		<form action="#" role="form" id="escalaCrusade" name="frmEscalaCrusade" method="POST" class="collapse show needs-validation" novalidate="validate">
			<div class="table-responsive">
			<table class="table border tabla-x">
				<thead>
					<tr>
						<th style="width:30%;">Descripción</th>
						<th style="width:29%;" class="text-right">Valor</th>
						<th style="width:26%;">Rango</th>
						<th style="width:15%;">Puntaje</th>
					</tr>
				</thead>
				<tbody class="">
					<tr id="lnRegistro1" >
						<td id="lbldscCrusade1"></td>
						<td>
							<div class="w100">
								<div class="input-group input-group-sm">
									<input type="number"
										id="lnValorHematocrito"
										name="lnValorHematocrito"
										class="form-control form-control-sm"
										value=""
										pattern="^[0-9]*$"
										min="0" max="100"
										maxlength="5"
										autocomplete="off"
										placeholder="0.0"
										tabindex="1"
										required="required">
									<div class="input-group-append">
										<small class="input-group-text"> % </small>
									</div>
								</div>
							</div>
						</td>
						<td>
							<select id="cboRangoHematocrito" data-id="1" data-letra="HB" class="form-control form-control-sm selectCrusade w-100 text-right required" disabled>
								<option value="-1" selected></option>
							</select>
						</td>
						<td>
							<span class="puntajeCrusade form-control form-control-sm text-center font-weight-bolder" id="puntajeHematocrito">0</span>
						</td>
					</tr>
					<tr id="lnRegistro2">
						<td>
							<div class="w-100">
								<p id="lbldscCrusade2"></p>
								Creatinina
								<div class="input-group input-group-sm">
									<input type="number"
										id="lnValorCreatinina"
										name="lnValorCreatinina"
										class="form-control form-control-sm inputCreatinina"
										value=""
										pattern="^[0-9]*$"
										min="0,5" max="6"
										maxlength="4"
										autocomplete="off"
										placeholder="0.00"
										tabindex="2"
										required="required" >
									<div class="input-group-append">
										<small class="input-group-text">mg/dL</small>
									</div>
								</div>
							</div>
						</td>
						<td class="align-bottom">
							<div class="w-100">
								CockCroft Gault
								<div class="input-group input-group-sm">
									<input type="text"
										id="lnValorCockcroft"
										name="lnValorCockcroft"
										class="form-control form-control-sm"
										value=""
										placeholder="0.0"
										disabled>
									<div class="input-group-append">
										<small class="input-group-text">mL/min</small>
									</div>
								</div>
							</div>
						</td>
						<td class="align-bottom">
							<select id="cboRangoCreatinina" data-id="2" data-letra="DC" class="form-control form-control-sm selectCrusade w-100 text-right required" disabled>
								<option value="-1" ></option>
							</select>
						</td>
						<td class="align-bottom">
							<span class="puntajeCrusade form-control form-control-sm text-center font-weight-bolder" id ="puntajeCreatinina">0</span>
						</td>
					</tr >
					<tr id="lnRegistro3">
						<td id="lbldscCrusade3"></td>
						<td>
							<div class="w100">
								<div class="input-group input-group-sm">
									<input type="number"
										id="lnValorFreCardi"
										name="lnValorFreCardi"
										class="form-control form-control-sm inputFreCardiaca"
										value=""
										placeholder="0.0"
										required="required"
										disabled>
									<div class="input-group-append">
										<small class="input-group-text">lat/min</small>
									</div>
								</div>
							</div>
						</td>
						<td>
							<select id="cboRangoFreCardi" data-id="3" data-letra="FC" class="form-control form-control-sm selectCrusade w-100 text-right required" disabled>
								<option value="-1" ></option>
							</select>
						</td>
						<td>
							<span class="puntajeCrusade form-control form-control-sm text-center font-weight-bolder" id="puntajeFreCardi">0</span>
						</td>
					</tr>
					<tr id="lnRegistro4">
						<td id="lbldscCrusade4" colspan="2"></td>
						<td>
							<select id="cboGenero" data-id="4"  data-letra="G" class="form-control form-control-sm selectCrusade w-100 text-right required" disabled>
								<option value="-1"></option>
							</select>
						</td>
						<td>
							<span class="puntajeCrusade form-control form-control-sm text-center font-weight-bolder" id="puntajeGenero">0</span>
						</td>
					</tr>
					<tr id="lnRegistro5">
						<td id="lbldscCrusade5" colspan="2"></td>
						<td>
							<select id="cboFallaCardi" data-id="5" data-letra="SF" class="form-control form-control-sm selectCrusade w-100 text-right" required="required" tabindex="3">
								<option value="-1"></option>
							</select>
						</td>
						<td>
							<span class="puntajeCrusade form-control form-control-sm text-center font-weight-bolder" id="puntajeFallaCardi">0</span>
						</td>
					</tr>
					<tr id="lnRegistro6">
						<td id="lbldscCrusade6" colspan="2"></td>
						<td>
							<select id="cboVascularPrevia" data-id="6" data-letra="EV" class="form-control form-control-sm selectCrusade w-100 text-right required" type="text" tabindex="4">
								<option value="-1"></option>
							</select>
						</td>
						<td>
							<span class="puntajeCrusade form-control form-control-sm text-center font-weight-bolder" id="puntajeVascularPrevia">0</span>
						</td>
					</tr>
					<tr id="lnRegistro7">
						<td id="lbldscCrusade7" colspan="2"></td>
						<td>
							<select id="cboDiabetesMellitus"  data-id="7" data-letra="DM" class="form-control form-control-sm selectCrusade w-100 text-right required" tabindex="5">
								<option value="-1" selected></option>
							</select>
						</td>
						<td>
							<span class="puntajeCrusade form-control form-control-sm text-center font-weight-bolder" id="puntajeDiabetesMellitus">0</span>
						</td>
					</tr>
					<tr id="lnRegistro8">
						<td id="lbldscCrusade8"></td>
						<td>
							<div class="w100">
								<div class="input-group input-group-sm">
									<input type="number"
										id="lnValorArteSisto"
										name="lnValorArteSisto"
										class="form-control form-control-sm inputArteSisto"
										value=""
										placeholder="0.0"
										required="required"
										disabled>
									<div class="input-group-append">
										<small class="input-group-text">mm Hg</small>
									</div>
								</div>
							</div>
						</td>
						<td>
							<select id="cboRangoArteSisto" data-id="8" data-letra="PS" class="form-control form-control-sm selectCrusade w-100 text-right required" disabled>
								<option value="-1" selected></option>
							</select>
						</td>
						<td>
							<span class="puntajeCrusade form-control form-control-sm text-center font-weight-bolder" id="puntajeArteSisto">0</span>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr class="border">
						<th>
							Interpretación
						</th>
						<th colspan="2">
							<span class="form-control form-control-sm text-center font-weight-bolder" id="esCrusInterpretacion">--</span>
						</th>
						<th>
							<span class="form-control form-control-sm text-center font-weight-bolder" id="esCrusTotalPuntaje">0</span>
						</th>
					</tr>
				</tfoot>
			</table>
			</div>
		</form>
	</div>
</div>
<script src="vista-comun/js/escala_crusade.js"></script>
<script src="vista-comun/js/comun.js"></script>
<script>
	var laDxCrusade = <?= json_encode($laDxCrusade) ?>;
</script>
	<?php
} // if ($nSalir == 0)
?>