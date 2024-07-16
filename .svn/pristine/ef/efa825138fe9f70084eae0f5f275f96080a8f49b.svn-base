var goTabla=$('#tblCensoPacientes'),
	goTablaAlta=$('#tblAltasTempranas'),
	goTablaSecUrgencia=$('#tblSeccionUrgencias'),
	goMobile = new MobileDetect(window.navigator.userAgent),
	gcUrlajax = "vista-censo-pacientes/ajax/ajax",
	gdFechaConsulta,
	gcTitulo='Censo pacientes',
	gcDatosRegistros=gcQuitaSalida=gcActivaCambioUbicacion='',
	gnTipoRegistro=gnMarcaVieneAlta=gnIndiceRegistroAlta=0,
	gcTituloRegistro=gcEnviarListaAltas=gcListaSecUrgencias=gcAutorizadoSalidaCenso='',
	goEnter=String.fromCharCode(13), goChr24=String.fromCharCode(24), goChr25=String.fromCharCode(25),
	goEstadosCenso={},
	gaTiporegadm=gaTiporegmed=gaTipousuadm=gaTipousumed=gaTipoExportar=gaTipoRegEnfer=gaTipoUsuEnfer='',
	gaDatosFila={},	goFila={},
	goColorIngreso = { 'S': '#ffff80', };
	gbAccesoColumnaJefeUrgencias = false;
	
$(function () {
	if (aDatosCenso=='urg'){
		$("#divTextoUrgencias").css("display","block");
		$("#divSinHabitacion").css("display","block");
		$("#divPacienteTipo").css("display","block");
		consultaUbicacionMedico();
	}
	consultaEstados();
	parametrosCenso();
	IniciarTablaSeccionUrgencias();
	IniciarTablaAltaTemprana();
	consultaUsuariosRegistros();
	consultaSecciones();
	consultarListaAltasTempranas();
	oAntecedentesConsulta.inicializar();
	IniciarOpcionesMenuOpc(aDatosCenso=='urg' ? 'urgencias' : 'hospitalizado');
	oModalObservaciones.inicializar();
	oModalTrasladoPacientes.inicializar();

	$('#selTipDoc').tiposDocumentos({horti: "1"});
	$('#divFiltro .input-group.date').datepicker({
		autoclose: true,
		clearBtn: true,
		daysOfWeekHighlighted: "0,6",
		format: "yyyy-mm-dd",
		language: "es",
		todayBtn: true,
		todayHighlight: true,
		toggleActive: true,
		weekStart: 1,
	});
	$('#btnLimpiar').on('click', limpiar);
	$('#btnBuscar').on('click', buscarPacientesCenso);
	$('#btnGuardaCensoUrgencias').on('click', validarRegistros);
	$('#btnCancelarCensoUrgencias').on('click', cancelarRegistros);
	$('#AdicionarAltaTempranaU').on('click', validarAdicionarAltaTemprana);
	$('#btnGuardaAlertaTemCenso').on('click', validarAltaTemprana);
	$('#btnSelSeccionesUrgencias').on('click', validarSeleccionUrgencias);
	$('#btnGuardarSeccionMedico').on('click', validarSeccionMedico);
	$('#btnCancelarSeccionesUrgencias').on('click', cancelarSeleccionUrgencias);
	$('#btnGuardarTipoPaciente').on('click', validarTipoPaciente);
	$('#botonAyudaSeccionesUrg').on('click', function(){ mostrarSeccionesUrg(); });
})

function mostrarSeccionesUrg() {
	$('#divSeccionesUrgenciasCensoU').modal('show');
}

function validarSeleccionUrgencias() {
	$('#txtSeccionesUrgencias').val('');
	let lnValidaLista=0;
	let lcSeleccionadasUrg='';
	gcListaSecUrgencias='';
	taListaUrgencias = $('#tblSeccionUrgencias').bootstrapTable('getData');

	traerSeccionesUrgencias(taListaUrgencias, function() {
		filtrarSeccionesUrgencias(gcListaSecUrgencias);
	});
}

function traerSeccionesUrgencias(taListaUrgencias, tfPost){
	$.each(taListaUrgencias, function( lcKey, loTipo ) {
		if (loTipo.SELECCION==true){
			gcListaSecUrgencias=gcListaSecUrgencias+loTipo.CODIGO+',';
		}
	});
	if (typeof tfPost == 'function') {
		tfPost();
	}
}

function filtrarSeccionesUrgencias(){
	$('#divSeccionesUrgenciasCensoU').modal('hide');

	if (gcListaSecUrgencias!=''){
		gcListaSecUrgencias=gcListaSecUrgencias.substr(0,gcListaSecUrgencias.trim().length- 1);
		$('#txtSeccionesUrgencias').val(gcListaSecUrgencias);
	}
	buscarPacientesCenso();
}

function cancelarSeleccionUrgencias(){
	gcListaSecUrgencias='';
	$('#txtSeccionesUrgencias').val('');
	$('#divSeccionesUrgenciasCensoU').modal('hide');

	$.each(taListaUrgencias, function( lcKey, loTipo ) {
		$('#tblSeccionUrgencias').bootstrapTable('updateRow', {
			index: lcKey,
			row: {
				SELECCION: false,
			}
		 });
	});
	buscarPacientesCenso();
}

function buscarPacientesCenso() {
	$("#btnBuscar").attr("disabled", true);
	goTabla.bootstrapTable('removeAll');
	goTabla.bootstrapTable('showLoading');
	let lcSeccionConsulta=$("#selSeccionCU").val();
	let lcSeccionMedicos=aDatosCenso=='urg' ? $("#selSeccionUrgencias").val() : '';
	let lcSinHabitacion=aDatosCenso=='urg' ? $("#selSinHabitacionUrgencias").val() : '';
	let lcGenero=$("#selGeneroUrgencias").val();
	let lcPacienteTipo=aDatosCenso=='urg' ? $("#selPacienteTipoUrgencias").val() : '';
	let lnMaximoIngreso=parseInt($('#txtIngreso').attr('max'));
	let lnNumeroIngreso=parseInt($("#txtIngreso").val());

	if (lnNumeroIngreso>lnMaximoIngreso){
		fnAlert("Número de ingreso no permitido, revise por favor.", gcTitulo, false, 'blue', 'medium');
		$("#btnBuscar").attr("disabled", false);
	}else{
		$.ajax({
			type: "POST",
			url: gcUrlajax,
			data: {
				accion:'pacientes',
				fechaini: $("#txtFechaIni").val(),
				fechafin: $("#txtFechaFin").val(),
				ingreso: $("#txtIngreso").val(),
				seccion: lcSeccionConsulta,
				tipocenso: aDatosCenso=='urg' ? 'U' : 'H',
				ubicacionmed: lcSeccionMedicos,
				sinhabitacion: lcSinHabitacion,
				generopac: lcGenero,
				pacientetipo: lcPacienteTipo
			},
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.error == '') {
					goTabla.bootstrapTable('refreshOptions', {data: loDatos.datos});
					gdFechaConsulta = new Date(loDatos.fechahora).getTime();
				} else {
					fnAlert(loDatos.error);
				}
				$("#btnBuscar").attr("disabled", false);
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta pacientes censo.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			goTabla.bootstrapTable('hideLoading');
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar pacientes censo.');
		});

	}
}

