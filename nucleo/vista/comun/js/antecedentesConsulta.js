var oAntecedentesConsulta = {
	ventana: $("#divAntecedentesConsultaModal"),
	tabla: $("#tblAntecedentesConsulta"),
	sinConsultar: true,

	inicializar: function()
	{
		this.tabla.bootstrapTable({
			classes: 'table table-bordered table-hover table-sm table-responsive-sm', // table-striped
			theadClasses: 'thead-light',
			locale: 'es-ES',
			undefinedText: '',
			toolbar: '#toolBarLstIntrExam',
			height: '400',
			pagination: false,
			columns: [
				{
					title: 'Fecha y Hora',
					formatter: function(tnValor, toFila) {
						return strNumAFecha(toFila.FCRAPA, '/')+' '+strNumAHora(toFila.HCRAPA);
					}
				},
				{
					title: 'Antecedentes',
					formatter: function(tnValor, toFila) {
						return (toFila.APRAPA==4? toFila.DAPAPA+' ': '')+toFila.DASAPA;
					}
				},
				{
					title: 'Médico',
					field: 'NUSAPA'
				},
				{
					title: 'Observaciones',
					field: 'DESAPA'
				}
			],
		});
	},
	consultar: function()
	{
		this.tabla.bootstrapTable('removeAll');
		this.tabla.bootstrapTable('showLoading');
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/antecedentesConsulta',
			data: {
				tipdoc: aDatosIngreso.cTipId,
				numdoc: aDatosIngreso.nNumId
			},
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.error == ''){
					oAntecedentesConsulta.tabla.bootstrapTable('refreshOptions', {data: loDatos.datos});
					this.sinConsultar = false;
				} else {
					fnAlert(loDatos.error)
				}
			} catch(err) {
				fnAlert('No se pudo realizar la consulta de estados.')
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			oAntecedentesConsulta.tabla.bootstrapTable('hideLoading');
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar estados.');
		});
	},
	mostrar: function()
	{
		if (this.sinConsultar) this.consultar();
		$("#divAntecedentesConsultaModal").modal('show');
	},
	ocultar: function()
	{
		$("#divAntecedentesConsultaModal").modal('hide');
	}
}