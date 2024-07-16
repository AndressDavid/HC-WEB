var esObjeto = {},
	gbGuardado=false,
	gcOrdenMedica='',
	gbConfirm=true,
	goDataImp=null;
oModalEspera.mostrar();

$(function () {
	// Modal de espera de carga de datos
	$("#divEspera").modal('show');
	// Validar los form ocultos
	$.validator.setDefaults({ignore: ""});
	// validacion objeto texarea para que no permita espacios en blanco y tecla enter
	$("textarea").on("focusout",function(e){
		$(this).val( $(this).val().trim() );
	})

	sonObjetos();
	oCabDatosPac.inicializar('HC_MenuTabs');
	oModalPlanesPaciente.inicializar();
	oModalAlertaIntranet.inicializar();
	oAnalisis.inicializar() ;
	oDatosEgreso.inicializar() ;
	oDiagnosticos.inicializar('F');
	oDiagnosticos.buscarDiagnosticos('EPI');
	if (esObjeto['oAmbulatorio']) {
		// Actualiza fecha inicial y final de la incapacidad
		if (typeof aDatosIngreso=='object'){
			oAmbulatorio.ValidarPlan(aDatosIngreso['cPlan']);
			var ldFechaDesde = parseInt(aDatosIngreso['nIngresoFecha']);
			$('#txtFechaDesde').val(strNumAFecha(ldFechaDesde));
			$('#txtFechaHasta').val($('#txtFechaDesde').val())
			$('#txtDiasIncapacidad').val(0);
		}
		oAmbulatorio.inicializar();
		oAmbulatorio.buscarUltimaFormula();
		oModalOrdAmbPDF.inicializar();
	}
	CargarReglas("Reglas","#FormAnalisis", "Analisis");
	CargarReglas("Reglas","#FormEgreso", "Egreso");
	CargarReglas("Reglas","#FormPreguntasmed", "Ambmedi");
	CargarReglas("Reglas","#FormRecomendacion", "Ambrecom");
	CargarReglas("Reglas","#FormIncapacidad", "Ambrehos");
	$('#btnGuardarEPI').on('click', guardarEpi);
	$('#btnVerPdfEPI').on('click', verPdfEPI);
	$('#btnVistaPrevia').on('click', vistaPrevia);
	$('#btnLibroHC').on('click', abrirLibro);

	// Seleccionar objeto en cada tab
	$("#HC_MenuTabs .nav-link").on("shown.bs.tab", function(e){
		$($(this).attr('data-focus')).focus();
	});

});


function CargarReglas(tcTipo, tcForma, tcTitulo ){
	oModalEspera.esperaAumentar();
 	$.ajax({
		type: "POST",
		url: "vista-epicrisis/ajax/Epicrisis.php",
		data: {lcTipo: tcTipo, lcTitulo: tcTitulo},
		dataType: "json"
	})
	.done(function( loObjObl ) {
		try {
			var lopciones={};
				$.each(loObjObl, function( lcKey, loObj ) {
					var llRequiere = true;
					if(loObjObl[lcKey]['REQUIERE'] !==''){
						llRequiere = eval(loObjObl[lcKey]['REQUIERE']);
					}
					if(llRequiere){
						if(loObjObl[lcKey]['CLASE']=="1"){
							lopciones=Object.assign(lopciones,JSON.parse(loObjObl[lcKey]['REGLAS']));

						} else {
							var loTemp = loObjObl[lcKey]['REGLAS'].split(',');
							lopciones[loTemp[0]] = {
								required: function(element){
									return ReglaDependienteValor(loTemp[1],loTemp[2],loObjObl[lcKey]['OBJETO']);
								}
							};
						}
						$('#'+loObjObl[lcKey]['OBJETO']).addClass("required");
					}
				});
				ValidarReglas(tcForma, lopciones);

		}  catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la busqueda de objetos obligatorios para Epicrisis WEB. ');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al buscar objetos obligatorios para Epicrisis WEB. ');
	});
};

function ReglaDependienteValor(tcObjeto, tcValor, tcLabel){
	var lcRetorno = $('#'+tcObjeto).val() == tcValor ;
	if(lcRetorno){
		$('#'+tcLabel).addClass("required");
	}else{
		$('#'+tcLabel).removeClass("required");
	}

	return $('#'+tcObjeto).val() == tcValor;
}

function ValidarReglas(tcForma, aOptions){
	$(tcForma).validate({
		rules: aOptions,
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
	});
	fnHabilitar();
	habilitarPlan();
}

function fnHabilitar() {
	oModalEspera.esperaOcultar(function(){
		$("#btnGuardarEPI").attr("disabled", false);
	});
}

