var oOxiGlucometriaOrdMedica = {
	gcUrlAjax: 'vista-ordenes-medicas/ajax/ajax',
	lcTitulo : 'Oxígeno/Glucometria ordenes médicas',
	lcFormaError : '', lcObjetoError: '', lcMensajeError: '',
	cOxigeno: '', cCodCup: '', cRefProc: '', nDosis: '', cUnidadDosis: '', cObservaciones: '', nConsFormula: '',
	nEstadoFormula: '', nEstadoAntes: '', cCodCupAntes: '', cSuspenderOxigeno: '', cEstadoGrabaAntes: '', 
	cCupsGlucometria: '', cDescCupsGlucometria: '', cCodigoEspGlucometria: '', cPosNoposGlucometria: '', cHexalisGlucometria: '', 
	nFechaAntes: 0, nDosisMinimaOxi: 0, nDosisMaximaOxi: 0, 
	aProcedimientosGlucometria: [],
	lPrimeraFormula: false, lCobrar_Consumos: true,

	inicializar: function(){
		this.cargarMetodosOxigeno();
		this.consultaCobrarOxigeno();
		this.consultarCupsGlucometria();
		this.cargarMetodosGlucometria();
		this.verificaGlucometriasDia();
		this.deactivaCamposInicia();
		
		$('#selOrdOxiPacNececesita').on('change',function() {
			oOxiGlucometriaOrdMedica.habilitaCamposOxigeno();
		});
		
		$('#selNecesarioGlucometria').on('change',function() {
			oOxiGlucometriaOrdMedica.habilitaCamposGlucometria();
		});
		
		$('#selTipoMetodoGlucometria').on('change',function() {
			oOxiGlucometriaOrdMedica.datosCupsGlucometria();
		});		
			
		$('#selTipoMetodoOxigeno, #txtDosisOxigeno, #txtObservacionesOxigeno').on('change',function() {
			oOxiGlucometriaOrdMedica.datosOxigenoEnfermeria();
		});	
		$('#chkSuspenderOxigeno').change(this.suspenderOxigeno);
		$('#FormOrdMedOxigenoGlucometrias').on('submit', function(e){e.preventDefault();});
	},
	
	datosCupsGlucometria: function (){
		$('#txtObservacionesGlucometria').val('');
		var lcIdObservacion = $("#selTipoMetodoGlucometria option:selected").attr("id-observa");
		var lcCantidadGlucometrias = $("#selTipoMetodoGlucometria option:selected").attr("id-cantidad");
		
		if (lcIdObservacion!=''){
			$('#txtObservacionesGlucometria').val(lcIdObservacion);
		}
		
		if (lcCantidadGlucometrias!=''){
			laDatosGlucometria = {CANTIDAD: lcCantidadGlucometrias, OBSERVACIONES: lcIdObservacion}
			oOxiGlucometriaOrdMedica.insertarCupsGlucometria(laDatosGlucometria);
		}
	},	
		
	insertarCupsGlucometria: function (taDatosGlucometria){
		oOxiGlucometriaOrdMedica.aProcedimientosGlucometria = [];
		var lncantidadCups =  parseInt(taDatosGlucometria.CANTIDAD);
		for (cantidad=1; cantidad<=lncantidadCups; cantidad++){
			oOxiGlucometriaOrdMedica.aProcedimientosGlucometria.push({
					TIPO: 'GLUCO', 
					CODIGO: oOxiGlucometriaOrdMedica.cCupsGlucometria, 
					DESCRIPCION: oOxiGlucometriaOrdMedica.cDescCupsGlucometria, 
					ESPECIALIDAD: oOxiGlucometriaOrdMedica.cCodigoEspGlucometria, 
					TIPOADT:'A01',
					TIPOMENSAJE: 'ADT',
					POSNOPOS: oOxiGlucometriaOrdMedica.cPosNoposGlucometria, 
					HEXALIS: oOxiGlucometriaOrdMedica.cHexalisGlucometria, 
					OBSERVACIONES: taDatosGlucometria.OBSERVACIONES, 
					LINEA: cantidad, 
					NECESARIOGLUCOMETRIA: $("#selNecesarioGlucometria").val(),
			});
		}
	},
	
	actualizaDatosGlucometria: function() {
		var lcObservaciones=$("#txtObservacionesGlucometria").val();
		
		for (var i = 0; i < oOxiGlucometriaOrdMedica.aProcedimientosGlucometria.length; i++) {
			oOxiGlucometriaOrdMedica.aProcedimientosGlucometria[i].OBSERVACIONES = lcObservaciones;
		}
	},	
	
	habilitaCamposGlucometria: function (){
		$('#selTipoMetodoGlucometria').val('');
		$('#txtObservacionesGlucometria').val('');
		
		if ($("#selNecesarioGlucometria").val()=='Si'){
			$('#lblTipoMetodoGlucometria').addClass("required");
			$('#selTipoMetodoGlucometria').removeAttr("disabled");
			$('#txtObservacionesGlucometria').removeAttr("disabled");
		}else{
			$('#lblTipoMetodoGlucometria').removeClass("required");
			$('#selTipoMetodoGlucometria').attr("disabled","disabled");
			$('#txtObservacionesGlucometria').attr("disabled","disabled");
		}	
	},
	
	habilitaCamposOxigeno: function (){
		$('#selTipoMetodoOxigeno,#txtOrdMedDatosOxigeno,#txtDosisOxigeno,#txtObservacionesOxigeno').val('');
		$('#selTipoMetodoOxigeno,#txtDosisOxigeno,#txtObservacionesOxigeno').removeClass("is-valid");
		if ($("#selOrdOxiPacNececesita").val()=='Si'){
			$('#lblTipoMetodoOxigeno').addClass("required");
			$('#selTipoMetodoOxigeno').removeAttr("disabled");
			$('#lblDosisOxigeno').addClass("required");
			$('#txtDosisOxigeno').removeAttr("disabled");
			$('#txtObservacionesOxigeno').removeAttr("disabled");
		}else{
			$('#lblTipoMetodoOxigeno').removeClass("required");
			$('#selTipoMetodoOxigeno').attr("disabled","disabled");
			$('#lblDosisOxigeno').removeClass("required");
			$('#txtDosisOxigeno').attr("disabled","disabled");
			$('#txtObservacionesOxigeno').attr("disabled","disabled");
			$('#txtOrdMedDatosOxigeno').val('No requiere Oxígeno');
		}
	},	
	
	deactivaCamposInicia: function (){
		$("#selTipoMetodoGlucometria").attr("disabled",true)
	},
	
	datosOxigenoEnfermeria: function() {
		//oOxiGlucometriaOrdMedica.cRefProc = $("#selTipoMetodoOxigeno option:selected").attr("id-cups");
		oOxiGlucometriaOrdMedica.cRefProc = $("#selTipoMetodoOxigeno").val();
		oOxiGlucometriaOrdMedica.cOxigeno = $("#selOrdOxiPacNececesita").val();
		oOxiGlucometriaOrdMedica.nDosis = $("#txtDosisOxigeno").val();
		oOxiGlucometriaOrdMedica.cObservaciones = $("#txtObservacionesOxigeno").val();
		
		if (oOxiGlucometriaOrdMedica.cRefProc!='' && oOxiGlucometriaOrdMedica.cOxigeno!='' && oOxiGlucometriaOrdMedica.nDosis!=''){
			oOxiGlucometriaOrdMedica.actualizaOxigenoEnfermeria('');
		}	
	},

	habilitar: function() {
		if (oOxiGlucometriaOrdMedica.cOxigeno!='Si'){
			$('#lblTipoMetodoOxigeno').removeClass("required");
			$('#lblDosisOxigeno').removeClass("required");
		}else{
			$('#selTipoMetodoOxigeno').removeAttr("disabled");
			$('#txtDosisOxigeno').removeAttr("disabled");
			$('#txtObservacionesOxigeno').removeAttr("disabled");
		}	
	},
	
	suspenderOxigeno: function() {
		if ($("#chkSuspenderOxigeno").prop("checked")){
			if ($("#selOrdOxiPacNececesita").val()=='Si'){
				oOxiGlucometriaOrdMedica.cSuspenderOxigeno = 'S';
				$("#selOrdOxiPacNececesita").val('No');
				$("#selTipoMetodoOxigeno").val('');
				$("#txtDosisOxigeno").val('');
				$("#txtObservacionesOxigeno").val('');
				$('#lblTipoMetodoOxigeno').removeClass("required");
				$('#lblDosisOxigeno').removeClass("required");
				$('#selTipoMetodoOxigeno').attr("disabled","disabled");
				$('#txtDosisOxigeno').attr("disabled","disabled");
				$('#txtObservacionesOxigeno').attr("disabled","disabled");
				oOxiGlucometriaOrdMedica.actualizaOxigenoEnfermeria('S');
			}
		}else{
			if (oOxiGlucometriaOrdMedica.cOxigeno!=''){
				let lcDosisOxigeno=oOxiGlucometriaOrdMedica.cOxigeno=='No' ? '' : oOxiGlucometriaOrdMedica.nDosis;
				$("#selOrdOxiPacNececesita").val(oOxiGlucometriaOrdMedica.cOxigeno);
				$("#selTipoMetodoOxigeno").val(oOxiGlucometriaOrdMedica.cCodCup);
				$("#txtDosisOxigeno").val(lcDosisOxigeno);
				$("#txtObservacionesOxigeno").val(oOxiGlucometriaOrdMedica.cObservaciones.trim());
				$('#selTipoMetodoOxigeno,#txtDosisOxigeno,#txtObservacionesOxigeno').removeAttr("disabled");
			}	
		}
	},
	
	consultarCupsGlucometria: function() {
		$.ajax({
			type: "POST",
			url: oOxiGlucometriaOrdMedica.gcUrlAjax,
			data: {accion: 'consultaCupsGlucometria'},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					oOxiGlucometriaOrdMedica.cCupsGlucometria = loTipos.TIPOS.CODIGO;
					oOxiGlucometriaOrdMedica.cDescCupsGlucometria = loTipos.TIPOS.DESCRIPCION;
					oOxiGlucometriaOrdMedica.cCodigoEspGlucometria = loTipos.TIPOS.ESPECIALIDAD;
					oOxiGlucometriaOrdMedica.cPosNoposGlucometria = loTipos.TIPOS.POSNOPOS;
					oOxiGlucometriaOrdMedica.cHexalisGlucometria = loTipos.TIPOS.HEXALIS;
				} else {
					fnAlert(loTipos.error + ' ', "warning");
				}

			} catch(err) {
				fnAlert('No se pudo realizar la busqueda consultar cups glucometría.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar listado consultar cups glucometría.', "danger");
		});
		return this;
	},
	
	cargarMetodosOxigeno: function() {
		var loSelect = $('#selTipoMetodoOxigeno');
		var lcTipo = 'tablaMetodoOxigeno';

		$.ajax({
			type: "POST",
			url: oOxiGlucometriaOrdMedica.gcUrlAjax,
			data: {accion: lcTipo},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
						//loSelect.append('<option id-cups="' + loTipo.CODREF + '" value="' + loTipo.CODCUP + '">' + loTipo.DESCUP + '</option>');
						loSelect.append('<option id-cups="' + loTipo.CODCUP + '" value="' + loTipo.CODREF + '">' + loTipo.DESCUP + '</option>');
					});
					oOxiGlucometriaOrdMedica.consultaUltFormOxigeno();
				} else {
					fnAlert(loTipos.error + ' ', "warning");
				}

			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de listado Cargar métodos oxígeno.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar listado Cargar métodos oxígeno.', "danger");
		});
		return this;
	},
	
	cargarMetodosGlucometria: function() {
		var loSelect = $('#selTipoMetodoGlucometria');
		var lcTipo = 'tablaMetodoGlucometria';

		$.ajax({
			type: "POST",
			url: oOxiGlucometriaOrdMedica.gcUrlAjax,
			data: {accion: lcTipo},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
						loSelect.append('<option id-cantidad="' + loTipo.CANTIDAD + '" id-observa="' + loTipo.OBSERVA + '" value="' + loTipo.CODIGO + '">' + loTipo.DESCRIP + '</option>');
					});
				} else {
					fnAlert(loTipos.error + ' ', "warning");
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de listado Cargar métodos glucometria.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar listado Cargar métodos glucometria.', "danger");
		});
		return this;
	},
	
	verificaGlucometriasDia: function() {
		var lcTipo = 'consultaGlucometriasDiarias';

		$.ajax({
			type: "POST",
			url: oOxiGlucometriaOrdMedica.gcUrlAjax,
			data: {accion: lcTipo, lnIngreso: aDatosIngreso.nIngreso},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					oOxiGlucometriaOrdMedica.alertaGlucometria(loTipos.TIPOS);
				} else {
					fnAlert(loTipos.error + ' ', "warning");
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de verifica glucometrías diarias.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar verifica gllucometrías diarias.', "danger");
		});
		return this;
	},
	
	alertaGlucometria: function(tcDatosAlerta) {
		var lcExistenGlucometrias = tcDatosAlerta.substring(0, 1);
		var lcTextoAlertaGlucometrias = tcDatosAlerta.substring(1, 120);
		$("#textoAlertaGlucometria").html(lcTextoAlertaGlucometrias);
		if (lcExistenGlucometrias!='N'){
			$("#textoAlertaGlucometria").addClass( "alert-danger" ).removeClass( "alert-light" );
			$("#textoAlertaGlucometria").addClass( "text-danger" ).removeClass( "text-dark" );
		}	
	},	

	consultaCobrarOxigeno: function() {
		var lcTipo = 'consultarCobroOxigeno';

		$.ajax({
			type: "POST",
			url: oOxiGlucometriaOrdMedica.gcUrlAjax,
			data: {accion: lcTipo, lcViaIngreso: aDatosIngreso.cCodVia, lcSeccion: aDatosIngreso.cSeccion},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					oOxiGlucometriaOrdMedica.lCobrar_Consumos = loTipos.TIPOS;
				} else {
					fnAlert(loTipos.error + ' ', "warning");
				}

			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de Consulta cobrar oxígeno.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar listado Consulta cobrar oxígeno.', "danger");
		});
		return this;
	},
	
	consultaUltFormOxigeno: function() {
		$('#chkSuspenderOxigeno').prop("checked",false).attr("disabled",true);
		$.ajax({
			type: "POST",
			url: oOxiGlucometriaOrdMedica.gcUrlAjax,
			data: {accion: 'consUltForOxigeno', lnIngreso: aDatosIngreso.nIngreso},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					if (loTipos.TIPOS!=false){
						oOxiGlucometriaOrdMedica.lPrimeraFormula = false;
						oOxiGlucometriaOrdMedica.cargarDatosOxigeno(loTipos.TIPOS);
					}else{
						$("#txtDosisOxigeno").val('');
						oOxiGlucometriaOrdMedica.lPrimeraFormula = true;
					}
				} else {
					fnAlert(loTipos.error + ' ', "warning");
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la busqueda de consulta última formula oxigeno.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar listado dietas médicas.', "danger");
		});
		return this;
	},
	
	cargarDatosOxigeno: function(taDatosOxigeno) {
		oOxiGlucometriaOrdMedica.cOxigeno = taDatosOxigeno.CUPS.trim()!='' ? 'Si' : 'No';
		oOxiGlucometriaOrdMedica.cCodCup = taDatosOxigeno.CUPS;
		oOxiGlucometriaOrdMedica.cRefProc = taDatosOxigeno.REFERENCIA_CUPS;
		oOxiGlucometriaOrdMedica.nDosis = taDatosOxigeno.DOSIS_FORMULA=='0' ? '' : taDatosOxigeno.DOSIS_FORMULA;
		oOxiGlucometriaOrdMedica.cUnidadDosis = taDatosOxigeno.UNIDAD_DOSIS;
		oOxiGlucometriaOrdMedica.cObservaciones = taDatosOxigeno.OBSERVACIONES.trim();
		oOxiGlucometriaOrdMedica.nConsFormula = taDatosOxigeno.CONS_FORMULA;
		oOxiGlucometriaOrdMedica.nEstadoFormula = taDatosOxigeno.ESTADO;
		oOxiGlucometriaOrdMedica.nEstadoAntes = taDatosOxigeno.ESTADO;
		oOxiGlucometriaOrdMedica.cCodCupAntes = taDatosOxigeno.CUPS;
		oOxiGlucometriaOrdMedica.nFechaAntes = taDatosOxigeno.FECHA_ANTES;
		oOxiGlucometriaOrdMedica.cEstadoGrabaAntes = taDatosOxigeno.ESTADO_GRABACION_ANTES;
		$("#selOrdOxiPacNececesita").val(oOxiGlucometriaOrdMedica.cOxigeno);
		$("#selTipoMetodoOxigeno").val(oOxiGlucometriaOrdMedica.cRefProc);
		$("#txtObservacionesOxigeno").val(oOxiGlucometriaOrdMedica.cObservaciones);

		if (oOxiGlucometriaOrdMedica.cOxigeno=='No'){
			$("#txtDosisOxigeno").val('');
			$('#selOrdOxiPacNececesita').removeAttr("disabled");
			$("#btnSuspenderOxigeno").attr("disabled","disabled");
		}else{
			$("#txtDosisOxigeno").val(oOxiGlucometriaOrdMedica.nDosis);
			$('#selOrdOxiPacNececesita').attr("disabled","disabled");
			$('#btnSuspenderOxigeno').removeAttr("disabled");
			$('#chkSuspenderOxigeno').attr("disabled",false);
		}	
		oOxiGlucometriaOrdMedica.actualizaOxigenoEnfermeria('');
	},	
	
	actualizaOxigenoEnfermeria: function(tcSuspendeOxigeno) {
		laDatosOxigeno = {
				'PacienteNecesita': '',
				'Cups': '',
				'Dosis': '',
				'Observacion': ''
			};
		
		if ($("#selOrdOxiPacNececesita").val()=='Si'){
			let lcDescripcionCupsOxigeno = oOxiGlucometriaOrdMedica.cRefProc!='' ? $("#selTipoMetodoOxigeno option[value="+oOxiGlucometriaOrdMedica.cRefProc+"]").text() : '';
			
			laDatosOxigeno = {
				'PacienteNecesita': oOxiGlucometriaOrdMedica.cOxigeno,
				'Cups': lcDescripcionCupsOxigeno,
				'Dosis': oOxiGlucometriaOrdMedica.nDosis,
				'Observacion': oOxiGlucometriaOrdMedica.cObservaciones
			};
			oOxiGlucometriaOrdMedica.habilitar();	
		}
		oEnfermeriaOrdMedica.actualizaDatosOxigeno(laDatosOxigeno,tcSuspendeOxigeno);
	},	
		
	validacionOxigeno: function() {
		var lbValido = true;
		oOxiGlucometriaOrdMedica.lcFormaError = 'FormOrdMedOxigenoGlucometrias';
		oOxiGlucometriaOrdMedica.lcObjetoError = 'selOrdOxiPacNececesita';
		
		var lcNecesitaOxigeno = $("#selOrdOxiPacNececesita").val();
		var lcIdMetodo = $("#selTipoMetodoOxigeno option:selected").attr("id-cups");
		var lcMetodoOxigeno = $("#selTipoMetodoOxigeno").val();
		var lcDosisoOxigeno = $("#txtDosisOxigeno").val();
		var lcObservacionOxigeno = $("#txtObservacionesOxigeno").val();
		
		if (lcNecesitaOxigeno==null){
			oOxiGlucometriaOrdMedica.lcMensajeError = 'Paciente necesita oxigeno nulo, revise por favor.';
			$("#selOrdOxiPacNececesita").focus();
			lbValido = false;
		}
		
		if (lcNecesitaOxigeno!='Si' && lcNecesitaOxigeno!='No'){
			oOxiGlucometriaOrdMedica.lcMensajeError = 'Paciente necesita oxigeno no corresponde, revise por favor.';
			$("#selOrdOxiPacNececesita").focus();
			lbValido = false;
		}

		if (lcNecesitaOxigeno==''){
			oOxiGlucometriaOrdMedica.lcMensajeError = 'Paciente necesita oxigeno obligatorio, revise por favor.';
			$("#selOrdOxiPacNececesita").focus();
			lbValido = false;
		}
		
		if (lcNecesitaOxigeno=='No'){
			if (lcMetodoOxigeno!='' || lcDosisoOxigeno!='' || lcObservacionOxigeno!=''){
				oOxiGlucometriaOrdMedica.lcMensajeError = 'Campos con datos registrados si indica NO necesita oxigeno, revise por favor.';
				$("#selOrdOxiPacNececesita").focus();
				lbValido = false;
			}
		}
		
		if (lcNecesitaOxigeno=='Si'){
			let lnDosisOxigeno=parseFloat($("#txtDosisOxigeno").val());
			if (lnDosisOxigeno<parseFloat(oOxiGlucometriaOrdMedica.nDosisMinimaOxi)){
				lcTextoOxigeno = 'Dosis oxigeno ' + lnDosisOxigeno + ', no puede ser menor a dosis mímima ' +oOxiGlucometriaOrdMedica.nDosisMinimaOxi+ ', revise por favor.';
				oOxiGlucometriaOrdMedica.lcMensajeError = lcTextoOxigeno;
				$("#selOrdOxiPacNececesita").focus();
				lbValido = false;
			}

			if (lnDosisOxigeno>parseFloat(oOxiGlucometriaOrdMedica.nDosisMaximaOxi)){
				lcTextoOxigeno = 'Dosis oxigeno ' + lnDosisOxigeno + ', no puede ser mayor a dosis máxima ' +oOxiGlucometriaOrdMedica.nDosisMaximaOxi+ ', revise por favor.';
				oOxiGlucometriaOrdMedica.lcMensajeError = lcTextoOxigeno;
				$("#selOrdOxiPacNececesita").focus();
				lbValido = false;
			}
			
			if (lcIdMetodo=='' || lcMetodoOxigeno=='' || lcDosisoOxigeno==''){
				oOxiGlucometriaOrdMedica.lcMensajeError = 'Campos con datos no registrados si indica SI necesita oxigeno, revise por favor.';
				$("#selOrdOxiPacNececesita").focus();
				lbValido = false;
			}
			lcDescripcionMetodo = $("#selTipoMetodoOxigeno option[id-cups="+lcIdMetodo+"]").text();
			if (lcDescripcionMetodo==''){
				oOxiGlucometriaOrdMedica.lcMensajeError = 'Descripción Método oxigeno no corresponde, revise por favor.';
				$("#selOrdOxiPacNececesita").focus();
				lbValido = false;
			}	
		}	
		return lbValido;
	},	
	
	validacionGlucometria: function() {
		var lbValido = true;
		var lcNecesarioGlucometrias = $("#selNecesarioGlucometria").val();
		var lcMetodoGlucometrias = $("#selTipoMetodoGlucometria").val();
		var lcObservacionesGlucometrias = $("#txtObservacionesGlucometria").val();
		
		if (lcNecesarioGlucometrias==''){
			oOxiGlucometriaOrdMedica.lcMensajeError = 'Debe selecionar si el paciente se le deben tomar glucometrías.';
			$("#selNecesarioGlucometria").focus();
			lbValido = false;
		}
		
		if (lcNecesarioGlucometrias!='Si' && lcNecesarioGlucometrias!='No'){
			oOxiGlucometriaOrdMedica.lcMensajeError = 'Es necesario tomar glucometrías al paciente obligatorio, revise por favor.';
			$("#selNecesarioGlucometria").focus();
			lbValido = false;
		}
		if (lcNecesarioGlucometrias=='Si'){
			if (lcMetodoGlucometrias==''){
				oOxiGlucometriaOrdMedica.lcMensajeError = 'Método glucometría obligatorio, revise por favor.';
				$("#selTipoMetodoGlucometria").focus();
				lbValido = false;
			}
			
			if (lcObservacionesGlucometrias==''){
				oOxiGlucometriaOrdMedica.lcMensajeError = 'Observaciones glucometría obligatorio, revise por favor.';
				$("#txtObservacionesGlucometria").focus();
				lbValido = false;
			}
		}		
		return lbValido;
	},	
		
	obtieneEstadoFormulaOxigeno: function() {
		var lcEstadoFormula = '';
		var lcNecesitaPaciente = $('#selOrdOxiPacNececesita').val();
		if (oOxiGlucometriaOrdMedica.cSuspenderOxigeno!=''){
			lcEstadoFormula = '14';
		}else{
			if (lcNecesitaPaciente=='Si'){
				lcEstadoFormula = oOxiGlucometriaOrdMedica.lPrimeraFormula==true ? '11' : ((oOxiGlucometriaOrdMedica.nEstadoAntes=='11' || oOxiGlucometriaOrdMedica.nEstadoAntes=='13') ? '13' : '11');
			}else{
				lcEstadoFormula = oOxiGlucometriaOrdMedica.lPrimeraFormula==true ? '99' : ((oOxiGlucometriaOrdMedica.nEstadoAntes=='11' || oOxiGlucometriaOrdMedica.nEstadoAntes=='13') ? '14' : '99');
			}	
		}
		return lcEstadoFormula;
	},	

	obtieneEstadoGrabacion: function() {
		var ldFechaActual = new Date();
		var llCobrarConsumos = oOxiGlucometriaOrdMedica.lCobrar_Consumos;
		var lcEstadoGrabaAntes = oOxiGlucometriaOrdMedica.cEstadoGrabaAntes;
		
		var lcCupsOxigeno = $('#selTipoMetodoOxigeno').val();
		var lnFechaActual = ldFechaActual.getFullYear()+((ldFechaActual.getMonth() + 1).toString()).padStart(2, "0")+((ldFechaActual.getDate()).toString()).padStart(2, "0");
		var lcEstadoGrabacion = '';
		var lcNecesitaPaciente = $('#selOrdOxiPacNececesita').val();
		
		if (oOxiGlucometriaOrdMedica.cSuspenderOxigeno!=''){
			lcEstadoGrabacion = 'S';
		}else{
			if (lcNecesitaPaciente=='No'){
				lcEstadoGrabacion = 'N';
			}else{
				lcEstadoGrabacion = (lcCupsOxigeno==oOxiGlucometriaOrdMedica.cCodCupAntes && oOxiGlucometriaOrdMedica.nFechaAntes==lnFechaActual) ? 'M' : (llCobrarConsumos==true ? 'C' : 'U');
				lcEstadoGrabacion = llCobrarConsumos==false && lcEstadoGrabacion=='C' ? 'U' : (llCobrarConsumos==true && lcEstadoGrabaAntes=='U' && lcEstadoGrabacion=='M' ? 'C' : lcEstadoGrabacion);
			}	
		}
		return lcEstadoGrabacion;
	},
	
	obtenerDatosOxigeno: function() {
		var lcEstadoFormulaOxigeno = oOxiGlucometriaOrdMedica.obtieneEstadoFormulaOxigeno();
		var lcEstadoGrabacionOxigeno = oOxiGlucometriaOrdMedica.obtieneEstadoGrabacion();
		
		var laOxigeno = {
			'ordOxiPacNececesita': $('#selOrdOxiPacNececesita').val(),
			'idMetodoOxigeno': $("#selTipoMetodoOxigeno option:selected").attr("id-cups")===undefined ? '' : $("#selTipoMetodoOxigeno option:selected").attr("id-cups"),
			'tipoMetodoOxigeno': $('#selTipoMetodoOxigeno').val(),
			'dosisOxigeno': $('#txtDosisOxigeno').val(),
			'unidadDosis': $('#selOrdOxiPacNececesita').val()=='Si' ? '37':'',
			'observacionesOxigeno': $('#txtObservacionesOxigeno').val(),
			'estadoformula': lcEstadoFormulaOxigeno,
			'estadograbacion': lcEstadoGrabacionOxigeno,
			'suspende': oOxiGlucometriaOrdMedica.cSuspenderOxigeno=='S' ? 'S': 'N',
		}
		
		return laOxigeno;		
	},
	
	obtenerDatosGlucometria: function() {
		laGlucometria = oOxiGlucometriaOrdMedica.aProcedimientosGlucometria;
		return laGlucometria;		
	},	
	
}	