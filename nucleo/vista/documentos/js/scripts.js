var goDataTable = [],	// Datos para la creación de la tabla
	goItemTree = [],	// Base para generar el árbol
	goLibro = {},		// Propiedades
	goTabla = $("#tblLstDocumentos"),
	gcUrlAjax = 'vista-documentos/ajax/ajax',	// Ruta para los ajax
	gcRutaImg = 'publico-imagenes/librohc/16x16/jpg/',	// Ruta de los íconos del árbol
	goTiposDoc, goTree, goNodeHC, goNodeAll, gcNombrePac = '', gcFiltroTree='', gcFiltroDsc='',
	goTipoIdDoc=[], gcTipId='', gnNumId=0,	// Documento del paciente
	gnTimeAnimation = "fast", gcSinIngreso = 'Sin_Ingreso',
	goMobile = new MobileDetect(window.navigator.userAgent),
	gcUrlGPC = '',
	goUltimoIngreso = false;

String.prototype.between=function(a,b){
	return this>=a && this<=b;
}

$(function() {
	$.ui.fancytree.debugLevel = 0; // 0:quiet, 1:info, 2:debug
	iniciarListas();
	iniciarDatos();
	getTiposLibro();
	getItemsTree();
	iniciarTabla();
	$("#inpTxtIngreso").focus();

	if (gnIngresoSmartRoom>0) {
		gcHcwIngreso=gnIngresoSmartRoom;
		$("#btnLimpiar,#btnConsultaPDF").remove();
	}

	$("#btnBuscar").on("click", buscar);
	$("#btnLimpiar").on("click", limpiar);
	$("#btnViewAll").on("click", function() { goTree.activateKey("HC"); });
	$("#btnExpandAll").on("click", function() { goTree.expandAll(); });
	$("#btnCollapseAll").on("click", function() { goTree.expandAll(false); });
	$("#btnLibroPDF").on("click", fVistaLibro);
	$("#btnConsultaPDF").on("click", consultarPdfGenerados);
	$("#btnDatosPaciente").on("click", verDatosPac);
	$("#btnVerFiltros").on("click", mostrarFiltros);
	$("#btnAplicarFiltros").on("click", aplicarFiltros);
	$("#btnQuitarFiltros").on("click", quitarFiltros);

	$("#btnVerTreeView").on("click", function(){
		$('#sidebar').toggle();
		$("#btnVerTreeView").html('<i class="fa fa-'+($("#sidebar").is(":visible")?'list-alt':'eye')+'">');
		goTabla.bootstrapTable('resetView');
	}).html('<i class="fa fa-'+($("#sidebar").is(":visible")?'eye':'list-alt')+'">');

	if(gcHcwIngreso>0){
		$("#inpTxtIngreso").val(gcHcwIngreso);
		buscar();
	}

	$('#divFiltros .input-group.date').datepicker({
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
});


/*
 *	Obtner datos de inicio
 */
function iniciarDatos() {
	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'inicio'},
		dataType: "json"
	})
	.done(function(loData){
		try {
			if (loData.error == '') {
				goLibro = loData.datos;
				$("#btnLibroPDF,#chkConAdjuntos").attr("disabled",goLibro.PuedeExportarPdf==0);
				if(goLibro.PuedeExportarPdf==0) $("#btnLibroPDF,#chkConAdjuntos,#lblConAdjuntos").hide();
				gcUrlGPC = goLibro.UrlDocumentosGPC;
				activarBotonGPC();

				// Activa la búsqueda para el filtro 'Profesional'
				$("#filtroMedico").listaMedicos({
					tipos: goLibro.FiltroProfesional[0],
					activos: goLibro.FiltroProfesional[1],
					mostrarRM: goLibro.FiltroProfesional[2]=='1',
				});

			} else {
				infoAlert($('#divIngresoInfo'), loData.error, 'warning', 'exclamation-triangle', false);
			}
		} catch(err) {
			infoAlert($('#divIngresoInfo'), 'No se pudo realizar la busqueda de items del tree. ', 'danger', 'exclamation-triangle', false);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		console.log(jqXHR.responseText);
		infoAlert($('#divIngresoInfo'), 'Se presentó un error al buscar items del tree. ', 'danger', 'exclamation-triangle', false);
	});
}


/*
 *	Iniciar listas
 */
function iniciarListas() {
	$('#selTipDoc').tiposDocumentos({
		horti: "1",
		fnpos: function(){
			if(gcHcwTipDoc!=='' && gcHcwNumDoc>0){
				$("#selTipDoc").val(gcHcwTipDoc);
				$("#inpNumDoc").val(gcHcwNumDoc);
				buscar();
			}
			$.each($("#selTipDoc option"), function(lcKey, loTipoDoc){
				var laData=$(loTipoDoc).text().split('-');
				if (laData[0].length>0) {
					goTipoIdDoc[laData[0].trim()]=$(loTipoDoc).val();
				}
			});
		}
	});
	iniciarFiltroVia();
}


