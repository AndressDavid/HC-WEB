// Script para consulta de Direccionamientos


/*
 *	Limpiar filtros
 */
function fnLimpiar() {
	$("#divFiltro #txtFechaHasta,#txtFechaDesde").val(gcFechaIni);
	$("#txtPrescripcion, #txtIngreso, #selTipoDoc, #txtNumeroDoc, #selCodEps").val('');
	goTabla.bootstrapTable('removeAll');
}

/*
 *	Consultar los direccionamientos
 */
function fnConsultar() {
	goTabla.bootstrapTable('removeAll');
	if(!fnValidarConsulta()){ return false; }

	goTabla.bootstrapTable('showLoading');
	var laData = {
		accion:'direccionamientos',
		fecini: $("#txtFechaDesde").val(),
		fecfin: $("#txtFechaHasta").val(),
		numprs: $("#txtPrescripcion").val(),
		ingres: $("#txtIngreso").val(),
		tipdoc: $("#selTipoDoc").val(),
		numdoc: $("#txtNumeroDoc").val(),
		codeps: $("#selCodEps").val()
	};
	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: laData,
		dataType: "json",
	})
	.done(function(loRta) {
		if (loRta.error == ''){
			goTabla.bootstrapTable('refreshOptions', {
				data: loRta.data
			});

		} else {
			fnAlert(loRta.error);
		}
		goTabla.bootstrapTable('hideLoading');

	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar');
	});
}



/*
 *	Abre el form para ejecutar una acción
 */
