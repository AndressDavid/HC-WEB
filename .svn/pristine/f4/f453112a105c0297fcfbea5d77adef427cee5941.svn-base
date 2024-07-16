(function ( $ ) {

	$.fn.secciones = function(toOpciones) {

		// Configuración
		var opciones = $.extend({
			// Valores por defecto
			tipo: '0',			// 0 = desde TABMAE, 1 = desde PRMTAB
			verCodigo: '1'		// 1 = muestra código - descripción, 0 = solo descripción
		}, toOpciones );

		this.each(function() {
			var loSelect = $(this),
				lcTipo = loSelect.attr('data-tipo') ? loSelect.attr('data-tipo') : opciones.tipo,
				lbVerCodigo = (loSelect.attr('data-verCodigo') ? loSelect.attr('data-verCodigo') : opciones.verCodigo) == '1';
			loSelect.append('<option value=""></option>');

			$.ajax({
				type: "POST",
				url: "vista-comun/ajax/secciones.php",
				data: {tipo: lcTipo},
				dataType: "json"
			})
			.done(function( loData ) {
				try {
					if (loData.error == ''){
						$.each(loData.SECCIONES, function( lcKey, loSeccion ) {
							loSelect.append('<option value="' + lcKey + '">' + (lbVerCodigo ? lcKey + ' - ' : '') + loSeccion.DESCRIPCION + '</option>');
						});
					} else {
						fnAlert(loData.error);
					}
				} catch(err) {
					fnAlert('No se pudo realizar la busqueda de secciones.');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log();
				fnAlert("Se presentó un error al buscar secciones. \n"+jqXHR.responseText);
			});
		});

		return this; 
	};
 
}( jQuery ));

