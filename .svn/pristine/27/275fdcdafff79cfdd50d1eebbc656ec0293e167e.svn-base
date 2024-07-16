(function ( $ ) {

	$.fn.tiposPlanes = function(toOpciones) {
		var opciones = $.extend({}, toOpciones );
		this.each(function() {
			var loSelect = $(this),
				lcTipo = loSelect.attr('data-tipo') ? $(this).attr('data-tipo') : opciones.tipo;
				
			$.ajax({
				type: "POST",
				url: "vista-comun/ajax/tiposPlanes.php",
				data: {lcTipoDato: lcTipo},
				dataType: "json"
			})
			.done(function( loTipos ) {
				try {
					if (loTipos.error == ''){
						$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
							if (typeof aDatosIngreso=='object'){
								loSelect.append('<option value="' + loTipo.PLNCON + '"' + (loTipo.PLNCON==aDatosIngreso['cPlan']?' selected="selected"':'') + '>' + loTipo.DSCCON + '</option>');
							}else{
								loSelect.append('<option value="' + loTipo.PLNCON + '">' + loTipo.DSCCON + '</option>');
							}	
						});
					} else {
						alert(loTipos.error);
					}
				} catch(err) {
					alert('No se pudo realizar la busqueda de Tipos de Planes.');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log();
				alert("Se presentó un error al buscar Tipos de Planes. \n"+jqXHR.responseText);
			});
		});

		return this;
	};

}( jQuery ));

