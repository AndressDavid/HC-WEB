var oPlanPaciente = {
	datosPlan: '',
	gcUrlAjax: 'vista-comun/ajax/planespaciente.php',
	
	planesPaciente: function(tcid, tcTipoIde, tnNroIde){
		var loSelect = tcid;
		$.ajax({
			type: "POST",
			url: oPlanPaciente.gcUrlAjax,
			data: { accion: 'planesdelpaciente', tipoIdentifica: tcTipoIde, numIdentifica: tnNroIde },
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.error == ''){
					$.each(loDatos.datos, function( lcKey, loTipo ) {
						loSelect.append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
					});
				} else {
					alert(loDatos.error + ' ', "warning");
				}
			} catch(err) {
				fnAlert('No se pudo realizar la consulta planes del paciente.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar planes del paciente.');
		});
	}

}
