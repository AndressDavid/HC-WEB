<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../publico/headJSCRIPT.php');
?>
let goMobile = new MobileDetect(window.navigator.userAgent);

function queryParams() {
	var params = {};
	$('#toolbarlistaBitacoras').find('input[name]').each(function () {
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
	return '<i class="fas fa-edit mr-2"></i>'+row.RECNO;
}

function usuarioFormatter(value, row, index) {
	return '<span class="font-weight-bold">'+row.USUARI+'<span>';
}

function documentoFormatter(value, row, index) {
	return '<span class="font-weight-bold">'+[row.TIDRGM,row.NIDRGM].join('-')+'<span>';
}

function estadoFormatter(value, row, index) {
	return '<span class="text-'+(value=='ACTIVO'?'success':'danger')+'">'+value+'<span>';
}

function fechaInicioFormatter(value, row, index) {
	return '<span class="p-0 badge badge-light bg-transparent">'+strNumAFecha(row.FVDRGM,'-')+'</span>';
}

function fechaFinFormatter(value, row, index) {
	return '<span class="p-0 badge badge-light bg-transparent">'+strNumAFecha(row.FVHRGM,'-')+'</span>';
}

$(function() {
	$('#tableUsuarios').bootstrapTable('destroy').bootstrapTable({
		locale: 'es-ES',
		classes: 'table table-bordered table-hover table-sm table-responsive-sm table-striped',
		theadClasses: 'thead-light',
		exportTypes: ['csv', 'txt', 'excel'],
		columns: [
					[
						{field: 'RECNO',title: 'ID', formatter: consecutivoFormatter, class: 'text-nowrap'},
						{field: 'USUARI',title: 'Usuario', formatter: usuarioFormatter, class: 'text-nowrap'},
						{field: 'DOCNUMERO',title: 'Documento', formatter: documentoFormatter, class: 'text-nowrap'},
						{field: 'REGMED', title: 'Registro'},
						{field: 'NOMMED', title: 'Apellidos'},
						{field: 'NNOMED', title: 'Nombres'},
						{field: 'CTPMRGM', title: 'Tipo de Usuario'},
						{field: 'CESTRGM', title: 'Estado', formatter: estadoFormatter, class: 'text-nowrap'},
						{field: 'CCODRGM', title: 'Especialidad'},
						{field: 'FVDRGM', title: 'V. Inicio', formatter: fechaInicioFormatter, class: 'text-nowrap'},
						{field: 'FVHRGM', title: 'V.Fin', formatter: fechaFinFormatter, class: 'text-nowrap'},
						{field: 'EMAIL', title: 'email'},
					]
				]
	});

	$('.fixed-table-body').css('min-height','480px');

	$('#tableUsuarios').on((goMobile.mobile()?'click-row.bs.table':'dbl-click-row.bs.table'), function (row, $element, field) {
		formPostTemp('modulo-usuarios&p=registroUsuario&id='+$element['LLAVE']+'&s=<?php print(isset($lcBuscar)?$lcBuscar:''); ?>', $element, false);
	});

	$('#documentoTipo2').tiposDocumentos();
})