var goDataImp=null;
	gbGuardado=false,
	gcOrdenMedica='',
	oModalEspera.mostrar();

 $(function () {
	$.validator.setDefaults({ ignore: "" });
	oAmbulatorio.inicializar();
	oCabDatosPac.inicializar();
	oModalAlertaIntranet.inicializar();
	oModalPlanesPaciente.inicializar();
	oDiagnosticos.consultarDiagnostico('txtcodigoCieOrdAmb','cCodigoCieOrdAmb','cDescripcionCieOrdAmb','','selViaOrdAmb');

	servicioMicroMedex.obtenerListaDiagnosticos(aDatosIngreso.nIngreso);

	$("textarea").on("focusout",function(e){
		$(this).val( $(this).val().trim() );
	})

	oModalOrdAmbPDF.inicializar();
	gcUrlajax = "vista-ordenes-ambulatorias/ajax/ajax",
	consultaViasIngreso($('#selViaOrdAmb'));
	CargarReglas("Reglas","#FormPreguntasmed","Ambmedi");
	CargarReglas("Reglas","#FormRecomendacion","Ambrecom");
	CargarReglas("Reglas","#FormOrdAmbulatoriaPac","AmbPrinc");
	CargarReglas("Reglas","#Formmedicamentos","AmbMedic");

	$("#selPlanPaciente,#selPrioridadAtencion,#seltipoPrioridad").attr("disabled", true);
	$('#selPrioridadAtencionOrdAmb').on('change', function(){
		$('#selPrioridadAtencion').val($('#selPrioridadAtencionOrdAmb').val());
		$('#seltipoPrioridad').val($('#selPrioridadAtencionOrdAmb').val());
	});

	$('#FormInterconsulta').validate({
		rules: {
			buscarInterconsulta: "required",
		},
		errorElement: "div",
		errorPlacement: function ( error, element ) {
			error.addClass( "invalid-tooltip" );

			if (element.prop("type")==="radio") {
				error.insertAfter(element.parent("label"));
			} else {
				error.insertAfter(element);
			}
		},
		highlight: function (element, errorClass, validClass) {
			$(element).addClass("is-invalid").removeClass("is-valid");
		},
		unhighlight: function (element, errorClass, validClass) {
			$(element).addClass("is-valid").removeClass("is-invalid");
		}
	});
	$('#FormOrdAmbulatoriaPac').on('submit', function(e){e.preventDefault();});
	$('#btnGuardarOrdenesAmb').on('click', validarEnviar);
	$('#btnVolverAmb').on('click', retornarPagina);
	$('#btnVerPdfAmb').on('click', verPdfAmb);
	$('#btnVistaPreviaAmb').on('click', vistaPreviaAmb);
})


