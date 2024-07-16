var oRevisionSistema = {
	validacion: function()
	{
		var lbValido = true;
		return lbValido;
	},
	obtenerDatos: function()
	{
		//serialización de datos dentro de laDatos
		return $( '#FormRevision').serializeArray();
	}
};
