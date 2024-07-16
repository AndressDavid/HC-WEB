oModalEspera.mostrar();
var esObjeto = {},
	gcTriageHtml='',
	gcOrdenMedica='',
	gbConfirm=true,
	gbGuardado=false,
	gaViasCensoAutorizadas='',
	gaSeccionesHdia='',
	goDataImp=null,
	goDataNihss=null;

$(function() {
	// Validar los form ocultos
	$.validator.setDefaults({ ignore: "" });

	sonObjetos();
	oCabDatosPac.inicializar('HC_MenuTabs');
	oModalPlanesPaciente.inicializar();
	oModalAlertaIntranet.inicializar();
	oSadPersons.inicializar();
	oActividadFisica.inicializar('');

	if (!esObjeto['oAval']) {
		oTextoInformativo.consultar(function(){
			if(oTextoInformativo.activa==false){
				$('#btnTextoInf').hide();
			}
		});
	}

	// validacion objeto texarea para que no permita espacios en blanco y tecla enter
	$("textarea").on("focusout",function(e){
		$(this).val( $(this).val().trim() );
	})

	oMotivo.inicializar();
	oConciliacion.inicializar();
	oAntecedentes.inicializar();
	oExamenFisico.inicializar();
	oDiagnosticos.inicializar('H');
	oPlanManejo.inicializar();
	oAntecedentesConsulta.inicializar();
	CargarObjNoVisibles();
	listaViasCenso();
	listaSeccionesHd();

	setTimeout(function(){
		oNihss.inicializar();
		if (esObjeto['oAmbulatorio']) {
			if (typeof aDatosIngreso=='object'){
				oAmbulatorio.ValidarPlan(aDatosIngreso['cPlan'], function() {
					oAmbulatorio.cargarDatosConcialicionIngreso();
				});
			}
			oAmbulatorio.inicializar();
		}
		if (esObjeto['oEscalaHasbled']) {oEscalaHasbled.inicializar();}
		if (esObjeto['oEscalaChadsvas']) {oEscalaChadsvas.inicializar();}
		if (esObjeto['oEscalaCrusade']) {oEscalaCrusade.inicializar();}
		if (esObjeto['oFinalidad']) {oFinalidad.inicializar('C');}
		if (esObjeto['oInterpretaExam']) {oInterpretaExam.inicializar();}
		if (esObjeto['oModalOrdAmbPDF']) {oModalOrdAmbPDF.inicializar();}
		oAlertaIRAG.inicializar();
	}, 5000);
	CrearVarGlobal(aDatosIngreso);
	CargarReglas("#FormMotivo", "Motivo");
	CargarReglas("#FormDolorT", "DolorT");
	CargarReglas("#FormAntecedentes", "Antece");
	CargarReglas("#FormConcilia1", "Concilia");
	CargarReglas("#FormConcilia3", "Concilia");
	CargarReglas("#FormExamen", "Examen");
	CargarReglas("#FormExamenN", "ExamenN");
	CargarReglas("#FormPlanManejo", "Planman");
	CargarReglas("#FormPreguntasmed", "Ambmedi");
	CargarReglas("#FormRecomendacion", "Ambrecom");
	CargarReglas("#formFinalidad", "Finalida");
	CargarReglas("#Formmedicamentos","AmbMedic");

	if (esObjeto['oAval']) {
		oAval.inicializar('HC');
	}

	$('#btnTriage').on('click', verTriage);
	$('#btnAntecedentes').on('click', verAntecedentes);
	$('#btnTextoInf').on('click', verInformativo);
	$('#btnDatosPaciente').on('click', verDatosPaciente);
	$('#btnGuardarHC').on('click', validarEnviar);
	$('#btnVerPdfHC').on('click', verPdfHCBtn);
	$('#btnVistaPrevia').on('click', vistaPrevia);
	$('#btnLibroHC').on('click', abrirLibro);
	$('#btnVolver').on('click', retornarPag);
	$('#btnCerrarTexInformativo').on('click', cerrarTextoInformativo);

	// Seleccionar objeto en cada tab
	$("#HC_MenuTabs .nav-link").on({
		"shown.bs.tab": function(e){
			// Seleccionar objeto en cada tab
			$($(this).attr('data-focus')).focus();
		},
		"hide.bs.tab": function(e){
			if(e.target.id=='tabDiagnostico' && gbConfirm && !gbGuardado){
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
								//$(e.relatedTarget).tab('show');
								gbConfirm=true;
							}
						}
					);
					return false;
				}
			}
			if(e.target.id=='tabOrdenesAmb' && gbConfirm && !gbGuardado){
				if(!oAmbulatorio.validacion()){
					ubicarObjeto('#tabOptOrdMedicamento', '#buscarMedicaAmb', '#tabOptOrdMedicamento');
					fnConfirm('Tenga en cuenta que falta registrar dosis diaria, tiempo tratamiento o cantidad en formulación de egreso.<br><b>¿Desea revisar?</b>', "Conciliación egreso", false, false, false,
						{
							text: 'Verificar'
						},
						{
							text: 'Continuar',
							action: function(){
								gbConfirm=false;
								$("#"+e.relatedTarget.id).tab('show');
								gbConfirm=true;
								oAmbulatorio.iniciaCamposMedicamento();
							}
						}

					);
					return false;
				}
			}
		}
	});

	// Teleconsulta
	$('#btnTeleconsulta').on('click', function () {
		let lnIngreso = $('#lblIngreso').html();
		window.open('vista-jtm/index?p='+lnIngreso, "JTM", "height=600,width=800,location=1,status=1,scrollbars=1");
	});
});

