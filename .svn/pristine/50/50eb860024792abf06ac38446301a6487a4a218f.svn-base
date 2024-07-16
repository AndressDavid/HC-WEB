var oRips1036 = {
	gotablaTransaccion : $('#tblRipsTransaccion'),
	gotablaConsultas : $('#tblRipsConsulta'),
	gotablaProcedimientos : $('#tblRipsProcedimientos'),
	gotablaUsuarios : $('#tblRipsUsuario'),
	gotablaUrgencias : $('#tblRipsUrgencias'),
	gotablaHospitalizacion : $('#tblRipsHospitalizacion'),
	gotablaOtrosServicios : $('#tblRipsOtrosServicios'),
	gotablaMedicamentos : $('#tblRipsMedicamentos'),
	gotablaRecienNacidos : $('#tblRipsRecienNacido'),
	gcUrlAjax: 'vista-facturacion/ajax/ajax',
	gcTitulo : 'Rips',
	lcFormaError : '',
	lcObjetoError: '',
	lcMensajeError: '',
	gcEnter: String.fromCharCode(13),
	
	inicializar: function(){
		this.consultarTipoDocumentos();
		this.iniciaTablaRipsTransaccion();
		this.iniciaTablaRipsConsulta();
		this.iniciaTablaRipsMedicamentos();
		this.iniciaTablaRipsProcedimientos();
		this.iniciaTablaRipsUsuario();
		this.iniciaTablaRipsUrgencias();
		this.iniciaTablaRipsHospitalizacion();
		this.iniciaTablaRipsOtros();
		this.iniciaTablaRipsRecienNacido();
		$('#btnBuscarFactura').on('click', this.consultaDatosRips);
	},

	inicializaDatos: function() {
		oRips1036.gotablaTransaccion.bootstrapTable('refreshOptions', {data: {}});
		oRips1036.gotablaConsultas.bootstrapTable('refreshOptions', {data: {}});
		oRips1036.gotablaProcedimientos.bootstrapTable('refreshOptions', {data: {}});
		oRips1036.gotablaUsuarios.bootstrapTable('refreshOptions', {data: {}});
		oRips1036.gotablaUrgencias.bootstrapTable('refreshOptions', {data: {}});
		oRips1036.gotablaHospitalizacion.bootstrapTable('refreshOptions', {data: {}});
		oRips1036.gotablaOtrosServicios.bootstrapTable('refreshOptions', {data: {}});
		oRips1036.gotablaMedicamentos.bootstrapTable('refreshOptions', {data: {}});
		oRips1036.gotablaRecienNacidos.bootstrapTable('refreshOptions', {data: {}});
	},
		
	consultarTipoDocumentos: function() {
		$.ajax({
			type: "POST",
			url: oRips1036.gcUrlAjax,
			data: {accion: 'consultatipodocumentos'},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
						$('#selTipoDocumentoR').append('<option value="' + loTipo.CODIGO + '">'+loTipo.DESCRIPCION + '</option>');
					});	
				} else {
					fnAlert(loTipos.error + ' ', "warning");
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la busqueda consultar Tipo Documentos.', oRips1036.gcTitulo);
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar consultar Tipo Documentos.', oRips1036.gcTitulo);
		});
	},
	
	consultaDatosRips: function() {
		oRips1036.inicializaDatos();
		let lnFactura=parseInt($("#txtFactura").val());
		let lcDocumentoTipo=$("#selTipoDocumentoR").val();
		
		if (lnFactura>0) {
			if (lcDocumentoTipo!=''){
				$.ajax({
					type: "POST",
					url: oRips1036.gcUrlAjax,
					data: {accion: 'consultarips', lnNroFactura: lnFactura, lcTipoDocumento: lcDocumentoTipo},
					dataType: "json",
				})
				.done(function( loTipos ) {
					try {
						if (loTipos.error == ''){
							$("#tblRipsTransaccion").bootstrapTable('refreshOptions', {data: loTipos.TIPOS.AF}); 
							$("#tblRipsConsulta").bootstrapTable('refreshOptions', {data: loTipos.TIPOS.AC}); 
							$("#tblRipsMedicamentos").bootstrapTable('refreshOptions', {data: loTipos.TIPOS.AM}); 
							$("#tblRipsUsuario").bootstrapTable('refreshOptions', {data: loTipos.TIPOS.US}); 
							$("#tblRipsProcedimientos").bootstrapTable('refreshOptions', {data: loTipos.TIPOS.AP}); 
							$("#tblRipsUrgencias").bootstrapTable('refreshOptions', {data: loTipos.TIPOS.AU}); 
							$("#tblRipsHospitalizacion").bootstrapTable('refreshOptions', {data: loTipos.TIPOS.AH}); 
							$("#tblRipsOtrosServicios").bootstrapTable('refreshOptions', {data: loTipos.TIPOS.AT}); 
							$("#tblRipsRecienNacido").bootstrapTable('refreshOptions', {data: loTipos.TIPOS.AN}); 
						} else {
							fnAlert(loTipos.error + ' ', "warning");
						}

					} catch(err) {
						console.log(err);
						fnAlert('No se pudo realizar la busqueda consulta datos rips.', oRips1036.gcTitulo);
					}
				})
				.fail(function(jqXHR, textStatus, errorThrown) {
					console.log(jqXHR.responseText);
					fnAlert('Se presentó un error al buscar consulta datos rips.', oRips1036.gcTitulo);
				});
			}else{
				fnAlert('Tipo documento obligatorio, revise por favor.', oRips1036.gcTitulo);
			}	
		}else{
			fnAlert('Factura obligatoria, revise por favor.', oRips1036.gcTitulo);
		}	
	},
	
	iniciaTablaRipsConsulta: function(){
		oRips1036.gotablaConsultas.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			locale: 'es-ES',
			iconSize: 'sm',
			columns: [
			{
				title: 'Prestador',
				field: 'codPrestador',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},{
				title: 'Fecha/Hora atención',
				field: 'fechaInicioAtencion',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Autorizacion',
				field: 'numAutorizacion',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Consulta',
				field: 'descripprocedimiento',
				formatter: function(tnValor, toFila){ return toFila.codConsulta+'-'+toFila.descripprocedimiento;},
				width: 40, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Modalidad Grupo Servicio',
				field: 'descripmodalidadatencion',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Grupo Servicio',
				field: 'descripgruposervicio',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Servicio',
				field: 'descripcodServicio',
				formatter: function(tnValor, toFila){ return toFila.codServicio+'-'+toFila.descripcodServicio;},
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Finalidad',
				field: 'descripFinalidad',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Causa Externa',
				field: 'descripcausaexterna',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Diagnóstico principal',
				field: 'descripcieprincipal',
				formatter: function(tnValor, toFila){ return toFila.codDiagnosticoPrincipal+'-'+toFila.descripcieprincipal;},
			  	width: 30, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Diagnóstico Relacionado 1',
				field: 'descripcieprincipal',
				formatter: function(tnValor, toFila){ return toFila.codDiagnosticoRelacionado1+'-'+toFila.descripcierelacionado1;},
			  	width: 30, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Tipo diagnóstico principal',
				field: 'descripcodtipodiagnosticoprincipal',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Identificación realiza',
				field: 'numDocumentoldentificacion',
				formatter: function(tnValor, toFila){ return toFila.tipoDocumentoIdentificacion+'-'+toFila.numDocumentoIdentificacion;},
			  	width: 20, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Valor servicio',
				field: 'vrServicio',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'right',
				formatter: function(tnValor, toFila){ return parseInt(tnValor).toLocaleString('es-CO', {minimumFractionDigits:0}); }	
			},
			{
				title: 'Tipo modelador',
				field: 'descripciontipomoderadora',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Valor modelador',
				field: 'valorPagoModerador',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'right',
				formatter: function(tnValor, toFila){ return parseInt(tnValor).toLocaleString('es-CO', {minimumFractionDigits:0}); }	
			}
		  ]
		});
	},

	iniciaTablaRipsMedicamentos: function(){
		oRips1036.gotablaMedicamentos.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			locale: 'es-ES',
			iconSize: 'sm',
			columns: [
			{
				title: 'Prestador',
				field: 'codPrestador',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Autorizacion',
				field: 'numAutorizacion',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'id MIPRES',
				field: 'idMIPRES',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Fecha prescripción',
				field: 'fechaDispensAdmon',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Diagnóstico Principal',
				field: 'codDiagnosticoPrincipal',
				formatter: function(tnValor, toFila){ return toFila.codDiagnosticoPrincipal+'-'+toFila.descripcieprincipal;},
				width: 20, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Diagnóstico Relacionado',
				field: 'codDiagnosticoRelacionado',
				formatter: function(tnValor, toFila){return toFila.codDiagnosticoRelacionado!=''?(toFila.codDiagnosticoRelacionado+'-'+toFila.descripcierelacionado):'';},
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Tipo',
				field: 'descripcTipoMedicamento',
				width: 20, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Código',
				field: 'codTecnologiaSalud',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Nombre',
				field: 'nomTecnologiaSalud',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Concentración',
				field: 'concentracionMedicamento',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Unidad Medida',
				field: 'descripcUnidadMedida',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Forma Farmaceutica',
				field: 'descripcFormaFarmaceutica',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Unidad Dispensación',
				field: 'descripcUnidMinimaDispensacion',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cantidad',
				field: 'cantidadMedicamento',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'center'
			},
			{
				title: 'Días Tratamiento',
				field: 'diasTratamiento',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Identificación Formula',
				formatter: function(tnValor, toFila){ return toFila.tipoDocumentoIdentificacion+'-'+toFila.numDocumentoIdentificacion;},
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Valor Unitario',
				field: 'vrUnitMedicamento',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'right',
				formatter: function(tnValor, toFila){ return parseInt(tnValor).toLocaleString('es-CO', {minimumFractionDigits:0}); }	
			},
			{
				title: 'Valor Servicio',
				field: 'vrServicio',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'right',
				formatter: function(tnValor, toFila){ return parseInt(tnValor).toLocaleString('es-CO', {minimumFractionDigits:0}); }	
			},
			{
				title: 'Tipo modelador',
				field: 'descripciontipomoderadora',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
			title: 'Valor modelador',
				field: 'valorPagoModerador',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'right',
				formatter: function(tnValor, toFila){ return parseInt(tnValor).toLocaleString('es-CO', {minimumFractionDigits:0}); }	
			}
		  ]
		});
	},

	iniciaTablaRipsUrgencias: function(){
		oRips1036.gotablaUrgencias.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			locale: 'es-ES',
			iconSize: 'sm',
			columns: [
			{
				title: 'Prestador',
				field: 'codPrestador',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left',
				visible: false
			},{
				title: 'Fecha inicio atención',
				field: 'fechaInicioAtencion',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Causa atención',
				field: 'descripcausaexterna',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cie principal',
				field: 'descripcieprincipal',
				formatter: function(tnValor, toFila){ return toFila.codDiagnosticoPrincipal+'-'+toFila.descripcieprincipal;},
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cie egreso',
				field: 'descripcieegreso',
				formatter: function(tnValor, toFila){ return toFila.codDiagnosticoPrincipalE+'-'+toFila.descripcieegreso;},
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cie relacionado egreso 1',
				field: 'descripcieegresorelacionado1',
				formatter: function(tnValor, toFila){ return toFila.codDiagnosticoRelacionadoE1+'-'+toFila.descripcieegresorelacionado1;},
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cie relacionado egreso 2',
				field: 'descripcieegresorelacionado2',
				formatter: function(tnValor, toFila){ return toFila.codDiagnosticoRelacionadoE2+'-'+toFila.descripcieegresorelacionado2;},
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cie relacionado egreso 3',
				field: 'descripcieegresorelacionado3',
				formatter: function(tnValor, toFila){ return toFila.codDiagnosticoRelacionadoE3+'-'+toFila.descripcieegresorelacionado3;},
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cie fallece',
				field: 'descripciefallece',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Condición Destino Egreso',
				field: 'descripcioncondicionegreso',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Fecha egreso',
				field: 'fechaEgreso',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			}
		  ]
		});
	},
	
	iniciaTablaRipsHospitalizacion: function(){
		oRips1036.gotablaHospitalizacion.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			locale: 'es-ES',
			iconSize: 'sm',
			columns: [
			{
				title: 'Prestador',
				field: 'codPrestador',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left',
				visible: false
			},
			{
				title: 'Vía ingreso servicio',
				field: 'descripviaingreso',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Fecha inicio atención',
				field: 'fechalnicioAtencion',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Número atención',
				field: 'numAutorizacion',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Causa atención',
				field: 'descripcausaexterna',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cie principal',
				field: 'descripcieprincipal',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cie egreso',
				field: 'descripcieegreso',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cie relacionado egreso 1',
				field: 'descripcieegresorelacionado1',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cie relacionado egreso 2',
				field: 'descripcieegresorelacionado2',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cie relacionado egreso 3',
				field: 'descripcieegresorelacionado3',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cie complicación',
				field: 'codComplicacion',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Condicion Destino Egreso',
				field: 'descripcioncondicionegreso',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cie fallece',
				field: 'descripciefallece',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Fecha egreso',
				field: 'fechaEgreso',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			}
		  ]
		});
	},

	iniciaTablaRipsRecienNacido: function(){
		oRips1036.gotablaRecienNacidos.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			locale: 'es-ES',
			iconSize: 'sm',
			columns: [
			{
				title: 'Prestador',
				field: 'codPrestador',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left',
				visible: false
			},
			{
				title: 'Tipo identificación',
				field: 'tipoDocumentoldentificacion',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Número identificación',
				field: 'numDocumentoldentificacion',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Fecha Nacimiento',
				field: 'fechaNacimiento',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Edad Gestacional',
				field: 'edadGestacional',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: '# Consultas prenatal',
				field: 'numConsultasCPrenatal',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Sexo Biológico',
				field: 'descripsexo',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Peso',
				field: 'peso',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Diagnóstico principal',
				field: 'descripcieprincipal',
				formatter: function(tnValor, toFila){ return toFila.codDiagnosticoPrincipal+'-'+toFila.descripcieprincipal;},
			  	width: 30, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Destino Usuario Egreso',
				field: 'descripcioncondicionegreso',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cie fallece',
				field: 'descripciefallece',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Fecha egreso',
				field: 'fechaEgreso',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			}
		  ]
		});
	},
	
	iniciaTablaRipsUsuario: function(){
		oRips1036.gotablaUsuarios.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			locale: 'es-ES',
			iconSize: 'sm',
			columns: [
			{
				title: 'Tipo Id.',
				field: 'tipoDocumentoIdentificacion',
				width: 3, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},{
				title: 'Nro. Identificación',
				field: 'numDocumentoIdentificacion',
				width: 5, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Paciente',
				field: 'nombrepaciente',
				width: 20, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},{
				title: 'Tipo usuario',
				field: 'descriptipousuario',
				width: 20, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Fecha nacimiento',
				field: 'fechaNacimiento',
				width: 5, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Género',
				field: 'descripsexo',
			  	width: 5, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Pais residencia',
				field: 'descrippaisresidencia',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Municipio residencia',
				field: 'descripciudadresidencia',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Zona territorial',
				field: 'descripzonaterritorial',
			  	width: 5, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Incapacidad',
				field: 'incapacidad',
			  	width: 5, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'País origen',
				field: 'descrippaisnacimiento',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			}
		  ]
		});
	},
	
	iniciaTablaRipsProcedimientos: function(){
		oRips1036.gotablaProcedimientos.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			locale: 'es-ES',
			iconSize: 'sm',
			columns: [
			{
				title: 'Prestador',
				field: 'codPrestador',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left',
				visible: false
			},{
				title: 'Fecha inicio atención',
				field: 'fechaInicioAtencion',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'id MIPRES',
				field: 'idMIPRES',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Autorizacion',
				field: 'numAutoriacion',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Procedimiento',
				field: 'codProcedimiento',
				formatter: function(tnValor, toFila){ return toFila.codProcedimiento+'-'+toFila.descripprocedimiento;},
				width: 40, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Vía ingreso',
				field: 'descripviaingreso',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Modalidad Grupo Servicio',
				field: 'descripmodalidadatencion',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Grupo servicio',
				field: 'descripgruposervicio',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Servicio',
				field: 'codServicio',
				formatter: function(tnValor, toFila){ return toFila.codServicio+'-'+toFila.descripcodServicio;},
			  	width: 30, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Finalidad',
				field: 'descripFinalidad',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Persona realizo',
				formatter: function(tnValor, toFila){ return toFila.tipoDocumentoIdentificacion+'-'+toFila.numDocumentoIdentificacion;},
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cie Principal', 
				field: 'codDiagnosticoPrincipal', 
				formatter: function(tnValor, toFila){ return toFila.codDiagnosticoPrincipal+'-'+toFila.descripcieprincipal;},
				width: 10, widthUnit: "%", halign: 'center', align: 'left'
			},
			{
				title: 'Cie Relacionado', 
				field: 'codDiagnosticoRelacionado', 
				formatter: function(tnValor, toFila){ return toFila.codDiagnosticoRelacionado+'-'+toFila.descripcierelacionado;},
				width: 10, widthUnit: "%", halign: 'center', align: 'left'
			},
			{
				title: 'Cie Complicación',
				field: 'codComplicacion',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Valor servicio',
				field: 'vrServicio',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'right',
				formatter: function(tnValor, toFila){ return parseInt(tnValor).toLocaleString('es-CO', {minimumFractionDigits:0}); }	
			},
			{
				title: 'Tipo modelador',
				field: 'descripciontipomoderadora',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Valor modelador',
				field: 'valorPagoModerador',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'right',
				formatter: function(tnValor, toFila){ return parseInt(tnValor).toLocaleString('es-CO', {minimumFractionDigits:0}); }
			}
		  ]
		});
	},
	
	formaDiagnostico: function(value, row, index){
		return [
			'<a class="datosdiagnostico" style="color: black;" href="javascript:void(0)" title="Diagnóstico">' + value,
			'</a>'
		].join('');
	},
		
		
	eventosDiagnostico:  {
		'click .datosdiagnostico': function (e, value, row, index) {
			fnConfirm('Desea modificar el(os) diagnóstico(s)?', false, false, false, false, function(){
				
				oRips1036.validarDiagnostico();
				
			},'');
		}
	},
	
	validarDiagnostico: function() {
		
		
		fnAlert("Validar diagnóstico");
		
	},	
		

	iniciaTablaRipsOtros: function(){
		oRips1036.gotablaOtrosServicios.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			locale: 'es-ES',
			iconSize: 'sm',
			columns: [
			{
				title: 'Prestador',
				field: 'codPrestador',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left',
				visible: false
			},
			{
				title: 'Autorizacion',
				field: 'numAutorizacion',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'id MIPRES',
				field: 'idMIPRES',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Fecha suministro',
				field: 'fechaSuministroTecnologia',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Tipo',
				field: 'descriptipootroservicio',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Código Tecnologia',
				field: 'codTecnologiaSalud',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Nombre Tecnologia',
				field: 'nomTecnologiaSalud',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Cantidad',
				field: 'cantidadOS',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'center'
			},
			{
				title: 'Persona realizo',
				formatter: function(tnValor, toFila){ return toFila.tipoDocumentoIdentificacion+'-'+toFila.numDocumentoIdentificacion;},
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Valor unitario',
				field: 'vrUnitOS',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'right',
				formatter: function(tnValor, toFila){ 
					return parseInt(tnValor)>0?parseInt(tnValor).toLocaleString('es-CO', {minimumFractionDigits:0}):'0'; 
				}	
			},
			{
				title: 'Valor servicio',
				field: 'vrServicio',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'right',
				formatter: function(tnValor, toFila){ 
					return parseInt(tnValor)>0?parseInt(tnValor).toLocaleString('es-CO', {minimumFractionDigits:0}):'0'; 
					}	
			},
			{
				title: 'Tipo modelador',
				field: 'descripciontipomoderadora',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Valor modelador',
				field: 'valorPagoModerador',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'right',
				formatter: function(tnValor, toFila){ return parseInt(tnValor).toLocaleString('es-CO', {minimumFractionDigits:0}); }	
			}
		  ]
		});
	},
	
	iniciaTablaRipsTransaccion: function(){
		oRips1036.gotablaTransaccion.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			locale: 'es-ES',
			iconSize: 'sm',
			columns: [
			{
				title: 'Documento',
				field: 'numDocumentoldObligado',
				width: 20, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Factura',
				field: 'numFactura',
				width: 30, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Tipo nota',
				field: 'TipoNota',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Número nota',
				field: 'numNota',
			  	width: 45, widthUnit: "%",
				halign: 'center',
				align: 'left'
			}
		  ]
		});
	}
}	