function guardarRegistros(tcRegistrosCenso) {
	let loData = {ingreso: gaDatosFila.NIGING, tiporegistro: gnTipoRegistro, datosregistro: tcRegistrosCenso, altatemprana: '', quitasalida: ''};
	let lcRegistroGuardar = 'Registro censo urgencias se ha guardado.';

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: 'Verificar', datos: loData},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				if(loDatos.datos['Valido']){
					blanquearModal();
					terminaCenso(lcRegistroGuardar);
				}else {
					fnAlert(loDatos['Mensaje'], 'CENSO URGENCIAS', false, false, 'medium');
				}
			} else {
				fnAlert(loDatos.error);
			}
			IniciarPacientes();
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la consulta guardar registros censo.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar guardar registros censo.');
	});
}

function guardarAltasTempranas(tcDatosAltasTempranas) {
	let loData = {ingreso: gaDatosFila.NIGING, tiporegistro: gnTipoRegistro, datosregistro: tcDatosAltasTempranas, altatemprana: 'S', quitasalida: ''};
	let lcRegistroGuardar = 'Registro altas tempranas se ha guardado.';

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: 'guardaAltasTempranas', datos: loData},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				if(loDatos.datos['Valido']){
					registrarBitacora();
					blanquearModalAltas();
					terminaCenso(lcRegistroGuardar);
				}else {
					fnAlert(loDatos['Mensaje'], 'CENSO URGENCIAS', false, false, 'medium');
				}
			} else {
				fnAlert(loDatos.error);
			}
			IniciarPacientes();
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la consulta guardar altas tempranas.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar guardar altas tempranas.');
	});
}

function registrarBitacora() {
	let loDatosAlta='';
	let taAltasTempranas = goTablaAlta.bootstrapTable('getData');
	$.each(taAltasTempranas, function( lcKey, loTipo ) {
		if (loTipo.NUEVA=='S'){
			loDatosAlta = {tipo: loTipo.TIPOALTA, observacion: loTipo.OBSERVACION};
			guardarBitacora(loDatosAlta);
		}
	});
}

function guardarBitacora(tcDatosAltaTemprana) {
	let loData = {ingreso: gaDatosFila.NIGING, tiporegistro: gnTipoRegistro, datosregistro: tcDatosAltaTemprana, altatemprana: '', quitasalida: '',};

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: 'guardaBitacora', datos: loData},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				if(loDatos.datos['Valido']){

				}else {
					fnAlert(loDatos['Mensaje'], 'CENSO URGENCIAS', false, false, 'medium');
				}
			} else {
				fnAlert(loDatos.error);
			}
			IniciarPacientes();
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la consulta guardar bitácora.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar guardar bitácora.');
	});
}

function validarSeccionMedico(){
	$("#btnGuardarSeccionMedico").attr("disabled", true);
	let lcNuevaUbicacion=$("#selNuevaUbicacionMed").val();

	if (lcNuevaUbicacion===''){
		$("#btnGuardarSeccionMedico").attr("disabled", false);
		fnAlert('Seleccione la nueva ubicación del paciente.', gcTitulo, false, false, 'medium');
	}else{
		guardarUbicacionMedico();
	}
}

function validarTipoPaciente(){
	$("#btnGuardarTipoPaciente").attr("disabled", true);
	let lcTipoPaciente=$("#selTipoPacienteCenso").val();

	if (lcTipoPaciente===''){
		$("#btnGuardarTipoPaciente").attr("disabled", false);
		fnAlert('Seleccione tipo de paciente.', gcTitulo, false, false, 'medium');
	}else{
		gcDatosRegistros=lcTipoPaciente;
		$("#selTipoPacienteCenso").val('');
		$('#divTipoPacienteCensoUrg').modal('hide');
		cambiarUbicacionCenso(gaDatosFila);
	}
}

function guardarUbicacionMedico() {
	gnTipoRegistro=20;
	let lcUbicacioneMedico=$("#selNuevaUbicacionMed").val();
	let loData = {ingreso: gaDatosFila.NIGING, tiporegistro: gnTipoRegistro, datosregistro: lcUbicacioneMedico, altatemprana: '', quitasalida: '',
				ubicacionmedico: lcUbicacioneMedico,};

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: 'procesosalidacenso', datos: loData},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				if(loDatos.datos['Valido']){
					let lcRegistroGuardar='Guarda ubicación médico';
					blanquearUbicacionMedico();
					terminaCenso(lcRegistroGuardar);
				}else {
					fnAlert(loDatos['Mensaje'], 'CENSO URGENCIAS', false, false, 'medium');
				}
			} else {
				fnAlert(loDatos.error);
			}
			IniciarPacientes();
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la consulta guardar ubicación médico.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar guardar ubicación médico.');
	});
}

function blanquearUbicacionMedico() {
	$('#divSeccionesMedicosUrg').modal('hide');
	gnTipoRegistro=0;
	gcTituloRegistro=gcEnviarListaAltas='';
	$("#selUbicacionActualMed,#selNuevaUbicacionMed").val('');
	$('#btnGuardarSeccionMedico').attr('disabled',false);
}

function salidaPacienteCenso(taDatosFila) {
	let lcTexto='';
	let loData = {ingreso: taDatosFila.NIGING, tiporegistro: gnTipoRegistro, datosregistro: gcDatosRegistros, altatemprana: '', quitasalida: gcQuitaSalida};

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: 'procesosalidacenso', datos: loData},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				if (loDatos.datos.Valido==false){
					lcTexto=loDatos.datos.Mensaje;
					fnAlert(lcTexto,gcTitulo);
				}else{
					IniciarPacientes();
					lcTexto="Actualización realizada satisfactoriamente.";
					fnAlert(lcTexto,gcTitulo,false,'blue',false);
				}
			} else {
				fnAlert(loDatos.error);
			}

		} catch(err) {
			fnAlert('No se pudo realizar la consulta excluir paciente censo.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar excluir paciente censo.');
	});
}

