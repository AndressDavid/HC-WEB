// carga css para visualizar documentos en html
document.write('<link rel="stylesheet" href="vista-comun/css/modalVistaPrevia.css" />');

var oModalVistaPrevia = {
	cUrlAjax:'vista-comun/ajax/modalVistaPrevia',

	mostrar: function(taEnvio, tcDescripcionDocumento, tcModulo)
	{
		var cDscDoc = typeof tcDescripcionDocumento == 'string' ? ('VISTA PREVIA '+tcDescripcionDocumento).trim() : 'VISTA PREVIA DOCUMENTO';
		var loDialog = fnInformation('Espere por favor ... <i class="fas fa-circle-notch fa-spin" style="font-size: 1.5em; color: Tomato;"></i>', 'Vista Previa', false, false, 'xlarge');
		$.post(this.cUrlAjax, {accion:'dochtml', datos:JSON.stringify(taEnvio), dsc:cDscDoc, mod:tcModulo}, function(tcHtml){
			if(typeof tcHtml=='object'){
				if(tcHtml.error){
					tcHtml='<div class="container-fluid hc-body"><div class="row"><div class="col"><h5>'+tcHtml.error+'</h5></div></div></div>'
				}else{
					tcHtml='<h3>Ocurrió un error al obtener la vista previa</h3>'
				}
			}
			loDialog.setContent(tcHtml);
		});
	}
}