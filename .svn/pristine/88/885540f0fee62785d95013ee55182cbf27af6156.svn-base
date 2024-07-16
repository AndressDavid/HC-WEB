(function ( $ ) {

	$.fn.tiposViasIngreso = function(toOpciones) {

		// Configuración
		var opciones = $.extend({}, toOpciones );

		this.each(function() {
			
			var loSelect = $(this),
				lcTipo = loSelect.attr('data-tipo') ? $(this).attr('data-tipo') : opciones.tipo;
				
			loSelect.append('<option selected> </option>');
			
			$.ajax({
				type: "POST",
				url: "vista-comun/ajax/tiposViasIngreso.php",
				data: {lcTipoDato: lcTipo},
				dataType: "json"
			})
			.done(function( loTipos ) {
				try {
					if (loTipos.error == ''){
						$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
							loSelect.append('<option value="' + loTipo.CODVIA + '">' + loTipo.DESVIA + '</option>');
						});
					} else {
						fnAlert(loTipos.error);
					}
				} catch(err) {
					fnAlert('No se pudo realizar la busqueda de tipos de vías de ingreso.');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log();
				fnAlert("Se presentó un error al buscar tipos de vías de ingreso. \n"+jqXHR.responseText);
			});
		});

		return this; 
	};
 
}( jQuery ));

