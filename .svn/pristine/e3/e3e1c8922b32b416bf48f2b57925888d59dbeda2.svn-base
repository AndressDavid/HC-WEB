var oModalEspera = {
	nHabilitar: 0,
	oDialogo: 0,
	cTitulo: '<i class="fas fa-circle-notch fa-spin" style="font-size: 2em; color: #ffffff;"></i><br>Espere por favor ...',
	cContenido: 'Se está preparando el entorno de trabajo',
	nIntervalo: 60, // segundos que espera para cerrar la ventana
	oIntervalo: null,

	mostrar: function(tcHtmlMensaje, tcHtmlTitulo)
	{
		tcHtmlTitulo = (typeof tcHtmlTitulo === 'string')? tcHtmlTitulo: oModalEspera.cTitulo;
		tcHtmlMensaje = (typeof tcHtmlMensaje === 'string')? tcHtmlMensaje: oModalEspera.cContenido;
		$("#divEsperaMensaje").html(tcHtmlMensaje);
		$("#divEsperaTitulo").html(tcHtmlTitulo);

		oModalEspera.oDialogo = $.dialog({
			theme: 'supervan', // dark, light, supervan, material, modern, bootstrap
			type: 'red', // dark, red, blue, green, orange, purple
			title: '',
			columnClass: 'm',
			backgroundDismissAnimation: '', // glow, shake
			content: $("#divEsperaModal").html(),
			closeIcon: false
		});
		if (oModalEspera.nIntervalo>0) {
			oModalEspera.oIntervalo = setInterval(oModalEspera.ocultar, oModalEspera.nIntervalo*1000);
		}
	},

	ocultar: function()
	{
		oModalEspera.oDialogo.close();
		clearInterval(oModalEspera.oIntervalo);
	},

	esperaAumentar: function()
	{
		oModalEspera.nHabilitar++;
	},

	esperaOcultar: function(tFuncionOcultar)
	{
		oModalEspera.nHabilitar--;
		if (oModalEspera.nHabilitar==0) {
			oModalEspera.ocultar();
			if (typeof tFuncionOcultar === 'function') {
				tFuncionOcultar();
			}
		}
	}
}