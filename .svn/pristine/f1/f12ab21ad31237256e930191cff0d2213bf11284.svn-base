var gcTipoMenu = '',
	gaOpcionesMenu = {},
	gcUrlAjaxMenuOpc = "vista-historiaclinica/ajax/menuOpciones";


/*
 * Inicia opciones de menú de acuerdo al tipo
 * @param string tcTipo: tipo de consulta
 * @param integer tnIngreso: número de ingreso del paciente
 */
function IniciarOpcionesMenuOpc(tcTipo){
	gcTipoMenu = tcTipo;
	$.post(
		gcUrlAjaxMenuOpc,
		{accion:'opcionesMenu', menu:gcTipoMenu},
		function(loDatos){
			try{
				if(loDatos.error.length==0){
					gaOpcionesMenu = loDatos.datos;
				}else{
					fnAlert(loDatos.error)
				}
			}catch(err){
				fnAlert('No se pudo realizar la consulta de opciones.')
			}
		},
		"json"
	)
	.fail(function(jqXHR, textStatus, errorThrown){
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error en la consulta de opciones de menú.');
	});
}

/*
 * Muestra menu de opciones para el ingreso
 * @param string tcTipo: tipo de consulta
 * @param integer tnIngreso: número de ingreso del paciente
 */
function mostrarMenuIngreso(tnIngreso) {
	switch (gcTipoMenu) {
		case 'hospitalizado':
			verificarEpicrisis(tnIngreso);
			break;
		case 'urgencias':
			verificarEpicrisis(tnIngreso);
			break;
	}
}

/*
 * Muestra menu de opciones para el ingreso
 * @param string tcTipo: tipo de consulta
 * @param integer tnIngreso: número de ingreso del paciente
 */
function verificarEpicrisis(tnIngreso){
	goFila.EPICRISIS = false;
	goFila.ESTADO = 0;
	goFila.CONCON = 0;
	goFila.CONDOC = 0;

	$.post(
		gcUrlAjaxMenuOpc,
		{
			accion:'Epicrisis',
			ingreso:tnIngreso
		},
		function(loDatos) {
			try {
				if (loDatos.error == ''){
					goFila.EPICRISIS = !loDatos.datos.Valido;
					goFila.ESTADO = loDatos.estado;
					goFila.CONCON = loDatos.datos.ConsConsulta;
					goFila.CONDOC = loDatos.datos.ConsDocum;
					if(loDatos.datos.Valido){
						verificarPacienteUr(goFila.INGRESO, goFila.CODVIA, goFila.SECCION);
					}else{
						fnAlert('Paciente con EPICRISIS, no podrá ingresar más información.', 'ESTADO EPICRISIS', false, false, false, function(){cargarOpciones(true,true)});
					}

				} else {
					fnAlert(loDatos.error);
				}
			} catch(err) {
				fnAlert('No se pudo verificar epicrisis.');
			}
		},
		'json'
	)
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al verificar epicrisis.');
	});
}

function verificarPacienteUr(tnIngreso, tcCodVia, tcSeccion){
	$.post(
		gcUrlAjaxMenuOpc,
		{
			accion:'pacienteUrgencias',
			ingreso: tnIngreso,
			via: tcCodVia,
			seccion: tcSeccion,
		},
		function(loDatos) {
			try {
				if (loDatos.error.length==0){
					cargarOpciones(loDatos.pacienteUrg, false);
				} else {
					fnAlert(loDatos.error);
				}
			} catch(err) {
				fnAlert('No se pudo verificar paciente urgencias.');
			}
		},
		"json"
	)
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al verificar paciente urgencias.');
	});
}


