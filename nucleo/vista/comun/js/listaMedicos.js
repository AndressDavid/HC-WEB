(function($){
$.fn.listaMedicos = function(toOpciones) {

	// Configuración
	var opciones = $.extend({
		tipos: "1,3,4,6,10,11,12,13",	// Tipos de usuario
		activos: "1",					// '1'=Solo Activos, '0'=Todos
		mostrarRM: false
	}, toOpciones);

	this.each(function() {
		var loInput = $(this),
			lcTipos = loInput.attr('data-tipos') ? loInput.attr('data-tipos') : opciones.tipos,
			lcActiv = loInput.attr('data-activos')?loInput.attr('data-activos'):opciones.activos,
			lcUrl = "vista-comun/ajax/listaMedicos?accion=medicosNombre&tipos="+lcTipos+"&activos="+lcActiv;
		loInput.attr('data-reg','').attr('data-nombre','')
		.autoComplete({
			preventEnter: true,
			resolverSettings: {
				url: lcUrl,
				queryKey: 'nombre',
				requestThrottling: 500,
				fail: function(e){}
			},
			events: {
				searchPost: function(loRes, loJQElement){
					if (loRes.error.length>0){
						fnAlert(loRes.error);
						return false;
					} else {
						return loRes.medicos;
					}
				},
			},
			formatResult: function (taItem) {
				laItem = {value: '', text: ''};
				if(taItem.REGISTRO!==undefined && taItem.MEDICO!==undefined){
					if(taItem.MEDICO.length>0 && taItem.REGISTRO.length>0){
						if (opciones.mostrarRM) {
							laItem = {
								value: taItem.REGISTRO,
								text: taItem.MEDICO + ' - '+ taItem.REGISTRO
							};
						} else {
							laItem = {
								value: taItem.REGISTRO,
								text: taItem.MEDICO
							};
						}
					}
				}
				return laItem;
			},
			noResultsText: 'No hay coincidencias',
		}).autoComplete('set', {
			REGISTRO: '',
			MEDICO: ''
		}).on('autocomplete.select', function(toEvt, toItem) {
			if(toItem.REGISTRO!=='' && toItem.MEDICO!==''){
				$(this).attr('data-reg',toItem.REGISTRO).attr('data-nombre',toItem.MEDICO);
			}
		}).on('autocomplete.freevalue', function(toEvt, tcValue) {
			$(this).attr('data-reg','').attr('data-nombre','');
			$(this).autoComplete('clear');
		});

	});
	return this;

}; }(jQuery));
