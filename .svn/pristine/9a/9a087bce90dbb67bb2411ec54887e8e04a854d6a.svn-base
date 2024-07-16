var oEscalaChadsvas = {
	aInterpretaChadsvas: {},
	lcInterpretaChadsvas: '',
	aDatosInicial: {},
	cObjetoError: '',
	cInterpretaC: '',
	cMensajeError: '',
	nEdad: 0,
	lnTotalPuntajeChadsvas: 0,
	cSexo: '',
	bLlenar: true,
	lHabilitar: false,
	lbDatosConsulta: false,

	inicializar: function(){
		this.habilitar([]);
		this.ConsultarEscalaChadsvas();
		this.obtenerinterpretacionesEsChad();
		oEscalaChadsvas.nEdad = parseInt(aDatosIngreso['aEdad']['y']);
		oEscalaChadsvas.cSexo = aDatosIngreso['cSexo'];
		var lnValor2 = (oEscalaChadsvas.nEdad >= 75) ? 2: 0;
		$("#cboSiNoeschads2").attr('disabled', true).val(lnValor2).change();
 		var lnValor6 = (oEscalaChadsvas.nEdad >= 65 && oEscalaChadsvas.nEdad <= 74) ? 1: 0;
		$('#cboSiNoeschads6').attr('disabled', true).val(lnValor6).change();
		var lnValor7 = (oEscalaChadsvas.cSexo.toUpperCase() == "F")? 1:0;
		$('#cboSiNoeschads7').attr('disabled', true).val(lnValor7).change();

		$('#escalaChadsvas').validate({
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
		$('.selectChadsvas').each(function() {
			$('.selectChadsvas').rules('add', {
					required: true,
					messages: {
						required: "your custom message",
					}
			});
		});
	},

	habilitar: function(taDiagnosticos){
		if(oEscalaChadsvas.lbDatosConsulta){return;}
		var lnEdadMinima = 20;
			lnEdadMinima = parseInt(laDxChadsvas.EDADMIN != "" ? laDxChadsvas.EDADMIN: lnEdadMinima);
		if(oEscalaChadsvas.nEdad < lnEdadMinima){
			oEscalaChadsvas.bLlenar = false;
			$(".selectChadsvas").attr('disabled', true);
		} else{
			var laCodDiagnosticos = [];
			$.each(taDiagnosticos, function(lnIndex, loDiagnostico){
				laCodDiagnosticos[lnIndex] = loDiagnostico.CODIGO;
			});
			if(oEscalaChadsvas.lHabilitar){
				$.each(laDxChadsvas.LISTADX, function(lnIndex, lcDxChadsvas){
					if($.inArray(lcDxChadsvas, laCodDiagnosticos) > -1){
						oEscalaChadsvas.lHabilitar = false;
					}
				});
				$(".selectChadsvas").attr('disabled', oEscalaChadsvas.lHabilitar);
				oEscalaChadsvas.bLlenar = !oEscalaChadsvas.lHabilitar;
			}  else{
				$(".selectChadsvas").attr('disabled', true);
				oEscalaChadsvas.bLlenar = oEscalaChadsvas.lHabilitar;
			}
			if(oEscalaChadsvas.bLlenar){oEscalaChadsvas.limpiarDatos();}		
			$("#cboSiNoeschads2").attr('disabled', true);
			$('#cboSiNoeschads6').attr('disabled', true);
			$('#cboSiNoeschads7').attr('disabled', true);
		}
	},

	limpiarDatos: function(){
		$('#cboSiNoeschads0').val(-1).change();
		$('#cboSiNoeschads1').val(-1).change();
		$('#cboSiNoeschads3').val(-1).change();
		$('#cboSiNoeschads4').val(-1).change();
		oEscalaChadsvas.lnTotalPuntajeChadsvas=0;
	},

	obtenerinterpretacionesEsChad: function(){
		$.ajax({
			url: 'vista-comun/ajax/escala_chadsvas.php',
			data: {accion: 'interpretarEsChad'},
			type: 'POST',
			dataType: 'json'
		})
		.done(function(loInterChadsvas){
			try{
				if(!$.isEmptyObject(loInterChadsvas) == true){
					$.each(loInterChadsvas, function(lnIndex, loElement){
						oEscalaChadsvas.aInterpretaChadsvas[loElement.CL3TMA] = {
							VrMinimo: parseInt(loElement.OP3TMA),
							VrMaximo: parseInt(loElement.OP4TMA),
							Descripcion: loElement.DE2TMA,
							Color: loElement.OP5TMA
						};
					});
					
					if (typeof oAval === 'object' && !oEscalaChadsvas.lbDatosConsulta){
						if(oAval.lcTipoAval=='HC'){
							oEscalaChadsvas.ConsultarAvalCH();
						}
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

	actualizaPuntajeChadsvas: function(){
		var lnIndice = $(this).attr('data-id'),
			lnValorReal = parseInt($(this).val()),
			lnValor = lnValorReal==-1 ? 0: lnValorReal;
		$('#txtPuntajeChadsvas'+lnIndice).text(lnValor);
		laPuntajeChadsvas[lnIndice].puntaje = lnValor;
		laPuntajeChadsvas[lnIndice].seleccion = lnValorReal>-1;
		$('#lbldscitemChadsvas'+lnIndice).css("font-style", "italic");
		oEscalaChadsvas.totalChadsvas();
	},

	totalChadsvas: function(){
		var lbTodos = true,
			loInterpretaChadsvas=$('#esChadInterpretacion'),
			loPuntajeChadsvas = $('#esChadTotalPuntaje'),
			lcInterpretaChadsvas='',
			lcClase='',
			lnTotal = 0;

		$.each(laPuntajeChadsvas, function(lnIndex, loElemento){
			lnTotal += loElemento.puntaje;
			lbTodos = lbTodos && loElemento.seleccion;
		});
		loPuntajeChadsvas.text(lnTotal);
		loInterpretaChadsvas.text('');
		$.each(oEscalaChadsvas.aInterpretaChadsvas, function(lnIndex, loElemento){
			if(lnTotal >= loElemento.VrMinimo && lnTotal <= loElemento.VrMaximo){
				lcInterpretaChadsvas = loElemento.Descripcion;
				lcClase = loElemento.Color;
			}
			loInterpretaChadsvas.removeClass(loElemento.Color);
			loPuntajeChadsvas.removeClass(loElemento.Color);
		});
		if (lbTodos) {
			loInterpretaChadsvas.text(lcInterpretaChadsvas).addClass(lcClase);
			loPuntajeChadsvas.addClass(lcClase);
			oEscalaChadsvas.lnTotalPuntajeChadsvas = lnTotal;
			oEscalaChadsvas.lcInterpretaChadsvas = lcInterpretaChadsvas;
			oEscalaChadsvas.obtenerDatos();
		}
	},

	ConsultarEscalaChadsvas: function(){
		$.ajax({
			url: 'vista-comun/ajax/escala_chadsvas.php',
			data: {accion: 'ConsultarEscala', ingreso: aDatosIngreso['nIngreso']},
			type: 'POST',
			dataType: 'json'
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (typeof toDatos.DATOS.H == "string") {
						oEscalaChadsvas.CargarEscala(toDatos.DATOS, true);
						oEscalaChadsvas.lbDatosConsulta = true;
						oEscalaChadsvas.cInterpretaC = toDatos.DATOS.INTERPRETA;
						oEscalaChadsvas.totalChadsvas();
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda para escala CHADSVAS.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar escala CHADSVAS.");
		});
	},
	
	CargarEscala: function(taDatos, tbInhabilitar){
		$.each(taDatos, function(lnIndice, loValor){
			$.each($('.selectChadsvas'), function(lnIndex, loElemento){		
				if ($(loElemento).attr('data-letra') == lnIndice){
					var lnValor = 0;
					if(loValor=='SI'){
						$.each($(loElemento).find("option"), function(lnIndexOpt, loOption){
							if($(loOption).text()=='SI'){
								lnValor = $(loOption).val();
							}
						});
					}
					$(loElemento).attr('disabled', tbInhabilitar).val(lnValor).change();
					return;
				}
			});
		});
		setTimeout(function(){
			$('#cboSiNoeschads7').change();
		}, 1000);
	},

	ConsultarAvalCH: function(){
		if (!(oAval.loDatosAval.Datos.escalaChadsvas===undefined)){
			if(oAval.loDatosAval.Datos.escalaChadsvas.EXISTE=='SI'){
				oEscalaChadsvas.bLlenar = true;
				oEscalaChadsvas.CargarEscala(oAval.loDatosAval.Datos.escalaChadsvas, false);
			}
		}
	},

	validacion: function(){
		if(oEscalaChadsvas.bLlenar == false) return true;
		var lbValido = true;
		$.each($('.selectChadsvas'), function(lnIndex, loElemento){
			if((loElemento.value) == "-1"){
				oEscalaChadsvas.cObjetoError = loElemento.id;
				oEscalaChadsvas.cMensajeError = $('#lbldscitemChadsvas'+lnIndex).html() +', debe ser valorado'
				lbValido = false;
				return false;
			}
		});
		return lbValido;
	},

	obtenerDatos: function(){
		var oDatosChadsvas = {};
		if(oEscalaChadsvas.lbDatosConsulta == false){
		$.each($('.selectChadsvas'), function(lnIndex, loElemento){
			oDatosChadsvas[lnIndex] = {
				lcLetra: $(this).attr('data-letra'),
				lcDescItemChadsvas: $('#lbldscitemChadsvas'+lnIndex).text(),
				lcCboValor: $(this).find("option:selected").text(),
				lnValor: $(this).val(),
				lnPuntaje: oEscalaChadsvas.lnTotalPuntajeChadsvas,
				lcInterpretacion: oEscalaChadsvas.lcInterpretaChadsvas,
				lnEdad: oEscalaChadsvas.nEdad,
				lnGenero: oEscalaChadsvas.cSexo,
			};
		});
		}
		return oDatosChadsvas;
	}
};

$(".selectChadsvas").change(oEscalaChadsvas.actualizaPuntajeChadsvas);
