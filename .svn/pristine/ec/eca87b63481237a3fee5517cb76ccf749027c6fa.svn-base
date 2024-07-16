var oRegistroEvolucionUci = {
	lcTitulo : 'Registro unidad UCI ',
	lcMensajeError : '',
	lcObjetoError: '',
	
	inicializar: function() 
	{
		$('#FormRegistroUnidades').on('submit', function(e){e.preventDefault();});
		this.ConsultarAntecedentesUCI( aDatosIngreso['nIngreso']);
	},

	ConsultarAntecedentesUCI: function(tnIngreso){

		$.ajax({
			type: "POST",
			url: "vista-evoluciones/ajax/evoluciones.php",
			data: {lcTipo: 'AntecedentesUCI', Ingreso: tnIngreso},
			dataType: "json"
		})
		.done(function(loDatos) {
			loObj=loDatos.Antecedentes;
			try {
				$('#edtAntecedentesUci').val(loDatos.Antecedentes);
				edtAntecedentesUci
			} catch(err) {
				alert('No se pudo realizar la busqueda de Antecedentes UCI en EVOLUCIONES WEB. ');
			}
		});
	},

	validacion: function()
	{
		if (! $('#FormRegistroUnidades').valid()){
			ubicarObjeto('#FormRegistroUnidades');
			return false;
		}
		return true;
	},

	obtenerDatos: function() {
		var laRegistrosUci = OrganizarSerializeArray($('#FormRegistroUnidades').serializeArray());
		return laRegistrosUci;
	},
	
	ubicarObjeto: function(toForma, tcObjeto){
		tcObjeto = typeof tcObjeto === 'string'? tcObjeto: false;
		var loForm = $(toForma);

		activarTab(loForm);
		if (tcObjeto===false) {
			var formerrorList = loForm.data('validator').errorList,
				lcObjeto = formerrorList[0].element.id;
			$('#'+lcObjeto).focus();
		} else {
			$(tcObjeto).focus();
		}
	}	
	
};
	