function cambiarUbicacionCenso(taDatosFila) {
	let lcTexto='';
	let loData = {ingreso: taDatosFila.NIGING, tiporegistro: gnTipoRegistro, datosregistro: gcDatosRegistros, altatemprana: '', quitasalida: ''};

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: 'procesosCambiarUbicacion', datos: loData},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				if (loDatos.datos.Valido==false){
					lcTexto=loDatos.datos.Mensaje;
					fnAlert(lcTexto,gcTitulo);
				}else{
					IniciarPacientes();
					lcTexto="Actualización realizada satisfactoriamente.";
					fnAlert(lcTexto,gcTitulo,false,'blue',false);
				}
			} else {
				fnAlert(loDatos.error);
			}

		} catch(err) {
			fnAlert('No se pudo realizar la consulta cambiar ubicación Censo.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar cambiar ubicación Censo.');
	});
}

function parametrosCenso() {
	gcAutorizadoSalidaCenso='';
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: 'consultaparametros'},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				gcAutorizadoSalidaCenso=loDatos.datos!='' ? 'S' : '';
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consulta parametros censo.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar parametros censo.');
	});
}

function consultarRegistros(taDatos) {
	let loData = {ingreso: taDatos.NIGING, tiporegistro: gnTipoRegistro, datosregistro: '', altatemprana: '', quitasalida: ''};
	let lcTexto=lcTextoHistorico='';
	$("#txtRegistrarCenso,#btnGuardaCensoUrgencias").attr("disabled", true);
	$("#txtHistoricoCenso,#txtRegistrarCenso").val('');
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: 'consultarregistrocenso', datos: loData},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				$('#divCensoUrgencias').modal('show');
				$('#modalCensoUrgencias').text(gcTituloRegistro);
				$('#txtHistoricoCenso').val(loDatos.datos);
				lcTipoRegistro=String(gnTipoRegistro);
				lcTipoUsuario=String(aAuditoria.cTipopUsuario);

				if ($.inArray(lcTipoRegistro, gaTiporegadm)>=0){
					if ($.inArray(lcTipoUsuario, gaTipousuadm)>=0){
						$("#txtRegistrarCenso,#btnGuardaCensoUrgencias").attr("disabled", false);
						$("#btnGuardaCensoUrgencias").css("display","block");
					}
				}

				if ($.inArray(lcTipoRegistro, gaTiporegmed)>=0){
					if ($.inArray(lcTipoUsuario, gaTipousumed)>=0){
						$("#txtRegistrarCenso,#btnGuardaCensoUrgencias").attr("disabled", false);
						$("#btnGuardaCensoUrgencias").css("display","block");
					}
				}

				if ($.inArray(lcTipoRegistro, gaTipoRegEnfer)>=0){
					if ($.inArray(lcTipoUsuario, gaTipoUsuEnfer)>=0){
						$("#txtRegistrarCenso,#btnGuardaCensoUrgencias").attr("disabled", false);
						$("#btnGuardaCensoUrgencias").css("display","block");
					}
				}
				if (gbAccesoColumnaJefeUrgencias=='true'){
						$("#txtRegistrarCenso,#btnGuardaCensoUrgencias").attr("disabled", false);
						$("#btnGuardaCensoUrgencias").css("display","block");
				}
				$("#txtRegistrarCenso").focus();
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la consulta consultar Registros.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar consultar Registros.');
	});
}

function registrosAltasTempranas(taDatos) {
	goTablaAlta.bootstrapTable('removeAll');
	let loData = {ingreso: taDatos.NIGING, tiporegistro: gnTipoRegistro, datosregistro: '', altatemprana: '', quitasalida: ''};
	$("#selAltaTempranaCensoU,#txtAltaTempranaCensoUrg").val('');

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: 'consultarDatosAltasTempranas', datos: loData},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				$.each(loDatos.datos, function( lcKey, loTipo ) {
					let laDatos = {CODIGO: loTipo.CODIGO, DESCRIPCION: loTipo.DESCRIPCION, OBSERVACION: loTipo.OBSERVACIONES,
					DATOSAUDITORIA: loTipo.DATOSAUDITORIA, MARCAVIENE: 1};
					adicionarAltaTemprana(laDatos,'');
				});
				mostrarAltasTempranas();
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la consulta registros Altas Tempranas.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar registros Altas Tempranas.');
	});
}

function consultaUsuariosRegistros() {
	gaTiporegadm=gaTiporegmed=gaTipousuadm=gaTipousumed=gaTipoExportar=gaTipoRegEnfer=gaTipoUsuEnfer='',
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion: 'consultarUsuariosRegistros', datos: ''},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				gaTiporegadm=loDatos.datos.tiporegadm;
				gaTiporegmed=loDatos.datos.tiporegmed;
				gaTipousuadm=loDatos.datos.tipousuadm;
				gaTipousumed=loDatos.datos.tipousumed;
				gaTipoExportar=loDatos.datos.tipoexportar;
				gaTipoUsuEnfer=loDatos.datos.tipousuenfer;
				gaTipoRegEnfer=loDatos.datos.tiporegenfer;
				gcActivaCambioUbicacion=loDatos.datos.activacambio;
				IniciarTablaCenso();
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la consulta Usuarios Registros.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar Usuarios Registros.');
	});
}

function mostrarAltasTempranas() {
	$("#selAltaTempranaCensoU,#txtAltaTempranaCensoUrg,#AdicionarAltaTempranaU").attr("disabled", true);
	$('#divAlertaTempranaCensoU').modal('show');
	let lcTipoUsuario=String(aAuditoria.cTipopUsuario);
	if ($.inArray(lcTipoUsuario, gaTipousumed)>=0){
		$("#selAltaTempranaCensoU,#txtAltaTempranaCensoUrg,#AdicionarAltaTempranaU").attr("disabled", false);
	}
	$('#modalCensoUrgenciasAlertaT').text("ALTAS TEMPRANA");
	$("#selAltaTempranaCensoU").focus();
}

function consultaEstados() {
let loData = {taTipoCenso: aDatosCenso=='urg' ? 'U' : 'H'};	
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: { accion: 'consultarEstados', datos: loData },
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				goEstadosCenso = loDatos.datos;
				crearConvencion();
				listadoTipoPaciente();
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la consulta estados.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar estados.');
	});
}

function listadoTipoPaciente() {
	lsTCen = (new URLSearchParams(window.location.href)).get('tcen');
	if (lsTCen=='urg'){
		listadoTipoPacienteUrg();
	}	
	if (lsTCen=='hos'){
		listadoTipoPacienteHos();
	}	
}

function listadoTipoPacienteUrg() {
	$.each(goEstadosCenso, function( lcKey, loTipo ) {
		if (loTipo.TIPO=='T'){
			$('#selTipoPacienteCenso').append('<option value="' + lcKey + '">'+loTipo.DESCR + '</option>');
			$('#selPacienteTipoUrgencias').append('<option value="' + lcKey + '">'+loTipo.DESCR + '</option>');			
		}
	});	
}

