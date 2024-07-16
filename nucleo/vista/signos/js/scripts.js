$(function () {
	$('.ingresoHabitacion').on('click', function() {
		$(location).attr('href','modulo-signos&ingreso='+$(this).data("ingreso")+'&seccion='+$(this).data("seccion"));
	});
	
	$('.btnAccionERR').on('click', function() {
		$('#btnAccionERR').data("objeto",$(this).attr('id'));
		$('#btnAccionERR').data("ingreso",$(this).data("ingreso"));
		$('#btnAccionERR').data("accion",$(this).data("accion"));
		
		$('#ingresoAccionERR').html($(this).data("ingreso"));
		$('#nombreAccionERR').html($(this).data("nombre"));
		switch($(this).data("accion")) {
			case "marcar":
				lcTexto = "&iquest;Desea marcar la llegada del equipo de respuesta r&aacute;pida?";
				lcAccion = "Marcar";
				break;
			default:
				lcTexto = "&iquest;Desea Activar alerta?";
				lcAccion = "Activar";
		}
		$('#textoAccionERR').html(lcTexto);
		$('#AccionERR').html(lcAccion);
		
		$('#infoAccionERR').html('');
		$('#formAccionErr').modal('show');
	});
	
	$('#btnAccionERR').on('click', function() {		
		var lcObjeto = $(this).data("objeto");
		$.ajax({
			type: 'POST',
			url: "vista-alerta-temprana/registroAccion",
			data: { ingreso: $(this).data("ingreso"), accion: $(this).data("accion")}
		})
		.done(function(response) {
			$('#'+lcObjeto).remove();
			$('#formAccionErr').modal('hide');
		})
		.fail(function(data) {
			$('#infoAccionERR').html('<i class="fa fa-exclamation-triangle"></i> Se presento un error al guardar el ingreso').addClass("alert").addClass("alert-danger").attr("role","alert");
		});		
	});
});


