var goTabla = $('#tblIngresos'),
	goDatosTabla = {},
	goListaSoportes = {},
	goEstados = {},
	goVias = {},
	gbEjecutandoProceso = false,
	gnDiasAdd = 5,
	goClaseFila = {
		//'00': 'alert-light',
		'GN': 'alert-success',
		'GS': 'alert-info',
		'ER': 'alert-danger',
		'NO': 'alert-secondary',
	};

$(function () {
	iniciarTabla();
	goTabla.bootstrapTable('showLoading');
	iniciarEstadosSoportes(buscar);
	$("#btnBuscar").on('click', buscar);
	$("#btnLimpiar").on('click', limpiar);
	$('#rowFiltros .input-group.date').datepicker({
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
});


function limpiar(){
	$("#txtIngreso,#selVia,#selEntidad,#txtFacturador").val('');
	$("#selEstado").val('N');
	$("#selTipoFecha").val('factura');
	$("#txtFechaIni,#txtFechaFin").val(cFechaActual);
	buscar();
}


var eventosAcciones = {
	'click .btnGenerarInmediato': enviarGenerarInmediato,
	'click .btnGenerarIngreso': enviarGenerarIngreso,
	'click .btnPausarIngreso': enviarPausarIngreso,
	'click .btnVolverGenerar': enviarVolverIngreso,
}
var eventosAccionesSub = {
	'click .btnGenerarSoporteInmediato': enviarGenerarSoporteInmediato,
	'click .btnGenerarSoporte': enviarGenerarSoporte,
	'click .btnPausarSoporte': enviarPausarSoporte,
	'click .btnVolverGenerarSoporte': enviarVolverSoporte,
}

// Cambia la fecha de soportes para que se generen al día siguiente
function validarEjecutar() {
	if (gbEjecutandoProceso) {
		console.log('Espere por favor, hay otro proceso ejecutándose.');
		return false;
	}
	gbEjecutandoProceso = true;
	return true;
}

// Cambia la fecha de soportes para que se generen al día siguiente
function enviarGenerar(tnIngreso, tcTipoSop, taSoportes, tcEstado, tnFecha) {
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {
			accion: 'actualizarEstadoFecha',
			ingreso: tnIngreso,
			tipo: tcTipoSop,
			soportes: taSoportes,
			estado: tcEstado,
			fecha: tnFecha,
		},
		dataType: "json",
		success: function(toDatos) {
			if (toDatos.error.length == 0) {
				fnInformation('Se actualizó los soportes del ingreso '+tnIngreso);
				buscar();

			} else {
				fnAlert(toDatos.error);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al actualizar estado/fecha de generación');
			goTabla.bootstrapTable('hideLoading');
		},
		complete: function(jqXHR, textStatus) {
			gbEjecutandoProceso = false;
		}
	});
}

// Inicia procesos para generar de inmediato los soportes del ingreso
function enviarGenerarInmediato(tEvent, tcValor, toFila, tnIndice) {
	if (!validarEjecutar()) return false;
	var lcMensaje = '',
		lcTitulo = 'Soportes del ingreso '+toFila.INGRESO;
	lcMensaje = [
		'Si el ingreso tiene muchos soportes puede ser conveniente esperar que el sistema lo genere a las horas de menor impacto.',
		'¿Desea generar los soportes del ingreso <b>'+toFila.INGRESO+'</b> de inmediato?',
	].join('');
	fnConfirm(lcMensaje, lcTitulo, false, false, 'medium', function(){
		$("#tblIngresos tr[data-index='"+tnIndice+"'] .btnGenerarInmediato").html('<i class="fas fa-redo fa-spin" style="color: #b62f2f;"></i>');
		enviarGenerar(toFila.INGRESO, toFila.TIPOSOP, obtenerListaSop(toFila.INGRESO, toFila.TIPOSOP), '00', 'INMEDIATO');
	}, function(){
		gbEjecutandoProceso = false;
	});
}

// Cambia la fecha de soportes para que se generen al día siguiente
function enviarGenerarIngreso(tEvent, tcValor, toFila, tnIndice) {
	if (!validarEjecutar()) return false;
	enviarGenerar(toFila.INGRESO, toFila.TIPOSOP, obtenerListaSop(toFila.INGRESO, toFila.TIPOSOP), '00', 'HOY');
}

// Adiciona x días más al tiempo para generar los soportes
function enviarPausarIngreso(tEvent, tcValor, toFila, tnIndice) {
	if (!validarEjecutar()) return false;
	enviarGenerar(toFila.INGRESO, toFila.TIPOSOP, obtenerListaSop(toFila.INGRESO, toFila.TIPOSOP), '00', 'add'+gnDiasAdd);
}

// Vuelve a colocar el soporte para generar
function enviarVolverIngreso(tEvent, tcValor, toFila, tnIndice) {
	if (!validarEjecutar()) return false;
	var lcMensaje = '',
		lcTitulo = 'Soportes del ingreso '+toFila.INGRESO;
	lcMensaje = '¿Desea volver a programar la generación de los soportes del ingreso <b>'+toFila.INGRESO+'</b>?';
	fnConfirm(lcMensaje, lcTitulo, false, false, 'medium', function(){
		enviarGenerar(toFila.INGRESO, toFila.TIPOSOP, obtenerListaSop(toFila.INGRESO, toFila.TIPOSOP), '00', 'HOY');
	}, function(){
		gbEjecutandoProceso = false;
	});
}

// Inicia procesos para generar de inmediato el soporte
function enviarGenerarSoporteInmediato(tEvent, tcValor, toFilaSub, tnIndice) {
	if (!validarEjecutar()) return false;
	enviarGenerar(toFilaSub.INGRESO, toFilaSub.TIPOSOP, [toFilaSub.SOPORTE], '00', 'INMEDIATO');
}

// Cambia la fecha de soportes para que se generen al día siguiente
function enviarGenerarSoporte(tEvent, tcValor, toFilaSub, tnIndice) {
	if (!validarEjecutar()) return false;
	enviarGenerar(toFilaSub.INGRESO, toFilaSub.TIPOSOP, [toFilaSub.SOPORTE], '00', 'HOY');
}

// Adiciona x días más al tiempo para generar los soportes
function enviarPausarSoporte(tEvent, tcValor, toFilaSub, tnIndice) {
	if (!validarEjecutar()) return false;
	enviarGenerar(toFilaSub.INGRESO, toFilaSub.TIPOSOP, [toFilaSub.SOPORTE], '00', 'add'+gnDiasAdd);
}

// Inicia procesos para generar de inmediato el soporte
function enviarVolverSoporte(tEvent, tcValor, toFilaSub, tnIndice) {
	if (!validarEjecutar()) return false;
	var lcMensaje = '',
		lcTitulo = 'Soporte '+toFilaSub.SOPORTE+' del ingreso '+toFilaSub.INGRESO;
	lcMensaje = '¿Desea volver a programar la generación del soporte <b>'+toFilaSub.SOPORTE+'</b> del ingreso <b>'+toFilaSub.INGRESO+'</b>?';
	fnConfirm(lcMensaje, lcTitulo, false, false, 'medium', function(){
		enviarGenerar(toFilaSub.INGRESO, toFilaSub.TIPOSOP, [toFilaSub.SOPORTE], '00', 'HOY');
	}, function(){
		gbEjecutandoProceso = false;
	});
}

function formatoAcciones(lnValue, loFila, lnIndice) {
	let lcAcciones = '';
	if ($.inArray(loFila.ESTADO, ['00','ER'])==-1) {
		if (!(loFila.ESTADO=='NO')) {
			lcAcciones = '<a class="btnVolverGenerar" href="javascript:void(0)" title="Volver a Generar"><i class="fas fa-recycle" style="color: #2471A3;"></i></a> &nbsp;&nbsp; ';
		}
	} else {
		lcAcciones = [
			'<a class="btnGenerarInmediato" href="javascript:void(0)" title="Generar de Inmediato"><i class="fas fa-angle-double-right" style="color: #2471A3;"></i></a> &nbsp;&nbsp; ',
			'<a class="btnGenerarIngreso" href="javascript:void(0)" title="Generar"><i class="fas fa-play-circle" style="color: #2471A3;"></i></a> &nbsp;&nbsp; ',
			'<a class="btnPausarIngreso" href="javascript:void(0)" title="Pausar '+gnDiasAdd+' días más"><i class="fas fa-pause-circle" style="color: #C70039;"></i></a> ',
		].join('');
	}
	return lcAcciones;
}

function formatoAccionesSub(lnValue, loFilaSub, lnIndice) {
	let lcAcciones = '';
	if ($.inArray(loFilaSub.ESTADO, ['00','ER'])==-1) {
		if (!(loFilaSub.ESTADO=='NO')) {
			lcAcciones = '<a class="btnVolverGenerarSoporte" href="javascript:void(0)" title="Volver a Generar"><i class="fas fa-recycle" style="color: #2471A3;"></i></a> &nbsp;&nbsp; ';
		}
	} else {
		lcAcciones = [
			'<a class="btnGenerarSoporteInmediato" href="javascript:void(0)" title="Generar de Inmediato"><i class="fas fa-angle-double-right" style="color: #2471A3;"></i></a> &nbsp;&nbsp; ',
			'<a class="btnGenerarSoporte" href="javascript:void(0)" title="Generar"><i class="fas fa-play-circle" style="color: #2471A3;"></i></a> &nbsp;&nbsp; ',
			'<a class="btnPausarSoporte" href="javascript:void(0)" title="Pausar '+gnDiasAdd+' días más"><i class="fas fa-pause-circle" style="color: #C70039;"></i></a> ',
		].join('');
	}
	return lcAcciones;
}

function iniciarTabla() {
	goTabla.bootstrapTable({
		classes: 'table table-bordered table-hover table-sm table-responsive-sm', // table-striped
		theadClasses: 'thead-light',
		locale: 'es-ES',
		undefinedText: '',
		toolbar: '#toolBarLstIntrExam',
		height: '550',
		pagination: true,
		pageSize: 100,
		pageList: [50,100,200,500,1000,'All'],
		detailView: true,
		onExpandRow: crearSubTabla,
		rowStyle: formatoColorFila,
		columns: [
			{
				title: 'Ingreso',
				field: 'INGRESO',
				sortable: true,
				searchable: false
			},
			{
				title: 'Paciente',
				field: 'PACIENTE',
				sortable: true,
				// width: 22, widthUnit: "rem"
			},
			{
				title: 'Vía',
				field: 'VIA',
				sortable: true,
				formatter: function(tnValor, toFila) { return goVias[tnValor]; },
			},
			{
				title: 'Fecha Factura',
				field: 'FECFACT',
				sortable: true,
				searchable: false,
				formatter: function(tnValor, toFila) { return strNumAFecha(tnValor,'/'); }
			},
			{
				title: 'Entidad',
				field: 'ENTIDAD',
				sortable: false,
				searchable: false,
				formatter: function(tcValor, toFila) { return tcValor.replaceAll('\r','<br>'); }
			},
			{
				title: 'Fecha Soportes',
				field: 'FECSOP',
				sortable: true,
				searchable: false,
				formatter: function(tnValor, toFila) { return strNumAFecha(tnValor,'/'); }
			},
			{
				title: 'Facturador',
				field: 'FACTURADOR',
				sortable: true,
			},
			{
				title: 'Estado',
				field: 'ESTADO',
				sortable: true,
				formatter: function(tnValor, toFila) { return goEstados[tnValor]; },
			},
			{
				title: 'Acciones',
				align: 'center',
				searchable: false,
				formatter: formatoAcciones,
				events: eventosAcciones,
			},
		],
	});
}

function crearSubTabla(tnIndice, toFila, $toDetalle) {
	let lcClave = toFila.INGRESO+'-'+toFila.TIPOSOP;
	let $loTbl = $toDetalle.html('<table id="tbl'+lcClave+'"></table>').find('table');
	$loTbl.bootstrapTable({
		classes: 'table table-bordered table-hover table-sm table-responsive-sm',
		height: '250',
		rowStyle: formatoColorFila,
		columns: [
			{
				title: 'Soporte',
				field: 'SOPORTE',
				formatter: function(tcValor, toFilaSub) { return tcValor+' - '+goListaSoportes[toFilaSub.TIPOSOP][tcValor].TITULO; },
			},
			{
				title: 'Estado',
				field: 'ESTADO',
				formatter: function(tcValor, toFilaSub) { return goEstados[tcValor]; },
			},
			{
				title: 'Fecha Soporte',
				field: 'FECSOP',
				formatter: function(tnValor, toFilaSub) { return strNumAFecha(tnValor,'/'); },
			},
			{
				title: 'Hora Soporte',
				field: 'HORASOP',
				formatter: function(tnValor, toFilaSub) { return strNumAHora(tnValor); },
			},
			{
				title: 'Acciones',
				align: 'center',
				searchable: false,
				formatter: formatoAccionesSub,
				events: eventosAccionesSub,
			},
		],
		data: goDatosTabla[lcClave],
	});
}

function formatoColorFila(toFila, tnIndice) {
	return goClaseFila[toFila.ESTADO]? {classes: goClaseFila[toFila.ESTADO]}: {};
}

function buscar() {
	$("#btnBuscar,#btnLimpiar").attr("disabled", true);
	goTabla.bootstrapTable('removeAll');
	goTabla.bootstrapTable('showLoading');

	let oEnviar = {
		accion:'consultaSoportes',
		ingreso: $("#txtIngreso").val(),
		via: $("#selVia").val(),
		entidad: $("#selEntidad").val(),
		facturador: $("#txtFacturador").val(),
		estado: $("#selEstado").val(),
		fechatipo: $("#selTipoFecha").val(),
		fechaini: $("#txtFechaIni").val().replace(/-/g,''),
		fechafin: $("#txtFechaFin").val().replace(/-/g,''),
	}
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: oEnviar,
		dataType: "json",
		success: function(toDatos) {
			if (toDatos.error.length == 0) {
				var laDatosTabla = organizarDatos(toDatos.lista);
				goTabla.bootstrapTable('refreshOptions', {data: laDatosTabla});

			} else {
				goTabla.bootstrapTable('hideLoading');
				fnAlert(toDatos.error);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar soportes');
			goTabla.bootstrapTable('hideLoading');
		},
		complete: function(jqXHR, textStatus) {
			$("#btnBuscar,#btnLimpiar").attr("disabled", false);
		}
	});
}

