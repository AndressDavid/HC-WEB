var oRecomendacionesEvolucionUcc = {
	goTablaGrupoMedicamentoUci: $('#tblgrupoMedicamentoUnidad'),
	aMedicamentosInicial: {},
	indexedit: 0,
	lcMensajeError: '',
	lcObjetoError: '',
	lcFormaUcc: '',

	inicializar: function() 
	{
		this.listadosRecomendaciones();
		this.IniciarTablaGrupoMedicamentoUci();
		this.CargarMedicamentos();
		this.ConsultarMedicamentoUcc();
		$('#FormRecomendacionesUci').on('submit', function(e){e.preventDefault();});
		$('#selGrupoMedicamentoUci').on('change',function() {
			$lcGrupoMedicamento = $("#selGrupoMedicamentoUci").val();
			oRecomendacionesEvolucionUcc.cargarListaMedicamentoGrupo();
		});
		
		$('#selMedicamentosUci').on('change',function() {
			lcCodigoMedicamentoGrupo = $('#selMedicamentosUci').val();
			lcDescripcioMedicamentoGrupo = $("#selMedicamentosUci option[value="+lcCodigoMedicamentoGrupo+"]").text();
			$("#medicamentoDescripcionUci").val(lcDescripcioMedicamentoGrupo);
		});
		$('#adicionarGrupoMedicamento').on('click', oRecomendacionesEvolucionUcc.validarRegistroUCC);
	},
	
	CargarMedicamentos: function () {
		$.ajax({
			type: "POST",
			url: "vista-evoluciones/ajax/recomendaciones.php",
			data: {accion: 'listaMedicamentos'},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					oRecomendacionesEvolucionUcc.aMedicamentosInicial = loTipos.medicamentosUci;
					oRecomendacionesEvolucionUcc.cargarListaMedicamentoGrupo();
				} else {
					alert(loTipos.error + ' ', "warning");
				}
			} catch(err) {
				alert('No se pudo realizar la busqueda de Lista de Medicamentos.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			alert('Se presentó un error al buscar ' + mensaje +'.', "danger");
		});
		return this;
	},
	
	cargarListaMedicamentoGrupo: function () {
		var lcCodigo = $("#selGrupoMedicamentoUci").val(); 
		var loMedica = oRecomendacionesEvolucionUcc.aMedicamentosInicial;
		$('#'+'medicamentoDescripcionUci').val('');
		var loSelect = $('#'+'selMedicamentosUci');
		loSelect.empty();
		loSelect.append('<option value=""></option>');
		$.each(loMedica, function( lcKey, loTipo ) {
			if(loTipo.CODGRP == lcCodigo || lcCodigo == ''){
				var lcOption = '<option value="' + loTipo.CODMED + '">' + loTipo.DESDES + '</option>';
					loSelect.append(lcOption);
				}
		});
		return this;
	},
	
	adicionarRegistroUCC: function (laDatos){
		
		oRecomendacionesEvolucionUcc.goTablaGrupoMedicamentoUci.bootstrapTable('insertRow', {
			index: 1,
			row: {
					CODMEDICA: laDatos.Medicamento,
					DESMEDICA: laDatos.DescMedicamento,
					CODGRUPMED: laDatos.CodigoGrupo,
					DESGRUPOMED: laDatos.DescGrupo,
					INDICADO: laDatos.Indicado
				}
			});
			oRecomendacionesEvolucionUcc.iniciaCamposGrupoMed();
		
	},
	
	// Modificación medicamento ya ingresado en la tabla
	modificarRegistroUCC: function(laDatos){
		
		oRecomendacionesEvolucionUcc.goTablaGrupoMedicamentoUci.bootstrapTable('updateRow', {
			index: oRecomendacionesEvolucionUcc.indexedit,
			row: {
					CODMEDICA: laDatos.Medicamento,
					DESMEDICA: laDatos.DescMedicamento,
					CODGRUPMED: laDatos.CodigoGrupo,
					DESGRUPOMED: laDatos.DescGrupo,
					INDICADO: laDatos.Indicado
				}
			});
			oRecomendacionesEvolucionUcc.iniciaCamposGrupoMed();
	},
	
	
	verificaCodigoExiste: function(tcCodigo) {
		var taTablaValida = oRecomendacionesEvolucionUcc.goTablaGrupoMedicamentoUci.bootstrapTable('getData');
		var llRetorno = true ;
		if(taTablaValida != ''){
			$.each(taTablaValida, function( lcKey, loTipo ) {
				if(loTipo['CODMEDICA']==tcCodigo){
					oRecomendacionesEvolucionUcc.indexedit = lcKey;
					llRetorno = false;
				}
			});
		};
		return llRetorno ;
	},
	
	iniciaCamposGrupoMed: function() {
		$("#selGrupoMedicamentoUci").val('');
		$("#selMedicamentosUci").val('');
		$("#medicamentoDescripcionUci").val('');
		$("#indicadoParaUci").val('');
		$('#selGrupoMedicamentoUci').focus();
	},
	
	listadosRecomendaciones: function (){
		$.ajax({
			type: "POST",
			url: "vista-evoluciones/ajax/recomendaciones",
			data: {accion:'listasRecomendaciones'},
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.error == ''){
					loSelect = $("#selGrupoMedicamentoUci");
					loSelect.empty();
					loSelect.append('<option value=""></option>');
					$.each(loDatos.grupoMedicamentosUci, function( lcKey, lcGrupo ) {
						var lcOption = '<option value="' + lcKey + '">' + lcGrupo + '</option>';
						loSelect.append(lcOption);
					});
				} else {
					fnAlert(loDatos.error)
				}
			} catch(err) {
				fnAlert('No se pudo realizar la consultar Recomendaciones UCI.')
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			oRecomendacionesEvolucionUcc.goTablaGrupoMedicamentoUci.bootstrapTable('hideLoading');
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar Recomendaciones UCI.');
		});
	},

	IniciarTablaGrupoMedicamentoUci: function (){
		oRecomendacionesEvolucionUcc.goTablaGrupoMedicamentoUci.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			checkboxHeader: false,
			clicktoselect: 'true',
			locale: 'es-ES',
			undefinedText: 'N/A',
			toolbar: '#toolBarLst',
			height: '500',
			search: false,
			pagination: false,
			iconSize: 'sm',
			columns: [
			{
				title: 'GRUPO MEDICAMENTO',
				field: 'DESGRUPOMED',
				width: 15, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},{
				title: 'MEDICAMENTO',
				field: 'DESMEDICA',
				width: 35, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},{
				title: 'INDICADO PARA',
				field: 'INDICADO',
				width: 45, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
			    title: 'Acción',
			    field: 'ACCIONES',
			    align: 'center',
			    clickToSelect: false,
			    events: this.operateEvents,
			    formatter: this.formatoAccion
		    }
			]
		});
	},
	
	operateEvents:
	{
		'click .editarUcc': function (e, value, row, index) {
			oRecomendacionesEvolucionUcc.editarRegistroUCC(row);
		},
		'click .borrarUcc': function (e, value, row, index) {
			fnConfirm('Desea eliminar el registro ?', false, false, false, false, function(){
				oRecomendacionesEvolucionUcc.goTablaGrupoMedicamentoUci.bootstrapTable('remove', {
					field: 'CODMEDICA',
					values: row.CODMEDICA
				});
			},'');
		}
	},
	
	formatoAccion: function()
	{
		return	'<a class="editarUcc" href="javascript:void(0)" title="Editar"><i class="fas fa-pencil-alt"></i></a> '+
				'<a class="borrarUcc" href="javascript:void(0)" title="Eliminar"><i class="fas fa-trash-alt" style="color:#E96B50"></i></a>';
	},

	// funcion que edita elemento seleccionado
	editarRegistroUCC: function(arow)
	{
		$("#selGrupoMedicamentoUci").val(arow.CODGRUPMED);
		oRecomendacionesEvolucionUcc.cargarListaMedicamentoGrupo();
		$("#selMedicamentosUci").val(arow.CODMEDICA);
		$("#medicamentoDescripcionUci").val(arow.DESMEDICA);
		$("#indicadoParaUci").val(arow.INDICADO);
		$('#indicadoParaUci').focus();
	},
	
	// funcion que edita elemento seleccionado
	ConsultarMedicamentoUcc: function () {
		
		$.ajax({
			type: "POST",
			url: "vista-evoluciones/ajax/recomendaciones.php",
			data: {accion : 'ConsultaMedicamentosUcc', lnIngreso: aDatosIngreso['nIngreso']},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					if (loTipos.medicamentosUCC != []) {
						oRecomendacionesEvolucionUcc.CargarMedicamentosUCC(loTipos);
					}
				} else {
					alert(loTipos.error + ' ', "warning");
				}
			} catch(err) {
				alert('No se pudo realizar la consulta de Medicamentos para recomendaciones UCC', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			alert('Se presentó un error al consultar Medicamentos para recomendaciones UCC', "danger");
		});
		return this;
	},
	
	// Cargar medicamentos que tiene el paciente para recomendaciones en UCC
	CargarMedicamentosUCC: function(taDatos)
	{
		if (!(taDatos.medicamentosUCC===undefined)){
			var aMedicaUCC = [];
			$.each(taDatos.medicamentosUCC,function(lckey, loValor){
				aMedicaUCC.push(loValor);
			});
			oRecomendacionesEvolucionUcc.goTablaGrupoMedicamentoUci.bootstrapTable('append', aMedicaUCC);
		}

	},
	
	validarRegistroUCC: function(e)
	{
		e.preventDefault();
		laObjeto = {
			1: 'selGrupoMedicamentoUci',
			2: 'selMedicamentosUci',
			3: 'indicadoParaUci',
		};
		
		var lcCodigoGrupo = $("#selGrupoMedicamentoUci").val();
		var lcCodigoMedicamento = $("#selMedicamentosUci").val();
		var lcDescMedicamento = $("#medicamentoDescripcionUci").val();
		var lcIndicadoPara = $("#indicadoParaUci").val();

		if (lcCodigoGrupo=='' || lcCodigoMedicamento=='' || lcDescMedicamento=='' || lcIndicadoPara==''){
			var lcObjeto = '';
			var lcmensaje = 'Campos Obligatorios Recomendaciones UCC:<br>';
			lcmensaje+=lcCodigoGrupo==''?'Grupo Medicamento,<br> ':'';
			lcObjeto = lcCodigoGrupo=='' && lcObjeto==''?laObjeto[1]:lcObjeto;
			lcmensaje+=lcCodigoMedicamento==''?'Medicamento,<br> ':'';
			lcObjeto = lcCodigoMedicamento=='' && lcObjeto==''?laObjeto[2]:lcObjeto;
			lcmensaje+=lcIndicadoPara==''?'Indicado para<br>':'';
			lcObjeto = lcIndicadoPara=='' && lcObjeto==''?laObjeto[3]:lcObjeto;

			$('#'+lcObjeto).focus();
			fnAlert(lcmensaje, 'Recomendaciones UCC', false, false, false);
			return false;
		}

		var lcDescripcionGrupo = $("#selGrupoMedicamentoUci option[value="+lcCodigoGrupo+"]").text();
		var lcMedicamento = {CodigoGrupo: lcCodigoGrupo, DescGrupo: lcDescripcionGrupo, Medicamento: lcCodigoMedicamento, DescMedicamento: lcDescMedicamento, Indicado: lcIndicadoPara};
		var llverifica = oRecomendacionesEvolucionUcc.verificaCodigoExiste(lcCodigoMedicamento);
		if(llverifica) {
			oRecomendacionesEvolucionUcc.adicionarRegistroUCC(lcMedicamento);
		}else{
			fnConfirm('El registro a ingresar ya existe. Desea modificarlo ?', 'Recomendaciones UCC', false, false, false, function(){
			oRecomendacionesEvolucionUcc.modificarRegistroUCC(lcMedicamento);});
		}

	},
		
	validacion: function() 
	{
		var lbValido = true;
		lbValido = oRecomendacionesEvolucionUcc.validarRecomendacionesUCC();	
		if(lbValido){
			lbValido = oRecomendacionesEvolucionUcc.validarIndicado();		
		}
		return lbValido;
	},
	
	validarIndicado: function() {
		
		llRetorno = true ;
		var taDatosTabla = oRecomendacionesEvolucionUcc.goTablaGrupoMedicamentoUci.bootstrapTable('getData');
		oRecomendacionesEvolucionUcc.lcObjetoError = '#selMedicamentosUci';
		oRecomendacionesEvolucionUcc.lcFormaUcc = '#FormGrupoUCC';
		if(taDatosTabla==''){
			oRecomendacionesEvolucionUcc.lcMensajeError = 'No ha ingresado medicamentos en las Recomendaciones de UCC';
			llRetorno = false;
		}else{
			$.each(taDatosTabla, function( lcKey, loTipo ) {
				if(loTipo['INDICADO']=='' && llRetorno){
					oRecomendacionesEvolucionUcc.lcMensajeError = 'Falta registrar el dato Indicado para del medicamento: ' + loTipo['DESMEDICA'] + ' revise por favor.';
					llRetorno = false;
				}
			});
		}
		return llRetorno ;
	},
	
	validarRecomendacionesUCC: function() {
		llRetorno = true ;
		var taDatos = $('#FormRecomendacionesUCC').serializeArray();
		if(taDatos!==''){
			oRecomendacionesEvolucionUcc.lcMensajeError = 'Falta registrar Parámetros para recomendaciones UCC revise por favor.';
			oRecomendacionesEvolucionUcc.lcFormaUcc = '#FormRecomendacionesUCC';
			$.each(taDatos, function( lcKey, loTipo ) {
				if(loTipo['value']=='' && llRetorno){
					oRecomendacionesEvolucionUcc.lcObjetoError = '#'+loTipo['name'];
					llRetorno = false;
				}
			});
		}
		return llRetorno ;
	},
	
	obtenerDatos: function() {
		var TablaRecomendacionesUCC = oRecomendacionesEvolucionUcc.goTablaGrupoMedicamentoUci.bootstrapTable('getData');
		var laDatos = OrganizarSerializeArray($('#FormRecomendacionesUCC').serializeArray().concat($('#FormGrupoUCC').serializeArray()));
		laDatos.Recomendaciones=TablaRecomendacionesUCC;
		laDatos.Tabaquism = laDatos.Tabaquism===undefined?'0':'1';
		laDatos.Ejercicio = laDatos.Ejercicio===undefined?'0':'1';
		laDatos.Dieta_Sal = laDatos.Dieta_Sal===undefined?'0':'1';
		laDatos.Prog_Reha = laDatos.Prog_Reha===undefined?'0':'1';
		laDatos.Tratamien = laDatos.Tratamien===undefined?'0':'1';
		return laDatos;
	},
	
}
