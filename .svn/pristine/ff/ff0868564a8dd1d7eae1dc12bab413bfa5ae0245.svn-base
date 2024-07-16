var goMobile = new MobileDetect(window.navigator.userAgent);
	gcUrlAjax = 'vista-documentos/buscar';
	esObjeto = {},
	gbConfirm=true,
	gbGuardado=false,
	goDataImp=null, goDataRecomen=null, goDataNihss=null;
	oModalEspera.mostrar();

$(function () {
	// Validar los form ocultos
	$.validator.setDefaults({ ignore: "" });
	sonObjetos();
	oCabDatosPac.inicializar('EV_MenuTabs');
	oInterconsultaOrdMedica.validaInterconsultasPendientes();
	oDxDescartados.inicializar();

	// validacion objeto texarea para que no permita espacios en blanco y tecla enter
	$("textarea").on("focusout",function(e){
		$(this).val( $(this).val().trim() );
	})

	if (!esObjeto['oEventualidad'] && !esObjeto['oAval']) {
		oTextoInformativo.consultar(function(){
			if(oTextoInformativo.activa==false){
				$('#btnTextoInf').hide();
			}
		});
	}

	if (esObjeto['oActividadFisica']) {oActividadFisica.inicializar('E');}

	if (esObjeto['oDiagnosticos']) {
		oDiagnosticos.inicializar('E');
		if (!esObjeto['oAval']) {
			oDiagnosticos.buscarDiagnosticos('');
		}
	}

	if (esObjeto['oInterpretacion']) {
		oInterpretacion.inicializar();
	}

	if (esObjeto['oConciliacion']) {oConciliacion.inicializar('EVO');}
	if (esObjeto['oNihss']) {oNihss.inicializar();}
	if (esObjeto['oEscalaHasbled']) {oEscalaHasbled.inicializar();}
	if (esObjeto['oEscalaChadsvas']) {oEscalaChadsvas.inicializar();}
	if (esObjeto['oEscalaCrusade']) {oEscalaCrusade.inicializar('EVO');}
	if (esObjeto['oSadPersons']) {oSadPersons.inicializar();}
	if (esObjeto['oAnalisis']) {oAnalisis.inicializar();}
	if (esObjeto['oRegistroEvolucionUci']) {oRegistroEvolucionUci.inicializar();}
	if (esObjeto['oProcedimientoEvolucionUci']) {oProcedimientoEvolucionUci.inicializar(); CargarReglas("Reglas","#FormProcedimientoUci", "Dxproc")}
	if (esObjeto['oRecomendacionesEvolucionUcc']) {oRecomendacionesEvolucionUcc.inicializar();}

	CargarReglas("Reglas","#FormEvolucion", "Evopiso");
	if (esObjeto['oConciliacion']) {
		CargarReglas("Reglas","#FormConcilia1", "Concilia");
		CargarReglas("Reglas","#FormConcilia3", "Concilia");
	}
	if (esObjeto['oAnalisis']) {CargarReglas("Reglas","#FormAnalisis", "Analisis");}
	if (esObjeto['oRegistroEvolucionUci']) {CargarReglas("Reglas","#FormRegistroUnidades", "Regisuci");}
	if (esObjeto['oEventualidad']) {CargarReglas("Reglas","#FormEventualidad", "Eventual");}
	if (esObjeto['oRecomendacionesEvolucionUcc']) {CargarReglas("Reglas","#FormRecomendacionesUCC", "RecomUCC");}

	if (esObjeto['oAval']) {
		oAval.inicializar(aDatosIngreso['TipoEV']);
	}

	$('#btnTextoInf').on('click', verInformativo);
	$('#btnDatosPacienteEV').on('click', ()=>oModalDatosPaciente.consultaDatos(aDatosIngreso));
	$('#btnGuardarEV').on('click', validarEnviar);
	$("#btnGuardarEV").attr("disabled", false);
	$('#btnVolverEV').on('click', retornarPagina);
	$('#btnVerPdfEV').on('click', verPdfEv);
	$('#btnVistaPreviaEV').on('click', ()=>oModalVistaPrevia.mostrar(goDataImp, 'EVOLUCIÓN '+goDataImp.tFechaHora, 'EVOLUCION'));
	$('#btnEvoluciones').on('click', ()=>formPostTemp('modulo-evoconsulta', {'ingreso':aDatosIngreso.nIngreso}, true));
	$('#btnLibroEV').on('click', abrirLibro);
	CargarObjNoVisibles();
	consultaReconocimiento();

	$("#EV_MenuTabs .nav-link").on({
		"shown.bs.tab": function(e){
			$($(this).attr('data-focus')).focus();
		},
		"hide.bs.tab": function(e){
			if(e.target.id=='tabDiagnosticosE' && gbConfirm && !gbGuardado){
				var laDxs = oDiagnosticos.obtenerDatos()
				if(laDxs.length==1){
					$('#buscarcodigoCie').focus();

					fnConfirm('Tenga en cuenta que se ha registrado un solo diagnóstico.<br><b>¿Desea adicionar más diagnósticos?</b>', oDiagnosticos.lcTitulo, false, false, false,
						{
							text: 'Adicionar'
						},
						{
							text: 'Continuar',
							action: function(){
								gbConfirm=false;
								$("#"+e.relatedTarget.id).tab('show');
								gbConfirm=true;
							}
						}
					);
					return false;
				}
			}
		}
	});
});