function listadoTipoPacienteHos() {
    lnindice = 0;
	var lnEstadoActual = '';
	$('#tblCensoPacientes').on('click-row.bs.table', function (e, row, $element) {
		var lnindice = $element.data('index');
		lnEstadoActual = $('#tblCensoPacientes').bootstrapTable('getData')[lnindice]['EXAURC'];
		$('#selTipoPacienteCenso').empty();
		$.each(goEstadosCenso, function( lcKey, loTipo ) {
			if(
				  ((lnEstadoActual!=lcKey) && ( (lnEstadoActual=='')  && (lcKey!=90) ))
				  ||
				  ( ( (lnEstadoActual!='')  && (lcKey==90) ) )
			) {	
				if (loTipo.TIPO=='T'){
					$('#selTipoPacienteCenso').append('<option value="' + lcKey + '">'+loTipo.DESCR +' </option>');
				}
			}	
		});
	});
}

function consultaGenero() {
	$.each(oGenerosPaciente.gaDatosGeneros, function( lcKey, loTipo ) {
		$('#selGeneroUrgencias').append('<option value="' + loTipo.CODIGO + '">'+loTipo.DESCRIPCION + '</option>');
	});
}

function crearConvencion() {
	var lcPopover = '<div class="container" width="380px"><div class="row"><div class="col"><small><table width="100%">',
		lnNum = 0, lnCol = 0
		lnNumEstados = Object.values(goEstadosCenso).length / 2;
	$.each(goEstadosCenso, function(lcClave, loEstado) {
		lnNum++;
		if(lnNum>lnNumEstados && lnCol==0){
			lcPopover += '</table></small></div><div class="col"><small><table width="100%">';
			lnCol++;
		}
		lcPopover += '<tr ><td style="background-color:#'+loEstado.COLOR+'" >'+loEstado.DESCR+'</td></tr>';
	});
	lcPopover += '</table></small></div></div></div>';
	$("#btnConvencionCenso").popover({
		animation: false,
		html: true,
		sanitize: false,
		placement: 'bottom',
		trigger: 'hover', // click | hover | focus | manual
		title: 'Estados censo',
		content: lcPopover,
		template: '<div class="popover" role="tooltip" style="width:600px;max-width:550px;"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
	});
}

function consultaSecciones() {
	let loSelect = $('#selSeccionCU');
	let lcSeccionesCenso = aDatosCenso=='urg' ? 'SeccionesUrgencias'  : 'SeccionesHospitalizados';
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: { accion: lcSeccionesCenso },
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				$.each(loDatos.datos, function( lcKey, loTipo ) {
					if (loTipo.CODPISO!='TU'){
						loSelect.append('<option value="' + loTipo.CODPISO + '">' + loTipo.CODPISO+' - '+loTipo.DESCRIP + '</option>');
					}
				});
				consultaGenero();
			} else {
				fnAlert(loDatos.error);
			}
			IniciarPacientes();
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la consulta Secciones.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar Secciones.');
	});
}

function consultaUbicacionMedico() {
	let lcSeccionesCenso = 'SeccionesUrgenciasMedicos';
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: { accion: lcSeccionesCenso },
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				$.each(loDatos.datos, function( lcKey, loTipo ) {
					$('#selSeccionUrgencias').append('<option value="' + loTipo.CODPISO + '">'+ loTipo.DESCRIP + '</option>');
					$('#selUbicacionActualMed').append('<option value="' + loTipo.CODPISO + '">'+ loTipo.DESCRIP + '</option>');
					$('#selNuevaUbicacionMed').append('<option value="' + loTipo.CODPISO + '">'+ loTipo.DESCRIP + '</option>');
				});
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la consulta Secciones.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar Secciones.');
	});
}

function adicionarSeccionesUrg(taDatos){
	let rows = []
		rows.push({
		CODIGO: taDatos.CODIGO,
		DESCRIPCION: taDatos.CODIGO +' - '+taDatos.DESCRIPCION,
	})
	$('#tblSeccionUrgencias').bootstrapTable('append', rows);
}

function consultarListaAltasTempranas() {
	let loSelect = $('#selAltaTempranaCensoU');
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: { accion:'consultaListaAltasTempranas' },
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				$.each(loDatos.datos, function( lcKey, loTipo ) {
					loSelect.append('<option value="' + loTipo.CODIGO + '">' +loTipo.DESCRIPCION + '</option>');
				});
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la consulta iniciar secciones censo.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar iniciar secciones censo.');
	});
}

function blanquearModal() {
	$("#txtHistoricoCenso,#txtRegistrarCenso").val('');
	$("#txtRegistrarCenso,#btnGuardaCensoUrgencias").attr("disabled", true);
	$("#btnGuardaCensoUrgencias").css("display","none");
	$('#divCensoUrgencias').modal('hide');
	gnTipoRegistro=0;
	gcTituloRegistro='';
}

function blanquearModalAltas() {
	$('#divAlertaTempranaCensoU').modal('hide');
	gnTipoRegistro=0;
	gcTituloRegistro=gcEnviarListaAltas='';
	$("#selAltaTempranaCensoU,#txtAltaTempranaCensoUrg").val('');
	$("#selAltaTempranaCensoU,#txtAltaTempranaCensoUrg,#AdicionarAltaTempranaU").attr("disabled", true);
	$('#btnGuardaAlertaTemCenso').attr('disabled',true);
}

function limpiar() {
	goTabla.bootstrapTable('removeAll');
	goTablaAlta.bootstrapTable('removeAll');
	IniciarPacientes();
}

function IniciarPacientes() {
	gnTipoRegistro=gnMarcaVieneAlta=gnIndiceRegistroAlta=0;
	gcTituloRegistro=gcDatosRegistros=gcQuitaSalida=gcEnviarListaAltas=gcListaSecUrgencias='';
	buscarPacientesCenso();
}

