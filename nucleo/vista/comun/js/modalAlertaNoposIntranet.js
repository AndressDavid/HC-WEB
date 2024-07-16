var oModalAlertaIntranet = {
	gcAjax: 'vista-comun/ajax/modalAlertaNoposIntranet.php',
	gcMedNoposIntranet:'', gcCupsNoposIntranet:'',
	gcRutaMipres: '', gcRutaIntranet: '', gcMipresIntranet:'', 
	lcMensajeError:'', lcFormaError:'', lcObjetoError:'', fnEjecutar: false,

	inicializar: function()
	{
		this.parametrosMipres();
		$('#btnAbrirIntranet').on('click', oModalAlertaIntranet.abrirIntranet);
		$('#btnCerrarIntranet').on('click', oModalAlertaIntranet.cancelarIntranet);
	},
	
	parametrosMipres: function(){
		$.ajax({
			url : oModalAlertaIntranet.gcAjax,
			data : {accion : 'consultaparametros'},
			type : 'POST',
			dataType : 'json'
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					oModalAlertaIntranet.gcRutaMipres=toDatos.TIPOS.rutamipres;
					oModalAlertaIntranet.gcRutaIntranet=toDatos.TIPOS.rutaintranet;
					oModalAlertaIntranet.gcMipresIntranet=toDatos.TIPOS.irmipresintranet;
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert(err);
				fnAlert('No se pudo realizar la busqueda de parametros MIPRES.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown){
			console.log(jqXHR.responseText);
			fnAlert('Se presento un error al realizar la busqueda parametros MIPRES.', '', 'fas fa-exclamation-circle','red','medium');
		});
	},
	
	abrirIntranet: function()
	{
		window.open(oModalAlertaIntranet.gcRutaIntranet, "_blank");
	},

	mostrar: function(tfEjecutar)
	{
		let lcTextoNoPOSIntranet='';
		if (oModalAlertaIntranet.gcCupsNoposIntranet!='' || oModalAlertaIntranet.gcMedNoposIntranet!=''){
			if (oModalAlertaIntranet.gcCupsNoposIntranet!=''){
				lcTextoNoPOSIntranet='Procedimientos NOPOS'+'\n'+oModalAlertaIntranet.gcCupsNoposIntranet;
			}

			if (oModalAlertaIntranet.gcMedNoposIntranet!=''){
				lcTextoNoPOSIntranet+='\n'+'Medicamentos NOPOS'+'\n'+oModalAlertaIntranet.gcMedNoposIntranet;
			}
		}else{
			if (oAmbulatorio!=undefined){
				$('#btnAceptarIntranet').hide();
				lcTextoNoPOSIntranet=oAmbulatorio.gcDatosNopos;
			}	
		}
		$('#txtListadoNoposIntranet').val(lcTextoNoPOSIntranet);
		$("#divAlertaNoposItranet").modal('show');
		oModalAlertaIntranet.fnEjecutar = tfEjecutar;
	},

	cancelarIntranet: function()
	{
		if (oModalAlertaIntranet.gcCupsNoposIntranet!='' || oModalAlertaIntranet.gcMedNoposIntranet!=''){	
			$("#btnGuardarOrdenesMedicas").attr("disabled", false);
			lcTextoMensaje="La acción se ha cancelado. <br>" + "¡La Órden Médica NO se ha Guardado!"
			fnAlert(lcTextoMensaje, "Alerta MIPRES");
		}	
		$("#divAlertaNoposItranet").modal('hide');
		if (typeof oModalAlertaIntranet.fnEjecutar==='function'){
			oModalAlertaIntranet.fnEjecutar();
		}
	},
	
	aceptar: function()
	{
		$("#divAlertaNoposItranet").modal('hide');
		if (typeof oModalAlertaIntranet.fnEjecutar==='function'){
			oModalAlertaIntranet.fnEjecutar();
		}
	}
}
