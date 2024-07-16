var oMotivo = {

	lcTitulo : 'Dolor Torácico ',
	lcMensajeError : '',
	lcObjetoError: '',

	inicializar: function()
	{
		$('#selTipoCausa').tiposCausa({});
		$('#selTipoCausa').change(this.validarMotivo);
		$('#selRemisionIPS').change(this.validarRemision);
	},

	// Función que inicializa la relacion de recibido en vacio si la respuesta del usuario es NO
	validarRemision: function()
	{
		if($("#selRemisionIPS").val() == 'No' ){
			$("#edtRelacion").val('');
			$('#lblRelacion').removeClass("required");
		}
		else{
			$('#lblRelacion').addClass("required");
		}
	},

	validarMotivo: function()
	{
		var lcTipoCausa = $("#selTipoCausa").val();
		if(lcTipoCausa != ''){
			var lcDescCausa = $("#selTipoCausa option[value="+lcTipoCausa+"]").text();
			fnConfirm('Esta seguro que la causa externa es: '+lcDescCausa, 'MOTIVO DE CONSULTA', false, false, false,
				{
					text: 'Si',
					action: function(){
						// Existe órdenes ambulatorias
						if ($("#FormIncapacidad #selCausaAtencion").length > 0) {
							var loOpcion = $("#selTipoCausa option:selected"),
								lcOrigen = loOpcion.attr('data-origen'),
								lcValor = loOpcion.val(),
								lcDescripcion = loOpcion.text();
							$('#FormIncapacidad #selTipoIncapacidad').val('').change();
							$("#FormIncapacidad #selOrigenIncapacidad").val(lcOrigen=='L' ? '02' : '01');
							$("#FormIncapacidad #selCausaAtencion").html('').append($('<option>').text(lcDescripcion).val(lcValor)).val(lcValor);
						} else {
							$("#FormIncapacidad #selOrigenIncapacidad,#selCausaAtencion").val('');
						}
					}
				},
				{
					text: 'No',
					action: function(){
						$("#selTipoCausa").val('');
						ubicarObjeto('#FormMotivo', '#selTipoCausa');
					}
				}
			)
		}
	},

	validacion: function()
	{
		var lbValido = true;

		// Valida que Intensidad al ser digitada no sea negativa ni mayores de 10
		if (($("#txtIntensidad").val())<0 || $("#txtIntensidad").val()>10){
			this.lcMensajeError = 'Error en el dato intensidad, valor del campo entre 0 y 10. Revise por favor!';
			this.lcObjetoError = "#txtIntensidad";
			lbValido = false;
			return lbValido;
		}

		//DURACION
		// Valida que los segundos al ser digitados no sean negativos
		if (($("#txtSegundosD").val())<0 || ($("#txtSegundosD").val())>59){
			this.lcMensajeError = 'Error en el dato segundos de Duración, valor del campo entre 0 y 59. Revise por favor!';
			this.lcObjetoError = "#txtSegundosD";
			lbValido = false;
			return lbValido;
		}

		// Valida que los minutos al ser digitados no sean negativos
		if (($("#txtMinutosD").val())<0 || ($("#txtMinutosD").val())>59){
			this.lcMensajeError = 'Error en el dato minutos de Duración, valor del campo entre 0 y 59. Revise por favor!';
			this.lcObjetoError = "#txtMinutosD";
			lbValido = false;
			return lbValido;
		}

		// Valida que las horas al ser digitadas no sean negativas
		if (($("#txtHorasD").val())<0 || ($("#txtHorasD").val())>24){
			this.lcMensajeError = 'Error en el dato horas de Duración, valor del campo entre 0 y 24. Revise por favor!';
			this.lcObjetoError = "#txtHorasD";
			lbValido = false;
			return lbValido;
		}

		// Valida que los días al ser digitados no sean negativos
		if (($("#txtDiasD").val())<0 || ($("#txtDiasD").val())>31){
			this.lcMensajeError = 'Error en el dato días de Duración, valor del campo entre 0 y 31. Revise por favor!';
			this.lcObjetoError = "#txtDiasD";
			lbValido = false;
			return lbValido;
		}

		//TIEMPO DE EVOLUCION
		// Valida que los segundos al ser digitados no sean negativos
		if (($("#txtSegundosE").val())<0 || ($("#txtSegundosE").val())>59){
			this.lcMensajeError = 'Error en el dato segundos de Tiempo de Evolución, valor del campo entre 0 y 59. Revise por favor!';
			this.lcObjetoError = "#txtSegundosE";
			lbValido = false;
			return lbValido;
		}

		// Valida que los minutos al ser digitados no sean negativos
		if (($("#txtMinutosE").val())<0 || ($("#txtMinutosE").val())>59){
			this.lcMensajeError = 'Error en el dato minutos de Tiempo de Evolución, valor del campo entre 0 y 59. Revise por favor!';
			this.lcObjetoError = "#txtMinutosE";
			lbValido = false;
			return lbValido;
		}

		// Valida que las horas al ser digitadas no sean negativas
		if (($("#txtHorasE").val())<0 || ($("#txtHorasE").val())>24){
			this.lcMensajeError = 'Error en el dato horas de Tiempo de Evolución, valor del campo entre 0 y 24. Revise por favor!';
			this.lcObjetoError = "#txtHorasE";
			lbValido = false;
			return lbValido;
		}

		// Valida que los días al ser digitados no sean negativos
		if (($("#txtDiasE").val())<0 || ($("#txtDiasE").val())>31){
			this.lcMensajeError = 'Error en el dato días de Tiempo de Evolución, valor del campo entre 0 y 31. Revise por favor!';
			this.lcObjetoError = "#txtDiasE";
			lbValido = false;
			return lbValido;
		}

		// Valida que las semanas al ser digitados no sean negativos
		if (($("#txtSemanasE").val())<0 || ($("#txtSemanasE").val())>5){
			this.lcMensajeError = 'Error en el dato semanas de Tiempo de Evolución, valor del campo entre 0 y 5. Revise por favor!';
			this.lcObjetoError = "#txtSemanasE";
			lbValido = false;
			return lbValido;
		}

		// Valida que los días al ser digitados no sean negativos
		if (($("#txtMesesE").val())<0 || ($("#txtMesesE").val())>12){
			this.lcMensajeError = 'Error en el dato meses de Tiempo de Evolución, valor del campo entre 0 y 12. Revise por favor!';
			this.lcObjetoError = "#txtMesesE";
			lbValido = false;
			return lbValido;
		}

		// Valida que los años al ser digitados no sean negativos
		if (($("#txtAnosE").val())<0 || ($("#txtAnosE").val())>99){
			this.lcMensajeError = 'Error en el dato años de Tiempo de Evolución, valor del campo entre 0 y 99. Revise por favor!';
			this.lcObjetoError = "#txtAnosE";
			lbValido = false;
			return lbValido;
		}
		return lbValido;

	},

	obtenerDatos: function()
	{
		//serialización de datos dentro de laDatos
		return ($('#FormMotivo').serializeArray()).concat($('#FormDolorT').serializeArray());
	}
};