function habilitarPlan() {
	if (esObjeto['oAmbulatorio']) {
		$.ajax({
		type: "POST",
		url: "vista-epicrisis/ajax/Epicrisis.php",
		data: {lcTipo: 'verificaPlanPaciente'},
		dataType: "json"
		})
		.done(function(loDatos) {
			try {
				$('#selPlanPaciente').val(aDatosIngreso.cPlan);

				if (loDatos.datos!=''){
					$("#selPlanPaciente").attr("disabled", true);
				}else{
					$("#selPlanPaciente").attr("disabled", false);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la busqueda plan paciente Epicrisis.');
			}
		});
	}
}

function guardarEpi(e) {
	e.preventDefault();
	if (esObjeto['oAmbulatorio']) {
		oAmbulatorio.fnInformacionNopos(validarGuardar);
	} else {
		validarGuardar();
	}
}

function validarGuardar() {
	if (validarFormas()) {
		fnConfirm('Si guarda los cambios, <b>NO</b> podra modificarlos después.<br><b>¿Está seguro que desea Guardar los datos?</b>', 'EPICRISIS', false, false, false,
			{
				text: 'Si',
				action: function(){
					$('#btnGuardarEPI').attr("disabled", true);
					enviarDatosEPI();
				}
			},
			{
				text: 'No',
			}
		);
	}
}

function validarFormas(){

	if (! $('#FormAnalisis').valid()){
		ubicarObjeto('#FormAnalisis');
		return false;
	}

	if(! oAnalisis.validacion()){
		ubicarObjeto('#FormAnalisis', '#'+oAnalisis.lcObjetoError);
		fnAlert(oAnalisis.lcMensajeError, 'Epicrisis', false, false, false);
		return false;
	}

	if(! oDiagnosticos.validacion()){
		ubicarObjeto('#FormDiagnostico', '#buscarcodigoCie');
		fnAlert(oDiagnosticos.lcMensajeError, 'Epicrisis');
		return false;
	}

	if (! $( '#FormEgreso' ).valid()){
		ubicarObjeto('#FormEgreso');
		return false;
	}

	if(! oDatosEgreso.validacion()){
		ubicarObjeto('#FormEgreso', '#'+oDatosEgreso.lcObjetoError);
		fnAlert(oDatosEgreso.lcMensajeError, 'Epicrisis', false, false, false);
		return false;
	}

	if (esObjeto['oAmbulatorio']) {
		if (!$('#FormIncapacidad').valid()){
			ubicarObjeto('#FormIncapacidad');
			ubicarObjeto('#tabOptOrdInterconsultas', '#txtFechaDesde', '#tabOptOrdInterconsultas');
			return false;
		}
		if (!$('#FormPreguntasmed').valid()){
			ubicarObjeto('#tabOptOrdMedicamento', '#selRealizoFormulacion', '#tabOptOrdMedicamento');
			return false;
		}
		if (!$('#FormRecomendacion').valid()){
			ubicarObjeto('#tabOptOrdRecomendacion', '#txtRecomendacionGeneral', '#tabOptOrdRecomendacion');
			return false;
		}
		if(!oAmbulatorio.validacion()){
			ubicarObjeto('#'+oAmbulatorio.lcFormaError, '#'+oAmbulatorio.lcObjetoError, '#tabOptOrdMedicamento');
			fnAlert(oAmbulatorio.lcMensajeError, 'Epicrisis', false, false, 'medium');
			return false;
		}
	}

	return true;
}


function obtenerDatosFormas(){
	var loEnviar={};
	loEnviar['Ingreso'] = aDatosIngreso['nIngreso'];
	loEnviar['Analisis'] = OrganizarSerializeArray(oAnalisis.obtenerDatos());
 	loEnviar['Diagnostico'] = oDiagnosticos.obtenerDatos();
 	loEnviar['Egreso'] = oDatosEgreso.obtenerDatos();
	if (esObjeto['oAmbulatorio']) {
		loEnviar['Ambulatorio'] = oAmbulatorio.obtenerDatos();
	}
	return loEnviar;
};


function enviarDatosEPI(){
	oModalEspera.mostrar('<b>Se está guardando la Epicrisis</b>');
	var loEnviar = obtenerDatosFormas();
	$.ajax({
		type: "POST",
		url: "vista-epicrisis/ajax/Epicrisis.php",
		data: {lcTipo: 'Verificar', toData: loEnviar},
		dataType: "json",
		success: function (oDataDev) {
			oModalEspera.ocultar();
			var lcError=(typeof oDataDev['error']=='string')?oDataDev['error'].trim():'',
				lcMsgErrGuardar = '<br><br><b>Es probable que la Epicrisis se haya guardado parcialmente<br>Por favor comuníquese con el depto de TI</b>';
			if(lcError==''){
				if(oDataDev['Valido']){
					goDataImp = oDataDev.dataEPI;
					gbGuardado=true;
					deshabilitarEPI();
					if (esObjeto['oAmbulatorio']){
						oModalOrdAmbPDF.oDatosEPI = goDataImp;
						oModalOrdAmbPDF.oDatos = oDataDev.dataOA;
						oModalOrdAmbPDF.habilitarBoton("EPICRISIS");
						oModalOrdAmbPDF.consultar(false, goDataImp.nIngreso, goDataImp.cTipDocPac, goDataImp.nNumDocPac, oDataDev.dataOA.tFechaHora, oDataDev.dataOA.nConsecCita, oDataDev.dataOA.nConsecCons, oDataDev.dataOA.nConsecDoc);

						if (oAmbulatorio.gcTipoMiPres=='S') {
							if (oAmbulatorio.gcDatosNopos != ''){
								$('#txtListadoNopos').val(oAmbulatorio.gcDatosNopos);
								oModalAlertaNopos.mostrar(function(){
									finalizaEpicrisis();
								});
							}else{
								finalizaEpicrisis();
							}
						}else{
							finalizaEpicrisis();
						}
					}else{
						finalizaEpicrisis();
					}
				}else{
					if(oDataDev['Objeto']!=''){
						$('#'+oDataDev['Objeto']).focus();
					}
					if (oDataDev['habGuardar']==true) {
						fnAlert(oDataDev['Mensaje'], 'Epicrisis');
						$('#btnGuardarEPI').attr("disabled", false);
					} else {
						fnAlert(oDataDev['Mensaje']+lcMsgErrGuardar, 'Epicrisis');
					}
				}
			} else {
				oModalEspera.ocultar();
				if((typeof oDataDev['error_sesion']!=='undefined')?oDataDev['error_sesion']:false){
					modalSesionHcWeb();
					$('#btnGuardarEPI').attr("disabled", false);
				} else {
					if (oDataDev['habGuardar']==true) {
						fnAlert(lcError, 'Epicrisis');
						$('#btnGuardarEPI').attr("disabled", false);
					} else {
						fnAlert(lcError+lcMsgErrGuardar, 'Epicrisis');
					}
				}
			}
		},
        error: function (t) {
			oModalEspera.ocultar();
			console.log(t.responseText);
			fnAlert('Error fatal al guardar Epicrisis','Epicrisis');
			$('#btnGuardarEPI').attr("disabled", false);
        }
	});
};

function finalizaEpicrisis(){
	$('#btnVerPdfEPI,#btnVistaPrevia').attr("disabled",false);
	if(aEstados['Estado']==2){
		$.post("vista-epicrisis/ajax/Epicrisis.php", {lcTipo:'rutaruaf'}, function(taRespuesta){
			if (taRespuesta.error.length==0) {
				if (taRespuesta.URL) {
					window.open(taRespuesta.URL, "_blank");
				} else {
					fnAlert('No se recuperó la ruta del RUAF.<br><b>Por favor, ingrese y haga el registro correspondiente.</b>');
				}
			} else {
				fnAlert(taRespuesta.error);
			}
		}, 'json')
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al obtener la ruta del RUAF.<br><b>Por favor, ingrese y haga el registro correspondiente.</b>');
		});
	}
	fnInformation('Epicrisis se ha Guardado', 'Epicrisis', false, false, false);
}

