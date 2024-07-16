var gcUrlajax = "vista-soportescm/ajax/planosentidad",
	goTabla=$("#tblFacturas"),
	gaTipos={},
	gaParam={},
	gcVel="fast",
	goClipBoard;

$(function () {
	iniciarListaConsultas();
	iniciarTabla();
	$('#selEntidad').on('change', changeSelEntidad);
	$('#btnLimpiar').on('click', limpiar).hide(gcVel);
	$('#btnAgregar').on('click', agregarFacturas);
	$('#btnObtener').on('click', obtenerPlanos);

	goClipBoard = new ClipboardJS('.btn-copiar');
	goClipBoard.on('success', function(e){
		fnInformation("Se copió el contenido.",'Copiar',false,false,false,false,{autoClose:'Aceptar|2500'});
		e.clearSelection();
	});
	goClipBoard.on('error', function(e){
		fnAlert("NO se logró copiar el contenido.",'Copiar',false,false,false,false,{autoClose:'Aceptar|2500'});
	});
})


function iniciarListaConsultas(){
	var lcMensaje='obtener la lista de entidades';
	var loSelect=$('#selEntidad');
	loSelect.attr('disabled',true).empty();
	gaTipos={};

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'listaConsultas'},
		dataType: "json"
	})
	.done(function(loDatos){
		try {
			if (loDatos.error==''){
				gaTipos=loDatos.lista;
				loSelect.append('<option value=""></option>');
				$.each(gaTipos, function(lcKey, loTipo) {
					loSelect.append('<option value="' + lcKey + '">' + loTipo.DSC + '</option>');
				});
				loSelect.attr('disabled',false);
			} else {
				fnAlert('<b>Función</b>: iniciarListaConsultas<br><b>Error</b>: '+loDatos.error);
			}
		} catch(err) {
			console.log(loDatos.error);
			fnAlert('No se pudo '+lcMensaje+'.<br>'+loDatos.error);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al '+lcMensaje+'.');
	});
}


function changeSelEntidad(){
	gaParam = {};
	var lcCodigo = $('#selEntidad').val();
	if(lcCodigo==''){
		limpiar(true);
	}else{
		var laTiposRes=gaTipos[$('#selEntidad').val()].RES.split('|');
		$('#selResultado').html('');
		$.each(laTiposRes, function(lnLlave, lcRes){
			$('#selResultado').append('<option value="'+lcRes+'">'+lcRes+'</option>');
		});
		$('#btnLimpiar,#divListaFac,#divObtener').show(gcVel);
		$('#txtListaFacturas').val('').focus();
	}
}


