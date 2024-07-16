var goTablaEquipos=$('#tblEquiposEspeciales'),
	goTablaCups=$('#tblCups'), gaCups=[],
	goMobile = new MobileDetect(window.navigator.userAgent),
	gcUrlajax = "vista-programacion-salas/ajax/ajax",
	gcTitulo = 'Agenda Salas Cirugia',
	gaSalas = [],
	laDatosEncuentra = [],
	datosProcedimiento = [],
	goFechaHoraProgramar = [],
	gcRegistroMedico = '',
	gnDiasMaximoSolicitud = 0,
	gcListadoEquiposGuardados = '';

$(function () {
	$("body").css("padding-bottom","50px");

	$('#selTipDocSala').tiposDocumentos({horti: "1", valor: gcTipoIdentificacion });
	IniciarListados();
	IniciarTablaEquiposEspec();
	IniciarTablaCups();
	cargarListadosAutocompletar();
	consultaDiferenciaSolicitud();

	if (gnConsecutivo>0){
		habilitar(true);
		$("#btnLimpiarSala").prop("disabled", true);
		cargaMedicoSolicita(gRegistroSolicita);
	}else{
		habilitar(false);
	}
	$('#selTipDocSala').focus();
	CargarReglas("Reglas","#FormAgendaCirugia", "Agensal");

	$('#FormAgendaCirugia .input-group.date').datepicker({
		autoclose: true,
		clearBtn: true,
		daysOfWeekHighlighted: "0,6",
		format: "yyyy-mm-dd",
		language: "es",
		todayBtn: true,
		todayHighlight: true,
		toggleActive: true,
		weekStart: 1
	});

	$('#txtNumDocSala').on('change',function() {
		if ($("#selTipDocSala").val()!='' && $("#txtNumDocSala").val()!=''){
			consultaPaciente(function(){
				habilitar(true);
				inactivarCampos(true);
				blanquearCampos();
			});
		}else{
			if ($("#selTipDocSala").val()==''){
				$('#selTipDocSala').focus();
				fnAlert("Falta tipo identificación, revise por favor.");
			}
		}
	});

	$('#selEspecialidadMedico').on('change',function() {
		$('#selMedicoPrograma').prop("disabled",false);
		$("#selMedicoPrograma").val('');
		if ($("#selEspecialidadMedico").val()!=''){
			cargarMedicos('selMedicoPrograma','cargarMedicosSala','Médicos salas por especialidad',$("#selEspecialidadMedico").val());
		}
	});
	
	$('#selTipoAnestesiaSala').on('change',function() {
		$("#selAnestesiologo").val('');
		$("#selAnestesiologo").prop("disabled",true);
		$("#lblAnestesiologo").removeClass("required");
		
		if ($("#selTipoAnestesiaSala").val()!=6 && $("#selTipoAnestesiaSala").val()!=''){
			$("#lblAnestesiologo").addClass("required");
			$("#selAnestesiologo").prop("disabled",false);
		}
	});
	limpiarDatos();
	$('#btnAdicionarCup').on('click', adicionarCup);
	$('#btnLimpiarSala').on('click', limpiarDatos);
	$('#btnGuardarSala').on('click', validarEnvia);
	$('#btnCerrar').on('click', cerrarVentana);
})

function cerrarVentana(){
	if (gnConsecutivo==0){
		fnConfirm('Desea cerrar, sin guardar los datos?', false, false, false, false,
			{
				text: 'Aceptar',
				action: volverAgenda
			},
			{ text: 'Cancelar' }
		);
	}else{
		volverAgenda();
	}
}

function consultaDiferenciaSolicitud(){
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: { accion:'obtieneDiasSolicitud'},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				gnDiasMaximoSolicitud = loDatos.datos;
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consulta diferencia días solicitud.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar diferencia días solicitud.');
	});
}

