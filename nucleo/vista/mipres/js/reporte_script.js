
$(function () {
	getTiposConsultas('24');
	getVariables(2);
	$('#btnConsultar').on('click', consultarMiPresDP);
});


/*
 *	Organizar datos obtenidos uno a uno
 */
function organizarMiPres(taMiPres) {
	var lcRes='';

	// Reportes de entrega uno a uno
	$.each(taMiPres, function(lnIndex, loItem) {
		var lcHead = 'Head' + lnIndex,
			lcCard = 'Card' + lnIndex;
		lcRes += '<div class="card"><div class="card-header" id="'+lcHead+'"><h6 class="mb-0">'
			+ '<button class="btn collapsed mr-2" type="button" data-toggle="collapse" data-target="#'+lcCard+'" aria-expanded="false" aria-controls="'+lcCard+'">Ver</button> '
			+ ' ID: ' + loItem.ID //+ ' - ID Reporte: ' + loItem.IDReporteEntrega
			+ ' - Pres: ' + loItem.NoPrescripcion
			+ ' - Tecno: ' + loItem.TipoTec + ' cns.' + loItem.ConTec
			+ ' - Fecha: ' + loItem.FecRepEntrega
			+ ' - Pac: ' + loItem.TipoIDPaciente + ' ' + loItem.NoIDPaciente
			+ ' - Est: ' + goParData['estrepentrega'].valores[loItem.EstRepEntrega]
			+ '</h6></div>'
			+ '<div id="'+lcCard+'" class="collapse" aria-labelledby="'+lcHead+'" data-parent="#divResFinal"><div class="card-body">'
			+ fnCamposItem(loItem, 'tblItem' + lnIndex) + '</div></div></div>';
	});

	return lcRes;
}

