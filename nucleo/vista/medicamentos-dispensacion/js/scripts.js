var lcUrlAjax = 'vista-medicamentos-dispensacion/buscar';
var laMedicamentosDisp = [];
var gnIngreso='';
var gnFechaFormula='';
var gnBodega=0;
var gnCenCostos=0;
var gnConsecutivo=0;
var laCodigosQr = new Object();

$(document).ready(function(e){
	cargarSecciones();
	cargarVias();

	$('#lnNumeroIngreso').focus();
	$('#btnLimpiar').on('click', limpiar);

	$('#btnBuscar').on('click', fnBuscarMed);
	$('#lnNumeroIngreso,#lcFechaFormula,#lcSeccion,#lcVia').on('keydown', function(event) {
		if(event.which == 13){
			fnBuscarMed();
		}
	});
	
	$('#frmConsultarCodigoQr').on('submit', function(event){
		event.preventDefault();
		var lnCodigoQr = $('#lnCodigoQr').val();
		var lcTipoQr = 'med'
		var lcValida= validarCodigoQr(lnCodigoQr, lcTipoQr);
		switch (lcValida){
			case 'CodMedValido':
				var lnCodigoQr = $('#lnCodigoQr').val();
				dispensarMedicamentos(lnCodigoQr);
			break;
		}
	});
	
	$('#btnGuardarDispensacion').on('click', function(){
		var lnLargo = 0;
		for (var propiedad in laCodigosQr){
			if (laCodigosQr.hasOwnProperty(propiedad)){
				lnLargo = lnLargo + laCodigosQr[propiedad].length;
			}
		}
		if (lnLargo > 0 ){
			$('#btnGuardarDispensacion').attr('disabled', true);
			guardarDispensacion();
			for (var propiedad in laCodigosQr){
				if (laCodigosQr.hasOwnProperty(propiedad)){
					laCodigosQr[propiedad]=[];
				}
			}
		}else{
			infoAlert('Verifique, no ha leído ningún código QR', 'warning', true);
			$('#lnCodigoQr').val('').focus();
		}
	});
});

function fnBuscarMed(){
	infoAlertClear();
	var lnIngreso = ajustar(8, $('#lnNumeroIngreso').val());
	if(lnIngreso > 0){
		buscarIngreso(lnIngreso, function(){
			var lcFechaFormula = $('#lcFechaFormula').val();
			cargarMedicamentosFormulados(lcFechaFormula, lnIngreso);
		});
	}else{
		var lcFechaFormula = $('#lcFechaFormula').val();
		var lcSeccion = $('#lcSeccion').val();
		var lcVia = $('#lcVia').val();
		listarIngresosConFormula(lcFechaFormula, lnIngreso, lcSeccion, lcVia);
		$('#divFiltro').hide("slow");
	} 

} 