/*
 *	Iniciar el filtro por vía de ingreso
 */
function iniciarFiltroVia() {
	$("#filtroVia").html("").DropDownTree({
		title: 'Vías',
		data: [
			{	title:'Urgencias',
				dataAttrs:[
					{title:'titulo',data:'Urgencias'},
					{title:'filtro',data:'01'},
				]},
			{	title:'Hospitalizado',
				dataAttrs:[
					{title:'titulo',data:'Hospitalizado'},
					{title:'filtro',data:'05'},
				]},
			{	title:'Ambulatorio',
				dataAttrs:[
					{title:'titulo',data:'Ambulatorio'},
					{title:'filtro',data:'02,06'},
				]},
		],
		multiSelect: true,
		selectChildren: false,
		checkHandler: function(loElement,loChecked){
			var laSelected=$("#filtroVia").GetSelected(),
				lnCount=laSelected.length,
				lcTitle='',lcSep='';
			$.each(laSelected, function(lnKey,loItem){
				lcTitle+=lcSep+loItem.attr('data-titulo');
				lcSep=', ';
			});
			$("#filtroVia").SetTitle(lcTitle);
		}
	});
}


/*
 *	Obtener Items para el Tree
 */
function getItemsTree() {
	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'tree'},
		dataType: "json"
	})
	.done(function(loItems){
		try {
			if (loItems.error=='') {
				goItemTree = loItems.tree;
				iniciarFiltroTipo();
			} else {
				infoAlert($('#divIngresoInfo'), loItems.error, 'warning', 'exclamation-triangle', false);
			}
		} catch(err) {
			infoAlert($('#divIngresoInfo'), 'No se pudo realizar la busqueda de items del tree. ', 'danger', 'exclamation-triangle', false);
			console.log(err);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		console.log(jqXHR.responseText);
		infoAlert($('#divIngresoInfo'), 'Se presentó un error al buscar items del tree. ', 'danger', 'exclamation-triangle', false);
	});
}


/*
 *	Inicializa el filtro por tipos
 */
function iniciarFiltroTipo() {
	$("#filtroTipoDoc").html("").DropDownTree({
		title: 'Tipos',
		data: generarDataFiltroTipo(goItemTree, ''),
		multiSelect: true,
		selectChildren: false,
		checkHandler: function(loElement,loChecked){
			var laSelected=$("#filtroTipoDoc").GetSelected(),
				lnCount=laSelected.length,
				lcTitle='',lcSep='';
			$.each(laSelected, function(lnKey,loItem){
				lcTitle+=lcSep+loItem.attr('data-descrp');
				lcSep=', ';
			});
			if(lnCount>4 || lcTitle.length>35){
				lcTitle=lnCount+' Tipos Seleccionados';
			}
			$("#filtroTipoDoc").SetTitle(lcTitle);
		}
	});
}


/*
 *	Retorna los hijos de un código para el arbol
 */
function generarDataFiltroTipo(toItems, tcCodigo) {
	var laDatos=[];
	$.each(toItems, function(lnLlave, loItem){
		var loHijos=[];
		if(loItem.PADRE==tcCodigo){
			var loElemento={
					title:'<img src="publico-imagenes/librohc/16x16/jpg/'+loItem.ICON.split('~')[1]+'"> '+loItem.DESCRIP,
					//href:'#'+lnLlave,
					dataAttrs:[
						{title:'descrp',data:loItem.DESCRIP},
						{title:'codigo',data:loItem.CODIGO},
						{title:'filtro',data:loItem.FILTRO.replace("'","","g")},
					]
				};
			if((loHijos=generarDataFiltroTipo(toItems, loItem.CODIGO)).length>0){
				loElemento.data=loHijos;
			}
			laDatos.push(loElemento);
		}
	});
	return laDatos;
}


/*
 *	Obtener filtros que se deben aplicar
 */
