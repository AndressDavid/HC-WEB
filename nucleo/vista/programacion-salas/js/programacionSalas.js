var goTabla=$('#tblSalasCirugia'),
	goMobile = new MobileDetect(window.navigator.userAgent),
	gcUrlajax = "vista-programacion-salas/ajax/ajax",
	gcTitulo = 'Agenda Salas Cirugia',
	gaSalas = [],
	laDatosEncuentra = [],
	gcMotivoTipo = '',
	gcEstadoCancelacion = '',
	gnConsecutivoAgendar = 0,
	gnConsecutivoReagenda = 0,
	goFechaHora = [],
	goColorFila = {
		'1': '#BBE0F0',
		'2': '#ffff80'
	};



$(function () {
	IniciarTabla();
	IniciarListados();

	$('#selEspecialidadNuevo').on('change',function() {
		laEspec = $("#selEspecialidadNuevo").val();
		elCiru = $("#codCirujanoActual").val(); 		
		$('#selCirujanoNuevo').prop("disabled",false);
		$("#selCirujanoNuevo").val('');
		if (laEspec!=''){
			cargaCirujanos(elCiru, laEspec);
		}
	});
	
	$('#FrmFiltrosSalas .input-group.date').datepicker({
		autoclose: true,
		clearBtn: true,
		daysOfWeekHighlighted: "0,6",
		format: "yyyy-mm-dd",
		language: "es",
		todayBtn: "linked",
		todayHighlight: true,
		toggleActive: true,
		weekStart: 1
	});

	$('#btnBuscarSala').on('click', function(){
		consultaAgendaPaciente($("#selSalasC").val(),$("#txtFechaDesdeSala").val(),$("#txtFechaHastaSala").val());
	});

	$('#selTipoCancelacionSc').on('change',function() {
		$lcTipoCancelacion = $("#selTipoCancelacionSc").val();
		cargarMotivosCancelacion($lcTipoCancelacion);
		$('#selMotivoCancelacionSc').attr("disabled",false);
	});

	$('#btnLimpiarFiltros').on('click', limpiarFiltros);
	$('#btnCancelarAgendaSc').on('click', terminaCancelacion);
	$('#btnGuardaAgendaSc').on('click', validaCancelacion);
	$('#btnGuardaAnestesiologo').on('click', validaAnestesiologo);
	$('#btnGuardaCirujano').on('click', validaCirujano);
})


function cargaTiposModal(tcOpcion,tcMensaje){
	$('#btnGuardaAgendaSc').attr("disabled", false);
	$('#selTipoCancelacionSc').val('');
	var loSelect = $('#selTipoCancelacionSc');
	loSelect.empty();

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'tipoCancelacion', lcTipoCancela: tcOpcion},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				loSelect.append('<option value=""></option>');
				$.each(loDatos.datos, function(lcKey, loTipo) {
					loSelect.append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
				});
			} else {
				fnAlert(loDatos.error);
			}

		} catch(err) {
			fnAlert('No se pudo realizar la consultar ' + tcMensaje +'.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar ' + tcMensaje +'.');
	});
}

function cargaEspecialidades(tcEspecialidadActual){
	$("#txtEspecialidadActual").val('');
	$('#selEspecialidadNuevo').val('');
	var loSelect = $('#selEspecialidadNuevo');
	loSelect.empty();
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'listaespecialidades', lcEspecialidadActual: tcEspecialidadActual},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				loSelect.append('<option value=""></option>');
				$.each(loDatos.especialidades, function(lcKey, laEspecialidad) {
					if (tcEspecialidadActual == laEspecialidad.CODESP){ $tmp_sel = " SELECTED ";}else{ $tmp_sel = "";}
					loSelect.append('<option value="' + laEspecialidad.CODESP +'" '+$tmp_sel+' >' + laEspecialidad.DESESP + '</option>');
				});
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consultar las especialidades.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar las especialidades.');
	});
}


function cargaAnestesiologos(tcAnestesiologoActual){
	$("#txtAnestesiologoActual").val('');
	$('#selAnestesiologoNuevo').val('');
	var loSelect = $('#selAnestesiologoNuevo');
	loSelect.empty();
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'listaanestesiologos', lcAnestesiologoActual: tcAnestesiologoActual},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				loSelect.append('<option value=""></option>');
				$.each(loDatos.anestesiologos, function(lcKey, laMedico) {
					loSelect.append('<option value="' + laMedico.REGISTRO + '">' + laMedico.NOMBRE + '</option>');
				});
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consultar de los anestesiólogos.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar de los anestesiólogos.');
	});
}

