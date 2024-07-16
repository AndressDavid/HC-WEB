(function($){

	$.fn.tiposMedica = function(toOpciones){
		// Configuración
		var opciones = $.extend({tipo: "Dosis",}, toOpciones);

		this.each(function(){
			var loSelect = $(this),
				lcTipo = loSelect.attr('data-tipo') ? $(this).attr('data-tipo') : opciones.tipo;
			loSelect.append('<option selected> </option>');
			
			$.ajax({
				type: "POST",
				url: "vista-comun/ajax/tiposMedica.php",
				data: {lcTipoDato: lcTipo},
				dataType: "json"
			})
			.done(function( loTipos ) {
				try {
					if (loTipos.error == ''){
						if (lcTipo=='Frecuencia'){
							$.each(loTipos.TIPOS, function(lcKey, loTipo) {
								if (loTipo.opcional1==='A'){
									loSelect.append('<option value="' + parseInt(lcKey) + '" data-unidad="' + loTipo.opcional3 + '">' + loTipo.desc + '</option>');
								}	
							});
						}else{
							$.each(loTipos.TIPOS, function(lcKey, loTipo) {
								loSelect.append('<option value="' + parseInt(lcKey) + '">' + loTipo + '</option>');
							});
						}
						if (typeof opciones.functionPost == 'function') {
							opciones.functionPost();
						}
					} else {
						alert(loTipos.error);
					}
				} catch(err) {
					alert('No se pudo realizar la busqueda de tipos de Medicamento.');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log();
				alert("Se presentó un error al buscar tipos de medicamento. \n"+jqXHR.responseText);
			});
		});

		return this;
	};

}(jQuery));

