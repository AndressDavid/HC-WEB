<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../publico/headJSCRIPT.php');
	
	$lcTipoIngreso = (isset($_GET['q'])?$_GET['q']:'DEFAULT');	
?>
let goMobile = new MobileDetect(window.navigator.userAgent);
  
function queryParams() {
	var params = {};
	$('#toolbarlistaIngresos').find('input[name]').each(function () {
		params[$(this).attr('name')] = $(this).val();
	})
	return params;
} 	

function queryParamsStringGet(){
	var params = '';
	$('#filterlistaAperturaSalas').find('input,select').each(function () {
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
	return '<i class="fas fa-edit mr-2"></i>'+row.INGRESO;
}
	

$(function() {	
	$('#tableListaIngresos').bootstrapTable('destroy').bootstrapTable({
		locale: 'es-ES',
		classes: 'table table-bordered table-hover table-sm table-responsive-sm table-striped',
		theadClasses: 'thead-light',			
		exportTypes: ['csv', 'txt', 'excel'],
		columns: [
					[
						{field: 'INGRESO',title: 'Ingreso', formatter: ingresoFormatter, class: 'text-nowrap'}, 
					]
				]
	});

	$('.fixed-table-body').css('min-height','480px');
	
	$('#tableListaIngresos').on((goMobile.mobile()?'click-row.bs.table':'dbl-click-row.bs.table'), function (row, $element, field) {
		formPostTemp('modulo-ingresos&p=registroIngresos&q=<?php print($lcTipoIngreso); ?>', $element, false);
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
	
	
	$('#btnAgregar').click(function() {
		formPostTemp('modulo-ingresos&p=registroIngresos&'+queryParamsStringGet(), {INGRESO: $('#nIngreso').val()}, false);		
	});	
});