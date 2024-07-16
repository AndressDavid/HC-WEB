(function($) {

	$.fn.EstadosSalida = function(toOpciones){
		// Configuración
		var opciones = $.extend({}, toOpciones);

		this.each(function() {

			var loSelect = $(this),
				lcTipo = loSelect.attr('data-tipo') ? $(this).attr('data-tipo') : opciones.tipo;
			loSelect.append('<option selected> </option>');
			$.ajax({
				type: "POST",
				url: "vista-epicrisis/ajax/EstadosSalida.php",
				data: {lcTipoDato: lcTipo},
				dataType: "json"
			})
			.done(function( loTipos ) {
				try {
					if (loTipos.error == ''){
						$.each(loTipos.ESTADOS, function( lcKey, loTipo ) {
							loSelect.append('<option value="' + lcKey + '">' + loTipo + '</option>');
						});
						if(typeof opciones.fnAfter=='function'){
							opciones.fnAfter();
						}
					} else {
						alert(loTipos.error);
					}
				} catch(err) {
					alert('No se pudo realizar la busqueda de Estados de Salida.');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log();
				alert("Se presentó un error al buscar Estados de Salida. \n"+jqXHR.responseText);
			});
		});

		return this;
	};

}(jQuery));
