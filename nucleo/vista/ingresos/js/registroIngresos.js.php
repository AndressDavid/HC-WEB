<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../publico/headJSCRIPT.php');

	require_once (__DIR__ .'/../../../controlador/class.UbicacionesGeograficas.php');
	require_once (__DIR__ .'/../../../controlador/class.Ingreso.php');

	$lcTipoIngreso = (isset($_GET['q'])?$_GET['q']:'DEFAULT');
	$laPaises = (new NUCLEO\UbicacionesGeograficas())->getPaises();
	$laPropiedadesIngreso = (new NUCLEO\Ingreso())->getParametrosModuloIngreso();

?>
var gaPaises = <?php print(json_encode($laPaises??''));?>;
var gaPropiedadesIngreso = <?php print(json_encode($laPropiedadesIngreso??''));?>;
var glGuardado = false;
var goConfirmarEntrada = $('#modalConfirmarEntrada');
var goConfirmarEntradaInput = $('#vModalConfirmarEntrada');
var goConfirmarEntradaLabel = $('#modalConfirmarEntradaLabel');
var goFormPacienteContacto = null;
var goFormPacienteGeneral = null;
var goFormPacienteInformacion = null;
var goFormPacienteLaboral = null;
var goFormPacientePlan = null;
var goFormPacienteRecide = null;
var goFormPacienteReferencia = null;
var goFormPacienteRemision = null;
var goFormResponsableInformacion = null;
var goFormResponsableLaboral = null;
var goFormResponsableRecide = null;
var goFormResponsableReferencia = null;
var goFormServicioEditarEntidad = null;
var goFormServicioInformacion = null;
var goFormServicioPlanUsar = null;
var goMobile = new MobileDetect(window.navigator.userAgent);

$.fn.onEnter = function(func) {
	this.bind('keypress', function(e) {
		if (e.keyCode == 13) func.apply(this, [e]);
	});
	return this;
};

$.fn.inputLectorPdf417 = function(toOpciones) {
	let opciones = $.extend({
		funcion: false
	}, toOpciones );

	this.each(function() {
		let loImput = $(this);
		let loModal = $(this).parent().closest('.modal');
		let lvFuncion = loImput.attr('data-funcion') ? loImput.attr('data-funcion') : opciones.funcion;

		let loRW = loModal.find('.pdf417-rw');
		let loBT = loModal.find('.pdf417-bt');
		let loID = loModal.find('.pdf417-id');
		let loAA = loModal.find('.pdf417-apellido1');
		let loAB = loModal.find('.pdf417-apellido2');
		let loNA = loModal.find('.pdf417-nombre1');
		let loNB = loModal.find('.pdf417-nombre2');
		let loSX = loModal.find('.pdf417-genero');
		let loFN = loModal.find('.pdf417-nacio');
		let loGS = loModal.find('.pdf417-gsrh');

		loImput.on("cut copy paste",function(e) {
			e.preventDefault();
		}).on("change", function() {
			let lcString = $(this).val();
			let lcError = "";
			$(this).attr('readonly','readonly');

			loBT.data('buffer','');

			$(this).parent().find('input').each(function() {
				$(this).val('');
			});

			if(lcString!="" && lcString!=undefined){
				try {
					var laCedula = JSON.parse(lcString);
					if(laCedula.MD!=undefined && laCedula.ID!=undefined && laCedula.AA!=undefined && laCedula.AB!=undefined && laCedula.NA!=undefined && laCedula.NB!=undefined && laCedula.ND!=undefined && laCedula.SX!=undefined && laCedula.FN!=undefined && laCedula.RH!=undefined){
						if(laCedula.MD=="DF-PDF417" && laCedula.WR=="NO-COPY"){
							let lnCedulaNumero = parseInt(laCedula.ID);
							if(lnCedulaNumero>0){

								loBT.data('target','#'+loModal.prop('id'));
								loBT.data('buffer',lcString);

								loID.val(lnCedulaNumero);
								loAA.val(laCedula.AA);
								loAB.val((laCedula.ND=="1"?"DE ":"")+laCedula.AB);
								loNA.val(laCedula.NA);
								loNB.val(laCedula.NB);
								loSX.val(laCedula.SX);
								loFN.val(laCedula.FN);
								loGS.val(laCedula.RH);

								loRW.addClass('d-none');
								loBT.removeAttr('readonly');

								if(typeof lvFuncion === 'function'){
									lvFuncion();
								}
							}else{
								lcError = 'El numero de documento no es valido.';
							}
						}else{
							lcError = 'Formato no valido.';
						}
					}
				} catch (error) {
					lcError = 'La entrada de texto en la casilla <b>Lectura de c&oacute;digo de barras</b> no es valida';
				}

				if(lcError!=""){
					$.alert({
						title: 'Información no valida',
						content: lcError,
					});
				}
			}
			$(this).prop('readonly','').val('').trigger('focus');
		});

		loModal.on('shown.bs.modal', function (event) {
			$(this).find('.pdf417-rw').removeClass('d-none');
			$(this).find('.pdf417-input-len').empty();
			$(this).find('input').each(function () {
				$(this).val('');
			})

			$(this).find('.pdf417-bt').data('callback',$(event.relatedTarget).data('callback')).attr('readonly','readonly');

			loImput.trigger('focus');
		});

		loBT.click(function() {
			let loModal = $($(this).data('target'));
			let lcCallBack = $(this).data('callback');

			if(eval("typeof "+lcCallBack) === 'function'){
				eval(lcCallBack+"('"+$(this).data('buffer')+"')");
			}

			loModal.find('input').each(function () {
				$(this).val('');
			})
			loModal.modal('hide');

		})

	});
	return this;
};

$.fn.selectSimple = function(toOpciones) {
	var opciones = $.extend({
		accion: "",
		valor: "",
		funcion: false
	}, toOpciones );

	this.each(function() {
		let loSelect = $(this);
		let lcAccion = loSelect.attr('data-accion') ? loSelect.attr('data-accion') : opciones.accion;
		let lcValor = loSelect.attr('data-valor') ? loSelect.attr('data-valor') : opciones.valor;
		let lvFuncion = loSelect.attr('data-funcion') ? loSelect.attr('data-funcion') : opciones.funcion;

		loSelect.append('<option value=""></option>');

		$.ajax({
			type: "POST",
			url: "vista-ingresos/ajax/registroIngresos.ajax",
			data: {accion:lcAccion},
			dataType: "json"
		})
		.done(function(response) {
			if(response!==undefined){
				$.each(response, function( key, row ) {
					loSelect.append($("<option></option>").attr("value", row.CODIGO).text(row.NOMBRE));
				});
				if(typeof lvFuncion === 'function'){
					lvFuncion();
				}
			}
			loSelect.val(lcValor);
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			alert("Se presentó un error al buscar "+lcAccion+". \n"+jqXHR.responseText);
		});
	});
	return this;
};

$.fn.ubicacionGeografica = function(toOpciones) {
	var opciones = $.extend({
		pais: "",
		departamento: "",
		ciudad: "",
	}, toOpciones );

	this.each(function() {
		let loControl = $(this);
		let lcPais = loControl.attr('data-pais') ? loControl.attr('data-pais') : opciones.pais;
		let lcDepartamento = loControl.attr('data-departamento') ? loControl.attr('data-departamento') : opciones.departamento;
		let lcCiudad = loControl.attr('data-ciudad') ? loControl.attr('data-ciudad') : opciones.ciudad;

		let loPais = loControl.find('.lugarPais');
		let loDepartamento = loControl.find('.lugarDepartamento');
		let loCiudad = loControl.find('.lugarCiudad');

		$(this).init.prototype.cargar = function(pais, departamento, ciudad){
			$(this).data('pais', pais).data('departamento', departamento).data('ciudad', ciudad).find('.lugarPais').trigger('change');
		};

		loPais.empty().append("<option></option>");
		if(typeof gaPaises !== "undefined"){
			$.each(gaPaises, function( key, row ) {
				loPais.append($("<option></option>").attr("value", row.CODIGO).text(row.NOMBRE));
			});
		}

		loPais.on("change", function() {

			loDepartamento.empty().append("<option></option>").trigger('change').attr('disabled','disabled');

			if(loControl.data('pais')!==undefined){
				$(this).val(loControl.data('pais'));
			}

			if(parseInt($(this).val())>0){
				$.ajax({
					type: "POST",
					url: "vista-ingresos/ajax/registroIngresos.ajax",
					data: {accion:'departamentos', pais:$(this).val()},
					dataType: "json"
				})
				.done(function(response) {
					if(response!==undefined){
						let lnDepartamentos = 0;
						$.each(response, function( key, row ) {
							lnDepartamentos++;
							loDepartamento.append($("<option></option>").attr("value", row.CODIGO).text(row.NOMBRE));
						});

						if(lnDepartamentos>0){
							loDepartamento.removeAttr('disabled').trigger('change');
						}

						if(loControl.data('departamento')!==undefined){
							loDepartamento.val(loControl.data('departamento')).trigger('change');
						}
					}
				})
				.fail(function(jqXHR, textStatus, errorThrown) {
					alert("Se presentó un error al cargar los departamentos. \n"+jqXHR.responseText);
				});
			}
		});

		loDepartamento.on("change", function() {

			loCiudad.empty().append("<option></option>").trigger('change').attr('disabled','disabled');
			if(parseInt(loPais.val())>0 && parseInt($(this).val())>0){
				$.ajax({
					type: "POST",
					url: "vista-ingresos/ajax/registroIngresos.ajax",
					data: {accion:'ciudades', pais:loPais.val(), departamento:$(this).val()},
					dataType: "json"
				})
				.done(function(response) {
					if(response!==undefined){
						let lnCiudades = 0;
						let lcCiudad = '';
						$.each(response, function( key, row ) {
							lnCiudades++;
							lcCiudad = 	row.CODIGO;
							loCiudad.append($("<option></option>").attr("value", lcCiudad).text(row.NOMBRE));
						});

						if(lnCiudades>0){
							loCiudad.removeAttr('disabled').trigger('change');
						}

						if(lnCiudades==1 && lcCiudad!==''){
							loCiudad.val(lcCiudad).trigger('change');
						}
					}
				})
				.fail(function(jqXHR, textStatus, errorThrown) {
					alert("Se presentó un error al cargar las ciudades. \n"+jqXHR.responseText);
				});
			}

			if(loControl.data('departamento')!==undefined){
				$(this).val(loControl.data('departamento'));
			}
		});

		loCiudad.data('callback', $(this).data('callback')).on("change", function() {

			let lcCallBack = $(this).data('callback');
			if(eval("typeof "+lcCallBack) === 'function'){
				eval(lcCallBack+"('"+$(this).data('buffer')+"')");
			}

			if(loControl.data('ciudad')!==undefined){
				$(this).val(loControl.data('ciudad'));
			}
		});


	});
	return this;
};

