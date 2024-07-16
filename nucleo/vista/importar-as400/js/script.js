var gcUrlajax = "vista-importar-as400/ajax/ajax",
	goTabla=$("#tblConsultas"),
	gaParam={},
	gaFilas=[],
	gcVel="fast";

$(function () {
	$('#selConsulta').attr('disabled',true);
	iniciarListaConsultas();
	$('#selConsulta').on('change', changeSelConsulta);
	$('#fileImportar').on('change', changeFileImportar);
	$('#btnLimpiar').on('click', limpiar).hide(gcVel);
	$('#btnImportar').on('click', importar);
})


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
				loSelect.append('<option value=""></option>');
				$.each(loDatos.lista, function(lcKey, lcTipo) {
					loSelect.append('<option value="' + lcKey + '">' + lcTipo + '</option>');
				});
				$('#selConsulta').attr('disabled',false);
			} else {
				fnAlert('<b>Función</b>: iniciarListaConsultas<br><b>Error</b>: '+loDatos.error);
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


function changeSelConsulta(){
	gaParam = {};
	var lcCodigo = $('#selConsulta').val();
	if(lcCodigo==''){
		limpiar();
	}else{
		var lcMensaje='consultar parámetros para importar';
		$.ajax({
			type: "POST",
			url: gcUrlajax,
			data: {accion:'obtenerParam', codigo:lcCodigo},
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.error==""){
					gaParam = loDatos.param;
					$("#btnLimpiar,#divArchivo").show(gcVel);
					$("#fileImportar").attr("accept",gaParam.tipo);
				} else {
					fnAlert('<b>Función</b>: changeSelConsulta<br><b>Error</b>: '+loDatos.error);
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
}


function changeFileImportar(e){
	gaFilas = [];
	var loArchivo = e.target.files[0];
	if (!loArchivo) {
		return;
	}
	mostrarEspera("Espere mientras se valida la estructura");
	$('#lblFileImportar').text(loArchivo.name);

	var loLector = new FileReader();
	loLector.onload = function(e) {
		var lcContenido = e.target.result;
		var loLineas = lcContenido.split(/\n/);
		if (validarLineas(loLineas)){
			iniciarTabla();
			cargarLineas();
		}
	};
	loLector.readAsText(loArchivo);
}


function validarLineas(toLineas){
	// validar estructura de las líneas
	var lnNumCol = Object.keys(gaParam.campos).length,
		lnImpo = "1", lcSepC=" | ";
	gaFilas = [];
	$.each(toLineas, function(lnClaveFila, lcLinea){
		if(lcLinea.trim()!==''){
			var loCampos=lcLinea.split(gaParam.separa),
				loFila={},
				lcMsg="",
				lcSep="";
			if(!(lnNumCol==loCampos.length)){
				lcMsg = "Número de datos incorrecto";
				lnImpo = "0";
			}else{
				var lnNum=0;
				$.each(gaParam.campos, function(lnClaveCampo, loCampo){
					var lcValor=loCampos[lnNum].trim();
					loFila[lnClaveCampo]=lcValor;
					lnNum++;
					if(typeof loCampo.regexp=="string"){
						var loRe=new RegExp(loCampo.regexp);
						if(loRe.exec(lcValor)==null) {
							lcMsg+=lcSep+"Error campo "+lnClaveCampo;
							lcSep=lcSepC;
							lnImpo="0";
						}
					}
					if(!(typeof loCampo.largo=="undefined")){
						var lnLargo=parseInt(loCampo.largo);
						if(lcValor.length!=lnLargo){
							lcMsg+=lcSep+lnClaveCampo+" debe tener "+lnLargo+" caracteres";
							lcSep=lcSepC;
							lnImpo="0";
						}
					}
					if(!(typeof loCampo.min=="undefined")){
						var lnMin=parseInt(loCampo.min);
						var lnValor=parseFloat(lcValor);
						if(lnValor<lnMin){
							lcMsg+=lcSep+lnClaveCampo+" menor a "+lnMin;
							lcSep=lcSepC;
							lnImpo="0";
						}
					}
					if(!(typeof loCampo.max=="undefined")){
						var lnMax=parseInt(loCampo.max);
						var lnValor=parseFloat(lcValor);
						if(lnValor>lnMax){
							lcMsg+=lcSep+lnClaveCampo+" mayor a "+lnMax;
							lcSep=lcSepC;
							lnImpo="0";
						}
					}
				});
			}
			loFila['_est_val_impo']=(lcMsg==''?'-':lcMsg);
			gaFilas.push(loFila);
		}
	});
	$("#btnImportar").attr("data-Impo", lnImpo);
	return true
}


function cargarLineas(){
	goTabla.bootstrapTable('append', gaFilas);
	if ($("#btnImportar").attr("data-Impo")=="1") {
		$("#divImportar").show(gcVel);
	}
}


function iniciarTabla(){
	var laColumnas=[{field:'_est_val_impo',title:'Validación',sortable: true}];
	$.each(gaParam.campos,function(lcClave,lcDato){
		laColumnas.push({
			field: lcClave,
			title: lcClave
		});
	});
	goTabla.bootstrapTable({
		classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
		theadClasses: 'thead-dark',
		locale: 'es-ES',
		undefinedText: '',
		height: '300',
		search: false,
		pagination: true,
		pageSize: '500',
		pageList: '[20,50,100,250,500,1000,All]',
		toolbar: "#toolbarConsultas",
		iconSize: 'sm',
		columns: laColumnas,
		rowStyle: formatoColorFila,
		sortName: '_est_val_impo',
		sortOrder: 'desc'
	});
	ocultarEspera();
}


function formatoColorFila(toFila, tnIndice) {
	var loReturn={};
	if(toFila._est_val_impo.length>5){
		loReturn={css: {'background-color':'#faa'}};
	}
	return loReturn;
}


function importar(){
	if (!$("#btnImportar").attr("data-Impo")=="1") { return }
	$("#btnImportar,#fileImportar").attr("disabled",true);
	mostrarEspera("Espere mientras se valida y se hace la importación")
	var lcMensaje = "Importar Datos";
	var lcCodigo = $('#selConsulta').val();

	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'importar', codigo:lcCodigo, datos:JSON.stringify(goTabla.bootstrapTable('getData'))},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			var lbSinError=true;
			if(loDatos.aerror.length>0){
				$.each(loDatos.aerror,function(lnKey,lcError){
					if(lcError.length>0){
						goTabla.bootstrapTable('updateCell',{
							index:lnKey, field:'_est_val_impo', value:lcError
						});
						lbSinError=false;
					}
				});
			}
			if(loDatos.error.length>0){
				fnAlert(loDatos.error);
				lbSinError=false;
			}
			if(lbSinError){
				var lcMsg = $("#selConsulta option:selected").text() + '<br>' +
							(typeof loDatos.data == 'string' && loDatos.data.length>0) ? loDatos.data : "Importación exitosa";
				fnAlert(lcMsg, "Importar AS400", "fas fa-check-circle", "blue");
				limpiar();
			} else {
				fnAlert('Existen errores en el archivo, revise por favor.');
			}
		} catch(err) {
			fnAlert('No se pudo '+lcMensaje+'.');
		} finally {
			ocultarEspera();
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		ocultarEspera();
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al '+lcMensaje+'.');
	});
}


function limpiar(){
	gaParam={};
	ocultarEspera();
	$('#btnLimpiar,#divArchivo,#divImportar').hide(gcVel);
	$('#selConsulta,#fileImportar').val('');
	$("#btnImportar").attr("data-Impo","0");
	$('#lblFileImportar').text("Seleccionar Archivo");
	$('#btnImportar,#fileImportar').attr("disabled",false);
	goTabla.bootstrapTable('destroy');
}

function temporal(){
	var lcMensaje='temporal';
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {accion:'temporal'},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error==""){
				fnAlert("Importación exitosa", "Importar AS400", "fas fa-check-circle", "blue");
				limpiar();
			} else {
				fnAlert('<b>Función</b>: importar<br><b>Error</b>: '+loDatos.error);
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

function mostrarEspera(tcMensaje){
	$("#spanMensajeEspera").html(tcMensaje);
	$("#divIconoEspera").show(gcVel);
}

function ocultarEspera(){
	$("#divIconoEspera").hide(gcVel);
}