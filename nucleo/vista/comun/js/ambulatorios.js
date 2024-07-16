var oAmbulatorio = {
	gotableInterconsultas : $('#tblInterconsulta'),
	gotableMedicamentos : $('#tblMedicaAmb'),
	gotableAnterioresMedicamentos : $('#tblMedicaAnteriores'),
	datosInterconsultaLista: [], datosInsumo: [], datosDietaLista: [],
	lcMensajeError: '', gcUnidadDosis: '', gnConcentracion: '', gcPresentacion: '', gcDatosMedicamento: '', lcFormaError : '',
	lcObjetoError: '', cTabError: '', gcDatosNopos : '', gcCodigoMedicamento : '',
	gnCaracteresObservacionesMedicamentos : 0, gnSeleccionPlanPaciente: 0,
	ListaMedicamentosAmb : {},
	goColorFilaAmb:  {
		'1': '#cce8fd',
		'2': '#ffcccc',
		'3': '#ffff00',
	},
	gcTipoNOPOS : '', gcTipoMiPres : '',
	gcUltMedica : {}, aMedicaNOPOS: {}, oRowedit: {},
	taObjetosAmb: {
		1: '#cMedicamentoAM',
		2: '#txtDosisAmb',
		3: '#selTipoDosisAmb',
		4: '#txtFrecuenciaAmb',
		5: '#selTipoFrecuenciaAmb',
		6: '#txtDosisdiariaAmb',
		7: '#selTipoDosisDiariaAmb',
		8: '#txtTiempoTratamientoAmb',
		9: '#selTiempoTratamientoAmb',
		10: '#txtCantidadAmb',
		11: '#txtTextoCantidadAmb',
		12: '#selTipoViaAmb',
		13: '#edtObservaAmb'
	},
	dFechaDesde: null, dFechaHasta: null,
	oDatosFecha: {
		autoclose: true,
		clearBtn: true,
		daysOfWeekHighlighted: "0,6",
		format: "yyyy-mm-dd",
		language: "es",
		todayBtn: 'linked',
		todayHighlight: true,
		toggleActive: true,
		weekStart: 1,
		// startDate: gnFechaActual,
	},
	oDatosFechaRetro: {},
	obligarIncapacidadHospitalaria: true,
	oIncapacidades: [],

	inicializar: function(){
		this.iniciarTablamedicamento();
		this.iniciarTablaInterconsulta();
		oProcedimientos.inicializar();
		oModalMedicamentoCTC.inicializar();
		this.iniciarTablaMedicamentosAnteriores();
		$('#selTipoDosisAmb,#selTipoDosisDiariaAmb').tiposMedica({tipo: "Dosis"});
		$('#selTipoFrecuenciaAmb').tiposMedica({tipo:'Frecuencia'});
		$('#selTipoViaAmb').tiposMedica({tipo:'Via'});
		oModalAlertaNopos.inicializar();
		this.validaIncapacidadHospitalaria();
		this.obtenerIncapacidades();
		this.cargarListadosAmbulatorios({cAccion:'interconsulta'}, 'Listado prioridad procedimientos', (loDatos)=>{oAmbulatorio.cargarListado(loDatos, $('#selPrioridadAtencion'))});
		this.cargarListadosAmbulatorios({cAccion:'interconsulta'}, 'Listado prioridad interconsulta', (loDatos)=>{oAmbulatorio.cargarListado(loDatos, $('#seltipoPrioridad'))});
		this.cargarListadosAmbulatorios({cAccion:'tabladieta'}, 'Listado dietas', (loDatos)=>{oAmbulatorio.cargarListado(loDatos, $('#seltipoDieta'))});
		this.cargarListadosAmbulatorios({cAccion:'interconsulta'}, 'Listado prioridad orden ambulatoria', (loDatos)=>{oAmbulatorio.cargarListado(loDatos, $('#selPrioridadAtencionOrdAmb'))});
		if ($("#selModalidadPrestacion").length>0)
			this.cargarListadosAmbulatorios({cAccion:'modalidadprest'}, 'Listado Modalidad Prestación', (loDatos)=>{oAmbulatorio.cargarListado(loDatos, $('#selModalidadPrestacion'))});
		this.cargarListadosAmbulatorios({cAccion:'incapacidad'}, 'Listados incapacidad', (loDatos)=>{
			var loClaves = {
				ORIGEN: '#selOrigenIncapacidad',
				RETROACTIVA: '#selIncapacidadRetroactiva',
			}
			$.each(loDatos, function(lnClave, loDatosInc){
				oAmbulatorio.cargarListado(loDatosInc, $(loClaves[lnClave]))
			});
			oAmbulatorio.validaIncapacidadHospitalaria();
		});

		oMedicamentos.consultaMedicamentos('cMedicamentoAM','cCodigoMedicamentoAM','cDescripcionMedicamentoAM','txtDosisAmb','AM');
		this.cargarListadosAutocompletar('Interconsultas','#buscarInterconsulta','Listado especialidades interconsultas','T');
		this.consultaCaracteresObservacionesMed();
		oDiagnosticos.consultarDiagnostico('txtCodigoCieOrdAmbR','cCodigoCieOrdAmbR','cDescripcionCieOrdAmbR','','txtFechaDesde');

		$('#buscarInterconsulta').on('keyup',function(){
			if ($("#buscarInterconsulta").val()==''){
				$("#buscarInterconsulta").removeClass("is-invalid");
				oAmbulatorio.autocompletar('#buscarInterconsulta',oAmbulatorio.datosInterconsultaLista,false);
			}
		});

		// Formulario de incapacidad
		this.dFechaDesde = Date.parse($('#txtFechaDesde').val());
		this.dFechaHasta = Date.parse($('#txtFechaHasta').val());
		this.oDatosFechaRetro = Object.assign({endDate: gnFechaActual}, this.oDatosFecha);
		$("#FormIncapacidad .fecha-incapacidad ").attr("style", "text-align: left;");
		$('#FormIncapacidad .fecha-retroactiva').attr("style", "text-align: left;").prop('readOnly', true)
		$('#FormIncapacidad #txtDiasIncapacidad').on('change', function(){
			var lnDiasIncapacidad = $('#FormIncapacidad #txtDiasIncapacidad').val();
			if(lnDiasIncapacidad>30){
				$('#FormIncapacidad .input-daterange-incapacidad input').val(gnFechaActual);
				$('#FormIncapacidad #txtDiasIncapacidad').val(0).focus();
				fnAlert('Días de incapacidad no puede ser mayor a 30, revise por favor.');
			}
			var ldFeDesde = $('#txtFechaDesde').val(),
				ldFeHasta = $('#txtFechaHasta').val();
			if (!ldFeDesde || !ldFeHasta){
				$('#FormIncapacidad #txtDiasIncapacidad').val(0);
				fnAlert('Debe indicar rango de fechas.');
				return false;
			}
			var laFecha = $('#FormIncapacidad #txtFechaDesde').val().split('-');
			var ldFecha = new Date(laFecha[0], laFecha[1]-1, laFecha[2]);
			var lcFechaFinal = strDateAFecha(sumarDiasFecha(ldFecha, lnDiasIncapacidad-1),'-');
			$('#FormIncapacidad #txtFechaHasta').val(lcFechaFinal);
			oAmbulatorio.validarIncapacidades();
		});
		$('#FormIncapacidad #txtFechaDesde').on('change', function(){
			oAmbulatorio.dFechaDesde = Date.parse($('#FormIncapacidad #txtFechaDesde').val());
			oAmbulatorio.dFechaHasta = Date.parse($('#FormIncapacidad #txtFechaHasta').val());
			if (oAmbulatorio.dFechaDesde>oAmbulatorio.dFechaHasta){
				$('#txtDiasIncapacidad').val(0);
				fnAlert('Fecha inicio de incapacidad no puede ser mayor a fecha final de incapacidad, revise por favor.');
				return false;
			}
			oAmbulatorio.calcularDiasIncapacidad();
		});
		$('#FormIncapacidad #txtFechaHasta').on('change', function(){
			oAmbulatorio.dFechaDesde = Date.parse($('#FormIncapacidad #txtFechaDesde').val());
			oAmbulatorio.dFechaHasta = Date.parse($('#FormIncapacidad #txtFechaHasta').val());
			if (oAmbulatorio.dFechaHasta<oAmbulatorio.dFechaDesde){
				$('#FormIncapacidad #txtDiasIncapacidad').val(0);
				fnAlert('Fecha final de incapacidad no puede ser menor a la fecha inicio de incapacidad, revise por favor.');
				return false;
			}
			oAmbulatorio.calcularDiasIncapacidad();
		});
		$('#FormIncapacidad .fecha-incapacidad,.fecha-retroactiva').on('change', function(){
			oAmbulatorio.validarIncapacidades();
		});
		$('#FormIncapacidad #selOrigenIncapacidad').on('change', function(){
			var lcOrigen = $(this).val();
			if (lcOrigen.length==0) {
				$('#FormIncapacidad #selCausaAtencion').val('').html('');
			} else {
				$('#FormIncapacidad #selCausaAtencion').tiposCausa({origen: lcOrigen=='02' ? 'L' : 'C'});
			}
		});
		$('#FormIncapacidad #selIncapacidadRetroactiva').on('change', function(){
			var lcIncRetr = $(this).val();
			$('#FormIncapacidad .input-group.input-daterange-retroactiva').datepicker('destroy');
			$('#FormIncapacidad .fecha-retroactiva').prop('readOnly', true).val('');
			if (lcIncRetr.length>0) {
				$('#FormIncapacidad .fecha-retroactiva').prop('readOnly', false);
				$('#FormIncapacidad .input-group.input-daterange-retroactiva').datepicker(oAmbulatorio.oDatosFechaRetro);
			}
		});
		$('#FormIncapacidad #selTipoIncapacidad').on('change', function(){
			$('#FormIncapacidad .input-daterange-incapacidad').datepicker('destroy');
			$('#FormIncapacidad .input-daterange-retroactiva').datepicker('destroy');
			$('#FormIncapacidad .ordamb-incap').attr("disabled", true).val('');
			switch ($(this).val()) {
				case 'AMB':
					$('#FormIncapacidad #selOrigenIncapacidad,#selCausaAtencion,#txtFechaDesde,#txtFechaHasta,#txtDiasIncapacidad,#txtobservacionesIncapacidad,#selIncapacidadHospitalaria').attr("disabled",false);
					$('#FormIncapacidad .input-daterange-incapacidad').datepicker(oAmbulatorio.oDatosFecha);
					$('#FormIncapacidad #selProrroga').val('N');
					break;
				case 'PRO':
					$('#FormIncapacidad #selOrigenIncapacidad,#selCausaAtencion,#txtFechaDesde,#txtFechaHasta,#txtDiasIncapacidad,#txtobservacionesIncapacidad,#selIncapacidadHospitalaria').attr("disabled",false);
					$('#FormIncapacidad .input-daterange-incapacidad').datepicker(oAmbulatorio.oDatosFecha);
					$('#FormIncapacidad #selProrroga').val('S');
					break;
				case 'RET':
					$('#FormIncapacidad #selCausaAtencion,#selIncapacidadRetroactiva,#txtFechaIniRetroactiva,#txtFechaFinRetroactiva,#txtobservacionesIncapacidad').attr("disabled",false);
					$('#FormIncapacidad #selOrigenIncapacidad').val("01").change();
					break;
			}
			if ($(this).val().length > 0 && $("#selTipoCausa").length > 0) {
				var loOpcion = $("#selTipoCausa option:selected"),
					lcOrigen = loOpcion.attr('data-origen'),
					lcValor = loOpcion.val(),
					lcDescripcion = loOpcion.text();
				if (lcOrigen=='L' && $(this).val()=='RET') {
					$('#FormIncapacidad .input-daterange-incapacidad').datepicker('destroy');
					$('#FormIncapacidad .input-daterange-retroactiva').datepicker('destroy');
					$('#FormIncapacidad .ordamb-incap').attr("disabled", true).val('');
					$("#FormIncapacidad #selTipoIncapacidad").val('').focus();
					fnAlert('El "Tipo de Causa" seleccionado en "Motivo de consulta" no permite el tipo de incapacidad Retroactiva', 'Tipo incorrecto', false, false, 'm');
				}
				$("#FormIncapacidad #selOrigenIncapacidad").val(lcOrigen=='L' ? '02' : '01');
				$("#FormIncapacidad #selCausaAtencion").html('').append($('<option>').text(lcDescripcion).val(lcValor)).val(lcValor);
				$("#FormIncapacidad #selOrigenIncapacidad,#selCausaAtencion").attr("disabled",true);
			}
		});
		$('#btnLimpiarDxRel').on('click', function(){
			$('#txtCodigoCieOrdAmbR, #cCodigoCieOrdAmbR, #cDescripcionCieOrdAmbR').val('').removeClass('is-valid');
		});

		$('#txtMedicamentoNCAmb').on('change', this.textoNoCodificado);
		$('#selTipoDosisAmb').on('change', function(){
			$('#selTipoDosisDiariaAmb').val($('#selTipoDosisAmb').val());
		});
		$('#selTipoDosisDiariaAmb').on('change', function(){
			$('#selTipoDosisAmb').val($('#selTipoDosisDiariaAmb').val());
		});

		$('#FormInsumos').validate({
			rules: {
				buscarInsumo: "required",
				idcantidadInsumo: "required",
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

		$('#FormInterconsulta').validate({
			rules: {
				buscarInterconsulta: "required",
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
		if (typeof aDatosIngreso=='object'){
			this.fnConsultaMedicamentosAnteriores();
		}

		var $laObjAmb = oAmbulatorio.taObjetosAmb;
		$('#FormInterconsulta').on('submit', function(e){e.preventDefault();});
		$('#AdicionarMedAmb').on('click', this.adicionarMedicamentos);
		$('#AdcionarInterconsulta').on('click', this.adicionarInterconsulta);
		$('#AdicionarInsumo').on('click', this.adicionarInsumo);
		$('#txtDosisAmb').on("keyup",function(){oAmbulatorio.validarTFrecuenciaAmb($laObjAmb)});
		$('#txtFrecuenciaAmb').on('keyup',function(){oAmbulatorio.validarTFrecuenciaAmb($laObjAmb)});
		$('#selTipoFrecuenciaAmb').on('change',function(){oAmbulatorio.validarTFrecuenciaAmb($laObjAmb)});
		$('#txtDosisdiariaAmb').on('keyup',function(){oAmbulatorio.calculaDosisDiaria($laObjAmb,'S')});
		$('#txtTiempoTratamientoAmb').on('keyup',function(){oAmbulatorio.calculaCantidadTotal()});
		$('#btnMedicamentosAnteriores').on('click', this.fnMedicamentosAnteriores);
		$('#btnAceptarMedAnteriores').on('click', this.traerMedicamentosAnteriores);
		$('#selPrioridadAtencionOrdAmb').on('change', this.validaPrioridadAtencion);
		$('#btnEliminarMedicamentosAmb').on('click', this.borraMedicamentos);
	},

	calcularDiasIncapacidad: function(){
		var lnDiasIncapacidad = Math.floor((oAmbulatorio.dFechaHasta - oAmbulatorio.dFechaDesde)/(1000*60*60*24)) + 1;
		if (lnDiasIncapacidad>30) {
			$('#FormIncapacidad #txtDiasIncapacidad').val("").focus();
			fnAlert('Días de incapacidad no puede ser mayor a 30, revise por favor.');
		} else {
			$('#FormIncapacidad #txtDiasIncapacidad').val(lnDiasIncapacidad);
		}
	},

	seleccionaMedicamentoAm: function(taItem){
		$('#txtMedicamentoNCAmb').val('');
		$('#chkMedicamentoNCAmbNoPos').prop("checked",false).attr("disabled",true);
		oAmbulatorio.datosMedicamento(taItem.CODIGO);
	},

	textoNoCodificado: function(){
		$('#cMedicamentoAM,#cCodigoMedicamentoAM,#cDescripcionMedicamentoAM').val('');
		$('#selTipoDosisAmb').val('7');
		$('#selTipoDosisDiariaAmb').val('7');
		$('#selTiempoTratamientoAmb').val('DIAS');
		llValidar = oAmbulatorio.gcTipoNOPOS==='S';
		$('#chkMedicamentoNCAmbNoPos').prop("checked",false).attr("disabled",!llValidar);
	},

	validaIncapacidadHospitalaria: function(){
		if (typeof aDatosIngreso=='object'){
			if (aDatosIngreso['cCodVia']=='01' || aDatosIngreso['cCodVia']=='02'){
				$("#divIncapacidadHospitalaria").hide();
				this.obligarIncapacidadHospitalaria=false;
			}
		}
	},

	asignaPlanPaciente: function(tcPlan){
		$('#selPlanOrdAmb').val(tcPlan);
		$('#selPlanPaciente').val(tcPlan);
		oAmbulatorio.ValidarPlan(tcPlan);
	},

	borraMedicamentos: function() {
		var taMedicamentoSeleccionados = oAmbulatorio.gotableMedicamentos.bootstrapTable('getSelections');
		if(taMedicamentoSeleccionados != ''){
			fnConfirm('Desea eliminar los medicamentos seleccionados?', false, false, false, 'medium', function(){
				$.each(taMedicamentoSeleccionados, function( lcKey, loTipo ) {
					lcCodigoMedicamento = loTipo['CODIGO'].trim();
					oAmbulatorio.gotableMedicamentos.bootstrapTable('remove', {
						field: 'CODIGO',
						values: [lcCodigoMedicamento]
					});
				});
				$('#cMedicamentoAM').focus();
			},'');
		}else{
			fnAlert('No existen medicamentos a eliminar, revise por favor.', '', 'fas fa-exclamation-circle','blue','medium');
			$('#cMedicamentoAM').focus();
		}
		oAmbulatorio.iniciaCamposMedicamento();
	},

	traerMedicamentosAnteriores: function(){
		taMedicamentosSeleccionados = $('#tblMedicaAnteriores').bootstrapTable('getData');
		if(taMedicamentosSeleccionados != ''){
			$.each(taMedicamentosSeleccionados, function( lcKey, loSeleccion ) {
				if (loSeleccion.SELECCION==true){
					lcCodigoAnterior = loSeleccion.CODIGO;
					var taTablaValida = oAmbulatorio.gotableMedicamentos.bootstrapTable('getData');
					var llverificaExiste = oAmbulatorio.verificaCodigoExiste(lcCodigoAnterior,taTablaValida);

					if(!llverificaExiste) {
						oAmbulatorio.eliminarMedicamento(lcCodigoAnterior);
					}
					oAmbulatorio.adicionarAnterioresSeleccionados(loSeleccion);
				}
			});
		};
	},

	adicionarAnterioresSeleccionados: function(camposRegistro) {
		if (oAmbulatorio.gcTipoNOPOS==='S'){
			lnMarcaMedicamento = camposRegistro.NOPOS==='1' ? '3': '1';
		}else{
			lnMarcaMedicamento = '1'
		}

		if (camposRegistro.CODIGO=='' || camposRegistro.DOSIS=='' || camposRegistro.TIPODCOD=='' || camposRegistro.FRECUENCIA=='' ||
			camposRegistro.TIPOCODF=='' || camposRegistro.DOSISDIA=='' || camposRegistro.TIPODCODDIA=='' || camposRegistro.TIEMPOTRATA=='' ||
			camposRegistro.TIPOTIEMTRAT=='' || camposRegistro.CANTID=='' || camposRegistro.VIACOD=='' || camposRegistro.VIA=='' ||
			camposRegistro.CANTIDADTRAT==''){
			lnMarcaMedicamento = '2';
		}

		if (oAmbulatorio.gcTipoNOPOS==='S' && lnMarcaMedicamento==='3'){
			if (camposRegistro.PRESENTANP=='' || camposRegistro.CONCENTRANP=='' || camposRegistro.UNIDADNP=='' || camposRegistro.GRUPOTNP=='' ||
				camposRegistro.TIEMPOTNP=='' || camposRegistro.EFECTO=='' || camposRegistro.EFECTOS=='' ||
				camposRegistro.PACIENTEINF=='' || camposRegistro.BIBLIOGRAFIA=='' || camposRegistro.RESUMENNP=='' || camposRegistro.RIESGOINP=='' ||
				camposRegistro.CANTIDADTRAT==''){
				lnMarcaMedicamento = '2';
			}
		}

		oAmbulatorio.gotableMedicamentos.bootstrapTable('insertRow', {
			index: 1,
			row: {
				CODIGO: camposRegistro.CODIGO,
				MEDICA: camposRegistro.DESCRIPCION,
				DOSIS: camposRegistro.DOSIS,
				TIPODCOD: camposRegistro.TIPODCOD,
				TIPOD: camposRegistro.TIPOD,
				FRECUENCIA: camposRegistro.FRECUENCIA,
				TIPOF: camposRegistro.TIPOF,
				TIPOCODF: camposRegistro.TIPOCODF,
				DOSISDIA: camposRegistro.DOSISDIA,
				TIPODCODDIA: camposRegistro.TIPODCODDIA,
				TIPODDIA: camposRegistro.TIPODDIA,
				TIEMPOTRATA: camposRegistro.TIEMPOTRATA,
				TIPOTIEMTRAT: camposRegistro.TIPOTIEMTRAT,
				TIPOCODTIEMTRAT: camposRegistro.TIPOCODTIEMTRAT,
				CANTID: camposRegistro.CANTID,
				CANTIDADTRAT: camposRegistro.CANTIDADTRAT,
				VIACOD: camposRegistro.VIACOD,
				VIA: camposRegistro.VIA,
				OBSERVA: camposRegistro.OBSERVA,
				PRESENTANP: camposRegistro.PRESENTANP,
				CONCENTRANP: camposRegistro.CONCENTRANP,
				UNIDADNP: camposRegistro.UNIDADNP,
				GRUPOTNP: camposRegistro.GRUPOTNP,
				TIEMPOTNP: camposRegistro.TIEMPOTNP,
				INVIMANP: camposRegistro.INVIMANP,
				EFECTO: camposRegistro.EFECTO,
				EFECTOS: camposRegistro.EFECTOS,
				PACIENTEINF: camposRegistro.PACIENTEINF,
				BIBLIOGRAFIA: camposRegistro.BIBLIOGRAFIA,
				CODIGOP: camposRegistro.CODIGOP,
				MEDICAP: (camposRegistro.CODIGOP!='' ? (camposRegistro.CODIGOP + ' - ' + camposRegistro.MEDICAP) : ''),
				DOSISP: camposRegistro.DOSISP,
				TIPODOSISP: camposRegistro.TIPODOSISP,
				FRECUENCIAP: camposRegistro.FRECUENCIAP,
				TFRECUENCIAP: camposRegistro.TFRECUENCIAP,
				DOSISDIAP: camposRegistro.DOSISDIAP,
				TIPODOSISDIAP: camposRegistro.TIPODOSISDIAP,
				TRATAMIENTOP: camposRegistro.TRATAMIENTOP,
				TIPOTRATAMP: camposRegistro.TIPOTRATAMP,
				CANTIDADP: camposRegistro.CANTIDADP,
				PRESENTAP: camposRegistro.PRESENTAP,
				CONCENTRAP: camposRegistro.CONCENTRAP,
				UNIDADP: camposRegistro.UNIDADP,
				RESUMENNP: camposRegistro.RESUMENNP,
				NOPOS: camposRegistro.NOPOS,
				RIESGOINP: camposRegistro.RIESGOINP,
				VIAP: camposRegistro.VIAP,
				MARCAMED: lnMarcaMedicamento
			}
		});
	},

	fnMedicamentosAnteriores: function(e){
		$('#tblMedicaAnteriores').bootstrapTable('togglePagination').bootstrapTable('uncheckAll').bootstrapTable('togglePagination');
		e.preventDefault();
		$("#divMedicamentosAnteriores").modal('show', function () {
		  $('#tblMedicaAnteriores').bootstrapTable('resetView')
		});
		oAmbulatorio.iniciaCamposMedicamento();
	},

	fnConsultaMedicamentosAnteriores: function(){
		$.ajax({
		type: "POST",
		url: 'vista-comun/ajax/ambulatorios.php',
		data: {cAccion: 'medicamentosAnteriores', lcTipoIden: aDatosIngreso['cTipId'], lnNroIden: aDatosIngreso['nNumId']},
		dataType: "json"
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					if(loTipos.TIPOS != ''){
						$.each(loTipos.TIPOS, function( lcKey, laTipo ) {
							oAmbulatorio.cargarMedicamentosAnteriores(laTipo);
						});
					};
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de medicamentos anteriores.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presenta un error al buscar de medicamentos anteriores.');
		});
	},

	cargarMedicamentosAnteriores: function(camposRegistro) {
		llSeleccion = false;

		oAmbulatorio.gotableAnterioresMedicamentos.bootstrapTable('insertRow', {
			index: 1,
			row: {
				SELECCION: llSeleccion,
				CODIGO: camposRegistro.CODIGO,
				DESCRIPCION: camposRegistro.DESCRIP,
				DOSIS: camposRegistro.DOSIS,
				TIPODCOD: camposRegistro.TIPODCOD,
				TIPOD: camposRegistro.DESTIPODOSIS,
				FRECUENCIA: camposRegistro.FRECUENCIA,
				TIPOF: camposRegistro.DESTIPOFRECUENCIA,
				TIPOCODF: camposRegistro.TIPOCODF,
				DOSISDIA: camposRegistro.DOSISDIA,
				TIPODCODDIA: camposRegistro.TIPODCODDIA,
				TIPODDIA: camposRegistro.DESTIPODOSISDIARIA,
				TIEMPOTRATA: camposRegistro.TIEMPOTRATA,
				TIPOTIEMTRAT: 'DIAS',
				TIPOCODTIEMTRAT: 'DIAS',
				CANTID: camposRegistro.CANTID,
				CANTIDADTRAT: camposRegistro.CANTIDADTRAT,
				VIACOD: camposRegistro.VIACOD,
				VIA: camposRegistro.DECRIPCIONVIACOD,
				OBSERVA: camposRegistro.OBSERVA,
				PRESENTANP: camposRegistro.PRESENTANP,
				CONCENTRANP: camposRegistro.CONCENTRANP,
				UNIDADNP: camposRegistro.UNIDADNP,
				GRUPOTNP: camposRegistro.GRUPOTNP,
				TIEMPOTNP: camposRegistro.TIEMPOTNP,
				INVIMANP: camposRegistro.INVIMANP,
				EFECTO: camposRegistro.EFECTO,
				EFECTOS: camposRegistro.EFECTOS,
				PACIENTEINF: camposRegistro.PACIENTEINF,
				BIBLIOGRAFIA: camposRegistro.BIBLIOGRAFIA,
				CODIGOP: camposRegistro.CODIGOP,
				MEDICAP: camposRegistro.MEDICAP,
				DOSISP: camposRegistro.DOSISP,
				TIPODOSISP: camposRegistro.TIPODOSISP,
				FRECUENCIAP: camposRegistro.FRECUENCIAP,
				TFRECUENCIAP: camposRegistro.TFRECUENCIAP,
				DOSISDIAP: camposRegistro.DOSISDIAP,
				TIPODOSISDIAP: camposRegistro.TIPODOSISDIAP,
				TRATAMIENTOP: camposRegistro.TRATAMIENTOP,
				TIPOTRATAMP: camposRegistro.TIPOTRATAMP,
				CANTIDADP: camposRegistro.CANTIDADP,
				PRESENTAP: camposRegistro.PRESENTAP,
				CONCENTRAP: camposRegistro.CONCENTRAP,
				UNIDADP: camposRegistro.UNIDADP,
				RESUMENNP: camposRegistro.RESUMENNP,
				NOPOS: camposRegistro.NOPOS,
				RIESGOINP: camposRegistro.RIESGOINP,
				VIAP: camposRegistro.VIAP
			}
		});
	},

	verificaCodigoExiste: function(tcCodigo,taTablaValida) {
		var llRetorno = true ;
			if(taTablaValida != ''){
				$.each(taTablaValida, function( lcKey, loTipo ) {
					if(loTipo['CODIGO']==tcCodigo){
						oAmbulatorio.indexedit = lcKey;
						llRetorno = false;
					}
				});
			};
		return llRetorno ;
	},

	eliminarMedicamento: function(tcCodigoMedicamento) {
		lcCodigoBorrar = tcCodigoMedicamento.trim();
		$('#tblMedicaAmb').bootstrapTable('remove', {
			field: 'CODIGO',
			values: [lcCodigoBorrar]
			});
	},

	fnInformacionNopos: function(tnFuncionPost){
		taCupsNp = oProcedimientos.gotableProcedimientos.bootstrapTable('getData');
		taMedicamentosNp = oAmbulatorio.gotableMedicamentos.bootstrapTable('getData');

		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/ambulatorios.php',
			data: {cAccion: 'noposRevisar', lcListadoCups: taCupsNp, lcListadoMedicamentos: taMedicamentosNp},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					oAmbulatorio.gcDatosNopos = loTipos.TIPOS;
				} else {
					fnAlert(loTipos.error);
				}
				if (typeof tnFuncionPost === 'function') {
					tnFuncionPost();
				}

			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la busqueda de NOPOS.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar NOPOS.');
		});
		return this;
	},

	verificaPlanNopos: function(){
		if ($('#selPlanOrdAmb').length > 0){
			if ($('#selPlanOrdAmb').val()!=''){
				oAmbulatorio.ValidarPlan($('#selPlanOrdAmb').val());
			}else{
				oAmbulatorio.gcTipoNOPOS='';
			}
		}
	},

	adicionarMedicamentos: function(e){
		e.preventDefault();

		if ($('#Formmedicamentos').valid()){
			var lcTextoNoCodificado='';
			var laObjeto = oAmbulatorio.taObjetosAmb ;
			var lcMedicamentoN = $("#cDescripcionMedicamentoAM").val().trim()!='' ? $("#cDescripcionMedicamentoAM").val().trim() : $("#txtMedicamentoNCAmb").val();
			var lcCodigo = $("#cCodigoMedicamentoAM").val();

			if ($('#selPlanOrdAmb').length > 0){
				if ($("#selPlanOrdAmb").val()==''){
					oAmbulatorio.gcTipoNOPOS='';
					fnAlert('Plan obligatorio, revise por favor.', oAmbulatorio.lcTitulo, false, false, false);
					return false;
				}
			}
			lcCodigo = oAmbulatorio.gcCodigoMedicamento!='' ? oAmbulatorio.gcCodigoMedicamento : lcCodigo;

			if (oAmbulatorio.gcCodigoMedicamento!=''){
				lcMedicamentoN = lcCodigo.substr(0,2)==='NC' ? ($("#txtMedicamentoNCAmb").val()).trim() : lcMedicamentoN ;
			}else{
				lcMedicamentoN = lcMedicamentoN;
			}

			if(lcCodigo===''){
				lcCodigo = '';
				lcMedicamentoN = $("#txtMedicamentoNCAmb").val();
				lcTextoNoCodificado = ($("#txtMedicamentoNCAmb").val()).trim();
				lcCodigo = oAmbulatorio.adicionarCodigo();
			}
			lcMedicamentoN = lcMedicamentoN.trim();
			lcTextoNoCodificado = lcMedicamentoN!='' ? lcMedicamentoN : lcTextoNoCodificado;

			if(lcMedicamentoN == ''){
				$("#cMedicamentoAM").focus();
				lcmensaje = 'Falta seleccionar medicamento, revise por favor.';
				fnAlert(lcmensaje, oAmbulatorio.lcTitulo, false, false, false);
				return false;
			}
			lcCodigo = lcCodigo.trim();
			var lnDosis = $("#txtDosisAmb").val();
			var lcTipoDosis = $("#selTipoDosisAmb").val();
			var lnFrecuencia = $("#txtFrecuenciaAmb").val();
			var lcTipoFrecuencia = $("#selTipoFrecuenciaAmb").val();
			var lcTipoVia = $("#selTipoViaAmb").val();
			var lnDosisDiaria = $("#txtDosisdiariaAmb").val();
			var lcTipoDosisDiaria = $("#selTipoDosisDiariaAmb").val();
			var lnTiempoTratamiento = $("#txtTiempoTratamientoAmb").val();
			var lcTipoTiempoTratamiento = $("#selTiempoTratamientoAmb").val();
			var lnCantidad = $("#txtCantidadAmb").val();
			var lcCantidadTratamiento = $("#txtTextoCantidadAmb").val().trim();
			var lcObserva = $("#edtObservaAmb").val();

			if (lcCantidadTratamiento==''){
				$("#txtTextoCantidadAmb").val('');
				$("#txtTextoCantidadAmb").removeClass("is-valid").addClass("is-invalid");
			}

			if ($("#cCodigoMedicamentoAM").val()=='' && $("#txtMedicamentoNCAmb").val()==''){
				fnAlert('Medicamento obligatorio, revise por favor.', oAmbulatorio.lcTitulo, false, false, false);
				return false;
			}

			if (lcMedicamentoN=='' || lnDosis<=0 || lcTipoDosis==0 || lnFrecuencia<=0 || lcTipoFrecuencia==0 || lcTipoVia==0
				|| lnDosisDiaria<=0 || lcTipoDosisDiaria==0 || lnTiempoTratamiento<=0 || lcTipoTiempoTratamiento==0	|| lnCantidad<=0
				|| lcCantidadTratamiento==''
			 ){
				var lcObjeto = '';
				var lcmensaje = 'Campos Obligatorios:<br>';
				lcmensaje+=lcMedicamentoN==''?'Medicamento,<br> ':'';
				lcObjeto = lcMedicamentoN=='' && lcObjeto==''?laObjeto[1]:lcObjeto;
				lcmensaje+=lnDosis<=0?'Dosis,<br> ':'';
				lcObjeto = lnDosis==0 && lcObjeto==''?laObjeto[2]:lcObjeto;
				lcmensaje+=lcTipoDosis==0?'Tipo de Dosis,<br>':'';
				lcObjeto = lcTipoDosis==0 && lcObjeto==''?laObjeto[3]:lcObjeto;
				lcmensaje+=lnFrecuencia<=0?'Frecuencia,<br>':'';
				lcObjeto = lnFrecuencia==0 && lcObjeto==''?laObjeto[4]:lcObjeto;
				lcmensaje+=lcTipoFrecuencia==0?'Tipo de Frecuencia,<br>':'';
				lcObjeto = lcTipoFrecuencia==0 && lcObjeto==''?laObjeto[5]:lcObjeto;
				lcmensaje+=lnDosisDiaria<=0?'Dosis diaria,<br> ':'';
				lcObjeto = lnDosisDiaria==0 && lcObjeto==''?laObjeto[6]:lcObjeto;
				lcmensaje+=lcTipoDosisDiaria==0?'Tipo de dosis diaria,<br>':'';
				lcObjeto = lcTipoDosisDiaria==0 && lcObjeto==''?laObjeto[7]:lcObjeto;
				lcmensaje+=lnTiempoTratamiento<=0?'Tiempo tratamiento,<br> ':'';
				lcObjeto = lnTiempoTratamiento==0 && lcObjeto==''?laObjeto[8]:lcObjeto;
				lcmensaje+=lcTipoTiempoTratamiento==0?'Tipo de Tiempo tratamiento,<br>':'';
				lcObjeto = lcTipoTiempoTratamiento==0 && lcObjeto==''?laObjeto[9]:lcObjeto;
				lcmensaje+=lnCantidad<=0?'Cantidad,<br> ':'';
				lcObjeto = lnCantidad==0 && lcObjeto==''?laObjeto[10]:lcObjeto;
				lcmensaje+=lcCantidadTratamiento==''?'Presentación cantidad,<br> ':'';
				lcObjeto = lcCantidadTratamiento=='' && lcObjeto==''?laObjeto[11]:lcObjeto;
				lcmensaje+=lcTipoVia==0?'Vía administración.<br>':'';
				lcObjeto = lcTipoVia==0 && lcObjeto==''?laObjeto[12]:lcObjeto;
				$(lcObjeto).focus();
				fnAlert(lcmensaje, oAmbulatorio.lcTitulo, false, false, false);
				return false;
			}

			if (lcCodigo.substr(0,2)=='NC' && lcTextoNoCodificado==''){
				$('#txtMedicamentoNCAmb').focus();
				fnAlert("Descripción medicamento no codificado obligatorio, revise por favor.", oAmbulatorio.lcTitulo, false, false, false);
				return false;
			}

			lcMedicamentoN = lcMedicamentoN.toUpperCase();
			var lcMedicamento = {Codigo: lcCodigo, Medicamento: lcMedicamentoN, Dosis: lnDosis, TipoDosis: lcTipoDosis, Frecuencia: lnFrecuencia, TipoFrecuencia: lcTipoFrecuencia, TipoVia: lcTipoVia, Dosisdiaria: lnDosisDiaria, TipoDosisdiaria: lcTipoDosisDiaria, TiempoTratamiento: lnTiempoTratamiento, TipoTiempoTratamiento: lcTipoTiempoTratamiento,
			Cantidad: lnCantidad, CantidadTratamiento: lcCantidadTratamiento, Observa: lcObserva};
			lcValidaCodigo = (oAmbulatorio.gcCodigoMedicamento!='' ? oAmbulatorio.gcCodigoMedicamento : lcCodigo);
			var llverifica = oAmbulatorio.verificaRegistro(lcValidaCodigo);

			if(oAmbulatorio.gcTipoNOPOS==='S'){
				if ($.inArray(lcCodigo, oAmbulatorio.aMedicaNOPOS)>=0 || (lcCodigo.substr(0,2)=='NC' && $("#chkMedicamentoNCAmbNoPos").prop("checked")))
				{
					laMedicamento = [];
					if(!llverifica){
						if(oAmbulatorio.oRowedit.MARCAMED == '3'){
							laMedicamento = oAmbulatorio.oRowedit ;
						}else{
							llverifica = !llverifica;
						}
					}
					oModalMedicamentoCTC.iniciaModalMedicamentoCTC(laMedicamento, lcMedicamento, llverifica);
					oAmbulatorio.gcUltMedica = lcMedicamento;
				}else{
					oAmbulatorio.insertarMedicamento(lcCodigo, lcMedicamento, false);
				}
			}else{
				oAmbulatorio.insertarMedicamento(lcValidaCodigo, lcMedicamento, false);
			}
		}
	},

	adicionarCodigo: function() {
		var lcCodigo = 'NC'+((~~(Math.random() * 1000)).toString()).padStart(3, "0");
		return lcCodigo;
	},

	verificaRegistro: function(tcMedicamento) {
		oAmbulatorio.verificaPlanNopos();
		var TablaMedica = oAmbulatorio.gotableMedicamentos.bootstrapTable('getData');
		var llRetorno = true;
		if(TablaMedica != ''){
			$.each(TablaMedica, function( lcKey, loElemento ) {
				if(loElemento['CODIGO']==tcMedicamento){
					oAmbulatorio.indexedit = lcKey;
					oAmbulatorio.oRowedit = loElemento;
					llRetorno = false;
					return llRetorno;
				}
			});
		};
		return llRetorno;
	},

	adicionarMedicamentoAmb: function(camposRegistro) {
		lcMarcaMedicamento = camposRegistro.NoPos==='1' ? '3': '1';

		oAmbulatorio.gotableMedicamentos.bootstrapTable('insertRow', {
			index: 1,
			row: {
				CODIGO: camposRegistro.Codigo,
				MEDICA: camposRegistro.Medicamento,
				DOSIS: camposRegistro.Dosis,
				TIPODCOD: camposRegistro.TipoDosis,
				TIPOD: $("#selTipoDosisAmb option[value="+camposRegistro.TipoDosis+"]").text(),
				FRECUENCIA: camposRegistro.Frecuencia,
				TIPOF: $("#selTipoFrecuenciaAmb option[value="+camposRegistro.TipoFrecuencia+"]").text(),
				TIPOCODF: camposRegistro.TipoFrecuencia,
				DOSISDIA: camposRegistro.Dosisdiaria,
				TIPODCODDIA: camposRegistro.TipoDosisdiaria,
				TIPODDIA: $("#selTipoDosisDiariaAmb option[value="+camposRegistro.TipoDosisdiaria+"]").text(),
				TIEMPOTRATA: camposRegistro.TiempoTratamiento,
				TIPOTIEMTRAT: camposRegistro.TipoTiempoTratamiento,
				TIPOCODTIEMTRAT: camposRegistro.TipoTiempoTratamiento,
				CANTID: camposRegistro.Cantidad,
				CANTIDADTRAT: camposRegistro.CantidadTratamiento,
				VIACOD: camposRegistro.TipoVia,
				VIA: $("#selTipoViaAmb option[value="+camposRegistro.TipoVia+"]").text(),
				OBSERVA: camposRegistro.Observa,
				MARCAMED: lcMarcaMedicamento,
				PRESENTANP: camposRegistro.PresentaNP===undefined?'':camposRegistro.PresentaNP,
				CONCENTRANP: camposRegistro.ConcentraNP===undefined?'':camposRegistro.ConcentraNP,
				UNIDADNP: camposRegistro.UnidadNP===undefined?'':camposRegistro.UnidadNP,
				GRUPOTNP: camposRegistro.GrupoTNP===undefined?'':camposRegistro.GrupoTNP,
				TIEMPOTNP: camposRegistro.TiempoRNP===undefined?'':camposRegistro.TiempoRNP,
				RIESGOINP: camposRegistro.RiesgoINP===undefined?'':camposRegistro.RiesgoINP,
				INVIMANP: camposRegistro.InvimaNP===undefined?'':camposRegistro.InvimaNP,
				RESUMENNP: camposRegistro.ResumenNP===undefined?'':camposRegistro.ResumenNP,
				CODIGOP: camposRegistro.CodigoP===undefined?'':camposRegistro.CodigoP,
				MEDICAP: camposRegistro.MedicaP===undefined?'':camposRegistro.MedicaP,
				MEDICAPOS: camposRegistro.MedicaPOS===undefined?'':camposRegistro.MedicaPOS,
				PRESENTAP: camposRegistro.PresentaP===undefined?'':camposRegistro.PresentaP,
				CONCENTRAP: camposRegistro.ConcentraP===undefined?'':camposRegistro.ConcentraP,
				UNIDADP: camposRegistro.UnidadP===undefined?'':camposRegistro.UnidadP,
				DOSISP: camposRegistro.DosisP===undefined?'':camposRegistro.DosisP,
				TIPODOSISP: camposRegistro.TDosisP===undefined?'':camposRegistro.TDosisP,
				FRECUENCIAP: camposRegistro.FrecuenciaP===undefined?'':camposRegistro.FrecuenciaP,
				TFRECUENCIAP: camposRegistro.TFrecuenciaP===undefined?'':camposRegistro.TFrecuenciaP,
				DOSISDIAP: camposRegistro.DosisDP===undefined?'':camposRegistro.DosisDP,
				TIPODOSISDIAP: camposRegistro.TDosisDP===undefined?'':camposRegistro.TDosisDP,
				TRATAMIENTOP: camposRegistro.TratamientoP===undefined?'':camposRegistro.TratamientoP,
				TIPOTRATAMP: camposRegistro.TTratamientoP===undefined?'':camposRegistro.TTratamientoP,
				CANTIDADP: camposRegistro.CantidadP===undefined?'':camposRegistro.CantidadP,
				TIPOCANTP: camposRegistro.TCantidadP===undefined?'':camposRegistro.TCantidadP,
				VIAP: camposRegistro.ViaP===undefined?'':camposRegistro.ViaP,
				EFECTO: camposRegistro.Efecto===undefined?'':camposRegistro.Efecto,
				EFECTOS: camposRegistro.EfectoS===undefined?'':camposRegistro.EfectoS,
				BIBLIOGRAFIA: camposRegistro.Bibliografia===undefined?'':camposRegistro.Bibliografia,
				PACIENTEINF: camposRegistro.PacienteInf===undefined?'':camposRegistro.PacienteInf,
				NOPOS: camposRegistro.NoPos===undefined?'0':camposRegistro.NoPos
			}
		});
		oAmbulatorio.iniciaCamposMedicamento();
	},

	cargarDatosConcialicionIngreso: function() {
		$.each(oConciliacion.aDatosConciliacionInicial,function(lckey, loDatosMedicamentos){
			if (loDatosMedicamentos.CONTINUA!='Suspende'){
				oAmbulatorio.consultarConcialicionIngreso(loDatosMedicamentos);
			}
		});
	},

	consultarConcialicionIngreso: function(taDatosMedicamentos) {
		lcCodigoMedicamento = taDatosMedicamentos.CODIGO;
		oAmbulatorio.obtieneParametroMedicamento(lcCodigoMedicamento, function() {
			oAmbulatorio.adicionarDatosConciliacion(taDatosMedicamentos);
		});
	},

	obtieneParametroMedicamento: function(tcCodigoMedicamento, tfPost) {
		tcCodigoMedicamento=tcCodigoMedicamento.split('-')[0],

		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/ambulatorios.php',
			data: {cAccion: 'valoresMedicamento', lcCodigoConsumo: tcCodigoMedicamento},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					oAmbulatorio.gcPresentacion = loTipos.TIPOS['Presentacion'];
					oAmbulatorio.gcUnidadDosis = loTipos.TIPOS['Unidad'];
					oAmbulatorio.gnConcentracion = loTipos.TIPOS['Concentracion'];

					if (typeof tfPost == 'function') {
						tfPost();
					}
				} else {
					alert(loTipos.error + ' ', "warning");
				}

			} catch(err) {
				alert('No se pudo realizar la busqueda parámetros medicamentos.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			alert('Se presentó un error al buscar obtiene parámetros medicamentos.', "danger");
		});
	},

	adicionarDatosConciliacion: function(camposRegistro) {
		lnDosisDiaTotal = 0;
		lcCodigoMedicamento = camposRegistro.CODIGO;

		if (lcCodigoMedicamento.substr(0,2)==='NC'){
			lcDescMedicamento = camposRegistro.MEDICA.substr(0,2)==='NC' ? camposRegistro.MEDICA.substr(8,50) : camposRegistro.MEDICA;
		}else{
			lcDescMedicamento = camposRegistro.MEDICA;
		}
		lcDescMedicamento = lcDescMedicamento.trim();

		var tablaActualMedicamentos = oAmbulatorio.gotableMedicamentos.bootstrapTable('getData');
		if(tablaActualMedicamentos != ''){
			$.each(tablaActualMedicamentos, function( lcKey, loTipo ) {
				if(loTipo['CODIGO']==lcCodigoMedicamento){
					oAmbulatorio.gotableMedicamentos.bootstrapTable('remove', {
						field: 'CODIGO',
						values: loTipo['CODIGO']
					});
					return false;
				}
			});
		};

		laDatosDosisDiaria = {
			1: '',
			2: camposRegistro.DOSIS,
			3: '',
			4: camposRegistro.FRECUENCIA,
			5: camposRegistro.TIPOCODF,
			6: '',
			7: '',
			8: '',
			9: '',
			10: '',
			11: '',
			12: '',
			13: ''
		};
		lnDosisDiaTotal = oAmbulatorio.calculaDosisDiaria(laDatosDosisDiaria,'A');
		oAmbulatorio.gotableMedicamentos.bootstrapTable('insertRow', {
			index: 1,
			row: {
				CODIGO: lcCodigoMedicamento,
				MEDICA: lcDescMedicamento,
				DOSIS: camposRegistro.DOSIS,
				TIPODCOD: camposRegistro.TIPODCOD,
				TIPOD: camposRegistro.TIPOD,
				FRECUENCIA: camposRegistro.FRECUENCIA,
				TIPOF: camposRegistro.TIPOF,
				TIPOCODF: camposRegistro.TIPOCODF,
				DOSISDIA: lnDosisDiaTotal,
				TIPODCODDIA: camposRegistro.TIPODCOD,
				TIPODDIA: camposRegistro.TIPOD,
				TIEMPOTRATA:'',
				TIPOTIEMTRAT:'DIAS',
				TIPOCODTIEMTRAT:'DIAS',
				CANTID:'',
				CANTIDADTRAT: oAmbulatorio.gcPresentacion,
				VIACOD: camposRegistro.VIACOD,
				VIA: camposRegistro.VIA,
				OBSERVA: camposRegistro.OBSERVA,
				MARCAMED: '2',
				EDITAR: '',
				BORRAR: '',
				PRESENTANP: '',
				CONCENTRANP: '',
				UNIDADNP: '',
				GRUPOTNP: '',
				TIEMPOTNP: '',
				RIESGOINP: '',
				INVIMANP: '',
				RESUMENNP: '',
				CODIGOP: '',
				MEDICAP: '',
				MEDICAPOS: '',
				PRESENTAP: '',
				CONCENTRAP: '',
				UNIDADP: '',
				DOSISP: '',
				TIPODOSISP: '',
				FRECUENCIAP: '',
				TFRECUENCIAP: '',
				DOSISDIAP: '',
				TIPODOSISDIAP: '',
				TRATAMIENTOP: '',
				TIPOTRATAMP: '',
				CANTIDADP: '',
				TIPOCANTP: '',
				VIAP: '',
				EFECTO: '',
				EFECTOS: '',
				BIBLIOGRAFIA: '',
				PACIENTEINF: ''
			}
		});
		oAmbulatorio.gcPresentacion = oAmbulatorio.gcUnidadDosis = oAmbulatorio.gnConcentracion = '';
	},

	eliminarConciliacion: function(tcCodigoMedicamento) {
		lcCodigoBorrar = tcCodigoMedicamento.trim();
		$('#tblMedicaAmb').bootstrapTable('remove', {
			field: 'CODIGO',
			values: [lcCodigoBorrar]
			});
	},

	adicionaUltimosMedicamentos: function(taUltimosMedicamentos) {
		if(taUltimosMedicamentos != ''){
			$.each(taUltimosMedicamentos, function( lcKey, loTipo ) {
				oAmbulatorio.adicionarMedicamentoUltimo(loTipo);
			});
		};
	},

	adicionarMedicamentoUltimo: function(camposRegistro) {
		oAmbulatorio.gotableMedicamentos.bootstrapTable('insertRow', {
			index: 1,
			row: {
				CODIGO: camposRegistro.CODIGO,
				MEDICA: camposRegistro.DESCRIPCION,
				DOSIS: camposRegistro.DOSIS,
				TIPODCOD: camposRegistro.UNIDAD_DOSIS,
				TIPOD: $("#selTipoDosisAmb option[value="+camposRegistro.UNIDAD_DOSIS+"]").text(),
				FRECUENCIA: camposRegistro.FRECUENCIA,
				TIPOF: $("#selTipoFrecuenciaAmb option[value="+camposRegistro.UNIDAD_FRECUENCIA+"]").text(),
				TIPOCODF: camposRegistro.UNIDAD_FRECUENCIA,
				DOSISDIA: '',
				TIPODCODDIA: '',
				TIPODDIA: '',
				TIEMPOTRATA: '',
				TIPOTIEMTRAT: 'DIAS',
				TIPOCODTIEMTRAT: 'DIAS',
				CANTID: 0,
				CANTIDADTRAT: '',
				VIACOD: '',
				VIA: '',
				OBSERVA: '',
				MARCAMED: '1'
			}
		});
		oAmbulatorio.oRowedit = [];
	},

	iniciaCamposMedicamento: function() {
		$("#cMedicamentoAM,#txtMedicamentoNCAmb,#txtDosisAmb,#selTipoDosisAmb,#txtFrecuenciaAmb,#selTipoFrecuenciaAmb,#selTipoViaAmb").removeClass("is-valid").removeClass("is-invalid");
		$("#cCodigoMedicamentoAM,#cDescripcionMedicamentoAM").removeClass("is-valid").removeClass("is-invalid");
		$("#selTipoDosisDiariaAmb,#txtDosisdiariaAmb,#txtTiempoTratamientoAmb,#selTiempoTratamientoAmb,#txtCantidadAmb,#txtTextoCantidadAmb,#edtObservaAmb").removeClass("is-valid").removeClass("is-invalid");
		$("#cCodigoMedicamentoAM,#cDescripcionMedicamentoAM").val('');
		$("#txtMedicamentoNCAmb").val('');
		$('#chkMedicamentoNCAmbNoPos').prop("checked",false).attr("disabled",true);
		$("#txtDosisAmb,#selTipoDosisAmb,#txtFrecuenciaAmb,#selTipoFrecuenciaAmb,#selTipoViaAmb,#selTipoDosisDiariaAmb").val('');
		$("#txtDosisdiariaAmb,#txtTiempoTratamientoAmb,#selTiempoTratamientoAmb,#txtCantidadAmb,#txtTextoCantidadAmb,#edtObservaAmb").val('');
		oAmbulatorio.gcCodigoMedicamento = '';
		$('#cMedicamentoAM').focus();
	},

	editarMedicamentoAmb: function(taFila) {
		oAmbulatorio.gcCodigoMedicamento = (taFila.CODIGO);
		var lcTextoMedicamento = taFila.MEDICA;

		if (taFila.CODIGO.substr(0,2)=='NC'){
			$("#cMedicamentoAM,#cCodigoMedicamentoAM,#cDescripcionMedicamentoAM").val('');
			$("#txtMedicamentoNCAmb").val(lcTextoMedicamento);
			llValidar = oAmbulatorio.gcTipoNOPOS==='S';
			$("#chkMedicamentoNCAmbNoPos").prop("checked",taFila.NOPOS=="1").attr("disabled",!llValidar);
		}else{
			lcMedicamentoCodificado = oAmbulatorio.gcCodigoMedicamento + ' - ' + lcTextoMedicamento;
			$("#cCodigoMedicamentoAM").val(oAmbulatorio.gcCodigoMedicamento);
			$("#cDescripcionMedicamentoAM").val(lcTextoMedicamento);
			$("#txtMedicamentoNCAmb").val('');
			$("#chkMedicamentoNCAmbNoPos").prop("checked",false).attr("disabled",true);
		}
		$("#txtDosisAmb").val(taFila.DOSIS);
		$("#selTipoDosisAmb").val(taFila.TIPODCOD);
		$("#txtFrecuenciaAmb").val(taFila.FRECUENCIA);
		$("#selTipoFrecuenciaAmb").val(taFila.TIPOCODF);
		$("#selTipoViaAmb").val(taFila.VIACOD);
		$("#txtDosisdiariaAmb").val(taFila.DOSISDIA);
		$("#selTipoDosisDiariaAmb").val(taFila.TIPODCODDIA);
		$("#txtTiempoTratamientoAmb").val(taFila.TIEMPOTRATA);
		$("#selTiempoTratamientoAmb").val(taFila.TIPOCODTIEMTRAT);
		$("#txtCantidadAmb").val(taFila.CANTID);
		$("#txtTextoCantidadAmb").val(taFila.CANTIDADTRAT);
		$("#edtObservaAmb").val(taFila.OBSERVA);
		$("#txtDosisAmb").focus();
	},

	modificarMedicamentoAmb: function(camposRegistro) {
		lcMarcaMedicamento = camposRegistro.NoPos==='1' ? '3': '1';

		oAmbulatorio.gotableMedicamentos.bootstrapTable('updateRow', {
			index: oAmbulatorio.indexedit,
			row: {
				CODIGO: camposRegistro.Codigo,
				MEDICA: camposRegistro.Medicamento,
				DOSIS: camposRegistro.Dosis,
				TIPODCOD: camposRegistro.TipoDosis,
				TIPOD: $("#selTipoDosisAmb option[value="+camposRegistro.TipoDosis+"]").text(),
				FRECUENCIA: camposRegistro.Frecuencia,
				TIPOF: $("#selTipoFrecuenciaAmb option[value="+camposRegistro.TipoFrecuencia+"]").text(),
				TIPOCODF: camposRegistro.TipoFrecuencia,
				VIACOD: camposRegistro.TipoVia,
				VIA: $("#selTipoViaAmb option[value="+camposRegistro.TipoVia+"]").text(),
				DOSISDIA: camposRegistro.Dosisdiaria,
				TIPODCODDIA: camposRegistro.TipoDosisdiaria,
				TIPODDIA: $("#selTipoDosisDiariaAmb option[value="+camposRegistro.TipoDosisdiaria+"]").text(),
				TIEMPOTRATA: camposRegistro.TiempoTratamiento,
				TIPOTIEMTRAT: camposRegistro.TipoTiempoTratamiento,
				TIPOCODTIEMTRAT: camposRegistro.TipoTiempoTratamiento,
				CANTID: camposRegistro.Cantidad,
				CANTIDADTRAT: camposRegistro.CantidadTratamiento,
				OBSERVA: camposRegistro.Observa,
				MARCAMED: lcMarcaMedicamento,
				EDITAR: '',
				BORRAR: '',
				PRESENTANP: camposRegistro.PresentaNP===undefined?'':camposRegistro.PresentaNP,
				CONCENTRANP: camposRegistro.ConcentraNP===undefined?'':camposRegistro.ConcentraNP,
				UNIDADNP: camposRegistro.UnidadNP===undefined?'':camposRegistro.UnidadNP,
				GRUPOTNP: camposRegistro.GrupoTNP===undefined?'':camposRegistro.GrupoTNP,
				TIEMPOTNP: camposRegistro.TiempoRNP===undefined?'':camposRegistro.TiempoRNP,
				RIESGOINP: camposRegistro.RiesgoINP===undefined?'':camposRegistro.RiesgoINP,
				INVIMANP: camposRegistro.InvimaNP===undefined?'':camposRegistro.InvimaNP,
				RESUMENNP: camposRegistro.ResumenNP===undefined?'':camposRegistro.ResumenNP,
				CODIGOP: camposRegistro.CodigoP===undefined?'':camposRegistro.CodigoP,
				MEDICAP: camposRegistro.MedicaP===undefined?'':camposRegistro.MedicaP,
				MEDICAPOS: camposRegistro.MedicaPOS===undefined?'':camposRegistro.MedicaPOS,
				PRESENTAP: camposRegistro.PresentaP===undefined?'':camposRegistro.PresentaP,
				CONCENTRAP: camposRegistro.ConcentraP===undefined?'':camposRegistro.ConcentraP,
				UNIDADP: camposRegistro.UnidadP===undefined?'':camposRegistro.UnidadP,
				DOSISP: camposRegistro.DosisP===undefined?'':camposRegistro.DosisP,
				TIPODOSISP: camposRegistro.TDosisP===undefined?'':camposRegistro.TDosisP,
				FRECUENCIAP: camposRegistro.FrecuenciaP===undefined?'':camposRegistro.FrecuenciaP,
				TFRECUENCIAP: camposRegistro.TFrecuenciaP===undefined?'':camposRegistro.TFrecuenciaP,
				DOSISDIAP: camposRegistro.DosisDP===undefined?'':camposRegistro.DosisDP,
				TIPODOSISDIAP: camposRegistro.TDosisDP===undefined?'':camposRegistro.TDosisDP,
				TRATAMIENTOP: camposRegistro.TratamientoP===undefined?'':camposRegistro.TratamientoP,
				TIPOTRATAMP: camposRegistro.TTratamientoP===undefined?'':camposRegistro.TTratamientoP,
				CANTIDADP: camposRegistro.CantidadP===undefined?'':camposRegistro.CantidadP,
				TIPOCANTP: camposRegistro.TCantidadP===undefined?'':camposRegistro.TCantidadP,
				VIAP: camposRegistro.ViaP===undefined?'':camposRegistro.ViaP,
				EFECTO: camposRegistro.Efecto===undefined?'':camposRegistro.Efecto,
				EFECTOS: camposRegistro.EfectoS===undefined?'':camposRegistro.EfectoS,
				BIBLIOGRAFIA: camposRegistro.Bibliografia===undefined?'':camposRegistro.Bibliografia,
				PACIENTEINF: camposRegistro.PacienteInf===undefined?'':camposRegistro.PacienteInf,
				NOPOS: camposRegistro.NoPos===undefined?'0':camposRegistro.NoPos
			}
		 });
		oAmbulatorio.iniciaCamposMedicamento();
	},

	cargarListadosAutocompletar: function(tcTipo,tcCampoUbica,tcMensaje,tcFiltro) {
		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/Autocompletar.php",
			data: {
				tipoDato: tcTipo,
				otros: {filtro: tcFiltro},
			},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					var loFunction = false;
					if (tcTipo=='Interconsultas'){
						oAmbulatorio.datosInterconsultaLista = toDatos.datos;
						lcListado = oAmbulatorio.datosInterconsultaLista;
					}
					oAmbulatorio.autocompletar(tcCampoUbica,lcListado,loFunction);
				} else {
					fnAlert(toDatos.Error);
				}
			}  catch(err) {
				fnAlert('No se pudo realizar la busqueda de ' + tcMensaje +'.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar tipos de de ' + tcMensaje +'.');
		});
	},

	datosMedicamento: function(tcCodigoMedicamento){
		var tcCodigoMedicamento = tcCodigoMedicamento.substr(0,10).trim();
		$("#txtTextoCantidadAmb").val('');
		$("#selTipoDosisAmb").val('');
		$("#selTipoDosisDiariaAmb").val('');
		$("#selTiempoTratamientoAmb").val('DIAS');
		tcCodigoMedicamento=tcCodigoMedicamento.split('-')[0],

		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/ambulatorios.php',
			data: {cAccion: 'valoresMedicamento', lcCodigoConsumo: tcCodigoMedicamento},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){

					$("#selTipoDosisAmb").val(loTipos.TIPOS['Unidaddosis']);
					$("#selTipoDosisDiariaAmb").val(loTipos.TIPOS['Unidaddosis']);
					$("#txtTextoCantidadAmb").val(loTipos.TIPOS['Presentacion']);
					oAmbulatorio.gcPresentacion = loTipos.TIPOS['Presentacion']
					oAmbulatorio.gcUnidadDosis = loTipos.TIPOS['Unidad'];
					oAmbulatorio.gnConcentracion = loTipos.TIPOS['Concentracion'];
				} else {
					alert(loTipos.error + ' ', "warning");
				}

			} catch(err) {
				alert('No se pudo realizar la busqueda de datos medicamentos.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			alert('Se presentó un error al buscar de datos medicamentos.', "danger");
		});
		return this;
	},

	inicializaCampos: function() {
		$("#buscarInterconsulta").val('');
		$("#motivoInterconsulta").val('');
		$("#buscarInsumo").val('');
		$("#idcantidadInsumo").val(0);
		$("#txtobservacionInsumo").val('');
		oAmbulatorio.quitarClases();
	},

	quitarClases: function() {
		$("#FormInsumos input, #FormInsumos select, #FormInsumos textarea").removeClass("is-valid");
		$("#FormInterconsulta input, #FormInterconsulta select, #FormInterconsulta textarea").removeClass("is-valid");
		$("#FormInterconsulta input").removeClass("is-invalid");
	},

	adicionarInterconsulta: function(e){
		e.preventDefault();
		var loFunction = false;
		if ($('#FormInterconsulta').valid()){
			var lcbuscarcodigoInterconsulta = $("#buscarInterconsulta").val();
			var lcTextoInterconsulta = lcbuscarcodigoInterconsulta.split('-');
			$("#codigoInterconsulta").val(lcTextoInterconsulta[0]);
			$("#descripcionInterconsulta").val(lcTextoInterconsulta[1]);
			var lcCodigoInterconsulta = $("#codigoInterconsulta").val();
			var lcDescripcionInterconsulta = $("#descripcionInterconsulta").val().trim();
			var lcMotivoInterconsulta = $("#motivoInterconsulta").val();

			if (lcDescripcionInterconsulta==''){
				oAmbulatorio.autocompletar('#buscarInterconsulta',oAmbulatorio.datosInterconsultaLista,loFunction);
				$('#buscarInterconsulta').focus();
				fnAlert('Interconsulta no valida, revise por favor.', '', false, false, false);
				return false;
			}

			var lcInterconsultaValidar = ($("#buscarInterconsulta").val());
			if(lcInterconsultaValidar != ''){
				llAdicionar = oAmbulatorio.validarModificacionManual(oAmbulatorio.datosInterconsultaLista[$.trim(lcInterconsultaValidar)]);
				if(!llAdicionar){
					$("#FormInterconsulta input, #FormInterconsulta select, #FormInterconsulta textarea").removeClass("is-valid");
					$("#buscarInterconsulta,#codigoInterconsulta,#descripcionInterconsulta,#motivoInterconsulta").val("");
					oAmbulatorio.autocompletar('#buscarInterconsulta',oAmbulatorio.datosInterconsultaLista,loFunction);
					$('#buscarInterconsulta').focus();
					lcmensaje = 'Interconsulta modificada manualmente, revise por favor.';
					fnAlert(lcmensaje, oAmbulatorio.lcTitulo, false, false, 'medium');
					return
				}
			}
			lcCodigoInterconsulta = lcCodigoInterconsulta.trim();
			var lcInterconsultas = {lcCodigoInterconsulta: lcCodigoInterconsulta, lcDescripcionInterconsulta: lcDescripcionInterconsulta, lcMotivoInterconsulta: lcMotivoInterconsulta};

			if (lcCodigoInterconsulta==''){
				oAmbulatorio.autocompletar('#buscarInterconsulta',oAmbulatorio.datosInterconsultaLista,loFunction);
				$('#buscarInterconsulta').focus();
				fnAlert('Campos obligatorios, revise por favor.', '', false, false, 'medium');
				return false;
			}
			var taTablaInterconsulta = $('#tblInterconsulta').bootstrapTable('getData');
			var llverificaExiste = oAmbulatorio.verificaCodigoExiste(lcCodigoInterconsulta,taTablaInterconsulta);
			if(llverificaExiste) {
				oAmbulatorio.adicionarFilaInterconsulta(lcInterconsultas);
				oAmbulatorio.inicializaCampos();
			}
			else{
				fnConfirm('Interconsulta ya ingresada, desea modificarla?', oAmbulatorio.lcTitulo, false, false, false, function(){
				oAmbulatorio.modificarInterconsulta(lcInterconsultas);});
			}
			$('#buscarInterconsulta').focus();
		} else {
			oAmbulatorio.autocompletar('#buscarInterconsulta',oAmbulatorio.datosInterconsultaLista,loFunction);
		}
	},

	adicionarFilaInterconsulta: function(camposFilaInterconsulta){
		var rows = []
			rows.push({
			CODIGO: camposFilaInterconsulta.lcCodigoInterconsulta,
			SERVICIO: camposFilaInterconsulta.lcDescripcionInterconsulta,
			OBSERVACION: camposFilaInterconsulta.lcMotivoInterconsulta,
			BORRAR: '',
		})
		$('#tblInterconsulta').bootstrapTable('append', rows);
	},

	validarModificacionManual: function(taListadoValidar) {
		if(taListadoValidar !=''){
			var lnidx = taListadoValidar;
			if(lnidx===undefined){
				return false
			}
			return true
		}
	},

	modificarInterconsulta: function(camposInterconsulta) {
		$('#tblInterconsulta').bootstrapTable('updateRow', {
			index: oAmbulatorio.indexedit,
			row: {
				CODIGO: camposInterconsulta.lcCodigoInterconsulta.trim(),
				SERVICIO: camposInterconsulta.lcDescripcionInterconsulta,
				OBSERVACION: camposInterconsulta.lcMotivoInterconsulta,
				BORRAR: '',
			}
		 });
		oAmbulatorio.inicializaCampos();
	},

	cargarListadosAmbulatorios: function(toDatosEnviar, tcMensaje, tfFuncionPost) {
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/ambulatorios.php',
			data: toDatosEnviar,
			dataType: "json",
		})
		.done(function(loRespuesta) {
			try {
				if (loRespuesta.error == ''){
					if (typeof tfFuncionPost == 'function') tfFuncionPost(loRespuesta.TIPOS);
				} else {
					alert(loRespuesta.error + ' ', "warning");
				}
			} catch(err) {
				alert('No se pudo realizar la busqueda de ' + tcMensaje +'.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			alert('Se presentó un error al buscar ' + tcMensaje +'.', "danger");
		});
		return this;
	},

	cargarListado: function(toOpciones, toSelect){
		$.each(toOpciones, function(lcKey, loTipo) {
			toSelect.append('<option value="' + lcKey + '">' + loTipo + '</option>');
		});
	},

	autocompletar: function(tcCampoListado, tcSource, tcFuncionSelecciona) {
		$(tcCampoListado).autocomplete({
			source: tcSource,
			maximumItems: 30,
			highlightClass: 'text-danger',
			onSelectItem: tcFuncionSelecciona
		});
	},

	iniciarTablaInterconsulta: function(){
		$('#tblInterconsulta').bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light', // 'thead-dark' 'thead-light'
			locale: 'es-ES',
			undefinedText: 'N/A',
			toolbar: '#toolBarLst',
			height: '400',
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
			singleSelect:'true',
			columns: [
			{
			  title: 'SERVICIO',
			  field: 'SERVICIO',
			  halign: 'center',
			  valign: 'middle',
			  sortable: true
			},
			{
			  title: 'OBSERVACION',
			  field: 'OBSERVACION',
			  halign: 'center',
			  valign: 'middle',
			  sortable: true
			},

			{
			  field: 'BORRAR',
			  title: 'Acción',
			  align: 'center',
			  clickToSelect: false,
			  events: this.eventoInterconsulta,
			  formatter: this.formatoBorrarElemento
			}
		  ]
		});
	},

	iniciarTablamedicamento: function()
	{
		$('#tblMedicaAmb').bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			locale: 'es-ES',
			undefinedText: 'N/A',
			toolbar: '#toolBarLst',
			height: '400',
			pagination: false,
			rowStyle: this.formatoColorFilaAmb,
			iconSize: 'sm',
			columns: [
			{
			title: '',
			field: 'SELECCION',
			checkbox: 'false',
			width: 5, widthUnit: "%",
			halign: 'center',
			align: 'center'
			},
			{
				title: 'Medicamento',
				field: 'MEDICA',
				halign: 'center',
				valign: 'middle',
				sortable: true,
				width: 180
			},
			{
				title: 'Dosis diaria',
				field: 'DOSISDIA',
				halign: 'center',
				valign: 'middle',
				formatter: this.formatoDosisDiaria,
				width: 50
			},
			{
				title: 'Tiempo',
				field: 'TIEMPOTRATA',
				halign: 'center',
				valign: 'middle',
				formatter: this.formatoTiempoTratamiento,
				width: 50
			},
			{
				title: 'Cantidad',
				field: 'CANTID',
				halign: 'center',
				valign: 'middle',
				formatter: this.formatoCantidadTotal,
				width: 50
			},
			{
				title: 'Observaciones',
				field: 'OBSERVA',
				halign: 'center',
				valign: 'middle',
				formatter: this.formatoObservacionesMedicamentos,
				width: 180
			},
			{
			  title: 'Acciones',
			  field: 'ACCIONES',
			  align: 'center',
			  clickToSelect: false,
			  events: this.eventoMedicamento,
			  formatter: this.formatoAcciones,
			  width: 30
			}
			]
		});
	},

	iniciarTablaMedicamentosAnteriores: function()
	{
		$('#tblMedicaAnteriores').bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			checkboxHeader: false,
			clicktoselect: 'true',
			locale: 'es-ES',
			undefinedText: 'N/A',
			toolbar: '#toolBarLst',
			height: '500',
			search: false,
			sortName: 'DESCRIPCION',
			pagination: false,
			iconSize: 'sm',
			columns: [
			{
				title: '',
				field: 'SELECCION',
				checkbox: 'true',
				width: 5, widthUnit: "%",
				halign: 'center',
				align: 'center'
			},
			{
				title: 'Código',
				field: 'CODIGO',
				width: 20, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Descripción',
				field: 'DESCRIPCION',
				width: 75, widthUnit: "%",
				halign: 'center',
				align: 'left'
			}
			]
		});
	},

	eventoInterconsulta:  {
		'click .like': function (e, value, row, index) {
			alert('Haga click sobre la acción, row: ' + JSON.stringify(row))
		},
		'click .removeCie': function (e, value, row, index) {
			fnConfirm('Desea eliminar la interconsulta?', false, false, false, false, function(){
				$('#tblInterconsulta').bootstrapTable('remove', {
				field: 'CODIGO',
				values: [row.CODIGO]
				});
			},'');
		}
	},

	eventoInsumo:  {
		'click .like': function (e, value, row, index) {
			alert('Haga click sobre la acción, row: ' + JSON.stringify(row))
		},
		'click .removeCie': function (e, value, row, index) {

			fnConfirm('Desea eliminar el insumo?', false, false, false, false, function(){
				$('#tblInsumo').bootstrapTable('remove', {
				field: 'CODIGO',
				values: [row.CODIGO]
				});
			},'');
		}
	},

	formatomedicamento: function (tnValor, toFila) {
		var lcTextoMedicamento = toFila.MEDICA.split('-');
		return (lcTextoMedicamento[1]);
	},

	formatoDosisDiaria: function (tnValor, toFila) {
		return toFila.DOSISDIA +' '+toFila.TIPODDIA;
	},

	formatoTiempoTratamiento: function (tnValor, toFila) {
		return toFila.TIEMPOTRATA +' '+toFila.TIPOCODTIEMTRAT;
	},

	formatoCantidadTotal: function (tnValor, toFila) {
		return toFila.CANTID +' '+toFila.CANTIDADTRAT;
	},

	formatoObservacionesMedicamentos: function (tnValor, toFila) {
		return toFila.OBSERVA.substring(0,oAmbulatorio.gnCaracteresObservacionesMedicamentos);
	},

	formatoBorrarElemento: function (value, row, index) {
		return [
		  '<a class="removeCie" href="javascript:void(0)" title="Eliminar">',
		  '<i class="fa fa-trash" style="color:#E96B50"></i>',
		  '</a>'
		].join('')
	},

	eventoMedicamento: {
		'click .editar': function (e, value, row, index) {
			oAmbulatorio.editarMedicamentoAmb(row);
		},
		'click .eliminar': function (e, value, row, index) {
			fnConfirm('Desea eliminar el medicamento ?', false, false, false, false, function(){
				oAmbulatorio.iniciaCamposMedicamento();
				oAmbulatorio.gotableMedicamentos.bootstrapTable('remove', {
					field: 'MEDICA',
					values: row.MEDICA
				});
			},'');
		}
	},

	formatoAcciones: function (value, row, index) {
		return [
		  '<a class="editar" href="javascript:void(0)" title="Editar">',
		  '<i class="fas fa-pencil-alt"></i>',
		  '</a>&nbsp;&nbsp;&nbsp;',
		  '<a class="eliminar" href="javascript:void(0)" title="Eliminar">',
		  '<i class="fas fa-trash-alt" style="color:#E96B50"></i>',
		  '</a>'
		].join('')
	},

	formatoColorFilaAmb: function (toFila, tnIndice) {
		var lcColor='0';
		lcColor = toFila['MARCAMED'];
		return oAmbulatorio.goColorFilaAmb[lcColor]? {css: {'background-color':oAmbulatorio.goColorFilaAmb[lcColor]}}: {};
	},

	validacion: function(){
		var lbValido = true;
		var aMedicamento = oAmbulatorio.gotableMedicamentos.bootstrapTable('getData');
		var aProcedimientos = oProcedimientos.gotableProcedimientos.bootstrapTable('getData');
		oAmbulatorio.lcObjetoError = '';
		oAmbulatorio.lcFormaError = '';

		if(aMedicamento != ''){
			$.each(aMedicamento, function( lcKey, loTipo ) {
				if(loTipo['CODIGO']=='' || loTipo['DOSIS']=='' || loTipo['TIPODCOD']=='' || loTipo['FRECUENCIA']==''  || loTipo['TIPOCODF']==''
				|| loTipo['DOSISDIA']=='' || loTipo['TIPODCODDIA']=='' || loTipo['TIPODDIA']=='' || loTipo['TIEMPOTRATA']==''
					|| loTipo['TIPOTIEMTRAT']=='' || loTipo['TIPOCODTIEMTRAT']=='' || loTipo['CANTID']=='' || loTipo['VIACOD']=='' || loTipo['VIA']==''
					|| loTipo['CANTIDADTRAT']==''
				){
					oAmbulatorio.lcMensajeError = 'Falta registrar dosis diaria, tiempo tratamiento, cantidad o vía administración en formulación de egreso, revise por favor.';
					oAmbulatorio.cTabError = 'tabOptOrdMedicamento';
					oAmbulatorio.lcFormaError = 'Formmedicamentos';
					oAmbulatorio.lcObjetoError = 'cMedicamentoAM';
					lbValido = false;
				}
			});
		}

		var lnDiasIncapacidad = parseInt($("#txtDiasIncapacidad").val()==''?'0':$("#txtDiasIncapacidad").val()),
			lcTipoIncapacidad = $("#selTipoIncapacidad").val(),
			lbTieneIncapacidad = lcTipoIncapacidad.length > 0;
		if (lbTieneIncapacidad) {
			oAmbulatorio.cTabError = 'tabOptOrdIncapacidad';
			oAmbulatorio.lcFormaError = 'FormIncapacidad';
			if ($("#selOrigenIncapacidad").val().length == 0){
				oAmbulatorio.lcObjetoError = 'selOrigenIncapacidad';
				oAmbulatorio.lcMensajeError = '"Origen incapacidad" es obligatorio, revise por favor.';
				return false;
			}
			if ($("#selCausaAtencion").val().length == 0){
				oAmbulatorio.lcObjetoError = 'selCausaAtencion';
				oAmbulatorio.lcMensajeError = '"Causa que Motiva la Atención" es obligatorio, revise por favor.';
				return false;
			}
			switch (lcTipoIncapacidad) {
				case 'AMB':		// Incapacidad Ambulatoria
				case 'PRO':		// Prórroga de incapacidad
					if (lnDiasIncapacidad < 1){
						oAmbulatorio.lcObjetoError = 'txtDiasIncapacidad';
						oAmbulatorio.lcMensajeError = 'Días de incapacidad es obligatorio, revise por favor.';
						return false;
					}
					var lnFechaInicioIncapacidad = parseInt($("#txtFechaDesde").val().replace(/-/g, ''));
					var lnFechaFinalIncapacidad = parseInt($("#txtFechaHasta").val().replace(/-/g, ''));
					if (lnFechaInicioIncapacidad>lnFechaFinalIncapacidad){
						oAmbulatorio.lcObjetoError = 'txtFechaDesde';
						oAmbulatorio.lcMensajeError = '"Fecha inicio" no puede ser mayor a "fecha final" en incapacidad, revise por favor.';
						return false;
					}
					if ($("#selProrroga").val().length == 0){
						oAmbulatorio.lcObjetoError = 'selProrroga';
						oAmbulatorio.lcMensajeError = '"Es Prórroga" en incapacidad es obligatorio, revise por favor.';
						return false;
					}
					break;

				case 'RET':		// Incapacidad Retroactiva
					if ($("#selIncapacidadRetroactiva").val().length == 0){
						oAmbulatorio.lcObjetoError = 'selIncapacidadRetroactiva';
						oAmbulatorio.lcMensajeError = '"Incapacidad Retroactiva" es obligatorio, revise por favor.';
						return false;
					}
					if ($("#txtFechaIniRetroactiva").val().length == 0){
						oAmbulatorio.lcObjetoError = 'txtFechaIniRetroactiva';
						oAmbulatorio.lcMensajeError = '"Fecha Inicio Incapacidad Retroactiva" es obligatorio, revise por favor.';
						return false;
					}
					if ($("#txtFechaFinRetroactiva").val().length == 0){
						oAmbulatorio.lcObjetoError = 'txtFechaFinRetroactiva';
						oAmbulatorio.lcMensajeError = '"Fecha Final Incapacidad Retroactiva" es obligatorio, revise por favor.';
						return false;
					}
					var lnFechaIniRetro = parseInt($("#txtFechaIniRetroactiva").val().replace(/-/g, ''));
					var lnFechaFinRetro = parseInt($("#txtFechaFinRetroactiva").val().replace(/-/g, ''));
					if (lnFechaIniRetro>lnFechaFinRetro){
						oAmbulatorio.lcObjetoError = 'txtFechaIniRetroactiva';
						oAmbulatorio.lcMensajeError = '"Fecha Inicio Incapacidad Retroactiva" no puede ser mayor a "Fecha Final Incapacidad Retroactiva", revise por favor.';
						return false;
					}
					break;

				default:
					oAmbulatorio.lcObjetoError = 'selTipoIncapacidad';
					oAmbulatorio.lcMensajeError = '"Tipo de Incapacidad" tiene un valor no permitido, revise por favor.';
					return false;
					break;
			}

			if (oAmbulatorio.obligarIncapacidadHospitalaria && $("#selIncapacidadHospitalaria").val().length == 0) {
				oAmbulatorio.lcObjetoError = 'selIncapacidadHospitalaria';
				oAmbulatorio.lcMensajeError = '"Requiere Incapacidad Hospitalaria" es obligatorio, revise por favor.';
				return false;
			}
		}

		if(aProcedimientos != '' && $('#selPrioridadAtencionOrdAmb').val()===''){
			$("#selPrioridadAtencionOrdAmb").removeClass("is-valid").addClass("is-invalid");
			oAmbulatorio.cTabError = 'tabOptOrdCups';
			oAmbulatorio.lcFormaError = 'FormOrdAmbulatoriaPac';
			oAmbulatorio.lcObjetoError = 'selPrioridadAtencionOrdAmb';
			oAmbulatorio.lcMensajeError = 'Prioridad Atención obligatoria, revise por favor.';
			$("#selPrioridadAtencionOrdAmb").focus();
			lbValido = false;
		}
		return lbValido;
	},

	validarTFrecuenciaAmb: function(taObjetos) {
		var lnValorFrecuencia=$('#txtFrecuenciaAmb').val();
		var lnValorUnidad = parseInt($("#selTipoFrecuenciaAmb option:selected").attr("data-unidad"));

		if (lnValorUnidad===1){
			$(taObjetos[4]).val(lnValorUnidad);
		}else{
			$(taObjetos[4]).val(lnValorFrecuencia);
		}
		$(taObjetos[6]).val(oAmbulatorio.calculaDosisDiaria(taObjetos,'S'));
		oAmbulatorio.calculaCantidadTotal();
	},

	validarFrecuenciaAmb: function(taObjetos) {
		if($(taObjetos[4]).val() > 1 && $(taObjetos[5]).val() !=1){
			$(taObjetos[5]).val('');
		}
		$(taObjetos[6]).val(oAmbulatorio.calculaDosisDiaria(taObjetos,'S'));
		oAmbulatorio.calculaCantidadTotal();
	},

	calculaDosisDiaria: function(taObjetos,tcTipo) {
		$lnValorDosisDiaria = 0;
		lcCantidadDosis = tcTipo=='A' ? taObjetos[2] : $(taObjetos[2]).val();
		lcTipoFrecuencia = tcTipo=='A' ? taObjetos[5] : $(taObjetos[5]).val();
		lcFrecuencia = tcTipo=='A' ? taObjetos[4] : $(taObjetos[4]).val();
		lnValorUnidadFrecuencia = (lcTipoFrecuencia=='1') ? 24 : 1;
		lnValorFrecuencia = lcFrecuencia>0 ? lcFrecuencia : 1;

		if (lcTipoFrecuencia!='' && lnValorFrecuencia>0 && lcCantidadDosis>0){
			$lnValorDosisDiaria = Math.abs(lnValorUnidadFrecuencia/lnValorFrecuencia)*lcCantidadDosis;
		}
		return $lnValorDosisDiaria;
	},

	calculaCantidadTotal: function() {
		var taObjetos = oAmbulatorio.taObjetosAmb;

		if (oAmbulatorio.gcUnidadDosis.trim()!=''){
			if (oAmbulatorio.gcUnidadDosis=='TAB' && $(taObjetos[6]).val()!='' && $(taObjetos[8]).val()!=''){
				$lnConcentracion = (oAmbulatorio.gnConcentracion !='') ? parseInt(oAmbulatorio.gnConcentracion) : 1;
				$lnDosisDiaria = $(taObjetos[6]).val();
				$lnCantidadAux = Math.abs($lnDosisDiaria/$lnConcentracion);
				$lnCantidadAux = ($lnCantidadAux<1) ? 1 : $lnCantidadAux;
				$lnCantidadAux = Math.ceil($lnCantidadAux*$(taObjetos[8]).val());
				$(taObjetos[10]).val($lnCantidadAux);
			}
		}else{
			lcCodigoMedicamento = $("#cCodigoMedicamentoAM").val()
			if (lcCodigoMedicamento!=''){
				oAmbulatorio.obtieneUnidadDosis(lcCodigoMedicamento);
			}
		}
		oAmbulatorio.gcPresentacion = oAmbulatorio.gcUnidadDosis = oAmbulatorio.gnConcentracion = '';
	},

	obtieneUnidadDosis: function(tcCodigoMedicamento) {
		tcCodigoMedicamento=tcCodigoMedicamento.split('-')[0],
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/ambulatorios.php',
			data: {cAccion: 'valoresMedicamento', lcCodigoConsumo: tcCodigoMedicamento},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					oAmbulatorio.gcUnidadDosis = loTipos.TIPOS['Unidad'];
					oAmbulatorio.gnConcentracion = loTipos.TIPOS['Concentracion'];
					oAmbulatorio.calculaCantidadTotal();
				} else {
					alert(loTipos.error + ' ', "warning");
				}
			} catch(err) {
				alert('No se pudo realizar la busqueda obtiene unidades.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			alert('Se presentó un error al buscar obtiene unidades.', "danger");
		});
	},

	buscarUltimaFormula: function() {
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/ambulatorios.php',
			data: {cAccion: 'ultimaFormula', lnNroIngreso: aDatosIngreso['nIngreso']},
			dataType: "json"
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					if (loTipos.TIPOS.length>0){
						oAmbulatorio.adicionaUltimosMedicamentos(loTipos.TIPOS);
					}else{
						if (aDatosIngreso['cCodVia']==='01'){
							oConciliacion.ConsultarConciliacion(1,1);
						}
					}
				}
			} catch(err) {
				fnAlert('No se pudo realizar busqueda última formula.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presenta un error al buscar última formula.');
		});
	},

	consultaCaracteresObservacionesMed: function(){
		$.ajax({
		type: "POST",
		url: 'vista-comun/ajax/ambulatorios.php',
		data: {cAccion: 'caracteresObservaciones'},
		dataType: "json"
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					oAmbulatorio.gnCaracteresObservacionesMedicamentos = parseInt(loTipos.TIPOS);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de medicamentos anteriores.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presenta un error al buscar de medicamentos anteriores.');
		});
	},

	ValidarPlan: function(tcCodigoPlan, tfPost){
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/ambulatorios.php',
			data: {cAccion: 'validaPlan', lcCodigoPlan: tcCodigoPlan},
			dataType: "json",
		})
		.done(function(loTipos) {
			try {
				if (loTipos.error == ''){
					oAmbulatorio.gcTipoNOPOS = '';
					if (typeof loTipos.TIPOS=='string'){
						oAmbulatorio.gcTipoNOPOS = loTipos.TIPOS.substr(0,1);
						oAmbulatorio.gcTipoMiPres = loTipos.TIPOS.substr(2,1);
						oProcedimientos.cargarListadosAyudaCups();
					}
					if(oAmbulatorio.gcTipoNOPOS == 'S'){
						oAmbulatorio.cargarMedicaNOPOS();
					}
					if (typeof tfPost == 'function') {
						tfPost();
					}
				} else {
					fnAlert(loTipos.error);
				}
			} catch(err) {
				fnAlert('No se pudo Validar Plan NOPOS.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar NOPOS.');
		});
	},

	cargarMedicaNOPOS: function(){
		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/modalCTC.php",
			data: {tipoDato: "MedicaNOPOS"},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.DATOS != []) {
						oAmbulatorio.aMedicaNOPOS = toDatos.DATOS.split(',');
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda para Medicamentos NOPOS.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar Medicamentos NOPOS.');
		});
	},

	insertarMedicamentoCTC: function(){
		laDatosCTC = OrganizarSerializeArray(oModalMedicamentoCTC.obtenerDatos());
		var lcMedicamentoP = (laDatosCTC.buscarMedicaP===undefined?'':laDatosCTC.buscarMedicaP);
		var lcCodigoP = lcMedicamentoP.substr(0,10);
		var lcNombreP = lcMedicamentoP.substr(10);
		var loMedicamento = {
			Codigo: oAmbulatorio.gcUltMedica.Codigo,
			Medicamento: oAmbulatorio.gcUltMedica.Medicamento,
			Dosis: oAmbulatorio.gcUltMedica.Dosis,
			TipoDosis: oAmbulatorio.gcUltMedica.TipoDosis,
			Frecuencia: oAmbulatorio.gcUltMedica.Frecuencia,
			TipoFrecuencia: oAmbulatorio.gcUltMedica.TipoFrecuencia,
			TipoVia: oAmbulatorio.gcUltMedica.TipoVia,
			Dosisdiaria: oAmbulatorio.gcUltMedica.Dosisdiaria,
			TipoDosisdiaria: oAmbulatorio.gcUltMedica.TipoDosisdiaria,
			TiempoTratamiento: oAmbulatorio.gcUltMedica.TiempoTratamiento,
			TipoTiempoTratamiento: oAmbulatorio.gcUltMedica.TipoTiempoTratamiento,
			Cantidad: oAmbulatorio.gcUltMedica.Cantidad,
			CantidadTratamiento: oAmbulatorio.gcUltMedica.CantidadTratamiento,
			Observa: oAmbulatorio.gcUltMedica.Observa,
			PresentaNP: (laDatosCTC.PresentaMed===undefined?'':laDatosCTC.PresentaMed.trim()),
			ConcentraNP: (laDatosCTC.ConcentraMed===undefined?'':laDatosCTC.ConcentraMed.trim()),
			UnidadNP: (laDatosCTC.UnidadMed===undefined?'':laDatosCTC.UnidadMed.trim()),
			GrupoTNP: (laDatosCTC.GrupoT===undefined?'':laDatosCTC.GrupoT.trim()),
			TiempoRNP: (laDatosCTC.TiempoR===undefined?'':laDatosCTC.TiempoR.trim()),
			RiesgoINP: (laDatosCTC.RiesgoI===undefined?'':laDatosCTC.RiesgoI.trim()),
			InvimaNP: (laDatosCTC.Invima===undefined?'':laDatosCTC.Invima.trim()),
			ResumenNP: (laDatosCTC.ResumenNP===undefined?'':laDatosCTC.ResumenNP.trim()),
			CodigoP: lcCodigoP.trim(), NombreMedP:lcNombreP.trim(),
			MedicaP: (laDatosCTC.buscarMedicaP===undefined?'':laDatosCTC.buscarMedicaP.trim()),
			PresentaP: (laDatosCTC.PresentaMedP===undefined?'':laDatosCTC.PresentaMedP.trim()),
			ConcentraP: (laDatosCTC.ConcentraMedP===undefined?'':laDatosCTC.ConcentraMedP.trim()),
			UnidadP: (laDatosCTC.UnidadMedP===undefined?'':laDatosCTC.UnidadMedP.trim()),
			DosisP: (laDatosCTC.DosisP===undefined?'':laDatosCTC.DosisP.trim()),
			TDosisP: (laDatosCTC.TdosisP===undefined?'':laDatosCTC.TdosisP.trim()),
			FrecuenciaP: (laDatosCTC.FrecuenciaP===undefined?'':laDatosCTC.FrecuenciaP.trim()),
			TFrecuenciaP: (laDatosCTC.TFrecuenciaP===undefined?'':laDatosCTC.TFrecuenciaP.trim()),
			DosisDP: (laDatosCTC.DosisDP===undefined?'':laDatosCTC.DosisDP.trim()),
			TDosisDP: (laDatosCTC.TdosisDP===undefined?'':laDatosCTC.TdosisDP.trim()),
			TratamientoP: (laDatosCTC.TratamientoP===undefined?'':laDatosCTC.TratamientoP.trim()),
			TTratamientoP: (laDatosCTC.TTratamientoP===undefined?'':laDatosCTC.TTratamientoP.trim()),
			CantidadP: (laDatosCTC.CantP===undefined?'':laDatosCTC.CantP.trim()),
			TCantidadP: (laDatosCTC.TipoCantidadP===undefined?'':laDatosCTC.TipoCantidadP.trim()),
			ViaP: (laDatosCTC.ViaP===undefined?'':laDatosCTC.ViaP.trim()),
			Efecto: (laDatosCTC.Efecto===undefined?'':laDatosCTC.Efecto.trim()),
			EfectoS: (laDatosCTC.EfectoS===undefined?'':laDatosCTC.EfectoS.trim()),
			Bibliografia: (laDatosCTC.Bibliografia===undefined?'':laDatosCTC.Bibliografia.trim()),
			PacienteInf: (laDatosCTC.chkPaciente===undefined?'':laDatosCTC.chkPaciente.trim()),
			NoPos: '1',
			MARCAMED: '3',
		};
		oModalMedicamentoCTC.inicializaMedicametoCTC();
		oAmbulatorio.insertarMedicamento(loMedicamento.Codigo, loMedicamento);
	},

	insertarMedicamento: function(tcCodigo, taMedicamento){
		var llverifica = oAmbulatorio.verificaRegistro(tcCodigo);

		if(llverifica) {
			oAmbulatorio.adicionarMedicamentoAmb(taMedicamento);
		}
		else{
			fnConfirm('El registro a ingresar ya existe. Desea modificarlo ?', oAmbulatorio.lcTitulo, false, false, false, function(){
				oAmbulatorio.modificarMedicamentoAmb(taMedicamento);
			});
		}
	},

	validaPrioridadAtencion: function(){
		if ($('#selPrioridadAtencionOrdAmb').val()!=''){
			$('#selPrioridadAtencionOrdAmb').addClass("is-valid").removeClass("is-invalid");
		}else{
			$('#selPrioridadAtencionOrdAmb').removeClass("is-invalid").removeClass("is-valid");
		}
	},

	obtenerDatos: function(){
		var laMedicamentos = $('#tblMedicaAmb').bootstrapTable('getData'),
			laProcedimientos = $('#tblProcedimiento').bootstrapTable('getData'),
			laInterconsultas = $('#tblInterconsulta').bootstrapTable('getData'),
			laIncapacidad = $('#FormIncapacidad').serializeAll();
		delete laIncapacidad.txtCodigoCieOrdAmbR;
		delete laIncapacidad.cDescripcionCieOrdAmbR;
		laIncapacidad.ModalidadPrestacion = $("#selModalidadPrestacion").length>0 ? $("#selModalidadPrestacion").val() : ($("#SelModalidadGrupo").length>0 ? $("#SelModalidadGrupo").val() : '01');

		return {
			MedicamentosAmb: laMedicamentos.length>0 ? laMedicamentos : '',
			RealizoFormulacion: $("#selRealizoFormulacion").val(),
			BrindoInformacion: $("#selBrindoInformacion").val(),
			Procedimientos: laProcedimientos.length>0 ? laProcedimientos : '',
			Prioridad: OrganizarSerializeArray($('#FormInterconsulta').serializeArray()),
			Interconsultas: laInterconsultas.length>0 ? laInterconsultas : '',
			Dieta: OrganizarSerializeArray($('#FormDieta').serializeArray()),
			Incapacidad: laIncapacidad,
			Insumos: $("#idObservacionesInsumos").val(),
			Otras: OrganizarSerializeArray($('#FormOtras').serializeArray()),
			Recomendaciones: OrganizarSerializeArray($('#FormRecomendacion').serializeArray()),
			PlanCups: $("#selPlanPaciente").val(),
			PrioridadCups: $("#selPrioridadAtencion").val()
		};
	},

	obtenerIncapacidades: function(){
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/ambulatorios.php',
			data: {cAccion:'incapacidades', cTipoDoc:aDatosIngreso.cTipId, nNumDoc: aDatosIngreso.nNumId},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.DATOS != []) {
						oAmbulatorio.oIncapacidades = toDatos.DATOS;
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la consulta de incapacidades del paciente.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar las incapacidades del paciente.');
		});
	},

	validarIncapacidades: function(){
		var lcTipoIncapacidad = $("#FormIncapacidad #selTipoIncapacidad").val(),
			lbTieneIncapacidad = lcTipoIncapacidad.length > 0;
		if (lbTieneIncapacidad && Object.values(oAmbulatorio.oIncapacidades).length > 0) {
			switch (lcTipoIncapacidad) {
				case 'AMB':		// Incapacidad Ambulatoria
				case 'PRO':		// Prórroga de incapacidad
					var lnFechaIni = parseInt($("#FormIncapacidad #txtFechaDesde").val().replace(/-/g, ''));
					var lnFechaFin = parseInt($("#FormIncapacidad #txtFechaHasta").val().replace(/-/g, ''));
					break;
				case 'RET':		// Incapacidad Retroactiva
					var lnFechaIni = parseInt($("#FormIncapacidad #txtFechaIniRetroactiva").val().replace(/-/g, ''));
					var lnFechaFin = parseInt($("#FormIncapacidad #txtFechaFinRetroactiva").val().replace(/-/g, ''));
					break;
			}
			if (lnFechaIni=='' || lnFechaIni==0 || lnFechaFin=='' || lnFechaFin==0) {
				return;
			}
			$.each(oAmbulatorio.oIncapacidades, function(tnKey, taIncapacidad){
				if (	(lnFechaIni >= taIncapacidad.fechaini && lnFechaIni <= taIncapacidad.fechafin)
				||	(lnFechaFin >= taIncapacidad.fechaini && lnFechaFin <= taIncapacidad.fechafin)
				||	(taIncapacidad.fechaini >= lnFechaIni && taIncapacidad.fechaini <= lnFechaFin)
				||	(taIncapacidad.fechafin >= lnFechaIni && taIncapacidad.fechafin <= lnFechaFin)
				) {
					var lcTipoInc = taIncapacidad.tipo=='PRO' ? 'Prórroga de incapacidad' : (taIncapacidad.tipo=='RET' ? 'Incapacidad Retroactiva' : 'Incapacidad');
					var lcMensaje, lcTitulo;
					lcMensaje = 'Las fechas de incapacidad seleccionadas coinciden con una incapacidad anterior.<br>'
						+lcTipoInc+'<br> - Ingreso: '+taIncapacidad.ingreso
						+'<br> - Rango: '+strNumAFecha(taIncapacidad.fechaini)+' - '+strNumAFecha(taIncapacidad.fechafin)
						+'<br><b>¿Desea continuar?</b>';
					lcTitulo = 'Fechas de Incapacidad';
					$('#FormIncapacidad #selTipoIncapacidad').focus();
					fnConfirm(lcMensaje, lcTitulo, false, false, 'medium', function() {
						return;
					}, function() {
						if (lcTipoIncapacidad=='RET'){
							$("#FormIncapacidad #txtFechaIniRetroactiva,#txtFechaFinRetroactiva").val('');
						} else {
							$("#FormIncapacidad #txtFechaDesde,#txtFechaHasta").val('');
							$("#FormIncapacidad #txtDiasIncapacidad").val(0);
						}
						return;
					}, {});
				}
			});
		}
	}
}
