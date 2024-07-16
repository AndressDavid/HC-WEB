var oAnalisis  = {
	ListaDxFallece: {},
	lcObjetoError: '',
	gcMensajeFallece: '',
	gcUrlajax: "vista-evoluciones/ajax/evoluciones.php",
	lcMensajeError : '',
	llMostrar: true,

	inicializar: function()
	{
		// Inicia datos para cargar opciones de conducta a seguir
		var lcConducta = 'EVPISO',
			lcTipo = '';
		lcTipo = typeof aDatosIngreso['TipoEV'] === 'string'? aDatosIngreso['TipoEV']: '';
		lcSeccion = aDatosIngreso['cSeccion'];

		if(lcTipo=='C' || lcTipo=='V' ||  (lcTipo=='P' && $.inArray(lcSeccion, ['CC','CV','CI','CA'])>=0)){
			lcConducta = 'EVUNID';
		}

		// se debe inicializar los diagnosticos fallece
		$('#selEstadoSalida').EstadoSalida({});
		$('#selConductaSeguir').conductaSeguir(lcConducta);
		$('#selConductaSeguir').change(oAnalisis.validarConductaSeguir);
		oDiagnosticos.consultarDiagnostico('buscarDxFallece','cCodigoDxFallece','cDescripcionDxFallece','FA','buscarDxFallece');
		oModalOrdenHospital.inicializar();
		if (!typeof oAval === 'object'){
			oAnalisis.textoFallece();
		}

		$('#buscarDxFallece').on('click',function() {
			if (oAnalisis.gcMensajeFallece!=''){
				fnAlert(oAnalisis.gcMensajeFallece, "Diagnóstico fallece", false, 'blue', 'medium');
			}
		});

		$('#selEstadoSalida').on('change',function() {
			if ($("#selEstadoSalida").val()=='002'){
				oModalAlertaFallece.mostrar();
			}else{
				oAnalisis.activarControles();
			}
		});	

		$('#btnAceptaFallece').on('click', oAnalisis.validarFallece);
		$('#btnCancelaFallece').on('click', oAnalisis.cancelarFallece);
	},

	textoFallece: function()
	{
		oAnalisis.gcMensajeFallece = '';
		$.ajax({
			type: "POST",
			url: oAnalisis.gcUrlajax,
			data: {lcTipo: 'consultartextofallece'},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					oAnalisis.gcMensajeFallece = toDatos.TIPOS;
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta mensaje fallece.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta  mensaje fallece.");
		});
	},

	validarFallece: function () {
		lcTextoFallece = $('#txtFallece').val().trim().toUpperCase();
		if (lcTextoFallece!='NO' && lcTextoFallece!='SI'){
			fnAlert("Debe escribir 'SI' ó 'NO', revise por favor.");
			return false;
		}

		if (lcTextoFallece=='NO'){
			oAnalisis.cancelarFallece();
			return false;
		}else{
			oAnalisis.activarControles();
		}
		oModalAlertaFallece.ocultar();
		$("#selEstadoSalida").focus();

		if (typeof oAval === 'object'){
			if(oAnalisis.llMostrar==true  && $("#selConductaSeguir").val()=='01'){
				oAnalisis.llMostrar=false;
				oAval.CargarTextoPandemia();
			}
		}
	},

	cancelarFallece: function () {
		$("#divEstadoSalida").show();
		$('#selEstadoSalida').val('');
		$('#cCodigoDxFallece').val('');
		$('#cDescripcionDxFallece').val('');
		$("#selEstadoSalida").focus();
		
		oModalAlertaFallece.ocultar();
		fnAlert("Seleccione nuevamente el estado de salida del paciente.", "", false, false, 'medium');
		if (typeof oAval === 'object'){
			if(oAnalisis.llMostrar==true  && $("#selConductaSeguir").val()=='01'){
				oAnalisis.llMostrar=false;
				oAval.CargarTextoPandemia();
			}
		}
	},

	// Función que valida la conducta a seguir
	validarConductaSeguir: function() {
		oModalOrdenHospital.inicializaOrdenHospitalizacion();
		var lcConducta = $("#selConductaSeguir").val();
		$('#selEstadoSalida').val('');
		$("#buscarDxFallece,#cCodigoDxFallece,#cDescripcionDxFallece").val('');
		switch (lcConducta) { 
			case '01': case 'Salida' :
				$("#divEstadoSalida").show();
				break;
			case '03':
				$("#divEstadoSalida,#divFecha,#divHora,#divFallece").hide();
				oModalOrdenHospital.verificarOrdenH(aDatosIngreso['nIngreso']);
				break;
			default:
				$("#divEstado,#divEstadoSalida,#divFecha,#divHora,#divFallece").hide();
				break;
		}
	},

	// Función que valida la conducta a seguir
	activarControles: function() {
		if($("#selEstadoSalida").val()=='002'){
			$("#divEstadoSalida,#divFecha,#divHora,#divFallece").show();
		}else{
			$("#divFecha,#divHora,#divFallece").hide();
		}
	},

	// Función que valida si el medicamento se encuentra en el listado
	validarDxFallece: function(tcDxFallece) {
		if(tcDxFallece !=''){
			var lnidx = oAnalisis.ListaDxFallece[tcDxFallece];
			if(lnidx===undefined){
				return false
			}
			return true
		}
	},

	// Función que adiciona el estado de salida cuando el paciente fallece
	AdicionarSalida: function() {
		$('#selEstadoSalida').val(aEstados['CodSalida']).attr('disabled',true);
		$('#txtEstado').val(aEstados['CodSalida']);
	},

	validacion: function()
	{
		var lbValido = true;
		// Si la conducta es salir el estado debe ser diligenciado
		var lcConducta = $("#selConductaSeguir").val();
		var lcEstado = $('#selEstadoSalida').val();
		var lcDxFallece = ($("#cCodigoDxFallece").val());

		if(lcConducta=='01' && lcEstado=='' ){
			oAnalisis.lcMensajeError = 'Dato Estado de Salida obligatorio. Revise por favor!';
			oAnalisis.lcObjetoError = "#selEstadoSalida";
			lbValido = false;
		}

		if(lcConducta=='01' && lcEstado=='002'){
			
			var lcfechahorafallece = moment($('#lcfechaFallece').val() + ' ' + $('#lcHoraFallece').val() , 'YYYY/MM/DD HH:mm');
			if (moment().isAfter(lcfechahorafallece) == false){
				oAnalisis.lcMensajeError = 'Dato Fecha y Hora Fallece mayor a la actual. Revise por favor!';
				oAnalisis.lcObjetoError = "#lcfechaFallece";
				lbValido = false;
			} 
			else{
				if(lcDxFallece==''){
					oAnalisis.lcMensajeError = 'Dato Diagnóstico Fallece obligatorio. Revise por favor!';
					oAnalisis.lcObjetoError = "#buscarDxFallece";
					lbValido = false;
				}
			}
		}

		if(lcConducta=='03'){
			var laOrdenHospitalizacion = $('#FormOrdenHospitalizacion').serializeAll(true);
			var lcAreaTrasladar=laOrdenHospitalizacion['AreaTrasladar'].trim();
			var lcEspecialidad=laOrdenHospitalizacion['EspecialidadOrden'].trim();
			var lcEstadoOrden=laOrdenHospitalizacion['EstadoOrden'].trim();
			var lcMedicoOrden=laOrdenHospitalizacion['medicoOrden'].trim();
			var lcUbicacionOrden=laOrdenHospitalizacion['selUbicacionTrasladar'].trim();

			if (lcEspecialidad==='' || lcEspecialidad===undefined){
				oAnalisis.lcMensajeError = 'Especialidad tratante orden hospitalización obligatoria, revise por favor.';
				oAnalisis.lcObjetoError = "#selEstadoSalida";
				lbValido = false;
			}

			if (lcMedicoOrden==='' || lcMedicoOrden===undefined){
				oAnalisis.lcMensajeError = 'Médico tratante orden hospitalización obligatoria, revise por favor.';
				oAnalisis.lcObjetoError = "#selEstadoSalida";
				lbValido = false;
			}

			if (lcAreaTrasladar=='' || lcAreaTrasladar===undefined){
				oAnalisis.lcMensajeError = 'Área a trasladar en orden hospitalización obligatoria, revise por favor.';
				oAnalisis.lcObjetoError = "#selEstadoSalida";
				lbValido = false;
			}

			if (lcUbicacionOrden=='' || lcUbicacionOrden===undefined){
				oAnalisis.lcMensajeError = 'Ubicación a trasladar en orden hospitalización obligatoria, revise por favor.';
				oAnalisis.lcObjetoError = "#selEstadoSalida";
				lbValido = false;
			}
		}
		return lbValido;
	},

	iniciaPlanManejo: function() {
	// inicia informacion de plan de manejo
		lcPlanManejo = '';
		if(typeof oEscalaHasbled === 'object' && oEscalaHasbled.cInterpretaC !== ''){
			lcPlanManejo = lcPlanManejo + oEscalaHasbled.cInterpretaC + '\n' ;
		}

		if(typeof oEscalaChadsvas === 'object' && oEscalaChadsvas.cInterpretaC !=='' ){
			lcPlanManejo = lcPlanManejo + oEscalaChadsvas.cInterpretaC + '\n' ;
		}

		if(typeof oEscalaCrusade === 'object' && oEscalaCrusade.cInterpretaC !== ''){
			lcPlanManejo = lcPlanManejo + oEscalaCrusade.cInterpretaC + '\n' ;
		}
		
		if(typeof oSadPersons === 'object' && oSadPersons.lcInterpretaConsultaSad !== ''){
			lcPlanManejo = lcPlanManejo + oSadPersons.lcInterpretaConsultaSad + '\n' ;
		}

		$('#edtManejo').val(lcPlanManejo);
	},	

	obtenerDatos: function() {
		var laAnalisis = OrganizarSerializeArray($('#FormAnalisis').serializeArray());
		if( $("#selConductaSeguir").val()=='03'){
			laAnalisis['OrdenHospitalizacion'] = oModalOrdenHospital.obtenerDatos();
		}
		return laAnalisis;
	}
};
