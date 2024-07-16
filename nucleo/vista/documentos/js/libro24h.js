var gcUrlAjax = "vista-documentos/ajax/ajax",	// Ruta para los ajax
	gnTimeAnimation = "fast";

$(function() {
	$("#inpTxtIngreso").focus();
	$('#btnBuscar').on('click', buscar);
	$('#btnLimpiar').on('click', limpiar);

	if(gcHcwIngreso>0){
		$("#inpTxtIngreso").val(gcHcwIngreso);
		buscar();
	}
});


function buscar()
{
	var lnIngreso = $('#inpTxtIngreso').val();

	if(lnIngreso==0) {
		infoAlert($('#divIngresoInfo'), 'Ingreso a consultar es obligatorio', 'warning', 'exclamation-triangle', false);
		$('#inpTxtIngreso').focus();
		return;
	}

	$('#filtroIngreso').hide(gnTimeAnimation);
	$('#divIconoEspera,#divLstDocumentos').show(gnTimeAnimation);

	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'ingreso',ingreso:lnIngreso,ult24h:1},
		dataType: "json"
	})
	.done(function(loIngreso) {
		try {
			if (loIngreso.error == ''){
				if (loIngreso.nIngreso > 0){
					infoAlertClear( $('#divIngresoInfo') );
					$('#infoPaciente').html(
							'Ingreso No. <span class="badge badge-success">'+loIngreso.nIngreso+'</span> - ' +
							'Documento <span class="badge badge-success">'+loIngreso.cDocumento+'</span> - ' +
							'Paciente <span class="badge badge-success">'+loIngreso.cNombre+'</span>'
						);
					$('#divLstDocumentos').show(gnTimeAnimation);

					buscarDocumentos(lnIngreso);
				} else {
					infoAlert($('#divIngresoInfo'), 'No se encontró el número de ingreso ' + lnIngreso, 'warning', 'exclamation-triangle', false);
					$('#divIconoEspera').hide(gnTimeAnimation);
				}
			} else {
				infoAlert($('#divIngresoInfo'), loIngreso.error, 'warning', 'exclamation-triangle', false);
				$('#divIconoEspera').hide(gnTimeAnimation);
			}

		} catch(err) {
			infoAlert($('#divIngresoInfo'), 'No se pudo realizar la busqueda ' + lnIngreso, 'danger', 'exclamation-triangle', false);
			$('#divIconoEspera').hide(gnTimeAnimation);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		infoAlert($('#divIngresoInfo'), 'Se presentó un error al buscar el ingreso ' + lnIngreso, 'danger', 'exclamation-triangle', false);
		$('#divIconoEspera').hide(gnTimeAnimation);
	});
}


/*
 *	Buscar los documentos del paciente
 */
function buscarDocumentos(tnIngreso)
{
	var loItemIng;
	$('#divIconoEspera').show(gnTimeAnimation);
	$('#wrpLstDocumentos').hide(gnTimeAnimation);

	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'ult24h', ingreso:tnIngreso}
	})
	.done(function(lcDocumentos) {
		if(typeof lcDocumentos==='string'){
			$('#divContenidoLibro').html(lcDocumentos);
			$('#wrpLstDocumentos').show(gnTimeAnimation);
		}else{
			if(typeof lcDocumentos==='object'){
				if(lcDocumentos.error===''){
					infoAlert($('#divIngresoInfo'), 'Se presentó un error al consultar Libro.', 'danger', 'exclamation-triangle', false);
				}else{
					infoAlert($('#divIngresoInfo'), lcDocumentos.error, lcDocumentos.tipoerror, 'exclamation-triangle', false);
				}
			}
		}
		$('#divIconoEspera').hide(gnTimeAnimation);
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		$('#divIconoEspera').hide(gnTimeAnimation);
		infoAlert($('#divIngresoInfo'), 'Se presentó un error al buscar documentos', 'danger', 'exclamation-triangle', false);
	});
}

function limpiar()
{
	$('#divLstDocumentos').hide(gnTimeAnimation);
	$('#wrpLstDocumentos').hide(gnTimeAnimation);
	$('#infoPaciente').html('');
	$('#divIconoEspera').hide(gnTimeAnimation);
	infoAlertClear( $('#divIngresoInfo') );
	$('#filtroIngreso').show(gnTimeAnimation);
	$("#inpTxtIngreso").val("").focus();
}
