
$(function () {
	getVariables(2);
	fnCrearFormCond();
	$('#btnEnviar').off('click').on('click', fnEjecutarCond);
	$('#btnLimpiar').off('click').on('click', fnCrearFormCond);
});

/*  Crea formulario para acción PUT  */
function fnCrearFormCond() {
	$('#cntPUT').html('');
	$('#divInfo').html('');
	$('#divResFinal').html('');
	$('#divResultado').hide();
	$('#divIconoEspera').hide();
	$('#divResFinal').hide();

	fnCrearFormPut('91');
}


/*  Ejecutar los tres procesos  */
function fnEjecutarCond() {

	$('#divInfo').html('');
	$('#divResultado').hide();
	$('#divIconoEspera').hide();
	$('#divResFinal').hide();
	$('#divResFinal').html('');

	if (!fnValidarPut()) return false;

	$('#divResultado').show();
	$('#divIconoEspera').show();

	// Datos para enviar
	var lcDatosP = '{', lcComaP = '', lcRutaP='Programacion/{nit}/{tokentmp}';
	var lcDatosE = '{', lcComaE = '', lcRutaE='Entrega/{nit}/{tokentmp}';
	var lcDatosR = '{', lcComaR = '', lcRutaR='ReporteEntrega/{nit}/{tokentmp}';
	$(".clsEnviar").each(function(tcIndex) {
		var lcValor=$(this).val().trim(),
			lbEnviarVacio=($(this).attr('tiput')?$(this).attr('tiput'):'x').indexOf('V') > -1;
		if (lcValor!=='' || lbEnviarVacio) {
			if (lcValor=='' && lbEnviarVacio) {lcValor=' ';}
			if ($(this).attr('tiput').indexOf('P') > -1) {
				lcDatosP += lcComaP + $(this).attr('vrbl') + ':"' + lcValor + '"'
				lcComaP = ',';
			}
			if ($(this).attr('tiput').indexOf('E') > -1) {
				lcDatosE += lcComaE + $(this).attr('vrbl') + ':"' + lcValor + '"'
				lcComaE = ',';
			}
			if ($(this).attr('tiput').indexOf('R') > -1) {
				lcDatosR += lcComaR + $(this).attr('vrbl') + ':"' + lcValor + '"'
				lcComaR = ',';
			}
		}
	});
	lcDatosP += '}'; lcDatosE += '}'; lcDatosR += '}';

	// Datos para enviar
	var laDataP = {
			accion	: 'mipresput',
			url		: gcRutaMiPres + lcRutaP,
			datos	: lcDatosP,
		},
		laDataE = {
			accion	: 'mipresput',
			url		: gcRutaMiPres + lcRutaE,
			datos	: lcDatosE,
		},
		laDataR = {
			accion	: 'mipresput',
			url		: gcRutaMiPres + lcRutaR,
			datos	: lcDatosR,
		};

	// ------- PROGRAMACIÓN ------- //
	envioPutAjax('Programación', laDataP, function(){

		// ------- ENTREGA ------- //
		envioPutAjax('Entrega', laDataE, function(){

			// ------- REPORTE ENTREGA ------- //
			envioPutAjax('Reporte Entrega', laDataR);
		});
	});
}

