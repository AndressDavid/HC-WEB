var oExamenFisico = {
	lcTitulo : 'Examen Físico ',
	lcMensajeError: '', lcObjetoError: '',
	gnMinPesoRecienNacido: 0, gnMaxPesoRecienNacido: 0,

	inicializar: function()
	{
		this.cargarparametros();
		$('#selNivelCE').tiposNivelC({});
		$('#txtPeso').change(this.calcularIMC);
		$('#txtTalla').change(this.calcularIMC);
		$('#txtPeso').change(this.calcularSC);
		$('#txtTalla').change(this.calcularSC);
		$('#chkTAS').on("change",function(){oExamenFisico.validarAusente('#lblTAS','#txtTAS','#chkTAS')});
		$('#chkTAD').on("change",function(){oExamenFisico.validarAusente('#lblTAD','#txtTAD','#chkTAD')});
		$('#chkFC').on("change",function(){oExamenFisico.validarAusente('#lblFC','#txtFC','#chkFC')});
		$('#chkFR').on("change",function(){oExamenFisico.validarAusente('#lblFR','#txtFR','#chkFR')});
		$('#txtTAS,#txtFC').on("change",function(){oExamenFisico.actualizaEscala()});
		$('#FormExamenN').on('submit', function(e){e.preventDefault();});
	},

	cargarparametros: function()
	{
		$.ajax({
			type: "POST",
			url: "vista-historiaclinica/ajax/HistoriaClinica.php",
			data: {lcTipo: 'rangospesoreciennacido'},
			dataType: "json"
		})
		.done(function(loDatos) {
			if(loDatos.error.length>0){
				fnAlert(loDatos.error);
				return;
			}
			try {
				oExamenFisico.gnMinPesoRecienNacido=parseFloat(loDatos.datos.DE2TMA.split('~')[0].trim());
				oExamenFisico.gnMaxPesoRecienNacido=parseInt(loDatos.datos.DE2TMA.split('~')[1].trim());
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de cargar parámetros.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Error al realizar la busqueda de cargar parámetros.');
		});
	},
	
	// Función que calcula el IMC
	calcularIMC: function()
	{
		$("#txtMasaC").val('');
		oFormulas.IMC(tnIMC=>$('#txtMasaC').val(tnIMC), $("#txtPeso").val(), $("#txtTalla").val());
	},

	// Función que calcula la superficie corporal
	calcularSC: function()
	{
		$('#txtSupC').val('');
		oFormulas.SuperficieCorporal(tnSC=>$('#txtSupC').val(tnSC), $("#txtPeso").val(), $("#txtTalla").val());
	},

	// Función que valida los datos ausentes
	validarAusente: function(tcObjLabel, tcObjDatos, tcObjChk)
	{
		if($(tcObjChk).prop('checked')){
			$(tcObjLabel).removeClass("required");
			$(tcObjDatos).val('').attr("readonly","readonly");
		}else{
			$(tcObjLabel).addClass("required");
			$(tcObjDatos).val('').removeAttr("readonly");
		}
	},

	actualizaEscala: function()
	{
		if(typeof oEscalaCrusade === 'object'){
			oEscalaCrusade.actualizarDatos();
		}
	},

	validacion: function()
	{
		var lbValido = true;
		// Valida escala de Glasgow entre 0 y 15
		if (($("#txtEscalaG").val())<0 || $("#txtEscalaG").val()>15){
			this.lcMensajeError = 'Error en el dato Escala de Glasgow, valor del campo entre 0 y 15. Revise por favor!';
			this.lcObjetoError = "#txtEscalaG";
			lbValido = false;
		}
		// valida masa corporal no pase de 3 digitos
		if ($("#txtMasaC").val()>999){
			this.lcMensajeError = 'Error en el dato Masa Corporal, Revise valores de peso y talla.';
			this.lcObjetoError = "#txtMasaC";
			lbValido = false;
		}
		
		if (aDatosIngreso['cCodVia']==='04'){
			if ($("#txtPeso").val()===0 || $("#txtPeso").val()===''){
				this.lcMensajeError = 'Falta especificar peso del paciente, revise por favor.';
				this.lcObjetoError = "#txtPeso";
				lbValido = false;
			}
			if (($("#txtPeso").val())<oExamenFisico.gnMinPesoRecienNacido || $("#txtPeso").val()>oExamenFisico.gnMaxPesoRecienNacido){
				this.lcMensajeError = 'Peso del paciente debe estar entre ' + oExamenFisico.gnMinPesoRecienNacido.toString() + ' y ' +oExamenFisico.gnMaxPesoRecienNacido.toString()+ ', kilogramos, revise por favor.';
				this.lcObjetoError = "#txtPeso";
				lbValido = false;
			}	
		}	

		return lbValido;
	},

	obtenerDatos: function()
	{
		//serialización de datos dentro de laDatos
		return ($( '#FormExamen').serializeArray()).concat(($( '#FormExamenG').serializeArray()), ($( '#FormExamenN').serializeArray()));
	}
};
