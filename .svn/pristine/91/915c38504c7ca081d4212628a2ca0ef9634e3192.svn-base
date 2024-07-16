var goValidador, goParam, goListaEvo=[],
	aDatosIngreso = {nIngreso:0},
	gcUrlAjax = 'vista-evoconsulta/ajax/ajax.php';

$(function() {
	if (gnIngresoSmartRoom>0) {
		$("#btnLimpiar").remove();
		gnIngreso=gnIngresoSmartRoom;
	}
	paramConsulta();

	$("#Ingreso").focus();
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

	$(window).on('resize', altoMaxDivEvo);
	altoMaxDivEvo();

	$("#Ingreso").on('change', consultarIngreso);
	$("#btnBuscar").on('click', consultar);
	$("#btnLimpiar").on('click', limpiar);
	$('#btnLibroHC').on('click', abrirLibro);
	$('#btnVerPdf').on('click', verPdf);
	$('.btn-exportar').on('click', function(){
		var lcDataTipo = $(this).attr('data-tipo');
		obtenerPDF(lcDataTipo,false,lcDataTipo);
	});

	iniciarEscalas();
})


function limpiar(e) {
	try {
		e.preventDefault();
	} catch (error) {}
	$("input,select").removeClass("is-invalid").removeClass("is-valid");
	$('#divIngresoInfo,#divEvoluciones').html("");
	$("#frmFiltros").get(0).reset();
	$("#FechaDesde,#FechaHasta,#TodasFechas,#btnBuscar,#btnVerPdf,#btnLibroHC,#btnMenuExportar").attr("disabled",true);
	if(gnIngresoSmartRoom==0) $("#Ingreso").attr("disabled",false).focus();
	goListaEvo = [];
	aDatosIngreso = {nIngreso:0};
	limpiarEscalas();
}


function consultarIngreso(e) {
	var lnIngreso = $("#Ingreso").val();
	limpiar();
	$("#Ingreso").val(lnIngreso).attr("disabled",true);

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
				var lcFechaIng = strNumAFecha(loDatos.datos.nIngresoFecha),
					lcFechaEgr = strNumAFecha(loDatos.datos.nEgresoFecha),
					laFechaIng = lcFechaIng.split('-'),
					ldFechaIng = new Date(),
					ldFechaFin = new Date(),
					lcDocumento = loDatos.datos.cTipId+' '+loDatos.datos.nNumId;
				$('#divIngresoInfo').load("vista-comun/cabecera", function(){
					oCabDatosPac.bEnviarTop=false;
					oCabDatosPac.inicializar();
					$("#divCabDatosPacRow").append([
						'<div class="col-md-6 col-lg-4"><div class="form-row">',
							'<div class="col-3 col-md-4"><span class="float-left">Fecha Ingreso</span></div>',
							'<div class="col-9 col-md-8"><label>'+lcFechaIng+'</label></div>',
						'</div></div>',
						'<div class="col-md-6 col-lg-4"><div class="form-row">',
							'<div class="col-3 col-md-4"><span class="float-left">Fecha Egreso</span></div>',
							'<div class="col-9 col-md-8"><label>'+lcFechaEgr+'</label></div>',
						'</div></div>',
					].join(''));
				});
				// Obtener fecha final
				if (loDatos.datos.nEgresoFecha==0) {
					// Si no hay fecha de egreso se toma la actual
					$("#grpFechaHasta").datepicker('update', ldFechaFin);
				} else {
					// Se toma la fecha de egreso
					var laFechaFin = lcFechaEgr.split('-');
					ldFechaFin.setFullYear(parseInt(laFechaFin[0]), parseInt(laFechaFin[1])-1, parseInt(laFechaFin[2]));
					$("#grpFechaHasta").datepicker('update', lcFechaEgr);
				}

				// Fecha de inicio = la menor entre fecha de ingreso y la fecha final menos ldNumDiasAtras
				var ldFechaIni = sumarDiasFecha(ldFechaFin, -1 * goParam.diasAnt);
				ldFechaIng.setFullYear(parseInt(laFechaIng[0]), parseInt(laFechaIng[1])-1, parseInt(laFechaIng[2]));
				if (ldFechaIni < ldFechaIng) {
					ldFechaIni = ldFechaIng;
				}
				$("#grpFechaDesde").datepicker('update', ldFechaIni);

				$("#FechaDesde,#FechaHasta,#btnBuscar,#btnVerPdf,#btnLibroHC,#btnMenuExportar").attr("disabled",false);
				$("#TodasFechas").attr("disabled", goParam.diasMax>0);
				$("#toastEvo").toast("show");

				consultar();
				obtenerPDF('EVOLUCION', true, '');

				oEscalaHasbled.ConsultarEscalaHasbled();
				oEscalaChadsvas.ConsultarEscalaChadsvas();
				oEscalaCrusade.cSexo = aDatosIngreso.cSexo;
				oEscalaCrusade.ConsultarEscalaCrusade();

			} else {
				$('#divIngresoInfo').html("");
				if(gnIngresoSmartRoom==0) $("#Ingreso").attr("disabled",false).focus();
				fnAlert(loDatos.error);
			}
		})
		.fail(function(jqXHR) {
			$('#divIngresoInfo').html("");
			if(gnIngresoSmartRoom==0) $("#Ingreso").attr("disabled",false).focus();
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
	$("#Ingreso,#FechaDesde,#FechaHasta,#TodasFechas,#btnBuscar").attr("disabled",true);

	var loEnviar = $("#frmFiltros").serializeAll();
	loEnviar.accion = 'evoluciones';
	$("#divEvoluciones").html([
		'<div class="container mb-3">',
			'<div class="row justify-content-center">',
				'<div class="col-auto">',
					'<h4><span class="fas fa-circle-notch fa-xs fa-spin" style="color:#f00"></span> Consultando Evoluciones. Espere por favor</h4>',
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
			$("#divEvoluciones").html('<div class="container-fluid mt-3 mb-5 hc-body">'+loDatos.html+'</div>');
			$("#btnVerPdf,#btnLibroHC,#btnMenuExportar").attr("disabled",false);
			$("#FechaDesde,#FechaHasta,#btnBuscar").attr("disabled",false);
			$("#TodasFechas").attr("disabled", goParam.diasMax>0);

		} else {
			$('#divEvoluciones').html("");
			//fnAlert(loDatos.error);
			$("#divEvoluciones").html('<div class="container-fluid mt-3 mb-5 hc-body"><h5>'+loDatos.error+'</h5></div>');
			if(gnIngresoSmartRoom==0) $("#Ingreso").attr("disabled",false);
			$("#FechaDesde,#FechaHasta,#btnBuscar").attr("disabled",false);
			$("#TodasFechas").attr("disabled", goParam.diasMax>0);
		}
	})
	.fail(function(jqXHR) {
		$('#divEvoluciones').html("");
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar evoluciones.');
		if(gnIngresoSmartRoom==0) $("#Ingreso").attr("disabled",false);
		$("#FechaDesde,#FechaHasta,#TodasFechas,#btnBuscar").attr("disabled",false);
		$("#TodasFechas").attr("disabled", goParam.diasMax>0);
	});
}


