var oInterconsultaOrdMedica = {
	gotableInterconsultasOM : $('#tblInterconsultaOM'),
	gcUrlAjax: 'vista-ordenes-medicas/ajax/ajax',
	lcTitulo : 'Interconsulta ordenes médicas',
	datosEspecialidadesFisioterapia: [],
	lcFormaError : '',
	lcObjetoError: '',
	lcMensajeError: '',
	aCupsInterconsulta: [],
	gcInterconsultasPendientes: '',
	gcEnter: String.fromCharCode(13),

	inicializar: function(){
		this.iniciarTablaInterconsulta();
		this.cargarListaInterconsultas();
		this.especialidadesFiosterapia();
		this.cargarListadosInterconsultas($('#selPrioridadInterconsultaOM'),'prioridadInterconsultas','Listado prioridad interconsultas');
		$('#AdicionarInterconsultaOM').on('click', this.validaAdicionarInterconsulta);
		$('#selListadoEspecialidadesOM').on('change', function(){
			oInterconsultaOrdMedica.validarInterconsultas($("#selListadoEspecialidadesOM").val());
		});

		$('#FormInterconsultaOM').validate({
			rules: {
				selListadoEspecialidadesOM: "required",
				selPrioridadInterconsultaOM: "required",
				txtMotivoInterconsultaOM: "required",
			},
			errorElement: "div",
			errorPlacement: function ( error, element ) {
				error.addClass( "invalid-tooltip" );

				if (element.prop("type")==="radio") {
					error.insertAfter(element.parent("label"));
				} else {
					error.insertAfter(element);
				}
			},
			highlight: function (element, errorClass, validClass) {
				$(element).addClass("is-invalid").removeClass("is-valid");
			},
			unhighlight: function (element, errorClass, validClass) {
				$(element).addClass("is-valid").removeClass("is-invalid");
			}
		});
	},

	validaAdicionarInterconsulta: function(e){
		e.preventDefault();
		var loFunction = false;
		if ($('#FormInterconsultaOM').valid()){
			var lcCodigoInterconsulta = $("#selListadoEspecialidadesOM").val();
			var lcTipoInterconsulta = $('input[name=opcTipoInterconsulta]:checked').val();
			var lcPrioridad = $("#selPrioridadInterconsultaOM").val();
			var lcMotivoInterconsulta = $("#txtMotivoInterconsultaOM").val().trim();
			
			if (lcTipoInterconsulta===undefined){
				fnAlert('Falta seleccionar tipo de consulta, verifique por favor.')
				return false;
			}
			
			if (lcMotivoInterconsulta==''){
				fnAlert('Debe describir el motivo de consulta, verifique por favor.')
				return false;
			}

			var lcInterconsultas = {lcCodigoInterconsulta: lcCodigoInterconsulta, lcTipoInterconsulta: lcTipoInterconsulta,
				lcPrioridad: lcPrioridad, lcMotivoInterconsulta: lcMotivoInterconsulta};
			oInterconsultaOrdMedica.validaInterconsultasSinResponder(lcCodigoInterconsulta,lcInterconsultas);
			$('#selListadoEspecialidadesOM').focus();
		}
	},

	adicionarInterconsulta: function(tcCodigoInterconsulta,tcDatos){
		var taTablaInterconsulta = oInterconsultaOrdMedica.gotableInterconsultasOM.bootstrapTable('getData');
		var llverificaExiste = oInterconsultaOrdMedica.verificaCodigoExiste(tcCodigoInterconsulta,taTablaInterconsulta);
		if(llverificaExiste) {
			oInterconsultaOrdMedica.adicionarRegistroInterconsulta(tcDatos);
		}
		else{
			fnConfirm('Interconsulta ya ingresada, desea modificarla?', oInterconsultaOrdMedica.lcTitulo, false, false, 'medium', function(){
				oInterconsultaOrdMedica.modificarInterconsulta(tcDatos);
			});
		}
	},

	validarInterconsultas: function(tcEspecialidad) {
		$('#radIntOpinion,#radIntManejoConjunto,#radIntTraslado').prop('checked',false);
		$('#radIntOpinion,#radIntManejoConjunto,#radIntTraslado').attr('disabled',false);

		if(oInterconsultaOrdMedica.datosEspecialidadesFisioterapia != ''){
			$.each(oInterconsultaOrdMedica.datosEspecialidadesFisioterapia, function( lcKey, loTipo ) {
				if(loTipo['CODIGO']==tcEspecialidad){
					$('#radIntOpinion,#radIntManejoConjunto,#radIntTraslado').attr('disabled',true);
					$('#radIntManejoConjunto').prop('checked',true);
					return false;
				}
			});
		};
	},

	verificaCodigoExiste: function(tcCodigo,taTablaValida) {
		var llRetorno = true ;
			if(taTablaValida != ''){
				$.each(taTablaValida, function( lcKey, loTipo ) {
					if(loTipo['CODIGOSERV']==tcCodigo){
						oInterconsultaOrdMedica.indexedit = lcKey;
						llRetorno = false;
					}
				});
			};
		return llRetorno ;
	},

	validaInterconsultasSinResponder: function(tcCodigoEspec,taDatos) {
		var lcIntSinResponder='';
		$.ajax({
			type: "POST",
			url: oInterconsultaOrdMedica.gcUrlAjax,
			data: {accion: 'consInterSisResponder', lnIngreso: aDatosIngreso.nIngreso, lcEspecialidad: tcCodigoEspec},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					if (loTipos.TIPOS.length>0){
						lcDescripcionEspec = $("#selListadoEspecialidadesOM option[value="+tcCodigoEspec+"]").text();
						$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
							lcAmpm = parseInt(strNumAHora(loTipo.HORA_SOLICITUD).substring(0,2))>=12 ? 'p.m.' : 'a.m.';
							lcIntSinResponder += '* Ordenado por: ' + loTipo.NOMBRE_MEDICO + ' '
							+ strNumAFecha(loTipo.FECHA_SOLICITUD) + ' a las ' + strNumAHora(loTipo.HORA_SOLICITUD) + ' '+lcAmpm + '<br>'
						});
						lcTextoInterconsultas = 'Ya existe(n) interconsulta(s) de ' + lcDescripcionEspec + ' sin responder:'
												+ '<br><br>' + lcIntSinResponder + '<br>'
												+ 'NO se podra solicitar interconsulta para ' +lcDescripcionEspec+ ' hasta que se responda(n).';
						fnAlert(lcTextoInterconsultas, 'Validación Interconsultas', false, false, 'medium');
					}else{
						oInterconsultaOrdMedica.adicionarInterconsulta(tcCodigoEspec,taDatos);
					}
				} else {
					alert(loTipos.error + ' ', "warning");
				}

			} catch(err) {
				console.log(err);
				alert('No se pudo realizar la busqueda de consultas sin responder.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			alert('Se presentó un error al buscar consultas sin responder.', "danger");
		});
	},

	validaInterconsultasPendientes: function() {
		oInterconsultaOrdMedica.gcInterconsultasPendientes='';
		$.ajax({
			type: "POST",
			url: oInterconsultaOrdMedica.gcUrlAjax,
			data: {accion: 'consInterSisResponder', lnIngreso: aDatosIngreso.nIngreso, lcEspecialidad: ''},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					if (loTipos.TIPOS.length>0){
						$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
							oInterconsultaOrdMedica.gcInterconsultasPendientes += '* ' + loTipo.ESPECIALIDAD +'.<br>';
						});
					}
				} else {
					alert(loTipos.error + ' ', "warning");
				}

			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la busqueda valida interconsultas pendientes.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al buscar consultas valida interconsultas pendientes.', "danger");
		});
	},

	validaInterconsultas: function()
	{
		if (oInterconsultaOrdMedica.gcInterconsultasPendientes!=''){
			lcTextoInterconsultas='Tenga en cuenta que las siguientes especialidades tienen interconsultas por responder: <br>' + oInterconsultaOrdMedica.gcInterconsultasPendientes;
			fnAlert(lcTextoInterconsultas, 'INTERCONSULTAS POR RESPONDER', false, 'blue', 'medium');
		}
	},

	adicionarRegistroInterconsulta: function(camposFilaInterconsulta){
		var rows = []
			rows.push({
			CODIGOSERV: camposFilaInterconsulta.lcCodigoInterconsulta,
			SERVICIO: $("#selListadoEspecialidadesOM option[value="+camposFilaInterconsulta.lcCodigoInterconsulta+"]").text(),
			CODTIPO: camposFilaInterconsulta.lcTipoInterconsulta,
			TIPO: $('input[name=opcTipoInterconsulta]:checked + label').text(),
			TIPONUMERO:  $('input[name=opcTipoInterconsulta]:checked').attr('id-num'),
			CUPS: $("#selListadoEspecialidadesOM option[value="+camposFilaInterconsulta.lcCodigoInterconsulta+"]").attr('id-cups'),
			CODPRIORIDAD: camposFilaInterconsulta.lcPrioridad,
			PRIORIDAD: $("#selPrioridadInterconsultaOM option[value="+camposFilaInterconsulta.lcPrioridad+"]").text(),
			TEXTO: camposFilaInterconsulta.lcMotivoInterconsulta,
			BORRAR: '',
		})
		oInterconsultaOrdMedica.gotableInterconsultasOM.bootstrapTable('append', rows);
		oInterconsultaOrdMedica.inicializaDatosInt();
	},

	modificarInterconsulta: function(camposInterconsulta) {
		oInterconsultaOrdMedica.gotableInterconsultasOM.bootstrapTable('updateRow', {
			index: oInterconsultaOrdMedica.indexedit,
			row: {
				CODIGOSERV: camposInterconsulta.lcCodigoInterconsulta.trim(),
				SERVICIO: $("#selListadoEspecialidadesOM option[value="+camposInterconsulta.lcCodigoInterconsulta+"]").text(),
				CODTIPO: camposInterconsulta.lcTipoInterconsulta,
				TIPO: $('input[name=opcTipoInterconsulta]:checked + label').text(),
				TIPONUMERO:  $('input[name=opcTipoInterconsulta]:checked').attr('id-num'),
				CUPS: $("#selListadoEspecialidadesOM option[value="+camposInterconsulta.lcCodigoInterconsulta+"]").attr('id-cups'),
				CODPRIORIDAD: camposInterconsulta.lcPrioridad,
				PRIORIDAD: $("#selPrioridadInterconsultaOM option[value="+camposInterconsulta.lcPrioridad+"]").text(),
				TEXTO: camposInterconsulta.lcMotivoInterconsulta,
				BORRAR: '',
			}
		 });
		oInterconsultaOrdMedica.inicializaDatosInt();
	},

	inicializaDatosInt: function () {
		$("#selListadoEspecialidadesOM").removeClass("is-valid");
		$("#selListadoEspecialidadesOM").val('');
		$("#selPrioridadInterconsultaOM").removeClass("is-valid");
		$("#selPrioridadInterconsultaOM").val('');
		$("#txtMotivoInterconsultaOM").removeClass("is-valid");
		$("#txtMotivoInterconsultaOM").val('');
		$('#radIntOpinion,#radIntManejoConjunto,#radIntTraslado').prop('checked',false);
		$('#selListadoEspecialidadesOM').focus();
		oInterconsultaOrdMedica.registroInterconsultas();
	},

	registroInterconsultas: function() {
		var laInterconsultas = $('#tblInterconsultaOM').bootstrapTable('getData')
		oInterconsultaOrdMedica.aCupsInterconsulta = [];

		$.each(laInterconsultas, function( lcKey, loTipo ) {
			oInterconsultaOrdMedica.aCupsInterconsulta.push({
					TIPO: 'INTER',
					CODIGO: loTipo.CUPS,
					DESCRIPCION: loTipo.SERVICIO,
					ESPECIALIDAD: loTipo.CODIGOSERV,
					POSNOPOS: 'P',
					HEXALIS: '',
					OBSERVACIONES: loTipo.TEXTO,
					SOLICITUDINTERCONSULTA: 'S',
					CODIGOTIPOINTERCONSULTA: loTipo.CODTIPO,
					DESCRTIPOINTERCONSULTA: loTipo.TIPO,
					NUMEROTIPOINTERCONSULTA: loTipo.TIPONUMERO,
					CODIGOPRIORIDADINTERCONSULTA: loTipo.CODPRIORIDAD,
					DESCRPRIORIDADINTERCONSULTA: loTipo.PRIORIDAD,

			});
		});
	},

	cargarListadosInterconsultas: function(id,lcTipo,mensaje) {
		var loSelect = id;

		$.ajax({
			type: "POST",
			url: oInterconsultaOrdMedica.gcUrlAjax,
			data: {accion: lcTipo},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
						loSelect.append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
					});
				} else {
					alert(loTipos.error + ' ', "warning");
				}

			} catch(err) {
				alert('No se pudo realizar la busqueda de ' + mensaje +'.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			alert('Se presentó un error al buscar ' + mensaje +'.', "danger");
		});
		return this;
	},


	cargarListaInterconsultas: function() {
		var loSelect = $('#selListadoEspecialidadesOM');
		var lcTipo = 'tablaInterconsultas';

		$.ajax({
			type: "POST",
			url: oInterconsultaOrdMedica.gcUrlAjax,
			data: {accion: lcTipo},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
						loSelect.append('<option id-cups="' + loTipo.CUPS + '" value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
					});
				} else {
					alert(loTipos.error + ' ', "warning");
				}

			} catch(err) {
				alert('No se pudo realizar la busqueda de listado interconsultas.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			alert('Se presentó un error al buscar listado listado interconsultas.', "danger");
		});
		return this;
	},

	especialidadesFiosterapia: function() {
		$.ajax({
			type: "POST",
			url: oInterconsultaOrdMedica.gcUrlAjax,
			data: {accion: 'especFisioterapia'},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					oInterconsultaOrdMedica.datosEspecialidadesFisioterapia = loTipos.TIPOS;
				} else {
					alert(loTipos.error + ' ', "warning");
				}
			} catch(err) {
				alert('No se pudo realizar la busqueda de especialidades Fisioterapia.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			alert('Se presentó un error al buscar listado especialidades Fisioterapia.', "danger");
		});
		return this;
	},


	validacion: function() {
		var lbValido = true;
		var aInterSolicitadas = oInterconsultaOrdMedica.gotableInterconsultasOM.bootstrapTable('getData');
		oInterconsultaOrdMedica.lcObjetoError = '';
		oInterconsultaOrdMedica.lcFormaError = '';

		if(aInterSolicitadas != ''){
			$.each(aInterSolicitadas, function( lcKey, loTipo ) {
				if(loTipo['CODIGOSERV']=='' || loTipo['SERVICIO']=='' || loTipo['CODTIPO']=='' || loTipo['TIPO']==''  || loTipo['CODPRIORIDAD']==''
				|| loTipo['PRIORIDAD']==''
				){
					oInterconsultaOrdMedica.lcMensajeError = 'Existen datos pendientes por registrar en las interconsultas.';
					oInterconsultaOrdMedica.lcFormaError = 'FormInterconsultaOM';
					oInterconsultaOrdMedica.lcObjetoError = 'selListadoEspecialidadesOM';
					lbValido = false;
				}
			});
		}

		if (lbValido){
			$.each(aInterSolicitadas, function( lcKey, loTipo ) {
				if(loTipo['CUPS']==''
				){
					oInterconsultaOrdMedica.lcMensajeError = 'Existen interconsultas sin PROCEDIMIENTO asignado.';
					oInterconsultaOrdMedica.lcFormaError = 'FormInterconsultaOM';
					oInterconsultaOrdMedica.lcObjetoError = 'selListadoEspecialidadesOM';
					lbValido = false;
				}
			});
		}

		return lbValido;
	},

	iniciarTablaInterconsulta: function(){
		oInterconsultaOrdMedica.gotableInterconsultasOM.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-dark',
			locale: 'es-ES',
			undefinedText: 'N/A',
			toolbar: '#toolBarLst',
			height: '400',
			pagination: false,
			pageSize: 25,
			pageList: '[10, 20, 50, 100, 250, 500, All]',
			filterAlgorithm: 'and',
			sortable: true,
			search: false,
			searchOnEnterKey: false,
			visibleSearch: false,
			showSearchButton: false,
			showSearchClearButton: false,
			trimOnSearch: true,
			iconSize: 'sm',
			singleSelect:'true',
			columns: [
			{
				title: 'Servicio Interconsultado',
				field: 'SERVICIO',
				width: 30, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Tipo',
				field: 'TIPO',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Prioridad',
				field: 'PRIORIDAD',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Motivo Interconsulta',
				field: 'TEXTO',
			  	width: 45, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Acción',
				field: 'BORRAR',
  				width: 5, widthUnit: "%",
				halign: 'center',
				align: 'center',
				clickToSelect: false,
				events: this.eventoInterconsulta,
				formatter: this.formatoBorrarInterconsulta
			}
		  ]
		});
	},

	eventoInterconsulta:  {
		'click .like': function (e, value, row, index) {
			alert('Haga click sobre la acción, row: ' + JSON.stringify(row))
		},
		'click .eliminaInterconsulta': function (e, value, row, index) {
			fnConfirm('Desea eliminar la interconsulta?', false, false, false, false, function(){
				oInterconsultaOrdMedica.gotableInterconsultasOM.bootstrapTable('remove', {
				field: 'CODIGOSERV',
				values: [row.CODIGOSERV]
				});
			},'');
		}
	},

	formatoBorrarInterconsulta: function (value, row, index) {
		return [
		  '<a class="eliminaInterconsulta" href="javascript:void(0)" title="Eliminar">',
		  '<i class="fa fa-trash" style="color:#E96B50"></i>',
		  '</a>'
		].join('')
	},

	obtenerDatos: function() {
		var laInterconsultas = oInterconsultaOrdMedica.aCupsInterconsulta;
		return laInterconsultas;
	},

}