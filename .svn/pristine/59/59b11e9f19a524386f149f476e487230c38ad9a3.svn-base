var goTabla=$('#tblPacientes'),
	goTblHC=$('#tblConsultas'),
	goMobile = new MobileDetect(window.navigator.userAgent),
	gcUrlajax = "vista-hc-cons-externa/ajax/ajax",
	gdFechaConsulta,
	goFilaSel,
	goEstados = {},
	gaEstadosNoPermiteHC = ['6','80'],
	gcTiposMed = "1, 3, 4, 6, 8, 10, 11, 12, 13, 91",
	lcTipo = (new URLSearchParams(window.location.href)).get('cp'),
	goFilaSelInter
	checkAll = false;
	;

$(function() {
	$("#frmFiltros label").css('margin-bottom','0.1rem');
	IniciarListas(buscarPacientes);
	IniciarTabla();
	IniciarTablaHC();
	$('#selTipDoc').tiposDocumentos({horti: "1"});
	$('#frmFiltros .input-group.date').datepicker({
		autoclose: true,
		clearBtn: true,
		daysOfWeekHighlighted: "0,6",
		format: "yyyy-mm-dd",
		language: "es",
		todayBtn: "linked",
		todayHighlight: true,
		toggleActive: true,
		weekStart: 1
	});
	$('#btnLimpiar').on('click', limpiar);
	$('#btnBuscar').on('click', buscarPacientes);
	$('#btnNuevaConsulta').on('click', nuevaConsulta);
	$('#selEspecialidad').on('change', function(){
		consultaMedicos($(this).val(), true)
	});

	ingresoSmartRoomEstablecer();
});

function ingresoSmartRoomEstablecer(){
	if (gnIngresoSmartRoom>0) {
		$("#txtIngreso").val(gnIngresoSmartRoom).attr("disabled",true);
	}
}

$("#allCheck").change(function() {
	activarFiltrosFecha(null, null);
});

function activarFiltrosFecha(fechaInicio, FechaFin){

	if(checkAll){
		$("#txtFechaIni").val(fechaInicio ?? "");
		$("#txtFechaFin").val(FechaFin ?? "");

		$("#txtFechaIni").prop( "disabled", true );
		$("#txtFechaFin").prop( "disabled", true );

		checkAll = false;
	}else{

		fechaActualFormato= new Date().toLocaleDateString('en-CA');
		fechaIn = (fechaInicio == '' || fechaInicio == null ) ? fechaActualFormato :fechaInicio ;
		fechafn = (FechaFin == '' || FechaFin == null ) ? fechaActualFormato :FechaFin ;



		$("#txtFechaIni").val(fechaIn);
		$("#txtFechaFin").val(fechafn);

		$("#txtFechaIni").prop( "disabled", false );
		$("#txtFechaFin").prop( "disabled", false );
		checkAll = true;
	}
}

function limpiar(){
	$("#txtFechaIni").prop( "disabled", false );
	$("#txtFechaFin").prop( "disabled", false );
	checkAll = true;
	goTabla.bootstrapTable('removeAll');
	$("#frmFiltros").get(0).reset();
	ingresoSmartRoomEstablecer();
}

