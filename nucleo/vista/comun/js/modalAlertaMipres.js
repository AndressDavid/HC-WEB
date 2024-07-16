var oModalAlertaMipres = {
	gotableConsumosMipres: $('#tblConsumosMipres'),
	gcAjax: 'vista-comun/ajax/modalAlertaMipres.php',
	lcMensajeError:'', lcFormaError:'', lcObjetoError:'', fnEjecutar: false,

	inicializar: function()
	{
		this.iniciarTablaConsumosMipres();
		$('#btnAbrirMiPresOM').on('click', oModalAlertaMipres.abrirMiPres);
		$('#btnCancelarMiPresOM').on('click', oModalAlertaMipres.cancelar);
		
		$('#tblConsumosMipres').on('focusout','.modmipres', function(){
			var lnOrden=$(this).attr("data-id"),
				lcCampo=$(this).attr("data-campo"),
				lcValor=$(this).val();

			var loTblData = $('#tblConsumosMipres').bootstrapTable("getData");
			$.each(loTblData, function(lnIndice, loFila){
				if(lnOrden==loFila.CODIGO){
					if(lcValor!==loFila[lcCampo]){
						$('#tblConsumosMipres').bootstrapTable("updateCell",{
							index:lnIndice,
							field:lcCampo,
							value:typeof lcValor==='undefined' ? '' : lcValor
						});
					}
					return;
				}
			});
		});
	},
	
	mostrar: function(tfEjecutar)
	{
		if (oModalAlertaIntranet.gcMipresIntranet==''){
			$("#divAlertaMipresOm").modal('show');
			oModalAlertaMipres.fnEjecutar = tfEjecutar;
		}else{
			oModalAlertaIntranet.mostrar(tfEjecutar);
		}	
	},

	ocultar: function()
	{
		$("#divAlertaMipresOm").modal('hide');
		if (typeof oModalAlertaMipres.fnEjecutar==='function'){
			oModalAlertaMipres.fnEjecutar();
		}
	},
	
	guardarMiPres: function(){
		var lbValido = true;
		var laDatosMipres = $('#tblConsumosMipres').bootstrapTable('getData');
		
		$.each(laDatosMipres, function( lcKey, loTipo ) {
			var lcCodigoCups=loTipo['CODIGO'];
			var lcDescripcionCups=loTipo['DESCRIPCION'];
			var lnCantidadOrdenada=parseInt(loTipo['CANTIDADORDENADO']);
			var lnCantidadPrescrita=parseInt(loTipo['CANTMIPRES']);
			
			if(loTipo['NUMMIPRES']==''){
				oModalAlertaMipres.lcMensajeError = 'Debe indicar Número de Prescripción, revise por favor.';
				oModalAlertaMipres.lcFormaError = 'FormAlertaMipresOm';
				oModalAlertaMipres.lcObjetoError = 'lblTextoMipres';
				lbValido = false;
			}
			if (lbValido){
				if(loTipo['NUMMIPRES'].length!='20'){
					oModalAlertaMipres.lcMensajeError = 'El Número de Prescripción debe ser de 20 dígitos, revise por favor.';
					oModalAlertaMipres.lcFormaError = 'FormAlertaMipresOm';
					oModalAlertaMipres.lcObjetoError = 'lblTextoMipres';
					lbValido = false;
				}
			}
			
			if (lbValido){
				if(loTipo['CANTMIPRES']=='' || loTipo['CANTMIPRES']==0){
					oModalAlertaMipres.lcMensajeError = 'Debe indicar Cantidad Prescrita, revise por favor.';
					oModalAlertaMipres.lcFormaError = 'FormAlertaMipresOm';
					oModalAlertaMipres.lcObjetoError = 'lblTextoMipres';
					lbValido = false;
				}
			}
			
			if (lbValido){
				if (lnCantidadOrdenada > lnCantidadPrescrita){
					laCupsVerificar= oProcedimientosOrdMedica.aCupsVerificaMipres.split(',');
					if($.inArray(lcCodigoCups, laCupsVerificar) > -1){
						lcTexto='La cantidad faltante por prescripción ';
					}else{
						lcTexto='La cantidad ordenada ';
					}	
					lcMsgTexto=lcTexto + '(' + lnCantidadOrdenada + ')' + ' supera la cantidad prescrita (' + lnCantidadPrescrita + '), procedimiento: <br>'
								+ lcDescripcionCups + '<br>Revise por favor.';
					oModalAlertaMipres.lcMensajeError=lcMsgTexto;
					oModalAlertaMipres.lcFormaError='FormAlertaMipresOm';
					oModalAlertaMipres.lcObjetoError='lblTextoMipres';
					lbValido = false;
				}
			}	
		});
		return lbValido;
	},
	
	aceptar: function()
	{
		$("#divAlertaMipresOm").modal('hide');
		if (typeof oModalAlertaMipres.fnEjecutar==='function'){
			oModalAlertaMipres.fnEjecutar();
		}
	},
	
	cancelar: function()
	{
		$('#tblConsumosMipres').bootstrapTable('removeAll');
		$("#btnGuardarOrdenesMedicas").attr("disabled", false);
		lcTextoMensaje="La acción se ha cancelado. <br>" + "¡La Órden Médica NO se ha Guardado!"
		fnAlert(lcTextoMensaje, "Alerta MIPRES");
		
		$("#divAlertaMipresOm").modal('hide');
		if (typeof oModalAlertaMipres.fnEjecutar==='function'){
			oModalAlertaMipres.fnEjecutar();
		}
	},

	abrirMiPres: function()
	{
		window.open(oModalAlertaIntranet.gcRutaMipres, "_blank");
	},
	
	iniciarTablaConsumosMipres: function(){
		oModalAlertaMipres.gotableConsumosMipres.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			locale: 'es-ES',
			undefinedText: 'N/A',
			toolbar: '#toolBarLst',
			height: '300',
			pagination: false,
			pageSize: 25,
			pageList: '[10, 20, 50, 100, 250, 500, All]',
			filterAlgorithm: 'and',
			sortable: true,
			search: false,
			searchOnEnterKey: false,
			visibleSearch: false,
			showSearchButton: false,
			showSearchClearButton: false,
			trimOnSearch: true,
			iconSize: 'sm',
			columns: [
			{
				title: 'CÓDIGO',
				field: 'CODIGO',
				width: 5, widthUnit: "%",
				halign: 'center',
				align: 'center'
			},
			{
				title: 'DESCRIPCIÓN',
				field: 'DESCRIPCION',
				width: 30, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'CODDCI',
				field: 'CODDCI',
				width: 5, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'DCI',
				field: 'DCI',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'NÚMERO MIPRES',
				field: 'NUMMIPRES',
				halign: 'center',
				formatter: function(tnValor, toFila) {
					return '<input type="number" id="numNumeroMipres" class="form-control form-control-sm col-12 modmipres inpNumMipres" data-id="'+toFila.CODIGO+'" data-campo="NUMMIPRES" value="'+tnValor+'">';
				},
			  	width: 40, widthUnit: "%"
			},
			{
				title: 'CANTIDAD',
				field: 'CANTMIPRES',
				halign: 'center',
			  	width: 5, widthUnit: "%" ,
				formatter: function(tnValor, toFila) {
					return '<input type="number" id="numCantidadMipres" class="form-control form-control-sm col-12 modmipres inpCantMipres" data-id="'+toFila.CODIGO+'" data-campo="CANTMIPRES" value="'+tnValor+'">';
				},
			}
		  ]
		});
	},
	
	obtenerDatos: function() {
		var laDatosMipres = $('#tblConsumosMipres').bootstrapTable('getData');
		
		var laMipres = {
			'CupsMipres': laDatosMipres,
			'RegistroMipres': oProcedimientosOrdMedica.gcRegistroCupsMipres,
			'TipoMipres': oProcedimientosOrdMedica.gcTipoCupsMipres
		}

		return laMipres;		
	}
}