function CargarReglas(tcTipo, tcForma, tcTitulo ){
	oModalEspera.esperaAumentar();
 	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: tcTipo, lcTitulo: tcTitulo},
		dataType: "json"
	})
	.done(function(loDatos) {
		loObjObl=loDatos.REGLAS;
		try {
			var lopciones={};
				$.each(loObjObl, function( lcKey, loObj ) {
					var llRequiere = true;

					if(loObjObl[lcKey]['REQUIERE'] !==''){
						llRequiere = eval(loObjObl[lcKey]['REQUIERE']);
					}

					if(llRequiere){
						if(loObjObl[lcKey]['CLASE']=="1" || loObjObl[lcKey]['CLASE']=="3" ){
							lopciones=Object.assign(lopciones,JSON.parse(loObjObl[lcKey]['REGLAS']));
						} else {
							var loTemp = loObjObl[lcKey]['REGLAS'].split('¤');
							lopciones[loTemp[0]]={required: function(element){
								return ReglaDependienteValor(loTemp[1],loTemp[2],loDatos.REGLAS[lcKey]['OBJETO']);
							}};
							if(loTemp.length==4){
								lopciones[loTemp[0]]=Object.assign(lopciones[loTemp[0]],JSON.parse(loTemp[3]));
							}
						}
						if(loObjObl[lcKey]['CLASE']=="1" || loObjObl[lcKey]['CLASE']=="2" ){
							$('#'+loObjObl[lcKey]['OBJETO']).addClass("required");
						}
					}
				});
				ValidarReglas(tcForma, lopciones);

		} catch(err) {
			alert('No se pudo realizar la busqueda de objetos obligatorios para ordenes ambulatorias WEB.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		alert('Se presentó un error al buscar objetos obligatorios para HC WEB. ');
	});
}

function ValidarReglas(tcForma, aOptions){
	$( tcForma ).validate({
		rules: aOptions,
		errorElement: "div",
		errorPlacement: function(error, element) {
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
	});
	fnHabilitar();
}

function fnHabilitar() {
	oModalEspera.esperaOcultar(function(){
		$("#btnGuardarOrdenesAmb").attr("disabled", false);
		oModalPlanesPaciente.mostrar();
	});
}


function consultaViasIngreso(id) {
	var loSelect = id;
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: { accion:'listaVias', lcTitulo: ''},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				$.each(loDatos.datos, function( lcKey, loTipo ) {
					loSelect.append('<option value="' + loTipo.CODVIA + '">' + loTipo.DESVIA.trim() + '</option>');
				});
				$('#selViaOrdAmb').val(aDatosIngreso.cCodigoVia);
			} else {
				alert(loDatos.error + ' ', "warning");
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consulta de vias de ingreso.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar vias de ingreso.');
	});
}


function validarEnviar(e){
	e.preventDefault();
	if (validarOrdenesAmb()) {
		llamarAlertasMedicamentos(function(){
			fnConfirm('Si guarda los cambios <b>NO</b> podrá modificarlos después.<br><b>¿Está seguro que desea Guardar los datos?</b>', 'ORDENES AMBULATORIAS', false, false, 'medium',
				{
					text: 'Si',
					action: function(){
						$('#btnGuardarOrdenesAmb').attr("disabled", true);
						enviarDatosOA();
					}
				},
				{
					text: 'No'
				}
			)
		});
	}
}

function validarOrdenesAmb(){
	if(!validarDatosOrdenAmb()){
		ubicarObjeto('#FormOrdAmbulatoriaPac', '#txtcodigoCieOrdAmb');
		fnAlert('No se han registrado ordenes ambulatorias, revise por favor.', 'Ordenes Ambulatorias.', false, false, 'medium');
		return false;
	}
	if (!$('#FormOrdAmbulatoriaPac').valid()){
		ubicarObjeto('#FormOrdAmbulatoriaPac', '#txtcodigoCieOrdAmb');
		return false;
	}
	if (!$('#FormPreguntasmed').valid()){
		ubicarObjeto('#Formmedicamentos', '#selRealizoFormulacion', '#tabOptOrdMedicamento');
		return false;
	}
	if (!$('#FormRecomendacion').valid()){
		ubicarObjeto('#FormRecomendacion', '#txtRecomendacionGeneral', '#tabOptOrdRecomendacion');
		return false;
	}
	if ($("#cCodigoCieOrdAmbR").val().length > 0) {
		if ($("#cCodigoCieOrdAmb").val() == $("#cCodigoCieOrdAmbR").val()) {
			ubicarObjeto('#FormIncapacidad', '#txtCodigoCieOrdAmbR', '#tabOptOrdIncapacidad');
			fnAlert('El Diagnóstico Relacionado no puede ser igual al Diagnóstico Principal', 'Ordenes Ambulatorias.', false, false, 'medium');
			return false;
		}
	}
	if(!oAmbulatorio.validacion()){
		ubicarObjeto('#'+oAmbulatorio.lcFormaError, '#'+oAmbulatorio.lcObjetoError, '#'+oAmbulatorio.cTabError);
		fnAlert(oAmbulatorio.lcMensajeError, 'Ordenes Ambulatorias.', false, false, 'medium');
		return false;
	}
	return true;
}