function CargarObjNoVisibles(){
	$.ajax({
		type: "POST",
		url: "vista-evoluciones/ajax/evoluciones.php",
		data: {lcTipo: 'NoVisible'},
		dataType: "json"
	})
	.done(function(loDatos) {
		loObjNV=loDatos.NOVISIBLES;
		try {
			$.each(loObjNV, function( lcKey, loObj ) {
				var llVisible = true;
				var llRequiere = true;
				if(loObjNV[lcKey]['OBJETOS'] !=='' && loObjNV[lcKey]['CONDICION'] !==''){
					llRequiere = eval(loObjNV[lcKey]['CONDICION']);
				}

				if(llRequiere){
					var loObj = loObjNV[lcKey]['OBJETOS']
					var loTemp = loObjNV[lcKey]['OBJETOS'].split('¤');
					$.each(loTemp, function( lcKey, loObj ) {
						$('#'+loObj).hide();
					});
				}
			});
		} catch(err) {
			alert('No se pudo realizar la busqueda de objetos visibles para EVOLUCIONES WEB. ');
		}
	});
}

function consultaReconocimiento(){
	$("#btnRecordObjetivo,#btnRecordPlanManejo,#btnRecordAnalisisEpicrisis").css("display","none");

 	$.ajax({
		type: "POST",
		url: "vista-evoluciones/ajax/evoluciones.php",
		data: {lcTipo: 'datosreconocimiento'},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error=='') {
				let lnHabilitarReconocimiento=(parseInt(loDatos.TIPOS.habilitar)===2) || (parseInt(loDatos.TIPOS.habilitar)===1 && ($.inArray(aAuditoria['cUsuario'], loDatos.TIPOS.usuarios)>=0))?1:0;
				if (lnHabilitarReconocimiento===1){
					$("#btnRecordObjetivo,#btnRecordPlanManejo,#btnRecordAnalisisEpicrisis").css("display","block");
					$("#btnRecordObjetivo").on("click",function(){ fnReconocimientoVoz($("#btnRecordObjetivo"),$("#edtObjetivo")); })
					$("#btnRecordPlanManejo").on("click",function(){ fnReconocimientoVoz($("#btnRecordPlanManejo"),$("#edtManejo")); })
					$("#btnRecordAnalisisEpicrisis").on("click",function(){ fnReconocimientoVoz($("#btnRecordAnalisisEpicrisis"),$("#edtAnalisis")); })
				}
			} else {
				fnAlert(toDatos.Error);
			}
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la busqueda de consulta Reconocimiento.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al buscar consulta Reconocimiento.');
	});
}

function CargarReglas(tcTipo, tcForma, tcTitulo ){
	oModalEspera.esperaAumentar();
 	$.ajax({
		type: "POST",
		url: "vista-evoluciones/ajax/evoluciones.php",
		data: {lcTipo: tcTipo, lcTitulo: tcTitulo},
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
			alert('No se pudo realizar la busqueda de objetos obligatorios para Evoluciones WEB. ');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		alert('Se presentó un error al buscar objetos obligatorios para Evoluciones WEB. ');
	});
}

