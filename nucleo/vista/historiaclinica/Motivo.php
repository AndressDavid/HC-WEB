<div class="card card-block">
	<div class="card-header" id="headerMotivo">
		<a href="#Motivo_uno" class="card-link text-dark"><b>Motivo de Consulta</b></a>
	</div>

	<div class="card-body">
		<form role="form" id="FormMotivo" name="FormMotivo" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">

			<div class="form-row">
				<label id="lblTipoCausa" for="selTipoCausa">Tipo de Causa</label>
				<div class="col-12">
					<select id="selTipoCausa" name="Causa" class="custom-select w-50">
					</select>
				</div>
			</div>

			<div class="form-row">
				<label id="lblMotivo" for="edtMotivo">Motivo de Consulta</label>
				<div class="col-12">
					<textarea type="text" class="form-control <?= $lcCopyPaste ?>" id="edtMotivo" name="Motivo"></textarea>
				</div>
			</div>

			<div class="form-row">
				<label for="lblEnfermedadActual"><b>Enfermedad Actual</b></label>
				<div class="col-12">
					<label for="edtEvento">Evento que originó la atención</label>
					<textarea type="text" class="form-control <?= $lcCopyPaste ?>" id="edtEvento" name="Evento"></textarea>
				</div>
			</div>

			<div class="form-row pt-2" id="divRemision">
				<div class="col-lg-3 col-md-5 col-sm-12 col-12">
					<label id="lblRemisionIPS" for="lblRemision">Ingreso por remisión de otra IPS? </label>
				</div>
				<div class="col-lg-2 col-md-3 col-sm-6 col-6">
					<select id="selRemisionIPS" name="Remision" class="custom-select">
						<option></option>
						<option>Si</option>
						<option>No</option>
					</select>
				</div>
			</div>

			<div class="form-row" id="divRecibido">
				<div class="col-12">
					<label id="lblRelacion" for="edtRelacion">Relación de recibido</label>
					<textarea  type="text"  class="form-control <?= $lcCopyPaste ?>" id="edtRelacion" name="Relacion"></textarea>
				</div>
				<small>
					<label for="lblInformación">
						Información a diligenciar en este campo: Vehículo en que fue transportado: AMBULANCIA BÁSICA/ AMBULANCIA MEDICALIZADA / OTRO, condiciones del paciente al ingreso: ESTABLE / NO ESTABLE / CRÍTICO y si existe eventualidad.
					</label>
				</small>
			</div>
		</form>
	</div>
</div>


