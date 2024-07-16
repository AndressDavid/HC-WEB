var oEscalaHasbled = {
	aInterpretaHasbled: {},
	cObjetoError: '',
	cMensajeError: '',
	cInterpretaC: '',
	nEdad: 0,
	lnTotalPuntajeHasbled: 0,
	bLlenar: true,
	lbDatosConsulta: false,
	lbHabilitar: false,

	inicializar: function(){
		$(".selectHasbled").attr('disabled', true);
		this.habilitar([]);
		this.ConsultarEscalaHasbled();
		this.obtenerinterpretacionesEscHas();
		oEscalaHasbled.nEdad  = parseInt(aDatosIngreso['aEdad']['y']);
		var lnValor6 = (oEscalaHasbled.nEdad > 65) ? 1:0;
		$("#cboSiNoeshas6").attr('disabled', true).val(lnValor6).change();
		$('#escalaHasbled').validate({
			errorPlacement: function ( error, element ) {
				error.addClass( "invalid-tooltip" );
				if (element.prop("type")==="radio") {
					error.insertAfter(element.parent("label"));
				} else {
					error.insertAfter(element);
				}
			},
			unhighlight: function (element, errorClass, validClass) {
				if ($('#'+element.id).val()==-1){
					$(element).addClass("is-invalid").removeClass("is-valid");
				}else{
					$(element).addClass("is-valid").removeClass("is-invalid");
				}
			}
		});
		$('.selectHasbled').each(function() {
			$('.selectHasbled').rules('add', {
					required: true,
					messages: {
						required: "your custom message",
					}
			});
		});
	},

	habilitar: function(taDiagnosticos){
		if(oEscalaHasbled.lbDatosConsulta){return;}
		var lnEdadMinima = 20;
			lnEdadMinima = parseInt(laDxHasbled.EDADMIN != "" ? laDxHasbled.EDADMIN: lnEdadMinima);
		if(oEscalaHasbled.nEdad < lnEdadMinima){
			oEscalaHasbled.bLlenar = false;
			$(".selectHasbled").attr('disabled', true);
		} else{
			var laCodDiagnosticos = [];
			$.each(taDiagnosticos, function(lnIndex, loDiagnostico){
				laCodDiagnosticos[lnIndex] = loDiagnostico.CODIGO;
			});
			var lbHabilitar = true;
			$.each(laDxHasbled.LISTADX , function(lnIndex, lcDxHasbled){
				if($.inArray(lcDxHasbled, laCodDiagnosticos) > -1){
					lbHabilitar = false;
				}
			});
			if(lbHabilitar){
				oEscalaHasbled.limpiarDatos();
				oEscalaChadsvas.limpiarDatos();
			}
			$(".selectHasbled").attr('disabled', lbHabilitar);
			$("#cboSiNoeshas6").attr('disabled', true);
			oEscalaHasbled.bLlenar = !lbHabilitar;
		}
	},

	limpiarDatos: function(){
		for (var lnId=0; lnId<=8; lnId++){
			if(lnId!==6){
				var lcDato = lnId.toString(); 
				var lcObjeto = 'cboSiNoeshas'+lcDato;
				$('#'+lcObjeto).val(-1).change();
			}
		}
		oEscalaHasbled.lnTotalPuntajeHasbled = 0;
	},

	obtenerinterpretacionesEscHas: function(){
		$.ajax({
			url: 'vista-comun/ajax/escala_hasbled.php',
			data: {accion: 'interpretarEscHas'},
			type: 'POST',
			dataType: 'json'
		})
		.done(function(loInterHasbled){
			try{
				if(!$.isEmptyObject(loInterHasbled) == true){
					$.each(loInterHasbled, function(lnIndex, loElement){
						oEscalaHasbled.aInterpretaHasbled[loElement.CL3TMA] = {
							VrMinimo: parseInt(loElement.OP3TMA),
							VrMaximo: parseInt(loElement.OP4TMA),
							Descripcion: (loElement.DE2TMA),
							Color: loElement.OP5TMA,
						};
					});
				}
				if (typeof oAval === 'object'){
					if(oAval.lcTipoAval=='HC'){
						oEscalaHasbled.ConsultarAvalHS();
					}
				}
			}catch(err){
				fnAlert('No se puede realizar la busqueda.', '', 'fas fa-exclamation-circle','red','medium');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown){
			console.log(jqXHR.responseText);
			fnAlert('Se presento un error al realizar la busqueda y no se obtuvo el resultado deseado.', '', 'fas fa-exclamation-circle','red','medium');
		});
	},

	actualizaPuntajeHasbled: function(){
		var lnIndice = $(this).attr('data-id'),
			lnValorReal = parseInt($(this).val()),
			lnValor = lnValorReal==-1 ? 0: lnValorReal;
		$('#txtPuntajeHasbled'+lnIndice).text(lnValor);
		laPuntajeHasbled[lnIndice].puntaje = lnValor;
		laPuntajeHasbled[lnIndice].seleccion = lnValorReal>-1;
		$('#lbldscitemhasbled'+lnIndice).css("font-style", "italic");
		oEscalaHasbled.totalHasbled();
	},

	totalHasbled: function(){
		var lbTodos = true,
			loInterpretaHasbled=$('#eshasInterpretacion'),
			loPuntajeHasbled = $('#eshastotalpuntaje'),
			lcInterpretaHasbled='',
			lcClase='',
			lnTotal = 0;
		$.each(laPuntajeHasbled, function(lnIndex, loElemento){
			lnTotal += loElemento.puntaje;
			lbTodos = lbTodos && loElemento.seleccion;
		});
		loPuntajeHasbled.text(lnTotal);
		loInterpretaHasbled.text('');
		$.each(oEscalaHasbled.aInterpretaHasbled, function(lnIndex, loElemento){
			if(lnTotal >= loElemento.VrMinimo && lnTotal <= loElemento.VrMaximo){
				lcInterpretaHasbled = loElemento.Descripcion;
				lcClase = loElemento.Color;
				oEscalaChadsvas.lHabilitar = loElemento.VrMinimo >= 3 && loElemento.VrMaximo <= 9;
				if (typeof oDiagnosticos === 'object') {
					oEscalaChadsvas.habilitar(oDiagnosticos.obtenerDatos(true));
				}
			}
			loInterpretaHasbled.removeClass(loElemento.Color);
			loPuntajeHasbled.removeClass(loElemento.Color);
		});
		if (lbTodos) {
			loInterpretaHasbled.text(lcInterpretaHasbled).addClass(lcClase);
			loPuntajeHasbled.addClass(lcClase);
			oEscalaHasbled.lnTotalPuntajeHasbled = lnTotal;
			oEscalaHasbled.lcInterpretaHasbled = lcInterpretaHasbled;
			oEscalaHasbled.obtenerDatos();
		}
	},

	ConsultarEscalaHasbled: function(){
		$.ajax({
			url: 'vista-comun/ajax/escala_hasbled.php',
			data: {accion: 'ConsultarEscala', ingreso: aDatosIngreso['nIngreso']},
			type: 'POST',
			dataType: 'json'
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (typeof toDatos.DATOS.H == "string") {
						oEscalaHasbled.CargarEscala(toDatos.DATOS, true);
						oEscalaHasbled.lbDatosConsulta = true;
						oEscalaHasbled.cInterpretaC = toDatos.DATOS.INTERPRETA;
						oEscalaHasbled.totalHasbled();
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda para escala HASBLED.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar escala HASBLED.");
		});
	},
	
	CargarEscala: function(taDatos, tbInhabilitar){
		$.each($('.selectHasbled'), function(lnIndex, loElemento){
			var lcLetra = $(loElemento).attr('data-letra');
			$(loElemento).attr('disabled', tbInhabilitar).val(taDatos[lcLetra]=='SI'?1:0).change();
		});
		if(tbInhabilitar){
			setTimeout(function(){
				$('#cboSiNoeshas8').change();
			}, 500);
		}
	},

	ConsultarAvalHS: function(){
		if (!(oAval.loDatosAval.Datos.escalaHasbled===undefined)){
			if(oAval.loDatosAval.Datos.escalaHasbled.EXISTE == 'SI'){
				oEscalaHasbled.bLlenar = true;
				oEscalaHasbled.CargarEscala(oAval.loDatosAval.Datos.escalaHasbled, false);
			}
		}
	},
	
	validacion: function(){
		if(oEscalaHasbled.bLlenar == false) return true;
		var lbValido = true;
		$.each($('.selectHasbled'), function(lnIndex, loElemento){
			if((loElemento.value) == "-1"){
				oEscalaHasbled.cObjetoError = loElemento.id;
				oEscalaHasbled.cMensajeError = $('#lbldscitemhasbled'+lnIndex).html() +', debe ser valorado';
				lbValido = false;
				return false;
			}
		});
		return lbValido;
	},

	obtenerDatos: function(){
		var oDatosHasbled = {};
		if(oEscalaHasbled.lbDatosConsulta==false){
		$.each($('.selectHasbled'), function(lnIndex, loElemento){
			oDatosHasbled[lnIndex] = {
				lcLetra: $(this).attr('data-letra'),
				lcDescItemHas: $('#lbldscitemhasbled'+lnIndex).text(),
				lcCboValor: $(this).find("option:selected").text(),
				lnValor: $(this).val(),
				lnPuntaje: oEscalaHasbled.lnTotalPuntajeHasbled,
				lcInterpretacion: oEscalaHasbled.lcInterpretaHasbled,
				lnEdad: oEscalaHasbled.nEdad,
			};
		});
		}
		return oDatosHasbled;
	},
};

$(".selectHasbled").change(oEscalaHasbled.actualizaPuntajeHasbled);
