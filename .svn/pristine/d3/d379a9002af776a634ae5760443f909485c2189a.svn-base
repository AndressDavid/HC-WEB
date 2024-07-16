
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

	fnCrearFormPut('92');
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
	var lcDatosE = '{', lcComaE = '', lcRutaE='EntregaAmbito/{nit}/{tokentmp}';
	var lcDatosA = '{', lcComaA = '', lcRutaA='EntregaCodigos/{nit}/{tokentmp}', lnNumA=0;
	var lcDatosR = '{', lcComaR = '', lcRutaR='ReporteEntrega/{nit}/{tokentmp}';
	$(".clsEnviar").each(function(tcIndex) {
		var lcValor=$(this).val().trim(),
			lbEnviarVacio=($(this).attr('tiput')?$(this).attr('tiput'):'x').indexOf('V') > -1;
		if (lcValor!=='' || lbEnviarVacio) {
			if (lcValor=='' && lbEnviarVacio) {lcValor=' ';}
			if ($(this).attr('tiput').indexOf('E') > -1) {
				lcDatosE += lcComaE + $(this).attr('vrbl') + ':"' + lcValor + '"'
				lcComaE = ',';
			}
			if ($(this).attr('tiput').indexOf('A') > -1) {
				lcDatosA += lcComaA + $(this).attr('vrbl') + ':"' + lcValor + '"'
				lcComaA = ','; 
				if ( lcValor ) lnNumA++;
			}
			if ($(this).attr('tiput').indexOf('R') > -1) {
				lcDatosR += lcComaR + $(this).attr('vrbl') + ':"' + lcValor + '"'
				lcComaR = ',';
			}
		}
	});
	lcDatosE += '}';

	if (lnNumA==1) {
		// Debería terminar o mostrar un mensaje de alerta?
	}

	var laDataE = {
			accion	: 'mipresput',
			url		: gcRutaMiPres + lcRutaE,
			datos	: lcDatosE,
		};
	
	// ------- ENTREGA ÁMBITO ------- //
	envioPutAjax('Entrega Ámbito', laDataE, function(toRtaE){
		// Obtener identificadores
		var lcId = toRtaE.Id;
			lcIdEntrega = toRtaE.IdEntrega;

		// Datos para reporte
		lcDatosR += ',ID:"'+lcId+'"}';
		var laDataR = {
				accion	: 'mipresput',
				url		: gcRutaMiPres + lcRutaR,
				datos	: lcDatosR,
			};

		if (lnNumA==3){
			// ------- ENTREGA CÓDIGO ------- //
			lcDatosA += ',IdEntrega:"'+lcIdEntrega+'"}';
			var laDataA = {
					accion	: 'mipresput',
					url		: gcRutaMiPres + lcRutaA,
					datos	: lcDatosA,
				};

			envioPutAjax('Entrega Código', laDataA, function(){
				// ------- REPORTE ENTREGA ------- //
				envioPutAjax('Reporte Entrega', laDataR);
			});

		} else {
			// ------- REPORTE ENTREGA ------- //
			envioPutAjax('Reporte Entrega', laDataR);
		}
		$('#divIconoEspera').hide();
		$('#divResultado').show();
		$('#divResFinal').show();
	});
}




