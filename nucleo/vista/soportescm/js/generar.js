var gnNumTareas=0;

$(function () {
	$(".ctrls-adicionales").hide();
	iniciarTiposSoportesIndex();
	$("#selTipoSoportes").on('change', iniciarSoportesIndex);
	$('#chkTipoTodos').on('click', marcarTodos);
	$("#divSoportes").on('click', '.chk-tipo-soporte', validaSelTodos);
	$('#btnAddSoportesIngresoGenerar').on('click', addSoportesIngreso);
})

function iniciarTiposSoportesIndex() {
	var lcMensaje = 'iniciar Tipos de Soportes de CM';
	var loSelect = $('#selTipoSoportes');
	loSelect.empty().attr('disabled',true);
	postAjax(
		lcMensaje,
		{accion: 'listaTipos'},
		function(taRetorno){
			try {
				loSelect.append('<option value=""></option>');
				$.each(taRetorno.tipos, function(lcKey, loTipo) {
					loSelect.append('<option value="' + loTipo.CODIGO + '">' + loTipo.TITULO + '</option>');
				});
				loSelect.attr('disabled',false);
			} catch(err) {
				fnAlert('No se pudo '+lcMensaje+'.');
			}
		}
	);
}

function iniciarSoportesIndex() {
	var lcMensaje = 'iniciar Soportes de CM';
	var lcTipoSoporte = $('#selTipoSoportes').val();
	var loDiv=$('#divSoportes');
	loDiv.empty();
	$('.ctrls-adicionales').hide(gcVel);

	if (lcTipoSoporte.length>0) {
		postAjax(
			lcMensaje,
			{accion: 'listaSoportes', tipo: lcTipoSoporte},
			function(taRetorno){
				try {
					$.each(taRetorno.soportes, function(lcCodigo, lcSoporte) {
						loDiv.append([
							'<div class="col-12 col-md-6 col-lg-4 col-xl-3">',
							'<div class="form-check pb-2">',
							'<input id="chkTipo'+lcCodigo+'" name="chkTipo'+lcCodigo+'" class="form-check-input chk-tipo-soporte" type="checkbox" value="'+lcCodigo+'" checked>',
							'<label for="chkTipo'+lcCodigo+'" class="form-check-label">'+lcCodigo+' - '+lcSoporte+'</label>',
							'</div></div>',
						].join(''));
					});
					$('#divSoportes .form-check-label').css('font-weight','normal');
					//$('#chkCarpetaTransf,#chkTipoTodos').prop("checked", true);
					$('#chkTipoTodos').prop("checked", true);
					$('.ctrls-adicionales').show(gcVel);
					$("#txtIngreso").focus();
				} catch(err) {
					fnAlert('No se pudo '+lcMensaje+'.');
				}
			}
		);
	}
}

function generarSoportes() {
	if (validar()===false) {
		return;
	}
	var lnIngreso = parseInt($("#txtIngreso").val()),
		laSoportes = [];
	$('.chk-tipo-soporte:checked').each(function(){
		laSoportes.push($(this).val());
	});

	var lcIdCol = 'div'+lnIngreso+uniqid('_'),
		lcFinal = '',
		lcCol = [
			'<div id="'+lcIdCol+'" class="col-12">',
				'<div class="alert alert-secondary" role="alert">',
					'<i class="fas fa-circle-notch fa-xs fa-spin" style="color:#f00"></i> ',
					'Programando soportes del ingreso <strong>'+lnIngreso+'</strong>',
				'</div>',
			'</div>'
		].join('');
	$("#divResultado").prepend(lcCol);

	var loEnviar = {
		accion: 'generarSoportes',
		tipo: $("#selTipoSoportes").val(),
		soportes: laSoportes,
		ingreso: lnIngreso,
		todos: ($('#chkTipoTodos').prop("checked") ? 1 : 0),
		//guardatransf: ($('#chkCarpetaTransf').prop("checked") ? 1 : 0)
		guardatransf: 0
	}

	$.post(
		gcUrlajax,
		loEnviar,
		function (taRetorno) {
			if (taRetorno.error.length==0){
				lcFinal = [
					'<div class="alert alert-primary alert-dismissible fade show" role="alert">',
						'<i class="fas fa-check-circle" style="color:#0b0"></i> ',
						'Se programaron soportes para el ingreso <b>'+lnIngreso+'</b>',
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>',
					'</div>'
				].join('');
			} else {
				var lcError = taRetorno.error.join(' - ');
				lcFinal = [
					'<div class="alert alert-danger" role="alert">',
						'<i class="fas fa-times-circle" style="color:#f00"></i> ',
						'Ingreso <b>'+lnIngreso+'</b> - Error: '+lcError,
					'</div>'
				].join('');
			}
		},
		'json'
	)
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		lcFinal = '<div class="alert alert-danger" role="alert">Se presentó un error al programar soportes para el ingreso <b>'+lnIngreso+'</b></div>';
	})
	.always(function() {
		$('#'+lcIdCol).html(lcFinal);
		if (gnNumTareas>0) {
			gnNumTareas--;
		} else {
			gnNumTareas=0;
		}
	});

	// limpiar
	limpiarTodo();
}


