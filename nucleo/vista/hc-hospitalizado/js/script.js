var goTabla=$('#tblPacientes'),
	goMobile = new MobileDetect(window.navigator.userAgent),
	gcUrlajax = "vista-hc-hospitalizado/ajax/ajax",
	goFila = null,
	goMenuModal = null,
	gcTipoEvolucion = '';

$(function () {
	IniciarListas();
	IniciarTabla();
	oModalTrasladoPacientes.inicializar();
	IniciarOpcionesMenuOpc('hospitalizado');
	if (gnIngresoSmartRoom>0) {
		$('#btnLimpiar,#btnBuscar').remove();
		$('#txtIngreso').val(gnIngresoSmartRoom);
		$('#txtIngreso,#selSeccion').attr('disabled',true);
		buscarPacientes();
	} else {
		if (gnIngresoSmartRoom<0) {
			buscarPacientes();
			$('#btnLimpiar').on('click', limpiar);
			$('#btnBuscar').on('click', buscarPacientes);
		} else {
			fnAlert('Habitación sin paciente registrado');
		}
	}
})

function limpiar(){
	goTabla.bootstrapTable('refreshOptions', {data: {}});
	$("#frmFiltros").get(0).reset();
}

function buscarPacientes(){
	goTabla.bootstrapTable('refreshOptions', {data: {}});
	goTabla.bootstrapTable('showLoading');
	$.ajax({
		type: "POST",
		url: gcUrlajax,
		data: {
			accion:'pacientes',
			ingreso: $("#txtIngreso").val(),
			seccion: $("#selSeccion").val(),
		},
		dataType: "json"
	})
	.done(function(loDatos) {
		try {
			if (loDatos.error == ''){
				goTabla.bootstrapTable('refreshOptions', {data: loDatos.datos});
				gdFechaConsulta = new Date(loDatos.fechahora).getTime();
			} else {
				fnAlert(loDatos.error)
			}
		} catch(err) {
			fnAlert('No se pudo realizar la consulta de estados.')
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		goTabla.bootstrapTable('hideLoading');
		console.log(jqXHR.responseText);
		fnAlert('Se presentó un error al consultar estados.');
	});
}


function IniciarListas(){
	$("#selSeccion").secciones({tipo: '1', verCodigo: '1'});
}


function IniciarTabla() {
	goTabla.bootstrapTable({
		classes: 'table table-bordered table-hover table-sm table-responsive-sm table-striped',
		theadClasses: 'thead-light',
		locale: 'es-ES',
		undefinedText: '',
		toolbar: '#toolBarLstIntrExam',
		height: '600',
		pagination: false,
		columns: [
			{
				title: 'Opc.',
				align: 'center',
				events: eventoOpciones,
				formatter: '<a class="opcionesHC" href="javascript:void(0)" title="Historia Clínica"><i class="fas fa-list-ol"></i></a>'
			},
			{
				title: 'Ingreso',
				field: 'INGRESO',
				sortable: true,
				formatter: function(tnValor, toFila){ return '<b>'+tnValor+'</b>'; }
			},
			{
				title: 'Fecha Ing',
				field: 'FECHA_ING',
				formatter: function(tnValor, toFila){ return strNumAFecha(tnValor,'/'); }
			},
			{
				title: 'Vía Ingreso',
				field: 'DESVIA'
			},
			{
				title: 'Documento',
				sortable: true,
				formatter: function(tnValor, toFila){ return '<b>'+toFila.TIPO_DOC+' '+toFila.NUM_DOC+'</b>';},
				width: 7, widthUnit: "rem"
			},
			{
				title: 'Paciente',
				field: 'PACIENTE',
				sortable: true,
				formatter: formatoPaciente,
				width: 20, widthUnit: "rem"
			},
			{
				title: 'Edad',
				field: 'EDAD',
				formatter: formatoEdad,
				width: 5, widthUnit: "rem"
			},
			{
				title: 'Hab',
				field: 'HABITACION',
				formatter: function(tnValor, toFila){ return toFila.SECCION+'-'+toFila.HABITACION;},
				width: 5, widthUnit: "rem"
			},
			{
				title: 'Aseguradora',
				field: 'PLAN'
			},
			{
				title: 'Médico Tratante',
				field: 'MEDICO'
			},
			{
				title: 'Especialidad',
				field: 'ESPECIALIDAD'
			}
		],
	});
}

function formatoPaciente(tnValor, toFila) {
	return '<i class="fa fa-'+(oGenerosPaciente.gaDatosGeneros[toFila.CODGENERO]? oGenerosPaciente.gaDatosGeneros[toFila.CODGENERO]['IMAGEN']: '')+'" style="font-size:1.4em;"></i> <b>'+toFila.PACIENTE+'</b>';
}

function formatoEdad(tnValor, toFila) {
	var laEdad = tnValor.split('-');
	return laEdad[0]>0 ? laEdad[0]+' Años' : (laEdad[1]>0 ? laEdad[1]+' Meses' : laEdad[2]+' Días');
}

var eventoOpciones = {
	'click .opcionesHC': function(e, tcValor, toFila, tnIndice) {
		goFila = toFila;
		mostrarMenuIngreso(goFila.INGRESO)
	}
}
