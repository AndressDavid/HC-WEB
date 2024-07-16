var gcUrlajax = "vista-soportescm/ajax/ajax",
	gcVel="fast";

function postAjax(tcMensaje, toEnviar, tfSuccess, tfError, tfComplete) {
	$.post(
		gcUrlajax,
		toEnviar,
		function(taRetorno) {
			if (taRetorno.error==''){
				if (typeof tfSuccess == 'function') { tfSuccess(taRetorno);	}
			} else {
				fnAlert(taRetorno.error);
			}
		},
		'json'
	)
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al '+tcMensaje+'.');
		if (typeof tfError == 'function') { tfError();	}
	})
	.always(tfComplete);
}


function iniciarEstados(tfFuncionPost) {
	var lcMensaje = 'iniciar Estados de Soportes de CM';
	$.post(
		gcUrlajax,
		{accion: 'listaEstados'},
		function (taRetorno) {
			if (taRetorno.error==''){
				if (typeof tfFuncionPost == 'function') {
					tfFuncionPost(taRetorno);
				}
			} else {
				fnAlert(taRetorno.error);
			}
		},
		'json'
	)
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al '+lcMensaje+'.');
	});
}
