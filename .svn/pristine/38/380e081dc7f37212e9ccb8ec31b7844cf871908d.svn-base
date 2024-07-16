(function ( $ ) {
	$.fn.modalidadGrupoServicio = function(toPrograma) {

		this.each(function() {
			var loSelect = $(this);
			lnNumRegistros=0;
			$.ajax({
				type: "POST",
				url: "vista-comun/ajax/modalidadGrupoServicio.php",
				data: {accion:'modalidadGrupoServicio'},
				dataType: "json"
			})
			.done(function( loTipos ) {
				try {
					if (loTipos.error == ''){
						$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
							lnNumRegistros++;		
						});	
						if (lnNumRegistros>1){
							loSelect.append('<option selected> </option>');	
						}
							
						$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
							loSelect.append('<option value="' + lcKey + '">' + loTipo.desc + '</option>');
						});
					} else {
						fnAlert(loTipos.error);
					}
				} catch(err) {
					fnAlert('No se pudo realizar la busqueda de modalidad grupo servicio.');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log();
				fnAlert("Se presentó un error al buscar modalidad grupo servicio. \n"+jqXHR.responseText);
			});
		});

		return this; 
	};

}( jQuery ));