<div class="card card-block">
	<div class="card-header" id="headerMotivoDolorT">
		<a href="#Motivo_dos" class="card-link text-dark" data-toggle="collapse" data-parent="#accordionForm"><b>Dolor Torácico</b></a>
	</div>

	<div class="card-body">

		<form role="form" id="FormDolorT" name="FormDolorT" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
			<div class="card border">
				<div class="card-header bg-transparent border"><b>Caracteristicas</b></div>

				<div class="card-body">
					<div class="row align-items-bottom">
						<div class="col-lg-2 col-md-6 col-sm-12 col-12">
							<div class="form-group" name="Cr_1" value="1">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkCr_1" name="0101" value="1">
									<label class="custom-control-label" for="chkCr_1">Opresión</label>
								</div>
							</div>
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkCr_2" name="0102" value="1">
									<label class="custom-control-label" for="chkCr_2">Ardor</label>
								</div>
							</div>
						</div>

						<div class="col-lg-3 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkCr_3" name="0103" value="1">
									<label class="custom-control-label" for="chkCr_3">Punzada</label>
								</div>
							</div>
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkCr_4" name="0104" value="1">
									<label class="custom-control-label" for="chkCr_4">Empeora Cambio de Posición</label>
								</div>
							</div>
						</div>

						<div class="col-lg-2 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkCr_5" name="0105" value="1">
									<label class="custom-control-label" for="chkCr_5">Empeora Respiración</label>
								</div>
							</div>
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkCr_6" name="0106" value="1">
									<label class="custom-control-label" for="chkCr_6">Intermitente</label>
								</div>
							</div>
						</div>

						<div class="col-lg-2 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkCr_7" name="0107" value="1">
									<label class="custom-control-label" for="chkCr_7">Permanente</label>
								</div>
							</div>
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkCr_8" name="0108" value="1">
									<label class="custom-control-label" for="chkCr_8">Produce con Inspiración</label>
								</div>
							</div>
						</div>

						<div class="col-lg-2 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkCr_9" name="0109" value="1">
									<label class="custom-control-label" for="chkCr_9">Produce con Tos</label>
								</div>
							</div>
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkCr_10" name="0110" value="1">
									<label class="custom-control-label" for="chkCr_10">Otro</label>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="card border">
				<div class="card-header bg-transparent border"><b> Irradiación</b>
				</div>
				<div class="card-body">
					<div class="row align-items-bottom">
						<div class="col-lg-2 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkIrr_1" name="0201" value="1">
									<label class="custom-control-label" for="chkIrr_1">Maxilar Inferior</label>
								</div>
							</div>
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkIrr_2" name="0202" value="1">
									<label class="custom-control-label" for="chkIrr_2">Cuello</label>
								</div>
							</div>
						</div>

						<div class="col-lg-3 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkIrr_3" name="0203" value="1">
									<label class="custom-control-label" for="chkIrr_3">Miembro Superior Izquierdo</label>
								</div>
							</div>
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkIrr_4" name="0204" value="1">
									<label class="custom-control-label" for="chkIrr_4">Miembro Superior Derecho</label>
								</div>
							</div>
						</div>

						<div class="col-lg-2 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkIrr_5" name="0205" value="1">
									<label class="custom-control-label" for="chkIrr_5">Interescapular</label>
								</div>
							</div>
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkIrr_6" name="0206" value="1">
									<label class="custom-control-label" for="chkIrr_6">Ambos</label>
								</div>
							</div>
						</div>

						<div class="col-lg-2 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkIrr_7" name="0207" value="1">
									<label class="custom-control-label" for="chkIrr_7">Dorsal</label>
								</div>
							</div>
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkIrr_8" name="0208" value="1">
									<label class="custom-control-label" for="chkIrr_8">No irradia</label>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="card border">
				<div class="card-header bg-transparent border"><b> Sintomas</b>
				</div>
				<div class="card-body">
					<div class="row align-items-bottom">
						<div class="col-lg-2 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkSin_1" name="0301" value="1">
									<label class="custom-control-label" for="chkSin_1">Mareo</label>
								</div>
							</div>
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkSin_2" name="0302" value="1">
									<label class="custom-control-label" for="chkSin_2">Nauseas</label>
								</div>
							</div>
						</div>

						<div class="col-lg-3 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkSin_3" name="0303" value="1">
									<label class="custom-control-label" for="chkSin_3">Sudoración</label>
								</div>
							</div>
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkSin_4" name="0304" value="1">
									<label class="custom-control-label" for="chkSin_4">Vomito</label>
								</div>
							</div>
						</div>

						<div class="col-lg-2 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkSin_5" name="0305" value="1">
									<label class="custom-control-label" for="chkSin_5">Palidez</label>
								</div>
							</div>
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkSin_6" name="0306" value="1">
									<label class="custom-control-label" for="chkSin_6">Disnea</label>
								</div>
							</div>
						</div>

						<div class="col-lg-2 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" class="custom-control-input" id="chkSin_7" name="0307" value="1">
									<label class="custom-control-label" for="chkSin_7">Otro</label>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="card border">
				<div class="card-header bg-transparent border"><b> Localizacion</b>
				</div>
				<div class="card-body">
					<div class="row align-items-bottom">
						<div class="col-lg-2 col-md-6 col-sm-12 col-12">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input type="checkbox" class="custom-control-input" id="chkLoc_1" name="0401" value="1">
								<label class="custom-control-label" for="chkLoc_1">Retroesternal</label>
							</div>
						</div>

						<div class="col-lg-2 col-md-6 col-sm-12 col-12">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input type="checkbox" class="custom-control-input" id="chkLoc_2" name="0402" value="1">
								<label class="custom-control-label" for="chkLoc_2">Precordial</label>
							</div>
						</div>

						<div class="col-lg-2 col-md-6 col-sm-12 col-12">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input type="checkbox" class="custom-control-input" id="chkLoc_3" name="0403" value="1">
								<label class="custom-control-label" for="chkLoc_3">Epigastrico</label>
							</div>
						</div>

						<div class="col-lg-2 col-md-6 col-sm-12 col-12">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input type="checkbox" class="custom-control-input" id="chkLoc_4" name="0404" value="1">
								<label class="custom-control-label" for="chkLoc_4">Dorsal</label>
							</div>
						</div>

						<div class="col-lg-2 col-md-6 col-sm-12 col-12">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input type="checkbox" class="custom-control-input" id="chkLoc_5" name="0405" value="1">
								<label class="custom-control-label" for="chkLoc_5">Otro</label>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="card border">
				<div class="card-header bg-transparent border"><b> Tiempo</b>
				</div>
				<div class="card-body">

					<div class="row align-items-bottom">
						<div class="col-lg-4 col-md-6 col-sm-12 col-12">
							<label id="lblIntensidad" for="txtIntensidad">Intensidad</label>
							<input id="txtIntensidad" name="Intensidad" type="number" placeholder="Intensidad" class="form-control mr-sm-2 w-50">
						</div>
					</div>

					<br><label for="lblDuracion"><b>Duración</b></label>
					<div class="row align-items-bottom">
						<div class="col-lg-4 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<label id="lblSegundosD" for="txtSegundosD">Segundos</label>
								<input id="txtSegundosD" name="Dsegundos" type="number" placeholder="Segundos" class="form-control mr-sm-2 w-50">
							</div>
						</div>

						<div class="col-lg-4 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<label id="lblMinutosD" for="txtMinutosD">Minutos</label>
								<input id="txtMinutosD" name="Dminutos" type="number" placeholder="Minutos" class="form-control mr-sm-2 w-50">
							</div>
						</div>

						<div class="col-lg-4 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<label id="lblHorasD" for="txtHorasD">Horas</label>
								<input id="txtHorasD" name="Dhoras" type="number"  placeholder="Horas" class="form-control mr-sm-2 w-50">
							</div>
						</div>

						<div class="col-lg-4 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<label id="lblDiasD" for="txtDiasD">Días</label>
								<input id="txtDiasD" name="Ddias" type="number" placeholder="Dias" class="form-control mr-sm-2 w-50">
							</div>
						</div>
					</div>

					<br><label for="lblTiempo"><b>Tiempo de Evolución</b></label>

					<div class="row align-items-bottom">

						<div class="col-lg-4 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<label id="lblSegundosE" for="txtSegundosE">Segundos</label>
								<input id="txtSegundosE" name="Tsegundos" type="number" placeholder="Segundos" class="form-control mr-sm-2 w-50">
							</div>
						</div>

						<div class="col-lg-4 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<label id="lblMinutosE" for="txtMinutosE">Minutos</label>
								<input id="txtMinutosE" name="Tminutos" type="number" placeholder="Minutos" class="form-control mr-sm-2 w-50">
							</div>
						</div>

						<div class="col-lg-4 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<label id="lblHorasE" for="txtHorasE">Horas</label>
								<input id="txtHorasE" name="Thoras" type="number" placeholder="Horas" class="form-control mr-sm-2 w-50">
							 </div>
						</div>

						<div class="col-lg-4 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<label id="lblDiasE" for="txtDiasE">Días</label>
								<input id="txtDiasE" name="Tdias" type="number"  placeholder="Dias" class="form-control mr-sm-2 w-50">
							</div>
						</div>

						<div class="col-lg-4 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<label id="lblSemanasE" for="txtSemanasE">Semanas</label>
								<input id="txtSemanasE" name="Tsemanas" type="number"  placeholder="Semanas" class="form-control mr-sm-2 w-50">
							</div>
						</div>

						<div class="col-lg-4 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<label id="lblMesesE" for="txtMesesE">Meses</label>
								<input id="txtMesesE" name="Tmeses" type="number" placeholder="Meses" class="form-control mr-sm-2 w-50">
							</div>
						</div>

						<div class="col-lg-4 col-md-6 col-sm-12 col-12">
							<div class="form-group">
								<label id="lblAnosE" for="txtAnosE">Años</label>
								<input id="txtAnosE" name="Tanos" type="number" placeholder="Años" class="form-control mr-sm-2 w-50">
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>


<script type="text/javascript" src="vista-historiaclinica/js/TiposCausa.js"></script>
<script type="text/javascript" src="vista-historiaclinica/js/Motivo.js"></script>
