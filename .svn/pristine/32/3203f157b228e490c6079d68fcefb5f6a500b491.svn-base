var oMedicamentosSuspendidos = {
	ventana: $("#divMedicamentosSuspendidosModal"),
	gotablaSuspendidos: $('#tblMedicamentosSuspendidos'),
	
	inicializar: function()
	{
		this.iniciarTablaSuspendidos();
		this.consultar(0);
	},
	
	iniciarTablaSuspendidos: function(){
		oMedicamentosSuspendidos.gotablaSuspendidos.bootstrapTable({
			classes: 'table table-bordered table-hover table-sm table-responsive-sm', // table-striped
			theadClasses: 'thead-light',
			locale: 'es-ES',
			undefinedText: '',
			toolbar: '#toolBarLstIntrExam',
			height: '400',
			pagination: false,
			columns: [
				{
					title: 'Medicamento',
					formatter: function(tnValor, toFila) {
						return toFila.MEDICAMENTO+' - '+toFila.DESCRIPCION_MEDICAMENTO;
					},
					halign: 'center', align: 'left'
				},
				{
					title: 'Dosis',
					formatter: function(tnValor, toFila) {
						return toFila.CANTIDAD_DOSIS+' - '+toFila.DESC_UNIDAD_DOSIS;
					},
					halign: 'center', align: 'left'
				},
				{
					title: 'Frecuencia',
					formatter: function(tnValor, toFila) {
						return toFila.CANTIDAD_FRECUENCIA+' - '+toFila.DESC_UNIDAD_FRECUENCIA;
					},
					halign: 'center', align: 'left'
				},
				{
					title: 'Vía', field: 'DESCR_VIA', halign: 'center', align: 'left'
				},
				{
					title: 'Fecha inicio antibiótico', 
					formatter: function(tnValor, toFila) {
						return strNumAFecha(toFila.FECHA_INICIO_ANTIBIOTICO, '/');
					},
					halign: 'center', align: 'center'
				},
				{
					title: 'Fecha/hora suspendido',
					formatter: function(tnValor, toFila) {
						return strNumAFecha(toFila.FECHA_SUSPENDIDO, '/')+' '+strNumAHora(toFila.HORA_SUSPENDIDO);
					},
					halign: 'center', align: 'center'
				}
			],
		});
	},
	
	consultar: function(tnIngreso)
	{
		let lnIngreso=tnIngreso===0 ? aDatosIngreso['nIngreso'] : tnIngreso;
		this.gotablaSuspendidos.bootstrapTable('removeAll');
		this.gotablaSuspendidos.bootstrapTable('showLoading');
		
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/modalMedicamentosSuspendidos',
			data: {
				lnIngreso: lnIngreso
			},
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.error == ''){
					oMedicamentosSuspendidos.gotablaSuspendidos.bootstrapTable('refreshOptions', {data: loDatos.TIPOS});
					if (tnIngreso>0){
						$("#divMedicamentosSuspendidosModal").modal('show');
					}
				} else {
					fnAlert(loDatos.error)
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta de medicamentos suspendidos.')
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			oAntecedentesConsulta.tabla.bootstrapTable('hideLoading');
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar medicamentos suspendidos.');
		});
	},
	
	mostrar: function(tnIngreso)
	{
		if (tnIngreso>0){
			oMedicamentosSuspendidos.iniciarTablaSuspendidos();
			oMedicamentosSuspendidos.consultar(tnIngreso);
		}else{
			$("#divMedicamentosSuspendidosModal").modal('show');
		}
	},
	
	ocultar: function()
	{
		$("#divMedicamentosSuspendidosModal").modal('hide');
	}
}