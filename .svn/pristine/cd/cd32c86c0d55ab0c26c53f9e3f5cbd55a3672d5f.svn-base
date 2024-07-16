var oModalMedicamentoControlado = {
	lcTitulo: 'Medicamento Controlado',
	gcUrlAjax: 'vista-comun/ajax/modalMedicamentoControlado.php',
	gaDatosmedicamento:'', gcValidarControlado:'', gaDatosControlado:'', gcTipoRegistro:'', gcAceptaIntercambio:'',
	gnIndex:0, gnEstado:0,

	inicializar: function()
	{
		this.consultarDiagnosticosPaciente();
		oDiagnosticos.consultarDiagnostico('txtCodigoControlado','cCodigoCieControlado','cDescripcionCieControlado','','inpCantidadControlado');
		$('#chkSoloDiagnosticoPaciente').prop('checked',true);
		$('#chkSoloDiagnosticoPaciente').change(this.verificarDiagnosticos);
		$('#selCieControladoPaciente').on('change',function() {
			oModalMedicamentoControlado.asignarDiagnosticoSeleccion();
		});
		$('#btnGuardaControlado').on('click', this.validarControlado);
		$('#btnCancelarControlado').on('click', this.cancelarControlado);
	},

	cancelarControlado: function () {
		lcTextoMensaje='MEDICAMENTO CONTROLADO <br>' + $('#cDescripcionControlado').val() + '<br>No se ha guardado el formato, el medicamento no se incluirá en la formulación.';
		
		fnConfirm(lcTextoMensaje, oModalMedicamentoControlado.lcTitulo, false, false, 'medium',
			{
				text: 'Aceptar',
					action: function(){
						$("#divMedicamentoControlado").modal('hide');
					}
			},

			{ 
				text: 'Cancelar',
					action: function(){
						$("#divMedicamentoControlado").modal('show');
					}
			}
		);
	},
	
	asignarDiagnosticoSeleccion: function()
	{
		var lcCodigoCie=$('#selCieControladoPaciente').val();
		var lcDescripcionCie=lcCodigoCie!='' ? $("#selCieControladoPaciente option[value="+lcCodigoCie+"]").text() : '';
		$('#cCodigoCieControlado').val(lcCodigoCie);
		$('#cDescripcionCieControlado').val(lcDescripcionCie);
		$('#selCieControladoPaciente').val('');
		$('#inpCantidadControlado').focus();
	},
	
	verificarDiagnosticos: function()
	{
		$("#selCieControladoPaciente,#cCodigoCieControlado,#cDescripcionCieControlado").val('');
		if($(chkSoloDiagnosticoPaciente).prop('checked')){
			$("#txtCodigoControlado").css("display","none");
			$("#selCieControladoPaciente").css("display","block");
			$('#selCieControladoPaciente').focus();		
		}else{
			$("#txtCodigoControlado").css("display","block");
			$("#selCieControladoPaciente").css("display","none");
			$('#txtCodigoControlado').focus();		
		}
	},	
	
	consultarDiagnosticosPaciente: function()
	{
		$.ajax({
			type: "POST",
			url: oModalMedicamentoControlado.gcUrlAjax,
			data: {accion: 'diagnosticosPaciente', lnIngreso: aDatosIngreso['nIngreso']},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					$.each(toDatos.TIPOS, function( lcKey, loTipo ) {
						$('#selCieControladoPaciente').append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCIONCIE + '</option>');
					});
					
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la busqueda Consultar diagnósticos paciente.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar Consultar diagnósticos paciente.");
		});
	},
	
	mostrar: function(taDatos,tcTipo,tnIdex,tnEstado,tcAceptaIntercambio)
	{
		oModalMedicamentoControlado.asignarUnidades(taDatos.CODIGO);
		let lnUnidadFrecuencia=parseInt(taDatos.CODUNIDADFRECUENCIA);
		let lnCantidadFrecuencia=parseInt(taDatos.FRECUENCIA);
		let lnCalculaFrecuencia=lnUnidadFrecuencia===1 && lnCantidadFrecuencia>0? Math.floor(24/lnCantidadFrecuencia) : 1;
		let lcDescripcioMedicamento=taDatos.DESCRIPCION===undefined ? (taDatos.MEDICAMENTO===undefined ? '' : taDatos.MEDICAMENTO) : taDatos.DESCRIPCION;
		oModalMedicamentoControlado.gaDatosmedicamento=taDatos;
		oModalMedicamentoControlado.gcTipoRegistro=tcTipo;
		oModalMedicamentoControlado.gnIndex=tnIdex;
		oModalMedicamentoControlado.gnEstado=tnEstado;
		oModalMedicamentoControlado.gcAceptaIntercambio=tcAceptaIntercambio;
		$('#cCodigoMedicamentoControlado').val(taDatos.CODIGO);
		$('#cDescripcionControlado').val(lcDescripcioMedicamento);
		$('#inpCantidadControlado').val(lnCalculaFrecuencia);
		$("#divMedicamentoControlado").modal('show');
	},

	asignarUnidades: function(tcCodigoMedicamento)
	{
		let lcUnidadPresenatcion='';
		$("#textoUnidadControlado").html('');
		$.ajax({
			type: "POST",
			url: oModalMedicamentoControlado.gcUrlAjax,
			data: {accion: 'consultarMedicamento', lcCodigoMedicamento: tcCodigoMedicamento},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					lcUnidadPresenatcion=toDatos.TIPOS[0]['PRESENTACION']+' '+toDatos.TIPOS[0]['CONCENTRACION']+' '+toDatos.TIPOS[0]['UNIDAD'];
					$("#textoUnidadControlado").html(lcUnidadPresenatcion);
					
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la busqueda consultar asignar unidades.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar Consultar asignar unidades.");
		});
	},	
	
	ocultar: function()
	{
		$("#divMedicamentoControlado").modal('hide');
	},
	
	blanqueDatos: function()
	{
		$('#cCodigoMedicamentoControlado,#cDescripcionControlado,#cCodigoCieControlado,#cDescripcionCieControlado,#txtObservacionesControlado').val('');
		$("#selCieControladoPaciente,#cCodigoCieControlado,#cDescripcionCieControlado,#txtCodigoControlado,#inpCantidadControlado").val('');
		oModalMedicamentoControlado.gcValidarControlado=oModalMedicamentoControlado.gaDatosControlado='';
		oModalMedicamentoControlado.gaDatosControlado.cCodigoCieControlado=oModalMedicamentoControlado.gaDatosControlado.ObservacionesControlado='';
		$('#chkSoloDiagnosticoPaciente').prop('checked',true);
	},
	
	validarControlado: function()
	{
		var lnCantidad=$("#inpCantidadControlado").val();
		var lcCodigoCie=($('#cCodigoCieControlado').val()).trim();
		var lcCodigoMedicamento=($('#cCodigoMedicamentoControlado').val()).trim();
		let lcDescripcioMedicamento=($('#cDescripcionControlado').val()).trim();
		oModalMedicamentoControlado.gaDatosControlado='';
		oModalMedicamentoControlado.gcValidarControlado='C';
		
		if (lcCodigoCie==''){
			if($(chkSoloDiagnosticoPaciente).prop('checked')){
				$('#selCieControladoPaciente').focus();	
			}else{
				$('#txtCodigoControlado').focus();		
			}		
			fnAlert('Debe seleccionar un diagnóstico.', oModalMedicamentoControlado.lcTitulo, false, false, false);
			return false;
		}
	
		if (parseInt(lnCantidad)==0 || lnCantidad==''){
			$('#inpCantidadControlado').focus();
			fnAlert('Debe indicar la cantidad prescrita.', oModalMedicamentoControlado.lcTitulo, false, false, false);
			return false;
		}
		
		if (lnCantidad<oMedicamentosOrdMedica.gnCantidadMinControlados || lnCantidad>oMedicamentosOrdMedica.gnCantidadMaxControlados){
			lcTextomensaje='Cantidad prescrita debe estar entre ' + oMedicamentosOrdMedica.gnCantidadMinControlados + ' y ' + oMedicamentosOrdMedica.gnCantidadMaxControlados;
			$('#inpCantidadControlado').focus();
			fnAlert(lcTextomensaje, oModalMedicamentoControlado.lcTitulo, false, false, false);
			return false;
		}
		
		let lcTextoCantidad='La cantidad del medicamento controlado esta correcta?.';
		fnConfirm(lcTextoCantidad, oModalMedicamentoControlado.lcTitulo, false, false, 'large',
			{ text: 'Aceptar', 
				action: function(){ 
					oModalMedicamentoControlado.gaDatosControlado=oModalMedicamentoControlado.obtenerDatos();
					oModalMedicamentoControlado.ocultar();
					
					if (oModalMedicamentoControlado.gcTipoRegistro=='A'){	
						oMedicamentosOrdMedica.adicionarMedicamento(lcCodigoMedicamento,oModalMedicamentoControlado.gaDatosmedicamento);
					}else{
						if (oModalMedicamentoControlado.gcTipoRegistro=='M'){
							oMedicamentosOrdMedica.preguntarUnirs(oModalMedicamentoControlado.gaDatosmedicamento,'',1,'','','M');
						}else{			
							if (oModalJustificacioInmediato.gcJustificacion!=''){
								
								if (oMedicamentosOrdMedica.gcEsUnirs!=''){
									oMedicamentosOrdMedica.preguntarUnirsMarcar(oModalJustificacioInmediato.gnIndex, 1, 1, 0, 3, oModalMedicamentoControlado.gnEstado, oModalMedicamentoControlado.gcAceptaIntercambio,oModalJustificacioInmediato.gcJustificacion,lcDescripcioMedicamento);
								}else{
									oMedicamentosOrdMedica.marcarFila(oModalJustificacioInmediato.gnIndex, 1, 1, 0, 3, oModalMedicamentoControlado.gnEstado, oModalMedicamentoControlado.gcAceptaIntercambio,oModalJustificacioInmediato.gcJustificacion);
								}
								oModalJustificacioInmediato.gcJustificacion='';
							}else{
								if (oMedicamentosOrdMedica.gcEsUnirs!=''){
									oMedicamentosOrdMedica.preguntarUnirsMarcar(oModalMedicamentoControlado.gnIndex, 1, oMedicamentosOrdMedica.gnSeleccionInmediato, 0, 3, oModalMedicamentoControlado.gnEstado, oModalMedicamentoControlado.gcAceptaIntercambio,'',lcDescripcioMedicamento);
								}else{
									oMedicamentosOrdMedica.marcarFila(oModalMedicamentoControlado.gnIndex, 1, oMedicamentosOrdMedica.gnSeleccionInmediato, 0, 3, oModalMedicamentoControlado.gnEstado, oModalMedicamentoControlado.gcAceptaIntercambio,'');
								}
								oMedicamentosOrdMedica.gnSeleccionInmediato=0;
							}
						}	
					}
				}
			},
			{  text: 'Cancelar',
				
			}
		);
	},
	
	obtenerDatos: function() {
		var laControlado = OrganizarSerializeArray($('#FormMedicamentoControlado').serializeArray());
		return laControlado;
	}
	
}