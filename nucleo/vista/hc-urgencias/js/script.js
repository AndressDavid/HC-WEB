var goTabla=$('#tblPacientes'),
	goMobile = new MobileDetect(window.navigator.userAgent),
	gcUrlajax = "vista-hc-urgencias/ajax/ajax",
	gnLargoEnfAct = 50, // Largo que se muestra en el grid de la Enfermedad Actual
	gdFechaConsulta,
	goFila = null,
	goMenuModal = null,
	goTriage = {},
	goColorFila = {
		'1': '#f3494e',
		'2': '#ff8046',
		'3': '#cce8fd',
	},
	goColorIngreso = {
		'1': '#ff4646', //'orangered',
		'2': '#ff8046', //'orange',
		'3': '#ffff80', //'yellow',
		'4': '#80ff80', //'limegreen',
		'5': '#c0c0c0', //'skyblue',
	};
	

$(function () {
	IniciarEstados();
	IniciarTabla();
	IniciarTiposTriage();
	IniciarOpcionesMenuOpc('urgencias');
	$('#selTipDoc').tiposDocumentos({horti: "1"});
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
	oModalObservaciones.inicializar();
	$('#btnLimpiar').on('click', limpiar);
	$('#btnBuscar').on('click', buscarPacientes);
})

function limpiar() {
	goTabla.bootstrapTable('removeAll');
	$("#frmFiltros").get(0).reset();
}

function buscarPacientes() {
	$("#btnBuscar").attr("disabled", true);
	goTabla.bootstrapTable('removeAll');
	goTabla.bootstrapTable('showLoading');
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {
			accion:'pacientes',
			fecha: $("#txtFecha").val(),
			estado: $("#selEstado").val(),
			ingreso: $("#txtIngreso").val(),
			tipoId: $("#selTipDoc").val(),
			numId: $("#txtNumDoc").val(),
			seccion: ''
		},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				goTabla.bootstrapTable('refreshOptions', {data: loDatos.datos});
				gdFechaConsulta = new Date(loDatos.fechahora).getTime();

			} else {
				fnAlert(loDatos.error);
			}
			$("#btnBuscar").attr("disabled", false);

		} catch(err) {
			fnAlert('No se pudo realizar la Búsqueda de pacientes.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al Buscar de pacientes.');
	});
	triagePendientesIngresoAdmin();
}


function IniciarEstados(){
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'estadosConsulta'},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			var loSelect = $("#selEstado");
			loSelect.append('<option value=""></option>');
			if (loDatos.error == '') {
				$.each(loDatos.datos, function(lcClave, lcDescrip) {
					loSelect.append('<option value="' + lcClave + '">' + lcDescrip + '</option>');
				});

			} else {
				fnAlert(loDatos.error)
			}

		} catch(err) {
			fnAlert('No se pudo realizar la consulta de estados.')
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar estados.')
	});
}

function IniciarTiposTriage() {
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'tiposTriage'},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			goTriage = loDatos.datos;
			buscarPacientes();
		} catch(err) {
			fnAlert('No se pudo realizar la consulta de tipos de triage.')
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar tipos de triage.')
	});
}


function IniciarTabla() {
	goTabla.bootstrapTable({
		classes: 'table table-bordered table-hover table-sm table-responsive-sm', // table-striped
		theadClasses: 'thead-light',
		locale: 'es-ES',
		undefinedText: '',
		toolbar: '#toolBarLstIntrExam',
		height: '550',
		search: true,
		pagination: true,
		pageSize: 1000,
		pageList: [1000,'All'],
		detailView: true,
		detailFormatter: formatoDetalle,
		rowStyle: formatoColorFila,
		onClickRow: function(toFila, toElement, toCampo){ if(goMobile.mobile()) abrirHCUrg(toFila); },
		onDblClickRow: function(toFila, toElement, toCampo){ if(!goMobile.mobile()) abrirHCUrg(toFila); },
		columns: [
			{
				title: 'Opc.',
				align: 'center',
				events: eventoOpciones,
				formatter: '<a class="opcionesHC" href="javascript:void(0)" title="Historia Clínica"><i class="fas fa-align-justify"></i></a>'
			},
			{
				title: 'Ingreso',
				field: 'INGRESO',
				sortable: true,
				searchable: false,
				cellStyle: formatoColorIngreso
			},
			{
				title: 'Paciente',
				field: 'PACIENTE',
				sortable: true,
				formatter: formatoPaciente,
				width: 22, widthUnit: "rem"
			},
			{
				title: 'Fecha',
				field: 'FECHA_RT',
				sortable: true,
				searchable: false,
				formatter: function(tnValor, toFila) { return strNumAFecha(tnValor,'/'); }
			},
			{
				title: 'Hora',
				field: 'HORA_RT',
				sortable: true,
				searchable: false,
				formatter: function(tnValor, toFila) { return strNumAHora(tnValor); }
			},
			{
				title: 'Ubicación',
				field: 'DSCUBI',
				sortable: true
			},
			{
				title: 'Cama',
				field: 'CAMA',
				sortable: true,
				width: 5, widthUnit: "rem",
				formatter: function(tnValor, toFila) { return toFila.SECCION+'-'+toFila.HABITACION; }
			},
			{
				title: 'Triage',
				field: 'CLMTRI',
				sortable: true
			},
			{
				title: 'Enfermedad Actual',
				field: 'ENFACT',
				width: 20, widthUnit: "rem",
				formatter: function(tnValor, toFila) { return tnValor.substr(0,gnLargoEnfAct)+' ...'; }
			},
			{
				title: 'Entidad',
				field: 'DSCCON',
				width: 20, widthUnit: "rem"
			},
			{
				title: 'Desblq',
				align: 'center',
				searchable: false,
				formatter: formatoAcciones,
				events: eventosAcciones,
			},
		],
	});
}

