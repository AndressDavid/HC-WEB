var oGenerosPaciente = {
	gaDatosGeneros: ''
}
$(function() {
	$.ajax({
		type: "POST",
		url: 'vista-comun/ajax/tiposGeneros',
		data: {accion: 'consultarGeneros'},
		dataType: "json"
	})
	.done(function(toDatos) {
		try {
			if (toDatos.error=='') {
				oGenerosPaciente.gaDatosGeneros=toDatos.datos;
			} else {
				fnAlert(toDatos.Error);
			}
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la consulta consulta Generos.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert("Se presentó un error al consulta consulta Generos.");
	});
});