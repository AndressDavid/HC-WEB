// Censo / Bitácora de Urgencias
var oCenso = {
	oVentana: $("#divRegistroCenso"),
	lObligarRegistro: false,
	cUrlAjax: 'vista-censourg/ajax/registro.php',
	tiposPermisos: [],
	oRegistro: {},
	oDatosIng: {},
	cValorPropiedades: '',
	cVacioSeccion: 'Ubicaciones por Sección ',
	fFunctionPost: null, // función que se debe ejecutar después de guardar el registro

	inicializar: function(tlObligarRegistro){
		if (oListaDiagnosticos.ListaDx.length==0) {
			var aDatos = typeof aDatosIngreso == 'object' ? {
					fecha: 0,
					sexo: aDatosIngreso.cSexo? aDatosIngreso.cSexo: '',
					edad: aDatosIngreso.aEdad? aDatosIngreso.aEdad: 0,
				} : {fecha:0, sexo:'', edad:0};
			oListaDiagnosticos.cargarDiagnosticos(aDatos);
		}
		oCenso.lObligarRegistro = (typeof tlObligarRegistro === "boolean") ? tlObligarRegistro: false;
		oCenso.consultarDatos();
		oCenso.autocompletar();
		$("#btnGuardarRegCenso").on('click', oCenso.guardar);
		$('#btnAntecedentesRegCenso').on('click', function(){
			oAntecedentesConsulta.mostrar();
		});
		$('#btnAdmisionRegCenso').on('click', function(){
			oModalDatosPaciente.consultaDatos(aDatosIngreso);
		});
	},

	consultarDatos: function(){
		$("#txtMedico").listaMedicos({tipos: "1,3,4,6,10,11,12,13"});
		$.ajax({
			type: "POST",
			url: oCenso.cUrlAjax,
			data: {accion: 'cargaini'},
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.error == '') {
					oCenso.tiposPermisos = loDatos.TiposPermisos;
					oCenso.cargaPlanesManejoAdm(loDatos.PlanesManejoAdm);
					oCenso.cargaPlanesManejoMed(loDatos.PlanesManejoMed);
					oCenso.cargaTiposDieta(loDatos.TiposDieta);
					oCenso.cargaUbicacionesSeccion(loDatos.UbicacionesSeccion);
				} else {
					fnAlert(loDatos.error);
				}
			} catch(err) {
				console.error(err);
				fnAlert('Error al obtener datos de inicio de Censo/Bitácora.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al cargar datos de inicio de Censo/Bitácora.');
		});
	},

	consultarDatosIngreso: function(tnIngreso, tcDxPrin, tcPlanDx){
		var loDatos = {
				accion: 'datosing',
				ingreso:tnIngreso?tnIngreso:0,
				dxprin:tcDxPrin?tcDxPrin:'',
				conducta:tcPlanDx?tcPlanDx:''
			};
		$.ajax({
			type: "POST",
			url: oCenso.cUrlAjax,
			data: loDatos,
			dataType: "json"
		})
		.done(async function(loDatos) {
			try {
				if (loDatos.error == '') {
					oCenso.oRegistro = loDatos.InfoCenso;
					oCenso.oDatosIng = loDatos.DatosIng;
					oCenso.cargaPermisos();
					oCenso.cargarDatosIng(loDatos.DatosIng);
					oCenso.cValorPropiedades = await oCenso.obtenerValorPropiedades();
				} else {
					fnAlert(loDatos.error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('Error al obtener datos de inicio de Censo/Bitácora.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al cargar datos de inicio de Censo/Bitácora.');
		});
	},

	cargarDatosIng: function(toDatos){

		if (toDatos.Ubicacion.length>0) {
			$("#selUbicacion").val(toDatos.Ubicacion);
			$("#divUbicacion").SetTitle($("#selUbicacion option:selected").text());
		} else {
			$("#selUbicacion").val('');
			$("#divUbicacion").SetTitle(oCenso.cVacioSeccion);
		}
		$("#selPlanAdmin").val(toDatos.PlanAdm);
		$("#selPlanMedico").val(toDatos.PlanMed);
		$("#selExcluirBit").val(toDatos.Excluir==''?'NO':toDatos.Excluir);

		$("#txtMedico")
			.val(goUser.name+' - '+goUser.regm)
			.attr('data-nombre',goUser.name)
			.attr('data-reg',goUser.regm);
		if (typeof toDatos.Medico.regmed=="string"){
			if (toDatos.Medico.regmed.length>0){
				$("#txtMedico")
					.val(toDatos.Medico.nombre+' - '+toDatos.Medico.regmed)
					.attr('data-nombre',toDatos.Medico.nombre)
					.attr('data-reg',toDatos.Medico.regmed);
			}
		}

		//oCenso.oRegistro
		$("#selDieta").val(toDatos.Dieta);
		if (oCenso.oRegistro.hay_dieta) {
			$("#selDieta").val(oCenso.oRegistro.codDieta ? oCenso.oRegistro.codDieta : '');
		}

		$("#txtObservacionesAdmin").html(toDatos.ObsAdm);
		$("#txtObservacionesAsist").html(toDatos.ObsMed);
		var lcHtml='';
		// Diagnósticos
		$.each(toDatos.Diagnos, function(lnIndex, lcData){
			lcHtml += lcData;
		});
		$("#txtObservacionesDiagnos").html(lcHtml);
		if (toDatos.DxPrincipal.length>0) {
			$.each(oListaDiagnosticos.ListaDx, function(lcDiagno, lnIndex){
				if (lcDiagno.substr(0,4)==toDatos.DxPrincipal) {
					$("#selDxPrincipal").val(lcDiagno);
					$("#codDxPrincipal").val(toDatos.DxPrincipal);
					return;
				}
			});
		}
	},

	cargaPermisos: function(){
		var laOpciones={};

		// ADMINISTRATIVOS
		var llPermiso = $.inArray("01", oCenso.tiposPermisos) > -1;
		// permisos
		$("#selPlanAdmin,#txtObsAdmin,#btnAdmisionRegCenso").attr('disabled', !llPermiso);
		// campos obligatorios
		$("#selPlanAdmin").attr('required', llPermiso);
		if (llPermiso) {
			laOpciones.selPlanAdmin = {required: true};
			$("#lblPlanAdmin").addClass('required');
			$('#tabOptObsAdmin').tab('show');
		}

		// ASISTENCIALES
		llPermiso = $.inArray("02", oCenso.tiposPermisos) > -1;
		// permisos
		//.cmdProcedimientos.enabled=(llPermiso==.t. AND (flValidarOpcionMenu('VFP','HCPPRI','1002')==.t. OR flValidarOpcionMenu('VFP','HCPPRI','1008')==.t.))
		$("#btnProcedimientosRegCenso").attr('disabled', !llPermiso);
		$("#selPlanMedico,#txtObsAsist,#txtObsDiagnostico,#btnAntecedentesRegCenso,.dx-RegCenso").attr('disabled', !llPermiso);
		$("#selDieta").attr('disabled', !llPermiso && oCenso.oRegistro.hay_dieta);
		//.StatusBarText=IIF(this.oRegistro.diemed==.t.,"La dieta esta definida en evolución medica, no es modificable desde la bitácora",.StatusBarText)
		// campos obligatorios
		$("#selPlanMedico,#selDieta").attr('required', llPermiso);
		if (llPermiso) {
			laOpciones.selPlanMedico = {required: true};
			laOpciones.selDieta = {required: true};
			$("#lblPlanMedico,#lblDieta").addClass('required');
			$('#tabOptObsAsist').tab('show');
		}

		// COMBINADOS
		llPermiso = ($.inArray("01", oCenso.tiposPermisos) > -1) || ($.inArray("02", oCenso.tiposPermisos) > -1);
		// permisos
		$("#selUbicacion,#selExcluirBit,#txtMedico,#btnGuardarRegCenso,#btnIngresosRegCenso").attr('disabled', !llPermiso);
		// campos obligatorios
		$("#selUbicacion,#selExcluirBit,#txtMedico").attr('required', llPermiso);
		if (llPermiso) {
			$("#lblUbicacion,#lblExcluirBit,#lblMedico").addClass('required');
			laOpciones.selUbicacion = {required: true};
			laOpciones.selExcluirBit = {required: true};
			laOpciones.txtMedico = {required: true};
		}

		if (oCenso.lObligarRegistro) {
			$("#btnGuardarRegCenso").attr('disabled', false);
			$("#btnCerrarRegCenso,.btnRegCensoConsulta").remove();
		}

		// Establece propiedades de validación
		$('#frmCabecera').validate( {
			rules: laOpciones,
			errorElement: "div",
			errorPlacement: function ( error, element ) {
				// Agregue la clase `help-block` al elemento de error
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

	cargaPlanesManejoAdm: function(toPlanes){
		$("#selPlanAdmin").append("<option></option>");
		$.each(toPlanes, function(lnIndex, lcDato){
			$("#selPlanAdmin").append('<option value="' + lnIndex + '">' + lcDato + '</option>');
		});
	},

	cargaPlanesManejoMed: function(toPlanes){
		$("#selPlanMedico").append("<option></option>");
		$.each(toPlanes, function(lnIndex, lcDato){
			$("#selPlanMedico").append('<option value="' + lnIndex + '">' + lcDato + '</option>');
		});
	},

	cargaTiposDieta: function(toDietas){
		$("#selDieta").append("<option></option>");
		$.each(toDietas, function(lnIndex, lcDato){
			$("#selDieta").append('<option value="' + lnIndex + '">' + lcDato.DSC + '</option>');
		});
	},

	cargaUbicacionesSeccion: function(toUbiSec){
		$("#selUbicacion").append("<option></option>");
		var laData=[],
			loDataSub=[],
			lcTitulo='';
		$.each(toUbiSec, function(lnIndex, lcDato){
			if (lcDato.tipo=='root') {
				if (lnIndex>0) {
					laData.push({
						title: '<i class="fa fa-'+lcDato.icono+'" /> '+lcTitulo,
						href: '#0',
						data: loDataSub
					});
				}
				lcTitulo=lcDato.descripcion;
				loDataSub=[];

			} else {
				$("#selUbicacion").append('<option value="' + lcDato.codigo + '">' + lcDato.descripcion + '</option>');

				loDataSub.push({
					title: '<i class="fa fa-'+lcDato.icono+'" /> '+lcDato.descripcion,
					href: '#1',
					dataAttrs: [{title:"codigo", data:lcDato.codigo}]
				});
			}
		});
		$("#divUbicacion").DropDownTree({
			title: oCenso.cVacioSeccion,
			data: laData,
			multiSelect: false,
			selectChildren: false,
			clickHandler: function(loElement){
				var loObject = $(loElement).find("a").first();
				// Si es una ubicación y no una sección
				if (loObject.attr('href')=="#1") {
					$("#divUbicacion").SetTitle(loObject.text());
					$("#selUbicacion").val(loElement.attr('data-codigo')); // valor en el select
					$("#divUbicacion").dropdown('hide'); // oculta menú
				}
			},
		});
	},

	mostrar: function(){
		oCenso.autocompletar();
		oCenso.oVentana.modal("show");
	},

	ocultar: function(){
		oCenso.oVentana.modal("hide");
	},

	limpiar: function(){
		var lcObj = "#selUbicacion,#selPlanAdmin,#selPlanMedico,#txtMedico,#selDieta,#txtObsAdmin,#txtObsAsist,#txtObsDiagnostico,#txtInterconsultas,#selDxPrincipal,#codDxPrincipal";
		for (lnI=1; lnI<4; lnI++) { lcObj+=',#selDxRelacionado'+lnI+',#codDxRelacionado'+lnI; }
		$(lcObj).val('');
		$("#txtMedico").attr('data-nombre','').attr('data-reg','');
		$("#txtObservacionesAdmin,#txtObservacionesAsist,#txtObservacionesDiagnos,#txtEvoluciones").html('');
		$("#selExcluirBit").val('NO');
		$("#divUbicacion").SetTitle(oCenso.cVacioSeccion);
	},

	guardar: async function(e){
		e.preventDefault();
		// validar si hay cambios en el form
		var lcValorPropiedades = await oCenso.obtenerValorPropiedades();
		if (oCenso.cValorPropiedades == lcValorPropiedades) {
			fnInformation('No hay cambios para guardar');
		} else {

			if (! $('#frmCabecera').valid()){
				return false;
			}

			fnConfirm('¿Esta seguro de guardar el censo de urgencias?', false, false, false, false,
				{
					text: 'Si',
					action: function(){
						if ($("#selExcluirBit").val()=='SI'){
							fnConfirm('¿Realmente desea excluir el registro del CENSO de urgencias?', false, false, false, false,
								{ 
									text: 'Si',
									action: function(){
										oCenso.envioGuardar();
									}
								},
								{
									text: 'No',
									action: function(){
										fnAlert("Evento cancelado");
									}
								}
							);
						} else {
							oCenso.envioGuardar();
						}
					}
				},
				{ text: 'No' }
			);
		}
	},

	envioGuardar: function(){
		var loDatos = oCenso.obtenerDatos();
		loDatos['accion'] = 'guardarreg';
		$.ajax({
			type: "POST",
			url: oCenso.cUrlAjax,
			data: loDatos,
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.error=='') {
					oCenso.ocultar();
					oCenso.limpiar();
					if (typeof oCenso.fFunctionPost==='function') {
						oCenso.fFunctionPost();
					}
				} else {
					fnAlert(loDatos.error);
				}
			} catch(err) {
				console.error(err);
				fnAlert('Error al guardar registro Censo/Bitácora.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al guardar registro Censo/Bitácora.');
		});
	},

	obtenerDatos: function() {
		return {
			ingreso: oCenso.oRegistro.ingreso,
			via: oCenso.oRegistro.via,
			ubicacion: $("#selUbicacion").val(),
			planAdmin: $("#selPlanAdmin").val().trim(),
			planMedico: $("#selPlanMedico").val().trim(),
			medico: $("#txtMedico").attr('data-reg'),
			dieta: $("#selDieta").val(),
			excluirBit: $("#selExcluirBit").val(),
			obsAdmin: $("#txtObsAdmin").val().trim(),
			obsAsist: $("#txtObsAsist").val().trim(),
			dxPrincipal: $("#codDxPrincipal").val(),
			dxRelacionado1: $("#codDxRelacionado1").val(),
			dxRelacionado2: $("#codDxRelacionado2").val(),
			dxRelacionado3: $("#codDxRelacionado3").val(),
			obsDiagnostico: $("#txtObsDiagnostico").val().trim()
		}
	},

	autocompletar: function() {
		oListaDiagnosticos.autocompletar('#selDxPrincipal', 6, 25);
		oListaDiagnosticos.autocompletar('#selDxRelacionado1', 6, 25);
		oListaDiagnosticos.autocompletar('#selDxRelacionado2', 6, 25);
		oListaDiagnosticos.autocompletar('#selDxRelacionado3', 6, 25);
	},

	obtenerValorPropiedades: async function() {
		return await digestMessage('SHA-256', JSON.stringify(oCenso.obtenerDatos()));
	}

};


async function digestMessage(tcAlgoritmo, tcTexto) {
	if (typeof tcAlgoritmo === 'string') {
		tcAlgoritmo = tcAlgoritmo.toUpperCase();
		if ($.inArray(tcAlgoritmo,['SHA-256','SHA-384','SHA-512'])==-1) {
			tcAlgoritmo = 'SHA-256';
		}
	} else {
		tcAlgoritmo = 'SHA-256';

	}
	const msgUint8 = new TextEncoder().encode(tcTexto);								// encode as (utf-8) Uint8Array
	const hashBuffer = await crypto.subtle.digest(tcAlgoritmo, msgUint8);			// hash the tcTexto
	const hashArray = Array.from(new Uint8Array(hashBuffer));						// convert buffer to byte array
	const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');	// convert bytes to hex string
	return hashHex;
}
