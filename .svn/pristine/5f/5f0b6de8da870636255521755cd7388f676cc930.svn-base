var gcUrlajax = "vista-consultas-as400/ajax/ajax",
	goTabla=$("#tblConsultas"),
	gaParam={},
	gaListas={}
	goDataParam='';

$(function(){
	$('#selConsulta').attr('disabled',true);
	iniciarListaConsultas();
	$('#btnConsulta').on('click', obtenerParametros);
	$('#btnLimpiar').on('click', limpiar).hide();
	$('#btnOtraVez').on('click', consultar).hide();
});


function iniciarListaConsultas(){
	var lcMensaje='obtener la lista de consultas';
	var loSelect=$('#selConsulta');
	loSelect.empty();

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'listaConsultas'},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error==''){
				gaListas=loDatos.lista;
				loSelect.append('<option value=""></option>');
				$.each(loDatos.lista, function(lcKey, loTipo) {
					loSelect.append('<option value="' + lcKey + '">' + loTipo.TITULO + '</option>');
				});
				$('#selConsulta').attr('disabled',false);
			} else {
				fnAlert(loDatos.error);
			}

		} catch(err) {
			fnAlert('No se pudo '+lcMensaje+'.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al '+lcMensaje+'.');
	});
}


function limpiar(){
	$("#btnLimpiar,#btnOtraVez,#divIconoEspera").hide();
	$('#selConsulta').val('').attr('disabled',false);
	$('#btnConsulta').attr('disabled',false).show();
	$('#btnExpoSrv').remove();
	limpiarConsulta();
	goDataParam='';
}

function limpiarConsulta(){
	$('#divFiltros').html('').removeClass("alert alert-info");
	oFrmInput.limpiarForm();
	goTabla.bootstrapTable('destroy');
	$("#btnExpoSrv").remove();
	gaParam={};
}

function obtenerParametros(){
	var lcCodigo=$('#selConsulta').val(),
		lcTitulo=$('#selConsulta option:selected').text();
	if(lcCodigo==''){
		$('#selConsulta').focus();
		fnAlert('Debe seleccionar una opción válida');
		return;
	}
	$("#divIconoEspera").show();
	var lcMensaje='obtener los parámetros de la consulta seleccionada';

	$('#selConsulta').attr('disabled',true);
	$('#btnConsulta').attr('disabled',true);

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'obtenerParam', codigo: lcCodigo, titulo: lcTitulo},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error==''){
				goDataParam=loDatos.param;
				consultar();
			} else {
				$("#divIconoEspera").hide();
				fnAlert(loDatos.error);
			}
		} catch(err) {
			$("#divIconoEspera").hide();
			fnAlert('No se pudo '+lcMensaje +'.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		$("#divIconoEspera").hide();
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al '+lcMensaje +'.');
	});
}

function consultar(){
	limpiarConsulta();
	if(goDataParam==''){
		ejecutarConsulta({});
		$('#btnConsulta').hide();
		$('#btnLimpiar,#btnOtraVez').show();
	} else {
		$("#divIconoEspera").hide();
		laControles = JSON.parse(goDataParam);
		oFrmInput.crearForm(
			laControles,
			function(){ // Aceptar
				ejecutarConsulta(oFrmInput.obtenerDatos());
				$('#btnConsulta').hide();
				$('#btnLimpiar,#btnOtraVez').show();
			},
			function(){ // Cancelar
				$('#selConsulta').val('').attr('disabled',false);
				$('#btnConsulta').attr('disabled',false).show();
				$('#btnLimpiar,#btnOtraVez').hide();
			}
		);
	}
}

