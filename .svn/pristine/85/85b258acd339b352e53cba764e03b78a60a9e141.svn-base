var oMedicamentosOrdMedica = {
	gotableMedicamentosOM : $('#tblMedicamentosOM'),
	gotableConciliacionOM : $('#tblConciliacionMed'),
	gcUrlAjax: 'vista-ordenes-medicas/ajax/ajax',
	lcTitulo : 'Medicamentos ordenes médicas',
	lcFormaError: '', lcObjetoError: '', lcMensajeError: '', gcControlAntibioticoMed:'', MedPosNopos:'', gcControlado:'', gcConcentracion:'',
	gcUnidad: '', gcPresentacion: '', gcDatosConciliacion:'', gcSolicitaFormato:'', gcViasJustificarInmediato:'', gcMedicamentoVia:'',
	gcSeccionesExcluidasInmediato:'', gcEstadosformular:'', gcEsUnirs:'', gcMarcaUnirs:'', gcTextoUnirs:'', gcRutaArchivoUnirs:'',
	gnCantidadTotalJustificacionMed:0, gnDiasMaximoAntibioticoMed:0, gnDiasUsadoAntibioticoMed:0, gnCantidadDiariaMezcla:0,
	gnCantidadTotalMezcla:0, gnFrecuenciaMinima:0, gnFrecuenciaMaxima:0, gnDiasParaAntibMax:0, gnCantidadMinControlados:0, gnCantidadMaxControlados:0,
	gcTipoJustificacionMed:'', gcTipoMezcla:'', gcDescripcionMezcla:'', gcPosNoposMed:'', gcGrupoFarmaceuticoMed:'', gcDescGrupoFarmaceuticoMed:'',
	gnDiasMinimoAntibioticoMed:1, gnConsMedId:1, gnCantidadMedicamentos:0, gnNumMedNoFor:0, gnSeleccionInmediato:0,
	gnCantidadMinInmediato:0, gnCantidadMaxInmediato: 0, gnNumFormulados:0, gnNumSuspendidos:0, gnNumNoFormulados:0, gnConfirmarNoFormulados:0,
	gnDiasAdicionalesAntibiotico:0, gnAntibValAntibiotico:0, gnDiasMaximoFrecuencia:0, gnColorViene:0,
	glActivarRangoAntibiotico:false, gcEsAntibioticoMed:false, glValidarValorAntibiotico:true, glMedicamentosTieneCambios:false,
	gllMostrarAlerta:false, glActualEsantibiotico:false, glActualFormulado:false,
	gaModificarMedicamento:[], gaDatoFila:[], gaDiagnosticosOrden:[], gcDiagnosticosPrincipal:'',
	gcTipoModificacionAntibiotico:'', gcActualCodUnidadDosis:'', gcActualCodigoFrecuencia:'',
	gcActualControlAlertaAntibiotico:'',
	gnActualCantUnidadDosis:0, gnActualCantFrecuencia:0,
	gaDatosmedicamento:[], gaEstadosnoformular:[], gaDatosModificar:[], gaDatosActualAntibiotico:[], gaDatosRegistrar:[],
	goColorFila:  {	'9': '#ffffff', '3': '#cce8fd', '8': '#fae3df', '5': '#fdfd96', },

	inicializar: function(){
		this.iniciarTablaMedicamentos();
		this.iniciarTablaConciliacion();
		this.consultarConciliacion(1);
		this.consultarFrecuencias();
		this.parametrosIniciales();
		this.consultarDiagnosticoPrincipal();
		this.habilitarCampos(true, true);
		this.activarCampos(true);
		this.habilitarAntibiotico(false,'');
		oMedicamentos.consultaMedicamentos('cMedicamentoOM','cCodigoMedicamentoOM','cDescripcionMedicamentoOM','txtDosisOM','OM');
		oDiagnosticos.consultarDiagnostico('txtCieOrdenMedica','cCodigoCieOrdenMedica','cDescripcionCieOrdenMedica','ORDENMEDICA','selOrdOxiPacNececesita');

		$('#FormmedicamentosOM').validate({
			rules: {
				cCodigoMedicamentoOM: "required",
				cDescripcionMedicamentoOM: "required",
				selTipoDosisOM: "required",
				selTipoFrecuenciaOM: "required",
				selTipoViaOM: "required",
			},
			errorElement: "div",
			errorPlacement: function ( error, element ) {
				error.addClass( "invalid-tooltip" );

				if (element.prop("type")==="radio") {
					error.insertAfter(element.parent("label"));
				} else {
					error.insertAfter(element);
				}
			},
			highlight: function (element, errorClass, validClass) {
				$(element).addClass("is-invalid").removeClass("is-valid");
			},
			unhighlight: function (element, errorClass, validClass) {
				$(element).addClass("is-valid").removeClass("is-invalid");
			}
		});
		$('#AdicionarMedicamentoOM').on('click', this.validarMedicamento);
		$('#btnGuardaSuspAntibiotico').on('click', oMedicamentosOrdMedica.guardarSuspenderAntibiotico);
		$('#btnCancelarSuspAntibiotico').on('click', oMedicamentosOrdMedica.cancelarSuspenderAntibiotico);
		$('#btnGuardaModifAntibiotico').on('click', oMedicamentosOrdMedica.guardarModificarAntibiotico);
		$('#btnCancelarModifAntibiotico').on('click', oMedicamentosOrdMedica.cancelarModificarAntibiotico);
		$('#txtDosisOM').on('change', oMedicamentosOrdMedica.validarParametroMezclaDosis);
		$('#txtDiasUsoAntibioticoOM').on('blur', oMedicamentosOrdMedica.validarDiasAntibiotico);
		$('#selTipoFrecuenciaOM,#txtFrecuenciaOM').on('change', oMedicamentosOrdMedica.validarFrecuencia);
		$('#btnMedSuspendidosOM').on('click', this.verMedicamentosSuspendidos);
		$('#btnConciliacionMed').on('click', this.verConciliacionMed);
	},

	verMedicamentosSuspendidos: function() {
		oMedicamentosSuspendidos.mostrar(0);
	},

	verConciliacionMed: function() {
		fnDialog($("#divModalConciliaMed").html(), "Conciliación Medicamentos", false, false, "xl");
	},

	consultarParametrosCie: function(tcLetraCie) {
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/diagnostico.php',
			data: {lcTipoDiagnostico: 'consultarValidaCieOrden', lcTipoCie: tcLetraCie},
			dataType: "json"
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					
					if (loTipos.TIPOS.mensaje==''){
						lcDiagnosticoPrincipal=oMedicamentosOrdMedica.gcDiagnosticosPrincipal;
						$('#cCodigoCieOrdenMedica').val(lcDiagnosticoPrincipal.split('~', 2)[0]);
						$('#cDescripcionCieOrdenMedica').val(lcDiagnosticoPrincipal.split('~', 2)[1]);
					}
					oMedicamentosOrdMedica.gaDiagnosticosOrden=loTipos.TIPOS.datoscie;
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de consultar parámetros CIE en ordenes médicas.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presenta un error al buscar de consultar parámetros CIE en ordenes médicas.');
		});
	},

	consultarDiagnosticoPrincipal: function() {
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/diagnostico.php',
			data: {lcTipoDiagnostico: 'consultaDiagnostico', lnNroIngreso: aDatosIngreso['nIngreso'], lcTipoCie: ''},
			dataType: "json"
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					if (loTipos.TIPOS.length > 0) {
						$.each(loTipos.TIPOS, function(lcKey, loSeleccion) {
							if (loSeleccion.TIPO=="1"){
								lcLetraDiagnostico=loSeleccion.DIAGNOSTICO.substring(0, 1);
								oMedicamentosOrdMedica.gcDiagnosticosPrincipal=loSeleccion.DIAGNOSTICO+'~'+loSeleccion.DESCRIPCION_CIE;
								oMedicamentosOrdMedica.consultarParametrosCie(lcLetraDiagnostico);
							}
						});
					}
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de consultar diagnósticos en ordenes médicas.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presenta un error al buscar de consultar diagnósticos en ordenes médicas.');
		});
	},
	
	validarFrecuencia: function()
	{
		let lcUnidadFrecuencia=$('#selTipoFrecuenciaOM').val();
		let lcFrecuenciaDataId=lcUnidadFrecuencia!='' ? $("#selTipoFrecuenciaOM option[value="+lcUnidadFrecuencia+"]").attr('data-unidad') : '';
		let lnFrecuencia=$('#txtFrecuenciaOM').val();

		if (lcUnidadFrecuencia!=''){
			if (lcUnidadFrecuencia!='1'){
				$('#txtFrecuenciaOM').val(lcFrecuenciaDataId);
			}
		}else{
			$('#selTipoFrecuenciaOM').removeClass("is-valid")
		}

		if (lnFrecuencia<0 || lnFrecuencia>oMedicamentosOrdMedica.gnDiasMaximoFrecuencia){
			let lcTextomensaje='El valor debe estar entre 0 y ' + oMedicamentosOrdMedica.gnDiasMaximoFrecuencia + ', revise por favor.';
			$('#txtFrecuenciaOM').val('');
			$('#txtFrecuenciaOM,#selTipoFrecuenciaOM').removeClass("is-valid");
			fnAlert(lcTextomensaje, 'Validación', false, false, 'large');
			return false;
		}
	},

	consultarUltimaFormula: function(tcEncontrar)
	{
		let llFormulado=true;
		let taTablaValidar = oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('getData');

		$.ajax({
			type: "POST",
			url: oMedicamentosOrdMedica.gcUrlAjax,
			data: {accion: 'ultimaFormulaMedicamentos', lnIngreso: aDatosIngreso['nIngreso']},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.TIPOS.length>0){
						$.each(toDatos.TIPOS, function(lcKey, loSeleccion) {
							lcCodigoMedicamento='';
							if (tcEncontrar!=''){
								lcCodigoMedicamento=loSeleccion.CODIGO;

								llverificaExiste = oMedicamentosOrdMedica.verificaCodigoExiste(lcCodigoMedicamento,taTablaValidar,'')
								if(llverificaExiste){
									oMedicamentosOrdMedica.registrarMedicamento(loSeleccion,llFormulado,0,tcEncontrar,'');
								}
							}else{
								oMedicamentosOrdMedica.registrarMedicamento(loSeleccion,llFormulado,0,'','');
							}
						});
					}else{
						oMedicamentosOrdMedica.formulaConciliacion();
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la busqueda para consultar Ultima Formula ordenes médicas.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar consultar Ultima Formula ordenes médicas.");
		});
	},

	formulaConciliacion: function(){
		let laDatosConcliacion=oMedicamentosOrdMedica.gcDatosConciliacion;
		let llFormulado='';
		let taTablaValidar = oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('getData');

		$.each(laDatosConcliacion, function(lcKey, loSeleccion) {
			llverificaExiste = oMedicamentosOrdMedica.verificaCodigoExiste(loSeleccion.CODIGO,taTablaValidar,'')
			if(llverificaExiste){
				lcCodigoFrecuencia=lcDescripcionFrecuencia=lcVerificarFrecuencia='';
				if (loSeleccion.CONTINUA!='Suspende'){
					if (loSeleccion.CODIGO.substr(0,2)!='NC'){
						lcVerificarFrecuencia = $("#selTipoFrecuenciaOM option[value="+loSeleccion.TIPOCODF+"]").text()
						lcCodigoFrecuencia=lcVerificarFrecuencia!=''?loSeleccion.TIPOCODF:'';
						lcDescripcionFrecuencia=lcVerificarFrecuencia!=''?loSeleccion.TIPOF:'';

						laDatosMedicamento = {CODIGO: loSeleccion.CODIGO, DESCRIPCION: loSeleccion.MEDICA, OBSERVACIONES: loSeleccion.OBSERVA,
										DOSIS: loSeleccion.DOSIS, CODUNIDADDOSIS: loSeleccion.TIPODCOD, DESCRUNIDADDOSIS: loSeleccion.TIPOD,
										FRECUENCIA: loSeleccion.FRECUENCIA,
										CODUNIDADFRECUENCIA: lcCodigoFrecuencia, DESCRUNIDADFRECUENCIA: lcDescripcionFrecuencia,
										VIA: loSeleccion.VIACOD, DESCRVIA: loSeleccion.VIA, DIASINGRESAANTIBIOTICO: '', ESTADO:99,
										ESTADO_MEDICAMENTO: loSeleccion.ESTADO, POSNOPOS: loSeleccion.POSNOPOS,
										CONTROLADO: loSeleccion.CONTROLADO, ESANTIBIOTICO: loSeleccion.ESANTIBIOTICO,
										CONTROLALERTAANTIB: loSeleccion.CONTROLALERTAANTIB, DIASMAXANTIBIOTICO: loSeleccion.DIASMAXANTIBIOTICO,
										GRUPOCODFARMACEUTICO: loSeleccion.GRUPOCODFARMACEUTICO, DESCRGRUPOCODFARMACEUTICO: loSeleccion.DESCRGRUPOCODFARMACEUTICO,
										LISTADOUNIRS: loSeleccion.LISTADOUNIRS,
										};
						oMedicamentosOrdMedica.registrarMedicamento(laDatosMedicamento,llFormulado,0,'','C');
					}
				}
			}
		});
	},

	consultarConciliacion: function(tnTipoConsulta)
	{
		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/Conciliacion.php",
			data: {TipoConsulta: tnTipoConsulta, TipoDoc: aDatosIngreso['cTipId'], NroDoc: aDatosIngreso['nNumId'], ingreso: aDatosIngreso['nIngreso']},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.DATOS != []) {
						oMedicamentosOrdMedica.gcDatosConciliacion=toDatos.DATOS.Medicamentos;
						oMedicamentosOrdMedica.cargarConciliacion(toDatos.DATOS.Medicamentos);
					}
					oMedicamentosOrdMedica.consultarUltimaFormula('');
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda para conciliacion ordenes médicas.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar conciliacion ordenes médicas.");
		});
	},

	cargarConciliacion: function(taDatos)
	{
		$.each(taDatos, function(lcKey, loSeleccion) {
			var rows = []
			rows.push({
				MEDICAMENTOC: loSeleccion.MEDICA,
				DOSISC: loSeleccion.DOSIS + '-' + loSeleccion.TIPOD,
				VIAADMINC: loSeleccion.VIA,
				FRECUENCIAC: loSeleccion.FRECUENCIA + '-' + loSeleccion.TIPOF,
				CONTINUAC: loSeleccion.CONTINUA,
				OBSERVACIONESC: loSeleccion.OBSERVA,
			})
			oMedicamentosOrdMedica.gotableConciliacionOM.bootstrapTable('append', rows);
		});
	},

	parametrosIniciales: function()
	{
		$.ajax({
			type: "POST",
			url: oMedicamentosOrdMedica.gcUrlAjax,
			data: {accion: 'parametrosIngreso'},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					oMedicamentosOrdMedica.gnFrecuenciaMinima=toDatos.TIPOS.frecuenciaminima;
					oMedicamentosOrdMedica.gnFrecuenciaMaxima=toDatos.TIPOS.frecuenciamaxima;
					oMedicamentosOrdMedica.glActivarRangoAntibiotico=toDatos.TIPOS.activarrangoantibiotico;
					oMedicamentosOrdMedica.gnDiasParaAntibMax=toDatos.TIPOS.diasparaantibmax;
					oMedicamentosOrdMedica.gnCantidadMinControlados=toDatos.TIPOS.cantidadmincontrolados;
					oMedicamentosOrdMedica.gnCantidadMaxControlados=toDatos.TIPOS.cantidadmaxcontrolados;
					oMedicamentosOrdMedica.gcSolicitaFormato=toDatos.TIPOS.solicitaformato;
					oMedicamentosOrdMedica.gnCantidadMinInmediato=toDatos.TIPOS.cantidadmininmediato;
					oMedicamentosOrdMedica.gnCantidadMaxInmediato=toDatos.TIPOS.cantidadmaxinmediato;
					oMedicamentosOrdMedica.gcViasJustificarInmediato=toDatos.TIPOS.viasjustificarinmediato;
					oMedicamentosOrdMedica.gcSeccionesExcluidasInmediato=toDatos.TIPOS.seccionesexcluidasinmediato;
					oMedicamentosOrdMedica.gaEstadosnoformular=toDatos.TIPOS.estadosnoformular;
					oMedicamentosOrdMedica.gcEstadosformular=toDatos.TIPOS.estadosformular;
					oMedicamentosOrdMedica.gnConfirmarNoFormulados=toDatos.TIPOS.confirmarnoformulados;
					oMedicamentosOrdMedica.gnDiasMaximoFrecuencia=toDatos.TIPOS.diasmaximosfrecuencia;
					oMedicamentosOrdMedica.gcTextoUnirs=toDatos.TIPOS.textounirs;
					oMedicamentosOrdMedica.gcRutaArchivoUnirs=toDatos.TIPOS.rutaarchivoounirs;

				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta parametros medicamentos.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta parametros medicamentos.");
		});
	},

	validarMedicamento: function(e){
		e.preventDefault();

		if ($('#FormmedicamentosOM').valid()){
			oMedicamentosOrdMedica.gaDatosmedicamento=[];
			let lcCodigoMedicamento=$("#cCodigoMedicamentoOM").val();
			let lcDescMedicamento=$("#cDescripcionMedicamentoOM").val();
			let lnDosis=$("#txtDosisOM").val();
			let lcDosisUnidad=$("#selTipoDosisOM").val();
			let lcDescripcionDosisUnidad=lcDosisUnidad!='' ? $("#selTipoDosisOM option[value="+lcDosisUnidad+"]").text() : '';
			let lnFrecuencia=$("#txtFrecuenciaOM").val();
			let lcFrecuencia=$("#selTipoFrecuenciaOM").val();
			let lcDescripcionFrecuenciaUnidad=lcFrecuencia!='' ? $("#selTipoFrecuenciaOM option[value="+lcFrecuencia+"]").text() : '';
			let lcViaAdministracion=$("#selTipoViaOM").val();
			let lcDescripcionViaAdministracion=lcViaAdministracion!='' ? $("#selTipoViaOM option[value="+lcViaAdministracion+"]").text() : '';
			let lcObservaciones=$("#edtObservacionesOM").val();
			let lnDiasAntibiotico=$("#txtDiasUsoAntibioticoOM").val();

			if (lcCodigoMedicamento==''){
				fnAlert('Medicamento obligatorio, revise por favor.', 'Valida medicamento', false, false, false);
				$('#cMedicamentoOM').focus();
				return false;
			}

			if (lcDescMedicamento==''){
				fnAlert('Descripción medicamento no valido, revise por favor.', 'Valida medicamento', false, false, false);
				$('#cMedicamentoOM').focus();
				return false;
			}

			if (lnDosis=='' || lnDosis<=0){
				fnAlert('Dosis obligatoria, revise por favor.', 'Valida dosis', false, false, false);
				$('#txtDosisOM').focus();
				return false;
			}

			if (oMedicamentosOrdMedica.gcTipoJustificacionMed=='JM' && oMedicamentosOrdMedica.gcTipoMezcla=='E'){
				fnAlert('Dosis de la mezcla fuera de rango, revise por favor.', 'Valida dosis', false, false, false);
				$('#cMedicamentoOM').focus();
				return false;
			}

			if (lcDosisUnidad==''){
				fnAlert('Dosis unidad obligatoria, revise por favor.', 'Valida dosis unidad', false, false, false);
				$('#selTipoDosisOM').focus();
				return false;
			}

			if (lnFrecuencia=='' || parseInt(lnFrecuencia)<=0){
				fnAlert('Frecuencia obligatoria, revise por favor.', 'Valida frecuencia', false, false, false);
				$('#txtFrecuenciaOM').focus();
				return false;
			}else{
				if (parseInt(lnFrecuencia)>oMedicamentosOrdMedica.gnFrecuenciaMaxima){
					$('#txtFrecuenciaOM').val('');
					lcMensaje='Frecuencia no valida, valor máximo ' + oMedicamentosOrdMedica.gnFrecuenciaMaxima + ', revise por favor.';
					fnAlert(lcMensaje, 'Valida Frecuencia', false, false, false);
					$('#txtFrecuenciaOM').focus();
					return false;
				}
			}

			if (lcFrecuencia==''){
				fnAlert('Frecuencia unidad Obligatoria, revise por favor.', 'Valida frecuencia unidad', false, false, false);
				$('#selTipoFrecuenciaOM').focus();
				return false;
			}else{
				if (lcFrecuencia=='1'){
					if (parseInt(lnFrecuencia) < oMedicamentosOrdMedica.gnFrecuenciaMinima || parseInt(lnFrecuencia) > oMedicamentosOrdMedica.gnFrecuenciaMaxima) {
						$('#txtFrecuenciaOM').val('');
						lcMensaje='Frecuencia no valida, debe estar entre ' + oMedicamentosOrdMedica.gnFrecuenciaMinima + ' y ' + oMedicamentosOrdMedica.gnFrecuenciaMaxima + ', revise por favor.';
						fnAlert(lcMensaje, 'Valida Frecuencia', false, false, false);
						$('#txtFrecuenciaOM').focus();
						return false;
					}
				}
			}

			if (lcViaAdministracion==''){
				fnAlert('Vía administración obligatoria, revise por favor.', 'Valida vía administración', false, false, false);
				$('#selTipoViaOM').focus();
				return false;
			}

			if (oMedicamentosOrdMedica.gcEsAntibioticoMed){
				oMedicamentosOrdMedica.validarDiasAntibiotico();
				if (!oMedicamentosOrdMedica.glValidarValorAntibiotico){
					return false;
				}
			}

			var laDatosMedicamento = {CODIGO: lcCodigoMedicamento, DESCRIPCION: lcDescMedicamento, OBSERVACIONES: lcObservaciones,
									DOSIS: lnDosis, CODUNIDADDOSIS: lcDosisUnidad, DESCRUNIDADDOSIS: lcDescripcionDosisUnidad,
									FRECUENCIA: lnFrecuencia, CODUNIDADFRECUENCIA: lcFrecuencia, DESCRUNIDADFRECUENCIA: lcDescripcionFrecuenciaUnidad,
									VIA: lcViaAdministracion, DESCRVIA: lcDescripcionViaAdministracion, DIASINGRESAANTIBIOTICO: lnDiasAntibiotico,
									CONTROLADO: oMedicamentosOrdMedica.gcControlado };
			oMedicamentosOrdMedica.gaDatosmedicamento=laDatosMedicamento;
			oMedicamentosOrdMedica.validaAntibiotico(lcCodigoMedicamento,laDatosMedicamento);
		}
	},

	validaAntibiotico: function(tcCodigoMed,taDatosmedicamento) {
		let lnDiasAntibiotico=taDatosmedicamento.DIASINGRESAANTIBIOTICO;
		let lnDiasUsadoAntibiotico=oMedicamentosOrdMedica.gnDiasUsadoAntibioticoMed;
		oMedicamentosOrdMedica.gnDiasAdicionalesAntibiotico=oMedicamentosOrdMedica.gnAntibValAntibiotico=0;

		if (lnDiasAntibiotico>0 && (lnDiasUsadoAntibiotico>lnDiasAntibiotico)){
			let lcTextoAntibiotico='Se esta excediendo los días de tratamiento para el ANTIBIOTICO <br>' + taDatosmedicamento.DESCRIPCION + '<br> ¿QUIERE FORMULARLO NUEVAMENTE?';
				fnConfirm(lcTextoAntibiotico, false, false, false, 'large',
					{ text: 'Aceptar',
							action: function(){

								let lcTextoDiasAdicionales = [
									'<div class="container-fluid small">',
										'<div class="row">',
											'<div class="col-12"><h6>Indicar días adicionales de uso para Antibiótico:</h6></div>',
											'<div class="col-12"><b>'+taDatosmedicamento.DESCRIPCION+'</b><br><br></div>',
										'</div><br>',
										'<div class="row">',
											'<div class="col-4">',
												'<label id="lblDiasAdicionalesAntibiotico" for="txtDiasAdicionalesAntibiotico" class="required">Días adicionales de uso:</label>',
											'</div>',
											'<div class="col-4">',
												'<input name="DiasAdicionalesAntibiotico" type="number" id="txtDiasAdicionalesAntibiotico" class="form-control mr-sm-2">',
											'</div>',
										'</div><br>',

									'</div>',
								].join('');

								fnConfirm(lcTextoDiasAdicionales, 'Días adicionales de uso - Antibiotico', false, false, 'large',
									{ text: 'Aceptar',
										action: function(){
											oMedicamentosOrdMedica.gnDiasAdicionalesAntibiotico=$("#txtDiasAdicionalesAntibiotico").val();
											if (oMedicamentosOrdMedica.gnDiasAdicionalesAntibiotico<=0 || oMedicamentosOrdMedica.gnDiasAdicionalesAntibiotico==''){
												$('#txtDiasAdicionalesAntibiotico').focus();
												fnAlert('Días adicionales de uso obligatorio, revise por favor.', 'Valida días adicionales de uso', false, false, 'medium');
												return false;
											}else{
												oMedicamentosOrdMedica.gnAntibValAntibiotico=2;
												oMedicamentosOrdMedica.adicionarMedicamento(tcCodigoMed,taDatosmedicamento);
											}
										}
									},
									{  text: 'Cancelar',
										action: function(){
											$('#tblMedicamentosOM').bootstrapTable('remove', {
												field: 'CODIGO',
												values: [tcCodigoMed]
											});
										}
									}
								);
							}
					},
					{  text: 'Cancelar',
						action: function(){
							$('#tblMedicamentosOM').bootstrapTable('remove', {
								field: 'CODIGO',
								values: [tcCodigoMed]
							});
						}
					}
				);

		}else{
			if (taDatosmedicamento.ESANTIBIOTICO && lnDiasAntibiotico==0){
				let lcTextoMensaje= "Deben indicarse los días de uso del Antibiótico <br>" + taDatosmedicamento.DESCRIPCION;
				fnAlert(lcTextoMensaje);
			}else{
				oMedicamentosOrdMedica.gnAntibValAntibiotico=1;
				oMedicamentosOrdMedica.adicionarMedicamento(tcCodigoMed,taDatosmedicamento);
			}
		}
	},

	validarDiasAntibiotico: function(){
		oMedicamentosOrdMedica.glValidarValorAntibiotico=true;
		let lnDiasAntibiotico=$("#txtDiasUsoAntibioticoOM").val();
		let lnDiasMinimoAntibiotico=oMedicamentosOrdMedica.gnDiasMinimoAntibioticoMed;
		let lnDiasMaximoAntibiotico=oMedicamentosOrdMedica.glActivarRangoAntibiotico==true ? oMedicamentosOrdMedica.gnDiasMaximoAntibioticoMed : oMedicamentosOrdMedica.gnDiasParaAntibMax;
		let lcDescMedicamento=$("#cDescripcionMedicamentoOM").val();

		if (lnDiasAntibiotico=='' || parseInt(lnDiasAntibiotico)<=0){
			fnAlert('Debe indicar el número de días que se usará el antibiótico <br>' + lcDescMedicamento, 'Valida Antibiótico', false, false, 'medium');
			$("#txtDiasUsoAntibioticoOM").removeClass("is-valid").addClass("is-invalid");
			oMedicamentosOrdMedica.glValidarValorAntibiotico=false;
		}

		if (lnDiasAntibiotico>lnDiasMaximoAntibiotico){
			lcTexto=lnDiasMaximoAntibiotico + ' días son el máximo indicado para el uso del antibiótico <br>' + lcDescMedicamento;
			fnAlert(lcTexto, 'Valida Antibiótico', false, false, 'medium');
			$('#txtDiasUsoAntibioticoOM').val('');
			$("#txtDiasUsoAntibioticoOM").removeClass("is-valid").addClass("is-invalid");
			oMedicamentosOrdMedica.glValidarValorAntibiotico=false;
		}
	},

	seleccionaMedicamento: function(taItem){
		oMedicamentosOrdMedica.MedPosNopos=taItem.POSNOPOS;
		oMedicamentosOrdMedica.gcControlado=taItem.CONTROLADO;
		oMedicamentosOrdMedica.gcConcentracion=taItem.CONCENTRACION;
		oMedicamentosOrdMedica.gcUnidad=taItem.UNIDAD;
		oMedicamentosOrdMedica.gcPresentacion=taItem.PRESENTACION;
		oMedicamentosOrdMedica.gcMedicamentoVia=taItem.FORMULAPORVIA;
		oMedicamentosOrdMedica.habilitarAntibiotico(false,'');
		oMedicamentosOrdMedica.activarCampos(true);
		oMedicamentosOrdMedica.habilitarCampos(true,false);
		oMedicamentosOrdMedica.consultaViaAdministracion(taItem.CODIGO,taItem.DESCRIPCION,true,'');
		oMedicamentosOrdMedica.parametrosMedicamento(taItem.CODIGO);
		oMedicamentosOrdMedica.consultaDosis(taItem.CODIGO,'');
		oMedicamentosOrdMedica.gcEsUnirs=taItem.UNIRS;
		$('#txtDosisOM').focus();
	},

	adicionarMedicamento: function(tcCodigo,tcDatos){
		oMedicamentosOrdMedica.gaModificarMedicamento=tcDatos;
		oMedicamentosOrdMedica.gcMarcaUnirs='';
		oMedicamentosOrdMedica.gcEsUnirs=oMedicamentosOrdMedica.gcEsUnirs!=''?oMedicamentosOrdMedica.gcEsUnirs:tcDatos.MARCAUNIRS;

		let lnCantidadDosis=tcDatos.DOSIS;
		let lcCodigoDosis=tcDatos.CODUNIDADDOSIS;
		let lnCantidadFrecuencia=tcDatos.FRECUENCIA;
		let lcCodigoFrecuencia=tcDatos.CODUNIDADFRECUENCIA;
		let llFormulado='';
		let taTablaValidar = oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('getData');
		let llverificaExiste = oMedicamentosOrdMedica.verificaCodigoExiste(tcCodigo,taTablaValidar,tcDatos.VIA);

		if(llverificaExiste){
			//	NO EXISTE
			if ((oMedicamentosOrdMedica.gcControlado!='' && oModalMedicamentoControlado.gcValidarControlado=='')
				|| oMedicamentosOrdMedica.gcEsAntibioticoMed){

				if (oMedicamentosOrdMedica.gcControlado!=''){
					oModalMedicamentoControlado.mostrar(tcDatos,'A',0,0,'');
				}else{
					if (oMedicamentosOrdMedica.gcControlAntibioticoMed=='S'){
						if (parseInt(aDatosIngreso['aEdad']['y']) > oProcedimientosOrdMedica.gnEdadMenor){
							oMedicamentosOrdMedica.alertaControlAntibiotico(tcDatos);
						}else{
							oMedicamentosOrdMedica.preguntarUnirs(tcDatos,llFormulado,1,'','','R');
						}
					}else{
						oMedicamentosOrdMedica.preguntarUnirs(tcDatos,llFormulado,1,'','','R');
					}
				}
			}else{
				oMedicamentosOrdMedica.preguntarUnirs(tcDatos,llFormulado,1,'','','R');
			}
		}else{
			fnConfirm('¿Medicamento ya ingresado, desea modificarlo?', oMedicamentosOrdMedica.lcTitulo, false, false, 'medium',
			{
				text: 'Si',
					action: function(){
					if (oMedicamentosOrdMedica.glActualFormulado){
						if (oMedicamentosOrdMedica.glActualEsantibiotico){
							if ((oMedicamentosOrdMedica.gnActualCantUnidadDosis!=lnCantidadDosis) || (oMedicamentosOrdMedica.gcActualCodUnidadDosis!=lcCodigoDosis) || (oMedicamentosOrdMedica.gnActualCantFrecuencia!=lnCantidadFrecuencia) || (oMedicamentosOrdMedica.gcActualCodigoFrecuencia!=lcCodigoFrecuencia)){
								oMedicamentosOrdMedica.gaModificarMedicamento=tcDatos;
								oMedicamentosOrdMedica.modificarAntibiotico();
							}else{
								if (oMedicamentosOrdMedica.gcControlAntibioticoMed=='S'){
									oMedicamentosOrdMedica.alertaControlAntibiotico(tcDatos);
								}else{
									oMedicamentosOrdMedica.preguntarUnirs(tcDatos,llFormulado,1,'','','M');
								}
							}
						}else{
							if (oMedicamentosOrdMedica.gcControlado!=''){
								if (oModalMedicamentoControlado.gaDatosControlado.cCodigoCieControlado=='' || oModalMedicamentoControlado.gaDatosControlado.cCodigoCieControlado==undefined){
									oModalMedicamentoControlado.mostrar(tcDatos,'M',0,0,'');
								}else{
									oMedicamentosOrdMedica.preguntarUnirs(tcDatos,llFormulado,1,'','','M');
								}
							}else{
								oMedicamentosOrdMedica.preguntarUnirs(tcDatos,llFormulado,1,'','','M');
							}
						}
					}else{
						if (oMedicamentosOrdMedica.gcControlAntibioticoMed=='S'){
							oMedicamentosOrdMedica.alertaControlAntibiotico(tcDatos);
						}else{
							oMedicamentosOrdMedica.preguntarUnirs(tcDatos,llFormulado,1,'','','M');
						}
					}
				}
			},
			{
				text: 'No',
				action: function(){
					oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('updateRow', {
						index: oMedicamentosOrdMedica.indexedit,
						row: {
							SEFORMULA: 0,
							INMEDIATO: 0,
							COLOR: oMedicamentosOrdMedica.gnColorViene,
						}
					});
				}
			}
			);
		}
	},

	preguntarUnirs: function(taDatos,tlFormulado,tnSeFormula,tcEstadoNuevo,tcConciliacion,tcTipoRegistro) {
		if (oMedicamentosOrdMedica.gcEsUnirs=='' || oMedicamentosOrdMedica.gcEsUnirs===undefined){
			if (tcTipoRegistro==='R'){
				oMedicamentosOrdMedica.registrarMedicamento(taDatos,tlFormulado,tnSeFormula,tcEstadoNuevo,tcConciliacion);
			}else{
				oMedicamentosOrdMedica.modificarMedicamento(taDatos);
			}
		}else{
			lcMensajeUnirs=oMedicamentosOrdMedica.gcTextoUnirs.replaceAll('~','<br>');
			lcTituloUnirs=taDatos.DESCRIPCION;
			fnConfirm(lcMensajeUnirs,lcTituloUnirs, false, false, 'large', false, false,
				{buttons: {
					Si: {
						action: function(){
							oMedicamentosOrdMedica.gcMarcaUnirs='S';

							if (tcTipoRegistro==='R'){
								oMedicamentosOrdMedica.registrarMedicamento(taDatos,tlFormulado,tnSeFormula,tcEstadoNuevo,tcConciliacion);
							}else{
								oMedicamentosOrdMedica.modificarMedicamento(taDatos);
							}
						}
					},
					No: {
						action: function(){
							oMedicamentosOrdMedica.gcMarcaUnirs='N';

							if (tcTipoRegistro==='R'){
								oMedicamentosOrdMedica.registrarMedicamento(taDatos,tlFormulado,tnSeFormula,tcEstadoNuevo,tcConciliacion);
							}else{
								oMedicamentosOrdMedica.modificarMedicamento(taDatos);
							}
						}
					},
					Listado_Unirs: {
						action: function(){
							window.open(oMedicamentosOrdMedica.gcRutaArchivoUnirs,"_blank");
							return false;
						}
					}}
				}
			);
		}
	},

	alertaControlAntibiotico: function(taDatosMedicamento) {
		let lcCodigoMedicamento=taDatosMedicamento.CODIGO;
		let lnDiasUsoAntibiotico=parseInt(taDatosMedicamento.DIASINGRESAANTIBIOTICO);
		let lnEdadAñosPaciente=parseInt(aDatosIngreso['aEdad']['y']);
		let lnEdaMenores=oProcedimientosOrdMedica.gnEdadMenor

		if (lnDiasUsoAntibiotico>0 && !oMedicamentosOrdMedica.gllMostrarAlerta && oMedicamentosOrdMedica.gcSolicitaFormato=='1' && (lnEdadAñosPaciente > lnEdaMenores)){
			oMedicamentosOrdMedica.verificarUsoAntibiotico(lcCodigoMedicamento,lnDiasUsoAntibiotico,taDatosMedicamento);
		}else{
			oMedicamentosOrdMedica.preguntarUnirs(oMedicamentosOrdMedica.gaModificarMedicamento,'',1,'','','M');
		}
	},

	verificarUsoAntibiotico: function(tcCodigoMed,tnDiasAntibiotico,tcDatos) {
		var llFormulado='';
		$.ajax({
			type: "POST",
			url: oMedicamentosOrdMedica.gcUrlAjax,
			data: {accion: 'verificausoantibiotico', lnIngreso: aDatosIngreso['nIngreso'], lcMedicamento: tcCodigoMed, lnDiasAntibiotico: tnDiasAntibiotico},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.TIPOS==false){
						if (oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.DIAGNOSTICOINFECCIOSO===undefined){
							oModalJustificacionUsoAntibiotico.mostrar();
						}else{
							lcTextoSeFormula='¿Ya existe formato Antibiótico para esta formulación. Desea generar uno nuevo?';
							fnConfirm(lcTextoSeFormula,"Anbiótico de uso", false, false, false, false, false,
								{buttons: {
									Si: {
										action: function(){
											oModalJustificacionUsoAntibiotico.mostrar();
										}
									},
									No: {
										action: function(){
											let taTablaValidar = oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('getData');
											let llverificaExiste = oMedicamentosOrdMedica.verificaCodigoExiste(tcCodigoMed,taTablaValidar,'');
											if(llverificaExiste){
												oMedicamentosOrdMedica.preguntarUnirs(tcDatos,llFormulado,1,'','','R');
											}else{
												oMedicamentosOrdMedica.preguntarUnirs(tcDatos,llFormulado,1,'','','M');
											}
										}
									},
									Cancelar: {
									}}
								}
							);
						}
					}else{
						oMedicamentosOrdMedica.gaDatosActualAntibiotico=toDatos.TIPOS;
						let taTablaValidar = oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('getData');
						let llverificaExiste = oMedicamentosOrdMedica.verificaCodigoExiste(tcCodigoMed,taTablaValidar,'');
						if(llverificaExiste){
							oMedicamentosOrdMedica.preguntarUnirs(tcDatos,llFormulado,1,'','','R');
						}else{
							oMedicamentosOrdMedica.preguntarUnirs(tcDatos,llFormulado,1,'','','M');
						}
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta dosis medicamento orden médica.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta dosis medicamento orden médica.");
		});
	},

	verificaCodigoExiste: function(tcCodigo,taTablaMedicamento,tcViaAdministracion) {
		oMedicamentosOrdMedica.glActualEsantibiotico=oMedicamentosOrdMedica.glActualFormulado=false;
		oMedicamentosOrdMedica.gcActualCodUnidadDosis=oMedicamentosOrdMedica.gcActualCodigoFrecuencia=oMedicamentosOrdMedica.gcActualControlAlertaAntibiotico='';
		oMedicamentosOrdMedica.gnActualCantUnidadDosis=oMedicamentosOrdMedica.gnActualCantFrecuencia=oMedicamentosOrdMedica.gnColorViene=0;

		let llRetorno = true ;
		if(taTablaMedicamento != ''){
			if (oMedicamentosOrdMedica.gcMedicamentoVia!=''){
				$.each(taTablaMedicamento, function( lcKey, loTipo ) {
					if(loTipo['CODIGO']==tcCodigo && loTipo['VIA']==tcViaAdministracion){
						oMedicamentosOrdMedica.glActualEsantibiotico=loTipo['ESANTIBIOTICO'];
						oMedicamentosOrdMedica.glActualFormulado=loTipo['FORMULADO'];
						oMedicamentosOrdMedica.gnActualCantUnidadDosis=loTipo['DOSIS'];
						oMedicamentosOrdMedica.gcActualCodUnidadDosis=loTipo['CODUNIDADDOSIS'];
						oMedicamentosOrdMedica.gnActualCantFrecuencia=loTipo['FRECUENCIA'];
						oMedicamentosOrdMedica.gcActualCodigoFrecuencia=loTipo['CODUNIDADFRECUENCIA'];
						oMedicamentosOrdMedica.gcActualControlAlertaAntibiotico=loTipo['CONTROLALERTAANTIB'];
						oMedicamentosOrdMedica.gnColorViene=loTipo['COLORORG'];
						oMedicamentosOrdMedica.indexedit = lcKey;
						llRetorno = false;
					}
				});
			}else{
				$.each(taTablaMedicamento, function( lcKey, loTipo ) {
					if(loTipo['CODIGO']==tcCodigo){
						oMedicamentosOrdMedica.glActualEsantibiotico=loTipo['ESANTIBIOTICO'];
						oMedicamentosOrdMedica.glActualFormulado=loTipo['FORMULADO'];
						oMedicamentosOrdMedica.gnActualCantUnidadDosis=loTipo['DOSIS'];
						oMedicamentosOrdMedica.gcActualCodUnidadDosis=loTipo['CODUNIDADDOSIS'];
						oMedicamentosOrdMedica.gnActualCantFrecuencia=loTipo['FRECUENCIA'];
						oMedicamentosOrdMedica.gcActualCodigoFrecuencia=loTipo['CODUNIDADFRECUENCIA'];
						oMedicamentosOrdMedica.gcActualControlAlertaAntibiotico=loTipo['CONTROLALERTAANTIB'];
						oMedicamentosOrdMedica.gnColorViene=loTipo['COLORORG'];
						oMedicamentosOrdMedica.indexedit = lcKey;
						llRetorno = false;
					}
				});
			}
		};		
		
		return llRetorno ;
	},

	editarMedicamentoOM: function(taFila) {
		oModalMedicamentoControlado.gaDatosControlado.CantidadControlado=oModalMedicamentoControlado.gaDatosControlado.cCodigoCieControlado=oModalMedicamentoControlado.gaDatosControlado.ObservacionesControlado='';

		oMedicamentosOrdMedica.gnDiasMaximoAntibioticoMed=0;
		$("#txtDiasUsoAntibioticoOM").attr("disabled",false);
		oMedicamentosOrdMedica.gaDatosModificar=taFila;
		oMedicamentosOrdMedica.consultaDosis(taFila.CODIGO,taFila.CODUNIDADDOSIS);
		oMedicamentosOrdMedica.gcControlado=taFila.CONTROLADO;
		if (oMedicamentosOrdMedica.gcControlado!=''){
			oMedicamentosOrdMedica.consultadatoscontrolado(taFila);
		}
		oMedicamentosOrdMedica.gcControlAntibioticoMed=taFila.CONTROLALERTAANTIB;
		oMedicamentosOrdMedica.gcEsAntibioticoMed=taFila.ESANTIBIOTICO;
		oMedicamentosOrdMedica.consultaViaAdministracion(taFila.CODIGO,'',false,taFila.VIA);
		$("#cCodigoMedicamentoOM").val(taFila.CODIGO);
		$("#cDescripcionMedicamentoOM").val(taFila.MEDICAMENTO);
		$("#txtDosisOM").val(taFila.DOSIS);
		$("#txtFrecuenciaOM").val(taFila.FRECUENCIA);
		$("#selTipoFrecuenciaOM").val(taFila.CODUNIDADFRECUENCIA);
		$("#edtObservacionesOM").val(taFila.OBSERVACIONES);
		oMedicamentosOrdMedica.gnDiasMaximoAntibioticoMed=taFila.DIASMAXANTIBIOTICO;
		oMedicamentosOrdMedica.gnDiasUsadoAntibioticoMed=taFila.SELEC1;
		oMedicamentosOrdMedica.gcEsUnirs=taFila.MARCAUNIRS;
		oMedicamentosOrdMedica.gcMedicamentoVia=taFila.FORMULAPORVIA;

		if (taFila.ESANTIBIOTICO){
			oMedicamentosOrdMedica.habilitarAntibiotico(true,'');
			$("#txtDiasUsoAntibioticoOM").val(taFila.DUSOANTIBIOTICO);

			if (taFila.DUSOANTIBIOTICO>0){
				$("#txtDiasUsoAntibioticoOM").attr("disabled",true);
			}
		}else{
			oMedicamentosOrdMedica.habilitarAntibiotico(false,'');
		}
		oMedicamentosOrdMedica.activarCampos(false);
		$("#txtDosisOM,#selTipoDosisOM,#txtFrecuenciaOM,#selTipoFrecuenciaOM,#selTipoViaOM").addClass("is-valid");
		if (taFila.OBSERVACIONES!=''){ $("#edtObservacionesOM").addClass("is-valid"); }

		oMedicamentos.alertaInr(taFila.CODIGO,taFila.MEDICAMENTO);
	},

	modificarMedicamento: function(taDatos) {
		let lcControladoObservaciones=lcControladoDiagnostico='';
		let lnControladoCantidad=0;
		let lnEstado=0;
		let lnEstadoActual=parseInt(oMedicamentosOrdMedica.gaDatosModificar.ESTDET);
		let lnDosisActual=oMedicamentosOrdMedica.gaDatosModificar.DOSIS;
		let lcCodigoDosisActual=oMedicamentosOrdMedica.gaDatosModificar.CODUNIDADDOSIS;
		let lnFrecuenciaActual=oMedicamentosOrdMedica.gaDatosModificar.FRECUENCIA;
		let lcCodigoFrecuenciaActual=oMedicamentosOrdMedica.gaDatosModificar.CODUNIDADFRECUENCIA;
		let lnDosisNueva=taDatos.DOSIS===undefined?'': taDatos.DOSIS;
		let lnCodigoDosisNueva=taDatos.CODUNIDADDOSIS===undefined?'': taDatos.CODUNIDADDOSIS;
		let lnFrecuenciaNueva=taDatos.FRECUENCIA===undefined?'': parseInt(taDatos.FRECUENCIA);
		let lnCodigoFrecuenciaNueva=taDatos.CODUNIDADFRECUENCIA===undefined?'': taDatos.CODUNIDADFRECUENCIA;
		let lnDiasAntibiotico=$("#txtDiasUsoAntibioticoOM").val();
		let lnDiasUsoAntibiotico=parseInt(lnDiasAntibiotico)>0 ? parseInt(lnDiasAntibiotico) + parseInt(oMedicamentosOrdMedica.gnDiasAdicionalesAntibiotico) : 0;
		let lcControlAlertaAntibiotico = oMedicamentosOrdMedica.gcActualControlAlertaAntibiotico===undefined?'':oMedicamentosOrdMedica.gcActualControlAlertaAntibiotico;
		let lcEsUnirs=oMedicamentosOrdMedica.gcMarcaUnirs;

		if (lnDosisActual!=lnDosisNueva || lcCodigoDosisActual!=lnCodigoDosisNueva || lnFrecuenciaActual!=lnFrecuenciaNueva || lcCodigoFrecuenciaActual!=lnCodigoFrecuenciaNueva){
			lnEstado='13';
		}else{
			lnEstado=(lnEstadoActual==99 || lnEstadoActual==14) ? '11' : lnEstadoActual;
		}

		if (oModalMedicamentoControlado.gaDatosControlado!=''){
			lnControladoCantidad=oModalMedicamentoControlado.gaDatosControlado.CantidadControlado;
			lcControladoDiagnostico=oModalMedicamentoControlado.gaDatosControlado.cCodigoCieControlado;
			lcControladoObservaciones=oModalMedicamentoControlado.gaDatosControlado.ObservacionesControlado;
		}

		oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('updateRow', {
			index: oMedicamentosOrdMedica.indexedit,
			row: {
				SEFORMULA: 1,
				DOSIS: lnDosisNueva,
				CODUNIDADDOSIS: lnCodigoDosisNueva,
				DESCRUNIDADDOSIS: taDatos.DESCRUNIDADDOSIS===undefined?'': taDatos.DESCRUNIDADDOSIS,
				FRECUENCIA: lnFrecuenciaNueva,
				CODUNIDADFRECUENCIA: lnCodigoFrecuenciaNueva,
				DESCRUNIDADFRECUENCIA: taDatos.DESCRUNIDADFRECUENCIA===undefined?'': taDatos.DESCRUNIDADFRECUENCIA,
				VIA: taDatos.VIA===undefined?'': taDatos.VIA,
				DESCRVIA: taDatos.DESCRVIA===undefined?'': taDatos.DESCRVIA,
				OBSERVACIONES: taDatos.OBSERVACIONES===undefined?'': taDatos.OBSERVACIONES,
				COLOR: 3,
				ESTDET: lnEstado,
				CONTROLADOCANTIDAD: lnControladoCantidad,
				CONTROLADOCIE: lcControladoDiagnostico,
				DUSOANTIBIOTICO: lnDiasUsoAntibiotico,
				ANTIBVAL: oMedicamentosOrdMedica.gnAntibValAntibiotico,
				DUSOADICION: parseInt(oMedicamentosOrdMedica.gnDiasAdicionalesAntibiotico),
				CONTROLADOOBSERVACIONES: lcControladoObservaciones,
				USOANTFECHAFIN: 0,
				TIPOMODIFICACIONANTIBIOTICO:oMedicamentosOrdMedica.gcTipoModificacionAntibiotico===undefined?'': oMedicamentosOrdMedica.gcTipoModificacionAntibiotico,
				USOANTBDIAGNOSTICOINFECCIOSO: lcControlAlertaAntibiotico==='S'?(oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.DIAGNOSTICOINFECCIOSO===undefined?'':oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.DIAGNOSTICOINFECCIOSO):'',
				USOANTBDIAGNOSTICOANEXO: lcControlAlertaAntibiotico==='S'?(oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.DIAGNOSTICOANEXO===undefined?'':oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.DIAGNOSTICOANEXO):'',
				USOANTBOTROSDIAGNOSTICOS: lcControlAlertaAntibiotico==='S'?(oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.OTROSDIAGNOSTICOS===undefined?'':oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.OTROSDIAGNOSTICOS):'',
				USOANTBTIPOTRATAMIENTO: lcControlAlertaAntibiotico==='S'?(oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.TIPOTRATAMIENTO===undefined?'':oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.TIPOTRATAMIENTO):'',
				USOANTBAJUSTES: lcControlAlertaAntibiotico==='S'?(oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.AJUSTES===undefined?'':oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.AJUSTES):'',
				USOANTBOBSERVACIONES: lcControlAlertaAntibiotico==='S'?(oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.OBSERVACIONES===undefined?'':oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.OBSERVACIONES):'',
				USOANTBORIGENMUESTRA: lcControlAlertaAntibiotico==='S'?(oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.ORIGENMUESTRA===undefined?'':oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.ORIGENMUESTRA):'',
				USOANTBRESULTADO: lcControlAlertaAntibiotico==='S'?(oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.RESULTADO===undefined?'':oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.RESULTADO):'',
				ESUNIRS: lcEsUnirs,
				FORMULAPORVIA: oMedicamentosOrdMedica.gcMedicamentoVia,
			}
		});
		oMedicamentosOrdMedica.terminaRegistro();
	},

	registrarMedicamento: function(taDatos,tlFormulado,tnSeFormula,tcEstadoNuevo,tcConciliacion) {
		lnIdMed=oMedicamentosOrdMedica.gnConsMedId++;
		let lcControladoObservaciones=lcControladoDiagnostico='';
		let lnControladoCantidad=0;
		let lnDiasUsadoAntibiotico=taDatos.SELEC1===undefined?oMedicamentosOrdMedica.gnDiasUsadoAntibioticoMed: parseInt(taDatos.SELEC1);
		let llEsAntibiotico=taDatos.ESANTIBIOTICO===undefined?oMedicamentosOrdMedica.gcEsAntibioticoMed: taDatos.ESANTIBIOTICO;
		let lcAntibioticosDias=llEsAntibiotico ? lnDiasUsadoAntibiotico : '';
		lcAntibioticosDias=taDatos.ANTIBDIAS===undefined ? lcAntibioticosDias : taDatos.ANTIBDIAS;
		let lnFechaFormulacion=taDatos.FECHA_CREACION_FORMULA===undefined?0: taDatos.FECHA_CREACION_FORMULA;
		let lnHoraFormulacion=taDatos.HORA_CREACION_FORMULA===undefined?0: taDatos.HORA_CREACION_FORMULA;
		let lnDiasUsoAntibiotico= (taDatos.DIASINGRESAANTIBIOTICO===undefined || taDatos.DIASINGRESAANTIBIOTICO=='')?0:(oMedicamentosOrdMedica.gnDiasUsadoAntibioticoMed>0 ? oMedicamentosOrdMedica.gnDiasUsadoAntibioticoMed : parseInt(taDatos.DIASINGRESAANTIBIOTICO) + parseInt(oMedicamentosOrdMedica.gnDiasAdicionalesAntibiotico));
		lnDiasUsoAntibiotico = taDatos.DUSOANTIBIOTICO===undefined ? lnDiasUsoAntibiotico : taDatos.DUSOANTIBIOTICO;
		let lcControlAlertaAntibiotico=taDatos.CONTROLALERTAANTIB===undefined?oMedicamentosOrdMedica.gcControlAntibioticoMed: taDatos.CONTROLALERTAANTIB;

		if (oModalMedicamentoControlado.gaDatosControlado!=''){
			lnControladoCantidad=oModalMedicamentoControlado.gaDatosControlado.CantidadControlado;
			lcControladoDiagnostico=oModalMedicamentoControlado.gaDatosControlado.cCodigoCieControlado;
			lcControladoObservaciones=oModalMedicamentoControlado.gaDatosControlado.ObservacionesControlado;
		}
		lnEstado=taDatos.ESTADO!=undefined?taDatos.ESTADO: (!tlFormulado?11 : 0);
		lnEstado=taDatos.ESTDET===undefined ? lnEstado : taDatos.ESTDET;
		lnEstado=tcEstadoNuevo!=''?99:lnEstado;

		if ($.inArray(lnEstado, oMedicamentosOrdMedica.gaEstadosnoformular)>=0){ lcColorEstado=5; }else{ lcColorEstado=9; }
		lnColor=taDatos.ESTADO=='14' ? 8 : (tnSeFormula==1 ? 3 : lcColorEstado);
		lnColorAnterior=tlFormulado==='' ? 9 : (taDatos.ESTADO=='14' ? 8 : (tnSeFormula==1 ? 3 : lcColorEstado));
		lnInmediato=taDatos.INMEDIATO==1 ? taDatos.INMEDIATO : tnSeFormula;
		tnSeFormula=taDatos.SEFORMULA==1 ? taDatos.SEFORMULA : tnSeFormula;
		lcDescripcionCodigo=taDatos.DESCRIPCION===undefined?(taDatos.MEDICAMENTO===undefined?'': taDatos.MEDICAMENTO): taDatos.DESCRIPCION;
		lnSuspenderMed=taDatos.ESTADO===undefined?0: (taDatos.ESTADO=='14' ? 1 : 0);
		lnSuspenderMed=taDatos.SUSPENDER==1 ? taDatos.SUSPENDER : lnSuspenderMed;
		lnColor=taDatos.COLOR===undefined ? lnColor : taDatos.COLOR;
		lnColorAnterior=taDatos.COLORORG===undefined ? lnColor : taDatos.COLORORG;
		lcPosNopos= oMedicamentosOrdMedica.MedPosNopos!='' ? oMedicamentosOrdMedica.MedPosNopos : (taDatos.POSNOPOS===undefined?'': taDatos.POSNOPOS)
		lcGrupodescfarmaceutico=oMedicamentosOrdMedica.gcDescGrupoFarmaceuticoMed!='' ? oMedicamentosOrdMedica.gcDescGrupoFarmaceuticoMed.substr(0,16) : (taDatos.DESCRGRUPOCODFARMACEUTICO===undefined?'': taDatos.DESCRGRUPOCODFARMACEUTICO.substr(0,16)),
		lcGrupodescfarmaceutico=taDatos.GRUPODESCFARMACEUTICO===undefined ? lcGrupodescfarmaceutico : taDatos.GRUPODESCFARMACEUTICO;
		lcDescGrupoFarmaceuticoMed=oMedicamentosOrdMedica.gcDescGrupoFarmaceuticoMed!='' ? oMedicamentosOrdMedica.gcDescGrupoFarmaceuticoMed : (taDatos.DESCRGRUPOCODFARMACEUTICO===undefined?'': taDatos.DESCRGRUPOCODFARMACEUTICO);
		lcDescGrupoFarmaceuticoMed=taDatos.GRUPOFARMACEUTICODET===undefined ? lcDescGrupoFarmaceuticoMed : taDatos.GRUPOFARMACEUTICODET;
		lcAntival=taDatos.ANTIBVAL===undefined ? oMedicamentosOrdMedica.gnAntibValAntibiotico : taDatos.ANTIBVAL;
		lnDusoAdicion=taDatos.DUSOADICION===undefined ? parseInt(oMedicamentosOrdMedica.gnDiasAdicionalesAntibiotico) : taDatos.DUSOADICION;
		lcHabilitado=taDatos.HABILITADO===undefined ? (taDatos.ESTADO_MEDICAMENTO===undefined?'': taDatos.ESTADO_MEDICAMENTO) : taDatos.HABILITADO;
		lnCantidadMed=taDatos.CANTID===undefined?(taDatos.CANTIDAD===undefined?0: taDatos.CANTIDAD): taDatos.CANTID;
		lcDescripcionCambio=taDatos.DESCAMBIO===undefined?(taDatos.DESCRIPCION_MEDCAMBIO===undefined?'': taDatos.DESCRIPCION_MEDCAMBIO): taDatos.DESCAMBIO;
		lnEvolucionCambio=taDatos.EVOCAMBIO===undefined?(taDatos.CONSECEVOLUCION===undefined?0: taDatos.CONSECEVOLUCION): taDatos.EVOCAMBIO;
		lnConsecutivoFormula=taDatos.CSCCAMBIO===undefined?(taDatos.CONSECFORMULA===undefined?0: taDatos.CONSECFORMULA): taDatos.CSCCAMBIO;
		lnEstadoOrigen=taDatos.ESTDETORIG===undefined?(taDatos.ESTADO===undefined?0: taDatos.ESTADO): taDatos.ESTDETORIG;
		lnEstadoOrigen=tcEstadoNuevo!=''?99:lnEstadoOrigen;
		lcNombreMedico=taDatos.MEDICO===undefined?(taDatos.NOMBRE_MEDICO===undefined?'': taDatos.NOMBRE_MEDICO): taDatos.MEDICO;
		lcUsoAntibioticoDiagInfeccioso=lcControlAlertaAntibiotico==='S'?(oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.DIAGNOSTICOINFECCIOSO===undefined?'':oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.DIAGNOSTICOINFECCIOSO):'';
		lcUsoAntibioticoDiagInfeccioso=taDatos.USOANTBDIAGNOSTICOINFECCIOSO===undefined?lcUsoAntibioticoDiagInfeccioso: taDatos.USOANTBDIAGNOSTICOINFECCIOSO;
		lcUsoAntibioticoDiagAnexo=lcControlAlertaAntibiotico==='S'?(oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.DIAGNOSTICOANEXO===undefined?'':oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.DIAGNOSTICOANEXO):'';
		lcUsoAntibioticoDiagAnexo=taDatos.USOANTBDIAGNOSTICOANEXO===undefined?lcUsoAntibioticoDiagAnexo: taDatos.USOANTBDIAGNOSTICOANEXO;
		lcUsoAntibioticoDiagOtros=lcControlAlertaAntibiotico==='S'?(oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.OTROSDIAGNOSTICOS===undefined?'':oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.OTROSDIAGNOSTICOS):'';
		lcUsoAntibioticoDiagOtros=taDatos.USOANTBOTROSDIAGNOSTICOS===undefined?lcUsoAntibioticoDiagOtros: taDatos.USOANTBOTROSDIAGNOSTICOS;
		lcUsoAntibioticoTipoTratamiento=lcControlAlertaAntibiotico==='S'?(oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.TIPOTRATAMIENTO===undefined?'':oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.TIPOTRATAMIENTO):'';
		lcUsoAntibioticoTipoTratamiento=taDatos.USOANTBTIPOTRATAMIENTO===undefined?lcUsoAntibioticoTipoTratamiento: taDatos.USOANTBTIPOTRATAMIENTO;
		lcUsoAntibioticoAjustes=lcControlAlertaAntibiotico==='S'?(oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.AJUSTES===undefined?'':oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.AJUSTES):'';
		lcUsoAntibioticoAjustes=taDatos.USOANTBAJUSTES===undefined?lcUsoAntibioticoAjustes: taDatos.USOANTBAJUSTES;
		lcUsoAntibioticoObservaciones=lcControlAlertaAntibiotico==='S'?(oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.OBSERVACIONES===undefined?'':oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.OBSERVACIONES):'';
		lcUsoAntibioticoObservaciones=taDatos.USOANTBOBSERVACIONES===undefined?lcUsoAntibioticoObservaciones: taDatos.USOANTBOBSERVACIONES;
		lcUsoAntibioticoOrigenMuestra=lcControlAlertaAntibiotico==='S'?(oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.ORIGENMUESTRA===undefined?'':oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.ORIGENMUESTRA):'';
		lcUsoAntibioticoOrigenMuestra=taDatos.USOANTBORIGENMUESTRA===undefined?lcUsoAntibioticoOrigenMuestra: taDatos.USOANTBORIGENMUESTRA;
		lcUsoAntibioticoResultado=lcControlAlertaAntibiotico==='S'?(oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.RESULTADO===undefined?'':oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico.RESULTADO):'';
		lcUsoAntibioticoResultado=taDatos.USOANTBRESULTADO===undefined?lcUsoAntibioticoResultado: taDatos.USOANTBRESULTADO;
		lcEsConciliacion=tcConciliacion===undefined?'':tcConciliacion;
		lcMarcaUnirs=oMedicamentosOrdMedica.gcEsUnirs!=''?oMedicamentosOrdMedica.gcEsUnirs:((taDatos.LISTADOUNIRS===undefined || taDatos.LISTADOUNIRS==='')?'': taDatos.LISTADOUNIRS);
		lcEsUnirs=oMedicamentosOrdMedica.gcMarcaUnirs!=''?oMedicamentosOrdMedica.gcMarcaUnirs:(taDatos.TIPOMEDICAMENTO===undefined?'': taDatos.TIPOMEDICAMENTO);
		lcFormulaVia=oMedicamentosOrdMedica.gcMedicamentoVia!=''?oMedicamentosOrdMedica.gcMedicamentoVia:(taDatos.FORMULAPORVIA===undefined?'': taDatos.FORMULAPORVIA);

		let rows = []
			rows.push({
			IDMED: lnIdMed,
			SEFORMULA: tnSeFormula,
			INMEDIATO: lnInmediato,
			TEXTOINMEDIATO: '',
			SUSPENDER: lnSuspenderMed,
			CODIGO: taDatos.CODIGO===undefined?'': taDatos.CODIGO,
			MEDICAMENTO: lcDescripcionCodigo,
			POSNOPOS: lcPosNopos,
			GRUPOCODFARMACEUTICO: oMedicamentosOrdMedica.gcGrupoFarmaceuticoMed!='' ? oMedicamentosOrdMedica.gcGrupoFarmaceuticoMed : (taDatos.GRUPOCODFARMACEUTICO===undefined?'': taDatos.GRUPOCODFARMACEUTICO),
			GRUPODESCFARMACEUTICO: lcGrupodescfarmaceutico,
			GRUPOFARMACEUTICODET: lcDescGrupoFarmaceuticoMed,
			OBSERVACIONES: taDatos.OBSERVACIONES===undefined?'': taDatos.OBSERVACIONES,
			DOSIS: taDatos.DOSIS===undefined?'': taDatos.DOSIS,
			CODUNIDADDOSIS: taDatos.CODUNIDADDOSIS===undefined?'': taDatos.CODUNIDADDOSIS,
			DESCRUNIDADDOSIS: taDatos.DESCRUNIDADDOSIS===undefined?'': taDatos.DESCRUNIDADDOSIS,
			FRECUENCIA: taDatos.FRECUENCIA===undefined?'': parseInt(taDatos.FRECUENCIA),
			CODUNIDADFRECUENCIA: taDatos.CODUNIDADFRECUENCIA===undefined?'': taDatos.CODUNIDADFRECUENCIA,
			DESCRUNIDADFRECUENCIA: taDatos.DESCRUNIDADFRECUENCIA===undefined?'': taDatos.DESCRUNIDADFRECUENCIA,
			VIA: taDatos.VIA===undefined?'': taDatos.VIA,
			DESCRVIA: taDatos.DESCRVIA===undefined?'': taDatos.DESCRVIA,
			ANTIBDIAS: lcAntibioticosDias,
			ESANTIBIOTICO: llEsAntibiotico,
			DIASMAXANTIBIOTICO: taDatos.DIASMAXANTIBIOTICO===undefined?oMedicamentosOrdMedica.gnDiasMaximoAntibioticoMed: taDatos.DIASMAXANTIBIOTICO,	// DMaxAntib
			DUSOANTIBIOTICO: lnDiasUsoAntibiotico===undefined || lnDiasUsoAntibiotico===0 ? 0 :lnDiasUsoAntibiotico,
			DIASUSADOANTIB:	lnDiasUsadoAntibiotico,
			CONTROLALERTAANTIB: lcControlAlertaAntibiotico,
			HRSINUSO: taDatos.HRSINUSO===undefined?0: taDatos.HRSINUSO,
			ANTIBVAL: lcAntival,
			DUSOADICION: lnDusoAdicion,
			SELEC1: taDatos.SELEC1===undefined?'': taDatos.SELEC1,
			HABILITADO: lcHabilitado,
			CANTID: lnCantidadMed,
			MEDCAMBIO: taDatos.MEDCAMBIO===undefined?'': taDatos.MEDCAMBIO,
			DESCAMBIO: lcDescripcionCambio,
			ACEPTACAMBIO: taDatos.ACEPTACAMBIO===undefined?'': taDatos.ACEPTACAMBIO,
			EVOCAMBIO: lnEvolucionCambio,
			CSCCAMBIO: lnConsecutivoFormula,
			ESTDET: lnEstado,
			ESTDETORIG: lnEstadoOrigen,
			MEDICO: lcNombreMedico,
			FORMULADO: tlFormulado,
			CONTROLADO: taDatos.CONTROLADO===undefined?'': taDatos.CONTROLADO,
			CONTROLADOCANTIDAD: taDatos.CONTROLADOCANTIDAD===undefined?lnControladoCantidad: taDatos.CONTROLADOCANTIDAD,
			CONTROLADOCIE: taDatos.CONTROLADOCIE===undefined?lcControladoDiagnostico: taDatos.CONTROLADOCIE,
			CONTROLADOOBSERVACIONES: taDatos.CONTROLADOOBSERVACIONES===undefined?lcControladoObservaciones: taDatos.CONTROLADOOBSERVACIONES,
			COLOR: lnColor,
			COLORORG: lnColorAnterior,
			FECHACREACIONFORMULA: taDatos.FECHACREACIONFORMULA===undefined?lnFechaFormulacion: taDatos.FECHACREACIONFORMULA,
			HORACREACIONFORMULA: taDatos.HORACREACIONFORMULA===undefined?lnHoraFormulacion: taDatos.HORACREACIONFORMULA,
			USOANTFECHAFIN: 0,
			FECINICIOANTIB: taDatos.FECINICIOANTIB===undefined?(taDatos.FECHAINICIOANTIBIOTICO===undefined?'': taDatos.FECHAINICIOANTIBIOTICO): taDatos.FECINICIOANTIB,
			FECFINALANTIB: taDatos.FECFINALANTIB===undefined?(taDatos.FECHAFINALANTIBIOTICO===undefined?'': taDatos.FECHAFINALANTIBIOTICO): taDatos.FECFINALANTIB,
			TIPOSUSPENSDEANTIBIOTICO: '',
			TIPOMODIFICACIONANTIBIOTICO: '',
			USOANTBDIAGNOSTICOINFECCIOSO: lcUsoAntibioticoDiagInfeccioso,
			USOANTBDIAGNOSTICOANEXO: lcUsoAntibioticoDiagAnexo,
			USOANTBOTROSDIAGNOSTICOS: lcUsoAntibioticoDiagOtros,
			USOANTBTIPOTRATAMIENTO: lcUsoAntibioticoTipoTratamiento,
			USOANTBAJUSTES: lcUsoAntibioticoAjustes,
			USOANTBOBSERVACIONES: lcUsoAntibioticoObservaciones,
			USOANTBORIGENMUESTRA: lcUsoAntibioticoOrigenMuestra,
			USOANTBRESULTADO: lcUsoAntibioticoResultado,
			ACCION:'',
			CONCILIACION: lcEsConciliacion,
			MARCAUNIRS: lcMarcaUnirs,
			ESUNIRS: lcEsUnirs,
			FORMULAPORVIA: lcFormulaVia,
		})
		oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('append', rows);
		oMedicamentosOrdMedica.terminaRegistro();
	},

	terminaRegistro: function() {
		oMedicamentosOrdMedica.habilitarCampos(true, true);
		oMedicamentosOrdMedica.activarCampos(true);
		oMedicamentosOrdMedica.habilitarAntibiotico(false,'');
		oMedicamentosOrdMedica.inicializaVariables();
		oModalMedicamentoControlado.blanqueDatos();
		oModalJustificacionUsoAntibiotico.inicializaVariables();
		$('#cMedicamentoOM').focus();
	},

	inicializaVariables: function() {
		oMedicamentosOrdMedica.gnDiasMaximoAntibioticoMed=oMedicamentosOrdMedica.gnDiasAdicionalesAntibiotico=oMedicamentosOrdMedica.gnAntibValAntibiotico=0;
		oMedicamentosOrdMedica.MedPosNopos=oMedicamentosOrdMedica.gcControlado=oMedicamentosOrdMedica.gcConcentracion='';
		oMedicamentosOrdMedica.gcUnidad=oMedicamentosOrdMedica.gcPresentacion='';
		oMedicamentosOrdMedica.gcEsUnirs=oMedicamentosOrdMedica.gcMarcaUnirs=oMedicamentosOrdMedica.gcMedicamentoVia='';
		oMedicamentosOrdMedica.gllMostrarAlerta=false;
		oMedicamentosOrdMedica.gaDatosModificar=oMedicamentosOrdMedica.gaDatosmedicamento=[];
		oMedicamentosOrdMedica.gcGrupoFarmaceuticoMed=oMedicamentosOrdMedica.gcDescGrupoFarmaceuticoMed=oMedicamentosOrdMedica.gcPosNoposMed='';
		oMedicamentosOrdMedica.gcTipoJustificacionMed=oMedicamentosOrdMedica.gcControlAntibioticoMed='';
		oMedicamentosOrdMedica.gcEsAntibioticoMed=false;
		oMedicamentosOrdMedica.gnCantidadTotalJustificacionMed=oMedicamentosOrdMedica.gnDiasUsadoAntibioticoMed=0;
		oMedicamentosOrdMedica.gaDatosRegistrar=oMedicamentosOrdMedica.gaModificarMedicamento=[];
		oMedicamentosOrdMedica.gcTipoModificacionAntibiotico=oMedicamentosOrdMedica.gcActualCodUnidadDosis=oMedicamentosOrdMedica.gcActualCodigoFrecuencia='';
		oMedicamentosOrdMedica.gcActualControlAlertaAntibiotico='';
		oMedicamentosOrdMedica.glActualEsantibiotico=oMedicamentosOrdMedica.glActualFormulado=false;
		oMedicamentosOrdMedica.gnActualCantUnidadDosis=oMedicamentosOrdMedica.gnActualCantFrecuencia=0;
	},

	habilitarCampos: function(tlHabilitar,tlBlanquear) {
		if (tlBlanquear){
			$("#cMedicamentoOM,#cCodigoMedicamentoOM,#cDescripcionMedicamentoOM").val('');
		}
		$("#txtDosisOM,#selTipoDosisOM,#txtFrecuenciaOM,#selTipoFrecuenciaOM,#selTipoViaOM,#edtObservacionesOM").val('');
		$('#selTipoDosisOM,#selTipoViaOM').empty();
		$("#cMedicamentoOM,#cCodigoMedicamentoOM,#cDescripcionMedicamentoOM,#txtDosisOM,#selTipoDosisOM,#txtFrecuenciaOM,#selTipoFrecuenciaOM,#selTipoViaOM,#edtObservacionesOM").removeClass("is-valid").removeClass("is-invalid");
		$("#txtDiasUsoAntibioticoOM").val('');
		$("#antibioticoOM").hide();
		$('#cMedicamentoOM').focus();
	},

	activarCampos: function(tlHabilitar) {
		$("#txtDosisOM,#selTipoDosisOM,#txtFrecuenciaOM,#selTipoFrecuenciaOM,#selTipoViaOM,#edtObservacionesOM").attr("disabled",tlHabilitar);
	},

	habilitarAntibiotico: function(tlHabilitar,tcCodigoMedicamento) {
		$("#txtDiasUsoAntibioticoOM").val('');
		$("#txtDiasUsoAntibioticoOM").removeClass("is-valid");

		if (tlHabilitar){
			$("#antibioticoOM").show();
			$("#txtDiasUsoAntibioticoOM").attr("disabled",false);
			oMedicamentosOrdMedica.verificaAntibioticoFommulado(tcCodigoMedicamento);
		} else {
			$("#antibioticoOM").hide();
		}
	},

	verificaAntibioticoFommulado: function(tcCodigoMedicamento){
		let lnDiasUso=0;
		let taTablaValidar = oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('getData');

		if(taTablaValidar != ''){
			$.each(taTablaValidar, function( lcKey, loTipo ) {
				if(loTipo['CODIGO']==tcCodigoMedicamento && parseInt(loTipo['DUSOANTIBIOTICO'])>0){
					$("#txtDiasUsoAntibioticoOM").attr("disabled",true);
					$("#txtDiasUsoAntibioticoOM").val(loTipo['DUSOANTIBIOTICO']);
					return false;
				}
			});
		};
	},

	validarParametroMezclaDosis: function(){
		var lcCodigoMedicamento=$("#cCodigoMedicamentoOM").val();
		var lnDosis=$("#txtDosisOM").val();
		if (lcCodigoMedicamento==''){ $("#txtDosisOM").val(''); }

		if (lcCodigoMedicamento!='' && parseInt(lnDosis)>=0){
			oMedicamentosOrdMedica.parametroMezclaDosis(lcCodigoMedicamento,lnDosis);
		}
	},

	parametroMezclaDosis: function(tcCodigo,tnDosis){
		$.ajax({
			type: "POST",
			url: oMedicamentosOrdMedica.gcUrlAjax,
			data: {accion: 'consultaMezclaMedicamento', lcMedicamento: tcCodigo, lnValorDosis: tnDosis},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					oMedicamentosOrdMedica.gcTipoMezcla=toDatos.TIPOS.tipomezcla;
					oMedicamentosOrdMedica.gnCantidadDiariaMezcla=parseInt(toDatos.TIPOS.cantidaddiariamezcla);
					oMedicamentosOrdMedica.gcDescripcionMezcla=toDatos.TIPOS.descripcionmezcla;
					oMedicamentosOrdMedica.gnCantidadTotalMezcla=parseInt(toDatos.TIPOS.cantidadtotalmezcla);
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta dosis medicamento orden médica.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta dosis medicamento orden médica.");
		});
	},

	parametrosMedicamento: function(tcCodigo){
		$.ajax({
			type: "POST",
			url: oMedicamentosOrdMedica.gcUrlAjax,
			data: {accion: 'consultaParametrosMedicamento', lcMedicamento: tcCodigo},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					oMedicamentosOrdMedica.gcPosNoposMed=toDatos.TIPOS.posnopos;
					oMedicamentosOrdMedica.gcTipoJustificacionMed=toDatos.TIPOS.tipojustificacion;
					oMedicamentosOrdMedica.gnCantidadTotalJustificacionMed=parseInt(toDatos.TIPOS.cantidadtotaljustificacion);
					oMedicamentosOrdMedica.gcGrupoFarmaceuticoMed=toDatos.TIPOS.grupocodigo;
					oMedicamentosOrdMedica.gcDescGrupoFarmaceuticoMed=toDatos.TIPOS.grupodescripcion;
					oMedicamentosOrdMedica.gcEsAntibioticoMed=toDatos.TIPOS.esantibiotico;
					oMedicamentosOrdMedica.gcControlAntibioticoMed=toDatos.TIPOS.controlantibiotico;
					oMedicamentosOrdMedica.gnDiasMaximoAntibioticoMed=oMedicamentosOrdMedica.glActivarRangoAntibiotico==true ? toDatos.TIPOS.diasmaximoantibiotico : oMedicamentosOrdMedica.gnDiasParaAntibMax;
					oMedicamentosOrdMedica.gnDiasUsadoAntibioticoMed=parseInt(toDatos.TIPOS.diasusadoantibiotico);

					if (oMedicamentosOrdMedica.gcEsAntibioticoMed){
						oMedicamentosOrdMedica.habilitarAntibiotico(oMedicamentosOrdMedica.gcEsAntibioticoMed,tcCodigo);
					}

				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta dosis medicamento orden médica.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta dosis medicamento orden médica.");
		});
	},

	consultaViaAdministracion: function(tcCodigo,tcDescripcion,tlConsulta,tcVia){
		var lcAltoRiesgo='';
		$('#selTipoViaOM').empty();
		$('#selTipoViaOM').val('');

		$.ajax({
			type: "POST",
			url: oMedicamentosOrdMedica.gcUrlAjax,
			data: {accion: 'consultaViaAdministracionMedicamento', lcMedicamento: tcCodigo},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.TIPOS.length>1){
						$('#selTipoViaOM').append('<option value=""></option>');
					}
					$.each(toDatos.TIPOS, function( lcKey, loTipo ) {
						lcAltoRiesgo=loTipo.ALTORIESGO;
						$('#selTipoViaOM').append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
					});

					if (tcVia!=''){
						$("#selTipoViaOM").val(tcVia);
					}
					oMedicamentosOrdMedica.activarCampos(false);

					if (tlConsulta){
						if (lcAltoRiesgo!=''){
							oMedicamentosOrdMedica.alertaAltoRiesgo(tcCodigo,tcDescripcion);
						}else{
							oMedicamentos.alertaInr(tcCodigo,tcDescripcion);
							$('#txtDosisOM').focus();
						}
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta dosis medicamento orden médica.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta dosis medicamento orden médica.");
		});
	},

	consultaDosis: function(tcCodigo,tcUnidadDosis){
		$('#selTipoDosisOM').empty();
		$('#selTipoDosisOM').val('');

		$.ajax({
			type: "POST",
			url: oMedicamentosOrdMedica.gcUrlAjax,
			data: {accion: 'consultaDosisMedicamento', lcMedicamento: tcCodigo},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.TIPOS.length>1){
						$('#selTipoDosisOM').append('<option value=""></option>');
					}
					$.each(toDatos.TIPOS, function( lcKey, loTipo ) {
						$('#selTipoDosisOM').append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
					});

					if (tcUnidadDosis!=''){
						$("#selTipoDosisOM").val(tcUnidadDosis);
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta dosis medicamento orden médica.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta dosis medicamento orden médica.");
		});
	},

	consultarFrecuencias: function(){
		$('#selTipoFrecuenciaOM').empty();
		$('#selTipoFrecuenciaOM').val('');

		$.ajax({
			type: "POST",
			url: oMedicamentosOrdMedica.gcUrlAjax,
			data: {accion: 'consultaListaFrecuencias'},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					$('#selTipoFrecuenciaOM').append('<option value=""></option>');
					$.each(toDatos.TIPOS, function( lcKey, loTipo ) {
						if (loTipo.estado==='F'){
							$('#selTipoFrecuenciaOM').append('<option value="' + lcKey + '" data-unidad="' + loTipo.unidad + '">' + loTipo.desc + '</option>');
						}
					});
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta dosis medicamento orden médica.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta dosis medicamento orden médica.");
		});
	},

	alertaAltoRiesgo: function(tcCodigo,tcDescripcion){
		lcMensaje = "Está prescribiendo un medicamento de ALTO RIESGO. <br>" + tcDescripcion + '<br>'
					+ "Por favor verifique Medicamento, Dosis, Frecuencia y Vía de Administración antes de finalizar la formulación.";

		fnAlert(lcMensaje, 'ÓRDENES MÉDICAS ALTO RIESGO', false, 'blue', 'medium', function(){
			oMedicamentos.alertaInr(tcCodigo,tcDescripcion);
		});
	},

	eventosGrupoFarmaceutico: {
		'click .eventoGrupoFarma': function(e, tcValor, toFila, tnIndice) {
			fnAlert(toFila.GRUPOFARMACEUTICODET,"Grupo Farmacologico", false, 'blue', 'medium');
		}
	},

	eventoNombreMedicamento: {
		'click .eventoNombreMed': function(e, tcValor, toFila, tnIndice) {
			oMedicamentosOrdMedica.editarMedicamentoOM(toFila);
		}
	},

	formatoColor: function (toFila, tnIndice) {
		var lcColor='0';
		lcColor = toFila['COLOR'];
		return oMedicamentosOrdMedica.goColorFila[lcColor]? {css: {'background-color':oMedicamentosOrdMedica.goColorFila[lcColor]}}: {};
	},

	formatoSeformula: function(tnValor, toFila){
		return [
			'<a class="intSeformula" id="intSeformula-'+toFila['CODIGO']+'" href="javascript:void(0)" title="Formulado">',
			'<i class="fa '+(tnValor==1 ? 'fa-check-square' : 'fa-square')+'" style="color:#878EA9"></i>',
			'</a>'
		].join('');
	},

	formatoInmediato: function(tnValor, toFila){
		return [
			'<a class="intInmediato" id="intInmediato-'+toFila['CODIGO']+'" href="javascript:void(0)" title="Inmediato">',
			'<i class="fa '+(tnValor==1 ? 'fa-check-square' : 'fa-square')+'" style="color:#878EA9"></i>',
			'</a>'
		].join('');
	},

	formatoSuspender: function(tnValor, toFila){
		return [
			'<a class="intSuspender" id="intSuspender-'+toFila['CODIGO']+'" href="javascript:void(0)" title="Suspender">',
			'<i class="fa '+(tnValor==1 ? 'fa-check-square' : 'fa-square')+'" style="color:#878EA9"></i>',
			'</a>'
		].join('');
	},

	verificaAlerta: function(taDatos){
		let lcCodigoMedicamento=taDatos.CODIGO;
		let lnFechaFormulacionAlerta=parseInt(taDatos.FECHACREACIONFORMULA);
		let lnDiasUsoAntibiotico=parseInt(taDatos.DUSOANTIBIOTICO);

		$.ajax({
			type: "POST",
			url: oMedicamentosOrdMedica.gcUrlAjax,
			data: {accion: 'verificarAlertaAntibioticos', lnIngreso: aDatosIngreso['nIngreso'],
					lcMedicamento: lcCodigoMedicamento, lnFechaFormula: lnFechaFormulacionAlerta,
					lnDiasAntibiotico: lnDiasUsoAntibiotico	},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.TIPOS!=''){
						var lcMensaje=toDatos.TIPOS;
						oMedicamentosOrdMedica.gllMostrarAlerta=true;
						fnAlert(toDatos.TIPOS, "ATENCIÓN", false, false, 'medium');
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la busqueda verifica alerta ordenes médicas/medicamentos.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar verifica alerta ordenes médicas/medicamentos.");
		});
	},

	marcarFila: function(tnIndex, tnSeformula, tnInmediato, tnSuspende, tnValor, tnEstado, tcAceptaCambio,tcInmediato){
		var lnControladoCantidad=lcControladoDiagnostico=lcControladoObservaciones='';
		if (oModalMedicamentoControlado.gaDatosControlado!=''){
			lnControladoCantidad=oModalMedicamentoControlado.gaDatosControlado.CantidadControlado;
			lcControladoDiagnostico=oModalMedicamentoControlado.gaDatosControlado.cCodigoCieControlado;
			lcControladoObservaciones=oModalMedicamentoControlado.gaDatosControlado.ObservacionesControlado;
		}

		oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('updateCell',{index:tnIndex, field:'SEFORMULA', value:tnSeformula});
		oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('updateCell',{index:tnIndex, field:'INMEDIATO', value:tnInmediato});
		oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('updateCell',{index:tnIndex, field:'SUSPENDER', value:tnSuspende});
		oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('updateCell',{index:tnIndex, field:'COLOR', value:tnValor});
		oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('updateCell',{index:tnIndex, field:'ESTDET', value:tnEstado});
		oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('updateCell',{index:tnIndex, field:'ACEPTACAMBIO', value:tcAceptaCambio});
		oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('updateCell',{index:tnIndex, field:'TEXTOINMEDIATO', value:tcInmediato});
		oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('updateCell',{index:tnIndex, field:'CONTROLADOCANTIDAD', value:lnControladoCantidad});
		oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('updateCell',{index:tnIndex, field:'CONTROLADOCIE', value:lcControladoDiagnostico});
		oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('updateCell',{index:tnIndex, field:'CONTROLADOOBSERVACIONES', value:lcControladoObservaciones});
		oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('updateCell',{index:tnIndex, field:'ESUNIRS', value:oMedicamentosOrdMedica.gcMarcaUnirs});
	},

	cambioMedicamentoPregunta: function(taDatos,tnIndex){
		$.ajax({
			type: "POST",
			url: oMedicamentosOrdMedica.gcUrlAjax,
			data: { accion: 'consultaCambioMedicamento',
					lnIngreso: aDatosIngreso['nIngreso'],
					lcMedActual: taDatos['CODIGO'],
					lnConsecFormula: taDatos['CSCCAMBIO'],
					lcMedCambiar: taDatos['MEDCAMBIO'],
					lcViaCambiar: taDatos['VIA'] },
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					var lcObservaciones=toDatos.TIPOS;
					if (lcObservaciones!=''){
						var lcMedicamentoActual=taDatos['CODIGO'] +' - '+taDatos['MEDICAMENTO'];
						var lcMedicamentoCambiar=taDatos['MEDCAMBIO'] +' - '+taDatos['DESCAMBIO'];
						var lcTextoCambio = [
							'<div class="container-fluid small">',
								'<div class="row">',
									'<div class="col-12"><h6>Intervención farmacia:</h6></div>',
									'<div class="col-12">Se sugiere el médicamento:</div>',
									'<div class="col-12"><b>'+lcMedicamentoActual+'</b></div>',
									'<div class="col-12">a cambio de:</div>',
									'<div class="col-12"><b>'+lcMedicamentoCambiar+'</b></div>',
								'</div><br>',
								'<div class="row">',
									'<div class="col-12"><h6>Justificación del cambio</h6></div>',
									'<textarea class="form-control" id="txtJustificacionCambioMedicamento" name="JustificacionCambioMedicamento" rows="4" disabled>'+lcObservaciones+'</textarea>',
								'</div><br>',
								'<label><b><h6>¿Desea aceptar el cambio?<h6></b></label>',
						].join('');

						fnConfirm(lcTextoCambio, false, false, false, 'large',
							{ text: 'Aceptar',
								action: function(){
									oMedicamentosOrdMedica.marcarFila(tnIndex, 0, 0, 1, 8, 14, 'S', '');
									oMedicamentosOrdMedica.duplicarMedicamento(taDatos);
								}
							},
							{  text: 'Cancelar',
								action: function(){
									oMedicamentosOrdMedica.marcarFila(tnIndex, 1, 0, 0, 3, 11, 'N', '');
								}
							}
						);
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la busqueda para consultar cambio medicamento pregunta.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar consultar cambio medicamento pregunta.");
		});
	},

	duplicarMedicamento: function(taDatos){
		llFormulado=false;
		laDatosMedicamento = {CODIGO: taDatos.MEDCAMBIO, DESCRIPCION: taDatos.DESCAMBIO, OBSERVACIONES: taDatos.OBSERVA,
								DOSIS: taDatos.DOSIS, CODUNIDADDOSIS: taDatos.CODUNIDADDOSIS, DESCRUNIDADDOSIS: taDatos.DESCRUNIDADDOSIS,
								FRECUENCIA: taDatos.FRECUENCIA, CODUNIDADFRECUENCIA: taDatos.CODUNIDADFRECUENCIA, DESCRUNIDADFRECUENCIA: taDatos.DESCRUNIDADFRECUENCIA,
								VIA: taDatos.VIA, DESCRVIA: taDatos.DESCRVIA, DIASUSOANTIBIOTICO: taDatos.DIASUSOANTIBIOTICO, ESTADO: 11,
								GRUPOCODFARMACEUTICO: taDatos.GRUPOCODFARMACEUTICO, DESCRGRUPOCODFARMACEUTICO: taDatos.GRUPODESCFARMACEUTICO,
								POSNOPOS: taDatos.POSNOPOS, NOMBRE_MEDICO:taDatos.MEDICO, CONSECEVOLUCION: taDatos.EVOCAMBIO,
								OBSERVACIONES: taDatos.OBSERVACIONES,FECHACREACIONFORMULA: taDatos.FECHACREACIONFORMULA,
								HORACREACIONFORMULA: taDatos.HORACREACIONFORMULA,
								};
		oMedicamentosOrdMedica.preguntarUnirs(laDatosMedicamento,llFormulado,1,'','','R');
	},

	seleccionSeFormula: function(tnValor, toFila, tnIndex){
		let lcCancelado='';
		oMedicamentosOrdMedica.gcEsUnirs='';
		oModalMedicamentoControlado.gaDatosControlado.CantidadControlado=0;
		oModalMedicamentoControlado.gaDatosControlado.cCodigoCieControlado=oModalMedicamentoControlado.gaDatosControlado.ObservacionesControlado='';
		oMedicamentosOrdMedica.consultadatoscontrolado(toFila);
		oMedicamentosOrdMedica.gcMarcaUnirs='';
		oMedicamentosOrdMedica.gcEsUnirs=toFila.MARCAUNIRS;

		if(tnValor==0){
			if (toFila['HABILITADO']=='1'){
				lcTextoSeFormula='El medicamento <br>' + toFila['MEDICAMENTO'] + '<br>se encuentra deshabilitado en inventario y no puede ser formulado nuevamente, por favor revisar.';
				fnAlert(lcTextoSeFormula, oMedicamentosOrdMedica.lcTitulo, false, 'blue', 'medium');
				return false;
			}
			oMedicamentosOrdMedica.editarMedicamentoOM(toFila);
			if (toFila['ESANTIBIOTICO'] && oMedicamentosOrdMedica.gcSolicitaFormato=='1' && toFila['CONTROLALERTAANTIB']=='S'){
				oMedicamentosOrdMedica.verificaAlerta(toFila);
			}

			if (toFila['SUSPENDER']==1){
				lcTextoSeFormula='¿Está seguro(a) que desea formular nuevamente el medicamento <br>' + toFila['MEDICAMENTO'];
				fnConfirm(lcTextoSeFormula, false, false, false, 'medium',
					{ text: 'Aceptar',
						action: function(){
							lcCancelado='';
						}
					},
					{  text: 'Cancelar',
						action: function(){
							oMedicamentosOrdMedica.marcarFila(tnIndex, 0, 0, 1, 8, 14, toFila['ACEPTACAMBIO'],'');
							lcCancelado='C';
						}
					}
				);
			}

			if (lcCancelado==''){
				let lnValor=1;
				let lnEstado=(toFila['ESTDET']=='12' || toFila['ESTDET']=='14' || toFila['ESTDET']=='99') ? 11 : toFila['ESTDET'];
				oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('updateCell',{index:tnIndex, field:'TIPOSUSPENSDEANTIBIOTICO', value:''});
				if (toFila['MEDCAMBIO']!='' && toFila['ACEPTACAMBIO']==''){
					oMedicamentosOrdMedica.cambioMedicamentoPregunta(toFila,tnIndex);
					oMedicamentosOrdMedica.marcarFila(tnIndex, lnValor, 0, 0, 3, lnEstado, toFila['ACEPTACAMBIO'],'');
				}else{
					if (toFila['CONTROLADO']!='' && toFila['CONTROLADOCIE']==''){
						oModalMedicamentoControlado.mostrar(toFila,'',tnIndex,lnEstado,toFila['ACEPTACAMBIO']);
					}else{
						if (oMedicamentosOrdMedica.gcEsUnirs!='' && !toFila['ESANTIBIOTICO']){
							oMedicamentosOrdMedica.preguntarUnirsMarcar(tnIndex, lnValor, 0, 0, 3, lnEstado, toFila['ACEPTACAMBIO'],'',toFila['MEDICAMENTO']);
						}else{
							oMedicamentosOrdMedica.marcarFila(tnIndex, lnValor, 0, 0, 3, lnEstado, toFila['ACEPTACAMBIO'],'');
						}
					}
				}
			}
		}else{
			oMedicamentosOrdMedica.editarMedicamentoOM(toFila);
			lnSuspender=toFila['ESTDETORIG']=='14' ? 1 : 0;
			oMedicamentosOrdMedica.marcarFila(tnIndex, 0,0,lnSuspender,toFila['COLORORG'],toFila['ESTDETORIG'], toFila['ACEPTACAMBIO'],'');
		}
	},

	preguntarUnirsMarcar: function(tnIndex, tnSeformula, tnInmediato, tnSuspende, tnValor, tnEstado, tcAceptaCambio, tcInmediato, tcDescmedicamento) {
		lcTituloUnirs=tcDescmedicamento;
		lcMensajeUnirs=oMedicamentosOrdMedica.gcTextoUnirs.replaceAll('~','<br>');

		fnConfirm(lcMensajeUnirs,lcTituloUnirs, false, false, 'large', false, false,
			{buttons: {
				Si: {
					action: function(){
						oMedicamentosOrdMedica.gcMarcaUnirs='S';
						oMedicamentosOrdMedica.marcarFila(tnIndex, tnSeformula, tnInmediato, tnSuspende, tnValor, tnEstado, tcAceptaCambio, tcInmediato);
					}
				},
				No: {
					action: function(){
						oMedicamentosOrdMedica.gcMarcaUnirs='N';
						oMedicamentosOrdMedica.marcarFila(tnIndex, tnSeformula, tnInmediato, tnSuspende, tnValor, tnEstado, tcAceptaCambio, tcInmediato);
					}
				},
				Listado_Unirs: {
					action: function(){
						window.open(oMedicamentosOrdMedica.gcRutaArchivoUnirs,"_blank");
						return false;
					}
				}}
			}
		);
	},

	justificacionInmediato: function(tnValor, toFila, tnIndex){
		lcTextoInmediato='Justifique, mínimo ' + oMedicamentosOrdMedica.gnCantidadMinInmediato + ' caracteres, por qué el medicamento <br>';
		lcTextoInmediato+=toFila['MEDICAMENTO'] + '<br>Debe ser suministrado de forma inmediata.'
		oModalJustificacioInmediato.mostrar(tnIndex,toFila,lcTextoInmediato);
	},

	consultadatoscontrolado: function(taDatosFila){
		oModalMedicamentoControlado.gaDatosControlado = { CantidadControlado: taDatosFila.CONTROLADOCANTIDAD, CieControladoPaciente: '',
				ObservacionesControlado: taDatosFila.CONTROLADOOBSERVACIONES, SoloDiagnosticoPaciente: '', cCodigoCieControlado: taDatosFila.CONTROLADOCIE,
				cCodigoMedicamentoControlado: taDatosFila.CODIGO, cDescripcionCieControlado: '', cDescripcionControlado: taDatosFila.MEDICAMENTO,
				txtCodigoControlado: '',
		};
	},

	seleccionInmediato: function(tnValor, toFila, tnIndex){
		let lcCancelado='';
		let lnSeFormula=toFila['SEFORMULA'];
		let lnColorRegistrar=0;
		oMedicamentosOrdMedica.gcEsUnirs='';
		oMedicamentosOrdMedica.gnSeleccionInmediato=1;
		oModalMedicamentoControlado.gaDatosControlado.CantidadControlado=0;
		oModalMedicamentoControlado.gaDatosControlado.cCodigoCieControlado=oModalMedicamentoControlado.gaDatosControlado.ObservacionesControlado='';
		oMedicamentosOrdMedica.consultadatoscontrolado(toFila);
		oMedicamentosOrdMedica.gcMarcaUnirs='';
		oMedicamentosOrdMedica.gcEsUnirs=toFila.MARCAUNIRS;

		if(tnValor==0){
			if (toFila['HABILITADO']=='1'){
				lcTextoSeFormula='El medicamento <br>' + toFila['MEDICAMENTO'] +'-'+ toFila['MEDICAMENTO'] + '<br>se encuentra deshabilitado en inventario y no puede ser formulado nuevamente, por favor revisar.';
				fnAlert(lcTextoSeFormula, oMedicamentosOrdMedica.lcTitulo, false, 'blue', 'medium');
				return false;
			}
			oMedicamentosOrdMedica.editarMedicamentoOM(toFila);
			if (toFila['ESANTIBIOTICO'] && oMedicamentosOrdMedica.gcSolicitaFormato=='1' && toFila['CONTROLALERTAANTIB']=='S'){
				oMedicamentosOrdMedica.verificaAlerta(toFila);
			}

			if (toFila['SUSPENDER']==1){
				lcTextoSeFormula='¿Está seguro(a) que desea formular nuevamente el medicamento <br>' + toFila['MEDICAMENTO'];
				fnConfirm(lcTextoSeFormula, false, false, false, 'medium',
					{ text: 'Aceptar',
						action: function(){
							lcCancelado='';
						}
					},
					{  text: 'Cancelar',
						action: function(){
							oMedicamentosOrdMedica.marcarFila(tnIndex, 0, 0, 1, 8, toFila['ESTDETORIG'], toFila['ACEPTACAMBIO'],'');
							lcCancelado='C';
						}
					}
				);
			}

			if (lcCancelado==''){
				lnEstadoInm=(toFila['ESTDET']=='14' || toFila['ESTDET']=='99') ? 12 : toFila['ESTDET'];
				oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('updateCell',{index:tnIndex, field:'TIPOSUSPENSDEANTIBIOTICO', value:''});
				if ($.inArray(aDatosIngreso['cCodVia'], oMedicamentosOrdMedica.gcViasJustificarInmediato)>=0){
					if (oMedicamentosOrdMedica.gcSeccionesExcluidasInmediato==''){
						oMedicamentosOrdMedica.justificacionInmediato(tnValor, toFila, tnIndex);
					}else{
						if ($.inArray(aDatosIngreso['cSeccion'], oMedicamentosOrdMedica.gcSeccionesExcluidasInmediato)>=0){
							if (toFila['CONTROLADO']!='' && toFila['CONTROLADOCIE']==''){
								oModalMedicamentoControlado.mostrar(toFila,'',tnIndex,lnEstadoInm,toFila['ACEPTACAMBIO']);
							}else{

								if (oMedicamentosOrdMedica.gcEsUnirs!=''){
									oMedicamentosOrdMedica.preguntarUnirsMarcar(tnIndex, 1, 1, 0, 3, lnEstadoInm, toFila['ACEPTACAMBIO'],'',toFila['MEDICAMENTO']);
								}else{
									oMedicamentosOrdMedica.marcarFila(tnIndex, 1, 1, 0, 3, lnEstadoInm, toFila['ACEPTACAMBIO'],'');
								}
							}
						}else{
							oMedicamentosOrdMedica.justificacionInmediato(tnValor, toFila, tnIndex);
						}
					}
				}else{
					if (toFila['CONTROLADO']!='' && toFila['CONTROLADOCIE']==''){
						oModalMedicamentoControlado.mostrar(toFila,'',tnIndex,lnEstadoInm,toFila['ACEPTACAMBIO']);
					}else{
						if (lcMarcaUnirs!=''){
							oMedicamentosOrdMedica.preguntarUnirsMarcar(tnIndex, 1, 1, 0, 3, lnEstadoInm, toFila['ACEPTACAMBIO'],'',toFila['MEDICAMENTO']);
						}else{
							oMedicamentosOrdMedica.marcarFila(tnIndex, 1, 1, 0, 3, lnEstadoInm, toFila['ACEPTACAMBIO'],'');
						}
					}
				}
			}
		}else{
			oMedicamentosOrdMedica.editarMedicamentoOM(toFila);
			lnSuspender=toFila['ESTDETORIG']=='14' ? 1 : 0;
			lnColorRegistrar=lnSeFormula===1 ? 3 : toFila['COLORORG'];
			oMedicamentosOrdMedica.marcarFila(tnIndex, 1,0,lnSuspender,lnColorRegistrar,toFila['ESTDETORIG'], toFila['ACEPTACAMBIO'],'');
		}
	},

	modificarAntibiotico: function () {
		$('#divModificarAntibiotico').modal('show');
		$('#selTipoModificacionAntibiotico').val('');
	},

	cancelarModificarAntibiotico: function () {
		$('#selTipoModificacionAntibiotico').val('');
		$('#divModificarAntibiotico').modal('hide');
	},

	guardarModificarAntibiotico: function () {
		oMedicamentosOrdMedica.gcTipoModificacionAntibiotico=$('#selTipoModificacionAntibiotico').val()!='' ? $('#selTipoModificacionAntibiotico').val() : '';
		if (oMedicamentosOrdMedica.gcTipoModificacionAntibiotico===''){
			fnAlert('Tipo modificacion obligatoria, revise por favor.', 'Validación Anbiótico', false, false, 'medium');
		}else{
			$('#selTipoModificacionAntibiotico').val('');
			$('#divModificarAntibiotico').modal('hide');

			if (oMedicamentosOrdMedica.gcControlAntibioticoMed=='S'){
				oMedicamentosOrdMedica.alertaControlAntibiotico(oMedicamentosOrdMedica.gaModificarMedicamento);
			}else{
				oMedicamentosOrdMedica.preguntarUnirs(oMedicamentosOrdMedica.gaModificarMedicamento,'',1,'','','M');

			}
		}
	},

	suspenderAntibiotico: function () {
		$('#divSuspensionAntibiotico').modal('show');
		$('#selTipoSuspenderAntibiotico').val('');
	},

	guardarSuspenderAntibiotico: function () {
		let lcTipoSusAntibiotico=$('#selTipoSuspenderAntibiotico').val();
		if (lcTipoSusAntibiotico===''){
			fnAlert('Tipo suspensión obligatoria, revise por favor.', 'Validación Anbiótico', false, false, 'medium');
		}else{
			oMedicamentosOrdMedica.marcarFila(oMedicamentosOrdMedica.gaDatosRegistrar.INDEX, 0, 0, 1, 8, oMedicamentosOrdMedica.gaDatosRegistrar.ESTADO, oMedicamentosOrdMedica.gaDatosRegistrar.ACEPTACAMBIO,'');
			oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('updateCell',{index:oMedicamentosOrdMedica.gaDatosRegistrar.INDEX, field:'TIPOSUSPENSDEANTIBIOTICO', value:lcTipoSusAntibiotico});
			$('#selTipoSuspenderAntibiotico').val('');
			$('#divSuspensionAntibiotico').modal('hide');
			oMedicamentosOrdMedica.gaDatosRegistrar=[];
		}
	},

	cancelarSuspenderAntibiotico: function () {
		$('#selTipoSuspenderAntibiotico').val('');
		$('#divSuspensionAntibiotico').modal('hide');
	},

	seleccionSuspender: function(tnValor, toFila, tnIndex){
		oModalMedicamentoControlado.gaDatosControlado.CantidadControlado=0;
		oModalMedicamentoControlado.gaDatosControlado.cCodigoCieControlado=oModalMedicamentoControlado.gaDatosControlado.ObservacionesControlado='';
		oMedicamentosOrdMedica.gaDatosRegistrar=[];
		oMedicamentosOrdMedica.consultadatoscontrolado(toFila);
		oMedicamentosOrdMedica.gcMarcaUnirs='';

		if (toFila['FORMULADO']){
			if(tnValor==0){
				lcTextoSeFormula='¿Está seguro(a) que desea Suspender el medicamento <br>'  + toFila['MEDICAMENTO'] +'<br>No aparecerá en la fórmula.';

				fnConfirm(lcTextoSeFormula, false, false, false, 'medium',
					{ text: 'Aceptar',
						action: function(){
							if (toFila['ESANTIBIOTICO']){
								oMedicamentosOrdMedica.gaDatosRegistrar= {INDEX: tnIndex, ESTADO: toFila['ESTDET'], ACEPTACAMBIO: toFila['ACEPTACAMBIO']}
								oMedicamentosOrdMedica.suspenderAntibiotico();
							}else{
								oMedicamentosOrdMedica.marcarFila(tnIndex, 0, 0, 1, 8, toFila['ESTDET'], toFila['ACEPTACAMBIO'],'');
							}
						}
					},
					{  text: 'Cancelar',

					}
				);
			}else{
				if (toFila['ESTDETORIG']==14){
					if (toFila['HABILITADO']==''){
						lcTextoSeFormula='¿Está seguro(a) que desea formular nuevamente el medicamento <br>' + toFila['MEDICAMENTO'];

						fnConfirm(lcTextoSeFormula, false, false, false, 'medium',
							{ text: 'Aceptar',
								action: function(){
									oMedicamentosOrdMedica.marcarFila(tnIndex, 1, 0, 0, 3, toFila['ESTDETORIG'], toFila['ACEPTACAMBIO'],'');
								}
							},
							{  text: 'Cancelar',
								action: function(){
									oMedicamentosOrdMedica.marcarFila(tnIndex, 0, 0, 1, toFila['COLORORG'], toFila['ESTDETORIG'], toFila['ACEPTACAMBIO'],'');
								}
							}
						);
					}else{
						oMedicamentosOrdMedica.marcarFila(tnIndex, 0, 0, 1, 8, toFila['ESTDETORIG'], toFila['ACEPTACAMBIO'],'');
					}
				}else{
					oMedicamentosOrdMedica.marcarFila(tnIndex, 0, 0, 0, toFila['COLORORG'], toFila['ESTDETORIG'], toFila['ACEPTACAMBIO'],'');
				}
			}
		}else{
			lcTextoSeFormula='Medicamento no viene de formulación anterior, no puede suspenderse.<br> Debe eliminarlo para retirarlo de la formulación.';
			fnAlert(lcTextoSeFormula, oMedicamentosOrdMedica.lcTitulo, false, 'blue', 'medium');
		}
	},

	eventoSeformula: {
		'click .intSeformula': function (e, tnValor, toFila, tnIndex) {
			if ($("#btnGuardarOrdenesMedicas").prop("disabled")==false){
				let lnScrollPos = $('#tblMedicamentosOM').bootstrapTable('getScrollPosition');
				oMedicamentosOrdMedica.seleccionSeFormula(tnValor, toFila, tnIndex);
				$('#tblMedicamentosOM').bootstrapTable('scrollTo',{unit: 'px', value: lnScrollPos});
			}
		},

		'click .intInmediato': function (e, tnValor, toFila, tnIndex) {
			if ($("#btnGuardarOrdenesMedicas").prop("disabled")==false){
				let lnScrollPos = $('#tblMedicamentosOM').bootstrapTable('getScrollPosition');
				oMedicamentosOrdMedica.seleccionInmediato(tnValor, toFila, tnIndex);
				$('#tblMedicamentosOM').bootstrapTable('scrollTo',{unit: 'px', value: lnScrollPos});
			}
		},

		'click .intSuspender': function (e, tnValor, toFila, tnIndex) {
			if ($("#btnGuardarOrdenesMedicas").prop("disabled")==false){
				let lnScrollPos = $('#tblMedicamentosOM').bootstrapTable('getScrollPosition');
				oMedicamentosOrdMedica.seleccionSuspender(tnValor, toFila, tnIndex);
				$('#tblMedicamentosOM').bootstrapTable('scrollTo',{unit: 'px', value: lnScrollPos});
			}
		},
	},

	eventoMedicamento:  {
		'click .eliminaMed': function (e, value, row, index) {
			if (row.FORMULADO){
				lcTexto = 'El medicamento<br>' + row.MEDICAMENTO + '<br>no se puede ser eliminado, debe suspenderlo.'
				fnAlert(lcTexto, 'Medicamento', false, 'blue', 'medium');
			}else{
				fnConfirm('Desea eliminar el medicamento?', false, false, false, false, function(){
					$('#tblMedicamentosOM').bootstrapTable('remove', {
					field: 'IDMED',
					values: [row.IDMED]
					});
					oMedicamentosOrdMedica.habilitarCampos(true, true);
					$('#cMedicamentoOM').focus();
				},'');
			}
		}
	},

	formatoMedicamento: function (value, row, index) {
		return [
		  '<a class="eliminaMed" href="javascript:void(0)" title="Eliminar medicamento">',
		  '<i class="fas fa-trash-alt" style="color:#E96B50"></i>',
		  '</a>'
		].join('')
	},

	cantidadRegistros: function(tfEjecutar) {
		var laMedicamentos=$('#tblMedicamentosOM').bootstrapTable('getData');
		oMedicamentosOrdMedica.gnCantidadMedicamentos=laMedicamentos.length;
		oMedicamentosOrdMedica.gnNumFormulados=oMedicamentosOrdMedica.gnNumSuspendidos=oMedicamentosOrdMedica.gnNumNoFormulados=0;
		oMedicamentosOrdMedica.gnNumMedNoFor=0;
		oMedicamentosOrdMedica.glMedicamentosTieneCambios=false;

		$.each(laMedicamentos, function(lnIndex, loMedicamentos) {
			if (loMedicamentos.SEFORMULA>0){
				oMedicamentosOrdMedica.gnNumFormulados++;
			}
			if (loMedicamentos.SUSPENDER>0){
				oMedicamentosOrdMedica.gnNumSuspendidos++;
				oMedicamentosOrdMedica.gnNumFormulados++;
				oMedicamentosOrdMedica.gnNumMedNoFor++;
			}

			if (loMedicamentos.ESTDET==99){
				oMedicamentosOrdMedica.gnNumNoFormulados++;
			}

			if ((loMedicamentos.ESTDET==0 || loMedicamentos.ESTDET==99) || (loMedicamentos.SUSPENDER>0 && loMedicamentos.ESTDETORIG==14)){
				oMedicamentosOrdMedica.gnNumMedNoFor++;
			}

			if (loMedicamentos.TIPOMODIFICACIONANTIBIOTICO!='' || loMedicamentos.TIPOSUSPENSDEANTIBIOTICO!=''){
				oMedicamentosOrdMedica.gnNumMedNoFor++;
			}
		});
		if(typeof tfEjecutar==='function'){
			tfEjecutar();
		}
	},

	cambiarEstados: function() {
		var laMedicamentos=$('#tblMedicamentosOM').bootstrapTable('getData');

		$.each(laMedicamentos, function(lnIndex, loMedicamentos) {
			var lnEstado=loMedicamentos.ESTDET;
			if (loMedicamentos.SEFORMULA>0){
				if ($.inArray(lnEstado, oMedicamentosOrdMedica.gaEstadosnoformular)<=0){
					$('#tblMedicamentosOM').bootstrapTable('updateRow', {
						index: lnIndex,
						row: {
							ESTDET: 11
						}
					});
				}
			}
		});
		oMedicamentosOrdMedica.glMedicamentosTieneCambios=(oMedicamentosOrdMedica.gnNumMedNoFor!=oMedicamentosOrdMedica.gnCantidadMedicamentos) || oMedicamentosOrdMedica.gnNumSuspendidos>0 || oMedicamentosOrdMedica.gnNumFormulados>0;
	},

	validacion: function() {
		var lbValido=true;
		var lcContinua='';
		var laMedicamentos=$('#tblMedicamentosOM').bootstrapTable('getData');

		$.each(laMedicamentos, function(lnIndex, loMedicamentos) {
			if (loMedicamentos.CODIGO==''){
				oMedicamentosOrdMedica.lcMensajeError = 'Existen medicamentos sin código.';
				oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
				oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
				lbValido = false;
			}

			if (lbValido){
				if (loMedicamentos.MEDICAMENTO==''){
					oMedicamentosOrdMedica.lcMensajeError = 'Existen medicamentos sin descripción valida.';
					oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
					oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
					lbValido = false;
				}
			}

			if (lbValido){
				if (loMedicamentos.DOSIS==''){
					oMedicamentosOrdMedica.lcMensajeError = 'Existen medicamentos sin dosis.';
					oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
					oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
					lbValido = false;
				}
			}

			if (lbValido){
				if (loMedicamentos.CODUNIDADDOSIS==''){
					oMedicamentosOrdMedica.lcMensajeError = 'Existen medicamentos sin unidad de dosis.';
					oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
					oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
					lbValido = false;
				}
			}

			if (lbValido){
				if (loMedicamentos.DESCRUNIDADDOSIS==''){
					oMedicamentosOrdMedica.lcMensajeError = 'Existen medicamentos con unidad de dosis no valido.';
					oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
					oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
					lbValido = false;
				}
			}

			if (lbValido){
				if (loMedicamentos.FRECUENCIA=='' || loMedicamentos.FRECUENCIA==0){
					oMedicamentosOrdMedica.lcMensajeError = 'Existen medicamentos sin frecuencia.';
					oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
					oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
					lbValido = false;
				}
			}

			if (lbValido){
				if (loMedicamentos.SEFORMULA==1 || loMedicamentos.INMEDIATO==1 || loMedicamentos.SUSPENDER==1){
					if (loMedicamentos.CODUNIDADFRECUENCIA==''){
						oMedicamentosOrdMedica.lcMensajeError = 'Existen medicamentos sin unidad de frecuencia.';
						oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
						oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
						lbValido = false;
					}
				}
			}

			if (lbValido){
				if (loMedicamentos.SEFORMULA==1 || loMedicamentos.INMEDIATO==1 || loMedicamentos.SUSPENDER==1){
					if (loMedicamentos.DESCRUNIDADFRECUENCIA==''){
						oMedicamentosOrdMedica.lcMensajeError = 'Existen medicamentos con unidad de frecuencia no valido.';
						oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
						oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
						lbValido = false;
					}
				}
			}

			if (lbValido){
				if (loMedicamentos.VIA==''){
					oMedicamentosOrdMedica.lcMensajeError = 'Existen medicamentos sin vía de administración.';
					oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
					oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
					lbValido = false;
				}
			}

			if (lbValido){
				if (loMedicamentos.DESCRVIA==''){
					oMedicamentosOrdMedica.lcMensajeError = 'Existen medicamentos con vía de administración no valida.';
					oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
					oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
					lbValido = false;
				}
			}

			if (lbValido){
				if (loMedicamentos.ESTDET=='' || loMedicamentos.ESTDET==0){
					oMedicamentosOrdMedica.lcMensajeError = 'Existen medicamentos sin estado válido.';
					oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
					oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
					lbValido = false;
				}
			}

			if (lbValido){
				if (loMedicamentos.GRUPOCODFARMACEUTICO!='' && loMedicamentos.GRUPODESCFARMACEUTICO==''){
					oMedicamentosOrdMedica.lcMensajeError = 'Existen medicamentos con grupo farmaceútico no válido.';
					oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
					oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
					lbValido = false;
				}
			}

			if (lbValido){
				if (loMedicamentos.SEFORMULA>0 || loMedicamentos.INMEDIATO>0){
					if (loMedicamentos.CONTROLADO!=''){
						if (loMedicamentos.CONTROLADOCANTIDAD=='' || loMedicamentos.CONTROLADOCANTIDAD==0){
							lcTexto='A continuación diligencie formulación de médicamentos CONTROLADOS, seleccione el médicamento y de click sobre la opción adicionar.';

							oMedicamentosOrdMedica.lcMensajeError = lcTexto;
							oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
							oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
							lbValido = false;
						}

						if (lbValido){
							if (loMedicamentos.CONTROLADOCIE==''){
								oMedicamentosOrdMedica.lcMensajeError = 'Existen medicamentos sin díagnotico (controlado)';
								oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
								oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
								lbValido = false;
							}
						}
					}
				}
			}

			if (lbValido){
				if ((loMedicamentos.SEFORMULA>0 || loMedicamentos.INMEDIATO>0) && loMedicamentos.ESANTIBIOTICO && loMedicamentos.DIASINGRESAANTIBIOTICO==0){
					oMedicamentosOrdMedica.lcMensajeError = 'Deben indicarse los días de uso del(os) Antibiótico(s)';
					oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
					oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
					lbValido = false;
				}
			}

			if (lbValido){
				if ((loMedicamentos.SEFORMULA>0 || loMedicamentos.INMEDIATO>0 || loMedicamentos.SUSPENDER>0) && loMedicamentos.FRECUENCIA>oMedicamentosOrdMedica.gnDiasMaximoFrecuencia){
					oMedicamentosOrdMedica.lcMensajeError = 'La frecuencia del médicamento ' + loMedicamentos.MEDICAMENTO + ', excede el máximo permitido (' + oMedicamentosOrdMedica.gnDiasMaximoFrecuencia + ').';
					oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
					oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
					lbValido = false;
				}
			}

			if (lbValido){
				if (loMedicamentos.ESANTIBIOTICO && loMedicamentos.DUSOANTIBIOTICO==0 && (loMedicamentos.SEFORMULA>0 || loMedicamentos.INMEDIATO>0)){
					oMedicamentosOrdMedica.lcMensajeError = 'Debe indicarse los días de uso del Antibiótico ' + loMedicamentos.MEDICAMENTO;
					oMedicamentosOrdMedica.lcFormaError = 'FormmedicamentosOM';
					oMedicamentosOrdMedica.lcObjetoError = 'cMedicamentoOM';
					lbValido = false;
				}
			}
		});
		return lbValido;
	},

	iniciarTablaMedicamentos: function(){
		$('#tblMedicamentosOM').bootstrapTable({
			classes: 'table table-bordered table-hover table-sm table-responsive-sm',
			theadClasses: 'thead-dark',
			locale: 'es-ES',
			undefinedText: '',
			toolbar: '#toolBarLst',
			height: '400',
			sortName: 'ESTDETORIG',
			pagination: false,
			rowStyle: this.formatoColor,
			resizable: 'true',
			iconSize: 'sm',
			columns: [
			[
				{
					title: 'Frm',
					titleTooltip: 'Formulado',
					field: 'SEFORMULA',
					width: 1, widthUnit: "%",
					halign: 'center',
					align: 'center',
					rowspan: 2, valign: 'middle',
					formatter: oMedicamentosOrdMedica.formatoSeformula,
					events: oMedicamentosOrdMedica.eventoSeformula
				},
				{
					title: 'Inmed',
					titleTooltip: 'Inmediato',
					field: 'INMEDIATO',
					width: 1, widthUnit: "%",
					halign: 'center',
					align: 'center',
					rowspan: 2, valign: 'middle',
					formatter: oMedicamentosOrdMedica.formatoInmediato,
					events: oMedicamentosOrdMedica.eventoSeformula
				},
				{
					title: 'Susp',
					titleTooltip: 'Suspender',
					field: 'SUSPENDER',
					width: 1, widthUnit: "%",
					halign: 'center',
					align: 'center',
					rowspan: 2, valign: 'middle',
					formatter: oMedicamentosOrdMedica.formatoSuspender,
					events: oMedicamentosOrdMedica.eventoSeformula
				},
				{
					title: 'Código',
					field: 'CODIGO',
					width: 10, widthUnit: "%",
					halign: 'center',
					align: 'left',
					rowspan: 2, valign: 'middle',
					formatter: oMedicamentosOrdMedica.formatoCodigoMedicamento
				},
				{
					title: 'Medicamento',
					field: 'MEDICAMENTO',
					width: 25, widthUnit: "%",
					halign: 'center',
					align: 'left',
					rowspan: 2, valign: 'middle',
					formatter: oMedicamentosOrdMedica.formatoNombreMedicamento,
					events: oMedicamentosOrdMedica.eventoNombreMedicamento
				},
				{
					title: 'Dosis',
					formatter: function(tnValor, toFila){ return toFila.DOSIS+' '+toFila.DESCRUNIDADDOSIS;},
					width: 10, widthUnit: "%",
					halign: 'center',
					align: 'left',
					rowspan: 2, valign: 'middle'
				},
				{
					title: 'Frecuencia',
					formatter: function(tnValor, toFila){ return toFila.FRECUENCIA+' '+toFila.DESCRUNIDADFRECUENCIA;},
					width: 5, widthUnit: "%",
					halign: 'center',
					align: 'left',
					rowspan: 2, valign: 'middle'
				},
				{
					title: 'Vía',
					field: 'DESCRVIA',
					width: 7, widthUnit: "%",
					halign: 'center',
					align: 'left',
					rowspan: 2, valign: 'middle'
				},
				{
					title: 'Observaciones',
					field: 'OBSERVACIONES',
					width: 15, widthUnit: "%",
					halign: 'center',
					align: 'left',
					rowspan: 2, valign: 'middle'
				},
				{
					title: 'Médico',
					field: 'MEDICO',
					width: 25, widthUnit: "%",
					halign: 'center',
					align: 'left',
					rowspan: 2, valign: 'middle'
				},
				{
					title: 'Antibiótico',
					colspan: 2,
					align: 'center'
				},
				{
					title: 'Grupo Farmaceútico',
					field: 'GRUPODESCFARMACEUTICO',
					width: 10, widthUnit: "%",
					halign: 'center',
					align: 'left',
					rowspan: 2, valign: 'middle',
					formatter: function(tnValor, toFila){
						return'<a class="eventoGrupoFarma" title="'+toFila.GRUPOFARMACEUTICODET+'"><i style="color:#000000;"><b>'+toFila.GRUPODESCFARMACEUTICO+'</b></i></a>';
					}
				},
				{
					title: 'ESTADO',
					field: 'ESTDETORIG',
					rowspan: 2,
					visible:  false
				},
				{
					title: 'Acción',
					field: 'ACCION',
					width: 1, widthUnit: "%",
					halign: 'center',
					align: 'center',
					rowspan: 2, valign: 'middle',
					clickToSelect: false,
					events: this.eventoMedicamento,
					formatter: this.formatoMedicamento
				}
			],
			[
				{
					title: 'Fecha Inicio',
					titleTooltip: 'Fecha Inicio Antibiótico',
					field: 'FECINICIOANTIB',
					width: 5, widthUnit: "%",
					halign: 'center',
					align: 'center',
					formatter: function(tnValor, toFila){ return tnValor!=''?strNumAFecha(tnValor,'/'):''; }
				},
				{
					title: 'Días Uso/Indicado',
					titleTooltip: 'Días de Uso / Días Indicado Antibiótico',
					field: 'DUSOANTIBIOTICO',
					width: 5, widthUnit: "%",
					halign: 'center',
					align: 'center',
					formatter: function(tnValor, toFila){
						let lnUso = toFila.ANTIBDIAS, lnDias = toFila.DUSOANTIBIOTICO;
						return lnDias==0 ? '' : lnUso+' / '+lnDias;
					}
				},
			]
			]
		});
	},

	formatoNombreMedicamento: function (tnValor, toFila) {
		return [
			'<a class="eventoNombreMed" title="Nombre medicamento">',
			'<i></i> <b>'+toFila.MEDICAMENTO+'</b>',
			'</a>',
		].join('');
 	},

	formatoCodigoMedicamento: function (tnValor, toFila) {
		return [
			'<a>',
			'<i class="fas fa-thin '+(toFila.CONTROLADO=='CONTROLADO'? 'fa-copyright': '')+'"></i>'+' '+toFila.CODIGO+'',
			'</a>',
		].join('');
 	},

	iniciarTablaConciliacion: function(){
		oMedicamentosOrdMedica.gotableConciliacionOM.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
				theadClasses: 'thead-dark',
				locale: 'es-ES',
				undefinedText: '-',
				height: '300',
				pagination: false,
				singleSelect:'true',
			columns: [
			{
				title: 'Medicamento',
				field: 'MEDICAMENTOC',
				width: 35, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Dosis',
				field: 'DOSISC',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left',
			},
			{
				title: 'Vía administración',
				field: 'VIAADMINC',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},{
				title: 'Frecuencia',
				field: 'FRECUENCIAC',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Conducta a seguir',
				field: 'CONTINUAC',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'center'
			},
			{
				title: 'Observaciones',
				field: 'OBSERVACIONESC',
			  	width: 35, widthUnit: "%",
				halign: 'center',
				align: 'left'
			}
		  ]
		});
	},

	obtenerDatos: function() {
		var laDatos = [];
		var laMedicamentos=oMedicamentosOrdMedica.glMedicamentosTieneCambios ? $('#tblMedicamentosOM').bootstrapTable('getData') : '';

		if (laMedicamentos!=''){
			var laDatos = {Medicamentos: laMedicamentos};
		}
		return laDatos;
	}

}