function obtenerFiltrosAdd() {
	var loFiltros={tipo:'',tipoDsc:'',fechaIni:'',fechaFin:'',medico:'',medicoDsc:'',via:'',viaDsc:'',descrip:''};
	var lcSep='';
	// tipo
	var laSelected=$("#filtroTipoDoc").GetSelected();
	$.each(laSelected, function(lnKey,loItem){
		loFiltros.tipo+=lcSep+loItem.attr('data-filtro');
		loFiltros.tipoDsc+=lcSep+loItem.attr('data-descrp');
		lcSep=', ';
	});
	// fechas
	if($("#filtroFechaTodas").prop('checked')==false){
		loFiltros.fechaIni=$("#filtroFechaIni").val();
		loFiltros.fechaFin=$("#filtroFechaFin").val();
	}
	// filtroProfesional
	loFiltros.medico=$("#filtroMedico").attr('data-reg');
	loFiltros.medicoDsc=$("#filtroMedico").val();
	// vía
	var laSelected=$("#filtroVia").GetSelected();
	lcSep='';
	$.each(laSelected, function(lnKey,loItem){
		loFiltros.via+=lcSep+loItem.attr('data-filtro');
		loFiltros.viaDsc+=lcSep+loItem.attr('data-titulo');
		lcSep=', ';
	});
	// descripción
	loFiltros.descrip=$("#filtroDescrip").val();

	// descripción de los filtros aplicados
	var loNodo = goTree.getActiveNode(),
		lcNodoFiltro = (loNodo==null)? '': loNodo.data.filtro.split("-"),
		lcTipo = lcNodoFiltro.length>1 ? loNodo.title+(loFiltros.tipoDsc.length>1 ? ', ' : '') : '';
	gcFiltroDsc = [
		((lcTipo.length>1 || loFiltros.tipoDsc.length>1) ? 'DOCUMENTO(S): '+lcTipo+loFiltros.tipoDsc+' | ' : ''),
		(loFiltros.fechaIni.length>1 ? 'FECHA: entre '+loFiltros.fechaIni+' y '+loFiltros.fechaFin+' | ' : ''),
		(loFiltros.medicoDsc.length>1 ? 'REALIZADOS POR: '+loFiltros.medicoDsc+' | ' : ''),
		(loFiltros.viaDsc.length>1 ? 'VÍA: '+loFiltros.viaDsc+' | ' : ''),
		(loFiltros.descrip.length>1 ? 'DESCRIPCIÓN CONTIENE: '+loFiltros.descrip+' | ' : ''),
	].join('');
	gcFiltroDsc = gcFiltroDsc.substr(0,gcFiltroDsc.length-3);

	return loFiltros;
}


/*
 *	Limpiar los filtros
 */
function limpiarFiltros() {
	var lcFecha=strDateAFecha(new Date(),"-");
	iniciarFiltroTipo(); iniciarFiltroTipo(); // necesario o deja de funcionar el filtro
	iniciarFiltroVia(); iniciarFiltroVia();
	$("#filtroFechaTodas").prop('checked',true);
	$("#filtroFechaIni").val(lcFecha);
	$("#filtroFechaFin").val(lcFecha);
	$("#filtroMedico").attr('data-reg','').autoComplete('clear');
	$("#filtroDescrip").val('');
	goUltimoIngreso = false;
}


/*
 *	Obtener Tipos de documentos
 */
function getTiposLibro() {
	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'tiposdoc'},
		dataType: "json"
	})
	.done(function(loTipos) {
		try {
			if (loTipos.error == '') {
				goTiposDoc = loTipos.tipos;
			} else {
				infoAlert($('#divIngresoInfo'), loTipos.error, 'warning', 'exclamation-triangle', false);
			}
		} catch(err) {
			infoAlert($('#divIngresoInfo'), 'No se pudo realizar la busqueda de tipos de documentos.', 'danger', 'exclamation-triangle', false);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		infoAlert($('#divIngresoInfo'), 'Se presentó un error al buscar tipos de documentos. ', 'danger', 'exclamation-triangle', false);
	});
}


/*
 *	Aplica los filtros personalizados
 */
function aplicarFiltros() {
	poblarTabla(gcFiltroTree);
}


/*
 *	Limpia los filtros personalizados
 */
function quitarFiltros() {
	limpiarFiltros();
	poblarTabla(gcFiltroTree);
}


/*
 *	Inicio de Buscar
 */
function buscar() {
	limpiarFiltros();
	var lnIngreso = $('#inpTxtIngreso').val(),
		lcTipId = $('#selTipDoc').val(),
		lnNumId = $('#inpNumDoc').val();
	if (lcTipId.length !== 0 && lnNumId.length !== 0) {
		if(lnNumId.length>13) {
			fnAlert("Número de documento no válido");
		} else {
			buscarPaciente(lcTipId, lnNumId);
		}
	} else if (lnIngreso.length !== 0) {
		if(lnIngreso.length>8) {
			fnAlert("Ingreso no válido");
		} else {
			buscarIngreso(lnIngreso);
		}
	}
}


/*
 *	Buscar los datos del ingreso
 */
function buscarIngreso(tnIngreso) {
	$('#filtroIngreso').hide(gnTimeAnimation);
	$('#divIconoEspera,#divLstDocumentos').show(gnTimeAnimation);

	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'ingreso',ingreso:tnIngreso},
		dataType: "json"
	})
	.done(function(loIngreso) {
		try {
			if (loIngreso.error == ''){
				if (loIngreso.nIngreso > 0){
					infoAlertClear($('#divIngresoInfo'));
					gcTipId=loIngreso.cTipId;
					gnNumId=loIngreso.nNumId;
					gcNombrePac=loIngreso.cNombre;
					$('#infoPaciente').html(
							'Ingreso No. <span class="badge badge-success">'+loIngreso.nIngreso+'</span> - ' +
							'Documento <span class="badge badge-success">'+loIngreso.cDocumento+'</span> - ' +
							'Paciente <span class="badge badge-success">'+loIngreso.cNombre+'</span>'
						);
					$('#divLstDocumentos').show(gnTimeAnimation);
					buscarDocumentos(tnIngreso);

				} else {
					infoAlert($('#divIngresoInfo'), 'No se encontró el número de ingreso ' + tnIngreso, 'warning', 'exclamation-triangle', false);
					$('#divLstDocumentos,#divIconoEspera').hide(gnTimeAnimation);
				}
			} else {
				infoAlert($('#divIngresoInfo'), loIngreso.error, 'warning', 'exclamation-triangle', false);
				$('#divLstDocumentos,#divIconoEspera').hide(gnTimeAnimation);
			}

		} catch(err) {
			infoAlert($('#divIngresoInfo'), 'No se pudo realizar la busqueda ' + tnIngreso, 'danger', 'exclamation-triangle', false);
			$('#divLstDocumentos,#divIconoEspera').hide(gnTimeAnimation);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		infoAlert($('#divIngresoInfo'), 'Se presentó un error al buscar el ingreso', 'danger', 'exclamation-triangle', false);
		$('#divLstDocumentos,#divIconoEspera').hide(gnTimeAnimation);
	});
}


