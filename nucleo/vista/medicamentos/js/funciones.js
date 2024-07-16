var lcUrlAjaxFun = 'vista-medicamentos/buscar';
function buscarIngreso(tnIngreso, toFuncion){
	funTipoDispositivo();
	$('#divFiltro').hide("slow");
	$('#divIconoEspera').show();
	$.ajax({
		url: lcUrlAjaxFun,
		data: {accion:'ingreso', ingreso:tnIngreso},
		type: "POST",
		dataType: "json",
	})
	.done(function(loIngreso){
		try{
			if(loIngreso.error == ''){
				if(loIngreso.nIngreso > 0){
					if(loIngreso.cEstado.trim()=="2"){
						$('#infoPaciente').html(
							'<div class="row col-sm-12 col-md-12">' +
								'<div class="col-sm-5 col-md-1">' +
										'Ingreso'+
								'</div>' +
								'<div class="col-sm-5 col-md-1">' +
										'<span class="badge badge-success">'+loIngreso.nIngreso+'</span>' +
								'</div>' +
								'<div class="col-sm-5 col-md-1">' +
										'Paciente' +
								'</div>' +
								'<div class="col-sm-5 col-md-5" style="text-align:justify">' +
										'<span class="badge badge-success">'+loIngreso.cNombre+'</span>' +
								'</div>' +
									
								'<div class="col-sm-5 col-md-1">' +
										'Peso' +
								'</div>' +
								'<div class="col-sm-5 col-md-1">' +
										'<span class="badge badge-success">'+loIngreso.nPeso+' '+loIngreso.cTipoPeso+'</span>' +
								'</div>' +
									'<div class="col-sm-5 col-md-1">' +
										'Talla' +
								'</div>' +
								'<div class="col-sm-5 col-md-1">' +
										'<span class="badge badge-success">'+loIngreso.aTalla['valor']+' '+loIngreso.aTalla['unidad']+'</span>' +
								'</div>' +
								'<div class="col-sm-5 col-md-1">' +
										'Edad' +
								'</div>' +
								'<div class="col-sm-5 col-md-2">' +
										'<span class="badge badge-success">'+loIngreso.nEdad+'</span>' +
								'</div>' +
								'<div class="col-sm-5 col-md-1">' +
										'Genero' +
								'</div>' +
								'<div class="col-sm-5 col-md-1">' +
										'<span class="badge badge-success">'+loIngreso.cSexo+'</span>' +
								'</div>' +
								'<div class="col-sm-5 col-md-1">' +
										'Vía' +
								'</div>' +
								'<div class="col-sm-5 col-md-2">' +
										'<span class="badge badge-success">'+loIngreso.cDescVia+'</span>' +
								'</div>' +
								
								'<div class="col-sm-5 col-md-1 text-nowrap  bd-highlight">' +
										' Habitación' +
								'</div>' +
								'<div class="col-sm-5 col-md-1">' +
										'<span class="badge badge-success">'+loIngreso.cUbicacion+'</span>' +
								'</div>' +
							'</div>'
						);
						$('#divBody').show();
						$('#divDatosPaciente').show(200);
						if (toFuncion) toFuncion();
					}else{
						infoAlertClear();
						infoAlert('No esta activo el ingreso ' + parseInt(tnIngreso,10), "warning");
						$('#divDatosPaciente').hide();
						$('#divFiltro').show("slow");
						$("#lnNumeroIngreso").val("").focus();
					}
				}else{
					infoAlert('No se encontró el ingreso número ' + parseInt(tnIngreso,10), "info");
					$('#divFiltro').show("slow");
					$("#lnNumeroIngreso").val("").focus();
				}
			} else {
				infoAlert(loIngreso.error, "danger");
			}
		} catch(err){
			infoAlert('No se pudo realizar la busqueda del ingreso número...' + parseInt(tnIngreso,10), "danger");
		}
		$('#divIconoEspera').hide();
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		console.log(jqXHR.responseText);
		infoAlert('Se presento un error al buscar el ingreso número....'+ parseInt(tnIngreso,10), "danger");
		$('#divFiltro').show("slow");
		$("#lnNumeroIngreso").val("").focus();
		$('#divIconoEspera').hide();
	});
}
function buscarAntecedentes(tnIngreso, tcTipoAnte){
	$('#divIconoEspera').show();
	$('#infoAlergias').html('');
	$.ajax({
		url: lcUrlAjaxFun,
		data: {accion: 'Antecedentes', ingreso: tnIngreso, tipoAnte:tcTipoAnte},
		type: "POST",
		dataType: "json"
	})
	.done(function (loAntecedentes){
		try{
			if(loAntecedentes.error == ''){
				if(loAntecedentes.cTipoAntecedente !== ''){
					$('#infoAlergias').html(
						' '+loAntecedentes.cTipoAntecedente+ ' : ' +
						' <span class="badge badge-warning">'+loAntecedentes.cDescripcion+'</span>'
					);
					$('#divDatosPaciente').show(200);
				}else{
					infoAlert('No tiene alergias', "warning", false);
				}
			}else{
				infoAlert(loAntecedentes.error, "warning", false);
			}
		} catch(err){
			infoAlert('No se pudo realizar la busqueda de antecedentes', "danger", false);
		}
		$('#divIconoEspera').hide();
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		console.log(jqXHR.responseText);
		infoAlert('Se presento un error al realizar la busqueda de antecedentes', "danger");
		$('#divIconoEspera').hide();
	});
}
function infoAlertClear(){
	$('#divInfoAlert')
		.html('')
		.removeClass("alert alert-warning alert-danger")
		.removeAttr("role");
}
function infoAlert(tcHtml, tcClase, tbClear=true){
	if (tbClear) infoAlertClear();
	var lcIcon = '<i class="fa fa-exclamation-triangle"></i> ';
	var lcHtml = '<div class="alert alert-'+tcClase+'" alert-dismissable fade show" role="alert">'+lcIcon+tcHtml+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
	$('#divInfoAlert').append(lcHtml);
}
function empty(data){
	if(typeof(data) == 'number' || typeof(data) == 'boolean')
	{
		return false;
	}
	if(typeof(data) == 'undefined' || data === null)
	{
		return true;
	}
	if(typeof(data.length) != 'undefined')
	{
		return data.length == 0;
	}
	var count = 0;
	for(var i in data)
	{
		if(data.hasOwnProperty(i))
		{
			count ++;
		}
	}
	return count == 0;
}
function ajustar(tam, num) {
	if (num.toString().length < tam)
		return ajustar(tam, "0" + num)
	else return num;
}
function funEliminarFilas(toTabla){
	var lnNumFilas = toTabla.rows.length;
	for (i=1;i< lnNumFilas; i++){
		toTabla.deleteRow(1);
	}
}
function validarCodigoQr(tnCodigoQr, tcTipoQr){
	infoAlertClear();
	if (empty(tnCodigoQr)){
		infoAlert('No leyó, ni digitó ningún código QR. ', "warning");
		$('#lnCodigoQr').val('').focus();
	}else{
		var lnValidacion = 0;
		if (tnCodigoQr.length>25){
			lnValidacion = tnCodigoQr.slice(-25);
		}else{
			lnValidacion = ajustar(25, tnCodigoQr);
		}
		var lnExpresionRegular = /^\d{25}$/;
		var lcResultadoValidacion = lnExpresionRegular.test(lnValidacion);
		tnCodigoQr = lnValidacion;
		if( lcResultadoValidacion ===false || tnCodigoQr <= 999){
			infoAlert('El Código QR número : '+tnCodigoQr+'.  No se encuentra.<p>Por favor, verifique y digite o lea con el dispositivo el código QR, nuevamente.</p>', "warning");
			$("#lnCodigoQr").val('').focus();
		} else {
			var resultado = '';
			if (tcTipoQr=='ins'){
				var lnCodBun = lnValidacion.substr(20,5);
				if(lnCodBun==00000){
					$('#divBodegas').hide();
					$('#divBotones').hide();
					$('#divCantidadInsumos').show();
					$('#lnCantidadInsumo').focus();
					var lnCantidadInsumo = document.getElementById("lnCantidadInsumo").value;
					if(parseInt(lnCantidadInsumo)>=0){
						$("#lnCodigoQr").val(lnValidacion);
						resultado = "CodInsuValido";
					}
				}else{
					$("#lnCodigoQr").val(lnValidacion);
					resultado = "CodInsuValido";
				}
			}else{
				$("#lnCodigoQr").val(lnValidacion);
				resultado = "CodMedValido";
			}
			return resultado;
		}
	}
}
function funTipoDispositivo(){
	if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
	   console.log('¡ SI Esta consultando desde un dispositivo móvil !');
	   $('#divVacio').show();
	}else{	
		console.log('¡ NO Esta consultando desde un dispositivo móvil !');
	}
}