function buscarPacientes(){
	if ($("#txtNumDoc").val()!=='' && $("#selTipDoc").val()=='') {
		$("#selTipDoc").focus();
		fnAlert('Debe seleccionar el tipo de documento');
		return false;
	}
	if ($("#selTipDoc").val()!=='' && $("#txtNumDoc").val()=='') {
		$("#txtNumDoc").focus();
		fnAlert('Debe indicar el número de documento');
		return false;
	}
	if ($("#txtFechaIni").val()>$("#txtFechaFin").val()) {
		$("#txtFechaIni").focus();
		fnAlert('La fecha inicial debe ser menor o igual que la fecha final');
		return false;
	}

	if(($("#txtFechaIni").val()=='') && ($("#txtFechaFin").val()=='') && checkAll){
		fnAlert('Debe indicar un rango de fechas valido, un número de ingreso o un número de documento');
		return false;
	}

	if (
		($("#txtFechaIni").val()=='') && ($("#txtFechaFin").val()=='')
		&&
		(
			($("#txtIngreso").val()=='') && ($("#txtNumDoc").val()=='')
		)
		&& !checkAll
		) {
		$("#txtIngreso").focus();
		fnAlert('Debe ingresar un número de ingreso o un número de documento');
		return false;
	}

	fechaini = $("#txtFechaIni").val();
	fechafin = $("#txtFechaFin").val();

	goTabla.bootstrapTable('removeAll');
	goTabla.bootstrapTable('showLoading');
	activarFiltros(false);
	var loData = {
			accion:'pacientes',
			ingreso: 	$("#txtIngreso").val(),
			tipoId: 	$("#selTipDoc").val(),
			numId: 		$("#txtNumDoc").val(),
			fechaini: 	fechaini,
			fechafin: 	fechafin,
			estado: 	$("#selEstado").val(),
			codesp: 	$("#selEspecialidad").val(),
			regmed: 	$("#selMedico").val(),
			origSol:	lcTipo
		};
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: loData,
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			activarFiltrosFecha(fechaini, fechafin);
			if (loDatos.error == ''){
				goTabla.bootstrapTable('refreshOptions', {data: loDatos.datos});
				gdFechaConsulta = new Date(loDatos.fechahora).getTime();
			} else {
				fnAlert(loDatos.error)
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consulta de pacientes.')
		} finally {
			$("#allCheck").prop( "checked", false );
			checkAll = false;
			activarFiltrosFecha(fechaini, fechafin);
			activarFiltros(true);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar pacientes.');
		$("#allCheck").prop( "checked", false );
		checkAll = false;
		activarFiltrosFecha(fechaini, fechafin);
		activarFiltros(true);
	});
}


function activarFiltros(tbActivar){
	$('#btnLimpiar, #btnBuscar').attr('disabled',!tbActivar);
	$('#frmFiltros input, #frmFiltros select').attr('disabled',!tbActivar);
	ingresoSmartRoomEstablecer();
}


function buscarConsultas(toFila){
	if (toFila.NINORD > 0) {
		goTblHC.bootstrapTable('removeAll').bootstrapTable('showLoading');
		$("#divPacienteConsultas").html('Espere por favor ... <i class="fas fa-circle-notch fa-spin" style="font-size: 1.5em; color: Tomato;">');
		$("#modalConsultas").modal("show");
		$('#btnNuevaConsulta').attr("disabled",true);
		goFilaSel = toFila;

		$.ajax({
			type: "POST",
			url: gcUrlajax,
			data: {
				accion:'consultas',
				ingreso: toFila.NINORD,
				tipoId: toFila.TIDORD,
				numId: toFila.NIDORD,
			},
			dataType: "json"
		})
		.done(function(loRet) {
			try {
				if (loRet.error == ''){
					var lcHtml = [
							'<table><tr>',
							'<td style="text-align:center;width:2.5rem"><br><i class="fa fa-'+(loRet.ingreso.cSexo=='F'? 'female': 'male')+' fa-3x"></i></td>',
							'<td>Paciente: <b>'+loRet.ingreso.cNombre+'</b>',
							'<br>Ingreso: <b>'+toFila.NINORD+'</b>, Fecha: <b>'+loRet.ingreso.nIngresoFecha+'</b> Vía: <b>'+loRet.ingreso.cDesVia+'</b>',
							'<br>Historia: <b>'+loRet.ingreso.nHistoria+'</b>, Edad: <b>'+loRet.ingreso.aEdad.y+' A, '+loRet.ingreso.aEdad.m+' M, '+loRet.ingreso.aEdad.d+' D</b>',
							'</td></tr></table>'
						].join('');
					$("#divPacienteConsultas").html(lcHtml);
					goTblHC.bootstrapTable('refreshOptions', {data: loRet.datos});
					$('#btnNuevaConsulta').attr("disabled",false);
				} else {
					$("#modalConsultas").modal("hide");
					fnAlert(loRet.error);
				}
			} catch(err) {
				$("#modalConsultas").modal("hide");
				fnAlert('No se pudo consultar atenciones del paciente.')
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			$("#modalConsultas").modal("hide");
			fnAlert('Se presentó un error al consultar atenciones del paciente.');
		});

	} else {
		fnAlert('El paciente no tiene ingreso');
	}
}


function IniciarListas(tfFuncion){
	activarFiltros(false);
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'listas', codesp:goUser.cesp, tipos:gcTiposMed},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				//estados
				goEstados = loDatos.estados;
				var loSelect = $("#selEstado");
				loSelect.append('<option value="">TODOS</option>');
				$.each(goEstados, function(lcClave, loEstado) {
					loSelect.append('<option value="' + lcClave + '"' + (lcClave=='8'?' selected="selected"':'') + '>' + loEstado.DESCR + '</option>');
				});
				crearConvencion();
				//especialidades
				loSelect = $("#selEspecialidad");
				loSelect.append('<option value="">TODAS</option>');
				$.each(loDatos.especialidades, function(lcCodigo, lcDescrip) {
					loSelect.append('<option value="' + lcCodigo + '"' + (lcCodigo==goUser.cesp?' selected="selected"':'') + '>' + lcDescrip + '</option>');
				});
				//medicos
					llenarListaMedicos(loDatos.medicos);
				if (typeof tfFuncion == 'function') tfFuncion();
			} else {
				fnAlert(loDatos.error)
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consulta de estados.')
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó una falla al consultar estados.');
	});
}


