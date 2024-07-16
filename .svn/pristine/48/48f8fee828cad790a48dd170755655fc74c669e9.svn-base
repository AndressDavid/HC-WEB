var gcDatosIngreso = ''

$(function () {
	consultarDatos();
});

function consultarDatos(){

	$.ajax({
		type: "POST",
		url: 'vista-HistoriaClinica/ajax/ingresopaciente.php',
		data: {lcIngresoPaciente: 'consultarIngreso', nIngreso: $("#nroIngreso").val()},
		dataType: "json",
		async:false
	})

	.done(function( loIngresoPaciente ) {
		try {
			if (loIngresoPaciente.error == ''){
				gcDatosIngreso = loIngresoPaciente;
				//console.log(gcDatosIngreso);
			} else {
				alert(loPlanManejo.error + ' ', "warning");
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consulta de los datos del paciente.', '', 'font-awesome','red','medium');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar datos del paciente.', '', 'font-awesome','red','medium');
	});
}

