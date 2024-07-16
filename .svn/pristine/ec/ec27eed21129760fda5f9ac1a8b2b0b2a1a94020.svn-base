var oListaPaquetes = {
	ListaCupsPaquete: [],

	cargarProcedimientos: function(tcCodigoCup, tfPost) {
		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/listaPaquetes.php",
			data: {lcPaquete: tcCodigoCup, lcGenero: aDatosIngreso['cSexo']},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					oListaPaquetes.ListaCupsPaquete = toDatos.datos;
					
					if (typeof tfPost == 'function') {
						tfPost();
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda del paquete.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar tipos del paquete.");
		});
	}
}