function organizarDatos(taDatos) {
	let laDatosTabla = [],
		laUnicos = [],
		lnNumDatos = -1,
		lnFechaReg = 0;
	goDatosTabla = {};

	$.each(taDatos, function(tnKey, toDato){
		let lcClave = toDato.INGRESO+'-'+toDato.TIPOSOP;
		if ($.inArray(lcClave, laUnicos)<0) {
			laUnicos.push(lcClave);
			goDatosTabla[lcClave] = [];
			let loObjAdd = Object.assign({}, toDato);
			delete loObjAdd.SOPORTE;
			loObjAdd.ESTADO = 'GN';
			laDatosTabla.push(loObjAdd);
			lnNumDatos++;
		}
		goDatosTabla[lcClave].push({
			INGRESO: toDato.INGRESO,
			TIPOSOP: toDato.TIPOSOP,
			SOPORTE: toDato.SOPORTE,
			FECSOP: toDato.FECSOP,
			HORASOP: toDato.HORASOP,
			ESTADO: toDato.ESTADO,
		});
		if (!(laDatosTabla[lnNumDatos].ESTADO=='ER')) {
			switch (toDato.ESTADO) {
				case '00':
				case 'GS':
					laDatosTabla[lnNumDatos].ESTADO = '00';
					laDatosTabla[lnNumDatos].FECSOP = toDato.FECSOP;
					break;
				case 'ER':
					laDatosTabla[lnNumDatos].ESTADO = 'ER';
					laDatosTabla[lnNumDatos].FECSOP = toDato.FECSOP;
					break;
			}
		}
	});

	return laDatosTabla;
}