function validarDatosOrdenAmb(){
	let lnTotalRegistros = 0
		+ ($('#tblMedicaAmb').bootstrapTable('getData').length>0 ? 1 : 0)
		+ ($('#tblProcedimiento').bootstrapTable('getData').length>0 ? 1 : 0)
		+ ($('#tblInterconsulta').bootstrapTable('getData').length>0 ? 1 : 0)
		+ ($("#idObservacionesInsumos").val()!='' ? 1 : 0)
		+ ($("#seltipoDieta").val()!='' ? 1 : 0)
		+ ($("#selTipoIncapacidad").val().length>0 ? 1 : 0)
		+ ($("#txtRecomendacionNutricional").val()!='' ? 1 : 0)
		+ ($("#txtRecomendacionGeneral").val()!='' ? 1 : 0)
		+ ($("#txtObservacionesOtras").val()!='' ? 1 : 0);
	return lnTotalRegistros>0;
}

function obtenerDatosFormas() {
	var loEnviar = {
		'Ingreso': aDatosIngreso['nIngreso'],
		'Diagnostico': $('#cCodigoCieOrdAmb').val(),
	};
	loEnviar['Ambulatorio'] = oAmbulatorio.obtenerDatos();
	return loEnviar;
}

function enviarDatosOA(){
	oModalEspera.mostrar('<b>Se está guardando órdenes ambulatorias</b>');
	var loData = obtenerDatosFormas();
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: 'Verificar', datos: loData},
		dataType: "json"
	})
	.done(function(oDatosOA) {
		oModalEspera.ocultar();
		if(oDatosOA['Valido']){
			goDataImp = oDatosOA.dataOA;
			oModalOrdAmbPDF.oDatos = goDataImp;
			oModalOrdAmbPDF.consultar(false, goDataImp.nIngreso, goDataImp.cTipDocPac, goDataImp.nNumDocPac, goDataImp.tFechaHora, goDataImp.nConsecCita, goDataImp.nConsecCons, goDataImp.nConsecDoc);
			gbGuardado=true;
			deshabilitarOA();

			if (oAmbulatorio.gcTipoMiPres=='S') {
				oAmbulatorio.fnInformacionNopos(function(){
					if (oAmbulatorio.gcDatosNopos!='') {
						$('#txtListadoNopos').val(oAmbulatorio.gcDatosNopos);
						oModalAlertaNopos.mostrar(function(){
							terminaOrdenAmb();
						});
					} else {
						terminaOrdenAmb();
					}
				});
			} else {
				terminaOrdenAmb();
			}

		} else {
			if(oDatosOA['Objeto']!=''){
				focusObjeto(oDatosOA['Objeto']);
			}
			fnAlert(oDatosOA['Mensaje'], 'Ordenes Ambulatorias.');
			$('#btnGuardarOrdenesAmb').attr("disabled", false);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		oModalEspera.ocultar();
		console.log(jqXHR.responseText);
		fnAlert('Ocurrió un error al guardar las Ordenes ambulatorias', 'Ordenes Ambulatorias..');
		$('#btnGuardarOrdenesAmb').attr("disabled", false);
	});
}