function fnAbrirFormAccion(tcTipoAcc) {
	var lcTipoAcc = '';
	$('#cntPUT').html('');
	$('#divInfo').html('');
	$("#btnEnviar").attr("tipoacc", tcTipoAcc);

	switch (tcTipoAcc) {

		// Programación
		case 'programa':
			$('#divFormPUT .modal-title').text('Programación');
			lcTipoAcc = '22000001';
			getUrlMiPres(2, function(){
				fnCrearFormPut('31', function(){
					$("#31000001").val(goFila.ID);								// Identificador
					$("#31000002").val(strNumAFecha(goFila.FECHAEGR));			// Fecha Máxima Entrega
					//$("#31000003").val('NI');									// Tipo ID Sede
					//$("#31000004").val('860006656');							// Número ID Sede
					//$("#31000005").val('PROV000766 ');						// Código de Sede
					$("#31000006").val(goFila.TIPOTEC=='P'?goFila.CODIGO:'');	// Código Servicio o Tecnología a Entregar
					$("#31000007").val(goFila.CANTTOTAL.replace('.00',''));		// Cantidad Total a Entregar
					$("#divFormPUT").modal('show');

					// Busca CUM de Medicamento - Producto Nutricional
					if (goFila.TIPOTEC=='M' || goFila.TIPOTEC=='N') {
						$.ajax({
							type: "POST",
							url: gcUrlAjax,
							data: {
								accion:'cum',
								ingreso:goFila.INGRESO,
								numpres:goFila.NUMPRES,
								tipo:goFila.TIPOTEC,
								consec:goFila.CNSTEC,
								codsha:goFila.CODIGO,
								},
							dataType: "json",
						})
						.done(function(loRta) {
							if (loRta.error == ''){
								$("#31000006").val(loRta.data.CUM);
							}
						});
					}
				});
			});
			break;

		// Entrega
		case 'entrega':
			$('#divFormPUT .modal-title').text('Entrega');
			lcTipoAcc = '23000001';
			getUrlMiPres(2, function(){
				fnCrearFormPut('94', function(){
					$("#94000001").val(goFila.ID);								// Identificador
					$("#94000002").val(goFila.CODIGOPRO);						// Código Servicio o Tecnología (se usa código programado)
					$("#94000003").val(goFila.CANTTOTPRO.replace('.000',''));	// Cantidad Total Entregada (se usa cantidad programada)
					//$("#94000004").val('');									// Código Servicio o Tecnología 2
					//$("#94000005").val(0);									// Cantidad Total Entregada 2
					//$("#94000006").val('1');									// Entrega Total (SI)
					//$("#94000007").val('0');									// Causa de No Entrega (N/A)
					$("#94000008").val(strNumAFecha(goFila.FECHAEGR));			// Fecha de Entrega
					//$("#94000009").val('');									// Lote entregado
					//$("#94000010").val('');									// Tipo de ID de quien recibe
					//$("#94000011").val('');									// Número de ID de quien recibe
					$("#divFormPUT").modal('show');

					// Busca CUM de Medicamento - Producto Nutricional
					if (goFila.TIPOTEC=='M' || goFila.TIPOTEC=='N') {
						$.ajax({
							type: "POST",
							url: gcUrlAjax,
							data: {
								accion:'cum',
								ingreso:goFila.INGRESO,
								numpres:goFila.NUMPRES,
								tipo:goFila.TIPOTEC,
								consec:goFila.CNSTEC,
								codsha:goFila.CODIGO,
								},
							dataType: "json",
						})
						.done(function(loRta) {
							if (loRta.error == ''){
								loRta.data.CUM = loRta.data.CUM ? loRta.data.CUM.trim() : '';
								goFila.CODIGOPRO = goFila.CODIGOPRO.trim();
								if (loRta.data.CUM!=='' && goFila.CODIGOPRO!==loRta.data.CUM) {
									$.confirm({
										title: 'Código facturado diferente',
										content: 'El Código facturado no corresponde con el programado<br>¿Desea usar el facturado?',
										buttons: {
											Aceptar: {
												text: 'Aceptar',
												btnClass: 'btn-blue',
												action: function () {
													$("#94000002").val(loRta.data.CUM);
												}
											},
											Cancelar: {
												text: 'Cancelar',
												action: function () {
													fnAlert('¡Se deja código programado!');
												}
											}
										}
									});
								}
							}
						});

					} else {
						$("#94000003").val(goFila.CANTIDAD.replace('.000000',''));	// Cantidad Total Entregada
						$("#div94000004").hide();				// Código Servicio o Tecnología 2
						$("#div94000005").hide();				// Cantidad Total Entregada 2
					}
				});
			});
			break;

		// Entrega Código
		case 'EntregaCodigo':
			$('#divFormPUT .modal-title').text('Entrega Códigos');
			lcTipoAcc = '23000000';
			getUrlMiPres(2, function(){
				fnCrearFormPut('40', function(){
					$("#40000001").val(goFila.IDENTREGA);				// Identificador Entrega
					//$("#40000002").val('');							// Código Servicio o Tecnología
					//$("#40000003").val('0');							// Cantidad Total Entregada
					$("#40000004").val(strNumAFecha(goFila.FECHAEGR));	// Fecha de Entrega
					$("#divFormPUT").modal('show');
				});
			});
			break;

		// Reporte Entrega
		case 'reporte':
			$('#divFormPUT .modal-title').text('Reporte Entrega');
			lcTipoAcc = '24000001';
			getUrlMiPres(2, function(){
				fnCrearFormPut('51', function(){
					$("#51000001").val(goFila.ID);				// Identificador
					$("#51000002").val('1');					// Estado entrega (Si se entrega)
					$("#51000003").val('0');					// Causa no entrega (N/A)
					$("#51000004").val(0);						// Valor de la entrega
					$("#divFormPUT").modal('show');
				});
			});
			break;

		// Reporte Facturación
		case 'factura':
			$('#divFormPUT .modal-title').text('Reporte Facturación');
			lcTipoAcc = '25000001';
			getUrlMiPres(3, function(){
				fnCrearFormPut('61', function(){
					var lcCantAdd=cantAddEntregada(goFila.CODENTADD);	// Cantidad entregada CUMS adicionales
					var lcCantTotal=(parseFloat(goFila.CANTENTR)+lcCantAdd);
					$("#61000001").val(goFila.NUMPRES);					// Prescripción
					$("#61000002").val(goFila.TIPOTEC);					// Tipo tecnología
					$("#61000003").val(goFila.CNSTEC);					// Consecutivo tecnología
					$("#61000004").val(goFila.TIPIDPAC);				// Tipo id paciente
					$("#61000005").val(goFila.NUMIDPAC);				// Número id paciente
					$("#61000006").val('1');							// Número entrega
					//$("#61000007").val('');							// Número de Factura
					$("#61000008").val(goFila.NITEPS);					// Nit EPS que recobra
					$("#61000009").val(goFila.CODEPS);					// Código EPS que recobra
					$("#61000010").val(goFila.CODSRVENT);				// Código Servicio o Tecnología
					$("#61000011").val(lcCantTotal);					// Cantidad en ud mín de dispensación
					//$("#61000012").val('0');							// Valor Unitario Facturado
					//$("#61000013").val('0');							// Valor Total Facturado
					//$("#61000014").val('0');							// Cuota Moderadora
					//$("#61000015").val('0');							// Copago

					// Obtener datos de facturación
					if (goFila.INGRESO !== '') {
						var lcData = {
							accion:'datfactec',
							ingreso:goFila.INGRESO,
							numpres:goFila.NUMPRES,
							tiptec:goFila.TIPOTEC,
							contec:goFila.CNSTEC,
							codigo:goFila.CODIGO
						};
						$.ajax({
							type: "POST",
							url: gcUrlAjax,
							data: lcData,
							dataType: "json",
						})
						.done(function(loRta) {
							if (loRta.error == ''){
								$("#61000007").val(loRta.Factura);
								$("#61000012").val(loRta.ValorUd.replace('.00',''));
								$("#61000014").val(loRta.CuotaM.replace('.00',''));
								$("#61000015").val(loRta.Copago.replace('.00',''));

								// Valida NIT facturado contra direccionamiento
								if (loRta.Factura.length>0 && goFila.NITEPS!==loRta.Nit) {
									fnAlert('El NIT facturado '+loRta.Nit+', es diferente al NIT de la EPS direccionada '+goFila.NITEPS, 'NIT no corresponde');
								}
								// Valida factura con varios valores unitarios de la misma tecnología
								if (loRta.DifVal=='S') {
									fnAlert('La factura presenta valores diferentes para la tecnología', 'Varios Valores Facturados');
								}
								// Valida cantidad facturada
								if (loRta.Cantidad>0 && loRta.Cantidad!=lcCantTotal) {
									if (loRta.Cantidad<lcCantTotal) {
										fnAlert('La cantidad facturada ('+loRta.Cantidad+') es menor que la reportada como entregada ('+lcCantTotal+')', '¡Factura Cantidad Menor!');
										$("#61000011").val(loRta.Cantidad.replace('.00',''));
									} else {
										fnAlert('La cantidad facturada ('+loRta.Cantidad+') es mayor que la reportada como entregada ('+lcCantTotal+')', '¡Factura Cantidad Mayor!');
									}
								}
								$("#61000013").val($("#61000012").val() * $("#61000011").val());
							} else {
								console.log(loRta.error);
							}
						})
						.fail(function(jqXHR, textStatus, errorThrown) {
							console.log(jqXHR.responseText);
						});
					}
					$("#divFormPUT").modal('show');
				});
			});
			break;
	}

	// Opciones del tipo de acción
	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'opctipo',cod:lcTipoAcc},
		dataType: "json",
	})
	.done(function(loRta) {
		if (loRta.error == ''){
			goOpcTipo = loRta.OPCIONES;
		} else {
			console.log(loRta.error);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
	});
}



