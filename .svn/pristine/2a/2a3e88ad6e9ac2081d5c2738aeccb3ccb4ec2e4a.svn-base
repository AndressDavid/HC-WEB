var oMedicamentos = {
	gcUrlAjax: 'vista-comun/ajax/medicamentos',
	lcMensajeError: '',
	lcFormaError: '',
	lcObjetoError: '',

	inicializar: function(){
	},

	consultaMedicamentos: function(toObjeto,toCodigo,tcDescripcion,toFocus,tnFuncion) {
		var loObjeto = '#'+toObjeto;
		var loCodigoAsigna = '#'+toCodigo;
		var loDescripcionAsigna = '#'+tcDescripcion;
		var loObjetoFocus = '#'+toFocus;
		var lcUrltemp = (tnFuncion=='AN'?"vista-comun/ajax/medicamentos?accion=consultarAntibioticos":"vista-comun/ajax/medicamentos?accion=consultarMedicamentos");

		$(loObjeto).autoComplete({
			preventEnter: true,
			resolverSettings: {
				url: lcUrltemp,
				queryKey: 'nombre',
				requestThrottling: 500,
				fail: function (e) {},
			},
			formatResult: function (taItem) {
				laItem = { value: '', text: '', html: ''};

				if(taItem.CODIGO!==undefined && taItem.DESCRIPCION!==undefined){
					if(taItem.DESCRIPCION.length>0 && taItem.CODIGO.length>0){
						laItem = {
							value: taItem.CODIGO,
							text: taItem.DESCRIPCION + ' - '+ taItem.CODIGO + ' - '+ taItem.POSNOPOS,
							html: taItem.DESCRIPCION + ' - '+ taItem.CODIGO + ' - '+ taItem.POSNOPOS
						};
					}
				}
				return laItem;
			},
			noResultsText: 'No hay coincidencias',
		})
		.autoComplete('set',
			{CODIGO:'', DESCRIPCION:''}

		).on('autocomplete.select', function(evt, item) {
			$(loCodigoAsigna).val(item.CODIGO);
			$(loDescripcionAsigna).val(item.DESCRIPCION);
			$(loObjeto).val('');
			$(loObjeto).removeClass("is-valid");
			$(loCodigoAsigna).removeClass("is-invalid");
			$(loDescripcionAsigna).removeClass("is-invalid");
			$(loObjetoFocus).focus();

			if (tnFuncion=='OM'){
				oMedicamentosOrdMedica.seleccionaMedicamento(item);
			}

			if (tnFuncion=='AM'){
				oAmbulatorio.seleccionaMedicamentoAm(item);
			}

			if (tnFuncion=='CO'){
				oConciliacion.seleccionaMedicamento(item);
			}

		}).on('autocomplete.freevalue', function(evt, value) {
			$(loObjeto).val('');
		});
	},

	indicacionesInvima: function(tcCodigo,tcDescripcion) {
		$.ajax({
			type: "POST",
			url: oMedicamentos.gcUrlAjax,
			data: {accion: 'consultarIndicacionesInvima', lcMedicamento: tcCodigo},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.TIPOS.INDICACION!='' || toDatos.TIPOS.ALERTA!=''){
						oMedicamentos.alertaInvima(tcCodigo,tcDescripcion,toDatos.TIPOS);
					}else{
						oMedicamentos.alertaInr(tcCodigo,tcDescripcion);
					}
					oMedicamentosOrdMedica.activarCampos(false);
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta dosis medicamento orden médica.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta dosis medicamento orden médica.");
		});
	},

	alertaInr: function(tcCodigo,tcDescripcion) {
		$.ajax({
			type: "POST",
			url: oMedicamentos.gcUrlAjax,
			data: {accion: 'consultarAlertaInr', lnNroIngreso: aDatosIngreso['nIngreso'], lcMedicamento: tcCodigo},
			dataType: "json"
		})
		.done(function(toDatos) {
			let lcConResultado=toDatos.TIPOS.RESULTADO;
			let lcTextoMensaje=toDatos.TIPOS.CUERPO.replaceAll('~','<br>');
			lcTextoMensaje=lcTextoMensaje.replace('desmedicamento',tcDescripcion);
			
			try {
				if (toDatos.error=='') {
					if (lcConResultado!='' && lcTextoMensaje!=''){
						fnAlert(lcTextoMensaje, 'WARFARINA - ALERTA INR', false, 'blue', 'l');
					}	
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta alerta INR.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta alerta INR.");
		});
	},
	
	alertaInvima: function(tcCodigo,tcDescripcion,taDatosInvima) {
		var lcIndicacion=taDatosInvima.INDICACION.trim();
		var lcAlerta=taDatosInvima.ALERTA.trim();

		var lcMsgHtml = [
			'<div class="container-fluid">',
				'<div class="row" style="font-size:14px;">',
					'<div class="col-12">Código: <b>'+tcCodigo+'</b></div>',
					'<div class="col-12">Medicamento: <b>'+tcDescripcion.trim()+'</b></div>',
				'</div><br>',
				'<div class="row">',
					'<div class="col-12"><b>INDICACIONES INVIMA:</b></div>',
				'</div>',
				'<div class="row">',
					'<div class="col-12 alert alert-light pb-3" style="font-size:14px;">'+lcIndicacion+'</div>',
				'</div>',
		].join('');
		if (lcAlerta.length>0) {
			lcMsgHtml +=
				'<div class="row">' +
					'<div class="col-12 alert alert-danger" style="font-size:14px;">'+lcAlerta+'</div>' +
				'</div>';
		}
		lcMsgHtml += '</div>';
		
		fnAlert(lcMsgHtml, 'INDICACIONES INVIMA - ALERTA', false, 'red', 'lg', function(){
			oMedicamentos.alertaInr(tcCodigo,tcDescripcion)
		});
	},
}