function agregarFacturas(){
	var lcLista=$('#txtListaFacturas').val().trim();
	if(lcLista.length>0){
		var laLista=[...new Set(lcLista.split(',').map(elem=>elem.trim()).filter(elem=>elem.length>0 && isNaN(elem)===false))];
		if(laLista.length==0){
			fnAlert('Factura(s) NO válida(s).');
			return;
		}
		var laFacGrid=goTabla.bootstrapTable('getData');
		if(laFacGrid.length>0){
			$.each(laFacGrid, function(lnIndex, laFila){
				var lnIndice=laLista.indexOf(laFila.FACTURA);
				if(lnIndice>=0){
					laLista.splice(lnIndice,1);
				}
			});
		}
		if(laLista.length==0){
			fnAlert('No hay Factura para consultar.');
			return;
		}
		mostrarEspera('Espere mientras se consultan las facturas.');
		$.ajax({
			type: "POST",
			url: gcUrlajax,
			data: {accion:'consultaFacturas',lista:laLista.join(',')},
			dataType: "json"
		})
		.always(ocultarEspera)
		.done(function(loDatos){
			try {
				if (loDatos.error==''){
					goTabla.bootstrapTable('append',loDatos.lista);
					$('#txtListaFacturas').val('');
				} else {
					fnAlert('<b>Función</b>: agregarFacturas<br><b>Error</b>: '+loDatos.error);
				}
			} catch(err) {
				fnAlert('No se pudo '+lcMensaje+'.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown){
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al '+lcMensaje+'.');
		});
	}
}


function obtenerPlanos(){
	var loFacturas=goTabla.bootstrapTable('getData'),
		lcMensaje='obtener datos para planos.',
		lcEnt=$("#selEntidad").val(),
		lcRes=$("#selResultado").val(),
		lcFac='', lcSep='';
	$.each(loFacturas, function(lnLlave, loFila){
		if(loFila.ERROR!=='S'){
			lcFac+=lcSep+loFila.FACTURA;
			lcSep=',';
		}
	});
	if (lcFac.length==0) {
		fnAlert('No hay facturas para consultar.');
		return;
	}
	var loData={accion:'obtenerPlanos', ent:lcEnt, res:lcRes, lst:lcFac};
	if(lcRes=='EXCEL'){
		formPostTemp(gcUrlajax, loData, true);
	}else{
		mostrarEspera('Espere mientras se consultan los datos para generar planos.');
		$.ajax({
			type: "POST",
			url: gcUrlajax,
			data: loData,
			dataType: "json"
		})
		.always(ocultarEspera)
		.done(function(loDatos){
			try {
				if(loDatos.error==''){
					fnInformation('<div class="small">'+loDatos.datos+'<div>', 'Datos obtenidos', 'fas fa-info-circle', 'blue', 'xl');
				} else {
					fnAlert('<b>Función</b>: obtenerPlanos<br><b>Error</b>: '+loDatos.error);
				}
			} catch(err) {
				fnAlert('No se pudo '+lcMensaje+'.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown){
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al '+lcMensaje+'.');
		});
	}
}


function iniciarTabla(){
	var laColumnas=[
		{
			field:'FACTURA',
			title:'Factura',
			sortable:true
		},
		{
			field:'FECHA',
			title:'Fecha'
		},
		{
			field:'INGRESO',
			title:'Ingreso'
		},
		{
			field:'DOCPAC',
			title:'Doc.Paciente'
		},
		{
			field:'PACIENTE',
			title:'Paciente'
		},
		{
			field:'CODPLAN',
			title:'Cód.Plan'
		},
		{
			field:'PLAN',
			title:'Plan'
		},
		{
			title:'Eliminar',
			align: 'center',
			events: eventoEliminarFactura,
			formatter: '<a class="eliminarFactura" href="javascript:void(0)" title="Eliminar"><i class="fas fa-trash-alt" style="color:#E96B50"></i></a>'
		}
	];
	goTabla.bootstrapTable({
		classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
		theadClasses: 'thead-dark',
		locale: 'es-ES',
		undefinedText: '',
		height: '300',
		search: false,
		pagination: true,
		pageSize: '500',
		pageList: '[10,20,50,100,500,All]',
		toolbar: "#toolbarFacturas",
		iconSize: 'sm',
		columns: laColumnas,
		rowStyle: formatoColorFila,
		//sortName: 'ERROR', // FACTURA
		//sortOrder: 'desc'
	});
}
var eventoEliminarFactura = {
	'click .eliminarFactura': function(e, tcValor, toFila, tnIndice) {
		goTabla.bootstrapTable('remove', {
			field: 'FACTURA',
			values: [toFila.FACTURA]
		});
	}
}
function formatoColorFila(toFila, tnIndice) {
	return toFila.ERROR=='S' ? {css: {'background-color':'#faa'}} : {};
}


function limpiar(tbDivRes){
	gaParam={};
	ocultarEspera();
	$('#divResultado').html('');
	$('#divObtener,#divResultado,#divListaFac,#btnLimpiar').hide(gcVel);
	$('#selEntidad').val('');
	goTabla.bootstrapTable('removeAll');
}


function mostrarEspera(tcMensaje){
	$("#spanMensajeEspera").html(tcMensaje);
	$("#divIconoEspera").show(gcVel);
}
function ocultarEspera(){
	$("#spanMensajeEspera").html('');
	$("#divIconoEspera").hide(gcVel);
}