function crearConvencion() {
	var lcPopover = '<div class="container" width="380px"><div class="row"><div class="col"><small><table>',
		lnNum = 0, lnCol = 0
		lnNumEstados = Object.values(goEstados).length / 2;
	$.each(goEstados, function(lcClave, loEstado) {
		lnNum++;
		if(lnNum>lnNumEstados && lnCol==0){
			lcPopover += '</table></small></div><div class="col"><small><table>';
			lnCol++;
		}
		lcPopover += '<tr><td style="background-color:#'+loEstado.COLOR+'">'+loEstado.DESCR+'</td></tr>';
	});
	lcPopover += '</table></small></div></div></div>';
	$("#btnConvencion").popover({
		animation: false,
		html: true,
		sanitize: false,
		placement: 'bottom',
		trigger: 'hover', // click | hover | focus | manual
		title: 'Estados Procedimientos',
		content: lcPopover,
		template: '<div class="popover" role="tooltip" style="width:400px;max-width:550px;"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
	});
}


function consultaMedicos(tcCodEsp, tbActivarSelect) {
	$("#selMedico").attr('disabled',true);
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'medicos', codesp:tcCodEsp, tipos:gcTiposMed},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				llenarListaMedicos(loDatos.datos);
			} else {
				fnAlert(loDatos.error)
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consulta de médicos.')
		} finally {
			if (tbActivarSelect) { $("#selMedico").attr('disabled',false); }
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar médicos.');
		if (tbActivarSelect) { $("#selMedico").attr('disabled',false); }
	});
}
function llenarListaMedicos(toDatos){
	loSelect = $("#selMedico");
	loSelect.html('').append('<option value="">TODOS</option>');
	$.each(toDatos, function(lcCodigo, loMedico) {
		var loOption = $('<option></option>').val(loMedico.REGISTRO).text(loMedico.MEDICO);
		if (loMedico.REGISTRO==goUser.regm) {
			if(lcTipo=='cex'){
				loOption.attr('selected',true);
			}
		}
		loSelect.append(loOption);
	});
}


