var oAlertaMalNutricion = {
	gcUrlAjax: 'vista-comun/ajax/alertaMalNutricion',
	lcTitulo : 'Alerta Mal Nutrición',
	lcFormaError: '', lcObjetoError: '', lcMensajeError: '', gcAlertaMalNutricion: '',

	inicializar: function(){
		this.verificaAlerta();
	},
	
	verificaAlerta: function() {
		$.ajax({
			type: "POST",
			url: oAlertaMalNutricion.gcUrlAjax,
			data: {accion: 'verificarAlerta', lnIngreso: aDatosIngreso['nIngreso']},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					if (loTipos.DATOS.TITULO!='' && loTipos.DATOS.TITULO!=undefined){
						oAlertaMalNutricion.gcAlertaMalNutricion=loTipos.DATOS;
					}
				} else {
					fnAlert(loTipos.error + ' ', "warning");
				}

			} catch(err) {
				fnAlert('No se pudo realizar la busqueda verifica alerta mal nutrición.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar verifica alerta mal nutrición.', "danger");
		});
	}
}	