(function ( $ ) {

	$.fn.tiposCausa = function(toOpciones) {
		// Configuración
		var opciones = $.extend({}, toOpciones );
		lcCodigoVia=aDatosIngreso['cCodVia'];

		this.each(function() {
			var loSelect = $(this),
				lcOrigen = loSelect.attr('data-origen') ? $(this).attr('data-origen') : (opciones.origen ?? '');

			loSelect.html('').append('<option selected> </option>');

			$.ajax({
				type: "POST",
				url: "vista-historiaclinica/ajax/TiposCausa.php",
				data: {origen: lcOrigen, viaingreso: lcCodigoVia},
				dataType: "json"
			})
			.done(function(loTipos) {
				try {
					if (loTipos.error == ''){
						$.each(loTipos.TIPOS, function(lcKey, loTipo) {
							loSelect.append('<option value="'+loTipo.codigo+'" data-origen="'+loTipo.origen+'" data-codigoIOHC="'+loTipo.codigoIOHC+'">'+loTipo.desc+'</option>');
						});
					} else {
						alert(loTipos.error);
					}
				} catch(err) {
					alert('No se pudo realizar la busqueda de Tipos de Causa.');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log();
				alert("Se presentó un error al buscar Tipos de Causa. \n"+jqXHR.responseText);
			});
		});

		return this;
	};

}( jQuery ));
