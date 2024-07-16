var oConciliacion = {
	gotableMC: $('#tblMedica'),
	aDatosConciliacionInicial: {}, ListaMedicamentos: {}, esObjetoAmb: {}, aMedicaInicial: {},
	gcCodigoMedicamento: '', lcMensajeError: '',
	indexedit: 0,
	llmostrarMensaje: true,
	lcTitulo: 'Conciliación Medicamentos',
	llModifica: false,

	inicializar: function(tcTipo)
	{
		$('#selTipoFrecuencia').tiposMedica({tipo:'Frecuencia'});
		oConciliacion.IniciarTabla();
		oConciliacion.validaConciliacionEgreso();
		oConciliacion.dosisViaAdministracion();
		oMedicamentos.consultaMedicamentos('cMedicamentoConc','cCodigoMedicamentoConc','cDescripcionMedicamentoConc','txtDosis','CO');
		$('#selConsume').change(oConciliacion.validarTabla);
		$('#selTipoFrecuencia').change(oConciliacion.validarTFrecuencia);
		$('#txtFrecuencia').on('change',oConciliacion.validarFrecuencia);
		$('#btnAdicionarM').on('click',oConciliacion.validarRegistroCM);
		
		$('#txtMedicamentoNC').on('keyup', function(){
			oConciliacion.validarObjeto("txtMedicamentoNC","cMedicamentoConc","lblMedicamentoC");
			$('#cCodigoMedicamentoConc,#cDescripcionMedicamentoConc').val('');
		});
		
		$('#cMedicamentoConc').on('keyup', function(){
			oConciliacion.validarObjeto("cMedicamentoConc","txtMedicamentoNC","lblMedicamentoNC");
		});

		if(tcTipo=='EVO'){
			oConciliacion.ConsultarConciliacion(1);
		}else{
			oConciliacion.ConsultarConciliacion(9,0);
		}
	},

	dosisViaAdministracion: function(){
		oConciliacion.consultaDosis('','');
		oConciliacion.consultaViaAdministracion('','','');
	},

	seleccionaMedicamento: function(taItem){
		oConciliacion.consultaDosis(taItem.CODIGO,'');
		oConciliacion.consultaViaAdministracion(taItem.CODIGO,taItem.DESCRIPCION,'');
	},
	
	consultaDosis: function(tcCodigo,tcUnidadDosis){
		$('#selTipoDosis').empty();
		$('#selTipoDosis').val('');
		
		$.ajax({
			type: "POST",
			url: 'vista-ordenes-medicas/ajax/ajax',
			data: {accion: 'consultaDosisMedicamento', lcMedicamento: tcCodigo},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.TIPOS.length>1){
						$('#selTipoDosis').append('<option value=""></option>');
					}
					$.each(toDatos.TIPOS, function( lcKey, loTipo ) {
						$('#selTipoDosis').append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
					});
					
					if (tcUnidadDosis!=''){
						$("#selTipoDosis").val(tcUnidadDosis);
					}
				} else {
					fnAlert(toDatos.Error);
				}	
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta dosis medicamento conciliación.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta dosis medicamento conciliación.");
		});
	},
	
	consultaViaAdministracion: function(tcCodigo,tcDescripcion,tcVia){
		var lcAltoRiesgo='';
		$('#selTipoVia').empty();
		$('#selTipoVia').val('');

		$.ajax({
			type: "POST",
			url: 'vista-ordenes-medicas/ajax/ajax',
			data: {accion: 'consultaViaAdministracionMedicamento', lcMedicamento: tcCodigo},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.TIPOS.length>1){
						$('#selTipoVia').append('<option value=""></option>');
					}
					$.each(toDatos.TIPOS, function( lcKey, loTipo ) {
						$('#selTipoVia').append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
					});
					
					if (tcVia!=''){
						$("#selTipoVia").val(tcVia);
					}	
				} else {
					fnAlert(toDatos.Error);
				}	
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta vía administración conciliación.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al consulta vía administración conciliación.");
		});
	},
	
	validaConciliacionEgreso: function()
	{
		oConciliacion.esObjetoAmb['oAmbulatorio'] = typeof oAmbulatorio === 'object';
	},

	// Valida cada uno de los medicamentos a ingresar
	validarRegistroCM: function(e)
	{
		e.preventDefault();
		var lcConsume = $("#selConsume").val(),
			laObjeto = {
				2: 'txtDosis',
				3: 'selTipoDosis',
				4: 'txtFrecuencia',
				5: 'selTipoFrecuencia',
				6: 'selTipoVia',
				7: 'selTipoConducta',
				8: 'edtObserva'
			};

		if (lcConsume != ''){
			if (lcConsume=='Si'){
				var lcMedicamentoN = $("#cDescripcionMedicamentoConc").val()!='' ? $("#cDescripcionMedicamentoConc").val() : $("#txtMedicamentoNC").val();
				var lcCodigo = oConciliacion.gcCodigoMedicamento != '' ? oConciliacion.gcCodigoMedicamento : 
				($("#cCodigoMedicamentoConc").val()!='' ? $("#cCodigoMedicamentoConc").val() : oConciliacion.adicionarCodigo());
				
				var lnDosis = $("#txtDosis").val();
				var lcTipoDosis = $("#selTipoDosis").val();
				var lnFrecuencia = $("#txtFrecuencia").val();
				var lcTipoFrecuencia = $("#selTipoFrecuencia").val();
				var lcTipoVia = $("#selTipoVia").val();
				var lcConducta = $("#selTipoConducta").val();
				var lcObserva = $("#edtObserva").val();

				 if (lcMedicamentoN=='' || lnDosis<=0 || lnDosis>9999999 || lcTipoDosis==0 || lnFrecuencia<=0 || lnFrecuencia>99 || lcTipoFrecuencia==0 || lcTipoVia==0 || lcConducta =='' || (lcConducta =='Suspende' && lcObserva =='')){
					var lcObjeto = '';
					var lcmensaje = 'Campos Obligatorios:<br>';

					lcmensaje+=lcMedicamentoN==''?'Medicamento,<br> ':'';
					lcObjeto = lcMedicamentoN=='' && lcObjeto==''?laObjeto[1]:lcObjeto;
					lcmensaje+=(lnDosis<=0 || lnDosis>9999999)?'Dosis,<br> ':'';
					lcObjeto = (lnDosis<=0 || lnDosis>9999999) && lcObjeto==''?laObjeto[2]:lcObjeto;
					lcmensaje+=lcTipoDosis==0?'Tipo de Dosis,<br>':'';
					lcObjeto = lcTipoDosis==0 && lcObjeto==''?laObjeto[3]:lcObjeto;
					lcmensaje+=(lnFrecuencia<=0 || lnFrecuencia>99)?'Frecuencia,<br>':'';
					lcObjeto = (lnFrecuencia<=0 || lnFrecuencia>99) && lcObjeto==''?laObjeto[4]:lcObjeto;
					lcmensaje+=lcTipoFrecuencia==0?'Tipo de Frecuencia,<br>':'';
					lcObjeto = lcTipoFrecuencia==0 && lcObjeto==''?laObjeto[5]:lcObjeto;
					lcmensaje+=lcTipoVia==0?'Vía administración.<br>':'';
					lcObjeto = lcTipoVia==0 && lcObjeto==''?laObjeto[6]:lcObjeto;
					lcmensaje+=lcConducta==''?'Conducta a seguir.<br>':'';
					lcObjeto = lcConducta=='' && lcObjeto==''?laObjeto[7]:lcObjeto;
					lcmensaje+=(lcConducta=='Suspende' && lcObserva =='')?'Observaciones.<br>':'';
					lcObjeto = (lcConducta=='Suspende' && lcObserva =='' && lcObjeto=='')?laObjeto[8]:lcObjeto;

					$('#'+lcObjeto).focus();
					fnAlert(lcmensaje, oConciliacion.lcTitulo, false, false, false);
					return false;

				}
				lcCodigo = lcCodigo.trim();
				var lcMedicamento = {Codigo: lcCodigo, Medicamento: lcMedicamentoN, Dosis: lnDosis, TipoDosis: lcTipoDosis, Frecuencia: lnFrecuencia, TipoFrecuencia: lcTipoFrecuencia, TipoVia: lcTipoVia, Conducta: lcConducta, Observa: lcObserva};
				var llverifica = oConciliacion.verificaRegistro(lcCodigo);

				if(llverifica) {
					oConciliacion.adicionarRegistroCM(lcMedicamento);
				}
				else{
					fnConfirm('El registro a ingresar ya existe. Desea modificarlo ?', oConciliacion.lcTitulo, false, false, false, function(){
					oConciliacion.modificarRegistroCM(lcMedicamento);});
				}
				
				if (lcMedicamento.Conducta !='Suspende'){
					oConciliacion.enviaConciliacionIngreso(lcMedicamento);
				}
				oConciliacion.dosisViaAdministracion();
			}
			else{
				fnAlert('No se puede adicionar, el paciente NO consume medicamentos', oConciliacion.lcTitulo, false, false, false);
				return;
			}
		}
		else{
			fnAlert('Indicar si el paciente consume o no medicamentos', oConciliacion.lcTitulo, false, false, false);
			return;
		}
	},

	// Modificación medicamento ya ingresado en la tabla
	modificarRegistroCM: function(camposRegistro)
	{
		oConciliacion.gotableMC.bootstrapTable('updateRow', {
			index: oConciliacion.indexedit,
			row: {
				CODIGO: camposRegistro.Codigo,
				MEDICA: camposRegistro.Medicamento,
				DOSIS: camposRegistro.Dosis,
				TIPODCOD: camposRegistro.TipoDosis,
				TIPOD: $("#selTipoDosis option[value="+camposRegistro.TipoDosis+"]").text(),
				FRECUENCIA: camposRegistro.Frecuencia,
				TIPOF: $("#selTipoFrecuencia option[value="+camposRegistro.TipoFrecuencia+"]").text(),
				TIPOCODF: camposRegistro.TipoFrecuencia,
				VIACOD: camposRegistro.TipoVia,
				VIA: $("#selTipoVia option[value="+camposRegistro.TipoVia+"]").text(),
				CONTINUA: camposRegistro.Conducta,
				OBSERVA: camposRegistro.Observa
			}
		 });
		oConciliacion.llModifica = true;
		oConciliacion.IniciaRegistroCM();
	},

	// Adiciona un nuevo registro a la tabla
	adicionarRegistroCM: function(camposRegistro)
	{
		oConciliacion.gotableMC.bootstrapTable('append', [
			{
				CODIGO: camposRegistro.Codigo,
				MEDICA: camposRegistro.Medicamento,
				DOSIS: camposRegistro.Dosis,
				TIPODCOD: camposRegistro.TipoDosis,
				TIPOD: $("#selTipoDosis option[value="+camposRegistro.TipoDosis+"]").text(),
				FRECUENCIA: camposRegistro.Frecuencia,
				TIPOF: $("#selTipoFrecuencia option[value="+camposRegistro.TipoFrecuencia+"]").text(),
				TIPOCODF: camposRegistro.TipoFrecuencia,
				VIACOD: camposRegistro.TipoVia,
				VIA: $("#selTipoVia option[value="+camposRegistro.TipoVia+"]").text(),
				CONTINUA: camposRegistro.Conducta,
				OBSERVA: camposRegistro.Observa
			}
		]);
		oConciliacion.llModifica = true;
		oConciliacion.IniciaRegistroCM();
	},

	enviaConciliacionIngreso: function(camposRegistro){
		if (camposRegistro.Conducta !='Suspende'){
			if (oConciliacion.esObjetoAmb['oAmbulatorio']){
				var aMedicamentos = {CODIGO: camposRegistro.Codigo, MEDICA: camposRegistro.Medicamento, DOSIS: camposRegistro.Dosis, TIPODCOD: camposRegistro.TipoDosis, 
				TIPOD: $("#selTipoDosis option[value="+camposRegistro.TipoDosis+"]").text(), 
				FRECUENCIA: camposRegistro.Frecuencia, TIPOF: $("#selTipoFrecuencia option[value="+camposRegistro.TipoFrecuencia+"]").text(), 
				TIPOCODF: camposRegistro.TipoFrecuencia, VIACOD: camposRegistro.TipoVia, 
				VIA: $("#selTipoVia option[value="+camposRegistro.TipoVia+"]").text(), OBSERVA: camposRegistro.Observa};
				oAmbulatorio.consultarConcialicionIngreso(aMedicamentos);
			}
		}
	},
	
	// Consultar conciliación
	ConsultarConciliacion: function(tnTipoConsulta,tnTipoDato)
	{
		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/Conciliacion.php",
			data: {TipoConsulta: tnTipoConsulta, TipoDoc: aDatosIngreso['cTipId'], NroDoc: aDatosIngreso['nNumId'], ingreso: aDatosIngreso['nIngreso']},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.DATOS != []) {
						oConciliacion.aMedicaInicial = toDatos.DATOS;
						oConciliacion.CargarConciliacion(toDatos);
						oConciliacion.aDatosConciliacionInicial = toDatos.DATOS.Medicamentos;
						if (tnTipoDato===1){
							oAmbulatorio.cargarDatosConcialicionIngreso();
						}
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda para conciliacion.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar conciliacion.");
		});
	},
	
	// Cargar conciliación en la tabla
	CargarConciliacion: function(taDatos)
	{
		var aMedica = [];
		var lcConsume = taDatos.DATOS.Consume
		oConciliacion.habilitarObjetos(lcConsume);
		if(lcConsume !==''){
			$('#selConsume').attr("disabled","disabled");
			$('#txtInformante').attr("disabled","disabled");
			$('#selInforma').attr("disabled","disabled");
			//$('#selNoConsume').attr("disabled","disabled");

			if(taDatos.DATOS.Habilita == true){
				$('#btnAdicionarM').attr("disabled",true);
				$('#txtInformante').attr("disabled",true);
				$('#selInforma').attr("disabled",true);
				//$('#selNoConsume').attr("disabled",true);
				oConciliacion.gotableMC.bootstrapTable('hideColumn', 'ACCIONES');
			}
		}else
		{
			$('#selConsume').removeAttr("disabled");
			//$('#selNoConsume').removeAttr("disabled");
		}

		$("#selConsume").val(lcConsume);
		var lnNoConsume = taDatos.DATOS.MotivoNC;
		$("#selInforma").val(taDatos.DATOS.Informa);
		$("#txtInformante").val(taDatos.DATOS.Informante);
		
		if (!(taDatos.DATOS.Medicamentos===undefined)){
			$.each(taDatos.DATOS.Medicamentos,function(lckey, loValor){
				loValor.MEDICA = loValor.CODIGO + ' - ' + loValor.MEDICA ;			
				aMedica.push(loValor);
			});
			oConciliacion.gotableMC.bootstrapTable('append', aMedica);
		}

		$('#selNoConsume').tiposMedica({
			tipo:'NoConsume',
			functionPost: function () {
				$("#selNoConsume").val(lnNoConsume);
			}
		});
	},

	// funcion que edita elemento seleccionado
	editarRegistroCM: function(arow)
	{
		if(arow.CODIGO.substring(0,2)=='NC'){
			$("#txtMedicamentoNC").val(arow.MEDICA);
			$("#cMedicamentoConc,#cCodigoMedicamentoConc,#cDescripcionMedicamentoConc").val();
		}else{
			$("#cMedicamentoConc,#txtMedicamentoNC").val('');
			$("#cCodigoMedicamentoConc").val(arow.CODIGO);
			$("#cDescripcionMedicamentoConc").val(arow.MEDICA);

		}
		oConciliacion.gcCodigoMedicamento = arow.CODIGO;
		$("#txtDosis").val(arow.DOSIS);
		$("#selTipoDosis").val(arow.TIPODCOD);
		$("#txtFrecuencia").val(arow.FRECUENCIA);
		$("#selTipoFrecuencia").val(arow.TIPOCODF);
		$("#selTipoVia").val(arow.VIACOD);
		$("#selTipoConducta").val(arow.CONTINUA);
		$("#edtObserva").val(arow.OBSERVA);
	},

	// Función que vacia los elementos del registro
	IniciaRegistroCM: function(tids)
	{
		oConciliacion.gcCodigoMedicamento = '';
		$("#txtMedicamentoNC,#cMedicamentoConc,#cCodigoMedicamentoConc,#cDescripcionMedicamentoConc").val('');
		$("#txtDosis").val(0);
		$("#selTipoDosis").val('');
		$("#txtFrecuencia").val(0);
		$("#selTipoFrecuencia").val('');
		$("#selTipoVia").val('');
		$("#selTipoConducta").val('');
		$("#edtObserva").val('');
		$('#lblMedicamentoNC').addClass("required");
		$('#lblMedicamentoC').addClass("required");
		$('#txtMedicamentoNC').removeAttr("disabled");
		$('#cMedicamentoConc').removeAttr("disabled");
		$('#cMedicamentoConc').focus();
	},

	//Función que verifica si el medicamento a ingresar ya existe
	verificaRegistro: function(tcMedicamento)
	{
		var TablaMedica = oConciliacion.gotableMC.bootstrapTable('getData');
		var llRetorno = true;
			if(TablaMedica != ''){
				$.each(TablaMedica, function( lcKey, loTipo ) {
					if(loTipo['CODIGO']==tcMedicamento){
						oConciliacion.indexedit = lcKey;
						llRetorno = false;
					}
				});
			};

		return llRetorno;
	},

	// Valida si existen elementos de la tabla y muestra mensaje de alerta de conciliacion
	validarTabla: function()
	{
		var lcConsume = $("#selConsume").val();
		if (lcConsume != ''){

			oConciliacion.habilitarObjetos(lcConsume);
			if (oConciliacion.llmostrarMensaje){
				oConciliacion.llmostrarMensaje = false;
				lcMensaje = 'EN CASO DE QUE EL PACIENTE PARTICIPE EN UN ESTUDIO DE INVESTIGACIÓN, ';
				lcMensaje+= 'NO OLVIDE DILIGENCIAR LA INFORMACIÓN DEL MEDICAMENTO EN ESTUDIO E INFORMAR AL MEDICO INVESTIGADOR DE LA PRESENCIA DEL PACIENTE EN EL SERVICIO';
				fnAlert(lcMensaje, oConciliacion.lcTitulo, false, false, 'medium');
			}

			if (lcConsume=='No'){
				$("#selNoConsume").val('');
				$('#lblNoConsume').addClass("required");
				var TablaMedica = oConciliacion.gotableMC.bootstrapTable('getData');
				if(TablaMedica != ''){
					fnConfirm('Existen medicamentos en la conciliación. Con esta acción los eliminará. Desea Continuar ?', false, false, false, false, function(){
					oConciliacion.gotableMC.bootstrapTable('removeAll')},function(){$("#selConsume").val('Si'); oConciliacion.habilitarObjetos('Si'); });
				}
			}else{
				$('#lblNoConsume').removeClass("required");
			}
		} 

	},

	// Función que habilita/inhabilita objetos y pone el asterisco
	habilitarObjetos: function(tcConsume)
	{
		if(tcConsume == 'No' ){
			$("#divNoConsume").show();				
			$('#lblMedicamentoNC').removeClass("required");
			$('#lblMedicamentoC').removeClass("required");
			$('#lblMedicamentoC').removeClass("required");
			$('#lblTipoDosis').removeClass("required");
			$('#lblFrecuencia').removeClass("required");
			$('#lblTipoVia').removeClass("required");
			$('#lblConducta').removeClass("required");
			$('#txtMedicamentoNC').attr("disabled","disabled");
			$('#cMedicamentoConc').attr("disabled","disabled");
			$('#txtDosis').attr("disabled","disabled");
			$('#selTipoDosis').attr("disabled","disabled");
			$('#txtFrecuencia').attr("disabled","disabled");
			$('#selTipoFrecuencia').attr("disabled","disabled");
			$('#selTipoVia').attr("disabled","disabled");
			$('#selTipoConducta').attr("disabled","disabled");
			$('#edtObserva').attr("disabled","disabled");
		}
		else{
			$("#divNoConsume").hide();	
			$('#lblMedicamentoNC').addClass("required");
			$('#lblMedicamentoC').addClass("required");
			$('#lblTipoDosis').addClass("required");
			$('#lblFrecuencia').addClass("required");
			$('#lblTipoVia').addClass("required");
			$('#lblConducta').addClass("required");
			$('#txtMedicamentoNC').removeAttr("disabled");
			$('#cMedicamentoConc').removeAttr("disabled");
			$('#txtDosis').removeAttr("disabled");
			$('#selTipoDosis').removeAttr("disabled");
			$('#txtFrecuencia').removeAttr("disabled");
			$('#selTipoFrecuencia').removeAttr("disabled");
			$('#selTipoVia').removeAttr("disabled");
			$('#selTipoConducta').removeAttr("disabled");
			$('#edtObserva').removeAttr("disabled");
		}
	},

	// Función que valida si el medicamento se encuentra en el listado
	validarMedicamentoC: function(tcMedicamento)
	{
		if(tcMedicamento !=''){

			var lnidx = oConciliacion.ListaMedicamentos[tcMedicamento];
			if(lnidx===undefined){
				return false
			}
			return true
		}
	},

	// Modifica el valor de la frecuencia a 1(uno) si eligen dosis unica o infusión continua
	validarTFrecuencia: function()
	{
		if($("#selTipoFrecuencia").val() !=1 ){
			$("#txtFrecuencia").val(1);
		}
	},

	// Coloca '' en tipo de frecuencia al elegir dato mayor a uno y que tenga dosis unica o infusión continua
	validarFrecuencia: function()
	{
		if($("#txtFrecuencia").val() > 1 && $("#selTipoFrecuencia").val() !=1){
			$("#selTipoFrecuencia").val('');
		}
	},

	// Cuando el medicamento es No codificado se le adiciona un código
	adicionarCodigo: function()
	{
		var lcCodigo = 'NC'+((~~(Math.random() * 1000)).toString()).padStart(3, "0");
		return lcCodigo;
	},

	validarObjeto: function(tcObjetoVal, tcObjetoHab, tclabelHab)
	{
		if ($("#"+tcObjetoVal).val().length === 0){
			$("#"+tclabelHab).addClass("required");
			$("#"+tcObjetoHab).val('');
			$("#"+tcObjetoHab).removeAttr("disabled");
		}
		else{
			$("#"+tcObjetoHab).attr("disabled","disabled");
			$("#"+tclabelHab).removeClass("required");
		}
	},

	IniciarTabla: function()
	{
		$('#tblMedica').bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			locale: 'es-ES',
			undefinedText: 'N/A',
			toolbar: '#toolBarLst',
			height: '400',
			pagination: false,
			iconSize: 'sm',
			columns: [
				{
					title: 'Medicamento',
					field: 'MEDICA',
					sortable: true
				},{
					title: 'Dosis',
					field: 'DOSIS',
					formatter: this.formatoDosis,
					sortable: true
				},{
					title: 'Frecuencia',
					field: 'FRECUENCIA',
					formatter: this.formatoFrecuencia
				},{
					title: 'Vía Administración',
					field: 'VIA'
				},{
					title: 'Conducta',
					field: 'CONTINUA'
				},{
					title: 'Observaciones',
					field: 'OBSERVA'
				},{
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
		'click .editarCM': function (e, value, row, index) {
			oConciliacion.editarRegistroCM(row);
		},
		'click .borrarCM': function (e, value, row, index) {
			fnConfirm('Desea eliminar el registro ?', false, false, false, false, function(){
				oConciliacion.gotableMC.bootstrapTable('remove', {
					field: 'MEDICA',
					values: row.MEDICA
				});
				oConciliacion.llModifica = true;
				if (oConciliacion.esObjetoAmb['oAmbulatorio']){
					oAmbulatorio.eliminarConciliacion(row.CODIGO);
				}

			},'');
		}
	},
	
	formatoDosis: function(tcValor, toFila)
	{
		return toFila.DOSIS + ' ' + toFila.TIPOD;
	},
	
	formatoFrecuencia: function(tcValor, toFila)
	{
		return toFila.FRECUENCIA + ' ' + toFila.TIPOF;
	},
	
	formatoAccion: function()
	{
		return	'<a class="editarCM" href="javascript:void(0)" title="Editar"><i class="fas fa-pencil-alt"></i></a> '+
				'<a class="borrarCM" href="javascript:void(0)" title="Eliminar"><i class="fas fa-trash-alt" style="color:#E96B50"></i></a>';
	},

	formatoBorrar: function()
	{
		
	},

	// VALIDACION PRINCIPAL
	validacion: function()
	{
		var lbValido = true;

		if($("#selConsume").val()=='Si' && oConciliacion.gotableMC.bootstrapTable('getData')==''){
			this.lcMensajeError = 'No ha ingresado medicamentos en la conciliación';
			lbValido = false;
		}

		return lbValido;
	},

	obtenerDatos: function()
	{
		if(oConciliacion.aMedicaInicial.Habilita){
			return {};
		}
		//serialización de datos dentro de laDatos
		var TablaMedica = oConciliacion.gotableMC.bootstrapTable('getData');
		var laDatosFinal = {Consume: $("#selConsume").val(), Informa: $("#selInforma").val(), Informante: $("#txtInformante").val(), Medicamentos:TablaMedica};

		delete oConciliacion.aMedicaInicial.Habilita;
		var laMedicamentos=[];
		$.each(oConciliacion.aMedicaInicial.Medicamentos, function(lnIndice, loMed){
			laMedicamentos.push(loMed);
		});

		oConciliacion.aMedicaInicial.Medicamentos = laMedicamentos;
		var llCompare = !(compareObj(laDatosFinal, oConciliacion.aMedicaInicial)) || oConciliacion.llModifica ;
		var laDatos = {Modifica: llCompare, Consume: $("#selConsume").val(), Informa: $("#selInforma").val(), Informante: $("#txtInformante").val(), Medicamentos:TablaMedica,NoConsume: $("#selNoConsume").val()};

		return laDatos;
	}
}