function consultaPaciente(fnDespues){
	var lnCaracteresIdentificacion=$("#txtNumDocSala").val().length;
	if (lnCaracteresIdentificacion<=13){
		$.ajax({
			type: "POST",
			url: gcUrlajax,
			data: { accion:'paciente', tipoId: $("#selTipDocSala").val(), numId: $("#txtNumDocSala").val()},
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.error == ''){
					asignarDatos(loDatos.datos);
					if (typeof fnDespues === 'function') {
						fnDespues();
					}
				} else {
					fnAlert(loDatos.error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la consulta datos del paciente.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar datos del paciente.');
		});
	}else{
		$("#txtNumDocSala").val('');
		$("#txtNumDocSala").removeClass("is-valid");
		fnAlert("El número de identificación no puede exceder a 13 caracteres, revise por favor.");
	}	
}

function obtenerFechaSistema(tfFuncion){
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'fechahorasistema'},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				goFechaHoraProgramar = loDatos.datos;
				if (typeof tfFuncion=='function'){
					tfFuncion();
				}
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			fnAlert('No se pudo consultar fecha/hora del sistema.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar fecha/hora del sistema.');
	});
}

function cargaMedicoSolicita(tcMedicoSolicita) {
	var loSelect = $('#selMedicoPrograma');
	loSelect.empty();

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'registromedicosolicita', lcRegistroMedico: tcMedicoSolicita, lcCodigoEnviar: ''},
		dataType: "json"
	})
	.done(function( loDatos ) {
		try {
			if (loDatos.error == ''){
				loSelect.append('<option value="' + loDatos.datos.cRegistro + '">' + loDatos.datos.cNombre + '</option>');
			} else {
				fnAlert(loDatos.error + ' ');
			}
		} catch(err) {
			fnAlert('No se pudo realizar la busqueda del médico que solicita.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al buscar del médico que solicita.');
	});
	return this;
}


function asignarDatos(laDatosPaciente){
	if (laDatosPaciente['PrimerNombre']===undefined){
		activarCampos(false);
	}else{
		lcFechaNacimiento = laDatosPaciente['FechaNacimiento'].substr(0,4)+'-'+laDatosPaciente['FechaNacimiento'].substr(4,2)+'-'+laDatosPaciente['FechaNacimiento'].substr(6,2);
		$("#txtPrimerNombre").val(laDatosPaciente['PrimerNombre']);
		$("#txtSegundoNombre").val(laDatosPaciente['SegundoNombre']);
		$("#txtPrimerApellido").val(laDatosPaciente['PrimerApellido']);
		$("#txtSegundoApellido").val(laDatosPaciente['SegundoApellido']);
		$("#txtGeneroSala").val(laDatosPaciente['Genero']);
		$("#txtITelefonoSala").val(laDatosPaciente['Telefono']);
		$("#txtEmailSala").val(laDatosPaciente['Email']);
		$("#txtFechaNacimiento").val(lcFechaNacimiento);
		$("#txtIngreso").val(laDatosPaciente['Ingreso']);
		$("#selEntidadSala").val(laDatosPaciente['Plan']);
		$("#txtHabitacionSala").val(laDatosPaciente['Habitacion']);
	}
}


function CargarReglas(tcTipo, tcForma, tcTitulo){
 	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'Reglas', lcCodigoEnviar: '', lcTitulo: '', datos: ''},
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
						if(loObjObl[lcKey]['CLASE']=="1"){
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
						$('#'+loObjObl[lcKey]['OBJETO']).addClass("required");
					}
				});
				ValidarReglas(tcForma, lopciones);

		} catch(err) {
			fnAlert('No se pudo realizar la busqueda de objetos obligatorios para Salas de cirugía. ');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al buscar objetos obligatorios para Salas de cirugía. ');
	});
}


function ValidarReglas(tcForma, aOptions){
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
	fnHabilitar();
}

function fnHabilitar() {
	$("#btnGuardarSala").prop("disabled", true);
}