// selecciona el tab que contiene un formulario y luego el objeto indicado
function ubicarObjeto(toForma, tcObjeto, tcTab){
	tcObjeto = typeof tcObjeto === 'string'? tcObjeto: false;
	var loForm = $(toForma);

	// Activar objeto
	activarTab(loForm);
	if (tcObjeto===false) {
		//setTimeout(function() { // necesario si los tab-pane tienen fade
			var formerrorList = loForm.data('validator').errorList,
				lcObjeto = formerrorList[0].element.id;
			$('#'+lcObjeto).focus();
		//}, (300));
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

// selecciona un objeto, activando primero el tab que lo contiene
function focusObjeto(tcIdObjeto){
	if (!(typeof tcIdObjeto === 'string')) return false;
	var loObjeto = $("#"+tcIdObjeto);
	activarTab(loObjeto);
	loObjeto.focus();
}

// activa el tab que contiene un objeto
function activarTab(toObjeto){
	var loTab = toObjeto.closest(".tab-pane");
	$("#"+loTab.attr("aria-labelledby")).tab("show");
}


function sonObjetos(){
	esObjeto['oAmbulatorio'] = typeof oAmbulatorio === 'object';
}

function deshabilitarEPI(){
	// deshabilita controles
	$("#divControlesEPI input,#divControlesEPI select,#divControlesEPI textarea").attr("disabled", true);
	// deshabilita botones en formularios
	$("#AdcionarCie").attr("disabled", true);
	$("#AdicionarMedAmb,#btnMedicamentosAnteriores,#AdicionarProcedimiento,#AdcionarInterconsulta,#AdicionarInsumo").attr("disabled", true);
	oDiagnosticos.gotableDiagnosticos.bootstrapTable('hideColumn', 'ACCIONES');
}

function abrirLibro(){
	formPostTemp('modulo-documentos', {'ingreso':aDatosIngreso['nIngreso']}, true);
}

function verPdfEPI(){
	if (esObjeto['oAmbulatorio']){
		oModalOrdAmbPDF.mostrar();
	} else {
		vistaPreviaPdf({'datos':JSON.stringify([goDataImp])}, null, 'EPICRISIS '+goDataImp.tFechaHora, 'EPICRISIS');
	}
}

function vistaPrevia(){
	oModalVistaPrevia.mostrar(goDataImp, 'EPICRISIS '+goDataImp.tFechaHora, 'EPICRISIS');
}
