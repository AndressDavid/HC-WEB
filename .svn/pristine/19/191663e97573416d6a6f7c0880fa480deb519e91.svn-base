var oModalJustificacionPos = {
	lcTitulo: 'Justificación POS',
	gcUrlAjax: 'vista-comun/ajax/modalJustificacionPos.php',
	gcCiePrincipal: '',
	gnCaracteresjustificacion: 0,
	aDatosJustificacion: [],
	fnEjecutar: false,

	inicializar: function()
	{
		oDiagnosticos.consultarDiagnostico('txtCieJustificacion','cCodigoCieJustificacion','cDescripcionCieJustificacion','','txtCieRelacionado1');
		oDiagnosticos.consultarDiagnostico('txtCieRelacionado1','cCodigoCieRel1Justificacion','cDescripcionCieRel1Justificacion','','txtCieRelacionado2');
		oDiagnosticos.consultarDiagnostico('txtCieRelacionado2','cCodigoCieRel2Justificacion','cDescripcionCieRel2Justificacion','','txtJustificacionPos');
		oModalJustificacionPos.cantidadCaracteresJustificacion();
		$('#btnGuardaJustificacion').on('click', oModalJustificacionPos.validarJustificacioPos);
		$('#btnSalirJustificacion').on('click', oModalJustificacionPos.cancelarJustificacionPos);
		$("#btnRelacionado1,#btnRelacionado2").on("click",function(e){
			lcObjeto = $(this).attr("id");
			lcMensaje = 'No existe diagnostico relacilacionado a eliminar.'
			lnValidar = 0;
			
			if (lcObjeto=='btnRelacionado1' && $('#cCodigoCieRel1Justificacion').val()==''){ lnValidar = 1; }
			if (lcObjeto=='btnRelacionado2' && $('#cCodigoCieRel2Justificacion').val()==''){ lnValidar = 2; }

			if (lnValidar==0){	
				oModalJustificacionPos.validarEliminarDiagnostico(lcObjeto); 
			}else{
				fnAlert(lcMensaje,oModalJustificacionPos.lcTitulo, false, 'blue', 'medium');
			}	
		});
		$('#txtJustificacionPos').on('keyup',function(){
			var lcJustificacion = $("#txtJustificacionPos").val().trim();
			oModalJustificacionPos.cantidadTextoJustificacion(lcJustificacion);
		});
	},
	
	cantidadTextoJustificacion: function(tcJustificacion)
	{
		var lnCaracteres = 0;
		if (tcJustificacion==''){
			lcTextoCaracteres = 'Caracteres: ' + 0;
			loCantidadJustificacion = $('#lblCaracteresJustificacionPos');
			loCantidadJustificacion.text(lcTextoCaracteres);
		}else{	
			lnCaracteres = $("#txtJustificacionPos").val().length;
			loCantidadJustificacion = $('#lblCaracteresJustificacionPos');
			lcTextoCaracteres = 'Caracteres: ' + lnCaracteres;
			loCantidadJustificacion.text(lcTextoCaracteres);
		}
		if (lnCaracteres==0 || (lnCaracteres<oModalJustificacionPos.gnCaracteresjustificacion)){
			$('#lblCaracteresJustificacionPos').addClass("text-danger").removeClass("text-primary");
		}else{
			$('#lblCaracteresJustificacionPos').addClass("text-primary").removeClass("text-danger");
		}
	},
	
	mostrar: function()
	{
		lcDatosProcedimiento=oProcedimientosOrdMedica.gcCupsOrdenar!='' ? (oProcedimientosOrdMedica.gcCupsOrdenar + ' - ' + oProcedimientosOrdMedica.gcDescripcionOrdenar) : (oModalAyudaProcedimientos.aDatosProcedimiento.CODIGO + ' - ' + oModalAyudaProcedimientos.aDatosProcedimiento.DESCRIPCION);
		$('#txtProcedimientosPos').val(lcDatosProcedimiento);

        setTimeout(function(){
			$("#divJustificacionPos").modal('show');
        }, 500);

	},

	validarEliminarDiagnostico: function(toObjeto)
	{
		lcTexto = 'Desea eliminar el diagnóstico relacionado ' + toObjeto.substr(14, 1) + '?';
		fnConfirm(lcTexto, oModalJustificacionPos.lcTitulo, false, false, 'medium',
			{
				text: 'Aceptar',
					action: function(){
						oModalJustificacionPos.eliminarDiagnostico(toObjeto);
					}
			},

			{ 
				text: 'Cancelar',
			}
		);
	},
	
	eliminarDiagnostico: function(tcTipo)
	{
		if (tcTipo=='btnRelacionado1'){
			$('#cCodigoCieRel1Justificacion,#cDescripcionCieRel1Justificacion').val('');
		}
		
		if (tcTipo=='btnRelacionado2'){
			$('#cCodigoCieRel2Justificacion,#cDescripcionCieRel2Justificacion').val('');
		}
	},
	
	inicializaJustificacion: function () {
		oModalJustificacionPos.gcCiePrincipal = '';
		$('#txtCieJustificacion,#txtCieRelacionado1,#txtCieRelacionado2,#txtJustificacionPos').val('');
		$('#cCodigoCieJustificacion,#cDescripcionCieJustificacion,#cCodigoCieRel1Justificacion').val('');
		$('#cDescripcionCieRel1Justificacion,#cCodigoCieRel2Justificacion,#cDescripcionCieRel2Justificacion').val('');
		$('#txtCieJustificacion').focus();
	},
	
	cantidadCaracteresJustificacion: function () {
		$.ajax({
			type: "POST",
			url: oModalJustificacionPos.gcUrlAjax,
			data: {accion: 'caracteresJustificacion'},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					oModalJustificacionPos.gnCaracteresjustificacion = parseInt(toDatos.TIPOS);
					oModalJustificacionPos.cantidadTextoJustificacion('');
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta cantidad caracteres justificación.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta cantidad caracteres justificación.");
		});
	},	
	
	validarJustificacioPos: function () {
		oModalJustificacionPos.aDatosJustificacion={};
		oModalJustificacionPos.gcCiePrincipal = ($("#cCodigoCieJustificacion").val()).trim();
		lcCieRelacionado1 = ($("#cCodigoCieRel1Justificacion").val()).trim();
		lcCieRelacionado2 = ($("#cCodigoCieRel2Justificacion").val()).trim();
		lcJustificacion = ($("#txtJustificacionPos").val()).trim();

		if (oModalJustificacionPos.gcCiePrincipal==''){
			$('#txtCieJustificacion').focus();
			fnAlert('Diagnóstico justificación obligatoria, revise por favor.');
			return false;
		}

		if (lcJustificacion==''){
			$('#txtJustificacionPos').focus();
			fnAlert('Justificación obligatoria, revise por favor.');
			return false;
		}
		 	
		if (oModalJustificacionPos.gcCiePrincipal!=''){
			if (lcCieRelacionado1!='' && oModalJustificacionPos.gcCiePrincipal==lcCieRelacionado1){
				$('#cCodigoCieRel1Justificacion').focus();
				fnAlert('Diagnósticos principal no puede ser igual a diagnóstico relacionado 1, revise por favor.');
				return false;	
			}
			
			if (lcCieRelacionado2!='' && oModalJustificacionPos.gcCiePrincipal==lcCieRelacionado2){
				$('#cCodigoCieRel2Justificacion').focus();
				fnAlert('Diagnósticos principal no puede ser igual a diagnóstico relacionado 2, revise por favor.');
				return false;	
			}

			if ((lcCieRelacionado1!='' && lcCieRelacionado2!='')
				&& (lcCieRelacionado1==lcCieRelacionado2)){
				$('#cCodigoCieRel1Justificacion').focus();
				fnAlert('Diagnósticos relacionado 1 no puede ser igual diagnóstico relacionado 2, revise por favor.');
				return false;	
			}
		}
		
		if (lcJustificacion.length<oModalJustificacionPos.gnCaracteresjustificacion){
			$('#txtJustificacionPos').focus();
			lcTexto = 'Justificación POS debe ser más completa (Mínimo ' + oModalJustificacionPos.gnCaracteresjustificacion +' caracteres), revise por favor.';
			fnAlert(lcTexto);
			return false;
		}
		
		oModalJustificacionPos.aDatosJustificacion= { POSCIEPRINCIPAL: oModalJustificacionPos.gcCiePrincipal,
					POSCIEPRINCIPALDES: ($("#cDescripcionCieJustificacion").val()).trim(),
					POSCIERELACIONADO1: lcCieRelacionado1,
					POSCIERELACIONADO1DES: ($("#cDescripcionCieRel1Justificacion").val()).trim(),
					POSCIERELACIONADO2: lcCieRelacionado2,
					POSCIERELACIONADO2DES: ($("#cDescripcionCieRel2Justificacion").val()).trim(),
					POSOBSERVACIONES: lcJustificacion};
		$("#divJustificacionPos").modal("hide");
		
		if (oProcedimientosOrdMedica.gcModuloAyudaCups!='A'){
			oProcedimientosOrdMedica.consolidarDatosCups();
		}
		oModalAyudaProcedimientos.validarHemocomponente();
	},
	
	cancelarJustificacionPos: function () {
		fnConfirm('Desea cancelar la orden del procedimiento POS?', oModalJustificacionPos.lcTitulo, false, false, 'medium',
			{
				text: 'Aceptar',
					action: function(){
						oModalJustificacionPos.inicializaJustificacion();
						oProcedimientosOrdMedica.inicializaCampos('');
						
						if($("#divObservacionCups").is(':visible')){
							$("#divObservacionCups").modal('hide');
						}	
						
						if (oProcedimientosOrdMedica.gcModuloAyudaCups==''){ 
							oProcedimientosOrdMedica.aCupsSelecionado.shift(); 
							oProcedimientosOrdMedica.validarTipoProcedimiento();
						}else{
							oModalAyudaProcedimientos.aCupsSelecionado.shift(); 
							oModalAyudaProcedimientos.solicitarObservaciones();
						}
					}
			},

			{ 
				text: 'Cancelar',
					action: function(){
						$('#divJustificacionPos').modal('show');
					}
			}
		);
	}

	
}