function cargarOpciones(tlPacienteUr, tlEpicrisis){
	var laOpciones=jQuery.extend(true, {}, gaOpcionesMenu);
	var laOptions=laOpciones.OPCIONES.options;
	if(goFila.ESTADO==0){
		laOptions.ResumenFinal='';
	}
	
	if(tlEpicrisis){
		laOptions.OrdenesMed='';
		laOptions.Eventualidad='';
		laOptions.EvolucionP='';
		laOptions.EvolucionU='';
		laOptions.EvolucionUr='';
		laOptions.Traslados='';
	}else{
		if(tlPacienteUr){
			laOptions.EvolucionP='';
			laOptions.EvolucionU='';
		}else{
			switch(goFila.CODVIA){
				case '01':
					laOptions.EvolucionP='';
					laOptions.EvolucionU='';
					break;
				case '05': case '06':
					laOptions.EvolucionUr='';
					if(goFila.SECCION.substr(0,1)!=='C'){
						laOptions.EvolucionU='';
					}
					break;
				default :
					laOptions.EvolucionUr='';
					laOptions.EvolucionU='';
					break;
			}
		}
	}

	// Crear Ventana
	var lnOpc=0, lnCol=1,
		lcContent=[
			'<div class="container-fluid">',
				'<div class="row">',
					'<div class="col">',
						'Ingreso: <b>'+goFila.INGRESO+'</b><br>',
						'Habitación: <b>'+goFila.SECCION+'-'+goFila.HABITACION+'</b>',
					'</div>',
					'<div class="col-auto">',
						'Documento: <b>'+goFila.TIPO_DOC+' '+goFila.NUM_DOC+'</b><br>',
						'Paciente: <b>'+goFila.PACIENTE+'</b>',
					'</div>',
				'</div>',
				'<div class="row">',
					'<div class="col-12 col-sm-6">',
						'<div class="card mt-2">',
							'<div class="card-body">',
		].join('');
	$.each(laOpciones, function(lcIndice, loOpciones){
		var lcSL='';
		lcContent+='<div class="alert alert-dark" role="alert" style="padding: .25rem .9rem; margin: .75rem 0 .5rem 0;"><b>'+loOpciones.title+'</b></div>';
		$.each(loOpciones.options, function(lcEvento, lcDescrip){
			if(lcDescrip.length>0){
				lcContent+=lcSL+'<a href="#" class="card-link menu-opcion" data-evento="'+lcEvento+'">'+lcDescrip+'</a>';
				lcSL='<br>';
			}
		});
		lnOpc++;
		if(lnCol==1 && lnOpc==1){
			lcContent+='</div></div></div><div class="col-12 col-sm-6"><div class="card mt-2"><div class="card-body">';
			lnOpc=0; lnCol++;
		}
	});
	lcContent+='</div></div></div></div></div>';
	goMenuModal=$.dialog({
		icon: 'fas fa-clinic-medical',
		title: 'Opciones '+gcTipoMenu,
		content: lcContent,
		type: 'dark',
		columnClass: 'large',
		animateFromElement: false,
		smoothContent: false,
		animationSpeed: 200,
		escapeKey: true,
		closeIcon: true
	});
}

function NuevaConsultaHC(){
	toFila = goFila;
	ExisteHC(function(){
		if (gcTipoMenu=='hospitalizado') {
			if (goExiste.Valido===true) {
				if ($.inArray(parseInt(toFila.CODVIA).toString(), goUser.vias)<0) {
					fnAlert('Vía no permitida para hacer Historia Clínica');
					return;
				} else {
					var laEdad = toFila.EDAD.split('-');
						loFila = {
							INGRESO: toFila.INGRESO,
							FECHAING: toFila.FECHA_ING,
							PACIENTE: toFila.PACIENTE,
							TIPODOC: toFila.TIPO_DOC,
							NUMDOC: toFila.NUM_DOC,
							FECHANAC: toFila.FECHA_NAC,
							EDAD_A: laEdad[0],
							GENERO: toFila.GENERO,
							TELEFONOS: toFila.TP1PAL+' - '+toFila.CP1PAL
						};
					validarPacienteHC(loFila, '',
						function(){
							var loEnvio = {
								ingreso: toFila.INGRESO,
								tipodoc: toFila.TIPO_DOC,
								numdoc: toFila.NUM_DOC,
								cita: 0,
								cons: 0,
								evol: 0,
								cup: '',
								via: toFila.CODVIA,
								form: 'hos'
							};
							formPostTemp('modulo-historiaclinica', loEnvio, false);
						}, false
					);
				}
			} else {
				var lbMostrarMensaje = true;
				if (typeof goExiste.Existe !== 'undefined'){
					ConsultarDocumento('2000', 'HCPPAL');
					lbMostrarMensaje = false;
				}
				if(lbMostrarMensaje){
					fnAlert(goExiste.Mensaje);
				}
			}
		} else if (gcTipoMenu=='urgencias') {
			if (goExiste.Valido===true) {
				abrirHCUrg(toFila);
			}else{
				if (goExiste.Mensaje.substr(0,30)=="Ya existe una Historia Clinica") {
					ConsultarDocumento('2000', 'HCPPAL');
				} else {
					fnAlert(goExiste.Mensaje);
				}
			}
		}
	});
}