function cargaCirujanos(tcCirujanoActual,tcEspecialidadSeleccionada){
	$('#selCirujanoNuevo').val('');
	var loSelect = $('#selCirujanoNuevo');
	loSelect.empty();

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'medicosespecialidad', lcCodigoEnviar: tcEspecialidadSeleccionada, lcCodigoCirujanoEnviar: tcCirujanoActual, lcTitulo: '', datos: '' },
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				loSelect.append('<option value=""></option>');
				$.each(loDatos.MEDICO, function( lcKey, loTipo ) {
					if(tcCirujanoActual!=lcKey){
						loSelect.append('<option value="' + lcKey + '">' + loTipo.desc + '</option>');
					}
				});
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consulta de los cirujanos.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar cirujanos.');
	});
}

function cargarMotivosCancelacion(tcTipoCancelacion){
	$('#selMotivoCancelacionSc').val('');
	var loSelect = $('#selMotivoCancelacionSc');
	loSelect.empty();

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'motivoCancelacion', lcTipoCancela: tcTipoCancelacion, lcTipoMotivo: gcMotivoTipo},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				loSelect.append('<option value=""></option>');
				$.each(loDatos.datos, function(lcKey, loTipo) {
					loSelect.append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
				});
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consultar Salas.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar listados.');
	});
}

function obtenerFechaSistema(tfFuncion){
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'fechahorasistema'},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				goFechaHora = loDatos.datos;
				if (typeof tfFuncion=='function'){
					tfFuncion();
				}
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			fnAlert('No se pudo consultar fecha/hora del sistema.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar fecha/hora del sistema.');
	});
}

function validaCancelacion(){
	var lcTipoCancelacion = $("#selTipoCancelacionSc").val();
	var lcMotivoCancelacion = $("#selMotivoCancelacionSc").val();

	if (gnConsecutivoAgendar==0 && gnConsecutivoReagenda==0){
		$('#selTipoCancelacionSc').focus();
		fnAlert('No existe datos de cancelación.');
		return false;
	}

	if (lcTipoCancelacion==''){
		$('#selTipoCancelacionSc').focus();
		fnAlert('Tipo cancelación obligatorio, revise por favor.');
		return false;
	}

	if (lcMotivoCancelacion==''){
		$('#selMotivoCancelacionSc').focus();
		fnAlert('Motivo cancelación obligatorio, revise por favor.');
		return false;
	}
	lcTipoMotivoCancelacion = lcTipoCancelacion + '-' + lcMotivoCancelacion;
	$('#btnGuardaAgendaSc').attr("disabled", true);
	if (gcEstadoCancelacion=='A'){
		$("#divCancelaAgendaSc").modal("hide");
	} else{
		guardarCancelacion(lcTipoMotivoCancelacion);
	}
}

function validaAnestesiologo(){
	var lcNuevoAnestesiologo = ($("#selAnestesiologoNuevo").val()).trim();
	if (gnConsecutivoAgendar==0){
		$('#selAnestesiologoNuevo').focus();
		fnAlert('No existe datos de modificación de anestesiologo.');
		return false;
	}
	if (lcNuevoAnestesiologo==''){
		$('#selAnestesiologoNuevo').focus();
		fnAlert('Debe seleccionar nuevo anestesiólogo, revise por favor.');
		return false;
	}
	guardarAnestesiologo(lcNuevoAnestesiologo);
}


function validaCirujano(){
	var lcNuevoCirujano = ($("#selCirujanoNuevo").val()).trim();
	var lcNuevaEspecialidad = ($("#selEspecialidadNuevo").val()).trim();
	if (gnConsecutivoAgendar==0){
		$('#selCirujanoNuevo').focus();
		fnAlert('No existe datos de modificación de Cirujano.');
		return false;
	}
	if (lcNuevaEspecialidad==''){
		$('#selEspecialidadNuevo').focus();
		fnAlert('Debe seleccionar la Especialidad, revise por favor.');
		return false;
	}
	if (lcNuevoCirujano==''){
		$('#selCirujanoNuevo').focus();
		fnAlert('Debe seleccionar nuevo Cirujano, revise por favor.');
		return false;
	}
	guardarCirujano(lcNuevoCirujano, lcNuevaEspecialidad);
}

