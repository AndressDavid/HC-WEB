var gcUrlAjax = 'vista-mipres/ajax',	// Ruta para los ajax
	goTabla = $("#tblDatos"),			// tabla
	gnIndex, goFila, goEntAdd=[],		// Datos de la fila seleccionada
	gaEPS = [],
	gcFechaIni,
	goOpcTipo = [],
	goEstados = ["Anulado", "Activo", "Procesado"],
	goParData = [],
	gcRutaMiPres = '',
	lcClassLabel = 'col-5', lcClassCtrol = 'col-6',
	gcInfo = '';

$(function() {
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
	$("#txtFechaDesde").on('change', function(){
		$("#txtFechaHasta").val($("#txtFechaDesde").val());
	});

	$("#btnBuscar").on('click', fnConsultar);
	$("#btnLimpiar").on('click', fnLimpiar);
	$("#btnEnviar").on('click', fnEjecutarAccion);
	$(".mp-filtro").on("change", function(){ $(this).removeClass("is-invalid") });

	$("#divContenidoPrincipal")
		.on('click', '.btnAcc', function() { fnAbrirFormAccion($(this).attr('tipoacc')); })
		.on('click', '.clsVerDoc', function() { fnVerAccion($(this).attr('tipo'), $(this).attr('numid')); })
		.on('click', '.clsAnularDoc', function() { fnAnularAccion($(this).attr('tipo'), $(this).attr('numid')); })
		.on('click', '.clsActEntCod', function() { fnActualizaEntCod($(this)); });

	obtenerParametros();
	obtenerEPS();
	iniciarTabla();

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

/*
 *	Valida los datos antes de consultar
 *	En consultas de direccionamientos y prescripciones
 */
function fnValidarConsulta() {
	var lbEsValido=true, loRE;
	$(".mp-filtro").removeClass("is-invalid");
	// Validación de fechas
	if($("#txtIngreso").val().length==0 && $("#txtPrescripcion").val().length==0 && $("#txtNumeroDoc").val().length==0){
		if($("#txtFechaDesde").val()==''){
			$("#txtFechaDesde").addClass("is-invalid");
			lbEsValido=false;
		}
		if($("#txtFechaHasta").val()==''){
			$("#txtFechaHasta").addClass("is-invalid");
			lbEsValido=false;
		}
	} else {
		if($("#txtIngreso").val().length>0){
			loRE=new RegExp(/^\d{7,8}$/);
			if(loRE.exec($("#txtIngreso").val())==null){
				$("#txtIngreso").addClass("is-invalid");
				lbEsValido=false;
			}
		}
		if($("#txtPrescripcion").val().length>0){
			loRE=new RegExp(/^\d{20}$/);
			if(loRE.exec($("#txtPrescripcion").val())==null){
				$("#txtPrescripcion").addClass("is-invalid");
				lbEsValido=false;
			}
		}
		if($("#txtNumeroDoc").val().length>0){
			loRE=new RegExp(/^\d{1,13}$/);
			if(loRE.exec($("#txtNumeroDoc").val())==null){
				$("#txtNumeroDoc").addClass("is-invalid");
				lbEsValido=false;
			}
			if($("#selTipoDoc").val().length==0){
				$("#selTipoDoc").addClass("is-invalid");
				lbEsValido=false;
			}
		}
	}
	return lbEsValido;
}

/*
 *  Obtener variables y valores para las consultas
 *
 *	@param string tcTipo: 1=URL Prescripciones, 2=URL Dispensador Proveedor, 3=URL Facturación
 */
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
				fnAlert(loRta.error + ' ', 'Error', 'exclamation-triangle', 'red', 'small');
			}
		} catch(err) {
			fnAlert('No se pudo realizar la busqueda de tipos de consulta. ', 'Error', 'exclamation-triangle', 'red', 'small');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al obtener tipos de consulta. ', 'Error', 'exclamation-triangle', 'red', 'small');
	});
}



/*
 *  Obtener URL para consultas MiPres
 *
 *	@param string tcTipo: 1=URL Prescripciones, 2=URL Dispensador Proveedor, 3=URL Facturación
 */
function getUrlMiPres(tcTipo, tfPost) {
	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'urlmipres',tipo:tcTipo},
		dataType: "json"
	})
	.done(function( loRta ) {
		try {
			gcRutaMiPres = loRta;
			if (typeof tfPost === 'function') tfPost();
		} catch(err) {
			fnAlert('No se pudo realizar la busqueda de tipos de consulta. ', 'Error', 'exclamation-triangle', 'red', 'small');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al obtener tipos de consulta. ', 'Error', 'exclamation-triangle', 'red', 'small');
	});
}



/*
 *	Crea formulario para acción PUT
 *
 *	@param string tcCodPut: Código de la acción PUT para obtener los controles
 *	@param function tFunPost: Función que se ejecuta si los controles pueden ser dibujados
 */