function listarIngresosConFormula(tcFechaFormula, tnIngreso, tcSeccion, tcVia){
	infoAlertClear();
	$('#divIconoEspera').show();
	var loTabla = $('#tblListaIngresosConFormula');
	$('#loListaIngresosConFormula tr').remove();
	$('#divFechaLista th').remove();
	$('#divFechaLista').append("<th>FORMULAS DEL DIA "+ tcFechaFormula+"</th>");
	//if(tcFechaFormula !== '' || tnIngreso === '' || tcSeccion !== '' || tcVia !== ''){
	if(tcFechaFormula !== '' || tnIngreso == 0 || tcSeccion !== '' || tcVia !== ''){
		var laData = {
			accion: 'listarIngresos',
			fecha: tcFechaFormula,
			seccion: tcSeccion,
			via: tcVia
		};
		$.ajax({
			url: lcUrlAjax,
			data: laData,
			type: 'POST',
			dataType: 'json'
		})
		.done(function(laListaIngresosConFormula){
			try{
				var lnLargo = laListaIngresosConFormula.length;
				if( lnLargo > 0){
					for (i = 0; i < lnLargo; i++){
						var lnEstaFor = laListaIngresosConFormula[i].ESTADO_FORMULA;
						var lcColor;
						switch (lnEstaFor){
							case  '11' :
								lcColor = 'FDDFE0'/*Formulado*/
								break;
							case  '12' :
								lcColor = 'F88B8E' /*Formulado Inmediato*/
								break;
							case  '13' :
								lcColor = 'FBBDBE' /*Formulado Modulado*/
								break;
							case  '14' :
								lcColor = 'FFFFC7' /*Suspendido*/
								break;
							case  '15' :
								lcColor = '9ED5FC' /*Dispensacion clompleta*/
								break;
							case  '16' :
								lcColor = 'DAEFFE' /*Dispensacion parcial*/
								break;
							default:
								lcColor = 'FFFFFF' /*No formulado*/
								break;
						}
						var lnIngreso = laListaIngresosConFormula[i].INGRESO;
						var lcNombres = laListaIngresosConFormula[i].PRIMER_NOMBRE +" "+laListaIngresosConFormula[i].SEGUNDO_NOMBRE +" "+ laListaIngresosConFormula[i].PRIMER_APELLIDO +" "+laListaIngresosConFormula[i].SEGUNDO_APELLIDO;
						var lcUbicacion = laListaIngresosConFormula[i].SECCION + " "+ laListaIngresosConFormula[i].HABITACION;
						var lcViaIngreso = laListaIngresosConFormula[i].DESCRIP_VIA;
						var lcFechaFormula = laListaIngresosConFormula[i].FECHA_FORMULA;
						var lcFila =
							"<tr bgcolor=\"#"+lcColor+"\" style=\"cursor:pointer;\">"+
								"<td style=\"border-bottom: 1px solid #bbb; padding: 1px; text-align:center;\">"+
									"<span class=\"ingresoLista\" fechaFormula=\""+lcFechaFormula+"\" lnNumeroIngreso=\""+lnIngreso+"\">"+
									"<strong>"+lnIngreso+"</strong></span>"+
									"<td class=\"ingresoLista\" fechaFormula=\""+lcFechaFormula+"\" lnNumeroIngreso=\""+lnIngreso+"\" style=\"border-bottom: 1px solid #bbb; padding: 1px;\">"+lcNombres+"</td>"+
									"<td class=\"ingresoLista\" fechaFormula=\""+lcFechaFormula+"\" lnNumeroIngreso=\""+lnIngreso+"\" style=\"border-bottom: 1px solid #bbb; padding: 1px;\">"+lcUbicacion+"</td>"+
									"<td class=\"ingresoLista\" fechaFormula=\""+lcFechaFormula+"\" lnNumeroIngreso=\""+lnIngreso+"\" style=\"border-bottom: 1px solid #bbb; padding: 1px;\">"+lcViaIngreso+"</td>"+
								"</td>"+
							"</tr>";
							$('#loListaIngresosConFormula').append(lcFila);

					};
					$(".ingresoLista").on({
/* 						mouseenter: function(){
							$(this).css("background-color", "#999");
						},
						mouseleave: function(){
							$(this).css("background-color", "#6FF");
						}, */
						click: function(){
							//$(this).css("background-color", "#0F0");
							$('#divListaIngresosConFormula').hide();
							buscarIngreso($(this).attr("lnNumeroIngreso"));
							cargarMedicamentosFormulados($(this).attr("fechaFormula"),$(this).attr("lnNumeroIngreso"));
							$('#divVolver').show();
							$('#btnRegresar')
								.off('click')
								.on('click',function(){
									$('#divDatosPaciente').hide();
									$('#divMedicamentosFormulados').hide();
									$('#divBodegas').hide();
									$('#divVolver').hide();
									$('#divCodigoMedicamento').hide();
									infoAlertClear();
									$('#divListaIngresosConFormula').show();
									listarIngresosConFormula(tcFechaFormula, tnIngreso, tcSeccion, tcVia);
							});
						}
					});
					$('#divBody').show();
					$('#divListaIngresosConFormula').show();
				}else{
					infoAlert('No hay pacientes con formula para la opción seleccionada', 'info', false);
					$('#divFiltro').show("slow");
					$('#lnNumeroIngreso').val('').focus();
				}
			}catch(error){
				infoAlert('No sepudo realizar la busqueda de los ingresos con formula', 'danger', false);
			}
			$('#divIconoEspera').hide();
		})
		.fail(function(jqXHR, textStatus, errorThrown){
			console.log(jqXHR.responseText);
			$('#divIconoEspera').hide();
			infoAlert('Se presento un error al buscar los ingresos con formula', 'danger', false);
		});

	}else{
		$('#divIconoEspera').hide();
		$('#divFiltro').show("slow");
		infoAlert('No selecciono ningun criterio de busqueda', 'warning', false);
	}
}

