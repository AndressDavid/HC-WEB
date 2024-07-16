var gPag = {
		regxpag: 250,
		totalregs: 0,
		pagina: 1,
		totalpags: 0,
		orden: 'VALFEA',
		dirorden:'DESC',
	},
	gBoton={
		PagUno: '&laquo;',
		PagAnt: '&lsaquo;',
		PagSig: '&rsaquo;',
		PagUlt: '&raquo;',
	},
	gRespuestas,
	gCampos,
	gData,
	goMobile = new MobileDetect(window.navigator.userAgent);

$(function () {
	var lcOrden = '';
	$("#txtIngreso").focus();

	// Ocultar registro del paciente
	$("#ingresoInfo").hide();

	// Controles datepicker
	$('#filtroIngreso .input-group.date').datepicker({
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

	if (goMobile.mobile()) {
		$(document).on('click', '.trSigno', function(){
			fConsultarDocumento($(this));
		});
	} else {
		$(document).on('dblclick', '.trSigno', function(){
			fConsultarDocumento($(this));
		});
	}


	// Botones de paginación
	$(".boton-pag").on('click', function(){
		if (!$(this).hasClass('disabled')) {
			switch ($(this).attr('id')) {
				case 'PagUno':
					gPag.pagina=1;
					break;
				case 'PagAnt':
					gPag.pagina=gPag.pagina>1?gPag.pagina-1:1;
					break;
				case 'PagSig':
					gPag.pagina=gPag.pagina<gPag.totalpags?gPag.pagina+1:gPag.totalpags;
					break;
				case 'PagUlt':
					gPag.pagina=gPag.totalpags;
					break;
			}
			fnConsultar();
		}
	});
	// Número de registros por página
	$("#selRegPorPag").on('change', function(){
		gPag.regxpag=$(this).val();
		fnConsultar();
	});


	// Filtro por ingreso
	if (gnIngresoSmartRoom>0) {
		$("#txtIngreso").val(gnIngresoSmartRoom).attr('disabled',true);
		$("#btnLimpiar,#btnExportar").remove();
	} else {
		$("#btnBuscar").on('click', fnConsultar);
		$("#btnLimpiar").on('click', fnLimpiar);
		$("#btnExportar").on('click', fnExpotar);
	}

	// Obtener títulos y datos de la tabla
	obtenerTitulos();
});


// Consulta datos de signos vitales
function fnConsultar()
{
	// limpiar datos en la tabla
	$("#tbodyData").html('');
	//$('#txtIngreso').focus();

	var llConsultar=true;
	if ($('#txtIngreso').val()){
		if($('#txtIngreso').val()<1 || $('#txtIngreso').val()>99999999){
			llConsultar=false;
			alert('Numero de ingreso no valido');
		}
	}

	// llamado a php para obtener datos
	if(llConsultar==true){
		$.ajax({
			type: "POST",
			url: 'vista-alerta-temprana/consultas_json.php',
			data: {
				accion:'consultar',
				ingreso:$('#txtIngreso').val(),
				fdesde:$('#txtFechaDesde').val(),
				fhasta:$('#txtFechaHasta').val(),
				pag:gPag.pagina,
				regxpag:gPag.regxpag,
				orden:gPag.orden,
				dirorden:gPag.dirorden,
			},
			dataType: "json"
		})
		.done(function(taData) {
			var lcData = '';
			var lcClasif='';
			gData=taData;
			try {
				//gPag.regxpag = taData.regxpag;
				gPag.totalregs = parseInt(taData.totalreg);
				gPag.pagina = parseInt(taData.pagina);
				gPag.totalpags = parseInt(taData.totalpag);
				gPag.orden = taData.orden;
				gPag.dirorden = taData.dirorden;
				$("#tbodyData").html(lcData);
				$.each(taData.signos, function(indicefila, fila){
					lcClasif = (fila.CLASIF.valor==''?'0':fila.CLASIF.valor);
					lcColorFila=(gRespuestas[lcClasif] ? ' class="table-'+gRespuestas[lcClasif].color : '')+' trSigno"';
					lcData += '<tr'+lcColorFila+' idSigno="'+indicefila+'" ingreso="'+fila.NIGING.valor+'" >';
					$.each(fila, function(indicecampo, campo){
						if(campo.visible=='S'){
							var lcCampo = validarDato(indicecampo, campo);
							lcData += '<td>'+lcCampo+'</td>';
						}
					});
					lcData += '</tr>';
				});
				$("#tbodyData").html(lcData);
			} catch(err) {
				console.log(err);
				alert('Error al consultar los signos');
			}
			actualizarFiltro();
			actualizarBotones();
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			alert('Se presentó un error al obtener los signos');
		});
	}

	// Muestra los datos del paciente
	if ($('#txtIngreso').val()){
		if($('#txtIngreso').val()>0 && $('#txtIngreso').val()<=99999999){
			var lnIngreso = $('#txtIngreso').val();
			$.ajax({
				type: "POST",
				url: 'vista-alerta-temprana/consultas_json.php',
				data: {
					accion:'ingreso',
					ingreso:$('#txtIngreso').val(),
				},
				dataType: "json"
			})
			.done(function(taData) {
				try {
					$('#nIngresoMostrar').text(lnIngreso);
					$('#cNombre').text(taData.cNombre);
					$('#cTipoIdMostrar').text(taData.cTipoId);
					$('#nIdMostrar').text(taData.nId);
					$('#cEdad').text(taData.cEdad);
					$('#cUbicacion').text(taData.cUbicacion);
					$('#divIconoPac').html('<i class="fas fa-'+(taData.cSexo=='F'?'female':'male')+' fa-5x"></i>');
					$('#ingresoInfo').show();
				} catch(err) {
					$('#ingresoInfo').hide();
					console.log(err);
					alert('Error al consultar ingreso');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				$('#ingresoInfo').hide();
				console.log(jqXHR.responseText);
				alert('Se presentó un error al obtener datos del ingreso');
			});

		} else {
			$('#ingresoInfo').hide();
		}
	} else {
		$('#ingresoInfo').hide();
	}
}


// Exportar datos de signos vitales
function fnExpotar()
{
	$('#txtIngreso').focus();
	var lnIngreso = $('#txtIngreso').val(),
		ldFeDesde = $('#txtFechaDesde').val(),
		ldFeHasta = $('#txtFechaHasta').val();
	if (!lnIngreso && !ldFeDesde && !ldFeHasta){
		alert('Debe indicar filtros para exportar');
		return false;
	}
	var laEnvio={
			accion:'exportar',
			ingreso:$('#txtIngreso').val(),
			fdesde:$('#txtFechaDesde').val(),
			fhasta:$('#txtFechaHasta').val(),
		};

	var loNewForm = $('<form>', {
		'action': 'nucleo/vista/alerta-temprana/consultas_json.php',
		'method': 'POST',
		'target': '_blank'
	});
	$(document.body).append(loNewForm);
	$.each(laEnvio, function(lcNombre, lcValor){
		loNewForm.append($('<input>', {'type':'hidden', 'name':lcNombre, 'value':lcValor}));
	});
	loNewForm.submit();
	loNewForm.remove();
}


// Limpiar filtros
function fnLimpiar()
{
	$('#txtIngreso, #txtFechaDesde, #txtFechaHasta').val('');
	//$('#txtIngreso').focus();
	fnConsultar();
}


// Consulta los títulos
function obtenerTitulos()
{
	$.ajax({
		type: "POST",
		url: 'vista-alerta-temprana/consultas_json.php',
		data: {accion:'titulos'},
		dataType: "json"
	})
	.done(function(taData) {
		try {
			gCampos = taData.campos;
			gRespuestas = taData.respuestas;
			var lcTitulos = '';
			$.each(taData.campos, function(indice, valor){
				if (valor.visible) {
					lcTitulos += '<th campo="'+indice+'">'+valor.titulo+'</th>';
				}
			});
			$("#trTitulos").html(lcTitulos);
			fnConsultar();
		} catch(err) {
			alert('Error al obtener los títulos');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		alert('Se presentó un error al obtener los títulos');
	});
}


//
function validarDato(tcIndice, tcCampo)
{
	var lcData, lcValor;
	//if (typeof tcCampo.dato !== 'undefined') {
	if (tcCampo.dato != null) {
		lcData = tcCampo.dato;
		lcValor = tcCampo.valor;
	} else {
		lcData = tcCampo.valor;
		lcValor = tcCampo.valor;
	}

	//if (typeof gCampos[tcIndice].alerta !== 'undefined'){
		//if (typeof gCampos[tcIndice].alerta.reglas !== 'undefined'){
	if (gCampos[tcIndice].alerta != null){
		if (gCampos[tcIndice].alerta.reglas != null){
			var loReglas=gCampos[tcIndice]['alerta']['reglas'];
			var lnPuntaje=gCampos[tcIndice]['alerta']['default'];
			$.each(loReglas, function(lnIndice, loRegla){
				if(lcValor>=loRegla['min'] && lcValor<=loRegla['max']) {
					lnPuntaje=loRegla.val;
				}
			});
			lcData = lnPuntaje>1 ? '<b>'+lcData+'</b>' : lcData;
		}
	}
	return lcData;
}


// Muestra el filtro utilizado
function actualizarFiltro()
{
	var lcFiltro = '';
	if ($("#txtIngreso").val()) {
		lcFiltro += 'Ingreso <span class="badge badge-success">' + $("#txtIngreso").val() + '</span>';
	}
	if ($("#txtFechaDesde").val() && $("#txtFechaHasta").val()) {
		lcFiltro += (lcFiltro==''?'':' y ')
			+ 'Fecha valoración entre <span class="badge badge-success">' + $("#txtFechaDesde").val()
			+ '</span> y  <span class="badge badge-success">' + $("#txtFechaHasta").val() + '</span>';
	} else {
		if ($("#txtFechaDesde").val())
			lcFiltro += (lcFiltro==''?'':' y ') + 'Fecha valoración mayor o igual a <span class="badge badge-success">' + $("#txtFechaDesde").val() + '</span>';
		if ($("#txtFechaHasta").val())
			lcFiltro += (lcFiltro==''?'':' y ') + 'Fecha valoración menor o igual a <span class="badge badge-success">' + $("#txtFechaHasta").val() + '</span>';
	}
	$("#filtroInfo").html(lcFiltro==''?'':'<b>Filtro:</b> '+lcFiltro);
}


// Activa o desactiva botones de acuerdo a la página
function actualizarBotones()
{
	activarBoton('PagUno', gPag.pagina>1);
	activarBoton('PagAnt', gPag.pagina>1);
	activarBoton('PagSig', gPag.pagina<gPag.totalpags);
	activarBoton('PagUlt', gPag.pagina<gPag.totalpags);
	$("#PagsDe").html('<span aria-hidden="true" aria-disabled="true"> Página '+gPag.pagina+' de '+gPag.totalpags+'</span>');
}
function activarBoton(tcIdBoton, tbActivar)
{
	if (tbActivar) {
		$('#'+tcIdBoton)
			.removeClass('disabled')
			.html('<span aria-hidden="true" aria-disabled="true">'+gBoton[tcIdBoton]+'</span>');
	} else {
		$('#'+tcIdBoton)
			.addClass('disabled')
			.html('<span aria-hidden="true">'+gBoton[tcIdBoton]+'</span>');
	}
}


// Mostrar Alerta temprana
function fConsultarDocumento(toFilaSel) {
	var lcCodigo = toFilaSel.attr("idsigno"),
		lnIngreso = toFilaSel.attr("ingreso"),
		laData = gData.signos[lcCodigo],
		laEnvio = {
			nIngreso	: laData.NIGING.valor,
			cTipDocPac	: '',
			nNumDocPac	: '',
			cRegMedico	: '',
			cTipoDocum	: 6000,
			cTipoProgr	: 'ALETEMP',
			tFechaHora	: laData.VALFEA.valor+' '+laData.VALHOA.valor,
			nConsecCita	: 0,
			nConsecCons	: 0,
			nConsecEvol	: 0,
			nConsecDoc	: lcCodigo,
			cCUP		: '',
			cCodVia		: '05',
			cSecHab		: laData.SECCIN.valor+'-'+laData.HABITA.valor,
		};
	//vistaPreviaPdf({datos:JSON.stringify([laEnvio])});
	oModalVistaPrevia.mostrar(laEnvio);
}
