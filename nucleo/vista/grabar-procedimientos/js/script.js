var gcUrlajax = "vista-grabar-procedimientos/ajax/ajax",
	gaTituloModulo='Grabar procedimientos',
	gnNumeroIngreso=0, gnConsecutivoOrden=0, gnLabIndependiente=200, 
	gcTiposMedicos='', gcCentroCostoGasesArteriales='', gcEspecialidadGasesArteriales='', gcEspecialidadLaboratorio='', gcHabitacionPaciente='',
	gaDatosProcedimientos=[], gaConsultaProcedimiento=[], gaReferencia1NoPermitida=[], gaReferencia3NoPermitida=[], gaTipoConsumoCausaExterna=[],
	glGuardado=false, 
	gotableGrabarProcedimientos = $('#tblGrabarProcedimientos'), 
	gotableCentrosCostos = $('#tblCentrosCostos');

$(function() {
	iniciarTablaGrabarprocedimientos();
	iniciarTablaCentrosDeCostos();
	cargarParametrosIniciales();
	$('#selMedicoRealizaCups').change(obtenerEspecialidadesMedico);
	$('#txtNumeroIngreso').on('change',function(){ 	buscarPaciente(); });
	$('#btnAdicionarGrabarCups').on('click', validarAdicionarProcedimiento);
	$('#btnSalirProcedimientos').on('click', retornarPagina);
	$('#btnLimpiarProcedimientos').on('click', limpiarPagina);
	$('#btnGuardarProcedimientos').on('click', validarEnviar);
	$('#btnCancelarCentroCosto').on('click', cancelarCentroCosto);
	$('#btnAceptarCentroCosto').on('click', validarCentroCosto);

	$('#frmGrabarProcedimiento').validate({
		rules: {
			selCentroServicioCups: "required",
			cantidadProcedimiento: "required",
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
	$("#txtNumeroIngreso").focus();
});

function cancelarCentroCosto() {
	fnConfirm('Desea cancelar el proceso de grabar el procedimiento?', gaTituloModulo, false, false, 'medium',
	{
		text: 'Aceptar',
		action: function(){
			$("#divCentrosDeCosto").modal('hide');
		}
	},
	{
		text: 'Cancelar',
			action: function(){
				$('#divCentrosDeCosto').modal('show');
			}
	}
	)
}

function validarCentroCosto() {
	let laDatosCentroDeCosto=$('#tblCentrosCostos').bootstrapTable('getData');
	if (laDatosCentroDeCosto.length>0){
		let cantidadCentrosDeCosto=laDatosCentroDeCosto.length;
		let lcCentroDeCosto='';

		for (var lnValorInicial = 1; lnValorInicial <= cantidadCentrosDeCosto; lnValorInicial++) {
			let laSeleccion = laDatosCentroDeCosto[lnValorInicial-1];
			if (laSeleccion.SELECCION==true){
				lcCentroDeCosto=laSeleccion.CENTROCOSTO;
				break;
			}
		}	
		if (lcCentroDeCosto==''){
			$('#divCentrosDeCosto').modal('show');
			fnAlert('Debe selecciona un centro de costo, verifique por favor.', gaTituloModulo, false, false, false);	
		}else{
			$("#divCentrosDeCosto").modal('hide');
			gotableCentrosCostos.bootstrapTable('removeAll');
			$('#txtProcedimientoCentroCosto').text('');
			adicionarProcedimiento(gaConsultaProcedimiento,lcCentroDeCosto);
		}
	}else{
		fnAlert('No existen centro de costo asociados.', gaTituloModulo, false, 'blue', false);
	}
}

function validarEnviar(e){
	e.preventDefault();
	if (validarGrabarProcedimientos()) {
		$('#btnGuardarProcedimientos').attr("disabled", true);

		fnConfirm('Si guarda los datos <b>NO</b> podrá modificarlos después.<br><b>¿Está seguro que desea Guardar los datos?</b>', gaTituloModulo, false, false, 'medium',
				{
					text: 'Si',
					action: function(){
						enviarDatosProcedimientos();
					}
				},
				{
					text: 'No',
					action: function(){
						$('#btnGuardarProcedimientos').attr("disabled", false);
					}
				}
			)
	}
}

function obtenerDatosEnviar() {
	let loEnviar = {
		'numeroingreso': $("#txtNumeroIngreso").val(),
		'identificacion': $("#txtIdentificacioPaciente").val(),
		'nombrepaciente': $("#txtNombrePaciente").val(),
		'habitacionpaciente': gcHabitacionPaciente,
		'usuarioingresa': aAuditoria.cUsuario,
	};
	loEnviar['procedimientos'] = $('#tblGrabarProcedimientos').bootstrapTable('getData');
	return loEnviar;
}

function enviarDatosProcedimientos(){
	let loDatosEnviar = obtenerDatosEnviar();

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: 'Verificar', datos: loDatosEnviar},
		dataType: "json"
	})
	.done(function(oDatosRecibe) {
		if(oDatosRecibe['Valido']){
			glGuardado=true;
			if (oDatosRecibe['Consecutivo']>0){
				lcTextoMensaje='Consecutivo de orden número: ' + oDatosRecibe['Consecutivo']
				fnAlert(lcTextoMensaje, gaTituloModulo, false, 'blue', 'medium', function(){
					
					fnConfirm('Desea imprimir el vale?', gaTituloModulo, false, false, false,
					{
						text: 'Si',
						action: function(){
							verPdfGrabar();
						}
					},
					{ text: 'No',
						action: function(){
							fnAlert('Grabación efectuada satisfactoriamente.', gaTituloModulo, false, 'blue', 'medium');
							limpiarPagina();
						}
					});
				});
			}else{
				fnConfirm('Desea imprimir el vale?', gaTituloModulo, false, false, false,
				{
					text: 'Si',
					action: function(){
						verPdfGrabar();
					}
				},
				{ text: 'No',
					action: function(){
						fnAlert('Grabación efectuada satisfactoriamente.', gaTituloModulo, false, 'blue', 'medium');
						limpiarPagina();
					}
				});
			}
		} else {
			fnAlert(oDatosRecibe['Mensaje'], gaTituloModulo);
			$('#btnGuardarProcedimientos').attr("disabled", false);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Ocurrió un error al guardar la grabación de los procedimientos', gaTituloModulo);
		$('#btnGuardarProcedimientos').attr("disabled", false);
	});
}

function verPdfGrabar(){
	let loDatosEnviar = obtenerDatosEnviar();
	var loData={accion:'imprimir', datosimprimir: JSON.stringify(loDatosEnviar)}; 
	formPostTemp(gcUrlajax, loData, true);
	limpiarPagina();
}

function validarGrabarProcedimientos(){
	let lnNumeroIngreso=$("#txtNumeroIngreso").val();

	if (gnNumeroIngreso!=lnNumeroIngreso){
		fnAlert('Número de ingreso es diferente a registrado inicialmente, revise por favor.', gaTituloModulo, false, 'blue', 'medium');
		return false;
	}

	let laDatosProcedimientos=$('#tblGrabarProcedimientos').bootstrapTable('getData');
	if (laDatosProcedimientos.length==0){
		fnAlert('No existen procedimientos para grabar, revise por favor.', gaTituloModulo, false, 'blue', 'medium');
		return false;
	}
	return true;
}

function cargarParametrosIniciales(){
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: 'cargarparametrosiniciales'},
		dataType: "json",
	})
	.done(function( loTipos ) {
		try {
			if (loTipos.error == ''){
				gcTiposMedicos=loTipos.DATOS.especialidadmedicos;
				gcCentroCostoGasesArteriales=loTipos.DATOS.centrocostogasesarteriales;
				gcEspecialidadGasesArteriales=loTipos.DATOS.especialidadgasesarteriales;
				gcEspecialidadLaboratorio=loTipos.DATOS.especialidadlaboratorio;
				gaReferencia1NoPermitida=loTipos.DATOS.referencia1nopermitida.split(",");
				gaReferencia3NoPermitida=loTipos.DATOS.referencia3nopermitida.split(",");
				gaTipoConsumoCausaExterna=loTipos.DATOS.tipoconsumocausaexterna.split(",");
				iniciarListas();
			} else {
				fnAlert(loTipos.error + ' ', "warning");
			}
		} catch(err) {
			fnAlert('No se pudo realizar la busqueda de cargar parametros iniciales.', "danger");
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al buscar cargar parametros iniciales.', "danger");
	});
}

