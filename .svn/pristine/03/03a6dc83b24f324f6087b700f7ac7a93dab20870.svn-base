var goValidador, goParam, goListaEvo=[],
	aDatosIngreso = {nIngreso:0},
	gcUrlAjax = 'vista-infconsulta/ajax/ajax.php';

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

	oMedicamentos.consultaMedicamentos('cMedicamentoInf','cCodigoMedicamentoInf','cDescripcionMedicamentoInf','txtDosis','AN');
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

	$("#Ingreso").on('change', consultarIngreso);
	$("#btnBuscar").on('click', consultar);
	$("#btnLimpiar").on('click', limpiar);
	$('#btnLibroHC').on('click', abrirLibro);
})

function limpiar(e) {
	try {
		e.preventDefault();
	} catch (error) {}
	$("input,select").removeClass("is-invalid").removeClass("is-valid");
	$('#divIngresoInfo,#divInfectologia').html("");
	$("#frmFiltros").get(0).reset();
	$("#FechaDesde,#FechaHasta,#cMedicamentoInf,#btnBuscar,#btnVerPdf,#btnLibroHC,#btnLibroExcel,#btnMenuExportar").attr("disabled",true);
	if(gnIngresoSmartRoom==0) $("#Ingreso").attr("disabled",false).focus();
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
				var lcFechaIng = strNumAFecha(loDatos.datos.nIngresoFecha),
					lcFechaEgr = strNumAFecha(loDatos.datos.nEgresoFecha),
					laFechaIng = lcFechaIng.split('-'),
					ldFechaIng = new Date(),
					ldFechaFin = new Date(),
					lcDocumento = loDatos.datos.cTipId+' '+loDatos.datos.nNumId;
				$('#divIngresoInfo').load("vista-comun/cabecera", function(){
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
				$("#grpFechaDesde").datepicker('update', lcFechaIng);

				$("#FechaDesde,#FechaHasta,#cMedicamentoInf,#btnBuscar,#btnVerPdf,#btnLibroHC,#btnMenuExportar").attr("disabled",false);
				$("#toastEvo").toast("show");

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
	$("#Ingreso,#FechaDesde,#FechaHasta,#cMedicamentoInf,#btnBuscar").attr("disabled",true);

	var loEnviar = $("#frmFiltros").serializeAll();
	loEnviar.accion = 'Infectologia';
	$("#divInfectologia").html([
		'<div class="container mb-3">',
			'<div class="row justify-content-center">',
				'<div class="col-auto">',
					'<h4><span class="fas fa-circle-notch fa-xs fa-spin" style="color:#f00"></span> Consultando Antibioticos. Espere por favor</h4>',
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
			$("#divInfectologia").html(loDatos.html);
			$("#btnVerPdf,#btnLibroHC,#btnMenuExportar").attr("disabled",false);
			$("#FechaDesde,#FechaHasta,#cMedicamentoInf,#btnBuscar,#btnLibroExcel").attr("disabled",false);

		} else {
			$("#divInfectologia").html('<div class="container-fluid mt-3 mb-5 hc-body"><h5>'+loDatos.error+'</h5></div>');
			if(gnIngresoSmartRoom==0) $("#Ingreso").attr("disabled",false);
			$("#FechaDesde,#FechaHasta,#cMedicamentoInf,#btnBuscar").attr("disabled",false);
		}
	})
	.fail(function(jqXHR) {
		$('#divInfectologia').html("");
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar Antibioticos.');
		if(gnIngresoSmartRoom==0) $("#Ingreso").attr("disabled",false);
		$("#FechaDesde,#FechaHasta,#cMedicamentoInf,#btnBuscar").attr("disabled",false);
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

function abrirLibro(){
	formPostTemp('modulo-documentos', {'ingreso':$("#Ingreso").val()}, true);
}

function ExportToExcel( type , name ){
	let data = document.getElementById('divInfectologia');
	let excelFile = XLSX.utils.table_to_book(data, {sheet: "INFCONSULTA"});
	excelFile["Sheets"]["INFCONSULTA"]["!cols"] = [{ wpx : 149 },{ wpx : 300 }, {wpx : 150}];
	XLSX.write(excelFile, { bookType: type, bookSST: true, type: 'base64' });
	XLSX.writeFile(excelFile, `${name}.${type}`);
}