function consultaAutorizacionUsuarioEspecifico(){
	let loData = {tsPermiso: 'usuarioJefeUrgencias'};		
		$.ajax({
		type: "POST",
		async: false,
		url: gcUrlajax,
		data: { accion: 'consultaAutorizacionUsuarioEspecifico', datos: loData },
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				gbAccesoColumnaJefeUrgencias = loDatos.datos['usuarioJefeUrgencias'];
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			console.log(err);
			fnAlert('No se pudo realizar la consulta  de Autorizacion Usuario Especifico');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar Autorizacion Usuario Especifico.');
	});
}
function IniciarTablaCenso() {
	consultaAutorizacionUsuarioEspecifico();
var jsonColumnsPrimeras = [
	{
		title: 'Opc.',
		align: 'center',
		events: eventoOpciones,
		formatter: '<a class="opcionesHC" href="javascript:void(0)" title="Menú Opciones"><i class="fas fa-list-ol"></i></a>'
	},
	{
		title: 'Sección', field: 'CAMA', halign: 'center', width: 5, widthUnit: "rem", sortable: true
	},
	{
		title: 'Ubicación', field: 'UBICAMED', halign: 'center', align: 'center', width: 5, widthUnit: "%", clickToSelect: false,
		formatter: formaUbicacionMedico, events: eventoUbicacionMedico, visible: aDatosCenso=='urg' ? true : false
	},
	{
		title: 'Paciente', field: 'PACIENTE', halign: 'center', width: 22, widthUnit: "rem", formatter: formatoPaciente,
		events: eventoSeguimiento, cellStyle: formatoColorPaciente
	},
	{
		title: 'Edad', field: 'EDAD_A', halign: 'center', align: 'center'
	},
	{
		title: 'Ingreso', field: 'NIGING', halign: 'center', searchable: false
	},
	{
		title: 'Identificación', field: 'IDENTIFICACION', halign: 'center', formatter: formatoIdentificacion, width: 8, widthUnit: "%"
	},
	{
		title: 'Entidad', field: 'DSCCON', halign: 'center', width: 20, widthUnit: "rem"
	},
	{
		title: 'Fecha Ingreso', field: 'FECHORAINGRESO', halign: 'center', width: 11, widthUnit: "%", sortable: true
	},
	{
		title: aDatosCenso=='urg' ? 'Est.(hr)' : 'Est.(días)', field: 'DIFEREN', halign: 'center', align: 'center', sortable: true
	},
	{
		title: 'Antec.', field: 'ANTECED', halign: 'center', align: 'center', width: 5, widthUnit: "%", clickToSelect: false,
		formatter: formaAntecedente, events: eventosRegistro, visible: aDatosCenso=='urg' ? true : false
	},
	{
		title: 'Diagnóstico', field: 'DIAGNOS', halign: 'center', width: 15, widthUnit: "%", formatter: formaDiagnostico, events: eventosRegistro
	},
	{
		title: aDatosCenso=='urg' ? 'Plan de Manejo': 'Seguimiento Médico Hospitalario', field: 'PLANM', halign: 'center',
		width: 15, widthUnit: "%", formatter: formaPlanManejo, events: eventosRegistro
	}
];
var jsonColumnsUltimas = [
	{
		title: 'Recomendaciones médico tratante', field: 'RECTRA', halign: 'center', width: 20, widthUnit: "rem",
		formatter: formaRecomendaciones, events: eventosRegistro, visible: aDatosCenso=='hos' ? true : false
	},
	{
		title: 'Médico tratante', field: 'MEDICO', halign: 'center', width: 20, widthUnit: "rem", formatter: formatoMedico
	},
	{
		title: 'Dieta', field: 'DIETA', halign: 'center', width: 20, widthUnit: "rem", formatter: formaDieta,
		events: eventosRegistro, visible: aDatosCenso=='urg' ? true : false
	},
	{
		title: 'Enfermeria', field: 'ENFERM', halign: 'center', width: 20, widthUnit: "rem",
		formatter: formaEnfermeria, events: eventosRegistro, visible: aDatosCenso=='urg' ? true : false
	},
	{
		title: 'Plan Administrativo', field: 'ADMIN', halign: 'center', width: 20, widthUnit: "rem",
		formatter: formaAdministrativo, events: eventosRegistro
	},
	{
		title: 'Ant. Patológicos', field: 'ANTPATOL', halign: 'center', align: 'center', width: 5, widthUnit: "%",
		visible: false
	},
	{
		title: 'Acciones', field: 'ACCIONES', width: 20, widthUnit: "rem", halign: 'center', align: 'center',
		clickToSelect: false, formatter: formatoAcciones, events: eventosRegistro
	}
	];
var jsonColumnsIntermedias = [
{
	title: 'DIRECCIÓN HOSPITALARIA', field: 'PENDREVI', halign: 'center', width: 20, widthUnit: "rem",
	formatter: formaPendientesRevistaTarde, events: eventosRegistro, visible: aDatosCenso=='hos' ? true : false
}
];
if (gbAccesoColumnaJefeUrgencias=='true'){ lascol=jsonColumnsPrimeras.concat(jsonColumnsIntermedias).concat(jsonColumnsUltimas) }
else{ lascol=jsonColumnsPrimeras.concat(jsonColumnsUltimas)};
	goTabla.bootstrapTable({
		classes: 'table table-bordered table-hover table-sm table-responsive-sm table-striped',
		theadClasses: 'thead-light',
		locale: 'es-ES',
		search: true,
		height: '600',
		pagination: true,
		pageSize: 25,
		pageList: '[10, 20, 50, 100, 250, 500, All]',
		showColumns: true,
		showExport: ($.inArray(String(aAuditoria.cTipopUsuario), gaTipoExportar)>=0) ? true : false,
		exportDataType: 'all',
		exportTypes: ['csv', 'xlsx'],
		iconSize: 'sm',
		rowStyle: formatoColorFila,
		columns: lascol
	});
}

var eventoOpciones = {
	'click .opcionesHC': function(e, tcValor, toFila, tnIndice) {
		goFila = {
			INGRESO: toFila.NIGING,
			TIPO_DOC: toFila.TIDING,
			NUM_DOC: toFila.NIDING,
			PACIENTE: toFila.PACIENTE,
			FECHA_NAC: toFila.FNAPAC,
			EDAD: toFila.EDAD_A.replace('A ','-').replace('M ','-').replace('D',''),
			GENERO: toFila.SEXPAC,
			SECCION: toFila.CAMA.substr(0,2),
			HABITACION: toFila.CAMA.substr(2,5),
			ESTADO: toFila.ESTHAB,
			CODVIA: toFila.VIAING,
			FECHA_ING: toFila.FEIING,
			CP1PAL: toFila.CP1PAL,
			TP1PAL: toFila.TP1PAL,
			TIPO_HABITACION: toFila.TIPO_HABITACION,
		};
		mostrarMenuIngreso(goFila.INGRESO)
	}
}

function formatoColorPaciente(tnValor, toFila) {
	return goEstadosCenso[toFila.EXAURC]? {css: {'background-color':'#'+goEstadosCenso[toFila.EXAURC].COLOR}}: {};
}

