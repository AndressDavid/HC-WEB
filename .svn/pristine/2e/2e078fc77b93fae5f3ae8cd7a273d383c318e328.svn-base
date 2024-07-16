<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../publico/headJSCRIPT.php');
	
	$lcTipoBitacora = (isset($_GET['q'])?$_GET['q']:'UNDEFINE');
	$lcEstado = (isset($_GET['r'])?$_GET['r']:'');
	$lcModo = strtoupper(strval(isset($_GET['s'])?$_GET['s']:'PACIENTE'));
	
?>
let goMobile = new MobileDetect(window.navigator.userAgent);
  
function queryParams() {
	var params = {};
	$('#toolbarlistaTamizajeNutricional').find('input[name]').each(function () {
		params[$(this).attr('name')] = $(this).val();
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

function consecutivoFormatter(value, row, index) {
	return [row.CONSECUTIVO,row.DETALLE].join('-');
}

function entidadFormatter(value, row, index) {
	return (row.ENTIDAD_RAZON_SOCIAL === ""?row.ENTIDAD_RAZON_COMERCIAL:row.ENTIDAD_RAZON_SOCIAL);
}

function habitacionFormatter(value, row, index) {
	return [row.SECCION,row.HABITACION].join('-');
}

function documentoFormatter(value, row, index) {
	return '<span class="font-weight-bold">'+[row.DOCTIPO,row.DOCNUMERO].join('-')+'<span>';
}

function ingresoFormatter(value, row, index) {
	return '<i class="fas fa-edit mr-2"></i>'+row.INGRESO;
}

function fechaHoraInicioFormatter(value, row, index) {
	return '<span class="p-0 badge badge-light bg-transparent">'+strNumAFecha(row.INICIO_FECHA,'-')+'</span> <span class="font-weight-bold">'+strNumAHora(row.INICIO_HORA,':')+'</span>';
}

function fechaHoraFinFormatter(value, row, index) {
	return '<span class="p-0 badge badge-light bg-transparent">'+strNumAFecha(row.FIN_FECHA,'-')+'</span> <span class="font-weight-bold">'+strNumAHora(row.FIN_HORA,':')+'</span>';
}

function fechaHoraEgresoFormatter(value, row, index) {
	return (row.EGRESO_FECHA==0 && row.EGRESO_HORA==0?'Sin egreso':'<span class="p-0 badge badge-light bg-transparent">'+strNumAFecha(row.EGRESO_FECHA,'-')+'</span> <span class="font-weight-bold">'+strNumAHora(row.EGRESO_HORA,':')+'</span>');
}
	

$(function() {	
	$('#tableListaNutriciones').bootstrapTable('destroy').bootstrapTable({
		locale: 'es-ES',
		classes: 'table table-bordered table-hover table-sm table-responsive-sm table-striped',
		theadClasses: 'thead-light',			
		exportTypes: ['csv', 'txt', 'excel'],
		columns: [
					[
						{field: 'CONSECUTIVO', title: 'ID', sortable: true, visible: false, class: 'text-nowrap'},
						{field: 'INGRESO', title: 'Ingreso', formatter: ingresoFormatter, class: 'text-nowrap'}, 
						{field: 'DOCNUMERO',title: 'Documento', formatter: documentoFormatter, class: 'text-nowrap'},  
						{field: 'PACIENTE', title: 'Nombre del paciente', class: 'text-nowrap'},
						{field: 'PISO', title: 'Piso', class: 'text-nowrap'},
						{field: 'CAMA', title: 'Cama', class: 'text-nowrap'},
						{field: 'FECHA', title: 'Fecha/Hora', class: 'text-nowrap'},
						{field: 'MEDICO', title: 'Médico', class: 'text-nowrap'},
						{field: 'ESPECIALIDAD', title: 'Especialidad', class: 'text-nowrap'},
						{field: 'CONTEO', title: 'Conteo', class: 'text-nowrap'},
						{field: 'VIA', title: 'Vía', class: 'text-nowrap'},
					]
				]
	});

	$('.fixed-table-body').css('min-height','480px');
	
	$('#tableListaNutriciones').on((goMobile.mobile()?'click-row.bs.table':'dbl-click-row.bs.table'), function (row, $element, field) {
		formPostTemp('modulo-nutricion&p=registroTamizajeNutricional', $element, false);
	});

	$('#btnNuevoRegistro').click(function() {
		formPostTemp('modulo-nutricion&p=registroTamizajeNutricional', {INGRESO: $(this).data('ingreso')}, false);		
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
});