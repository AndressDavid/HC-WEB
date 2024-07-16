(function ( $ ) {
	$.fn.selectUsuarioCargo = function() {
		this.each(function() {
			var loSelect = $(this);		
			var lnTipo = parseInt(loSelect.data('tipo') ? loSelect.data('tipo') : '0');
			var lnArea = parseInt(loSelect.data('area') ? loSelect.data('area') :'0');
			loSelect.children().remove();
			loSelect.empty().append('<option value=""></option>');

			$.ajax({
				type: "POST",
				url: "vista-comun/ajax/selectUsuarioCargo.php",
				data: {tipoCargo: lnTipo, areaCargo: lnArea},
				dataType: "json"
			})
			.done(function( loTipos ) {
				try {
					if (loTipos.error == ''){
						$.each(loTipos.CARGOS, function( lcKey, lcCargo ) {
							loSelect.append('<option value="' + lcKey + '">' + lcCargo + '</option>');
						});
					} else {
						alert(loTipos.error);
					}
				} catch(err) {
					alert('No se pudo realizar la busqueda de cargos para usuario.');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {				
				alert("Se presentó un error al buscar cargos para usuario. \n"+jqXHR.responseText);
			});
		});

		return this; 
	};
 
}( jQuery ));

