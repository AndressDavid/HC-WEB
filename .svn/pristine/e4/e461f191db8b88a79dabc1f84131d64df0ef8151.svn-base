<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../publico/headJSCRIPT.php');

	$lnIngreso = intval(isset($_GET['nIngreso'])?$_GET['nIngreso']:'0');
	
?>let goMobile = new MobileDetect(window.navigator.userAgent);

function queryParams() {
	var params = {};
	$('#filterlistaAperturaSalas').find('input,select').each(function () {
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

function getPacienteAgregar(){
	$.ajax({
		type: 'GET',
		url: "vista-apertura-salas/ajax/listaAperturaSalas.ajax?accion=apertura-saladas-obtener-paciente",
		data: queryParams()
	})
	.done(function(response) {
		if(response.TIPO!=='' && response.NUMERO>0){
			$('#cDocumento option[value="'+response.TIPO+'"]').attr("selected", "selected");
			$('#nDocumento').val(response.NUMERO);
		}
		$('#cPaciente').val(response.NOMBRE);
		$('#nIngreso').removeAttr("disabled", "disabled");
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		alert('Se presentó un error al obtener datos del paciente');
	});		
}

function fechaHoraFormatter(value, row, index) {
	return '<span class="p-0 badge badge-light bg-transparent">'+strNumAFecha(row.FECHA,'-')+'</span> <span class="font-weight-bold">'+strNumAHora(row.HORA,':')+'</span>';
}

function documentoFormatter(value, row, index) {
	return '<span class="font-weight-bold">'+[row.DOCTIPO,row.DOCNUMERO].join('-')+'<span>';
}

function ingresoFormatter(value, row, index) {
	return '<i class="fas fa-edit mr-2"></i>'+row.INGRESO;
}
	
function estadoFormatter(value, row, index) {
	return (row.ESTADO=='2'?'Borrado':(row.ESTADO=='4'?'Liquidado':(row.ESTADO=='5'?'Facturado':(row.ESTADO=='0' || row.ESTADO==''?'Activo':row.ESTADO))));
}

function cseFormatter(value, row, index) {
	return [row.CSE,'<span class="font-weight-bold">'+row.CSE_NOMBRE+'</span>'].join('-');
}


$(function() {	
	$('#tableListaAperturaSalas').bootstrapTable('destroy').bootstrapTable({
		locale: 'es-ES',
		classes: 'table table-bordered table-hover table-sm table-responsive-sm table-striped',
		theadClasses: 'thead-light',			
		exportTypes: ['csv', 'txt', 'excel'],
		columns: [
					[
						{field: 'INGRESO',title: 'Ingreso', formatter: ingresoFormatter, class: 'text-nowrap'},
						{field: 'SALA',title: 'Sala'},
						{field: 'DOCNUMERO',title: 'Documento', formatter: documentoFormatter, class: 'text-nowrap'}, 
						{field: 'PACIENTE',title: 'Paciente'},	
						{field: 'ESTADO',title: 'Estado', formatter: estadoFormatter}, 
						{field: 'CONSECUTIVO',title: 'Consecutivo', visible: false},
						{field: 'CSE',title: 'Centro de Servicios', formatter: cseFormatter},
						{field: 'FECHA',title: 'Fecha', formatter: fechaHoraFormatter, class: 'text-nowrap'}, 
						{field: 'HABITACION',title: 'Habitaci&oacute;n', class: 'text-nowrap'}, 
					]
				]
	});

	$('.fixed-table-body').css('min-height','320px');
	
	$('#tableListaAperturaSalas').on((goMobile.mobile()?'click-row.bs.table':'dbl-click-row.bs.table'), function (row, $element, field) {
		formPostTemp('modulo-apertura-salas&p=registroAperturaSalas&'+queryParamsStringGet(), $element, false);
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


	$('#nIngreso').on("change", function() {
		$(this).attr("disabled", "disabled");
		getPacienteAgregar();
	});	


	$('#btnBuscar').click(function () {
		$('#tableListaAperturaSalas').bootstrapTable('refresh');
    });	
	
	$('#btnAgregar').click(function() {
		formPostTemp('modulo-apertura-salas&p=registroAperturaSalas&'+queryParamsStringGet(), {INGRESO: $('#nIngreso').val()}, false);		
	});
	
	<?php if($lnIngreso>0){ ?>$('#nIngreso').val(<?php print($lnIngreso); ?>).attr("disabled", "disabled").trigger("change");<?php }?>
			
});