function formErrorPlacement( error, element ) {
	error.addClass( "invalid-tooltip" );
	if ( element.prop( "type" ) === "checkbox" ) {
		error.insertAfter(element.parent("label"));
	} else {
		error.insertAfter(element);
	}
}

function formHighlight( element, errorClass, validClass ) {
	$(element).addClass("is-invalid").removeClass("is-valid");
}

function formUnhighlight(element, errorClass, validClass) {
	$(element).removeClass("is-valid").removeClass("is-invalid");
	if($(element).val()!==''){
		$(element).addClass("is-valid").removeClass("is-invalid");
	}
}

function formInvalidHandler(event, validator, formId) {
	let lnErrores = validator.numberOfInvalids();
	let llIgnoreShowFormAlert = ($(formId).data('no-show-alert') === 'si');

	if(llIgnoreShowFormAlert === false){
		if (lnErrores) {
			fnAlert("Existe(n) "+lnErrores+" campo(s) en este bloque que no cumple(n) con los requerimientos, verifique.", $(formId).data('title'));
		}
	}
}

function queryParams() {
	var params = {};
	$('#toolbarlistaIngresos').find('input[name]').each(function () {
		params[$(this).attr('name')] = $(this).val();
	})
	return params;
}

function queryParamsStringGet(){
	var params = '';
	$('#filterlistaAperturaSalas').find('input,select').each(function () {
		if($(this).val()){
			params += (params==''?'':'&')+$(this).attr('name')+'='+$(this).val();
		}
	})
	return params;
}

function entidadesRowStyle(row, index) {
	if (row.USAPLA=='USAR') {
		return {
			classes: 'font-weight-bolder',
			cursor: 'pointer'
		}
	}

	return {
		css: {
			cursor: 'pointer'
		}
	}
}

function rowStyle(row, index) {
	return {
		css: {
			cursor: 'pointer'
		}
	}
}

function planCheckFormatter(value, row, index) {
	if (row.PLAPLA === gaPropiedadesIngreso['PlanParticular']) {
		return {
			disabled: true,
			checked: false
		}
	}
	return value
}

function localidadResidenciaPaciente(){
	$('#nPacienteRecideLocalidad').val('').attr('disabled','disabled');
	if($('#nPacienteRecidePais').val()=='101' && $('#nPacienteRecideDepartamento').val()=='11' && $('#nPacienteRecideCiudad').val()=='1'){
		if($('#nPacienteRecideLocalidad').data('localidad')!==undefined && $('#nPacienteRecideLocalidad').data('localidad')!==''){
			$('#nPacienteRecideLocalidad').val($('#nPacienteRecideLocalidad').data('localidad'));
		}
		$('#nPacienteRecideLocalidad').removeAttr('disabled');
	}
}

function lecturaCedulaPaciente(bufferString){
	let lcError = "";
	try {
		var laCedula = JSON.parse(bufferString);
		if(laCedula.MD!=undefined && laCedula.ID!=undefined && laCedula.AA!=undefined && laCedula.AB!=undefined && laCedula.NA!=undefined && laCedula.NB!=undefined && laCedula.ND!=undefined && laCedula.SX!=undefined && laCedula.FN!=undefined && laCedula.RH!=undefined){
			if(laCedula.MD=="DF-PDF417" && laCedula.WR=="NO-COPY"){
				let lnCedulaNumero = parseInt(laCedula.ID);
				if(lnCedulaNumero>0){

					$.each(laCedula, function(index, value) {
						if(typeof value === 'string'){
							laCedula[index] = value.trim();
						}
					});

					$('#cPacienteGenero').val(laCedula.SX).find('option:not(:selected)').attr('disabled', 'disabled');
					$('#cPacienteGrupoRH').val(laCedula.RH).find('option:not(:selected)').attr('disabled', 'disabled');
					$('#cPacienteApellido1').val(laCedula.AA).prop('readonly', 'readonly');
					$('#cPacienteApellido2').val((laCedula.ND=="1"?"DE ":"")+laCedula.AB).prop('readonly', 'readonly');
					$('#cPacienteId').val('C').find('option:not(:selected)').attr('disabled', 'disabled').trigger('change');
					$('#cPacienteIdCapturaMetodo').val('BARRAS')
					$('#cPacienteNombre1').val(laCedula.NA).prop('readonly', 'readonly');
					$('#cPacienteNombre2').val(laCedula.NB).prop('readonly', 'readonly');
					$('#nNacimiento').data('confirmado','si').val(laCedula.FN).trigger('change').prop('readonly', 'readonly');
					$('#nPacienteId').data('confirmado','si').data('lecturaCedulaPaciente','si').val(lnCedulaNumero).prop('readonly', 'readonly').trigger('change');

				}else{
					lcError = 'El numero de documento no es valido.';
				}
			}else{
				lcError = 'Formato no valido.';
			}
		}
	} catch (error) {
		lcError = 'La entrada de texto en la casilla <b>Lectura de c&oacute;digo de barras</b> no es valida.' + error;
	}

	if(lcError!=""){
		$.alert({
			title: 'Información no valida',
			content: lcError,
		});
	}
}

function lecturaCedulaResponsable(bufferString){
	let lcError = "";
	try {
		var laCedula = JSON.parse(bufferString);
		if(laCedula.MD!=undefined && laCedula.ID!=undefined && laCedula.AA!=undefined && laCedula.AB!=undefined && laCedula.NA!=undefined && laCedula.NB!=undefined && laCedula.ND!=undefined && laCedula.SX!=undefined && laCedula.FN!=undefined && laCedula.RH!=undefined){
			if(laCedula.MD=="DF-PDF417" && laCedula.WR=="NO-COPY"){
				let lnCedulaNumero = parseInt(laCedula.ID);
				if(lnCedulaNumero>0){

					$.each(laCedula, function(index, value) {
						if(typeof value === 'string'){
							laCedula[index] = value.trim();
						}
					});

					$('#cResponsableId').val('C').find('option:not(:selected)').attr('disabled', 'disabled');
					$('#nResponsableId').val(lnCedulaNumero).prop('readonly', 'readonly');
					$('#cResponsableApellido1').val(laCedula.AA).prop('readonly', 'readonly');
					$('#cResponsableApellido2').val((laCedula.ND=="1"?"DE ":"")+laCedula.AB).prop('readonly', 'readonly');
					$('#cResponsableNombre1').val(laCedula.NA).prop('readonly', 'readonly');
					$('#cResponsableNombre2').val(laCedula.NB).prop('readonly', 'readonly');
				}else{
					lcError = 'El numero de documento no es valido.';
				}
			}else{
				lcError = 'Formato no valido.';
			}
		}
	} catch (error) {
		lcError = 'La entrada de texto en la casilla <b>Lectura de c&oacute;digo de barras</b> no es valida.' + error;
	}

	if(lcError!=""){
		$.alert({
			title: 'Información no valida',
			content: lcError,
		});
	}
}

function copiarDatosPacienteResponsable(tcTarget, overwrite){
	tcTarget = (tcTarget==undefined?'':tcTarget);
	let laTarget = tcTarget.split(' ');
	let laCampos = [];
	let llRealizado = false;
	let lcIncompletos = '';
	let lnIncompletos = 0;

	laCampos['paciente'] = ['c[%]Id','n[%]Id','c[%]LugarExpedicion','c[%]Nombre1','c[%]Nombre2','c[%]Apellido1','c[%]Apellido2'];
	laCampos['residencia'] = ['c[%]RecideBarrio','c[%]RecideDireccion'];
	laCampos['contacto'] = ['c[%]Telefono1','c[%]Telefono2','c[%]Telefono3','c[%]Telefono4','c[%]TieneEmail','c[%]Email'];
	laCampos['laboral'] = ['c[%]LaboralTrabajo','c[%]LaboralEmpresa','c[%]LaboralCargo','c[%]LaboralAntiguedad','c[%]LaboralDireccion','c[%]LaboralTelefono'];
	laCampos['referencia'] = ['c[%]ReferenciaNombre','c[%]ReferenciaDireccion','c[%]ReferenciaTelefono'];

	$.each(laTarget, function( lnTargetIndex, lcTarget) {
		$('form[data-requerido-copiar=si]').each(function( index ) {
			if($(this).data('alias') === lcTarget || lcTarget == 'todo'){
				$(this).data('no-show-alert','si');
				if($(this).valid()==false){
					lcIncompletos += '<li class="text-danger">'+$(this).data('title')+'</li>';
					lnIncompletos += 1;
				}
				$(this).data('no-show-alert', 'no');
			}
		});
	});

	if(lnIncompletos == 0){
		$.each(laTarget, function( lnTargetIndex, lcTarget) {
			$('form[data-requerido-copiar=si]').each(function( index ) {

				if($(this).data('alias') === lcTarget || lcTarget == 'todo'){
					let laLista = laCampos[$(this).data('alias')];
					$.each(laLista, function(index, data ) {
						let lcSource = data.replace('[%]', 'Paciente', 'gi');
						let lcTarget = data.replace('[%]', 'Responsable', 'gi');
						let lcValor = $('#'+lcSource).val();

						lcValor = (lcValor==undefined?'':lcValor);

						if($('#'+lcTarget).val()==undefined || $('#'+lcTarget).val()=='' || overwrite=='overwrite'){
							$('#'+lcTarget).data('confirmado','si').val(lcValor).trigger('change').removeAttr('data-confirmado');
						}
					});

					if(lcTarget === 'todo' || $(this).data('alias') === 'residencia'){
						$('#ubicacionResidenciaResponsable').cargar($('#nPacienteRecidePais').val(), $('#nPacienteRecideDepartamento').val(), $('#nPacienteRecideCiudad').val());
					}
				}
			});
		});
		llRealizado = true;
	}else{
		$.alert({
			icon: 'fas fa-save',
			title: 'Copiar',
			type: 'red',
			content: "Existe(n) "+lnIncompletos+" bloque(s) que tienen campos que no cumple(n) con los requerimientos para copiar la información.<br/><br/><ul>"+lcIncompletos+"</ul>",
			buttons: {
				Cerrar: {text: 'Cerrar',}
			}
		});
	}

	return llRealizado;
}


