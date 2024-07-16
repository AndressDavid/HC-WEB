(function ( $ ) {

	$.fn.tiposRiesgo = function(toOpciones) {

		// Configuración
		var opciones = $.extend({tipoDato: "TiposRiesgo",}, toOpciones );

		this.each(function() {
						
			var loSelect = $(this),
				lcTipo = loSelect.attr('data-tipo') ? $(this).attr('data-tipo') : opciones.tipoDato;
				
			loSelect.append('<option selected> </option>');
			
			$.ajax({
				type: "POST",
				url: "vista-comun/ajax/modalCTC.php",
				data: {tipoDato: lcTipo},
				dataType: "json"
			})
			.done(function( loTipos ) {
				try {
					if (loTipos.error == ''){
						$.each(loTipos.DATOS, function( lcKey, loTipo ) {
							loSelect.append('<option value="' + parseInt(lcKey) + '">' + loTipo + '</option>');
						});
					} else {
						alert(loTipos.error);
					}
				} catch(err) {
					alert('No se pudo realizar la busqueda de tipos de Riesgo.');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log();
				alert("Se presentó un error al buscar tipos de Riesgo. \n"+jqXHR.responseText);
			});
		});

		return this; 
	};
 
}( jQuery ));

