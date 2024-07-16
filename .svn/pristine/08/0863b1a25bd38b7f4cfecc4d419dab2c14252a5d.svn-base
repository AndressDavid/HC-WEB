// valida el paciente seleccionado
function validarPacienteHC(toData, tcAtendidoRgr, fnAceptar, fnCancelar) {
	fnAceptar = typeof fnAceptar === 'function' ? fnAceptar : false;
	fnCancelar = typeof fnCancelar === 'function' ? fnCancelar : false;

	var lcMsgHtml = [
		'<div class="container-fluid">',
			'<h5>Dr. '+goUser.name+', ¿está seguro(a) de realizar la Historia Clínica para el siguiente paciente?</h5>',
			'<div class="row">',
				'<div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">Ingreso: <b>'+toData.INGRESO+'</b></div>',
				'<div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">Fecha Ingreso: <b>'+toData.FECHAING+'</b></div>',
			'</div>',
			'<div class="row">',
				'<div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">Nombre: <b>'+toData.PACIENTE+'</b></div>',
				'<div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">Identificación: <b>'+toData.TIPODOC+' '+toData.NUMDOC+'</b></div>',
			'</div>',
			'<div class="row">',
				'<div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">Fecha Nac: <b>'+toData.FECHANAC+'</b> - Edad: <b>'+toData.EDAD_A+' Años</b></div>',
				'<div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">Sexo: <b>'+toData.GENERO+'</b></div>',
			'</div>',
			'<div class="row">',
				'<div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">Teléfono(s): <b>'+toData.TELEFONOS+'</b> </div>',
			'</div>',
	].join('');
	// solo para urgencias
	if (tcAtendidoRgr!=='') {
		lcMsgHtml += [
			'<div class="row pt-3">',
				'<div class="col-12">',
					'<form id="frmAtendidoClinica"><div class="form-check">',
						'<input type="checkbox" class="form-check-input" id="chkAtendidoClinica">',
						'<label class="form-check-label text-danger" for="chkAtendidoClinica">'+tcAtendidoRgr+'</label>',
					'</div></form>',
				'</div>',
			'</div>',
		].join('');
	}
	lcMsgHtml += '</div>';

	fnConfirm(lcMsgHtml, false, false, false, 'large', fnAceptar, fnCancelar);
}