/*
 *	Actualiza datos del direccionamiento actual, luego de haber reportado una acción
 */
function actualizarRegistro() {
	var laData = {
		accion:'direccionamientos',
		numdir:goFila.ID,
		numprs:goFila.NUMPRES,
		tiptec:goFila.TIPOTEC,
		cnstec:goFila.CNSTEC
	};
	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: laData,
		dataType: "json",
	})
	.done(function(loRta) {
		if (loRta.error == ''){
			goTabla.bootstrapTable('updateRow', {index: gnIndex, row: loRta.data[0]});
			mostrarDataRegistro();

		} else {
			fnAlert(loRta.error);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al actualizar consulta');
	});
}



/*
 *	Iniciar tabla de prescripciones
 */
function iniciarTabla() {
	$.get("vista-mipres/json/CamposDirecc.json", function(data) {
		goCampos = data;
		var loColumnas = [];
		$.each(goCampos, function(lcIndice, laProp){
			laProp.field = lcIndice;
			loColumnas.push(laProp);
		});
		goTabla.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-dark',
			undefinedText: 'N/A',
			height: '600',
			showPaginationSwitch: true,
			pagination: true,
			pageSize: 20,
			pageList: '[10, 20, 50, 100, 250, 500, All]',
			sortable: true,
			search: true,
			searchOnEnterKey: true,
			visibleSearch: true,
			showSearchButton: true,
			showSearchClearButton: true,
			trimOnSearch: true,
			showExport: true,
			exportDataType: 'all',
			exportTypes: ['csv', 'txt', 'excel', 'xlsx'],
			iconSize: 'sm',
			columns: loColumnas
		});
	})
	.fail(function( jqXHR, textStatus, errorThrown ){
		console.log(jqXHR.responseText);
	});
}



