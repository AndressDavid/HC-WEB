var gnNoCopyPaste = 0;
$(function() {
	$(".nocopypaste").on( {
		'paste': function(e){
			e.preventDefault();
			errorNoCopyPaste($(this),'Pegar');
		},
		'cut': function(e){
			e.preventDefault();
			errorNoCopyPaste($(this),'Cortar');
		},
		'copy': function(e){
			e.preventDefault();
			errorNoCopyPaste($(this),'Copiar');
		},
		'drop': function(e){
			e.preventDefault();
			errorNoCopyPaste($(this),'Soltar');
		},
	});
});
function errorNoCopyPaste(toObjeto,tcAccion){
	var lcId = 'nocopypaste-error'+gnNoCopyPaste,
		lcIdObj = '#'+lcId,
		lcDiv = '<div id="'+lcId+'" class="error invalid-tooltip nocopypaste-error">Acción '+tcAccion+' inválida.</div>';
	toObjeto.after(lcDiv);
	$(lcIdObj).show();
	setTimeout(function() {
		$(lcIdObj).fadeOut('slow');
		$('.nocopypaste-error').remove();
	}, 1000);
}
