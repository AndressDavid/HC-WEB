var oTextoInformativo = {
	sinConsultar: true,
	activa: false,

	inicializar: function(){
		$('#btnCerrarTexInformativo').on('click', oInterconsultaOrdMedica.validaInterconsultas);	
	},	
	
	consultar: function(tfDespues)
	{
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/textoInformativo',
			data: {},
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.error == ''){
					oTextoInformativo.activa = loDatos['cTextoInfo'].length>0;					
					$("#edtxtPandemia").val(loDatos['cTextoInfo']);
					this.sinConsultar = false;
					if(typeof tfDespues==='function'){
						tfDespues();
					}
				} else {
					fnAlert(loDatos.error)
				}
			} catch(err) {
				fnAlert('No se pudo realizar la consulta de Texto Informativo.')
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar Texto Informativo.');
		});
	},
	
	mostrar: function()
	{
		if (this.activa){
			$("#divTextInformativoModal").modal('show');
		}
	},

	/* validaInterconsultas: function()
	{
		if (oInterconsultaOrdMedica.gcInterconsultasPendientes!=''){
			lcTextoInterconsultas='Tenga en cuenta que las siguientes especialidades tienen interconsultas por responder: <br>' + oInterconsultaOrdMedica.gcInterconsultasPendientes;
			fnAlert(lcTextoInterconsultas, 'INTERCONSULTAS POR RESPONDER', false, 'blue', 'medium');
		}	
	}, */
	
	obtenerDatos: function()
	{
		//serialización de datos dentro de laDatos
		return ($("#edtxtPandemia").val());
	}
		
}