function abrirHCUrg(toFila) {
	$.post(
		gcUrlAjaxMenuOpc,
		{accion:'validaTipoUsu'},
		function(loDatos) {
			if (loDatos.error.length==0){
				mostrarMensajeHCUrg(toFila);
			} else {
				fnAlert(loDatos.error);
			}
		},
		"json"
	)
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al validar tipo de usuario');
	});
}


function mostrarMensajeHCUrg(toFila) {
	$.post(
		gcUrlAjaxMenuOpc,
		{accion:'mensajesHC', ingreso:toFila.INGRESO},
		function(loDatos) {
			try {
				if (loDatos.error == ''){
					var lcAtendidoRgr = loDatos.mensajes.atendidoRgr,
						lcAlertaCvd19 = loDatos.mensajes.alertaCvd19;

					if (lcAlertaCvd19.length>0) {
						fnAlert(lcAlertaCvd19, false, false, false, false,
							function(){
								fnValidarPacienteHCUrg(toFila, lcAtendidoRgr);
							}
						);
					} else {
						fnValidarPacienteHCUrg(toFila, lcAtendidoRgr);
					}
				} else {
					fnAlert(loDatos.error);
				}
			} catch(err) {
				fnAlert('No se pudo obtener mensajes HC urgencias');
			}
		},
		"json"
	)
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al obtener mensajes HC urgencias');
	});
}

function fnValidarPacienteHCUrg(toFila, tcAtendidoRgr) {
	var loFila = {
		INGRESO: toFila.INGRESO,
		FECHAING: toFila.FECHA_ING,
		PACIENTE: toFila.PACIENTE,
		TIPODOC: toFila.TIPO_DOC,
		NUMDOC: toFila.NUM_DOC,
		FECHANAC: toFila.FECHA_NAC,
		EDAD_A: toFila.EDAD_A,
		GENERO: toFila.GENERO,
		TELEFONOS: toFila.TP1PAL+' - '+toFila.CP1PAL
	};
	validarPacienteHC(loFila, tcAtendidoRgr,
		function postMostrarMensajeHC() {
			if ($("#chkAtendidoClinica").length > 0) {
				if (!$("#chkAtendidoClinica").prop('checked')) {
					$("#chkAtendidoClinica").focus();
					fnAlert("Debe marcar 'Atendido en la clínica u otra institución'");
					return false;
				}
			}
			// Bloquear el ingreso
			$.post(
				"vista-hc-urgencias/ajax/ajax",
				{accion:'bloquearIngreso', ingreso: toFila.INGRESO},
				function(loDatos) {
					if (loDatos.error == ''){
						formPostTemp(
							'modulo-historiaclinica',
							{
								ingreso: toFila.INGRESO,
								tipodoc: toFila.DOCUME,
								numdoc: toFila.NIDORD,
								cita: toFila.CCIORD,
								cons: toFila.CCOORD,
								evol: toFila.EVOORD,
								cup: toFila.COAORD,
								via: toFila.CODVIA,
								form: 'urg'
							},
							false
						);
					} else {
						fnAlert(loDatos.error);
					}
				},
				"json"
			)
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.responseText);
				fnAlert('Se presentó un error al bloquear ingreso para atención.');
			});
		}, false
	);
}


// Valida si el paciente actual ya tiene registro de historia clínica
function ExisteHC(tfuncion){
	$.post(
		gcUrlAjaxMenuOpc,
		{accion:'existeHC', ingreso:goFila.INGRESO, via:goFila.CODVIA},
		function(loDatos) {
			if (loDatos.error == ''){
				goExiste = loDatos.datos;
				if (typeof tfuncion === 'function') {
					tfuncion();
				}
			} else {
				fnAlert(loDatos.error);
			}
		},
		"json"
	)
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar si existe HC');
	});
}