function reagendarCita(taDatosReagenda){
	lcTipoMotivoCancelacion = ($("#selTipoCancelacionSc").val()).trim() + '-' + ($("#selMotivoCancelacionSc").val()).trim();
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'reagendarCita', lnConsecutivo: gnConsecutivoReagenda, lcDescCancelacion: lcTipoMotivoCancelacion, lcDatos: taDatosReagenda},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				fnAlert('Proceso realizado satisfactoriamente.', '', false, 'blue', false);
				terminaCancelacion();
			}else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consultar Salas.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar listados.');
	});
}

function guardarCancelacion(tcTipoMotivoCancelacion){
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'actualizarCancelacion', lnConsecutivo: gnConsecutivoAgendar, lcEstadoCancela: gcEstadoCancelacion, lcDescCancelacion: tcTipoMotivoCancelacion},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				fnAlert('Proceso realizado satisfactoriamente.', '', false, 'blue', false);
				terminaCancelacion();
			}else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consultar Salas.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar listados.');
	});
}

function guardarAnestesiologo(tcNuevoAnestesiologo){
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'actualizarAnestesiologo', lnConsecutivo: gnConsecutivoAgendar, lcEnviaAnestesiologo: tcNuevoAnestesiologo},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				fnAlert('Modificación realizada satisfactoriamente.', '', false, 'blue', false);
				terminaAnestesiologo();
			}else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consultar Salas.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar listados.');
	});
}

function guardarCirujano(tcNuevoCirujano, tcNuevaEspecialidad){
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'actualizarCirujano', lnConsecutivo: gnConsecutivoAgendar, lcEnviaCirujano: tcNuevoCirujano, lcEnviaEspecialidad: tcNuevaEspecialidad}, 
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				fnAlert('Modificación realizada satisfactoriamente.', '', false, 'blue', false);
				terminaCirujano();
			}else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consulta para Actualización de Cirujano');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar listados.');
	});
}


function terminaCancelacion(){
	gcEstadoCancelacion = '';
	gnConsecutivoAgendar = 0;
	gnConsecutivoReagenda = 0;
	$("#selTipoCancelacionSc").val('');
	$("#selMotivoCancelacionSc").empty();
	$("#selMotivoCancelacionSc").val('');
	$('#selMotivoCancelacionSc').attr("disabled",true);
	$("#divCancelaAgendaSc").modal("hide");
	consultaAgendaPaciente($("#selSalasC").val(),$("#txtFechaDesdeSala").val(),$("#txtFechaHastaSala").val());
	$("#selSalasC").focus();
}

function terminaAnestesiologo(){
	gcEstadoCancelacion = '';
	gnConsecutivoAgendar = 0;
	gnConsecutivoReagenda = 0;
	$("#txtAnestesiologoActual").val('');
	$("#selAnestesiologoNuevo").empty();
	$("#selAnestesiologoNuevo").val('');
	$("#divModificarAnestesiologo").modal("hide");
	consultaAgendaPaciente($("#selSalasC").val(),$("#txtFechaDesdeSala").val(),$("#txtFechaHastaSala").val());
	$("#selSalasC").focus();
}

function terminaCirujano(){
	gcEstadoCancelacion = '';
	gnConsecutivoAgendar = 0;
	gnConsecutivoReagenda = 0;
	$("#txtCirujanoActual").val('');
	$("#selCirujanoNuevo").empty();
	$("#selCirujanoNuevo").val('');
	$("#divModificarCirujano").modal("hide");
	consultaAgendaPaciente($("#selSalasC").val(),$("#txtFechaDesdeSala").val(),$("#txtFechaHastaSala").val());
	$("#selSalasC").focus();
}


function iniciaReagendar(){
	$("#selTipoCancelacionSc").val('');
	$("#selMotivoCancelacionSc").empty();
	$("#selMotivoCancelacionSc").val('');
	$('#selMotivoCancelacionSc').attr("disabled",true);
}


function IniciarListados(){
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'listadosPrincipal', lcCodigoEnviar: '', lcTitulo: '', datos: ''},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				//	SALAS
				loSelect = $("#selSalasC");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loDatos.salas, function( lcKey, loTipo ) {
					lcHabitacion = loTipo.SECHAB +' - '+ loTipo.NUMHAB;
					loSelect.append('<option value="' + lcHabitacion + '"' + (gcFiltroSala==lcHabitacion? ' selected="selected"': '') + '>' + lcHabitacion + '</option>');
					gaSalas.push(loTipo.SECHAB +' - '+ loTipo.NUMHAB);
				});
				if (gcFiltroSala.length>0) {
					consultaAgendaPaciente(gcFiltroSala, $("#txtFechaDesdeSala").val(),$("#txtFechaHastaSala").val())
				}
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consultar Salas.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar listados.');
	});
}

