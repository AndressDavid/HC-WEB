var oNihss = {
	laTotalNihss : [],
	lcMensajeError : '',
	lcObjetoError : '',
	lnCantidadResp : 0,
	inicializar: function() 
	{
		oNihss.iniciarPuntajes() ;
		oNihss.cargarPreguntas() ;
		oNihss.cargarRespuestas() ;
		$('.selNihssPuntaje').change(oNihss.actualizaPuntaje);
	},
	
	iniciarPuntajes: function() {
		
	for (var i=0; i<16; i++) {
		oNihss.laTotalNihss.push(0);
		}
	},

	// Carga Preguntas 
	cargarPreguntas: function() {
		for (var lnId=1; lnId<=15; lnId++){
			var lcDato = lnId.toString(); 
			var lcObjeto = 'lblNihss'+lcDato.padStart(2, "0");
			oNihss.getPreguntasNihss(lcObjeto , lnId , 'Preguntas Escala NISHH.', 'P');
		}
	},

	getPreguntasNihss: function(tcObjeto, tnIndice, tcMensaje, tcTipoC) {
		var loObjeto = $('#'+tcObjeto);

		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/Nihss.php",
			data: {lnIndice: tnIndice, lcTipoC: tcTipoC},
			dataType: "json"
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
						loObjeto.append(loTipo);
					});
				} else {
					alert(loTipos.error + ' ', "warning");
				}
			} catch(err) {
				alert('No se pudo realizar la busqueda de ' + tcMensaje , "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			alert('Se presentó un error al buscar ' + tcMensaje, "danger");
		});
	},

	// Carga Respuestas en cada select
	cargarRespuestas: function() {
		oNihss.lnCantidadResp=0;
		for (var lnId=1; lnId<=15; lnId++){
			var lcDato = lnId.toString(); 
			var lcObjeto = 'selNihss'+lcDato.padStart(2, "0");
			oNihss.getRespuestasNihss(lcObjeto , lnId , 'Respuestas Escala NISHH.', 'R');
		}
	},
	
	getRespuestasNihss: function(tcObjeto, tnIndice, tcMensaje, tcTipoC) {
		var loObjeto = $('#'+tcObjeto);
		// adiciona opción en blanco
		loObjeto.append('<option value="0" data-puntaje=""></option>');

		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/Nihss.php",
			data: {lnIndice: tnIndice, lcTipoC: tcTipoC},
			dataType: "json"
		})
		.done(function(loTipos) {
			oNihss.lnCantidadResp++;
			try {
				if (loTipos.error == ''){
					$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
						loObjeto.append('<option value="' + lcKey + '" data-puntaje="' + loTipo[0] + '">' + loTipo[1] + '</option>');
					});
				} else {
					alert(loTipos.error + ' ', "warning");
				}
			} catch(err) {
				alert('No se pudo realizar la busqueda de ' + tcMensaje , "danger");
			}

			if (typeof oAval === 'object' && oNihss.lnCantidadResp==15){
				if(oAval.lcTipoAval == 'HC'){
					oAval.CargarNihss();
				}
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			alert('Se presentó un error al buscar ' + tcMensaje, "danger");
		});
	},
	
	totalNihss: function() {

		var loObjetoT = $('#txtTotalN'),
			lnTotalN = 0;
		
		$.each(oNihss.laTotalNihss, function(lnIndex, loElemento){
			if (isNaN(loElemento)) {loElemento=0}
			lnTotalN += loElemento;
		});
		
		loObjetoT.val(lnTotalN);
			
	},

	actualizaPuntaje: function() {
		var lcIndice = $(this).attr('data-id'),
		lnIndice = parseInt(lcIndice),
		loObjeto = $('#lblPunto'+lcIndice),
		loObjetot = $('#txtPunto'+lcIndice),
		lnValor = $("#selNihss"+lcIndice).val(),
		lnPuntaje = $("#selNihss"+lcIndice+' option:selected').attr('data-puntaje');
	
		$(loObjeto).empty();
		loObjeto.text(lnPuntaje);
		$(loObjetot).empty();
		loObjetot.val(lnPuntaje);
		oNihss.laTotalNihss[lnIndice] = parseInt(lnPuntaje);
		oNihss.totalNihss();
	},

	// VALIDACION PRINCIPAL
	validacion: function() 
	{
		var lbValido = true;
		lbValido = oNihss.validarRespuestas();		
		return lbValido;
	},
	
	validarRespuestas: function() {
		var lnCantidad = 0 ;
		oNihss.lcObjetoError = '';
		for (var lnId=1; lnId<=15; lnId++){
			var lcDato = lnId.toString(); 
			var lcObjeto = 'selNihss'+lcDato.padStart(2, "0");
						
			if($('#'+lcObjeto).val() == 0){
				lnCantidad++
				oNihss.lcObjetoError = oNihss.lcObjetoError == ''? lcObjeto: oNihss.lcObjetoError ;
			}
			 
		}
		llRetorno = true ;
		if(lnCantidad>0 && lnCantidad<15){
			oNihss.lcMensajeError = 'Escala NIHSS debe ser diligenciada en su totalidad. Revise por favor !';
			llRetorno = false ;
		}
		return llRetorno ;
	},
	
	obtenerDatos: function() 
	{
		//serialización de datos dento de laDatos
		return $( '#FormNihss').serializeArray();
	}
};