function CargarObjNoVisibles(){
	$.ajax({
		type: "POST",
		url: "vista-historiaclinica/ajax/HistoriaClinica.php",
		data: {lcTipo: 'NoVisible'},
		dataType: "json"
	})
	.done(function(loDatos) {
		loObjNV=loDatos.NOVISIBLES;
		try {
			$.each(loObjNV, function( lcKey, loObj ) {
				var llVisible = true;
				if(loObjNV[lcKey]['OBJETOS'] !==''){
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
			console.log(err);
			fnAlert('No se pudo realizar la busqueda de objetos visibles para HC WEB. ');
		}
	});
}

function listaViasCenso(){
	gaViasCensoAutorizadas='';
	$.ajax({
		type: "POST",
		url: "vista-historiaclinica/ajax/HistoriaClinica.php",
		data: {lcTipo: 'listadoViasCenso'},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				gaViasCensoAutorizadas=loDatos.datos;
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la consulta lista vías mostrar censo para HC WEB.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar lista vías mostrar censo para HC WEB.');
	});
}

function listaSeccionesHd(){
	gaSeccionesHdia='';
	$.ajax({
		type: "POST",
		url: "vista-historiaclinica/ajax/HistoriaClinica.php",
		data: {lcTipo: 'listadoSeccionesHd'},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				gaSeccionesHdia=loDatos.datos;
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la consulta lista secciones hospital día HC WEB.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar lista secciones hospital día HC WEB.');
	});
}

function CargarReglas(tcForma, tcTitulo){
	oModalEspera.esperaAumentar();
 	$.ajax({
		type: "POST",
		url: "vista-historiaclinica/ajax/HistoriaClinica.php",
		data: {lcTipo: 'Reglas', lcTitulo: tcTitulo},
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
			console.log(err);
			fnAlert('No se pudo realizar la busqueda de objetos obligatorios para HC WEB. ');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al buscar objetos obligatorios para HC WEB. ');
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
		$("#btnLibroHC").attr("disabled", false);
		verInformativo();
	});
}

function validarEnviar(e){
	e.preventDefault();
	if (validarFormas()) {
		fnConfirm('Si guarda los cambios, <b>NO</b> podra modificarlos después.<br><b>¿Está seguro que desea Guardar los datos?</b>', 'HISTORIA CLINICA', false, false, false,
			{
				text: 'Si',
				action: function(){
					$('#btnGuardarHC').attr("disabled", true);
					enviarDatosHC();
				}
			},
			{
				text: 'No'
			}
		)
	}
}

function validarFormas(){
	if (! $('#FormMotivo').valid()){
		ubicarObjeto('#FormMotivo');
		return false;
	}
	if (! $('#FormDolorT').valid()){
		ubicarObjeto('#FormDolorT');
		return false;
	}
	if (! oAntecedentes.validacion()){
		return false;
	}
	if (! oActividadFisica.validacion()){
		ubicarObjeto('#FormAntecedentes', '#selRealizaActividad');
		fnAlert(oActividadFisica.lcMensajeError, 'Historia Clínica');
		return false;
	}
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
		fnAlert(oConciliacion.lcMensajeError, 'Historia Clínica');
		return false;
	}
	if (! $('#FormRevision').valid()){
		ubicarObjeto('#FormRevision');
		return false;
	}
	if (! $('#FormExamen').valid()){
		ubicarObjeto('#FormExamen');
		return false;
	}
	if (! $('#FormExamenN').valid()){
		ubicarObjeto('#FormExamenN');
		return false;
	}
	if(! oExamenFisico.validacion()){
		ubicarObjeto('#FormExamenN',oExamenFisico.lcObjetoError);
		fnAlert(oExamenFisico.lcMensajeError, 'Historia Clínica');
		return false;
	}
	if(! oNihss.validacion()){
		ubicarObjeto('#FormNihss', '#'+oNihss.lcObjetoError);
		fnAlert(oNihss.lcMensajeError, 'Historia Clínica');
		return false;
	}
	if(! oDiagnosticos.validacion()){
		ubicarObjeto('#FormDiagnostico', '#buscarcodigoCie');
		fnAlert(oDiagnosticos.lcMensajeError, 'Historia Clínica');
		return false;
	}
	if (! $('#FormPlanManejo').valid()){
		ubicarObjeto('#FormPlanManejo');
		return false;
	}
	if(! oPlanManejo.validacion()){
		ubicarObjeto('#FormPlanManejo', '#'+oPlanManejo.lcObjetoError);
		fnAlert(oPlanManejo.lcMensajeError, 'Historia Clínica');
		return false;
	}
	if (esObjeto['oAmbulatorio']) {
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
			fnAlert(oAmbulatorio.lcMensajeError, 'Historia Clínica', false, false, 'medium');
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
	if(!oSadPersons.validacion()){
		ubicarObjeto('#FormSadPersons','#'+oSadPersons.lcObjetoError);
		fnAlert(oSadPersons.lcMensajeError, 'Puntaje Sad persons');
		return false;
	}
	if (esObjeto['oFinalidad']) {
		if (! $('#formFinalidad').valid()){
			ubicarObjeto('#formFinalidad');
			return false;
		}
	}
	if (esObjeto['oInterpretaExam']) {
		if(! oInterpretaExam.validacion()){
			ubicarObjeto('#formIntrExamEx','#txtIntrExamProc');
			fnAlert(oInterpretaExam.lcMensajeError, 'Historia Clínica');
			return false;
		}
	}

	return true;
}

function obtenerDatosFormas(){
	var loEnviar = {
		'Ingreso': aDatosIngreso['nIngreso'],
		'nConCons': aDatosIngreso['nConCons'],
		'nConCita': aDatosIngreso['nConCita'],
		'nConEvol': aDatosIngreso['nConEvol'],
		'cCodCup': aDatosIngreso['cCodCup'],
		'cFormAnterior': aDatosIngreso['cFormAnterior'],
		'Auditoria': aAuditoria,
		'ctxtPandemia': oTextoInformativo.obtenerDatos(),
		'MotivoC': OrganizarSerializeArray(oMotivo.obtenerDatos()),
		'Antecedentes': OrganizarSerializeArray(oAntecedentes.obtenerDatos()),
		'Conciliacion': oConciliacion.obtenerDatos(),
		'Revision': OrganizarSerializeArray(oRevisionSistema.obtenerDatos()),
		'Examen': OrganizarSerializeArray(oExamenFisico.obtenerDatos()),
		'Nihss': OrganizarSerializeArray(oNihss.obtenerDatos()),
		'Diagnostico': oDiagnosticos.obtenerDatos(),
		'Planmanejo': oPlanManejo.obtenerDatos(),
		'Actividadfisica': oActividadFisica.obtenerDatos()
	};

	if(oSadPersons.bLlenarSadPerson){
		loEnviar['DatosSadPersons'] = oSadPersons.obtenerDatos();
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
	if (esObjeto['oAmbulatorio']) {loEnviar['Ambulatorio'] = oAmbulatorio.obtenerDatos();}
	if (esObjeto['oFinalidad']) {loEnviar['Finalidad'] = OrganizarSerializeArray(oFinalidad.obtenerDatos());}
	if (esObjeto['oInterpretaExam']) {loEnviar['InterpretaExam'] = oInterpretaExam.obtenerDatos();}
	if (esObjeto['oAval']) {loEnviar['PorAvalar'] = 'Si';}
	return loEnviar;
}

function enviarDatosHC(){
	oModalEspera.mostrar('<b>Se está guardando la Historia Clínica</b>');
	// El modal demora en aparecer. Se hace pausa para evitar que quede abierto si el ajax se ejecuta más rápido.
	setTimeout(function(){
		var loData = obtenerDatosFormas();
		$.ajax({
			type: "POST",
			url: "vista-historiaclinica/ajax/HistoriaClinica.php",
			data: {lcTipo: 'Verificar', datos: loData},
			dataType: "json"
		})
		.done(function(oDataDev){
			oModalEspera.ocultar();
			var lcError=(typeof oDataDev['error']=='string')?oDataDev['error'].trim():'',
				lcMsgErrGuardar = '<br><br><b>Es probable que la Historia Clínica se haya guardado parcialmente<br>Por favor comuníquese con el depto de TI</b>';
			if(lcError==''){
				if(oDataDev['Valido']){
					goDataImp = oDataDev.dataHC;
					if (typeof oDataDev.dataNihss !== 'undefined') {
						goDataNihss = oDataDev.dataNihss;
					}
					if (typeof oDataDev.dataOA !== 'undefined' && typeof loData.Ambulatorio == 'object') {
						oModalOrdAmbPDF.oDatosHC = goDataImp;
						oModalOrdAmbPDF.oDatosNihss = goDataNihss;
						oModalOrdAmbPDF.oDatos = oDataDev.dataOA;
						oModalOrdAmbPDF.habilitarBoton("HISTORIA");
						oModalOrdAmbPDF.consultar(false, goDataImp.nIngreso, goDataImp.cTipDocPac, goDataImp.nNumDocPac, oDataDev.dataOA.tFechaHora, oDataDev.dataOA.nConsecCita, oDataDev.dataOA.nConsecCons, oDataDev.dataOA.nConsecDoc);
					}
					gbGuardado=true;
					deshabilitarHC();

					oAlertaIRAG.validarMostrar(function(){
						if (esObjeto['oAmbulatorio']) {
							if (oAmbulatorio.gcTipoMiPres=='S') {
								oAmbulatorio.fnInformacionNopos(function(){
									if (oAmbulatorio.gcDatosNopos != '') {
										$('#txtListadoNopos').val(oAmbulatorio.gcDatosNopos);
										oModalAlertaNopos.mostrar(function(){
											finalizaHC();
										});
									} else {
										finalizaHC();
									}
								});
							} else {
								finalizaHC();
							}
						} else {
							finalizaHC();
						}
					});

				} else {
					if(oDataDev['Objeto']!=''){
						focusObjeto(oDataDev['Objeto']);
					}
					if (oDataDev['habGuardar']==true) {
						fnAlert(oDataDev['Mensaje'], 'Historia Clínica');
						$('#btnGuardarHC').attr("disabled", false);
					} else {
						fnAlert(oDataDev['Mensaje']+lcMsgErrGuardar, 'Historia Clínica');
					}
				}
			} else {
				oModalEspera.ocultar();
				if((typeof oDataDev['error_sesion']!=='undefined')?oDataDev['error_sesion']:false){
					modalSesionHcWeb();
					$('#btnGuardarHC').attr("disabled", false);
				} else {
					if (oDataDev['habGuardar']==true) {
						fnAlert(lcError, 'Historia Clínica');
						$('#btnGuardarHC').attr("disabled", false);
					} else {
						fnAlert(lcError+lcMsgErrGuardar, 'Historia Clínica');
					}
				}
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			oModalEspera.ocultar();
			console.log(jqXHR.responseText);
			fnAlert('Ocurrió un error al guardar la Historia Clínica', 'Historia Clínica');
			$('#btnGuardarHC').attr("disabled", false);
		});
	}, 100);
}

function finalizaHC(){
	// si es urgencias debe llenar bitácora/censo urgencias
	if (aDatosIngreso.cCodVia=='01') {
		var laDxs = oDiagnosticos.obtenerDatos();
		var lcDxPrin = '';
		$.each(laDxs, function(lnIndex, loDx) {
			if (loDx.CODTIPO=='1') {
				lcDxPrin = loDx.CODIGO;
				return;
			}
		});
		redirigirHC();
	} else {
		redirigirHC();
	}
}

function redirigirHC(){
	$('#btnVerPdfHC').attr("disabled",false);
	$('#btnVistaPrevia').attr("disabled",false);

	if (aAuditoria['lRequiereAval']==true){
		$('#btnVerPdfHC').attr("disabled",false);
		$('#btnVistaPrevia').attr("disabled",false);
		fnInformation('Historia Clínica para avalar se ha Guardado', 'Historia Clínica', false, false, false, function(){
			window.history.back();
		});
	}else{
		// Si es salida enviar a Epicrisis
		if ($("#selConductaSeguir").val()=='01') {
			verPdfHC(function(){
				var loDatosEnviar={cp:'epi', ingreso:aDatosIngreso['nIngreso']};
				formPostTemp('modulo-historiaclinica', loDatosEnviar, false);
			});
		} else {
			fnAlert('Historia Clínica se ha Guardado', 'Historia Clínica', 'exclamation-triangle', 'blue', 'small', function(){
				let lcViaIngreso=parseInt(aDatosIngreso.cCodVia).toString();
				if ($.inArray(lcViaIngreso, gaViasCensoAutorizadas)>=0){
					if (aDatosIngreso['cSeccion']!=gaSeccionesHdia){
						if (lcViaIngreso=='1'){ // urgencias
							window.open("modulo-censo-pacientes&q=censo&tcen=urg&tingreso="+aDatosIngreso['nIngreso'], "_blank");
						}
						if (lcViaIngreso=='5'){ // hospitalizacion
							window.open("modulo-censo-pacientes&q=censo&tcen=host&tingreso="+aDatosIngreso['nIngreso'], "_blank");
						}
					}
				}
			});
		}
	}
}

function verPdfHCBtn(){
	if (gbEsCE) {
		oModalOrdAmbPDF.mostrar();
	} else {
		verPdfHC();
	}
}

function verPdfHC(tfPostFunction){
	var laEnviar = [goDataImp];
	if (!(goDataNihss == null)) laEnviar.push(goDataNihss);
	vistaPreviaPdf({'datos':JSON.stringify(laEnviar)}, tfPostFunction, 'HISTORIA CLÍNICA '+goDataImp.tFechaHora, 'HISCLI');
}

function vistaPrevia(){
	oModalVistaPrevia.mostrar(goDataImp, 'HISTORIA CLÍNICA '+goDataImp.tFechaHora, 'HISCLI');
}

function abrirLibro(){
	formPostTemp('modulo-documentos', {'ingreso':aDatosIngreso['nIngreso']}, true);
}

function cerrarTextoInformativo(){
	if (esObjeto['oAmbulatorio']) {
		$.ajax({
		type: "POST",
		url: "vista-historiaclinica/ajax/HistoriaClinica.php",
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
				fnAlert('No se pudo realizar la busqueda de validación planes paciente.');
			}
		});
	}
}

function retornarPag(){
	if (gbGuardado) {
		window.history.back();
		return;
	}
	fnConfirm('¡Perderá los cambios realizados!<br><b>¿Está seguro que desea volver?</b>', 'HISTORIA CLINICA', false, false, false,
		{
			text: 'Si',
			action: function(){
				// Si es de urgencias desmarcar el ingreso
				if (aDatosIngreso.cCodVia=='01') {
					$.ajax({
						type: "POST",
						url: "vista-hc-urgencias/ajax/ajax",
						data: { accion:'desbloquearIngreso', ingreso:aDatosIngreso['nIngreso'] },
						dataType: "json"
					})
					.always(function(jqXHR, textStatus) {
						window.history.back();
					});
				} else {
					window.history.back();
				}
			}
		},
		{ text: 'No' }
	)
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

// Valida los objetos que se cargan condicionalmente
function sonObjetos(){
	esObjeto['oEscalaHasbled'] = typeof oEscalaHasbled === 'object';
	esObjeto['oEscalaChadsvas'] = typeof oEscalaChadsvas === 'object';
	esObjeto['oEscalaCrusade'] = typeof oEscalaCrusade === 'object';
	esObjeto['oSadPersons'] = typeof oSadPersons === 'object';
	esObjeto['oAmbulatorio'] = typeof oAmbulatorio === 'object';
	esObjeto['oFinalidad'] = typeof oFinalidad === 'object';
	esObjeto['oInterpretaExam'] = typeof oInterpretaExam === 'object';
	esObjeto['oModalOrdAmbPDF'] = typeof oModalOrdAmbPDF === 'object';
	esObjeto['oAval'] = (typeof oAval === 'object' && aAuditoria.lRequiereAval==false);
}

function verAntecedentes(){
	oAntecedentesConsulta.mostrar();
}

function verInformativo(){
	oTextoInformativo.mostrar();
}

function verDatosPaciente(){
	oModalDatosPaciente.consultaDatos(aDatosIngreso);
}

function verTriage(){
	oModalVistaPrevia.mostrar(gaTriage, 'TRIAGE '+gaTriage.tFechaHora, 'HISCLI');
}

function deshabilitarHC(){
	// deshabilita controles
	$("#divControlesHC input,#divControlesHC select,#divControlesHC textarea,#divTextInformativoModal textarea").attr("disabled", true);
	// deshabilita botones en formularios
	$("#btnAdicionarM,#AdcionarCie,#btnIntrExamAdd,#adicionarActividad").attr("disabled", true);
	$("#AdicionarMedAmb,#btnMedicamentosAnteriores,#AdicionarProcedimiento,#AdcionarInterconsulta,#AdicionarInsumo").attr("disabled", true);
	oDiagnosticos.gotableDiagnosticos.bootstrapTable('hideColumn', 'ACCIONES');
	oActividadFisica.gotableActividadHC.bootstrapTable('hideColumn', 'ACCION');
}
