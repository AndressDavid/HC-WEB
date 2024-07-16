var oEnfermeriaOrdMedica = {
	gcUrlAjax: 'vista-ordenes-medicas/ajax/ajax',
	lcTitulo: 'Enfermeria ordenes médicas',
	gcUltimosDatosEnfermeria:'',
	lcFormaError:'',
	lcObjetoError: '',
	lcMensajeError: '',

	inicializar: function(){
		this.consultarDatosEnfermeria();
		$("#tabOptOrdMedEnfermeria").on("click", function () {
			if ($("#btnGuardarOrdenesMedicas").prop("disabled")==false){
				oEnfermeriaOrdMedica.observacionEnfermeria();
			}	
		});
	},
	
	observacionEnfermeria: function() {
		var lcDatosOrdenEnfermeria=$('#txtOrdMedEnfermeria').val();
		if (lcDatosOrdenEnfermeria==='' && oEnfermeriaOrdMedica.gcUltimosDatosEnfermeria!=''){
			fnConfirm('Desea ver la última orden de enfermería?', oEnfermeriaOrdMedica.lcTitulo, false, false, 'medium',
				{ 
					text: 'Si',
					action: function(){
						$('#txtOrdMedEnfermeria').val(oEnfermeriaOrdMedica.gcUltimosDatosEnfermeria);
						$('#txtOrdMedEnfermeria').focus();
					}
				},
				{ text: 'No',
					action: function(){
						$('#txtOrdMedEnfermeria').val('');
						$('#txtOrdMedEnfermeria').focus();
					}
				}
			);
		}	
	},
	
	consultarDatosEnfermeria: function()
	{
		$.ajax({
			type: "POST",
			url: oEnfermeriaOrdMedica.gcUrlAjax,
			data: {accion: 'datosEnfermeria', lnIngreso: aDatosIngreso.nIngreso},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					oEnfermeriaOrdMedica.gcUltimosDatosEnfermeria=toDatos.TIPOS;
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consultar Datos Enfermeria.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consultar Datos Enfermeria.");
		});
	},
	
	actualizaDatosOxigeno: function(taDatosOxigeno,tcSuspendeOxigeno) {
		$('#txtOrdMedDatosOxigeno').val('');
		if (tcSuspendeOxigeno=='S'){
			$('#txtOrdMedDatosOxigeno').val('Suspender Oxígeno');
		}
		
		lcPacNecistaOxigeno = taDatosOxigeno.PacienteNecesita;
		if (lcPacNecistaOxigeno=='Si'){
			lcTextoOxigeno = taDatosOxigeno.Cups + ' - Dosis: ' + taDatosOxigeno.Dosis + ' L/min por 24 horas ' + String.fromCharCode(13) + taDatosOxigeno.Observacion;
			$('#txtOrdMedDatosOxigeno').val(lcTextoOxigeno);
		}	
	},
}	