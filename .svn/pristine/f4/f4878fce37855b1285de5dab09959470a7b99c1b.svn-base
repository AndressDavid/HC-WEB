// consulta.js
var gcFechaIni = '',
	gcUrlAjax = 'vista-documentos/ajax/ajax',
	goTabla = $("#tblDatos"),
	goCampos;

$(function () {
	iniciarFechas();
	getTiposDoc();
	obtenerEntidades();
	iniciarTabla();

	// No debería ocultarse para algunas personas
	// Falta crear permisos
	//$("#divEstado").show()

	$("#btnBuscar").on('click', fnConsultar);
	$("#btnLimpiar").on('click', fnLimpiar);

});


/*
 *	Inicia los controles de fecha
 */
function iniciarFechas() {
	// Controles datepicker
	$('#divFiltro .input-group.date').datepicker({
		autoclose: true,
		clearBtn: true,
		daysOfWeekHighlighted: "0,6",
		format: "yyyy-mm-dd",
		language: "es",
		todayBtn: true,
		todayHighlight: true,
		toggleActive: true,
		weekStart: 1,
	});
	gcFechaIni = $("#txtFechaDesde").val();
	$('#selFechaTipo')
		.val('egreso')
		.on('change', function(){
			if ($('#selFechaTipo').val()=='') {
				$('#divFiltro .input-group.date').datepicker('clearDates');
			} else {
				$('#divFiltro .input-group.date').datepicker('update', gcFechaIni);
			}
		});
}

/*
 *	Poblar control select de tipos de documento
 */
function getTiposDoc() {
	// adiciona opción en blanco
	$('#selTipoDoc').append('<option selected> </option>');

	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'tiposid'},
		dataType: "json"
	})
	.done(function(loTipos) {
		if (loTipos.error == '') {
			$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
				$('#selTipoDoc').append('<option value="' + lcKey + '">' + loTipo + '</option>');
			});
		} else {
			fnAlert(loTipos.error + ' ', 'Alerta', 'exclamation-triangle', 'red', 'small');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al buscar tipos de documento. ', 'Alerta', 'exclamation-triangle', 'red', 'small');
	});
}


/*
 *	Poblar control select de entidades
 */
function obtenerEntidades() {
	// adiciona opción en blanco
	$('#selCodEntidad').html('').append('<option selected> </option>');

	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'entidades'},
		dataType: "json",
	})
	.done(function(loRta) {
		if (loRta.error == ''){
			var laEntTmp = [];
			gaEntidades = loRta.data;
			// Ordenar los datos en el select por nombre
			$.each(gaEntidades, function(lcNit, lcEntidad){
				laEntTmp.push(lcEntidad+' - '+lcNit+'¤'+lcNit);
			});
			laEntTmp.sort();
			$.each(laEntTmp, function(lnKey, lcEntidadNit){
				var laSepara = lcEntidadNit.split('¤');
				$('#selCodEntidad').append('<option value="'+laSepara[1]+'">'+laSepara[0]+'</option>');
			});

		} else {
			fnAlert(loRta.error, 'Error', 'exclamation-triangle', 'red', 'small');
		}

	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Error: '+jqXHR.responseText, 'Alerta', 'exclamation-triangle', 'red', 'small');
	});
}



/*
 *	Limpiar filtros
 */
function fnLimpiar() {
	$("#txtIngreso, #selTipoDoc, #txtNumeroDoc, #selCodEntidad").val('');
	//$('#selFechaTipo').val('egreso'); $('#divFiltro .input-group.date').datepicker('update', gcFechaIni);
	$('#selFechaTipo').val(''); $('#divFiltro .input-group.date').datepicker('clearDates');
	goTabla.bootstrapTable('removeAll');
}



/*
 *	Consultar los libros ya generados
 */