function IniciarTabla() {
	goTabla.bootstrapTable({
		classes: 'table table-bordered table-hover table-sm table-responsive-sm', // table-striped
		theadClasses: 'thead-light',
		locale: 'es-ES',
		undefinedText: '',
		toolbar: '#toolBarLstIntrExam',
		height: '400',
		pagination: false,
		rowStyle: formatoColorFila,
		onClickRow: function(toFila, toElement, toCampo){  if(lcTipo=='cex'){ if(goMobile.mobile()) buscarConsultas(toFila);} },
		onDblClickRow: function(toFila, toElement, toCampo){  if(lcTipo=='cex'){  if(!goMobile.mobile()) buscarConsultas(toFila);} },
		columns: [
			{
				title: 'NC',
				align: 'center',
				events: eventoNuevaConsulta,
				formatter: formatoNuevaConsulta
			},
			{
				title: 'Ingreso',
				field: 'NINORD',
				sortable: true,
				formatter: function(tnValor, toFila) { return '<b>'+tnValor+'</b>'; }
			},
			{
				title: 'Documento',
				field: 'NIDORD',
				sortable: true,
				formatter: formatoDocumento,
				width: 7, widthUnit: "rem"
			},
			{
				title: 'Nombre',
				field: 'NM1PAC',
				sortable: true,
				formatter: formatoNombres
			},
			{
				title: 'Apellidos',
				field: 'AP1PAC',
				sortable: true,
				formatter: formatoApellidos
			},
			{
				title: 'Fecha Ord',
				field: 'FRLORD',
				sortable: true,
				formatter: function(tnValor, toFila) { return strNumAFecha(tnValor,'/'); }
			},
			{
				title: 'Hora Ord',
				field: 'HOCORD',
				sortable: true,
				formatter: function(tnValor, toFila) { return strNumAHora(tnValor); }
			},
			{
				title: 'Fecha Rea',
				field: 'FERORD',
				formatter: function(tnValor, toFila) { return strNumAFecha(tnValor,'/'); }
			},
			{
				title: 'Hora Rea',
				field: 'HRLORD',
				formatter: function(tnValor, toFila) { return strNumAHora(tnValor); }
			},
			{
				title: 'Tipo Atención',
				field: 'CODCUP',
				formatter: formatoCUP
			},
			{
				title: 'Vía Ingreso',
				field: 'DESVIA'
			},
			{
				title: 'Cama',
				field: 'HABITA'
			},
			{
				title: 'Especialidad',
				field: 'DESESP'
			},
			{
				title: 'Cita',
				field: 'CCIORD'
			}
		],
	});
}


function IniciarTablaHC() {
	goTblHC.bootstrapTable({
		classes: 'table table-bordered table-hover table-sm table-responsive-sm', // table-striped
		theadClasses: 'thead-light',
		locale: 'es-ES',
		undefinedText: '',
		toolbar: '#toolBarLstIntrExam',
		height: '400',
		pagination: false,
		columns: [
			{
				title: 'Procedimiento',
				field: 'DESCUP'
			},
			{
				title: 'Especialidad',
				field: 'DESESP'
			},
			{
				title: 'Ingreso',
				field: 'NINCHC'
			},
			{
				title: 'Cons',
				field: 'CCOCHC'
			},
			{
				title: 'Vía Ingreso',
				field: 'DESVIA'
			},
			{
				title: 'Fecha',
				field: 'FCOCHC',
				formatter: function(tnValor, toFila) { return strNumAFecha(tnValor,'/'); }
			},
			{
				title: 'Médico',
				field: 'NOMMED',
				width: 10, widthUnit: "rem"
			},
			{
				title: 'PDF',
				align: 'center',
				events: eventoVerConsulta,
				formatter: '<a class="verConsulta" href="javascript:void(0)" title="Ver Consulta"><i class="fas fa-file-pdf" style="color: #f11;"></i></a>'
			}
		],
	});
}


function formatoColorFila(toFila, tnIndice) {
	//return goEstados[toFila.ESTORD]? {css: {'background-color':'#'+goEstados[toFila.ESTORD].COLOR}}: {};
	return toFila.COLORFOX>0 ? {css: {'background-color':'#'+colorFoxToRGB(toFila.COLORFOX)}}: {};
}

function formatoDocumento(tnValor, toFila) {
	return '<b>'+toFila.TIDORD+' '+toFila.NIDORD+'</b>';
}

function formatoApellidos(tnValor, toFila) {
	return '<b>'+toFila.AP1PAC+' '+toFila.AP2PAC+'</b>';
}

function formatoNombres(tnValor, toFila) {
	return '<b>'+toFila.NM1PAC+' '+toFila.NM2PAC+'</b>';
}

function formatoCUP(tnValor, toFila) {
	return toFila.DESCUP+' '+toFila.CODCUP;
}

function formatoNuevaConsulta(tnValor, toFila) {
	var lcIcon, lcMensaje;
	if (toFila.CONCIT=='S') {
		lcIcon = 	'fa-camera';
		lcMensaje = 'Nueva TeleConsulta';
	} else {
		lcIcon = 	'fa-file';
		lcMensaje = 'Nueva Consulta';
		if ( toFila.COAORD.indexOf('890',0)==0 ){ // interconsultas
			if (toFila.ESTORD=='13') {
				lcIcon = 	'fa-file';
				lcMensaje = 'Nueva consulta';
			}
			if (toFila.ESTORD=='3') {
				lcIcon = 	'fa-file-pdf';
				lcMensaje = 'Ver resultado';
			}
			if ( (toFila.ESTORD!='81') && (toFila.ESTORD!='3') && (toFila.ESTORD!='8') && (toFila.ESTORD!='13') ){  // estados indeterminados
				lcIcon = 	'';
				lcMensaje = '';
			}
		}
	}
	return (gaEstadosNoPermiteHC.includes(toFila.ESTORD))? '': '<a class="nuevaConsulta" href="javascript:void(0)" title="'+lcMensaje+'"><i class="fas '+lcIcon+'" style="color: red;"></i></a>';
}

