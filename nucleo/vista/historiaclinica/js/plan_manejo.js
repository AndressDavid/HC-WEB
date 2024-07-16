var oPlanManejo = {
	lcTitulo : 'Plan de manejo',
	lcObjetoError : '',
	lcMensajeError: '',

	inicializar: function(){
		$('#selEstadoSalidaPlan').EstadoSalida();
		$('#selConductaSeguir').conductaSeguir('HC');
		$('#SelModalidadGrupo').modalidadGrupoServicio();
		this.deactivaCamposInicia();
		this.reingresoIngreso();
		oModalOrdenHospital.inicializar();

		$('#SeltuvoElectro').on('change',function() {
			$('#txtTuvoElectrocardiograma').val('');
			if ($("#SeltuvoElectro").val()=='Si'){
				$('#txtTuvoElectrocardiograma').removeAttr("disabled");
				$('#lblTuvoElectrocardiograma').addClass("required");
			}else{
				$('#txtTuvoElectrocardiograma').attr("disabled","disabled");
				$('#lblTuvoElectrocardiograma').removeClass("required");
			}
		});

		$('#selConductaSeguir').on('change',function() {
			$('#selEstadoSalidaPlan').val('');
			if ($("#selConductaSeguir").val()=='01'){
				// $("#divestadoSalidaPlan").css("visibility", "visible");
				$("#divestadoSalidaPlan").show();
			}else{
				// $("#divestadoSalidaPlan").css("visibility", "hidden");
				$("#divestadoSalidaPlan").hide();
			}
			if ($("#selConductaSeguir").val()=='03'){
				oModalOrdenHospital.verificarOrdenH(aDatosIngreso['nIngreso']);
			}else{
				oModalOrdenHospital.inicializaOrdenHospitalizacion();
			}
		});

		$('#selEstadoSalidaPlan').on('change',function() {
			if ($("#selEstadoSalidaPlan").val()=='002'){
				$('#txtFallece').val('');
				oModalAlertaFallece.mostrar();
			}
		});
		$('#btnAceptaFallece').on('click', this.validarFallece);
		$('#btnCancelaFallece').on('click', this.cancelarFallece);
	},

	validarFallece: function () {
		lcTextoFallece = $('#txtFallece').val().trim().toUpperCase();

		if (lcTextoFallece!='NO' && lcTextoFallece!='SI'){
			fnAlert("Debe escribir 'SI' ó 'NO', revise por favor.");
			return false;
		}
		if (lcTextoFallece=='NO'){
			oPlanManejo.cancelarFallece();
			return false;
		}
		oModalAlertaFallece.ocultar();
		$("#selEstadoSalidaPlan").focus();
	},

	cancelarFallece: function () {
		fnAlert("Seleccione nuevamente el estado de salida del paciente.", "", false, false, 'medium');
		$('#selEstadoSalidaPlan').val('');
		oModalAlertaFallece.ocultar();
		$("#selEstadoSalidaPlan").focus();
	},

	deactivaCamposInicia: function (){
		$("#txtTuvoElectrocardiograma").attr("disabled",true)
		// $("#divestadoSalidaPlan").css("visibility", "hidden");
		$("#divestadoSalidaPlan").hide();
		$("#divReingresoMismaCausa").hide();
	},

	reingresoIngreso: function () {
		$.ajax({
			type: "POST",
			url: 'vista-historiaclinica/ajax/plan_manejo.php',
			data: {lcConductaSeguir: 'validaReingreso'},
			dataType: "json"
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					if (loTipos.TIPOS.validar==true){
						// $("#divReingresoMismaCausa").css("visibility", "visible");
						$("#divReingresoMismaCausa").hide();
					}
				} else {
					alert(loTipos.error + ' ', "warning");
				}
			} catch(err) {
				alert('No se pudo realizar la busqueda de ' + mensaje +'.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			alert('Se presentó un error al buscar ' + mensaje +'.', "danger");
		});
		return this;
	},

	validacion: function(){
		var lbValido = true;
		this.lcObjetoError = '';
		$lcCodigoConductaSeguir = $("#selConductaSeguir").val();
		$lcCodigoEstadoSalida = $("#selEstadoSalidaPlan").val();

		if ($("#selConductaSeguir").val()!=''){
			if ($("#selConductaSeguir option[value="+ $lcCodigoConductaSeguir +"]").length === 0){
				this.lcObjetoError = 'selConductaSeguir';
				this.lcMensajeError = 'Conducta a seguir modificada, revise por favor.' ;
				lbValido = false;
			}
		}

		if ($lcCodigoConductaSeguir =='01' && $lcCodigoEstadoSalida==''){
			this.lcObjetoError = 'selEstadoSalidaPlan';
			this.lcMensajeError = 'Estado de salida obligatorio, revise por favor.' ;
			lbValido = false;
		}

		if ($lcCodigoEstadoSalida!=''){
			if ($("#selEstadoSalidaPlan option[value="+ $lcCodigoEstadoSalida +"]").length === 0){
				this.lcObjetoError = 'selEstadoSalidaPlan';
				this.lcMensajeError = 'Estado salida modificado, revise por favor.';
				lbValido = false;
			}
		}

		if ($("#SeltuvoElectro").val()=='Si'){
			if ($("#txtTuvoElectrocardiograma").val()==''){
				this.lcObjetoError = 'txtTuvoElectrocardiograma';
				this.lcMensajeError = 'Descripción electrocardiograma es obligatorio., revise por favor.';
				lbValido = false;
			}
		}

		if ($("#SelReingreso").val()!=''){
			if ($("#SelReingreso").val()!='S' &&  $("#SelReingreso").val()!='N'){
				this.lcObjetoError = 'SelReingreso';
				this.lcMensajeError = 'Reingreso misma causa modificado, revise por favor.';
				lbValido = false;
			}
		}

		if ($("#SelDoctorInforma").val()!='S' &&  $("#SelDoctorInforma").val()!='N'){
			this.lcObjetoError = 'SelDoctorInforma';
			this.lcMensajeError = 'El doctor informa al paciente modificado, revise por favor.';
			lbValido = false;
		}

		if ($("#txtAnalisisPlan").val()==''){
			this.lcObjetoError = 'txtAnalisisPlan';
			this.lcMensajeError = 'Descripción Análisis y plan de manejo modificado, no valido, revise por favor.';
			lbValido = false;
		}

		if ($("#SelModalidadGrupo").val()==''){
			this.lcObjetoError = 'SelModalidadGrupo';
			this.lcMensajeError = 'Modalidad grupo servicio es obligatorio., revise por favor.';
			lbValido = false;
		}

		if ($("#SelAtencionDomiciliaria").val()!=''){
			if ($("#SelAtencionDomiciliaria").val()!='S' &&  $("#SelAtencionDomiciliaria").val()!='N'){
				this.lcObjetoError = 'SelAtencionDomiciliaria';
				this.lcMensajeError = 'Atención derivada domiciliaria, revise por favor.';
				lbValido = false;
			}
		}

		return lbValido;
	},

	obtenerDatos: function() {
		var laPlanManejo = OrganizarSerializeArray($('#FormPlanManejo').serializeArray());
		var laOrdenHospitalizacion = oModalOrdenHospital.obtenerDatos();
		laPlanManejo['OrdenHospitalizacion']=laOrdenHospitalizacion;
		return laPlanManejo;
	},
}