function addSoportesIngreso() {
	if (validar()===false) {
		return;
	}
	var lnIngreso = parseInt($("#txtIngreso").val()),
		laSoportes = [];
	$('.chk-tipo-soporte:checked').each(function(){
		laSoportes.push($(this).val());
	});

	var lcIdCol = 'div'+lnIngreso+uniqid('_'),
		lcFinal = '',
		lcCol = [
			'<div id="'+lcIdCol+'" class="col-12">',
				'<div class="alert alert-secondary" role="alert">',
					'<i class="fas fa-circle-notch fa-xs fa-spin" style="color:#f00"></i> ',
					'Programando generación de soportes del ingreso <strong>'+lnIngreso+'</strong>',
				'</div>',
			'</div>'
		].join('');
	$("#divResultado").prepend(lcCol);

	var loEnviar = {
		accion: 'addSoportesIngreso',
		tipo: $("#selTipoSoportes").val(),
		soportes: laSoportes,
		ingreso: lnIngreso,
	}
	console.log(loEnviar);
	$.post(
		gcUrlajax,
		loEnviar,
		function (taRetorno) {
			if (taRetorno.error.length==0){
				lcFinal = [
					'<div class="alert alert-primary alert-dismissible fade show" role="alert">',
						'<i class="fas fa-check-circle" style="color:#0b0"></i> ',
						'Se programó generación de soportes del ingreso <b>'+lnIngreso+'</b>',
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>',
					'</div>'
				].join('');
			} else {
				var lcError = taRetorno.error.join(' - ');
				lcFinal = [
					'<div class="alert alert-danger" role="alert">',
						'<i class="fas fa-times-circle" style="color:#f00"></i> ',
						'Ingreso <b>'+lnIngreso+'</b> - Error: '+lcError,
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>',
					'</div>'
				].join('');
			}
		},
		'json'
	)
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		lcFinal = '<div class="alert alert-danger" role="alert">Se presentó un error al programar soportes para el ingreso <b>'+lnIngreso+'</b></div>';
	})
	.always(function() {
		$('#'+lcIdCol).html(lcFinal);
		if (gnNumTareas>0) {
			gnNumTareas--;
		} else {
			gnNumTareas=0;
		}
	});

	// limpiar
	limpiarTodo();
}

function validar(){
	// validar
	var lnNumCheckMarca = $('.chk-tipo-soporte:checked').length;
	if (lnNumCheckMarca==0) {
		fnAlert('Debe seleccionar al menos un soporte a generar.');
		return false;
	}
	var lnIngreso = parseInt($("#txtIngreso").val());
	if (lnIngreso<2000000 || lnIngreso>99999999 || isNaN(lnIngreso)) {
		$("#txtIngreso").focus();
		fnAlert('Número de ingreso incorrecto, revise por favor.');
		return false;
	}
	if (gnNumTareas>=10) {
		fnAlert('Se están consultando 10 ingresos, espere por favor');
		return false;
	}
	gnNumTareas++
	return true;
}

function marcarTodos(){
	$('.chk-tipo-soporte').prop("checked", $(this).prop("checked"));
}

function validaSelTodos() {
	var lnNumCheck = $('.chk-tipo-soporte').length;
	var lnNumCheckMarca = $('.chk-tipo-soporte:checked').length;
	$('#chkTipoTodos').prop("checked", lnNumCheck==lnNumCheckMarca);
}

function limpiarTodo() {
	$("#txtIngreso").val('').focus();
}
