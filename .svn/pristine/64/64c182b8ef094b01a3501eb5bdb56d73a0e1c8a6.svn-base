<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../publico/headJSCRIPT.php');	
?>
let goMobile = new MobileDetect(window.navigator.userAgent);


function getIdSelections() {
	return $.map($('#tableAgregarListaCitasTelemedicina').bootstrapTable('getSelections'), function (row) {
		return row;
	})
}
  
  
function queryParamsAgregar() {
	var params = {};
	$('#filterAgregarListaCitasTelemedicina').find('input,select').each(function () {
		params[$(this).attr('name')] = $(this).val();
	})
	return params;
} 	

function queryParams() {
	var params = {};
	$('#filterlistaCitasTelemedicina').find('input,select').each(function () {
		params[$(this).attr('name')] = $(this).val();
	})
	return params;
} 	

function queryParamsStringGet(){
	var params = '';
	$('#filterlistaCitasTelemedicina').find('input,select').each(function () {
		if($(this).val()){
			params += (params==''?'':'&')+$(this).attr('name')+'='+$(this).val();
		}
	})
	return params;	
}

function rowStyle(row, index) {
	return {
		css: {
			cursor: 'pointer'
		}
	}
}

function programarCitasTelemedicina(){
	$.ajax({
		type: 'POST',
		url: "vista-citas-telemedicina/ajax/listaCitasTelemedicina.ajax?accion=programa-citas-telemedicina",
		data: {'CITAS': getIdSelections()}
	})
	.done(function(response) {
		$('#tableAgregarListaCitasTelemedicina').bootstrapTable('refresh');
	})
	.fail(function(data) {
		$('#tableAgregarListaCitasTelemedicina').bootstrapTable('refresh');
	});	
}

function getPacienteAgregar(){
	$.ajax({
		type: 'POST',
		url: "vista-citas-telemedicina/ajax/listaCitasTelemedicina.ajax?accion=programa-cita-telemedicina-obtener-paciente",
		data: queryParamsAgregar()
	})
	.done(function(response) {
		$('#cNombreAgregar').text(response.NOMBRE);
	});		
}

function getProcedimientosLista(tcEspecialidad){
	$('#cProcedimiento').attr('disabled', 'disabled');
	
	$.ajax({
		type: 'POST',
		url: "vista-citas-telemedicina/ajax/listaCitasTelemedicina.ajax?accion=procedimientos-especialidad",
		data: {ESPECIALIDAD: tcEspecialidad}
	})
	.done(function(response) {
		
		if(response==''){
			$('#cProcedimiento').empty().append($("<option></option>"));
		}else{
			$('#cProcedimiento').empty().append($("<option></option>"));
			$.each(response, function(key, row) {   
				$('#cProcedimiento')
					.append($("<option></option>")
								.attr("value", row.CODIGO)
								.text(row.DESCRIPCION+" ("+row.CODIGO+")")); 
			});	
		}
		$('#cProcedimiento').removeAttr('disabled', 'disabled');
	})
	.fail(function(data) {
		$('#proveedorRegistro').empty().html('<i class="fa fa-exclamation-triangle"></i> Se presento un error al validar la existencia del codigo '+$(this).val()).addClass("alert").addClass("alert-danger").attr("role","alert");
	});		
}

function getMedicosLista(tcEspecialidad){
	$('#cMedico').attr('disabled', 'disabled');
	
	$.ajax({
		type: 'POST',
		url: "vista-citas-telemedicina/ajax/listaCitasTelemedicina.ajax?accion=medicos-especialidad",
		data: {ESPECIALIDAD: tcEspecialidad, TIPOS: "1, 3, 4, 6, 8, 10, 11, 12, 13, 91"}
	})
	.done(function(response) {
		
		if(response==''){
			$('#cMedico').empty().append($("<option></option>"));
		}else{
			$('#cMedico').empty().append($("<option></option>"));
			$.each(response, function(key, row) {   
				$('#cMedico')
					.append($("<option></option>")
								.attr("value", row.REGISTRO)
								.text(row.MEDICO.toUpperCase())); 
			});	
		}
		$('#cMedico').removeAttr('disabled', 'disabled');
	})
	.fail(function(data) {
		$('#proveedorRegistro').empty().html('<i class="fa fa-exclamation-triangle"></i> Se presento un error al validar la existencia del codigo '+$(this).val()).addClass("alert").addClass("alert-danger").attr("role","alert");
	});		
}

function ingresoFormatter(value, row, index) {
	return ('<i class="fas fa-edit mr-2"></i>'+(row.INGRESO>0?row.INGRESO:'Sin ingreso'));
}

function documentoFormatter(value, row, index) {
	return '<span class="font-weight-bold">'+[row.DOCUMENTO_TIPO,row.DOCUMENTO].join('-')+'<span>';
}

