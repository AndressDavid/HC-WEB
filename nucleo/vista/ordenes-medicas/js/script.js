oModalEspera.mostrar();
var goDataImp=null,
	gaProcedimientosMipres=[], gaMedicamentosActuales={},
	gnMostrarAlerta=0,
	gcTipoNoposOM='', gcTipoMiPresOM='', gcPacienteExcluido='',
	gcOrdenMedica='OM',
	glObligarMipres=false,
	gcUrlajaxOm= "vista-ordenes-medicas/ajax/ajax",
	gbGuardado=false;

 $(function () {
	$.validator.setDefaults({ ignore: "" });
	oCabDatosPac.inicializar();
	parametrosEntidad();

	$("textarea").on("focusout",function(e){
		$(this).val( $(this).val().trim() );
	})

	consultarAntecedentes();
	oAlertaMalNutricion.inicializar();
	oOxiGlucometriaOrdMedica.inicializar();
	oMedicamentosOrdMedica.inicializar();
	oProcedimientosOrdMedica.inicializar();
	oModalJustificacionPos.inicializar();
	oModalHemocomponentes.inicializar();
	oInterconsultaOrdMedica.inicializar();
	oAntecedentesConsulta.inicializar();
	oDietaOrdMedica.inicializar();
	oModalAlertaMipres.inicializar();
	oModalAlertaIntranet.inicializar();
	oModalObservacionesCups.inicializar();
	oEnfermeriaOrdMedica.inicializar();
	oModalMedicamentoControlado.inicializar();
	oModalJustificacioInmediato.inicializar();
	oModalJustificacionUsoAntibiotico.inicializar();
	oMedicamentosSuspendidos.inicializar();

	CargarReglas("Reglas","#FormOrdMedOxigenoGlucometrias","MedOxig");
	$('#btnDatosPacienteOM').on('click', ()=>oModalDatosPaciente.consultaDatos(aDatosIngreso));
	$('#btnVerPdfOrdenesMed').on('click', ()=>vistaPreviaPdf({'datos':JSON.stringify([goDataImp])}, null, 'ORDEN MÉDICA '+goDataImp.tFechaHora, 'ORDMED'));
	$('#btnVistaPreviaOrdenesMed').on('click', ()=>oModalVistaPrevia.mostrar(goDataImp, 'ORDEN MÉDICA '+goDataImp.tFechaHora, 'ORDMED'));
	$('#btnEvolucionesOM').on('click', ()=>formPostTemp('modulo-evoconsulta', {'ingreso':aDatosIngreso.nIngreso}, true));
	$('#btnAntecedentesOM').on('click', ()=>oAntecedentesConsulta.mostrar());
	$('#btncontrolGlucometriaOM').on('click', ()=>oModalRespCup.mostrar('GLUCOMETRIAS', aDatosIngreso.nIngreso, aDatosIngreso.cNombre));
	$('#btnLibroMed').on('click', ()=>formPostTemp('modulo-documentos', {'ingreso':aDatosIngreso.nIngreso}, true));
	$('#btnVolverOrdenesMed').on('click', retornarPagina);
	$('#btnGuardarOrdenesMedicas').on('click', validarEnviar);
	$('#btnAceptarMiPresOM').on('click', verificarMipres);
	$('#btnAceptarIntranet').on('click', verificarMipres);

	servicioMicroMedex.obtenerListaDiagnosticos(aDatosIngreso.nIngreso);
})