/*
 *	Buscar los datos del paciente por documento
 */
function buscarPaciente(tcTipId, tnNumId) {
	tcTipId = typeof tcTipId !== 'undefined' ? tcTipId : '';
	tnNumId = typeof tnNumId !== 'undefined' ? tnNumId : 0;

	$('#filtroIngreso').hide(gnTimeAnimation);
	$('#divIconoEspera,#divLstDocumentos').show(gnTimeAnimation);

	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'paciente', tipId:tcTipId, numId:tnNumId},
		dataType: "json"
	})
	.done(function(loPaciente) {
		try {
			if (loPaciente.error == ''){
				if (loPaciente.cNombre !== ''){
					infoAlertClear($('#divIngresoInfo'));
					gcTipId=loPaciente.cTipId;
					gnNumId=loPaciente.nNumId;
					gcNombrePac=loPaciente.cNombre;
					$('#infoPaciente').html(
							'Documento <span class="badge badge-success">'+loPaciente.cDocumento+'</span> - ' +
							'Paciente <span class="badge badge-success">'+loPaciente.cNombre+'</span>'
						);
					$('#divLstDocumentos').show(gnTimeAnimation);

					buscarDocumentos(0, tcTipId, tnNumId);
				} else {
					infoAlert($('#divIngresoInfo'), 'No se encontró el paciente', 'warning', 'exclamation-triangle', false);
					$('#divLstDocumentos,#divIconoEspera').hide(gnTimeAnimation);
				}
			} else {
				infoAlert($('#divIngresoInfo'), loPaciente.error, 'warning', 'exclamation-triangle', false);
				$('#divLstDocumentos,#divIconoEspera').hide(gnTimeAnimation);
			}

		} catch(err) {
			console.log(err);
			infoAlert($('#divIngresoInfo'), 'No se pudo realizar la busqueda', 'danger', 'exclamation-triangle', false);
			$('#divLstDocumentos,#divIconoEspera').hide(gnTimeAnimation);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		infoAlert($('#divIngresoInfo'), 'Se presentó un error al buscar el ingreso', 'danger', 'exclamation-triangle', false);
		$('#divLstDocumentos,#divIconoEspera').hide(gnTimeAnimation);
	});
}


/*
 *	Buscar los documentos del paciente
 */
