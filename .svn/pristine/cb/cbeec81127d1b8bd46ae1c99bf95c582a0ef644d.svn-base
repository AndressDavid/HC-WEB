// Script general para las páginas de MiPres
// var lcClassLabel = '$lcClassLabel', lcClassCtrol = '$lcClassCtrol';

var gcUrlAjax = 'vista-mipres/ajax',	// Ruta para los ajax
	gcRutaMiPres = '',
	gcJsonSpanish = '',
	goTiposConsulta = [],
	goVariables = [],
	goParData = [],
	goTipos = {},
	goCtrlPut = {};

$(function() {
	fnParData();

	// Botones en la toolBar
	$('.btnToolBarDir').on('click', function() {
		window.location.href = $(this).attr('pagina');
	});
	habTooltip();

	//$('#txtFecha').datepicker({
	$('#divGET .input-group.date').datepicker({
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

	$("#selConsulta")
		.focus()
		.on('change', habilitarCtrConsulta);
	$('#btnLimpiar').on('click', fnLimpiar);
	$('#btnEnviar').on('click', consultarMiPresPUT);
	$('#aTokenTmp').on('click', function(e){
		e.preventDefault;
		fnMostrarTokenTmp('tokentmp');
	});
	$('#aTokenFacTmp').on('click', function(e){
		e.preventDefault;
		fnMostrarTokenTmp('tokenfactmp');
	});
	$('#aGenTokenTmp').on('click', function(e){
		e.preventDefault;
		fnGenerarTokenTmp('tokentmp');
	});
	$('#aGenTokenFacTmp').on('click', function(e){
		e.preventDefault;
		fnGenerarTokenTmp('tokenfactmp');
	});
	$('#aExportXlsx').on('click', function(e){
		e.preventDefault;
		fnFechasExportarXlsx();
	});

	jQuery.validator.setDefaults({
		errorElement: "div",
		errorPlacement: function(error,element){
			//error.addClass("invalid-tooltip");
			error.addClass("badge badge-pill badge-danger mt-1 mb-1");
			if (element.prop("type")==="checkbox"){
				error.insertAfter(element.parent("label"));
			} else {
				error.insertAfter(element);
			}
		},
		highlight: function(element,errorClass,validClass){
			$(element).addClass("is-invalid").removeClass("is-valid");
		},
		unhighlight: function(element,errorClass,validClass){
			$(element).addClass("is-valid").removeClass("is-invalid");
		},
	});
});


/*  Obtener datos de consulta MiPres Dispensador Proveedor  */
function consultarMiPresDP() {
	var lcNombre = goTiposConsulta[$("#selConsulta").val()].NOMBRE;
	if (lcNombre=='ANULAR') {
		consultarMiPresPUT();
		return;
	}

	fnLimpiar(false);
	if ($("#selConsulta").val()) {
		$('#divInfo').html('');
		var lbValido = true;
		$(".clsConsulta").each(function(tnIndex) {
			if ($(this).prop('disabled')==false && $(this).val()=='') {
				$(this).focus();
				infoAlert($('#divInfo'), 'Debe llenar el valor de "' + $("label[for='" + $(this).attr('id') + "']").text() + '"', 'warning', 'exclamation-triangle', false);
				lbValido = false;
			}
		});
		if (!lbValido) return false;

		$('#divResultado').show();
		$('#divIconoEspera').show();

		var laData = {
			accion	: 'mipres',
			url		: gcRutaMiPres + goTiposConsulta[$("#selConsulta").val()].RUTA,
			fecha	: $("#txtFecha").val(),
			tipDoc	: $("#selTipoDoc").val(),
			numDoc	: $("#txtNumDoc").val(),
			numPrs	: $("#txtNumPres").val(),
		};
		$.ajax({
			type: "POST",
			url: gcUrlAjax,
			data: laData,
			dataType: "json"
		})
		.done(function( loRta ) {
			$('#divResultado').hide();
			$('#divIconoEspera').hide();
			$('#divResFinal').html('');

			if (loRta.error == ''){

				var lcRes = '',
					lcInfo = '<h5>' + $('select[name="selConsulta"] option:selected').text()
						+ ( $('#txtNumPres').val() ? ' - No Prescripción: ' + $('#txtNumPres').val() : '' )
						+ ( $('#txtFecha').val() ? ' - Fecha: ' + $('#txtFecha').val() : '' )
						+ ( $('#selTipoDoc').val() ? ' - Documento: ' + $('#selTipoDoc').val() + ' ' + $('#txtNumDoc').val() : '' )
						+ '</h5>Registros Obtenidos: ' + loRta.MIPRES.length;
				$('#infoConsulta').html(lcInfo);

				if (loRta.MIPRES.length > 0) {
					// Tabla de resultados
					lcRes += '<div class="card"><div class="card-header" id="HeadTbl"><h6 class="mb-0">'
						+ '<button class="btn collapsed mr-2" type="button" data-toggle="collapse" data-target="#CardTbl" aria-expanded="false" aria-controls="CardTbl">Ver Tabla</button> '
						+ '</h6></div><div id="CardTbl" class="collapse" aria-labelledby="HeadTbl" data-parent="#divResFinal"><div class="card-body">'
						+ '<table class="table table-bordered table-sm display nowrap" id="tblTodo">';

					$.each(loRta.MIPRES, function(lnIndex, loItem) {
						var lcTblH = '', lcTbl = '';
						$.each(loItem, function(lcIndexCM, lvFldEle) {
							var lcIndice = lcIndexCM.toLowerCase();
							var lvValor = objectToString(lvFldEle);
							if ( typeof(goParData[lcIndice]) != "undefined" ) {
								if (lnIndex==0) {
									lcTblH += '<th data-toggle="tooltip" data-placement="top" title="'+goParData[lcIndice].descrip+'" class="thTblAll">'+lcIndexCM+'</th>';
								}
								lcTbl += '<td>'+lvValor+ ( ( typeof(goParData[lcIndice].valores[lvValor]) != "undefined" ) ? ' - '+goParData[lcIndice].valores[lvValor] : '' )+'</td>';
							} else {
								if (lnIndex==0) {
									lcTblH += '<th data-toggle="tooltip" data-placement="top" title="'+lcIndice+'">'+lcIndexCM+'</th>';
								}
								lcTbl += '<td>'+lvValor+'</td>';
							}
						});
						lcRes += ( lcTblH ? '<thead><tr>'+lcTblH+'</tr></thead><tbody>' : '' ) + '<tr>'+lcTbl+'</tr>';
					});
					lcRes += '</tbody></table></div></div></div>';

					// Elementos uno a uno
					lcRes += organizarMiPres(loRta.MIPRES);

					$('#divResFinal').append(lcRes);
					$('#tblTodo').DataTable( {
						scrollY: 400,
						scrollX: true,
						paging: false,
						searching: false,
						//language: { "url": "publico-complementos/datatables/1.10.18/DataTables/Spanish.json" },
						language: gcJsonSpanish,
						dom: 'Bfrtip',
						buttons: ['copy', 'csv', 'excel', 'pdf'],
					} );
					habTooltip();
				}

				$('#divResultado').show();
				$('#divResFinal').show();
			} else {
				infoAlert($('#divInfo'), loRta.error + (loRta.body.Message? ' <br>' + loRta.body.Message: '') + ' ', 'warning', 'exclamation-triangle', true);
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			infoAlert($('#divInfo'), 'Se presentó un error al consultar', 'danger', 'exclamation-triangle', true);
		});
	} else {
		infoAlert($('#divInfo'), 'Debe especificar el tipo de consulta', 'warning', 'exclamation-triangle', true);
	}
}


/*  Enviar PUT a MiPres  */
function consultarMiPresPUT() {

	$('#divInfo').html('');
	$('#divResultado').hide();
	$('#divIconoEspera').hide();
	$('#divResFinal').hide();
	$('#divResFinal').html('');

	var lnTipo = $("#selConsulta").val();
	var loTipo = goTiposConsulta[lnTipo];

	if (lnTipo && ( loTipo.NOMBRE=='ANULAR' || (loTipo.NOMBRE=='PUT' && loTipo.NUMPUT>0) ) ) {

		if (loTipo.NOMBRE!=='ANULAR') {
			if (!fnValidarPut()) return false;
		}

		$('#divResultado').show();
		$('#divIconoEspera').show();

		// Datos para enviar
		var laData = {}; // new Object();
		laData.form = {};

		var lcDatos = '{', lcComa = '';
		$(".clsEnviar").each(function(tcIndex) {
			var lcValor=$(this).val().trim(),
				lbEnviarVacio=($(this).attr('tiput')?$(this).attr('tiput'):'x').indexOf('V') > -1;
			if (lcValor!=='' || lbEnviarVacio) {
				if (lcValor=='' && lbEnviarVacio) {lcValor=' ';}
				lcDatos += lcComa + $(this).attr('vrbl') + ':"' + lcValor + '"'
				lcComa = ',';
				laData.form[$(this).attr('vrbl')] = lcValor;
			}
		});
		lcDatos += '}';

		laData.accion = 'mipresput';
		laData.url = gcRutaMiPres + goTiposConsulta[$("#selConsulta").val()].RUTA;
		laData.datos = lcDatos;
		laData.idAnula = $('#txtIdAnular').val();

		$.ajax({
			type: "POST",
			url: gcUrlAjax,
			data: laData,
			dataType: "json"
		})
		.done(function( loRta ) {
			$('#divResultado').hide();
			$('#divIconoEspera').hide();
			$('#divResFinal').html('');

			if (loRta.error == ''){
				if (loTipo.NOMBRE=='ANULAR') {
					var lcInfo = '<h5>' + $('select[name="selConsulta"] option:selected').text() + ' ' + $('#txtIdAnular').val()
								+ '</h5>Mensaje: ' + loRta.MIPRES[0].Mensaje;
					$('#infoConsulta').html(lcInfo);

				} else {
					var lcInfo = '<h5>' + $('select[name="selConsulta"] option:selected').text() + '</h5>Registros Obtenidos: ' + loRta.MIPRES.length;
					$('#infoConsulta').html(lcInfo);

					// Ver tabla de resultados
					fnTblResultados(loRta.MIPRES, '');
				}

				$('#divResultado').show();
				$('#divResFinal').show();
			} else {
				infoAlert($('#divInfo'), loRta.error + (loRta.body.Message? ' <br>' + loRta.body.Message: '') + ' ', 'warning', 'exclamation-triangle', true);
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			infoAlert($('#divInfo'), 'Se presentó un error en el envío', 'danger', 'exclamation-triangle', true);
		});
	} else {
		infoAlert($('#divInfo'), 'Debe especificar el tipo de consulta.', 'warning', 'exclamation-triangle', true);
	}
}


/*  Tabla Campos - Descripción - Valor  */
function fnCamposItem(loItem, lcIdTabla) {
	var lcRta = '<table class="table table-bordered table-sm" id="'+lcIdTabla+'">';

	$.each(loItem, function(lcIndexCM, lvFldEle) {
		var lcIndice = lcIndexCM.toLowerCase();
		var lvValor = objectToString(lvFldEle);
		if ( typeof(goParData[lcIndice]) != "undefined" ) {
			lcRta += '<tr><th>'+lcIndexCM+'</th><th>'+goParData[lcIndice].descrip+'</th><td>'+lvValor
				+ ( ( typeof(goParData[lcIndice].valores[lvValor]) != "undefined" ) ? ' - '+goParData[lcIndice].valores[lvValor] : '' )+'</td></tr>';
		} else {
			lcRta += '<tr><th>'+lcIndexCM+'</th><th>'+lcIndice+'</th><td>'+lvValor+'</td></tr>';
		}
	});
	lcRta += '</table>';
	return lcRta;
}


/*  Tabla de resultados PUT  */
function fnTblResultados(toMiPres, tcTitulo){
	var lcTitulo = (tcTitulo) ? '<h4>'+tcTitulo+'</h4>' : '';
	var lcResp = '<div class="row">'+lcTitulo+'<table class="table table-bordered table-sm">';

	$.each(toMiPres, function(lnIndex, loItem) {
		var lcTblH = '', lcTbl = '';
		$.each(loItem, function(lcIndexCM, lvFldEle) {
			var lcIndice = lcIndexCM.toLowerCase();
			var lvValor = objectToString(lvFldEle);
			if (lnIndex==0) {
				lcTblH += '<th data-toggle="tooltip" data-placement="top" title="'+goParData[lcIndice].descrip+'">'+lcIndexCM+'</th>';
			}
			lcTbl += '<td>'+lvValor+ ( ( typeof(goParData[lcIndice].valores[lvValor]) != "undefined" ) ? ' - '+goParData[lcIndice].valores[lvValor] : '' )+'</td>';
		});
		lcResp += ( lcTblH ? '<thead><tr>'+lcTblH+'</tr></thead><tbody>' : '' ) + '<tr>'+lcTbl+'</tr>';
	});
	lcResp += '</tbody></table></div>';

	$('#divResFinal').append(lcResp);
	habTooltip();
}


/*  Objeto a texto  */
function objectToString(loObject) {
	//if (typeof loObject === 'null') {
	if (loObject == undefined) {
		return '-';

	//} else if (typeof loObject === 'object' && !(loObject == undefined)) {
	} else if (typeof loObject === 'object') {
		var lcCad = JSON.stringify(loObject, undefined, 2);
		lcCad = '<pre>' + lcCad.replace(/\[|\]|"|,|}/gi,'').replace(/{\n/gi,'*') + '</pre>';
		return lcCad;
		
	} else {
		return loObject;
	}
}
	

/*  Habilita controles de acuerdo a la consulta seleccionada  */
function habilitarCtrConsulta() {
	var lnTipo = $("#selConsulta").val();
	if (lnTipo) {
		var loTipo = goTiposConsulta[lnTipo];
		var lcRuta = loTipo.RUTA;
		var ldFecha = new Date();

		fnLimpiar(false);

		// Deshabilitar todos los controles
		$(".clsConsulta").prop('disabled', true).val('');

		// Habilita controles necesarios
		if (lcRuta.indexOf("{fecha}") > -1) $("#txtFecha").prop('disabled', false).val(ldFecha.yyyymmdd());
		if (lcRuta.indexOf("{tipodoc}") > -1) $("#selTipoDoc").prop('disabled', false);
		if (lcRuta.indexOf("{numdoc}") > -1) $("#txtNumDoc").prop('disabled', false);
		if (lcRuta.indexOf("{NoPresc}") > -1) $("#txtNumPres").prop('disabled', false);
		if (lcRuta.indexOf("{IdAnular}") > -1) $("#txtIdAnular").prop('disabled', false);

		if (loTipo.NOMBRE=='PUT' && loTipo.NUMPUT>0) fnCrearFormPut(loTipo.NUMPUT);

	} else {
		fnLimpiar();
	}
}


/*  Poblar control select de tipos de consultas  */
function getTiposConsultas(tcCod) {
	// adiciona opción en blanco
	$('#selConsulta').append('<option selected value=""></option>');

	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'tipos', cod:tcCod},
		dataType: "json"
	})
	.done(function( loRta ) {
		try {
			if (loRta.error == '') {
				goTiposConsulta = loRta.TIPOS;
				$.each(goTiposConsulta, function( lnClave, loTipo ) {
					if (loTipo.CODIGO!==tcCod) {
						$('#selConsulta').append('<option value="' + lnClave + '">' + loTipo.DESCRIP + '</option>');
					}
				});
				$('#selConsulta').prop('disabled', false);
			} else {
				infoAlert($('#divInfo'), loRta.error + ' ', 'warning', 'exclamation-triangle', true);
			}
		} catch(err) {
			infoAlert($('#divInfo'), 'No se pudo realizar la busqueda de tipos de consulta. ', 'danger', 'exclamation-triangle', true);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		infoAlert($('#divInfo'), 'Se presentó un error al obtener tipos de consulta', 'danger', 'exclamation-triangle', true);
	});
}


/*  Crea formulario para acción PUT  */
function fnCrearFormPut(tcCodPut) {
	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'ctrlput', cod:tcCodPut},
		dataType: "json"
	})
	.done(function( loRta ) {
		try {
			if (loRta.error == '') {
				var lcLabel = '<label for="{nctrl}" class="'+lcClassLabel+'">{label}</label>',
					lcCtrl = '<input type="{ctrl}" class="form-control form-control-sm '+lcClassCtrol+' clsEnviar" name="{nctrl}" id="{idctrl}" vrbl="{vctrl}" placeholder="" value="{valor}" {prop}>',
					lcSel = '<select class="form-control form-control-sm '+lcClassCtrol+' clsEnviar" name="{nctrl}" id="{idctrl}" vrbl="{vctrl}" value="{valor}" {prop}>',
					lcCtrlF = '<div class="input-group input-group-sm date '+lcClassCtrol+'" style="padding: 0px;">'
						+ '<div class="input-group-prepend" style="height: 31px;"><span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span></div>'
						+ '<input type="text" class="form-control clsEnviar" name="{nctrl}" id="{idctrl}" vrbl="{vctrl}" value="{valor}" placeholder="aaaa-mm-dd" {prop}></div>',
					lcItmCtrl, lcProp;

				$('#cntPUT').append('<form id="formPUT"><form>');
				goCtrlPut = loRta.TIPOS;
				$.each(goCtrlPut, function(lnClave, loTipo) {
					if (loTipo.CODIGO!==tcCodPut) {
						lcProp = loTipo.PROP.replace(/´/g,'');
						// Tipo de control lista
						if (loTipo.TIPOD=='L') {
							lcItmCtrl = ''
									+ lcLabel.replace('{label}',loTipo.DESCRIP)
										.replace('{nctrl}',loTipo.CODIGO)
									+ lcSel.replace('{nctrl}',loTipo.CODIGO)
										.replace('{idctrl}',loTipo.CODIGO)
										.replace('{vctrl}',loTipo.VARIABLE)
										.replace('{valor}',loTipo.VALOR)
										.replace('{prop}', lcProp);
							var lcIndice = loTipo.VARIABLE.toLowerCase();
							$.each(goParData[lcIndice].valores, function( lcIdxOpc, loDscOpc ) {
								lcItmCtrl += '<option value="'+lcIdxOpc+'">'+loDscOpc+'</option>';
							});
							lcItmCtrl += '</select>';

						// Tipo fecha
						} else if (loTipo.TIPOD=='F') {
							lcItmCtrl = lcCtrlF.replace('{nctrl}',loTipo.CODIGO)
											.replace('{idctrl}',loTipo.CODIGO)
											.replace('{vctrl}',loTipo.VARIABLE)
											.replace('{valor}',loTipo.VALOR)
											.replace('{prop}',lcProp);
							lcItmCtrl = ''
									+ lcLabel.replace('{label}',loTipo.DESCRIP)
										.replace('{nctrl}',loTipo.CODIGO)
									+ lcItmCtrl;

						// Otros tipos
						} else {
							lcItmCtrl = lcCtrl.replace('{nctrl}',loTipo.CODIGO)
											.replace('{idctrl}',loTipo.CODIGO)
											.replace('{vctrl}',loTipo.VARIABLE)
											.replace('{valor}',loTipo.VALOR)
											.replace('{prop}',lcProp);
							if (loTipo.VALOR=='') {
								lcTpCtrl = loTipo.TIPOD=='A' ? 'text' : (loTipo.TIPOD=='N' ? 'number' : (loTipo.TIPOD=='D' ? 'number' : (loTipo.TIPOD=='F' ? 'date' : 'text') ) );
								lcItmCtrl = ''
										+ lcLabel.replace('{label}',loTipo.DESCRIP)
											.replace('{nctrl}',loTipo.CODIGO)
										+ lcItmCtrl.replace('{ctrl}',lcTpCtrl);

							// Si viene una valor se oculta el control y no se pone label
							} else {
								lcTpCtrl = 'hidden';
								lcItmCtrl = lcItmCtrl.replace('{ctrl}',lcTpCtrl);
							}
						}
						$('#formPUT').append('<div class="form-group row mb-0">'+lcItmCtrl+'</div>');
					}
					$('#formPUT').validate();
				});
				$('#divGET').hide();
				$('#cntPUT .input-group.date').datepicker({
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
				$('#divPUT').show();
			} else {
				infoAlert($('#divInfo'), loRta.error + ' ', 'warning', 'exclamation-triangle', true);
			}
		} catch(err) {
			console.log(err);
			infoAlert($('#divInfo'), 'Error al generar campos de formulario. ', 'danger', 'exclamation-triangle', true);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		infoAlert($('#divInfo'), 'Se presentó un error al obtener campos de formulario', 'danger', 'exclamation-triangle', true);
	});
}


/*  Llenar lista tipos de documento  */
function getTiposDoc() {
	// adiciona opción en blanco
	$('#selTipoDoc').html('').append('<option selected> </option>');
	$.each(goParData.tipoidpaciente.valores, function( lcClave, lcTipo ) {
		$('#selTipoDoc').append('<option value="' + lcClave + '">' + lcClave + ' - ' + lcTipo + '</option>');
	});
}


/*  Limpiar controles  */
function fnLimpiar(tbForm) {
	tbForm = typeof tbForm !== 'undefined' ?  tbForm : true;
	$('#divInfo').html('');
	$('#divResultado').hide();
	$('#divIconoEspera').hide();
	$('#infoConsulta').html('');
	$('#cntPUT').html('');
	$('#divPUT').hide();
	$('#divGET').show();
	$('#divResFinal').hide();
	$('#divResFinal').html('');
	if (tbForm) {
		$('#selConsulta').val('').focus();
		$('#txtFecha').prop('disabled', true).val('');
		$('#selTipoDoc').prop('disabled', true).val('');
		$('#txtNumDoc').prop('disabled', true).val('');
		$('#txtNumPres').prop('disabled', true).val('');
	}
}


/*  Validar el formulario  */
function fnValidarPut() {
	return $("#formPUT").valid();
}


/* Mostrar token temporal actual */
function fnMostrarTokenTmp(tcTipo) {
	tcTipo = typeof tcTipo !== 'undefined' ?  tcTipo : 'tokentmp';
	var lcTitulo = tcTipo=='tokentmp' ? 'Token Temporal' : 'Token de Facturación Temporal';
	$.post(gcUrlAjax, {accion: 'tkntmp', tipo: tcTipo}, function(loRta){
		try {
			if (loRta.error == '') {
				fnAlert(loRta.token, lcTitulo, '', 'blue');
			} else {
				fnAlert(loRta.error, lcTitulo, '', 'orange');
			}
		} catch(err) {
			fnAlert('Error, no se puede mostrar token temporal.', lcTitulo, 'fa fa-exclamation-triangle', 'red');
		}
	}, 'json')
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Error al consultar token temporal.', lcTitulo, 'fa fa-exclamation-triangle', 'red');
	});
}


/* Generar nuevo token temporal */
function fnGenerarTokenTmp(tcTipo) {
	tcTipo = typeof tcTipo !== 'undefined' ?  tcTipo : 'tokentmp';
	$.post(gcUrlAjax, {accion: 'gentkntmp', tipo: tcTipo}, function(loRta){
		fnAlert(loRta, 'Token Temporal', '', 'blue');
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Error al geenrar token temporal.', 'Token Temporal', 'fa fa-exclamation-triangle', 'red');
	});
}


/* Exportar a excel los datos de un periodo de tiempo */
function fnFechasExportarXlsx() {
	$.confirm({
		icon: 'fa fa-file-excel',
		title: 'Consultar Registros',
		content: 'Seleccione las fechas:<br>'
					+'<label for="txtFechaIniExpo" class="col-4">Fecha Inicio:</label>'
						+'<input type="date" id="txtFechaIniExpo" value="2019-03-01" />'
					+'<label for="txtFechaFinExpo" class="col-4">Fecha Fin:</label>'
						+'<input type="date" id="txtFechaFinExpo" value="2019-03-01" />',
		columnClass: 'medium',
		buttons: {
			ok: {
				text: 'Aceptar',
				btnClass: 'btn-blue',
				action: function(okButton) {
					var lnFecIni = $('#txtFechaIniExpo').val(),
						lnFecFin = $('#txtFechaFinExpo').val();
					lnFecIni = lnFecIni.replace('-','').replace('-','');
					lnFecFin = lnFecFin.replace('-','').replace('-','');
					if (lnFecIni>lnFecFin) {
						fnAlert("'Fecha Fin' debe ser mayor a 'Fecha Inicio'", "Consultar Registros", 'fa fa-exclamation-triangle', 'red');
						return false;
					}

					var laEnvio = {
							accion:'expoXls',
							fi:lnFecIni,
							ff:lnFecFin
						};
					infoAlertClear( $('#divInfo') );

					var loNewForm = $('<form>', {
						'action': 'nucleo/vista/mipres/ajax.php',
						'method': 'POST',
						'target': '_blank'
					});
					$(document.body).append(loNewForm);
					$.each(laEnvio, function(lcNombre, lcValor){
						loNewForm.append($('<input>', {'type':'hidden', 'name':lcNombre, 'value':lcValor}));
					});
					loNewForm.submit();
					loNewForm.remove();
				},
			},
			cancel: { text: 'Cancelar', },
		},
		closeIcon: true,
	});
}


/*  Envío PUT por ajax para condensados  */
function envioPutAjax(tcPaso, taData, tFuncion) {
	tFuncion = typeof tFuncion !== 'undefined' ?  tFuncion : false;
	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: taData,
		dataType: "json",
	})
	.done(function(loRta) {
		$('#divIconoEspera').hide();
		$('#divResultado').show();
		$('#divResFinal').show();

		if (loRta.error == ''){
			// Tabla de resultados
			fnTblResultados(loRta.MIPRES, 'Resultado '+tcPaso);
			if(tFuncion) tFuncion(loRta.MIPRES[0]);

		} else {
			infoAlert($('#divInfo'), loRta.error + (loRta.body.Message? ' <br>' + loRta.body.Message: '') + ' ', 'warning', 'exclamation-triangle', true);
		}

	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		infoAlert($('#divInfo'), 'Se presentó un error en '+tcPaso, 'danger', 'exclamation-triangle', true);
	});
}