function CargarReglas(tcTipo, tcForma, tcTitulo ){
	oModalEspera.esperaAumentar();
 	$.ajax({
		type: "POST",
		url: "vista-ordenes-medicas/ajax/ajax",
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
				oOxiGlucometriaOrdMedica.habilitar();

		} catch(err) {
			alert('No se pudo realizar la busqueda de objetos obligatorios para ordenes médicas WEB.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		alert('Se presentó un error al buscar objetos obligatorios para ordenes médicas WEB. ');
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
		if (parseInt(aDatosIngreso.cEstado)===2){
			alertaMalNutricion();
			$("#btnGuardarOrdenesMedicas").attr("disabled", false);
		}else{
			alertaIngreso();
		}
	});
}

function alertaMalNutricion(){
	if (oAlertaMalNutricion.gcAlertaMalNutricion!=''){
		var laTextoMensaje=oAlertaMalNutricion.gcAlertaMalNutricion.DESCRIPCION.split('~');
		$("#lblAlertaRiesgoMalnutricion").text(laTextoMensaje[0] + '. ' + laTextoMensaje[1]);
		var lcTextoMensaje=laTextoMensaje[0] + '<br>' + laTextoMensaje[1];
		fnAlert(lcTextoMensaje, oAlertaMalNutricion.gcAlertaMalNutricion.TITULO, false, 'blue', 'medium');
	}
}

function consultarAntecedentes(){
	$.ajax({
		type: "POST",
		url: gcUrlajaxOm,
		data: {accion: 'ultimoAntecedente', lnIngreso: aDatosIngreso.nIngreso, tipdoc: aDatosIngreso.cTipId, numdoc: aDatosIngreso.nNumId},
		dataType: "json",
	})
	.done(function(loTipos) {
		try {
			if (loTipos.error == ''){
			datosAntecedentes(loTipos.TIPOS[15]);
			} else {
				fnAlert(loTipos.error);
			}

		} catch(err) {
			fnAlert('No se pudo la consulta de Antecedentes.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error la consulta de Antecedentes.');
	});
}

function parametrosEntidad(){
	$.ajax({
		type: "POST",
		url: gcUrlajaxOm,
		data: {accion: 'parametroentidad', lcCodigoPlan: aDatosIngreso.cPlan, lnIngreso: aDatosIngreso.nIngreso},
		dataType: "json",
	})
	.done(function(loTipos) {

		try {
			if (loTipos.error == ''){
				gcTipoNoposOM=loTipos.TIPOS.tiponopos;
				gcTipoMiPresOM=loTipos.TIPOS.tipomipres;
				glObligarMipres=loTipos.TIPOS.obligarmipres;
				gcPacienteExcluido=loTipos.TIPOS.pacienteexcluido;
			} else {
				fnAlert(loTipos.error);
			}

		} catch(err) {
			fnAlert('No se pudo Validar Plan NOPOS.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al buscar Plan NOPOS.');
	});
}

function datosAntecedentes(taDatos) {
	let lcAntAlergicos=lcAntClinicoPatologicos='';
	let lnFechaInicial=lnHoraInicial=lnFechaFinal=lnHoraFinal=0;
	lcAntAlergicos= taDatos[8].trim();
	lcAntClinicoPatologicos= taDatos[1].trim();
	$("#txtOrdMedAntAlergicos").val(lcAntAlergicos);
	$("#txtOrdMedAntClinicoPat").val(lcAntClinicoPatologicos);
}

function validarEnviar(e){
	e.preventDefault();
	llamarAlertasMedicamentos(function(){
		$('#tblConsumosMipres').bootstrapTable('removeAll');
		$("#btnGuardarOrdenesMedicas").attr("disabled", true);
		validacionIngreso();
	});
}

function alertaIngreso(){
	deshabilitarOM();
	$("#btnGuardarOrdenesMedicas").attr("disabled", true);
	let lcMensajeIngreso='Ingreso cerrado/Facturado, no se pueden realizar ordenes médicas, por favor verificar con el área de facturación.';
	fnAlert(lcMensajeIngreso, 'Validación ingreso', false, false, 'medium');
}

function validacionIngreso(){
	$.ajax({
		type: "POST",
		url: gcUrlajaxOm,
		data: {accion: 'verificarEstadoIngreso', lnIngreso: aDatosIngreso.nIngreso},
		dataType: "json",
	})
	.done(function(loTipos) {
		try {
			if (loTipos.error == ''){
				if (parseInt(loTipos.TIPOS)===2){
					oMedicamentosOrdMedica.cantidadRegistros(function(){
						oMedicamentosOrdMedica.cambiarEstados();
					});

					gnMostrarAlerta=0;
					if (glObligarMipres && gcTipoMiPresOM=='S' && gcPacienteExcluido==''){
						var laProcedimientos=$('#tblProcedimientoOM').bootstrapTable('getData');
						var laMedicamentos=$('#tblMedicamentosOM').bootstrapTable('getData');

						consultarConsumosNopos(laProcedimientos, function() {
							consultarMedicamentosNopos(laMedicamentos);
						});
					}
					if (validarOrdenesMedicas()) {
						fnConfirm('Desea guardar la orden médica?.', 'ORDENES MEDICAS', false, false, 'medium',
							{
								text: 'Si',
								action: function(){
									verificaFormulada();
								}
							},
							{
								text: 'No',
								action: function(){
									$("#btnGuardarOrdenesMedicas").attr("disabled", false);
								}
							}
						)
					}else{
						$("#btnGuardarOrdenesMedicas").attr("disabled", false);
					}
				}else{
					alertaIngreso();
				}
			} else {
				fnAlert(loTipos.error);
			}
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo Validar validación ingreso.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al buscar validación ingreso.');
	});
}

function verificaFormulada(){
	if ((oMedicamentosOrdMedica.gnNumFormulados<=oMedicamentosOrdMedica.gnConfirmarNoFormulados) && (oMedicamentosOrdMedica.gnNumNoFormulados>0)){
		lcTextoNoFormualdos="Hay " + oMedicamentosOrdMedica.gnNumNoFormulados + " medicamento(s) en estado 'No Formulado' <br>";
		lctextoMensaje=oMedicamentosOrdMedica.gnNumFormulados==0 ? 'No hay medicamentos Formulados <br>' : lcTextoNoFormualdos;
		lctextoMensaje=lctextoMensaje +'¿Desea continuar?';
		fnConfirm(lctextoMensaje, false, false, false, 'medium',
			{ text: 'Aceptar',
				action: function(){
					validarMipres();
				}
			},
			{  text: 'Cancelar',
				action: function(){
					$("#btnGuardarOrdenesMedicas").attr("disabled", false);
				}
			}
		);
	}else{
		validarMipres();
	}
}

function validarMipres(){
	if (gnMostrarAlerta>0){
		oModalAlertaMipres.mostrar();
	}else{
		$('#btnGuardarOrdenesMedicas').attr("disabled", true);
		enviarDatosOM();
	}
}

function verificaCodigoExiste(tcCodigo,taTablaValida){
	var llRetorno = true ;
		if(taTablaValida != ''){
			$.each(taTablaValida, function( lcKey, loTipo ) {
				if(loTipo['CODIGO']==tcCodigo){
					llRetorno = false;
				}
			});
		};
	return llRetorno ;
}

function verificarMipres(){
	if (oModalAlertaIntranet.gcMipresIntranet==''){
		if(!oModalAlertaMipres.guardarMiPres()){
			ubicarObjeto('#'+oModalAlertaMipres.lcFormaError, '#'+oModalAlertaMipres.lcObjetoError, '');
			fnAlert(oModalAlertaMipres.lcMensajeError, 'Alerta NOPOS', false, false, 'large');
			return false;
		}
		oModalAlertaMipres.aceptar();
	}else{
		oModalAlertaIntranet.aceptar();
	}
	$('#btnGuardarOrdenesMedicas').attr("disabled", true);
	enviarDatosOM();
}

function consultarConsumosNopos(taProcedimientos, tfPost){
	gaProcedimientosMipres=[];
	oModalAlertaIntranet.gcCupsNoposIntranet='';
	var laAgrupaCups={};
	var laProcedimientos=taProcedimientos;

	for(dato in laProcedimientos){
		lcCodigoCups=laProcedimientos[dato]['CODIGO'];
		lcDescripcionCups=laProcedimientos[dato]['DESCRIPCION'];
		lcPosNopos=laProcedimientos[dato]['POSNOPOS'];
		lcSiempreNopos=laProcedimientos[dato]['SIEMPRENOPOS'];

		if (lcPosNopos=='N' || lcSiempreNopos!=''){
			if(laAgrupaCups[laProcedimientos[dato]['CODIGO']]){
				laAgrupaCups[laProcedimientos[dato]['CODIGO']]++;
			}else{
				laAgrupaCups[laProcedimientos[dato]['CODIGO']]=1;
				oModalAlertaIntranet.gcCupsNoposIntranet+=lcCodigoCups+'-'+lcDescripcionCups+'\n';
			}
		}
	}

	$.each(laAgrupaCups, function( lcKey, loTipo ) {
		lcCodigoCups=lcKey;
		lnTotalCantidad=loTipo;
		lcDescripcionCups='';
		gaProcedimientosMipres.push({CODIGO: lcCodigoCups, TOTAL: lnTotalCantidad});
	});
	if (typeof tfPost == 'function') { tfPost(); }
}

function consultarMedicamentosNopos(taMedicamentos){
	var laMedicamentosMipres=[];
	oModalAlertaIntranet.gcMedNoposIntranet='';
	for(dato in taMedicamentos){
		lcCodigo=taMedicamentos[dato]['CODIGO'];
		lcDescripcion=taMedicamentos[dato]['MEDICAMENTO'];
		lcPosNopos=taMedicamentos[dato]['POSNOPOS'];
		lnSeformula=taMedicamentos[dato]['SEFORMULA'];
		lnInmediato=taMedicamentos[dato]['INMEDIATO'];

		if (lcPosNopos=='NOPOS' && (lnSeformula==1 ||  lnInmediato==1)){
			laMedicamentosMipres.push({CODIGO: lcCodigo, DESCRIPCION: lcDescripcion});
			oModalAlertaIntranet.gcMedNoposIntranet+=lcCodigo+'-'+lcDescripcion+'\n';
		}
	}
	fnNoPosSeDebeJustificar(gaProcedimientosMipres,laMedicamentosMipres);
}

function fnNoPosSeDebeJustificar(taProcedimientos,taMedicamentos){
	$.ajax({
		type: "POST",
		url: gcUrlajaxOm,
		data: {accion: 'justificacionMipres', lnIngreso: aDatosIngreso.nIngreso, laProcedimientos: taProcedimientos, laMedicamentos: taMedicamentos},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.TIPOS.length>0){
				gnMostrarAlerta=1;
				$.each(loDatos.TIPOS, function( lcKey, loTipo ) {
					oModalAlertaMipres.gotableConsumosMipres.bootstrapTable('insertRow', {
						index: 1,
						row: {
							CODIGO: loTipo.CODIGO,
							DESCRIPCION: loTipo.DESCRIPCION,
							CODDCI: '',
							DCI: '',
							NUMMIPRES: '',
							CANTIDADORDENADO: loTipo.CANTIDAD,
							CANTMIPRES: 0
						}
					});
				});
			}
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la consulta Justificación MIPRES.')
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar Justificación MIPRES.');
	});
}

function validarOrdenesMedicas(){
	oOxiGlucometriaOrdMedica.actualizaDatosGlucometria();
	oProcedimientosOrdMedica.incluirCupsNoInvasivos();

	if(!validarDiagnostico()){
		$('#txtCieOrdenMedica').focus();	
		return false;
	}
	
	if(!oOxiGlucometriaOrdMedica.validacionOxigeno()){
		ubicarObjeto('#'+oOxiGlucometriaOrdMedica.lcFormaError, '#'+oOxiGlucometriaOrdMedica.lcObjetoError, '#tabOptOrdMedOxigeno');
		fnAlert(oOxiGlucometriaOrdMedica.lcMensajeError, 'Ordenes Médicas Oxigeno', false, false, 'medium');
		return false;
	}

	if(!oOxiGlucometriaOrdMedica.validacionGlucometria()){
		ubicarObjeto('#'+oOxiGlucometriaOrdMedica.lcFormaError, '#'+oOxiGlucometriaOrdMedica.lcObjetoError, '#tabOptOrdMedOxigeno');
		fnAlert(oOxiGlucometriaOrdMedica.lcMensajeError, 'Ordenes Médicas Glucometria', false, false, 'medium');
		return false;
	}

	if(!oDietaOrdMedica.validacion()){
		ubicarObjeto('#'+oDietaOrdMedica.lcFormaError, '#'+oDietaOrdMedica.lcObjetoError, '#tabOptOrdMedDieta');
		fnAlert(oDietaOrdMedica.lcMensajeError, 'Ordenes Médicas Dietas', false, false, 'medium');
		return false;
	}

	if(!oMedicamentosOrdMedica.validacion()){
		ubicarObjeto('#'+oMedicamentosOrdMedica.lcFormaError, '#'+oMedicamentosOrdMedica.lcObjetoError, '#tabOptOrdMedMedicamentos');
		fnAlert(oMedicamentosOrdMedica.lcMensajeError, 'Ordenes Médicas - Medicamentos', false, false, 'medium');
		return false;
	}

	if(!oProcedimientosOrdMedica.validacion()){
		ubicarObjeto('#'+oProcedimientosOrdMedica.lcFormaError, '#'+oProcedimientosOrdMedica.lcObjetoError, '#tabOptOrdMedProcedimientos');
		fnAlert(oProcedimientosOrdMedica.lcMensajeError, 'Ordenes Médicas Procedimientos', false, false, 'medium');
		return false;
	}

	if(!oInterconsultaOrdMedica.validacion()){
		ubicarObjeto('#'+oInterconsultaOrdMedica.lcFormaError, '#'+oInterconsultaOrdMedica.lcObjetoError, '#tabOptOrdMedInterconsultas');
		fnAlert(oInterconsultaOrdMedica.lcMensajeError, 'Ordenes Médicas Interconsultas', false, false, 'medium');
		return false;
	}
	return true;
}

function validarDiagnostico(){
	let lcDiagnosticoOrden=$('#cCodigoCieOrdenMedica').val();
	let lcDiagnosticoDescripcion=$('#cDescripcionCieOrdenMedica').val();
	let lcLetraDiagnosticoOrden=lcDiagnosticoOrden.substring(0, 1);
	
	if (lcDiagnosticoOrden==''){
		fnAlert('Diagnóstico obligatorio, revise por favor.', 'Ordenes Médicas - Diagnóstico', false, false, 'medium');
		return false;
	}
	for(dato in oMedicamentosOrdMedica.gaDiagnosticosOrden){
		lcCodigoLetra=oMedicamentosOrdMedica.gaDiagnosticosOrden[dato]['LETRA'];
		
		if (lcCodigoLetra==lcLetraDiagnosticoOrden){
			lcTextoMensaje=oMedicamentosOrdMedica.gaDiagnosticosOrden[dato]['DESCRIPCION'];
			lcTituloMensaje=lcDiagnosticoOrden+'-'+lcDiagnosticoDescripcion;
			
			fnAlert(lcTextoMensaje, lcTituloMensaje, false, false, 'medium');
			return false;
		}
	}
	return true;
}

function enviarDatosOM(){
	oModalEspera.mostrar('<b>Se está guardando ordenes médicas</b>');
	var loData = JSON.stringify(obtenerDatosFormas());

	$.ajax({
		type: "POST",
		url: gcUrlajaxOm,
		data: {accion: 'Verificar', datos: loData},
		dataType: "json"
	})
	.done(function(oDatosOM) {
		oModalEspera.ocultar();
		var lcError=(typeof oDatosOM['error']=='string')?oDatosOM['error'].trim():'';
		if(lcError==''){
			if(oDatosOM['Valido']){
				goDataImp = oDatosOM.dataOM;
				gbGuardado=true;
				deshabilitarOM();
				terminaOrdenMedicas();
			}else {
				if(oDatosOM['Objeto']!='ordengrabar'){
					focusObjeto(oDatosOM['Objeto']);
				}

				obtenerFormulaActual(function() {
					reactivarFormulamedica();
				});

				fnAlert(oDatosOM['Mensaje'], 'ORDENES MEDICAS', false, false, 'medium');
				
				if(oDatosOM['Objeto']==='ordengrabar'){
					$('#btnGuardarOrdenesMedicas').attr("disabled", true);
				}else{
					$('#btnGuardarOrdenesMedicas').attr("disabled", false);
				}	
			}
		}else {
			oModalEspera.ocultar();
			if((typeof oDatosOM['error_sesion']!=='undefined')?oDatosOM['error_sesion']:false){
				modalSesionHcWeb();
			} else {
				fnAlert(lcError,'ORDENES MEDICAS');
			}
			$('#btnGuardarOrdenesMedicas').attr("disabled", false);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		oModalEspera.ocultar();
		console.log(jqXHR.responseText);
		fnAlert('Ocurrió un error al guardar las Ordenes médicas', 'Ordenes médicas.');
		$('#btnGuardarOrdenesMedicas').attr("disabled", false);
	});
}

function obtenerFormulaActual(tfPost){
	gaMedicamentosActuales=[];

	loTabla = $('#tblMedicamentosOM').bootstrapTable('getData');
	$.each(loTabla, function(lnIndice, loSeleccion){
		if (loSeleccion.SEFORMULA>0 || loSeleccion.INMEDIATO>0 || loSeleccion.SUSPENDER>0){
			gaMedicamentosActuales.push(loSeleccion);
		}
	});
	if (typeof tfPost == 'function') { tfPost(); }
}

function reactivarFormulamedica(){
	$('#tblMedicamentosOM').bootstrapTable('removeAll');

	$.each(gaMedicamentosActuales, function(lcKey, loSeleccion) {
		oMedicamentosOrdMedica.registrarMedicamento(loSeleccion,true,0,'','');
	});
	oMedicamentosOrdMedica.consultarUltimaFormula('B');
}

function terminaOrdenMedicas(){
	$('#btnVerPdfOrdenesMed').attr("disabled",false);
	$('#btnVistaPreviaOrdenesMed').attr("disabled",false);
	fnInformation('Orden médica se ha guardado.', 'Orden Médica', false, false, 'medium');
}

function deshabilitarOM(){
	$("#divControlesOM input,#divControlesOM select,#divControlesOM textarea").attr("disabled", true);
	$("#AdicionarInterconsultaOM,#btnSuspenderOxigeno,#AdicionarProcedimientoOM,#eliminarProcedimientosOM,#AdicionarMedicamentoOM").attr("disabled", true);
	oInterconsultaOrdMedica.gotableInterconsultasOM.bootstrapTable('hideColumn', 'BORRAR');
	oProcedimientosOrdMedica.gotableProcedimientosOM.bootstrapTable('hideColumn', 'SELECCION');
	oProcedimientosOrdMedica.gotableProcedimientosOM.bootstrapTable('hideColumn', 'ACCION');
	oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('hideColumn', 'ACCION');
	oMedicamentosOrdMedica.habilitarCampos(true, true);
	$('.modobservacion').attr("disabled",true);
}

function obtenerDatosFormas() {
	var loEnviar = {
		'Ingreso': aDatosIngreso.nIngreso,
	};
	loEnviar['OrdenesMedicas'] = obtenerDatosDatos();

	return loEnviar;
}

function obtenerDatosDatos() {
	var laDatos = [];
	var laOxigeno = oOxiGlucometriaOrdMedica.obtenerDatosOxigeno();
	var laMedicamentos = oMedicamentosOrdMedica.obtenerDatos();
	var laProcedimientos = oOxiGlucometriaOrdMedica.obtenerDatosGlucometria().concat(oInterconsultaOrdMedica.obtenerDatos()).concat(oProcedimientosOrdMedica.obtenerDatos());
	var laDieta = oDietaOrdMedica.obtenerDatos();
	var laHemocomponente = oModalHemocomponentes.obtenerDatos();
	var laMipres=oModalAlertaIntranet.gcMipresIntranet==''?oModalAlertaMipres.obtenerDatos():'';
	var laEnfermeria = {
		'OrdMedEnfermeria': $('#txtOrdMedEnfermeria').val(),
		'OrdMedDatosOxigeno': $('#txtOrdMedDatosOxigeno').val()
	}
	lcObtieneProcedimientos = laProcedimientos.length>0 ? laProcedimientos : '';
	lcDatosDiagnostico=$('#cCodigoCieOrdenMedica').val()+'~'+$('#cDescripcionCieOrdenMedica').val();
	var laDatos = {Oxigeno:laOxigeno, Medicamentos:laMedicamentos, Procedimientos: lcObtieneProcedimientos, Dieta:laDieta,
					Enfermeria: laEnfermeria, Hemocomponente: laHemocomponente, Mipres: laMipres,
					CieOrdenMedica: lcDatosDiagnostico	};
	return laDatos;
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

function focusObjeto(tcIdObjeto){
	if (!(typeof tcIdObjeto === 'string')) return false;
	var loObjeto = $("#"+tcIdObjeto);
	activarTab(loObjeto);
	loObjeto.focus();
}

function activarTab(toObjeto){
	var loTab = toObjeto.closest(".tab-pane");
	$("#"+loTab.attr("aria-labelledby")).tab("show");
}

function retornarPagina(){
	if (gbGuardado) {
		window.history.back();
		return;
	}
	fnConfirm('¡Perderá los cambios realizados!<br><b>¿Está seguro que desea volver?</b>', 'ORDENES MEDICAS', false, false, false,
		{
			text: 'Si',
			action: function(){
				window.history.back();
			}
		},
		{ text: 'No' }
	)
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
		loMedicamentos = $('#tblMedicamentosOM').bootstrapTable('getData'),
		lnCuentaMedNuevos = 0,
		lnCuentaFormulados = 0;
	$.each(loMedicamentos, function(tnClaveMed, loMedicamento){
		if (loMedicamento.SEFORMULA==1 || loMedicamento.INMEDIATO==1) {
			lnCuentaFormulados++;
		}
		if (loMedicamento.SUSPENDER !== 1) {
			loMedNuevos[loMedicamento.CODIGO] = loMedicamento.MEDICAMENTO+'|'+loMedicamento.DOSIS+' '+loMedicamento.DESCRUNIDADDOSIS+' cada '+loMedicamento.FRECUENCIA+' '+loMedicamento.DESCRUNIDADFRECUENCIA+' - Vía '+loMedicamento.DESCRVIA;
			lnCuentaMedNuevos++;
		}
	});
	if (lnCuentaFormulados == 0) {
		lnCuentaMedNuevos=0;
		loMedNuevos = {}
	}
	var lcDxPrincipal = $('#cCodigoCieOrdenMedica').val();
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