function obtenerListaSop(tnIngreso, tcTipoSop) {
	let laRta = [];
	$.each(goDatosTabla[tnIngreso+'-'+tcTipoSop], function(lnClave, loDato){
		if (!(loDato.ESTADO=='GN')) {
			laRta.push(loDato.SOPORTE);
		}
	});
	return laRta;
}


function iniciarEstadosSoportes(tfFuncionPost) {
	var lcMensaje = 'iniciar Listas de Estados y de Soportes de CM';
	postAjax(
		lcMensaje,
		{accion: 'listaEstadosSoportes'},
		function(taRetorno){
			try {
				goListaSoportes = taRetorno.soportes;
				goVias = taRetorno.vias;
				goEstados = taRetorno.estados;
				gnDiasAdd = taRetorno.diasAdd;

				$.each(goVias, function(lnCodVia, lcDesVia){
					$("#selVia").append($("<option />").val(lnCodVia).text(lcDesVia));
				});
				$.each(taRetorno.entidades, function(lnNitEntidad, lcDesEntidad){
					$("#selEntidad").append($("<option />").val(lnNitEntidad).text(lcDesEntidad));
				});

				if (typeof tfFuncionPost == 'function') tfFuncionPost();

			} catch(err) {
				console.log(err);
				fnAlert('No se pudo '+lcMensaje+'.');
			}
		}
	);
}