function limpiarFiltros(){
	goTabla.bootstrapTable('refreshOptions', {data: {}});
	$("#selSalasC").val("");
	var fechaActual = new Date();  
	$('#txtFechaDesdeSala, #txtFechaHastaSala').datepicker({ format: "yyyy-mm-dd" }); 
	$('#txtFechaDesdeSala, #txtFechaHastaSala').datepicker('setDate', fechaActual);
}

function ListaHoras() {
	var ltHorai=$("#txtFechaDesdeSala").val(),
		ltHoraf=$("#txtFechaHastaSala").val(),
		ltHoraIni = new Date(Date.UTC(parseInt(ltHorai.substr(0,4),10),parseInt(ltHorai.substr(5,2),10)-1,parseInt(ltHorai.substr(8,2),10),0,0,0)),
		ltHoraFin = new Date(Date.UTC(parseInt(ltHoraf.substr(0,4),10),parseInt(ltHoraf.substr(5,2),10)-1,parseInt(ltHoraf.substr(8,2),10),23,59,59)),
		ltHora = new Date(ltHoraIni),
		lnMinCambio = 30,
		lnSegCambio = lnMinCambio * 60,
		goData = [],
		lnNum = 0,
		lnConsecutivo = 0,
		lcNombrePaciente = '',
		lcDescripcionCups = '',
		lcDescripcionEspec = '',
		lcNombreMedico = '';

	if ($("#selSalasC").val()=='') {
		gaSalas.forEach(function(lcSala, lnIndice, array) {
			ltHora = new Date(ltHoraIni);
			while (ltHora <= ltHoraFin) {
				lnConsecutivo = 0;
				lcNombrePaciente = '';
				lcDescripcionCups = '';
				lcDescripcionEspec = '';
				lcNombreMedico = '';
				lcNombreAnestesiologo = '';
				lcRegistroAnestesiologo = '';
				lcRegistroEspecialidad = '';
				lcRegistroCirujano = '';
				lnFecha = ltHora.toISOString().substr(0,10).replace('-','').replace('-','');
				lnHoraIni = ltHora.toISOString().substr(11,8);
				lnHoraIni = lnHoraIni.replace(':','');
				lnHoraIni = lnHoraIni.replace(':','');

				$.each(laDatosEncuentra, function(lnKey, laData){
					if (laData.SALSAL==lcSala && laData.FPRSAL==lnFecha && (parseInt(laData.HPRSAL)<= parseInt(lnHoraIni) && laData.horafin>=parseInt(lnHoraIni))){
						lnConsecutivo = laData.CONSAL;
						lcNombrePaciente = laData.NM1PAL + ' ' + laData.NM2PAL + ' ' + laData.AP1PAL + ' ' + laData.AP2PAL;
						lcDescripcionCups = laData.CUPSAL + ' - ' + laData.DESCUP;
						lcDescripcionEspec = laData.DESESP;
						lcNombreMedico = laData.MEDICO;
						lcNombreAnestesiologo = laData.ANESTESIOLOGO;
						lcRegistroAnestesiologo = laData.ANESAL;
						lcRegistroEspecialidad = laData.DESESP;						
						lcRegistroCirujano = laData.MEDICO ;
						if(laData.horafin==lnHoraIni+10000){
							if(lnKey==0){
								laDatosEncuentra = laDatosEncuentra.slice(1);
							} else {
								laDatosEncuentra = laDatosEncuentra.slice(0,lnKey)+laDatosEncuentra.slice(lnKey+1);
							}
						}
					}
				});
				goData.push({idFila:lnNum++, sala: lcSala, fecha: ltHora.toISOString().substr(0,10), hora: ltHora.toISOString().substr(11,8),
				consecutivo:lnConsecutivo, paciente:lcNombrePaciente, procedimiento:lcDescripcionCups, especialidad:lcDescripcionEspec, cirujano:lcNombreMedico, anestesiologo:lcNombreAnestesiologo, reganestesiologo:lcRegistroAnestesiologo, regcirujano:lcRegistroCirujano, regespecialidad:lcRegistroEspecialidad});
				ltHora.setSeconds(lnSegCambio);
			}
		});
	} else {
		var lcSala=$("#selSalasC").val();
		ltHora = new Date(ltHoraIni);

		while (ltHora <= ltHoraFin) {
			lnConsecutivo = 0;
			lcNombrePaciente = '';
			lcDescripcionCups = '';
			lcDescripcionEspec = '';
			lcNombreMedico = '';
			lcNombreAnestesiologo = '';
			lcRegistroAnestesiologo = '';
			lcRegistroEspecialidad = '';
			lcRegistroCirujano = '';			
			lnFecha = ltHora.toISOString().substr(0,10).replace('-','').replace('-','');
			lnHoraIni = ltHora.toISOString().substr(11,8);
			lnHoraIni = lnHoraIni.replace(':','');
			lnHoraIni = lnHoraIni.replace(':','');

			$.each(laDatosEncuentra, function(lnKey, laData){
				if (laData.SALSAL==lcSala && laData.FPRSAL==lnFecha && (parseInt(laData.HPRSAL)<= parseInt(lnHoraIni) && laData.horafin>=parseInt(lnHoraIni))){
					lnConsecutivo = laData.CONSAL;
					lcNombrePaciente = laData.NM1PAL + ' ' + laData.NM2PAL + ' ' + laData.AP1PAL + ' ' + laData.AP2PAL;
					lcDescripcionCups = laData.CUPSAL + ' - ' + laData.DESCUP;
					lcDescripcionEspec = laData.DESESP;
					lcNombreMedico = laData.MEDICO;
					lcNombreAnestesiologo = laData.ANESTESIOLOGO;
					lcRegistroAnestesiologo = laData.ANESAL;
					lcRegistroEspecialidad = laData.CODESP;
					lcRegistroCirujano = laData.REGMED;					

					if(laData.horafin==lnHoraIni+10000){
						if(lnKey==0){
							laDatosEncuentra = laDatosEncuentra.slice(1);
						} else {
							laDatosEncuentra = laDatosEncuentra.slice(0,lnKey)+laDatosEncuentra.slice(lnKey+1);
						}
					}
					return;
				}
			});
			goData.push({idFila:lnNum++, sala: lcSala, fecha: ltHora.toISOString().substr(0,10), hora: ltHora.toISOString().substr(11,8),
			consecutivo:lnConsecutivo, paciente:lcNombrePaciente, procedimiento:lcDescripcionCups, especialidad:lcDescripcionEspec, cirujano:lcNombreMedico, anestesiologo:lcNombreAnestesiologo, reganestesiologo:lcRegistroAnestesiologo, regcirujano:lcRegistroCirujano, regespecialidad:lcRegistroEspecialidad});
			ltHora.setSeconds(lnSegCambio);
		}
	}
	goTabla.bootstrapTable('refreshOptions',{ data: goData });
}