/*  Obtener variables y valores para las consultas  */
function getVariables(tcTipo) {
	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'variables',tipo:tcTipo},
		dataType: "json"
	})
	.done(function( loRta ) {
		try {
			if (loRta.error == ''){
				goVariables = loRta.VARIABLES;
				gcRutaMiPres = loRta.URL;
			} else {
				infoAlert($('#divInfo'), loRta.error + ' ', 'warning', 'exclamation-triangle', true);
			}
		} catch(err) {
			infoAlert($('#divInfo'), 'No se pudo realizar la busqueda de tipos de consulta. ', 'danger', 'exclamation-triangle', true);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		infoAlert($('#divInfo'), 'Se presentó un error al obtener tipos de consulta', 'danger', 'exclamation-triangle', true);
	});
}


/*  Establecer formato a la fecha actual  */
Date.prototype.yyyymmdd = function(tcSepara='-') {
	var yyyy = this.getFullYear().toString();
	var mm = (this.getMonth()+1).toString();
	var dd  = this.getDate().toString();
	return yyyy + tcSepara + (mm[1]?mm:"0"+mm[0]) + tcSepara + (dd[1]?dd:"0"+dd[0]);
};


function habTooltip() {
	$('[data-toggle="tooltip"]').tooltip();
}


/*  Parámetros iniciales de variables del webService  */
function fnParData() {
	// Campos y sus propiedades
	$.get("nucleo/vista/mipres/json/ParData.json", function(data) {
		goParData = data;
		getTiposDoc();
	})
	.fail(function( jqXHR, textStatus, errorThrown ){
		console.log(jqXHR.responseText);
	});

	// Idioma español para dataTable
	$.get("publico-complementos/datatables/1.10.18/DataTables/Spanish.json", function(data) {
		gcJsonSpanish = data;
	})
	.fail(function( jqXHR, textStatus, errorThrown ){
		console.log(jqXHR.responseText);
	});
}
