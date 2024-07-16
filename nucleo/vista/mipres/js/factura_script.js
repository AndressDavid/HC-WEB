var lbConfirm = false;

$(function () {
	getTiposConsultas('25');
	getVariables(3);
	$('#btnConsultar').on('click', consultarMiPresDP);


	$("#selConsulta").on('focusout', function(){
		if ($("#selConsulta").val()=='0') {
			// Datos de la prescripción
			$('#61000001').off('focusout').on('focusout', consultaDatosPres);
			$('#61000002').off('focusout').on('focusout', consultaDatosPres);
			$('#61000003').off('focusout').on('focusout', consultaDatosPres);
			$('#61000007').off('focusout').on('focusout', consultaDatosFact);
		}
	});

});


/*
 *	Organizar datos obtenidos uno a uno
 */
function organizarMiPres(taMiPres) {
	var lcRes='';

	// Facturas una a una
	$.each(taMiPres, function(lnIndex, loItem) {
		var lcHead = 'Head' + lnIndex,
			lcCard = 'Card' + lnIndex;
		lcRes += '<div class="card"><div class="card-header" id="'+lcHead+'"><h6 class="mb-0">'
			+ '<button class="btn collapsed mr-2" type="button" data-toggle="collapse" data-target="#'+lcCard+'" aria-expanded="false" aria-controls="'+lcCard+'">Ver</button> '
			+ ' ID: ' + loItem.ID //+ ' - ID Entrega: ' + loItem.IDFacturacion
			+ ' - Pres: ' + loItem.NoPrescripcion
			+ ' - Fecha: ' + loItem.FecFacturacion
			+ ' - Tecno: ' + loItem.TipoTec + ' cns.' + loItem.ConTec
			+ ' - Pac: ' + loItem.TipoIDPaciente + ' ' + loItem.NoIDPaciente
			+ ' - Est: ' + goParData['estfacturacion'].valores[loItem.EstFacturacion]
			+ '</h6></div>'
			+ '<div id="'+lcCard+'" class="collapse" aria-labelledby="'+lcHead+'" data-parent="#divResFinal"><div class="card-body">'
			+ fnCamposItem(loItem, 'tblItem' + lnIndex) + '</div></div></div>';
	});

	return lcRes;
}


/*
 *	Consulta la prescripción y llena datos
 */
function consultaDatosPres() {
	var lnNumPres = $('#61000001').val(),
		lnTipSrv = $('#61000002').val(),
		lnNumSrv = $('#61000003').val();

	//if (lnNumPres === null || lnNumPres === '' || lnTipSrv === null || lnTipSrv === '' || lnNumSrv === null || lnNumSrv === '') {
	if (lnNumPres === '' || lnTipSrv === '' || lnNumSrv === '') {
		// ¿Limpiar campos?
		return;

	} else {
		$.ajax({
			type: "POST",
			url: gcUrlAjax,
			data: {accion:'datpres', numprs:lnNumPres, tiptec:lnTipSrv, contec:lnNumSrv },
			dataType: "json"
		})
		.done(function( loRta ) {
			try {
				if (loRta.error == '') {

					$('#61000004').val(loRta.TipoIDPaciente);
					$('#61000005').val(loRta.NoIDPaciente);
					$('#61000008').val(loRta.NoIDEPS);
					$('#61000009').val(loRta.CodEPS);
					$('#61000010').val(loRta.CodSerTecAEntregado);

				} else {
					infoAlert($('#divInfo'), loRta.error + ' ', 'warning', 'exclamation-triangle', false);
				}
			} catch(err) {
				infoAlert($('#divInfo'), 'No se pudo realizar la busqueda de prescripción. ', 'danger', 'exclamation-triangle', false);
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			infoAlert($('#divInfo'), 'Se presentó un error al obtener datos de la prescripción', 'danger', 'exclamation-triangle', false);
		});
	}
}

/*
 *	Consulta la factura y llena datos
 */
function consultaDatosFact() {
	var lnNumFac = $('#61000007').val();

	if (lnNumFac === '') {
		return;

	} else if (lnNumFac.length<7 || lnNumFac.length>8) {
		fnAlert("Número de Factura no válido");
		$('#61000007').val('');
		return;

	} else {
		$.ajax({
			type: "POST",
			url: gcUrlAjax,
			data: {accion:'datfac', numfac:lnNumFac },
			dataType: "json"
		})
		.done(function( loRta ) {
			try {
				if (loRta.error == '') {
					if (!(loRta.Nit=='')) {
						var lcNitActual = $('#61000008').val();
						if (lcNitActual=='') {
							$('#61000008').val(loRta.Nit);
						} else if (!(loRta.Nit==lcNitActual)) {
							$.confirm({
								icon: 'fa fa-question-circle',
								title: 'Reemplazar NIT',
								content: 'El Nit de la factura ('+loRta.Nit+'), es diferente al regitrado en Nit EPS que recobra ('+lcNitActual+')<br>¿Desea modificar este campo?',
								columnClass: 'medium',
								buttons: {
									ok: {
										text: 'Aceptar',
										btnClass: 'btn-blue',
										action: function(okButton) {
											$('#61000008').val(loRta.Nit);
										},
									},
									cancel: { text: 'Cancelar', },
								},
								closeIcon: true,
							});
						}
					}
					$('#61000014').val(loRta.CuotaM);
					$('#61000015').val(loRta.Copago);

				} else {
					infoAlert($('#divInfo'), loRta.error + ' ', 'warning', 'exclamation-triangle', false);
				}
			} catch(err) {
				infoAlert($('#divInfo'), 'No se pudo realizar la busqueda de factura. ', 'danger', 'exclamation-triangle', false);
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			infoAlert($('#divInfo'), 'Se presentó un error al obtener datos de la factura', 'danger', 'exclamation-triangle', false);
		});
	}
}