function validarEnvia(e){
	e.preventDefault();
	
	obtenerFechaSistema(function(){
		var lcFecha = $("#txtFechaProgramadaSeleccionada").val(),
			lcHora =  $("#txtHoraSeleccionada").val();

		var ldFechaAgendaPr = new Date(lcFecha.substr(0,4),parseInt(lcFecha.substr(5,2))-1,lcFecha.substr(8,2),lcHora.substr(0,2),lcHora.substr(3,2));
		lcFecha = goFechaHoraProgramar.fecha;
		lcHora = goFechaHoraProgramar.hora.padStart(6,'0');
		var ldFechaSistemaPr = new Date(lcFecha.substr(0,4),parseInt(lcFecha.substr(4,2))-1,lcFecha.substr(6,2),lcHora.substr(0,2),lcHora.substr(2,2));
		
		if (ldFechaAgendaPr<ldFechaSistemaPr){
			$('#txtSalaSeleccionada').focus();
			fnAlert("No se puede PROGRAMAR, fecha/hora agendar es menor a fecha/hora sistema.");
			$("#btnGuardarSala").prop("disabled", true);
			return false;
		}else{
			obtenerFechaSistema(function(){
				var lcFechaSolicita = $("#txtFechaSolicitudMedico").val();
				var ldFechaSolicitud = new Date(lcFechaSolicita.substr(0,4),parseInt(lcFechaSolicita.substr(5,2))-1,lcFechaSolicita.substr(8,2));
				lcFecha = goFechaHoraProgramar.fecha;
				var ldFechaSistemaSol = new Date(lcFecha.substr(0,4),parseInt(lcFecha.substr(4,2))-1,lcFecha.substr(6,2));
				var diferenciaDias = (ldFechaSolicitud - ldFechaSistemaSol)/(1000*60*60*24);
				
				if (ldFechaSolicitud<ldFechaSistemaSol || diferenciaDias > gnDiasMaximoSolicitud){
					$("#txtFechaSolicitudMedico").val("");
					$('#txtFechaSolicitudMedico').focus();
					
					if (ldFechaSolicitud<ldFechaSistemaSol){
						fnAlert("Fecha SOLICITUD, no puede ser menor a fecha del sistema.");
					}
					
					if (diferenciaDias > gnDiasMaximoSolicitud){
						fnAlert("Fecha SOLICITUD, no puede ser mayor a " + gnDiasMaximoSolicitud + " días.");
					}
					return false;
				}else{
					if (validarDatos()) {
						fnConfirm('Esta seguro de guardar la información, una vez guardado no se podrá modificar?.', false, false, false, false,
							{
								text: 'Aceptar',
								action: function(){
									$("#btnGuardarSala").prop("disabled", true);
									enviarDatosSC();
								}
							},
							{ text: 'Cancelar' }
						);
					}
				}
			});
		}
	});
}

function validarDatos(){
	if (! $('#FormAgendaCirugia').valid()){
		ubicarObjeto('#FormAgendaCirugia');
		return false;
	}
	
	if(!validarFechas()){
		return false;
	}
	
	if(!validarListaCups()){
		return false;
	}
	if(!validarListaEquipos()){
		return false;
	}
	if(!validarTiempoCups()){
		return false;
	}
	return true;
}

function validarFechas(){
	var lnFechaNacimiento=$("#txtFechaNacimiento").val().replace(/-/g, '').length;
	if (lnFechaNacimiento!=8){
		fnAlert('Fecha nacimiento invalida, revisse por favor.', '', false, false, false, function(){
			$("#txtFechaNacimiento").val('')
			$("#txtFechaNacimiento").focus();
		});
		return false;
	}
	
	var lnFechaNacimiento=$("#txtFechaSolicitudMedico").val().replace(/-/g, '').length;
	if (lnFechaNacimiento!=8){
		fnAlert('Fecha solicitud cirujano invalida, revisse por favor.', '', false, false, false, function(){
			$("#txtFechaSolicitudMedico").val('')
			$("#txtFechaSolicitudMedico").focus();
		});
		return false;
	}
	
	return true;
}	
	
function validarListaCups(){
	var laDatos=goTablaCups.bootstrapTable('getData');
	if(laDatos.length==0) {
		fnAlert('Debe adicionar por lo menos un Procedimiento', '', false, false, false, function(){
			$("#buscarCupsSala").focus();
		});
		return false;
	} else {
		if(laDatos.length>1) {
			var lbExistePpal = false;
			$.each(laDatos, function(lnIndex, laFila){
				if (laFila.PPL) {
					lbExistePpal=true;
					return;
				}
			});
			if(!lbExistePpal) {
				fnAlert('Debe seleccionar el Procedimiento principal', '', false, false, false, function(){
					$("#buscarCupsSala").focus();
				});
				return false;
			}
		} else {
			goTablaCups.bootstrapTable('updateCell', {index:0, field:'PPL', value:true});
		}
	}
	return true;
}

function validarTiempoCups(){
	if ((parseInt($("#txtTiempoCups").val())==0 && parseInt($("#selTiempoMinutos").val())==0) ||
		($("#txtTiempoCups").val()=='' && $("#selTiempoMinutos").val()=='')
	){
		
		fnAlert('Tiempo procedimiento obligatorio, revise por favor.', '', false, false, false, function(){
			$("#txtTiempoCups").removeClass("is-valid").addClass("is-invalid");
			$("#selTiempoMinutos").removeClass("is-valid").addClass("is-invalid");
			$("#txtTiempoCups").focus();
		});
		return false;
	}
	return true;
}	

