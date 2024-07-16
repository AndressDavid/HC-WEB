var oModalPlanesPaciente = {
	lcTitulo : 'Planes paciente',
	fnEjecutar: false,
	
	inicializar: function()
	{
		this.tituloPlanes();
		oPlanPaciente.planesPaciente($('#selPlanOrdAmb'),aDatosIngreso.cTipId,aDatosIngreso.nNumId);
		oPlanPaciente.planesPaciente($('#selPlanPaciente'),aDatosIngreso.cTipId,aDatosIngreso.nNumId);
		oPlanPaciente.planesPaciente($('#selPlanPacienteOA'),aDatosIngreso.cTipId,aDatosIngreso.nNumId);
		$('#btnAceptarPlan').on('click', this.validarPlanPaciente);
	},
	
	validarPlanPaciente: function () {
		var lcPlan = $("#selPlanPacienteOA").val();

		if (lcPlan==''){
			$('#selPlanPacienteOA').focus();
			fnAlert('Plan obligatorio, revise por favor.');
			return false;
		}
		oAmbulatorio.asignaPlanPaciente(lcPlan);
		$("#divPlanesPaciente").modal("hide");
	},
	
	tituloPlanes: function()
	{
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/modalPlanesPaciente.php',
			data: {accion: 'textotitulo'},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					$('#titPlanPaciente').html(loTipos.TIPOS);
					$('#btnAceptarPlan').attr("disabled", false);
				} else {
					fnAlert(loTipos.error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de titulo planes paciente.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar titulo planes paciente.');
		});
	},
	
	cargarPlanes: function(taPlanesPaciente)
	{
		var loSelect = $('#selPlanPacienteOA');
 		$.each(taPlanesPaciente, function( lcKey, loTipo ) {
			loSelect.append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
		});
	},
	
	mostrar: function(tfEjecutar)
	{
		$("#divPlanesPaciente").modal('show');
		oModalPlanesPaciente.fnEjecutar = tfEjecutar;
	},
	
	ocultar: function()
	{
		$("#divPlanesPaciente").modal('hide');
		
		if (typeof oModalPlanesPaciente.fnEjecutar==='function'){
			oModalPlanesPaciente.fnEjecutar();
		}
	},
}