var oInterpretacion = {

	gotableIE : $('#tblInterpretacion'),
	lcTitulo : 'Interpretación de Exámenes',
	lcMensajeError : '',
	goColorFila:  {
		'3': '#cce8fd',
		'50': '#b9ffb9',
		'51': '#c8aadc',
		'52': '#99dfe0',
		'65': '#ffff00',
		'66': '#06ff06',
		'69': '#82aafa',
	},

	inicializar: function()
	{
		this.IniciarTabla();
		this.ConsultarInterpretacion();
		$('#btnAceptar').on('click', this.validarInterpretacion);	
		$('#btnLaboratorios').on('click', this.abrirlaboratorios);	
		$('#btnAgility').on('click', this.abrirAgility);	
	},

	// Consultar procedimientos para interpretación de Exámenes
	ConsultarInterpretacion: function() {
		$.ajax({
			type: "POST",
			url: "vista-evoluciones/ajax/interpretacion.php",
			data: {ingreso: aDatosIngreso['nIngreso'], tipo: 'Procedimientos'},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.DATOS!==[]) {
						oInterpretacion.CargarInterpretacion(toDatos);
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de procedimientos para Interpretación de Exámenes');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar procedimientos para Interpretación de Exámenes.");
		});
	},

	// Cargar Interpretacion en la tabla 
	CargarInterpretacion: function(taDatos) {
		var aInterpreta = [];

		$.each(taDatos.DATOS,function(lckey, loValor){
			aInterpreta.push(loValor);
		});
		oInterpretacion.gotableIE.bootstrapTable('append', aInterpreta);
	},

	IniciarTabla: function()
	{
		$('#tblInterpretacion').bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			locale: 'es-ES',
			undefinedText: 'N/A',
			toolbar: '#toolBarLst',
			height: '400',
			pagination: false,
			rowStyle: this.formatoColorInterpreta,
			iconSize: 'sm',
			columns: [
			{
				title: 'Vista',
				align: 'center',
				clickToSelect: false,
				events: this.eventoVistaPrevia,
				formatter: this.formatoVistaPreliminar
			},
			{
				title: 'CUPS',
				field: 'CUPS',
				align: 'center'
			},
			{
				title: 'Descripción',
				field: 'DESCRIPCION',
				align: 'center',
				sortable: true
			}, {
				title: 'Fecha Ord.',
				field: 'FECHORD',
				align: 'center',
				formatter: function(tnValor, toFila) { return strNumAFecha(tnValor,'/'); }
			},{
				title: 'Hora Ord.',
				field: 'HORAORD',
				align: 'center',
				formatter: function(tnValor, toFila) { return strNumAHora(tnValor); }
			},
			{
				title: 'Estado',
				field: 'ESTADO',
				align: 'center'
			},
			{
				title: 'Observaciones',
				field: 'OBSERVA',
				align: 'center'
			},{
				title: 'Normal',
				field: 'NORMAL',
				align: 'center',
				formatter: oInterpretacion.formatoCheckbox,
				events: oInterpretacion.eventoNormal
			},
			{
				title: 'Anormal',
				field: 'ANORMAL',
				align: 'center',
				formatter: oInterpretacion.formatoCheckboxA,
				events: oInterpretacion.eventoAnormal
			},
			{
				title: 'Obligatorio',
				field: 'OBLIGATORIO',
				align: 'center'
			},
			{
				title: 'Espec. Solicitante',
				field: 'ESPSOLICITA',
				align: 'center'
			},
			{
				title: 'Médico',
				field: 'MEDICOSOL',
				align: 'center'
			}
			]
		});
	},

	formatoColorInterpreta: function (toFila, tnIndice) {
		var lcColor='0';
		lcColor = toFila['CODESTADO'];
		return oInterpretacion.goColorFila[lcColor]? {css: {'background-color':oInterpretacion.goColorFila[lcColor]}}: {};
	},

	eventoNormal: {
		'click .intNormal': function (e, tnValor, toFila, tnIndex) {
			var lnCodCit=toFila['CODCIT'];
			if(tnValor==0){
					oInterpretacion.gotableIE.bootstrapTable('updateCell',{index:tnIndex, field:'NORMAL', value:1});
					oInterpretacion.gotableIE.bootstrapTable('updateCell',{index:tnIndex, field:'ANORMAL', value:0});
					oInterpretacion.gotableIE.bootstrapTable('updateCell',{index:tnIndex, field:'OBSERVA', value:'EXAMEN BAJO PARÁMETROS NORMALES'});
			}else{
					oInterpretacion.gotableIE.bootstrapTable('updateCell',{index:tnIndex, field:'NORMAL', value:0});
					oInterpretacion.gotableIE.bootstrapTable('updateCell',{index:tnIndex, field:'ANORMAL', value:0});
					oInterpretacion.gotableIE.bootstrapTable('updateCell',{index:tnIndex, field:'OBSERVA', value:''});
			}
		}
	},

	eventoAnormal: {
		'click .intAnormal': function (e, tnValor, toFila, tnIndex) {
			if(tnValor==0){
				$("#edtInterpretacion").attr('indice',tnIndex);
				$("#divObservaAnormal").modal('show');
				$('#edtInterpretacion').focus();
			}else{
				fnConfirm('Desea desmarcar este procedimiento ?, perderá información de la interpretación', false, false, false, false, function(){
					oInterpretacion.gotableIE.bootstrapTable('updateCell',{index:tnIndex, field:'NORMAL', value:0});
					oInterpretacion.gotableIE.bootstrapTable('updateCell',{index:tnIndex, field:'ANORMAL', value:0});
					oInterpretacion.gotableIE.bootstrapTable('updateCell',{index:tnIndex, field:'OBSERVA', value:''});
					$("#edtInterpretacion").val('');
				});
			}
		}
	},

	eventoVistaPrevia : {
		'click .vistaPreliminar': function(e, tcValor, toFila, tnIndice) {
			oInterpretacion.fValidarVista(toFila);
		}
	},

	formatoCheckbox: function(tnValor, toFila){
		return [
			'<a class="intNormal" id="intNormal-'+toFila['CODCIT']+'" href="javascript:void(0)" title="Normal">',
			'<i class="fa '+(tnValor==1 ? 'fa-check-square' : 'fa-square')+'"></i>',
			'</a>'
		].join('');
	},

	formatoCheckboxA: function(tnValor, toFila){
		return [
			'<a class="intAnormal" id="intAnormal-'+toFila['CODCIT']+'" href="javascript:void(0)" title="Anormal">',
			'<i class="fa '+(tnValor==1 ? 'fa-check-square' : 'fa-square')+'"></i>',
			'</a>'
		].join('');
	},

	formatoVistaPreliminar: function(){
		return '<a class="vistaPreliminar" href="javascript:void(0)" title="Vista Previa"><i class="fas fa-eye"></i></a>';
	},

	validarInterpretacion: function () {
		var lcInterpreta = ($("#edtInterpretacion").val()).trim();
		var lnIndice = $("#edtInterpretacion").attr("indice");

		if (lcInterpreta.length<10){
			$('#edtInterpretacion').focus();
			fnAlert( 'Interpretación debe ser mas completa(mínimo 10 caracteres), Revise por favor.');
			return false;
		}else{
			oInterpretacion.gotableIE.bootstrapTable('updateCell',{index:lnIndice, field:'NORMAL', value:0});
			oInterpretacion.gotableIE.bootstrapTable('updateCell',{index:lnIndice, field:'ANORMAL', value:1});
			oInterpretacion.gotableIE.bootstrapTable('updateCell',{index:lnIndice, field:'OBSERVA', value:lcInterpreta});
			$("#edtInterpretacion").val('');
		}
	},

	fValidarVista: function(toData) {
		$.ajax({
			type: "POST",
			url: "vista-evoluciones/ajax/interpretacion.php",
			data: {tipo: 'Interpretacion', ingreso: aDatosIngreso['nIngreso'], Cups: toData.CUPS, Estado: toData.CODESTADO, CodCita: toData.CODCIT, CodEspec: toData.CODESP},
			dataType: "json"
		})
		.done(function(toDatos) {
			try {
				if (toDatos.error=='') {
					if (toDatos.RUTA.Ruta!=='') {
						lcRuta = toDatos.RUTA.Ruta;

						if(lcRuta=='LIBROHC'){
							oInterpretacion.fVistaPrevia(toData);
						}else{
							if(toDatos.RUTA.Acceso !== ''){
								lcRuta = toDatos.RUTA.Ruta + aDatosIngreso.cTipId + aDatosIngreso.nNumId + '&AccessionNumber=' + toDatos.RUTA.Acceso + '&user=MEDICO&password=medico';
							}else{
								lcRuta = toDatos.RUTA.Ruta + 'Usuario.cgi?AccionServidor=AccionImprimirNShaio&Alias=HIS&Clave=HIS&NShaio=' + aDatosIngreso['nIngreso'] + '&CodProcedimiento=' + toData.CODCIT;
							}
							if (lcRuta!=='') {
								window.open(lcRuta, "_blank");
							}
						}
					}
				} else {
					fnAlert(toDatos.Error);
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de vista preliminar para Interpretación de Exámenes');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar vista preliminar para Interpretación de Exámenes.");
		});
	},

	fVistaPrevia: function(toData) {
		lcTipoDocum = '1000';
		var laEnvio = {
			nIngreso	: toData.NINORD,
			cTipDocPac	: toData.TIDORD,
			nNumDocPac	: toData.NIDORD,
			cRegMedico	: toData.RMRORD,
			cTipoDocum	: lcTipoDocum,
			cTipoProgr	: toData.PGRCUP,
			tFechaHora	: toData.FECHAREA + toData.HORAREA ,
			nConsecCita	: toData.CODCIT,
			nConsecCons	: 0,
			nConsecEvol	: toData.EVOORD,
			nConsecDoc	: '',
			cCUP		: toData.CUPS,
			cCodVia		: '',
			cSecHab		: '',
		};
		oModalVistaPrevia.mostrar(laEnvio);
	},

	abrirlaboratorios: function()
	{
		lcRuta = "http://srvlablisweb/cgi/Usuario.cgi?AccionServidor=AccionOrdenesNShaio&Alias=HIS&Clave=HIS&NShaio=" + aDatosIngreso['nIngreso'];
		window.open(lcRuta, "_blank");
	},
	
	abrirAgility: function()
	{
		lcRuta ="http://xero.shaio.org/xero/?theme=epr&PatientId=" + aDatosIngreso['cTipId'] + aDatosIngreso['nNumId'] + "&user=MEDICO&password=medico";
		window.open(lcRuta, "_blank");
	},

	// VALIDACION PRINCIPAL
	validacion: function() 
	{
		let lbValido = this.validarExamenes();
		return lbValido;
	},
	
	validarExamenes: function() {
		let llValidar = true;
		let TablaInterpreta = oInterpretacion.gotableIE.bootstrapTable('getData');
		if(TablaInterpreta != ''){
			llSalida = ($("#selConductaSeguir").val()==='01');
			$.each(TablaInterpreta, function( lcKey, loTipo ) {
				if(llSalida == true){
					if(loTipo['NORMAL']==0 && loTipo['ANORMAL']==0){
						oInterpretacion.lcMensajeError = 'Falta interpretar procedimientos, Revise por favor.';
						llValidar = false;
					}
				} else {
					if(loTipo['NORMAL']==0 && loTipo['ANORMAL']==0 && loTipo['ESPSOL']==loTipo['MEDACT']){
						oInterpretacion.lcMensajeError = 'Falta interpretar procedimientos, Revise por favor.';
						llValidar = false;
					}
				}
			});
		};
		return llValidar;
	},

	obtenerDatos: function()
	{
		//serialización de datos dentro de laDatos
		return oInterpretacion.gotableIE.bootstrapTable('getData');
	}
}