function activarBuscarAutocompletar(tcGeneroPaciente,taEdadPaciente){
	aDatosIngreso.cSexo=tcGeneroPaciente;
	aDatosIngreso.aEdad=taEdadPaciente;
	oProcedimientos.consultaProcedimientos('cProcedimientoBuscar','codigoProcedimiento','descripcionProcedimiento','cantidadProcedimiento','GP');
	oDiagnosticos.consultarDiagnostico('txtDiagnosticoPaciente','cCodigoDiagnosticoPaciente','cDescripcionDiagnosticoPaciente','','selTipoDiagnosticoCups');
}

function datosProcedimientosGrabacion(){
	if (oProcedimientos.gcTipoPaquete=='PAQLAB'){
		$("#cantidadProcedimiento").val(1);
		$('#cantidadProcedimiento').attr("disabled", true);
	}	

	if (($.inArray(oProcedimientos.gcCupsTipoRips, gaTipoConsumoCausaExterna)>=0) || oProcedimientos.gcTipoPaquete=='PAQLAB') {
	}else{
		$('#lblCausaExternaCups,#lblTipoDiagnosticoCups').removeClass("required");
		$("#selCausaExternaCups,#selTipoDiagnosticoCups").attr('required', false);
	}

}

function buscarPaciente(){
	let lnNumeroIngreso = $("#txtNumeroIngreso").val();
	gnNumeroIngreso=lnNumeroIngreso;
	let lnCantidadcaracteres = lnNumeroIngreso.length;
	
	if (lnNumeroIngreso<=0) {
		$("#txtNumeroIngreso").focus();
		fnAlert("Número ingreso obligatorio");
		return;
	}

	if (lnCantidadcaracteres>8) {
		$("#txtNumeroIngreso").focus();
		fnAlert("Número de ingreso no existe, revise por favor.");
		return;
	}	

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: { accion: 'consultaringreso', numeroingreso: lnNumeroIngreso},
		dataType: "json",
	})
	.done(function( loTipos ) {
		try {
			if (loTipos.error == ''){
				let lcEstadoIngreso=loTipos.DATOS.estadoingreso;
				let lnNumeroIdentificacion=loTipos.DATOS.numeroidentificacion;
				if (lnNumeroIdentificacion>0){
					if (lcEstadoIngreso=='2'){
						let lcIdentificacion=loTipos.DATOS.tipoidentificacion+'-'+lnNumeroIdentificacion;
						$("#txtNombrePaciente").val(loTipos.DATOS.nombrepaciente);
						$("#txtIdentificacioPaciente").val(lcIdentificacion);
						$("#txtGeneroPaciente").val(loTipos.DATOS.descripciongeneropaciente);
						$("#txtViaIngreso").val(loTipos.DATOS.descripcionviaingreso);
						$("#selCentroServicioCups").val(loTipos.DATOS.centrodeservicio);
						gcHabitacionPaciente=loTipos.DATOS.habitacionpaciente;
						activarBuscarAutocompletar(loTipos.DATOS.generopaciente,loTipos.DATOS.edadpaciente);
						consultarDiagnosticoPrincipal(lnNumeroIngreso);
						habilitarCampos(false);
						limpiarDatos(false);
					}else{
						let lcTextoMensaje = 'No se pueden cargar consumos, ingreso con estado '+loTipos.DATOS.descripcionestadoingreso+', revise por favor.';
						fnAlert(lcTextoMensaje, gaTituloModulo, 'fas fa-exclamation-circle','blue','medium');
					}
				}else{
					fnAlert('Número de ingreso no encontrado, revise por favor.', gaTituloModulo, 'fas fa-exclamation-circle','blue','medium');
				} 
			} else {
				fnAlert(loTipos.error + ' ', "warning");
			}
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la busqueda de buscar pacientes.', "danger");
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al buscar buscar pacientes.', "danger");
	});
}