function consultaAgendaPaciente(tcSala, tcFechaInicio, tcFechaFinal){
	goTabla.bootstrapTable('refreshOptions', {data: {}});
	goTabla.bootstrapTable('showLoading');
	laDatosEncuentra = [];

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'agendaPaciente', tipoSala: tcSala, fechaInicio: tcFechaInicio, fechaFinal: tcFechaFinal},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				$.each(loDatos.datos, function(lnKey, laData){
					lcMinutos = laData.TCUSAL.padStart(4,'0');
					lcMinutos = lcMinutos.substr(2,2);
					if (lcMinutos=='30'){
						lnSumarMinutos = 4000;
					}else{
						lnSumarMinutos = 0;
					}
					laData.horafin=parseInt(laData.HPRSAL)+(parseInt(laData.TCUSAL)*100)+lnSumarMinutos;
					laDatosEncuentra.push(laData);
				});
				ListaHoras();
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			console.error(error);
			fnAlert('No se pudo realizar la consulta agenda datos del paciente.');
		} finally {

		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar agenda datos del paciente.');
	});
}

function IniciarTabla() {
	goTabla.bootstrapTable({
		classes: 'table table-bordered table-hover table-sm table-responsive-sm', // table-striped
		theadClasses: 'thead-light',
		locale: 'es-ES',
		undefinedText: '',
		toolbar: '#toolBarLstIntrExam',
		pagination: false,
		rowStyle: formatoColorFila,
		onClickRow: function(toFila, toElement, toCampo){ if(goMobile.mobile()) fnPacienteSala(toFila); },
		onDblClickRow: function(toFila, toElement, toCampo){ if(!goMobile.mobile()) fnPacienteSala(toFila); },
		columns: [
			{
				title: 'Sala',
				field: 'sala',
				halign: 'center',
				width: 7, widthUnit: "%"
			},{
				title: 'Fecha',
				field: 'fecha',
				halign: 'center',
				width: 7, widthUnit: "%",
				formatter: formatoFechaAgenda
			},{
				title: 'Hora',
				field: 'hora',
				halign: 'center',
				width: 7, widthUnit: "%"
			},{
				title: 'Paciente',
				field: 'paciente',
				halign: 'center'
			},{
				title: 'Procedimiento',
				field: 'procedimiento',
				halign: 'center'
			},{
				title: 'Especialidad',
				field: 'especialidad',
				halign: 'center'
			},{
				title: 'Registro',
				field: 'regespecialidad',
				halign: 'center',
				visible: false
			},{				
				title: 'Cirujano',
				field: 'cirujano',
				formatter: formatoModificarCirujano,
				events: modificarCirujano,
				halign: 'center'
			},{
				title: 'Registro',
				field: 'regcirujano',
				halign: 'center',
				visible: false				
			},{
				title: 'Registro',
				field: 'reganestesiologo',
				halign: 'center',
				visible: false				
			},{
				title: 'Anestesiólogo',
				field: 'anestesiologo',
				formatter: formatoModificarAnestesiologo,
				events: modificarAnestesiologo,
				halign: 'center'
			},{
				title: 'Acciones',
				align: 'center',
				formatter: formatoAcciones,
				events: eventoAcciones,
				width: 12, widthUnit: "%"
			}
		],
	});
	$('.fixed-table-body').css('min-height','480px');
}


