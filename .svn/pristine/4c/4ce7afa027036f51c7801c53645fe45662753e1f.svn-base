	
$(function() {
	getTiposConsultas('02');
	getVariables(1);
	$('#btnConsultar').on('click', consultarMiPres);
});


//  Obtener datos de consulta MiPres
function consultarMiPres() {
	fnLimpiar(false);
	if ($("#selConsulta").val()) {
		$('#divResultado').show();
		$('#divIconoEspera').show();
		var lbValido = true;
		$(".clsConsulta").each(function(tnIndex) {
			if ($(this).prop('disabled')==false && $(this).val()=='') {
				$(this).focus();
				alert('Debe llenar el valor de ' + $(this).attr('name'));
				lbValido = false;
			}
		});
		if (!lbValido) return false;

		var laData = {
			accion	: 'mipres',
			url		: gcRutaMiPres + goTiposConsulta[$("#selConsulta").val()].RUTA,
			fecha	: $("#txtFecha").val(),
			tipDoc	: $("#selTipoDoc").val(),
			numDoc	: $("#txtNumDoc").val(),
			numPrs	: $("#txtNumPres").val(),
		};
		$.ajax({
			type: "POST",
			url: gcUrlAjax,
			data: laData,
			dataType: "json"
		})
		.done(function( loRta ) {
			$('#divResultado').hide();
			$('#divIconoEspera').hide();
			$('#divResFinal').html('');

			if (loRta.error == ''){
				console.log(loRta.MIPRES);

				var lcRes = '',
					lcInfo = $('select[name="selConsulta"] option:selected').text()
						+ ( $('#txtFecha').val() ? ' - Fecha: ' + $('#txtFecha').val() : '' )
						+ ( $('#selTipoDoc').val() ? ' - Documento: ' + $('#selTipoDoc').val() + ' ' + $('#txtNumDoc').val() : '' )
						+ '<br>Registros Obtenidos: ' + loRta.MIPRES.length;
				$('#infoConsulta').html(lcInfo);
				$.each(loRta.MIPRES, function(lnIndex, loItem) {
					if (loItem.prescripcion) {
						var loPres = loItem.prescripcion,
							lcHead = 'Head' + lnIndex,
							lcCard = 'Card' + lnIndex;
						lcRes += '<div class="card"><div class="card-header" id="'+lcHead+'"><h6 class="mb-0">'
							+ '<button class="btn collapsed mr-2" type="button" data-toggle="collapse" data-target="#'+lcCard+'" aria-expanded="false" aria-controls="'+lcCard+'">Ver</button> '
							+ 'Prescripción No ' + loPres.NoPrescripcion
							+ ' - FechaHora: ' + loPres.FPrescripcion.substr(0,10) + ' ' + loPres.HPrescripcion
							+ ' - Paciente: ' + loPres.TipoIDPaciente + ' ' + loPres.NroIDPaciente
							+ ' - ' + loPres.PNPaciente + ' ' + loPres.PAPaciente
							+ '</h6></div>'
							+ '<div id="'+lcCard+'" class="collapse" aria-labelledby="'+lcHead+'" data-parent="#divResFinal"><div class="card-body">';

						$.each(loItem, function(lcIndexE, loElemento) {
							if ( lcIndexE=='prescripcion' ) {
								var lcTblH = '', lcTbl = '';
								lcRes += '<h5>'+ucwords(lcIndexE)+'</h5>' + '<div style="overflow: scroll;"><table class="table table-bordered table-sm display nowrap">';
								$.each(loElemento, function(lcIndexCM, lvFldEle) {
									lcTblH += '<th>'+lcIndexCM+'</th>';
									lcTbl += '<td>'+lvFldEle+'</td>';
								});
								lcRes += ( lcTblH ? '<thead><tr>'+lcTblH+'</tr></thead>' : '' ) + ( lcTbl ? '<tbody>'+lcTbl+'</tbody>' : '' ) + '</table></div>';

							} else {
								if (loElemento.length>0) {
									var lcTblH = '', lcTbl = '';
									lcRes += '<h5>'+ucwords(lcIndexE)+'</h5>' + '<table class="table table-bordered table-sm tblDataTable display nowrap">';
									$.each(loElemento, function(lcIndexM, loEle) {
										lcTbl += '<tr>';
										$.each(loEle, function(lcIndexCM, lvFldEle) {
											if (lcIndexM==0)
												lcTblH += '<th>'+lcIndexCM+'</th>';
											//if ( lcIndexCM == 'PrincipiosActivos' ) {
											if ( typeof lvFldEle === 'object' || Array.isArray(lvFldEle) ) {
												lcTbl += '<td>'+JSON.stringify(lvFldEle)+'</td>';
												/*
												lcTbl += '<td>';
												$.each(lvFldEle, function(lcIndexPA, lvFldElePA) {
													//lcTbl += ' - '+lvFldElePA.CodPriAct;
													lcTbl += ' - '+JSON.stringify(lvFldElePA);
												});
												lcTbl += '</td>';
												*/
											} else {
												lcTbl += '<td>'+lvFldEle+'</td>';
											}
										});
										lcTbl += '</tr>';
									});
									lcRes += ( lcTblH ? '<thead><tr>'+lcTblH+'</tr></thead>' : '' ) + ( lcTbl ? '<tbody>'+lcTbl+'</tbody>' : '' ) + '</table>';
								}
							}
						});
						lcRes += '</div></div></div>';

					} else if (loItem.prescripcion_novedades) {
						var lcTblH = '', lcTbl = '';
						if (lnIndex==0)
							lcRes += '<h5>Prescripción Novedades</h5>' + '<table class="table table-bordered table-sm tblDataTable display nowrap">';
						$.each(loItem.prescripcion_novedades, function(lcIndexCM, lvFldEle) {
							if (lnIndex==0)
								lcTblH += '<th>'+lcIndexCM+'</th>';
							lcTbl += '<td>'+lvFldEle+'</td>';
						});
						lcRes += ( lcTblH ? '<thead><tr>'+lcTblH+'</tr></thead><tbody>' : '' ) + '<tr>'+lcTbl+'</tr>';
						if (lnIndex==loRta.MIPRES.length-1) lcRes += '</tbody></table>';

					} else {
/*
						var lcTblH = '', lcTbl = '';
						if (lnIndex==0)
							lcRes += '<h5>Prescripción Novedades</h5>' + '<table class="table table-sm">';
						$.each(loItem.prescripcion_novedades, function(lcIndexCM, lvFldEle) {
							if (lnIndex==0)
								lcTblH += '<th>'+lcIndexCM+'</th>';
							lcTbl += '<td>'+lvFldEle+'</td>';
						});
						lcRes += ( lcTblH ? '<thead><tr>'+lcTblH+'</tr></thead><tbody>' : '' ) + '<tr>'+lcTbl+'</tr>';
						if (lnIndex==loRta.MIPRES.length-1) lcRes += '</tbody></table>';
*/
					}

				});
				$('#divResFinal').append(lcRes);
				habTooltip();

				$('.tblDataTable').DataTable( {
					scrollY: 200,
					scrollX: true,
					paging: false,
					searching: false,
					language: { "url": "publico-complementos/datatables/1.10.18/DataTables/Spanish.json" },
				} );

				$('#divResultado').show();
				$('#divResFinal').show();
			} else {
				infoAlert($('#divInfo'), loRta.error + ' ', 'warning', 'exclamation-triangle', true);
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			infoAlert($('#divInfo'), 'Se presentó un error al obtener tipos de consulta', 'danger', 'exclamation-triangle', true);
		});
	} else {
		infoAlert($('#divInfo'), 'Debe especificar el tipo de consulta', 'danger', 'exclamation-triangle', true);
	}
}

