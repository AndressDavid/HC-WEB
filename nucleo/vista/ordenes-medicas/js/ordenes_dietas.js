var oDietaOrdMedica = {
	gcUrlAjax: 'vista-ordenes-medicas/ajax/ajax',
	lcTitulo : 'Dietas ordenes médicas',
	lcFormaError : '',
	lcObjetoError: '',
	lcMensajeError: '',

	inicializar: function(){
		this.cargarListaDietas();
	},
	
	cargarListaDietas: function() {
		var loSelect = $('#seltipoDietaMedicas');
		var lcTipo = 'tabladieta';

		$.ajax({
			type: "POST",
			url: oDietaOrdMedica.gcUrlAjax,
			data: {accion: lcTipo},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
						loSelect.append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
					});
				} else {
					alert(loTipos.error + ' ', "warning");
				}

			} catch(err) {
				alert('No se pudo realizar la busqueda de listado dietas médicas.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			alert('Se presentó un error al buscar listado dietas médicas.', "danger");
		});
		return this;
	},
	
	validacion: function() {
		var lbValido = true;
		oDietaOrdMedica.lcObjetoError = '';
		oDietaOrdMedica.lcFormaError = '';
		var lcTipoDieta = $("#seltipoDietaMedicas").val().trim();
		var lcDescripcionTipoDieta = '';
		
		if (lcTipoDieta!=''){
			lcDescripcionTipoDieta = $("#seltipoDietaMedicas option[value="+lcTipoDieta+"]").text();
			
			if (lcDescripcionTipoDieta===''){
				oDietaOrdMedica.lcFormaError = 'FormDietaOrdmedicas';
				oDietaOrdMedica.lcObjetoError = 'seltipoDietaMedicas';
				oDietaOrdMedica.lcMensajeError = 'Tipo de dieta no corresponde, revise por favor.';
				$("#seltipoDietaMedicas").focus();
				lbValido = false;
			}
		}
		return lbValido;
	},	
	
	obtenerDatos: function() {
		var lcTipoDieta = $("#seltipoDietaMedicas").val();
		var lcDescripcionTipoDieta = lcObservacionDieta = '';
		
		if (lcTipoDieta!=''){
			lcDescripcionTipoDieta = $("#seltipoDietaMedicas option[value="+lcTipoDieta+"]").text();
			 lcObservacionDieta = $("#txtObservacionDieta").val().trim();
		}	
		var laDieta = {
			'tipoDietaMedicas':  lcTipoDieta,
			'descripcionDietaMedicas': lcDescripcionTipoDieta,
			'observacionDieta': lcObservacionDieta
		}
		return laDieta;		
	}
}	