function validarCups(){
	var lcTextoCups = ($("#buscarCupsSala").val()).split('-'),
		lcCodigoCups = lcTextoCups[0].trim(),
		lcDescripcionCups = lcTextoCups.length>1? lcTextoCups[1].trim(): '';
	$("#codigoCupsSala").val(lcCodigoCups);
	$("#descripcionCupsSala").val(lcDescripcionCups);

	if (lcDescripcionCups==''){
		$('#buscarCupsSala').focus();
		fnAlert('Procedimiento no valido, revise por favor.', '', false, false, false);
		return false;
	}
	var lcCupsValidar = ($("#buscarCupsSala").val()).trim();
	if(lcCupsValidar != ''){
		if(!validarModificacionManual(datosProcedimiento[lcCupsValidar])){
			$('#buscarCupsSala').focus();
			fnAlert('Procedimiento modificado manualmente, revise por favor.', gcTitulo, false, false, false);
			return false;
		}
	}
	return true;
}


function validarListaEquipos(){
	taListaEquipos = $('#tblEquiposEspeciales').bootstrapTable('getData');
	var lnValidaLista = 0;

	if(taListaEquipos != ''){
		$.each(taListaEquipos, function(lcKey, loSeleccion) {
			if (loSeleccion.SELECCION==true){
				lnValidaLista = 1;
				return;
			}
		});
	}
	if (lnValidaLista==0){
		$('#tblEquiposEspeciales').focus();
		fnAlert('No ha seleccionado ningún equipo especial, revise por favor.', gcTitulo, false, false, false);
		return false;
	}
	return true;
}


function validarModificacionManual(taListadoValidar){
	if(taListadoValidar !=''){
		if(taListadoValidar===undefined){
			return false;
		}
		return true;
	}
}


function enviarDatosSC(){
	var loData = obtenerDatos();

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'Validar', datos:loData},
		dataType: "json"
	})
	.done(function(oDataDev) {
		if(oDataDev['Valido']){
			$("#btnGuardarSala").prop("disabled", true);
			fnAlert('Proceso terminado.');
			volverAgenda();
		} else {
			$("#btnGuardarSala").prop("disabled", false);
			if(oDataDev['Objeto']!=''){
				focusObjeto(oDataDev['Objeto']);
			}
			fnAlert(oDataDev['Mensaje'], 'Salas de cirugía.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Ocurrió un error al guardar los datos de Salas de cirugía', 'Salas de cirugía');
	});
}


function obtenerDatos(){
	return {
		Datospaciente: $('#FormAgendaCirugia').serializeAll(),
		Procedimientos: goTablaCups.bootstrapTable('getData'),
		Observaciones: $("#txtObservacionesSala").val(),
		EquipoEspeciales: goTablaEquipos.bootstrapTable('getData')
	};
}


function ubicarObjeto(toForma, tcObjeto){
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

function focusObjeto(tcIdObjeto){
	if (!(typeof tcIdObjeto === 'string')) return false;
	var loObjeto = $("#"+tcIdObjeto);
	activarTab(loObjeto);
	loObjeto.focus();
}

function activarTab(toObjeto){
	var loTab = toObjeto.closest(".tab-pane");
	$("#"+loTab.prop("aria-labelledby")).tab("show");
}

function cargarMedicos(id, lcTipo, mensaje, lcCodigoEnvia) {
	var loSelect = $('#'+id);
	lnRegistros = 0;
	loSelect.empty();
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'medicosespecialidad', lcCodigoEnviar: lcCodigoEnvia, lcTitulo: '', datos: ''},
		dataType: "json",
	})
	.done(function( loDatos ) {
		try {
			if (loDatos.error == ''){
				loSelect.append('<option value=""></option>');
				$.each(loDatos.MEDICO, function( lcKey, loTipo ) {
					lnRegistros++;
					loSelect.append('<option value="' + lcKey + '">' + loTipo.desc + '</option>');
				});

				if (lnRegistros>0){
					$('#selMedicoPrograma').prop("disabled",false);
				}else{
					$('#selMedicoPrograma').prop("disabled",true);
					fnAlert("No existen médicos registrados a esta especialidad, revise por favor.");
				}

			} else {
				fnAlert(loDatos.error + '');
			}
		} catch(err) {
			fnAlert('No se pudo realizar la busqueda de ' + mensaje + '.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al buscar ' + mensaje + '.');
	});
	return this;
}