function cargarMedicamentosFormulados(tcFechaFormula, tnIngreso){
	gnFechaFormula = tcFechaFormula;
	gnIngreso = ajustar(8, tnIngreso);
	var tcFechaFormula = tcFechaFormula.replace(/-/g, '');
	$('#divIconoEspera').show();
	$('#divFechaFormula th').remove();
	$('#loMedicamentosFormulados tr').remove();
	laMedicamentosDisp = [];
	if($('#lnBodega')==null){
		var lnCodBodega =  '';
	}else{
		var lnCodBodega = $('#lnBodega').val();
	}
	gnBodega = lnCodBodega;
	gnCenCostos = $('#lcCenCosto').val();
	var anio = tcFechaFormula.substring(0, 4);
	var mes = tcFechaFormula.substring(4,6);
	var dia = tcFechaFormula.substring(6,8);
	var fechaMostrar =anio+'-'+mes+'-'+dia;
	$('#divFechaFormula').append("<th>MEDICAMENTOS FORMULADOS EL DIA "+fechaMostrar+"</th>");
	var laData = {
		accion: 'cargarMedicamentosFormulados',
		fecha: tcFechaFormula,
		//ingreso: tnIngreso,
		ingreso: gnIngreso,
		bodega: lnCodBodega
	};
	$.ajax({
		url: lcUrlAjax,
		data: laData,
		type: "POST",
		dataType: 'json'
	})
	.done(function(loListaMeFor){
		try{
			var lnLargo = loListaMeFor.length;
			$('#loMedicamentosFormulados tr').remove();
			laMedicamentosDisp = [];
			if((lnLargo) !== 0){
				var lnContador=0;
				for(i=0;i<lnLargo;i++){
					var lnIndice = i;
					var lnEstaFor = loListaMeFor[i].ESTMED;

					if(lnEstaFor!=15){
						lnContador++;
					}
					var lcColor;
					switch (lnEstaFor){
						case  '11' :
							lcColor = 'FDDFE0'/*Formulado*/
							break;
						case  '12' :
							lcColor = 'F88B8E' /*Formulado Inmediato*/
							break;
						case  '13' :
							lcColor = 'FBBDBE' /*Formulado Modulado*/
							break;
						case  '14' :
							lcColor = 'FFFFC7' /*Suspendido*/
							break;
						case  '15' :
							lcColor = '9ED5FC' /*Dispensacion clompleta*/
							break;
						case  '16' :
							lcColor = 'DAEFFE' /*Dispensacion parcial*/
							break;
						default:
							lcColor = 'FFFFFF' /*No formulado*/
							break;
					}
					var lcMedicamento = $.trim(loListaMeFor[i].MEDICAMENTO).replace(/\s/g, '_');
					var lnDispensar = (empty(loListaMeFor[i].DISPENSAR)?0:loListaMeFor[i].DISPENSAR);
					var lnDispensado = (empty(loListaMeFor[i].DISPENSADO)?0:loListaMeFor[i].DISPENSADO);
					var lnTotalDispensado = (empty(loListaMeFor[i].DESPACHADO)?0:loListaMeFor[i].DESPACHADO);
					var lcDesDosis = $.trim(loListaMeFor[i].DES_DOSIS);
					var lnSaldo = ( (loListaMeFor[i].SALDO==null) || (loListaMeFor[i].SALDO < 0) ? 0: (empty(loListaMeFor[i].SALDO)?0:loListaMeFor[i].SALDO) );
					var lcFila = "<tr bgcolor=\"#"+lcColor+"\" codMed=\""+$.trim(loListaMeFor[i].COD_MEDI)+"\">"+
								"<td class=\"\" style=\"border-bottom: 1px solid #bbb; padding: 1px;\">"+
									"<div>"+
										"<div class=\"\"><label id=\"lblNombreMedicamento\">"+$.trim(loListaMeFor[i].COD_MEDI)+"</label></div>"+
									"</div>"+
								"</td >"+
								"<td style=\"border-bottom: 1px solid #bbb; padding: 1px;\">"+
										"<div class=\"\"><label id=\"lblNombreMedicamento\">"+lcMedicamento+"</label></div>"+
								"</td>"+
								"<td style=\"border-bottom: 1px solid #bbb; padding: 1px;\">"+
										"<div class=\"\"><label id=\"lblMedicamento\">"+loListaMeFor[i].DOSIS+"_"+lcDesDosis.replace(/\s/g, '_')+"</label></div>"+
								"</td>"+
								"<td style=\"border-bottom: 1px solid #bbb; padding: 1px;\">"+
										"<div class=\"\"><label id=\"lblMedicamento\">"+loListaMeFor[i].COD_FREC+"_"+loListaMeFor[i].DESC_FREC+"</label></div>"+
								"</td>"+
								"<td style=\"border-bottom: 1px solid #bbb; padding: 1px;\">"+
										"<div class=\"\"><label id=\"lblMedicamento\">"+loListaMeFor[i].DESC_VIA+"</label></div>"+
								"</td>"+
								"<td style=\"border-bottom: 1px solid #bbb; padding: 1px;\">"+
									"<div>"+
									"<div class=\"\"><center><label class=\"control-label\" id=\"lblDispensar["+lnIndice+"]\">"+lnDispensar+"</label></center></div>"+
									"</div>"+
								"</td>"+
								"<td style=\"border-bottom: 1px solid #bbb; padding: 1px;\">"+
									"<div>"+
									"<div class=\"\"><center><label class=\"control-label\" id=\"lblDispensado"+lnIndice+"\">"+lnDispensado+"</label></center></div>"+
									"</div>"+
								"</td>"+
								"<td style=\"border-bottom: 1px solid #bbb; padding: 1px;\">"+
									"<div>"+
									"<div class=\"\"><center><label class=\"control-label\" id=\"lblTotalDispensado["+lnIndice+"]\">"+lnTotalDispensado+"</label></center></div>"+
									"</div>"+
								"</td>"+
								"<td style=\"border-bottom: 1px solid #bbb; padding: 1px;\">"+
									"<div>"+
									"<div class=\"\"><center><label class=\"control-label\" id=\"lblSaldoBodega["+lnIndice+"]\">"+lnSaldo+"</label></center></div>"+
									"</div>"+
								"</td>"+
							"</tr>";
					$('#loMedicamentosFormulados').append(lcFila);
					$('#divIconoEspera').hide();
					$('#divMedicamentosFormulados').show();
					laMedicamentosDisp.push( {COD_MEDI: $.trim(loListaMeFor[i].COD_MEDI), disp:0} );
					eval("laCodigosQr._"+$.trim(loListaMeFor[i].COD_MEDI)+"=[];");

				}
				if(fechaMostrar===vFechaAyer || fechaMostrar=== vFechaHoy){
					$('#divBodegas').show();
					$('#divCodigoMedicamento').show();
					$('#btnGuardarDispensacion').show();
					$('#lnCodigoQr').val('').focus();
					cargarConsecutivo(tcFechaFormula, tnIngreso);
				}

				$('#lnBodega')
					.off('change')
					.on('change', function(){
						cargarMedicamentosFormulados(tcFechaFormula, tnIngreso);
					});

				//$('#divCodigoMedicamento').show();
				if(lnContador==0){
					$('#divBodegas').hide();
					$('#divCodigoMedicamento').hide();
				}
			}else{
				infoAlert("El ingreso número...:<strong>"+" "+tnIngreso+"</strong>, NO tiene medicamentos formulados, para la fecha "+tcFechaFormula, "info", false);
				$('#divBody').hide();
			}
		}catch(error){
			infoAlert('No se pudo realizar la busqueda de medicamentos del paciente', "danger", false);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		console.log(jqXHR, responseText);
		$('#divIconoEspera').hide();
		infoAlert('Se presento un error al cargar los medicamentos del paciente y no se obtuvo el resultado deseado', "danger", false);
	});
}
function cargarConsecutivo(tcFechaFormula, tnIngreso){
	var laData = {
		accion: 'cargarConsecutivo',
		fecha: tcFechaFormula,
		ingreso: tnIngreso
	};
	$.ajax({
		url: lcUrlAjax,
		data: laData,
		type: 'POST',
		dataType: 'json'
	})
	.done(function(lnConsecutivo){
		try{
			$('#lnDispensacion').html('<strong>DISPENSACIÓN :</strong><span class="">'+' '+lnConsecutivo.CONDISP+'</span>');
			gnConsecutivo = lnConsecutivo.CONDISP;
		}catch(error){
			infoAlert('No se pudo cargar el consecutivo de dispensación para el paciente.', 'danger', false);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		infoAlert('Se presento un error al cargar el consecutivo de dispensación y no se obtuvo el resultado deseado', 'danger', false);
	});
}

function dispensarMedicamentos(tnCodigoQr){
	if(!empty(gnBodega)){
		var laData = {
			accion: 'dispensarMedicamentos',
			fecha: gnFechaFormula,
			ingreso: gnIngreso,
			bodega: gnBodega,
			codigoQr: tnCodigoQr
		};
		$.ajax({
			url: lcUrlAjax,
			data: laData,
			type: 'POST',
			dataType: 'json',
		})
		.done(function(laDispensarMedicamento){
			try{
				if(laDispensarMedicamento['CODMEN'] === 5 ){
					for(i=0; i < laMedicamentosDisp.length; i++){
						if(laMedicamentosDisp[i].COD_MEDI == laDispensarMedicamento['CODSHA']){
							if ((laMedicamentosDisp[i].disp) < (laDispensarMedicamento['CAN_PENDI'])){
								var laMedica = eval("laCodigosQr._"+laDispensarMedicamento['CODSHA']);
								var lnLargo = laMedica.length,
									lnExiste = false;
								for (j=0; j < lnLargo; j++){
									if(tnCodigoQr == laMedica[j] && laDispensarMedicamento['LNCODBUN'] != 00000){
										lnExiste = true;
										break;
									}
								};
								if(lnExiste == true){
									infoAlert('Por favor verifique, el código de barras No.'+tnCodigoQr+'. del medicamento ya fue leido.', 'info', false);
								}else{
									if(laMedicamentosDisp[i].disp < laDispensarMedicamento['SALDO']){
										laMedicamentosDisp[i].disp = laMedicamentosDisp[i].disp+1;
										$('#lblDispensado'+i).html(laMedicamentosDisp[i].disp);
										eval('laCodigosQr._'+laDispensarMedicamento['CODSHA']+'.push(tnCodigoQr);');
									}else{
										infoAlert('No hay saldo en la bodega', 'info', false);
									}
								}
							}else{
								infoAlert('La cantidad dispensada + Total dispensado, no puede ser mayor que la cantidad a dispensar.', 'info', false);
							}
						}
					};
				}else{
					infoAlert(laDispensarMedicamento['MENSAJE'], 'info', false);
					$('#lnCodigoQr').val('').focus();
				}
				$('#lnCodigoQr').val('').focus();
			}catch(error){
				infoAlert('No se pudo realizar la dispensacion del medicamento', 'danger', false);
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown){
			console.log(jqXHR.responseText);
			infoAlert('Se presento un error al realizar la dispensación de medicamentos y no se obtuvo el resultado deseado', 'danger', false);
		});
	}else{
		infoAlert('No hay bodega seleccionada, no se puede continuar', 'info', false);
		$('#lnCodigoQr').val('').focus();
	}
}

function guardarDispensacion(){
	if (!empty(gnCenCostos)){
		var laData = {
			accion: 'guardarDispensacion',
			fecha: gnFechaFormula,
			ingreso: gnIngreso,
			bodega: gnBodega,
			cenCostos: gnCenCostos,
			codigosQr: laCodigosQr,
			consecutivo: gnConsecutivo,
		};
		$.ajax({
			url: lcUrlAjax,
			data: laData,
			type: 'POST',
			dataType: 'json',
		})
		.done(function(laGuardarDispensacion){
			try{
				if(laGuardarDispensacion['CODMEN']===1){
					infoAlert(laGuardarDispensacion['MENSAJE'], 'success', true);
					cargarMedicamentosFormulados(gnFechaFormula, gnIngreso);
				}else{
					infoAlert(laGuardarDispensacion['MENSAJE'], 'warning', false);
				}
			}catch(error){
				infoAlert('No se pudo guardar la dispensación', 'danger', false);
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown){
			console.log(jqXHR.responseText);
			infoAlert('Se presento un error al guardar la dispensación y no se obtuvo el resultado deseado', 'danger',false);
		});
	}else{
		alert('No tiene centro de costos, no se puede continuar', 'info', false);
	}
	$('#btnGuardarDispensacion').attr('disabled', false);
}

function limpiar(){
	infoAlertClear();
	$('#lcSeccion').val('');
	$('#lcVia').val('');
	$('#lcFechaFormula').val(vFechaHoy);
	$('#divDatosPaciente').hide();
	$('#divListaIngresosConFormula').hide();
	$('#divMedicamentosFormulados').hide();
	$('#divBodegas').hide();
	$('#divFiltro').show("slow");
	$('#lnNumeroIngreso').val('').focus();
	$('#divVolver').hide();
	$('#divCodigoMedicamento').hide();
	$('#btnGuardarDispensacion').hide();
}
function cargarSecciones(){
	var loOption = new Option("Seleccione  ...", "");
	$('#lcSeccion').append(loOption);
	var laData = {
		accion: 'listaSecciones'
	};
	$.ajax({
		url: lcUrlAjax,
		data: laData,
		type: 'POST',
		dataType: 'json'
	})
	.done(function(laSecciones){
		try{
			if(!$.isEmptyObject(laSecciones) == true){
				$.each(laSecciones, function(id,value){
					var Seccion = {
							'lcCodSecc': ''+id+'' ,
							'lcDescriSecc': ''+value['NOMBRE']+''
					};
					var loOption = new Option(Seccion.lcDescriSecc, Seccion.lcCodSecc);
					$('#lcSeccion').append(loOption);
				});
			}
		}catch(error){
			infoAlert('No se pudo realizar la busqueda de secciones', "danger", false);
		}
		$('#divIconoEspera').hide();
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		console.log(jqXHR.responseText);
		$('#divIconoEspera').hide();
		infoAlert('Se presento un error al cargar las Secciones y no se obtuvo el resultado deseado', "danger", false);
	});
}
function cargarVias(){
	var loOption = new Option("Seleccione  ...", "");
	$('#lcVia').append(loOption);
	var laData = {
		accion: 'listaVias'
	};
	$.ajax({
		url: lcUrlAjax,
		data: laData,
		type: 'POST',
		dataType: 'json'
	})
	.done(function(loVias){
		try{
			if(!$.isEmptyObject(loVias)==true){


				$.each(loVias, function(id, value){
					var lcVia = {
							'lcId': ''+value['CODVIA']+'',
							'lcDescripcion':''+value['DESVIA']+''
					};
					var loOption = new Option($.trim(lcVia.lcDescripcion), lcVia.lcId);
					$('#lcVia').append(loOption);
				});
			}
		}catch(error){
			infoAlert('No se pudo realizar la busqueda de las vias de ingreso', "danger", false);
		}
		$('#divIconoEspera').hide();
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		console.log(jqXHR.responseText);
		$('#divIconoEspera').hide();
		infoAlert('Se presento un error al cargar las vias de ingreso y no se obtuvo el resultado deseado', "danger", false);
	});
}