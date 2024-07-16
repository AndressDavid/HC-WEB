var oModalAyudaProcedimientos = {
	lcTitulo: 'Ayuda procedimientos',
	gcUrlAjax: 'vista-comun/ajax/modalAyudaProcedimientos.php',
	fnEjecutar: false,
	gaTablaProcedimientos: '', gcModuloViene: '', gcTipoEntidadPosnopos: '', gcCodigoCupsAyuda:'', gcCupsPaquete:'',
	gcDatosporPaquete: '',
	oRoweditCTC: {},
	aPedirCTC: [], aCupsSelecionado: [], aDatosProcedimiento: [], aCupsObservacion: [],
	llSolicitarCTC: false,

	inicializar: function()
	{
		this.iniciarTablaAyudaProcedimientos();
		$('#btnAceptarAyudaProcedimiento').on('click', oModalAyudaProcedimientos.traerCupsSelecionados);
		this.textoPaquetes();
	},

	textoPaquetes: function() {
		$.ajax({
			type: "POST",
			url: oModalAyudaProcedimientos.gcUrlAjax,
			data: {accion: 'consultaTextoPaquetes'},
			dataType: "json",
		})
		.done(function( loTipos ) {
			lcTextoPaquete=loTipos.TIPOS.split('~')[0];
			lcTextoInterconsulta=loTipos.TIPOS.split('~')[1];
			try {
				if (loTipos.error == ''){
					$('#lblPaquetesAyuda').text(lcTextoPaquete);
					$('#lblInterconsultasAyuda').text(lcTextoInterconsulta);
				} else {
					fnAlert(loTipos.error);
				}

			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta texto paquetes.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar texto paquetes.');
		});
		return this;
	},
	
	adicionarProcedimientos: function(taListaCups,taTablaProcedimientos,tcModulo,tcTipoEntidadPosnopos){
		oModalAyudaProcedimientos.gaTablaProcedimientos=taTablaProcedimientos;
		oModalAyudaProcedimientos.gcModuloViene=tcModulo;
		oModalAyudaProcedimientos.gcTipoEntidadPosnopos=tcTipoEntidadPosnopos;
		$('#tblAyudaProcedimiento').bootstrapTable('refreshOptions', {data: taListaCups});
	},

	mostrar: function(tfEjecutar)
	{
		if (oModalAyudaProcedimientos.gcModuloViene=='OM'){	
			oProcedimientosOrdMedica.gcModuloAyudaCups='A';
		}else{
			$("#lblPaquetesAyuda").css("visibility","hidden");
			$("#lblInterconsultasAyuda").css("visibility","hidden");
		}
		$("#divAyudaProcedimiento").modal('show');
		$("#divAyudaProcedimiento .search").css("width","100%");
		$('#txtListaProcedimientos').val('');
		setTimeout(function() {
			$('#tblAyudaProcedimiento').bootstrapTable('selectPage',2).bootstrapTable('selectPage',1);
		}, (200));
		oModalAyudaProcedimientos.fnEjecutar = tfEjecutar;
	},

	ocultar: function()
	{
		$("#divAyudaProcedimiento").modal('hide');
		if (typeof oModalAyudaProcedimientos.fnEjecutar==='function'){
			oModalAyudaProcedimientos.fnEjecutar();
		}
	},

	desmarcarCupsSelecionados: function()
	{
		var taCupsSeleccionados = $('#tblAyudaProcedimiento').bootstrapTable('getSelections');
		var taListaCups = $('#tblAyudaProcedimiento').bootstrapTable('getData');
	
		$.each(taCupsSeleccionados, function( lnIndex, loSeleccion) {
			lcCodigoSeleccionado = loSeleccion.CODIGO;
			lnIndice = taListaCups.findIndex(loElement => loElement.CODIGO === lcCodigoSeleccionado);

			$('#tblAyudaProcedimiento').bootstrapTable('updateRow', {
			index: lnIndice,
			row: {
				0: false
			}
			});
		});
	},	
	
	traerCupsSelecionados: function()
	{
		$('#tblAyudaProcedimiento').bootstrapTable('resetSearch');
		var taCupsSeleccionados = $('#tblAyudaProcedimiento').bootstrapTable('getSelections');
		var taTablaValida = oModalAyudaProcedimientos.gaTablaProcedimientos.bootstrapTable('getData');
		oModalAyudaProcedimientos.aPedirCTC = [];
		oModalAyudaProcedimientos.aCupsSelecionado = [];
		
		oModalAyudaProcedimientos.registraDatosCups(taCupsSeleccionados, function() {
			if (oModalAyudaProcedimientos.gcModuloViene=='OM'){
				oModalAyudaProcedimientos.desmarcarCupsSelecionados();
				oModalAyudaProcedimientos.solicitarObservaciones();
			}	
			
			if (oModalAyudaProcedimientos.gcModuloViene=='OA'){
				oModalAyudaProcedimientos.cargarProcedimientos();
			}	
		});
	},

	registraDatosCups: function(taCupsSelecionados, tfPost)
	{
		oModalAyudaProcedimientos.gcCupsPaquete='';
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
						oModalAyudaProcedimientos.gcCupsPaquete=loSeleccion.TIPO;
						oModalAyudaProcedimientos.aCupsSelecionado.push({CODIGO: loSeleccion.CODIGO, 
						DESCRIPCION: loSeleccion.DESCRIPCION,
						REFERENCIA1: loSeleccion.REFERENCIA1, 						
						POSNOPOS: loSeleccion.POSNOPOS,
						PAQUETE: loSeleccion.TIPO, 
						ESPECIALIDAD: loSeleccion.ESPECIALIDAD,
						AGFA: loSeleccion.ENVIAAGFA,
						SIEMPRENOPOS: loSeleccion.SIEMPRENOPOS,
						JUSTIFICACIONPOS: loSeleccion.JUSTIFICACIONPOS,
						TIPOHEMOCOMPONENTE: loSeleccion.HEMOCOMPONENTE,
						HEXALIS: loSeleccion.HEXALIS,
						LABESPEC: loSeleccion.LABESPEC,
						});
					});	
					if (typeof tfPost == 'function') {
						tfPost();
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta registrar datos procedimientos.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta  registrar datos procedimientos.");
		});
	},
	
	validarJustificacion: function(){
		aEnviarDatos='';
		var taTablaValida = oModalAyudaProcedimientos.gaTablaProcedimientos.bootstrapTable('getData');
		var lcCodigoCups=oModalAyudaProcedimientos.aCupsObservacion.CODIGO;
		var lcDescripcionCups=oModalAyudaProcedimientos.aCupsObservacion.DESCRIPCION;
		var lcJustificacionPos=oModalAyudaProcedimientos.aCupsObservacion.JUSTIFICACIONPOS;
		var lcPosNopos=oModalAyudaProcedimientos.aCupsObservacion.POSNOPOS;
		var llverificaExiste = oModalAyudaProcedimientos.verificaCodigoExiste(lcCodigoCups,taTablaValida);

		if (lcJustificacionPos!=''){
			oModalJustificacionPos.mostrar();
		}else{
			oModalAyudaProcedimientos.validarHemocomponente();
		}	
	},

	cargarProcedimientos: function(){
		var taTablaValida = oModalAyudaProcedimientos.gaTablaProcedimientos.bootstrapTable('getData');
		
		$.each(oModalAyudaProcedimientos.aCupsSelecionado, function(lcKey, loSeleccion) {
			var lcCodigoSeleccionado = loSeleccion.CODIGO;
			var llverificaExiste = oModalAyudaProcedimientos.verificaCodigoExiste(lcCodigoSeleccionado,taTablaValida);
			llAdicionar = true ;
			
			if (llAdicionar){
				if(!llverificaExiste) {
					oModalAyudaProcedimientos.gaTablaProcedimientos.bootstrapTable('remove', {
						field: 'CODIGO',
						values: [ lcCodigoSeleccionado.trim() ]
					});
				}
				
				oModalAyudaProcedimientos.gaTablaProcedimientos.bootstrapTable('insertRow', {
					index: 1,
					row: {
						CODIGO: loSeleccion.CODIGO,
						DESCRIPCION: loSeleccion.DESCRIPCION,
						OBSERVACION: '',
						CANTIDAD: 1,
						BORRAR: '',
					}
				});
			}
		});	
		oModalAyudaProcedimientos.desmarcarCupsSelecionados();
		
		if(oModalAyudaProcedimientos.llSolicitarCTC){
			oModalAyudaProcedimientos.SolicitarJustificacionCTC();
		}
	},
	
	verificaCodigoExiste: function(tcCodigo,taTablaValida)
	{
		var llRetorno = true ;
		if(taTablaValida != ''){
			$.each(taTablaValida, function( lcKey, loTipo ) {
				if(loTipo['CODIGO']==tcCodigo){
					oModalAyudaProcedimientos.indexedit = lcKey;
					oModalAyudaProcedimientos.oRoweditCTC = loTipo;
					llRetorno = false;
					return llRetorno;
				}
			});
		};
		return llRetorno ;
	},
	
	validarHemocomponente: function(){
		var lcCodigoCups=oProcedimientosOrdMedica.aDatosProcedimiento.CODIGO!=undefined ? oProcedimientosOrdMedica.aDatosProcedimiento.CODIGO : (oModalAyudaProcedimientos.aDatosProcedimiento.CODIGO!=undefined ? oModalAyudaProcedimientos.aDatosProcedimiento.CODIGO : '');	
		var lcEsHemocomponente=oProcedimientosOrdMedica.aDatosProcedimiento.TIPOHEMOCOMPONENTE!=undefined ? oProcedimientosOrdMedica.aDatosProcedimiento.TIPOHEMOCOMPONENTE : (oModalAyudaProcedimientos.aDatosProcedimiento.TIPOHEMOCOMPONENTE!=undefined ? oModalAyudaProcedimientos.aDatosProcedimiento.TIPOHEMOCOMPONENTE : '');
		var laDatos=oProcedimientosOrdMedica.aDatosProcedimiento!='' ? oProcedimientosOrdMedica.aDatosProcedimiento : (oModalAyudaProcedimientos.aDatosProcedimiento!='' ? oModalAyudaProcedimientos.aDatosProcedimiento : '');
		
		if (lcEsHemocomponente==='' || lcEsHemocomponente===undefined){
			oProcedimientosOrdMedica.alistarRegistro(lcCodigoCups,laDatos);
		}else{
			oModalHemocomponentes.hemocomponenteOrdenado(lcCodigoCups,laDatos);
		}	
	},
	
	salirObservaciones: function(taDatosObservacion)
	{
		oModalAyudaProcedimientos.aDatosProcedimiento='';
		let lcCodigoCups=oModalAyudaProcedimientos.aCupsObservacion.CODIGO;
		let lcDescripcionCups=oModalAyudaProcedimientos.aCupsObservacion.DESCRIPCION;
		let lcReferencia1=oModalAyudaProcedimientos.aCupsObservacion.REFERENCIA1;
		let lcPosNopos=oModalAyudaProcedimientos.aCupsObservacion.POSNOPOS;
		let lcEspecialidad=taDatosObservacion.ESPECIALIDAD!='' ? taDatosObservacion.ESPECIALIDAD : oModalAyudaProcedimientos.aCupsObservacion.ESPECIALIDAD;
		let lcEnvioAgfa=oModalAyudaProcedimientos.aCupsObservacion.AGFA;
		let lcTipoHemocomponente=oModalAyudaProcedimientos.aCupsObservacion.TIPOHEMOCOMPONENTE;
		let lnCantidad=taDatosObservacion.CANTIDAD>0 ? taDatosObservacion.CANTIDAD : taDatosObservacion.FRECUENCIA;
		let lnPortatil=taDatosObservacion.PORTATIL;
		let lcServicioUrgencias=taDatosObservacion.URGENCIAS;
		let lnObservaciones=taDatosObservacion.OBSERVACIONES;
		let lcHexalis=oModalAyudaProcedimientos.aCupsObservacion.HEXALIS;
		
		oModalAyudaProcedimientos.aDatosProcedimiento = { 
					CANTIDADCUPS: lnCantidad, CODIGO: lcCodigoCups, DESCRIPCION: lcDescripcionCups, POSNOPOS: lcPosNopos, 
					REFERENCIA1: lcReferencia1, 
					POSNOTEXTO: lcPosNopos=='NOPOS' ? 'NOPOS' : (oModalJustificacionPos.gcCiePrincipal!='' ? 'POS-JUSTIFICAR' : 'POS'),
					ESPECIALIDAD: lcEspecialidad, AGFA: lcEnvioAgfa, OBSERVACIONES: lnObservaciones,
					PORTATIL: lnPortatil, SERVURGENCIAS: lcServicioUrgencias, TIPOHEMOCOMPONENTE: lcTipoHemocomponente, HEXALIS: lcHexalis};
		oModalObservacionesCups.gaDatosObervaciones='';			
	},
	
	solicitarObservaciones: function()
	{
		oProcedimientos.gcLabIndependiente='';
		var lnRegistros = oModalAyudaProcedimientos.aCupsSelecionado.length;
		if(lnRegistros > 0){
			oModalAyudaProcedimientos.aCupsObservacion=[];
			oModalAyudaProcedimientos.aCupsObservacion=oModalAyudaProcedimientos.aCupsSelecionado[0];
			oProcedimientos.gcLabIndependiente=oModalAyudaProcedimientos.aCupsObservacion.LABESPEC===undefined ? '' : oModalAyudaProcedimientos.aCupsObservacion.LABESPEC; 
			oModalObservacionesCups.iniciaObservaciones();
		}
	},	
	
	//	SE LLAMA DESDE AMBULATORIOS
	SolicitarJustificacionCTC: function()
	{
		var lnLong = oModalAyudaProcedimientos.aPedirCTC.length;
		if(lnLong > 0){
			laProcedimientos = oModalAyudaProcedimientos.aPedirCTC[0];
			oModalProcedimientoCTC.iniciaModalProcedimientoCTC(laProcedimientos, laProcedimientos, laProcedimientos.EXISTEREG);
			oModalAyudaProcedimientos.aPedirCTC.shift();
			lnLong = oModalAyudaProcedimientos.aPedirCTC.length;
			oModalAyudaProcedimientos.llSolicitarCTC = (lnLong > 0);
		}
	},
	
	iniciarTablaAyudaProcedimientos: function()
	{
		$('#tblAyudaProcedimiento').bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			locale: 'es-ES',
			undefinedText: 'N/A',
			checkboxHeader: false,
			height: '450',
			toolbar: '#toolbarModalAyudaPr',
			search: true,
			searchAlign: 'right',
			searchAccentNeutralise: true,
			trimOnsearch: true,
			searchTimeOut: 250,
			showSearchClearButton: true,
			pagination: true,
			pageSize: 250,
			pageList: [100, 250, 500, 1000, 'All'],
			columns: [
				{
					title: '',
					checkbox: true,
					width: 5, widthUnit: "%",
					halign: 'center',
					align: 'center'
				},
				{
					title: 'Código',
					field: 'CODIGO',
					width: 10, widthUnit: "%",
					halign: 'center',
					align: 'left'
				},
				{
					title: 'Procedimiento',
					field: 'DESCRIPCION',
					width: 65, widthUnit: "%",
					halign: 'center',
					align: 'left'
				},
				{
					title: 'Pos',
					field: 'POSNOPOS',
					width: 10, widthUnit: "%",
					halign: 'center',
					align: 'left'
				},
				{
					title: 'Clasificación',
					field: 'CLASIFICACION1',
					width: 10, widthUnit: "%",
					halign: 'center',
					align: 'left'
				}
			]
		}).on('check.bs.table uncheck.bs.table ' + 'check-all.bs.table uncheck-all.bs.table', function () {
			selections = oModalAyudaProcedimientos.eventoSeleccionCups();
		})
	},
	
	eventoSeleccionCups: function() 
	{
		$('input[type=text]').removeClass("is-valid");
		var lcEnter = String.fromCharCode(13);
		var lcTextoFiltro = $("#divCapturaAyudaProcedimiento .search-input").val();
		$('#txtListaProcedimientos').val('');
		$('#tblAyudaProcedimiento').bootstrapTable('refreshOptions',{searchText: ''});
		lcDescripcion = '';

		var taCupsSeleccionados = $('#tblAyudaProcedimiento').bootstrapTable('getSelections');
		$.each(taCupsSeleccionados, function(lcKey, loSeleccion) {
			lcDescripcion+= loSeleccion.CODIGO + '-' + loSeleccion.DESCRIPCION + lcEnter;
		});
		$('#tblAyudaProcedimiento').bootstrapTable('refreshOptions',{searchText: lcTextoFiltro});
		$("#divAyudaProcedimiento .search").css("width","100%");
		$('#txtListaProcedimientos').val(lcDescripcion);
		$('#divCapturaAyudaProcedimiento .search-input').focus();
	}
	
}