function formatoColorFila(toFila, tnIndice) {
	var lcColor='0';
	lnConsecutivoAgendar = toFila['consecutivo'];
	lcColor = lnConsecutivoAgendar>0 ? '1' : '2';
	return goColorFila[lcColor]? {css: {'background-color':goColorFila[lcColor]}}: {};
}

function fnPacienteSala(toFilaSel) {
	var loEnvio = toFilaSel;
	loEnvio.filtroSala = $("#selSalasC").val();
	loEnvio.filtroFini = $("#txtFechaDesdeSala").val();
	loEnvio.filtroFfin = $("#txtFechaHastaSala").val();
	gnConsecutivoAgendar = loEnvio['consecutivo'];
	if (gcEstadoCancelacion=='A'){
		fnConfirm('Desea modificar la agenda seleccionada?.', false, false, false, false,
			{
				text: 'Aceptar',
				action: function(){
					reagendarCita(loEnvio);
				}
			},
			{
				text: 'Retornar',
				action: function(){
					terminaCancelacion();
				}
			}
		);
	} else {
		if (gnConsecutivoAgendar==0){
			obtenerFechaSistema(function(){
				var lcFecha = loEnvio['fecha'],
					lcHora =  loEnvio['hora'];

				var ldFechaAgenda = new Date(lcFecha.substr(0,4),parseInt(lcFecha.substr(5,2))-1,lcFecha.substr(8,2),lcHora.substr(0,2),lcHora.substr(3,2));
				lcFecha = goFechaHora.fecha;
				lcHora = goFechaHora.hora.padStart(6,'0');
				var ldFechaSistema = new Date(lcFecha.substr(0,4),parseInt(lcFecha.substr(4,2))-1,lcFecha.substr(6,2),lcHora.substr(0,2),lcHora.substr(2,2));
				if (ldFechaSistema<ldFechaAgenda){
					formPostTemp('modulo-programacion-salas&q=datosCirugia', loEnvio, false);
				} else {
					fnAlert("No se puede asignar la agenda, fecha/hora agenda es menor a fecha/hora sistema.");
					return false;
				}
			});
		} else {
			formPostTemp('modulo-programacion-salas&q=datosCirugia', loEnvio, false);
		}
	}
}

