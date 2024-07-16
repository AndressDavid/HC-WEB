(function ( $ ) {
	$.fn.tiposEstadosIngreso = function(toOpciones) {
		let opciones = $.extend({}, toOpciones );

		this.each(function() {
			let loSelect = $(this),
				lcTipo = loSelect.attr('data-tipo') ? $(this).attr('data-tipo') : opciones.tipo;
			loSelect.empty();
			loSelect.append('<option selected> </option>');
			$.ajax({
				type: "POST",
				url: "vista-comun/ajax/tiposEstadosIngreso.php",
				data: {lcTipoDato: lcTipo},
				dataType: "json"
			})
			.done(function( loTipos ) {
				try {
					if (loTipos.error == ''){
						$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
							loSelect.append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
						});
						
						if (toOpciones!=''){
							loSelect.val(toOpciones);
						}	
					} else {
						fnAlert(loTipos.error);
					}
				} catch(err) {
					fnAlert('No se pudo realizar la busqueda de tipos de estados ingreso.');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log();
				fnAlert("Se presentó un error al buscar tipos de estados ingreso. \n"+jqXHR.responseText);
			});
		});

		return this; 
	};
 
}( jQuery ));