function volverAgenda() {
	formPostTemp('modulo-programacion-salas&q=programacionSalas', gaFiltroAgenda, false);
}


function IniciarListados(){
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'listados', lcCodigoEnviar: '', lcTitulo: '', datos: ''},
		dataType: "json"
	})
	.done(function(loDatos) {
		gcListadoEquiposGuardados = gcEquiposEspeciales;
		try {
			if (loDatos.error == ''){
				//	PLANES
				loSelect = $("#selEntidadSala");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loDatos.planes, function( lcKey, loTipo ) {
					var lcOption = '<option value="' + loTipo.PLNCON + '">' + loTipo.DSCCON + '</option>';
					loSelect.append(lcOption);
				});
				loSelect.val(gcEntidad);

				//	ORIGEN
				$("#selOrigenSala").val('');
				loSelect = $("#selOrigenSala");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loDatos.origensala, function( lcKey, loTipo ) {
					var lcOption = '<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>';
					loSelect.append(lcOption);
				});
				loSelect.val(gcOrigen);

				//	ESPECIALIDADES SALA
				loSelect = $("#selEspecialidadMedico");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loDatos.especialidades, function( lcKey, loTipo ) {
					var lcOption = '<option value="' + loTipo.CODESP + '">' + loTipo.DESESP + '</option>';
					loSelect.append(lcOption);
				});
				loSelect.val(gcEspecialidadSolicita);

				//	TIPO PROCEDIMIENTO
				loSelect = $("#selTipoProcedimientoSala");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loDatos.tipoprocedimientosala, function( lcKey, loTipo ) {
					var lcOption = '<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>';
					loSelect.append(lcOption);
				});
				loSelect.val(gcTipoProcedimiento);

				//	LATERALIDAD
				loSelect = $("#selLateralidadSala");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loDatos.lateralidadsala, function( lcKey, loTipo ) {
					var lcOption = '<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>';
					loSelect.append(lcOption);
				});
				loSelect.val(gcLateralidad);

				//	TIPO ANESTESIA
				loSelect = $("#selTipoAnestesiaSala");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loDatos.tipoanestesia, function( lcKey, loTipo ) {
					var lcOption = '<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>';
					loSelect.append(lcOption);
				});
				loSelect.val(gcTipoAnestesia);

				//	DISPOSITIVOS CARDIACOS
				loSelect = $("#selDispositivosCardiacoSala");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loDatos.dispositivoscardiaco, function( lcKey, loTipo ) {
					var lcOption = '<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>';
					loSelect.append(lcOption);
				});
				loSelect.val(gcDispositivoCardiaca);

				//	REQUERIMIENTOS ESPECIALES
				loSelect = $("#selRequerimientos");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loDatos.requerimientosespec, function( lcKey, loTipo ) {
					var lcOption = '<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>';
					loSelect.append(lcOption);
				});
				loSelect.val(gcRequerimientosEspec);

				//	VIAS DE ACCESO
				loSelect = $("#selViaAcceso");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loDatos.viasacceso, function( lcKey, loTipo ) {
					var lcOption = '<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>';
					loSelect.append(lcOption);
				});
				loSelect.val(gcViaAcceso);
				
				//	ANESTESIOLOGOS
				loSelect = $("#selAnestesiologo");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loDatos.anestesiologos, function( lcKey, loTipo ) {
					var lcOption = '<option value="' + loTipo.REGISTRO + '">' + loTipo.NOMBRE + '</option>';
					loSelect.append(lcOption);
				});
				loSelect.val(gcAnestesiologo);
				
				//	GENEROS
				loSelect = $("#txtGeneroSala");
				loSelect.empty();
				loSelect.append('<option value=""></option>');
				$.each(loDatos.generospacientes, function( lcKey, loTipo ) {
					var lcOption = '<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>';
					loSelect.append(lcOption);
				});
				loSelect.val(gcGeneroPaciente);

				//	EQUIPOS ESPECIALES
				$('#tblEquiposEspeciales').bootstrapTable('refreshOptions', {data: {}});
				$.each(loDatos.equiposespeciales, function( lcKey, loTipo ) {
					adicionarEquiposEspeciales(loTipo);
				});
			} else {
				fnAlert(loDatos.error)
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consultar listados.')
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTablaEquipos.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar listados.');
	});
}