function fnCrearFormPut(tcCodPut, tFunPost) {
	$('#cntPUT').html('');
	$('#divInfo').html('');
	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'ctrlput', cod:tcCodPut},
		dataType: "json"
	})
	.done(function( loRta ) {
		try {
			if (loRta.error == '') {
				var lcLabel = '<label for="{nctrl}" class="'+lcClassLabel+'"><b>{label}</b></label>',
					lcCtrl = '<input type="{ctrl}" class="form-control form-control-sm '+lcClassCtrol+' clsEnviar" name="{nctrl}" id="{idctrl}" vrbl="{vctrl}" placeholder="" value="{valor}" {prop}>',
					lcSel = '<select class="form-control form-control-sm '+lcClassCtrol+' clsEnviar" name="{nctrl}" id="{idctrl}" vrbl="{vctrl}" value="{valor}" {prop}>',
					lcCtrlF = '<div class="input-group input-group-sm date '+lcClassCtrol+'" style="padding: 0px;">'
						+ '<div class="input-group-prepend" style="height: 31px;"><span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span></div>'
						+ '<input type="text" class="form-control clsEnviar" name="{nctrl}" id="{idctrl}" vrbl="{vctrl}" value="{valor}" placeholder="aaaa-mm-dd" {prop}></div>',
					lcItmCtrl, lcProp;

				$('#cntPUT').append('<form id="formPUT"><form>');
				goCtrlPut = loRta.TIPOS;
				$.each(goCtrlPut, function( lnClave, loTipo ) {
					if (loTipo.CODIGO!==tcCodPut) {
						lcProp = loTipo.PROP.replace(/´/g,'');
						// Tipo de control lista
						if (loTipo.TIPOD=='L') {
							lcItmCtrl = '<div class="form-group row mb-0" id="div'+loTipo.CODIGO+'">'
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
							lcItmCtrl += '</select></div>';

						// Tipo fecha
						} else if (loTipo.TIPOD=='F') {
							lcItmCtrl = lcCtrlF.replace('{nctrl}',loTipo.CODIGO)
											.replace('{idctrl}',loTipo.CODIGO)
											.replace('{vctrl}',loTipo.VARIABLE)
											.replace('{valor}',loTipo.VALOR)
											.replace('{prop}',lcProp);
							lcItmCtrl = '<div class="form-group row mb-0" id="div'+loTipo.CODIGO+'">'
									+ lcLabel.replace('{label}',loTipo.DESCRIP)
										.replace('{nctrl}',loTipo.CODIGO)
									+ lcItmCtrl + '</div>';

						// Otros tipos
						} else {
							lcItmCtrl = lcCtrl.replace('{nctrl}',loTipo.CODIGO)
											.replace('{idctrl}',loTipo.CODIGO)
											.replace('{vctrl}',loTipo.VARIABLE)
											.replace('{valor}',loTipo.VALOR)
											.replace('{prop}',lcProp);
							if (loTipo.VALOR=='') {
								lcTpCtrl = loTipo.TIPOD=='A' ? 'text' : (loTipo.TIPOD=='N' ? 'number' : (loTipo.TIPOD=='D' ? 'number' : (loTipo.TIPOD=='F' ? 'date' : 'text') ) );
								lcItmCtrl = '<div class="form-group row mb-0" id="div'+loTipo.CODIGO+'">'
										+ lcLabel.replace('{label}',loTipo.DESCRIP)
											.replace('{nctrl}',loTipo.CODIGO)
										+ lcItmCtrl.replace('{ctrl}',lcTpCtrl)
										+ '</div>';

							// Si viene una valor se oculta el control y no se pone label
							} else {
								lcTpCtrl = 'hidden';
								lcItmCtrl = '<div class="form-group row mb-0" id="div'+loTipo.CODIGO+'">'
										+ lcItmCtrl.replace('{ctrl}',lcTpCtrl)
										+ '</div>';
							}
						}
						$('#formPUT').append(lcItmCtrl);
					}
					$('#formPUT').validate();
				});
				$('#divPUT .input-group.date').datepicker({
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
				if (typeof tFunPost === 'function') tFunPost();

			} else {
				fnAlert(loRta.error + ' ', 'Error', 'exclamation-triangle', 'red', 'small');
			}
		} catch(err) {
			console.log(err);
			fnAlert('Error al generar campos de formulario. ', 'Error', 'exclamation-triangle', 'red', 'small');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al obtener campos de formulario', 'Error', 'exclamation-triangle', 'red', 'small');
	});
}



/*
 *  Enviar PUT a MiPres
 *
 *	@param object toOpcTipo: opciones de acuerdo al tipo a ejecutar
 *	@param string tcAnula: id que se va a anular
 *	@param string tfCorrecto: función que se ejecuta cuando el envío es correcto
 *	@param string tfError: función que se ejecuta cuando hay error en el envío
 */
function enviarMiPresPUT(toOpcTipo, tcAnula, tfCorrecto, tfError) {

	// Datos para enviar
	var laData = {}; // new Object();
	laData.form = {};
	if ( $(".clsEnviar").length>0 ) {
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
	} else {
		var lcDatos = '{}';
	}
	laData.accion = 'mipresput';
	laData.url = gcRutaMiPres + toOpcTipo.RUTA;
	laData.datos = lcDatos;
	laData.idAnula = tcAnula;
	if (typeof goFila === 'object') laData.fila = goFila;
	delete laData.fila.CODENTADD;

	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: laData,
		dataType: "json"
	})
	.done(function( loRta ) {
		if (loRta.error == ''){
			if ('string' === typeof loRta.error_bd)
				if (loRta.error_bd !== '')
					fnAlert(loRta.error_bd, 'Error', 'exclamation-triangle', 'red', 'small');
			if ('function' === typeof tfCorrecto)
				tfCorrecto(loRta.MIPRES);

		} else {
			var lcError = loRta.error
			try {
				lcError = loRta.error + (loRta.body.Message? ' <br>' + loRta.body.Message: '') + ' ';
			} catch (error) {
				// error
			}
			if ('function' === typeof tfError)
				tfError(lcError);
			else
				fnAlert(lcError, 'Error', 'exclamation-triangle', 'red', 'small');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		if ('function' === typeof tfError)
			tfError(errorThrown);
		else
			fnAlert('Se presentó un error en el envío', 'Error', 'exclamation-triangle', 'red', 'small');
	});
}



/*
 *  Envío PUT por ajax para condensados
 */
function envioPutAjax(tcPaso, taData, tFuncion) {

	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: taData,
		dataType: "json",
	})
	.done(function(loRta) {
		if (loRta.error == ''){
			if(tFuncion) {tFuncion(loRta.MIPRES[0]);}

		} else {
			fnAlert(loRta.error + (loRta.body.Message? ' <br>' + loRta.body.Message: '') + ' ', 'Error', 'exclamation-triangle', 'red', 'small');
		}

	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error en '+tcPaso, 'Error', 'exclamation-triangle', 'red', 'small');
	});
}



