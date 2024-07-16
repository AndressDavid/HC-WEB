(function ( $ ) {
	$.fn.condicionDestinoEgreso = function(toEstado) {
		lcEstado = toEstado;
		
		this.each(function() {
			var loSelect = $(this);
			if (lcEstado==''){
				loSelect.append('<option selected> </option>');	
			}	
			$.ajax({
				type: "POST",
				url: "vista-comun/ajax/condicionDestinoEgreso.php",
				data: {accion:'condicionDestinoEgreso', estado: lcEstado},
				dataType: "json"
			})
			.done(function( loTipos ) {
				try {
					if (loTipos.error == ''){
						$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
							loSelect.append('<option value="' + lcKey + '">' + loTipo.desc + '</option>');
						});
					} else {
						fnAlert(loTipos.error);
					}
				} catch(err) {
					fnAlert('No se pudo realizar la busqueda de condición destino egreso.');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log();
				fnAlert("Se presentó un error al buscar condición destino egreso. \n"+jqXHR.responseText);
			});
		});

		return this; 
	};

}( jQuery ));

