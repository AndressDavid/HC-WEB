var lcUrlAjax = 'vista-triage/ajax/triage';
$(document).ready(function(e){
	$(function(){
		//cargarUbicaciones();
		$('#cmbUbicacion').focus();

		$('#btnAceptar').on('click', pacientesTriage);

		$('#btnAceptar').on('keydown', function(event) {
			if (event.which == 13) {
				pacientesTriage();
			}
		});
	});
});

function pacientesTriage(){
		window.location.href = 'modulo-triage&target=pacientesTriage';
}

function cargarUbicaciones(){
	var loOption;
	$('#cmbUbicacion').append(loOption);
	var laData = {
		accion : 'listaUbicaciones'
	};
	$.ajax({
		url : lcUrlAjax,
		data : laData,
		type : 'POST',
		dataType : 'json'
	})
	.done(function(laUbicaciones){
		try{
			if(!$.isEmptyObject(laUbicaciones) == true){
					fnAlert('Se pudo realizar la busqueda de secciones', 'TRIAGE', 'fas fa-exclamation-circle','red','medium');
				$.each(laUbicaciones, function(id, value){
					var ubicacion = {
						'lcCodUbicacion' : ''+id+'',
						'lcDescriUbicacion' : ''+value['LCDESCRIPCION1']+''
					};
					var loOption = new Option(ubicacion.lcDescriUbicacion, ubicacion.lcCodUbicacion);
					$('#cmbUbicacion').append(loOption);
				});
			}
		}catch(error){
			fnAlert('No se pudo realizar la busqueda de secciones', "danger", false);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		console.log(jqXHR.responseText);
		fnAlert('Se presento un error al cargar las ubicaciones y no se obtuvo el resultado deseado,', 'TRIAGE', 'fas fa-exclamation-circle','red','medium');
	});
}