function consultarDiagnosticoPrincipal(tnNumeroIngreso){
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: 'obtenerDiagnosticoPrincipal', lnNumeroIngreso: tnNumeroIngreso},
		dataType: "json",
	})
	.done(function( loTipos ) {
		try {
			if (loTipos.error == ''){
				$("#cCodigoDiagnosticoPaciente").val(loTipos.DATOS.CODIGO);
				$("#cDescripcionDiagnosticoPaciente").val(loTipos.DATOS.DESCRIPCIONCIE);
			} else {
				fnAlert(loTipos.error + ' ', "warning");
			}
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la busqueda consultar diagnóstico principal.', "danger");
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al buscar consultar diagnóstico principal.', "danger");
	});
}

function habilitarCampos(tlHabilitar){
	$('#txtNumeroIngreso').attr('disabled',!tlHabilitar);
	$('#cProcedimientoBuscar,#cantidadProcedimiento,#selCentroServicioCups,#selCausaExternaCups,#selFinalidadCups,#txtDiagnosticoPaciente').attr('disabled',tlHabilitar);
	$('#selTipoDiagnosticoCups,#selMedicoRealizaCups,#txtInformacionClinica,#btnAdicionarGrabarCups').attr('disabled',tlHabilitar);
	$('#cProcedimientoBuscar').focus();
	if (tlHabilitar){ $('#btnGuardarProcedimientos').attr("disabled", false); }
}

