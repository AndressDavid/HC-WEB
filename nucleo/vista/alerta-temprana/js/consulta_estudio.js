$(function() {
	// Controles datepicker
	$('#filtroIngreso .input-group.date').datepicker({
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

	$("#btnExportar").on('click', fnExpotar);
});

// Exportar datos
function fnExpotar()
{
	var ldFeDesde = $('#txtFechaDesde').val(),
		ldFeHasta = $('#txtFechaHasta').val();
	if (!ldFeDesde || !ldFeHasta){
		alert('Debe indicar rango de fechas para exportar');
		return false;
	}
	var laEnvio={
			accion:'exportarEstudio',
			fdesde:$('#txtFechaDesde').val(),
			fhasta:$('#txtFechaHasta').val(),
		};

	var loNewForm = $('<form>', {
		'action': 'nucleo/vista/alerta-temprana/consultas_json.php',
		'method': 'POST',
		'target': '_blank'
	});
	$(document.body).append(loNewForm);
	$.each(laEnvio, function(lcNombre, lcValor){
		loNewForm.append($('<input>', {'type':'hidden', 'name':lcNombre, 'value':lcValor}));
	});
	loNewForm.submit();
	loNewForm.remove();
}