function fechaHoraCita(value, row, index) {
	return '<span class="p-0 badge badge-light bg-transparent">'+strNumAFecha(row.CITA_FECHA,'-')+'</span> <span class="font-weight-bold">'+strNumAHora(row.CITA_HORA,':')+'</span>';
}

function fechaHoraRealizado(value, row, index) {
	if(row.RELIZA_FECHA==row.CITA_FECHA && row.RELIZA_HORA==row.CITA_HORA){
		return 'Programado';
	}else{
		return '<span class="p-0 badge badge-light bg-transparent">'+strNumAFecha(row.RELIZA_FECHA,'-')+'</span> <span class="font-weight-bold">'+strNumAHora(row.RELIZA_HORA,':')+'</span>';
	}
}

function fechaHoraFinRealizado(value, row, index) {
	if(row.RELIZA_FIN_FECHA==0 && row.RELIZA_FIN_HORA==0){
		return 'Si especificar';
	}else{
		return '<span class="p-0 badge badge-light bg-transparent">'+strNumAFecha(row.RELIZA_FIN_FECHA,'-')+'</span> <span class="font-weight-bold">'+strNumAHora(row.RELIZA_FIN_HORA,':')+'</span>';
	}
}

function archivosFormatter(value, row, index) {
	return (row.ARCHIVOS>0?row.ARCHIVOS+'<i class="far fa-file ml-1"></i>':'Sin archivos');
}

function medicoFormatter(value, row, index) {
	return ('<span class="text-uppercase"><i class="fas fa-user-md pr-2"></i>'+row.MEDICO+'</i>');
}

function medicoAgendadooFormatter(value, row, index) {
	return ('<span class="text-uppercase text-secondary"><i class="fas fa-user-clock pr-2"></i>'+row.MEDICO_AGENDADO+'</i>');
}

function portalPacientes(value, row, index) {
	return (value=='SIN-REGISTRAR-PP'?'SIN-REGISTRAR-PP':'REGISTRADO');
}

function fechaFormato(value, row, index) {
	return strNumAFecha(value,'-');
}