function limpiarDatos(tlHabilitar)
{
	$('#cProcedimientoBuscar,#cantidadProcedimiento,#selCausaExternaCups,#selFinalidadCups,#txtDiagnosticoPaciente').val("");
	$('#codigoProcedimiento,#descripcionProcedimiento,#cCodigoDiagnosticoPaciente,#cDescripcionDiagnosticoPaciente').val("");
	$('#selTipoDiagnosticoCups,#selMedicoRealizaCups,#txtInformacionClinica').val("");

	if (tlHabilitar){
		$('#txtNumeroIngreso,#txtIdentificacioPaciente,#txtNombrePaciente,#txtNombrePaciente,#txtViaIngreso,#txtGeneroPaciente').val("");
		$('#selCentroServicioCups,#cCodigoDiagnosticoPaciente,#cDescripcionDiagnosticoPaciente').val("");
		$('#txtNumeroIngreso,#selCentroServicioCups,#selCausaExternaCups,#selTipoDiagnosticoCups,#cCodigoDiagnosticoPaciente,#cDescripcionDiagnosticoPaciente').removeClass("is-valid");
		$('#selMedicoRealizaCups,#selEspecialidadRealizaCups,#txtInformacionClinica,#selFinalidadCups,#cantidadProcedimiento').removeClass("is-valid");
		inicializarEspecialidadMedico();
		gotableGrabarProcedimientos.bootstrapTable('removeAll');
		$('#txtNumeroIngreso').focus();
	}	
}

function iniciarListas(){
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: 'consultarListados', tiposmedicos: gcTiposMedicos},
		dataType: "json",
	})
	.done(function( loTipos ) {
		try {
			if (loTipos.error == ''){
				loSelect = $("#selCentroServicioCups");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loTipos.centrosdeservicio, function( lcKey, loTipo ) {
					var lcOption = '<option id-centroservicio="' + loTipo.ID + '" value="' + loTipo.CODIGO + '">' + loTipo.NOMBRE + '</option>';
					loSelect.append(lcOption);
				});

				loSelect = $("#selCausaExternaCups");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loTipos.causasexternas, function( lcKey, loTipo ) {
					var lcOption = '<option value="' + loTipo.codigo + '">' + loTipo.desc + '</option>';
					loSelect.append(lcOption);
				});
				
				loSelect = $("#selFinalidadCups");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loTipos.finalidades, function( lcKey, loTipo ) {
					var lcOption = '<option value="' + lcKey + '">' + loTipo.desc + '</option>';
					loSelect.append(lcOption);
				});

				loSelect = $("#selTipoDiagnosticoCups");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loTipos.tipodiagnostico, function( lcKey, loTipo ) {
					var lcOption = '<option value="' + loTipo.TABCOD + '">' + loTipo.TABDSC + '</option>';
					loSelect.append(lcOption);
				});
				
				loSelect = $("#selMedicoRealizaCups");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loTipos.listadomedicos, function( lcKey, loTipo ) {
					var lcOption = '<option value="' + loTipo.REGISTRO + '">' + loTipo.MEDICO + '</option>';
					loSelect.append(lcOption);
				});

			} else {
				fnAlert(loTipos.error + ' ', "warning");
			}
		} catch(err) {
			fnAlert('No se pudo realizar la busqueda de iniciar listados.', "danger");
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al buscar iniciar listados.', "danger");
	});
}

function obtenerEspecialidadesMedico(){
	loSelect = $("#selEspecialidadRealizaCups");
	loSelect.empty();
	loSelect.append('<option value=""></option>');
	loSelect.attr('disabled',true);
	let lcRegistroMedico=$("#selMedicoRealizaCups").val();

	if (lcRegistroMedico=='') {
		fnAlert("Debe seleccionar un profesional.");
		return;
	}

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: 'consultarEspecialidades', tiposmedicos: gcTiposMedicos, registromedico: lcRegistroMedico},
		dataType: "json",
	})
	.done(function( loTipos ) {
		try {
			if (loTipos.error == ''){
				if (loTipos.DATOS!=''){
					$.each(loTipos.DATOS, function( lcKey, loTipo ) {
						var lcOption = '<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>';
						loSelect.append(lcOption);
					});
					$("#selEspecialidadRealizaCups").attr('disabled',false);
				}	
			} else {
				fnAlert(loTipos.error + ' ', "warning");
			}
		} catch(err) {
			fnAlert('No se pudo realizar la busqueda de obtener especialidades médico.', "danger");
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al buscar obtener especialidades médico.', "danger");
	});
}

