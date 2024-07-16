//var lcUrlAjax = 'vista-triage/ajax';

$(function(){
	wscancelarturno(3);

});

function wscancelarturno(tnTurno){	//357
	var lnTipo = 0,
			loError = '', 
			loFrmMarcarTurno = '';
			
	tnTurno = typeof(tnTurno) === 'number' ? parseInt(tnTurno): 0;
	
	if(typeof oSoapClient === 'object'){
		if(tnTurno > 0){
			lnTipo = parseInt(oSoapClient.cModuloParametro)
		}
	}
}

 