function adicionarEquiposEspeciales(camposRegistro){
	llEquipo = false;
	lcCodigoEquipo = "-"+camposRegistro.CODIGO+"-";

	if (gcEquiposEspeciales=!''){
		lnBuscarEquipo = gcListadoEquiposGuardados.indexOf(lcCodigoEquipo);
		llEquipo = (lnBuscarEquipo>=0 ? true : false);
	}

	goTablaEquipos.bootstrapTable('insertRow', {
		index: 1,
		row: {
			SELECCION: llEquipo,
			CODIGO: camposRegistro.CODIGO,
			DESCRIPCION: camposRegistro.DESCRIPCION
		}
	});
}

function actualizaEquipos(){
	var laListaEquipos = $('#tblEquiposEspeciales').bootstrapTable('getData');
	$.each(laListaEquipos, function( lnIndex, loListaEquipos ) {
		$('#tblEquiposEspeciales').bootstrapTable('updateRow', {
			index: lnIndex,
			row: {
				SELECCION: false
			}
		});
	});
}

function activarCampos(tlValor){
	var lcLista = '#txtPrimerNombre,#txtSegundoNombre,#txtPrimerApellido,#txtSegundoApellido,#txtFechaNacimiento,#txtGeneroSala,#txtITelefonoSala,#txtEmailSala,#selEntidadSala';
	$(lcLista).prop("disabled",tlValor);
	$('#txtPrimerNombre').focus();
}

function habilitar(tlValor){
 	$('#selTipDocSala, #txtNumDocSala').prop("disabled",tlValor);
}

function inactivarCampos(tlValor){
	var lcLista = '#txtITelefonoSala,#txtEmailSala,#selEntidadSala,#txtFechaSolicitudMedico,#selOrigenSala,#selEspecialidadMedico,#buscarCupsSala,'
				+ '#selTipoProcedimientoSala,#txtTiempoCups,#selTiempoMinutos,#selLateralidadSala,#selTipoAnestesiaSala,'
				+ '#selCirugiaContaminada,#selReintervencion,#txtEscalaMents,#selPruebaCovid19,#selDispositivosCardiacoSala,#selRequerimientos,'
				+ '#selViaAcceso,#selReservaSangre,#selAyudanteQuirurgico,#txtObservacionesSala,#selAutorizada,#btnAdicionarCup,#btnGuardarSala';
	$(lcLista).prop("disabled",!tlValor);
	
	if ($("#txtPrimerNombre").val()!='' && $("#selOrigenSala").val()!=''){
		$('#selTipDocSala').prop("disabled",true);
		$('#txtNumDocSala').prop("disabled",true);
	}
	if ($("#txtPrimerNombre").val()!=''){
		$('#txtITelefonoSala').focus();
	}else{
		$('#txtPrimerNombre').focus();	
	}	
}

function limpiarDatos(){
	$("#FormAgendaCirugia").get(0).reset();
	$("#FormObservacionesCirugia").get(0).reset();
	actualizaEquipos();
	habilitar(false);
	inactivarCampos(false);
	activarCampos(true);
	$('#selMedicoPrograma').prop("disabled",true);
	$('#selTipDocSala').focus();
}

function blanquearCampos(){
	$('#txtFechaSolicitudMedico').val('');
}

