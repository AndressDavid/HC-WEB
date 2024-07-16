(function ( $ ) {
	$.fn.conductaSeguir = function(toPrograma) {
		lcModulo = toPrograma;
		lnIngreso = aDatosIngreso.nIngreso
		lcVia = aDatosIngreso.cCodVia;
		lcSeccion = aDatosIngreso.cSeccion;

		this.each(function() {
			var loSelect = $(this);
			loSelect.append('<option selected> </option>');

			$.ajax({
				type: "POST",
				url: "vista-comun/ajax/conductaSeguir.php",
				data: {accion:'conductaaSeguir', ingreso:lnIngreso, via:lcVia, seccion:lcSeccion, modulo:lcModulo},
				dataType: "json"
			})
			.done(function( loTipos ) {
				try {
					if (loTipos.error == ''){
						$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
							loSelect.append('<option value="' + lcKey + '">' + loTipo.desc + '</option>');
						});
					} else {
						alert(loTipos.error);
					}
				} catch(err) {
					alert('No se pudo realizar la busqueda de conducta a seguir.');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log();
				alert("Se presentó un error al buscar conducta a seguir. \n"+jqXHR.responseText);
			});
		});

		return this; 
	};

}( jQuery ));

