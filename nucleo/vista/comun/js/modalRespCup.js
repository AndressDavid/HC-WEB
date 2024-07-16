var oModalRespCup = {
	oModal: null,
	oTabla: null,
	cIcono: 'fas fa-poll',
	cTitulo: 'Consulta Procedimientos',
	cSubtitulo: '<div class="row"><div class="col"><b><<ingreso>></b> - <b><<paciente>></b></div></div>',
	cContenido: '<div id="divPacienteResultadosCup" class="container-fluid"></div><small><table id="tblResultadosCup"></table></small>',
	cConfig: {
		'GLUCOMETRIAS': {
			titulo: 'Consulta Glucometrías',
			ancho:'xl',
			alto:400,
			columnas: [
				{
					title: 'Cama',
					field: 'sechab'
				},
				{
					title: 'Fecha/Hora Reporte',
					field: 'fecha'
				},
				{
					title: 'Valor Glucometría',
					field: 'ValorGluc'
				},
				{
					title: 'Observaciones Glucometría',
					field: 'ObsGluc'
				},
				{
					title: 'Médico Ordena',
					formatter: function(tnValor, toFila) {
						return toFila.medOrdNm+' '+toFila.medOrdAp;
					}
				},
				{
					title: 'Fecha/Hora Orden',
					field: 'fechaOrd'
				},
				{
					title: 'Observaciones Médicas',
					field: 'ObsMed'
				},
				{
					title: 'Cita',
					field: 'cnsCita'
				}
			]
		},
		'GASESART48': {
			titulo: 'Gases Arteriales (48 hr)',
			ancho:'m',
			alto:300,
			columnas: [
				{
					title: 'Fecha/Hora Reporte',
					field: 'fecha'
				},
				{
					title: 'Cama',
					field: 'sechab'
				},
				{
					title: 'Cita',
					field: 'cnsCita'
				},
				{
					title: 'Vista',
					align: 'center',
					events: {
						'click .verDocHtml': function(e, tcValor, toFila, tnIndice) {
							oModalRespCup.consultarDocumento(toFila, 'HTML');
						}
					},
					formatter: function(tnValor, toFila) {
						return '<a class="verDocHtml" href="javascript:void(0)" title="Vista Previa" ><i class="fas fa-eye" style="color: #444;"></i></a>';
					}
				}
			]
		},
	},
	nIngreso:0, cTipDoc:'', nNumDoc:0,


	iniciarTabla: function(tcTipo)
	{
		var laColumnas = [];
		oModalRespCup.oTabla = $('#tblResultadosCup');
		oModalRespCup.oTabla.bootstrapTable({
			classes: 'table table-bordered table-hover table-sm table-responsive-sm', // table-striped
			theadClasses: 'thead-light',
			locale: 'es-ES',
			undefinedText: '',
			toolbar: '#toolBarResultCup',
			height: oModalRespCup.cConfig[tcTipo].alto,
			pagination: false,
			sortName: 'fecha', sortOrder:'desc',
			columns: oModalRespCup.cConfig[tcTipo].columnas
		});
	},

	consultar: function(tcTipo, tnIngreso)
	{
		oModalRespCup.nIngreso=0; oModalRespCup.cTipDoc=''; oModalRespCup.nNumDoc=0;
		this.oTabla.bootstrapTable('showLoading');
		$.post(
			'vista-comun/ajax/consultaResultCup',
			{tipo:tcTipo, ingreso:tnIngreso, accion:'consultaRealizados'},
			function(loDatos){
				try {
					if (loDatos.error == '') {
						oModalRespCup.oTabla.bootstrapTable('refreshOptions', {data: loDatos.REALIZADOS['LISTA']});
						oModalRespCup.nIngreso = tnIngreso;
						oModalRespCup.cTipDoc = loDatos.REALIZADOS['TIPDOC'];
						oModalRespCup.nNumDoc = loDatos.REALIZADOS['NUMDOC'];
					} else {
						fnAlert(loDatos.error)
					}
				} catch(err) {
					fnAlert('No se pudo realizar la consulta de procedimientos.')
				}
			},
			'json')
		.fail(function(jqXHR, textStatus, errorThrown) {
			oModalRespCup.oTabla.bootstrapTable('hideLoading');
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar procedimientos.');
		});
	},

	consultarDocumento: function(toFila, tcTipo)
	{
		var laEnvio = {
			nIngreso	: oModalRespCup.nIngreso,
			cTipDocPac	: oModalRespCup.cTipDoc,
			nNumDocPac	: oModalRespCup.nNumDoc,
			cRegMedico	: toFila.medRegMd,
			cTipoDocum	: toFila.tipoDoc,
			cTipoProgr	: toFila.tipoPrg,
			tFechaHora	: toFila.fecha,
			nConsecCita	: toFila.cnsCita,
			nConsecCons	: toFila.cnsCons,
			nConsecEvol	: toFila.cnsEvo,
			nConsecDoc	: toFila.cnsDoc,
			cCUP		: toFila.codCup,
			cCodVia		: toFila.codvia,
			cSecHab		: toFila.sechab,
		};
		if (tcTipo=='PDF') {
			vistaPreviaPdf({datos:JSON.stringify([laEnvio])});
		} else if (tcTipo=='HTML') {
			oModalVistaPrevia.mostrar(laEnvio);
		}
	},

	mostrar: function(tcTipo, tnIngreso, tcPaciente, tfFuncionCerrar)
	{
		this.oModal = fnDialog(
			oModalRespCup.cContenido,
			oModalRespCup.cConfig[tcTipo].titulo,
			oModalRespCup.cIcono,
			'dark',
			oModalRespCup.cConfig[tcTipo].ancho,
			tfFuncionCerrar,
			{
				onOpen: function(){
					$('body').css({overflow:'hidden'});
					oModalRespCup.iniciarTabla(tcTipo);
					oModalRespCup.consultar(tcTipo, tnIngreso);
					$("#divPacienteResultadosCup").html(oModalRespCup.cSubtitulo.replace('<<ingreso>>',tnIngreso).replace('<<paciente>>',tcPaciente));
				}
			}
		);
	},

	ocultar: function()
	{
		this.oModal.close();
	}
}