function paramConsulta(){
	goParam = {diasAnt: 6, diasMax: 0};
	$.post(
		gcUrlAjax,
		{accion:'paramconsulta'},
		function(loDatos) {
			if (loDatos.error=="") {
				goParam = loDatos.par;
			} else {
				fnAlert(loDatos.error);
			}
			adicionarReglas();
			if (parseInt(gnIngreso)>0) {
				$("#Ingreso").val(gnIngreso);
				consultarIngreso();
			}
		},
		'json'
	)
	.fail(function(jqXHR) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar parámetros.');
	});
}


function adicionarReglas(){
	$.validator.addMethod("mayorIgualQue", function(tValor, tElemento, tParams) {
		var target = $(tParams).val();
		var isValueNumeric = !isNaN(parseFloat(tValor)) && isFinite(tValor);
		var isTargetNumeric = !isNaN(parseFloat(target)) && isFinite(target);
		if (isValueNumeric && isTargetNumeric) {
			return Number(tValor) >= Number(target);
		}
		if (!/Invalid|NaN/.test(new Date(tValor))) {
			return new Date(tValor) >= new Date(target);
		}
		return false;
	},'Debe ser mayor o igual a {0}.');

	$.validator.addMethod("maxRangoFecha", function(tValor, tElemento, tParams) {
		if (goParam.diasMax > 0) {
			var target = $(tParams).val();
			if (!/Invalid|NaN/.test(new Date(tValor))) {
				var nDiasDiff = (new Date(tValor) - new Date(target))/1000/60/60/24;
				return nDiasDiff <= goParam.diasMax;
			}
			return false;
		} else {
			return true;
		}
	}, 'El número de días a consultar no puede ser superior a ' + goParam.diasMax);

	goValidador = $("#frmFiltros").validate({
		rules: {
			Ingreso: {
				required: true,
				digits: true,
				range: [1000000,9999999]
			},
			FechaDesde: {
				required: true,
				dateISO: true,
			},
			FechaHasta: {
				required: true,
				dateISO: true,
				mayorIgualQue: "#FechaDesde",
				maxRangoFecha: "#FechaDesde",
			},
			TodasFechas: {
				required: true,
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


function verPdf(){
	// Pregunta el número de la evolución
	var lcHTML = [
		'<div id="divNumEvo" class="container-fluid">',
			'<div class="row"><div class="col">',
				'<b>Número de Evolución:<b>',
			'</div></div>',
			'<div class="row"><div class="col">',
				'<input id="numEvolucion" type="number" class="form-control form-control-sm" value="0" min="0">',
			'</div></div>',
		'</div>',
	].join('');
	fnConfirm(lcHTML, 'Ver Evolución en PDF', false, false, false, function(){
		var lnNumEvo = $("#divNumEvo #numEvolucion").val();
		if (lnNumEvo > 0) {
			var lbExiste = false, loEvolucion = {}, loEvolSel = {};
			// Valida que el ingreso tenga ese número
			$.each(goListaEvo, function(lnKey, loEvol){
				if (loEvol.nConsecEvol == lnNumEvo) {
					lbExiste = true;
					loEvolucion = {datos: JSON.stringify([loEvol])};
					loEvolSel = loEvol;
					return true;
				}
			});
			if (loEvolucion.datos) {
				// Muestra la evolución en PDF
				vistaPreviaPdf(loEvolucion, false, 'EVOLUCION '+loEvolSel.tFechaHora, 'CONSULTA_EVO');
			} else {
				$("#divNumEvo #numEvolucion").focus();
				fnAlert('¡Número de evolución no existe para el ingreso!');
				return false;
			}
		} else {
			$("#divNumEvo #numEvolucion").focus();
			fnAlert('¡Número de evolución debe ser mayor que cero!');
			return false;
		}
	}, false, {
		onContentReady: function(){
			$("#divNumEvo #numEvolucion").focus();
		}
	});
}


function obtenerPDF(tcTipo, tbConsultaEvol, tcDescDoc){
	lnIngreso =
	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'listadoc', ingreso:$("#Ingreso").val(), tipo:tcTipo},
		dataType: "json"
	})
	.done(function(loDatos) {
		if (loDatos.error=="") {
			if (loDatos.numdoc > 0) {
				if (tbConsultaEvol==true) {
					goListaEvo = loDatos.lista;
				} else {
					var loEnviar = {datos:JSON.stringify(loDatos.lista)}
					// Se abre en una ventana aparte para evitar problemas con pacientes de estancia prolongada
					fnRegMovAudDoc({
						nIngreso:loDatos.lista.nIngreso,
						cTipDocPac:loDatos.lista.cTipDocPac,
						nNumDocPac:loDatos.lista.nNumDocPac,
					}, 'EXPOPDF', 'EXPORTAR '+tcDescDoc+' EN PDF', 'CONSULTA_EVO')
					formPostTemp('nucleo/vista/documentos/vistaprevia.php', loEnviar, true);
				}
			} else {
				if (tbConsultaEvol!==true) {
					fnAlert('No existen documentos para exportar');
				}
			}
		} else {
			fnAlert(loDatos.error);
		}
	})
	.fail(function(jqXHR) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar evoluciones.');
	});
}


function abrirLibro(){
	formPostTemp('modulo-documentos', {'ingreso':$("#Ingreso").val()}, true);
}


function iniciarEscalas(){
	if (typeof oEscalaCrusade==='object') {
		oEscalaHasbled.obtenerinterpretacionesEscHas();
		oEscalaChadsvas.obtenerinterpretacionesEsChad();
		oEscalaCrusade.cargarDatosEsCrusade(0);
		$(".selectHasbled, .selectChadsvas, .selectCrusade, #lnValorHematocrito, #lnValorCreatinina").attr("disabled",true);
	}
}

function limpiarEscalas(){
	if (typeof oEscalaCrusade==='object') {
		$(".selectHasbled, .selectChadsvas, .selectCrusade").val('');
		$(".txtPuntajeHasbled, .txtPuntajeChadsvas, .puntajeCrusade").text('0');
		$("#lnValorHematocrito, #lnValorCreatinina, #lnValorCockcroft, #lnValorFreCardi, #lnValorArteSisto").val('0.0');

		$('#eshasInterpretacion, #esChadInterpretacion, #esCrusInterpretacion').text('--').removeClass('alert-warning alert-danger');
		$('#eshastotalpuntaje, #esChadTotalPuntaje, #esCrusTotalPuntaje').text('0').removeClass('alert-warning alert-danger');
	}
}

function altoMaxDivEvo(){
	var loPosDiv = $("#divEvoluciones").offset(),
		lnAltoMax = $(window).height() - $("footer").height() - loPosDiv.top - 100;
	$("#divEvoluciones").css("max-height", (lnAltoMax<400 ? 400 : lnAltoMax) + "px");
}