// Valida y lanza nuevo registro de Evolución
function RegistroEv(tcEvento){
	var llEnviar = false;
	ExisteHC(function(){
		if (goExiste.Valido===true) {
			fnAlert('No existe Historia Clínica de ingreso, Revise por favor.');
			return false;
		} else {
			var lcMensaje='', lcTitulo='';
			if(goFila.ESTADO == 1 || goFila.ESTADO == 2){
				llEnviar = false;
				lcMensaje = 'Ya existe evolución de SALIDA. ¿Desea Continuar?';
			}else{
				llEnviar = true;
			}
			switch(tcEvento){
				case 'EvolucionUr':
					lcTipoEvol = 'U';
					lcTitulo = 'Evolución Urgencias';
					break;
				case 'EvolucionP':
					lcTipoEvol = 'P';
					lcTitulo = 'Evolución de Piso';
					break;
				case 'EvolucionU':
					lcTipoEvol = goFila.SECCION=='CC' ? 'C' : 'V';
					lcTitulo = 'Evolución Unidades';
					break;
				case 'Eventualidad':
					llEnviar = false;
					lcTipoEvol = 'E';
					lcMensaje = 'Esta opción es para ingresar información que corresponda a una eventualidad del paciente, NO para realizar historias clínicas, responder interconsultas, notas, etc. contiene un límite máximo de 400 caracteres, debe utilizarse evolución para notas más completas, Desea continuar ? ';
					lcTitulo = 'Eventualidad';
					break;
					
				case 'Traslados':
					lcTipoEvol = 'K';
					lcTitulo = 'Traslados servicios';
					break;
					
			}
			if(llEnviar){
				ingresarEvolucion(lcTipoEvol);
			} else {
				fnConfirm(lcMensaje, lcTitulo, false, false, 'medium', function(){ingresarEvolucion(lcTipoEvol);});
			}
		}
	});
}

// Nuevo registro de Evolución
function ingresarEvolucion(tcTipoEvol){
	var loEnvio = {
		cp: 'evo',
		ingreso: goFila.INGRESO,
		tipoev: tcTipoEvol,
	};
	formPostTemp('modulo-historiaclinica', loEnvio, false);
}

// Nuevo registro de Órdenes Médicas
function RegistroOrdenesMed(){
	ExisteHC(function(){
		if (goExiste.Valido===true && $.inArray(goFila.CODVIA, ['01','02'])==-1) {
			fnAlert('No existe Historia Clínica de ingreso, Revise por favor.');
			return false;
		} else {
			var loEnvio = {
				cp:'orm',
				ingreso:goFila.INGRESO
			};
			formPostTemp('modulo-historiaclinica', loEnvio, false);
		}
	});
}

// Nuevo registro de traslados pacientes
function RegistroTrasladosPaciente(){
	ExisteHC(function(){
		if (goExiste.Valido===true && $.inArray(goFila.CODVIA, ['01','02'])==-1) {
			fnAlert('No existe Historia Clínica de ingreso, Revise por favor.');
			return false;
		} else {
			goMenuModal.close();
			oModalTrasladoPacientes.consultarRegistros(goFila.INGRESO, goFila.PACIENTE, goFila.SECCION+'-'+goFila.HABITACION);
		}
	});
}

// Consulta o abre registro de epicrisis
function ConsultarEpicrisis(){
	if(goFila.EPICRISIS){
		ConsultarDocumento('4100', 'EPI002');
	}else{
		if(goFila.ESTADO == 0){
			fnAlert('No existe evolución de salida. Revise por Favor.');
			return;
		}else{
			formPostTemp('modulo-historiaclinica', {cp:'epi', ingreso:goFila.INGRESO}, false);
		}
	}
}

