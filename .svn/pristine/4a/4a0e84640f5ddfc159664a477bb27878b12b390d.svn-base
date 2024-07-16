
$(function () {
	getTiposConsultas('23');
	getVariables(2);
	$('#btnConsultar').on('click', consultarMiPresDP);

	// Si Causa de no entrega <> 0 - N/A => no son obligatorios varios controles
	var lcCausaNoEntrega  = '#41000005'; // Entrega PUT 
	var lcCausaNoEntregaA = '#42000010'; // Entrega Ambito PUT
	$('#divCard')
	.on('change', lcCausaNoEntrega, function(){ // Si es Entrega PUT
		var laCtrls = ['#41000002','#41000003','#41000004','#41000006'];
		if($(this).val()=='0'){
			$.each(laCtrls, function(lnIndex, lcControl){
				$(lcControl).attr('required','required');
			});
		} else {
			$.each(laCtrls, function(lnIndex, lcControl){
				$(lcControl).removeAttr('required');
			});
		}
	})
	.on('change', lcCausaNoEntregaA, function(){ // Si es Entrega Ambito PUT
		var laCtrls = ['#42000007','#42000008','#42000009','#42000011'];
		if($(this).val()=='0'){
			$.each(laCtrls, function(lnIndex, lcControl){
				$(lcControl).attr('required','required');
			});
		} else {
			$.each(laCtrls, function(lnIndex, lcControl){
				$(lcControl).removeAttr('required');
			});
		}
	});
});


/*
 *	Organizar datos obtenidos uno a uno
 */
function organizarMiPres(taMiPres) {
	var lcRes='';

	// Entregas una a una
	$.each(taMiPres, function(lnIndex, loItem) {
		var lcHead = 'Head' + lnIndex,
			lcCard = 'Card' + lnIndex;
		lcRes += '<div class="card"><div class="card-header" id="'+lcHead+'"><h6 class="mb-0">'
			+ '<button class="btn collapsed mr-2" type="button" data-toggle="collapse" data-target="#'+lcCard+'" aria-expanded="false" aria-controls="'+lcCard+'">Ver</button> '
			+ ' ID: ' + loItem.ID //+ ' - ID Entrega: ' + loItem.IDEntrega
			+ ' - Pres: ' + loItem.NoPrescripcion
			+ ' - Tecno: ' + loItem.TipoTec + ' cns.' + loItem.ConTec
			+ ' - Fecha: ' + loItem.FecEntrega
			+ ' - Pac: ' + loItem.TipoIDPaciente + ' ' + loItem.NoIDPaciente
			+ ' - Est: ' + goParData['estentrega'].valores[loItem.EstEntrega]
			+ '</h6></div>'
			+ '<div id="'+lcCard+'" class="collapse" aria-labelledby="'+lcHead+'" data-parent="#divResFinal"><div class="card-body">'
			+ fnCamposItem(loItem, 'tblItem' + lnIndex) + '</div></div></div>';
	});

	return lcRes;
}


