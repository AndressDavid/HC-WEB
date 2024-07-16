var oModalObservaciones= {
	lcTitulo: 'Observaciones Urgencias',
	nIngreso: 0,
	cPaciente: '',

	inicializar: function()
	{
		$('#FormObservaciones').validate({
			rules: {
				edtNuevaObserva: "required",
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
		$('#btnGuardaObs').on('click', oModalObservaciones.validarEnviar);
	},

	mostrar: function(tnIngreso, tcPaciente)
	{
		oModalObservaciones.nIngreso = tnIngreso;
		oModalObservaciones.cPaciente = tcPaciente;
		oModalObservaciones.consultaAnteriores();
		$("#divPacObsUrg").html("<b>"+tnIngreso+" - "+tcPaciente+"</b>");
		$("#divObservaciones").modal("toggle");
	},

	consultaAnteriores: function()
	{
		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/modalObservaciones.php",
			data: {TipoConsulta: 'Urgencias', ingreso: oModalObservaciones.nIngreso},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.DATOS != []) {
						$("#edtObservacionesAnt").val(toDatos.DATOS);
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda para Observaciones.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar Observaciones Anteriores.");
		});
	},

	validarEnviar: function()
	{
		if (oModalObservaciones.validacion()) {
			fnConfirm('Si guarda los cambios, <b>NO</b> podra modificarlos después.<br><b>¿Está seguro que desea Guardar los datos?</b>', 'OBSERVACIONES URGENCIAS', false, false, false,
				{
					text: 'Si',
					action: function(){
						oModalObservaciones.enviarDatos();
					}
				},
				{
					text: 'No'
				}
			)
		}
	},

	enviarDatos: function(toEnviar)
	{
		var loEnviar = {
			'Ingreso': oModalObservaciones.nIngreso,
			'Observaciones': oModalObservaciones.obtenerDatos()
		};
		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/modalObservaciones.php",
			data: {TipoConsulta: 'Verificar', datos: loEnviar},
			dataType: "json"
		})
		.done(function(oDataDev) {
			if(oDataDev['Valido']){
				fnInformation(oDataDev['Mensaje'], 'Observaciones');
				oModalObservaciones.finalizarObs();
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Ocurrió un error al guardar las Observaciones.");
		});
	},

	validacion: function()
	{
		var lbValido = true;
		if (!$('#FormObservaciones').valid()){
			$('#edtNuevaObserva').focus();
			lbValido = false;
		}
		return lbValido;
	},

	ocultar: function()
	{
		$("#divObservaciones").modal('hide');
	},

	obtenerDatos: function() {
		return ($('#edtNuevaObserva').val());
	},

	finalizarObs: function(){
		$('#edtNuevaObserva').val('');
		oModalObservaciones.ocultar();
	}
}