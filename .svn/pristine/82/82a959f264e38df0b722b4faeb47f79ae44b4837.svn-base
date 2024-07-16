var oModalProcedimientoCTC = {
	lcTitulo: 'Justificación Procedimientos NOPOS',
	fnEjecutar: false,
	gcMostrarDiagnostico:'',
	aProcedimientoPOS: {},

	inicializar: function(tcMostrarDiagnostico)
	{
		oDiagnosticos.consultarDiagnostico('txtCodigoCieOM','cCodigoCieOM','cDescripcionCieOM','','chkExistePOSPr');
		oModalProcedimientoCTC.gcMostrarDiagnostico=tcMostrarDiagnostico;
		oModalProcedimientoCTC.CargarReglas();
		oModalProcedimientoCTC.ConsultarParametroCTC("#selSolicitadoNP","ubicacionProcCTC");
		oModalProcedimientoCTC.ConsultarParametroCTC("#selObjetivoNP","objetivosProcCTC");
		$('#chkExistePOSPr').on("change",function(){oModalProcedimientoCTC.habilitarPrPos()});
		$('#btnGuardaProcCTC').on('click', oModalProcedimientoCTC.validarEnvioProcCTC);
		$('#btnCancelaProcCTC').on('click', oModalProcedimientoCTC.cancelarProcedimientoCTC);
		$('#selRiesgoNP').tiposRiesgo({tipo:'TiposRiesgo'});
		oModalProcedimientoCTC.habilitarPrPos();
		oProcedimientos.consultaProcedimientos('buscarProcedimientoP','codigoProcedimientoP','descripcionProcedimientoP','txtCantidadP','');
		$('#divProcedimientosCTC').on('hidden.bs.modal', oModalAyudaProcedimientos.SolicitarJustificacionCTC);
		
		if (oModalProcedimientoCTC.gcMostrarDiagnostico==''){
			$("#divDiagnosticoOM").hide();
		}	
	},

	// Valida cada uno de los procedimientos a ingresar
	validarEnvioProcCTC: function()
	{
		if (oModalProcedimientoCTC.validarFormasProcCTC()) {
			$("#divProcedimientosCTC").modal("hide");
			
			if (gcOrdenMedica=='OM'){
				oProcedimientosOrdMedica.insertarProcedimientoCTC();
			}else{	
				oProcedimientos.insertarProcedimientoCTC();
			}	
		}
	},

	// Valida cada uno de los medicamentos a ingresar
	validarFormasProcCTC: function()
	{
		if (! $('#FormProcedimientosCTC1').valid()){
			ubicarObjeto('#FormProcedimientosCTC1');
			return false;
		}

		if (! oModalProcedimientoCTC.validarPPOSCTC()){
			return false;
		}

		if (! $('#FormProcedimientosCTC3').valid()){
			ubicarObjeto('#FormProcedimientosCTC3');
			return false;
		}
		oProcedimientos.gcCupsPosNopos = '';
		return true;
	},

	mostrar: function(tfEjecutar)
	{
		$("#divProcedimientosCTC").modal('show');
		oModalProcedimientoCTC.fnEjecutar = tfEjecutar;
	},

	ocultar: function()
	{
		$("#divProcedimientosCTC").modal('hide');
		if (typeof oModalProcedimientoCTC.fnEjecutar==='function'){
			oModalProcedimientoCTC.fnEjecutar();
		}
	},

	validarProcedimientoPosCTC: function(tcProcedimientoPOS)
	{
		var llRetorno = true;
		var lcProcedimientoPOS = tcProcedimientoPOS;

		if($(chkExistePOSPr).prop('checked')){
			if (lcProcedimientoPOS==''){
				lcmensaje = 'Procedimiento POS obligatorio, revise por favor.';
				$("#buscarProcedimientoP").focus();
				fnAlert(lcmensaje, oModalProcedimientoCTC.lcTitulo, false, false, 'medium');
				llRetorno = false;
			}
			
			if (oProcedimientos.gcCupsPosNopos=='NOPOS'){
				lcmensaje = 'El procedimiento es NO POS, revise por favor.';
				$("#buscarProcedimientoP").focus();
				fnAlert(lcmensaje, oModalProcedimientoCTC.lcTitulo, false, false, 'medium');
				llRetorno = false;
			}
		}	
		return llRetorno;
	},
	
	iniciaModalProcedimientoCTC: function(taProcExiste, tcProcedimiento, tlExiste)
	{
		$("#chkRiesgoNP").prop('checked', false);
		oModalProcedimientoCTC.cargarProcedimiento(taProcExiste, tcProcedimiento, tlExiste);
		oModalProcedimientoCTC.mostrar()
	},

	CargarReglas: function()
	{
		loObjObl = {
			'#FormProcedimientosCTC1':[
				{OBJETO: "lblSolicitadoNP", REGLAS: '{"SolicitadoNP": {"required": true}}', CLASE:"1"},
				{OBJETO: "lblObjetivoNP", REGLAS: '{"ObjetivoNP": {"required": true}}', CLASE:"1"},
				{OBJETO: "lblCantidadNP", REGLAS: '{"CantidadNP": {"required": true}}', CLASE:"1"},
				{OBJETO: "lblRiesgoNP", REGLAS: '{"selRiesgoNP": {"required": true}}', CLASE:"1"},
				{OBJETO: "lblchkPacientePr", REGLAS: '{"chkPacientePr": {"required": true}}', CLASE:"1"},
				{OBJETO: "lblResumenP", REGLAS: '{"edtResumenP": {"required": true}}', CLASE:"1"}
			],
			'#FormProcedimientosCTC3':[
				{OBJETO: "lblResumenP", REGLAS: '{"edtResumenP": {"required": true}}', CLASE:"1"}
			]
		};

		var lopciones={};
		$.each(loObjObl, function( lcKeyForma, loOpciones ) {
			$.each(loOpciones, function( lcKey, loObj ) {

				if(loObj['CLASE']=="1" || loObj['CLASE']=="3" ){
					lopciones=Object.assign(lopciones,JSON.parse(loObj['REGLAS']));
				} else {
					var loTemp = loObj['REGLAS'].split('¤');
					lopciones[loTemp[0]]={required: function(element){
						return ReglaDependienteValor(loTemp[1],loTemp[2],loDatos.REGLAS[lcKey]['OBJETO']);
					}};
					if(loTemp.length==4){
						lopciones[loTemp[0]]=Object.assign(lopciones[loTemp[0]],JSON.parse(loTemp[3]));
					}
				}

				if(loObj['CLASE']=="1" || loObj['CLASE']=="2" ){
					$('#'+loObj['OBJETO']).addClass("required");
				}
			});
			oModalProcedimientoCTC.ValidarReglas(lcKeyForma, lopciones);
		});
	},

	ValidarReglas: function(tcForma, aOptions)
	{
		$( tcForma ).validate( {
			rules: aOptions,
			errorElement: "div",
			errorPlacement: function ( error, element ) {
				error.addClass( "invalid-tooltip" );

				if ( element.prop( "type" ) === "checkbox" ) {
					error.insertAfter( element.parent( "label" ) );
				} else {
					error.insertAfter( element );
				}
			},
			highlight: function ( element, errorClass, validClass ) {
				$( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
			},
			unhighlight: function (element, errorClass, validClass) {
				$( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
			},
		} );
	},

	ubicarObjetoCTC: function(toForma, tcObjeto)
	{
		tcObjeto = typeof tcObjeto === 'string'? tcObjeto: false;
		if (tcObjeto===false){
			var formerrorList = $(toForma).data('validator').errorList,
			lcObjeto = formerrorList[0].element.id;
			$('#'+lcObjeto).focus();
		}else {
			$(tcObjeto).focus();
		}
	},

	cancelarProcedimientoCTC: function () {
		fnConfirm('Desea cancelar la Justificación del Procedimiento?', oModalProcedimientoCTC.lcTitulo, false, false, 'medium',
				{
					text: 'Si',
					action: function(){
						oModalProcedimientoCTC.inicializaProcedimientoCTC();
						$('#divProcedimientosCTC').modal('hide');
						
						if (oProcedimientosOrdMedica.gcModuloAyudaCups==''){
							oProcedimientosOrdMedica.aCupsSelecionado.shift(); 
							oProcedimientosOrdMedica.validarTipoProcedimiento();
						}else{
							oModalAyudaProcedimientos.aCupsSelecionado.shift(); 
							oModalAyudaProcedimientos.solicitarObservaciones();
						}
					}
				},

				{ text: 'No',
					action: function(){
						$('#divProcedimientosCTC').modal('show');
					}
				}
			);
	},

	ConsultarParametroCTC: function(toObjeto, tcTipo)
	{
		var loSelect = $(toObjeto)
		loSelect.append('<option selected> </option>');

		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/modalCTC.php",
			data: {tipoDato: tcTipo},
			dataType: "json"
		})

		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.TIPOS != []) {
						$.each(toDatos.TIPOS, function( lcKey, loTipo ) {
							loSelect.append('<option value="' + lcKey + '">' + loTipo + '</option>');
						});
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de parámetros para Procedimientos CTC.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar parámetros para Procedimientos CTC.');
		});
	},

	habilitarPrPos: function()
	{
		oProcedimientos.gcCupsPosNopos = '';
		if($(chkExistePOSPr).prop('checked')){
			$('#lblProcedimientoP').addClass("required");
			$('#lblCantidadP').addClass("required");
			$('#buscarProcedimientoP').attr("disabled",false);
			$('#txtCantidadP').attr("disabled",false);
			$('#buscarProcedimientoP').focus();
		}else{
			$('#lblProcedimientoP').removeClass("required");
			$('#lblCantidadP').removeClass("required");
			$('#buscarProcedimientoP').attr("disabled","disabled");
			$('#txtCantidadP').attr("disabled","disabled");
			oModalProcedimientoCTC.IniciaProcedimientoPOS();
		}
	},

	IniciaProcedimientoPOS: function()
	{
		$("#buscarProcedimientoP,#codigoProcedimientoP,#descripcionProcedimientoP,#txtCantidadP").val('');
	},

	IniciaProcedimientoNoPOS: function()
	{
		$("#cCodigoCieOM,#cDescripcionCieOM,#ProcedimientoNP,#selSolicitadoNP,#selObjetivoNP,#txtCantidadNP,#edtRespuestaP").val('');
		$("#edtResumenP,#edtBibliografiaP,#chkPacientePr").val('');
		$("#selRiesgoNP,#chkExistePOSPr,#chkRiesgoNP").val(0);
	},
	
	validarPPOSCTC: function()
	{
		var llRetorno = true;
		if($(chkExistePOSPr).prop('checked')){
			laObjeto = {
					1: 'buscarProcedimientoP',
					2: 'txtCantidadP',
			};
			var lnCantidad = $("#txtCantidadP").val();
			var lcprocedimientoPOS = $("#codigoProcedimientoP").val();
			var llRetorno = oModalProcedimientoCTC.validarProcedimientoPosCTC(lcprocedimientoPOS);
			
			if(!llRetorno){
				return false;
			}

			if (lcprocedimientoPOS=='' || lnCantidad<=0 || lnCantidad>999){
				var lcObjeto = '';
				var lcmensaje = 'Campos Obligatorios:<br>';
				lcmensaje+=lcprocedimientoPOS==''?'Procedimiento POS,<br> ':'';
				lcObjeto = lcprocedimientoPOS=='' && lcObjeto==''?laObjeto[1]:lcObjeto;
				lcmensaje+=(lnCantidad<=0 || lnCantidad>999)?'Cantidad,<br> ':'';
				lcObjeto = (lnCantidad<=0 || lnCantidad>999) && lcObjeto==''?laObjeto[2]:lcObjeto;

				$('#'+lcObjeto).focus();
				fnAlert(lcmensaje, oModalProcedimientoCTC.lcTitulo, false, false, 'medium');
				return false;
			}
		}
		
		if (oModalProcedimientoCTC.gcMostrarDiagnostico=='OM'){
			if ($("#cCodigoCieOM").val()==''){
				lcmensaje='Diagnóstico obligatorio, revise por favor.';
				$('#txtCodigoCieOM').focus();
				fnAlert(lcmensaje, oModalProcedimientoCTC.lcTitulo, false, false, 'medium');
				return false;
			}
		}
		
		return llRetorno;
	},

	inicializaProcedimientoCTC: function()
	{
		oModalProcedimientoCTC.IniciaProcedimientoNoPOS();
		oModalProcedimientoCTC.IniciaProcedimientoPOS();
	},

	cargarProcedimiento: function(taExiste, taProcedimiento, tlExiste)
	{
		if(tlExiste){
			var lcProdNP = taProcedimiento.lcCodigoCups===undefined?taProcedimiento.CODIGO + ' - ' + taProcedimiento.DESCRIPCION:taProcedimiento.lcCodigoCups + ' - ' + taProcedimiento.lcDescripcionCups ;
			var lnCantidad = taProcedimiento.lcCantidadCups===undefined?taProcedimiento.CANTIDAD:taProcedimiento.lcCantidadCups;
			$("#ProcedimientoNP").val(lcProdNP);
			$("#selSolicitadoNP").val(taExiste.SOLICITADO);
			$("#selObjetivoNP").val(taExiste.OBJETIVO);
			$("#txtCantidadNP").val(lnCantidad);
			$("#selRiesgoNP").val(taExiste.RIESGO);
			$("#chkRiesgoNP").prop('checked', (taExiste.TIPOR=='1'));
			$("#edtRespuestaP").val(taExiste.RESPUESTA);
			$("#chkExistePOSPr").val(taExiste.EXISTE);
			$("#codigoProcedimientoP").val(taExiste.CODIGOPOS.trim());
			$("#descripcionProcedimientoP").val(taExiste.PROCEDIMPOS.trim());
			$("#txtCantidadP").val(taExiste.CANTIDADPOS);
			$("#edtResumenP").val(taExiste.RESUMEN);
			$('#edtBibliografiaP').val(taExiste.BIBLIOGRAFIA);
			$('#chkPacientePr').val(taExiste.PACIENTE);
		}else{
			oModalProcedimientoCTC.inicializaProcedimientoCTC();
			$("#ProcedimientoNP,#selSolicitadoNP,#selObjetivoNP,#txtCantidadNP,#selRiesgoNP,#edtRespuestaP,#edtResumenP,#edtBibliografiaP,#chkPacientePr,#cCodigoCieOM,#cDescripcionCieOM").removeClass("is-valid");
			$('#chkExistePOSPr,#chkRiesgoNP').prop('checked',false)
			$("#ProcedimientoNP").val(taProcedimiento.lcCodigoCups + ' - ' + taProcedimiento.lcDescripcionCups);
			$("#txtCantidadNP").val(taProcedimiento.lcCantidadCups);
			oModalProcedimientoCTC.habilitarPrPos();
		}
	},

	obtenerDatos: function() {
		return ($('#FormProcedimientosCTC1').serializeArray()).concat($('#FormProcedimientosCTC2').serializeArray()).concat($('#FormProcedimientosCTC3').serializeArray()).concat($('#FormProcedimientosCTC4').serializeArray()) ;
	},
}