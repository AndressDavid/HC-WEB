var oProcedimientosOrdMedica = {
	gotableProcedimientosOM : $('#tblProcedimientoOM'),
	gcUrlAjax: 'vista-ordenes-medicas/ajax/ajax',
	lcTitulo: 'Procedimientos ordenes médicas',
	aPedirCTC: [], 	aCupsRegistrar: [], aCupsVerificaFisioterapia: [], aCupsVerificaMipres: [], aCupsNeumologia: [],
	aCupsSelecionado: [], aListaCups: [], aDatosInvasivos: [],
	lcFormaError:'', lcObjetoError:'', lcMensajeError:'', gcCupsOrdenar:'', gcDescripcionOrdenar:'', gcObservaciones:'',
	gcEspecialidadImagenes:'', gcTextoPortatil:'', gcServicioUrgencias:'', gcEnUrgenciasPaciente:'', gcCupsVerificaInterconsulta:'',
	gcCupsVerificaGlucometria:'', gcEsCupsUrgencias:'', gcEnvioAgfa:'', gcPlanManejo:'', gcCupsNuclear:'', gcCupsNoInvasivos:'',
	gnConsecutivoId:1, gnMarcaPortatil:0, gnEdadMenor:0, gnCantidadCups:0, gnFrecuenciaCups:0, gcCantidadMaxima:0,
	gcFrecuenciaMaxima:0, gnEdadPaciente:0, gnDiasProcedimiento:0, gnLabIndependiente:0,
	gcEspecialidadSinObserva:'', gcEspecialidadHemocomponente:'', gcEsHemocomponente:'', aDatosProcedimiento:'',
	gcRegistroCupsMipres:'', gcTipoCupsMipres:'', gcMarcaPaquete:'', gcModuloAyudaCups:'',
	llSolicitarCTC: false,

	inicializar: function(){
		this.iniciarTablaProcedimientos();
		this.inicializaCampos('');
		this.cantidadFrecuenciaCups(oProcedimientos.gcCupsEspecialidad);
		this.cargarListadosAyudaProcedimientos();
		this.consultarParametros();
		this.consultarPlanManejo();
		this.consultarPacienteUrgencias();
		this.activaCupsUrgencias('');
		oModalAyudaProcedimientos.inicializar();

		$('#FormCupsOM').validate({
			rules: {
				buscarProcedimientoOM: "required",
				inpCantidadCupsOM: "required",
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
		$('#AdicionarProcedimientoOM').on('click', this.adicionarCupsOrdMedica);
		$('#btnAyudaProcedimiento').on('click', this.ayudaProcedimientos);
		$('#eliminarProcedimientosOM').on('click', this.borraProcedimientos);
		oProcedimientos.consultaProcedimientos('cProcedimientoOM','cCodigoProcedimientoOM','cDescripcionProcedimientoOM','inpCantidadCupsOM','OM');

		$('#inpCantidadCupsOM').on('keydown', function(){
			$("#inpFrecuenciaCupsOM").val('');
			$('#inpFrecuenciaCupsOM').removeClass("is-valid");
		});

		$('#inpFrecuenciaCupsOM').on('keyup', function(){
			if ($("#inpFrecuenciaCupsOM").val()>0){
				$("#inpCantidadCupsOM").val('');
				$('#inpCantidadCupsOM').removeClass("is-valid");
			}else{
				$('#inpFrecuenciaCupsOM').removeClass("is-valid");
			}
		});

		$('#tblProcedimientoOM').on('focusout','.modobservacion', function(){
			var lnOrden=$(this).attr("data-id"),
				lcCampo=$(this).attr("data-campo"),
				lcValor=$(this).val();

			// buscar indice
			var loTblData = $('#tblProcedimientoOM').bootstrapTable("getData");
			$.each(loTblData, function(lnIndice, loFila){
				if(lnOrden==loFila.IDCUPS){
					if(lcValor!==loFila[lcCampo]){
						$('#tblProcedimientoOM').bootstrapTable("updateCell",{
							index:lnIndice,
							field:lcCampo,
							value:lcValor
						});
					}
					return;
				}
			});
		});
	},

	seleccionaProcedimiento: function() {
		oProcedimientosOrdMedica.gcEsCupsUrgencias='';
		var lcCodigoCups=oProcedimientos.gcCupsSolicitar;

		llVerificarCups = oProcedimientosOrdMedica.verificaProcedimiento(lcCodigoCups);
		if(llVerificarCups) {
			oProcedimientosOrdMedica.inicializaCampos(lcCodigoCups);
			oProcedimientosOrdMedica.cantidadFrecuenciaCups(oProcedimientos.gcCupsEspecialidad);
			if (lcCodigoCups!=''){
				if (oProcedimientos.gcCupsEspecialidad!=oProcedimientosOrdMedica.gcEspecialidadSinObserva && oProcedimientos.gcCupsReferencia1==='DIAG'){
					$("#txtInformacionClinicaCups").val(oProcedimientosOrdMedica.gcPlanManejo);
				}
				if (oProcedimientosOrdMedica.gcEnUrgenciasPaciente!=''){
					oProcedimientosOrdMedica.consultarCupsUrgencias(lcCodigoCups);
				}
			}
		}else{
			oProcedimientosOrdMedica.inicializaCampos('');
		}
	},

	cargarListadosAyudaProcedimientos: function() {
		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/Autocompletar.php",
			data: {
				tipoDato: 'Procedimientos',
				otros: {filtro: '', genero: aDatosIngreso.cSexo},
			},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					oModalAyudaProcedimientos.adicionarProcedimientos(toDatos.datos,$('#tblProcedimientoOM'),'OM',gcTipoNoposOM);
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert(err);
				fnAlert('No se pudo realizar la busqueda de listado ayuda procedimientos.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar tipos de listado ayuda procedimientos.');
		});
	},

	consultarParametros: function()
	{
		oProcedimientosOrdMedica.gnEdadPaciente = parseInt(aDatosIngreso.aEdad.y);
		$.ajax({
			type: "POST",
			url: oProcedimientosOrdMedica.gcUrlAjax,
			data: {accion: 'parametrosCups'},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					oProcedimientosOrdMedica.gcEspecialidadImagenes=toDatos.TIPOS.espportatil;
					oProcedimientosOrdMedica.gcTextoPortatil = toDatos.TIPOS.textoportatil;
					oProcedimientosOrdMedica.gcEspecialidadSinObserva = toDatos.TIPOS.espnotraeplan;
					oProcedimientosOrdMedica.gcCupsVerificaInterconsulta = toDatos.TIPOS.cupsinterconsulta;
					oProcedimientosOrdMedica.gcCupsVerificaGlucometria = toDatos.TIPOS.cupsglucometria;
					oProcedimientosOrdMedica.aCupsVerificaFisioterapia = toDatos.TIPOS.cupsfisioterapia;
					oProcedimientosOrdMedica.aCupsVerificaMipres = toDatos.TIPOS.procedimientosmipres;
					oProcedimientosOrdMedica.gnEdadMenor = parseInt(toDatos.TIPOS.edadmenor);
					oProcedimientosOrdMedica.gcEspecialidadHemocomponente = toDatos.TIPOS.esphemocomponente;
					oProcedimientosOrdMedica.gnDiasProcedimiento = parseInt(toDatos.TIPOS.diasprocedimiento);
					oProcedimientosOrdMedica.aCupsNeumologia = toDatos.TIPOS.excepcionneumo;
					oProcedimientosOrdMedica.gcCupsNuclear=toDatos.TIPOS.cupsmedicinanuclear;
					oProcedimientosOrdMedica.gcCupsNoInvasivos=toDatos.TIPOS.cupsnoinvasivos;
					oProcedimientosOrdMedica.gnLabIndependiente=toDatos.TIPOS.conseclabAdicional;
					oProcedimientosOrdMedica.gcRegistroCupsMipres=toDatos.TIPOS.tiporegistromipres.split('~')[0];
					oProcedimientosOrdMedica.gcTipoCupsMipres=toDatos.TIPOS.tiporegistromipres.split('~')[1];
					oProcedimientosOrdMedica.activaPortatil('');
					oOxiGlucometriaOrdMedica.nDosisMinimaOxi=toDatos.TIPOS.dosisminimaoxigeno;
					oOxiGlucometriaOrdMedica.nDosisMaximaOxi=toDatos.TIPOS.dosismaximaoxigeno;
					$("#txtDosisOxigeno").attr("min",oOxiGlucometriaOrdMedica.nDosisMinimaOxi);
					$("#txtDosisOxigeno").attr("max",oOxiGlucometriaOrdMedica.nDosisMaximaOxi);
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta parametros imágenes.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta parametros imágenes.");
		});
	},

	consultarCupsUrgencias: function(tcCupsSolicitar)
	{
		oProcedimientosOrdMedica.gcEsCupsUrgencias='';
		$.ajax({
			type: "POST",
			url: oProcedimientosOrdMedica.gcUrlAjax,
			data: {accion: 'consultaCupsUrgencias', lcProcedimiento: tcCupsSolicitar},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.TIPOS!=''){
						oProcedimientosOrdMedica.gcEsCupsUrgencias='URG';
						oProcedimientosOrdMedica.activaCupsUrgencias(toDatos.TIPOS);
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta procedimiento urgencias.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta procedimiento urgencias.");
		});
	},

	consultarPlanManejo: function()
	{
		$.ajax({
			type: "POST",
			url: oProcedimientosOrdMedica.gcUrlAjax,
			data: {accion: 'planDeManejo', lnIngreso: aDatosIngreso.nIngreso},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					oProcedimientosOrdMedica.gcPlanManejo = toDatos.TIPOS;
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta plan de manejo.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta plan de manejo.");
		});
	},

	consultarPacienteUrgencias: function()
	{
		$.ajax({
			type: "POST",
			url: oProcedimientosOrdMedica.gcUrlAjax,
			data: {accion: 'pacienteUrgencias', lcViaIngreso: aDatosIngreso.cCodVia, lcSeccion: aDatosIngreso.cSeccion},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					$.each(toDatos.TIPOS, function( lcKey, loTipo ) {
						oProcedimientosOrdMedica.gcEnUrgenciasPaciente = 'U';
						$('#selServicioRealizaOM').append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
						$('#selServicioRealizaObs').append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
					});

				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta paciente urgencias.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta paciente urgencias.");
		});
	},

	borraProcedimientos: function() {
		var taCupsSeleccionados = oProcedimientosOrdMedica.gotableProcedimientosOM.bootstrapTable('getSelections');
		if(taCupsSeleccionados != ''){
			$('#cProcedimientoOM').focus();

			fnConfirm('Desea eliminar los procedimientos seleccionados?', false, false, false, 'medium', function(){
				$.each(taCupsSeleccionados, function( lcKey, loTipo ) {
					lcIdCodigoCups = loTipo['IDCUPS'];
					oProcedimientosOrdMedica.gotableProcedimientosOM.bootstrapTable('remove', {
						field: 'IDCUPS',
						values: [lcIdCodigoCups]
					});
				});
				oProcedimientosOrdMedica.verificaHemocomponente();
			},'');
		}else{
			$('#cProcedimientoOM').focus();
			fnAlert('No existen procedimientos a eliminar, revise por favor.', '', 'fas fa-exclamation-circle','blue','medium');
		}
	},

	verificaProcedimiento: function(tcCodigoCups){
		var llRetorno = true;

		if (tcCodigoCups.substr(0, 4)==oProcedimientosOrdMedica.gcCupsVerificaInterconsulta){
			fnAlert('Procedimiento Interconsulta, debe solicitarse en la ficha INTERCONSULTAS.', '', false, false, false);
			llRetorno = false;
		}

		if (tcCodigoCups==oProcedimientosOrdMedica.gcCupsVerificaGlucometria){
			fnAlert('Procedimiento Glucometría, debe solicitarse en la ficha OXÍGENO - GLUCOMETRÍA.', '', false, false, false);
			llRetorno = false;
		}

		if ($.inArray(tcCodigoCups, oProcedimientosOrdMedica.aCupsVerificaFisioterapia)>=0){
			lcTexto = 'El procedimiento ' + tcCodigoCups + ' - ' + oProcedimientos.gcCupsDescripcion + ', NO se puede ordenar, debe solicitar interconsulta, por favor verificar.'
			fnAlert(lcTexto, '', false, false, false);
			llRetorno = false;
		}
		return llRetorno ;
	},

	activaCupsUrgencias: function(tcCodigoCups)
	{
		$("#selServicioRealizaOM,#selServicioRealizaObs").val('');
		$("#lblServicioRealizaOM,#lblServicioRealizaObs").css("display","none");
		$("#selServicioRealizaOM,#selServicioRealizaObs").css("visibility","hidden");

		if (tcCodigoCups!=''){
			$("#lblServicioRealizaOM,#lblServicioRealizaObs").css("display","block");
			$("#selServicioRealizaOM,#selServicioRealizaObs").css("visibility","visible");
		}
	},

	ayudaProcedimientos: function(e){
		e.preventDefault();
		oModalAyudaProcedimientos.mostrar();
	},

	activaPortatil: function(tcEspecialidad) {
		$("#chkPortatilCupsOM").css("display","none");
		$("#lblPortatilCupsOM").css("display","none");

		if (tcEspecialidad==oProcedimientosOrdMedica.gcEspecialidadImagenes){
			$("#chkPortatilCupsOM").css("display","block");
			$("#lblPortatilCupsOM").css("display","block");
		}
	},

	verificaCodigoExiste: function(tcCodigo,taTablaValida) {
		var llRetorno = true ;
			if(taTablaValida != ''){
				$.each(taTablaValida, function( lcKey, loTipo ) {
					if(loTipo['CODIGO']==tcCodigo){
						llRetorno = false;
					}
				});
			};
		return llRetorno ;
	},

	adicionarCupsOrdMedica: function(e){
		e.preventDefault();
		var loFunction = false;
		oProcedimientosOrdMedica.aListaCups=[];
		oProcedimientosOrdMedica.gcObservaciones=oProcedimientosOrdMedica.gcServicioUrgencias='';
		oProcedimientosOrdMedica.gnMarcaPortatil=0;

		if ($('#FormCupsOM').valid()){
			var lcCodigoCups=$("#cCodigoProcedimientoOM").val();
			var lcDescripcionCups=($("#cDescripcionProcedimientoOM").val()).trim();
			oProcedimientosOrdMedica.gcServicioUrgencias=$("#selServicioRealizaOM").val();
			oProcedimientosOrdMedica.gnCantidadCups=$("#inpCantidadCupsOM").val();
			oProcedimientosOrdMedica.gnFrecuenciaCups=$("#inpFrecuenciaCupsOM").val();
			oProcedimientosOrdMedica.gcObservaciones=$("#txtInformacionClinicaCups").val();
			if($("#chkPortatilCupsOM").prop('checked')){
				oProcedimientosOrdMedica.gnMarcaPortatil=1;
			}

			if (lcDescripcionCups==''){
				fnAlert('Procedimiento no valido, revise por favor.', '', false, false, false);
				$('#cProcedimientoOM').focus();
				return false;
			}

			if (lcCodigoCups==''){
				fnAlert('Procedimiento obligatorio, revise por favor.', '', false, false, false);
				return false;
			}

			if ((oProcedimientosOrdMedica.gnCantidadCups==0 && oProcedimientosOrdMedica.gnFrecuenciaCups==0)
				|| (oProcedimientosOrdMedica.gnCantidadCups=='' && oProcedimientosOrdMedica.gnFrecuenciaCups=='')){
				fnAlert('Cantidad o frecuencia obligatoria, revise por favor.', '', false, false, false);
				return false;
			}

			if (oProcedimientosOrdMedica.gnCantidadCups>0 && oProcedimientosOrdMedica.gnCantidadCups>oProcedimientosOrdMedica.gcCantidadMaxima){
				lcTexto = 'Cantidad a Solicitar no puede ser mayor a ' + oProcedimientosOrdMedica.gcCantidadMaxima + ' procedimientos, revise por favor.'
				fnAlert(lcTexto);
				return false;
			}

			if (oProcedimientosOrdMedica.gnFrecuenciaCups>0 && oProcedimientosOrdMedica.gnFrecuenciaCups>oProcedimientosOrdMedica.gcFrecuenciaMaxima){
				lcTexto = 'Frecuencia a Solicitar no puede ser mayor a ' + oProcedimientosOrdMedica.gcFrecuenciaMaxima + ', revise por favor.'
				fnAlert(lcTexto);
				return false;
			}

			if (oProcedimientosOrdMedica.gnCantidadCups>=2 && oProcedimientos.gcCupsEspecialidad==oProcedimientosOrdMedica.gcEspecialidadSinObserva){
				lcTexto = 'Tener presente la cantidad de laboratorios solicitados.';
				fnAlert(lcTexto);
			}

			if (oProcedimientosOrdMedica.gcServicioUrgencias=='' && oProcedimientosOrdMedica.gcEsCupsUrgencias!=''){
				fnAlert('Servicio que realiza obligatorio, revise por favor.', '', false, false, false);
				return false;
			}

			if (oProcedimientosOrdMedica.gcObservaciones==''){
				fnAlert('Observaciones obligatorio, revise por favor.', '', false, false, false);
				return false;
			}
			oProcedimientosOrdMedica.aCupsSelecionado = [];
			oProcedimientosOrdMedica.aListaCups.push({CODIGO: lcCodigoCups });
			
			oProcedimientosOrdMedica.registraDatosCups(oProcedimientosOrdMedica.aListaCups, function() {
				oProcedimientosOrdMedica.validarTipoProcedimiento();
			});
		}
	},

	registraDatosCups: function(taCupsSelecionados, tfPost)
	{
		oProcedimientosOrdMedica.gcMarcaPaquete='';
		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/listaPaquetes.php",
			data: {lcPaquete: taCupsSelecionados, lcGenero: aDatosIngreso['cSexo'], lnEdadaños: parseInt(aDatosIngreso.aEdad.y)},
			dataType: "json"
		})

		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					$.each(toDatos.datos, function(lcKey, loSeleccion) {
						oProcedimientosOrdMedica.gcMarcaPaquete=loSeleccion.TIPO;
						oProcedimientosOrdMedica.aCupsSelecionado.push({CODIGO: loSeleccion.CODIGO, DESCRIPCION: loSeleccion.DESCRIPCION,
						REFERENCIA1: loSeleccion.REFERENCIA1,
						POSNOPOS: loSeleccion.POSNOPOS, PAQUETE: loSeleccion.TIPO, ESPECIALIDAD: loSeleccion.ESPECIALIDAD,
						AGFA: loSeleccion.ENVIAAGFA, SIEMPRENOPOS: loSeleccion.SIEMPRENOPOS,
						JUSTIFICACIONPOS: loSeleccion.JUSTIFICACIONPOS, TIPOHEMOCOMPONENTE: loSeleccion.HEMOCOMPONENTE,
						HEXALIS: loSeleccion.HEXALIS, LABESPEC: loSeleccion.LABESPEC, OBSERVAPAQUETE: loSeleccion.OBSERVACIONES });
					});

					if (typeof tfPost == 'function') {
						tfPost();
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar Registra datos cups.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al registrar datos cups.");
		});
	},

	cantidadFrecuenciaCups: function(tcEspecialidad)
	{
		$.ajax({
			type: "POST",
			url: oProcedimientosOrdMedica.gcUrlAjax,
			data: {accion: 'parametrosCantidades', lcEspecialidad: tcEspecialidad},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					$.each(toDatos.TIPOS, function(lcKey, loSeleccion) {
						if (loSeleccion.CODIGO=='CANTIDAD'){ oProcedimientosOrdMedica.gcCantidadMaxima = parseInt(loSeleccion.DESCRIPCION); }
						if (loSeleccion.CODIGO=='FRECUENC'){ oProcedimientosOrdMedica.gcFrecuenciaMaxima = parseInt(loSeleccion.DESCRIPCION); }
					});
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la cantidad frecuencia cups.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al cantidad frecuencia cups.");
		});
	},

	validarTipoProcedimiento: function()
	{
		oModalAyudaProcedimientos.aDatosProcedimiento=oProcedimientosOrdMedica.gcModuloAyudaCups=oProcedimientos.gcLabIndependiente='';
		oModalJustificacionPos.aDatosJustificacion={};
		var lnRegistros = oProcedimientosOrdMedica.aCupsSelecionado.length;

		if(lnRegistros > 0){
			oProcedimientosOrdMedica.aCupsRegistrar=[];
			oProcedimientosOrdMedica.aCupsRegistrar=oProcedimientosOrdMedica.aCupsSelecionado[0];
			oProcedimientos.gcLabIndependiente=oProcedimientosOrdMedica.aCupsRegistrar.LABESPEC===undefined ? '' : oProcedimientosOrdMedica.aCupsRegistrar.LABESPEC;
			oModalAyudaProcedimientos.aDatosProcedimiento=oProcedimientosOrdMedica.aCupsRegistrar;
			oProcedimientosOrdMedica.verificarProcedimientos();
		}else{
			oProcedimientosOrdMedica.gcObservaciones='';
		}
	},

	verificarProcedimientos: function()
	{
		oProcedimientosOrdMedica.consolidarDatosCups();
		aEnviarDatos='';
		var taTablaValida = oProcedimientosOrdMedica.gotableProcedimientosOM.bootstrapTable('getData');
		var lcCodigoCups=oProcedimientosOrdMedica.aCupsRegistrar.CODIGO;
		var lcDescripcionCups=oProcedimientosOrdMedica.aCupsRegistrar.DESCRIPCION;
		var lcJustificacionPos=oProcedimientosOrdMedica.aCupsRegistrar.JUSTIFICACIONPOS;
		var lcPosNopos=oProcedimientosOrdMedica.aCupsRegistrar.POSNOPOS;
		var llverificaExiste = oProcedimientosOrdMedica.verificaCodigoExiste(lcCodigoCups, taTablaValida);

		if (lcJustificacionPos!=''){
			oModalJustificacionPos.mostrar();
		}else{
			oModalAyudaProcedimientos.validarHemocomponente();
		}
	},

	consolidarDatosCups: function()
	{
		oProcedimientosOrdMedica.aDatosProcedimiento='';
		var lcEspecialidadUsuario=aAuditoria.cEspUsuario;

		if (oProcedimientosOrdMedica.gnFrecuenciaCups>0){
			lnFrecuencia = oProcedimientosOrdMedica.gnFrecuenciaCups<6 ? 6 : oProcedimientosOrdMedica.gnFrecuenciaCups;
			lncantidadCups = Math.round(oProcedimientosOrdMedica.gcFrecuenciaMaxima/lnFrecuencia);
		}else{
			lncantidadCups = oProcedimientosOrdMedica.gnCantidadCups;
		}
		lncantidadCups=oProcedimientosOrdMedica.gcMarcaPaquete=='P'?1:lncantidadCups;
		lcCodigoCups=oProcedimientosOrdMedica.aCupsRegistrar.CODIGO;
		lcDescripcionCups=oProcedimientosOrdMedica.aCupsRegistrar.DESCRIPCION;
		lcPaquete=oProcedimientosOrdMedica.aCupsRegistrar.PAQUETE===undefined?'':oProcedimientosOrdMedica.aCupsRegistrar.PAQUETE;
		lcPosNopos=oProcedimientosOrdMedica.aCupsRegistrar.POSNOPOS=='NOPB' ? 'NOPOS' : 'POS';
		lcEspecialidad=(oProcedimientosOrdMedica.gcServicioUrgencias!='' && oProcedimientosOrdMedica.gcServicioUrgencias!='2') ? lcEspecialidadUsuario : oProcedimientosOrdMedica.aCupsRegistrar.ESPECIALIDAD;
		lcAgfa=oProcedimientosOrdMedica.aCupsRegistrar.AGFA;
		lcObservaciones=oProcedimientosOrdMedica.gnMarcaPortatil==1 ? (oProcedimientosOrdMedica.gcTextoPortatil+' '+oProcedimientosOrdMedica.gcObservaciones) : (oProcedimientosOrdMedica.aCupsRegistrar.OBSERVAPAQUETE!='' ? oProcedimientosOrdMedica.aCupsRegistrar.OBSERVAPAQUETE : oProcedimientosOrdMedica.gcObservaciones);
		lcSiempreNopos=oProcedimientosOrdMedica.aCupsRegistrar.SIEMPRENOPOS;
		lcHemocomponente=oProcedimientosOrdMedica.aCupsRegistrar.TIPOHEMOCOMPONENTE;
		lcHexalis=oProcedimientosOrdMedica.aCupsRegistrar.HEXALIS;
		oProcedimientosOrdMedica.gcCupsOrdenar=lcCodigoCups;
		oProcedimientosOrdMedica.gcDescripcionOrdenar=lcDescripcionCups;
		oProcedimientosOrdMedica.gcEsHemocomponente=lcHemocomponente;

		laDatosCups = { CANTIDADCUPS: lncantidadCups, CODIGO: lcCodigoCups,
				DESCRIPCION: lcDescripcionCups,
				POSNOPOS: lcPosNopos,
				POSNOTEXTO: lcPosNopos=='NOPOS' ? 'NOPOS' : (oModalJustificacionPos.gcCiePrincipal!='' ? 'POS-JUSTIFICAR' : 'POS'),
				ESPECIALIDAD: lcEspecialidad,
				AGFA: lcAgfa,
				OBSERVACIONES: lcObservaciones,
				SIEMPRENOPOS: lcSiempreNopos,
				TIPOHEMOCOMPONENTE: lcHemocomponente,
				HEXALIS: lcHexalis,
				PAQUETE: lcPaquete };
		oProcedimientosOrdMedica.aDatosProcedimiento=laDatosCups;
	},

	//	SE LLAMA DESDE AMBULATORIOS
	SolicitarJustificacionCTC: function()
	{
		var lnLong = oProcedimientosOrdMedica.aPedirCTC.length;
		if(lnLong > 0){
			laProcedimientos = oProcedimientosOrdMedica.aPedirCTC[0];
			oModalProcedimientoCTC.iniciaModalProcedimientoCTC(laProcedimientos, laProcedimientos, laProcedimientos.EXISTEREG);
			oProcedimientosOrdMedica.aPedirCTC.shift();
			lnLong = oProcedimientosOrdMedica.aPedirCTC.length;
			oProcedimientosOrdMedica.llSolicitarCTC = (lnLong > 0);
		}
	},

	alistarRegistro: function(tcCupsOrdenar,taDatosRegistrar){
		var taTablaValida=oProcedimientosOrdMedica.gotableProcedimientosOM.bootstrapTable('getData');
		lcCodigoCups=tcCupsOrdenar;
		llverificaExiste = oProcedimientosOrdMedica.verificaCodigoExiste(lcCodigoCups, taTablaValida);
		if ($.inArray(lcCodigoCups, oProcedimientosOrdMedica.aCupsNeumologia)>=0){
			llverificaExiste = true;
		}
		lcPaquete=taDatosRegistrar.PAQUETE===undefined?'':taDatosRegistrar.PAQUETE;

		if(llverificaExiste || lcPaquete!=''){
			oProcedimientosOrdMedica.adicionarProcedimiento(taDatosRegistrar);
		}else{
			fnConfirm('Procedimiento ya ingresado, desea modificarlo?', oProcedimientosOrdMedica.lcTitulo, false, false, false,
				{
					text: 'Si',
					action: function(){
						$('#tblProcedimientoOM').bootstrapTable('remove', {
							field: 'CODIGO',
							values: [ lcCodigoCups.trim() ]
						});
						oProcedimientosOrdMedica.adicionarProcedimiento(taDatosRegistrar);
					}
				},
				{ text: 'No',
					action: function(){
						oProcedimientosOrdMedica.inicializaCampos('');
						$('#buscarProcedimientoOM').focus();
						oModalAyudaProcedimientos.solicitarObservaciones();
					}
				}
			);
		}
		oProcedimientosOrdMedica.inicializaCampos('');
	},

	adicionarProcedimiento: function(taDatosCups){
		lcCiePrincipalpos=lcDescCiePrincipalpos=lcRelacionado1pos=lcDescRelacionado1pos=lcRelacionado2pos=lcDescRelacionado2pos=lcObservacionespos = '';
		lnCantidad=1;
		lnCantidadTotal=taDatosCups.CANTIDADCUPS;
		lcCodigoCups=taDatosCups.CODIGO;
		lcDescripcionCups=taDatosCups.DESCRIPCION;
		lcObservaciones=taDatosCups.OBSERVACIONES;
		lcPosNopos=(taDatosCups.POSNOPOS=='NOPOS' || taDatosCups.POSNOPOS=='NOPB') ? 'N' : 'P';
		lcPosNoposTexto=(taDatosCups.POSNOPOS=='NOPOS' || taDatosCups.POSNOPOS=='NOPB') ? 'NOPOS' : (oModalJustificacionPos.gcCiePrincipal!='' ? 'POS-JUSTIFICAR' : 'POS');
		taDatosCups.POSNOTEXTO;
		lcEspecialidad=taDatosCups.ESPECIALIDAD;
		lcHexalis=taDatosCups.HEXALIS===undefined?'':taDatosCups.HEXALIS;
		lcAgfa=taDatosCups.AGFA;
		lcSiempreNopos=taDatosCups.SIEMPRENOPOS===undefined?'':taDatosCups.SIEMPRENOPOS;
		lcTipoHemocomponente=taDatosCups.TIPOHEMOCOMPONENTE===undefined?'':taDatosCups.TIPOHEMOCOMPONENTE;
		lcCiePrincipalpos=(oModalJustificacionPos.aDatosJustificacion.POSCIEPRINCIPAL===undefined?'':oModalJustificacionPos.aDatosJustificacion.POSCIEPRINCIPAL);
		lcDescCiePrincipalpos=(oModalJustificacionPos.aDatosJustificacion.POSCIEPRINCIPALDES===undefined?'':oModalJustificacionPos.aDatosJustificacion.POSCIEPRINCIPALDES);
		lcRelacionado1pos=(oModalJustificacionPos.aDatosJustificacion.POSCIERELACIONADO1===undefined?'':oModalJustificacionPos.aDatosJustificacion.POSCIERELACIONADO1);
		lcDescRelacionado1pos=(oModalJustificacionPos.aDatosJustificacion.POSCIERELACIONADO1DES===undefined?'':oModalJustificacionPos.aDatosJustificacion.POSCIERELACIONADO1DES);
		lcRelacionado2pos=(oModalJustificacionPos.aDatosJustificacion.POSCIERELACIONADO2===undefined?'':oModalJustificacionPos.aDatosJustificacion.POSCIERELACIONADO2);
		lcDescRelacionado2pos=(oModalJustificacionPos.aDatosJustificacion.POSCIERELACIONADO2DES===undefined?'':oModalJustificacionPos.aDatosJustificacion.POSCIERELACIONADO2DES);
		lcObservacionespos=(oModalJustificacionPos.aDatosJustificacion.POSOBSERVACIONES===undefined?'':oModalJustificacionPos.aDatosJustificacion.POSOBSERVACIONES);

		for (cantidad=1; cantidad<=lnCantidadTotal; cantidad++){
			oProcedimientosOrdMedica.gnLabIndependiente=oProcedimientos.gcLabIndependiente!='' ? oProcedimientosOrdMedica.gnLabIndependiente+1: oProcedimientosOrdMedica.gnLabIndependiente;
			lnCantidadLinea=oProcedimientos.gcLabIndependiente==='' ? cantidad : oProcedimientosOrdMedica.gnLabIndependiente;
			lniD=oProcedimientosOrdMedica.gnConsecutivoId++;

			oProcedimientosOrdMedica.gotableProcedimientosOM.bootstrapTable('insertRow', {
				index: 1,
				row: {
					TIPO: 'CUPS',
					CODIGO: lcCodigoCups,
					DESCRIPCION: lcDescripcionCups,
					OBSERVACIONES: lcObservaciones,
					CANTIDAD: lnCantidad,
					POSNOPOS: lcPosNopos,
					POSNOPTEXTO: lcPosNoposTexto,
					ESPECIALIDAD: lcEspecialidad!='' ?lcEspecialidad : aAuditoria.cEspUsuario,
					HEXALIS: lcHexalis,
					AGFA: lcAgfa,
					MODELOEQUIPO: lcAgfa!='' ? 'AGFA' :'',
					TIPOADT: lcAgfa!='' ? 'A02':'',
					TIPOMENSAJE: lcAgfa!='' ? 'ADT' : '',
					LINEA: lnCantidadLinea,
					CIEJUSTIFICAPOS: lcCiePrincipalpos,
					DESCRIPCIONCIEJUSTIFICAPOS: lcDescCiePrincipalpos,
					CIEREL1JUSTIFICAPOS: lcRelacionado1pos,
					DESCRIPCIONCIEREL1JUSTIFICAPOS: lcDescRelacionado1pos,
					CIEREL2JUSTIFICAPOS: lcRelacionado2pos,
					DESCRIPCIONCIEREL2JUSTIFICAPOS: lcDescRelacionado2pos,
					OBSJUSTIFICAPOS: lcObservacionespos,
					SOLICITADO: taDatosCups.SOLICITADO===undefined?'':taDatosCups.SOLICITADO,
					OBJETIVO: taDatosCups.OBJETIVO===undefined?'':taDatosCups.OBJETIVO,
					RIESGO: taDatosCups.RIESGO===undefined?'':taDatosCups.RIESGO,
					TIPOR: taDatosCups.TIPOR===undefined?'':taDatosCups.TIPOR,
					DIAGNOSTICONP: taDatosCups.DIAGNOSTICONP===undefined?'':taDatosCups.DIAGNOSTICONP,
					EXISTE: taDatosCups.EXISTEPOSPR===undefined?'':taDatosCups.EXISTEPOSPR,
					PROCEDIMPOS: taDatosCups.PROCEDIMPOS===undefined?'':taDatosCups.PROCEDIMPOS,
					CODIGOPOS: taDatosCups.CODIGOPOS===undefined?'':taDatosCups.CODIGOPOS,
					CANTIDADPOS: taDatosCups.CANTIDADPOS===undefined?'':taDatosCups.CANTIDADPOS,
					RESPUESTA: taDatosCups.RESPUESTAP===undefined?'':taDatosCups.RESPUESTAP,
					RESUMEN: taDatosCups.RESUMENNP===undefined?'':taDatosCups.RESUMENNP,
					BIBLIOGRAFIA: taDatosCups.BIBLIOGRAFIAP===undefined?'':taDatosCups.BIBLIOGRAFIAP,
					PACIENTE: taDatosCups.PACIENTEPR===undefined?'':taDatosCups.PACIENTEPR,
					POS: taDatosCups.POS===undefined?'':taDatosCups.POS,
					ACCION: '',
					HEMOCOMPONENTE: lcTipoHemocomponente,
					IDCUPS:lniD,
					ENTIDADNOPOS: gcTipoNoposOM,
					SIEMPRENOPOS: lcSiempreNopos,
					REGISTROMIPRES: oProcedimientosOrdMedica.gcRegistroCupsMipres,
					TIPOMIPRES: oProcedimientosOrdMedica.gcTipoCupsMipres,
				}
			});
		}

		if (lcCodigoCups==oProcedimientosOrdMedica.gcCupsNuclear){
			oProcedimientosOrdMedica.cupsCardiologiaNoInvasiva(lcCodigoCups)
		}
		oProcedimientosOrdMedica.aDatosProcedimiento=oModalJustificacionPos.gcCiePrincipal=	'';
		$('#buscarProcedimientoOM').focus();

		if (oProcedimientosOrdMedica.gcModuloAyudaCups==='A'){
			oModalAyudaProcedimientos.aCupsSelecionado.shift();
			if($("#divObservacionCups").is(':visible')){
				$("#divObservacionCups").modal('hide');
				$('#divObservacionCups').on('hidden.bs.modal', oModalAyudaProcedimientos.solicitarObservaciones);
			}else{
				$("#divObservacionCups").modal('hide');
				oModalAyudaProcedimientos.solicitarObservaciones();
			}
		}else{
			oProcedimientosOrdMedica.aCupsSelecionado.shift();
			oProcedimientosOrdMedica.validarTipoProcedimiento();
		}

	},

	insertarProcedimientoCTC: function(){
		laDatosCTC = OrganizarSerializeArray(oModalProcedimientoCTC.obtenerDatos());
		oModalProcedimientoCTC.inicializaProcedimientoCTC();
		var lcCodigoCupsNP=oModalAyudaProcedimientos.aDatosProcedimiento.CODIGO;
		var lcDescripcionCupsNP=oModalAyudaProcedimientos.aDatosProcedimiento.DESCRIPCION;
		var lcObservaciones=oProcedimientosOrdMedica.gcObservaciones!='' ? oProcedimientosOrdMedica.gcObservaciones : oModalObservacionesCups.gcObservaciones;
		var lcCantidad=parseInt(laDatosCTC.CantidadNP===undefined?'':laDatosCTC.CantidadNP.trim());
		var lcPosNopos=oModalAyudaProcedimientos.aDatosProcedimiento.POSNOPOS=='NOPB' ? 'NOPOS' : 'POS';
		var lcPosNoTexto=lcPosNopos=='NOPOS' ? 'NOPOS' : (oModalJustificacionPos.gcCiePrincipal!='' ? 'POS-JUSTIFICAR' : 'POS');
		var lcEspecialidad=oModalAyudaProcedimientos.aDatosProcedimiento.ESPECIALIDAD;
		var lcSiempreNopos=oModalAyudaProcedimientos.aDatosProcedimiento.SIEMPRENOPOS;

		var laProcedimientos = {CODIGO: lcCodigoCupsNP,
								DESCRIPCION: lcDescripcionCupsNP,
								OBSERVACIONES: lcObservaciones,
								CANTIDADCUPS: lcCantidad,
								POSNOPOS: lcPosNopos,
								POSNOTEXTO:lcPosNoTexto,
								ESPECIALIDAD:lcEspecialidad,
								HEXALIS: oProcedimientos.gcCupsHexalis,
								AGFA: oProcedimientosOrdMedica.gcEnvioAgfa,
								MODELOEQUIPO: oProcedimientosOrdMedica.gcEnvioAgfa!='' ? 'AGFA' : '',
								TIPOADT: oProcedimientosOrdMedica.gcEnvioAgfa!='' ? 'A02' : '',
								TIPOMENSAJE: oProcedimientosOrdMedica.gcEnvioAgfa!='' ? 'ADT' : '',
								LINEA: 0,
								SOLICITADO: laDatosCTC.SolicitadoNP===undefined?'':laDatosCTC.SolicitadoNP,
								OBJETIVO: laDatosCTC.ObjetivoNP===undefined?'':laDatosCTC.ObjetivoNP,
								RIESGO: laDatosCTC.selRiesgoNP===undefined?'0':laDatosCTC.selRiesgoNP,
								TIPOR: laDatosCTC.chkRiesgoNP===undefined?'0':laDatosCTC.chkRiesgoNP,
								DIAGNOSTICONP: laDatosCTC.cCodigoCieOM===undefined?'':laDatosCTC.cCodigoCieOM,
								EXISTEPOSPR: laDatosCTC.chkExistePOSPr===undefined?'':laDatosCTC.chkExistePOSPr,
								PROCEDIMPOS: laDatosCTC.descripcionProcedimientoP===undefined?'':laDatosCTC.descripcionProcedimientoP,
								CODIGOPOS: laDatosCTC.codigoProcedimientoP===undefined?'':laDatosCTC.codigoProcedimientoP,
								CANTIDADPOS: (laDatosCTC.CantidadP===undefined?'':laDatosCTC.CantidadP.trim()),
								RESPUESTAP: (laDatosCTC.RespuestaP===undefined?'':laDatosCTC.RespuestaP.trim()),
								RESUMENNP: (laDatosCTC.edtResumenP===undefined?'':laDatosCTC.edtResumenP.trim()),
								BIBLIOGRAFIAP: (laDatosCTC.BibliografiaP===undefined?'':laDatosCTC.BibliografiaP.trim()),
								PACIENTEPR: (laDatosCTC.chkPacientePr===undefined?'':laDatosCTC.chkPacientePr),
								POS: '1',
								ACCION: '',
								HEMOCOMPONENTE: oProcedimientosOrdMedica.gcEsHemocomponente,
								ENTIDADNOPOS: gcTipoNoposOM,
								SIEMPRENOPOS: lcSiempreNopos,
								REGISTROMIPRES: oProcedimientosOrdMedica.gcRegistroCupsMipres,
								TIPOMIPRES: oProcedimientosOrdMedica.gcTipoCupsMipres
								};

		oProcedimientosOrdMedica.alistarRegistro(lcCodigoCupsNP, laProcedimientos);
	},

	modificarProcedimiento: function(camposProcedimiento) {
		lcCodigoCups = camposProcedimiento.lcCodigoCups.trim();
		$('#tblProcedimientoOM').bootstrapTable('remove', {
			field: 'CODIGO',
			values: [lcCodigoCups]
		});
		oProcedimientosOrdMedica.adicionarFilaCups(camposProcedimiento);
	},

	textoConPortatil: function () {
		oProcedimientosOrdMedica.gcObservaciones = oProcedimientosOrdMedica.gcTextoPortatil + ' ' + oProcedimientosOrdMedica.gcObservaciones;
	},

	inicializaCampos: function (tcCodigoCups) {
		if (tcCodigoCups==''){
			let seleccionaEnfoque = document.getElementById("cProcedimientoOM");
			$("#inpCantidadCupsOM,#inpFrecuenciaCupsOM,#cCodigoProcedimientoOM,#cDescripcionProcedimientoOM").val('');
			oProcedimientosOrdMedica.gnCantidadCups = oProcedimientosOrdMedica.gnFrecuenciaCups = 0;
			oProcedimientosOrdMedica.inicializaVariables();
			seleccionaEnfoque.focus();
		}
		$("#txtInformacionClinicaCups,#selServicioRealizaOM").val('');
		$("#cCodigoProcedimientoOM,#cDescripcionProcedimientoOM,#inpCantidadCupsOM,#inpFrecuenciaCupsOM,#txtInformacionClinicaCups,#selServicioRealizaOM").removeClass("is-valid");
		$('#chkPortatilCupsOM').prop('checked',false);
		oModalJustificacionPos.aDatosJustificacion={};
		oProcedimientosOrdMedica.activaPortatil(oProcedimientos.gcCupsEspecialidad);
		oProcedimientosOrdMedica.activaCupsUrgencias('');
	},

	inicializaVariables: function () {
		oProcedimientosOrdMedica.gcCupsOrdenar=oProcedimientosOrdMedica.gcDescripcionOrdenar=oProcedimientosOrdMedica.gcEsHemocomponente='';
		oProcedimientos.gcCupsEspecialidad='';
	},

	verificaHemocomponente: function() {
		var taTablaValida = oProcedimientosOrdMedica.gotableProcedimientosOM.bootstrapTable('getData');

		if (taTablaValida.length==0){
			oModalHemocomponentes.gaDatosHemocomponente=[];
		}else{
			var lcHemocomponente='';
			$.each(taTablaValida, function( lcKey, loTipo ) {
				lcTipoHemocompoenente=loTipo['HEMOCOMPONENTE'].trim();
				if (lcTipoHemocompoenente!=''){
					lcHemocomponente=lcTipoHemocompoenente;
				}
			});
		}

		if (lcHemocomponente==''){
			oModalHemocomponentes.gaDatosHemocomponente=[];
		}
	},

	iniciarTablaProcedimientos: function(){
		oProcedimientosOrdMedica.gotableProcedimientosOM.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-dark',
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
				title: 'Código',
				field: 'CODIGO',
				width: 5, widthUnit: "%",
				halign: 'center',
				align: 'center'
			},
			{
				title: 'Descripción',
				field: 'DESCRIPCION',
			  	width: 35, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Información Clínica',
				field: 'OBSERVACIONES',
			  	width: 40, widthUnit: "%",
				halign: 'center',
				formatter: function(tnValor, toFila) {
					return '<input type="text" class="form-control form-control-sm col-12 modobservacion" data-id="'+toFila.IDCUPS+'" data-campo="OBSERVACIONES" value="'+tnValor+'">';
				},

				align: 'left'
			},
			{
				title: 'POS/NoPOS',
				field: 'POSNOPTEXTO',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Acción',
				field: 'ACCION',
				align: 'center',
				clickToSelect: false,
				events: this.eventoprocedimiento,
				formatter: this.formatoAcciones
			}
		  ]
		});
	},

	eventoprocedimiento:  {
		'click .eliminaCups': function (e, value, row, index) {
			fnConfirm('Desea eliminar el procedimiento?', false, false, false, false, function(){
				$('#tblProcedimientoOM').bootstrapTable('remove', {
				field: 'IDCUPS',
				values: [row.IDCUPS]
				});
				oProcedimientosOrdMedica.inicializaCampos();
				oProcedimientosOrdMedica.verificaHemocomponente();
				$('#cProcedimientoOM').focus();
			},'');
		}
	},

	formatoAcciones: function (value, row, index) {
		return [
		  '<a class="eliminaCups" href="javascript:void(0)" title="Eliminar procedimiento">',
		  '<i class="fas fa-trash-alt" style="color:#E96B50"></i>',
		  '</a>'
		].join('')
	},

	validacion: function() {
		var lbValido = true;
		var aCupsSolicitados = oProcedimientosOrdMedica.gotableProcedimientosOM.bootstrapTable('getData');
		oProcedimientosOrdMedica.lcObjetoError = '';
		oProcedimientosOrdMedica.lcFormaError = '';

		if(aCupsSolicitados != ''){
			$.each(aCupsSolicitados, function( lcKey, loTipo ) {
				if(loTipo['TIPO']=='' || loTipo['CODIGO']=='' || loTipo['DESCRIPCION']=='' || loTipo['OBSERVACIONES']==''
				|| loTipo['CANTIDAD']=='' || loTipo['POSNOPOS']==''  || loTipo['ESPECIALIDAD']==''
				){
					oProcedimientosOrdMedica.lcMensajeError = 'Existen datos pendientes por registrar en los procedimientos.';
					oProcedimientosOrdMedica.lcFormaError = 'FormCupsOM';
					oProcedimientosOrdMedica.lcObjetoError = 'cProcedimientoOM';
					lbValido = false;
				}
			});
		}
		return lbValido;
	},

	cupsCardiologiaNoInvasiva: function(tcCupsMedicinaNuclear)
	{
		$.ajax({
			type: "POST",
			url: oProcedimientosOrdMedica.gcUrlAjax,
			data: {accion: 'consultaCupsNoInvasivos', lcProcedimiento: tcCupsMedicinaNuclear},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					oProcedimientosOrdMedica.aDatosInvasivos.push({
						TIPO: 'CUPS',
						CODIGO: toDatos.TIPOS.CODIGO,
						DESCRIPCION: toDatos.TIPOS.DESCRIPCION,
						OBSERVACIONES: '',
						CANTIDAD: 1,
						POSNOPOS: toDatos.TIPOS.POSNOPOS=='NOPB' ? 'N' : 'P',
						POSNOPTEXTO: toDatos.TIPOS.POSNOPOS=='NOPB' ? 'NOPOS' : 'POS',
						ESPECIALIDAD: toDatos.TIPOS.ESPECIALIDAD,
						});
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta procedimientos no invasivos por medicina nuclear.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta procedimientos no invasivos por medicina nuclear.");
		});
	},

	observacionNuclear: function() {
		var lcObservacion='';
		var taTablaValida=oProcedimientosOrdMedica.gotableProcedimientosOM.bootstrapTable('getData');

		$.each(taTablaValida, function( lcKey, loTipo ) {
			if (loTipo.CODIGO==oProcedimientosOrdMedica.gcCupsNuclear){
				lcObservacion=loTipo.OBSERVACIONES;
			}
		});
		return lcObservacion;
	},

	incluirCupsNoInvasivos: function() {
		var taCupInvasivos=oProcedimientosOrdMedica.aDatosInvasivos;
		var taTablaValida=oProcedimientosOrdMedica.gotableProcedimientosOM.bootstrapTable('getData');
		llverificaExisteGamagrafia=oProcedimientosOrdMedica.verificaCodigoExiste(oProcedimientosOrdMedica.gcCupsNuclear, taTablaValida);
		var lcObservaciones=oProcedimientosOrdMedica.observacionNuclear;

		if(!llverificaExisteGamagrafia){
			$.each(taCupInvasivos, function( lcKey, loTipo ) {
				laDatosCups = { CANTIDADCUPS: loTipo.CANTIDAD, CODIGO: loTipo.CODIGO, DESCRIPCION:  loTipo.DESCRIPCION,
								POSNOPOS: loTipo.POSNOPOS,
								POSNOTEXTO: loTipo.POSNOPTEXTO,
								ESPECIALIDAD: loTipo.ESPECIALIDAD,
								OBSERVACIONES: lcObservaciones};

				llverificaExiste = oProcedimientosOrdMedica.verificaCodigoExiste(loTipo.CODIGO, taTablaValida);
				if(llverificaExiste){
					oProcedimientosOrdMedica.adicionarProcedimiento(laDatosCups);
				}
			});
		}
	},

	obtenerDatos: function() {
		var laProcedimientos = $('#tblProcedimientoOM').bootstrapTable('getData');
		return laProcedimientos;
	}

}