var modificarAnestesiologo = {
	'click .modificaranes': function(e, tcValor, toFila, tnIndice) {
		var loEnvio = toFila;
		gnConsecutivoAgendar = toFila['consecutivo'];
		if (gnConsecutivoAgendar>0){
			obtenerFechaSistema(function(){
				var lcFecha = loEnvio['fecha'],
					lcHora =  loEnvio['hora'];
				var ldFechaAgenda = new Date(lcFecha.substr(0,4),parseInt(lcFecha.substr(5,2))-1,lcFecha.substr(8,2),lcHora.substr(0,2),lcHora.substr(3,2));
				lcFecha = goFechaHora.fecha;
				lcHora = goFechaHora.hora.padStart(6,'0');
				var ldFechaSistema = new Date(lcFecha.substr(0,4),parseInt(lcFecha.substr(4,2))-1,lcFecha.substr(6,2),lcHora.substr(0,2),lcHora.substr(2,2));				
				if (ldFechaSistema<ldFechaAgenda){
					cargaAnestesiologos(toFila['reganestesiologo']);
					$("#txtAnestesiologoActual").val(toFila['anestesiologo']);
					$('#divModificarAnestesiologo').modal('show');
				} else {
					fnAlert("No se puede modificar el médico Anestesiólogo, fecha/hora agenda es menor a fecha/hora sistema.", "Alerta", false, false, 'medium');
					return false;
				}
			});
		}else{
			fnAlert("No se puede modificar el médico Anestesiólogo, no existe paciente seleccionado.");
		}
	}
}

var modificarCirujano = {
	'click .modificarciru': function(e, tcValor, toFila, tnIndice) {
		var loEnvio = toFila;
		gnConsecutivoAgendar = toFila['consecutivo'];
		if (gnConsecutivoAgendar>0){
			obtenerFechaSistema(function(){
				var lcFecha = loEnvio['fecha'],
					lcHora =  loEnvio['hora'];
				var ldFechaAgenda = new Date(lcFecha.substr(0,4),parseInt(lcFecha.substr(5,2))-1,lcFecha.substr(8,2),lcHora.substr(0,2),lcHora.substr(3,2));
				lcFecha = goFechaHora.fecha;
				lcHora = goFechaHora.hora.padStart(6,'0');
				var ldFechaSistema = new Date(lcFecha.substr(0,4),parseInt(lcFecha.substr(4,2))-1,lcFecha.substr(6,2),lcHora.substr(0,2),lcHora.substr(2,2));
				if (ldFechaSistema<ldFechaAgenda){
				cargaEspecialidades(toFila['regespecialidad']);
				cargaCirujanos(toFila['regcirujano'],toFila['regespecialidad']);
				$("#codCirujanoActual").val(toFila['regcirujano']);

				$("#txtEspecialidadActual").val(toFila['especialidad']);
				$("#txtCirujanoActual").val(toFila['cirujano']);
				
				$('#divModificarCirujano').modal('show');
				} else {
					fnAlert("No se puede modificar el médico Cirujano, fecha/hora agenda es menor a fecha/hora sistema.", "Alerta", false, false, 'medium');
					return false;
				}
			});
		}else{
			fnAlert("No se puede modificar el médico Cirujano, no existe paciente seleccionado.");
		}
	}
}

function formatoFechaAgenda(value, row, index) {
	return value.replace('-','/').replace('-','/');
}


function formatoModificarAnestesiologo(value, row, index) {
	if (row['consecutivo']>0){
		return [
			'<a class="modificaranes" href="javascript:void(0)" title="Modificar Anestesiólogo">' + value,
			'</a>'
		].join('');
	} else {
		return '';
	}
}


function formatoModificarCirujano(value, row, index) {
	if (row['consecutivo']>0){
		return [
			'<a class="modificarciru" href="javascript:void(0)" title="Modificar Cirujano">' + value,
			'</a>'
		].join('');
	} else {
		return '';
	}
}


function formatoAcciones(value, row, index) {
	if (row['consecutivo']>0){
		return [
			'<a class="cancelar" href="javascript:void(0)" title="Cancelar">',
			'<i class="fa fa-trash" style="color:#527DF0"></i>',
			'</a>&nbsp;&nbsp;&nbsp;',
			'<a class="anular" href="javascript:void(0)" title="Anular">',
			'<i class="fa fa-window-close" style="color:#527DF0"></i>',
			'</a>&nbsp;&nbsp;&nbsp;',
			'<a class="reagendar" href="javascript:void(0)" title="Reagendar">',
			'<i class="fas fa-power-off" style="color:#527DF0"></i>',
			'</a>'
		].join('');
	} else {
		return '';
	}
}


