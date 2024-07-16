var oDxDescartados = {
	gotablaDescarte: $('#tblDxDescarte'),
	
	inicializar: function()
	{
		//oDxDescartados.iniciarTablaDescarte();
		$("#divDiagnosticosDescarteModal").modal('hide');
		oDxDescartados.consultar(0);
		oDxDescartados.ocultar();

	},
	
	iniciarTablaDescarte: function(){
		oDxDescartados.gotablaDescarte.bootstrapTable({
			classes: 'table table-bordered table-hover table-sm table-responsive-sm', 
			theadClasses: 'thead-light',
			locale: 'es-ES',
			undefinedText: '',
			toolbar: '#toolBarLstIntrExam',
			height: '400',
			pagination: false,
			columns: [
				{
					title: 'Código',
					formatter: function(tnValor, toFila) {
						return toFila.DIAGNOSTICO;
					},
					halign: 'center', align: 'center'
				},
				{
					title: 'Descripción',
					formatter: function(tnValor, toFila) {
						return toFila.DESCRIPCION_CIE;
					},
					halign: 'center', align: 'left'
				},
				{
					title: 'Tipo',
					formatter: function(tnValor, toFila) {
						return toFila.DESCRIPCION_TIPO;
					},
					halign: 'center', align: 'center'
				},
				{
					title: 'Clase', field: 'DESCRIPCION_CLASE', halign: 'center', align: 'center'
				},
				{
					title: 'Descarte', field: 'TIPO_DESCARTE', halign: 'center', align: 'center'
				},
				{
					title: 'Fecha/hora descartado',
					formatter: function(tnValor, toFila) {
						return strNumAFecha(toFila.FECEDC, '/')+' '+strNumAHora(toFila.HOREDC);
					},
					halign: 'center', align: 'center'
				}
			],
		});
	},
	
	consultar: function(tnIngreso)
	{
		let lnIngreso=tnIngreso===0 ? aDatosIngreso['nIngreso'] : tnIngreso;
		this.gotablaDescarte.bootstrapTable('removeAll');
		this.gotablaDescarte.bootstrapTable('showLoading');

		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/modalDxDescartados',
			data: {
				lnIngreso: lnIngreso
			},
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.error == ''){
					oDxDescartados.gotablaDescarte.bootstrapTable('refreshOptions', {data: loDatos.TIPOS});
					if (tnIngreso>0){
						$("#divDiagnosticosDescarteModal").modal('show');
						oDiagnosticos.llDxObliga = (aDatosIngreso.DxPpal[0].OBLIGA==1?true:false);
					}else{
						$("#divDiagnosticosDescarteModal").modal('hide');
					}

				} else {
					fnAlert(loDatos.error)
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta de Diagnosticos Descartados.')
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar Diagnosticos Descartados.');
		});
	},
	
	mostrar: function(tnIngreso)
	{
		if (tnIngreso>0){
			oDxDescartados.iniciarTablaDescarte();
			oDxDescartados.consultar(tnIngreso);
		}else{
			$("#divDiagnosticosDescarteModal").modal('show');
		}
	},
	
	ocultar: function()
	{
		$("#divDiagnosticosDescarteModal").modal('hide');
	}
}