/*
 *	Establecer formato a la fecha actual
 */
Date.prototype.yyyymmdd = function(tcSepara) {
	tcSepara = tcSepara? tcSepara: '-';
	var yyyy = this.getFullYear().toString();
	var mm = (this.getMonth()+1).toString();
	var dd  = this.getDate().toString();
	return yyyy + tcSepara + (mm[1]?mm:"0"+mm[0]) + tcSepara + (dd[1]?dd:"0"+dd[0]);
};



/*
 *  Crea tabla de dos columnas propiedad - valor
 *
 *	@param string lcClases: clases de la tabla
 *	@param array laDatos: matriz con los datos (titulo - valor)
 *	@param string laTitulos: títulos que deben aparecer (titulo - valor)
 *	@param string laPropCol: propiedades de las columnas (titulo - valor)
 */
function crearTabla(lcClases, laDatos, laTitulos, laPropCol) {
	var loTabla = $('<table class="table '+lcClases+'" />'),
		lbProp = false;
	if (laTitulos) {
		var lotHead = $('<thead />').appendTo(loTabla);
		if (!laPropCol)
			laPropCol = {titulo:'', valor:''};
		$('<tr><th '+laPropCol.titulo+'>'+laTitulos.titulo+'</b></th><th '+laPropCol.valor+'>'+laTitulos.valor+'</th></tr>').appendTo(lotHead);
	} else {
		lbProp = laPropCol ? true : false;
	}
	var lotBody = $('<tbody />').appendTo(loTabla);
	$.each(laDatos, function(lnIndice, lcData) {
		if (lbProp)
			$('<tr><td '+laPropCol.titulo+'><b>'+lcData.titulo+'</b></td><td '+laPropCol.valor+'>'+lcData.valor+'</td></tr>').appendTo(lotBody);
		else {
			$('<tr><td><b>'+lcData.titulo+'</b></td><td>'+lcData.valor+'</td></tr>').appendTo(lotBody);
		}
	});
	return loTabla
}



/*
 *	Poblar control select de tipos de documento
 */
function getTiposDoc() {
	// adiciona opción en blanco
	$('#selTipoDoc').html('').append('<option selected> </option>');
	$.each(goParData.tipoidpaciente.valores, function( lcClave, lcTipo ) {
		//$('#selTipoDoc').append('<option value="' + lcClave + '">' + lcClave + ' - ' + lcTipo + '</option>');
		$('#selTipoDoc').append('<option value="' + lcClave + '">' + lcClave + '</option>');
	});
}



/*
 *	Poblar control select de EPS
 */
function obtenerEPS() {
	// adiciona opción en blanco
	$('#selCodEps').html('').append('<option selected> </option>');

	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'eps'},
		dataType: "json",
	})
	.done(function(loRta) {
		if (loRta.error == ''){
			gaEPS = loRta.data;
			$.each(gaEPS, function(lcIndex, laEps){
				$('#selCodEps').append('<option value="'+lcIndex+'">'+lcIndex+' - '+laEps+'</option>');
			});

		} else {
			fnAlert(loRta.error, 'Error', 'exclamation-triangle', 'red', 'small');
		}

	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Error: '+jqXHR.responseText, 'Error', 'exclamation-triangle', 'red', 'small');
	});
}



/*
 *	Parámetros iniciales
 */
