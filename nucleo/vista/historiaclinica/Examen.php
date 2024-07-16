<div class="card card-block">
	<div class="card-header" id="headerExamenUno">
		<a href="#Examen_uno" class="card-link text-dark"><b>Exámen Físico Estado</b></a>
	</div>

	<div class="card-body">
		<form role="form" id="FormExamen" name="FormMotivo" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
			<div class="row pb-2">
				<div class="col-12">
					<label id="lblEstadoGeneral" for="edtEstadoGeneral">Estado General</label>
					<textarea name="estado" type="text" class="form-control <?= $lcCopyPaste ?>" id="edtEstadoGeneral"></textarea>
				</div>

				<div class="col-lg-4 col-md-4 col-sm-10 col-10">
					<label id="lblTAS" for="txtTAS">Tensión Arterial Sistólica</label>
					<input id="txtTAS" name="tas" type="number" placeholder="Tensión Arterial Sistólica" class="form-control mr-sm-2">
				</div>

				<div class="col-2">
					<br><br>
					<div class="custom-control custom-checkbox custom-control-inline">
						<input type="checkbox" class="custom-control-input" id="chkTAS" name="chk_TAS" value="1">
						<label class="custom-control-label" for="chkTAS">Ausente</label>
					</div>
				</div>

				<div class="col-lg-4 col-md-4 col-sm-10 col-10">
					<label id="lblTAD" for="txtTAD">Tensión Arterial Diastólica</label>
					<input id="txtTAD" name="tad" type="number" placeholder="Tensión Arterial Diastólica" class="form-control mr-sm-2">
				</div>

				<div class="col-2">
					<br><br>
					<div class="custom-control custom-checkbox custom-control-inline">
						<input type="checkbox" class="custom-control-input" id="chkTAD" name="chk_TAD" value="1">
						<label class="custom-control-label" for="chkTAD">Ausente</label>
					</div>
				</div>

				<div class="col-lg-4 col-md-4 col-sm-10 col-10">
					<label id="lblFC" for="txtFC">Frecuencia Cardiáca</label>
					<input id="txtFC" name="fc" type="number" placeholder="Frecuencia Cardiáca" class="form-control mr-sm-2">
				</div>

				<div class="col-2">
					<br><br>
					<div class="custom-control custom-checkbox custom-control-inline">
						<input type="checkbox" class="custom-control-input" id="chkFC" name="chk_FC" value="1">
						<label class="custom-control-label" for="chkFC">Ausente</label>
					</div>
				</div>

				<div class="col-lg-4 col-md-4 col-sm-10 col-10">
					<label id="lblFR" for="txtFR">Frecuencia Respiratoria</label>
					<input id="txtFR" name="fr" type="number" placeholder="Frecuencia Respiratoria" class="form-control mr-sm-2">
				</div>

				<div class="col-2">
					<br><br>
					<div class="custom-control custom-checkbox custom-control-inline">
						<input type="checkbox" class="custom-control-input" id="chkFR" name="chk_FR" value="1">
						<label class="custom-control-label" for="chkFR">Ausente</label>
					</div>
				</div>

				<div class="col-lg-3 col-md-6 col-sm-11 col-11">
					<label id="lblTemp" for="txtTemp">Temperatura</label>
					<input id="txtTemp" name="temp" type="number" placeholder="Temperatura" class="form-control mr-sm-2">
				</div>

				<div class="col-lg-3 col-md-6 col-sm-11 col-11">
					<label id="lblPeso" for="txtPeso">Peso (kg)</label>
					<input id="txtPeso" name="peso" type="number" placeholder="Peso" class="form-control mr-sm-2">
				</div>

				<div class="col-lg-3 col-md-6 col-sm-11 col-11">
					<label id="lblSo2" for="txtSatur">Saturación</label>
					<input id="txtSo2" name="so2" type="number" placeholder="Saturación" class="form-control mr-sm-2">
				</div>

				<div class="col-lg-3 col-md-6 col-sm-11 col-11">
					<label id="lblTempR" for="txtTempR">Temperatura Rectal</label>
					<input id="txtTempR" name="tempR" type="number" placeholder="Temperatura Rectal" class="form-control mr-sm-2">
				</div>

				<div class="col-lg-3 col-md-6 col-sm-11 col-11">
					<label for="lblTalla" for="txtTalla">Talla (cm)</label>
					<input id="txtTalla" name="talla" type="number" placeholder="Talla" class="form-control mr-sm-2">
				</div>

				<div class="col-lg-3 col-md-6 col-sm-11 col-11">
					<label for="txtSupC">Superficie Corporal</label>
					<input id="txtSupC" name="supC" type="number" placeholder="Superficie Corporal" class="form-control mr-sm-2" readonly>
				</div>

				<div class="col-lg-3 col-md-6 col-sm-11 col-11">
					<label for="txtMasaC">Masa Corporal</label>
					<input id="txtMasaC" name="masaC" type="number" placeholder="Masa Corporal" class="form-control mr-sm-2" readonly>
				</div>
			</div><br>
		</form>
	</div>