// Consulta de evoluciones
function ConsultarDocumento(tcTipoDocum, tcTipoPrg){
	oModalVistaPrevia.mostrar({
		nIngreso	: goFila.INGRESO,
		cTipDocPac	: '',
		nNumDocPac	: 0,
		cRegMedico	: '',
		cTipoDocum	: tcTipoDocum,
		cTipoProgr	: tcTipoPrg,
		tFechaHora	: '',
		nConsecCita	: goFila.CONCON,
		nConsecCons	: 0,
		nConsecEvol	: 0,
		nConsecDoc	: goFila.CONDOC,
		cCUP		: '',
		cCodVia		: '',
		cSecHab		: '',
	});
}


function verificarIngresoUrg() {
	$.post(
		gcUrlAjaxMenuOpc,
		{accion:'verificarIngresoUrg', ingreso: goFila.INGRESO},
		function(loDatos) {
			try {
				if(loDatos.valido.Valido==true){
					ingresarEvolucion('U');
				}else{
					if(loDatos.valido.Existe==true){
						ingresarEvolucion('U');
					}else{
						fnAlert(loDatos.valido.Mensaje);
					}
				}
			} catch(err) {
				fnAlert('No se pudo verificar ingreso de urgencias')
			}
		},
		"json"
	)
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al verificar ingreso de urgencias');
	});
}

function abrirObservaciones(){
	goMenuModal.close();
	oModalObservaciones.mostrar(goFila.INGRESO, goFila.PACIENTE);
}

// Al hacer clic en una de las opciones de menú
$("body").on("click", ".menu-opcion", function(){
	var lcEvento=$(this).attr("data-evento");

	switch(lcEvento){

	// OPCIONES
		case 'Historia':
			NuevaConsultaHC();
			break;
		case 'EvolucionUr':
			if (gcTipoMenu=='hospitalizado') {
				RegistroEv(lcEvento);
			} else if (gcTipoMenu=='urgencias') {
				verificarIngresoUrg();
			}
			break;
		case 'EvolucionP':
		case 'EvolucionU':
		case 'Eventualidad':
		
			RegistroEv(lcEvento);
			break;
		case 'OrdenesMed':
			RegistroOrdenesMed();
			break;
		case 'ResumenFinal':
			fnConfirm('Este es el resumen de egreso de la institución. Esta seguro que el paciente ya tiene salida ?', 'RESUMEN FINAL', false, false, false,
				{
					text: 'No',
				},
				{
					text: 'Si',
					action: function(){
						ConsultarEpicrisis();
					}
				}
			)
			break;
		case 'OrdenesAmb':
			formPostTemp('modulo-ordenes-ambulatorias&q=ordenes_ambulatorias', {'t':goFila.TIPO_DOC, 'n':goFila.NUM_DOC}, false);
			break;
		case 'Observaciones':
			abrirObservaciones();
			break;
		
		case 'Traslados':
			RegistroTrasladosPaciente();
			break;

	// CONSULTAS
		case 'LibroHC':
			formPostTemp('modulo-documentos', {'ingreso':goFila.INGRESO}, true);
			break;
		case 'Evoluciones':
			formPostTemp('modulo-evoconsulta', {'ingreso':goFila.INGRESO}, true);
			break;
		case 'Proa':
			formPostTemp('modulo-infconsulta', {'ingreso':goFila.INGRESO}, true);
			break;
		case 'ConsGlucometria':
			oModalRespCup.mostrar('GLUCOMETRIAS', goFila.INGRESO, goFila.PACIENTE);
			break;
		case 'GasesArt48':
			oModalRespCup.mostrar('GASESART48', goFila.INGRESO, goFila.PACIENTE);
			break;
		case 'Laboratorios':
			lcRuta = "http://srvlablisweb/cgi/Usuario.cgi?AccionServidor=AccionOrdenesNShaio&Alias=HIS&Clave=HIS&NShaio=" + goFila.INGRESO;
			window.open(lcRuta, "_blank");
			break;
		case 'Agility':
			lcRuta ="http://xero.shaio.org/xero/?theme=epr&PatientId=" + goFila.TIPO_DOC + goFila.NUM_DOC + "&user=MEDICO&password=medico";
			window.open(lcRuta, "_blank");
			break;

		default:
			fnAlert('MODULO EN CONSTRUCCIÓN: '+lcEvento);
	}
})
