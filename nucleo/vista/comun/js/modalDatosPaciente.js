var oModalDatosPaciente = {
	cTitulo : "Consulta Admisión",
	cIcono: "fa fa-hospital-user",
	cAncho: "col-md-10 col-md-offset-2",
	cEstilo: "blue",
	fnEjecutar: false,

	consultaDatos: function (taDatosIngreso){
		var lnIngreso = taDatosIngreso.nIngreso,
			lnFechaHoraIngreso = taDatosIngreso.nIngresoFecha + taDatosIngreso.nIngresoHora.toString().padStart(6, "0"),
			lcMsg = 'Espere por favor ... <i class="fas fa-circle-notch fa-spin" style="font-size: 1.5em; color: Tomato;"></i>',
			loModal = fnAlert(lcMsg, this.cTitulo, this.cIcono, this.cEstilo, this.cAncho),
			loEnviar = {accion: 'Ingreso', ingreso: lnIngreso, fechahoraingreso: lnFechaHoraIngreso};
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/modalDatosPaciente.php',
			data: loEnviar,
			dataType: "json",
		})
		.done(function(loRespuesta) {
 			try {
				if (loRespuesta.error == ''){
					if(loModal.isOpen()){
						loModal.setContent(oModalDatosPaciente.mostrar(loRespuesta.DATOS));
					}else{
						loModal=fnAlert(oModalDatosPaciente.mostrar(loRespuesta.DATOS), this.cTitulo, this.cIcono, this.cEstilo, this.cAncho);
					}
				} else {
					if(loModal.isOpen()){loModal.close();}
					fnAlert(loRespuesta.error, oModalDatosPaciente.cTitulo);
				}
			} catch(err) {
				if(loModal.isOpen()){loModal.close();}
				fnAlert('No se pudo realizar la busqueda de Consulta datos paciente.', oModalDatosPaciente.lcTitulo);
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			if(loModal.isOpen()){loModal.close();}
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar Consulta datos paciente.', oModalDatosPaciente.lcTitulo);
		});
	},

	mostrar: function(taDatosPaciente)
	{
		var lcFechaIngreso = taDatosPaciente.nIngresoFecha.toString().substr(0,4) + '/' + taDatosPaciente.nIngresoFecha.toString().substr(4,2)+ '/' +taDatosPaciente.nIngresoFecha.toString().substr(6,2),
			lcNombrePaciente = taDatosPaciente.oPaciente.cNombre1 + ' ' + taDatosPaciente.oPaciente.cNombre2 + ' ' + taDatosPaciente.oPaciente.cApellido1 + ' ' + taDatosPaciente.oPaciente.cApellido2,
			lcFechaNacimiento = taDatosPaciente.oPaciente.nNacio.toString().substr(0,4) + '/' +	taDatosPaciente.oPaciente.nNacio.toString().substr(4,2) + '/' +
			taDatosPaciente.oPaciente.nNacio.toString().substr(6,2),
			lcEdadPaciente = taDatosPaciente['aEdad']['y']+'A '+taDatosPaciente['aEdad']['m']+'M '+taDatosPaciente['aEdad']['d']+'D ',
			lcGenero = (oGenerosPaciente.gaDatosGeneros[taDatosPaciente.oPaciente.cSexo]? oGenerosPaciente.gaDatosGeneros[taDatosPaciente.oPaciente.cSexo]['DESCRIPCION']: ''),
			lnNombreResponsable = taDatosPaciente.oResponsable.cNombre1 + ' ' + taDatosPaciente.oResponsable.cNombre2 + ' ' + taDatosPaciente.oResponsable.cApellido1 + ' ' + taDatosPaciente.oResponsable.cApellido2,
			lcLugarResidencia = taDatosPaciente.oPaciente.cDecrPaisResidencia + ' / ' + taDatosPaciente.oPaciente.cDecrDptoResidencia  + '  / ' + taDatosPaciente.oPaciente.cDecrCiudadResidencia,
			lcTelefonosResidencia = taDatosPaciente.oPaciente.cTelefono + (taDatosPaciente.oPaciente.cTelefono2!='' ? ' - ' + taDatosPaciente.oPaciente.cTelefono2 : ''),
			lcCelularesResidencia = taDatosPaciente.oPaciente.cCelular + (taDatosPaciente.oPaciente.cCelular2!='' ? ' - ' + taDatosPaciente.oPaciente.cCelular2 : '');
		var lcMsgHtml = [
			'<div class="container-fluid small">',
				'<div class="row">',
					'<div class="col-12"><h5>Información Ingreso</h5></div>',
					'<div class="col-6 col-md-4">Ingreso: <b>'+taDatosPaciente.nIngreso+'</b></div>',
					'<div class="col-6 col-md-4">Vía Ingreso: <b>'+taDatosPaciente.cDescVia+'</b></div>',
					'<div class="col-12 col-md-4">Fecha: <b>'+lcFechaIngreso+'</b></div>',
				'</div><hr>',
				'<div class="row">',
					'<div class="col-12"><h5>Datos del paciente</h5></div>',
					'<div class="col-12">Nombre: <b>'+ lcNombrePaciente+'</b></div>',
					'<div class="col-12 col-md-6 col-lg-4">Identificación: <b>'+ taDatosPaciente.cId + ' - ' + taDatosPaciente.nId +'</b></div>',
					'<div class="col-12 col-md-6 col-lg-4">Género: <b>'+ lcGenero+'</b></div>',
					'<div class="col-12 col-md-6 col-lg-4">Historia: <b>'+ taDatosPaciente.oPaciente.nNumHistoria+'</b></div>',
					'<div class="col-12 col-md-6 col-lg-4">Fecha Nac.: <b>'+ lcFechaNacimiento +'</b></div>',
					'<div class="col-12 col-md-6 col-lg-4">Edad: <b>'+ lcEdadPaciente +'</b></div>',
					'<div class="col-12 col-md-6 col-lg-4">Estado civil: <b>'+ taDatosPaciente.cEstadoCivil +'</b></div>',
				'</div>',
				'<div class="row mt-1">',
					'<div class="col-12">Pais/Dpto./Ciudad: '+ lcLugarResidencia +'</div>',
					'<div class="col-12 col-lg-6">Dirección: '+ taDatosPaciente.oPaciente.cDireccion +'</div>',
					'<div class="col-12 col-lg-6">Email: '+ taDatosPaciente.oPaciente.cEmail +'</div>',
					'<div class="col-12 col-lg-6">Teléfono(s): '+ lcTelefonosResidencia +'</div>',
					'<div class="col-12 col-lg-6">Celular(s): '+ lcCelularesResidencia +'</div>',
				'</div>',
				'<div class="row mt-1">',
					'<div class="col-12">Ocupación: '+ taDatosPaciente.oPaciente.cOcupacion +'</div>',
					'<div class="col-12">Nivel educativo: '+ taDatosPaciente.oPaciente.cDecrNivelEducativo +'</div>',
					'<div class="col-12">Pertenecia étnica: '+ taDatosPaciente.oPaciente.cDecrPertenenciaEtnica +'</div>',
				'</div><hr>',
				'<div class="row">',
					'<div class="col-12"><h5>Responsable</h5></div>',
					'<div class="col-12">Nombre: <b>'+ lnNombreResponsable +'</b></div>',
					'<div class="col-12 col-lg-6">Dirección: <b>'+ taDatosPaciente.oResponsable.cDireccionResp +'</b></div>',
					'<div class="col-12 col-lg-6">Teléfono: <b>'+ taDatosPaciente.oResponsable.cTelefonoResp +'</b></div>',
				'</div><hr>',
		].join('');

		if(typeof aDatosIngreso !== "undefined"){
			var lcProcedimiento = aDatosIngreso['cCodCup']===undefined?'':(aDatosIngreso['cCodCup'] +'-'+aDatosIngreso['cDescripcionCup']);
			var lcMedico = aDatosIngreso['cMedRealiza']===undefined?'':aDatosIngreso['cMedRealiza'];
			lcMsgHtml += [
				'<div class="row">',
					'<div class="col-12"><h5>Entidad</h5></div>',
					'<div class="col-12">Entidad: <b>'+ aDatosIngreso['cPlanDsc'] +'</b></div>',
					'<div class="col-12">Tipo usuario: <b>'+ taDatosPaciente['cDescripcionAfiliadoUsuario'] +'</b></div>',
					'<div class="col-12">Médico: <b>'+ lcMedico +'</b></div>',
					'<div class="col-12">Atención: <b>'+ lcProcedimiento +'</b></div>',
				'</div>',
			].join('');
		}

		return lcMsgHtml+'</div>';
	}
}