var oModalObservacionesCups = {
	lcTitulo: 'Observación Procedimientos',
	gcUrlAjax: 'vista-comun/ajax/modalJustificacionPos.php',
	gcObservaciones:'',
	gcEspecialidadObservaciones:'',
	aPaqueteObservaciones:'',
	gaDatosObervaciones:'',
	fnEjecutar: false,

	inicializar: function()
	{
		$('#btnSalirObsCups').on('click', oModalObservacionesCups.validarObservaciones);
		$('#btnCancelarObsCups').on('click', oModalObservacionesCups.cancelarObservaciones);
		oProcedimientosOrdMedica.activaCupsUrgencias('');
	},
	
	mostrar: function()
	{
		$("#divObservacionCups").modal('show');
	},
	
	iniciaObservaciones: function()
	{
		var lcObservaciones='';
		oProcedimientosOrdMedica.gcEsCupsUrgencias='';
		var lcCodigoCups=oModalAyudaProcedimientos.aCupsObservacion.CODIGO;
		var lcDescripcionCups=oModalAyudaProcedimientos.aCupsObservacion.DESCRIPCION;
		
		if (oModalObservacionesCups.gcObservaciones.trim()!=''){
			lcObservaciones = oModalObservacionesCups.gcObservaciones
		}else{
			if (oModalAyudaProcedimientos.aCupsObservacion.ESPECIALIDAD!=oProcedimientosOrdMedica.gcEspecialidadSinObserva && oModalAyudaProcedimientos.aCupsObservacion.REFERENCIA1==='DIAG'){
				lcObservaciones=oProcedimientosOrdMedica.gcPlanManejo;
			}
		}	
		
		$("#inpCantidadCupsObs").val(1);
		$("#inpFrecuenciaCupsObs").val('');
		oProcedimientosOrdMedica.consultarCupsUrgencias(lcCodigoCups);
		oModalObservacionesCups.activaPortatil(oModalAyudaProcedimientos.aCupsObservacion.ESPECIALIDAD);
		lcDatosProcedimiento=lcCodigoCups + ' - ' + lcDescripcionCups;
		$('#txtProcedimientoObs').val(lcDatosProcedimiento);
		$("#txtInformacionClinicaObs").val(lcObservaciones);
		oModalObservacionesCups.mostrar();
	},
	
	activaPortatil: function(tcEspecialidad) {
		$("#chkPortatilCupsObs").css("display","none");
		$("#lblPortatilCupsObs").css("display","none");
		
		if (tcEspecialidad==oProcedimientosOrdMedica.gcEspecialidadImagenes){
			$("#chkPortatilCupsObs").css("display","block");
			$("#lblPortatilCupsObs").css("display","block");
		}
	},	
	
	cancelarObservaciones: function () {
		oModalObservacionesCups.ocultar();
	},	
	
	validarObservaciones: function () {
		oModalObservacionesCups.aPaqueteObservaciones=oModalObservacionesCups.gcObservaciones=oModalObservacionesCups.gaDatosObervaciones='';
		oModalObservacionesCups.gcEspecialidadObservaciones='';
		var lncantidadCups=0;
		var lnCantidad=$("#inpCantidadCupsObs").val();
		var lnFrecuencia=$("#inpFrecuenciaCupsObs").val();
		var lcObservaciones=$('#txtInformacionClinicaObs').val();
		var lcServicioUrgencias=$('#selServicioRealizaObs').val();
		var lcEspecialidadUsuario=aAuditoria.cEspUsuario;
		
		if($("#chkPortatilCupsObs").prop('checked')){
			lcObservaciones=oProcedimientosOrdMedica.gcTextoPortatil+' '+lcObservaciones;
		}
		oModalObservacionesCups.gcObservaciones=lcObservaciones;
		
		if (lcServicioUrgencias=='' && oProcedimientosOrdMedica.gcEsCupsUrgencias!=''){
			fnAlert('Servicio que realiza obligatorio, revise por favor.', '', false, false, false);
			return false;
		}
			
		if (lcObservaciones==''){
			$('#txtInformacionClinicaObs').focus();
			fnAlert('Observaciones obligatoria, revise por favor.');
			return false;
		}
		
		if (lnCantidad>0 && lnCantidad>oProcedimientosOrdMedica.gcCantidadMaxima){
			lcTexto = 'Cantidad a Solicitar no puede ser mayor a ' + oProcedimientosOrdMedica.gcCantidadMaxima + ' procedimientos, revise por favor.'
			fnAlert(lcTexto);
			return false;
		}
		
		if (lnFrecuencia>0){
			lnFrecuenciaTotal = lnFrecuencia<6 ? 6 : lnFrecuencia;
			lncantidadCups = Math.round(oProcedimientosOrdMedica.gcFrecuenciaMaxima/lnFrecuenciaTotal);
		}else{
			lncantidadCups = lnCantidad;
		}
		
		if (lncantidadCups=='' || parseInt(lncantidadCups)<=0){
			$('#inpCantidadCupsObs').focus();
			fnAlert('Cantidad obligatoria, revise por favor.');
			return false;
		}
		oModalObservacionesCups.gcEspecialidadObservaciones=(lcServicioUrgencias!='' && lcServicioUrgencias!='2') ? lcEspecialidadUsuario : oModalAyudaProcedimientos.aCupsObservacion.ESPECIALIDAD;
		oModalObservacionesCups.gaDatosObervaciones=({CANTIDAD: lncantidadCups, ESPECIALIDAD: oModalObservacionesCups.gcEspecialidadObservaciones, OBSERVACIONES: lcObservaciones});
		oModalAyudaProcedimientos.salirObservaciones(oModalObservacionesCups.gaDatosObervaciones);
		oModalAyudaProcedimientos.validarJustificacion();
	},

	registrarPaquete: function () {
		var aDatos=oModalObservacionesCups.aPaqueteObservaciones;
		
		for(let i = 0; i < aDatos.length; i = i + 1 ) {  
			aEnviarDatos = { CANTIDADCUPS: 1, CODIGO: aDatos[i].CODIGO, DESCRIPCION: aDatos[i].DESCRIPCION, 
							POSNOPOS: aDatos[i].POSNOPOS, POSNOTEXTO: aDatos[i].POSNOPOS=='NOPB' ? 'NOPOS' : 'POS',
							ESPECIALIDAD: aDatos[i].ESPECIALIDAD, AGFA: aDatos[i].AGFA, OBSERVACIONES: oModalObservacionesCups.gcObservaciones,
							PORTATIL: 0, SERVURGENCIAS: '', TIPOHEMOCOMPONENTE: aDatos[i].TIPOHEMOCOMPONENTE};
			
			oProcedimientosOrdMedica.alistarRegistro(aDatos[i].CODIGO,aEnviarDatos);
		}		
	},	

	ocultar: function () {
		$("#divObservacionCups").modal('hide');
	}	
	
}