function formatoAcciones(value, row, index) {
	let lcTipoUsuario=String(aAuditoria.cTipopUsuario);

	return [
		'<a class="altatempranacenso badge badge-'+(row.ALTATEMP=='S'?'warning':'secondary')+'" href="javascript:void(0)" title="Alta temprana">',
		'<i class="fa fa-bell"></i>',
		'</a>&nbsp;&nbsp;&nbsp;',
		(($.inArray(lcTipoUsuario, gaTipousuadm)>=0) && (gcAutorizadoSalidaCenso!='')?'<a class="excluirdecenso badge badge-'+(row.ESTURC!=99?'danger':'primary')+'" href="javascript:void(0)" title="Excluir del censo"><i class="fa fa-trash"></i></a>&nbsp;&nbsp;&nbsp;':''),
		(($.inArray(lcTipoUsuario, gaTipousuadm)>=0) && (row.SALIDAPROV==='') && (aDatosCenso=='urg')?'<a class="salidaprovisional badge badge-'+(row.SALIDAPROV=='S'?'info':'secondary')+'" href="javascript:void(0)" title="Salida temporal"><i class="fa fa-door-closed"></i></a>&nbsp;&nbsp;&nbsp;':''),
		(gcActivaCambioUbicacion==''?'<a class="cambiarubicacion" href="javascript:void(0)" title="Cambiar ubicacion"><i class="fa fa-cloud" style="color:#51C2BB"></i>':''),
		 '</a>'
	].join('');
}

function IniciarTablaAltaTemprana() {
	goTablaAlta.bootstrapTable({
		classes: 'table table-bordered table-hover table-sm table-responsive-sm table-striped',
		theadClasses: 'thead-light',
		locale: 'es-ES',
		undefinedText: '',
		toolbar: '#toolBarLstIntrExam',
		height: '250',
		pagination: false,
		columns: [
			{
				title: 'Tipo Alta', field: 'TIPOALTA', halign: 'center', width: 5, widthUnit: "%", visible: false
			},{
				title: 'Alta Temprana', field: 'DESCRIPCIONALTA', halign: 'center', width: 20, widthUnit: "%"
			},{
				title: 'Observaciones', field: 'OBSERVACION', halign: 'center', width: 50, widthUnit: "%"
			},{
				title: 'Registrado por', field: 'AUDITORIA', halign: 'center', width: 25, widthUnit: "%"
			},{
				title: 'ACCIÓN', field: 'ACCIONES', width: 5, widthUnit: "%", halign: 'center', align: 'center',
				clickToSelect: false, events: eventoAccionltaTemprana, formatter: formatoAccionltaTemprana
			}
		],
	});
}

function IniciarTablaSeccionUrgencias(){
	goTablaSecUrgencia.bootstrapTable({
		classes: 'table table-hover table-striped table-sm table-responsive-sm',
		theadClasses: 'thead-light',
		locale: 'es-ES',
		checkboxHeader: false,
		clicktoselect: 'true',
		height: '450',
		columns: [
		{title: '', field: 'SELECCION', checkbox: 'false', width: 5, widthUnit: "%", halign: 'center', align: 'center'},
		{title: 'Secciones urgencias', field: 'DESCRIPCION', width: 95, widthUnit: "%", halign: 'center', align: 'left'}
		]
	});
}

function formaAntecedente(value, row, index) {
	return [
	 '<a class="datosaregistrar badge badge-secondary" href="javascript:void(0)" title="Antecedentes">',
		'<i class="fa fa-receipt"',
	 '</a>'
	].join('');
}

function formaUbicacionMedico(tnValor, toFila) {
	let lcTipoUbicacion=toFila.UBICAMED;
	let lcDescripcionUbicacion=lcTipoUbicacion==='-' ? lcTipoUbicacion : $("#selSeccionUrgencias option[value="+lcTipoUbicacion+"]").text();
	return [
		'<a class="datoUbicacionMedico" style="color: black;" href="javascript:void(0)" title="Ubicación médico">' + lcDescripcionUbicacion,
		'</a>'
	].join('');
}

function formaDiagnostico(value, row, index) {
	return [
		'<a class="datosdiagnostico" style="color: black;" href="javascript:void(0)" title="Diagnóstico">' + value,
		'</a>'
	].join('');
}

function formaEnfermeria(value, row, index) {
	return [
		'<a class="datosenfermeria" style="color: black;" href="javascript:void(0)" title="Enfermeria">' + value,
		'</a>'
	].join('');
}

function formaPlanManejo(value, row, index) {
	return [
		'<a class="datosPlanManejo" style="color: black;" href="javascript:void(0)" title="Plan de manejo">' + value,
		'</a>'
	].join('');
}

function formaDieta(value, row, index) {
	return [
		'<a class="datosDieta" style="color: black;" href="javascript:void(0)" title="Dieta">' + value,
		'</a>'
	].join('');
}

function formaAdministrativo(value, row, index) {
	return [
		'<a class="datosAdministrativo" style="color: black;" href="javascript:void(0)" title="Administrativo">' + value,
		'</a>'
	].join('');
}

function formaRecomendaciones(value, row, index) {
	return [
		'<a class="datosRecomendaciones" style="color: black;" href="javascript:void(0)" title="Recomendaciones médico tratante">' + value,
		'</a>'
	].join('');
}

function formaPendientesRevistaTarde(value, row, index) {
	return [
		'<a class="datosPendientesRevistaTarde" style="color: black;" href="javascript:void(0)" title="DIRECCIÓN HOSPITALARIA">' + value,
		'</a>'
	].join('');
}

function verAntecedentes(){
	oAntecedentesConsulta.mostrar();
}

function formatoExportar(){
	llRetornar=false;
	return llRetornar;
}

function formatoAccionltaTemprana(value, row, index) {
	let lcTipoUsuario=String(aAuditoria.cTipopUsuario);
	if ($.inArray(lcTipoUsuario, gaTipousumed)>=0){
		if (row.MARCAVIENE==1){
			return [
				'<a class="editaraltatemprana" href="javascript:void(0)" title="Editar">',
				'<i class="fas fa-pencil-alt"></i>',
				'</a>'
			].join('')
		}else{
			return [
				'<a class="editaraltatemprana" href="javascript:void(0)" title="Editar">',
				'<i class="fas fa-pencil-alt"></i>',
				'</a>&nbsp;&nbsp;&nbsp;',
				'<a class="quitaraltatemprana" href="javascript:void(0)" title="Eliminar Alta">',
				'<i class="fas fa-trash-alt" style="color:#E96B50"></i>',
				'</a>'
			].join('')
		}
	}
}

var eventoAccionltaTemprana = {
	'click .editaraltatemprana': function (e, value, row, index) {
		editaAltaTemprana(row);
	},

	'click .quitaraltatemprana': function(e, tcValor, toFila, tnIndice) {
		fnConfirm('Desea eliminar el alta temprana?', false, false, false, false, function(){
			goTablaAlta.bootstrapTable('remove', {
			field: 'TIPOALTA',
			values: [toFila.TIPOALTA]
			});
		},'');
	},
}

