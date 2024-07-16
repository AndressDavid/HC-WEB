var oModalJustificacionUsoAntibiotico = {
	lcTitulo: 'Justificación uso antibiotico',
	goTablaUsoAntibiotico: $('#tblUsoAntibioiticosOM'),
	gcUrlAjax: 'vista-comun/ajax/modalJustificacionUsoAntibiotico.php',
	fnEjecutar: false, gcDiagnosticoAxexo: false,
	aDatosUsoAntibiotico: {},

	inicializar: function()
	{
		this.iniciarTablaAntibioticos();
		this.consultaDatosAntibioticos();
		$('#selCieUsoInfeccioso').on('change', oModalJustificacionUsoAntibiotico.validarDiagnostico);
		$('#selOrigenMuestraUsoAntibiotico').on('change', oModalJustificacionUsoAntibiotico.validarOrigenMuestra);
		$('#btnGuardaUsoAntibiotico').on('click', oModalJustificacionUsoAntibiotico.validarUsoAntibiotico);
		$('#btnSalirUsoAntibiotico').on('click', oModalJustificacionUsoAntibiotico.salirJustificacionAntibiotico);
	},
	
	CargarReglas: function(tcTipo, tcForma, tcTitulo){	
		$.ajax({
			type: "POST",
			url: oModalJustificacionUsoAntibiotico.gcUrlAjax,
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
					oModalJustificacionUsoAntibiotico.ValidarReglas(tcForma, lopciones);

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
	
	validarOrigenMuestra: function(){
		var lcOrigenMuestra=$("#selOrigenMuestraUsoAntibiotico").val();
		$('#lblResultadosUsoAntibiotico').removeClass("required");
		
		if (lcOrigenMuestra!='' && lcOrigenMuestra!='00000001'){
			$('#lblResultadosUsoAntibiotico').addClass("required");
		}
	},
	
	validarDiagnostico: function(){
		$("#selCieUsoAntInfecto").css("visibility","hidden");
		oModalJustificacionUsoAntibiotico.gcDiagnosticoAxexo=false;

		var lcCodigoDiagnostico=$("#selCieUsoInfeccioso").val();
		$('#selCieUsoAntInfecto').empty();
		$('#selCieUsoAntInfecto').val('');

		$.ajax({
			type: "POST",
			url: oModalJustificacionUsoAntibiotico.gcUrlAjax,
			data: {accion: 'consultaAnexoCieInfeccioso', lcDiagnostico: lcCodigoDiagnostico},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					$('#selCieUsoAntInfecto').append('<option value=""></option>');
					if (toDatos.TIPOS.length>0){
						oModalJustificacionUsoAntibiotico.gcDiagnosticoAxexo=true;
						$("#selCieUsoAntInfecto").css("visibility","visible");
						
						$.each(toDatos.TIPOS, function( lcKey, loTipo ) {
							$('#selCieUsoAntInfecto').append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
						});	
					}	
				} else {
					fnAlert(toDatos.Error);
				}	
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta validar diagnóstico antibioticos.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta validar diagnóstico antibioticos.");
		});
	},	
	
	consultaDatosAntibioticos: function(){
		$('#selCieUsoInfeccioso,#selCieUsoAntInfecto,#selTipoTratamientoUsoAntibiotico,#selAjustesUsoAntibiotico,#selOrigenMuestraUsoAntibiotico').empty();
		$('#selTipoSuspenderAntibiotico,#selTipoModificacionAntibiotico').empty();
		$('#selCieUsoInfeccioso,#selCieUsoAntInfecto,#selTipoTratamientoUsoAntibiotico,#selAjustesUsoAntibiotico,#selOrigenMuestraUsoAntibiotico').val('');
		$('#selTipoSuspenderAntibiotico,#selTipoModificacionAntibiotico').val('');

		$.ajax({
			type: "POST",
			url: oModalJustificacionUsoAntibiotico.gcUrlAjax,
			data: {accion: 'consultaAntibioticos'},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					$('#selCieUsoInfeccioso,#selCieUsoAntInfecto,#selTipoTratamientoUsoAntibiotico,#selAjustesUsoAntibiotico,#selOrigenMuestraUsoAntibiotico').append('<option value=""></option>');
					$('#selTipoSuspenderAntibiotico,#selTipoModificacionAntibiotico').append('<option value=""></option>');
					$.each(toDatos.TIPOS, function( lcKey, loTipo ) {
						
						if (loTipo.CLASIFICACION1==='DIAGNOS' && loTipo.CLASIFICACION3==='01'){
							$('#selCieUsoInfeccioso').append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
						}	

						if (loTipo.CLASIFICACION1==='TIPOTRA'){
							$('#selTipoTratamientoUsoAntibiotico').append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
						}	
						
						if (loTipo.CLASIFICACION1==='AJUSTES'){
							$('#selAjustesUsoAntibiotico').append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
						}

						if (loTipo.CLASIFICACION1==='MUESTRA'){
							$('#selOrigenMuestraUsoAntibiotico').append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
						}	
						
						if (loTipo.CLASIFICACION1==='SUSPENDE'){
							$('#selTipoSuspenderAntibiotico').append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
						}	
						if (loTipo.CLASIFICACION1==='MODIFICA'){
							$('#selTipoModificacionAntibiotico').append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
						}	
						
					});
				} else {
					fnAlert(toDatos.Error);
				}	
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta datos antibioticos.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta datos antibioticos.");
		});
	},
	
	mostrar: function()
	{
		oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico={};
		oModalJustificacionUsoAntibiotico.inicializaVariables();
		oModalJustificacionUsoAntibiotico.CargarReglas('Reglas','#FormUsoAntibiotico','Antibio');
		$("#divUsoAntibiotico").modal('show');

		$('#lblNombreUsoAntibiotico').text('ANTIBIOTICO:   ' + oMedicamentosOrdMedica.gaDatosmedicamento.CODIGO+ ' - ' + oMedicamentosOrdMedica.gaDatosmedicamento.DESCRIPCION);
		oModalJustificacionUsoAntibiotico.listaAntibioticos();
	},

	ocultar: function()
	{
		$("#divUsoAntibiotico").modal('hide');
	},

	inicializaVariables: function () {
		oModalJustificacionUsoAntibiotico.goTablaUsoAntibiotico.bootstrapTable('refreshOptions', {data: {}});
		$("#selCieUsoAntInfecto").css("visibility","hidden");
		$('#selCieUsoInfeccioso,#selCieUsoAntInfecto,#txtOtrosDiagnosticosAntibiotico,#selTipoTratamientoUsoAntibiotico').val('');
		$('#selAjustesUsoAntibiotico,#txtObservacionesUsoAntibiotico,#selOrigenMuestraUsoAntibiotico,#txtResultadosUsoAntibiotico').val('');
		$('#selCieUsoInfeccioso,#selCieUsoAntInfecto,#txtOtrosDiagnosticosAntibiotico,#selTipoTratamientoUsoAntibiotico').removeClass("is-valid");
		$('#selAjustesUsoAntibiotico,#txtObservacionesUsoAntibiotico,#selOrigenMuestraUsoAntibiotico,#txtResultadosUsoAntibiotico').removeClass("is-valid");
	},
	
	listaAntibioticos: function () {
		oModalJustificacionUsoAntibiotico.goTablaUsoAntibiotico.bootstrapTable('refreshOptions', {data: {}});

		var rows = []
			rows.push({
			CODIGO: oMedicamentosOrdMedica.gaDatosmedicamento.CODIGO,
			DESCRIPCION: oMedicamentosOrdMedica.gaDatosmedicamento.DESCRIPCION,
			DOSIS: oMedicamentosOrdMedica.gaDatosmedicamento.DOSIS + ' ' + oMedicamentosOrdMedica.gaDatosmedicamento.DESCRUNIDADDOSIS,
			FRECUENCIA: oMedicamentosOrdMedica.gaDatosmedicamento.FRECUENCIA + ' ' + oMedicamentosOrdMedica.gaDatosmedicamento.DESCRUNIDADFRECUENCIA,
			VIA: oMedicamentosOrdMedica.gaDatosmedicamento.DESCRVIA,
			OBSERVACIONES: oMedicamentosOrdMedica.gaDatosmedicamento.OBSERVACIONES,
		})
		oModalJustificacionUsoAntibiotico.goTablaUsoAntibiotico.bootstrapTable('append', rows);
	},	

	validarUsoAntibiotico: function () {
		let lcDiagnosticoInfeccioso=$("#selCieUsoInfeccioso").val();
		let lcDiagnosticoAnexo=$("#selCieUsoAntInfecto").val();
		let lcOtrosDiagnosticos=$("#txtOtrosDiagnosticosAntibiotico").val();
		let lcTipoTratamiento=$("#selTipoTratamientoUsoAntibiotico").val();
		let lcAjustes=$("#selAjustesUsoAntibiotico").val();
		let lcObservaciones=$("#txtObservacionesUsoAntibiotico").val().trim();
		let lcOrigenMuestra=$("#selOrigenMuestraUsoAntibiotico").val();
		let lcResultado=$("#txtResultadosUsoAntibiotico").val();
		
		if (lcDiagnosticoInfeccioso==''){
			$('#selCieUsoInfeccioso').focus();
			fnAlert('Diagnóstico infecccioso obligatorio, revise por favor.');
			return false;
		}

		if (lcDiagnosticoInfeccioso!='' && lcDiagnosticoAnexo=='' && oModalJustificacionUsoAntibiotico.gcDiagnosticoAxexo){
			$('#selCieUsoAntInfecto').focus();
			fnAlert('Tipo diagnóstico obligatorio, revise por favor.');
			return false;
		}	
		
		if (lcTipoTratamiento==''){
			$('#selTipoTratamientoUsoAntibiotico').focus();
			fnAlert('Tipo de tratamiento obligatorio, revise por favor.');
			return false;
		}
		
		if (lcAjustes==''){
			$('#selAjustesUsoAntibiotico').focus();
			fnAlert('Ajustes obligatorio, revise por favor.');
			return false;
		}
		
		if (lcObservaciones==''){
			$('#txtObservacionesUsoAntibiotico').focus();
			fnAlert('Observaciones obligatorio, revise por favor.');
			return false;
		}
		
		if (lcOrigenMuestra==''){
			$('#txtObservacionesUsoAntibiotico').focus();
			fnAlert('Observaciones obligatorio, revise por favor.');
			return false;
		}
		
		if (lcOrigenMuestra!='' && lcResultado=='' && lcOrigenMuestra!='00000001'){
			$('#txtResultadosUsoAntibiotico').focus();
			fnAlert('Resultado obligatorio, revise por favor.');
			return false;
		}
		
		fnConfirm('¿Si guarda el formato, NO podra modificarlos después. Esta seguro que desea guardar los datos?', oModalJustificacionUsoAntibiotico.lcTitulo, false, false, 'medium',
			{
				text: 'Si',
				action: function(){
					
					oModalJustificacionUsoAntibiotico.aDatosUsoAntibiotico= { 
							DIAGNOSTICOINFECCIOSO: lcDiagnosticoInfeccioso,
							DIAGNOSTICOANEXO: lcDiagnosticoAnexo,
							OTROSDIAGNOSTICOS: lcOtrosDiagnosticos,
							TIPOTRATAMIENTO: lcTipoTratamiento,
							AJUSTES: lcAjustes,
							OBSERVACIONES: lcObservaciones,
							ORIGENMUESTRA: lcOrigenMuestra,
							RESULTADO: lcResultado};
					oModalJustificacionUsoAntibiotico.registrarInformacion();
				}
			},

			{ text: 'No',
				action: function(){
					$("#divUsoAntibiotico").modal('show');
				}
			}
		);
 	},
	
	registrarInformacion: function(){
		var llFormulado='';
		var tcDatos=oMedicamentosOrdMedica.gaDatosmedicamento;
		var lcCodigo=oMedicamentosOrdMedica.gaDatosmedicamento.CODIGO;
		var taTablaValidar = oMedicamentosOrdMedica.gotableMedicamentosOM.bootstrapTable('getData');
		var llverificaExiste = oMedicamentosOrdMedica.verificaCodigoExiste(lcCodigo,taTablaValidar);

		if(llverificaExiste) {
			oModalJustificacionUsoAntibiotico.ocultar();
			oMedicamentosOrdMedica.preguntarUnirs(tcDatos,llFormulado,1,'','','R');
		}else{
			oModalJustificacionUsoAntibiotico.ocultar();
			oMedicamentosOrdMedica.preguntarUnirs(tcDatos,llFormulado,1,'','','M');
		}
	},
	
	salirJustificacionAntibiotico: function () {
		fnConfirm('¿Si no se diligencia el formato no podra formular el antibiótico?', oModalJustificacionUsoAntibiotico.lcTitulo, false, false, 'medium',
				{
					text: 'Si',
					action: function(){
						oModalJustificacionUsoAntibiotico.ocultar();
					}
				},

				{ text: 'No',
					action: function(){
						$("#divUsoAntibiotico").modal('show');
					}
				}
			);
	},
	
	iniciarTablaAntibioticos: function(){
		$('#tblUsoAntibioiticosOM').bootstrapTable({
			classes: 'table table-bordered table-hover table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			locale: 'es-ES',
			undefinedText: '',
			toolbar: '#toolBarLst',
			height: '100',
			pagination: false,
			iconSize: 'sm',
			columns: [
			{
				title: 'CODIGO',
				field: 'CODIGO',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'center',
			},
			{
				title: 'DESCRIPCION ANTIBIOTICO',
				field: 'DESCRIPCION',
				width: 25, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'DOSIS',
				field: 'DOSIS',
				width: 25, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'FRECUENCIA',
				field: 'FRECUENCIA',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left',
			},
			{
				title: 'VIA',
				field: 'VIA',
			  	width: 7, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'OBSERVACIONES',
				field: 'OBSERVACIONES',
			  	width: 15, widthUnit: "%",
				halign: 'center',
				align: 'left'
			}
		  ]
		});
	}
	
}