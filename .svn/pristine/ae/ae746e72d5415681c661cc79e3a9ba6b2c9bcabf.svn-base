var gcUrlajax = "vista-pacientes-correo/ajax/ajax",
	gcVel="fast";

$(function() {
	activarFiltros(false);
	IniciarListas();

	$("#btnBuscar").on("click", function(e){
		e.preventDefault();
		buscarPaciente();
	});
	$("#btnLimpiar").on("click", function(e){
		e.preventDefault();
		limpiar("TODO");
	});
	$("#btnActualizar").on("click", function(e){
		e.preventDefault();
		actulizarPaciente();
	});
});

function buscarPaciente()
{
	limpiar("");
	activarFiltros(false);

	var lcTipDoc = $("#selTipDoc").val(),
		lnNumDoc = $("#txtNumDoc").val();
	if (lcTipDoc.length==0) {
		$("#selTipDoc").focus();
		fnAlert("Tipo de documento obligatorio");
		return;
	}
	if (lnNumDoc<=0) {
		$("#txtNumDoc").focus();
		fnAlert("Número de documento obligatorio");
		return;
	}

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {
			accion: 'consulta',
			tipodoc: lcTipDoc,
			numerodoc: lnNumDoc
		},
		dataType: "json",
		success: function(toDatos) {
			if (toDatos.error.length == 0) {
				if (toDatos.datos.apellido1.length>0 && toDatos.datos.nombre1.length>0) {
					$("#divPaciente").html("<b>Paciente</b>: "+toDatos.datos.nombre1+" "+toDatos.datos.nombre2+" "+toDatos.datos.apellido1+" "+toDatos.datos.apellido2);
					$("#frmCorreo").show(gcVel);
					$("#txtCorreoAnterior").val(toDatos.datos.correo);
					$("#txtCorreoActual").focus();

				} else {
					fnAlert("Paciente no se encuentra.");
					activarFiltros(true);
				}

			} else {
				fnAlert(toDatos.error);
				activarFiltros(true);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.error(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar paciente.');
			activarFiltros(true);
		}
	});
}

function actulizarPaciente()
{
	var lcTipDoc = $("#selTipDoc").val(),
		lnNumDoc = $("#txtNumDoc").val(),
		lcCorreoAntes = $("#txtCorreoAnterior").val().trim(),
		lcCorreo = $("#txtCorreoActual").val().trim();
	if (lcCorreo.length==0) {
		$("#txtCorreoActual").focus();
		fnAlert("Correo nuevo es obligatorio.");
		return;
	}
	if (lcCorreo==lcCorreoAntes){
		$("#txtCorreoActual").focus();
		fnAlert("Correo nuevo es igual al correo actual del paciente.");
		return;
	}

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {
			accion: 'guardar',
			tipodoc: lcTipDoc,
			numerodoc: lnNumDoc,
			correo: lcCorreo,
		},
		dataType: "json",
		success: function(toDatos) {
			if (toDatos.error.length == 0) {
				if (toDatos.data.valida) {
					fnDialog('Se actualizó exitosamente el correo electrónico.', 'Actualización correo', 'fas fa-check-circle');
					limpiar('TODO');
				} else {
					fnAlert(toDatos.data.error);
					activarFiltros(true);
				}
			} else {
				fnAlert(toDatos.error);
				activarFiltros(true);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.error(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar paciente.');
			activarFiltros(true);
		}
	});
}

function IniciarListas()
{
	$("#selTipDoc").tiposDocumentos({horti: "1", fnpos: ()=>{
		activarFiltros(true);
		$("#selTipDoc").focus();
	}});
}

function activarFiltros(tcActivar)
{
	$("#selTipDoc,#txtNumDoc,#btnBuscar").attr("disabled",!tcActivar);
}

function limpiar(tcNivel)
{
	if (tcNivel=="TODO") {
		$("#selTipDoc,#txtNumDoc").val("");
		activarFiltros(true);
	}
	$("#divPaciente").html("");
	$("#frmCorreo").hide(gcVel);
	$("#txtCorreoAnterior,#txtCorreoActual").val("");
}
