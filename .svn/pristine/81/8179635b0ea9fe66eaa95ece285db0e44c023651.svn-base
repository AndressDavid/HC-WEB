var oModalAlertaFallece = {
	fnEjecutar: false,
	
	inicializar: function()
	{
		$('#btnCancelaFallece').on('click', oModalAlertaFallece.ocultar);
	},
	
	mostrar: function(tfEjecutar)
	{
		$("#divVerificaFalllece").modal('show');
		$('#txtFallece').val('');
		$("#txtFallece").focus();
		oModalAlertaFallece.fnEjecutar = tfEjecutar;
	},
	
	ocultar: function()
	{
		$("#divVerificaFalllece").modal('hide');
		
		if (typeof oModalAlertaFallece.fnEjecutar==='function'){
			oModalAlertaFallece.fnEjecutar();
		}
	},
}