function buscarDocumentos(tnIngreso, tcTipId, tnNumId) {
	tnIngreso = typeof tnIngreso !== 'undefined' ? tnIngreso : 0;
	tcTipId = typeof tcTipId !== 'undefined' ? tcTipId : '';
	tnNumId = typeof tnNumId !== 'undefined' ? tnNumId : 0;

	var loItemIng,
		loData = {accion:'lista', ingreso:tnIngreso, tid:tcTipId, nid:tnNumId};
	if (tcTipId=='X') {
		$.each(goTree.getNodeByKey(tnIngreso).li.children[0].children, function(lnKey, loItem){
			if (loItem.className=="fancytree-icon") {
				loItemIng = loItem;
				loItemIng.src = gcRutaImg+'../../loading.gif';
				return;
			}
		});
		if (tnIngreso==gcSinIngreso) {
			loData.tid = goTipoIdDoc[gcTipId];
			loData.nid = gnNumId;
		}
	} else {
		$('#divIconoEspera').show(gnTimeAnimation);
		$('#wrpLstDocumentos').hide(gnTimeAnimation);
	}

	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: loData,
		dataType: "json"
	})
	.done(function(loDocumentos) {
		if (loDocumentos.error == ''){
			infoAlertClear($('#divIngresoInfo'));
			// Tree
			try {
				goUltimoIngreso = loDocumentos.ultimoIng;

				if (tcTipId=='X') {
					var lnIngreso = tnIngreso;
					var loNodeIng = goTree.getNodeByKey(lnIngreso);
					if (typeof loNodeIng === "object") {
						loNodeIng.data['filtro'] = lnIngreso;
						loNodeIng.extraClasses = '';
						goDataTable[lnIngreso] = loDocumentos.documentos[lnIngreso];

						adicionarItemsTreePorIngreso(lnIngreso, loNodeIng, loDocumentos.tree[lnIngreso]);
						loItemIng.src = gcRutaImg+'PatientMale.jpg';
						poblarTabla(tnNumId==0 ? lnIngreso : '');
					}

				} else {
					reiniciarTree();
					$.each(loDocumentos.tree, function(lnIngreso, loDatoIng) {
						var loNodeIng = goNodeHC.addChildren({
							key:lnIngreso,
							title:lnIngreso,
							filtro: tcTipId=='' ? (tnIngreso==lnIngreso ? lnIngreso : 'X') : lnIngreso,
							icon:gcRutaImg+'PatientMale.jpg',
							extraClasses: tcTipId=='' ? (tnIngreso==lnIngreso ? '' : 'noing') : '',
						});
						adicionarItemsTreePorIngreso(lnIngreso, loNodeIng, loDatoIng);
					});
					$("#divTree").fancytree("getTree").getNodeByKey("HC").setExpanded(true);
					if (tnIngreso>0) {
						goTree.activateKey(tnIngreso);
					} else {
						goTree.activateKey("HC");
					}

					// Data table
					try {
						goDataTable = loDocumentos.documentos;
						poblarTabla(tnIngreso>0 ? tnIngreso : '');
					} catch(toErr) {
						infoAlert($('#divIngresoInfo'), 'No se pudo realizar la busqueda de documentos del ingreso ' + tnIngreso, 'danger', 'exclamation-triangle', false);
						console.log(toErr);
					}
				}
			} catch(toErr) {
				infoAlert($('#divIngresoInfo'), 'No se puede mostrar el tree', 'warning', 'exclamation-triangle', false);
				console.log(toErr);
			}
		} else {
			infoAlert($('#divIngresoInfo'), loDocumentos.error, 'warning', 'exclamation-triangle', false);
		}
		$('#divIconoEspera').hide(gnTimeAnimation);
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		$('#divIconoEspera').hide(gnTimeAnimation);
		infoAlert($('#divIngresoInfo'), 'Se presentó un error al buscar documentos', 'danger', 'exclamation-triangle', false);
	});
}


/*
 *	adicionarItemsTreePorIngreso
 */
function adicionarItemsTreePorIngreso(tnIngreso, toNodeIng, toDatoIng) {
	if (tnIngreso==gcSinIngreso) return;
	$.each(goItemTree, function(lnGrupo, loDatGrupo) {
		$.each(toDatoIng, function(lcTipo, loDataTipo) {
			if (loDatGrupo.FILTRO.indexOf(lcTipo+'')>=0) {
				var laAdd = {
						key:tnIngreso+'-'+loDatGrupo.CODIGO,
						title:loDatGrupo.DESCRIP,
						filtro:tnIngreso+'-'+loDatGrupo.FILTRO,
						icon:gcRutaImg+loDatGrupo.ICON.split('~')[1]
					};
				if (loDatGrupo.PADRE=='') {
					toNodeIng.addChildren(laAdd);
					// Coloca ítem en la opción Todos los ingresos
					if (goTree.getNodeByKey('ALL-'+loDatGrupo.CODIGO)===null) {
						laAdd.key = 'ALL-'+loDatGrupo.CODIGO;
						laAdd.filtro = 'ALL-'+loDatGrupo.FILTRO;
						goNodeAll.addChildren(laAdd);
					}
				} else {
					goTree.getNodeByKey(tnIngreso+'-'+loDatGrupo.PADRE).addChildren(laAdd);
					// Coloca ítem en la opción Todos los ingresos
					if (goTree.getNodeByKey('ALL-'+loDatGrupo.CODIGO)===null) {
						laAdd.key = 'ALL-'+loDatGrupo.CODIGO;
						laAdd.filtro = 'ALL-'+loDatGrupo.FILTRO;
						goTree.getNodeByKey('ALL-'+loDatGrupo.PADRE).addChildren(laAdd);
					}
				}
				return false;
			}
		});
	});
	return
}


/*
 *	reiniciarTree
 */
function reiniciarTree() {
	$(":ui-fancytree").fancytree("destroy");

	$('#divTree').fancytree({
		extensions: ["glyph"],
		selectMode: 3,
		glyph: { preset:"awesome5", map:{} },
		source: [{
			key:'HC',
			title:'Historia Clínica',
			filtro:'',
			icon:gcRutaImg+'PatientFile.jpg'
		}],
		activate: function(evento, datos) {
			nodeClick(evento, datos);
		},
	});
	goTree = $("#divTree").fancytree("getTree");
	goNodeHC = goTree.getNodeByKey("HC");
	goNodeAll = goNodeHC.addChildren({
		key:'ALL',
		title:'Todos los ingresos',
		filtro:'ALL',
		icon:gcRutaImg+'PatientMale.jpg'
	});
}


/*
 *	Llena los datos en la tabla
 */