function ubicarObjeto(toForma, tcObjeto, tcTab){
	tcObjeto = typeof tcObjeto === 'string'? tcObjeto: false;
	var loForm = $(toForma);

	activarTab(loForm);
	if (tcObjeto===false) {
		var formerrorList = loForm.data('validator').errorList,
			lcObjeto = formerrorList[0].element.id;
		$('#'+lcObjeto).focus();
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

function activarTab(toObjeto){
	var loTab = toObjeto.closest(".tab-pane");
	$("#"+loTab.attr("aria-labelledby")).tab("show");
}

function focusObjeto(tcIdObjeto){
	if (!(typeof tcIdObjeto === 'string')) return false;
	var loObjeto = $("#"+tcIdObjeto);
	activarTab(loObjeto);
	loObjeto.focus();
}

function deshabilitarOA(){
	$("#divControlesOA input,#divControlesOA select,#divControlesOA textarea,#divTextInformativoModal textarea").attr("disabled", true);
	$("#txtcodigoCieOrdAmb,#selPlanOrdAmb,#selViaOrdAmb,#selPrioridadAtencionOrdAmb,#seleccionarProcedimiento").attr("disabled", true);
	$("#AdicionarMedAmb,#btnMedicamentosAnteriores,#AdicionarProcedimiento,#AdcionarInterconsulta,#AdicionarInsumo,#eliminarProcedimientos,#btnEliminarMedicamentosAmb").attr("disabled", true);
	oAmbulatorio.gotableMedicamentos.bootstrapTable('hideColumn', 'SELECCION');
	oAmbulatorio.gotableMedicamentos.bootstrapTable('hideColumn', 'ACCIONES');
	oAmbulatorio.gotableInterconsultas.bootstrapTable('hideColumn', 'BORRAR');
	oProcedimientos.gotableProcedimientos.bootstrapTable('hideColumn', 'SELECCION');
	oProcedimientos.gotableProcedimientos.bootstrapTable('hideColumn', 'ACCIONES');
}

function terminaOrdenAmb(){
	$('#btnVerPdfAmb').attr("disabled",false);
	$('#btnVistaPreviaAmb').attr("disabled",false);
	fnInformation('Orden ambulatoria se ha guardado', 'Orden Ambulatoria');
}

function retornarPagina(){
	if (gbGuardado) {
		window.history.back();
		return;
	}
	fnConfirm('¡Perderá los cambios realizados!<br><b>¿Está seguro que desea volver?</b>', 'ORDENES AMBULATORIAS', false, false, false,
		{
			text: 'Si',
			action: function(){
				window.history.back();
			}
		},
		{ text: 'No' }
	)
}

function verPdfAmb(){
	oModalOrdAmbPDF.mostrar();
}

function vistaPreviaAmb(){
	oModalVistaPrevia.mostrar(goDataImp, 'ORDENES AMBULATORIAS '+goDataImp.tFechaHora, 'ORDAMB');
}



// FUNCIONES PARA MICROMEDEX

function llamarAlertasMedicamentos(tfFuncionPost){
	var loMed = generarListaMedicamentosParaAlertas();
	if (loMed.medicamentos.nuevoscuenta+loMed.medicamentos.actualescuenta>0) {
		servicioMicroMedex.mostrarAlertasMedicamentos(loMed, tfFuncionPost);
	} else {
		tfFuncionPost();
	}
}

function generarListaMedicamentosParaAlertas()
{
	var loMedNuevos = {},
		loMedicamentos = $('#tblMedicaAmb').bootstrapTable('getData'),
		lnCuentaMedNuevos = 0;
	$.each(loMedicamentos, function(tnClaveMed, loMedicamento){
		if (!(loMedicamento.CODIGO.substring(0,2)=='NC')) {
			loMedNuevos[loMedicamento.CODIGO] = loMedicamento.MEDICA+'|'+loMedicamento.DOSIS+' '+loMedicamento.TIPOD+' cada '+loMedicamento.FRECUENCIA+' '+loMedicamento.TIPOF+' Durante '+loMedicamento.TIEMPOTRATA+' '+loMedicamento.TIPOCODTIEMTRAT+' - Vía '+loMedicamento.VIA;
			lnCuentaMedNuevos++;
		}
	});
	var lcDxPrincipal = $('#cCodigoCieOrdAmb').val();
	if (lcDxPrincipal.length>0 && $.inArray(lcDxPrincipal, servicioMicroMedex.aListaDxPaciente)<0) {
		servicioMicroMedex.aListaDxPaciente.push(lcDxPrincipal);
	}

	var loEnviar = {
		paciente: {
			ingreso: aDatosIngreso.nIngreso,
			//	fuma: false,
			//	embarazo: false,
			//	lactancia: false
		},
		medicamentos: {
			nuevos: loMedNuevos,
			actuales: {},
			nuevoscuenta: lnCuentaMedNuevos,
			actualescuenta: 0
		},
		alergenos: [],
		diagnosticos: servicioMicroMedex.aListaDxPaciente,
	}

	return loEnviar;
}