function cargarListadosAutocompletar(tcTipo,tcCampoUbica){
	$.ajax({
		type: "POST",
		url: "vista-comun/ajax/Autocompletar.php",
		data: {
				tipoDato: 'Procedimientos',
				otros: {filtro: '', genero: ''},
			},
		
		dataType: "json"
	})
	.done(function(toDatos) {
		try {
			if (toDatos.error=='') {
				var loFunction = false;
				datosProcedimiento = {};
				$.each(toDatos.datos, function(lnIndex, laProcedimiento){
					datosProcedimiento[laProcedimiento.CODIGO + ' - ' + laProcedimiento.DESCRIPCION]=lnIndex;
				});
				autocompletar('#buscarCupsSala',datosProcedimiento,loFunction);
				adicionarCupsAlmacenados();
			} else {
				fnAlert(toDatos.Error);
			}
		} catch(err) {
			fnAlert('No se pudo realizar la busqueda de Listado procedimientos.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al buscar Listado procedimientos.');
	});
}


function autocompletar(tcCampoListado,tcSource,tcFuncionSelecciona){
	$(tcCampoListado).autocomplete({
		source: tcSource,
		maximumItems: 30,
		highlightClass: 'text-danger',
		onSelectItem: tcFuncionSelecciona
	});
}


function IniciarTablaEquiposEspec(){
	goTablaEquipos.bootstrapTable({
		classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
		theadClasses: 'thead-light',
		checkboxHeader: false,
		clicktoselect: 'true',
		locale: 'es-ES',
		undefinedText: 'N/A',
		toolbar: '#toolBarLst',
		height: '750',
		search: false,
		sortName: 'DESCRIPCION',
		pagination: false,
		iconSize: 'sm',
		columns: [
		{
			title: '',
			field: 'SELECCION',
			checkbox: 'false',
			width: 5, widthUnit: "%",
			halign: 'center',
			align: 'center',
			visible: (gnConsecutivo==0 ? true: false)
		},{
			title: '',
			field: 'VERIFICA',
			formatter: formatoCheck,
			width: 5, widthUnit: "%",
			halign: 'center',
			align: 'center',
			visible: (gnConsecutivo>0 ? true: false)
		},
		{
			title: 'Equipos especiales',
			field: 'DESCRIPCION',
			width: 95, widthUnit: "%",
			halign: 'center',
			align: 'left'
		}
		]
	});
}


function formatoCheck(value, row, index) {
	if (gnConsecutivo>0){
		if (row['SELECCION']==true){
			return [
				'<a class="cancelar" href="javascript:void(0)" title="Cancelar agenda">',
				'<i class="fas fa-check-square" style="color:#9B9B9B"></i>',
				'</a>'
			].join('')
		}else{
			return [
				'<a class="cancelar" href="javascript:void(0)" title="Cancelar agenda">',
				'<i class="far fa-square" style="color:#9B9B9B"></i>',
				'</a>'
			].join('')
		}
    }
}


function IniciarTablaCups() {
	goTablaCups.bootstrapTable({
		classes: 'table table-bordered table-hover table-sm table-responsive-sm', // table-striped
		theadClasses: 'thead-light',
		locale: 'es-ES',
		undefinedText: '',
		toolbar: '#toolBarLstIntrExam',
		height: '200',
		pagination: false,
		columns: [
			{
				title: 'Principal',
				field: 'PPL',
				radio: true
			},{
				title: 'CUP',
				field: 'CUP'
			},{
				title: 'Procedimiento',
				field: 'DSC'
			},{
				title: 'Acciones',
				align: 'center',
				formatter: '<a class="eliminaCup" href="javascript:void(0)" title="Eliminar CUP"><i class="fas fa-trash" style="color:#527DF0"></i></a>',
				events: eventoAccionesCups
			}
		],
	});
}


var eventoAccionesCups = {
	'click .eliminaCup': function(e, tcValor, toFila, tnIndice) {
		goTablaCups.bootstrapTable('remove', {field:'CUP', values:[toFila.CUP]});
	}
}


function adicionarCup(){
	if(validarCups()){
		var lcCup = $("#codigoCupsSala").val(),
			lcDsc = $("#descripcionCupsSala").val(),
			laCups = [];
		$.each(goTablaCups.bootstrapTable('getData'), function(lnIndex, laFila){
			laCups.push(laFila.CUP);
		});
		if ($.inArray(lcCup, laCups)<0) {
			var lbEsPrimero = laCups.length==0;
			goTablaCups.bootstrapTable('append', [{PPL:lbEsPrimero, CUP: lcCup, DSC: lcDsc}]);
		}
	}
	$("#buscarCupsSala,#codigoCupsSala,#descripcionCupsSala").val("");
	$("#buscarCupsSala").focus();
}


function adicionarCupsAlmacenados(){
	if(gcCups.length>0){
		var laCups = gcCups.split('|');
		$.each(laCups, function(lnIndex, lcCup){
			var laProc = lcCup.split('~')
				lcDscCup = '';
			$.each(datosProcedimiento, function(lcDsc, lcNum){
				if (laProc[0]==lcDsc.substr(0, laProc[0].length)) {
					lcDscCup = lcDsc.substr(laProc[0].length+2);
					return false;
				}
			});
			goTablaCups.bootstrapTable('append',[{PPL:laProc[1]=='1', CUP: laProc[0], DSC: lcDscCup}]);
			$(".eliminaCup").remove();
		});
	}
}
