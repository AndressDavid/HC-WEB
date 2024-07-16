var goValidador, goParam, goListaEvo=[],
	aDatosIngreso = {nIngreso:0},
	gotableAval = $('#tblMedicoAval'),
	gcUrlAjax = 'vista-consultaaval/ajax/ajax.php';

$(function() {
	goParam = {diasAnt: 6, diasMax: 0};
	adicionarReglas();
	if (gnIngresoSmartRoom>0) {
		gnIngreso=gnIngresoSmartRoom;
		$("#btnLimpiar").remove();
	}
	if (parseInt(gnIngreso)>0) {
		$("#Ingreso").val(gnIngreso);
		consultarIngreso();
	}

	$("#Ingreso").on('change', consultarIngreso);
	$("#btnBuscar").on('click', consultar);
	$("#btnLimpiar").on('click', limpiar);
	$('#btnLibroHC').on('click', abrirLibro);

	$("#Ingreso").focus();
})

$("#frmFiltros").on('submit', function(e){
	e.preventDefault();
});

function limpiar(e) {
	try {
		e.preventDefault();
	} catch (error) {}
	$("input,select").removeClass("is-invalid").removeClass("is-valid");
	$('#divIngresoInfo,#divAval').html("");
	$("#frmFiltros").get(0).reset();
	$("#btnBuscar,#btnVerPdf,#btnLibroHC,#btnMenuExportar").attr("disabled",true);
	if (gnIngresoSmartRoom<=0) {
		$("#Ingreso").attr("disabled",false).focus();
	}
	gotableAval.bootstrapTable('removeAll');
	goListaEvo = [];
	aDatosIngreso = {nIngreso:0};
}

function consultarIngreso(e) {
	var lnIngreso = $("#Ingreso").val();
	limpiar();
	$("#Ingreso").val(lnIngreso);

	if (goValidador.element("#Ingreso")) {
		$('#divIngresoInfo').html('<h4><span class="fas fa-circle-notch fa-xs fa-spin" style="color:#f00"></span> Consultando el ingreso, espere por favor<h4>');
		$.ajax({
			type: "POST",
			url: gcUrlAjax,
			data: {accion:'ingreso', ingreso:lnIngreso},
			dataType: "json"
		})
		.done(function(loDatos) {
			if (loDatos.error=="") {
				aDatosIngreso = loDatos.datos;
				$('#divIngresoInfo').load("vista-comun/cabecera", function(){
					oCabDatosPac.inicializar();
				});

				$("#btnBuscar,#btnVerPdf,#btnLibroHC,#btnMenuExportar").attr("disabled",false);
				consultar();
			} else {
				$('#divIngresoInfo').html("");
				fnAlert(loDatos.error);
			}
		})
		.fail(function(jqXHR) {
			$('#divIngresoInfo').html("");
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar ingreso.');
		});
	}
}

function consultar(e) {
	try {
		e.preventDefault();
	} catch (error) {}
	if (!$("#frmFiltros").valid()) {
		return false;
	}
	$("#Ingreso,#btnBuscar").attr("disabled",true);
	gotableAval.bootstrapTable('removeAll');

	var loEnviar = $("#frmFiltros").serializeAll();
	loEnviar.accion = 'Aval';
	$("#divAval").html([
		'<div class="container mb-3">',
			'<div class="row justify-content-center">',
				'<div class="col-auto">',
					'<h4><span class="fas fa-circle-notch fa-xs fa-spin" style="color:#f00"></span> Consultando HC para avalar. Espere por favor</h4>',
				'</div>',
			'</div>',
		'</div>',
	].join(''));

	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: loEnviar,
		dataType: "json"
	})
	.done(function(loDatos) {
		if (loDatos.error=="") {
			$("#divAval").html('');
			$("#btnVerPdf,#btnLibroHC,#btnMenuExportar").attr("disabled",false);
			$("#btnBuscar").attr("disabled",false);
			IniciarTabla();
			cargarEncabezado(loDatos.datos);
		} else {
			$("#divAval").html('<div class="container-fluid mt-3 mb-5 hc-body"><h5>'+loDatos.error+'</h5></div>');
		}
	})
	.fail(function(jqXHR) {
		$('#divAval').html("");
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar registros de Aval.');
	})
	.always(function(){
		if (gnIngresoSmartRoom<=0) {
			$("#Ingreso").attr("disabled",false).focus();
		}
		$("#btnBuscar").attr("disabled",false);
	});
}