function editaAltaTemprana(arow) {
	gnMarcaVieneAlta=arow.MARCAVIENE;
	$("#selAltaTempranaCensoU").val(arow.TIPOALTA);
	$("#txtAltaTempranaCensoUrg").val(arow.OBSERVACION);
	$("#selAltaTempranaCensoU").focus();
}

var eventoUbicacionMedico = {
	'click .datoUbicacionMedico': function(e, tcValor, toFila, tnIndice) {
		gaDatosFila=toFila;
		gcTituloRegistro='UBICACION MÉDICO';
		$('#selUbicacionActualMed').val(toFila.UBICAMED);
		$('#divSeccionesMedicosUrg').modal('show');
	}
}

var eventoSeguimiento = {
	'click .datoSeguimiento': function(e, tcValor, toFila, tnIndice) {
		let lcTipoUsuario=String(aAuditoria.cTipopUsuario);
			if ($.inArray(lcTipoUsuario, gaTipousumed)>=0){
				$("#selTipoPacienteCenso").val('');
				gaDatosFila=toFila;
				gnTipoRegistro=30;
				$('#btnGuardarTipoPaciente').attr('disabled',false);
				$('#divTipoPacienteCensoUrg').modal('show');
			}
	}
}

var eventosRegistro = {
	'click .datosaregistrar': function(e, tcValor, toFila, tnIndice) {
		aDatosIngreso.cTipId=toFila.TIDING;
		aDatosIngreso.nNumId=toFila.NIDING;
		verAntecedentes();
	},

	'click .datosdiagnostico': function(e, tcValor, toFila, tnIndice) {
		gaDatosFila=toFila;
		gnTipoRegistro=1;
		gcTituloRegistro='DIAGNÓSTICO';
		consultarRegistros(toFila);
	},

	'click .datosPlanManejo': function(e, tcValor, toFila, tnIndice) {
		gaDatosFila=toFila;
		gnTipoRegistro=2;
		gcTituloRegistro=toFila.UBPURC=='U'?'PLAN DE MANEJO':'SEGUIMIENTO MÉDICO HOSPITALARIO';
		consultarRegistros(toFila);
	},

	'click .datosDieta': function(e, tcValor, toFila, tnIndice) {
		gaDatosFila=toFila;
		gnTipoRegistro=3;
		gcTituloRegistro='DIETA PACIENTE';
		consultarRegistros(toFila);
	},

	'click .datosAdministrativo': function(e, tcValor, toFila, tnIndice) {
		gaDatosFila=toFila;
		gnTipoRegistro=4;
		gcTituloRegistro='PLAN ADMINISTRATIVO';
		consultarRegistros(toFila);
	},

	'click .altatempranacenso': function(e, tcValor, toFila, tnIndice) {
		gaDatosFila=toFila;
		gnTipoRegistro=5;
		registrosAltasTempranas(toFila);
	},

	'click .datosenfermeria': function(e, tcValor, toFila, tnIndice) {
		gaDatosFila=toFila;
		gnTipoRegistro=6;
		gcTituloRegistro='ENFERMERIA';
		consultarRegistros(toFila);
	},

	'click .datosRecomendaciones': function(e, tcValor, toFila, tnIndice) {
		gaDatosFila=toFila;
		gnTipoRegistro=7;
		gcTituloRegistro='RECOMENDACIONES MÉDICO TRATANTE';
		consultarRegistros(toFila);
	},

	'click .datosPendientesRevistaTarde': function(e, tcValor, toFila, tnIndice) {
		gaDatosFila=toFila;
		gnTipoRegistro=9;
		gcTituloRegistro='DIRECCIÓN HOSPITALARIA';
		consultarRegistros(toFila);
	},

	'click .cambiarubicacion': function(e, tcValor, toFila, tnIndice) {
		let lcUbicacion=toFila.UBPURC=='U' ? 'Urgencias' : (toFila.UBPURC=='H' ? 'Hospitalización' : '');
		let lcUbicacionNueva=toFila.UBPURC=='U' ? 'Hospitalización' : (toFila.UBPURC=='H' ? 'Urgencias' : '');
		let lcTextoUbicacion= 'Desea cambiar la ubicación a ' +lcUbicacionNueva+ '?, actualmente se encuentra en el censo '+lcUbicacion+'.';

		fnConfirm(lcTextoUbicacion, gcTitulo, false, false, 'medium',
			{
				text: 'Si',
				action: function(){
					gaDatosFila=toFila;
					gnTipoRegistro=25;
					gcDatosRegistros=toFila.UBPURC=='U' ? 'H' : 'U';
					cambiarUbicacionCenso(toFila);
				}
			},

			{ text: 'No',
			}
		);
	},

	'click .excluirdecenso': function(e, tcValor, toFila, tnIndice) {
		if (gcAutorizadoSalidaCenso===''){
			fnAlert("Usuario no autorizado para excluir pacientes del censo, revise por favor.", gcTitulo, false, 'blue', false);
		}else{
			let lcTipoUsuario=String(aAuditoria.cTipopUsuario);

			if ($.inArray(lcTipoUsuario, gaTipousuadm)>=0){
				let lnEstado=parseInt(toFila.ESTURC);
				let lcSalidaProvisional=toFila.SALIDAPROV;

				let lcTextoExluir= lnEstado==99 ? 'Desea activarlo al paciente del censo?' : 'Desear excluir al paciente del censo?.';
				let lcDatosRegistro= lnEstado==99 ? 'Activar paciente censo' : 'Excluir paciente censo';
				let lcQuitaSalida= lnEstado==99 ? 'Q' : lcSalidaProvisional;

				fnConfirm(lcTextoExluir, gcTitulo, false, false, false,
					{
						text: 'Si',
						action: function(){
							gnTipoRegistro=99;
							gcDatosRegistros=lcDatosRegistro;
							gcQuitaSalida=lcQuitaSalida;
							salidaPacienteCenso(toFila);
						}
					},

					{ text: 'No',
					}
				);
			}else{
				fnAlert("Opción solo para usuario administrativo.", gcTitulo, false, 'blue', 'medium');
			}
		}
	},

	'click .salidaprovisional': function(e, tcValor, toFila, tnIndice) {
		if (toFila.SALIDAPROV=='S'){
			fnAlert("Paciente ya tiene asignada salida temporal.", gcTitulo, false, 'blue', false);
		}else{
			let lcTextoSalidaProvisional = 'Desea asignarle SALIDA TEMPORAL al paciente <br>' + toFila.PACIENTE + '?.';
			let lcDatosRegistro= 'Salida temporal'
			lcQuitaSalida='';

			fnConfirm(lcTextoSalidaProvisional, gcTitulo, false, false, 'medium',
				{
					text: 'Si',
					action: function(){
						gnTipoRegistro=8;
						gcDatosRegistros=lcDatosRegistro;
						gcQuitaSalida=lcQuitaSalida;
						salidaPacienteCenso(toFila);
					}
				},

				{ text: 'No',
				}
			);
		}
	}
}

