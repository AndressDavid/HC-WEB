var oProcedimientos = {
	gotableProcedimientos : $('#tblProcedimiento'),
	datosProcedimiento: [], ListaCupsPaquete: [],
	gnCantidad: 0,
	gcObservaciones: '', lcMensajeError: '', lcFormaError: '', lcObjetoError: '', gcCupsSolicitar: '', gcCupsDescripcion: '',
	gcCupsEspecialidad: '', gcCupsReferencia1: '', gcCupsReferencia2: '', gcCupsReferencia3: '', 
	gcCupsPosNopos: '', gcCupsHexalis: '', gcLabIndependiente: '', gcTipoPaquete: '', gcCupsTipoRips: '', 
	aProcNOPOS: {},  oRowedit: {},
	aCupsSelecionado: [], aListaCups: [],
	llSolicitarCTC: false,

	inicializar: function(){
		this.iniciarTablaCups();
		this.consultaProcedimientos('buscarProcedimiento','codigoProcedimiento','descripcionProcedimiento','idCantidadProcedimiento','');
		oModalAyudaProcedimientos.inicializar();
		oModalProcedimientoCTC.inicializar('');

		$('#FormCups').validate({
			rules: {
				codigoProcedimiento: "required",
				descripcionProcedimiento: "required",
				idCantidadProcedimiento: "required",
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
		$('#AdicionarProcedimiento').on('click', this.adicionarCups);
		$('#seleccionarProcedimiento').on('click', this.ingresaAyudaCups);
		$('#eliminarProcedimientos').on('click', this.borraProcedimientos);
	},
	
	cargarListadosAyudaCups: function() {
		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/Autocompletar.php",
			data: {
				tipoDato: 'Procedimientos',
				otros: {filtro: 'ORDAMB', genero: aDatosIngreso.cSexo},
			},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					oModalAyudaProcedimientos.adicionarProcedimientos(toDatos.datos,$('#tblProcedimiento'),'OA',oAmbulatorio.gcTipoNOPOS);
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
	
	consultaProcedimientos: function(toObjeto,toCodigo,tcDescripcion,toFocus,tnFuncion) {
		oProcedimientos.gcCupsSolicitar=oProcedimientos.gcCupsDescripcion=oProcedimientos.gcCupsEspecialidad='';
		oProcedimientos.gcCupsReferencia1=oProcedimientos.gcCupsReferencia2=oProcedimientos.gcCupsReferencia3='';
		oProcedimientos.gcCupsPosNopos=oProcedimientos.gcCupsHexalis=oProcedimientos.gcLabIndependiente=oProcedimientos.gcTipoPaquete=oProcedimientos.gcCupsTipoRips='';
		var loObjeto = '#'+toObjeto;
		var loCodigoAsigna = '#'+toCodigo;
		var loDescripcionAsigna = '#'+tcDescripcion;
		var loObjetoFocus = '#'+toFocus;
		
		laDatosPaciente = {
			genero: aDatosIngreso.cSexo,
			edad: aDatosIngreso.aEdad
		},
		
		$(loObjeto).autoComplete({
			preventEnter: true,
			resolverSettings: {
				url: "vista-comun/ajax/procedimientos?accion=consultarProcedimientos&lcDatosPacientes="+JSON.stringify(laDatosPaciente)+"",
				queryKey: 'nombre',
				requestThrottling: 500,
				fail: function (e) {},
			},
			formatResult: function (taItem) {
				laItem = { value: '', text: '', html: ''};
	
				if(taItem.CODIGO!==undefined && taItem.DESCRIPCION!==undefined){
					if(taItem.DESCRIPCION.length>0 && taItem.CODIGO.length>0){
						laItem = {
							value: taItem.CODIGO,
							text: taItem.DESCRIPCION + ' - '+ taItem.CODIGO,
							html: taItem.DESCRIPCION + ' - '+ taItem.CODIGO
						};
					}
				}
				return laItem;
			},
			noResultsText: 'No hay coincidencias',
		})
		.autoComplete('set',
			{CODIGO:'', DESCRIPCION:'', ESPECIALIDAD:''}

		).on('autocomplete.select', function(evt, item) {
			oProcedimientos.gcCupsSolicitar=item.CODIGO;
			oProcedimientos.gcCupsDescripcion=item.DESCRIPCION;
			oProcedimientos.gcCupsEspecialidad=item.ESPECIALIDAD;
			oProcedimientos.gcCupsReferencia1=item.REFERENCIA1;
			oProcedimientos.gcCupsReferencia2=item.REFERENCIA2;
			oProcedimientos.gcCupsReferencia3=item.REFERENCIA3;
			oProcedimientos.gcCupsPosNopos=item.POSNOPOS=='NOPB' ? 'NOPOS' : 'POS';
			oProcedimientos.gcCupsHexalis=item.HEXALIS;
			oProcedimientos.gcLabIndependiente=item.LABESPEC;
			oProcedimientos.gcTipoPaquete=item.PAQLAB;
			oProcedimientos.gcCupsTipoRips=item.TIPORIPS;
			$(loCodigoAsigna).val(item.CODIGO);
			$(loDescripcionAsigna).val(item.DESCRIPCION);
			$(loObjeto).val('');
			$(loObjeto).removeClass("is-valid");
			$(loCodigoAsigna).removeClass("is-invalid");
			$(loDescripcionAsigna).removeClass("is-invalid");
			$(loObjetoFocus).focus();
			
			if (tnFuncion=='OM'){			
				oProcedimientosOrdMedica.seleccionaProcedimiento();
			}	

			if (tnFuncion=='GP'){			
				datosProcedimientosGrabacion();
			}	
			
		}).on('autocomplete.freevalue', function(evt, value) {
			$(loObjeto).val('');
		});
	},
	
	ingresaAyudaCups: function(e){
		e.preventDefault();
		oModalAyudaProcedimientos.mostrar();
	},
	
	borraProcedimientos: function() {
		var taCupsSeleccionados = oProcedimientos.gotableProcedimientos.bootstrapTable('getSelections');
		if(taCupsSeleccionados != ''){
			fnConfirm('Desea eliminar los procedimientos seleccionados?', false, false, false, 'medium', function(){
				$.each(taCupsSeleccionados, function( lcKey, loTipo ) {
					lcCodigoCups = loTipo['CODIGO'].trim();
					oProcedimientos.gotableProcedimientos.bootstrapTable('remove', {
						field: 'CODIGO',
						values: [lcCodigoCups]
					});
				});
				$('#buscarProcedimiento').focus();
			},'');
		}else{
			fnAlert('No existen procedimientos a eliminar, revise por favor.', '', 'fas fa-exclamation-circle','blue','medium');
			$('#buscarProcedimiento').focus();
		}	
	},
	
	verificaRegistro: function(tcMedicamento) {
		var TablaMedica = oProcedimientos.gotableMedicamentos.bootstrapTable('getData');
		var llRetorno = true;
			if(TablaMedica != ''){
				$.each(TablaMedica, function( lcKey, loTipo ) {
					if(loTipo['CODIGO']==tcMedicamento){
						oProcedimientos.indexedit = lcKey;
						oProcedimientos.oRowedit = loTipo;
						llRetorno = false;
					}
				});
			};
		return llRetorno;
	},

	adicionarCups: function(e){
		e.preventDefault();
		var loFunction = false;
		oProcedimientos.aListaCups = [];
		oProcedimientos.gcObservaciones = '';

		if ($('#FormCups').valid()){
			var lcCodigoCups = $("#codigoProcedimiento").val();
			var lcDescripcionCups = ($("#descripcionProcedimiento").val()).trim();
			var lcCantidadCups = $("#idCantidadProcedimiento").val();
			var lcObservacionesCups = $("#txtObservacionProcedimiento").val();
						
			if (lcCodigoCups==''){
				fnAlert('Código procedimiento no valido, revise por favor.', '', false, false, false);
				$('#buscarProcedimiento').focus();
				return false;
			}
			
			if (lcDescripcionCups==''){
				fnAlert('Descripción procedimiento no valido, revise por favor.', '', false, false, false);
				$('#buscarProcedimiento').focus();
				return false;
			}

			if (lcCantidadCups=='' || parseInt(lcCantidadCups)<=0){
				$('#idCantidadProcedimiento').focus();
				fnAlert('Cantidad no permitida, revise por favor.', '', false, false, false);
				return false;
			}
			if (lcCodigoCups=='' && lcCantidadCups==''){
				fnAlert('Campos obligatorios, revise por favor.', '', false, false, false);
				return false;
			}
			oProcedimientos.gnCantidad = parseInt(lcCantidadCups);
			oProcedimientos.gcObservaciones = lcObservacionesCups;
			oProcedimientos.aCupsSelecionado = [];
			oProcedimientos.aListaCups.push({CODIGO: lcCodigoCups });
			
			oProcedimientos.registraDatosCups(oProcedimientos.aListaCups, function() {
				oProcedimientos.cargarProcedimientos();
			});
			$("#buscarProcedimiento,#codigoProcedimiento,#descripcionProcedimiento,#idCantidadProcedimiento,#txtObservacionProcedimiento").removeClass("is-invalid");
			$('#buscarProcedimiento').focus();
		}
	},
	
	registraDatosCups: function(taCupsSelecionados, tfPost)
	{
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
						oProcedimientos.aCupsSelecionado.push({CODIGO: loSeleccion.CODIGO, DESCRIPCION: loSeleccion.DESCRIPCION, REFERENCIA1: loSeleccion.REFERENCIA1, POSNOPOS: loSeleccion.POSNOPOS, PAQUETE: loSeleccion.TIPO });
					});	
					if (typeof tfPost == 'function') {
						tfPost();
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta de paquete.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta paquete.");
		});
	},
	
	cargarProcedimientos: function(){
		var loFunction = false;
		oModalAyudaProcedimientos.aPedirCTC = [];
		
		$.each(oProcedimientos.aCupsSelecionado, function(lcKey, loDatosCups) {
			var taTablaValida = oProcedimientos.gotableProcedimientos.bootstrapTable('getData');
			var lcCodigoCups = loDatosCups.CODIGO;
			var lcDescripcionCups = loDatosCups.DESCRIPCION;
			var lcPosNopos = loDatosCups.POSNOPOS;
			var lnCantidad = loDatosCups.PAQUETE==='P' ? 1 : oProcedimientos.gnCantidad;
			laDatosCups = {lcCodigoCups: lcCodigoCups, lcDescripcionCups: lcDescripcionCups, lcCantidadCups: lnCantidad, lcObservacionesCups: oProcedimientos.gcObservaciones, POS: lcPosNopos };
			llverificaExiste = oProcedimientos.verificaCodigoExiste(lcCodigoCups, taTablaValida);
			llAdicionar = true ;
			if(oAmbulatorio.gcTipoNOPOS=='S'){
				if(lcPosNopos == "NOPB"){
					llAdicionar = false ;
					oProcedimientos.llSolicitarCTC = true;
					if(llverificaExiste){
						oModalAyudaProcedimientos.aPedirCTC.push({EXISTEREG: false, lcCodigoCups: lcCodigoCups, lcDescripcionCups: lcDescripcionCups, lcCantidadCups: lnCantidad, lcObservacionesCups: oProcedimientos.gcObservaciones });
					}else{
						oModalAyudaProcedimientos.oRoweditCTC.EXISTEREG = true;
						oModalAyudaProcedimientos.oRoweditCTC.CANTIDAD = 1;
						oModalAyudaProcedimientos.aPedirCTC.push(oModalAyudaProcedimientos.oRoweditCTC);
					}
				}
			}
			
			$('#tblProcedimiento').bootstrapTable('remove', {
				field: 'CODIGO',
				values: [lcCodigoCups]
			});
					
			if (llAdicionar){
				oProcedimientos.gotableProcedimientos.bootstrapTable('insertRow', {
					index: 1,
					row: {
						CODIGO: lcCodigoCups,
						DESCRIPCION: lcDescripcionCups,
						OBSERVACION: oProcedimientos.gcObservaciones,
						CANTIDAD: lnCantidad,
						BORRAR: '',
					}
				});
			}
		});
		if(oProcedimientos.llSolicitarCTC){
			oModalAyudaProcedimientos.SolicitarJustificacionCTC();
		}
		oProcedimientos.inicializaCampos();		
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
	
	verificaCodigoExiste: function(tcCodigo,taTablaValida)
	{
		var llRetorno = true ;
		if(taTablaValida != ''){
			$.each(taTablaValida, function( lcKey, loTipo ) {
				if(loTipo['CODIGO']==tcCodigo){
					oAmbulatorio.indexedit = lcKey;
					oModalAyudaProcedimientos.oRoweditCTC = loTipo;
					llRetorno = false;
					return llRetorno;
				}
			});
		};
		return llRetorno ;
	},
	
	adicionarFilaCups: function(camposFilaCups){
		var rows = []
			rows.push({
			CODIGO: camposFilaCups.lcCodigoCups.trim(),
			DESCRIPCION: camposFilaCups.lcDescripcionCups.trim(),
			OBSERVACION: camposFilaCups.lcObservacionesCups,
			CANTIDAD: camposFilaCups.lcCantidadCups,
			SOLICITADO: camposFilaCups.SolicitadoNP===undefined?'':camposFilaCups.SolicitadoNP,
			OBJETIVO: camposFilaCups.ObjetivoNP===undefined?'':camposFilaCups.ObjetivoNP,
			RIESGO: camposFilaCups.RiesgoI===undefined?'0':camposFilaCups.RiesgoI,
			TIPOR: camposFilaCups.RiesgoNP===undefined?'0':camposFilaCups.RiesgoNP,
			EXISTE: camposFilaCups.ExistePOSPr===undefined?'':camposFilaCups.ExistePOSPr,
			PROCEDIMPOS: camposFilaCups.ProcedimientoP===undefined?'':camposFilaCups.ProcedimientoP,
			CODIGOPOS: camposFilaCups.CodigoPrP===undefined?'':camposFilaCups.CodigoPrP,
			CANTIDADPOS: camposFilaCups.CantidadP===undefined?'0':camposFilaCups.CantidadP,
			RESPUESTA: camposFilaCups.RespuestaP===undefined?'':camposFilaCups.RespuestaP,
			RESUMEN: camposFilaCups.ResumenNP===undefined?'':camposFilaCups.ResumenNP,
			BIBLIOGRAFIA: camposFilaCups.BibliografiaP===undefined?'':camposFilaCups.BibliografiaP,
			PACIENTE: camposFilaCups.PacientePr===undefined?'':camposFilaCups.PacientePr,
			POS: camposFilaCups.POS===undefined?'0':camposFilaCups.POS,
		})
		$('#tblProcedimiento').bootstrapTable('append', rows);
		oProcedimientos.inicializaCampos();
	},
								
	modificarProcedimiento: function(camposProcedimiento) {
		lcCodigoCups = camposProcedimiento.lcCodigoCups.trim();
		$('#tblProcedimiento').bootstrapTable('remove', {
			field: 'CODIGO',
			values: [lcCodigoCups]
		});
		oProcedimientos.adicionarFilaCups(camposProcedimiento);
	},

	inicializaCampos: function() {
		$("#buscarProcedimiento,#codigoProcedimiento,#descripcionProcedimiento,#txtObservacionProcedimiento,#idCantidadProcedimiento").removeClass("is-valid").removeClass("is-invalid");
		$("#buscarProcedimiento,#codigoProcedimiento,#descripcionProcedimiento,#txtObservacionProcedimiento,#idCantidadProcedimiento").val('');
	},

	iniciarTablaCups: function(){
		$('#tblProcedimiento').bootstrapTable({
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
			  title: 'CODIGO',
			  field: 'CODIGO',
			  width: 5, widthUnit: "%",
			  halign: 'center',
			  align: 'center',
			  sortable: true
			}, {
			  title: 'DESCRIPCION',
			  field: 'DESCRIPCION',
			  width: 40, widthUnit: "%",
			  halign: 'center',
			  valign: 'middle',
			  sortable: true
			},
			{
			  title: 'OBSERVACION',
			  field: 'OBSERVACION',
			  width: 40, widthUnit: "%",
			  halign: 'center',
			  sortable: true
			},
			{
			  title: 'CANTIDAD',
			  field: 'CANTIDAD',
			  width: 5, widthUnit: "%",
			  align: 'center',
			  sortable: true
			},
			{
			  title: 'ACCIONES',
			  field: 'ACCIONES',
			  width: 5, widthUnit: "%",
			  align: 'center',
			  clickToSelect: false,
			  events: this.eventoprocedimiento,
			  formatter: this.formatoAcciones
			}
		  ]
		});
	},

	eventoprocedimiento:  {
		'click .eliminar': function (e, value, row, index) {

			fnConfirm('Desea eliminar el procedimiento?', false, false, false, false, function(){
				$('#tblProcedimiento').bootstrapTable('remove', {
				field: 'CODIGO',
				values: [row.CODIGO]
				});
				oProcedimientos.inicializaCampos();
				$('#buscarProcedimiento').focus();
			},'');
		},

		'click .editar': function (e, value, row, index) {
			oProcedimientos.editarProcedimiento(row);
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
	
	editarProcedimiento: function(arow) {
		$("#codigoProcedimiento").val(arow.CODIGO);
		$("#descripcionProcedimiento").val(arow.DESCRIPCION);
		$("#idCantidadProcedimiento").val(arow.CANTIDAD);
		$("#txtObservacionProcedimiento").val(arow.OBSERVACION);
		$("#buscarProcedimiento").focus();
	},
	
	insertarProcedimiento: function(tcCodigo, taProcedimiento, tcValidar){
		var taTablaValida = oProcedimientos.gotableProcedimientos.bootstrapTable('getData');

		var llverifica = tcValidar==='' ? oProcedimientos.verificaCodigoExiste(tcCodigo, taTablaValida) : true;
		if(llverifica) {
			oProcedimientos.adicionarFilaCups(taProcedimiento);
		}
		else{
			fnConfirm('Procedimiento ya ingresado, desea modificarlo?', oProcedimientos.lcTitulo, false, false, false, function(){
				oProcedimientos.modificarProcedimiento(taProcedimiento);
				$('#buscarProcedimiento').focus();
			});
		}
	},	
	
	insertarProcedimientoCTC: function(){
		laDatosCTC = OrganizarSerializeArray(oModalProcedimientoCTC.obtenerDatos());
		oModalProcedimientoCTC.inicializaProcedimientoCTC();
		
		var lcProcedimientoNP = (laDatosCTC.procedimientoNP===undefined?'':laDatosCTC.procedimientoNP); 
		var lcTextoCups = lcProcedimientoNP.split('-');
		var lcCodigoCupsNP = lcTextoCups[0];
		var lcDescripcionCupsNP = lcProcedimientoNP.substr(lcTextoCups[0].length + 2, 120);
		var lcObservacionesCups = $("#txtObservacionProcedimiento").val();
		var lcCodigoCupsP = laDatosCTC.codigoProcedimientoP;
		var lcDescripcionCupsP = laDatosCTC.descripcionProcedimientoP;
		
		var lcProcedimientos = {lcCodigoCups: lcCodigoCupsNP, lcDescripcionCups: lcDescripcionCupsNP, 
								lcCantidadCups: parseInt(laDatosCTC.CantidadNP===undefined?'':laDatosCTC.CantidadNP.trim()),lcObservacionesCups: oProcedimientos.gcObservaciones,
								SolicitadoNP: (laDatosCTC.SolicitadoNP===undefined?'':laDatosCTC.SolicitadoNP.trim()), 
								ObjetivoNP: (laDatosCTC.ObjetivoNP===undefined?'':laDatosCTC.ObjetivoNP.trim()), 
								RiesgoI: (laDatosCTC.selRiesgoNP===undefined?'0':laDatosCTC.selRiesgoNP.trim()), 
								RiesgoNP: (laDatosCTC.chkRiesgoNP===undefined?'0':(laDatosCTC.chkRiesgoNP.trim()==='on'?'1':'0')),
								ExistePOSPr: (laDatosCTC.chkExistePOSPr===undefined?'0':'1'), 
								ProcedimientoP: lcDescripcionCupsP, CodigoPrP: lcCodigoCupsP,
								CantidadP: (laDatosCTC.CantidadP===undefined?'':laDatosCTC.CantidadP.trim()), 
								RespuestaP: (laDatosCTC.RespuestaP===undefined?'':laDatosCTC.RespuestaP.trim()), 
								ResumenNP: (laDatosCTC.edtResumenP===undefined?'':laDatosCTC.edtResumenP.trim()), 
								BibliografiaP: (laDatosCTC.BibliografiaP===undefined?'':laDatosCTC.BibliografiaP.trim()), 
								PacientePr: (laDatosCTC.chkPacientePr===undefined?'':(laDatosCTC.chkPacientePr==='S' ? '1' : '0')),
								POS: '1',		
								};
	
		oProcedimientos.insertarProcedimiento(lcCodigoCupsNP.trim(), lcProcedimientos)
	},
}