var eventoVerConsulta = {
	'click .verConsulta': function(e, tcValor, toFila, tnIndice) {
		var loEnvia = {
			nIngreso	: toFila.NINCHC,
			cTipoDocum	: "2200",
			cTipoProgr	: "HCPPAL",
			nConsecCita	: toFila.CCIORD,
			nConsecCons	: toFila.CCOCHC,
			nConsecEvol	: toFila.CORORA,
			cCUP		: toFila.CCUCHC,
			cCodVia		: toFila.CODVIA,
			cSecHab		: toFila.SCAORD+'-'+toFila.NCAORD
		}
		formPostTemp('nucleo/vista/documentos/vistaprevia.php', {'datos':JSON.stringify([loEnvia])}, true);
	}
}

var eventoNuevaConsulta = {
	'click .nuevaConsulta': function(e, tcValor, toFila, tnIndice) {
		goFilaSel = toFila;
		nuevaConsulta();
	}
}

function nuevaConsulta() {
	if (lcTipo=='cex'){
		nuevaConsultaConsultaExterna();
	}
	if (lcTipo=='int'){
		if (goFilaSel.ESTORD!=3){
			var loEnvio = {
				NINORD: 	goFilaSel.NINORD,
				NIDORD: 	goFilaSel.NIDORD,
				TIDORD: 	goFilaSel.TIDORD,
				CCIORD: 	goFilaSel.CCIORD,
				CCOORD: 	goFilaSel.CCOORD,
				EVOORD:		goFilaSel.EVOORD,
				CODCUP:		goFilaSel.CODCUP,
				CODVIA: 	goFilaSel.CODVIA,
				CODORD: 	goFilaSel.CODORD,
				RMeOrd: 	goFilaSel.RMEORD,
				RMROrd: 	goFilaSel.RMRORD,
				DESESP: 	goFilaSel.DESESP,
				NOMMED: 	goFilaSel.NOMMED,
				NNOMED: 	goFilaSel.NNOMED,
				SECHAB: 	goFilaSel.SECHAB,
				ESTORD: 	goFilaSel.ESTORD,
				lcTipo:		'int',
				cp: 		'rint'
			};
			formPostTemp('modulo-historiaclinica', loEnvio, false);
		}else{
			var laInterAtendida =	 {
				'nIngreso' 		: goFilaSel.NINORD,
				'nConsecCita' 	: goFilaSel.CCIORD,
				'cCUP' 			: goFilaSel.CODCUP,
				'cCodVia' 		: goFilaSel.CODVIA,
				'cRegMedico' 	: goFilaSel.RMRORD,
				'cSecHab' 		: goFilaSel.SECHAB+'-'+goFilaSel.NUMHAB,
				'cTipDocPac' 	: goFilaSel.TIDORD,
				'nNumDocPac' 	: goFilaSel.NIDORD,
				"NIDORD": 	goFilaSel.NIDORD,
				};
				verPDFInterconsultaAtendida(laInterAtendida);
		}
	}
	if(lcTipo=='proest'){
		var resolver = {
			DESESP:		goFilaSel.DESESP,
			NINORD: 	goFilaSel.NINORD,
			CODORD: 	goFilaSel.CODORD,
			MED: 		goFilaSel.NOMMED +" "+goFilaSel.NNOMED,
			lcTipo:		'proest',
			cp: 		'rprocont',
			CCIORD: 	goFilaSel.CCIORD,
			DESCUP: 	goFilaSel.DESCUP,
			FRLORD: 	goFilaSel.FRLORD,
			HOCORD:		goFilaSel.HOCORD
		};
		formPostTemp('modulo-historiaclinica', resolver, true);
	}
}