function dateDiff(tcIniDate, tcEndDate, tlShowHtml) {
	tcIniDate = (!tcIniDate ? new Date().toISOString().substr(0, 10) : tcIniDate);    // need date in YYYY-MM-DD format
	tcEndDate = (!tcEndDate ? new Date().toISOString().substr(0, 10) : tcEndDate);    // need date in YYYY-MM-DD format

	let lcResult = '';

	try{
		var ldIniDate = new Date(new Date(tcIniDate).toISOString().substr(0, 10));
		var ldEndDate = new Date(tcEndDate);

		if (ldIniDate > ldEndDate) {
			var ldAux = ldIniDate;
			ldIniDate = ldEndDate;
			ldEndDate = ldAux;
		}

		tlShowHtml = (!tlShowHtml?false:tlShowHtml);

		var lnIniYear = ldIniDate.getFullYear();
		var lnDaysFebruary = (lnIniYear % 4 === 0 && lnIniYear % 100 !== 0) || lnIniYear % 400 === 0 ? 29 : 28;
		var laDaysInMonth = [31, lnDaysFebruary, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
		var lnYearDiff = ldEndDate.getFullYear() - lnIniYear;
		var lnMonthDiff = ldEndDate.getMonth() - ldIniDate.getMonth();

		if (lnMonthDiff < 0) {
			lnYearDiff--;
			lnMonthDiff += 12;
		}

		var lnDayDiff = ldEndDate.getDate() - ldIniDate.getDate();
		if (lnDayDiff < 0) {
			if (lnMonthDiff > 0) {
				lnMonthDiff--;
			} else {
				lnYearDiff--;
				lnMonthDiff = 11;
			}
			lnDayDiff += laDaysInMonth[ldIniDate.getMonth()];
		}

		lcResult = lnYearDiff + 'A ' + lnMonthDiff + 'M ' + lnDayDiff + 'D';
		lcResult = (tlShowHtml==false?lcResult:'<b>'+lnYearDiff + '</b>A, <b>' + lnMonthDiff + '</b>M, <b>' + lnDayDiff + '</b>D');
	} catch (e) {
		lcResult = "Fechas no validas";
	}

	return lcResult;
}

function planReclasificar(tcPlan){
	let $table = $('#tableEntidades');
	let lnFila = 0;
	let lcUltimo = '';
	let lcMarcado = '';

	$.each($table.bootstrapTable('getData'), function(index, data ) {
		if(data.PLAPLA==gaPropiedadesIngreso['PlanParticular']){
			$table.bootstrapTable('updateCellByUniqueId', {id: data.PLAPLA, field: 'SECPLA', value: gaPropiedadesIngreso['PlanMaximo']});
		}else{
			lnFila += 1;
			lcUltimo = data.PLAPLA;
			lcMarcado = (data.USAPLA == 'USAR' ? data.PLAPLA : lcMarcado);
			$table.bootstrapTable('updateCellByUniqueId', {id: data.PLAPLA, field: 'SECPLA', value: lnFila});
		}
	});

	lcUltimo = (lcMarcado.length>0 ? lcMarcado : lcUltimo);
	tcPlan = (lnFila==0 ? gaPropiedadesIngreso['PlanParticular'] : (typeof tcPlan !== 'string' ? (lcUltimo.length>0 ? lcUltimo : gaPropiedadesIngreso['PlanParticular']) : (tcPlan.length<=0 ? (lcUltimo.length>0 ? lcUltimo : gaPropiedadesIngreso['PlanParticular']) : tcPlan)));
	if(tcPlan.length>0){
		$.each($table.bootstrapTable('getData'), function(index, data ) {
			$table.bootstrapTable('updateCellByUniqueId', {id: data.PLAPLA, field: 'USAPLA', value: (data.PLAPLA==tcPlan ? 'USAR' : '')});
		});
	}

	let lnMapla = 0;
	$.each($table.bootstrapTable('getData'), function(index, data ) {
		if(data.USAPLA=='USAR'){
			$('#cPlanUsar').val(data.PLAPLA);
			$('#cPlanUsarDescripcion').val(data.ENTPLA);
			$('#cPlanUsarEntidadTipo').val(data.TENPLA);
			$('#nPlanUsarAfiliadoTipo').val(data.TAFPLA);
			$('#nPlanUsarContrato').val(data.CONPLA);
			$('#nPlanUsarEntidad').val(data.NITPLA);
			$('#nPlanUsarEstrato').val(data.ESTPLA);
			$('#nPlanUsarRegional').val(data.REGPLA);
			$('#nPlanUsarTipo').val(data.TUSPLA);
		}
		lnMapla += (data.TENPLA=='05'?1:0);
	});
	$('#nMapla').val(lnMapla);
		
	return lnFila;
}

function planAgregar(tcPlan, tcPlanTipo, tcPlanAfiliadoTipo, tcPlanAfiliadoEstrato, tcPlanTipoDisplay, tcPlanAfiliadoTipoDisplay, tcIdTipo, tnIdNumero, tnPlanConteo, tnVCupla, tcCarPla){
	tcPlan = (!tcPlan?'':tcPlan);
	tcPlanTipo = (!tcPlanTipo?'':tcPlanTipo);
	tcPlanAfiliadoTipo = (!tcPlanAfiliadoTipo?'':tcPlanAfiliadoTipo);
	tcPlanAfiliadoEstrato = (!tcPlanAfiliadoEstrato?'':tcPlanAfiliadoEstrato);
	tcPlanTipoDisplay = (!tcPlanTipoDisplay?'':tcPlanTipoDisplay);
	tcPlanAfiliadoTipoDisplay = (!tcPlanAfiliadoTipoDisplay?'':tcPlanAfiliadoTipoDisplay);
	tcIdTipo = (!tcIdTipo?'':tcIdTipo);
	tcCarPla = (!tcCarPla?'':tcCarPla);
	tnIdNumero = parseInt(!tnIdNumero?0:tnIdNumero);
	tnPlanConteo = parseInt(!tnPlanConteo?0:tnPlanConteo);
	tnVCupla = parseInt(!tnVCupla?0:tnVCupla);

	let lnRegistros = 0

	if(gaPropiedadesIngreso['PlanRestringidoUrgencias'].includes("'"+tcPlan+"'")==true && $('#cIngresoVia').val()=='01'){
		fnAlert("Plan no autorizado para la vía actual", 'Ingreso');
	}else{
		if(tcPlan !== ''){
			$.ajax({
				type: "POST",
				url: "vista-ingresos/ajax/registroIngresos.ajax",
				data: {accion:'plan', plan:tcPlan},
				dataType: "json"
			})
			.done(function(plan) {
				if(plan!==undefined){

					let lnSel = 1;
					let lcEstado = '0';
					let lcDescripcion = 'Activo';
					let lcTipoEntidad = plan['TIPO'];
					let lnMapla = (plan['TIPO']=='05'?1:0);
					let $table = $('#tableEntidades');

					// Borra plan si existe
					$table.bootstrapTable('removeByUniqueId', tcPlan);

					lnRegistros = planReclasificar();

					if(lnRegistros < (gaPropiedadesIngreso['PlanMaximo']-1)){

						let row = [];

						row.push({
							USAPLA: 'USAR',
							TIPPLA: tcIdTipo,
							NIDPLA: tnIdNumero,
							SECPLA: (tnPlanConteo<=0?(lnRegistros+1):tnPlanConteo),
							NITPLA: plan['NIT'],
							CONPLA: plan['CONTRATO'],
							ENTPLA: plan['DESCRIPCION'],
							REGPLA: plan['REGIONAL'],
							PLAPLA: tcPlan,
							ESTPLA: tcPlanAfiliadoEstrato,
							TUSPLA: tcPlanTipo,
							DUSPLA: tcPlanTipoDisplay,
							TAFPLA: tcPlanAfiliadoTipo,
							DAFPLA: tcPlanAfiliadoTipoDisplay,
							ESEPLA: lcEstado,
							DSEPLA: lcDescripcion,
							TENPLA: plan['TIPO_NOMBRE'],
							VCUPLA: tnVCupla,
							CARPLA: tcCarPla,
							FECPLA: <?php print(intval(date('Ymd'))); ?>,
						});

						$table.bootstrapTable('append', row);
						planReclasificar(tcPlan);

						$('#modalServicioEditarEntidad').modal('hide');

					} else {
						fnAlert("Numero máximo de planes agregados", 'Ingreso');
					}

				}else{
					fnAlert("No se encontró información del plan "+tcPlan, 'Ingreso');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				alert("Se presentó un error al cargar la información de plan. \n"+jqXHR.responseText);
			});
		}else{
			fnAlert("Código de plan no valido", 'Ingreso');
		}
	}
}

function obtenerTriageClasificacion(){
	let lnIngreso = parseInt($('#nIngreso').val());
	if(lnIngreso>0){
		$.ajax({
			type: "POST",
			url: "vista-ingresos/ajax/registroIngresos.ajax",
			data: {accion:'triageClasificacion', ingreso:lnIngreso},
			dataType: "json"
		})
		.done(function(clasifiaccion) {
			if(clasifiaccion.CLASIFIACION !== undefined){
				$('#cTriage').val(clasifiaccion.CLASIFIACION);
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			alert("Se presentó un error al cargar la clasificación traige. \n"+jqXHR.responseText);
		});
	}
}

function obtenerTriageEnfermedad(){
	let lnIngreso = parseInt($('#nIngreso').val());
	if(lnIngreso>0){
		$.ajax({
			type: "POST",
			url: "vista-ingresos/ajax/registroIngresos.ajax",
			data: {accion:'triageEnfermedad', ingreso:lnIngreso},
			dataType: "json"
		})
		.done(function(enfermedad) {
			if(enfermedad.ENFERMEDAD !== undefined){
				$('#cEnfermedadActual').val(enfermedad.ENFERMEDAD);
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			alert("Se presentó un error al cargar la enfermedad actual. \n"+jqXHR.responseText);
		});
	}
}

function obtenerMedicoTratante(){
	let lnIngreso = parseInt($('#nIngreso').val());
	if(lnIngreso>0){
		$.ajax({
			type: "POST",
			url: "vista-ingresos/ajax/registroIngresos.ajax",
			data: {accion:'obtenerMedicoTratante', ingreso:lnIngreso},
			dataType: "json"
		})
		.done(function(medico) {
			if(medico.REGISTRO !== undefined){
				$('#cMedicoTratanteId').val(medico.REGISTRO);
				$('#cMedicoTratanteNombre').val(medico.MEDICO);
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			alert("Se presentó un error al cargar el medico tratante. \n"+jqXHR.responseText);
		});
	}
}


function cargarPaciente(tcTipo){
	tcTipo = String(tcTipo).trim();
	lcPacienteId = String($('#cPacienteId').val());
	lcPacienteId = lcPacienteId.toUpperCase().trim();
	lnPacienteId = parseInt($('#nPacienteId').val());
	lnIngreso = parseInt($('#nIngreso').val());

	if(lcPacienteId!=='' && lnPacienteId>0 && $('#nPacienteId').data('confirmado')=='si'){
		$.ajax({
			type: "POST",
			url: "vista-ingresos/ajax/registroIngresos.ajax",
			data: {accion:'paciente', tipo:lcPacienteId, numero:lnPacienteId, ingreso:lnIngreso},
			dataType: "json"
		})
		.done(function(paciente) {
			if(paciente !== undefined){
				if(paciente.VALIDO == true){
					let $loPaciente = paciente.PACIENTE;

					$('#cPacienteId').find('option:not(:selected)').attr('disabled', 'disabled');
					$('#nPacienteId').data('confirmado', 'cargado').attr('readonly', 'readonly');

					$('#nHistoria').val($loPaciente.HISTORIA);
					$('#nHistoriaAyuda').html('Historia No. '+$loPaciente.HISTORIA);

					$('#nConsulta').val($loPaciente.CONSECUTIVO_CONSULTA);
					$('#nConsultaAyuda').html('CN: '+$loPaciente.CONSECUTIVO_CONSULTA);

					$('#nCita').val($loPaciente.CONSECUTIVO_CITA);
					$('#nCitaAyuda').html('CT: '+$loPaciente.CONSECUTIVO_CITA);

					if(tcTipo!=='lecturaCedulaPaciente'){
						$('#cPacienteNombre1').val($loPaciente.NOMBRE1).prop('readonly', 'readonly');
						$('#cPacienteNombre2').val($loPaciente.NOMBRE2).prop('readonly', 'readonly');
						$('#cPacienteApellido1').val($loPaciente.APELLIDO1).prop('readonly', 'readonly');
						$('#cPacienteApellido2').val($loPaciente.APELLIDO2).prop('readonly', 'readonly');
						$('#nNacimiento').data('confirmado','si').val(strNumAFecha($loPaciente.NACIO)).trigger('change').prop('readonly', 'readonly');
						$('#cPacienteGenero').val($loPaciente.GENERO).find('option:not(:selected)').attr('disabled', 'disabled');
						$('#cPacienteGrupoRH').val($loPaciente.GSRH).find('option:not(:selected)').attr('disabled', 'disabled');
					}

					$('#cPacienteLugarExpedicion').val($loPaciente.DOCUMENTO_LUGAR_EXPEDICION);
					$('#ubicacionNacimientoPaciente').cargar($loPaciente.NACIO_PAIS, $loPaciente.NACIO_DEPARTAMENTO, $loPaciente.NACIO_MUNICIPIO);
					$('#formPacienteInformacion').valid();

					$('#cPacienteEstadoCivil').val($loPaciente.ESTADO_CIVIL);
					$('#cEpicrisisEmail').val($loPaciente.ENVIAR_EPICRISIS_EMAIL=='1'?'SI':'').trigger('change');
					$('#cPacientePertenenciaEtnica').val($loPaciente.PERTENECIA_ETNICA);
					$('#cPacienteNivelEducativo').val($loPaciente.NIVEL_EDUCATIVO);
					$('#formPacienteGeneral').valid();

					$('#nPacienteRecideLocalidad').data('localidad', $loPaciente.RESIDE_LOCALIDAD);
					$('#ubicacionResidenciaPaciente').cargar($loPaciente.RESIDE_PAIS, $loPaciente.RESIDE_DEPARTAMENTO, $loPaciente.RESIDE_MUNICIPIO);
					$('#cPacienteRecideBarrio').val($loPaciente.RESIDE_BARRIO);
					$('#cPacienteRecideDireccion').val($loPaciente.RESIDE_DIRECCION);
					$('#nPacienteRecideZona').val($loPaciente.RESIDE_ZONA);
					$('#formPacienteRecide').valid();

					$('#cPacienteTelefono1').val($loPaciente.TELEFONO1);
					$('#cPacienteTelefono2').val($loPaciente.TELEFONO2);
					$('#cPacienteTelefono3').val($loPaciente.TELEFONO3);
					$('#cPacienteTelefono4').val($loPaciente.TELEFONO4);
					$('#cPacienteTieneEmail').val($loPaciente.MAIL_PACIENTE.trim()!==''?'SI':'');
					$('#cPacienteEmail').val($loPaciente.MAIL_PACIENTE);
					$('#formPacienteContacto').valid();

					$('#nPacienteLaboralOcupacion').val($loPaciente.LABORAL_OCUPACION).trigger('change');
					$('#cPacienteLaboralTrabajo').val($loPaciente.LABORAL_TRABAJO).trigger('change');
					$('#cPacienteLaboralEmpresa').val($loPaciente.LABORAL_EMPRESA).trigger('change');
					$('#cPacienteLaboralCargo').val($loPaciente.LABORAL_CARGO).trigger('change');
					$('#cPacienteLaboralAntiguedad').val($loPaciente.LABORAL_ANTIGUEDAD).trigger('change');
					$('#cPacienteLaboralDireccion').val($loPaciente.LABORAL_DIRECCION);
					$('#cPacienteLaboralTelefono').val($loPaciente.LABORAL_TELEFONO);
					$('#formPacienteLaboral').valid();

					$('#cPacienteReferenciaNombre').val($loPaciente.REFERENCIA_MOMBRE);
					$('#cPacienteReferenciaDireccion').val($loPaciente.REFERENCIA_DIRECCION);
					$('#cPacienteReferenciaTelefono').val($loPaciente.REFERENCIA_TELEFONO);
					$('#formPacienteReferencia').valid();


				}
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			alert("Se presentó un error al cargar la información del paciente. \n"+jqXHR.responseText);
		});
	}
}

function planAgregarParticular(){
	let lnIngreso = parseInt($('#nIngreso').val());
 	let lnPlanParticular = 0;
	let lcPlanTipo = 'C';
	let lcPlanAfiliadoTipo = 'A';
	let lcCarPla = $('#nPacienteId').val();

	lnIngreso = (isNaN(lnIngreso)?0:lnIngreso);
	if(lnIngreso==0){
		planAgregar(gaPropiedadesIngreso['PlanParticular'],lcPlanTipo,lcPlanAfiliadoTipo,'01',gaPropiedadesIngreso['PlanParticularTipoDisplay'],gaPropiedadesIngreso['PlanParticularAfiliadoTipoDisplay'],$('#cPacienteId').val(),$('#nPacienteId').val(),gaPropiedadesIngreso['PlanMaximo'],999999999,lcCarPla);
	}
}

window.entidadOperateEvents = {
	'click .usarEntidadPlan': function (e, value, row, index) {
		planReclasificar(row.PLAPLA);
	}
}

function entidadOperateFormatter(value, row, index) {
	return '<button type="button" class="btn btn-outline-dark btn-sm usarEntidadPlan">Usar</button>';
}

$(document).ajaxStop(function() {
	oModalEspera.ocultar();
});

$(function() {

	$.validator.setDefaults({
		ignore: ".ignore",
		errorElement: "div",
		errorPlacement: function(error, element) {
			formErrorPlacement(error, element);
		},
		highlight: function(element, errorClass, validClass) {
			formHighlight(element, errorClass, validClass);
		},
		unhighlight: function(element, errorClass, validClass) {
			formUnhighlight(element, errorClass, validClass);
		},
		submitHandler: function(form, event) {
			event.preventDefault();
		},
	});

	$.validator.addMethod('direccion', function (value) {
		return /<?php print($laPropiedadesIngreso['ExpresionRegularDireccion']); ?>/.test(value);
	}, 'Por favor ingrese la dirección usando caracteres, números y espacios unicamente.');

	$.validator.addMethod('nombreApellido', function (value) {
		return /<?php print($laPropiedadesIngreso['ExpresionRegularNombresApellidos']); ?>/.test(value);
	}, 'Por favor ingrese el nombre usando caracteres, números y espacios unicamente.');


	$('#tableEntidades').bootstrapTable('destroy').bootstrapTable({
		locale: 'es-ES',
		classes: 'table table-bordered table-hover table-sm table-responsive-sm table-striped',
		theadClasses: 'thead-light',
		exportTypes: ['csv', 'txt', 'excel'],
		sortName: "SECPLA",
		columns: [
					[
						{checkbox: true,title: 'Sel', formatter: planCheckFormatter},
						{field: 'SELPLA',title: 'Marcar', align: 'center', clickToSelect: false, events: window.entidadOperateEvents, formatter: entidadOperateFormatter },
						{field: 'USAPLA',title: 'Usar', visible: false},
						{field: 'TIPPLA',title: 'Tipo de Identificaci&oacute;n', visible: false},
						{field: 'NIDPLA',title: 'Numero de identificaci&oacute;n', visible: false},
						{field: 'SECPLA',title: 'Orden'},
						{field: 'NITPLA',title: 'Nit entidad', visible: false},
						{field: 'CONPLA',title: 'Numero de contrato', visible: false},
						{field: 'PLAPLA',title: 'Plan'},
						{field: 'ENTPLA',title: 'Descripci&oacute;n plan'},
						{field: 'REGPLA',title: 'Regional', visible: false},
						{field: 'TUSPLA',title: 'C&oacute;digo tipo de usuario', visible: false},
						{field: 'DUSPLA',title: 'Tipo de usuario'},
						{field: 'TAFPLA',title: 'C&oacute;digo tipo de afiliado', visible: false},
						{field: 'DAFPLA',title: 'Tipo de afiliado'},
						{field: 'ESTPLA',title: 'Estrato'},
						{field: 'ESEPLA',title: 'C&oacute;digo estado de entidad', visible: false},
						{field: 'DSEPLA',title: 'Estado de entidad'},
						{field: 'VCUPLA',title: 'Valor cubierto', visible: false},
						{field: 'SGMPLA',title: 'Valor copagos año', visible: false},
						{field: 'FECPLA',title: 'Fecha de ingreso', visible: false},
						{field: 'TENPLA',title: 'Tipo de entidad', visible: false},
						{field: 'CARPLA',title: 'Numero del carnet', visible: false},
					]
				]
	});

	$('.fixed-table-body').css('min-height','320px').css('background-color','#fdfdfd').css('border','1px dotted #e9ecef');

	$('#tableEntidades').on((goMobile.mobile()?'click-row.bs.table':'dbl-click-row.bs.table'), function (row, $element, field) {

	});

	$('.input-group.date').datepicker({
		autoclose: true,
		format: "yyyy-mm-dd",
		startDate: "<?php print(date("Y")-$laPropiedadesIngreso['EdadMaximaPaciente']); ?>-01-01",
		endDate: "<?php print(date("Y-m-d")); ?>",
		language: "es",
		todayBtn: false,
		todayHighlight: false,
		toggleActive: false,
		weekStart: 1,
		enableOnReadonly: false
	});

	$('.estadoCivil').selectSimple({accion: 'estadosCiviles'});
	$('.ipss').selectSimple({accion: 'ipss'});
	$('.lugarLocalidad').selectSimple({accion: 'localidades'});
	$('.nivelEducativo').selectSimple({accion: 'nivelesEducativos'});
	$('.ocupacion').selectSimple({accion: 'ocupaciones'});
	$('.parentesco').selectSimple({accion: 'parentescos'});
	$('.pertenenciaEtnica').selectSimple({accion: 'pertenenciasEtnicas'});
	$('.tipoDocumento').selectSimple({accion: 'tiposDocumentos'});
	$('.trabajo').selectSimple({accion: 'trabajos'});
	$('.triageListaClasificaciones').selectSimple({accion: 'triageListaClasificaciones'});
	$('.pdf417-buffer').inputLectorPdf417();
	$('.ubicacionGeografica').ubicacionGeografica();

	$('.modal').on('shown.bs.modal hidden.bs.modal', function () {
		$(this).find('input[id], select[id]').each(function () {
			$(this).val("");
		});

		switch($(this).prop('id')){
			case 'modalConfirmarEntrada':
				$('#vModalConfirmarEntrada').trigger('focus');
				break;

			case 'modalServicioEditarEntidad':
				$('#cServicioEditarPlan').trigger('focus');
				$('#cServicioEditarPlanIdAyuda').empty();
				goFormServicioEditarEntidad.resetForm();
				break;

		}
	});

	$('.btnCpy').click(function() {
		copiarDatosPacienteResponsable($(this).data('target'),'overwrite');
	});

	$('#btnServicioEditarEntidadGuardar').click(function() {
		if($("#formServicioEditarEntidad").valid()==true){
			let lcServicioEditarPlanTipo = $('#cServicioEditarPlanTipo option:selected').text();
			let lcServicioEditarPlanAfiliadoTipo = $('#cServicioEditarPlanAfiliadoTipo option:selected').text();
			planAgregar($('#cServicioEditarPlanId').val(),$('#cServicioEditarPlanTipo').val(),$('#cServicioEditarPlanAfiliadoTipo').val(),$('#cServicioEditarPlanAfiliadoEstrato').val(),lcServicioEditarPlanTipo,lcServicioEditarPlanAfiliadoTipo,$('#cPacienteId').val(),$('#nPacienteId').val(),0)
		}
	});

	$('#btnAgregarEntidad').click(function () {
		if($('#formPacienteInformacion').valid()==true){
			if($('#formPacienteRemision').valid()==true){
				if($('#formPacientePlan').valid()==true){
					$('#modalServicioEditarEntidad').modal('show');
				}
			}
		}
	});

    $('#btnQuitarEntidad').click(function () {
		let $table = $('#tableEntidades');
		let laPlanes = $.map($table.bootstrapTable('getSelections'), function (row) { return row.PLAPLA });

		$table.bootstrapTable('remove', {
			field: 'PLAPLA',
			values: laPlanes
		});

		planReclasificar();
    })

	$('#btnConfirmarEntradaCancelar').click(function() {
		let lcControl =  goConfirmarEntrada.data('control');
		let lcControlType =  goConfirmarEntrada.data('control-type');
		$(lcControl).val("");
		$(lcControl).prop('type',lcControlType);
		goConfirmarEntrada.modal('hide');
		$(lcControl).trigger('focus');
	});

	$('#btnConfirmarEntradaAceptar').click(function() {
		let lcControl =  goConfirmarEntrada.data('control');
		let lcControlType =  goConfirmarEntrada.data('control-type');
		let lcValue =  goConfirmarEntrada.data('val').toUpperCase();
		var lcValueConfirmacion = goConfirmarEntradaInput.val().toUpperCase();

		if(lcValue != lcValueConfirmacion){
			fnAlert("El valor inicial no coincide con la confirmaci&oacute;n, verifique.", goConfirmarEntradaLabel.text());
		}else{
			$(lcControl).data('confirmado', 'si');
			switch(lcControl){
				case '#nPacienteId':
					cargarPaciente();
					break;
			}

			$(lcControl).prop('type',lcControlType);
			goConfirmarEntrada.modal('hide');
			$(lcControl).trigger('focus');
		}
	});

	$('.confirmar').on("change", function(e) {
		let lcValue = $(this).val();
		if($(this).data('confirmado')==='si'){
		}else{
			if(lcValue != ''){
				goConfirmarEntradaLabel.text($(this).data('label'));
				goConfirmarEntradaInput.prop('type',$(this).prop('type'));
				goConfirmarEntrada.data('control-type', $(this).prop('type')).data('control','#'+$(this).prop('id')).data('val', lcValue).modal('show');
				$(this).prop('type','password');
			}
		}
	}).on("focus", function() {
		$(this).data('confirmado','no');
		$(this).data('value',$(this).val());
		if($(this).data('control-type')==undefined){
			$(this).data('control-type', $(this).prop('type'));
		}
		$(this).prop('type',$(this).data('control-type'));
	}).keydown(function(e) {
		var code = e.keyCode || e.which;
		if (code === 9) {
			if($(this).data('value')!==$(this).val()){
				e.preventDefault();
				$(this).trigger('change');
			}
		}
	});

	$('#cPacienteId').on("change", function() {
		lcPacienteId = String($(this).val());
		lcPacienteId = lcPacienteId.trim();

		if(gaPropiedadesIngreso['ListaTipoIdePasaporte'].includes(lcPacienteId)==true){
			$('#cPasaporte').removeAttr('readonly');
			$('#cPacientePermisoPermanenciaTiene').val('').removeAttr('disabled');
		}else{
			$('#cPasaporte').val('').attr('readonly', 'readonly');
			$('#cPacientePermisoPermanenciaTiene').val('NO').attr('disabled','disabled').trigger('change');
		}

		if($(this).val()!==''){
			$("#"+$(this).prop('id')+" option[value='']").remove();
		}

		if($('#nPacienteId').data('confirmado')=='si'){
			cargarPaciente();
		}
	});

	$('#nPacienteId').on("change", function() {
		lnPacienteId = parseInt($(this).val());
		lnPacienteId = (isNaN(lnPacienteId)?0:lnPacienteId);

		let loPacienteCarnet = $('#cPacienteCarnet');

		if(lnPacienteId>0){
			if(loPacienteCarnet.val()==undefined || loPacienteCarnet.val().trim()===''){
				let lcPacienteId = String(lnPacienteId);
				lcPacienteId = lcPacienteId.trim().substring(0,13).trim();

				if(lcPacienteId==undefined || lcPacienteId!==''){
					loPacienteCarnet.val(lcPacienteId);
				}
			}
		}

		if($('#nPacienteId').data('confirmado')=='si' || $('#nPacienteId').data('lecturaCedulaPaciente')=='si'){
			cargarPaciente($('#nPacienteId').data('lecturaCedulaPaciente')=='si'?'lecturaCedulaPaciente':'');
		}
	});

	$('#nNacimiento').on("change", function() {
		$('#cEdad').html('Edad');
		if($(this).val()!=='' && $(this).val()!==undefined){
			$('#cEdad').html(dateDiff($(this).val(), $('#nFechaIngreso').val(), true));
		}
	}).on("focus", function() {
		if($(this).val()===''){
			$('#cEdad').html('Edad');
		}
	});

	$('#cIngresoVia').on("change", function() {
		let lcIngresoVia = $(this).val();
		let llRequiereReferencia = true

		$('#cMedicoTratante').val('').attr('disabled', 'disabled');
		$('#cMedicoTratanteId').val('');
		$('#cMedicoTratanteNombre').val('');

		$('label[for=cHabitacion]').removeClass('required');
		$('#cHabitacion').empty().val('').attr('disabled', 'disabled');

		$('label[for=cSeccion]').removeClass('required');
		$('#cSeccion').val('').attr('disabled', 'disabled');

		$('#cTriage').val('').attr('disabled', 'disabled');
		$('#cEnfermedadActual').val('').attr('disabled', 'disabled');


		// Activando objetos segun seleccion
		$('.ingresoVia').val('').attr('disabled', 'disabled');

		$('label[for=cTriage]').removeClass('required');
		$('label[for=cMedicoTratante]').removeClass('required');
		$('label[for=cEnfermedadActual]').removeClass('required');

		switch(lcIngresoVia){
			case '01':
				$('label[for=cTriage]').addClass('required');
				$('#cTriage').removeAttr('disabled').val('');
				obtenerTriageClasificacion();

				$('label[for=cEnfermedadActual]').addClass('required');
				$('#cEnfermedadActual').removeAttr('disabled').val('');
				obtenerTriageEnfermedad();

				break;

			case '05':
			case '06':
				$('label[for=cSeccion]').addClass('required');
				$('label[for=cHabitacion]').addClass('required');
				$('label[for=cMedicoTratante]').addClass('required');

				$('.ingresoVia').removeAttr('disabled');
				$('#cMedicoTratante').removeAttr('disabled').val('');
				obtenerMedicoTratante();

				break;
		}

		// Referencia solo vías parametrizadas
		llRequiere = (gaPropiedadesIngreso['ViasRequierenReferencia'].includes("'"+lcIngresoVia+"'"));
		let laFields = ['c[%]ReferenciaNombre', 'c[%]ReferenciaDireccion', 'c[%]ReferenciaTelefono'];
		$.each(laFields, function( lnFieldIndex, lcField) {
			$.each(['Paciente', 'Responsable'], function( lnPrefixIndex, lcPrefix) {
				let lcFieldName = lcField.replace('[%]', lcPrefix, 'gi');
				let loField = $('label[for='+lcFieldName+']');

				loField.removeClass("required");
				if(llRequiere==true){
					loField.addClass("required");
				}
			});
		});

		if($(this).val()!==''){
			$("#"+$(this).prop('id')+" option[value='']").remove();
		}

	});

	$('#cSeccion').on("change", function() {
		let lcSeccion = String($(this).val());
		let loHabitacion = $('#cHabitacion');

		loHabitacion.empty().append($("<option></option>"));

		if(lcSeccion !== ''){
			$.ajax({
				type: "POST",
				url: "vista-ingresos/ajax/registroIngresos.ajax",
				data: {accion:'habitacionDisponible', seccion:lcSeccion},
				dataType: "json"
			})
			.done(function(habitaciones) {
				if(habitaciones !== undefined){
					$.each(habitaciones, function( key, row ) {
						loHabitacion.append($("<option></option>").attr("value", row.CODIGO).text(row.NOMBRE));
					});
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				alert("Se presentó un error al cargar las habitaciones disponibles. \n"+jqXHR.responseText);
			});
		}
	});

	let lcServicioEditarPlanAfiliadoTipo =

	$('#nPacienteRecideCiudad').on("change", function() {
		let lcPacienteRecideCiudadDisplay = $(this).find('option:selected').text();
		if(lcPacienteRecideCiudadDisplay.length>0){
			$('#cPacienteRecideCiudadDisplay').val(lcPacienteRecideCiudadDisplay);
		}
	});

	$('#cResponsablePaciente').on("change", function() {
		let loResponsableParentesco = $('#cResponsableParentesco');
		let lcValue = String($(this).val()).trim().toUpperCase();
		let llReadOnly = false;

		if(lcValue=='PACIENTE'){
			if(copiarDatosPacienteResponsable('todo','overwrite')==true){
				loResponsableParentesco.val('').attr('disabled','disabled');
			}else{
				loResponsableParentesco.val('').attr('disabled','disabled');
				$('#cResponsableId').val('').attr('disabled','disabled');
				$(this).val('');
			}
			llReadOnly = true;

		}else if(lcValue=='OTRO'){
			loResponsableParentesco.removeAttr('disabled');
			$('#formResponsableInformacion').trigger('reset').find('#'+$(this).prop('id')).val(lcValue);

		}else{
			loResponsableParentesco.val('').attr('disabled','disabled');
			$('#formResponsableInformacion').trigger('reset').find('#'+$(this).prop('id')).val(lcValue);
			llReadOnly = true;
		}

		if($(this).val()!==''){
			$("#"+$(this).prop('id')+" option[value='']").remove();
		}

		$('#formResponsableInformacion input[id], #formResponsableInformacion select[id], #formResponsableInformacion button[id]').each(function(index){
			if("cResponsablePaciente,cResponsableParentesco".includes($(this).prop('id'))===false){
				if(llReadOnly==true){
					if($(this).is("input")===true){
						$(this).attr('readonly', 'readonly');
					}else{
						$(this).attr('disabled', 'disabled');
					}
				}else{
					if($(this).is("input")===true){
						$(this).removeAttr('readonly', 'readonly');
					}else{
						$(this).removeAttr('disabled', 'disabled');
					}
				}
			}
		});

	});

	$('#nResponsableRecideCiudad').on("change", function() {
		let lcResponsableRecideCiudadDisplay = $(this).find('option:selected').text();
		if(lcResponsableRecideCiudadDisplay.length>0){
			$('#cResponsableRecideCiudadDisplay').val(lcResponsableRecideCiudadDisplay);
		}
	});

	$('#cEpicrisisEmail').on("change", function() {
		if($(this).val()=='SI'){
			$('#cPacienteTieneEmail').val('SI').change().find('option:not(:selected)').attr('disabled', 'disabled');
		}else{
			$('#cPacienteTieneEmail').find('option').removeAttr('disabled', 'disabled');
		}
	});

	$('#cPacientePermisoPermanenciaTiene').on("change", function() {
		if($(this).val()=='SI'){
			$('#cPacientePermisoPermanencia').removeAttr('readonly');
		}else{
			$('#cPacientePermisoPermanencia').val("").attr('readonly', 'readonly');
		}
	});

	$('#cPacienteLaboralTrabajo').on("change", function() {
		if($(this).val()=='01' || $(this).val()=='02'){
			$('.pacienteTrabajo').removeAttr('readonly');
		}else{
			$('.pacienteTrabajo').val("").attr('readonly', 'readonly');
		}
	});

	$('#cResponsableLaboralTrabajo').on("change", function() {
		if($(this).val()=='01' || $(this).val()=='02'){
			$('.responsableTrabajo').removeAttr('readonly');
		}else{
			$('.responsableTrabajo').val("").attr('readonly', 'readonly');
		}
	});

	$('input[type=text]').on("change", function() {
		let lcValue = $(this).val().toUpperCase().trim();
		$(this).val(lcValue);
	});

	$('#cResponsableTieneEmail').on("change", function() {
		if($(this).val()=='SI'){
			$('#cResponsableEmail').removeAttr('readonly');
		}else{
			$('#cResponsableEmail').val("").attr('readonly', 'readonly');
		}
	});

	$('#cPacienteTieneEmail').on("change", function() {
		if($(this).val()=='SI'){
			$('#cPacienteEmail').removeAttr('readonly');
		}else{
			$('#cPacienteEmail').val("").attr('readonly', 'readonly');
		}
	});

	$('#cRemitido').on("change", function() {
		if($(this).val()=='SI'){
			$('#cRemiteEntidad').removeAttr('disabled');
			$('#cRemitePais').removeAttr('disabled');
		}else{
			$('#cRemiteEntidad').val("").attr('disabled', 'disabled');
			$('#cRemitePais').val("").attr('disabled', 'disabled').trigger('change');
		}
	});

	goConfirmarEntradaInput.onEnter( function() { $('#btnConfirmarEntradaAceptar').trigger('click'); });

	goFormServicioEditarEntidad = $("#formServicioEditarEntidad").validate({
		invalidHandler: function(event, validator) {
			formInvalidHandler(event, validator, '#formServicioEditarEntidad');
		},
		rules: {
			cServicioEditarPlanId: "required",
			cServicioEditarPlanNombre: "required",
			cServicioEditarPlanTipo: "required",
			cServicioEditarPlanAfiliadoTipo: "required",
			cServicioEditarPlanAfiliadoEstrato: "required",
		}
	});

	goFormPacienteInformacion = $("#formPacienteInformacion").validate({
		invalidHandler: function(event, validator) {
			formInvalidHandler(event, validator, '#formPacienteInformacion');
		},
		rules: {
			cPacienteId: "required",
			nPacienteId: {
				required: true,
				min: 1,
				max: 9999999999999,
				digits: true
			},
			cPacienteLugarExpedicion: {
				required: true,
				minlength: 1,
				maxlength: 30
			},
			cPacienteNombre1: {
				required: true,
				minlength: 1,
				maxlength: 40,
				nombreApellido: gaPropiedadesIngreso['ExpresionHabilitaNombresApellidos']
			},
			cPacienteNombre2: {
				minlength: 1,
				maxlength: 40,
				nombreApellido: gaPropiedadesIngreso['ExpresionHabilitaNombresApellidos']
			},
			cPacienteApellido1: {
				required: true,
				minlength: 1,
				maxlength: 40,
				nombreApellido: gaPropiedadesIngreso['ExpresionHabilitaNombresApellidos']
			},
			cPacienteApellido2: {
				minlength: 1,
				maxlength: 40,
				nombreApellido: gaPropiedadesIngreso['ExpresionHabilitaNombresApellidos']
			},
			nNacimiento: {
				required: true,
				dateISO: true
			},
			nNacioPais: "required",
			nNacioDepartamento: "required",
			nNacioCiudad: "required",
			cPacienteGenero: "required",
			cIngresoVia: "required",
			cSeccion: {
				required: {
					depends: function(element) {
						return ("'05','06'".includes("'"+$('#cIngresoVia').val()+"'"));
					}
				}
			},
			cHabitacion: {
				required: {
					depends: function(element) {
						return ("'05','06'".includes("'"+$('#cIngresoVia').val()+"'"));
					}
				}
			}
		}
	});

	goFormPacienteGeneral = $("#formPacienteGeneral").validate({
		invalidHandler: function(event, validator) {
			formInvalidHandler(event, validator, '#formPacienteGeneral');
		},
		rules: {
			cPacienteEstadoCivil: "required",
			cEpicrisisEmail: "required",
			cPacientePermisoPermanenciaTiene: "required",
			cPacientePermisoPermanencia: {
				minlength: 1,
				maxlength: 220,
				required: {
					depends: function(element) {
						return ($("#cPacientePermisoPermanenciaTiene").val()==='SI');
					}
				}
			},
			cPacientePertenenciaEtnica: "required",
			cPacienteNivelEducativo: "required",
		}
	});

	goFormPacienteRecide = $("#formPacienteRecide").validate({
		invalidHandler: function(event, validator) {
			formInvalidHandler(event, validator, '#formPacienteRecide');
		},
		rules: {
			nPacienteRecidePais: "required",
			nPacienteRecideDepartamento: "required",
			nPacienteRecideCiudad: "required",
			cPacienteRecideBarrio: {
				minlength: 1,
				maxlength: 30,
				required: true
			},
			nPacienteRecideZona: "required",
			cPacienteRecideDireccion: {
				minlength: 5,
				maxlength: 30,
				required: true,
				direccion: gaPropiedadesIngreso['ExpresionHabilitaDirecion']
			}
		}
	});

	goFormPacienteContacto = $("#formPacienteContacto").validate({
		invalidHandler: function(event, validator) {
			formInvalidHandler(event, validator, '#formPacienteContacto');
		},
		rules: {
			cPacienteTelefono1: {
				minlength: 7,
				maxlength: 15,
				digits: true,
				required: true
			},
			cPacienteTelefono2: {
				minlength: 7,
				maxlength: 7,
				digits: true,
				required: false
			},
			cPacienteTelefono3: {
				minlength: 10,
				maxlength: 15,
				digits: true,
				required: true
			},
			cPacienteTelefono4: {
				minlength: 10,
				maxlength: 10,
				digits: true,
				required: false
			},
			cPacienteTieneEmail: "required",
			cPacienteEmail: {
				required: {
					depends: function(element) {
						return ($("#cPacienteTieneEmail").val()==='SI');
					}
				},
				email: {
					depends: function(element) {
						return ($("#cPacienteTieneEmail").val()==='SI');
					}
				}
			}
		}
	});

	goFormPacienteLaboral = $("#formPacienteLaboral").validate({
		invalidHandler: function(event, validator) {
			formInvalidHandler(event, validator, '#formPacienteLaboral');
		},
		rules: {
			nPacienteLaboralOcupacion: "required",
			cPacienteLaboralTrabajo: "required",
			cPacienteLaboralEmpresa: {
				minlength: 0,
				maxlength: 60,
				required: {
					depends: function(element) {
						return ($("#cPacienteLaboralTrabajo").val()=='01' || $("#cPacienteLaboralTrabajo").val()=='02');
					}
				}
			},
			cPacienteLaboralCargo: {
				minlength: 0,
				maxlength: 30,
				required: {
					depends: function(element) {
						return ($("#cPacienteLaboralTrabajo").val()=='01' || $("#cPacienteLaboralTrabajo").val()=='02');
					}
				}
			},
			cPacienteLaboralAntiguedad: {
				minlength: 0,
				maxlength: 30,
				required: {
					depends: function(element) {
						return ($("#cPacienteLaboralTrabajo").val()=='01' || $("#cPacienteLaboralTrabajo").val()=='02');
					}
				}
			},
			cPacienteLaboralDireccion: {
				minlength: 0,
				maxlength: 30,
				required: {
					depends: function(element) {
						return ($("#cPacienteLaboralTrabajo").val()=='01' || $("#cPacienteLaboralTrabajo").val()=='02');
					}
				},
				direccion: gaPropiedadesIngreso['ExpresionHabilitaDirecion']
			},
			cPacienteLaboralTelefono: {
				minlength: 7,
				maxlength: 15,
				digits: true
			}
		}
	});

	goFormPacienteReferencia = $("#formPacienteReferencia").validate({
		invalidHandler: function(event, validator) {
			formInvalidHandler(event, validator, '#formPacienteReferencia');
		},
		rules: {
			cPacienteReferenciaNombre: {
				minlength: 0,
				maxlength: 30,
				required: {
					depends: function(element) {
						return (gaPropiedadesIngreso['ViasRequierenReferencia'].includes("'"+$('#cIngresoVia').val()+"'"));
					}
				},
			},
			cPacienteReferenciaDireccion: {
				minlength: 0,
				maxlength: 60,
				required: {
					depends: function(element) {
						return (gaPropiedadesIngreso['ViasRequierenReferencia'].includes("'"+$('#cIngresoVia').val()+"'"));
					}
				},
				direccion: gaPropiedadesIngreso['ExpresionHabilitaDirecion']
			},
			cPacienteReferenciaTelefono: {
				minlength: 7,
				maxlength: 15,
				digits: true,
				required: {
					depends: function(element) {
						return (gaPropiedadesIngreso['ViasRequierenReferencia'].includes("'"+$('#cIngresoVia').val()+"'"));
					}
				},
			}
		}
	});

	goFormPacienteRemision = $("#formPacienteRemision").validate({
		invalidHandler: function(event, validator) {
			formInvalidHandler(event, validator, '#formPacienteRemision');
		},
		rules: {
			cRemitido: "required",
			cRemiteEntidad: {
				required: {
					depends: function(element) {
						return ($("#cRemitido").val()=='SI');
					}
				}
			},
			cRemitePais: {
				required: {
					depends: function(element) {
						return ($("#cRemitido").val()=='SI');
					}
				}
			},
			cRemiteDepartamento: {
				required: {
					depends: function(element) {
						return ($("#cRemitido").val()=='SI');
					}
				}
			},
			cRemiteCiudad: {
				required: {
					depends: function(element) {
						return ($("#cRemitido").val()=='SI');
					}
				}
			}
		}
	});


	goFormPacientePlan = $("#formPacientePlan").validate({
		invalidHandler: function(event, validator) {
			formInvalidHandler(event, validator, '#formPacientePlan');
		},
		rules: {
			cPacienteNoEPS: "required",
			cPacienteCarnet: {
				minlength: 5,
				maxlength: 20
			}
		}
	});

	goFormResponsableInformacion = $("#formResponsableInformacion").validate({
		invalidHandler: function(event, validator) {
			formInvalidHandler(event, validator, '#formResponsableInformacion');
		},
		rules: {
			cResponsablePaciente: "required",
			cResponsableParentesco: {
				required: {
					depends: function(element) {
						return ($("#cResponsablePaciente").val()=='OTRO');
					}
				}
			},
			cResponsableId: "required",
			nResponsableId: {
				required: true,
				min: 1,
				max: 9999999999999,
				digits: true
			},
			cResponsableLugarExpedicion: {
				required: true,
				minlength: 1,
				maxlength: 30
			},
			cResponsableNombre1: {
				required: true,
				minlength: 1,
				maxlength: 40,
				nombreApellido: gaPropiedadesIngreso['ExpresionHabilitaNombresApellidos']
			},
			cResponsableNombre2: {
				minlength: 1,
				maxlength: 40,
				nombreApellido: gaPropiedadesIngreso['ExpresionHabilitaNombresApellidos']
			},
			cResponsableApellido1: {
				required: true,
				minlength: 1,
				maxlength: 40,
				nombreApellido: gaPropiedadesIngreso['ExpresionHabilitaNombresApellidos']
			},
			cResponsableApellido2: {
				minlength: 1,
				maxlength: 40,
				nombreApellido: gaPropiedadesIngreso['ExpresionHabilitaNombresApellidos']
			}
		}
	});

	goFormResponsableRecide = $("#formResponsableRecide").validate({
		invalidHandler: function(event, validator) {
			formInvalidHandler(event, validator, '#formResponsableRecide');
		},
		rules: {
			nResponsableRecidePais: "required",
			nResponsableRecideDepartamento: "required",
			nResponsableRecideCiudad: "required",
			cResponsableRecideBarrio: "required",
			cResponsableRecideDireccion: {
				minlength: 5,
				maxlength: 30,
				required: true,
				direccion: gaPropiedadesIngreso['ExpresionHabilitaDirecion']
			}
		}
	});

	goFormResponsableContacto = $("#formResponsableContacto").validate({
		invalidHandler: function(event, validator) {
			formInvalidHandler(event, validator, '#formResponsableContacto');
		},
		rules: {
			cResponsableTelefono1: {
				minlength: 7,
				maxlength: 15,
				digits: true,
				required: true
			},
			cResponsableTelefono2: {
				minlength: 7,
				maxlength: 7,
				digits: true,
				required: false
			},
			cResponsableTelefono3: {
				minlength: 10,
				maxlength: 15,
				digits: true,
				required: true
			},
			cResponsableTelefono4: {
				minlength: 10,
				maxlength: 10,
				digits: true,
				required: false
			},
			cResponsableTieneEmail: "required",
			cResponsableEmail: {
				required: {
					depends: function(element) {
						return ($("#cResponsableTieneEmail").val()==='SI');
					}
				},
				email: {
					depends: function(element) {
						return ($("#cResponsableTieneEmail").val()==='SI');
					}
				}
			}
		}
	});

	goFormResponsableLaboral = $("#formResponsableLaboral").validate({
		invalidHandler: function(event, validator) {
			formInvalidHandler(event, validator, '#formResponsableLaboral');
		},
		rules: {
			cResponsableLaboralTrabajo: "required",
			cResponsableLaboralEmpresa: {
				minlength: 0,
				maxlength: 60,
				required: {
					depends: function(element) {
						return ($("#cResponsableLaboralTrabajo").val()=='01' || $("#cResponsableLaboralTrabajo").val()=='02');
					}
				}
			},
			cResponsableLaboralCargo: {
				minlength: 0,
				maxlength: 30,
				required: {
					depends: function(element) {
						return ($("#cResponsableLaboralTrabajo").val()=='01' || $("#cResponsableLaboralTrabajo").val()=='02');
					}
				}
			},
			cResponsableLaboralAntiguedad: {
				minlength: 0,
				maxlength: 30,
				required: {
					depends: function(element) {
						return ($("#cResponsableLaboralTrabajo").val()=='01' || $("#cResponsableLaboralTrabajo").val()=='02');
					}
				}
			},
			cResponsableLaboralDireccion: {
				minlength: 0,
				maxlength: 30,
				required: {
					depends: function(element) {
						return ($("#cResponsableLaboralTrabajo").val()=='01' || $("#cResponsableLaboralTrabajo").val()=='02');
					}
				},
				direccion: gaPropiedadesIngreso['ExpresionHabilitaDirecion']
			},
			cResponsableLaboralTelefono: {
				minlength: 7,
				maxlength: 15,
				digits: true
			}
		}
	});

	goFormResponsableReferencia = $("#formResponsableReferencia").validate({
		invalidHandler: function(event, validator) {
			formInvalidHandler(event, validator, '#formResponsableReferencia');
		},
		rules: {
			cResponsableReferenciaNombre: {
				minlength: 0,
				maxlength: 30,
				required: {
					depends: function(element) {
						return (gaPropiedadesIngreso['ViasRequierenReferencia'].includes("'"+$('#cIngresoVia').val()+"'"));
					}
				},
			},
			cResponsableReferenciaDireccion: {
				minlength: 0,
				maxlength: 60,
				required: {
					depends: function(element) {
						return (gaPropiedadesIngreso['ViasRequierenReferencia'].includes("'"+$('#cIngresoVia').val()+"'"));
					}
				},
				direccion: gaPropiedadesIngreso['ExpresionHabilitaDirecion']
			},
			cResponsableReferenciaTelefono: {
				minlength: 7,
				maxlength: 15,
				digits: true,
				required: {
					depends: function(element) {
						return (gaPropiedadesIngreso['ViasRequierenReferencia'].includes("'"+$('#cIngresoVia').val()+"'"));
					}
				},
			}
		}
	});

	goFormServicioInformacion = $("#formServicioInformacion").validate({
		invalidHandler: function(event, validator) {
			formInvalidHandler(event, validator, '#formServicioInformacion');
		},
		rules: {
			cTriage: {
				required: {
					depends: function(element) {
						return ($('#cIngresoVia').val()=='01');
					}
				},
			},
			cEnfermedadActual: {
				required: {
					depends: function(element) {
						return ($('#cIngresoVia').val()=='01');
					}
				},
				direccion: gaPropiedadesIngreso['ExpresionHabilitaDirecion']
			},
			cMedicoTratanteId: {
				required: {
					depends: function(element) {
						return ("'05','06'".includes("'"+$('#cIngresoVia').val()+"'"));
					}
				},
			},
			cMedicoTratanteNombre: {
				required: {
					depends: function(element) {
						return ("'05','06'".includes("'"+$('#cIngresoVia').val()+"'"));
					}
				},
			}
		}
	});


	goFormServicioPlanUsar = $("#formServicioPlanUsar").validate({
		invalidHandler: function(event, validator) {
			formInvalidHandler(event, validator, '#formServicioPlanUsar');
		},
		rules: {
			cPlanUsar: "required",
			cPlanUsarDescripcion: "required",
			cPlanUsarEntidadTipo: "required",
			nPlanUsarAfiliadoTipo: "required",
			nPlanUsarContrato: "required",
			nPlanUsarEntidad: "required",
			nPlanUsarEstrato: "required",
			nPlanUsarRegional: "required",
			nPlanUsarTipo: "required",
			nMapla: "required",
		}
	});

	$('#btnLinkVolver').click(function () {
		$.confirm({
			title: 'Cerrar',
			content: (glGuardado==false?'¿Está seguro de cerrar?, puede perder los cambios sin guarda.': '¿Está seguro de cerrar?'),
			type: 'orange',
			buttons: {
				Si: {
					btnClass: 'btn-warning',
					action: function(){
						location.href = $('#aLinkVolver').attr("href");
					}
				},
				Cancelar: {
					btnClass: 'btn-blue',
					action: function(){}
				}
			}
		});
	});

	$('#cMedicoTratante').autoComplete({
		preventEnter: true,
		resolverSettings: {
			url: "vista-ingresos/ajax/registroIngresos.ajax?accion=medicosTratantes",
			queryKey: 'nombre',
			requestThrottling: 500,
			fail: function (e) {},
		},
		formatResult: function (taItem) {
			laItem = { value: '', text: '', html: ''};

			if(taItem.REGISTRO!==undefined && taItem.MEDICO!==undefined){
				if(taItem.MEDICO.length>0 && taItem.REGISTRO.length>0){
					laItem = {
						value: taItem.REGISTRO,
						text: taItem.MEDICO + ' - '+ taItem.REGISTRO,
						html: taItem.MEDICO + ' - '+ taItem.REGISTRO
					};
				}
			}
			return laItem;
		},
		noResultsText: 'No hay coincidencias',
	}).autoComplete('set',
		{REGISTRO: '', MEDICO: ''}

	).on('autocomplete.select', function(evt, item) {
		if(item.REGISTRO!=='' && item.MEDICO!==''){
			$.confirm({
				title: 'Asignar',
				content: '¿Está seguro de asignar a '+item.MEDICO+' como medico tratante?',
				type: 'orange',
				buttons: {
					Si: {
						btnClass: 'btn-warning',
						action: function(){
							$('#cMedicoTratanteId').val(item.REGISTRO);
							$('#cMedicoTratanteNombre').val(item.MEDICO);
							$('#cMedicoTratante').val('');
						}
					},
					Cancelar: {
						btnClass: 'btn-blue',
						action: function(){}
					}
				}
			});
		}

	}).on('autocomplete.freevalue', function(evt, value) {
		$('#cMedicoTratante').val('');

	});

	$('#cServicioEditarPlan').autoComplete({
		preventEnter: true,
		resolverSettings: {
			url: "vista-ingresos/ajax/registroIngresos.ajax?accion=planes",
			queryKey: 'nombre',
			requestThrottling: 500,
			fail: function (e) {},
		},
		formatResult: function (taItem) {
			laItem = { value: '', text: '', html: ''};

			if(taItem.CODIGO!==undefined && taItem.NOMBRE!==undefined){
				if(taItem.NOMBRE.length>0 && taItem.CODIGO.length>0){
					laItem = {
						value: taItem.CODIGO,
						text: taItem.NOMBRE + ' - '+ taItem.CODIGO,
						html: taItem.NOMBRE + ' - '+ taItem.CODIGO
					};
				}
			}
			return laItem;
		},
		noResultsText: 'No hay coincidencias',
	}).autoComplete('set',
		{CODIGO: '', NOMBRE: ''}

	).on('autocomplete.select', function(evt, item) {
		if(item.CODIGO!=='' && item.NOMBRE!==''){
			$('#cServicioEditarPlanId').val(item.CODIGO);
			$('#cServicioEditarPlanIdAyuda').html('<div class="alert alert-success alert-dismissible mt-2 p-2 fade show" role="alert"><small><b>Plan seleccionado!</b><br/>Complete la informaci&oacute;n, luego haga clic en el bot&oacute;n guardar, para agregalo o actualizarlo.</small><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
			$('#cServicioEditarPlanNombre').val(item.NOMBRE);
			$('#cServicioEditarPlan').val('');
		}

	}).on('autocomplete.freevalue', function(evt, value) {
		$('#cServicioEditarPlan').val('');

	});

	planAgregarParticular();

	$('#btnGuardarIngreso').click(function () {
		let lnIncompletos = 0;
		let lcIncompletos = '';

		$('form[data-requerido-guardar=si]').each(function( index ) {
			$(this).data('no-show-alert','si');
			if($(this).valid()==false){
				lcIncompletos += '<li class="text-danger">'+$(this).data('title')+'</li>';
				lnIncompletos += 1;
			}
			$(this).data('no-show-alert', 'no');
		});

		if(lnIncompletos==0){
			$.confirm({
				title: 'Guardar',
				content: '¿Está seguro de <b>guardar la informaci&oacute;n</b> actual?',
				type: 'blue',
				buttons: {
					Si: {
						btnClass: 'btn-warning',
						action: function(){
							let laFieldsConfirm = [
													{nHistoria: 'Consecutivo HC'},
													{Indetificacion: [
														{cPacienteId: 'Identificación'},
														{nPacienteId: '-'},
													]},
													{Nombres: [
														{cPacienteNombre1: 'Nombres'},
														{cPacienteNombre2: '-'},
														{cPacienteApellido1: '-'},
														{cPacienteApellido2: '-'},
													]},
													{cPacienteEstadoCivil: ''},
													{cEpicrisisEmail: ''},
													{cPacientePermisoPermanencia: ''},
													{cPacientePertenenciaEtnica: ''},
													{cPacienteNivelEducativo: ''},
													{Recide: [
														{nPacienteRecidePais: 'Ubicación'},
														{nPacienteRecideDepartamento: '-'},
														{nPacienteRecideCiudad: '-'},
														{nPacienteRecideLocalidad: '-'},
														{cPacienteRecideBarrio: '-'},
														{nPacienteRecideZona: '-'}
													]},
													{cPacienteRecideDireccion: ''},
													{cPacienteTelefono1: ''},
													{cPacienteTelefono2: ''},
													{cPacienteTelefono3: ''},
													{cPacienteTelefono4: ''},
													{cPacienteTieneEmail: 'Tiene e-mail'},
													{cPacienteEmail: 'e-mail'},
													{nPacienteLaboralOcupacion: ''},
													{cPacienteLaboralTrabajo: ''},
													{cPacienteLaboralEmpresa: ''},
													{cPacienteLaboralCargo: ''},
													{cPacienteLaboralAntiguedad: ''},
													{cPacienteLaboralDireccion: ''},
													{cPacienteLaboralTelefono: ''},
													{cPacienteReferenciaNombre: ''},
													{cPacienteReferenciaDireccion: ''},
													{cPacienteReferenciaTelefono: ''},

												];

							let lcTemplateConfirmCard = $('#templateConfirmCard').html();
							let lcTemplateConfirmCol = $('#templateConfirmCol').html();

							var lcConfirmData = '';

							$.each(laFieldsConfirm, function(lnFieldConfirm, laFieldConfirm) {
								$.each(laFieldConfirm, function( lcFieldName, lvFieldRules) {

									if(Array.isArray(lvFieldRules)==true){
										lcFieldTitle = '';
										lcFieldValueAll = ''

										$.each(lvFieldRules, function( lnFieldRule, lvFieldRule) {
											$.each(lvFieldRule, function( lcSubFieldName, lcSubFieldTitle) {
												let $loControl = $("#"+lcSubFieldName);

												lcFieldValue = ($loControl.is("input")===true?$loControl.val():$("#"+lcSubFieldName+" option:selected").text());

												if(lcFieldValue !== null && lcFieldValue !== ''){
													lcSubFieldTitle = (lcSubFieldTitle==''?$('label[for='+lcSubFieldName+']').text():lcSubFieldTitle);
													if(lcSubFieldTitle !== null && lcSubFieldTitle !== '' && lcSubFieldTitle !== '-'){
														lcFieldTitle += (lcFieldTitle==''?'':' ')+lcSubFieldTitle;
													}
													lcFieldValueAll += (lcFieldValueAll==''?'':' ')+lcFieldValue;
												}
											});
										});

										if(lcFieldTitle!=='' && lcFieldValueAll!==''){
											lcConfirmData += lcTemplateConfirmCol.replace('%TITLE%', lcFieldTitle).replace('%VALUE%', lcFieldValueAll);
										}

									}else{
										let $loControl = $("#"+lcFieldName);

										lcFieldValue = ($loControl.is("input")===true?$loControl.val():$("#"+lcFieldName+" option:selected").text());

										if(lcFieldValue !== null && lcFieldValue !== ''){
											lcFieldTitle = lvFieldRules;
											lcFieldTitle = (lcFieldTitle==''?$('label[for='+lcFieldName+']').text():lcFieldTitle);
											if(lcFieldTitle !== null && lcFieldTitle !== ''){
												lcConfirmData += lcTemplateConfirmCol.replace('%TITLE%', lcFieldTitle).replace('%VALUE%', lcFieldValue);
											}
										}
									}
								});

							});

							lcConfirmData = lcTemplateConfirmCard.replace('%DATA%', lcConfirmData);


							$.confirm({
								title: 'Guardar ingreso',
								content: lcConfirmData,
								columnClass: 'col-md-12',
								type: 'orange',
								buttons: {
									Guardar: {
										text: 'Sí, es correcta',
										btnClass: 'btn-success',
										action: function () {
											
											oModalEspera.mostrar('Espere por favor', 'Guardando');
											
											let laPlanes = $('#tableEntidades').bootstrapTable('getData');
											let laIngreso = new Map();
											$('form[data-requerido-guardar=si]').each(function( index ) {
												$.each($(this).serializeArray(), function( lnFieldIndex, lnFieldData) {
													laIngreso[lnFieldData.name] = lnFieldData.value;
												});
											});

											$.ajax({
												type: "POST",
												url: "vista-ingresos/ajax/registroIngresos.ajax",
												data: {accion:'guardar', ingreso:laIngreso, planes:laPlanes},
												dataType: "json"
											})
											.done(function(response) {
												oModalEspera.ocultar();
												
												if(response!==undefined){
													if(response.NUMERO_INGRESO!==undefined){
														response.NUMERO_INGRESO = parseInt(response.NUMERO_INGRESO);
														if(response.GUARDADO==true){
															$('#nIngreso').val(response.NUMERO_INGRESO);
															$('#nTriageId').val(response.NUMERO_TRIAGE);
															
															let $loIngreso =  response.INGRESO;
															$('#nHistoria').val($loIngreso.nHistoria);
															$('#nHistoriaAyuda').html('Historia No. '+$loIngreso.nHistoria);
	
															$('#nCita').val($loIngreso.nCita);
															$('#nCitaAyuda').html('CT: '+$loIngreso.nCita);
															
															$('#nConsulta').val($loIngreso.nConsulta);
															$('#nConsultaAyuda').html('CN: '+$loIngreso.nConsulta);
															
															$('#btnGuardarIngreso').attr('disabled','disabled');
															$('#btnDocumentoGarantia').removeAttr('disabled');
															$('#btnImprimir').removeAttr('disabled');
															$('#btnManilla').removeAttr('disabled');															
															
															fnAlert("Se guardo el Ingreso No."+response.NUMERO_INGRESO, 'Ingreso', 'fas fa-save', 'green');
														}else{
															if(response.NUMERO_INGRESO>0){
																fnAlert("No se guardo el Ingreso No."+response.NUMERO_INGRESO+". "+response.MENSAJE, 'Ingreso');
															}else{
																fnAlert("No se guardo el Ingreso. "+response.MENSAJE, 'Ingreso');
															}
														}
													}
												}else{
													fnAlert("No se proceso la solicitud para guardar", 'Ingreso');
												}
											})
											.fail(function(jqXHR, textStatus, errorThrown) {
												oModalEspera.ocultar();
												
												alert("Se presentó un error al guardar el ingreso. \n"+jqXHR.responseText);
											});
										}
									},
									Cancelar: {
										text: 'Es incorrecta',
										btnClass: 'btn-warning',
										action: function () {
										}
									},
								}
							});

						}
					},
					Cancelar: {
						btnClass: 'btn-blue',
						action: function(){ }
					}
				}
			});
		}else{
			$.alert({
				icon: 'fas fa-save',
				title: 'Guardar',
				type: 'red',
				content: "Existe(n) "+lnIncompletos+" bloque(s) que tienen campos que no cumple(n) con los requerimientos para guardar el ingreso.<br/><br/><ul>"+lcIncompletos+"</ul>",
				buttons: {
					Cerrar: {text: 'Cerrar',}
				}
			});
		}
	});

	/*$('#nIngreso').val('3251943');
	$('#nTriageId').val('707164');*/

});