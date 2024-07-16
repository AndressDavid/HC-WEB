<div class="card card-block">
	<div class="card-header" id="headerUnidades">
		<a href="#unidades" class="card-link text-dark"><b>Registro unidades</b></a>
	</div>

	<div class="card-body">
		<form role="form" id="FormRegistroUnidades" name="FormRegistroUnidades" method="POST" enctype="application/x-www-form-urlencoded" novalidate="validate" action="#">
			<div class="row pb-2">
				<div class="col-12">
					<label id="lblAntecedentesUci" for="edtAntecedentesUci">Antecedentes U.C.I</label>
					<textarea class="form-control <?= $lcCopyPaste ?>" id="edtAntecedentesUci" name="AntecedentesUci" rows="5"></textarea>
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12">
					<label id="lblSubjetivoUci" for="edtSubjetivoUci">Subjetivo</label>
					<textarea class="form-control <?= $lcCopyPaste ?>" id="edtSubjetivoUci" name="SubjetivoUci" rows="5"></textarea>
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12">
					<label id="lblResultadosLaboratorioUci" for="edtResultadosLaboratorioUci">Resultados de Laboratorio</label>
					<textarea class="form-control <?= $lcCopyPaste ?>" id="edtResultadosLaboratorioUci" name="ResultadosLaboratorioUci" rows="5"></textarea>
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12">
					<label id="lblEcgUci" for="edtEcgUci">ECG</label>
					<textarea class="form-control <?= $lcCopyPaste ?>" id="edtEcgUci" name="EcgUci" rows="5"></textarea>
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12">
					<label id="lblRxToraxUci" for="edtRxToraxUci">RX de Torax</label>
					<textarea class="form-control <?= $lcCopyPaste ?>" id="edtRxToraxUci" name="RxToraxUci" rows="5"></textarea>
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12">
					<label id="lblGasimetriaAvUci" for="edtGasimetriaAvUci">Gasimetria A/V</label>
					<textarea class="form-control <?= $lcCopyPaste ?>" id="edtGasimetriaAvUci" name="GasimetriaAvUci" rows="5"></textarea>
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12">
					<label id="lblPerfilHemodinamicoUci" for="edtPerfilHemodinamicoUci">Perfil hemodinámico</label>
					<textarea class="form-control <?= $lcCopyPaste ?>" id="edtPerfilHemodinamicoUci" name="PerfilHemodinamicoUci" rows="5"></textarea>
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12">
					<label id="lblSignosUci" for="edtSignosUci">Signos vitales</label>
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12 col-sm-6 col-md-6 col-lg-3 col-xl-3">
					<label id="lblFcUci" for="txtFcUci">Frecuencia Cardiáca</label>
					<input id="txtFcUci" name="Fcuci" type="number" class="form-control mr-sm-2">
				</div>

				<div class="col-12 col-sm-6 col-md-6 col-lg-3 col-xl-3">
					<label id="lblFrUci" for="txtFrUci">Frecuencia Respiratoria</label>
					<input id="txtFrUci" name="Fruci" type="number"  class="form-control mr-sm-2">
				</div>

				<div class="col-12 col-sm-6 col-md-6 col-lg-3 col-xl-3">
					<label id="lblPasUci" for="txtPasUci">Tensión Arterial Sistólica</label>
					<input id="txtPasUci" name="Pasuci" type="number" class="form-control mr-sm-2">
				</div>

				<div class="col-12 col-sm-6 col-md-6 col-lg-3 col-xl-3">
					<label id="lblPadUci" for="txtPadUci">Tensión Arterial Diastólica.</label>
					<input id="txtPadUci" name="Paduci" type="number"  class="form-control mr-sm-2">
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12 col-sm-6 col-md-6 col-lg-3 col-xl-3">
					<label id="lblPamUci" for="txtPamUci">Tensión Arterial Media</label>
					<input id="txtPamUci" name="Pamuci" type="number" class="form-control mr-sm-2">
				</div>

				<div class="col-12 col-sm-6 col-md-6 col-lg-3 col-xl-3">
					<label id="lblPvcUci" for="txtPvcUci">Presión Venosa Central</label>
					<input id="txtPvcUci" name="Pvcuci" type="number" class="form-control mr-sm-2">
				</div>

				<div class="col-12 col-sm-6 col-md-6 col-lg-3 col-xl-3">
					<label id="lblPcpUci" for="txtPcpUci">Presión Capilar Pulmonar</label>
					<input id="txtPcpUci" name="Pcpuci" type="number" class="form-control mr-sm-2">
				</div>

				<div class="col-12 col-sm-6 col-md-6 col-lg-3 col-xl-3">
					<label id="lblIcUci" for="txtIcUci">I.C.</label>
					<input id="txtIcUci" name="Icuci" type="number" class="form-control mr-sm-2">
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12">
					<label id="lblExamenFisicoUci" for="edtExamenFisicoUci">Examen físico</label>
					<textarea class="form-control <?= $lcCopyPaste ?>" id="edtExamenFisicoUci" name="ExamenFisicoUci" rows="5"></textarea>
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12">
					<label id="lblManejoActualUci" for="edtManejoActualUci">Manejo actual</label>
					<textarea class="form-control <?= $lcCopyPaste ?>" id="edtManejoActualUci" name="ManejoActualUci" rows="5"></textarea>
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12">
					<label id="lblExamenSolicitarUci" for="edtExamenSolicitarUci">Examenes y procedimientos a solicitar</label>
					<textarea class="form-control <?= $lcCopyPaste ?>" id="edtExamenSolicitarUci" name="ExamenSolicitarUci" rows="5"></textarea>
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12 col-sm-6 col-md-6 col-lg-3 col-xl-2">
					<label id="lblApacheUci" for="txtApacheUci">Apache</label>
					<input id="txtApacheUci" name="ApacheUci" type="number" class="form-control mr-sm-2">
				</div>

				<div class="col-12 col-sm-6 col-md-6 col-lg-3 col-xl-2">
					<label id="lblSofaUci" for="txtSofaUci">Sofa</label>
					<input id="txtSofaUci" name="SofaUci" type="number"  class="form-control mr-sm-2">
				</div>

				<div class="col-12 col-sm-6 col-md-6 col-lg-3 col-xl-2">
					<label id="lblParsonettUci" for="txtParsonettUci">Parsonett</label>
					<input id="txtParsonettUci" name="ParsonettUci" type="number" class="form-control mr-sm-2">
				</div>

				<div class="col-12 col-sm-6 col-md-6 col-lg-3 col-xl-2">
					<label id="lblTimiUci" for="txtTimiUci">TIMI</label>
					<input id="txtTimiUci" name="TimiUci" type="number"  class="form-control mr-sm-2">
				</div>

				<div class="col-12 col-sm-6 col-md-6 col-lg-3 col-xl-2">
					<label id="lblTissUci" for="txtTissUci">TISS-28</label>
					<input id="txtTissUci" name="TissUci" type="number"  class="form-control mr-sm-2">
				</div>

				<div class="col-12 col-sm-6 col-md-6 col-lg-3 col-xl-2">
					<label id="lblPocasUci" for="txtPocasUci">Pocas</label>
					<input id="txtPocasUci" name="PocasUci" type="number"  class="form-control mr-sm-2">
				</div>
			</div>

			<div class="row pb-2">
				<div class="col-12">
					<label id="lblPronosticoUci" for="edtPronosticoUci">Pronostico</label>
					<textarea class="form-control <?= $lcCopyPaste ?>" id="edtPronosticoUci" name="PronosticoUci" rows="5"></textarea>
				</div>
			</div>

		</form>

		<?php
			include __DIR__ . '/analisis.php';
		?>

	</div>
</div>
<script type="text/javascript" src="vista-evoluciones/js/registro_unidad.js"></script>