function nuevaConsultaConsultaExterna() {
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {
			accion:'validarNueva',
			ingreso: goFilaSel.NINORD,
			codesp: goFilaSel.CODORD,
			fecrea: goFilaSel.FERORD,
		},
		dataType: "json"
	})
	.done(function(loRet) {
		try {
			if (loRet.error == ''){
				var lcFechaNac = strNumAFecha(goFilaSel.FNAPAC),
					lcFechaIng = strNumAFecha(goFilaSel.FEIING),
					ldFechaNac = Date.parse(lcFechaNac),
					ldFechaIng = Date.parse(lcFechaIng),
					lnEdadA = moment(ldFechaIng).diff(moment(ldFechaNac),'years'),
					loFila = {
						INGRESO: goFilaSel.NINORD,
						FECHAING: lcFechaIng,
						PACIENTE: goFilaSel.NM1PAC+' '+goFilaSel.NM2PAC+' '+goFilaSel.AP1PAC+' '+goFilaSel.AP2PAC,
						TIPODOC: goFilaSel.TIDORD,
						NUMDOC: goFilaSel.NIDORD,
						FECHANAC: lcFechaNac,
						EDAD_A: lnEdadA,
						GENERO: goFilaSel.GENERO,
						TELEFONOS: goFilaSel.TP1PAL+' - '+goFilaSel.CP1PAL,
						MEDICOREALIZA: goFilaSel.NOMMED +' '+goFilaSel.NNOMED
					};
				validarPacienteHC(loFila, '',
					function(){
						var loEnvio = {
								ingreso: goFilaSel.NINORD,
								tipodoc: goFilaSel.TIDORD,
								numdoc: goFilaSel.NIDORD,
								cita: goFilaSel.CCIORD,
								cons: goFilaSel.CCOORD,
								evol: goFilaSel.EVOORD,
								cup: goFilaSel.CODCUP,
								via: (goFilaSel.CODVIA==''? '02': goFilaSel.CODVIA),
								codesp: goFilaSel.CODORD,
								fecrea: goFilaSel.FERORD,
								medrealiza: goFilaSel.NOMMED +' '+goFilaSel.NNOMED,
								form: 'cex'
							};
						formPostTemp('modulo-historiaclinica', loEnvio, false);
					}, false
				);

			} else {
				$("#modalConsultas").modal("hide");
				fnAlert(loRet.error);
			}
		} catch(err) {
			$("#modalConsultas").modal("hide");
			fnAlert('No se pudo consultar atenciones del paciente.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		$("#modalConsultas").modal("hide");
		fnAlert('Se presentó un error al consultar atenciones del paciente.');
	});
}

function verPDFInterconsultaAtendida(laInterAtendida){
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {
			accion:	'consultarInterconsultaAtendida',
			IngInt:	laInterAtendida.nIngreso,
			CorInt: laInterAtendida.nConsecCita
		},
		dataType: "json"
	})
	.done(function(loRet) {
		try {
		var lsFechaSol = loRet.datos.FECEVL.substring(0,4)+'-'+loRet.datos.FECEVL.substring(4,6)+'-'+loRet.datos.FECEVL.substring(6,8)+' '+loRet.datos.HOREVL.substring(0,2)+':'+loRet.datos.HOREVL.substring(2,4)+':'+loRet.datos.HOREVL.substring(4,6);
		var loInterAtendida =	 [{
				"cTipoDocum" 	: '1900', 					// const
				"cTipoProgr" 	: 'HIS001',
				"nConsecCons" 	: '0', 						// const
				"nConsecDoc" 	: '',  						// const
				"nIngreso" 		: laInterAtendida.nIngreso,
				"nConsecCita" 	: laInterAtendida.nConsecCita,
				"cCUP" 			: laInterAtendida.cCUP,
				"cCodVia" 		: laInterAtendida.cCodVia,
				"cRegMedico" 	: laInterAtendida.cRegMedico,
				"cSecHab" 		: laInterAtendida.cSecHab,
				"cTipDocPac" 	: laInterAtendida.cTipDocPac,
				"nNumDocPac" 	: laInterAtendida.nNumDocPac,
				"nConsecEvol" 	: loRet.datos.CONEVL,
				"tFechaHora" 	: lsFechaSol
				}];
				vistaPreviaPdf({'datos':JSON.stringify(loInterAtendida)}, null);
		} catch(err) {
			//
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar Interconsulta ya Atendida');
	});

}