function validarAdicionarProcedimiento(){
	gaDatosProcedimientos=laListaCups=[];
	let lcDescripcionEspecialidadMedico='';

	if ($('#frmGrabarProcedimiento').valid()){
		let lcCodigoCups=$("#codigoProcedimiento").val();
		let lcCentroServicio=$("#selCentroServicioCups").val();
		let lcCausaExterna=$("#selCausaExternaCups").val();
		let lcFinalidadCups=$("#selFinalidadCups").val();
		let lcCodigoDiagnostico=$("#cCodigoDiagnosticoPaciente").val();
		let lcTipoDiagnostico=$("#selTipoDiagnosticoCups").val();
		let lcMedicoRealiza=$("#selMedicoRealizaCups").val();
		let lcEspecialidadMedico=$("#selEspecialidadRealizaCups").val();
		let lcInformacionClinica=$("#txtInformacionClinica").val();
		let lnCantidadProcedimiento=$("#cantidadProcedimiento").val();
		
		if (lcCodigoCups==''){
			fnAlert('Falta seleccionar el procedimiento, verifique por favor.', gaTituloModulo, false, false, false)
			return false;
		}
		if (lnCantidadProcedimiento=='' || lnCantidadProcedimiento==0){
			fnAlert('Falta digitar cantidad del procedimiento, verifique por favor.', gaTituloModulo, false, false, false)
			return false;
		}
		if (lcCentroServicio==''){
			fnAlert('Debe seleccionar el centro de servicio, verifique por favor.', gaTituloModulo, false, false, false)
			return false;
		}

		if (($.inArray(oProcedimientos.gcCupsTipoRips, gaTipoConsumoCausaExterna)>=0) || oProcedimientos.gcTipoPaquete=='PAQLAB') {		
			if (lcCausaExterna==''){
				fnAlert('Debe seleccionar una causa externa, verifique por favor.', gaTituloModulo, false, false, false)
				return false;
			}
			
			if (lcTipoDiagnostico==''){
				fnAlert('Debe seleccionar un tipo de diagnóstico del procedimiento, verifique por favor.', gaTituloModulo, false, false, false)
				return false;
			}
		}	

		if (lcCodigoDiagnostico==''){
			fnAlert('Debe seleccionar un diagnóstico del procedimiento, verifique por favor.', gaTituloModulo, false, false, false)
			return false;
		}

		if (lcMedicoRealiza==''){
			fnAlert('Debe seleccionar un mpedico que realiza el procedimiento, verifique por favor.', gaTituloModulo, false, false, false)
			return false;
		}
		if (lcEspecialidadMedico==''){
			fnAlert('Debe seleccionar un mpedico que realiza el procedimiento, verifique por favor.', gaTituloModulo, false, false, false)
			return false;
		}else{
			lcDescripcionEspecialidadMedico=$("#selEspecialidadRealizaCups option[value="+lcEspecialidadMedico+"]").text();
		}

		if ($.inArray(oProcedimientos.gcCupsReferencia1, gaReferencia1NoPermitida)>=0 || $.inArray(oProcedimientos.gcCupsReferencia3, gaReferencia3NoPermitida)>=0){
			fnAlert('Este procedimiento debe ser grabado por salas, revise por favor.', gaTituloModulo, false, false, false)
			return false;
		}
 
		llverificaExisteCups = verificarCodigoExiste(lcCodigoCups);
		if(llverificaExisteCups){
			gaDatosProcedimientos = {procedimiento: lcCodigoCups, cantidadprocedimiento: lnCantidadProcedimiento, centroservicio: lcCentroServicio, 
				causaexterna: lcCausaExterna, finalidadprocedimiento: lcFinalidadCups, codigodiagnostico: lcCodigoDiagnostico,
				tipodiagnostico: lcTipoDiagnostico, medicorealiza: lcMedicoRealiza,  especialidadmedico: lcEspecialidadMedico, 
				descripcionespecialidadmedico: lcDescripcionEspecialidadMedico, informacionclinica: lcInformacionClinica};
	
			laListaCups.push({CODIGO: lcCodigoCups, CENTROSERVICIO: lcCentroServicio, MEDICOREALIZA: lcMedicoRealiza,  });
			registrarProcedimientos(laListaCups);
		}else{
			fnAlert('Procedimiento ya ingresado, verifique por favor.', gaTituloModulo, false, 'blue', false);
		}
	}
}	

