var oSadPersons = {
	aInterpretaSadPersons : {},
	laTotalSad : [],
	lcMensajeError : '',
	lcObjetoError : '',
	nEdad : 0,
	lnEdadMinima : 0,
	lnEdadMaxima : 0,
	lnInterpretacionMinimo : 0,
	lnColorAlerta : 0,
	lnTipoDiagnostico : 0,
	cSexo : '',
	lbHabilitar : false,
	bLlenarSadPerson : true,
	lnTotalPuntajeSadPersons : 0,
	lcInterpretaSad : '',
	lcInterpretaConsultaSad : '',
	lbDatosConsulta : false,
	lcUrlAjaxSP: 'vista-comun/ajax/escala_sad_persons.php',

	inicializar: function() 
	{
		oSadPersons.ConsultarEscalaSadP();
		$(".selectSadPersons").attr('disabled', true);
		oSadPersons.rangoEdad();
		oSadPersons.obtenerinterpretacionesEscSadPersons();
		oSadPersons.cSexo = aDatosIngreso['cSexo'];
		oSadPersons.nEdad = parseInt(aDatosIngreso['aEdad']['y']);
		oSadPersons.calcularPuntajeGenero(oSadPersons.cSexo);
		oSadPersons.calcularPuntajeEdad(oSadPersons.nEdad);
		oSadPersons.habilitar([]);
		$('.selectSadPersons').change(oSadPersons.actualizaPuntajeSad) ;
							
		$('#FormSadPersons').validate({
			errorElement: "div",
			validClass: "is-valid",
					
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
	},

	calcularPuntajeGenero : function(tcSexo){
		var lnGenero = (tcSexo.toUpperCase() == "M") ? 1 : 0;
		$('#cboSiNoesad01').attr('disabled', true).val(lnGenero).change();
		lnIndice = $("#cboSiNoesad01").attr('data-id');
		laPuntajeSadperson[lnIndice].puntaje = lnGenero;
		laPuntajeSadperson[lnIndice].seleccion = true;
	},
	
	calcularPuntajeEdad : function(tnEdad){
		var lnEdad = (tnEdad < oSadPersons.lnEdadMinima || tnEdad > oSadPersons.lnEdadMaxima) ? 1 : 0;
		$("#cboSiNoesad02").attr('disabled', true).val(lnEdad).change();
		lnIndice = $("#cboSiNoesad02").attr('data-id');
		laPuntajeSadperson[lnIndice].puntaje = lnEdad;
		laPuntajeSadperson[lnIndice].seleccion = true;
	},
	
	obtenerinterpretacionesEscSadPersons : function(){
		$.ajax({
			url: oSadPersons.lcUrlAjaxSP,
			data : {accion : 'interpretarEscSadPersons'},
			type : 'POST',
			dataType : 'json'
		})
		.done(function(loInterSadPersons){
		try{
				if(!$.isEmptyObject(loInterSadPersons) == true){
					$.each(loInterSadPersons, function(lnIndex, loElement){
						oSadPersons.aInterpretaSadPersons[loElement.CL3TMA] = {
							VrMinimo: parseInt(loElement.OP3TMA),
							VrMaximo: parseInt(loElement.OP4TMA),
							Descripcion : (loElement.DE2TMA),
							Color: loElement.OP5TMA,
						};
					});
				}
			}catch(err){
				fnAlert('No se puede realizar la busqueda interpretación sadpersons.', '', 'fas fa-exclamation-circle','red','medium');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown){
			console.log(jqXHR.responseText);
			fnAlert('Se presento un error al realizar la busqueda y no se obtuvo el resultado deseado interpretación sadpersons.', '', 'fas fa-exclamation-circle','red','medium');
		});
	},
	
	habilitar : function(taDiagnosticos){
		var laCodDiagnosticos = [];
		$.each(taDiagnosticos, function(lnIndex, loDiagnostico){
			if($.inArray(loDiagnostico.CODTIPO, laTiposDiagnostico) > -1){
				laCodDiagnosticos[lnIndex] = loDiagnostico.CODIGO;
			}
		});
		
		var lbHabilitar = true;
		$.each(laDxSanPersons , function(lnIndex, lcDxSanPersons){
			if($.inArray(lcDxSanPersons, laCodDiagnosticos) > -1){
				lbHabilitar = false;
			}
		});
				
		if (lbHabilitar == true){
			oSadPersons.restaurarSadPersons();
		}
		
		$(".selectSadPersons").attr('disabled', lbHabilitar);
		$("#cboSiNoesad01").attr('disabled', true);
		$("#cboSiNoesad02").attr('disabled', true);
		oSadPersons.bLlenarSadPerson = !lbHabilitar;
	},
	
	rangoEdad: function() {
		var datosEdades = laRangoEdad.split('~');
		oSadPersons.lnEdadMinima = datosEdades[0];
		oSadPersons.lnEdadMaxima = datosEdades[1];
		oSadPersons.lnInterpretacionMinimo = datosEdades[2];
		oSadPersons.lnColorAlerta = datosEdades[3];
		oSadPersons.lnTipoDiagnostico = datosEdades[4];
	},	
		
	totalSadPersons: function() {
		var lbTodos = true,
			loInterpretaSadPersons=$('#escSadPersonsInterpretacion'),
			loPuntajeSadPersons = $('#escSadPersonstotalpuntaje'),
			lcInterpretaSad='',
			lcClase='',
			lnTotal = 0;
		
		$.each(laPuntajeSadperson, function(lnIndex, loElemento){
			lnTotal += loElemento.puntaje;
			lbTodos = lbTodos && loElemento.seleccion;
		});
		loPuntajeSadPersons.text(lnTotal);
		loInterpretaSadPersons.text('');
		
		$.each(oSadPersons.aInterpretaSadPersons, function(lnIndex, loElemento){
			if(lnTotal >= loElemento.VrMinimo && lnTotal <= loElemento.VrMaximo){
				lcInterpretaSad = loElemento.Descripcion;
				lcClase = loElemento.Color;
				oSadPersons.lbHabilitar = true;
				oSadPersons.habilitar(oDiagnosticos.obtenerDatos());
			}
			loInterpretaSadPersons.removeClass(loElemento.Color);
			loPuntajeSadPersons.removeClass(loElemento.Color);
		});
		if (lbTodos) {
			loInterpretaSadPersons.text(lcInterpretaSad).addClass(lcClase);
			loPuntajeSadPersons.addClass(lcClase);
			oSadPersons.lnTotalPuntajeSadPersons = lnTotal;
			oSadPersons.lcInterpretaSad = lcInterpretaSad;
			oSadPersons.obtenerDatos();

			if (lnTotal>=oSadPersons.lnInterpretacionMinimo){
				lcColor = (lnTotal>=oSadPersons.lnColorAlerta) ? 'red' : 'orange';
				lcTexto = "Interpretación: " + '<b>' + oSadPersons.lcInterpretaSad + '</b>' + " con puntaje: " + '<b>' + lnTotal+ '</b>';
				if(oSadPersons.lbDatosConsulta == false){
					fnAlert(lcTexto, 'ESCALA SAD PERSONS', 'fas fa-exclamation-circle',lcColor,'medium');
				}
			}
		}
	},
	
	restaurarSadPersons: function() {
		var loInterpretaSadPersons=$('#escSadPersonsInterpretacion'),
			loPuntajeSadPersons = $('#escSadPersonstotalpuntaje'),
			lcInterpretaSad='',
			lcClase='',
			lnTotal = 0;
		
		$.each($('.selectSadPersons'), function(lnIndex, loElemento){
			var lnCodigo = parseInt($(this).attr('data-codigo'));
			if (lnCodigo == 1 || lnCodigo == 2){}else{ $('#'+loElemento.id).removeClass("is-invalid").removeClass("is-valid").val("-1"); }	
		});
		
		$.each(laPuntajeSadperson, function(lnIndex, loElemento){
			lnCodigoItem = parseInt(loElemento.codigoesc);
			
			if (lnCodigoItem == 1 || lnCodigoItem == 2){
				lnTotal += loElemento.puntaje;
			}
			else{	
				laPuntajeSadperson[lnIndex].puntaje = 0;
				laPuntajeSadperson[lnIndex].seleccion = false;
			}
		});
		loPuntajeSadPersons.text(lnTotal);
		loInterpretaSadPersons.text('');
		
		$.each(oSadPersons.aInterpretaSadPersons, function(lnIndex, loElemento){
			if(lnTotal >= loElemento.VrMinimo && lnTotal <= loElemento.VrMaximo){
				lcClase = loElemento.Color;
			}
			loInterpretaSadPersons.removeClass(loElemento.Color);
			loPuntajeSadPersons.removeClass(loElemento.Color);
		});
	},
	
	actualizaPuntajeSad: function() 
	{
		var lnValorItem = 0;
		lnIndice = $(this).attr('data-id');
		lcCodigo = $(this).attr('data-codigo');
		lnValorReal = parseInt($(this).val());
		lnValor = lnValorReal==-1 ? 0 : lnValorReal;
		laPuntajeSadperson[lnIndice].puntaje = lnValor;
		laPuntajeSadperson[lnIndice].seleccion = lnValorReal>-1;
		oSadPersons.totalSadPersons();
	},

	ConsultarEscalaSadP: function(){
		$.ajax({
			url: oSadPersons.lcUrlAjaxSP,
			data: {accion: 'ConsultarEscala', ingreso: aDatosIngreso['nIngreso']},
			type: 'POST',
			dataType: 'json'
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.DATOS.INTERPRETA !== '') {
						oSadPersons.lbDatosConsulta = true;
						oSadPersons.CargarEscala(toDatos.DATOS, true);
						oSadPersons.bLlenarSadPerson = false;
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda para escala Sad Persons.');
			}
			oSadPersons.totalSadPersons();
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar escala Sad Persons.");
		});
	},

	CargarEscala: function(taDatos, tbInhabilitar){
		$.each($('.selectSadPersons'), function(lnIndex, loElemento){
			var lcCodigo = $(loElemento).attr('data-codigo');
			if(lcCodigo=='07'){
				$(loElemento).attr('disabled', tbInhabilitar).val(taDatos[lcCodigo]=='SI'?"0":"1").change();
			}else{
				$(loElemento).attr('disabled', tbInhabilitar).val(taDatos[lcCodigo]=='SI'?"1":"0").change();
			}
		});
		if(tbInhabilitar){
			setTimeout(function(){
				$('#cboSiNoesad10').change();
				oSadPersons.Inhabilitar();
			}, 500);
		}
		oSadPersons.lcInterpretaConsultaSad = taDatos.INTERPRETA;
	},

	Inhabilitar : function(){
		for (var lnId=1; lnId<=10; lnId++){
			var lcDato = lnId.toString(); 
			var lcObjeto = '#cboSiNoesad'+lcDato.padStart(2, "0");
			oSadPersons.bLlenarSadPerson = false;

			$('#cboSiNoesad'+lcDato.padStart(2, "0")).attr('disabled', true);
		}
	},
	
	validacion : function(){
		if(oSadPersons.bLlenarSadPerson == false) return true;
		var lbValido = true;
		$.each($('.selectSadPersons'), function(lnIndex, loElemento){
			lnIndex = lnIndex+1;
			if((loElemento.value) == "-1"){
				oSadPersons.lcObjetoError = loElemento.id;
				oSadPersons.lcMensajeError = $('#lblSiNoesad'+lnIndex).html() +', debe ser valorado';
				lbValido = false;
				return false;
			}
		});
		
		if (lbValido){
			if (oSadPersons.lnTotalPuntajeSadPersons==0){
				oSadPersons.lcObjetoError = 'cboSiNoesad03';
				oSadPersons.lcMensajeError = 'Falta puntaje'
				lbValido = false;
				return false;
			}
		}
		
		if (lbValido){
			if (oSadPersons.lcInterpretaSad==''){
				oSadPersons.lcObjetoError = 'cboSiNoesad03';
				oSadPersons.lcMensajeError = 'Falta interpretación'
				lbValido = false;
				return false;
			}
		}
		return lbValido;
	},
	
	obtenerDatos : function(){
		var oDatosSadPersons = {};
		$.each($('.selectSadPersons'), function(lnIndex, loElemento){
			oDatosSadPersons[lnIndex] = {
				lcCodigo : $(this).attr('data-codigo'),
				lcCboValor : $(this).find("option:selected").text(),
				lnValor : $(this).val(),
				lnPuntajeSad : oSadPersons.lnTotalPuntajeSadPersons,
				lcInterpretacionSad: oSadPersons.lcInterpretaSad,
			};
		});
		return oDatosSadPersons;
	}
};