function fnConsultar() {
	var fechaDesde = $("#txtFechaDesde").val(),
		fechaHasta = $("#txtFechaHasta").val(),
		fechaTipo = $("#selFechaTipo").val();

	// validar filtros
	if ($("#txtIngreso").val()=='' && $("#txtNumeroDoc").val()=='' && $("#selFechaTipo").val()=='' && $("#selCodEntidad").val()=='') {
		fnAlert('Debe seleccionar por lo menos un filtro', 'Alerta', 'exclamation-triangle', 'red', 'small');
		return false;
	}
	if (!$("#selCodEntidad").val()=='' && $("#txtIngreso").val()=='' && $("#txtNumeroDoc").val()=='' && $("#selFechaTipo").val()=='') {
		fnAlert('Debe indicar más criterios de filtro (ingreso, documento o fecha)', 'Alerta', 'exclamation-triangle', 'red', 'small');
		return false;
	}
	if (!fechaTipo=='') {
		if (fechaDesde=='') {
			fnAlert('Debe seleccionar Fecha Desde', 'Alerta', 'exclamation-triangle', 'red', 'small', function(){
				$("#txtFechaDesde").focus();
			});
			return false;
		}
		if (fechaHasta=='') {
			fnAlert('Debe seleccionar Fecha Hasta', 'Alerta', 'exclamation-triangle', 'red', 'small', function(){
				$("#txtFechaHasta").focus();
			});
			return false;
		}
		var fechaDesdeT = new Date(fechaDesde).getTime(),
			fechaHastaT = new Date(fechaHasta).getTime(),
			numDias = (fechaHastaT - fechaDesdeT) / (1000 * 60 * 60 * 24);
		if (fechaDesdeT > fechaHastaT) {
			fnAlert('Fecha Desde no puede ser mayor que Fecha Hasta', 'Alerta', 'exclamation-triangle', 'red', 'small', function(){
				$("#txtFechaDesde").focus();
			});
			return false;
		}
		if (numDias>365) {
			fnAlert('Intervalo entre fechas no puede ser mayor a un año', 'Alerta', 'exclamation-triangle', 'red', 'small', function(){
				$("#txtFechaHasta").focus();
			});
			return false;
		}
	}

	var laData = {
		accion:'listapdf',
		ingres: $("#txtIngreso").val(),
		tipdoc: $("#selTipoDoc").val(),
		numdoc: $("#txtNumeroDoc").val(),
		fectip: fechaTipo,
		fecini: fechaTipo=='' ? '' : fechaDesde,
		fecfin: fechaTipo=='' ? '' : fechaHasta,
		codent: $("#selCodEntidad").val(),
		estado: $("#selEstado").val()
	};
	goTabla.bootstrapTable('removeAll');
	goTabla.bootstrapTable('showLoading');
	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: laData,
		dataType: "json",
	})
	.done(function(loRta) {
		if (loRta.error == ''){
			goTabla.bootstrapTable('refreshOptions', {
				data: loRta.data
			});

		} else {
			$.alert(loRta.error);
		}
		goTabla.bootstrapTable('hideLoading');

	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		$.alert('Se presentó un error al consultar');
	});
}



/*
 *	Abrir el PDF seleccionado
 */
function abrirPdf(toFila)
{
	var loNewForm = $('<form>', {
		'id': 'frmPreview',
		'action': 'nucleo/vista/documentos/buscar.php',
		'method': 'POST',
		'target': '_blank'
	});
	//if (!goMobile.mobile()) { loNewForm.attr('target','_blank'); }

	$(document.body).append(loNewForm);
	loNewForm.show();
	loNewForm.append($('<input>', {'type':'text', 'name':'accion', 'value':'abrirpdf'}));
	loNewForm.append($('<input>', {'type':'text', 'name':'ingreso', 'value':toFila.INGRESO}));
	loNewForm.append($('<input>', {'type':'text', 'name':'ruta', 'value':toFila.RUTA}));
	loNewForm.append($('<input>', {'type':'text', 'name':'archivo', 'value':toFila.ARCHIVO}));
	loNewForm.submit();
	loNewForm.remove();
}



/*
 *	Iniciar tabla
 */
function iniciarTabla()
{
	goTabla.bootstrapTable({
		classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
		theadClasses: 'thead-dark',
		undefinedText: 'N/A',
		height: '600',
		showPaginationSwitch: true,
		pagination: true,
		pageSize: 20,
		pageList: '[5, 10, 20, 50, 100, 250, 500, All]',
		sortable: true,
		iconSize: 'sm',
		columns: [
			{
				field: 'INGRESO',
				title: 'Ingreso',
				sortable: true
			},
			{
				field: 'TIPDOC',
				title: 'TipDoc',
				sortable: true
			},
			{
				field: 'NUMDOC',
				title: 'Número Doc',
				sortable: true
			},
			{
				field: 'PACIENTE',
				title: 'Paciente',
				sortable: true
			},
			{
				field: 'ENTIDAD',
				title: 'Entidad',
				sortable: true
			},
			{
				field: 'ESTADO',
				title: 'Estado',
				sortable: true
			},
			{
				field: 'ACCION',
				title: 'Acción',
				formatter: 'accionFormato',
				events: 'window.accionEventos'
			}
		]
	});
}



/*  Genera campo con botones para cada fila  */
function accionFormato(tcValor, toFila, tnIndice) {
	if (toFila.ESTADO=='G') {
		return [
			'<a class="clsVerPdf" href="javascript:void(0)" title="Ver PDF">',
			'<i class="fas fa-file-pdf" style="color: #f11;"></i>',
			'</a>  '
		].join('');
	} else {
		return '';
	}
}



/*
 *	Código que se ejecuta con los botones de acción
 */
window.accionEventos = {
	'click .clsVerPdf': function (e, tcValor, toFila, tnIndice) {
		abrirPdf(toFila);
	}
}