function ejecutarConsulta(taParam){
	gaParam=taParam;
	$("#divIconoEspera").show();
	var lcMensaje="consultar los campos",
		laEnviar={
			accion:'campos',
			c:$('#selConsulta').val(),
		};
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: laEnviar,
		dataType: "json"
	})
	.done(function(loDatos){
		try {
			if (loDatos.error==''){
				var laCampos=JSON.parse(loDatos.campos),
					lbConsultar=true;
				if(typeof laCampos==='object'){
					if(laCampos.length>0){
						lbConsultar=false;
						MostrarFiltros();
						crearTabla(laCampos);
					}
				}
				// si no existe archivo con datos de las columnas hace consulta para obtenerlas (puede ser muy lento)
				if(lbConsultar){
					var lcMensaje="consultar los datos",
						laEnviar={
							accion:'query',
							c:$('#selConsulta').val(),
							v:JSON.stringify(taParam),
							offset:0,
							limit:1
						};
					$.ajax({
						type: "POST",
						url: gcUrlajax,
						data: laEnviar,
						dataType: "json"
					})
					.done(function(loDatos){
						try {
							if (loDatos.error==''){
								if(loDatos.rows.length){
									var laColumnas=[];
									$.each(loDatos.rows[0],function(lcClave,lcDato){
										if(lcClave!=="NUM_FILA_SQL"){
											laColumnas.push({
												field: lcClave,
												title: lcClave
											});
										}
									});
									MostrarFiltros();
									crearTabla(laColumnas);

								} else {
									fnAlert('La consulta no retornó datos');
									$("#divIconoEspera").hide();
								}
							} else {
								$("#divIconoEspera").hide();
								fnAlert(loDatos.error);
							}
						} catch(err) {
							console.log(err);
							$("#divIconoEspera").hide();
							fnAlert('No se pudo '+lcMensaje +'.');
						}
					})
					.fail(function(jqXHR, textStatus, errorThrown){
						$("#divIconoEspera").hide();
						console.log(jqXHR.responseText);
						fnAlert('Se presentó un error al '+lcMensaje +'.');
					});
				}
			} else {
				$("#divIconoEspera").hide();
				fnAlert(loDatos.error);
			}
		} catch(err) {
			console.log(err);
			$("#divIconoEspera").hide();
			fnAlert('No se pudo '+lcMensaje +'.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		$("#divIconoEspera").hide();
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al '+lcMensaje +'.');
	});
}


function MostrarFiltros() {
	var lcFiltroHtml='',
		lcSalto='';
	$.each(oFrmInput.oFiltro, function(lcClave,loDato){
		lcFiltroHtml+=lcSalto+'<b>'+loDato.titulo+'</b>: '+loDato.valor;
		lcSalto='<br>';
	});
	if(lcFiltroHtml.length>0){
		$("#divFiltros").addClass("alert alert-info").html(lcFiltroHtml);
	}
}


function crearTabla(taColumnas){
	var loConsulta = gaListas[$('#selConsulta').val()],
		lcSidePag = 'server',	// 'client' o 'server'
		lbSearch = false,		// true o false
		lbSortable = false;		// true o false
	if(loConsulta['TIPO']=='FUN' || loConsulta['CLIPAG']=='C'){
		lcSidePag = 'client';
		lbSearch = true;
		lbSortable = true;
	}
	goTabla.bootstrapTable({
		classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
		theadClasses: 'thead-dark',
		locale: 'es-ES',
		undefinedText: '',
		height: '500',
		search: lbSearch,
		sortable: lbSortable,
		pagination: true,
		pageSize: 50,
		pageList: '[20,50,100,250,500,1000,All]',
		toolbar: "#toolbarConsultas",
		toolbarAlign: "right",
		showPaginationSwitch: false,
		showFullscreen: true,
		showColumns: true,
		showExport: false,
		exportDataType: 'all',
		exportTypes: ['csv', 'xlsx'],
		iconSize: 'sm',
		sidePagination: lcSidePag,
		ajax: 'consultaDatosTabla',
		columns: taColumnas
	});
	$("#divIconoEspera").hide();
	$("#toolbarConsultas").append('<button class="btn btn-secondary btn-sm" type="button" name="btnExpoSrv" id="btnExpoSrv" aria-label="btnExpoSrv" title="Exportar a Excel"><i class="fa fa-download"></i></button>');
	$("#btnExpoSrv").on('click',exportarServer);
}


function consultaDatosTabla(toParams){
	var lcMensaje='consultar datos de la tabla',
		loData=Object.assign({accion:'query',c:$('#selConsulta').val(),v:JSON.stringify(gaParam)},toParams.data);
	$.ajax({ type:"POST", url:gcUrlajax, data:loData, dataType:"json" })
	.done(function(loDatos){
		try{
			if (loDatos.error==''){
				toParams.success(loDatos);
			}else{
				$("#divIconoEspera").hide();
				fnAlert(loDatos.error);
			}
		}catch(err){
			$("#divIconoEspera").hide();
			fnAlert('No se pudo '+lcMensaje +'.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown){
		$("#divIconoEspera").hide();
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al '+lcMensaje +'.');
	});
}


// Exportar desde Sevidor
function exportarServer() {
	$("#divIconoEspera").show();
	var lcMensaje="consultar los datos para exportar ",
		laEnviar={
			accion:'exportxlsx',
			c:$('#selConsulta').val(),
			v:JSON.stringify(gaParam)
		};
	formPostTemp(gcUrlajax, laEnviar, true, function(){$("#divIconoEspera").hide();});
}