function obtenerParametros() {
	// Campos y sus propiedades
	$.get("vista-mipres/json/ParData.json", function(data) {
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



/*
 *	Ejecuta la acción
 */
function fnEjecutarAccion() {
	//$("#divPUT").hide();
	$("#divInfo").html('<div class="row justify-content-center"><div class="fa-3x" style="color: #17A2B8;"><i class="fas fa-circle-notch fa-spin"></i></div></div>');
	gcInfo = '';

	if (!$("#formPUT").valid()){
		$("#divInfo").html('');
		return false;
	}

	// Entrega o Entrega Ambito + Entrega Codigo
	if (goOpcTipo.NUMPUT=='41' || goOpcTipo.NUMPUT=='42') {

		var laData = {}; // new Object();
		laData.form = {};
		if ( $(".clsEnviar").length>0 ) {
			var lcDatos = '{', lcComa = '';
			$(".clsEnviar").each(function(tcIndex) {
				var lcValor=$(this).val().trim(),
					lbEnviarVacio=($(this).attr('tiput')?$(this).attr('tiput'):'x').indexOf('V') > -1;
				if (lcValor!=='' || lbEnviarVacio) {
					var lcVar = $(this).attr('vrbl');
					if (lcValor=='' && lbEnviarVacio) {
						let lnCausaNoEntrega = parseInt($("#94000007").val());
						lcValor = (lcVar=='CodSerTecEntregado' && $.inArray(lnCausaNoEntrega, [0, 11, 12, 13])==-1) ? '' : ' ';
					}
					lcDatos += lcComa + lcVar + ':"' + lcValor + '"'
					lcComa = ',';
					laData.form[lcVar] = lcValor;
				}
				if (typeof goFila === 'object') laData.fila = goFila;
			});
			lcDatos += '}';
		} else {
			var lcDatos = '{}';
		}

		laData.accion = 'mipresput';
		laData.url = gcRutaMiPres + goOpcTipo.RUTA;
		laData.datos = lcDatos;
		laData.idAnula = '0';

		// Datos para enviar
		var laDataE = {}, laDataA = {};
		laDataE.form = {};
		laDataA.form = {};
		if (typeof goFila === 'object') laDataE.fila = goFila;

		var lcDatosE = '{', lcComaE = '',
			lcDatosA = '{', lcComaA = '',
			lcRutaE = goOpcTipo.NUMPUT=='41'?'Entrega/{nit}/{tokentmp}':'EntregaAmbito/{nit}/{tokentmp}',
			lcRutaA = 'EntregaCodigos/{nit}/{tokentmp}',
			lnNumA=0;
		$(".clsEnviar").each(function(tcIndex) {
			var lcValor=$(this).val().trim(),
				lbEnviarVacio=($(this).attr('tiput')?$(this).attr('tiput'):'x').indexOf('V') > -1;
			if (lcValor!=='' || lbEnviarVacio) {
				var lcVar = $(this).attr('vrbl');
				if (lcValor=='' && lbEnviarVacio) {
					let lnCausaNoEntrega = parseInt($("#94000007").val());
					lcValor = (lcVar == 'CodSerTecEntregado' && $.inArray(lnCausaNoEntrega, [0, 11, 12, 13])==-1) ? '' : ' ';
				}
				if ($(this).attr('tiput').indexOf('E') > -1) {
					laDataE.form[$(this).attr('vrbl')] = lcValor;
					lcDatosE += lcComaE + $(this).attr('vrbl') + ':"' + lcValor + '"'
					lcComaE = ',';
				}
				if ($(this).attr('tiput').indexOf('A') > -1) {
					laDataA.form[$(this).attr('vrbl')] = lcValor;
					lcDatosA += lcComaA + $(this).attr('vrbl') + ':"' + lcValor + '"'
					lcComaA = ',';
					if ( lcValor ) lnNumA++;
				}
			}
		});
		lcDatosE += '}';

		laDataE.accion	= 'mipresput';
		laDataE.url		= gcRutaMiPres + lcRutaE;
		laDataE.datos	= lcDatosE;

		// ------- ENTREGA ------- //
		var lcEntrega = goOpcTipo.NUMPUT=='41' ? 'Entrega' : 'Entrega Ámbito';
		envioPutAjax(lcEntrega, laDataE, function(toRtaE){
			// Obtener identificadores
			var lcId = toRtaE.Id;
				lcIdEntrega = toRtaE.IdEntrega;

			if (lnNumA==3){
				// ------- ENTREGA CÓDIGO ------- //
				lcDatosA += ',IdEntrega:"'+lcIdEntrega+'"}';
				laDataA.form.IdEntrega = lcIdEntrega;
				if (typeof goFila === 'object') {
					goFila.IDENTREGA = lcIdEntrega;
					laDataA.fila = goFila;
				}
				laDataA.accion	= 'mipresput';
				laDataA.url		= gcRutaMiPres + lcRutaA;
				laDataA.datos	= lcDatosA;

				envioPutAjax('Entrega Código', laDataA, function(toRtaA){
					gcInfo = '<h5>' + goOpcTipo.DESCRIP + '</h5>'
							+ '* Entrega Ambito: ' + JSON.stringify(toRtaE) + '<br />'
							+ '* Entrega Código: ' + JSON.stringify(toRtaA);
					fnAlert(gcInfo , 'Resultado', '', 'blue', 'large');
					$('#divFormPUT').modal('hide');
					$('#cntPUT').html('');
					actualizarRegistro();
				});

			} else {
				var lcMsg = JSON.stringify(toRtaE);
				gcInfo = '<h5>' + goOpcTipo.DESCRIP + '</h5>' + lcMsg.replace(/\[/gi, '').replace(/\]/gi, '').replace(/\{/gi, '').replace(/\}/gi, '').replace(/\"/gi, '').replace(/,/gi, '<br>').replace(/:/gi, ': ');
				fnAlert(gcInfo , 'Resultado', '', 'blue', 'large');
				$('#divFormPUT').modal('hide');
				$('#cntPUT').html('');
				actualizarRegistro();
			}
		});


	// Programación, Reporte de entrega y facturación
	} else {
		enviarMiPresPUT(goOpcTipo, '',
			function(loRta){
				var lcMsg = JSON.stringify(loRta);
				gcInfo = '<h5>' + goOpcTipo.DESCRIP + '</h5>' + lcMsg.replace(/\[/gi, '').replace(/\]/gi, '').replace(/\{/gi, '').replace(/\}/gi, '').replace(/\"/gi, '').replace(/,/gi, '<br>').replace(/:/gi, ': ');
				fnAlert(gcInfo , 'Resultado', '', 'blue', 'large');
				$('#divFormPUT').modal('hide');
				$('#cntPUT').html('');
				actualizarRegistro();
			},
			function(tcError){
				fnAlert(tcError, 'Error', 'fas fa-warning', 'red');
				$("#divPUT").show();
				$("#divInfo").html('');
			}
		);
	}
}



/*
 *	Anular la acción realizada
 */
function fnAnularAccion(tcTipo, tnId) {
	gcInfo = '';

	$.confirm({
		title: 'Anular',
		content: '¿Desea anular la '+tcTipo+' número '+tnId+'?',
		columnClass: 'medium',
		buttons: {
			aceptar:{
				text: 'Aceptar',
				btnClass: 'btn-blue',
				action: function(){

					$('#divFormPUT .modal-title').text('Anulando '+tcTipo+' '+tnId);
					$('#divFormPUT #cntPUT').html('');
					$('#divFormPUT #divInfo').html('<div class="row justify-content-center"><div class="fa-3x" style="color: #17A2B8;"><i class="fas fa-circle-notch fa-spin"></i></div></div>');
					$('#divFormPUT .modal-footer').hide();
					$("#divFormPUT").modal('show');

					// Opciones de anulación
					switch(tcTipo){
						case 'Programa': lcTipoAcc='22000005'; break;
						case 'Entrega': lcTipoAcc='23000006'; break;
						case 'Reporte': lcTipoAcc='24000005'; break;
						case 'Factura': lcTipoAcc='25000004'; break;
						case 'EntregaCodigo': lcTipoAcc='23000007'; break;
					}
					$.ajax({
						type: "POST",
						url: gcUrlAjax,
						data: {accion:'opctipo', cod:lcTipoAcc},
						dataType: "json",
					})
					.done(function(loRta) {
						if (loRta.error == ''){
							goOpcTipo = loRta.OPCIONES;
							var lcTipoUrl = tcTipo=='Factura' ? 3 : 2;
							getUrlMiPres(lcTipoUrl, function(){
								var lcIdAnula = tcTipo=='Programa'?goFila.IDPROGRAMA:(tcTipo=='Entrega'?goFila.IDENTREGA:(tcTipo=='Reporte'?goFila.IDREPORTE:(tcTipo=='Factura'?goFila.IDFACTURA:'')));
								switch(tcTipo){
									case 'Programa': lcIdAnula=goFila.IDPROGRAMA; break;
									case 'Entrega': lcIdAnula=goFila.IDENTREGA; break;
									case 'Reporte': lcIdAnula=goFila.IDREPORTE; break;
									case 'Factura': lcIdAnula=goFila.IDFACTURA; break;
									case 'EntregaCodigo': lcIdAnula=tnId; break;
								}
								enviarMiPresPUT(goOpcTipo, lcIdAnula,
									function(loRta){
										$("#divFormPUT").modal('hide');
										$('#divFormPUT .modal-footer').show();
										var lcMsg = JSON.stringify(loRta);
										gcInfo = '<h5>' + goOpcTipo.DESCRIP + '</h5>' + lcMsg.replace(/\[/gi, '').replace(/\]/gi, '').replace(/\{/gi, '').replace(/\}/gi, '').replace(/\"/gi, '').replace(/,/gi, '<br>').replace(/:/gi, ': ');
										fnAlert(gcInfo , 'Resultado', '', 'blue', 'large');
										actualizarRegistro();

										// SI ES FACTURA, DEBE CAMBIAR ESTADO EN RECEPCION RADICACION

									},
									function(tcError){
										$("#divFormPUT").modal('hide');
										$('#divFormPUT .modal-footer').show();
										fnAlert(tcError, 'Error', 'fas fa-warning', 'red');
										$("#divPUT").show();
									}
								);
							});

						} else {
							console.log(loRta.error);
						}
					})
					.fail(function(jqXHR, textStatus, errorThrown) {
						console.log(jqXHR.responseText);
					});
				}
			},
			cancelar:{
				text: 'Cancelar',
				action: function(){
					fnAlert('Acción Cancelada', '', 'exclamation-triangle', 'red', 'small');
					$("#divPUT").show();
				}
			}
		}
	});
}



/*
 *	Ver propiedades de la acción
 */
function fnVerAccion(tcTipo, tnId) {
	var lcClass = 'table-bordered table-hover table-striped table-sm table-responsive-sm';

	if (tcTipo=='EntregaCodigo') {
		$('#divAcciones .modal-title').text(tcTipo+' '+goEntAdd[tnId].IDEntregaCodigo);

		var loDatos = [
			{titulo:'IDEntregaCodigo', valor:goEntAdd[tnId].IDEntregaCodigo},
			{titulo:'Código Serv Tec', valor:goEntAdd[tnId].CodSerTecEntregado},
			{titulo:'Cantidad Total', valor:goEntAdd[tnId].CantTotEntregada},
			{titulo:'Fecha Entrega', valor:goEntAdd[tnId].FecEntrega},
			{titulo:'Estado', valor:goEntAdd[tnId].EstEntregaCodigo+' - '+ goParData.estrepentrega.valores[goEntAdd[tnId].EstEntregaCodigo]}
		];
		if(goEntAdd[tnId].EstEntregaCodigo==0){
			loDatos.push({titulo:'Fecha Anulación', valor:goEntAdd[tnId].FecAnulacion});
		}
		$('#divContenidoAccion').html(crearTabla(lcClass, loDatos, false, {titulo:'style="width:150px;"', valor:''}));
		$("#divAcciones").modal('show');

	// Otros diferentes a EntregaCodigo
	} else {
		$('#divAcciones .modal-title').text(tcTipo+' '+tnId);
		$('#divAcciones #divContenidoAccion').html('<div class="row justify-content-center"><div class="fa-3x" style="color: #17A2B8;"><i class="fas fa-circle-notch fa-spin"></i></div></div>');
		$("#divAcciones").modal('show');

		$.ajax({
			type: "POST",
			url: gcUrlAjax,
			data: {accion:'datosacc', tipo:tcTipo, numid:tnId},
			dataType: "json",
			success: function(loRta) {
				if (loRta.error == '') {
					$('#divContenidoAccion').html('');
					var loDato = loRta.data;

					switch (tcTipo) {

						case 'Programa':
							var loDatos = [
								{titulo:'Identificador', valor:loDato.IDENTF},
								{titulo:'Fecha Programación', valor:strNumAFecha(loDato.FECHAP)},
								{titulo:'Fecha Máxima Entrega', valor:loDato.FECMAX},
								{titulo:'Código a Entregar', valor:loDato.CODSRV},
								{titulo:'Cantidad a Entregar', valor:loDato.CATTOT.replace('.000','')},
								{titulo:'Estado', valor:loDato.ESTPRO+' - '+ goParData.estrepentrega.valores[loDato.ESTPRO]},
								{titulo:'Log Creado', valor:loDato.USCPMP+' - '+strNumAFecha(loDato.FECPMP)+' - '+strNumAHora(loDato.HOCPMP)},
								{titulo:'Log Modifica', valor: (loDato.USMPMP.trim()==='' ? '' : loDato.USMPMP+' - '+strNumAFecha(loDato.FEMPMP)+' - '+strNumAHora(loDato.HOMPMP))}
							];
							break;

						case 'Entrega':
							// Códigos Adicionales
							//var lcCodAdd = '';
							//if (!loDato.CODENT=='') {
							//	var loCodAdd = JSON.parse(loDato.CODENT);
							//	if (typeof loCodAdd == 'object') {
							//		var lcSalto = '';
							//		$.each(loCodAdd, function(lnIndice, loDataCA) {
							//			var lcIni = '* ';
							//			$.each(loDataCA, function(lcNombre, lcValor) {
							//				lcCodAdd += lcSalto + lcIni + lcNombre + ': ' + lcValor;
							//				lcSalto = '<br />'; lcIni = '&nbsp;&nbsp;&nbsp;';
							//			});
							//		});
							//	}
							//}
							var loDatos = [
								{titulo:'Identificador', valor:loDato.IDENTF},
								{titulo:'Fecha Realizado', valor:strNumAFecha(loDato.FECHAE)},
								{titulo:'Número de Entrega', valor:loDato.NUMENT},
								{titulo:'Código Entregado', valor:loDato.CODSRV},
								{titulo:'Cantidad Entregada', valor:loDato.CATTOT.replace('.00','')},
								{titulo:'¿Entrega Total?', valor:(loDato.ENTTOT=='1'?'SI':'NO')},
								{titulo:'Causa No Entrega', valor:loDato.CAUSNO+' - '+ goParData.causanoentrega.valores[loDato.CAUSNO]},
								{titulo:'Fecha Entrega', valor:loDato.FECENT.substr(0,10)},
								{titulo:'Número Lote', valor:loDato.NOLOTE},
								{titulo:'Receptor', valor:loDato.TIDREC+' - '+loDato.NIDREC},
								//{titulo:'Códigos Adicionales', valor: lcCodAdd},
								{titulo:'Estado', valor:loDato.ESTENT+' - '+ goParData.estentrega.valores[loDato.ESTENT]},
								{titulo:'Log Creado', valor:loDato.USCEMP+' - '+strNumAFecha(loDato.FECEMP)+' - '+strNumAHora(loDato.HOCEMP)},
								{titulo:'Log Modifica', valor:(loDato.USMEMP.trim()==='' ? '' : loDato.USMEMP+' - '+strNumAFecha(loDato.FEMEMP)+' - '+strNumAHora(loDato.HOMEMP))}
							];
							break;

						case 'Reporte':
							var loDatos = [
								{titulo:'Identificador', valor:loDato.IDENTF},
								{titulo:'Fecha Realizado', valor:strNumAFecha(loDato.FECHAR)},
								{titulo:'Estado de Entrega', valor:loDato.ESTENT+' - '+ goParData.estadoentrega.valores[loDato.ESTENT]},
								{titulo:'Causa No Entrega', valor:loDato.CAUSNO+' - '+ goParData.causanoentrega.valores[loDato.CAUSNO]},
								{titulo:'Valor Entregado', valor:loDato.VALORE},
								{titulo:'Fecha Reporte', valor:loDato.FECREP.substr(0,10)},
								{titulo:'Estado', valor:loDato.ESTREP+' - '+ goParData.estrepentrega.valores[loDato.ESTREP]},
								{titulo:'Log Creado', valor:loDato.USCRMP+' - '+strNumAFecha(loDato.FECRMP)+' - '+strNumAHora(loDato.HOCRMP)},
								{titulo:'Log Modifica', valor: (loDato.USMRMP.trim()==='' ? '' : loDato.USMRMP+' - '+strNumAFecha(loDato.FEMRMP)+' - '+strNumAHora(loDato.HOMRMP))}
							];
							break;

						case 'Factura':
							var loDatos = [
								{titulo:'Identificador Factura', valor:loDato.IDENTF},
								{titulo:'Id Reporte Facturación', valor:loDato.IDFACT},
								{titulo:'Fecha Realizado', valor:strNumAFecha(loDato.FECHAF)},
								{titulo:'Número Factura', valor:loDato.NUMFAC},
								{titulo:'EPS', valor:loDato.CODEPS+' - '+loDato.NITEPS+' - '+gaEPS[loDato.CODEPS]},
								{titulo:'Código Entregado', valor:loDato.CODSRV},
								{titulo:'Cant. en Und Mínimas', valor:loDato.CNTUMN.replace('.000','')},
								{titulo:'Vr. Unitario Facturado', valor:loDato.VLRFAC.replace('.000','')},
								{titulo:'Vr. Total Facturado', valor:loDato.VLRFAT.replace('.000','')},
								{titulo:'Cuota Moderadora', valor:loDato.CUOTAM.replace('.000','')},
								{titulo:'Copago', valor:loDato.COPAGO.replace('.000','')},
								{titulo:'Fecha Reporte', valor:loDato.FECFAC.substr(0,10)},
								{titulo:'Estado', valor:loDato.ESTFAC+' - '+ goParData.estfacturacion.valores[loDato.ESTFAC]},
								{titulo:'Log Creado', valor:loDato.USCFMP+' - '+strNumAFecha(loDato.FECFMP)+' - '+strNumAHora(loDato.HOCFMP)},
								{titulo:'Log Modifica', valor:(loDato.USMFMP.trim()==='' ? '' : loDato.USMFMP+' - '+strNumAFecha(loDato.FEMFMP)+' - '+strNumAHora(loDato.HOMFMP))}
							];
							break;

					}
					var loTabla = crearTabla(lcClass, loDatos, false, {titulo:'style="width:150px;"', valor:''});
					loTabla.appendTo('#divContenidoAccion');
					$("#divAcciones").modal('show');

				} else {
					fnAlert(loRta.error, 'Alerta', 'exclamation-triangle', 'red', 'small');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.responseText);
				fnAlert('<b>Estado</b>: '+textStatus+'<br><b>Error</b>: '+jqXHR.responseText, 'Alerta', 'exclamation-triangle', 'red', 'small');
			},
			complete: function(jqXHR, textStatus, errorThrown){
				//goTabla.bootstrapTable('hideLoading');
			}
		});
	}
}



/* Une campos tipo y número de documento del paciente */
function docPaciente(value, row, index) {
	return row.TIPIDPAC+' '+row.NUMIDPAC;
}



/*  Une campos cantidad y unidad  */
function cantUnidad(tcValor, toFila, tnIndice) {
	return toFila.CANTIDAD.replace('.000000','')+' '+toFila.UNIDAD;
}



/*  Une campos cantidad y unidad para direccionamientos  */
function cantUnidadDir(tcValor, toFila, tnIndice) {
	return toFila.CANTTOTAL.replace('.00','')+' '+toFila.UNIDAD;
}



/*  Estado de las prescripciones  */
function estadoPres(tcValor, toFila, tnIndice) {
	return estadoProgreso('PRESCR', tcValor, toFila, tnIndice);
}

/*  Estado de los direccionamientos  */
function estadoDir(tcValor, toFila, tnIndice) {
	return estadoProgreso('DIRECC', tcValor, toFila, tnIndice);
}

/*  Estado de progreso registros  */
function estadoProgreso(tcTipo, tcValor, toFila, tnIndice) {
	var loEstado = {},
		lcProgress = '<div class="progress" style="height:10px;" data-toggle="tooltip" data-placement="top" title="TITLE">'
				+'<div class="progress-bar progress-bar-striped bg-info" role="progressbar" style="width: VALOR%" aria-valuenow="VALOR" aria-valuemin="0" aria-valuemax="100"></div>'
				+'<div class="progress-bar progress-bar-striped bg-warning" role="progressbar" style="width: VALRS%" aria-valuenow="VALRS" aria-valuemin="0" aria-valuemax="100"></div>'
				+'</div>';
	switch (tcTipo) {
		case 'PRESCR':
			loEstado = toFila.IDFACTURA > 0 ? {dsc:'Rep.Factura', val:100,vrs:0} :
					 ( toFila.IDREPORTE > 0 ? {dsc:'Rep.Entrega', val:67, vrs:33} :
					 ( toFila.IDENTREGA > 0 ? {dsc:'Entrega',	  val:33, vrs:67} : {dsc:'N/A', val:0, vrs:100} ) );
			break;
		case 'DIRECC':
			loEstado = toFila.IDFACTURA > 0 ? {dsc:'Rep.Factura', val:100,vrs:0} :
					 ( toFila.IDREPORTE > 0 ? {dsc:'Rep.Entrega', val:75, vrs:25} :
					 ( toFila.IDENTREGA > 0 ? {dsc:'Entrega',	  val:50, vrs:50} :
					 ( toFila.IDPROGRAMA> 0 ? {dsc:'Programa',	  val:25, vrs:75}: {dsc:'N/A', val:0, vrs:100} ) ) );
			break;
	}
	return lcProgress.replace(/VALOR/g,loEstado.val).replace(/VALRS/g,loEstado.vrs).replace('TITLE',loEstado.dsc);
}


/*  Muestra nombre de la EPS  */
function epsFormato(tcValor, toFila, tnIndice) {
	if ( gaEPS[toFila.CODEPS] ) {
		return '<span data-toggle="tooltip" data-placement="top" title="'+gaEPS[toFila.CODEPS]+'">'+toFila.CODEPS+'<span>';
	} else {
		return toFila.CODEPS;
	}
}


/*  Genera campo con botones para cada fila  */
function accionFormato(tcValor, toFila, tnIndice) {
	return [
		'<a class="clsVerFila" href="javascript:void(0)" title="Ver">',
		'<i class="fas fa-eye" style="color: #17A2B8;"></i>',
		'</a>  '
	].join('');
}


/*
 *	Código que se ejecuta con los botones de acción
 */
window.accionEventos = {
	'click .clsVerFila': function (e, tcValor, toFila, tnIndice) {
		gnIndex = tnIndice;
		goFila = toFila;
		mostrarDataRegistro();
	},
	'click .clsAccion': function (e, tcValor, toFila, tnIndice) {
		fnAlert('Click clsAccion, Fila: ' + JSON.stringify(toFila), 'clsAccion', 'exclamation-triangle', 'default', 'small');
	}
}


/*
 *	Actualiza Entrega Códigos del registro actual
 */
function fnActualizaEntCod(tcBoton) {
	$("i",tcBoton).addClass("fa-spin");
	var laData = {
		accion:'actEntCod',
		numprs:goFila.NUMPRES,
		ident:goFila.IDENTREGA
	};
	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: laData,
		dataType: "json",
	})
	.done(function(loRta) {
		if (loRta.error == ''){
			actualizarRegistro();

		} else {
			fnAlert(loRta.error);
		}
		$("i",this).removeClass("fa-spin");
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		$("i",this).removeClass("fa-spin");
		fnAlert('Se presentó un error al actualizar datos de entrega');
	});
}


/*
 *	Retorna cantidad entregada CUMS adicionales, a partir del JSON de entregas adicionales
 */
function cantAddEntregada(tcEntregaAddJson){
	var lcCantAdd=0;
	if(goFila.CODENTADD.length>0){
		var loMedAdds = JSON.parse(tcEntregaAddJson);
		$.each(loMedAdds, function(lnKey, loMedAdd){
			if(loMedAdd.FecAnulacion==null){
				lcCantAdd+=parseFloat(loMedAdd.CantTotEntregada);
			}
		});
	}
	return lcCantAdd;
}
