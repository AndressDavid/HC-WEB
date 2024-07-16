var oModalOrdAmbPDF = {
	cUrlAjax:'vista-comun/ajax/modalOrdAmbPDF',
	oDatos: null,
	oDatosHC: null,
	oDatosEPI: null,
	oDatosNihss: null,
	bMostrarInfo: false,
	oItems: {
		MEDIC: {activo:false, indices: '1,8',		titulo: 'Medicamentos'},
		CTCMD: {activo:false, indices: '1,8,99',	titulo: 'CTC Medica.'},
		PROCE: {activo:false, indices: '1,9,91',	titulo: 'Procedimientos'},
		CTCPR: {activo:false, indices: '1,9,99',	titulo: 'CTC Proced.'},
		INSUM: {activo:false, indices: '1,9,92',	titulo: 'Insumos'},
		INSUN: {activo:false, indices: '13',		titulo: 'Insumos'},
		INTER: {activo:false, indices: '10',		titulo: 'Interconsultas'},
		DIETA: {activo:false, indices: '1,4',		titulo: 'Dieta'},
		INCAP: {activo:false, indices: '1,6,14',	titulo: 'Incapacidad'},
		OTRAS: {activo:false, indices: '7',			titulo: 'Otras'},
		RECOM: {activo:false, indices: '11,12',		titulo: 'Recomendaciones'},
		TODOS: {activo:false, indices: '',			titulo: 'Ver Todo'}
	},

	inicializar: function()
	{
		oModalOrdAmbPDF.obtenerItemsTodos();
		$("#btnOrdAmbPDFTodos").addClass("btn-light").html('<span style="color: Tomato;"><i class="fas fa-file-pdf mr-2"></i></span> '+oModalOrdAmbPDF.oItems.TODOS.titulo);
		$("#divOrdAmbPDFContent").on("click", ".btn-ordamb", oModalOrdAmbPDF.verPdf);
	},

	verPdf: function()
	{
		var lcTipo = $(this).attr("data-tipo"),
			lcAcc = $(this).attr("data-acc"),
			loEnvia = [],
			lcDescripcion = '',
			lcModulo = 'ORDAMB';
		if (!(oModalOrdAmbPDF.oDatosHC==null)){
			lcModulo = 'HISCLI';
		} else if (!(oModalOrdAmbPDF.oDatosEPI==null)){
			lcModulo = 'EPICRISIS';
		}
		if (lcAcc=="PDF") {
			switch(lcTipo){
				case "HISTORIA":
					loEnvia.push(oModalOrdAmbPDF.oDatosHC);
					if (oModalOrdAmbPDF.oDatosNihss !== null) { loEnvia.push(oModalOrdAmbPDF.oDatosNihss); }
					lcDescripcion = 'HISTORIA CLÍNICA '+oModalOrdAmbPDF.oDatosHC.tFechaHora;
					break;
				case "EPICRISIS":
					loEnvia.push(oModalOrdAmbPDF.oDatosEPI);
					lcDescripcion = 'EPICRISIS '+oModalOrdAmbPDF.oDatosEPI.tFechaHora;
					lcModulo = 'EPICRISIS';
					break;
				case "TODOS":
					if (oModalOrdAmbPDF.oDatosHC !== null){
						loEnvia.push(oModalOrdAmbPDF.oDatosHC);
						if (oModalOrdAmbPDF.oDatosNihss !== null) { loEnvia.push(oModalOrdAmbPDF.oDatosNihss); }
						lcDescripcion = 'HISTORIA CLÍNICA Y ORDENES AMB. '+oModalOrdAmbPDF.oDatosHC.tFechaHora;
					} else if (oModalOrdAmbPDF.oDatosEPI !== null){
						loEnvia.push(oModalOrdAmbPDF.oDatosEPI);
						lcDescripcion = 'EPICRISIS Y ORDENES AMB. '+oModalOrdAmbPDF.oDatosEPI.tFechaHora;
					} else {
						lcDescripcion = 'ORDENES AMBULATORIAS '+oModalOrdAmbPDF.oDatos.tFechaHora;
					}
					// break; // No Break
				default:
					var loTipo = oModalOrdAmbPDF.oItems[lcTipo];
					if (lcDescripcion=='') {
						lcDescripcion = 'ORDENES AMB. '+loTipo.titulo+' '+oModalOrdAmbPDF.oDatos.tFechaHora;
					}
					oModalOrdAmbPDF.oDatos.nConsecEvol = loTipo.indices;
					loEnvia.push(oModalOrdAmbPDF.oDatos);
			}
			vistaPreviaPdf({datos:JSON.stringify(loEnvia)}, null, lcDescripcion, lcModulo);

		} else if (lcAcc=="VER") {
			switch(lcTipo){
				case "HISTORIA":
					loEnvia = oModalOrdAmbPDF.oDatosHC;
					lcDescripcion = 'HISTORIA CLÍNICA '+oModalOrdAmbPDF.oDatosHC.tFechaHora;
					break;
				case "EPICRISIS":
					loEnvia = oModalOrdAmbPDF.oDatosEPI;
					lcDescripcion = 'EPICRISIS '+oModalOrdAmbPDF.oDatosEPI.tFechaHora;
					break;
				default:
					var loTipo = oModalOrdAmbPDF.oItems[lcTipo];
					lcDescripcion = 'ORDENES AMB. '+loTipo.titulo+' '+oModalOrdAmbPDF.oDatos.tFechaHora;
					oModalOrdAmbPDF.oDatos.nConsecEvol = oModalOrdAmbPDF.oItems[lcTipo].indices;
					loEnvia = oModalOrdAmbPDF.oDatos;
			}
			oModalVistaPrevia.mostrar(loEnvia, lcDescripcion, lcModulo);
		}
	},

	obtenerItemsTodos: function()
	{
		$.ajax({
			type: "POST",
			url: oModalOrdAmbPDF.cUrlAjax,
			data: {accion: 'todos'},
			dataType: "json"
		})
		.done(function(loDatos) {
			if (loDatos.error=='') {
				oModalOrdAmbPDF.oItems['TODOS'].indices=loDatos.datos.indices;
				laListaTodos=loDatos.datos.nombres.split(',');
				oModalOrdAmbPDF.oItems.MEDIC.activo = $.inArray("FORMULA",laListaTodos)>-1;
				oModalOrdAmbPDF.oItems.CTCMD.activo = $.inArray("NOPOS",laListaTodos)>-1;
				oModalOrdAmbPDF.oItems.PROCE.activo = $.inArray("PROCEDIMIENTOS",laListaTodos)>-1;
				oModalOrdAmbPDF.oItems.CTCPR.activo = $.inArray("PROCEDIMIENTOS",laListaTodos)>-1;
				oModalOrdAmbPDF.oItems.INSUM.activo = $.inArray("INSUMOS",laListaTodos)>-1;
				oModalOrdAmbPDF.oItems.INSUN.activo = $.inArray("INSUMOS",laListaTodos)>-1;
				oModalOrdAmbPDF.oItems.INTER.activo = $.inArray("INTERCONSULTAS",laListaTodos)>-1;
				oModalOrdAmbPDF.oItems.DIETA.activo = $.inArray("DIETA",laListaTodos)>-1;
				oModalOrdAmbPDF.oItems.INCAP.activo = $.inArray("INCAPACIDAD",laListaTodos)>-1;
				oModalOrdAmbPDF.oItems.OTRAS.activo = $.inArray("OTRAS",laListaTodos)>-1;
				oModalOrdAmbPDF.oItems.RECOM.activo = $.inArray("RECOMENDACION",laListaTodos)>-1;
			} else {
				fnAlert(loDatos.error);
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar elementos para imprimir.');
		});
	},

	consultar: function(tbMostrar, tnIngreso, tcTipoId, tnNumId, tnFechaHora, tnCnsCita, tnCnsConsulta, tnCnsOrden)
	{
		oModalOrdAmbPDF.oDatos = {
			nIngreso	: tnIngreso,
			cTipDocPac	: tcTipoId,
			nNumDocPac	: tnNumId,
			cTipoDocum	: "5000",
			cTipoProgr	: "ORDA01A",
			tFechaHora	: tnFechaHora,
			nConsecCita	: tnCnsCita,
			nConsecCons	: tnCnsConsulta,
			nConsecDoc  : tnCnsOrden,
			nConsecEvol	: '',
			cCUP		: '',
			cCodVia		: '',
			cSecHab		: ''
		}
		$("#divOrdAmbPDF").html("");
		$.ajax({
			type: "POST",
			url: oModalOrdAmbPDF.cUrlAjax,
			data: {
				accion: 'conordamb',
				ingreso: tnIngreso,
				cnsord: tnCnsOrden
			},
			dataType: "json"
		})
		.done(function(loDatos) {
			if (loDatos.error=='') {
				oModalOrdAmbPDF.crearBotones(loDatos.datos);
				if (tbMostrar) {
					if (oModalOrdAmbPDF.bMostrarInfo) {
						var laFecHora = tnFechaHora.split(' '),
							lcFechaHora = strNumAFecha(laFecHora[0],'/')+' '+strNumAHora(laFecHora[1]);
						$("#divOrdAmbPDFInfo").html('<b>Orden '+tnCnsOrden+'</b> - '+lcFechaHora+'<br><b>Ingreso:</b> '+tnIngreso+'<hr>');
					}
					oModalOrdAmbPDF.mostrar();
				}
			} else {
				fnAlert(loDatos.error);
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar la órden ambulatoria.');
		});
	},

	crearBotonesDatos: function(toOrdAmb){
		$("#divOrdAmbPDF").html("");
		var lbMed = typeof toOrdAmb.MedicamentosAmb == 'object',
			lbPrc = typeof toOrdAmb.Procedimientos == 'object',
			lbCTCMed = false,
			lbCTCPrc = false;
		// Existe CTC Medicamentos
		if (lbMed) {
			$.each(toOrdAmb.MedicamentosAmb, function(lnClave, loMed){
				if (loMed.NOPOS=="1") {
					lbCTCMed = true;
					return;
				}
			});
		}
		// Existe CTC Procedimientos
		if (lbPrc) {
			$.each(toOrdAmb.Procedimientos, function(lnClave, loProc){
				if (!(typeof loProc.RESUMEN=="undefined")) {
					if (!(loProc.RESUMEN=='')) {
						lbCTCPrc = true;
						return;
					}
				}
			});
		}
		oModalOrdAmbPDF.crearBotones({
			MEDIC: lbMed,
			CTCMD: lbCTCMed,
			PROCE: lbPrc,
			CTCPR: lbCTCPrc,
			INSUM: typeof toOrdAmb.Insumos == 'object',
			INSUN: toOrdAmb.Insumos!= '',
			INTER: typeof toOrdAmb.Interconsultas == 'object',
			DIETA: toOrdAmb.Dieta.tipoDieta.length > 0,
			INCAP: toOrdAmb.Incapacidad.DiasIncapacidad > 0,
			OTRAS: toOrdAmb.Otras.ObservacionesOtras.length > 0,
			RECOM: toOrdAmb.Recomendaciones.RecomendacionGeneral.length > 0 || toOrdAmb.Recomendaciones.RecomendacionNutricional.length > 0
		});
	},

	crearBotones: function(toBotones){
		var lbDeshabilitarTodos = true;
		$.each(toBotones, function(lcKey, lbValor){
			if (lbValor) {
				var laHtml = [
						'<div class="col-12 mb-1">',
						'<button type="button" class="btn btn-sm btn-light btn-ordamb" data-tipo="'+lcKey+'" data-acc="VER"><span style="color: Dodgerblue;"><i class="fas fa-eye"></i></span></button> ',
						'<button type="button" class="btn btn-sm btn-light mr-3" data-tipo="'+lcKey+'" disabled="true"><span style="color: #AAA"><i class="fas fa-file-pdf"></i></span></button> ',
						oModalOrdAmbPDF.oItems[lcKey].titulo,
						'</div>',
					];
				if (oModalOrdAmbPDF.oItems[lcKey].activo) {
					lbDeshabilitarTodos = false;
					laHtml[2] = '<button type="button" class="btn btn-sm btn-light btn-ordamb mr-3" data-tipo="'+lcKey+'" data-acc="PDF"><span style="color: Tomato;"><i class="fas fa-file-pdf"></i></span></button> ';
				}
				$("#divOrdAmbPDF").append(laHtml.join(''));
			}
		});
		$("#btnOrdAmbPDFTodos").attr("disabled",lbDeshabilitarTodos);
	},

	habilitarBoton: function(toBoton){
		switch(toBoton){
			case "HISTORIA":	$("#divOrdAmbPDF_HC").show(); break;
			case "EPICRISIS":	$("#divOrdAmbPDF_Epi").show(); break;
		}
	},

	mostrar: function(){
		$("#modalOrdAmbPDF").modal("show");
	},

	ocultar: function(){
		$("#modalOrdAmbPDF").modal("hide");
	}
}