var oModalAlertaNopos = {
	fnEjecutar: false,

	inicializar: function()
	{
		$('#btnAbrirMiPres').on('click', oModalAlertaNopos.abrirMiPres);
		$('#btnCerrarNopos').on('click', oModalAlertaNopos.ocultar);
	},

	mostrar: function(tfEjecutar)
	{
		if (oModalAlertaIntranet.gcMipresIntranet==''){
			$("#divAlertaMipres").modal('show');
			oModalAlertaNopos.fnEjecutar = tfEjecutar;
		}else{
			oModalAlertaIntranet.mostrar(tfEjecutar);
		}	
	},

	ocultar: function()
	{
		$("#divAlertaMipres").modal('hide');
		if (typeof oModalAlertaNopos.fnEjecutar==='function'){
			oModalAlertaNopos.fnEjecutar();
		}
	},

	abrirMiPres: function()
	{
		window.open(oModalAlertaIntranet.gcRutaMipres, "_blank");
	},
}