var lcUrlAjax = 'vista-medicamentos-administracion/buscar';//Ruta para el ajax
$(document).ready(function(e){
	$(function (){
		$('#lnNumeroIngreso').focus();
		$('#btnLimpiar').on('click', limpiar);
		$('#btnBuscar').on('click', fnBuscarMed);
		$('#lnNumeroIngreso').on('keydown', function(event) {
			if (event.which == 13) {
				fnBuscarMed();
			}
		});
		$('#loMedicamentosQueDiluyen').on("click", ".listaDilu", function(){
			var lnIngreso = $('#lnNumeroIngreso').val();
			$('#lnCodigoQrDiluyente').val($(this).text());
			$('#divMedicamentosQueDiluyen').hide();
			administrarMedicamentosPaciente(lnIngreso);
		});
		$("#frmConsultarCodigoQr").on("submit", function(event){
			event.preventDefault();
			var lnIngreso = $('#lnNumeroIngreso').val();
			var lnCodigoQr = $('#lnCodigoQr').val();
			var lcTipoQr = 'med'
			var lcValida= validarCodigoQr(lnCodigoQr, lcTipoQr);
			switch (lcValida){
				case 'CodMedValido':
					administrarMedicamentosPaciente(lnIngreso);
					break;
			}
		});
	});
});
function fnBuscarMed() {
	infoAlertClear();
	var lnIngreso = $('#lnNumeroIngreso').val();
	if(lnIngreso.length !== 0){
		buscarIngreso(lnIngreso, function(){
			buscarAntecedentes(lnIngreso, '8');
			cargarMedicamentosProgramados(lnIngreso);
			cargarMedicamentosNoProgramados(lnIngreso);
		});
	}else{
		infoAlert('No ingreso ningún número ', "warning");
		$('#lnNumeroIngreso').focus();
	}
}
function cargarMedicamentosProgramados(tnIngreso){
	$('#divIconoEspera').show();
	$('#loMedicamentosProgramados tr').remove();
	var laData={
		accion: 'cargarMedicamentosProgramados',
		ingreso: tnIngreso
	};
	$.ajax({
		url: lcUrlAjax,
		data: laData,
		type: "POST",
		dataType: 'json',
	})
	.done(function(loListaMePro){
		try{
			var lnLargo = loListaMePro.length;
			if(lnLargo == 0){
				infoAlert('Para esta hora no tiene medicamentos programados. ', "info", false );
			}else{
				for (i = 0; i < lnLargo; i++){
					var lcFecha = loListaMePro[i].FEC_PROGRA;
					var lcAnio = lcFecha.substr(0,4);
					var lcMes = lcFecha.substr(4,2);
					var lcDia = lcFecha.substr(6,2);
					var lcHoras = loListaMePro[i].HORA_PROGRA;
					if ((lcHoras.length)==5){
						var lcHora = lcHoras.substr(0,1);
						var lcMinu = lcHoras.substr(1,2);
					} else {
						var lcHora = lcHoras.substr(0,2);
						var lcMinu = lcHoras.substr(2,2);
					}
					var lnEstaFor = loListaMePro[i].EST_FORM;
					var lcColor;
					switch (lnEstaFor){
						case  '2' :
							lcColor = 'D5AAFF'
							break;
						case  '3' :
							lcColor = 'FA1414'
							break;
						case  '4' :
							lcColor = '99DFE0'
							break;
						case  '5' :
							lcColor = 'FF854A'
							break;
						case  '6' :
							lcColor = 'F1F389'
							break;
						case  '12' :
							lcColor = 'F88B8E'
							break;
						case  '15' :
							lcColor = '9ED5FC'
							break;
						case  '16' :
							lcColor = '06FF06'
							break;
						default:
							lcColor = 'FFFFFF'
							break;
					}
					var lcMedicamento = $.trim(loListaMePro[i].MDDADM).replace(/\s/g, '_');
					var lcDesDosis = $.trim(loListaMePro[i].DES_DOSIS).replace(/\s/g, '_');
					var lcFila = "<tr bgcolor=\"#"+lcColor+"\">"+
									"<td style=\"padding: 1px;\"><div id=\"lblFechaProgramada\">"+lcAnio+ "/" +lcMes+ "/" +lcDia+"</div></td>"+
									"<td style=\"padding: 1px;\"><div id=\"lblHoraProgramada\">"+lcHora+":"+lcMinu+"</div></td>"+
									"<td style=\"padding: 1px;\"><div id=\"lblCodigoMedicamento["+i+"]\">"+loListaMePro[i].COD_MEDICA+"</div></td>"+
									"<td style=\"padding: 1px;\"><div id=\"lblNombreMedicamento["+i+"]\">"+lcMedicamento+"</div></td>"+
									"<td style=\"padding: 1px;\"><div id=\"lblDosisMedicamento\">"+loListaMePro[i].DOSIS+" "+lcDesDosis+"</div></td>"+
									"<td style=\"padding: 1px;\"><div id=\"lblDescriFrecuenciaMedicamento\">"+loListaMePro[i].CADA_FREC+" "+loListaMePro[i].DESC_FREC+"</div></td>"+
									"<td style=\"padding: 1px;\"><div id=\"lblViaMedicamento\">"+loListaMePro[i].DESC_VIA+"</div></t>"+
									"<td style=\"padding: 1px;\"><div id=\"lblDescriObservacionesMedico\">"+"<strong>"+loListaMePro[i].OBS_MED+"</strong>"+"</div></td>"+
									"<td style=\"padding: 1px;\"><div id=\"lblDescriObservacionesAdmini\">"+"<strong>"+loListaMePro[i].OBS_ADM+"</strong>"+"</div></td>"+
								"</tr>";
					$('#loMedicamentosProgramados').append(lcFila);
					$('#divIconoEspera').hide();
					$('#divBody').show();
					$('#divMedicamentosPaciente').show();
					$('#divMedicamentos').show();
					if(lnEstaFor == 4){
						$('#divCodigoMedicamento').show();
						$("#lnCodigoQr").focus();
					}
				}
			}
		}catch(err){
			infoAlert('No se pudo realizar la busqueda medicamentos programados para el ingreso Número...' + tnIngreso, "danger", false);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		console.log(jqXHR.responseText);
		$('#divIconoEspera').hide();
		infoAlert('Sepresentó un error al buscar medicamentos programados',"danger", false);
	});
}
function cargarMedicamentosNoProgramados(tnIngreso){
	$('#divIconoEspera').show();
	$('#loMedicamentosNoProgramados tr').remove();
	var laData ={
		accion:'cargarMedicamentosNoProgramados',
		ingreso: tnIngreso
	};
	$.ajax({
		url: lcUrlAjax,
		data: laData,
		type: "POST",
		dataType: 'json',
	})
	.done(function(loListaMeNoPro){
		try{
			var lnLargo = loListaMeNoPro.length;
			if(lnLargo == 0){
				infoAlert('No tiene medicamentos Por Programar. ', "info", false);
			}else{
				for(i = 0; i < lnLargo; i++){
					var lcFecha = loListaMeNoPro[i].FECHA_ORDEN;
					var lnEstaFor = loListaMeNoPro[i].ESTFRD;
					var lcColor;
					switch (lnEstaFor){
							case  '11' :
								lcColor = 'F1F389'
								break;
							case  '12' :
								lcColor = '278BFF'
								break;
							case  '13' :
								lcColor = 'FBBDBE'
								break;
							case  '14' :
								lcColor = 'FA1414'
								break;
							case  '15' :
								lcColor = '97FA19'
								break;
							case  '16' :
								lcColor = '06FF06'
								break;
							default:
								lcColor = 'FF0000'
								break;
					}
					var lcAnio = lcFecha.substr(0,4);
					var lcMes = lcFecha.substr(4,2);
					var lcDia = lcFecha.substr(6,2);
					var lcHoras = loListaMeNoPro[i].HORA_ORDEN;
					if((lcHoras.length)==5){
						var lcHora = lcHoras.substr(0,1);
						var lcMinu = lcHoras.substr(1,2);
					}else{
						var lcHora = lcHoras.substr(0,2);
						var lcMinu = lcHoras.substr(2,2);
					}
					var lcMedicamento = $.trim(loListaMeNoPro[i].MEDICAMENTO).replace(/\s/g, '_');
					var lcDesDosis = $.trim(loListaMeNoPro[i].DES_DOSIS).replace(/\s/g, '_');
					var lcFila = "<tr bgcolor=\"#"+lcColor+"\">"+
									"<td style=\"padding: 1px;\"><div id=\"lblFechaProgramada\">"+lcAnio+ "/" +lcMes+ "/" +lcDia+"</div></td>"+
									"<td style=\"padding: 1px;\"><div id=\"lblHoraProgramada\">"+lcHora+":"+lcMinu+"</div></td>"+
									"<td style=\"padding: 1px;\"><div id=\"lblCodigoMedicamento["+i+"]\">"+loListaMeNoPro[i].CODI_MEDIC+"</div></td>"+
									"<td style=\"padding: 1px;\"><div id=\"lblNombreMedicamento["+i+"]\">"+lcMedicamento+"</div></td>"+
									"<td style=\"padding: 1px;\"><div id=\"lblDosisMedicamento\">"+loListaMeNoPro[i].DOSIS+"_"+lcDesDosis+"</div></td>"+
									"<td style=\"padding: 1px;\"><div id=\"lblDescriFrecuenciaMedicamento\">"+loListaMeNoPro[i].CADA_FREC+" "+loListaMeNoPro[i].DESC_FREC+"</div></td>"+
									"<td style=\"padding: 1px;\"><div id=\"lblViaMedicamento\">"+loListaMeNoPro[i].DESC_VIA+"</div></t>"+
									"<td style=\"padding: 1px;\"><div id=\"lblDescriObservacionesMedico\">"+"<strong>"+loListaMeNoPro[i].OBS_MED+"</strong>"+"</div></td>"+
									"<td style=\"padding: 1px;\"><div id=\"lblDescriObservacionesAdmini\">"+"<strong>"+loListaMeNoPro[i].OBS_ADM+"</strong>"+"</div></td>"+
								"</tr>";
					$('#loMedicamentosNoProgramados').append(lcFila);
					$('#divIconoEspera').hide();
					$('#divBody').show();
					$('#divMedicamentosPaciente').show();
					$('#divMedicamentos').show();
				}
			}
		}catch(err){
			infoAlert('No se pudo realizar la busqueda medicamentos No programados para el ingreso Número...' + tnIngreso, "danger");
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		console.log(jqXHR.responseText);
		$('#divIconoEspera').hide();
		infoAlert('Sepresentó un error al buscar medicamentos No programados',"danger");
	});
}
function administrarMedicamentosPaciente(tnIngreso){
	$('#divIconoEspera').show();
	var lnCodigoQrValido = $('#lnCodigoQr').val();
	var lnCodigoQrDiluyente = $('#lnCodigoQrDiluyente').val();
	var laData={
		accion: 'administrarMedicamentosPaciente',
		ingreso: tnIngreso,
		CodigoQr :lnCodigoQrValido,
		CodigoQrDiluyente : lnCodigoQrDiluyente
	};
	$.ajax({
		url: lcUrlAjax,
		data: laData,
		type: "POST",
		dataType: 'json',
	})
	.done(function(laAdministrarMedicamento){
		try{
			if(laAdministrarMedicamento == 1){
				$('#divIconoEspera').hide();
				$('#divCodigoMedicamento').hide();
				$('#lnCodigoQrDiluyente').val('');
				infoAlert('El medicamento ha sido administrado correctamente.', "success");
				cargarMedicamentosProgramados(tnIngreso);
				$('#lnCodigoQr').val('').focus();
			}else if(laAdministrarMedicamento == -1){
				$('#divIconoEspera').hide();
				infoAlert('El Código QR número : '+lnCodigoQrValido+', no ha sido dispensado para el paciente.  Por favor verifique.', "warning");
				$('#lnCodigoQr').val('').focus();
			}else if (laAdministrarMedicamento==0){
				$('#divIconoEspera').hide();
				infoAlert('El Código QR número : '+lnCodigoQrValido+',  no se encuentra programado para administrar a esta hora, Por favor, verifique la hora y el medicamento que va a administrar ', "warning", false);
				$('#lnCodigoQr').val('').focus();
			}else{
				$('#divIconoEspera').hide();
				$('#divMedicamentos').hide();
				$('#divCodigoMedicamento').hide();
				//$('#divBody').show();
				$('#divMedicamentosQueDiluyen').show();
				var lnLargo = laAdministrarMedicamento.length;
				var loTabla = document.getElementById("tblListaDiluyentes");
				funEliminarFilas(loTabla);
				for(i=0;i<lnLargo;i++){
					var lcCodigoDilu = $.trim(laAdministrarMedicamento[i].CODIGODILU);
					var lcMediDilu = $.trim(laAdministrarMedicamento[i].DILUYENTE);
					var lcCantiDilu = $.trim(laAdministrarMedicamento[i].CANTIDAD);
					var lcFila = '<tr><td><span class="listaDilu">'+ lcCodigoDilu +"</span></td><td>"+ lcMediDilu +"</td><td>"+lcCantiDilu+"</td></tr>";
					$('#loMedicamentosQueDiluyen').append(lcFila);
					$(".listaDilu").on({
						mouseenter: function(){
							$(this).css("background-color", "#999");
						},
						mouseleave: function(){
							$(this).css("background-color", "#6FF");
						},
						click: function(){
							$(this).css("background-color", "#0F0")
						}
					});
				};
			}
		}catch(err){
			infoAlert('No se pudo realizar la administración del medicamento', "danger", false);
			$('#lnCodigoQr').val('').focus();
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		console.log(jqXHR.responseText);
		$('#divIconoEspera').hide();
		infoAlert('Se presento un error al Administrar los medicamentos y no se obtuvo el resultado deseado', "danger", false);
	});
}

function limpiar(){
		infoAlertClear();
		$('#divFiltro').show("slow");
		$('#divDatosPaciente').hide();
		$('#divMedicamentosPaciente').hide();
		$('#divIconoEspera').hide();
		$("#lnNumeroIngreso").val("").focus();
}