function ReglaDependienteValor(tcObjeto, tcValor, tcLabel){

	var loObjeto = $('#'+tcObjeto),
		luValor = loObjeto.prop('type')=='checkbox'? (loObjeto.prop('checked') ? 1: 0): loObjeto.val(),
		lbRetorno = luValor == tcValor;
	if(lbRetorno){
		$('#'+tcLabel).addClass("required");
	}else{
		$('#'+tcLabel).removeClass("required");
	}
	return lbRetorno;
}

function ValidarReglas(tcForma, aOptions){
	$( tcForma ).validate( {
		rules: aOptions,
		errorElement: "div",
		errorPlacement: function ( error, element ) {
			// Agregue la clase `help-block` al elemento de error
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
	} );
	fnHabilitar();
}

function fnHabilitar() {
	oModalEspera.esperaOcultar(function(){
		$("#btnGuardarHC").attr("disabled", false);
		if (!esObjeto['oEventualidad']) { verInformativo();} else {oModalEspera.ocultar();};
		if (!esObjeto['oAval']) {
			if (esObjeto['oAnalisis']) {oAnalisis.iniciaPlanManejo();}
		}
	});
}

function validarEnviar(e){
	e.preventDefault();

	if (validarFormas()) {

		fnConfirm('¿Si Guarda los Cambios, NO podra modificarlos después. Esta Seguro que desea Guardar los Datos?', 'EVOLUCIONES', false, false, false,
		{
			text: 'Si',
			action: function(){
				enviarDatosEVO();
			}

		},
		{
			text: 'No'
		}
		)

	}

}

function validarFormas(){

	if (esObjeto['oDiagnosticos']) {
		if(! oDiagnosticos.validacion()){
			ubicarObjeto('#FormDiagnostico', '#buscarcodigoCie');
			fnAlert(oDiagnosticos.lcMensajeError, 'Evoluciones');
			return false;
		}
	}

	if (esObjeto['oEvolucion']) {
		if (! $('#FormEvolucion').valid()){
			ubicarObjeto('#FormEvolucion');
			return false;
		}
	}

	if (esObjeto['oRegistroEvolucionUci']) {
		if (! $('#FormRegistroUnidades').valid()){
			ubicarObjeto('#FormRegistroUnidades');
			return false;
		}
	}

	var lcConductaSalida = ''
	var lcEstadoSalida = ''
	if (esObjeto['oAnalisis']) {
		if (! $('#FormAnalisis').valid()){
			ubicarObjeto('#FormAnalisis');
			return false;
		}
		if(! oAnalisis.validacion()){
			ubicarObjeto('#FormAnalisis', oAnalisis.lcObjetoError);
			fnAlert(oAnalisis.lcMensajeError, 'Evolución');
			return false;
		}

		lcConductaSalida = $("#selConductaSeguir").val();
		lcEstadoSalida = $('#selEstadoSalida').val();
	}

	if (esObjeto['oConciliacion']) {
		if (! $('#FormConcilia1').valid()){
			ubicarObjeto('#FormConcilia1');
			return false;
		}
		if (! $('#FormConcilia3').valid()){
			ubicarObjeto('#FormConcilia3');
			return false;
		}
		if(! oConciliacion.validacion()){
			ubicarObjeto('#FormConcilia', '#btnAdicionarM');
			fnAlert(oConciliacion.lcMensajeError, 'Evolución');
			return false;
		}
	}

	if (esObjeto['oProcedimientoEvolucionUci']) {
		if (! $('#FormProcedimientoUci').valid()){
			ubicarObjeto('#FormProcedimientoUci');
			return false;
		}
		if(! oProcedimientoEvolucionUci.validacion()){
			ubicarObjeto('#FormProcedimientoUci', oProcedimientoEvolucionUci.lcObjetoError);
			fnAlert(oProcedimientoEvolucionUci.lcMensajeError, 'Evolución');
			return false;
		}
	}

	if (esObjeto['oInterpretacion']) {
		if(!oInterpretacion.validacion()){
			ubicarObjeto('#FormInterpreta', '#tblInterpretacion');
			fnAlert(oInterpretacion.lcMensajeError, 'Interpretación de Exámenes');
			return false;
		}
	}

	if (esObjeto['oEscalaHasbled']) {
		if(!oEscalaHasbled.validacion()){
			ubicarObjeto('#escalaHasbled','#'+oEscalaHasbled.cObjetoError);
			fnAlert(oEscalaHasbled.cMensajeError, 'Puntaje Hasbled');
			return false;
		}
	}

	if (esObjeto['oEscalaChadsvas']) {
		if(!oEscalaChadsvas.validacion()){
			ubicarObjeto('#escalaChadsvas','#'+oEscalaChadsvas.cObjetoError);
			fnAlert(oEscalaChadsvas.cMensajeError, 'Puntaje Chadsvas');
			return false;
		}
	}

	if (esObjeto['oEscalaCrusade']) {
		if(!oEscalaCrusade.validacion()){
			ubicarObjeto('#escalaCrusade','#'+oEscalaCrusade.cObjetoError);
			fnAlert(oEscalaCrusade.cMensajeError, 'Puntaje Crusade');
			return false;
		}
	}

	if (esObjeto['oNihss']) {
		if(! oNihss.validacion()){
			ubicarObjeto('#FormNihss', '#'+oNihss.lcObjetoError);
			fnAlert(oNihss.lcMensajeError, 'Evolución');
			return false;
		}
	}

	if (esObjeto['oRecomendacionesEvolucionUcc']) {
		if (lcConductaSalida == '01' &&  lcEstadoSalida =='001'){
			if (! $('#FormRecomendacionesUCC').valid()){
				ubicarObjeto('#FormRecomendacionesUCC');
				return false;
			}

			if(! oRecomendacionesEvolucionUcc.validacion()){
				activarTab($("#headerProcedimientoUci"));
				ubicarObjeto(oRecomendacionesEvolucionUcc.lcFormaUcc, oRecomendacionesEvolucionUcc.lcObjetoError);
				fnAlert(oRecomendacionesEvolucionUcc.lcMensajeError, 'Evolución');
				return false;
			}
		}
	}

	if (esObjeto['oEventualidad']) {
		if (! $('#FormEventualidad').valid()){
			ubicarObjeto('#FormEventualidad');
			return false;
		}
	} else {
		if (oActividadFisica.gcRespuestaAF===''){
			if (! oActividadFisica.validacion()){
				ubicarObjeto('#FormActividadFisica', '#selRealizaActividad');
				return false;
			}
		}
	}
	if (esObjeto['oSadPersons']) {
		if(!oSadPersons.validacion()){
			ubicarObjeto('#FormSadPersons','#'+oSadPersons.lcObjetoError);
			fnAlert(oSadPersons.lcMensajeError, 'Puntaje Sad persons');
			return false;
		}
	}

	return true;
};

function obtenerDatosFormas(){

	var loEnviar = {
		'Ingreso': aDatosIngreso['nIngreso'],
		'Tipo': aDatosIngreso['TipoEV'],
		'nConCons': aDatosIngreso['nConCons'],
		'Seccion': aDatosIngreso['cSeccion'],
		'ctxtPandemia': oTextoInformativo.obtenerDatos(),
	};

	if (!esObjeto['oEventualidad']) {
		loEnviar['Actividadfisica']=oActividadFisica.gcRespuestaAF=='' ? oActividadFisica.obtenerDatos() : '';
	}

	if (esObjeto['oInterpretacion']) {
		loEnviar['Interpretacion'] = oInterpretacion.obtenerDatos();
	}

	if (esObjeto['oDiagnosticos']) {
		loEnviar['Diagnostico'] = oDiagnosticos.obtenerDatos();
	}

	if (esObjeto['oEvolucion']) {
		loEnviar['EvolucionP'] = OrganizarSerializeArray(oEvolucion.obtenerDatos());
	}

	if (esObjeto['oConciliacion']) {
		loEnviar['Conciliacion'] = oConciliacion.obtenerDatos();
	}

	if (esObjeto['oEscalaHasbled']) {
		if(oEscalaHasbled.bLlenar){
			loEnviar['escalaHasbled'] = oEscalaHasbled.obtenerDatos();
		}
	}

	if (esObjeto['oEscalaChadsvas']) {
		if(oEscalaChadsvas.bLlenar){
			loEnviar['escalaChadsvas'] = oEscalaChadsvas.obtenerDatos();
		}
	}
	if (esObjeto['oEscalaCrusade']) {
		if(oEscalaCrusade.bLlenar){
			loEnviar['escalaCrusade'] = oEscalaCrusade.obtenerDatos();
		}
	}

	if (esObjeto['oNihss']) {
		loEnviar['Nihss'] = OrganizarSerializeArray(oNihss.obtenerDatos());
	}

	if (esObjeto['oRegistroEvolucionUci']) {
		loEnviar['RegistroUci'] = oRegistroEvolucionUci.obtenerDatos();
	}

	if (esObjeto['oProcedimientoEvolucionUci']) {
		loEnviar['ProcedimientosUci'] = oProcedimientoEvolucionUci.obtenerDatos();
	}

	if (esObjeto['oAnalisis']) {
		loEnviar['Analisis'] = oAnalisis.obtenerDatos();
	}

	if (esObjeto['oRecomendacionesEvolucionUcc']) {
		loEnviar['RecomendacionesUCC'] = oRecomendacionesEvolucionUcc.obtenerDatos();
	}

	if (esObjeto['oEventualidad']) {
		loEnviar['Eventualidad'] = oEventualidad.obtenerDatos();
	}
	if (esObjeto['oSadPersons']) {
		if(oSadPersons.bLlenarSadPerson){
			loEnviar['DatosSadPersons'] = oSadPersons.obtenerDatos();
		}
	}

	if (esObjeto['oAval']) {loEnviar['PorAvalar'] = 'Si';}
	return loEnviar;
};

function enviarDatosEVO(){
	oModalEspera.mostrar('<b>Se está guardando la Evolucion</b>');
	var loEnviar = JSON.stringify(obtenerDatosFormas());
	$.ajax({
		type: "POST",
		url: "vista-evoluciones/ajax/evoluciones.php",
		data: {lcTipo: 'Verificar', datos: loEnviar},
		dataType: "json"
	})
	.done(function(oDataDev) {
		oModalEspera.ocultar();
		if(oDataDev['Valido']){
			if (aAuditoria['lRequiereAval']==true){
				fnInformation('Evolución para avalar se ha Guardado', 'Evoluciones', false, false, false, function(){
					window.history.back();
				});
			}else{
				goDataImp = oDataDev.dataEV.dataEV;
				if (typeof oDataDev.dataEV.dataNihss !== 'undefined') {
					goDataNihss = oDataDev.dataEV.dataNihss;
				}
				if (typeof oDataDev.dataEV.dataRecom !== 'undefined') {
					goDataRecomen = oDataDev.dataEV.dataRecom;
				}
				gbGuardado=true;
				deshabilitarEV();
				finalizaEV();
			}
		} else {
			if(oDataDev['Objeto']!=''){
				var loTemp = oDataDev['Objeto'].split('-');
				if (loTemp[0]=='selConductaSeguirAct'){

					oDataDev['Objeto']='selConductaSeguir';
					aDatosIngreso['cSeccion'] = (loTemp[1]==''?aDatosIngreso['cSeccion']:loTemp[1]);

					// Inicia datos para cargar opciones de conducta a seguir
					var lcConducta = 'EVPISO',
					lcTipo = '';
					lcTipo = typeof aDatosIngreso['TipoEV'] === 'string'? aDatosIngreso['TipoEV']: '';
					lcSeccion = aDatosIngreso['cSeccion'];

					if(lcTipo=='C' || lcTipo=='V' ||  (lcTipo=='P' && $.inArray(lcSeccion, ['CC','CV','CI','CA'])>=0)){
						lcConducta = 'EVUNID';
					}
					$('#selConductaSeguir').html('').conductaSeguir(lcConducta);
				}

				focusObjeto(oDataDev['Objeto']);
			}
			fnAlert(oDataDev['Mensaje'], 'Evoluciones');
			$('#btnGuardarEV').attr("disabled", false);
		}

	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Ocurrió un error al guardar la Evolución', 'Evoluciones');
	});
}

function deshabilitarEV(){
	$("#btnGuardarEV,#btnTextoInf,#AdcionarCie,#btnAdicionarM,#btnEuroscore,#adicinaGrupoMedicamento,#adicionarActividad").attr("disabled", true);
	$("#divControlesEV input,#divControlesEV select,#divControlesEV textarea,#divTextInformativoModal textarea").attr("disabled", true);
	if (!esObjeto['oEventualidad']) {
		oActividadFisica.gotableActividadHC.bootstrapTable('hideColumn', 'ACCION');
	}
}

// selecciona el tab que contiene un formulario y luego el objeto indicado
function ubicarObjeto(toForma, tcObjeto){
	tcObjeto = typeof tcObjeto === 'string'? tcObjeto: false;
	var loForm = $(toForma);

	// Activar objeto
	activarTab(loForm);
	if (tcObjeto===false) {
		var formerrorList = loForm.data('validator').errorList,
		lcObjeto = formerrorList[0].element.id;
		$('#'+lcObjeto).focus();

	} else {
		$(tcObjeto).focus();
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

function verInformativo(){
	oTextoInformativo.mostrar();
}

function finalizaEV(){

	$('#btnVerPdfEV').attr("disabled",false);
	$('#btnVistaPreviaEV').attr("disabled",false);

	if (esObjeto['oDiagnosticos']) {
		oDiagnosticos.gotableDiagnosticos.bootstrapTable('hideColumn', 'ACCIONES');
	}

	if (aAuditoria['lRequiereAval']==true){
		fnInformation('Evolución para avalar se ha Guardado', 'Evoluciones', false, false, false, function(){
			window.history.back();
		});
	} else {
		//Si es salida enviar a Epicrisis
		if ($("#selConductaSeguir").val()=='01') {
			verPdfEv(function(){
				var loDatosEnviar={cp:'epi', ingreso:aDatosIngreso['nIngreso']};
				setTimeout(function(){
					formPostTemp('modulo-historiaclinica', loDatosEnviar, false);
				}, 500);
			});
		}
		else {
			verPdfEv();
			fnInformation('Evolución se ha guardado', 'Evolución');
		}
	}
}

// Valida los objetos que se cargan condicionalmente
function sonObjetos(){
	esObjeto['oEvolucion'] = typeof oEvolucion === 'object';
	esObjeto['oConciliacion'] = typeof oConciliacion === 'object';
	esObjeto['oDiagnosticos'] = typeof oDiagnosticos === 'object';
	esObjeto['oNihss'] = typeof oNihss === 'object';
	esObjeto['oAnalisis'] = typeof oAnalisis === 'object';
	esObjeto['oEventualidad'] = typeof oEventualidad === 'object';
	esObjeto['oRegistroEvolucionUci'] = typeof oRegistroEvolucionUci === 'object';
	esObjeto['oProcedimientoEvolucionUci'] = typeof oProcedimientoEvolucionUci === 'object';
	esObjeto['oRecomendacionesEvolucionUcc'] = typeof oRecomendacionesEvolucionUcc === 'object';
	esObjeto['oEscalaHasbled'] = typeof oEscalaHasbled === 'object';
	esObjeto['oEscalaChadsvas'] = typeof oEscalaChadsvas === 'object';
	esObjeto['oEscalaCrusade'] = typeof oEscalaCrusade === 'object';
	esObjeto['oSadPersons'] = typeof oSadPersons === 'object';
	esObjeto['oInterpretacion'] = typeof oInterpretacion === 'object';
	esObjeto['oActividadFisica'] = typeof oActividadFisica === 'object';
	esObjeto['oAval'] = (typeof oAval === 'object' && aAuditoria.lRequiereAval==false);
}

function retornarPagina(){
	if (gbGuardado) {
		window.history.back();
		return;
	}
	fnConfirm('¡Perderá los cambios realizados!<br><b>¿Está seguro que desea volver?</b>', 'EVOLUCION', false, false, false,
		{
			text: 'Si',
			action: function(){
				window.history.back();
			}
		},
		{ text: 'No' }
	)
}

function verPdfEv(tfPostFunction){
	var laEnviar = [goDataImp];
	if (!(goDataNihss == null)) laEnviar.push(goDataNihss);
	if (!(goDataRecomen == null)) laEnviar.push(goDataRecomen);
	vistaPreviaPdf({'datos':JSON.stringify(laEnviar)}, tfPostFunction, 'EVOLUCIÓN '+goDataImp.tFechaHora, 'EVOLUCION');
}
