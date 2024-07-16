(function($) {

	$.fn.tiposDocumentos = function(toOpciones) {

		// Configuración
		var opciones = $.extend({
			// Valores por defecto
			tipo: "T",			// Texto en los options: A = abreviatura, D = descripción, T = abreviatura + descripción)
			horti: "0",			// mostrar documentos con HORTI=0 (1=true)
			valor: "",
			fnpos: false		// función a ejecutar después de cargar los datos
		}, toOpciones );

		this.each(function() {
			var loSelect = $(this),
				lcTipo = loSelect.attr('data-tipo') ? loSelect.attr('data-tipo') : opciones.tipo,
				lbHorti = loSelect.attr('data-horti') ? loSelect.attr('data-horti') : opciones.horti,
				lcValor = loSelect.attr('data-valor') ? loSelect.attr('data-valor') : opciones.valor,
				lfunPos = loSelect.attr('data-fnpos') ? loSelect.attr('data-fnpos') : opciones.fnpos;
			loSelect.append('<option value=""></option>');

			$.ajax({
				type: "POST",
				url: "vista-comun/ajax/tiposDocumentos.php",
				data: {descrip: lcTipo, horti: lbHorti},
				dataType: "json"
			})
			.done(function(loTipos) {
				try {
					if (loTipos.error == ''){
						$.each(loTipos.TIPOS, function(lcKey, loTipo) {
							loSelect.append('<option value="' + lcKey + '"' +(lcKey==lcValor?' selected ': '') + '>' + loTipo + '</option>');
						});
						if(typeof lfunPos === 'function'){
							lfunPos();
						}
					} else {
						alert(loTipos.error);
					}
				} catch(err) {
					alert('No se pudo realizar la busqueda de tipos de documento.');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log();
				alert("Se presentó un error al buscar tipos de documento. \n"+jqXHR.responseText);
			});
		});

		return this;
	};

}(jQuery));

