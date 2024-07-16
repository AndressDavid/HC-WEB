var goTabla=$('#tblOrdenesAmbulatorias'),
	goMobile = new MobileDetect(window.navigator.userAgent),
	gcUrlajax = "vista-ordenes-ambulatorias/ajax/ajax",
	goColorFila = {
		'1': '#BBE0F0',
		'2': '#ffff80'
	};

$(function () {
	IniciarTabla();
	oModalOrdAmbPDF.inicializar();
	oModalOrdAmbPDF.bMostrarInfo=true;
	limpiar();

	$("textarea").on("focusout",function(e){
		$(this).val( $(this).val().trim() );
	})
	$('#selTipDocOrdAmb').tiposDocumentos({horti: "1"});
	$('#btnLimpiarOrdenesAmb').on('click', limpiar);
	$('#btnIngresarOrdenesAmb').on('click', abrirOrdAmb);
	$('#btnSalirOrdenesAmb').on('click', retornarPagina);
	$('#btnAyudaOrdenesAmb').on('click', datosPacienteOrdenes);
})

function datosPaciente() {
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: { accion:'paciente', tipoIde: $("#selTipDocOrdAmb").val(), numIde: $("#txtNumDocOrdAmb").val()},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				if (loDatos.nombrePaciente.length>1){
					$("#txtNombrePacienteOrdAmb").val(loDatos.nombrePaciente);
					$('#selTipDocOrdAmb, #txtNumDocOrdAmb').prop("disabled",true);
					consultarOrdenes();
					ultimoIngresoAmbulatorio();
				}else{
					limpiar();
					fnAlert('No existe paciente, verifique identificación del paciente.', 'Consulta paciente', 'fas fa-exclamation-circle', 'blue', 'medium', '');
				}
			} else {
				fnAlert(loDatos.error);
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consulta datos del paciente.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar datos del paciente.');
	});
}

function consultarOrdenes() {
	goTabla.bootstrapTable('removeAll');
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {
			accion:'ordenes', tipoIde: $("#selTipDocOrdAmb").val(), numIde: $("#txtNumDocOrdAmb").val()},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				goTabla.bootstrapTable('refreshOptions', {data: loDatos.datos});
			} else {
				fnAlert(loDatos.error);
			}

		} catch(err) {
			fnAlert('No se pudo realizar la consulta de ordenes ambulatorias.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar ordenes ambulatorias.');
	});
}

function ultimoIngresoAmbulatorio() {
	goTabla.bootstrapTable('removeAll');
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {
			accion:'ultimoIngreso', tipoIde: $("#selTipDocOrdAmb").val(), numIde: $("#txtNumDocOrdAmb").val()},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == '') {
				laDatos = loDatos.ingresoamb.trim().split('-');
				lnFechaIngreso = parseInt(laDatos[0].trim());
				lnDiasMaximos = parseInt(laDatos[1].trim());

				if (lnFechaIngreso>0){
					lcFechaIngreso = strNumAFecha(lnFechaIngreso);
					ldFechaIngreso = Date.parse(lcFechaIngreso);
					ldFechaActual = new Date();
					lcFechaActual = ldFechaActual.getFullYear() + "-" + (ldFechaActual.getMonth() + 1) + "-" + ldFechaActual.getDate();
					ldFechaHoy = Date.parse(lcFechaActual);
					lnDiferenciaDias = Math.floor((ldFechaHoy - ldFechaIngreso)/(1000*60*60*24));

					if (lnDiferenciaDias > lnDiasMaximos){
						$("#btnIngresarOrdenesAmb").attr("disabled", true);
						lcTextoAlerta = 'No se puede realizar ordenes ambulatorios, fecha último ingreso ambulatorio (' + lcFechaIngreso + ') del paciente, excede los días (' + lnDiasMaximos + ') máximos permitidos.';
						fnAlert(lcTextoAlerta, 'Valida ordenes ambulatorias', 'fas fa-exclamation-circle', 'blue', 'medium', '');
					}else{
						$("#btnIngresarOrdenesAmb").attr("disabled", false);
					}
				}else{
					$("#btnIngresarOrdenesAmb").attr("disabled", true);
					fnAlert('Fecha de ingreso no existe para este paciente, revise por favor.', 'Valida ordenes ambulatorias', 'fas fa-exclamation-circle', 'red', '', '');
				}
			} else {
				fnAlert(loDatos.error);
			}

		} catch(err) {
			fnAlert('No se pudo realizar la consulta último ingreso.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar último ingreso.');
	});
}