function formatoColorFila(toFila, tnIndice) {
	return goEstadosCenso[toFila.COLOR]? {css: {'background-color':'#'+goEstadosCenso[toFila.COLOR].COLOR}}: {};
}

function formatoIdentificacion(tnValor, toFila) {
	return toFila.TIDING+'-'+toFila.NIDING;
}

function formatoPaciente(tnValor, toFila) {
	return [
		'<a class="datoSeguimiento" style="color: black;" href="javascript:void(0)" title="Seguimiento">',
		'<i class="fa fa-'+(oGenerosPaciente.gaDatosGeneros[toFila.SEXPAC]? oGenerosPaciente.gaDatosGeneros[toFila.SEXPAC]['IMAGEN']: '')+'"></i> <b>'+toFila.PACIENTE+'</b>',
		'</a>'
	].join('');
}

function formatoMedico(tnValor, toFila) {
	let lcDatosMedico='';
	if (toFila.UBPURC=='U'){
		lcDatosMedico=(toFila.NOMBRE_MEDICO_URGENCIAS===undefined || toFila.NOMBRE_MEDICO_URGENCIAS==='')? '' : toFila.ESPECIALIDAD_URGENCIAS+'-'+toFila.NOMBRE_MEDICO_URGENCIAS;
	}else{
		lcDatosMedico=(toFila.NOMBRE_MEDICO===undefined  || toFila.NOMBRE_MEDICO==='') ? '' : toFila.ESPECIALIDAD+'-'+toFila.NOMBRE_MEDICO;
	}
	return lcDatosMedico;
}

function expandirFila(tnIndex, toFila, $toDetalle) {
	goTabla.bootstrapTable('collapseAllRows');
	goTabla.bootstrapTable('expandRow', tnIndex);
}

function cancelarRegistros() {
	blanquearModal();
}

function validarRegistros() {
	$("#btnGuardaCensoUrgencias").attr("disabled", true);
	let lcRegistrosCenso=$("#txtRegistrarCenso").val().trim();

	if (lcRegistrosCenso==''){
		$("#btnGuardaCensoUrgencias").attr("disabled", false);
		fnAlert("Registro obligatorio, revise por favor.", gcTitulo, false, false);
	}else{
		guardarRegistros(lcRegistrosCenso);
	}
}

function validarAdicionarAltaTemprana(e) {
	e.preventDefault();
	let lcTipoAlta=$("#selAltaTempranaCensoU").val();
	let lcDescripcionTipoAlta=$("#selAltaTempranaCensoU option[value="+lcTipoAlta+"]").text();
	let lcObservacionesAlta=$("#txtAltaTempranaCensoUrg").val();
	gnIndiceRegistroAlta=0;

	if (lcTipoAlta==''){
		fnAlert("Tipo alta temprana obligatorio, revise por favor.", gcTitulo, false, false);
	}else{
		let laDatos = {CODIGO: lcTipoAlta, DESCRIPCION: lcDescripcionTipoAlta, OBSERVACION: lcObservacionesAlta, MARCAVIENE: 0};
		let taTablaConsultar=goTablaAlta.bootstrapTable('getData');
		let llverificaExiste = verificaCodigoExiste(lcTipoAlta,taTablaConsultar);

		if(llverificaExiste) {
			adicionarAltaTemprana(laDatos,'S');
		}else{
			modificarAltaTemprana(laDatos);
		}
		blanqueaAltaTemprana();
		$('#btnGuardaAlertaTemCenso').attr('disabled',false);
	}
}

function verificaCodigoExiste(tcCodigo,taTablaValida){
	let llRetorno = true ;
		if(taTablaValida != ''){
			$.each(taTablaValida, function( lcKey, loTipo ) {
				if(loTipo['TIPOALTA']==tcCodigo){
					gnIndiceRegistroAlta = lcKey;
					llRetorno = false;
				}
			});
		};
	return llRetorno;
}

function modificarAltaTemprana(taDatos){
	$('#tblAltasTempranas').bootstrapTable('updateRow', {
		index: gnIndiceRegistroAlta,
		row: {
			TIPOALTA: taDatos.CODIGO,
			DESCRIPCIONALTA: taDatos.DESCRIPCION,
			OBSERVACION: taDatos.OBSERVACION,
			MARCAVIENE: gnMarcaVieneAlta===undefined ? taDatos.MARCAVIENE : gnMarcaVieneAlta,
			NUEVA: 'S',
			ACCIONES: '',
		}
	 });
	 gnMarcaVieneAlta=0;
}

function adicionarAltaTemprana(taDatos,tcEsNuevo){
	let rows = []
		rows.push({
		TIPOALTA: taDatos.CODIGO,
		DESCRIPCIONALTA: taDatos.DESCRIPCION,
		OBSERVACION: taDatos.OBSERVACION,
		AUDITORIA: taDatos.DATOSAUDITORIA,
		MARCAVIENE: taDatos.MARCAVIENE,
		NUEVA: tcEsNuevo,
		ACCIONES: '',
	})
	$('#tblAltasTempranas').bootstrapTable('append', rows);
}

function validarAltaTemprana(){
	$("#btnGuardaAlertaTemCenso").attr("disabled", true);
	gcEnviarListaAltas='';
	let taAltasTempranas = goTablaAlta.bootstrapTable('getData');
	let lcEnviarListaAltas='';
	if(taAltasTempranas != ''){
		traerDatosAltasTempranas(taAltasTempranas, function() {
			guardarAltasTempranas(gcEnviarListaAltas);
		});
	}else{
		$("#btnGuardaAlertaTemCenso").attr("disabled", false);
		fnAlert('No se ha registrado ninguna alta temprana, por favor verificar.', gcTitulo, false, false, 'medium');
	}
}

function traerDatosAltasTempranas(taAltasTempranas, tfPost){
	$.each(taAltasTempranas, function( lcKey, loTipo ) {
		gcEnviarListaAltas=gcEnviarListaAltas+loTipo.TIPOALTA+goChr24+loTipo.OBSERVACION+goChr25;
	});
	if (typeof tfPost == 'function') {
		tfPost();
	}
}

function blanqueaAltaTemprana(){
	$("#selAltaTempranaCensoU,#txtAltaTempranaCensoUrg").val('');
	$("#selAltaTempranaCensoU").focus();
}

function terminaCenso(tcMensaje){
	limpiar();
	fnInformation('Registro censo guardado.', 'CENSO URGENCIAS', false, false, 'medium');
}