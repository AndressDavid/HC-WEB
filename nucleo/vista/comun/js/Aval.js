var oAval = {

	lcTitulo : '',
	lcMensajeError : '',
	lcObjetoError: '',
	loDatosAval: [],
	lcTipoAval: '',

	inicializar: function(tcTipo)
	{
		oAval.lcTipoAval = tcTipo;
		oAval.ConsultarAval(tcTipo);
	},

	ConsultarAval: function(tcTipoConsulta)
	{
		oAval.lcTitulo = (tcTipoConsulta=='HC'?'Avalar Historia Clínica':'Avalar Evoluciones');
		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/Aval.php",
			data: {TipoConsulta: tcTipoConsulta, Consec: aDatosIngreso['nConCons'], ingreso: aDatosIngreso['nIngreso']},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.DATOS != []) {(toDatos.DATOS)
						oAval.loDatosAval = toDatos.DATOS;
						if(tcTipoConsulta=='HC'){
						   oAval.CargarDatosAvalHC(toDatos.DATOS);
						}else{
							if(tcTipoConsulta==='E'){
								oAval.CargarObjetos(toDatos.DATOS.Datos.Eventualidad);
							}else{
								oAval.CargarDatosAvalEV(toDatos.DATOS);
							}
						}
 					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda para Avalar HC.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consultar Aval de HC.");
		});
	},

	CargarDatosAvalHC: function(toDatosAval)
	{
		// Texto pandemia
		if (toDatosAval.Datos.edtxtPandemia!=''){
			oTextoInformativo.activa = toDatosAval.Datos.edtxtPandemia.length>0;
			if(oTextoInformativo.activa){
				$("#edtxtPandemia").val(toDatosAval.Datos.edtxtPandemia);
				oTextoInformativo.mostrar();
			}
		}

		// Motivo de Consulta
		if (!(toDatosAval.Datos.MotivoC===undefined)){
			oAval.CargarObjetos(toDatosAval.Datos.MotivoC);
		}

		// Antecedentes
		if (!(toDatosAval.Datos.Antecedentes===undefined)){
			oAval.CargarObjetos(toDatosAval.Datos.Antecedentes);
			oAntecedentes.validarEstado();
		}

		// Vacuna COVID
		if (!(toDatosAval.Datos.COVID===undefined)){
			oAval.CargarCovid(toDatosAval.Datos.COVID);
		}

		// Revision Sistema
		if (!(toDatosAval.Datos.Revision===undefined)){
			oAval.CargarObjetos(toDatosAval.Datos.Revision);
		}

		// Examen Físico
		if (!(toDatosAval.Datos.Examen===undefined)){
			oAval.CargarObjetos(toDatosAval.Datos.Examen);
		}

		// Actividad Física
		if (!(toDatosAval.Datos.Actividad===undefined)){
			oAval.CargarObjetos(toDatosAval.Datos.Actividad);
			oAval.CargarActividad(toDatosAval.Datos.Actividad);
		}

		 // Conciliacion
		if (!(toDatosAval.Datos.Conciliacion===undefined)){
			loDatoObjeto.DATOS = toDatosAval.Datos.Conciliacion;
			oConciliacion.CargarConciliacion(loDatoObjeto);
			$('#selConsume').removeAttr("disabled");
			$('#selInforma').removeAttr("disabled");
		}

		// Diagnostico
		if (!(toDatosAval.Datos.Diagnostico===undefined)){
			oAval.CargarDiagnostico(toDatosAval.Datos.Diagnostico);
		}

		// Plan de manejo
		if (!(toDatosAval.Datos.Planmanejo===undefined)){
			oAval.CargarObjetos(toDatosAval.Datos.Planmanejo);
			if ($("#SeltuvoElectro").val()=='Si'){
				$('#txtTuvoElectrocardiograma').removeAttr("disabled");
				$('#lblTuvoElectrocardiograma').addClass("required");
			}else{
				$('#txtTuvoElectrocardiograma').attr("disabled","disabled");
				$('#lblTuvoElectrocardiograma').removeClass("required");
			}
		}

		// Escala SadPersons
		if (!(toDatosAval.Datos.escalaSadPersons===undefined)){
			oAval.CargarEscalaSAD();
		}
	},

	CargarDatosAvalEV: function(toDatosAval)
	{
		// Texto pandemia
		var loDatoAnalisis = oAval.loDatosAval.Datos.Analisis;
		if (!(loDatoAnalisis.selConductaSeguir=='01' && loDatoAnalisis.selEstadoSalida == '002')){
			oAval.CargarTextoPandemia();
		}

		// Diagnostico
		if (!(toDatosAval.Datos.Diagnostico===undefined)){
			oAval.CargarDiagnostico(toDatosAval.Datos.Diagnostico);
		}

		if (!(toDatosAval.Datos.RegistroUci===undefined)){
			oAval.CargarObjetos(toDatosAval.Datos.RegistroUci);
		}

		if (!(toDatosAval.Datos.DatosCieCups===undefined)){
			if (toDatosAval.Datos.DatosCieCups.length>0){
				oAval.CargarObjetos(toDatosAval.Datos.DatosCieCups);
				oAval.CargarComplicaciones(toDatosAval.Datos.DatosCieCups);
			}
		}
		oAval.CargarNihss();
		 // Actividad Física
		if (!(toDatosAval.Datos.Actividad===undefined)){
			oAval.CargarObjetos(toDatosAval.Datos.Actividad);
			oAval.CargarActividad(toDatosAval.Datos.Actividad);
		 }

		if (!(toDatosAval.Datos.escalaHasbled===undefined)){
			oEscalaHasbled.ConsultarAvalHS();
		}

		if (!(toDatosAval.Datos.escalaChadsvas===undefined)){
			oEscalaChadsvas.ConsultarAvalCH();
		}

		if (!(toDatosAval.Datos.escalaCrusade===undefined)){
			oEscalaCrusade.ConsultarAvalCR();
		}

		if (!(toDatosAval.Datos.Analisis===undefined)){
			oAval.CargarObjetos(toDatosAval.Datos.Analisis, true);
		}

		 // Escala SadPersons
		if (!(toDatosAval.Datos.escalaSadPersons===undefined)){
			oAval.CargarEscalaSAD();
		}
	},
	CargarTextoPandemia: function()
	{
		var lcDato = oAval.loDatosAval.Datos.edtxtPandemia;
		if (lcDato!=''){
			oTextoInformativo.activa = lcDato.length>0;
			if(oTextoInformativo.activa){
				$("#edtxtPandemia").val(lcDato);
				oTextoInformativo.mostrar();
			}
		}
	},

	CargarObjetos: function(toDatosObjeto, tlChange)
	{
		loDatoObjeto = toDatosObjeto;
		$.each(loDatoObjeto,function(lckey, loValor){
			loObjeto = $('#'+lckey);
			if(loObjeto.prop('type')=='checkbox'){
			   loObjeto.prop('checked',true);
			}else{
				var lcCadena = lckey.substring(0,8);
				if(lcCadena == 'lblPunto'){
					loObjeto.text(loValor);
				}else{
					loObjeto.val(loValor);
				}
				if(tlChange==true){
					loObjeto.change();
				}
			}
		});
	},

	CargarCovid: function(toDatosObjeto)
	{
		lcDatoObjeto = toDatosObjeto.DATOS;
		var laRegistro=lcDatoObjeto.split('|'),
			aDataVacCvd=[], lnId=1;
		$.each(laRegistro, function(lnKey,lcVC){
			if(lcVC.length>0){
				var loVacCov=JSON.parse(lcVC);
				loVacCov.vacuna=toDatosObjeto.VACUNAD;
				loVacCov.vacunac=toDatosObjeto.VACUNA;
				loVacCov.id=lnId++;
				aDataVacCvd.push(loVacCov);
			}
		});
		$("#tblVacunas").bootstrapTable('append', aDataVacCvd);
		oAntecedentes.nNumVacuna=lnId;
	},

	CargarActividad: function(toDatosObjeto)
	{
		var laRegistro=toDatosObjeto.actividades,
			aDataAF=[], lnId=1;
		$.each(laRegistro, function(lnKey,lcAF){
			lcAF.id=lnId++;
			lcAF.TIPO=lcAF.DESCRIPCIONTIPO;
			lcAF.CLASE=lcAF.DESCRIPCIONCLASE;
			lcAF.INTENSIDAD=lcAF.DESCRIPCIONINTENSIDAD;
			lcAF.ACTIVIDAD=lcAF.DESCRIPCIONACTIVIDAD;
			aDataAF.push(lcAF);
		});
		$("#tblActividadHC").bootstrapTable('append', aDataAF);
		$("#selRealizaActividad").change();
	},

	CargarDiagnostico: function(toDatosObjeto)
	{
		var laRegistro=toDatosObjeto,
			aDataDx=[], lnId=1;
		$.each(laRegistro, function(lnKey,lcDx){
			lcDx.id=lnId++;
			lcDx.DESCARTE='';
			lcDx.CODDESCARTE='';
			lcDx.MARCAVIENE=1;
			aDataDx.push(lcDx);
		});
		$("#tblCiePrincipal").bootstrapTable('append', aDataDx);
	},

	CargarNihss: function()
	{
		if (!(oAval.loDatosAval.Datos.Nihss===undefined)){
			oAval.CargarObjetos(oAval.loDatosAval.Datos.Nihss, true);
		}
	},

	CargarEscalaSAD: function(){
		laDatos = oAval.loDatosAval.Datos.escalaSadPersons;
		$.each($('.selectSadPersons'), function(lnIndex, loElemento){
			var lcCodigo = $(loElemento).attr('data-codigo');
			$(loElemento).attr('disabled', false).val(laDatos[lcCodigo]=='SI'?1:0).change();
		});
	},

	CargarComplicaciones: function(toDatosObjeto)
	{
		var  llSeleccion = false;
		var TablaComplica = oProcedimientoEvolucionUci.goTablaComplicacionesUci.bootstrapTable('getData');
		if(TablaComplica != ''){
			$.each(TablaComplica, function(lcKey, loTipo) {
				  llSeleccion = (toDatosObjeto.ListadoComplicaciones[lcKey]==1?true:false);;
				  loTipo.SELECCION = llSeleccion;
				  oProcedimientoEvolucionUci.goTablaComplicacionesUci.bootstrapTable('updateRow', {
					index: lcKey,
					row: {
						SELECCION: llSeleccion
					}
				 });
			});
		};
	},
};