function nodeClick(evento, datos) {
	if (datos.node.key=='HC') {
		var lbTodos = true;
		$.each(goNodeHC.children, function(lcKey, loDataIng){
			if (loDataIng.data.filtro=='X') {
				buscarDocumentos(loDataIng.key, 'X', 1);
				lbTodos = false;
			}
		});
		if (lbTodos) poblarTabla();
	} else {
		var lcFiltro = datos.node.data.filtro;
		// ingresos no consultados
		if (lcFiltro=='X') {
			buscarDocumentos(datos.node.key, 'X');
		// ingresos ya consultados
		} else {
			poblarTabla(lcFiltro);
		}
	}
}


/*
 *	Llena los datos en la tabla
 */
function poblarTabla(tcFiltro) {
	tcFiltro = typeof tcFiltro !== 'undefined' ?  tcFiltro.trim() : '';
	gcFiltroTree = tcFiltro;
	var laFiltro = tcFiltro.split("-"),
		laFiltroAdd = obtenerFiltrosAdd(),
		lbHayFiltros = tcFiltro.length>0,
		lbHayFiltrosAdd = false,
		loDatos = [],
		loData = [];
	goTabla.bootstrapTable('removeAll');

	// Valida si se deben aplicar filtros
	$.each(laFiltroAdd, function(lcKey, lcFiltro){
		if(lcFiltro.length>0){
			lbHayFiltrosAdd=true;
			return;
		}
	});

	// filtra los documentos
	if (!lbHayFiltros && !lbHayFiltrosAdd) {
		loDatos = goDataTable;
	} else {
		$.each(goDataTable, function(lcIngreso, laArray){
			// Filtro por ingreso
			if (laFiltro['0'].length==0 || laFiltro['0']=='ALL' || laFiltro['0']==lcIngreso) {
				if (lcIngreso==gcSinIngreso) lcIngreso = 1;
				if (laFiltro.length>1 || lbHayFiltrosAdd) {
					if (laFiltro.length>1) {
						laFiltroAdd.tipo+=','+laFiltro['1'];
					}
					loDatos[lcIngreso.toString()] = [];
					// Filtro por tipo de procedimiento
					$.each(laArray, function(lcClave, laItem){
						var lbAdicionar=true;
						if (lbAdicionar && laFiltroAdd.tipo.length>0) {
							lbAdicionar=lbAdicionar && laFiltroAdd.tipo.indexOf(laItem.tipoDoc)>-1;
						}
						if (lbAdicionar && laFiltroAdd.medico.length>0) {
							lbAdicionar=lbAdicionar && laItem.medRegMd.length>0 && laFiltroAdd.medico.indexOf(laItem.medRegMd)>-1;
						}
						if (lbAdicionar && laFiltroAdd.via.length>0) {
							lbAdicionar=lbAdicionar && laFiltroAdd.via.indexOf(laItem.codvia)>-1;
						}
						if (lbAdicionar && laFiltroAdd.descrip.length>0) {
							lbAdicionar=lbAdicionar && laItem.descrip.toUpperCase().indexOf(laFiltroAdd.descrip.toUpperCase())>-1;
						}
						if (lbAdicionar && laFiltroAdd.fechaIni.length>0) {
							if (laItem.fecha.length>0) {
								lbAdicionar=lbAdicionar && laItem.fecha.substr(0,10).between(laFiltroAdd.fechaIni,laFiltroAdd.fechaFin)
							} else {
								lbAdicionar=false;
							}
						}
						if (lbAdicionar) {
							loDatos[lcIngreso.toString()][lcClave]=laItem;
						}
					});
				} else {
					if (lcIngreso==gcSinIngreso) lcIngreso = 1;
					loDatos[lcIngreso.toString()] = laArray;
				}
			}
		});
	}

	// Adiciona documentos a la tabla
	$.each(loDatos, function(lcKey, loIngreso) {
		var lnIngreso = lcKey=='1' ? gcSinIngreso : lcKey;
		var lnNumId = 0;
		$.each(loIngreso, function(lnKey, loDoc) {
			if (typeof loDoc==='object') {
				loData.push({
					ingreso:	lnIngreso,
					fecha:		loDoc.fecha,
					descrip:	loDoc.descrip,
					medRegMd:	loDoc.medRegMd,
					medico:		loDoc.medNombr+' '+loDoc.medApell,
					tipoPrg:	loDoc.tipoPrg,
					tipoDoc:	loDoc.tipoDoc,
					tipoDsc:	goTiposDoc[loDoc.tipoDoc].Descr,
					codCup:		loDoc.codCup,
					cnsCita:	loDoc.cnsCita,
					cnsCons:	loDoc.cnsCons,
					cnsEvo:		loDoc.cnsEvo,
					cnsDoc:		loDoc.cnsDoc,
					codvia:		loDoc.codvia,
					sechab:		loDoc.sechab,
				});
			}
		});
	});
	$('#wrpLstDocumentos').show(gnTimeAnimation, function() {
		goTabla.bootstrapTable('refreshOptions',{ data: loData });
	});
}



