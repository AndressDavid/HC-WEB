var oEscalaCrusade = {
	lcUrlAjaxEscCru: 'vista-comun/ajax/escala_crusade.php',
	cObjetoError: '',
	cMensajeError: '',
	cInterpretaC: '',
	nPeso: 0,
	nEdad: -1,
	cSexo: '',
	nFreCardi: 0,
	nArteSisto: 0,
	nEdadMinima: 0,
	cDiagnosticos: [],
	cInterCrusa: [],
	aPorcent: [],
	bConsulta: false,
	bLlenar: false,
	bObligatoria: false,
	HayLaboratorio: false,
	lbDatosConsulta: false,
	lDatosEvolucion: false,
	lcInterpretaCrusade: '',
	lnTotalPuntajeCrusade: 0,
	aCreatinina: {min:0.5, max:6},
	aHematocrito: {min:0.0, max:200.0},

	inicializar: function(tcTipoEvolucion)
	{
		var laDiagnosticos = [];
		if (tcTipoEvolucion!=='CONSULTA') this.habilitar([]);
		this.ConsultarEscalaCrusade();
		if(tcTipoEvolucion=='EVO'){this.lDatosEvolucion = true;}else{this.lDatosEvolucion = false;}
		this.cSexo = aDatosIngreso['cSexo'];
		this.nEdad = parseInt(aDatosIngreso['aEdad']['y']);
		this.calcularPuntajeGenero(this.cSexo);
		this.cargarDatosEsCrusade(aDatosIngreso['nIngreso']);

		$('#escalaCrusade').validate({
			errorPlacement: function(error, element) {
				error.addClass("invalid-tooltip");
				if (element.prop("type")==="radio") {
					error.insertAfter(element.parent("label"));
				} else {
					error.insertAfter(element);
				}
			},
			unhighlight: function(element, errorClass, validClass) {
				if ($('#'+element.id).val()==-1){
					$(element).addClass("is-invalid").removeClass("is-valid");
				}else{
					$(element).addClass("is-valid").removeClass("is-invalid");
				}
			}
		});
		$('.selectCrusade').each(function() {
			var lnVal = $('.selectCrusade').val();
			if(lnVal== "S"){
				$('.selectCrusade').val(1);
			}
			if (lnVal == "-1"){
				$('.selectCrusade').rules('add', {
					required: true,
				});
			}
		});
	},

	cargarDatosEsCrusade: function(tnIngreso){
		$.ajax({
			url: oEscalaCrusade.lcUrlAjaxEscCru,
			data: {accion: 'cargarDatosEsCrusade', ingreso: tnIngreso},
			type: 'POST',
			dataType: 'json',
		})
		.done(function(loDatosEsCrusade){
			try{
				if(!$.isEmptyObject(loDatosEsCrusade) == true){
					var lcPercent = '';
					$.each(loDatosEsCrusade, function(lnIndex, laElemento){
						if(lnIndex == 'Habilita'){
							oEscalaCrusade.HayLaboratorio = laElemento;
							return;
						}
						if(laElemento.CL2TMA.substr(0,4) == 'ELEM' && laElemento.CL3TMA.substr(1,2) == '01'){
							var i = laElemento.CL3TMA.substr(0, 1);
							$('#lbldscCrusade'+i).text(laElemento.DE2TMA);
							$('#lnRegistro'+i).val(laElemento.CL2TMA);
							$('#lnValorHematocrito').focus();
							if(i%2!=0){
								$('#lnRegistro'+i).addClass('bg-light');
							}
						}

						switch(true){

							case laElemento.CL2TMA == 'DX':
								oEscalaCrusade.nEdadMinima = laElemento.OP3TMA;
								oEscalaCrusade.cDiagnosticos = laElemento.DE2TMA;
								break;

							case laElemento.CL2TMA == 'ELEM100':
								var loOption = $('<option></option>');
								loOption.val(laElemento.OP1TMA).text(laElemento.OP2TMA).attr('vrMin', laElemento.OP3TMA).attr('vrMax', laElemento.OP4TMA).attr('puntaje', laElemento.OP7TMA);
								$('#cboRangoHematocrito').append(loOption);
								break;

							case laElemento.CL2TMA == 'ELEM200':
								var loOption = $('<option></option>');
								loOption.val(laElemento.OP1TMA).text(laElemento.OP2TMA).attr('vrMin', laElemento.OP3TMA).attr('vrMax', laElemento.OP4TMA).attr('puntaje', laElemento.OP7TMA);
								$('#cboRangoCreatinina').append(loOption);
								break;

							case laElemento.CL2TMA == 'ELEM300':
								var loOption = $('<option></option>');
								loOption.val(laElemento.OP1TMA).text(laElemento.OP2TMA).attr('vrMin', laElemento.OP3TMA).attr('vrMax', laElemento.OP4TMA).attr('puntaje', laElemento.OP7TMA);
								$('#cboRangoFreCardi').append(loOption);
								break;

							case laElemento.CL2TMA == 'ELEM400':
								var loOption = $('<option></option>');
								loOption.val(laElemento.OP1TMA).text(laElemento.OP2TMA).attr('puntaje', laElemento.OP7TMA);
								$('#cboGenero').append(loOption);
								break;

							case laElemento.CL2TMA == 'ELEM500':
								var loOption = $('<option></option>');
								loOption.val(laElemento.OP1TMA).text(laElemento.OP2TMA).attr('puntaje', laElemento.OP7TMA);
								$('#cboFallaCardi').append(loOption);
								break;

							case laElemento.CL2TMA == 'ELEM600':
								var loOption = $('<option></option>');
								loOption.val(laElemento.OP1TMA).text(laElemento.OP2TMA).attr('puntaje', laElemento.OP7TMA);
								$('#cboVascularPrevia').append(loOption);
								break;

							case laElemento.CL2TMA == 'ELEM700':
								var loOption = $('<option></option>');
								loOption.val(laElemento.OP1TMA).text(laElemento.OP2TMA).attr('puntaje', laElemento.OP7TMA);
								$('#cboDiabetesMellitus').append(loOption);
								break;

							case laElemento.CL2TMA == 'ELEM800':
								var loOption = $('<option></option>');
								loOption.val(laElemento.OP1TMA).text(laElemento.OP2TMA).attr('vrMin', laElemento.OP3TMA).attr('vrMax', laElemento.OP4TMA).attr('puntaje', laElemento.OP7TMA);
								$('#cboRangoArteSisto').append(loOption);
								break;

							case laElemento.CL2TMA == 'INTERP':
								oEscalaCrusade.cInterCrusa.push({
									vrMin: laElemento.OP3TMA,
									vrMax: laElemento.OP4TMA,
									Descrip: laElemento.DE2TMA,
									Porcent: laElemento.OP2TMA,
									color: laElemento.OP5TMA
								});
								break;

							case laElemento.CL2TMA == 'PERCENT':
								lcPercent += laElemento.DE2TMA;
								break;

							case laElemento.CL2TMA == 'RANGO':
								if (laElemento.CL3TMA == 'CREATINI') {
									let laRango = laElemento.DE2TMA.split('~'),
										lnMin = parseFloat(laRango[0]),
										lnMax = parseFloat(laRango[1]);
									oEscalaCrusade.aCreatinina = {min: lnMin, max: lnMax};
									$("#escalaCrusade #lnValorCreatinina").attr('min',lnMin).attr('max',lnMax)
								} else if (laElemento.CL3TMA == 'HEMATOCR') {
									let laRango = laElemento.DE2TMA.split('~'),
										lnMin = parseFloat(laRango[0]),
										lnMax = parseFloat(laRango[1]);
									oEscalaCrusade.aHematocrito = {min: lnMin, max: lnMax};
									$("#escalaCrusade #lnValorHematocrito").attr('min',lnMin).attr('max',lnMax)
								}
								break;
						}
					});
					oEscalaCrusade.aPorcent = lcPercent.split('~');
					oEscalaCrusade.calcularPuntajeGenero(oEscalaCrusade.cSexo);
					oEscalaCrusade.actualizarPuntajeCrusade();
				}
			}catch(err){
				fnAlert('No se pudo realizar la carga de datos crusade.', '', 'fas fa-exclamation-circle','red','medium');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown){
			console.log(jqXHR.responseText);
			fnAlert('Se presento un error al realizar la carga de datos crusade y no se obtuvo el resultado deseado.', '', 'fas fa-exclamation-circle','red','medium');
		});
	},

	habilitar: function (taDiagnosticos){
		if(oEscalaCrusade.lbDatosConsulta){oEscalaCrusade.habilitarFrecuenciaPresion(true); return;}
		var lnEdadMinima = parseInt(laDxCrusade.EDADMIN != "" ? laDxCrusade.EDADMIN: 20);
		if(oEscalaCrusade.nEdad < 0) {
			oEscalaCrusade.nEdad = parseInt(aDatosIngreso['aEdad']['y']);
		}
		oEscalaCrusade.bLlenar = false;
		if(oEscalaCrusade.nEdad < lnEdadMinima) {
			oEscalaCrusade.bObligatoria = false;
			$(".selectCrusade").attr('disabled', true);
			$("#lnValorHematocrito").attr('disabled', true);
			$("#lnValorCreatinina").attr('disabled', true);
		}else{
			var lbDeshabilitar = true;
			$.each(taDiagnosticos, function(lnIndex, loDiagnostico){
				if($.inArray(loDiagnostico.CODIGO, laDxCrusade.LISTADX) >-1){
					lbDeshabilitar = false;
					oEscalaCrusade.bLlenar = true;
					if (oEscalaCrusade.HayLaboratorio) {
						oEscalaCrusade.bObligatoria = true;
					}
					return;
				}
			});

			$(".selectCrusade").attr('disabled', true);
			$("#lnValorHematocrito,#lnValorCreatinina,#cboFallaCardi,#cboVascularPrevia,#cboDiabetesMellitus").attr('disabled', lbDeshabilitar);
			if(lbDeshabilitar){oEscalaCrusade.iniciarDatos();}
			oEscalaCrusade.habilitarFrecuenciaPresion(lbDeshabilitar);
		}
	},

	obtenerValorHematocrito: function(){
		var lnValorHematocrito = parseFloat($('#lnValorHematocrito').val());
		if (lnValorHematocrito <= oEscalaCrusade.aHematocrito.min || lnValorHematocrito > oEscalaCrusade.aHematocrito.max){
			let lcMensaje = 'Valor de Hematocrito basal que digitó esta fuera del rango ';
			fnAlert(lcMensaje, 'Puntaje Crusade', 'fas fa-exclamation-circle','red','medium');
			$('#lnValorHematocrito').focus().val("");
			$('#puntajeHematocrito').text('');
			$('#cboRangoHematocrito').val('');
		}else{
			if (lnValorHematocrito > 0){
				oEscalaCrusade.calcularPuntajeHematocrito(lnValorHematocrito);
			}
		}
	},

	calcularPuntajeHematocrito: function(tnValorHematocrito){
		$('#cboRangoHematocrito option').each(function(lnIndex, loOption){
			var lnMin = parseFloat($(this).attr('vrMin')),
					lnMax = parseFloat($(this).attr('vrMax'));
			if(tnValorHematocrito >= lnMin && tnValorHematocrito <= lnMax ){
				$('#puntajeHematocrito').text($(this).attr('puntaje'));
				$('#cboRangoHematocrito').val($(this).val());
			}
		});
		oEscalaCrusade.actualizarPuntajeCrusade();
	},

	obtenerValorCreatinina: function(){
		var lnValorCreatinina = parseFloat($('#lnValorCreatinina').val());
		oEscalaCrusade.nPeso = (aDatosIngreso.cPesoUnidad).split(' ')[0];
		oEscalaCrusade.nPeso = (oEscalaCrusade.nPeso==0?$("#txtPeso").val():oEscalaCrusade.nPeso);
		if(oEscalaCrusade.nPeso == 0 || oEscalaCrusade.nPeso == ''){
			$('#lnValorCreatinina').val('');
			ubicarObjeto('#FormExamen','#txtPeso');
			fnAlert('Falta el valor del Peso del paciente.', 'Fórmula de Cockcroft-Gault', 'fas fa-exclamation-circle','red','medium');
		}else {
			if (lnValorCreatinina != '' && lnValorCreatinina > 0 && lnValorCreatinina >= oEscalaCrusade.aCreatinina.min && lnValorCreatinina <= oEscalaCrusade.aCreatinina.max){
				oEscalaCrusade.calcularPuntajeCreatinina(lnValorCreatinina, oEscalaCrusade.nPeso, oEscalaCrusade.nEdad, oEscalaCrusade.cSexo);
			} else{
				let lcMensaje = 'Valor de Creatinina debe estar entre '+oEscalaCrusade.aCreatinina.min+' y '+oEscalaCrusade.aCreatinina.max+' mg/dL';
				fnAlert(lcMensaje, 'Fórmula de Cockcroft-Gault', 'fas fa-exclamation-circle','red','medium');
				$('#lnValorCreatinina,#lnValorCockcroft').val('');
				$('#puntajeCreatinina').text(0);
				$('#esCrusInterpretacion,#esCrusTotalPuntaje').text('');
				$('#cboRangoCreatinina').val('-1');
			}
		}
	},

	calcularPuntajeCreatinina: function(tnValorCreatinina, tnPeso, tnEdad, tcSexo){
		if(tnPeso>0){
			var laData = {
				accion: 'calcularPuntajeCreatinina',
				lnValorCreatinina: tnValorCreatinina,
				lnPeso: tnPeso,
				lnEdad: tnEdad,
				lcSexo: tcSexo,
			}
			$.ajax({
				url: oEscalaCrusade.lcUrlAjaxEscCru,
				data: laData,
				type: 'POST',
				dataType: 'json',
			})
			.done(function(loPuntajeCreatinina){
				try{
					if(!$.isEmptyObject(loPuntajeCreatinina) == true){
						if(!$.isEmptyObject(loPuntajeCreatinina.MENSAJE) == true){
							fnAlert(loPuntajeCreatinina.MENSAJE, 'Fórmula de Cockcroft-Gault', 'fas fa-exclamation-circle','red','medium');
							$('#lnValorCreatinina').focus().val("");
							$('#lnValorCockcroft').val('');
							$('#cboRangoCreatinina').val('');
							$('#puntajeCreatinina').text(0);
						}else{
							oEscalaCrusade.actualizaRangosCreatinina(loPuntajeCreatinina.CREATININA);
						}
					}
				}catch(err){
					fnAlert('No se pudo realizar el calculo de puntaje Creatinina.', '', 'fas fa-exclamation-circle','red','medium');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown){
				console.log(jqXHR.responseText);
				fnAlert('Se presento un error al realizar el calculo del puntaje Creatinina y no se obtuvo el resultado deseado.', '', 'fas fa-exclamation-circle','red','medium');
			});
		}else{
			fnAlert('Falta valor del peso del paciente', '', 'fas fa-exclamation-circle','red','medium');
		}
	},

	calcularPuntajeFreCardiaca: function(){
		if(oEscalaCrusade.lDatosEvolucion){
			oEscalaCrusade.nFreCardi = parseInt($('#lnValorFreCardi').val());
		}
		oEscalaCrusade.nFreCardi = (oEscalaCrusade.nFreCardi==0?parseInt($('#txtFC').val()):oEscalaCrusade.nFreCardi);
		if(oEscalaCrusade.nFreCardi >= 20 && oEscalaCrusade.nFreCardi <=300){
			$('#lnValorFreCardi').val(oEscalaCrusade.nFreCardi);
			var lnFrecardi = parseFloat(oEscalaCrusade.nFreCardi);
			$('#cboRangoFreCardi option').each(function(lnIndex, loOption){
				var lnMin = parseFloat($(this).attr('vrMin')),
					lnMax = parseFloat($(this).attr('vrMax'));
				if(lnFrecardi >= lnMin && lnFrecardi <= lnMax ){
					$('#cboRangoFreCardi').val($(this).val());
					$('#puntajeFreCardi').text($(this).attr('puntaje'));
				}
			});
		}else{
			fnAlert('El valor de la Frecuencia Cardiáca debe estar entre 20 y 300', 'Puntaje Crusade', 'fas fa-exclamation-circle','red','medium');
			$('#lnValorFreCardi').val(0.00);
			$('#puntajeFreCardi').text('');
			$('#cboRangoFreCardi').val(-1);
		}
		oEscalaCrusade.actualizarPuntajeCrusade();
	},

 	calcularPuntajeGenero: function(tcSexo){
		$('#cboGenero').val(tcSexo);
		var lnPuntaje = $('#cboGenero option:selected').attr('puntaje');
		$('#puntajeGenero').text(lnPuntaje);
		oEscalaCrusade.actualizarPuntajeCrusade();
	},

	obtenerValorFallaCardi: function(){
		var lnPuntaje = $('#cboFallaCardi').val()=="-1" ? "0" : $('#cboFallaCardi option:selected').attr('puntaje');
		$('#puntajeFallaCardi').text(lnPuntaje);
	},

	obtenerValorVascuPrev: function(){
		var lnPuntaje = $('#cboVascularPrevia').val()=="-1" ? "0" : $('#cboVascularPrevia option:selected').attr('puntaje');
		$('#puntajeVascularPrevia').text(lnPuntaje);
	},

	obtenerValorDiaMelli: function(){
		var lnPuntaje = $('#cboDiabetesMellitus').val()=="-1" ? "0" : $('#cboDiabetesMellitus option:selected').attr('puntaje');
		$('#puntajeDiabetesMellitus').text(lnPuntaje);
	},

	calcularPuntajeArteSisto: function(){
		if(oEscalaCrusade.lDatosEvolucion){
			oEscalaCrusade.nArteSisto = parseInt($('#lnValorArteSisto').val());
		}
		oEscalaCrusade.nArteSisto = (oEscalaCrusade.nArteSisto==0?$('#txtTAS').val():oEscalaCrusade.nArteSisto);
		if(oEscalaCrusade.nArteSisto > 0 && oEscalaCrusade.nArteSisto < 300){
			$('#lnValorArteSisto').val(oEscalaCrusade.nArteSisto);
			var lnArteSisto = parseFloat(oEscalaCrusade.nArteSisto);
			$('#cboRangoArteSisto option').each(function(lnIndex, loOption){
				var lnMin = parseFloat($(this).attr('vrMin')),
					lnMax = parseFloat($(this).attr('vrMax'));
				if(lnArteSisto >= lnMin && lnArteSisto <= lnMax ){
					$('#puntajeArteSisto').text($(this).attr('puntaje'));
					$('#cboRangoArteSisto').val($(this).val());
				}
			});
		}else{
			fnAlert('El valor de la Presión Arterial Sistólica debe estar entre 20 y 300', 'Puntaje Crusade', 'fas fa-exclamation-circle','red','medium');
			$('#lnValorFreCardi').val(0.00);
			$('#puntajeFreCardi').text('');
			$('#cboRangoFreCardi').val(-1);
		}
		oEscalaCrusade.actualizarPuntajeCrusade();
	},

	actualizarPuntajeCrusade: function(){
		if(oEscalaCrusade.validaTodoLleno()){
			var lnTotal = 0,
				lcColor = '';
			$.each($('.puntajeCrusade') , function(lnIndex, loElemento){
				lnTotal += parseInt($(this).text());
			});
			var lcInterpreta = oEscalaCrusade.aPorcent[lnTotal] + ' % ';
			$.each(oEscalaCrusade.cInterCrusa, function(lnIndex, loElemento){
				if(lnTotal >= loElemento.vrMin && lnTotal <= loElemento.vrMax){
					lcInterpreta += loElemento.Descrip;
					lcColor = loElemento.color;
				}
				if (loElemento.color.length>0) {
					$('#esCrusInterpretacion,#esCrusTotalPuntaje').removeClass(loElemento.color);
				}
			});
			$('#esCrusInterpretacion').text(lcInterpreta);
			$('#esCrusTotalPuntaje').text(lnTotal);
			if (lcColor.length>0) {
				$('#esCrusInterpretacion,#esCrusTotalPuntaje').addClass(lcColor);
			}
			oEscalaCrusade.lnTotalPuntajeCrusade = lnTotal;
			oEscalaCrusade.lcInterpretaCrusade = lcInterpreta;
		}else{
			$('#esCrusInterpretacion,#esCrusTotalPuntaje').text('');
		}
	},

	validaTodoLleno: function(){
		var lbTodo = true;
		$.each($('.selectCrusade'), function(lnIndex, loElemento){
			if($(this).val() == "-1"){lbTodo = false;}
		});
		return lbTodo;
	},

	ConsultarEscalaCrusade: function(){
		$.ajax({
			url: oEscalaCrusade.lcUrlAjaxEscCru,
			data: {accion: 'ConsultarEscala', ingreso: aDatosIngreso.nIngreso},
			type: 'POST',
			dataType: 'json'
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (typeof toDatos.DATOS.HB == "string") {
						oEscalaCrusade.CargarEscala(toDatos.DATOS, true);
						oEscalaCrusade.lbDatosConsulta = true;
						oEscalaCrusade.cInterpretaC = toDatos.DATOS.INTERPRETA;
						oEscalaCrusade.habilitar([]);
					} else {
						if (typeof oDiagnosticos == "object") {
							oEscalaCrusade.lbDatosConsulta = false;
							oEscalaCrusade.habilitar(oDiagnosticos.obtenerDatos(true));
						}
						if (typeof oAval === 'object'){
							if(oAval.lcTipoAval=='HC'){
								oEscalaCrusade.ConsultarAvalCR();
							}
						}
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda para escala CRUSADE.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar escala CRUSADE.");
		});
	},

	CargarEscala: function(taDatos, tbInhabilitar){
		$.each($('.selectCrusade'), function(lnIndex, loElemento){
			var lcValor = taDatos[$(loElemento).attr('data-letra')];
			$.each($(loElemento).find("option"), function(lnIndexOpt, loOption){
				var lpuntaje = $(this).attr("puntaje");
				if(lpuntaje==lcValor){
					lnValor = $(loOption).val();
					$(loElemento).attr('disabled', tbInhabilitar).val(lnValor).change();
				}
			});
		});

		$('#lnValorHematocrito').val(taDatos.HB).change();
		$('#lnValorCockcroft').val(taDatos.DC);
		oEscalaCrusade.nFreCardi = taDatos.FC;
		$('#lnValorFreCardi').val(taDatos.FC).change();
		$('#puntajeFallaCardi').val(taDatos.SF);
		oEscalaCrusade.nArteSisto = taDatos.PS;
		$('#lnValorArteSisto').val(taDatos.PS).change();
		oEscalaCrusade.nPeso = parseFloat(taDatos.Peso);
		oEscalaCrusade.obtenerCreatinina(parseFloat(taDatos.DC), oEscalaCrusade.nPeso);
	},

	actualizaRangosCreatinina: function(tnValorCockcroft){
		var lnCockcroft = parseFloat(tnValorCockcroft);
		$('#cboRangoCreatinina option').each(function(lnIndex, loOption){
			var lnMin = parseFloat($(this).attr('vrMin')),
				lnMax = parseFloat($(this).attr('vrMax'));
			if(lnCockcroft >= lnMin && lnCockcroft <= lnMax ){
				$('#puntajeCreatinina').text($(this).attr('puntaje'));
				$('#cboRangoCreatinina').val($(this).val());
				return false;
			}
		});
		$('#lnValorCockcroft').val(tnValorCockcroft);
		oEscalaCrusade.actualizarPuntajeCrusade();
	},

	obtenerCreatinina: function(tnCockcroft, tnPeso){
		// Valores para cálculo
		lnCockcroft = parseFloat(tnCockcroft);
		lnPeso = tnPeso;
		lnEdad = aDatosIngreso['aEdad']['y'];
		oEscalaCrusade.nEdad = lnEdad;
		lnSexo = (aDatosIngreso['cSexo']=='M'?1:0.85);

		// Depuración Creatinina
		if(lnCockcroft > 0){
			lnValor = Math.round(lnSexo * ((140 - lnEdad) / lnCockcroft) * (lnPeso / 72) * 100) / 100
			$('#lnValorCreatinina').val(lnValor);
			oEscalaCrusade.actualizaRangosCreatinina($('#lnValorCockcroft').val());
		}
	},

	confirmarObligatorio: function(){
		if ($("#lnValorHematocrito").val()==0 && $("#lnValorCreatinina").val()==0 && $("#cboFallaCardi").val()==-1 && $("#cboFallaCardi").val()==-1 && $('#cboVascularPrevia').val()==-1 && $("#cboDiabetesMellitus").val()==-1){
			oEscalaCrusade.bObligatoria = false;
		}else{
			oEscalaCrusade.bObligatoria = true;
		}
	},

	habilitarFrecuenciaPresion: function(tlhabilitar){
		if(oEscalaCrusade.lDatosEvolucion){
			$('#lnValorFreCardi,#lnValorArteSisto').attr('disabled', tlhabilitar);
		}
		$('#lnValorCreatinina,#lnValorHematocrito').attr('disabled', tlhabilitar);
	},

	actualizarDatos: function(){
		if(oEscalaCrusade.lbDatosConsulta==false){
			oEscalaCrusade.nFreCardi = $('#txtFC').val();
			oEscalaCrusade.nArteSisto = $('#txtTAS').val();
			$('#lnValorFreCardi').val($('#txtFC').val());
			$('#lnValorArteSisto').val($('#txtTAS').val());
		}
	},

	iniciarDatos: function(){
		$('#lnValorHematocrito,#cboRangoHematocrito,#lnValorCockcroft').val("");
		$('#puntajeHematocrito,#puntajeFallaCardi,#puntajeVascularPrevia,#puntajeDiabetesMellitus').text('');
		$('#cboRangoCreatinina,#cboFallaCardi,#cboVascularPrevia,#cboDiabetesMellitus').val(-1);
		$("#lnValorCreatinina").val(0.00);
		$('#puntajeCreatinina').text(0);
		if(oEscalaCrusade.lDatosEvolucion){
			oEscalaCrusade.nFreCardi = oEscalaCrusade.nArteSisto = 0;
			$('#puntajeFreCardi,#puntajeArteSisto').text('');
			$('#cboRangoFreCardi,#cboRangoArteSisto').val(-1);
			$('#lnValorFreCardi').val(0.00);
			$('#lnValorArteSisto').val(0);
		}
		oEscalaCrusade.actualizarPuntajeCrusade();
	},

	ConsultarAvalCR: function(){
		if (!(oAval.loDatosAval.Datos.escalaCrusade===undefined)){
			if(oAval.loDatosAval.Datos.escalaCrusade.EXISTE=='SI'){
				oEscalaCrusade.bLlenar = true;
				oEscalaCrusade.CargarEscala(oAval.loDatosAval.Datos.escalaCrusade, false);
			}
		}
	},

	validacion: function(){
		if(!oEscalaCrusade.bLlenar) return true;
		oEscalaCrusade.confirmarObligatorio();
		if(!oEscalaCrusade.bObligatoria) return true;
		var lbValido = true;
		$.each($('.selectCrusade'), function(lnIndex, loElemento){
			lnIndex = lnIndex+1;
			if(loElemento.value == "-1"){
				oEscalaCrusade.cObjetoError = loElemento.id;
				switch(loElemento.id){
					case 'cboRangoHematocrito':
						oEscalaCrusade.cObjetoError = 'lnValorHematocrito';
						break;
					case 'cboRangoCreatinina':
						oEscalaCrusade.cObjetoError = 'lnValorCreatinina';
						break;
					case 'cboRangoFreCardi':
						oEscalaCrusade.cObjetoError = (lDatosEvolucion?'lnValorFreCardi':'txtFC');
						break;
					case 'cboRangoArteSisto':
						oEscalaCrusade.cObjetoError = (lDatosEvolucion?'lnValorArteSisto':'txTAS');
						break;
				}
				oEscalaCrusade.cMensajeError = $('#lbldscCrusade'+lnIndex).html() +', debe ser valorado';
				$('#' + oEscalaCrusade.cObjetoError).focus()
				lbValido = false;
				return false;
			}
		});
		return lbValido;
	},

 	obtenerDatos: function(){
		oDatosCrusade = {};
		if(oEscalaCrusade.lbDatosConsulta==false){
			if(oEscalaCrusade.validaTodoLleno()){
				var oDatosCrusade = {
					lnPeso: oEscalaCrusade.nPeso,
					lnEdad: oEscalaCrusade.nEdad,
					lnGenero: oEscalaCrusade.cSexo,
					lnFreCardi:oEscalaCrusade.nFreCardi,
					lnArteSisto: oEscalaCrusade.nArteSisto,
					lnPuntaje: oEscalaCrusade.lnTotalPuntajeCrusade,
					lcInterpretacion: oEscalaCrusade.lcInterpretaCrusade,
					lnHematocrito: $('#lnValorHematocrito').val(),
					lnCockcroft: $('#lnValorCockcroft').val()
				};
				$.each($('.selectCrusade'), function(lnIndex, loElemento){
					var lcObjPuntaje = loElemento.id.replace('cbo', 'puntaje');
					oDatosCrusade[$(this).attr('data-letra')] = {
						lcLetra: $(this).attr('data-letra'),
						lnValor: $(this).val(),
						lnPuntaje: $('#'+lcObjPuntaje).text()
					};
				});
				return oDatosCrusade;
			}
		}
		return oDatosCrusade;
	},
};

//$('#txtFC,#chkFC,#lnValorFreCardi').change(oEscalaCrusade.calcularPuntajeFreCardiaca);
//$('#txtTAS,#chkTAS,#lnValorArteSisto').change(oEscalaCrusade.calcularPuntajeArteSisto);
$('#lnValorHematocrito').change(oEscalaCrusade.obtenerValorHematocrito);
$('#lnValorCreatinina').change(oEscalaCrusade.obtenerValorCreatinina);
$('#cboFallaCardi').change(oEscalaCrusade.obtenerValorFallaCardi);
$('#cboVascularPrevia').change(oEscalaCrusade.obtenerValorVascuPrev);
$('#cboDiabetesMellitus').change(oEscalaCrusade.obtenerValorDiaMelli);
$('.selectCrusade').change(oEscalaCrusade.actualizarPuntajeCrusade);