var eventoAcciones = {
	'click .cancelar': function(e, tcValor, toFila, tnIndice) {
		gnConsecutivoAgendar = toFila['consecutivo'];
		gcEstadoCancelacion = 'C';
		lcTipoTipo = 'TIPOCAN';
		gcMotivoTipo = 'MOTICAN';
		if (gnConsecutivoAgendar>0){
			obtenerFechaSistema(function(){
				var lcFecha = toFila['fecha'],
					lcHora =  toFila['hora'];

				var ldFechaAgenda = new Date(lcFecha.substr(0,4),parseInt(lcFecha.substr(5,2))-1,lcFecha.substr(8,2),lcHora.substr(0,2),lcHora.substr(3,2));
				lcFecha = goFechaHora.fecha;
				lcHora = goFechaHora.hora.padStart(6,'0');
				var ldFechaSistema = new Date(lcFecha.substr(0,4),parseInt(lcFecha.substr(4,2))-1,lcFecha.substr(6,2),lcHora.substr(0,2),lcHora.substr(2,2));
				if (ldFechaSistema<ldFechaAgenda){
					iniciaReagendar();
					cargaTiposModal(lcTipoTipo,'Cancelar');
					$('#divCancelaAgendaSc').modal('show');
				} else {
					fnAlert("No se puede realizar la cancelación, fecha/hora agenda es menor a fecha/hora sistema.", false, false, false, 'medium');
					return false;
				}
			});
		}else{
			fnAlert("No se puede cancelar la cita, no existe paciente seleccionado.");
		}
	},

	'click .anular': function(e, tcValor, toFila, tnIndice) {
		gnConsecutivoAgendar = toFila['consecutivo'];
		gcEstadoCancelacion = 'N';
		lcTipoTipo = 'TIPOANU';
		gcMotivoTipo = 'MOTIANU';
		if (gnConsecutivoAgendar>0){
			obtenerFechaSistema(function(){
				var lcFecha = toFila['fecha'],
					lcHora =  toFila['hora'];

				var ldFechaAgenda = new Date(lcFecha.substr(0,4),parseInt(lcFecha.substr(5,2))-1,lcFecha.substr(8,2),lcHora.substr(0,2),lcHora.substr(3,2));
				lcFecha = goFechaHora.fecha;
				lcHora = goFechaHora.hora.padStart(6,'0');
				var ldFechaSistema = new Date(lcFecha.substr(0,4),parseInt(lcFecha.substr(4,2))-1,lcFecha.substr(6,2),lcHora.substr(0,2),lcHora.substr(2,2));
				if (ldFechaSistema<ldFechaAgenda){
					iniciaReagendar();
					cargaTiposModal(lcTipoTipo,'Anular');
					$('#divCancelaAgendaSc').modal('show');
				} else {
					fnAlert("No se puede realizar la anulación, fecha/hora agenda es menor a fecha/hora sistema.", "Alerta", false, false, 'medium');
					return false;
				}
			});
		}else{
			fnAlert("No se puede anular la cita, no existe paciente seleccionado.");
		}
	},

	'click .reagendar': function(e, tcValor, toFila, tnIndice) {
		var loEnvio = toFila;
		gnConsecutivoAgendar = 0;
		gnConsecutivoReagenda = toFila['consecutivo'];
		gcEstadoCancelacion = 'A';
		lcTipoTipo = 'TIPOCAN';
		gcMotivoTipo = 'MOTICAN';
		if (gnConsecutivoReagenda>0){
			obtenerFechaSistema(function(){
				var lcFecha = loEnvio['fecha'],
					lcHora =  loEnvio['hora'];
				var ldFechaAgenda = new Date(lcFecha.substr(0,4),parseInt(lcFecha.substr(5,2))-1,lcFecha.substr(8,2),lcHora.substr(0,2),lcHora.substr(3,2));
				lcFecha = goFechaHora.fecha;
				lcHora = goFechaHora.hora.padStart(6,'0');
				var ldFechaSistema = new Date(lcFecha.substr(0,4),parseInt(lcFecha.substr(4,2))-1,lcFecha.substr(6,2),lcHora.substr(0,2),lcHora.substr(2,2));
				if (ldFechaSistema<ldFechaAgenda){
					iniciaReagendar();
					cargaTiposModal(lcTipoTipo,'Reagendar');
					$('#divCancelaAgendaSc').modal('show');
				} else {
					fnAlert("No se puede reagendar la cita, fecha/hora agenda es menor a fecha/hora sistema.", "Alerta", false, false, 'medium');
					return false;
				}
			});
		}else{
			fnAlert("No se puede reagendar la cita, no existe paciente seleccionado.");
		}
	}
}