function adicionarReglas(){
	goValidador = $("#frmFiltros").validate({
		rules: {
			Ingreso: {
				required: true,
				digits: true,
				range: [1000000,9999999]
			},
		},
		errorElement: "div",
		errorPlacement: function (error, element) {
			error.addClass("invalid-tooltip");
			if (element.prop("type") === "checkbox") {
				error.insertAfter(element.parent("label"));
			} else {
				error.insertAfter(element);
			}
		},
		highlight: function (element, errorClass, validClass) {
			$(element).addClass("is-invalid").removeClass("is-valid");
		},
		unhighlight: function (element, errorClass, validClass) {
			$(element).addClass("is-valid").removeClass("is-invalid");
		},
	});
}

function IniciarTabla(){
	$('#tblMedicoAval').bootstrapTable({
		classes: 'table table-striped table-sm table-responsive-sm',
		theadClasses: 'thead-light',
		locale: 'es-ES',
		undefinedText: 'N/A',
		toolbar: '#toolBarLst',
		pagination: false,
		showLoading: false,
		iconSize: 'sm',
		columns: [
			{
				title: 'Consecutivo',
				field: 'CONSEC',
				align: 'center',
				sortable: true
			},{
				title: 'Registro',
				field: 'REGIS',
				sortable: true
			},{
				title: 'Fecha',
				field: 'FECHA',
				align: 'center',
				sortable: true,
				searchable: false,
				formatter: function(tnValor, toFila) { return strNumAFecha(tnValor,'/'); }
			},{
				title: 'Hora',
				field: 'HORA',
				align: 'center',
				sortable: true,
				searchable: false,
				formatter: function(tnValor, toFila) { return strNumAHora(tnValor); }
			},{
				title: 'Usuario',
				field: 'USUARIO',
				align: 'center',
			},{
				title: 'Acción',
				field: 'ACCIONES',
				align: 'center',
				clickToSelect: false,
				formatter: formatoAcciones,
				events: eventosAcciones,
			}
		]
	});
}

function formatoAcciones(tnValor, toFila) {
	return [
		'<a class="editar" href="javascript:void(0)" title="Editar HC">',
		'<i class="fas fa-align-justify"></i>',
		'</a>'
	].join('')
}

var eventosAcciones = {
	'click .editar': function(e, tcValor, toFila, tnIndice) {
		switch (toFila.TIPO) {
			case 'HC':
				EditarHC(toFila);
				break;
			case 'EP': case 'ER': case 'EU': case 'ET':
				EditarEV(toFila);
				break;
			case 'RI':
				EditarRI(toFila);
				break;
			default:
				fnAlert('Formato no soportado, consulte con el departamento de TI en la ext 2173.')
				break;
		}
	}
}

function cargarEncabezado(taDatos){
	var aEncabezado = [];
	$.each(taDatos,function(lckey, loValor){
		aEncabezado.push(loValor);
	});
	gotableAval.bootstrapTable('append', aEncabezado);
}

function EditarHC(toFila) {
	var loEnvio = {
		ingreso: $("#Ingreso").val(),
		tipodoc: '',
		numdoc: '',
		cita: 0,
		cons: toFila.CONSEC,
		evol: 0,
		cup: '',
		via: aDatosIngreso.cCodVia,
		form: 'hos',
		Avalar: true
	};
	formPostTemp('modulo-historiaclinica', loEnvio, true);
}

function EditarEV(toFila) {
	var loEnvio = {
		cp: 'evo',
		ingreso: $("#Ingreso").val(),
		tipoev: toFila.LETRA,
		cons: toFila.CONSEC,
		Avalar: true
	};
	formPostTemp('modulo-historiaclinica', loEnvio, true);
}

function EditarRI(toFila) {
	var loEnvio = {
		cp: 'rint',
		NINORD: $("#Ingreso").val(),
		CCIORD: toFila.INDICE,
		CODCUP: toFila.CUP,
		DESPRO: toFila.DESCUP,
		RMRORD: "",
		CODORD: toFila.CODESP,
		DESESP: toFila.DSCESP,
		RMeOrd: toFila.RMEORD,
		RMROrd: "",
		lcTipo: "int",
		cons: toFila.CONSEC,
		Avalar: true,
		lsInterfaz: "aval"
	};
	formPostTemp('modulo-historiaclinica', loEnvio, true);
}

function abrirLibro(){
	formPostTemp('modulo-documentos', {'ingreso':$("#Ingreso").val()}, true);
}