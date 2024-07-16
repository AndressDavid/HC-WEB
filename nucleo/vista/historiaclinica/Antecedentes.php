<div class="card card-block">
	<div class="card-header" id="headerAntecedentes">
		<a href="#Antec_uno" class="card-link text-dark"><b>Antecedentes</b></a>
	</div>

	<div class="card-body">
		<form role="form" id="FormAntecedentes" name="FormAntecedentes" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
			<?php
				$lcPrefijo='ant';
				$laLista=[
					'Alergicos'			=>['Alérgicos'			,'allergies'],
					'Familiares'		=>['Familiares'			,'users'],
					'Patologicos'		=>['Clínico Patológicos','stethoscope'],
					'Hospitalarios'		=>['Hospitalarios'		,'hospital'],
					'Quirurgicos'		=>['Quirúrgicos'		,'medical'],
					'Toxicos'			=>['Tóxicos'			,'head-side-cough'],
					'Transfusionales'	=>['Transfusionales'	,'tint'],
					'Traumaticos'		=>['Traumáticos'		,'file-medical-alt'],
					'Gineco'			=>['Gineco Obstétricos'	,'file-medical-alt'],
					'Vacunas'			=>['De Vacunas'			,'syringe'],
				];
				$laDiscapacidad=[
					'01' => 'Física',
					'02' => 'Visual',
					'03' => 'Auditiva',
					'04' => 'Intelectual',
					'05' => 'Psicosocial (Mental)',
					'06' => 'Sordoceguera',
				];

				foreach($laLista as $lcNombre=>$laItem){
					$lcIdLabel='lbl'.$lcNombre;
					$lcIdControl=$lcPrefijo.$lcNombre;

					if ($lcNombre=='Hospitalarios'){?>
						<div class="col-12 pb-4" id="divDiscapacidad">
							<div class="form-row pt-2" id="divDiscapacidad">
								<div class="col-lg-2 col-md-4 col-sm-6">
									<label id="lblDiscapacidad" for="antDiscapacidad">¿Tiene Discapacidad?</label>
								</div>
								<div class="col-lg-2 col-md-3 col-sm-3">
									<select id="selDiscapacidad" name="antDiscapacidad" class="custom-select">
										<option></option>
										<option>Si</option>
										<option>No</option>
									</select>
								</div>
							</div>
						</div>

						<div class="col-12 pb-4" id="divOpcDiscapacidad">
							<div class="card border">
								<div class="card-body">
									<div class="row align-items-bottom">
									<?php
										foreach ($laDiscapacidad as $lnDiscap => $lcDiscap) {
											$lcIdDiscap = 'chk'.$lnDiscap;
											?>
											<div class="col-auto">
												<div class="form-group">
													<div class="custom-control custom-checkbox custom-control-inline">
														<input type="checkbox" class="custom-control-input" id="<?=$lcIdDiscap?>" name="<?=$lnDiscap?>">
														<label class="custom-control-label" for="<?=$lcIdDiscap?>"><?=$lcDiscap?></label>
													</div>
												</div>
											</div>
											<?php
										}
									?>
									</div>
								</div>
							</div>
						</div>

					<?php	} ?>

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

			<!-- Vacuna COVID-19 -->
			<div class="col-12 pb-4" id="divVacunaCovid">
				<div class="row">
					<div class="col-6 col-md-4 col-xl-3">
						<label for="selVacunaCovid" id="lblVacunaCovid" class="control-label required"><i class="fas fa-syringe"></i> Vacuna Covid 19</label>
						<select id="selVacunaCovid" class="form-control form-control-sm" data-codigo="24" data-nombre="SARS-CoV-2"></select>
						<div class="invalid-tooltip">Debe adicionar registro de Vacuna Covid 19</div>
					</div>
					<div class="col-6 col-md-4 col-xl-3">
						<label for="selLabVacuna" class="control-label">Laboratorio</label>
						<select id="selLabVacuna" class="form-control form-control-sm" disabled></select>
					</div>
					<div class="col-6 col-md-4 col-xl-3">
						<label for="selDosisVacuna" class="control-label">Dosis</label>
						<select id="selDosisVacuna" class="form-control form-control-sm" disabled></select>
					</div>
					<div class="col-6 col-md-4 col-xl-3">
						<label for="fechaVacuna" class="control-label">Fecha Dosis</label>
						<div class="input-group date">
							<div class="input-group-prepend">
								<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>
							</div>
							<input id="fechaVacuna" type="text" class="form-control form-control-sm" value="" disabled />
						</div>
					</div>
				</div>
				<div class="row pt-2">
					<div class="col-auto ml-auto">
						<button id="btnAddVacuna" class="btn btn-secondary btn-sm">Adicionar</button>
					</div>
				</div>
				<div class="row pt-2">
					<div class="col">
						<table id="tblVacunas"></table>
					</div>
				</div>
				<!-- Fin Vacuna COVID-19 -->
			</div>

			<div class="col-12 pb-4" id="divPrenatal">
				<div class="row pb-2">
					<div class="col-lg-4 col-md-4 col-sm-10 col-10">
						<label id="lblEdadGestacional" for="txtEdadGestacional">Edad gestacional (Semanas)</label>
						<input id="txtEdadGestacional" name="edadgestacional" type="number" placeholder="" class="form-control mr-sm-2">
					</div>

					<div class="col-lg-4 col-md-4 col-sm-10 col-10">
						<label id="lblNroPrenatales" for="txtNroPrenatales">Número de consultas prenatales</label>
						<input id="txtNroPrenatales" name="nroprenatales" type="number" placeholder="" class="form-control mr-sm-2">
					</div>
				</div>
			</div>

			<div id="divActividadFisica" class="card-body">
				<?php
					include __DIR__ .'/../comun/escala_actividad_fisica.php';
				?>
			</div>

		</form>
	</div>
</div>

<script type="text/javascript" src="vista-historiaclinica/js/Antecedentes.js"></script>
<script type="text/javascript">var gcHoy='<?php print(date("Y-m-d")); ?>'</script>
