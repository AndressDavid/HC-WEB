(function ( $ ) {
	$.fn.cargarDxFallece = function(toObjeto) {
	
		var loControl = $(this);
		var loListaFallece = {};
		var lfAutocompletar = function() {
		
		loControl.autocomplete({
			source: loListaFallece,
			maximumItems: 30,
			highlightClass: 'text-danger'
			});
		};

		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/Autocompletar.php",
			data: {tipoDato: 'DxFallece'},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					loListaFallece = toDatos.datos;
					toObjeto.ListaDxFallece=loListaFallece;
					lfAutocompletar();
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de Diagnósticos Fallece.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar tipos de Diagnósticos Fallece.");
		});
	};

 }( jQuery ));



