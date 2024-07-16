var oAlertaIRAG = {
	activa: false,
	funcionPost: false,
	dx: false,
	linkFichas: '',

	inicializar: function()
	{
		oAlertaIRAG.cargarConfiguracion();
		$("#btnOkAlertaIRAG").on('click', oAlertaIRAG.btnAceptarClick);
	},
	cargarConfiguracion: function () {
		$.ajax({
			type: "POST",
			url: 'vista-historiaclinica/ajax/modal_irag.php',
			data: {accion: 'config'},
			dataType: "json",
		})
		.done(function(loData) {
			try {
				if (loData.error == ''){
					oAlertaIRAG.activa = loData.config.lAlertaActiva
					if (oAlertaIRAG.activa===true) {
						var laConfigMsg = loData.config.cAlertaMensaje.split('~');
						oAlertaIRAG.linkFichas = loData.config.cLinkFichas;
						$("#spnBodyTituloIRAG").text(laConfigMsg[1]);
						$("#spnBodyMensajeIRAG").text(laConfigMsg[2]);
						$("#lblPacAplicaIRAG").text(laConfigMsg[3]);
						$("#selPacAplicaIRAG")
							.append('<option value="SI">'+laConfigMsg[4]+'</option>')
							.append('<option value="NO">'+laConfigMsg[5]+'</option>');
					}
				} else {
					fnAlert(loData.error);
				}
			} catch(err) {
				fnAlert("No se pudo obtener la configuración de IRAG");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al obtener la configuración de IRAG");
		});
		return;
	},
	validarMostrar: function(tfPost)
	{
		oAlertaIRAG.funcionPost = (typeof tfPost === 'function') ? tfPost : false;
		oAlertaIRAG.valida();
	},
	valida: function()
	{
		if(oAlertaIRAG.activa) {
			oAlertaIRAG.obtenerDx();
			var laData = {
				accion:'validar',
				ingreso:aDatosIngreso.nIngreso,
				dxPrincipal:oAlertaIRAG.dx.prn,
				dxOtros:oAlertaIRAG.dx.rel
			};
			$.ajax({
				type: "POST",
				url: 'vista-historiaclinica/ajax/modal_irag.php',
				data: laData,
				dataType: "json",
			})
			.done(function(loData) {
				var lbEjecutarSinMostrar = true;
				try {
					if (loData.error == ''){
						if (loData.valida) {
							oAlertaIRAG.mostrar();
							lbEjecutarSinMostrar = false;
						}
					} else {
						fnAlert(loData.error);
					}
				} catch(err) {
					fnAlert("No se pudo obtener la configuración de IRAG");
				} finally {
					if (lbEjecutarSinMostrar) {
						oAlertaIRAG.ejecutarPost();
					}
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.responseText);
				fnAlert("Se presentó un error al obtener la configuración de IRAG");
			});
		} else {
			oAlertaIRAG.ejecutarPost();
		}
		return false;
	},
	btnAceptarClick: function()
	{
		var lcAplica = $("#selPacAplicaIRAG").val();
		if(lcAplica=='') {
			fnAlert("Debe seleccionar Paciente Aplica SI o NO");
			return false;

		} else {
			if(lcAplica=='SI') {
				window.open(oAlertaIRAG.linkFichas, '_blank');
			}
			var laData = {
				accion: 'guardar',
				ingreso:aDatosIngreso.nIngreso,
				dxPrincipal:oAlertaIRAG.dx.prn,
				dxOtros:oAlertaIRAG.dx.rel,
				aplica:$("#selPacAplicaIRAG").val(),
				programa:'HCPPALWEB'
			};
			$.ajax({
				type: "POST",
				url: 'vista-historiaclinica/ajax/modal_irag.php',
				data: laData,
				dataType: "json",
			})
			.done(function(loData) {
				try {
					if (loData.error == ''){
						// guardado sin error
					} else {
						fnAlert(loData.error);
					}
				} catch(err) {
					fnAlert("No se pudo guardar respuesta IRAG");
				} finally {
					oAlertaIRAG.ejecutarPost();
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.responseText);
				fnAlert("Se presentó un error al guardar respuesta IRAG");
			});
		}
	},
	ejecutarPost: function()
	{
		if (typeof oAlertaIRAG.funcionPost === 'function') {
			oAlertaIRAG.funcionPost();
		}
	},
	obtenerDx: function()
	{
		var laListaDx=oDiagnosticos.obtenerDatos(),
			lcDxPrin="", lcDxRel="", lcComa="";
		$.each(laListaDx, function(lnIndex, laDx){
			if(laDx.CODTIPO=="1") {
				lcDxPrin=laDx.CODIGO
			} else {
				lcDxRel+=lcComa+laDx.CODIGO;
				lcComa=",";
			}
		});
		oAlertaIRAG.dx = {
			prn:lcDxPrin,
			rel:lcDxRel
		};
	},
	mostrar: function()
	{
		$("#divAlertaIRAG").modal('show');
	},
	ocultar: function()
	{
		$("#divAlertaIRAG").modal('hide');
	}
};
