//$(document).ready(function(e){
	$(function(){
		$('#triagesDesde').focus();
		
		$('#btnNuevaValoracion').on('click', valoracionTriage);
		
		$('#btnNuevaValoracion').on('keydown', function(event){
			if(event.which == 13){
				valoracionTriage();
			}
		});
		
		
	});
//});

function valoracionTriage(){
	window.location.href = 'modulo-triage&target=valoracionTriage';

	
}
