var interconsultas ={

	gcUrlajax: "vista-hc-interconsultas/ajax/ajax",
	gdFechaConsulta: '',
	goEstados: {},
	lcTipo: 'int',
	gaParamPrevia: [],
	lsInterfaz: 'inter',
	lRutaAgility: "http://xero.shaio.org/xero/?theme=epr&PatientId",
	lRutaLaboratorio: "http://srvlablisweb/cgi/Usuario.cgi?AccionServidor=AccionOrdenesNShaio&Alias=HIS&Clave=HIS&NShaio",
	lTipoConsulta:'',
	lRegresar: false,

	inicializar: function(){

		$('footer').ready(function(){
			$("footer").css( "margin-bottom", "55px");
		}),
		$("#txtCodigoCie").on("keydown", function(event) {
			if(event.which == 13){
				setTimeout(function() {if($('#cCodigoCie').val() !=''){
					$("#cCodigoCie").addClass("is-valid").removeClass("is-invalid");
					$("#txtCodigoCie").addClass("is-valid").removeClass("is-invalid");
					$("#cDescripcionCie").addClass("is-valid").removeClass("is-invalid");
				}}, (300));
			}
		}),
		$(document).ready(function(){
			$("#txtCodigoCie").change(function(){
				setTimeout(function() {if($('#cCodigoCie').val() !=''){
					$("#cCodigoCie").addClass("is-valid").removeClass("is-invalid");
					$("#txtCodigoCie").addClass("is-valid").removeClass("is-invalid");
					$("#cDescripcionCie").addClass("is-valid").removeClass("is-invalid");
				}}, (100));	
			});
		}),	
		oCabDatosPac.inicializar(),
		$("#interconsultas").validate({
			rules: {
				txtRespuestaInterconsulta: "required",
				txtAnalisisEpicrisis: "required",
				cCodigoCie: "required",
				tipoDiagnostico: "required",
				txtRespuestaInterconsulta: "required",
				txtAnalisisEpicrisis: "required",
				txtNuevoSeguimiento: "required"
			},
			errorElement: "div",
			errorPlacement: function(error, element) {
				error.addClass("invalid-tooltip");
				if ( element.prop("type") === "checkbox" ) {
					error.insertAfter(element.parent("label") );
				} else {
					error.insertAfter(element);
				}
			},
			highlight: function (element, errorClass, validClass) {
				$(element).addClass("is-invalid").removeClass("is-valid");
			},
			unhighlight: function (element, errorClass, validClass) {
				$(element).addClass("is-valid").removeClass("is-invalid");
			},
		}),
		oDiagnosticos.consultarDiagnostico('txtCodigoCie','cCodigoCie','cDescripcionCie','INTERCONSULTAS','tipoDiagnostico','');
		oDiagnosticos.cargarListaDiagnosticos('tipoDiagnostico','clase','tipos de diagnóstico');
		$("#frmFiltros label").css('margin-bottom','0.1rem');
		this.respondeInterconsulta();
		$('#btnEvolucionesOM').on('click', function(){ formPostTemp('modulo-evoconsulta', {'ingreso':aDatosIngreso.nIngreso}, true); });
		$('#btnLaboratorios').on('click', this.abrirlaboratorios);
		$('#btnAgility').on('click', this.abrirAgility);
		$('#btnLibroHC').on('click', abrirLibro);
		$('#btnVerPdfHC').on('click', function(){ vistaPreviaPdf({'datos':JSON.stringify(gaParamPrevia)}, null, 'RESPUESTA INTERCONSULTA '+gaParamPrevia[0].tFechaHora, 'RTAINTERC'); });
		$('#btnVistaPrevia').on('click', function(){ oModalVistaPrevia.mostrar(gaParamPrevia[0], 'RESPUESTA INTERCONSULTA '+gaParamPrevia[0].tFechaHora, 'RTAINTERC'); });
		$('#btnTextoInf').on('click', function(){ oTextoInformativo.mostrar(); });
		$('#btnVolver').on('click', function(){ interconsultas.validaSalida(); });
		$('#btnGuardarResultadosInterConsulta').on('click', this.validarRespuestasInterConsulta);
	},

	abrirAgility: function(){
		lcRuta = this.lRutaAgility +"=" + goFilaSelInter.ingreso.cTipId + goFilaSelInter.ingreso.nIngreso + "&user=MEDICO&password=medico";
		window.open(lcRuta, "_blank");
	},

	abrirlaboratorios: function (){
		lcRuta = this.lRutaLaboratorio+"=" + goFilaSelInter.ingreso.nIngreso;
		window.open(lcRuta, "_blank");
	},

	limpiarBloquearCampos: function(){
		$('#txtNuevoSeguimiento').attr("disabled",true);
		$('#txtRespuestaInterconsulta').attr("disabled",true);
		$('#txtAnalisisEpicrisis').attr("disabled",true);
		$('#btnGuardarResultadosInterConsulta').attr("disabled",true);
	},

	BloquearInpusts: function(){
		$('#txtNuevoSeguimiento').attr('disabled',true);
		$('#txtMedicoSolicitud').attr('disabled',true);
		$('#txtNuevoSeguimiento').attr('disabled',true);
		$('#txtSeguimientos').attr('disabled',true);
		$('#selAceptaTrasladoPaciente').attr('disabled',true);
		$('#selAceptaTrasladoPaciente').attr('disabled',true);
		$('#txtCodigoCie').attr('disabled',true);
		$('#cCodigoCie').attr('disabled',true);
		$('#cDescripcionCie').attr('disabled',true);
		$('#tipoDiagnostico').attr('disabled',true);
		$('#txtRespuestaInterconsulta').attr('disabled',true);
		$('#txtAnalisisEpicrisis').attr('disabled',true);
		$('#btnGuardarResultadosInterConsulta').attr('disabled',true);
	},
	aplicarDesBloqueosEdicion: function(bloqueo_seguimiento,bloqueo_respuestas ){
		if(bloqueo_seguimiento==0){

			
			$('#txtNuevoSeguimiento').attr("disabled",false);
			$("#txtNuevoSeguimiento").attr('placeholder', '');

			$("#cCodigoCie").attr('value','NA');
			$("#tipoDiagnostico").append('<option selected>NA</option>');
			$("#txtRespuestaInterconsulta").val('Para ingresar la respuesta de la interconsulta debe tener la Especialidad solicitada en la Interconsulta o Equivalente, y no requerir Aval');
			$("#txtAnalisisEpicrisis").val('Para ingresar el análisis de Epicrisis, debe tener la Especialidad solicitada en la Interconsulta o Equivalente, y no requerir Aval');
	
		}
		if(bloqueo_respuestas==0){
			
			$('#txtCodigoCie').attr("disabled",false);
			$('#tipoDiagnostico').attr("disabled",false);
			$('#txtRespuestaInterconsulta').attr("disabled",false);
			$('#txtAnalisisEpicrisis').attr("disabled",false);
			$("#selAceptaTrasladoPaciente").attr("disabled",false);
			$("#txtRespuestaInterconsulta").attr('placeholder', '');
			$("#txtNuevoSeguimiento").val('Para ingresar Seguimientos de Interconsultas, debe tener la misma Especialidad del Médico Solicitante, y no requerir Aval.');
		
		}
		if( (bloqueo_seguimiento==0) || (bloqueo_respuestas)==0 ){
			$('#btnGuardarResultadosInterConsulta').attr("disabled",false);
		}
	},

	
	respondeInterconsulta: function () {
		interconsultas.limpiarBloquearCampos();
		if (goFilaSelInter.NINORD > 0) {
			$("#divPacienteInterConsultas").html('Espere por favor ... <i class="fas fa-circle-notch fa-spin" style="font-size: 1.5em; color: Tomato;">');
			$("#modalInterConsultas").modal("show");
			$.ajax({
				type: "POST",
				url: this.gcUrlajax,
				data: {
					accion:			'atiende_interconsulta',
					ingreso: 		goFilaSelInter.NINORD,
					numOrd: 		goFilaSelInter.CCIORD,
					numCUP: 		goFilaSelInter.CODCUP,
					nRegmedRealiza: goFilaSelInter.RMRORD,
					codOrd: 		goFilaSelInter.CODORD,
					RMeOrd: 		goFilaSelInter.RMeOrd,
					RMROrd: 		goFilaSelInter.RMROrd,
					origSol:		goFilaSelInter.lcTipo,
					lsInterfaz:     goFilaSelInter.lsInterfaz ?? 'inter'
				},
				dataType: "json"
			})
			.done(function(loRet) {
				try {
					interconsultas.lTipoConsulta=loRet.datosInterconsulta.TipoInterc;
					lsInterfaz =  goFilaSelInter.lsInterfaz ?? 'inter';
					aDatosIngreso= loRet.ingreso;
					$.extend( goFilaSelInter, loRet);
					if (loRet.error == ''){
						if(goFilaSelInter.datosInterconsulta.TipoInterc!='Traslado'){
							$("#divAceptaTrasladoPaciente").css("display", 'none');
						}
						if ( (goFilaSelInter.datosMedicoSolicito.Especialidad.nId==999) && (goFilaSelInter.datosMedicoSolicito.EspeMedSoliOrden.nombre!=false) ){
							loRet.datosMedicoSolicito.Especialidad.cNombre = goFilaSelInter.datosMedicoSolicito.EspeMedSoliOrden.nombre;
							goFilaSelInter.datosMedicoSolicito.Especialidad.nId = goFilaSelInter.datosMedicoSolicito.EspeMedSoliOrden.cod;
						}
							// carga datos en la interfaz
							// si tiene respuestas para avalar y (esta en la interfaz de Avales o es un estudiante ingresando nueva respuesta)
						if(  (goFilaSelInter.datosRtasPorAvalar.UltRtaEstud!='') && (goFilaSelInter.datosRtasPorAvalar.UltAnaEpicEstud!='') && ((lsInterfaz=='aval') ||  (goFilaSelInter.datosUsuarioActual.EsEstudiante==true) ) ){
							$("#txtRespuestaInterconsulta").val(goFilaSelInter.datosRtasPorAvalar.UltRtaEstud);
							$("#txtAnalisisEpicrisis").val(goFilaSelInter.datosRtasPorAvalar.UltAnaEpicEstud);
						}else{
							$("#txtRespuestaInterconsulta").val(loRet.datosInterconsulta.Respuesta);
							$("#txtAnalisisEpicrisis").val(loRet.datosInterconsulta.AnalisisEpi);
						}
						$("#txtEspecialidadSolicitud").html(  (loRet.datosMedicoSolicito.Especialidad.cNombre!=null)? loRet.datosMedicoSolicito.Especialidad.cNombre.toUpperCase():'');
						$("#txtTituloEspecialidadConsultada").html( 'SOLICITUD DE INTERCONSULTA PARA ESPECIALIDAD: '+ goFilaSelInter.DESESP.toUpperCase() );
						$("#txtMedicoSolicitud").html(loRet.datosMedicoSolicito.NombreCompleto.toUpperCase());
						$("#txtSolicitud").val(loRet.datosInterconsulta.Solicitud);
						$("#txtSeguimientos").val(loRet.datosInterconsulta.Seguimientos);
						$("#txtMedicoInterconsultado").html(loRet.datosMedicoRespondio.NombreCompleto.toUpperCase() );
						$("#txtEspecialidadConsultada").html(goFilaSelInter.DESESP.toUpperCase());
						$("#txtProposito").html(loRet.datosInterconsulta.TipoInterc);
						$("#txtPrioridad").html(loRet.datosInterconsulta.TextoPrioridad);

						var bloqueo_seguimiento = 1;
						var bloqueo_respuestas = 1;
						// logica para establecer los bloqueos de edición
						if (goFilaSelInter.ESTORD != 3){

								/// modo solo lectura
								if(goFilaSelInter.datosUsuarioActual.soloLectura){		
									return false;
								}

								// des bloquear ingreso de seguimientos
								if (
									(goFilaSelInter.datosUsuarioActual.EspecialidadMedicoActual != goFilaSelInter.CODORD)
									&&
									(goFilaSelInter.datosUsuarioActual.EsProfesor)
									&& !goFilaSelInter.datosUsuarioActual.tieneOtraEspecialidadPermitida
									)
								{
									bloqueo_seguimiento = 0;
									$('#globalGuardar').val('saveSeguimiento');
								}
								// des bloquear ingreso de respuestas
								if	(
									(goFilaSelInter.datosUsuarioActual.EspecialidadMedicoActual == goFilaSelInter.CODORD)
									||
									(goFilaSelInter.datosUsuarioActual.tieneOtraEspecialidadPermitida)
								)
								{
									$('#globalGuardar').val('saveInterconsulta');
									bloqueo_respuestas = 0;
									oTextoInformativo.consultar(function(){
										if(oTextoInformativo.activa==false){
											$('#btnTextoInf').hide();
										} else {
											oTextoInformativo.mostrar();
											$('#btnTextoInf').on('click', function(){oTextoInformativo.mostrar()});
										}
									});
								}
						}

						if(lbestado){
							interconsultas.BloquearInpusts();
						}else{
							interconsultas.aplicarDesBloqueosEdicion(bloqueo_seguimiento,bloqueo_respuestas);
						}
						
						oModalEspera.ocultar();
					} else {
						fnAlert(loRet.error);
					}
				} catch(err) {
					fnAlert('No se pudo buscar interconsultas del paciente.'+err)
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR);
				fnAlert('Se presentó un error al buscar interconsultas del paciente.');
			});

		} else {
			fnAlert('El paciente no tiene ingreso');
		}
	},

	// validarRespuestasInterConsulta
	validarRespuestasInterConsulta : function (){

		if (goFilaSelInter.ESTORD == 3){
			fnAlert('Esta Solicitud de interconsulta ya fue respondida');
			return false;
		}

		if(goFilaSelInter.datosInterconsulta.TipoInterc=='Traslado' && $('#selAceptaTrasladoPaciente').val() == '' && $('#globalGuardar').val() != 'saveSeguimiento'){
			$("#selAceptaTrasladoPaciente").addClass("is-invalid").removeClass("is-valid");
			interconsultas.ubicarObjeto('#interconsultas', '#selAceptaTrasladoPaciente');
			return false;
		}	
		$("#cCodigoCie").addClass("is-invalid").removeClass("is-valid");
		if( !$('#cCodigoCie').valid() ){
			$("#txtCodigoCie").addClass("is-invalid").removeClass("is-valid");
			$("#cCodigoCie").addClass("is-invalid").removeClass("is-valid");
			interconsultas.ubicarObjeto('#interconsultas', '#cCodigoCie');
			return false;
		}

		if(  !$('#tipoDiagnostico').valid() ){
			$("#tipoDiagnostico").addClass("is-invalid").removeClass("is-valid");
			interconsultas.ubicarObjeto('#interconsultas', '#tipoDiagnostico');
			return false;
		}

		if( !$('#txtRespuestaInterconsulta').valid() ){
			$("#txtRespuestaInterconsulta").addClass("is-invalid").removeClass("is-valid");
			interconsultas.ubicarObjeto('#interconsultas', '#txtRespuestaInterconsulta');
			return false;
		}

		if( !$('#txtAnalisisEpicrisis').valid() ){
			$("#txtAnalisisEpicrisis").addClass("is-invalid").removeClass("is-valid");
			interconsultas.ubicarObjeto('#interconsultas', '#txtAnalisisEpicrisis');
			return false;
		}

		if( $('#txtNuevoSeguimiento').val() =='' ){
			$("#txtNuevoSeguimiento").addClass("is-invalid").removeClass("is-valid");
			interconsultas.ubicarObjeto('#interconsultas', '#txtNuevoSeguimiento');
			return false;
		}

		// solicitud de Confirmación de respuestas

		var lcTitulo = $('#globalGuardar').val()  =='saveInterconsulta' ? 'Confirmación de respuesta y análisis para epicrisis' : 'Confirmación de seguimiento';
		var lcCuerpo = $('#globalGuardar').val()  =='saveInterconsulta' ? '¿La respuesta y análisis para epicrisis \
		de esta interconsulta, no se pueden modificar después de guardadas.<br> Desea guardar?' : '¿Desea Guardar el nuevo seguimiento?';

		fnConfirm(
			lcCuerpo,
			lcTitulo, false, false, false,
			{
				text: 'Si',
					action: function(){
					interconsultas.BloquearInpusts();
					interconsultas.guardarRespuestasInterConsulta();
				}
			},
			{ text: 'No',
				action: function(){}
			}
		);
	},

	guardarRespuestasInterConsulta: function (){
		//oModalEspera.esperaAumentar();
		oModalEspera.mostrar('Espere por favor', 'Guardando');
		interconsultas.lRegresar= true;
		$.ajax({
			type: "POST",
			url: this.gcUrlajax,
			data: {
		
				/**  CYAB 27-09-2023 */
		
				diagpri: $("#cCodigoCie").val(),
				tipoDiag: $('#tipoDiagnostico').val(),		
				tipoConsulta: interconsultas.lTipoConsulta,
				tipoTransaccion: $('#globalGuardar').val(),
				Tidord:   goFilaSelInter.TIDORD ?? aDatosIngreso.cTipId,
		
				/** */
				accion:							'guardarRespuestasSeguimiento',
				DesInt: 						$("#txtRespuestaInterconsulta").val(),
				DesIntEpi: 						$("#txtAnalisisEpicrisis").val(),
				DesIntNuevoSeguimiento: 		$("#txtNuevoSeguimiento").val(),
				AceptaTraslado:					$('#selAceptaTrasladoPaciente').val(),
				TextoPandemia:                  $("#edtxtPandemia").val(),
				  IngInt: 						goFilaSelInter.NINORD,
				  CorInt:  						goFilaSelInter.CCIORD,
				  numCUP:  						goFilaSelInter.CODCUP,
				numIdPac: 						goFilaSelInter.ingreso.nNumId ?? aDatosIngreso.nNumId,
				cSeccion:                       goFilaSelInter.ingreso.cSeccion,
				cHabita:						goFilaSelInter.ingreso.cHabita,
				cCodVia: 						goFilaSelInter.ingreso.cCodVia,
				cPlan: 						    goFilaSelInter.ingreso.cPlan,
				  DesOrd: 						goFilaSelInter.datosInterconsulta.Solicitud,
				  OtcInt:						goFilaSelInter.datosInterconsulta.CodTipoInterc,
				RegMedicoActual:  				goFilaSelInter.datosUsuarioActual.RegMedicoActual,
				EspecialidadMedicoActual:		goFilaSelInter.datosUsuarioActual.EspecialidadMedicoActual,
				NombreEspecialidadMedicoActual:	goFilaSelInter.datosUsuarioActual.NombreEspecialidadMedicoActual,
				  NomCompMedico: 				goFilaSelInter.datosMedicoRespondio.NombreCompleto,
				RegMedicoOrdena:  				goFilaSelInter.datosMedicoSolicito.RegMedico,
				  ConsRtaEst:                   goFilaSelInter.datosRtasPorAvalar.ConsRtaEst,
				interfaz:                       lsInterfaz
			},
			dataType: "json"
		})
		.done(function(loRet) {
			try {

				if($('#globalGuardar').val()  =='saveSeguimiento' ){
					interconsultas.respondeInterconsulta();
					$('#txtNuevoSeguimiento').val('');
					$("#txtNuevoSeguimiento").removeClass("is-valid");
					aDatosIngreso = undefined;
				}else{
		
					fnInformation('Se guardo exitosamente la respuesta y análisis para epicriris', 'Confirmación de guardado');
					setTimeout(function () {
						interconsultas.CargarDatosPDF(loRet);
					}, 2000);
		
				}
			} catch(err) {
				
			}
			oModalEspera.ocultar();
			interconsultas.BloquearInpusts();
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			fnAlert('Se presentó un error al guardar la respuesta a la interconsulta.');
		});
	},

	CargarDatosPDF: function (pDatosRespuesta){

		goFilaSelInter.ESTORD=3;
		if(goFilaSelInter.datosUsuarioActual.EsProfesor==true){
			// datos para Vista Previa y Vista PDF
			gaParamPrevia =	 [{
				'nIngreso' 		: goFilaSelInter.NINORD,
				'nConsecCita' 	: goFilaSelInter.CCIORD,
				'cCUP' 			: goFilaSelInter.CODCUP,
				'cCodVia' 		: goFilaSelInter.ingreso.cCodVia,
				'cRegMedico' 	: goFilaSelInter.datosUsuarioActual.RegMedicoActual,
				'cSecHab' 		: goFilaSelInter.ingreso.cSeccion+' - '+goFilaSelInter.ingreso.cHabita,
				'cTipDocPac' 	: goFilaSelInter.ingreso.cTipId,
				'nNumDocPac' 	: goFilaSelInter.ingreso.nNumId,
				'nConsecEvol' 	: pDatosRespuesta.datos.consecutivo,
				'tFechaHora' 	: pDatosRespuesta.datos.fechahora.date.substring(0,19),
				'cTipoDocum' 	: '1900', 					// const
				'cTipoProgr' 	: 'HIS001',
				'nConsecCons' 	: '0', 						// const
				'nConsecDoc' 	: ''  						// const
				}];
	
				oModalVistaPrevia.mostrar(gaParamPrevia[0], 'RESPUESTA INTERCONSULTA '+gaParamPrevia[0].tFechaHora, 'RTAINTERC');
	
				$('#btnVerPdfHC').attr('disabled',false);
				$('#btnVistaPrevia').attr('disabled',false);
		}
	
	},

	validaSalida: function () {

		if(interconsultas.lRegresar){
			window.location.href='\modulo-historiaclinica&cp=int';
		}

		fnConfirm(
			'¿Se perderá lo que ha escrito. <br> Desea regresar?',
			'Solicitud de confirmación', false, false, false,
			{
				text: 'Si',
					action: function(){
						window.location.href='\modulo-historiaclinica&cp=int';
				}
			},
			{ text: 'No',
					action: function(){
					$('#txtRespuestaInterconsulta').focus();
				}
			}
		);
	},

	ubicarObjeto: function (toForma, tcObjeto, tcTab){
		tcObjeto = typeof tcObjeto === 'string'? tcObjeto: false;
		var loForm = $(toForma);
		if (tcObjeto===false) {
			// setTimeout(function() { // necesario si los tab-pane tienen fade
			// 	var formerrorList = loForm.data('validator').errorList,
			// 		lcObjeto = formerrorList[0].element.id;
			// 	$('#'+lcObjeto).focus();
			// }, (300));
		} else {
			tcTab = typeof tcTab === 'string'? tcTab: false;
			if (!tcTab===false){
				$(tcTab).tab('show');
				setTimeout(function() {
					$(tcObjeto).focus();
				}, (300));
			}else{
				$(tcObjeto).focus();
			}
		}
	}
	
}