function limpiar() {
	goTabla.bootstrapTable('removeAll');
	$('#selTipDocOrdAmb, #txtNumDocOrdAmb').prop("disabled",false);
	$("#btnIngresarOrdenesAmb").attr("disabled", true);
	$("#frmFiltrosOrdenesAmb").get(0).reset();
	$('#selTipDocOrdAmb').focus();
}

function IniciarTabla() {
	$('#tblOrdenesAmbulatorias').bootstrapTable({
		classes: 'table table-bordered table-hover table-sm table-responsive-sm', // table-striped
		theadClasses: 'thead-light',
		locale: 'es-ES',
		undefinedText: 'N/A',
		toolbar: '#toolBarLst',
		height: '450',
		search: false,
		pagination: false,
		rowStyle: formatoColorFila,
		iconSize: 'sm',
		columns: [
			{
				title: 'Consecutivo',
				field: 'ORDEN',
				halign: 'center',
				width: 5, widthUnit: "%"
			},{
				title: 'Ingreso',
				field: 'INGRESO',
				halign: 'center',
				width: 5, widthUnit: "%"
			},
			{
				title: 'Fecha',
				field: 'FECHA',
				formatter: function(tnValor, toFila) { return strNumAFecha(tnValor,'/'); },
				width: 5, widthUnit: "%"
			},
			{
				title: 'Hora',
				field: 'HORA',
				formatter: function(tnValor, toFila) { return strNumAHora(tnValor); },
				width: 5, widthUnit: "%"
			},
			{
				title: 'Diagnóstico',
				field: 'DESCRIPCION_CIE',
				halign: 'center',
				width: 40, widthUnit: "%"
			},
			{
				title: 'Plan',
				field: 'DESCRIPCION_PLAN',
				halign: 'center',
				width: 40, widthUnit: "%"
			},
			{
				title: 'PDF',
				align: 'center',
				events: eventoVerOrdenAmb,
				formatter: '<a class="verOrdenAmb" href="javascript:void(0)" title="Consultar orden ambulatoria"><i class="fas fa-folder-open" style="color: #2471A3;"></i></a>'
			}
		],
	});
}

var eventoVerOrdenAmb = {
	'click .verOrdenAmb': function(e, tcValor, toFila, tnIndice) {
		var lbMostrar = true;
		oModalOrdAmbPDF.consultar(
			lbMostrar,
			toFila.INGRESO,
			toFila.TIPOIDE,
			toFila.NROIDE,
			toFila.FECHA+''+toFila.HORA,
			toFila.CONSEC_CITA,
			toFila.CONSEC_CONSULTA,
			toFila.ORDEN);
	}
}

function abrirOrdAmb() {
	if ($("#selTipDocOrdAmb").val()!='' && $("#txtNumDocOrdAmb").val()!=''){
		lcTipoIde = $("#selTipDocOrdAmb").val();
		lcNroIde = $("#txtNumDocOrdAmb").val();
		lcIdePaciente = 'ID';
		lcPaciente = 'PACIENTE';
		formPostTemp('modulo-ordenes-ambulatorias&q=ordenes_ambulatorias', {'t':lcTipoIde, 'n':lcNroIde}, false);
	}else{
		fnAlert('Por favor verifique, tipo y número de identificación del paciente.');
	}
}

function formatoColorFila(toFila, tnIndice) {
	return {css: {'background-color':goColorFila['1']}};
}

function retornarPagina(){
	fnConfirm('¡Esta seguro de retornar a la página anterior?</b>', 'ORDENES AMBULATORIAS', false, false, false,
		{
			text: 'Si',
			action: function(){
				window.history.back();
			}
		},
		{ text: 'No' }
	)
}

function datosPacienteOrdenes(){
	var lcTipoIde = $("#selTipDocOrdAmb").val();
	var lcNroIde = $("#txtNumDocOrdAmb").val();

	if (lcTipoIde==''){
		fnAlert('Tipo de identificación obligatoria, revise por favor.');
		return false;
	}

	if (lcNroIde=='' || lcNroIde==0){
		fnAlert('Número de identificación obligatoria, revise por favor.');
		return false;
	}

	llValidarIde = valNumeroId(lcNroIde);
	if (llValidarIde==true){
		datosPaciente();
	}else{
		fnAlert("Número de identificación incorrecto, verifique por favor.");
	}
}