$(function() {	
	$('#tableAgregarListaCitasTelemedicina').bootstrapTable('destroy').bootstrapTable({
		locale: 'es-ES',
		classes: 'table table-bordered table-hover table-sm table-responsive-sm table-striped',
		theadClasses: 'thead-light',			
		exportTypes: ['csv', 'txt', 'excel'],
		columns: [
					[
						{checkbox: true},
						{field: 'INGRESO', title: 'Ingreso', sortable: true, visible: true, class: 'text-nowrap'},
						{field: 'DOCUMENTO_TIPO', title: 'Documento', sortable: true, visible: true, class: 'text-nowrap', formatter: documentoFormatter},
						{field: 'PACIENTE', title: 'Nombre del paciente', class: 'text-nowrap'}, 
						{field: 'ESTADO_CODIGO', title: 'Estado codigo', sortable: true, visible: false, class: 'text-nowrap'},
						{field: 'ESPECIALIDAD', title: 'Especialidad', visible: true, class: 'text-nowrap'},
						{field: 'MEDICO', title: 'M&eacute;dico programado', sortable: true, visible: true, class: 'text-nowrap', formatter: medicoFormatter},
						{field: 'CUP_CODIGO', title: 'Codigo procedimiento', sortable: true, visible: false, class: 'text-nowrap'},
						{field: 'CUP_NOMBRE', title: 'Procedimiento', visible: true, class: 'text-nowrap'},
						{field: 'CITA_FECHA', title: 'Fecha/Hora<br/>Cita', sortable: true, visible: true, class: 'text-nowrap', formatter: fechaHoraCita},
					]
				]
	}).on('check.bs.table uncheck.bs.table ' + 'check-all.bs.table uncheck-all.bs.table', function () {
		selections = getIdSelections();
    })	
	
	$('#tableListaCitasTelemedicina').bootstrapTable('destroy').bootstrapTable({
		locale: 'es-ES',
		classes: 'table table-bordered table-hover table-sm table-responsive-sm table-striped',
		theadClasses: 'thead-light',			
		exportTypes: ['csv', 'txt', 'excel'],
		columns: [
					[
						{field: 'INGRESO', title: 'Ingreso', sortable: true, visible: true, class: 'text-nowrap', formatter: ingresoFormatter},
						{field: 'DOCUMENTO_TIPO', title: 'Documento', sortable: true, visible: true, class: 'text-nowrap', formatter: documentoFormatter},
						{field: 'EMAIL2', title: 'Portal Pacientes', visible: false, class: 'text-nowrap', formatter: portalPacientes},
						{field: 'PACIENTE', title: 'Nombre del paciente', class: 'text-nowrap'}, 
						{field: 'ESTADO_ORDEN_CODIGO', title: 'Estado orden codigo', sortable: true, visible: false, class: 'text-nowrap'},
						{field: 'ESTADO_ORDEN', title: 'Estado orden', sortable: true, visible: true, class: 'text-nowrap'},
						{field: 'ESTADO_CODIGO', title: 'Estado codigo', sortable: true, visible: false, class: 'text-nowrap'},
						{field: 'ESTADO', title: 'Estado', sortable: true, visible: true, class: 'text-nowrap'},		
						{field: 'EMAIL1', title: 'E-mail', visible: true, class: 'text-nowrap'},
						{field: 'ARCHIVOS', title: 'Archivos', visible: true, class: 'text-nowrap', formatter: archivosFormatter, align: 'center'},
						{field: 'ESPECIALIDAD', title: 'Especialidad', visible: true, class: 'text-nowrap'},
						{field: 'MEDICO', title: 'M&eacute;dico atiende', sortable: true, visible: true, class: 'text-nowrap', formatter: medicoFormatter},
						{field: 'MEDICO_AGENDADO', title: 'M&eacute;dico agendado', sortable: true, visible: true, class: 'text-nowrap', formatter: medicoAgendadooFormatter},
						{field: 'CUP_CODIGO', title: 'Codigo procedimiento', sortable: true, visible: false, class: 'text-nowrap'},
						{field: 'CUP_NOMBRE', title: 'Procedimiento', visible: true, class: 'text-nowrap'},
						{field: 'VALORACION_PAC', title: 'Valoraci&oacute;n<br/><small>Paciente</small>', sortable: true, visible: false, class: 'text-nowrap', align: 'right'},
						{field: 'VALORACION_MED', title: 'Valoraci&oacute;n<br/><small>M&eacute;dico</small>', sortable: true, visible: false, class: 'text-nowrap', align: 'right'},
						{field: 'CITA_FECHA', title: 'Fecha/Hora<br/>Cita', sortable: true, visible: true, class: 'text-nowrap', formatter: fechaHoraCita},
						{field: 'RELIZA_FECHA', title: 'Fecha/Hora<br/>Realizado', sortable: true, visible: true, class: 'text-nowrap', formatter: fechaHoraRealizado},
						{field: 'RELIZA_FIN_FECHA', title: 'Fecha/Hora<br/>Fin realizado', sortable: true, visible: true, class: 'text-nowrap', formatter: fechaHoraFinRealizado},	
						{field: 'EMAIL2', title: 'E-mail paciente', visible: false, class: 'text-nowrap'},
						{field: 'TELEFONO1', title: 'Tel&eacute;fono', visible: true, class: 'text-nowrap'},
						{field: 'TELEFONO2', title: 'Tel&eacute;fono 2', visible: false, class: 'text-nowrap'},
						{field: 'TELEFONO3', title: 'Tel&eacute;fono 3', visible: false, class: 'text-nowrap'},
						{field: 'TELEFONO4', title: 'Tel&eacute;fono autoregistrado', visible: false, class: 'text-nowrap'},
						{field: 'DIRECCION1', title: 'Direcci&oacute;n', visible: true, class: 'text-nowrap'},
						{field: 'DIRECCION2', title: 'Direcci&oacute;n 2', visible: false, class: 'text-nowrap'},
						{field: 'DIRECCION3', title: 'Direcci&oacute;n autoregistrada', visible: false, class: 'text-nowrap'},
						{field: 'NOTIFICACION_FECHA', title: 'Fecha<br/>Notificaci&oacute;n', sortable: true, visible: false, class: 'text-nowrap', formatter: fechaFormato},
						{field: 'RECORDATORIO_HORA', title: 'Fecha<br/>Recordatorio', sortable: true, visible: false, class: 'text-nowrap', formatter: fechaFormato},	
					]
				]
	});

	$('.fixed-table-body').css('min-height','320px');
	
	$('#tableListaCitasTelemedicina').on((goMobile.mobile()?'click-row.bs.table':'dbl-click-row.bs.table'), function (row, $element, field) {
		formPostTemp('modulo-citas-telemedicina&p=registroCitasTelemedicina&'+queryParamsStringGet(), $element, false);
	});
	
	$('.input-group.date').datepicker({
		autoclose: true,
		clearBtn: true,
		daysOfWeekHighlighted: "0,6",
		format: "yyyy-mm-dd",
		language: "es",
		todayBtn: true,
		todayHighlight: true,
		toggleActive: true,
		weekStart: 1
	});

	$('#cEspecialidad').on("change", function() {		
		getProcedimientosLista($(this).val());
		getMedicosLista($(this).val());
	});

	$('#cDocumentoAgregar').on("change", function() {		
		getPacienteAgregar();
	});	

	$('#nDocumentoAgregar').on("change", function() {		
		getPacienteAgregar();
	});	

	$('#btnBuscar').click(function () {
		$('#tableListaCitasTelemedicina').bootstrapTable('refresh');
    });	

	$('#btnBuscarAgregar').click(function () {
		$('#tableAgregarListaCitasTelemedicina').bootstrapTable('refresh');
    });		
	
	$('#btnAgregarTelemedicina').click(function () {
		programarCitasTelemedicina();
	})
	
	getMedicosLista('*');
});