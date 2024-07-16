var oFinalidad = {

	inicializar: function(toFinalidad, toValor='') {
		lcTipoFinalidad = toFinalidad;
		lcValor = toValor 

		let laDatosPaciente = {
			genero: aDatosIngreso.cSexo,
			edad: aDatosIngreso.aEdad,
		}

		$.ajax({
			type: "POST",
			url: "vista-historiaclinica/ajax/HistoriaClinica.php",
			data: {lcTipo: 'finalidad', tipofin: lcTipoFinalidad, laPacienteDatos: laDatosPaciente},
			dataType: "json"
		})
		.done(function( loDatos ) {
			laDatosFinalidad=loDatos.finalidades;
			let laListaFinalidades = Object.entries(laDatosFinalidad).map(([clave, descripcion]) => ({ clave: parseInt(clave), descripcion }));
			laListaFinalidades.sort((a, b) => a.descripcion.localeCompare(b.descripcion));

			try {
				if (loDatos.error == ''){
					$.each(laListaFinalidades, function( lcCodigo, lcFinalidad ) {
						if(lcValor == lcFinalidad.clave){
							$("#selFinalidad").append('<option selected value="' + lcFinalidad.clave + '">' + lcFinalidad.descripcion + '</option>');
						}else{
							$("#selFinalidad").append('<option value="' + lcFinalidad.clave + '">' + lcFinalidad.descripcion + '</option>');
						}
						
					}); 
				} else {
					fnAlert(loDatos.error);
				}
			} catch(err) {
				fnAlert('Error al obtener la finalidad.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar las finalidades.');
		})
	},

	validacion: function() {
		return true;
	},

	obtenerDatos: function() {
		return $('#formFinalidad').serializeArray();
	}
}
