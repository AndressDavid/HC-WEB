var oProcedimientoEvolucionUci = {
	goTablaComplicacionesUci: $('#tblComplicacionesUci'),
	goTablaCupsUci: $('#tblCupsDiagnosticoUci'),
	ListaDxProc : {},
	lcObjetoError : '',
	
	inicializar: function() 
	{
		this.IniciarTablaComplicaciones();
		this.listadoDiagnosticosUci();
		oProcedimientos.consultaProcedimientos('buscarProcedimientoUci','codigoProcedimientoUci','descripcionProcedimientoUci','chkTotUci','');
		oModalEuroscore.inicializar();
		$('#btnEuroscore').on('click', this.ingresaEuroscore);
		$('#btnAceptarEuroscore').on('click', this.registraEuroscore);
		$('#chkSinComplicaciones').change(this.habilitarComplicaciones);
		$('#chkNingunoUci').change(this.validarNinguno);
		$("#chkTotUci").change(this.desactivarNinguno);
		$("#chkCvcUci").change(this.desactivarNinguno);
		$("#chkSvUci").change(this.desactivarNinguno);
	},

	ingresaEuroscore: function(){
		oModalEuroscore.mostrar();
	},
	
	registraEuroscore: function(){
		var lcResultadoEuroescore = oModalEuroscore.resultadoFinalEuroscore();
		var lcTextoEuroescore = lcResultadoEuroescore.split('~');
		$('#txtEuroescoreUci').val(parseInt(lcTextoEuroescore[0]));
		$('#txtResultadoEuroescoreUci').val(lcTextoEuroescore[1]);
		oModalEuroscore.ocultar();
		$("#txtEuroescoreUci").focus();
	},
	
	listadoDiagnosticosUci: function(){
		oProcedimientoEvolucionUci.goTablaComplicacionesUci.bootstrapTable('refreshOptions', {data: {}});
		$.ajax({
			type: "POST",
			url: "vista-evoluciones/ajax/diagnosticoProcedimiento",
			data: {accion:'listasDiagnosticos', lcGenero:aDatosIngreso['cSexo']},
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.error == ''){
					$.each(loDatos.datos, function( lcKey, loTipo ) {
						oProcedimientoEvolucionUci.adicionarComplicacionesUnidad(loTipo);
					});
					
				} else {
					fnAlert(loDatos.error)
				}
			} catch(err) {
				fnAlert('No se pudo realizar la consultar procedimientos Uci.')
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			oProcedimientoEvolucionUci.goTablaComplicacionesUci.bootstrapTable('hideLoading');
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar procedimientos Uci.');
		});
	},

	adicionarComplicacionesUnidad: function (camposRegistro){
		llSeleccion = false;

		oProcedimientoEvolucionUci.goTablaComplicacionesUci.bootstrapTable('insertRow', {
			index: 1,
			row: {
				SELECCION: llSeleccion,
				CODIGO: camposRegistro.CODIGO,
				DESCRIPCION: camposRegistro.DESCRIPCION
			}
		});
	},

	IniciarTablaComplicaciones: function (){
		oProcedimientoEvolucionUci.goTablaComplicacionesUci.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			checkboxHeader: false,
			clicktoselect: 'true',
			locale: 'es-ES',
			undefinedText: 'N/A',
			toolbar: '#toolBarLst',
			height: '350',
			search: false,
			sortName: 'CODIGO',
			pagination: false,
			iconSize: 'sm',
			columns: [
			{
				title: '',
				field: 'SELECCION',
				checkbox: 'false',
				width: 5, widthUnit: "%",
				halign: 'center',
				align: 'center'
			},{
				title: 'CODIGO',
				field: 'CODIGO',
				width: 5, widthUnit: "%",
				halign: 'center',
				align: 'left',
				visible: false
			},{
				title: 'Tipo Complicación',
				field: 'DESCRIPCION',
				width: 90, widthUnit: "%",
				halign: 'center',
				align: 'left'
			}
			]
		});
	},
	
	//Función que valida si el procedimiento a ingresar ya existe
	validarProcedimiento: function(tcProcedimiento) {
		if(tcProcedimiento !=''){
			var lnidx = oProcedimientoEvolucionUci.ListaDxProc[tcProcedimiento];
			if(lnidx===undefined){
				return false
			}
			return true
		}
	},
	
	habilitarComplicaciones: function (){
		if($(chkSinComplicaciones).prop('checked')){
			oProcedimientoEvolucionUci.goTablaComplicacionesUci.bootstrapTable('uncheckAll', 'SELECCION');
			$("#"+oProcedimientoEvolucionUci.goTablaComplicacionesUci.attr("id")+" input[type=checkbox]").attr("disabled",true);
		}else{
			
			$("#"+oProcedimientoEvolucionUci.goTablaComplicacionesUci.attr("id")+" input[type=checkbox]").attr("disabled",false);
		}
	},
	
	validarNinguno: function()
	{
		if($(chkNingunoUci).prop('checked')){
			$("#chkTotUci").prop('checked', false);
			$("#chkCvcUci").prop('checked', false);
			$("#chkSvUci").prop('checked', false);
		} 		
	},
	
	desactivarNinguno: function()
	{
		$("#chkNingunoUci").prop('checked', false);
	},

	validacion: function()
	{
		var lbValido = true;
		if(aDatosIngreso['ActivarDxProc']==true){
			var lcProcedimiento = $("#codigoProcedimientoUci").val(); 
			if(lcProcedimiento == ''){
				this.lcObjetoError = "buscarProcedimientoUci" ;
				this.lcMensajeError = 'Error en el Diagnostico Procedimiento, Revise por favor';
				lbValido = false ;
			}
			if($(chkNingunoUci).prop('checked')==false && $(chkTotUci).prop('checked')==false && $(chkCvcUci).prop('checked')==false && $(chkSvUci).prop('checked')==false){
				this.lcObjetoError = "chkTotUci" ;
				this.lcMensajeError = 'Datos de Invasión Obligatorio, Debe marcar por lo menos uno. !';
				lbValido = false ;
			}else{
				if($("#txtEuroescoreUci").val()!='' && $("#txtResultadoEuroescoreUci").val()==''){
					this.lcObjetoError = "txtEuroescoreUci" ;
					this.lcMensajeError = 'La escala Euroscore debe ser diligenciada. !';
					lbValido = false ;
				}else{
					if($(chkSinComplicaciones).prop('checked')==false){						
						loDatosComplica = oProcedimientoEvolucionUci.goTablaComplicacionesUci.bootstrapTable('getData');
						var lnRespuestas = 0 ;
						$.each(loDatosComplica, function(lnIndex, laDato){
							if(laDato.SELECCION==true){
								lnRespuestas++;
							}
						});						
						if(lnRespuestas == 0){
							this.lcObjetoError = "chkSinComplicaciones" ;
							this.lcMensajeError = 'Datos de Complicaciones Obligatorio, Debe marcar por lo menos uno. !';
							lbValido = false ;
						}
					}
				}
			}
		}
		return lbValido;	
	},
	
	obtenerDatos: function()
	{
		return {
			DatosCieCups: OrganizarSerializeArray($('#FormProcedimientoUci').serializeArray()),
			ListadoComplicaciones: oProcedimientoEvolucionUci.goTablaComplicacionesUci.bootstrapTable('getData')
		};
	},

	ubicarObjeto: function(toForma, tcObjeto){
		tcObjeto = typeof tcObjeto === 'string'? tcObjeto: false;
		var loForm = $(toForma);

		activarTab(loForm);
		if (tcObjeto===false) {
			var formerrorList = loForm.data('validator').errorList,
				lcObjeto = formerrorList[0].element.id;
			$('#'+lcObjeto).focus();
		} else {
			$(tcObjeto).focus();
		}
	}

};
