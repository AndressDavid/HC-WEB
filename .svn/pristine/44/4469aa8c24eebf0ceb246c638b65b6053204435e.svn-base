var oAnalisis = {

	inicializar: function()
	{
		//trae datos del analisis
		$('#edtAnalisis').text(aEstados['Analisis']);
		$('#edtInterpreta').text(aEstados['Interpreta']);
		if(aEstados.Fibrilacion){
			$('#divHeaderFibrilacion').show();
			$('#divFibrilacion').show();
		}
	},

	// VALIDACION PRINCIPAL
	validacion: function()
	{
		var lbValido = true;
		if(aEstados['Fibrilacion']==true){
			lbValido = this.validarRespuestasFA();
		}
		return lbValido;
	},

	validarRespuestasFA: function()
	{
		var lnCantidad = 0;
		this.lcObjetoError = '';
		var llRetorno = true;

		for (var lnId=1; lnId<=8; lnId++){
			var lcDato = lnId.toString();
			var lcObjeto = 'selRespuesta'+lcDato.padStart(2, "0");

			if($('#'+lcObjeto).val() == ''){
				lnCantidad++
				this.lcObjetoError = this.lcObjetoError == ''? lcObjeto: this.lcObjetoError;
			}
		}

		if(lnCantidad>0 && lnCantidad<=8){
			this.lcMensajeError = 'Escala de FIBRILACION AURICULAR debe ser diligenciada en su totalidad. Revise por favor !';
			llRetorno = false;
		}
		return llRetorno;
	},

	obtenerDatos: function()
	{
		//serialización de datos dentro de laDatos
		return ($('#FormAnalisis').serializeArray()).concat($('#FormFibrilacion').serializeArray());
	}
}