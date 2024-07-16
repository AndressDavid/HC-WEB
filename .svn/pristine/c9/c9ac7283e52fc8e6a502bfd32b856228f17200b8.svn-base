<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../publico/headJSCRIPT.php');	
?>
let goMobile = new MobileDetect(window.navigator.userAgent);


function queryParams() {
	var params = {};
	$('#filterlistaNutricionPacientes').find('input,select').each(function () {
		params[$(this).attr('name')] = $(this).val();
	})
	return params;
} 	

function queryParamsStringGet(){
	var params = '';
	$('#filterlistaNutricionPacientes').find('input,select').each(function () {
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

function ingresoFormatter(value, row, index) {
	return ('<i class="fas fa-edit mr-2"></i>'+(row.INGRESO>0?row.INGRESO:'Sin ingreso'));
}

function documentoFormatter(value, row, index) {
	return '<span class="font-weight-bold">'+[row.DOCUMENTO_TIPO,row.DOCUMENTO].join('-')+'<span>';
}

function fechaHoraFormatter(value, row, index) {
	return '<span class="p-0 badge badge-light bg-transparent">'+strNumAFecha(row.FECHA_CREACION,'-')+'</span> <span class="font-weight-bold">'+strNumAHora(row.HORA_CREACION,':')+'</span>';
}

function fechaNacimientoFormatter(value, row, index) {
	return strNumAFecha(row.FECHA_NACIMIENTO,'-');
}

function habitacionFormatter(value, row, index) {
	return [row.SECCION,row.HABITACION].join('-');
}

function habitacionActualFormatter(value, row, index) {
	return [row.SECCION_ACTUAL,row.HABITACION_ACTUAL].join('-');
}


function estadoFormatter(value, row, index) {
	return (value=='3' ? 'Azul' : 'Rosado');
}

function EdadFormatter(value, row, index) {
	var laEdad = value.split('-');
	return laEdad[0]>0 ? laEdad[0]+' A&ntilde;os' : (laEdad[1]>0 ? laEdad[1]+' Meses' : laEdad[2]+' D&iacute;as');
}


$(function() {	
	
	$('#tableListaNutricionPacientes').bootstrapTable('destroy').bootstrapTable({
		locale: 'es-ES',
		classes: 'table table-bordered table-hover table-sm table-responsive-sm table-striped',
		theadClasses: 'thead-light',			
		exportTypes: ['csv', 'txt', 'excel'],
		columns: [
					[ 
						{field: 'INGRESO', title: 'Ingreso', sortable: true, visible: true, class: 'text-nowrap', formatter: ingresoFormatter},
						{field: 'CONSECUTIVO_NUTRICION', title: 'Consecutivo', sortable: true, visible: false, class: 'text-nowrap'},
						{field: 'DOCUMENTO_TIPO', title: 'Documento', sortable: true, visible: true, class: 'text-nowrap', formatter: documentoFormatter},
						{field: 'PACIENTE', title: 'Nombre del paciente', class: 'text-nowrap'}, 
						{field: 'FECHA_CREACION', title: 'Fecha', sortable: true, class: 'text-nowrap', formatter: fechaHoraFormatter},
						{field: 'ESTADO_NUTRICION', title: 'Codigo Estado', visible: false, class: 'text-nowrap'}, 
						{field: 'ESTADO_NUTRICION', title: 'Estado', class: 'text-nowrap', formatter: estadoFormatter}, 
						{field: 'FECHA_NACIMIENTO', title: 'Fecha Nacimiento', sortable: false, visible: false, class: 'text-nowrap', formatter: fechaNacimientoFormatter},
						{field: 'GENERO', title: 'Genero', visible: false, class: 'text-nowrap'}, 
						{field: 'CODIGO_VIA', title: 'Codigo vía', sortable: false, visible: false, class: 'text-nowrap'}, 
						{field: 'DESCRIPCION_VIA', title: 'Vía', class: 'text-nowrap'}, 
						{field: 'EDAD', title: 'Edad', class: 'text-nowrap', formatter: EdadFormatter},
						{field: 'REGISTRO_MEDICO', title: 'Registro m&eacute;dico', sortable: false, visible: false, class: 'text-nowrap'}, 
						{field: 'HABITACION', title: 'Habitaci&oacute;n<br/>Registrada', sortable: true, visible: false, class: 'text-nowrap', formatter: habitacionFormatter},
						{field: 'HABITACION_ACTUAL', title: 'Habitaci&oacute;n<br/>actual', sortable: true, class: 'text-nowrap', formatter: habitacionActualFormatter},
						{field: 'INGRESO_HABITACION', title: '<small>Ingreso</small><br/>Habitaci&oacute;n<br/>actual', sortable: false, visible: false, class: 'text-nowrap', formatter: habitacionFormatter},
						{field: 'CODIGO_ESTADO_HABITACION', title: '<small>Codigo</small><br/>Estado Habitaci&oacute;n<br/>actua', sortable: false, visible: false, class: 'text-nowrap'}, 
						{field: 'ESTADO_HABITACION', title: '<small>Estado</small><br/>Habitaci&oacute;n<br/>actual', visible: false, class: 'text-nowrap'}, 
						{field: 'FECHA_INGRESO', title: 'Fecha de Ingreso', class: 'text-nowrap'}, 
						{field: 'PLAN', title: 'Aseguradora', class: 'text-nowrap'}, 
						{field: 'MEDICO', title: 'M&eacute;dico Tratante', class: 'text-nowrap'}, 
						{field: 'CODIGO_ESPECIALIDAD', title: 'Codigo Especialidad', sortable: false, visible: false, class: 'text-nowrap'}, 
						{field: 'ESPECIALIDAD', title: 'Especialidad', class: 'text-nowrap'}, 
												
					]
				]
	});

	$('.fixed-table-body').css('min-height','320px');
	
	$('#tableListaNutricionPacientes').on((goMobile.mobile()?'click-row.bs.table':'dbl-click-row.bs.table'), function (row, $element, field) {
		formPostTemp('modulo-nutricion&p=registroNutricionPacientes&'+queryParamsStringGet(), $element, false);
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


	$('#btnBuscar').click(function () {
		$('#tableListaNutricionPacientes').bootstrapTable('refresh');
    });	
});