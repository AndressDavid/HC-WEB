var oListaDiagnosticos = {
	ListaDx: [],

	cargarDiagnosticos: function(toOtros, toFuncionPost) {
		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/Autocompletar.php",
			data: {
				tipoDato: 'Diagnosticos',
				otros: toOtros
			},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					oListaDiagnosticos.ListaDx = toDatos.datos;
					if (typeof toFuncionPost==='function') {
						toFuncionPost();
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la búsqueda del listado de diagnósticos.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar listado de diagnósticos.");
		});
	},

	autocompletar: function(tcSelector, tnMinChar, tnMaxItems, tcHighlightClass) {
		tnMinChar = (tnMinChar) ? tnMinChar: 5;
		tnMaxItems = (tnMaxItems) ? tnMaxItems: 25;
		tcHighlightClass = (tcHighlightClass) ? tcHighlightClass: 'text-danger';
		$(tcSelector).autocomplete({
			minChars: tnMinChar,
			source: oListaDiagnosticos.ListaDx,
			maximumItems: tnMaxItems,
			highlightClass: tcHighlightClass
		});
	}
}