</div>


<div class="card card-block">
	<div class="card-header" id="headerExamenDos">
		<a href="#Examen_dos" class="card-link text-dark"><b>Exámen Físico General</b></a>
	</div>
	<div class="card-body">
		<form role="form" id="FormExamenG" name="FormExamenG" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">

<?php
		$lcPrefijo='ex';
		$laLista=[
			'Cabeza'		=>['Cabeza - Cuello'		,'head-side-virus'],
			'Organos'		=>['Órganos de los sentidos','deaf'],
			'Torax'			=>['Toráx Cardio Pulmonar'	,'lungs'],
			'Abdomen'		=>['Abdomen'				,'file-alt'],
			'Genito'		=>['Genito - Urinario'		,'dot-circle'],
			'Extremidades'	=>['Extremidades'			,'child'],
		];
		foreach($laLista as $lcNombre=>$laItem){
			$lcIdLabel='lbl'.$lcNombre;
			$lcIdControl=$lcPrefijo.$lcNombre; ?>
			<div class="col-12 pb-4">
				<label id="<?php echo $lcIdLabel; ?>" for="<?php echo $lcIdControl; ?>"><?php echo $laItem[0]; ?></label>
				<div class="input-group">
				  <div class="input-group-prepend">
					<span class="input-group-text"><i class="fas fa-<?php echo $laItem[1]; ?>"></i></span>
				  </div>
				  <textarea name="<?php echo $lcIdControl; ?>" id="<?php echo $lcIdControl; ?>" type="text" class="form-control <?= $lcCopyPaste ?>"></textarea>
				</div>
			</div>
<?php	} ?>

		</form>
	</div>
</div>


<div class="card card-block">
	<div class="card-header" id="headerExamenTres">
		<a href="#Examen_Tres" class="card-link text-dark"><b>Exámen Físico Neurológico</b></a>
	</div>

	<div class="card-body">
		<form role="form" id="FormExamenN" name="FormExamenN" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">

			<div class="input-group pb-4">
				<div class="col-lg-12 col-md-12 col-sm-6 col-12">
					<br><label for="lblMental">Nivel de Conciencia</label>
				</div>
				<div class="col-lg-12 col-md-12 col-sm-6 col-12">
					<select name="nivelC" class="custom-select w-50" id="selNivelCE"></select>
				</div>
			</div>

			<div class="input-group pb-4">
				<div class="col-lg-12 col-md-12 col-sm-6 col-12">
					<label for="lblEscalaG">Escala Glasgow</label>
				</div>
				<div class="col-lg-4 col-md-4 col-sm-8 col-12">
					<input name="escalaG" id="txtEscalaG" type="number" placeholder="Escala" class="form-control mr-sm-2">
				</div>
				<div class="col-lg-8 col-md-8 col-sm-4 col-4">
					 <label for="lblEscalaG1">/ 15 </label>
				</div>
			</div>

<?php
		$lcPrefijo='ex';
		$laLista=[
			'Mental'		=>['Exámen Mental'		,'head-side-virus'],
			'Craneales'		=>['Pares Craneales'	,'brain'],
			'Motor'			=>['Estado Motor'		,'walking'],
			'Sensitivo'		=>['Estado Sensitivo'	,'thermometer-three-quarters'],
			'Reflejos'		=>['Reflejos (N)'		,'eye'],
			'Meningeos'		=>['Signos Meningeos'	,'file-alt'],
			'Neurovascular'	=>['Neurovascular'		,'file'],
		];
		foreach($laLista as $lcNombre=>$laItem){
			$lcIdLabel='lbl'.$lcNombre;
			$lcIdControl=$lcPrefijo.$lcNombre; ?>
			<div class="col-12 pb-4">
				<label id="<?php echo $lcIdLabel; ?>" for="<?php echo $lcIdControl; ?>"><?php echo $laItem[0]; ?></label>
				<div class="input-group">
				  <div class="input-group-prepend">
					<span class="input-group-text"><i class="fas fa-<?php echo $laItem[1]; ?>"></i></span>
				  </div>
				  <textarea name="<?php echo $lcIdControl; ?>" id="<?php echo $lcIdControl; ?>" type="text" class="form-control <?= $lcCopyPaste ?>"></textarea>
				</div>
			</div>
<?php	} ?>

		</form>
	</div>
</div>

<script type="text/javascript" src="vista-comun/js/TiposNivelC.js"></script>
<script type="text/javascript" src="vista-historiaclinica/js/Examen.js"></script>