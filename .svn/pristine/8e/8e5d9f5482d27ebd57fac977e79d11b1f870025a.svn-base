var oModalHemocomponentes = {
	lcTitulo: 'Hemocomponentes Sanguineos',
	gcUrlAjax: 'vista-comun/ajax/modalHemocomponentes.php',
	gaDatosHemocomponente: [],
	gaDatosProcedimiento: [],
	gcCupsHemocomponente:'',
	fnEjecutar: false,
	
	inicializar: function()
	{
		this.inicializaHemocomponentes();
		oDiagnosticos.consultarDiagnostico('txtDiagnosticoHemoc','cCodigoDiagnosticoHemoc','cDescripcionDiagnosticoHemoc','','txtProcedimientoRealizarHemoc');
		this.cargarListas($('#selTipoReservaHemoc'),'consultartiporeserva','Listado tipo reserva');
		this.cargarListas($('#selHemoclasificacionHemoc'),'consultarhemoclasificacion','Listado hemoclasificacion');
		this.cargarListas($('#selRiesgoTransfucionalHemoc'),'consultarriesgotransfucional','Listado riesgo transfucional');
		this.ayudasHemocomponentes('ayudaTipoReserva', 'Tipo reserva');
		this.ayudasHemocomponentes('ayudaRiesgoTransfucional', 'Listado riesgo transfucional');
		
		$('#FormHemocomponentes .input-group.date').datepicker({
			autoclose: true,
			clearBtn: true,
			daysOfWeekHighlighted: "0,6",
			format: "yyyy-mm-dd",
			language: "es",
			todayBtn: true,
			todayHighlight: true,
			toggleActive: true,
			weekStart: 1,
		});
		$('#btnGuardaHemocomponentes').on('click', this.validarHemocomponentes);
		$('#btnSalirHemocomponentes').on('click', this.cancelarHemocomponentes);
		$('#btnTipoReservaHemoc').on('click', function(){fnAlert($("#ayudaTipoReservaHemoc").html(), 'TIPO DE RESERVA','fas fa-atlas','blue','large');});
		$('#btnRiesgoTransfucionalHemoc').on('click', function(){fnAlert($("#ayudaRiesgoTransfucionalHemoc").html(), 'LISTA DE RIESGO TRANSFUCIONAL','fas fa-atlas','blue','large');});
	},
	
	CargarReglas: function(tcTipo, tcForma, tcTitulo){	
		$.ajax({
			type: "POST",
			url: oModalHemocomponentes.gcUrlAjax,
			data: {accion: tcTipo, lcTitulo: tcTitulo},
			dataType: "json"
		})
		.done(function(loDatos) {
			loObjObl=loDatos.REGLAS;
			try {
				var lopciones={};
					$.each(loObjObl, function( lcKey, loObj ) {
						var llRequiere = true;

						if(loObjObl[lcKey]['REQUIERE'] !==''){
							llRequiere = eval(loObjObl[lcKey]['REQUIERE']);
						}

						if(llRequiere){
							if(loObjObl[lcKey]['CLASE']=="1" || loObjObl[lcKey]['CLASE']=="3" ){
								lopciones=Object.assign(lopciones,JSON.parse(loObjObl[lcKey]['REGLAS']));
							} else {
								var loTemp = loObjObl[lcKey]['REGLAS'].split('¤');
								lopciones[loTemp[0]]={required: function(element){
									return ReglaDependienteValor(loTemp[1],loTemp[2],loDatos.REGLAS[lcKey]['OBJETO']);
								}};
								if(loTemp.length==4){
									lopciones[loTemp[0]]=Object.assign(lopciones[loTemp[0]],JSON.parse(loTemp[3]));
								}
							}
							if(loObjObl[lcKey]['CLASE']=="1" || loObjObl[lcKey]['CLASE']=="2" ){
								$('#'+loObjObl[lcKey]['OBJETO']).addClass("required");
							}
						}
					});
					oModalHemocomponentes.ValidarReglas(tcForma, lopciones);

			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de objetos obligatorios para hemocomponentes WEB.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar objetos obligatorios para hemocomponentes WEB. ');
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
	
	ayudasHemocomponentes: function(lcTipo,mensaje) {
		$.ajax({
			type: "POST",
			url: oModalHemocomponentes.gcUrlAjax,
			data: {accion: lcTipo},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){

					if (lcTipo=='ayudaTipoReserva'){
						$("#ayudaTipoReservaHemoc").html(loTipos.TIPOS);
					}
					
					if (lcTipo=='ayudaRiesgoTransfucional'){
						$("#ayudaRiesgoTransfucionalHemoc").html(loTipos.TIPOS);
					}	
					
				} else {
					fnAlert(loTipos.error);
				}

			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta de ' + mensaje +'.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar ' + mensaje +'.');
		});
		return this;
	},
	
	cargarListas: function(id,lcTipo,mensaje) {
		var loSelect = id;

		$.ajax({
			type: "POST",
			url: oModalHemocomponentes.gcUrlAjax,
			data: {accion: lcTipo},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
						loSelect.append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
					});
				} else {
					fnAlert(loTipos.error);
				}

			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta de ' + mensaje +'.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar ' + mensaje +'.');
		});
		return this;
	},
	
	listaJustificacion: function(tcCupsSolicitar) {
		var loSelect = $('#selTipoJustificacionHemoc');
		loSelect.empty();

		$.ajax({
			type: "POST",
			url: oModalHemocomponentes.gcUrlAjax,
			data: {accion: 'consultarListaJustificacion', lcProcedimiento: tcCupsSolicitar},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					loSelect.append('<option value=""></option>');
					$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
						loSelect.append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
					});
				} else {
					fnAlert(loTipos.error);
				}
				$('#txtObsJustificacionHemoc').val('');
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta de lista justificación.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar lista justificación.');
		});
		return this;
	},
	
	hemocomponenteOrdenado: function(tcCupsSolicitar,taDatosCups) {
		oModalHemocomponentes.gcCupsHemocomponente=tcCupsSolicitar;
		oModalHemocomponentes.gaDatosProcedimiento=taDatosCups;
		oProcedimientosOrdMedica.gcEsHemocomponente=oModalHemocomponentes.gaDatosProcedimiento.TIPOHEMOCOMPONENTE;
		oProcedimientos.gcCupsSolicitar=oModalHemocomponentes.gcCupsHemocomponente
		oProcedimientos.gcCupsDescripcion=oModalHemocomponentes.gaDatosProcedimiento.DESCRIPCION;
	
		$.ajax({
			type: "POST",
			url: oModalHemocomponentes.gcUrlAjax,
			data: {accion: 'consultarHemocomponenteOrdenado', lnIngreso: aDatosIngreso.nIngreso, lcProcedimiento: tcCupsSolicitar},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					oModalHemocomponentes.listaJustificacion(tcCupsSolicitar);
					if (loTipos.TIPOS.length!=0){
						lnCantidad = loTipos.TIPOS.CANTIDAD;
						
						laEstados = loTipos.TIPOS.DESCRIPCION.split('&')
						$.each(laEstados, function( lcKey, loTipo ) {
							if (loTipo!=''){
								lcEstados = '*' + loTipo +'<br>';
							}
						});
						
						lcTexto = 'Se ha(n) solicitado ' + lnCantidad + ' procedimiento(s) ' + tcCupsSolicitar
						+ '-' + oProcedimientos.gcCupsDescripcion
						+ ', los cuales se encuentran con el(los) siguiente(s) estado(s):' + '<br>'
						+ lcEstados  +'<br>' + 'Desea solicitar nuevamente el procesamiento?';
						
						fnConfirm(lcTexto, oModalHemocomponentes.lcTitulo, false, false, 'large',
							{
								text: 'Aceptar',
									action: function(){
										oModalHemocomponentes.mostrar();
									}
							},

							{ 
								text: 'Cancelar',
									action: function(){
										oProcedimientosOrdMedica.inicializaCampos('');
									}
							}
						);
						
					}else{
						oModalHemocomponentes.mostrar();
					}
					
				} else {
					fnAlert(loTipos.error);
				}

			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta hemocomponente ordenado.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar  hemocomponente ordenado.');
		});
		return this;
	},
	
	mostrar: function(tfEjecutar)
	{
		oModalHemocomponentes.CargarReglas("Reglas","#FormHemocomponentes",oProcedimientosOrdMedica.gcEsHemocomponente);
		$('#selTipoJustificacionHemoc').val('');
		$("#divHemocomponentes").modal('show');
		$('#txtCupsHemocomponente').text(oProcedimientos.gcCupsSolicitar+ ' - ' + oProcedimientos.gcCupsDescripcion);
		oModalHemocomponentes.fnEjecutar = tfEjecutar;
	},
	
	ocultar: function()
	{
		$("#divHemocomponentes").modal('hide');
		if (typeof oModalHemocomponentes.fnEjecutar==='function'){
			oModalHemocomponentes.fnEjecutar();
		}
	},
	
	inicializaHemocomponentes: function () {
		oModalHemocomponentes.gaDatosHemocomponente=[];
		$("#selTipoReservaHemoc, #selHemoclasificacionHemoc, #txtFechaEstimadaHemoc, #txtHbHemoc, #txtHematocritoHemoc, #txtPlaquetasHemoc").val('');
		$("#txtInrHemoc, #txtPtHemoc, #txtPttHemoc, #txtFibronogenoHemoc, #txtDiagnosticoHemoc, #cCodigoDiagnosticoHemoc, #cDescripcionDiagnosticoHemoc").val('');
		$("#txtProcedimientoRealizarHemoc, #selRiesgoTransfucionalHemoc, #selRequiereFiltroHemoc, #selTipoJustificacionHemoc, #txtObsJustificacionHemoc").val('');
	},
	
	validarHemocomponentes: function () {
		laDatosHemocomponentes = [];
		var ldFechaActual = new Date();
		var lcTipoReserva = $("#selTipoReservaHemoc").val();
		var lcHemoclasificacion = $("#selHemoclasificacionHemoc").val();
		var lcProcedimientoRealizar = $("#txtProcedimientoRealizarHemoc").val();
		var lcFechaEstimada = $("#txtFechaEstimadaHemoc").val();
		var lclabHb = $("#txtHbHemoc").val();
		var lclabHematocrito = $("#txtHematocritoHemoc").val();
		var lclabPlaquetas = $("#txtPlaquetasHemoc").val();
		var lclabInr = $("#txtInrHemoc").val();
		var lclabPt = $("#txtPtHemoc").val();
		var lclabPtt = $("#txtPttHemoc").val();
		var lclabFibronogeno = $("#txtFibronogenoHemoc").val();
		var lcDiagnostico = $("#cCodigoDiagnosticoHemoc").val();
		var lcRiesgo = $("#selRiesgoTransfucionalHemoc").val();
		var lcFiltro = $("#selRequiereFiltroHemoc").val();
		var lcTipoJustificacion = $("#selTipoJustificacionHemoc").val();
		var lcJustificacion = $("#txtObsJustificacionHemoc").val();
		var lcFechaActual = ldFechaActual.getFullYear()+((ldFechaActual.getMonth() + 1).toString()).padStart(2, "0")+((ldFechaActual.getDate()).toString()).padStart(2, "0");
		var lnFechaEstimada = parseInt(lcFechaEstimada.replace(/-/g,''));
		var lnFechaActual = parseInt(lcFechaActual.replace(/-/g,''));
		var lcFechaEstimadaCups = strNumAFecha(lnFechaEstimada);
		var ldFechaEstimada = Date.parse(lcFechaEstimadaCups);
		var lcFechaActualDate = ldFechaActual.getFullYear() + "-" + (ldFechaActual.getMonth() + 1) + "-" + ldFechaActual.getDate();
		var ldFechaHoy = Date.parse(lcFechaActualDate);
		var lnDiferenciaDias = Math.floor((ldFechaEstimada - ldFechaHoy)/(1000*60*60*24)) + 1;
		
		if (lcTipoReserva==''){
			$('#selTipoReservaHemoc').focus();
			fnAlert('Tipo de reserva obligatorio, revise por favor.');
			return false;
		}
		
		if (lcTipoReserva!='3' && lcFechaEstimada==''){
			$('#txtFechaEstimadaHemoc').focus();
			fnAlert('Fecha estimada de procedimiento obligatoria, revise por favor.');
			return false;
		}
		
		if (lnFechaEstimada < lcFechaActual){
			$('#txtFechaEstimadaHemoc').focus();
			fnAlert('Fecha procedimiento no puede ser menor a la fecha actual, revise por favor.');
			return false;
		}
		
		if (lnDiferenciaDias > oProcedimientosOrdMedica.gnDiasProcedimiento){
			$('#txtFechaEstimadaHemoc').focus();
			lcTextoMensaje = 'Fecha vencimiento excede máximo de días permitidos (' + oProcedimientosOrdMedica.gnDiasProcedimiento + '), revise por favor.';
			fnAlert(lcTextoMensaje);
			return false;
		}
		
		if (oProcedimientosOrdMedica.gcEsHemocomponente=='CRIOS' && lclabFibronogeno==''){
			$('#txtFibronogenoHemoc').focus();
			fnAlert('Fibronógeno obligatorio, revise por favor.');
			return false;
		}
		
		if (oProcedimientosOrdMedica.gcEsHemocomponente=='GR'){
			if (lclabHb=='' || lclabHematocrito==''){
				$('#txtHbHemoc').focus();
				fnAlert('Hb/Hetamorcrito obligatorio(s), revise por favor.');
				return false;
			}	
		}
		
		if (oProcedimientosOrdMedica.gcEsHemocomponente=='PFC'){
			if (lclabInr=='' || lclabPt=='' || lclabPtt==''){
				$('#txtInrHemoc').focus();
				fnAlert('Inr/Pt/Pttb obligatorio(s), revise por favor.');
				return false;
			}	
		}
		
		if (oProcedimientosOrdMedica.gcEsHemocomponente=='PLT'){
			if (lclabPlaquetas==''){
				$('#txtPlaquetasHemoc').focus();
				fnAlert('Plaquetas obligatorio, revise por favor.');
				return false;
			}	
		}
		
		if (lcDiagnostico==''){
			$('#txtDiagnosticoHemoc').focus();
			fnAlert('Diagnóstico obligatorio, revise por favor.');
			return false;
		}
		
		if (lcProcedimientoRealizar==''){
			$('#txtProcedimientoRealizarHemoc').focus();
			fnAlert('Indicación de la transfusión (procedimiento a realizar) obligatoria, revise por favor.');
			return false;
		}
		
		if (lcRiesgo==''){
			$('#selRiesgoTransfucionalHemoc').focus();
			fnAlert('Riesgo transfusional obligatoria, revise por favor.');
			return false;
		}
		
		if (lcFiltro==''){
			$('#selRequiereFiltroHemoc').focus();
			fnAlert('Requiere filtro obligatoria, revise por favor.');
			return false;
		}
		
		if (lcTipoJustificacion==''){
			$('#selTipoJustificacionHemoc').focus();
			fnAlert('Tipo de justificación obligatoria obligatoria, revise por favor.');
			return false;
		}else{
			if (parseInt(lcTipoJustificacion)>=90 && lcJustificacion==''){
				$('#txtObsJustificacionHemoc').focus();
				fnAlert('Observaciones de la justificación obligatorio, revise por favor.');
				return false;
			}
		}
		oModalHemocomponentes.eliminarRegistros()
		
		lnIndice=1;
		lcDescripcion=lcTipoReserva.trim().padEnd(15," ") +lcHemoclasificacion.trim().padEnd(15," ")
					+lcFechaEstimada.replace(/-/g,'').trim().padEnd(15," ")+lclabHb.trim().padEnd(15," ")
					+lclabHematocrito.trim().padEnd(15," ")+lclabPlaquetas.trim().padEnd(15," ")+lclabInr.trim().padEnd(15," ")
					+lclabPt.trim().padEnd(15," ")+lclabPtt.trim().padEnd(15," ")+lclabFibronogeno.trim().padEnd(15," ")
					+lcDiagnostico.trim().padEnd(10," ")+lcRiesgo.trim().padEnd(10," ")+lcFiltro.trim().padEnd(10," ");
		oModalHemocomponentes.gaDatosHemocomponente.push({INDICE: lnIndice, TIPOJUSTIFICACION: '', CODIGOJUSTIFICACION: '', DESCRIPCION: lcDescripcion });

		lnIndice=2;
		lcDescripcion=lcProcedimientoRealizar;
		oModalHemocomponentes.gaDatosHemocomponente.push({INDICE: lnIndice, TIPOJUSTIFICACION: '', CODIGOJUSTIFICACION: '', DESCRIPCION: lcDescripcion });

		lnIndice=3;
		lcDescripcion=lcJustificacion;
		oModalHemocomponentes.gaDatosHemocomponente.push({INDICE: lnIndice, TIPOJUSTIFICACION: lcTipoJustificacion, CODIGOJUSTIFICACION: oProcedimientosOrdMedica.gcEsHemocomponente, DESCRIPCION: lcDescripcion });
		$("#divHemocomponentes").modal("hide");
		oProcedimientosOrdMedica.alistarRegistro(oModalHemocomponentes.gcCupsHemocomponente,oModalHemocomponentes.gaDatosProcedimiento);
	},
	
	eliminarRegistros: function() {
		if (oModalHemocomponentes.gaDatosHemocomponente.length>0){
			var taTemporal = [];
		
			$.each(oModalHemocomponentes.gaDatosHemocomponente, function(index, loTipo) {
				lnIndice=loTipo.INDICE;
				lcCodigoGrupo=loTipo.CODIGOJUSTIFICACION;
				
				if (lnIndice==3 && lcCodigoGrupo!=oProcedimientosOrdMedica.gcEsHemocomponente){
					taTemporal.push({INDICE: loTipo.INDICE, TIPOJUSTIFICACION: loTipo.TIPOJUSTIFICACION, 
					CODIGOJUSTIFICACION: loTipo.CODIGOJUSTIFICACION, DESCRIPCION:  loTipo.DESCRIPCION });
				}	
			});
			oModalHemocomponentes.gaDatosHemocomponente = [];
			oModalHemocomponentes.gaDatosHemocomponente=taTemporal;
		}	
	},
	
	cancelarHemocomponentes: function () {
		fnConfirm('Desea cancelar la orden del procedimiento?', oModalHemocomponentes.lcTitulo, false, false, 'medium',
			{
				text: 'Aceptar',
					action: function(){
						oProcedimientosOrdMedica.inicializaCampos('');
						oProcedimientosOrdMedica.verificaHemocomponente();
						
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
						$('#divHemocomponentes').modal('show');
					}
			}
		);
	},
	
	obtenerDatos: function() {
		var laDatosHemocomponente = oModalHemocomponentes.gaDatosHemocomponente;
		return laDatosHemocomponente;		
	},

}