/*
 *	Mostrar datos del Direccionamiento seleccionado
 */
function mostrarDataRegistro() {
	goEntAdd=[];
	var lcBtnVerDoc = ' <a class="clsVerDoc" href="javascript:void(0)" title="Ver" tipo="%tipo%" numid="%num%"><i class="fas fa-eye" style="color: #17A2B8;"></i></a> ',
		lcBtnAnuDoc = ' <a class="clsAnularDoc" href="javascript:void(0)" title="Anular" tipo="%tipo%" numid="%num%"><i class="fas fa-backspace" style="color: #E15361;"></i></a> ',
		lcClassBtn = 'class="btn btn-sm btn-secondary btnAcc"';

	$('#divContenidoPrincipal').html('');

	// Título del modal
	$('#divPrincipal .modal-title').text('Direccionamiento '+goFila.ID);

	// Contenido del modal
	var lcClass = 'table-bordered table-hover table-striped table-sm table-responsive-sm',
		loDatos = [
			{titulo:'Prescripción', valor:goFila.NUMPRES},
			{titulo:'Tipo y Cns Tecnología', valor:goParData.tipotec.valores[goFila.TIPOTEC]+' '+goFila.TIPOTEC+' - '+goFila.CNSTEC},
			{titulo:'Descripción', valor:(goFila.CODIGO?goFila.CODIGO+' - ':'')+goFila.DESCRIPCION},
			{titulo:'Código a Entregar', valor:goFila.CODTECENT},
			{titulo:'Cantidad a Entregar', valor:goFila.CANTTOTAL.replace('.00','')+' '+goFila.UNIDAD+' de '+goFila.CANTIDAD.replace('.000000','')+' '+goFila.UNIDAD},
			{titulo:'Número Entrega', valor:goFila.NUMENT},
			{titulo:'Fecha Máxima Entrega', valor:goFila.FECMAXENT},
			{titulo:'Ingreso', valor:goFila.INGRESO},
			{titulo:'Fechas Ingreso|Egreso', valor:strNumAFecha(goFila.FECHAING,'-','-')+' | '+strNumAFecha(goFila.FECHAEGR,'-','-')},
			{titulo:'Paciente', valor:goFila.TIPIDPAC+' '+goFila.NUMIDPAC+' - '+goFila.PACIENTE},
			{titulo:'EPS', valor:goFila.CODEPS+' - '+goFila.NITEPS+' - '+gaEPS[goFila.CODEPS]},
		];

	// Datos de programación
	if (goFila.IDPROGRAMA>0) {
		lcBtns = lcBtnVerDoc + (goFila.IDENTREGA>0 ? '' : lcBtnAnuDoc);
		loDatos.push({titulo:'Id Programación', valor:goFila.IDPROGRAMA+lcBtns.replace(/%num%/gi,goFila.IDPROGRAMA).replace(/%tipo%/gi,'Programa')});

		// Datos de entrega
		if (goFila.IDENTREGA>0) {
			var lbAnular = goFila.IDREPORTE>0;
			lcBtns = lcBtnVerDoc + (lbAnular ? '' : lcBtnAnuDoc);
			loDatos.push({titulo:'Id Entrega', valor:goFila.IDENTREGA+lcBtns.replace(/%num%/gi,goFila.IDENTREGA).replace(/%tipo%/gi,'Entrega')});
			// Datos de entrega códigos adicionales
			if(goFila.TIPOTEC=='M'){
				var lcTipoAcc = 'EntregaCodigo',
					lcEntCod = goFila.IDREPORTE==0? '<button '+lcClassBtn+' tipoacc="'+lcTipoAcc+'">Entrega Codigo</button>': '';
				lcEntCod += ' <a class="clsActEntCod" href="javascript:void(0)" title="Actualizar Entrega Código"><i class="fas fa-redo" style="color: #52BE80;"></i></a> '
				if(goFila.CODENTADD!=''){
					goEntAdd = JSON.parse(goFila.CODENTADD);
					$.each(goEntAdd, function(lnIndice, loEntAdd){
						lcEntCod += '<br>IDEntregaCodigo: '+loEntAdd.IDEntregaCodigo
								 +	(lcBtnVerDoc.replace(/%num%/gi,lnIndice)+(loEntAdd.EstEntregaCodigo==0? ' (Anulado)': (lbAnular? '': lcBtnAnuDoc.replace(/%num%/gi,loEntAdd.IDEntregaCodigo)))).replace(/%tipo%/gi,lcTipoAcc);
					});
				}
				if(lcEntCod!==''){
					loDatos.push({titulo:'Entrega Código', valor:lcEntCod});
				}
			}

			// Datos de reporte de entrega
			if (goFila.IDREPORTE>0) {
				lcBtns = lcBtnVerDoc + (goFila.IDFACTURA>0 ? '' : lcBtnAnuDoc);
				loDatos.push({titulo:'Id Reporte Entrega', valor:goFila.IDREPORTE+lcBtns.replace(/%num%/gi,goFila.IDREPORTE).replace(/%tipo%/gi,'Reporte')});

				// Datos de reporte de factura
				if (goFila.IDFACTURA>0) {
					lcBtns = lcBtnVerDoc + lcBtnAnuDoc;
					loDatos.push({titulo:'Identificador Factura', valor:goFila.IDENTIFFACT});
					loDatos.push({titulo:'Id Reporte Factura', valor:goFila.IDFACTURA+lcBtns.replace(/%num%/gi,goFila.IDFACTURA).replace(/%tipo%/gi,'Factura')});
				} else {
					loDatos.push({titulo:'Reporte Facturación', valor:'<button '+lcClassBtn+' tipoacc="factura">Reporte Facturación</button>'});
				}
			} else {
				loDatos.push({titulo:'Reporte Entrega', valor:'<button '+lcClassBtn+' tipoacc="reporte">Reporte Entrega</button>'});
			}
		} else {
			loDatos.push({titulo:'Entrega', valor:'<button '+lcClassBtn+' tipoacc="entrega">Entregar</button>'});
		}
	} else {
		loDatos.push({titulo:'Programación', valor:'<button '+lcClassBtn+' tipoacc="programa">Programar</button>'});
	}

	var loTabla = crearTabla(lcClass, loDatos, false, {titulo:'style="width:150px;"', valor:''});
	loTabla.appendTo('#divContenidoPrincipal');
	$('#divPrincipal').modal('show');
}



