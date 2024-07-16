var oModalMedicamentoCTC = {
	sinConsultar: true,
	fnEjecutar: false,
	ListaMedicamentos: {},
	gcTipoOrden:'', gaDatosIniciales:'',
	lcTitulo: 'Justificación Medicamentos NO POS',
	lcMensaje: '',
	taObjetosMedCTC: {
					1: '#buscarMedicaP',
					2: '#txtDosisP',
					3: '#selTipoDosisP',
					4: '#txtFrecuenciaP',
					5: '#selTipoFrecuenciaP',
					6: '#txtDosisDP',
					7: '#selTipoDosisDP',
					8: '#txtTTratamientoP',
					9: '#selTTratamientoP',
					10: '#txtCantP',
					11: '#txtTipoCantidadP',
					12: '#selTipoViaP'
				},

	inicializar: function()
	{
		this.CargarMedicamentosP();
		$('#selTipoDosisNP').tiposMedica({tipo: "Dosis"});
		$('#selTipoDosisDNP').tiposMedica({tipo: "Dosis"});
		$('#selTipoDosisP').tiposMedica({tipo: "Dosis"});
		$('#selTipoDosisDP').tiposMedica({tipo: "Dosis"});
		$('#selTipoFrecuenciaNP').tiposMedica({tipo:'Frecuencia'});
		$('#selTipoFrecuenciaP').tiposMedica({tipo:'Frecuencia'});
		$('#selTipoViaNP').tiposMedica({tipo:'Via'});
		$('#selTipoViaP').tiposMedica({tipo:'Via'});
		$('#RiesgoI').tiposRiesgo({tipo:'TiposRiesgo'});

		$('#chkExistePOS').on("change",function(){oModalMedicamentoCTC.habilitarPos()});
		$('#btnGuardaMedCTC').on('click', function(){oModalMedicamentoCTC.validarEnvioCTC()});
		$('#btnCancelaMedCTC').on('click', function(){oModalMedicamentoCTC.cancelarMedicamentoCTC()});

		laObjCTC = oModalMedicamentoCTC.taObjetosMedCTC ;
		$('#selTipoFrecuenciaP').on("change",function(){oAmbulatorio.validarTFrecuenciaAmb(laObjCTC)});
		$('#txtDosisP').on("change",function(){oAmbulatorio.validarTFrecuenciaAmb(laObjCTC)});
		$('#txtFrecuenciaP').on('change',function(){oAmbulatorio.validarFrecuenciaAmb(laObjCTC)});
		$('#txtTTratamientoP').on('change',function(){oAmbulatorio.calculaCantidadTotal(laObjCTC)});
		$('#selTipoDosisP').on('change',function(){$(laObjCTC[7]).val($(laObjCTC[3]).val())});
		$('#selTipoDosisDP').on('change',function(){$(laObjCTC[3]).val($(laObjCTC[7]).val())});

		oModalMedicamentoCTC.habilitarPos();
		oModalMedicamentoCTC.CargarReglas();

	},

	// Valida cada uno de los medicamentos a ingresar
	validarEnvioCTC: function()
	{
		if (oModalMedicamentoCTC.validarFormasCTC()) {
			oAmbulatorio.insertarMedicamentoCTC();
			$("#divMedicamentosCTC").modal("hide");
		}
	},

	// Valida cada uno de los medicamentos a ingresar
	validarFormasCTC: function()
	{
		if (! $('#FormMedicamentoCTC1').valid()){
			ubicarObjeto('#FormMedicamentoCTC1');
			return false;
		}

		if (! oModalMedicamentoCTC.validarPOSCTC()){
			return false;
		}

		if (! $('#FormMedicamentoCTC3').valid()){
			ubicarObjeto('#FormMedicamentoCTC3');
			return false;
		}
		return true;
	},

	validarPOSCTC: function()
	{
		var llRetorno = true;

		if($(chkExistePOS).prop('checked')){

			laObjeto = oModalMedicamentoCTC.taObjetosMedCTC;
			var lnDosis = $("#txtDosisP").val();
			var lcTipoDosis = $("#selTipoDosisP").val();
			var lnFrecuencia = $("#txtFrecuenciaP").val();
			var lcTipoFrecuencia = $("#selTipoFrecuenciaP").val();
			var lnDosisDiaria = $("#txtDosisDP").val();
			var lcTipoDosisDiaria = $("#selTipoDosisDP").val();
			var lnTiempo = $("#txtTTratamientoP").val();
			var lcTipoTiempo = $("#selTTratamientoP").val();
			var lnCantidad = $("#txtCantP").val();
			var lcTipoCantidad = $("#txtTipoCantidadP").val();
			var lcTipoVia = $("#selTipoViaP").val();
			var lcMedicamentoPOS = $("#buscarMedicaP").val();
			var lcCodigo = ($.trim(lcMedicamentoPOS.substr(0,10)));
			var llRetorno = oModalMedicamentoCTC.validarMedicamentoPosCTC(lcMedicamentoPOS);

			if(!llRetorno){
				return false;
			}

			if (lcMedicamentoPOS=='' || lnDosis<=0 || lnDosis>9999999 || lcTipoDosis==0 || lnFrecuencia<=0 || lnFrecuencia>99 || lcTipoFrecuencia=='' || lnDosisDiaria<=0 || lnDosisDiaria>9999999 || lcTipoDosisDiaria=='' || lnTiempo<=0 || lnTiempo>999 || lcTipoTiempo=='' || lnCantidad<=0 || lnCantidad>999 || lcTipoCantidad=='' || lcTipoVia=='' ){

				var lcObjeto = '';
				var lcmensaje = 'Campos Obligatorios:<br>';

				lcmensaje+=lcMedicamentoPOS==''?'Medicamento,<br> ':'';
				lcObjeto = lcMedicamentoPOS=='' && lcObjeto==''?laObjeto[1]:lcObjeto;
				lcmensaje+=(lnDosis<=0 || lnDosis>9999999)?'Dosis,<br> ':'';
				lcObjeto = (lnDosis<=0 || lnDosis>9999999) && lcObjeto==''?laObjeto[2]:lcObjeto;
				lcmensaje+=lcTipoDosis==''?'Tipo de Dosis,<br>':'';
				lcObjeto = lcTipoDosis=='' && lcObjeto==''?laObjeto[3]:lcObjeto;
				lcmensaje+=(lnFrecuencia<=0 || lnFrecuencia>99)?'Frecuencia,<br>':'';
				lcObjeto = (lnFrecuencia<=0 || lnFrecuencia>99) && lcObjeto==''?laObjeto[4]:lcObjeto;
				lcmensaje+=lcTipoFrecuencia==0?'Tipo de Frecuencia,<br>':'';
				lcObjeto = lcTipoFrecuencia==0 && lcObjeto==''?laObjeto[5]:lcObjeto;
				lcmensaje+=(lnDosisDiaria<=0 || lnDosisDiaria>9999999)?'Dosis Diaria,<br> ':'';
				lcObjeto = (lnDosisDiaria<=0 || lnDosisDiaria>9999999) && lcObjeto==''?laObjeto[6]:lcObjeto;
				lcmensaje+=lcTipoDosisDiaria==''?'Tipo de Dosis Diaria,<br>':'';
				lcObjeto = lcTipoDosisDiaria=='' && lcObjeto==''?laObjeto[7]:lcObjeto;
				lcmensaje+=(lnTiempo<=0 || lnTiempo>999)?'Tiempo de Tratamiento,<br> ':'';
				lcObjeto = (lnTiempo<=0 || lnTiempo>999) && lcObjeto==''?laObjeto[8]:lcObjeto;
				lcmensaje+=lcTipoTiempo==''?'Tipo de Tiempo de Tratamiento,<br>':'';
				lcObjeto = lcTipoTiempo=='' && lcObjeto==''?laObjeto[9]:lcObjeto;
				lcmensaje+=(lnCantidad<=0 || lnCantidad>999)?'Cantidad,<br> ':'';
				lcObjeto = (lnCantidad<=0 || lnCantidad>999) && lcObjeto==''?laObjeto[10]:lcObjeto;
				lcmensaje+=lcTipoCantidad==''?'Unidad de Cantidad,<br>':'';
				lcObjeto = lcTipoCantidad=='' && lcObjeto==''?laObjeto[11]:lcObjeto;
				lcmensaje+=lcTipoVia==''?'Vía administración.<br>':'';
				lcObjeto = lcTipoVia=='' && lcObjeto==''?laObjeto[12]:lcObjeto;

				$(lcObjeto).focus();
				fnAlert(lcmensaje, oModalMedicamentoCTC.lcTitulo, false, false, false);
				return false;

			}
		}
		return llRetorno;
	},

	validarMedicamentoPosCTC: function(tcMedicamentoPOS)
	{
		var lcMedicamentoPOS = tcMedicamentoPOS;
		var lcCodigo = ($.trim(lcMedicamentoPOS.substr(0,10)));

		if(lcMedicamentoPOS != ''){
			var lnidx = oModalMedicamentoCTC.ListaMedicamentos[lcMedicamentoPOS];
			if(lnidx===undefined){
				lcmensaje = 'Error en el medicamento codificado, Revise por favor';
				$("#buscarMedicaP").focus();
				fnAlert(lcmensaje, oModalMedicamentoCTC.lcTitulo, false, false, false);
				oModalMedicamentoCTC.IniciaMedicaPOS();
				return false;
			}

			if ($.inArray(lcCodigo, oAmbulatorio.aMedicaNOPOS)>=0){
				lcmensaje = 'El medicamento es NO POS, seleccione otro medicamento';
				$("#PresentaMedP").val('');
				$("#ConcentraMedP").val('');
				$("#UnidadMedP").val('');
				$('#txtTipoCantidadP').val('');
				$("#buscarMedicaP").focus();
				fnAlert(lcmensaje, oModalMedicamentoCTC.lcTitulo, false, false, false);
				return false;
			}
			var laMedica={};
			oModalMedicamentoCTC.ConsultarMedica(laMedica, lcCodigo, 'POS');
			return true;
		}else{
			lcmensaje = 'Medicamento POS obligatorio, Revise por favor';
			$("#buscarMedicaP").focus();
			fnAlert(lcmensaje, oModalMedicamentoCTC.lcTitulo, false, false, false);
			return false;
		}
	},

	iniciaModalMedicamentoCTC: function(taMedicaExiste, taMedicamento, tlConsultar)
	{
		oModalMedicamentoCTC.gaDatosIniciales=taMedicaExiste;
		oModalMedicamentoCTC.inicializaMedicametoCTC();
		$("#chkPaciente").prop('checked', true);
		$("#chkExistePOS").prop('checked', false);
		oModalMedicamentoCTC.habilitarPos();
		if(tlConsultar){
			oModalMedicamentoCTC.ConsultarMedica(taMedicamento, taMedicamento.Codigo, 'NOPOS');
		}else{
			oModalMedicamentoCTC.cargarMedicamento(taMedicaExiste, taMedicamento, 'EXISTE');
		}

		// Activar controles para No Codificados
		$("#GrupoT,#TiempoR,#Invima,#edtEfectoNP,#edtEfectoSNP").attr("readonly",taMedicamento.Codigo.substr(0,2)!=='NC');

		oModalMedicamentoCTC.mostrar();
	},

	mostrar: function()
	{
		$("#divMedicamentosCTC").modal('show');
		oModalMedicamentoCTC.fnEjecutar = true;
	},

	ConsultarMedica: function(taMedicamento, tcCodigoMedicamento, tcTipo)
	{
		if (tcCodigoMedicamento.substr(0,2)=='NC') {
			oModalMedicamentoCTC.cargarMedicamento({
				DESMED: tcCodigoMedicamento.Medicamento,
				PRESE : $("#txtTextoCantidadAmb").val(),
				CONCE : $("#txtDosisAmb").val(),
				UNIDA : $("#selTipoDosisAmb option:selected").text(),
				GTEJUS: '',
				TIMJUS: '',
				RGIJUS: '',
				EFEJUS: '',
				ESRJUS: '',
				BIBJUS: ''
			}, taMedicamento, tcTipo);
			return;
		}

		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/modalCTC.php",
			data: {tipoDato: "Medicamento", Codigo: tcCodigoMedicamento},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.DATOS != []) {
						oModalMedicamentoCTC.aMedicaNOPOS = toDatos.DATOS;
						oModalMedicamentoCTC.cargarMedicamento(toDatos.DATOS, taMedicamento, tcTipo);
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda para Medicamento.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar Medicamento.');
		});
	},

	habilitarPos: function()
	{
		if($(chkExistePOS).prop('checked')){

			$('#lblPOS').addClass("required");
			$('#lblTipoDosisP').addClass("required");
			$('#lblFrecuenciaP').addClass("required");
			$('#lblTipoDosisDP').addClass("required");
			$('#lblTTratamientoP').addClass("required");
			$('#lblCantP').addClass("required");
			$('#lblViaP').addClass("required");

			$('#buscarMedicaP').attr("disabled",false);
			$('#txtDosisP').attr("disabled",false);
			$('#selTipoDosisP').attr("disabled",false);
			$('#txtFrecuenciaP').attr("disabled",false);
			$('#selTipoFrecuenciaP').attr("disabled",false);
			$('#txtDosisDP').attr("disabled",false);
			$('#selTipoDosisDP').attr("disabled",false);
			$('#txtTTratamientoP').attr("disabled",false);
			$('#selTTratamientoP').attr("disabled",false);
			$('#txtCantP').attr("disabled",false);
			$('#txtTipoCantidadP').attr("disabled",false);
			$('#selTipoViaP').attr("disabled",false);
			$('#buscarMedicaP').focus();

		}else{

			$('#lblPOS').removeClass("required");
			$('#lblTipoDosisP').removeClass("required");
			$('#lblFrecuenciaP').removeClass("required");
			$('#lblTipoDosisDP').removeClass("required");
			$('#lblTTratamientoP').removeClass("required");
			$('#lblCantP').removeClass("required");
			$('#lblViaP').removeClass("required");

			$('#buscarMedicaP').attr("disabled","disabled");
			$('#txtDosisP').attr("disabled","disabled");
			$('#selTipoDosisP').attr("disabled","disabled");
			$('#txtFrecuenciaP').attr("disabled","disabled");
			$('#selTipoFrecuenciaP').attr("disabled","disabled");
			$('#txtDosisDP').attr("disabled","disabled");
			$('#selTipoDosisDP').attr("disabled","disabled");
			$('#txtTTratamientoP').attr("disabled","disabled");
			$('#selTTratamientoP').attr("disabled","disabled");
			$('#txtCantP').attr("disabled","disabled");
			$('#txtTipoCantidadP').attr("disabled","disabled");
			$('#selTipoViaP').attr("disabled","disabled");
			oModalMedicamentoCTC.IniciaMedicaPOS();
		}
	},

	IniciaMedicaPOS: function()
	{
		$("#buscarMedicaP").val('');
		$("#PresentaMedP").val('');
		$("#ConcentraMedP").val('');
		$("#UnidadMedP").val('');
		$("#txtDosisP").val(0);
		$("#selTipoDosisP").val('');
		$("#txtFrecuenciaP").val(0);
		$("#selTipoFrecuenciaP").val('');
		$("#txtDosisDP").val(0);
		$("#selTipoDosisDP").val('');
		$("#txtTTratamientoP").val('');
		$('#selTTratamientoP').val('');
		$('#txtCantP').val(0);
		$('#txtTipoCantidadP').val('');
		$('#selTipoViaP').val('');
	},

	CargarReglas: function()
	{
		loObjObl = {
			'#FormMedicamentoCTC1':[
				{OBJETO: "lblTipoDosisNP", REGLAS: '{"DosisNP": {"required": true}}', CLASE:"1"},
				{OBJETO: "lblTipoDosisNP", REGLAS: '{"TdosisNP": {"required": true}}', CLASE:"1"},
				{OBJETO: "lblFrecuenciaNP", REGLAS: '{"FrecuenciaNP": {"required": true}}', CLASE:"1"},
				{OBJETO: "lblFrecuenciaNP", REGLAS: '{"TFrecuencia": {"required": true}}', CLASE:"1"},
				{OBJETO: "lblTipoDosisDNP", REGLAS: '{"DosisDNP": {"required": true}}', CLASE:"1"},
				{OBJETO: "lblTipoDosisDNP", REGLAS: '{"TdosisDNP": {"required": true}}', CLASE:"1"},
				{OBJETO: "lblTTratamientoNP", REGLAS: '{"TTratamientoNP": {"required": true}}', CLASE:"1"},
				{OBJETO: "lblCantNP", REGLAS: '{"CantidadNP": {"required": true}}', CLASE:"1"},
				{OBJETO: "lblCantNP", REGLAS: '{"TcantidadNP": {"required": true}}', CLASE:"1"},
				{OBJETO: "lblViaNP", REGLAS: '{"ViaNP": {"required": true}}', CLASE:"1"},
				{OBJETO: "lblRiesgoI", REGLAS: '{"RiesgoI": {"required": true}}', CLASE:"1"},
				{OBJETO: "lblResumenNP", REGLAS: '{"ResumenNP": {"required": true}}', CLASE:"1"}
			],
			'#FormMedicamentoCTC3':[
				{OBJETO: "lblBibliografiaNP", REGLAS: '{"Bibliografia": {"required": true}}', CLASE:"1"}
			]
		};

		var lopciones={};
		$.each(loObjObl, function( lcKeyForma, loOpciones ) {
			$.each(loOpciones, function( lcKey, loObj ) {

				if(loObj['CLASE']=="1" || loObj['CLASE']=="3" ){
					lopciones=Object.assign(lopciones,JSON.parse(loObj['REGLAS']));
				} else {
					var loTemp = loObj['REGLAS'].split('¤');
					lopciones[loTemp[0]]={required: function(element){
						return ReglaDependienteValor(loTemp[1],loTemp[2],loDatos.REGLAS[lcKey]['OBJETO']);
					}};
					if(loTemp.length==4){
						lopciones[loTemp[0]]=Object.assign(lopciones[loTemp[0]],JSON.parse(loTemp[3]));
					}
				}

				if(loObj['CLASE']=="1" || loObj['CLASE']=="2" ){
					$('#'+loObj['OBJETO']).addClass("required");
				}

			});
			oModalMedicamentoCTC.ValidarReglas(lcKeyForma, lopciones);
		});
	},

	ValidarReglas: function(tcForma, aOptions)
	{
		$( tcForma ).validate( {
			rules: aOptions,
			errorElement: "div",
			errorPlacement: function ( error, element ) {
				// Agregue la clase `help-block` al elemento de error
				error.addClass( "invalid-tooltip" );

				if ( element.prop( "type" ) === "checkbox" ) {
					error.insertAfter( element.parent( "label" ) );
				} else {
					error.insertAfter( element );
				}
			},
			highlight: function ( element, errorClass, validClass ) {
				$( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
			},
			unhighlight: function (element, errorClass, validClass) {
				$( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
			},
		} );
	},

	ubicarObjetoCTC: function(toForma, tcObjeto)
	{
		tcObjeto = typeof tcObjeto === 'string'? tcObjeto: false;
		if (tcObjeto===false) {
			var formerrorList = $(toForma).data('validator').errorList,
			lcObjeto = formerrorList[0].element.id;
			$('#'+lcObjeto).focus();

		} else {
			$(tcObjeto).focus();
		}
	},

	// Cargar medicamentos de lista
	CargarMedicamentosP: function()
	{
		$.ajax({
			type: "POST",
			url: "vista-comun/ajax/Autocompletar.php",
			data: {tipoDato: 'Medicamentos'},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					oModalMedicamentoCTC.ListaMedicamentos = toDatos.datos;
					oModalMedicamentoCTC.autocompletar();
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de tipos de Medicamento para conciliacion.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar tipos de medicamento para conciliacion.");
		});
	},

	// función utilizada para la busqueda de medicamentos
	autocompletar: function()
	{
		$('#buscarMedicaP').autocomplete({
			source: oModalMedicamentoCTC.ListaMedicamentos,
			maximumItems: 30,
			highlightClass: 'text-danger',
			onSelectItem: function(tcValor, tcLabel){
				oModalMedicamentoCTC.validarMedicamentoPosCTC(tcValor['label']);
			}
		});
	},

	cargarMedicamento: function(taMedica, taMedicaAmb, tcTipo)
	{
		switch(tcTipo){
			case 'NOPOS':
				var lcTcantidadNP = taMedicaAmb.CantidadTratamiento!='' ? taMedicaAmb.CantidadTratamiento : taMedica.PRESE;
				$("#NOPOS").val(taMedica.DESMED);
				$("#PresentaMed").val(taMedica.PRESE);
				$("#ConcentraMed").val(taMedica.CONCE);
				$("#UnidadMed").val(taMedica.UNIDA);
				$("#DosisNP").val(taMedicaAmb.Dosis);
				$("#selTipoDosisNP").val(taMedicaAmb.TipoDosis);
				$("#FrecuenciaNP").val(taMedicaAmb.Frecuencia);
				$("#selTipoFrecuenciaNP").val(taMedicaAmb.TipoFrecuencia);
				$("#DosisDNP").val(taMedicaAmb.Dosisdiaria);
				$("#selTipoDosisDNP").val(taMedicaAmb.TipoDosisdiaria);
				$("#TTratamientoNP").val(taMedicaAmb.TiempoTratamiento);
				$("#selTTratamiento").val(taMedicaAmb.TipoTiempoTratamiento);
				$("#CantNP").val(taMedicaAmb.Cantidad);
				$("#TcantidadNP").val(lcTcantidadNP);
				$("#selTipoViaNP").val(taMedicaAmb.TipoVia);
				$("#GrupoT").val(taMedica.GTEJUS);
				$("#TiempoR").val(taMedica.TIMJUS);
				$("#Invima").val(taMedica.RGIJUS);
				$("#edtEfectoNP").val(taMedica.EFEJUS);
				$('#edtEfectoSNP').val(taMedica.ESRJUS);
				$('#edtBibliografiaNP').val(taMedica.BIBJUS);
				break;

			case 'POS':
				$("#PresentaMedP").val(taMedica.PRESE);
				$("#ConcentraMedP").val(taMedica.CONCE);
				$("#UnidadMedP").val(taMedica.UNIDA);
				$("#txtTipoCantidadP").val(taMedica.PRESE);
				$("#TipoCantidadP").val(taMedica.PRESE);
				$("#selTTratamientoP").val('DIAS');
				break;

			case 'EXISTE':
				if (taMedica.MEDICAP == ''){
					$("#chkExistePOS").prop('checked', false);
				}else {$("#chkExistePOS").prop('checked', true);}
				oModalMedicamentoCTC.habilitarPos();

				$("#NOPOS").val(taMedicaAmb.Medicamento);
				$("#PresentaMed").val(taMedica.PRESENTANP);
				$("#ConcentraMed").val(taMedica.CONCENTRANP);
				$("#UnidadMed").val(taMedica.UNIDADNP);
				$("#DosisNP").val(taMedicaAmb.Dosis);
				$("#selTipoDosisNP").val(taMedicaAmb.TipoDosis);
				$("#FrecuenciaNP").val(taMedicaAmb.Frecuencia);
				$("#selTipoFrecuenciaNP").val(taMedicaAmb.TipoFrecuencia);
				$("#DosisDNP").val(taMedicaAmb.Dosisdiaria);
				$("#selTipoDosisDNP").val(taMedicaAmb.TipoDosisdiaria);
				$("#TTratamientoNP").val(taMedicaAmb.TiempoTratamiento);
				$("#selTTratamiento").val(taMedicaAmb.TipoTiempoTratamiento);
				$("#CantNP").val(taMedicaAmb.Cantidad);
				$("#TcantidadNP").val(taMedicaAmb.CantidadTratamiento);
				$("#selTipoViaNP").val(taMedicaAmb.TipoVia);
				$("#GrupoT").val(taMedica.GRUPOTNP);
				$("#TiempoR").val(taMedica.TIEMPOTNP);
				$("#RiesgoI").val(taMedica.RIESGOINP);
				$("#Invima").val(taMedica.INVIMANP);
				$("#ResumenNP").val(taMedica.RESUMENNP);
				$("#edtEfectoNP").val(taMedica.EFECTO);
				$('#edtEfectoSNP').val(taMedica.EFECTOS);
				$('#edtBibliografiaNP').val(taMedica.BIBLIOGRAFIA);
				$("#buscarMedicaP").val(taMedica.MEDICAP);
				$("#PresentaMedP").val(taMedica.PRESENTAP);
				$("#ConcentraMedP").val(taMedica.CONCENTRAP);
				$("#UnidadMedP").val(taMedica.UNIDADP);
				$("#txtDosisP").val(taMedica.DOSISP);
				$("#selTipoDosisP").val(taMedica.TIPODOSISP);
				$("#txtFrecuenciaP").val(taMedica.FRECUENCIAP);
				$("#selTipoFrecuenciaP").val(taMedica.TFRECUENCIAP);
				$("#txtDosisDP").val(taMedica.DOSISDIAP);
				$("#selTipoDosisDP").val(taMedica.TIPODOSISDIAP);
				$("#txtTTratamientoP").val(taMedica.TRATAMIENTOP);
				$("#selTTratamientoP").val(taMedica.TIPOTRATAMP);
				$("#txtCantP").val(taMedica.CANTIDADP);
				$("#txtTipoCantidadP").val(taMedica.CANTIDADTRAT);
				$("#selTipoViaP").val(taMedica.VIAP);
				break;
		}
	},

	inicializaMedicametoCTC: function()
	{
		$("#NOPOS").val('');
		$("#PresentaMed").val('');
		$("#ConcentraMed").val('');
		$("#UnidadMed").val('');
		$("#DosisNP").val(0);
		$("#selTipoDosisNP").val('');
		$("#FrecuenciaNP").val(0);
		$("#selTipoFrecuenciaNP").val('');
		$("#DosisDNP").val(0);
		$("#selTipoDosisDNP").val('');
		$("#TTratamientoNP").val(0);
		$("#selTTratamiento").val('');
		$("#CantNP").val(0);
		$("#TcantidadNP").val('');
		$("#selTipoViaNP").val('');
		$("#GrupoT").val('');
		$("#TiempoR").val('');
		$("#RiesgoI").val('');
		$("#Invima").val('');
		$("#ResumenNP").val('');

		oModalMedicamentoCTC.IniciaMedicaPOS()

		$("#edtEfectoNP").val('');
		$('#edtEfectoSNP').val('');
		$('#edtBibliografiaNP').val('');
	},

	cancelarMedicamentoCTC: function () {
		fnConfirm('Desea cancelar la Justificación del Medicamento?', oModalMedicamentoCTC.lcTitulo, false, false, false,
			{
				text: 'Si',
				action: function(){
					oModalMedicamentoCTC.inicializaMedicametoCTC();
					$('#divMedicamentosCTC').modal('hide');
				}
			},
			{
				text: 'No',
				action: function(){
					$('#divMedicamentosCTC').modal('show');
				}
			}
		);
	},

	obtenerDatos: function()
	{
		//serialización de datos dentro de laDatos
		return ($('#FormMedicamentoCTC1').serializeArray()).concat($('#FormMedicamentoCTC2').serializeArray()).concat($('#FormMedicamentoCTC3').serializeArray()).concat($('#FormMedicamentoCTC4').serializeArray()) ;
	}
}