/*
 *	Verifica si el usuario tiene entidades asociadas => abre la consulta de PDF generados
 */
function consultarPdfGenerados(){
	$.ajax({
		type: "POST",
		url: gcUrlAjax,
		data: {accion:'ctaentidades'},
		dataType: "json"
	})
	.done(function(toData) {
		try {
			if (toData.error == ''){
				lnCuentaEntidades = toData.data;
				if (lnCuentaEntidades > 0) {
					window.location.href = 'modulo-documentos&q=consulta';
				} else {
					fnAlert('El usuario no tiene entidades asignadas para consultar', 'Alerta', 'exclamation-triangle', 'red', 'small');
				}
			} else {
				infoAlert($('#divIngresoInfo'), toData.error, 'warning', 'exclamation-triangle', false);
			}
		} catch(toErr) {
			infoAlert($('#divIngresoInfo'), 'No se pudo realizar la busqueda de tipos de documento. ', 'danger', 'exclamation-triangle', false);
			console.log(toErr);
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		infoAlert($('#divIngresoInfo'), 'Se presentó un error al buscar tipos de documento. ', 'danger', 'exclamation-triangle', false);
	});
}



/*
 *	Retorna el entorno al estado inicial
 */
function limpiar() {
	goDataTable=null;
	gcTipId='';
	gnNumId=0;
	goTabla.bootstrapTable('removeAll').bootstrapTable('refreshOptions', {sortName: 'fecha', sortOrder: 'desc'});
	$('#divLstDocumentos').hide(gnTimeAnimation);
	$('#wrpLstDocumentos').hide(gnTimeAnimation);
	$('#infoPaciente').html('');
	$('#divIconoEspera').hide(gnTimeAnimation);
	infoAlertClear($('#divIngresoInfo'));
	$('#filtroIngreso').show(gnTimeAnimation);
	$("#inpTxtIngreso").val("").focus();
	$("#selTipDoc").val("");
	$("#inpNumDoc").val("");
	$("#chkConAdjuntos").prop("checked",false);
	// limpiar el botón de búsqueda
	$("div.bootstrap-table button[name=clearSearch]").click();
	limpiarFiltros();
	if($("#divFiltros").is(":visible")){
		mostrarFiltros()
	}
}


/*
 *	Obtener vista previa de uno de los documentos
 */
function fConsultarDocumento(toData, tcTipo) {
	var laEnvio = organizaObj(toData);
	var lcDscDoc = toData.tipoDsc + ' ' + toData.fecha;
	infoAlertClear($('#divIngresoInfo'));
	if (tcTipo=='PDF') {
		vistaPreviaPdf({datos:JSON.stringify([laEnvio])}, null, lcDscDoc, 'LIBROHC');
	} else if (tcTipo=='HTML') {
		oModalVistaPrevia.mostrar(laEnvio, lcDscDoc, 'LIBROHC');
	}
}

/*
 *	Obtener vista previa del libro
 */
function fVistaLibro() {
	if(goLibro.PuedeExportarPdf==0){
		fnAlert('El usuario no tiene permisos para exportar en lote');
		return false;
	}
	var laDatos = goTabla.bootstrapTable('getData');
	// Validar laboratorios por cantidad
	if ($("#chkConAdjuntos").prop("checked")){
		var laTiposLab = goLibro.FiltroMaxLabPdf.split(','),
			laLabs = laDatos.filter(dato => $.inArray(dato.tipoDoc, laTiposLab)>-1),
			lnNumLab = laLabs.length;
		laLabs=false;
		if(lnNumLab>goLibro.AlertaMaxLabPdf){
			var lcMsg = "La exportación a PDF incluirá "+lnNumLab+" laboratorios.<br>"+
						"Esta operación tardará varios minutos en terminar.<br>"+
						"¿Desea continuar?"
			fnConfirm(lcMsg, 'Exportar PDF', false, false, false,
				function(){
					fVistaLibroFin(laDatos);
				},
				function(){
					fnAlert('Acción cancelada');
				}
			);
		} else {
			fVistaLibroFin(laDatos);
		}
	} else {
		fVistaLibroFin(laDatos);
	}
}
function fVistaLibroFin(taDatos) {
	var laEnvio = [];
	$.each(taDatos, function(lnIndex, laData){
		var lbAdd = true;
		if (!($("#chkConAdjuntos").prop("checked"))){
			if(laData.tipoPrg=="ADJUNTOS" || laData.tipoDoc=="1100"){
				lbAdd = false;
			}
		}
		if (lbAdd) {
			laEnvio.push(organizaObj(laData));
		}
	});

	// filtro aplicado
	var loNodo = goTree.getActiveNode(),
		laFiltro = (loNodo==null)? '': loNodo.data.filtro.split("-"),
		lnIngreso = 0, lcFiltro = '';
	if (typeof laFiltro == 'object') {
		if (laFiltro[0]!=='HC') {
			if (laFiltro[0]!=='ALL') {
				lnIngreso = laFiltro[0];
			}
		}
	}
	var loPortada = {
			cTipId:gcTipId,
			nNumId:gnNumId,
			cNomPac:gcNombrePac,
			nIngreso:lnIngreso,
			cFiltro:gcFiltroDsc
		},
		loEnviar = {
			datos:JSON.stringify(laEnvio),
			portada:JSON.stringify(loPortada)
		}

	fnRegMovAudDoc({
		nIngreso:lnIngreso,
		cTipDocPac:gcTipId,
		nNumDocPac:gnNumId,
	}, 'EXPOPDF', 'EXPORTAR LIBRO HC EN PDF', 'LIBROHC')

	//vistaPreviaPdf(loEnviar);
	formPostTemp('nucleo/vista/documentos/vistaprevia.php', loEnviar, !goMobile.mobile());
}


/*
 *	Organiza los datos para enviar
 */
function organizaObj(taData) {
	var lcCup = taData.tipoPrg=="ADJUNTOS" ? taData.descrip :						// adjuntos
				(taData.tipoDoc=='1100' ?  taData.codCup+' - '+taData.descrip :		// laboratorios
					taData.codCup);
	return {
		nIngreso	: taData.ingreso,
		cTipDocPac	: gcTipId,
		nNumDocPac	: gnNumId,
		cRegMedico	: taData.medRegMd,
		cTipoDocum	: taData.tipoDoc,
		cTipoProgr	: taData.tipoPrg,
		tFechaHora	: taData.fecha,
		nConsecCita	: taData.cnsCita,
		nConsecCons	: taData.cnsCons,
		nConsecEvol	: taData.cnsEvo,
		nConsecDoc	: taData.cnsDoc,
		cCUP		: lcCup,
		cCodVia		: taData.codvia,
		cSecHab		: taData.sechab,
	};
}


/*
 *	Iniciar tabla de documentos
 */
function iniciarTabla() {
	goTabla.bootstrapTable({
		classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
		theadClasses: 'thead-dark', // 'thead-dark' 'thead-light'
		locale: 'es-ES',
		undefinedText: 'N/A',
		toolbar: '#toolBarLst',
		height: '600',
		pagination: true,
		pageSize: 20,
		pageList: '[10, 20, 50, 100, 250, 500, All]',
		filterAlgorithm: 'and',
		sortable: true,
		showSearchButton: true,
		showSearchClearButton: true,
		trimOnSearch: true,
		iconSize: 'sm',
		sortName: 'fecha',
		sortOrder: 'desc',
		columns: [
			{
				title: 'Ingreso',
				field: 'ingreso',
				sortable: true
			},{
				title: 'Cita',
				field: 'cnsCita',
				sortable: true
			},{
				title: 'Fecha Hora',
				field: 'fecha',
				sortable: true
			},{
				title: 'Tipo',
				field: 'tipoDsc',
				sortable: true
			},{
				title: 'Descripción',
				field: 'descrip',
				sortable: true
			},{
				title: 'Médico/Enf',
				field: 'medico',
				sortable: true
			},{
				title: 'PDF - Vista',
				align: 'center',
				events: eventoVerDocumento,
				formatter: formatoVerDocumento
			}
		]
	});
}

function formatoVerDocumento(tnValor, toFila) {
	var lcBtn = '&nbsp; <a class="verDocPdf"  href="javascript:void(0)" title="Documento PDF"><i class="fas fa-file-pdf" style="color: #f11;"></i></a> &nbsp; &nbsp; ';
	// Evitan laboratorios y adjunto
	if (toFila.tipoDoc!=="1100" && toFila.tipoPrg!=="ADJUNTOS") {
		lcBtn += '<a class="verDocHtml" href="javascript:void(0)" title="Vista Previa" ><i class="fas fa-eye" style="color: #444;"></i></a> &nbsp;';
	}
	return lcBtn;
}

var eventoVerDocumento = {
	'click .verDocPdf': function(e, tcValor, toFila, tnIndice) {
		console.log(toFila);
		fConsultarDocumento(toFila, 'PDF');
	},
	'click .verDocHtml': function(e, tcValor, toFila, tnIndice) {
		fConsultarDocumento(toFila, 'HTML');
	}
}

function mostrarFiltros(){
	$("#divFiltros").toggle();
	$("#btnVerFiltros").text($("#divFiltros").is(":visible") ? 'Ocultar Filtros' : 'Ver Filtros');
}

function verDatosPac(){
	if(goUltimoIngreso!==false){
		oModalDatosPaciente.cTitulo='Consulta Último Ingreso';
		oModalDatosPaciente.consultaDatos(goUltimoIngreso);
	}else{
		fnAlert('No existe ingreso para consultar.');
	}
}

function activarBotonGPC(){
	if (gcUrlGPC.length>0) {
		$("#btnCalidadGPC").show().on("click", function(){
			let loWindow = window.open(atob(gcUrlGPC), "_blank");
			loWindow.focus();
		});
	}
}
