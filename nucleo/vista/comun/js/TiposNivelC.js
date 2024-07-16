(function ( $ ) {

	$.fn.tiposNivelC = function(toOpciones) {

		// Configuración
		var opciones = $.extend({}, toOpciones );

		this.each(function() {
			
			var loSelect = $(this),
				lcTipo = loSelect.attr('data-tipo') ? $(this).attr('data-tipo') : opciones.tipo;
				
			loSelect.append('<option selected> </option>');
			
			$.ajax({
				type: "POST",
				url: "vista-comun/ajax/TiposNivelC.php",
				data: {lcTipoDato: lcTipo},
				dataType: "json"
			})
			.done(function( loTipos ) {
				try {
					if (loTipos.error == ''){
						$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
							loSelect.append('<option value="' + lcKey + '">' + loTipo + '</option>');
						});
					} else {
						alert(loTipos.error);
					}
				} catch(err) {
					alert('No se pudo realizar la busqueda de tipos de niveles de conciencia.');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log();
				alert("Se presentó un error al buscar tipos de niveles de conciencia. \n"+jqXHR.responseText);
			});
		});

		return this; 
	};
 
}( jQuery ));






















$(function () {
	getTiposNivelC();
});

function getTiposNivelC() {
	// adiciona opción en blanco
	$('#selNivelC').append('<option selected> </option>');

	$.ajax({
		type: "POST",
		url: "vista-comun/ajax/TiposNivelC.php",
		data: {},
		dataType: "json"
	})
	.done(function( loTipos ) {
		try {
			if (loTipos.error == ''){
				$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
					$('#selNivelC').append('<option value="' + lcKey + '">' + loTipo + '</option>');
				});
			} else {
				infoAlert(loTipos.error + ' ', "warning");
			}
		} catch(err) {
			infoAlert('No se pudo realizar la busqueda de Tipos de Nivel de Conciencia. ', "danger");
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		infoAlert('Se presentó un error al buscar Tipos de Nivel de Conciencia. ', "danger");
	});
}
	
function infoAlert(tcHtml, tcClase) {
	var lcIcon = '<i class="fa fa-exclamation-triangle"></i> ';
	$('#divIngresoInfo')
		.html(lcIcon+tcHtml)
		.addClass("alert alert-"+tcClase)
		.attr("role", "alert");
}