function verificarCodigoExiste(tcCodigo){	
	let taTablaValida=gotableGrabarProcedimientos.bootstrapTable('getData');
	var llRetorno = true ;
		if(taTablaValida != ''){
			$.each(taTablaValida, function( lcKey, loTipo ) {
				if(loTipo['CUPS']==tcCodigo){
					llRetorno = false;
				}
			});
		};
	return llRetorno ;
}

function contarProcedimientos(){	

	let taTablaValida=gotableGrabarProcedimientos.bootstrapTable('getData');
	let lnCantidadCups=taTablaValida.length;
	let lnCantidadLaboratorios=0

	$.each(taTablaValida, function(lcKey, loSeleccion) {
		if (loSeleccion.ESPECIALIDADPROCEDIMIENTO==gcEspecialidadLaboratorio){
			lnCantidadLaboratorios++;
		}
	});
	let lcTextoCantidadCups = "Cantidad procedimientos/laboratorios: " + lnCantidadCups +'/'+lnCantidadLaboratorios;
	$('#txtCantidadProcedimientos').text(lcTextoCantidadCups);
}

function registrarProcedimientos(taProcedimiento){
	$.ajax({
		type: "POST",
		url: "vista-comun/ajax/listaPaquetes.php",
		data: {lcPaquete: taProcedimiento, lcGenero: aDatosIngreso['cSexo'], lnEdadaños: parseInt(aDatosIngreso.aEdad.y)},
		dataType: "json",
	})
	.done(function( loTipos ) {
		try {
			if (oProcedimientos.gcTipoPaquete=='PAQLAB'){
				$.each(loTipos.datos, function(lcKey, loSeleccion) {
					adicionarProcedimiento(loSeleccion,loSeleccion.CENTROCOSTO);	
				});
			}else{
				if (loTipos.datos[0]['CENTROCOSTO'].length>1){
					$.each(loTipos.datos, function(lcKey, loSeleccion) {
						gaConsultaProcedimiento=loSeleccion;
						mostrarCentrosDeCostos(loTipos.datos[0]['CENTROCOSTO']);
					});
				}else{
					let lcCentroDeCosto='';
					$.each(loTipos.datos, function(lcKey, loSeleccion) {
						let laCentroCosto=loSeleccion.CENTROCOSTO;
						if (laCentroCosto.length>0){
							lcCentroDeCosto=laCentroCosto[0]['CENTROCOSTO'];
						}
						adicionarProcedimiento(loSeleccion,lcCentroDeCosto);
					});
				}
			}
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la busqueda registrar procedimientos.', "danger");
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al buscar registrar procedimientos.', "danger");
	});
}

function mostrarCentrosDeCostos(taCentrosDeCosto) {
	gotableCentrosCostos.bootstrapTable('removeAll');
	$('#txtProcedimientoCentroCosto').text($("#codigoProcedimiento").val()+ ' - ' + $("#descripcionProcedimiento").val());

	$.each(taCentrosDeCosto, function(lcKey, loSeleccion) {
		let rows = []
			rows.push({
			SELECCION: false,	
			REGISTROMEDICO: loSeleccion.MEDICO,
			NOMBREMEDICO: loSeleccion.NOMBREMEDICO,
			CENTROCOSTO: loSeleccion.CENTROCOSTO,
			DESCRIPCIONCENTROCOSTO: loSeleccion.DESCRIPCIONCENTROCOSTO,
			CENTROSERVICIO: gaDatosProcedimientos.centroservicio,
			DESCRIPCIONCENTROSERVICIO: $("#selCentroServicioCups option[value="+gaDatosProcedimientos.centroservicio+"]").text(),
		})
		gotableCentrosCostos.bootstrapTable('append', rows);
	});
	$('#divCentrosDeCosto').modal('show');
}

function adicionarProcedimiento(taDatosProcedimiento,tcCentroDeCosto){

	let lnCantidadProcedimiento=gaDatosProcedimientos.cantidadprocedimiento;
	let lcCodigoProcedimiento=taDatosProcedimiento.CODIGO;
	let lcDescripcionProcedimiento=taDatosProcedimiento.DESCRIPCION;
	let lcLaboratorioEspecial=taDatosProcedimiento.LABESPEC;
	let lcPosNopos=taDatosProcedimiento.POSNOPOS==='NOPB'?'N':(taDatosProcedimiento.POSNOPOS==='PB'?'P':'');
	let lcCentroDeCosto=tcCentroDeCosto===undefined ? '' : tcCentroDeCosto;
	let lcEspecialidadprocedimiento=(lcCentroDeCosto==gcCentroCostoGasesArteriales) ? gcEspecialidadGasesArteriales : taDatosProcedimiento.ESPECIALIDAD;

	for (lnInicio=1; lnInicio<=lnCantidadProcedimiento; lnInicio++){
		$lcDescripcionCausaExterna=$lcDescripcionTipoDiagnostico='';
		gnConsecutivoOrden++;
		gnLabIndependiente=lcLaboratorioEspecial!='' ? gnLabIndependiente+1: gnLabIndependiente;
		lnCantidadLinea=lcLaboratorioEspecial==='' ? lnInicio : gnLabIndependiente;
		$lcDescripcionCausaExterna=gaDatosProcedimientos.causaexterna!=''?$("#selCausaExternaCups option[value="+gaDatosProcedimientos.causaexterna+"]").text():'';
		$lcDescripcionTipoDiagnostico=gaDatosProcedimientos.tipodiagnostico!=''?$("#selTipoDiagnosticoCups option[value="+gaDatosProcedimientos.tipodiagnostico+"]").text():'';

		var rows = []
			rows.push({
			IDCUPS:gnConsecutivoOrden,
			LINEA: lnCantidadLinea,
			CUPS: lcCodigoProcedimiento,
			DESCRIPCIONCUPS: lcDescripcionProcedimiento,
			CENTROSERVICIO: gaDatosProcedimientos.centroservicio,
			DESCRIPCIONCENTROSERVICIO: $("#selCentroServicioCups option[value="+gaDatosProcedimientos.centroservicio+"]").text(),
			CAUSAEXTERNA: gaDatosProcedimientos.causaexterna,
			DESCRIPCIONCAUSAEXTERNA: $lcDescripcionCausaExterna,
			FINALIDAD: gaDatosProcedimientos.finalidadprocedimiento,
			DESCRIPCIONFINALIDAD: $("#selFinalidadCups option[value="+gaDatosProcedimientos.finalidadprocedimiento+"]").text(),
			DIAGNOSTICO: gaDatosProcedimientos.codigodiagnostico,
			DESCRIPCIONDIAGNOSTICO: $("#cDescripcionDiagnosticoPaciente").val(),
			TIPODIAGNOSTICO: gaDatosProcedimientos.tipodiagnostico,
			DESCRIPCIONTIPODIAGNOSTICO: $lcDescripcionTipoDiagnostico,
			MEDICOREALIZA: gaDatosProcedimientos.medicorealiza,
			NOMBREMEDICOREALIZA: $("#selMedicoRealizaCups option[value="+gaDatosProcedimientos.medicorealiza+"]").text(),
			ESPECIALIDADMEDICO: gaDatosProcedimientos.especialidadmedico,
			DESCRIPCIONESPECIALIDADMEDICO: gaDatosProcedimientos.descripcionespecialidadmedico,
			INFORMACIONCLINICA: gaDatosProcedimientos.informacionclinica,
			CODIGOHEXALIS: taDatosProcedimiento.HEXALIS,
			ENVIARAGFA: taDatosProcedimiento.ENVIAAGFA,
			ESPECIALIDADPROCEDIMIENTO: lcEspecialidadprocedimiento,
			POSNOPOS: lcPosNopos,
			CENTRODECOSTO: lcCentroDeCosto,
			BORRAR: '',
		})
		gotableGrabarProcedimientos.bootstrapTable('append', rows);
	}
	limpiarAdicionarProcedimiento();
	contarProcedimientos();	
}	

function iniciarTablaGrabarprocedimientos() {
	gotableGrabarProcedimientos.bootstrapTable({
		classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
		theadClasses: 'thead-dark',
		locale: 'es-ES',
		undefinedText: '',
		toolbar: '#toolBarLst',
		height: '450',
		search: false,
		pagination: false,
		columns: [
			{
				title: 'Procedimiento',
				formatter: function(tnValor, toFila){ return toFila.CUPS+'-'+toFila.DESCRIPCIONCUPS;},
				width: 20, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Causa externa',
				field: 'DESCRIPCIONCAUSAEXTERNA',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Finalidad',
				field: 'DESCRIPCIONFINALIDAD',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Diagnóstico principal',
				formatter: function(tnValor, toFila){ return toFila.DIAGNOSTICO+'-'+toFila.DESCRIPCIONDIAGNOSTICO;},
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Tipo Diagnóstico',
				field: 'DESCRIPCIONTIPODIAGNOSTICO',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Especialidad/Médico realiza',
				formatter: function(tnValor, toFila){ return toFila.DESCRIPCIONESPECIALIDADMEDICO+'-'+toFila.NOMBREMEDICOREALIZA;},
				  width: 20, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Información clínica',
				field: 'INFORMACIONCLINICA',
				  width: 45, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Acción',
				field: 'BORRAR',
				width: 5, widthUnit: "%",
				halign: 'center',
				align: 'center',
				clickToSelect: false,
				events: eventoProcedimiento,
				formatter: formatoEliminarProcedimiento
			}
		  ],
	});
}

function iniciarTablaCentrosDeCostos() {
	gotableCentrosCostos.bootstrapTable({
		classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
		theadClasses: 'thead-dark',
		locale: 'es-ES',
		undefinedText: '',
		toolbar: '#toolBarLst',
		checkboxHeader: false,
		height: '250',
		search: false,
		pagination: false,
		columns: [
			{
				title: '',
				field: 'SELECCION',
				radio: 'true',
				width: 5, widthUnit: "%",
				halign: 'center',
				align: 'center'
			},
			{
				title: 'Médico',
				field: 'NOMBREMEDICO',
				width: 30, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Centro de Costo',
				field: 'DESCRIPCIONCENTROCOSTO',
				width: 40, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Centro de Servicio',
				field: 'DESCRIPCIONCENTROSERVICIO',
				width: 40, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
		  ],
	});
}

function limpiarPagina(){
	habilitarCampos(true);
	limpiarDatos(true);
	contarProcedimientos();	
	resetearVariablesGlobales();
	$("#txtNumeroIngreso").focus();
}

function resetearVariablesGlobales(){
	gcHabitacionPaciente='';
	gnNumeroIngreso=gnConsecutivoOrden=0;
}

function retornarPagina(){
	if (!glGuardado) {
		window.history.back();
		return;
	}
	fnConfirm('¡Perderá los datos registrados!<br><b>¿Está seguro que desea volver a la pagina anterior?</b>', gaTituloModulo, false, false, 'medium',
		{
			text: 'Si',
			action: function(){
				window.history.back();
			}
		},
		{ text: 'No' }
	)
}

function limpiarAdicionarProcedimiento(){
	$('#cProcedimientoBuscar,#codigoProcedimiento,#descripcionProcedimiento,#cantidadProcedimiento,#txtInformacionClinica').val("");
	$('#cantidadProcedimiento,#txtInformacionClinica').removeClass("is-valid");
	//inicializarEspecialidadMedico();
	$('#btnGuardarProcedimientos').attr("disabled", false);
	$('#lblCausaExternaCups,#lblTipoDiagnosticoCups').addClass("required");
	$("#selCausaExternaCups,#selTipoDiagnosticoCups").attr('required', true);
	glGuardado=true;
	gaConsultaProcedimiento=[];
	$('#cProcedimientoBuscar').focus();
}

function inicializarEspecialidadMedico(){
	$("#selEspecialidadRealizaCups").empty();
	$("#selEspecialidadRealizaCups").append('<option value=""></option>');
	$("#selEspecialidadRealizaCups").attr('disabled',true);
}

function formatoEliminarProcedimiento(tnValor, toFila) {
	return [
		'<a class="eliminaProcedimiento" href="javascript:void(0)" title="Eliminar">',
		'<i class="fa fa-trash" style="color:#E96B50"></i>',
		'</a>'
	  ].join('');
}

var eventoProcedimiento = {
	'click .eliminaProcedimiento': function (e, value, row, index) {
		let lcTextoMensaje= '¿Desea eliminar el procedimiento ' +  row.DESCRIPCIONCUPS + '?.';

		fnConfirm(lcTextoMensaje, false, false, false, 'medium', function(){
			gotableGrabarProcedimientos.bootstrapTable('remove', {
			field: 'IDCUPS',
			values: [row.IDCUPS]
			});
			verificarProcedimientosRegistrados();
			contarProcedimientos();
			$('#cProcedimientoBuscar').focus();
		},'');
	}
}

function verificarProcedimientosRegistrados() {
	if ($('#tblGrabarProcedimientos').bootstrapTable('getData').length==0){
		$('#btnGuardarProcedimientos').attr("disabled", true);
		glGuardado=false;	
	}	
}