var eventoOpciones = {
	'click .opcionesHC': function(e, tcValor, toFila, tnIndice) {
		goFila = toFila;
		verificarEpicrisis(goFila.INGRESO);
	}
}

function formatoColorIngreso(tnValor, toFila) {
	return goColorIngreso[toFila.CLMTRI]? {css: {'background-color':goColorIngreso[toFila.CLMTRI]}}: {};
}

function formatoColorFila(toFila, tnIndice) {
	var lcColor='0';

	if (toFila.ESTORD=='8') {
		var lnFeHo=0,
			lnFechaIng=parseInt(toFila.FECHA_RT),
			lnHoraIng =parseInt(toFila.HORA_RT);

		if (lnFechaIng==0 && lnHoraIng==0) {
			lnFeHo=0;
		} else {
			if (toFila.FETTRI==0 && toFila.HRTTRI==0) {
				lnFeHo=0;
			} else {
				var ldEntra = new Date(strNumAFecha(lnFechaIng)+'T'+strNumAHora(lnHoraIng)).getTime(),
					lnFeHo = (gdFechaConsulta - ldEntra) / (1000);
			}
		}

		var lnSegundos = (goTriage[toFila.CLMTRI]? parseInt(goTriage[toFila.CLMTRI]['TIEMPO']): 0) * 60;

		if (toFila.CLMTRI=='1' || (lnSegundos>0 && lnFeHo>lnSegundos)) {
			lcColor = '1';
		} else if (toFila.CLMTRI=='2') {
			lcColor = '2';
		}

	} else if (toFila.ESTORD=='3') {
		lcColor='3';
	}
	return goColorFila[lcColor]? {css: {'background-color':goColorFila[lcColor]}}: {};
}
	
function formatoPaciente(tnValor, toFila) {
	return '<i class="fa fa-'+(oGenerosPaciente.gaDatosGeneros[toFila.CODGENERO]? oGenerosPaciente.gaDatosGeneros[toFila.CODGENERO]['IMAGEN']: '')+'"></i> <b>'+toFila.PACIENTE+'</b>';
}

function formatoAcciones(tnValor, toFila) {
	return [
		'<a class="desbloquear" href="javascript:void(0)" title="Desbloquear">',
		'<i class="fa fa-unlock-alt"></i>',
		'</a>'
	].join('')
}

var eventosAcciones = {
	'click .desbloquear': function(e, tcValor, toFila, tnIndice) {
		desbloquearIngreso(toFila.INGRESO);
	}
}

function formatoDetalle(tnIndice, toFila) {
	return [
		'<table><tr>',
		'<td style="text-align: center;"><small>Triage</small><br><h3> '+toFila.CLMTRI+' </h3></td>',
		'<td style="text-align: center;"> <i class="fa fa-'+(oGenerosPaciente.gaDatosGeneros[toFila.CODGENERO]? oGenerosPaciente.gaDatosGeneros[toFila.CODGENERO]['IMAGEN']: '')+' fa-3x"></i> </td>',
		'<td>Ingreso: <b>'+toFila.INGRESO+'</b>, Paciente: <b>'+toFila.PACIENTE+'</b>, Edad: <b>'+toFila.EDAD_A+' Años</b>',
		'<br>'+toFila.ENFACT+'</td>',
		'</tr></table>'
	].join('');
}

function expandirFila(tnIndex, toFila, $toDetalle) {
	goTabla.bootstrapTable('collapseAllRows');
	goTabla.bootstrapTable('expandRow', tnIndex);
}


function desbloquearIngreso(tnIngreso) {
	fnConfirm('¿Desea desbloquear la historia del ingreso '+tnIngreso+'?', false, false, false, false,
		// botón Aceptar
		function(){
			$.ajax({
				type: "POST",
				url: gcUrlajax,
				data: { accion:'desbloquearIngreso', ingreso:tnIngreso },
				dataType: "json"
			})
			.done(function(loDatos) {
				if (loDatos.error == '') {
					fnInformation('Desbloqueo exitoso');
				} else {
					fnAlert(loDatos.error);
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.responseText);
				fnAlert('Se presentó un error al consultar tipo de usuario');
			});
		}, false
	);
}


function triagePendientesIngresoAdmin() {
	$("#divPendientesEnTriage").html("");
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'triagePendientesIngresoAdmin'},
		dataType: "json"
	})
	.done(function(loDatos) {
		if (loDatos.error == '') {
			let lnCta = loDatos.triage.length;
			let lcHtmlPT = '';
			if (lnCta>0) {
				$.each(loDatos.triage, function (lnClave, laTriage) {
					lcHtmlPT += '<span class="badge badge-warning">TRIAGE '+laTriage.TRIAGE+' = '+laTriage.REGISTROS+'</span>';
				})
				lcHtmlPT += ' | ';
			}
			lcHtmlPT += '<b>TOTAL PENDIENTES '+lnCta+'</b> | Actualizado ' + loDatos.fechahora;
			$("#divPendientesEnTriage").html(lcHtmlPT);
		} else {
			fnAlert(loDatos.error);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar pacientes en triage pendientes de ingreso administrativo');
	});
}
