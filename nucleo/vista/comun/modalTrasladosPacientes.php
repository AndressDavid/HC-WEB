<?php
	$laAuditoria['cUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getUsuario();
	$laAuditoria['cEspUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getEspecialidad();
	$laAuditoria['cTipopUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getTipoUsuario(false);
	$laAuditoria['cEspUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getEspecialidad();
	
	require_once (__DIR__ .'/../../controlador/class.SignosNews.php') ;
	$loSignosNews = new NUCLEO\SignosNews();
?>

<div id="divTrasladosPacientes" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="divTrasladosPacientes" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header badge badge-light">
				<h6 class="modal-title col-11 text-center" id="divTrasladosPacientes">TRASLADOS PACIENTES</h5>
			</div>
			<div id="divCapturaAgendaBody" class="modal-body">
				<form id="FormTrasladosPacientes" name="FormTrasladosPacientes" class="needs-validation" novalidate>
					<div class="row">
						<div id="txtDatosIngreso" style="font-size: 22px; font-weight: bold;" class="col"></div>
					</div><br>
					<div class="form-row pb-2">
						<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
							<div class="row">
								<div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-4">
									<label id="lblAreaTrasladarTP" for="selAreaTrasladarTP" class="required">Área a trasladar</label>									
									<select class="custom-select d-block w-100" id="selAreaTrasladarTP" name="AreaTrasladarTP">
									<option value=""></option></select>
								</div>

								<div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-4">
									<label id="lblEspecialidadTrasladarTP" for="selEspecialidadTrasladarTP" class="required">Especialidad a trasladar</label>									
									<select class="custom-select d-block w-100" id="selEspecialidadTrasladarTP" name="EspecialidadTrasladarTP">
										<option value=""></option>
									</select>
								</div>

								<div class="col-12 col-sm-12 col-md-12 col-lg-4 col-xl-4">
									<label id="lblMedicoTrasladaTP" for="selMedicoTrasladaTP" class="required">Médico recibe</label>									
									<select class="custom-select d-block w-100" id="selMedicoTrasladaTP" name="MedicoTrasladaTP">
										<option value=""></option>
									</select>
								</div>
							</div>
						</div>
					</div>
					
					<div class="form-row pb-2">
						<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
							<div class="row">
								<div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-4">
									<label id="lblSeInformaFamiliarTP" for="selSeInformaFamiliarTP"class="required">Se informa a familiar de traslado</label>									
									<select class="custom-select d-block w-100" id="selSeInformaFamiliarTP" name="SeInformaFamiliarTP">
									<option value=""></option><option value="S">Si</option><option value="N">No</option></select>
								</div>

								<div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-4">
									<label id="lblSetrasladaFamiliarTP" for="SelSetrasladaFamiliarTP"class="required">Se traslada en compañia de familiar</label>									
									<select class="custom-select d-block w-100" id="SelSetrasladaFamiliarTP" name="SetrasladaFamiliarTP">
										<option value=""></option>
										<option value="S">Si</option>
										<option value="N">No</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<br><h5 class="required">Signos vitales</h5>					
					<div class="row align-items-end">
						<?php
							foreach($loSignosNews->getSignos() as $lcSigno => $laSigno){
								if(is_array($laSigno)){
									printf('<div class="col-lg-3 col-md-3 col-sm-6 col-12">');
									printf('<div class="form-group">');
									printf('<label for="lastName"><b>%s</b></label>',$laSigno['titulo']);

									switch ($laSigno['tipo']){
										case 'select':
											printf('<select class="form-control form-control-lg" id="%s" name="%s" required="">',$lcSigno,$lcSigno);
											printf('<option></option>');
											if(isset($laSigno['valores'])==true){
												$laSelectValores=$laSigno['valores'];
												foreach($laSelectValores as $lnKey=>$laValue){
													printf('<option value="%s">%s</option>',$lnKey,$laValue["NOMBRE"]);
												}
											}
											printf('</select>');
											break;

										default:
											printf('<input type="number" class=" form-control form-control-lg" id="%s" name="%s" placeholder="" value="" required="">',$lcSigno,$lcSigno);
											break;
									}
									printf('</div>');
									printf('</div>');
								}
							}
						?>
					</div>
					
					<div class="form-row pb-2">
						<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
							<div class="row">
								<div class="col-12 col-sm-12 col-md-6 col-lg-3 col-xl-3">
									<label id="lblEscalaDolorTr" for="selEscalaDolorTr" class="required">Escala de dolor</label>									
									<select class="custom-select d-block w-100" id="selEscalaDolorTr" name="EscalaDolorTr">
									<option value=""></option></select>
								</div>
							</div>
						</div>
					</div>
					
					<div class="form-row pt-2" id="divAnalisis">
						<label id="lblRegistrarTraslado" for="edtRegistrarTraslado"class="required">Justificación de traslado</label>
						<div class="col-12">
							<textarea rows="4" type="text" class="form-control" id="edtRegistrarTraslado" name="RegistrarTraslado"></textarea>
						</div>
					</div>
					<label id="lblCaracteresTraslado" for="txtCaracteresTraslado" class="text-primary" style="font-size:12px;"></label>
				</form>
				<script>
					$( document ).ready( function () {
						$( "#FormTrasladosPacientes" ).validate( {
							rules: {
								ingreso: {
									required: true,
									digits: true
								},
								<?php
									if(isset($loSignosNews)==true){
										foreach($loSignosNews->getSignos() as $lcSigno => $laSigno){
											if(is_array($laSigno)){
												if(isset($laSigno['max'])==true && isset($laSigno['min'])==true){
													switch($laSigno['dato']){
														case "integer":
															printf('%s: {required: true, digits: true, min: %s, max: %s},',$lcSigno,$laSigno['min'],$laSigno['max']);
															break;
														case "float":
															$lcStep='';
															if($lcSigno='t'){
																$lcStep=', step: 0.01';
															}
															printf('%s: {required: true, range: [%s, %s]%s},',$lcSigno,$laSigno['min']+0.00,$laSigno['max']+0.00,$lcStep);
															break;
													}
												}
											}
										}
									}
								?>
							},
							errorElement: "div",
							errorPlacement: function ( error, element ) {
								// Add the `help-block` class to the error element
								error.addClass( "invalid-tooltip" );

								if ( element.prop( "type" ) === "checkbox" ) {
									error.insertAfter( element.parent( "label" ) );
								} else {
									error.insertAfter( element );
								}
							},
							highlight: function ( element, errorClass, validClass ) {
								$( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
							},
							unhighlight: function (element, errorClass, validClass) {
								$( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
							},
							submitHandler: function () {
								$("#btnGuardar").attr("disabled",  true);
								$.ajax({
									type: 'POST',
									url: "vista-signos/guardarSignos",
									data: $("#registroSignosForm").serialize()
								})
								.done(function(response) {
									$('#registroSignosInfo').html(response);
									$("#modalSignosGuardar").on("hidden.bs.modal", function () {
										$('#registroSignosInfo').html('').removeClass("alert").removeClass("alert-warning").removeAttr("role");
										$("#registroSignosForm")[0].reset();
										$(location).attr('href', 'modulo-signos<?php print(isset($_GET['seccion'])==true?'&seccion='.$_GET['seccion']:''); ?>');
									});
									$('#modalSignosGuardar').modal('show');
								})
								.fail(function(data) {
									$('#registroSignosInfo').html('<i class="fa fa-exclamation-triangle"></i> Se presento un error al guardar el ingreso').addClass("alert").addClass("alert-danger").attr("role","alert");
								});
							}
						} );
					} );
				</script>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Guardar información" aria-pressed="true" id="btnGuardaTrasladosPacientes" accesskey="G"><u>G</u>uardar</button>
				<button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Consultar registros anteriores" aria-pressed="true" id="btnHistoricoTraslados" accesskey="R"><u>R</u>egistros anteriores</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarTrasladosPacientes" accesskey="S"><u>S</u>alir</button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="vista-comun/js/modalTrasladosPacientes.js"></script>

<script type="text/javascript">
	var aAuditoria = btoObj('<?= base64_encode(json_encode($laAuditoria)) ?>');
</script>